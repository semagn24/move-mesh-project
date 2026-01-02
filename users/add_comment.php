<?php
session_start();
require_once __DIR__ . '/../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['user_id'])) {
    header('Location: /movie_stream/index.php');
    exit;
}

$user_id = (int) $_SESSION['user_id'];
$movie_id = filter_input(INPUT_POST, 'movie_id', FILTER_VALIDATE_INT);
$comment = trim($_POST['comment'] ?? '');
$rating = filter_input(INPUT_POST, 'rating', FILTER_VALIDATE_INT);

// Basic validation
if (!$movie_id || !$comment || !$rating || $rating < 1 || $rating > 5) {
    header("Location: /movie_stream/movies/movie.php?id={$movie_id}&error=invalid_input");
    exit;
}

// Ensure the movie exists
$check = $pdo->prepare("SELECT id FROM movies WHERE id = ?");
$check->execute([$movie_id]);
if (!$check->fetch()) {
    header('Location: /movie_stream/index.php');
    exit;
}

// Limit comment length to avoid very large payloads
$comment = mb_substr($comment, 0, 2000);

try {
    $stmt = $pdo->prepare("INSERT INTO comments (user_id, movie_id, comment, rating, created_at) VALUES (?, ?, ?, ?, NOW())");
    $stmt->execute([$user_id, $movie_id, $comment, $rating]);

    // Recalculate average rating and update movies.rating (rounded to 1 decimal)
    $avgStmt = $pdo->prepare("SELECT AVG(rating) AS avg_rating FROM comments WHERE movie_id = ?");
    $avgStmt->execute([$movie_id]);
    $avgRow = $avgStmt->fetch();
    $avgVal = $avgRow && $avgRow['avg_rating'] ? round((float)$avgRow['avg_rating'], 1) : 0;

    $pdo->prepare("UPDATE movies SET rating = ? WHERE id = ?")->execute([$avgVal, $movie_id]);

    header("Location: /movie_stream/movies/movie.php?id={$movie_id}&success=comment_added");
    exit;
} catch (Exception $e) {
    header("Location: /movie_stream/movies/movie.php?id={$movie_id}&error=server");
    exit;
}