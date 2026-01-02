<?php
session_start();
require "../config/db.php";
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);
if (!is_array($data)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid JSON']);
    exit;
}

if (!isset($data['movie_id'], $data['progress'], $data['current_time'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing fields']);
    exit;
}

$user_id = (int) $_SESSION['user_id'];
$movie_id = (int) $data['movie_id'];
$progress = max(0, min(100, (int) $data['progress']));
$current_time = max(0, (float) $data['current_time']);

// Ensure movie exists
$check = $pdo->prepare("SELECT id FROM movies WHERE id = ?");
$check->execute([$movie_id]);
if (!$check->fetch()) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Movie not found']);
    exit;
}

try {
    // Insert or update the history row atomically
    $stmt = $pdo->prepare(
        "INSERT INTO history (user_id, movie_id, watched_at, progress, last_time) VALUES (?, ?, NOW(), ?, ?) 
         ON DUPLICATE KEY UPDATE progress = VALUES(progress), last_time = VALUES(last_time), watched_at = NOW()"
    );
    $stmt->execute([$user_id, $movie_id, $progress, $current_time]);

    echo json_encode(['success' => true, 'progress' => $progress, 'last_time' => $current_time]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error']);
}

