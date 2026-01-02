<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/app.php'; 

$movie_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$movie_id) { header("Location: catalog.php"); exit(); }

/* ================= FETCH MOVIE ================= */
$stmt = $pdo->prepare("SELECT * FROM movies WHERE id = ?");
$stmt->execute([$movie_id]);
$movie = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$movie) { die("Movie not found."); }

/* ================= CHECK FAVORITE ================= */
$is_favorited = false;
if (isset($_SESSION['user_id'])) {
    $f_stmt = $pdo->prepare("SELECT 1 FROM favorites WHERE user_id = ? AND movie_id = ?");
    $f_stmt->execute([$_SESSION['user_id'], $movie_id]);
    $is_favorited = (bool)$f_stmt->fetch();
}

/* ================= FETCH PROGRESS (FIXED QUERY) ================= */
$lastTime = 0;
if (isset($_SESSION['user_id'])) {
    // Using created_at instead of id to avoid the column error
    $h_stmt = $pdo->prepare("SELECT last_time FROM history WHERE user_id = ? AND movie_id = ? ORDER BY watched_at DESC LIMIT 1");
    $h_stmt->execute([$_SESSION['user_id'], $movie_id]);
    $h_row = $h_stmt->fetch();
    $lastTime = $h_row ? (float)$h_row['last_time'] : 0;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Watching <?= htmlspecialchars($movie['title']) ?></title>
    <link href="https://vjs.zencdn.net/8.10.0/video-js.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { margin: 0; background: #050505; color: white; font-family: sans-serif; }
        .container { max-width: 1000px; margin: 20px auto; padding: 0 20px; }
        .video-holder { background: #000; border-radius: 15px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.5); }
        .movie-meta { display: flex; justify-content: space-between; align-items: center; margin-top: 20px; }
        .fav-btn { 
            background: #222; border: 1px solid #444; color: white; padding: 10px 20px; 
            border-radius: 50px; cursor: pointer; transition: 0.3s;
        }
        .fav-btn.active { background: #E50914; border-color: #E50914; }

        /* Toast Notification */
        #toast {
            visibility: hidden;
            min-width: 250px;
            background-color: #333;
            color: #fff;
            text-align: center;
            border-radius: 8px;
            padding: 16px;
            position: fixed;
            z-index: 1000;
            left: 50%;
            bottom: 30px;
            transform: translateX(-50%);
            box-shadow: 0 10px 30px rgba(0,0,0,0.5);
            border-left: 4px solid #E50914;
            font-family: sans-serif;
        }
        #toast.show { visibility: visible; animation: fadein 0.5s, fadeout 0.5s 2.5s; }
        @keyframes fadein { from {bottom: 0; opacity: 0;} to {bottom: 30px; opacity: 1;} }
        @keyframes fadeout { from {bottom: 30px; opacity: 1;} to {bottom: 0; opacity: 0;} }

        /* Chaos Mode Button */
        .chaos-btn {
            background: #ff9800; border: none; color: black; padding: 10px 15px; 
            border-radius: 5px; cursor: pointer; font-weight: bold; margin-left: 10px;
            transition: 0.3s; display: flex; align-items: center; gap: 5px;
        }
        .chaos-btn:hover { background: #f57c00; transform: scale(1.05); }
    </style>
</head>
<body>

<div class="container">
    <div class="video-holder">
        <video id="vid-player" class="video-js vjs-16-9 vjs-big-play-centered" controls preload="auto" 
               poster="<?= BASE_URL ?>uploads/posters/<?= $movie['poster'] ?>">
            <source src="<?= BASE_URL ?>uploads/videos/<?= htmlspecialchars($movie['video']) ?>" type="video/mp4">
        </video>
    </div>

    <div class="movie-meta">
        <div>
            <h1><?= htmlspecialchars($movie['title']) ?></h1>
            <p style="color: #888;"><?= $movie['genre'] ?> • <?= $movie['rating'] ?>/10</p>
        </div>
        
        <?php if(isset($_SESSION['user_id'])): ?>
        <div style="display: flex; align-items: center;">
            <button id="favToggle" class="fav-btn <?= $is_favorited ? 'active' : '' ?>" onclick="handleFavorite(<?= $movie_id ?>)">
                <i class="fa-heart <?= $is_favorited ? 'fa-solid' : 'fa-regular' ?>"></i> 
                <span><?= $is_favorited ? 'Saved' : 'Save to List' ?></span>
            </button>
            <button class="chaos-btn" onclick="startChaos()" title="Simulate Server Failure">
                <i class="fa fa-bolt"></i> chaos Mode
            </button>
        </div>
        <?php endif; ?>
    </div>
</div>

<script src="https://vjs.zencdn.net/8.10.0/video.min.js"></script>
<script>
    const player = videojs('vid-player');
    const streamServers = <?= json_encode(STREAM_SERVERS) ?>;
    let currentServerIndex = 0;
    const movieVideoFile = <?= json_encode($movie['video']) ?>;

    // FAILOVER LOGIC
    player.on('error', function() {
        const error = player.error();
        console.warn("Playback error detected:", error);

        if (currentServerIndex < streamServers.length - 1) {
            currentServerIndex++;
            const nextServer = streamServers[currentServerIndex];
            const nextSrc = nextServer + 'uploads/videos/' + movieVideoFile;
            const currentTime = player.currentTime();

            console.log("Failing over to server: " + nextServer);
            showToast("Connection issue. Switching server...");

            player.src({ type: 'video/mp4', src: nextSrc });
            player.load();
            
            player.one('loadedmetadata', function() {
                player.currentTime(currentTime);
                player.play().catch(e => console.error("Auto-play failed after failover:", e));
            });
        } else {
            showToast("All stream servers are currently unreachable.");
        }
    });

    // RESUME LOGIC
    player.ready(() => {
        const last = <?= (float)$lastTime ?>;
        if (last > 10) {
            if (confirm("Resume watching from " + Math.floor(last/60) + "m " + Math.floor(last%60) + "s?")) {
                player.currentTime(last);
            }
        }
    });

    // FAVORITE LOGIC
    function handleFavorite(mid) {
        const btn = document.querySelector('#favToggle');
        fetch('<?= BASE_URL ?>users/toggle_favorite.php?id=' + mid)
        .then(r => r.json())
        .then(res => {
            if(res.status === 'added') {
                btn.classList.add('active');
                btn.querySelector('i').className = 'fa-solid fa-heart';
                btn.querySelector('span').innerText = 'Saved';
                showToast("Added to your list!");
            } else if(res.status === 'removed') {
                btn.classList.remove('active');
                btn.querySelector('i').className = 'fa-regular fa-heart';
                btn.querySelector('span').innerText = 'Save to List';
                showToast("Removed from your list.");
            } else {
                showToast("Error: " + (res.message || "Could not update list."));
            }
        }).catch(err => {
            showToast("Error updating list. Please check login.");
        });
    }

    function showToast(msg) {
        const x = document.getElementById("toast");
        if(x) {
            x.innerHTML = msg;
            x.className = "show";
            setTimeout(function(){ x.className = x.className.replace("show", ""); }, 3000);
        }
    }

    // CHAOS MODE - Simulate failure for Demo
    function startChaos() {
        if (confirm("This will simulate a server failure. The system should automatically switch to a backup server and resume. Proceed?")) {
            console.warn("Chaos Mode: Forcing playback error...");
            const currentTime = player.currentTime();
            // Point to a non-existent file to trigger the 'error' event
            player.src({ type: 'video/mp4', src: '<?= BASE_URL ?>uploads/videos/non_existent_failover_test.mp4' });
            player.load();
        }
    }

    // AUTO-SAVE PROGRESS (Every 5 seconds for tighter synchronization)
    setInterval(() => {
        if (!player.paused()) {
            fetch('<?= BASE_URL ?>users/save_progress.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({
                    movie_id: <?= $movie_id ?>,
                    current_time: player.currentTime(),
                    progress: Math.floor((player.currentTime() / player.duration()) * 100) || 0
                })
            });
        }
    }, 5000);
</script>

<div id="toast"></div>

</body>
</html>