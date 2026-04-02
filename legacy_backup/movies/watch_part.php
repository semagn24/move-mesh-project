
<?php
session_start();
require_once dirname(__DIR__) . "/config/app.php";
require_once ROOT_PATH . 'config/db.php';

/* ================= VALIDATE MOVIE ID ================= */
$movie_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

// Reject invalid, missing, zero or negative ids
if ($movie_id === false || $movie_id === null || $movie_id <= 0) {
    header("Location: " . BASE_URL . "movies/catalog.php");
    exit();
}

/* ================= FETCH MOVIE ================= */
$stmt = $pdo->prepare("SELECT * FROM movies WHERE id = ?");
$stmt->execute([$movie_id]);
$movie = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$movie) {
    header("Location: " . BASE_URL . "movies/catalog.php");
    exit();
}

/* ================= SUBSCRIPTION CHECK ================= */
$access_allowed = true;
$lock_reason = "";

$is_premium = isset($movie['is_premium']) && $movie['is_premium'] == 1;
if ($is_premium) {
    if (!isset($_SESSION['user_id'])) {
        $access_allowed = false;
        $lock_reason = "This is a premium movie. Please <a href='" . BASE_URL . "auth/login.php'>Sign In</a> to watch.";
    } else {
        // Check user's subscription status
        $u_stmt = $pdo->prepare("SELECT subscription_status, subscription_expiry FROM users WHERE id = ?");
        $u_stmt->execute([$_SESSION['user_id']]);
        $user_sub = $u_stmt->fetch(PDO::FETCH_ASSOC);

        $now = new DateTime();
        $expiry = isset($user_sub['subscription_expiry']) && $user_sub['subscription_expiry'] ? new DateTime($user_sub['subscription_expiry']) : null;

        if (!isset($user_sub['subscription_status']) || $user_sub['subscription_status'] !== 'premium' || ($expiry && $expiry < $now)) {
            $access_allowed = false;
            $lock_reason = "ðŸ’Ž This content requires an active Premium Subscription.";
        }
    }
}

