<?php
session_start();
require_once "../config/db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

if (!isset($_GET['id'])) {
    die("Movie ID missing.");
}

$user_id = $_SESSION['user_id'];
$movie_id = intval($_GET['id']);

// Delete favorite
$sql = "DELETE FROM favorites WHERE user_id = ? AND movie_id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$user_id, $movie_id]);

header("Location: list_favorites.php");
exit;
