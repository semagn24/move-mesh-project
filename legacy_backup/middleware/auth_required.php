<?php
// Start session safely
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redirect to login if user is not authenticated
if (!isset($_SESSION['user_id']) && !isset($_SESSION['user'])) {
    header("Location: ../auth/login.php");
    exit;
}
