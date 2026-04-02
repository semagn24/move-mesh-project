<?php
session_start();
require_once __DIR__ . "/../config/app.php";
require_once ROOT_PATH . "config/db.php";

/* ================= AUTH CHECK ================= */
if (!isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "auth/login.php");
    exit();
}

$user_id = (int) $_SESSION['user_id'];

/* ================= FETCH USER ================= */
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) die("User not found.");

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
<title><?= htmlspecialchars($user['username']); ?> • Profile</title>

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

<style>
/* =====================================================
🚀   NETFLIX-STYLE PROFILE DESIGN
===================================================== */
:root {
    --primary:#E50914;
    --text:#fff;
    --muted:#9b9b9b;
    --bg:#0F0F0F;
    --card:#1b1b1b;
    --radius:14px;
}

*{box-sizing:border-box;}

body{
    margin:0;
    font-family:'Inter',sans-serif;
    background:var(--bg);
    color:var(--text);
}

.container{
    max-width:1300px;
    margin:auto;
    padding:20px;
}

/* HEADER */
.profile-header{
    background:url('https://images.unsplash.com/photo-1517602302552-471fe67acf66?auto=format&fit=crop&w=1950&q=80') center/cover;
    padding:60px 25px;
    border-radius:var(--radius);
    position:relative;
    display:flex;
    flex-wrap:wrap;
    gap:30px;
    align-items:center;
}

.settings-btn {
    position:absolute;
    top:20px;
    right:25px;
    color:#fff;
    font-size:23px;
    transition:.3s;
}
.settings-btn:hover {color:var(--primary); transform:rotate(90deg);}

/* AVATAR */
.avatar{
    width:120px; height:120px;
    border-radius:50%;
    border:3px solid var(--primary);
    background:rgba(255,255,255,.1);
    overflow:hidden;
    display:flex; justify-content:center; align-items:center;
    font-size:45px; font-weight:700;
    color:var(--primary);
}

.avatar img{width:100%; height:100%; object-fit:cover;}

/* USER INFO */
.info h1{margin:0; font-size:2.4rem;}
.info p{margin:6px 0; color:var(--muted); font-size:0.9rem;}

.stats{
    display:flex; gap:20px;
    margin-top:10px; flex-wrap:wrap;
}
.stat{
    background:rgba(255,255,255,.08);
    padding:10px 15px;
    border-radius:8px;
    font-size:.85rem;
}
.stat span{color:var(--primary); font-weight:700;}

.logout-btn{
    margin-left:auto;
    padding:12px 22px;
    border-radius:var(--radius);
    background:var(--primary);
    font-weight:600;
    text-decoration:none;
    color:white;
}

/* SECTIONS */
.section{
    margin-top:50px;
}
.section-title{
    border-left:4px solid var(--primary);
    padding-left:12px;
    font-size:1.6rem;
    margin-bottom:20px;
}

/* MOVIE GRID */
.grid{
    display:grid;
    grid-template-columns:repeat(auto-fill,minmax(170px,1fr));
    gap:22px;
}
.card{
    background:var(--card);
    border-radius:var(--radius);
    overflow:hidden;
    cursor:pointer;
    position:relative;
    transition:.2s;
}
.card:hover{transform:scale(1.05);}
.card img{width:100%; height:250px; object-fit:cover;}

.card-overlay{
    position:absolute; left:0; right:0; bottom:0;
    padding:12px;
    background:linear-gradient(transparent,rgba(0,0,0,.8));
    opacity:0; transition:.3s;
}
.card:hover .card-overlay{opacity:1;}
.card-overlay p{margin:0; font-weight:600;}
.play-btn{
    display:block; margin-top:8px;
    background:var(--primary);
    padding:8px; text-align:center;
    border-radius:6px; color:white; font-size:.85rem;
    text-decoration:none;
}

@media(max-width:768px){
    .profile-header{padding:25px; text-align:center; justify-content:center;}
    .logout-btn{margin:20px auto 0; display:block;}
}
</style>
</head>

<body>

<?php if (file_exists(ROOT_PATH . 'users/navibar.php')) include ROOT_PATH . 'users/navibar.php'; ?>

<div class="container">

    <section class="profile-header">
        <a href="edit_profile.php" class="settings-btn"><i class="fa-solid fa-gear"></i></a>

        <div class="avatar">
            <?php if ($user['profile_pic']): ?>
                <img src="<?= BASE_URL ?>uploads/profiles/<?= htmlspecialchars($user['profile_pic']) ?>">
            <?php else: ?>
                <?= strtoupper($user['username'][0]) ?>
            <?php endif; ?>
        </div>

        <div class="info">
            <h1><?= htmlspecialchars($user['username']) ?></h1>
            <p><i class="fa-solid fa-envelope"></i> <?= htmlspecialchars($user['email']) ?></p>

            <p><strong>Plan:</strong>
                <?php
                    $isPremium = $user['subscription_status']==='premium' && strtotime($user['subscription_expiry'])>time();
                    echo $isPremium ? "<span style='color:#2ecc71;'>Premium</span>" : "<span>Free</span>";
                ?>
            </p>

            <div class="stats">
                <div class="stat"><span><?= $total_watched ?></span> Watched</div>
                <div class="stat"><span><?= $total_favorites ?></span> Favorites</div>
            </div>
        </div>

        <a href="<?= BASE_URL ?>auth/logout.php" class="logout-btn">
            <i class="fa-solid fa-right-from-bracket"></i> Logout
        </a>
    </section>


    <!-- CONTINUE WATCHING -->
    <div class="section">
        <h2 class="section-title"><i class="fa fa-history"></i> Continue Watching</h2>
        
        <?php if (!$history): ?>
            <p style="color:gray;">You haven’t watched anything yet.</p>
        <?php else: ?>
        <div class="grid">
            <?php foreach (array_slice($history,0,8) as $m): ?>
                <div class="card">
                    <img src="<?= BASE_URL ?>uploads/posters/<?= $m['poster'] ?>">
                    <div class="card-overlay">
                        <p><?= htmlspecialchars($m['title']) ?></p>
                        <a href="<?= BASE_URL ?>movies/watch.php?id=<?= $m['id'] ?>" class="play-btn">Resume</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>

    <!-- MY LIST (FAVORITES) -->
    <div class="section">
        <h2 class="section-title"><i class="fa fa-heart"></i> My List</h2>
        
        <?php if (!$favorites): ?>
            <p style="color:gray;">Your list is empty. Start adding movies!</p>
        <?php else: ?>
        <div class="grid">
            <?php foreach ($favorites as $fav): ?>
                <div class="card">
                    <img src="<?= BASE_URL ?>uploads/posters/<?= $fav['poster'] ?>">
                    <div class="card-overlay">
                        <p><?= htmlspecialchars($fav['title']) ?></p>
                        <div style="display:flex; gap:5px;">
                            <a href="<?= BASE_URL ?>movies/watch.php?id=<?= $fav['id'] ?>" class="play-btn" style="flex:1;">Watch</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>

</div>
</body>
</html>
