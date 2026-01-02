<?php
session_start();
require "../config/db.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    exit("Unauthorized");
}

$user_id = (int)$_POST['user_id'];

// Prevent self-delete
if ($user_id === $_SESSION['user_id']) {
    exit("You cannot delete yourself.");
}

$pdo->prepare("DELETE FROM users WHERE id=?")->execute([$user_id]);

header("Location: users.php");
