<?php
session_start();
require "../config/db.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    exit("Unauthorized");
}

$user_id = (int)$_POST['user_id'];

$stmt = $pdo->prepare("SELECT role FROM users WHERE id=?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if ($user) {
    $newRole = $user['role'] === 'admin' ? 'user' : 'admin';
    $pdo->prepare("UPDATE users SET role=? WHERE id=?")
        ->execute([$newRole, $user_id]);
}

header("Location: users.php");
