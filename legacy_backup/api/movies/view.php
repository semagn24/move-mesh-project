<?php
require_once '../config/cors.php';
require_once '../config/database.php';

session_start();

$data = json_decode(file_get_contents("php://input"));
$movie_id = $data->movie_id ?? null;

if (!$movie_id) {
    echo json_encode(["error" => "ID required"]);
    exit();
}

try {
    // Increment views in movies table
    $stmt = $pdo->prepare("UPDATE movies SET views = views + 1 WHERE id = ?");
    $stmt->execute([$movie_id]);

    // Record in history if user is logged in
    if (isset($_SESSION['user_id'])) {
        $stmt = $pdo->prepare("INSERT INTO history (user_id, movie_id, watched_at) VALUES (?, ?, NOW())");
        $stmt->execute([$_SESSION['user_id'], $movie_id]);
    }

    echo json_encode(["success" => true]);
} catch (Exception $e) {
    echo json_encode(["error" => $e->getMessage()]);
}
?>
