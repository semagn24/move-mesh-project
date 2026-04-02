<?php
session_start();
require_once "../config/db.php";

// Check if user logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch user favorites
$sql = "SELECT movies.id, movies.title, movies.poster, movies.year 
        FROM favorites 
        JOIN movies ON favorites.movie_id = movies.id
        WHERE favorites.user_id = ?
        ORDER BY favorites.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute([$user_id]);
$favorites = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
    <title>My Favorites</title>
    <style>
        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
            gap: 20px;
        }
        .movie-card {
            border: 1px solid #ccc;
            padding: 10px;
            text-align: center;
            border-radius: 6px;
        }
        img {
            width: 100%;
            height: 250px;
            object-fit: cover;
        }
    </style>
</head>
<body>

<h2>❤️ My Favorite Movies</h2>

<div class="grid">
    <?php foreach ($favorites as $m): ?>
        <div class="movie-card">
            <img src="../uploads/posters/<?= htmlspecialchars($m['poster'] ?: 'default.png') ?>" alt="<?= htmlspecialchars($m['title']) ?>">
            <h3><?= htmlspecialchars($m['title']) ?></h3>
            <p><?= htmlspecialchars($m['year']) ?></p>

            <a href="remove_favorite.php?id=<?= $m['id'] ?>">Remove ❌</a>
        </div>
    <?php endforeach; ?>
</div>

</body>
</html>