/* ================= UPDATE VIEWS & HISTORY (only if allowed) ================= */
if ($access_allowed) {
    $pdo->prepare("UPDATE movies SET views = views + 1 WHERE id = ?")
        ->execute([$movie_id]);

    if (isset($_SESSION['user_id'])) {
        $user_id = (int) $_SESSION['user_id'];

        // Requires UNIQUE (user_id, movie_id)
        $stmt = $pdo->prepare("\n            INSERT INTO history (user_id, movie_id, watched_at, progress)\n            VALUES (?, ?, NOW(), 0)\n            ON DUPLICATE KEY UPDATE watched_at = NOW()\n        ");
        $stmt->execute([$user_id, $movie_id]);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Watch <?= htmlspecialchars($movie['title']) ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <style>
        body {
            margin: 0;
            background: #141414;
            color: #fff;
            font-family: "Segoe UI", Arial, sans-serif;
            padding: 20px;
        }

        h2 {
            margin-bottom: 15px;
        }

        video {
            width: 100%;
            max-width: 900px;
            border-radius: 12px;
            background: #000;
        }

        .info {
            max-width: 900px;
            margin-top: 20px;
            background: #1c1c1c;
            padding: 20px;
            border-radius: 12px;
        }

        .info p {
            margin: 6px 0;
            color: #ccc;
        }

        a {
            color: #e50914;
            font-weight: bold;
            text-decoration: none;
            margin-right: 15px;
        }

        a:hover {
            text-decoration: underline;
        }
    </style>
</head>

<body>

<h2>ðŸŽ¬ <?= htmlspecialchars($movie['title']) ?></h2>

<?php if (!$access_allowed): ?>
    <div style="background: #1c1c1c; padding: 60px; text-align: center; border-radius: 12px; border: 1px dashed #e50914; max-width:900px; margin-top:10px;">
        <h2 style="color: #e50914; margin-top:0;">Content Locked</h2>
        <p style="color:#ccc"><?= $lock_reason ?></p>
        <a href="<?= BASE_URL ?>users/subscribe.php" style="display:inline-block; margin-top:15px; background:#e50914; color:white; padding:12px 24px; text-decoration:none; border-radius:5px; font-weight:bold;">Get Premium Access</a>
    </div>
<?php else: ?>

<div id="videoContainer" style="max-width:900px; margin-top:10px; position:relative;"></div>

<div id="resumeOverlay" style="max-width:900px; margin-top:10px; position:relative;">
    <div id="resumeBox" style="position:absolute; top:8px; left:8px; background:rgba(0,0,0,0.85); color:#fff; padding:8px 12px; border-radius:8px; display:none; z-index:5;">
        <div style="margin-bottom:6px;">Resume from <strong id="resumePercent">0</strong>%</div>
        <div>
            <button id="resumeBtn" style="margin-right:6px;">Resume</button>
            <button id="restartBtn" style="margin-right:6px;">Start Over</button>
            <button id="dismissBtn">Dismiss</button>
        </div>
    </div>
</div>

<div id="playerControls" style="max-width:900px; margin-top:8px; display:flex; align-items:center; gap:10px;">
    <button id="rewindBtn" title="Rewind 10 seconds" aria-label="Rewind 10 seconds" style="padding:8px 12px; border-radius:6px; background:rgba(0,0,0,0.5); color:#fff; border:1px solid #333;">âŸ² Rewind 10s</button>
    <button id="forwardBtn" title="Forward 10 seconds" aria-label="Forward 10 seconds" style="padding:8px 12px; border-radius:6px; background:rgba(0,0,0,0.5); color:#fff; border:1px solid #333;">Forward 10s âŸ³</button>
    <input id="seekRange" type="range" min="0" max="100" value="0" step="0.1" style="flex:1;">
    <span id="timeDisplay" style="min-width:120px; text-align:right; font-size:0.95rem; color:#ccc;">0:00 / 0:00</span>
</div>

<script src="https://cdn.jsdelivr.net/npm/webtorrent/webtorrent.min.js"></script>
<script>
// Resume / resume UI data from server
<?php
$isLoggedIn = isset($_SESSION['user_id']);
$lastTime = 0;
$lastProgress = 0;
if ($isLoggedIn) {
    $stmt = $pdo->prepare("SELECT last_time, progress FROM history WHERE user_id = ? AND movie_id = ?");
    $stmt->execute([$_SESSION['user_id'], $movie_id]);
    $row = $stmt->fetch();
    if ($row) {
        $lastTime = (float)$row['last_time'];
        $lastProgress = (int)$row['progress'];
    }
}
$magnetLink = $movie['magnet_link'] ?? '';
?>
const movieId = <?= $movie_id ?>;
const isLoggedIn = <?= $isLoggedIn ? 'true' : 'false' ?>;
const lastTimeFromServer = <?= json_encode($lastTime) ?>;
const lastProgressFromServer = <?= json_encode($lastProgress) ?>;
const magnetLink = <?= json_encode($magnetLink) ?>;

function showResumeOverlay(progressPercent, timeSeconds, video) {
    const overlay = document.getElementById("resumeBox");
    document.getElementById("resumePercent").textContent = progressPercent;
    overlay.style.display = "block";

    document.getElementById("resumeBtn").onclick = () => {
        video.currentTime = Math.min(timeSeconds, video.duration ? video.duration - 0.1 : timeSeconds);
        overlay.style.display = "none";
        video.play();
    };
    document.getElementById("restartBtn").onclick = () => {
        video.currentTime = 0;
        overlay.style.display = "none";
        video.play();
    };
    document.getElementById("dismissBtn").onclick = () => {
        overlay.style.display = "none";
    };
}

function initPlayer(video) {
    // --- Attach event handlers and controls (migrated from previous inline script) ---
    const seekRange = document.getElementById('seekRange');
    const rewindBtn = document.getElementById('rewindBtn');
    const forwardBtn = document.getElementById('forwardBtn');
    const timeDisplay = document.getElementById('timeDisplay');
}
</script>
<?php endif; ?>
</body>
</html>
