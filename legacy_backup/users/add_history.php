<?php
session_start();
require "../config/db.php";

if(!isset($_SESSION['user_id'])) {
    die("Please login to mark history.");
}

if(!isset($_GET['id'])) {
    die("Movie ID missing.");
}

$user_id = $_SESSION['user_id'];
$movie_id = intval($_GET['id']);

// Prevent duplicate history for same day
$stmt = $pdo->prepare("SELECT * FROM history WHERE user_id=? AND movie_id=? AND DATE(watched_at) = CURDATE()");
$stmt->execute([$user_id, $movie_id]);
if($stmt->rowCount() == 0) {
    $stmt = $pdo->prepare("INSERT INTO history (user_id, movie_id) VALUES (?, ?)");
    $stmt->execute([$user_id, $movie_id]);
}

// Redirect back to catalog
header("Location: ../movies/catalog.php");
exit;
