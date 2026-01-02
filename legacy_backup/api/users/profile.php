<?php
require_once '../config/cors.php';
require_once '../config/database.php';

session_start();

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(["error" => "Unauthorized"]);
    exit();
}

$user_id = $_SESSION['user_id'];

try {
    // 1. Fetch User Info
    $stmt = $pdo->prepare("SELECT id, username, email, role, profile_pic, subscription_status, subscription_expiry FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        http_response_code(404);
        echo json_encode(["error" => "User not found"]);
        exit();
    }

    // 2. Fetch Favorites
    $favStmt = $pdo->prepare("
        SELECT m.id, m.title, m.poster 
        FROM movies m
        JOIN favorites f ON m.id = f.movie_id
        WHERE f.user_id = ?
    ");
    $favStmt->execute([$user_id]);
    $favorites = $favStmt->fetchAll(PDO::FETCH_ASSOC);

    // 3. Fetch History
    $histStmt = $pdo->prepare("
        SELECT m.id, m.title, m.poster, h.progress, h.watched_at
        FROM movies m
        JOIN history h ON m.id = h.movie_id
        WHERE h.user_id = ?
        ORDER BY h.watched_at DESC
        LIMIT 20
    ");
    $histStmt->execute([$user_id]);
    $history = $histStmt->fetchAll(PDO::FETCH_ASSOC);

    // Helper to format URLs
    $formatMovie = function($m) {
        $m['poster_url'] = defined('BASE_URL') ? BASE_URL . "uploads/posters/" . ($m['poster'] ?: 'default.png') : "/uploads/posters/" . ($m['poster'] ?: 'default.png');
        return $m;
    };

    echo json_encode([
        "success" => true,
        "user" => [
            "username" => $user['username'],
            "email" => $user['email'],
            "profile_pic" => $user['profile_pic'],
            "is_premium" => ($user['subscription_status'] === 'premium' && strtotime($user['subscription_expiry']) > time())
        ],
        "stats" => [
            "favorites_count" => count($favorites),
            "watched_count" => count($history)
        ],
        "favorites" => array_map($formatMovie, $favorites),
        "history" => array_map($formatMovie, $history)
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => $e->getMessage()]);
}
?>
