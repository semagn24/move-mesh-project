<?php
session_start();
require_once __DIR__ . "/../config/db.php";

/* ================= AUTH CHECK ================= */
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

$user_id = (int) $_SESSION['user_id'];

/* ================= FETCH USER ================= */
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    die("User not found.");
}

/* ================= FETCH FAVORITES ================= */
$stmt = $pdo->prepare("
    SELECT m.*
    FROM movies m
    JOIN favorites f ON m.id = f.movie_id
    WHERE f.user_id = ?
");
$stmt->execute([$user_id]);
$favorites = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* ================= FETCH HISTORY ================= */
$stmt = $pdo->prepare(" 
    SELECT m.*, h.watched_at, h.progress
    FROM movies m
    JOIN history h ON m.id = h.movie_id
    WHERE h.user_id = ?
    ORDER BY h.watched_at DESC
");
$stmt->execute([$user_id]);
$history = $stmt->fetchAll(PDO::FETCH_ASSOC);

$total_favorites = count($favorites);
$total_watched = count($history);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile | <?= htmlspecialchars($user['username']) ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        :root {
            --primary: #e50914;
            --dark-bg: #0b0b0b;
            --card-bg: #1a1a1a;
            --sidebar-width: 240px;
        }

        body {
            margin: 0;
            font-family: 'Poppins', sans-serif;
            background: var(--dark-bg);
            color: white;
            overflow-x: hidden;
        }

        .main-wrapper {
            margin-left: var(--sidebar-width);
            transition: margin 0.3s ease;
            min-height: 100vh;
            padding: 40px 5%;
        }

        /* Profile Header Card */
        .profile-hero {
            background: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.9)), 
                        url('https://images.unsplash.com/photo-1478720568477-152d9b164e26?auto=format&fit=crop&w=1200&q=80');
            background-size: cover;
            background-position: center;
            border-radius: 20px;
            padding: 40px;
            display: flex;
            align-items: center;
            gap: 30px;
            margin-bottom: 40px;
            border: 1px solid rgba(255,255,255,0.1);
            position: relative; /* Crucial for absolute positioning of settings icon */
        }

        .settings-link {
            position: absolute;
            top: 20px;
            right: 25px;
            color: rgba(255,255,255,0.6);
            font-size: 22px;
            transition: 0.3s;
            text-decoration: none;
        }

        .settings-link:hover {
            color: var(--primary);
            transform: rotate(90deg);
        }

        .avatar-circle {
            width: 120px;
            height: 120px;
            background: var(--primary);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden; /* Clips the image to a circle */
            box-shadow: 0 10px 20px rgba(229, 9, 20, 0.3);
            border: 3px solid var(--primary);
        }

        .avatar-circle img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .user-details h1 { margin: 0; font-size: 2.5rem; }
        .user-details p { color: #aaa; margin: 5px 0; }

        .user-stats {
            display: flex;
            gap: 20px;
            margin-top: 15px;
        }

        .stat-item {
            background: rgba(255,255,255,0.1);
            padding: 8px 15px;
            border-radius: 8px;
            font-size: 0.9rem;
        }

        .stat-item span { color: var(--primary); font-weight: bold; }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            border-left: 4px solid var(--primary);
            padding-left: 15px;
        }

        .section-header h3 { margin: 0; font-size: 1.5rem; }

        .movie-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
            gap: 25px;
            margin-bottom: 50px;
        }

        .movie-card {
            background: var(--card-bg);
            border-radius: 12px;
            overflow: hidden;
            transition: 0.3s;
            position: relative;
        }

        .movie-card:hover {
            transform: scale(1.05);
            z-index: 2;
        }

        .movie-card img {
            width: 100%;
            height: 270px;
            object-fit: cover;
        }

        .movie-overlay {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: linear-gradient(transparent, black);
            padding: 15px;
            opacity: 0;
            transition: 0.3s;
            z-index: 3;
        }

        .movie-card:hover .movie-overlay { opacity: 1; }

        .btn-action {
            display: block;
            text-align: center;
            padding: 10px;
            border-radius: 6px;
            font-size: 0.85rem;
            font-weight: 600;
            text-decoration: none;
            margin-top: 5px;
        }

        .btn-play { background: var(--primary); color: white; }
        .btn-remove { background: rgba(255,255,255,0.2); color: white; margin-top: 10px; }
        .btn-remove:hover { background: #ff4d4d; }

        @media (max-width: 992px) {
            .main-wrapper { margin-left: 0; padding-top: 80px; }
            .profile-hero { flex-direction: column; text-align: center; }
            .user-stats { justify-content: center; }
        }
    </style>
</head>
<body>

<?php 
    $nav_path = __DIR__ . '/navibar.php'; 
    if(file_exists($nav_path)) include $nav_path; 
?>

<div class="main-wrapper">
    
    <section class="profile-hero">
        <a href="edit_profile.php" class="settings-link" title="Edit Profile">
            <i class="fa-solid fa-gear"></i>
        </a>

        <div class="avatar-circle">
            <?php if (!empty($user['profile_pic'])): ?>
                <img src="../uploads/profiles/<?= htmlspecialchars($user['profile_pic']) ?>" alt="Profile">
            <?php else: ?>
                <?= strtoupper(substr($user['username'], 0, 1)) ?>
            <?php endif; ?>
        </div>

        <div class="user-details">
            <h1><?= htmlspecialchars($user['username']) ?></h1>
            <p><i class="fa fa-envelope"></i> <?= htmlspecialchars($user['email']) ?></p>
            
            <p style="margin:6px 0;">
                <strong>Subscription:</strong>
                <?php 
                    $sub_status = $user['subscription_status'] ?? 'free';
                    $sub_expiry = $user['subscription_expiry'] ?? null;
                    if ($sub_status === 'premium' && $sub_expiry && strtotime($sub_expiry) > time()): 
                ?>
                    <span style="color:#2ecc71; font-weight:700">Premium until <?= date('Y-m-d', strtotime($sub_expiry)) ?></span>
                <?php else: ?>
                    <span style="color:#aaa">Free Plan</span>
                <?php endif; ?>
            </p>

            <div class="user-stats">
                <div class="stat-item"><span><?= $total_watched ?></span> Watched</div>
                <div class="stat-item"><span><?= $total_favorites ?></span> Favorites</div>
            </div>
        </div>

        <a href="../auth/logout.php" class="btn-action btn-play" style="margin-left: auto; padding: 12px 25px;">
            <i class="fa fa-sign-out-alt"></i> Logout
        </a>
    </section>

    <div class="section-header">
        <h3><i class="fa fa-history"></i> Continue Watching</h3>
    </div>

    <?php if (empty($history)): ?>
        <p style="color: #666; padding: 20px;">No movies in your history yet.</p>
    <?php else: ?>
        <div class="movie-grid">
            <?php foreach (array_slice($history, 0, 6) as $m): ?>
                <div class="movie-card">
                    <img src="../uploads/posters/<?= $m['poster'] ?>" alt="<?= htmlspecialchars($m['title']) ?>">
                    <div class="movie-overlay">
                        <p style="margin:0 0 10px; font-weight:bold;"><?= htmlspecialchars($m['title']) ?></p>
                        <a href="../movies/watch.php?id=<?= $m['id'] ?>" class="btn-action btn-play">Resume</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

</div>

</body>
</html>