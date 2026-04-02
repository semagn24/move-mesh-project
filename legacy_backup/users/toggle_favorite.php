<?php
session_start();
require_once __DIR__ . "/../config/app.php";
require_once ROOT_PATH . "config/db.php";

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(["status" => "error", "message" => "Unauthorized"]);
    exit();
}

$user_id = (int)$_SESSION['user_id'];
$movie_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$movie_id) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Movie ID required"]);
    exit();
}

try {
    $check = $pdo->prepare("SELECT 1 FROM favorites WHERE user_id = ? AND movie_id = ?");
    $check->execute([$user_id, $movie_id]);
    
    if ($check->rowCount() > 0) {
        $pdo->prepare("DELETE FROM favorites WHERE user_id = ? AND movie_id = ?")->execute([$user_id, $movie_id]);
        echo json_encode(["status" => "removed"]);
    } else {
        $pdo->prepare("INSERT INTO favorites (user_id, movie_id) VALUES (?, ?)")->execute([$user_id, $movie_id]);
        echo json_encode(["status" => "added"]);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
