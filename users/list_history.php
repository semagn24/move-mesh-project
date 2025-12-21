<?php
session_start();
require_once "../config/db.php";

// Check login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch watch history
$sql = "SELECT movies.id, movies.title, movies.poster, movies.year, history.watched_at
        FROM history
        JOIN movies ON history.movie_id = movies.id
        WHERE history.user_id = ?
        ORDER BY history.watched_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute([$user_id]);
$history = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Watch History</title>
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

<h2>👁 Watch History</h2>

<div class="grid">
    <?php foreach ($history as $m): ?>
        <div class="movie-card">
            <img src="../uploads/posters/<?= htmlspecialchars($m['poster'] ?: 'default.png') ?>" alt="<?= htmlspecialchars($m['title']) ?>">
            <h3><?= htmlspecialchars($m['title']) ?></h3>
            <p><?= htmlspecialchars($m['year']) ?></p>
            <small>Watched: <?= htmlspecialchars($m['watched_at']) ?></small>
        </div>
    <?php endforeach; ?>
</div>

</body>
</html>
