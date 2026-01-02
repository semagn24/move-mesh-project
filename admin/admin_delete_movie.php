<?php
session_start();

require "../middleware/admin_only.php";
require "../config/db.php";

// Validate ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: movies.php?error=invalid_id");
    exit;
}

$id = (int) $_GET['id'];

// Delete movie
$stmt = $pdo->prepare("DELETE FROM movies WHERE id = ?");
$stmt->execute([$id]);

// Redirect back
header("Location: movies.php?msg=deleted");
exit;
