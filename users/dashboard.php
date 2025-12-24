<?php
session_start();
require "../config/db.php";

if (!isset($_SESSION["user"])) { 
    header("Location: ../auth/login.php"); 
    exit();
}

$userId = $_SESSION["user"]["id"];

// Favorites
$favStmt = $pdo->prepare("
    SELECT movies.* FROM favorites 
    JOIN movies ON favorites.movie_id = movies.id
    WHERE favorites.user_id=?");
$favStmt->execute([$userId]);
$favorites = $favStmt->fetchAll();

// History
$hisStmt = $pdo->prepare("
    SELECT movies.* FROM history 
    JOIN movies ON history.movie_id = movies.id
    WHERE history.user_id=?");
$hisStmt->execute([$userId]);
$history = $hisStmt->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
<title>User Dashboard</title>
<style>
.card {
    border:1px solid #ccc;
    padding:10px;
    margin-bottom:10px;
    border-radius:5px;
}
</style>
</head>
<body>

<h2>My Dashboard</h2>
<a href="../index.php">Home</a> | <a href="profile.php">Profile</a>

<hr>

<h3>My Favorites ❤️</h3>

<?php foreach($favorites as $m): ?>
<div class="card">
    <strong><?= $m["title"] ?></strong><br>
    Genre: <?= $m["genre"] ?><br>
</div>
<?php endforeach; ?>

<hr>

<h3>Watch History 📜</h3>

<?php foreach($history as $m): ?>
<div class="card">
    <strong><?= $m["title"] ?></strong><br>
    Watched
</div>
<?php endforeach; ?>

</body>
</html>
