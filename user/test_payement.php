<?php
session_start();
require '../config/db.php';

if (!isset($_SESSION['user_id'])) die("Login first!");

$user_id = $_SESSION['user_id'];
$amount = 150.00;
$tx_ref = "TEST_" . uniqid();

// 1. Record the payment in the database
$stmt = $pdo->prepare("INSERT INTO payments (user_id, amount, tx_ref, status) VALUES (?, ?, ?, 'success')");
$stmt->execute([$user_id, $amount, $tx_ref]);

// 2. Upgrade the user to premium
$expiry = date('Y-m-d H:i:s', strtotime('+30 days'));
$stmt = $pdo->prepare("UPDATE users SET subscription_status = 'premium', subscription_expiry = ? WHERE id = ?");
$stmt->execute([$expiry, $user_id]);

echo "Simulation Successful! Check your Admin Dashboard now.";