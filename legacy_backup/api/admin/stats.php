<?php
require_once '../config/cors.php';
require_once '../config/database.php';

session_start();

// Admin protection
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(["error" => "Unauthorized"]);
    exit();
}

try {
    $totalMovies = $pdo->query("SELECT COUNT(*) FROM movies")->fetchColumn();
    $totalUsers  = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    $admins      = $pdo->query("SELECT COUNT(*) FROM users WHERE role='admin'")->fetchColumn();
    
    // Check if views/payments tables/columns exist to avoid errors if schema differs
    $watchCount = 0;
    try {
        $watchCount = $pdo->query("SELECT SUM(views) FROM movies")->fetchColumn() ?: 0;
    } catch (Exception $e) { /* Ignore if column missing */ }

    $totalRevenue = 0;
    $premiumMembers = 0;
    try {
        $totalRevenue = $pdo->query("SELECT SUM(amount) FROM payments WHERE status='success'")->fetchColumn() ?: 0;
        $premiumMembers = $pdo->query("SELECT COUNT(*) FROM users WHERE subscription_status='premium'")->fetchColumn();
    } catch (Exception $e) { /* Ignore if table missing */ }

    echo json_encode([
        "success" => true,
        "stats" => [
            "total_movies" => $totalMovies,
            "total_users" => $totalUsers,
            "admins" => $admins,
            "watch_count" => $watchCount,
            "revenue" => $totalRevenue,
            "premium_users" => $premiumMembers
        ]
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => "Server error: " . $e->getMessage()]);
}
?>
