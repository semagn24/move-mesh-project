<?php
session_start();
require "../config/db.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    exit("Unauthorized");
}

$movie_id = (int)$_POST['movie_id'];

/* OPTIONAL: delete related data */
$pdo->prepare("DELETE FROM favorites WHERE movie_id=?")->execute([$movie_id]);
$pdo->prepare("DELETE FROM history WHERE movie_id=?")->execute([$movie_id]);

/* Delete movie */
$pdo->prepare("DELETE FROM movies WHERE id=?")->execute([$movie_id]);

header("Location: movies.php");
