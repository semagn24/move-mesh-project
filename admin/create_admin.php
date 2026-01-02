<?php
require "../config/db.php"; // adjust path if needed

$username = "admin";
$email = "admin@example.com";
$password = password_hash("admin123", PASSWORD_DEFAULT); // hashed password
$role = "admin";

// Check if admin already exists
$stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
$stmt->execute([$username]);
if ($stmt->rowCount() == 0) {
    $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role, created_at) VALUES (?, ?, ?, ?, NOW())");
    $stmt->execute([$username, $email, $password, $role]);
    echo "Admin account created successfully!";
} else {
    echo "Admin account already exists!";
}
