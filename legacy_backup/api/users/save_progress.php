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
$progress = $data->progress ?? 0;

if (!$movie_id) {
    http_response_code(400);
    echo json_encode(["error" => "Movie ID required"]);
    exit();
}

$user_id = $_SESSION['user_id'];

try {
    // Check if entry exists for this user/movie combo
    $check = $pdo->prepare("SELECT id FROM history WHERE user_id = ? AND movie_id = ?");
    $check->execute([$user_id, $movie_id]);
    
    if ($check->rowCount() > 0) {
        $pdo->prepare("UPDATE history SET progress = ?, watched_at = NOW() WHERE user_id = ? AND movie_id = ?")->execute([$progress, $user_id, $movie_id]);
    } else {
        $pdo->prepare("INSERT INTO history (user_id, movie_id, progress, watched_at) VALUES (?, ?, ?, NOW())")->execute([$user_id, $movie_id, $progress]);
    }
    
    echo json_encode(["success" => true]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => $e->getMessage()]);
}
?>
