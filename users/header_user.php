<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

// Optional: Check if role is 'user'
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'user') {
    // If admin logs in here, you can redirect to admin dashboard
    if ($_SESSION['role'] === 'admin') {
        header("Location: ../admin/admin_dashboard.php");
        exit;
    }
    // Otherwise, force logout
    header("Location: ../auth/login.php");
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>User Panel</title>
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f1f3f6;
        }
        .user-nav {
            background: #2b6cff; /* Blue */
            padding: 15px 25px;
            display: flex;
            align-items: center;
        }
        .user-nav a {
            color: white;
            margin-right: 25px;
            font-weight: bold;
            text-decoration: none;
        }
        .user-nav a:hover {
            text-decoration: underline;
        }
        .user-nav .right {
            margin-left: auto;
            color: white;
        }
    </style>
</head>
<body>

<!-- User Navigation -->
<div class="user-nav">
    <a href="dashboard.php">Dashboard</a>
    <a href="list_favorites.php">❤️ Favorites</a>
    <a href="list_history.php">👁 History</a>
    <a href="profile.php">Profile</a>
    <div class="right">
        👤 <?= isset($_SESSION['username']) ? $_SESSION['username'] : 'User'; ?> &nbsp;&nbsp;
        <a href="../auth/logout.php">Logout</a>
    </div>
</div>
