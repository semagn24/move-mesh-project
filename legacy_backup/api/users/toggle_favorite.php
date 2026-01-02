<?php
require_once '../config/cors.php';
require_once '../config/database.php';

session_start();

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(["error" => "Unauthorized"]);
    exit();
}

$data = json_decode(file_get_contents("php://input"));
$movie_id = $data->movie_id ?? 0;

if (!$movie_id) {
    http_response_code(400);
    echo json_encode(["error" => "Movie ID required"]);
    exit();
}

$user_id = $_SESSION['user_id'];

try {
    $check = $pdo->prepare("SELECT id FROM favorites WHERE user_id = ? AND movie_id = ?");
    $check->execute([$user_id, $movie_id]);
    
    if ($check->rowCount() > 0) {
        $pdo->prepare("DELETE FROM favorites WHERE user_id = ? AND movie_id = ?")->execute([$user_id, $movie_id]);
        echo json_encode(["success" => true, "status" => "removed"]);
    } else {
        $pdo->prepare("INSERT INTO favorites (user_id, movie_id) VALUES (?, ?)")->execute([$user_id, $movie_id]);
        echo json_encode(["success" => true, "status" => "added"]);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => $e->getMessage()]);
}
?>
