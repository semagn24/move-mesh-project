<?php
require_once '../config/cors.php';
require_once '../config/database.php';

session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(["error" => "Unauthorized"]);
    exit();
}

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title     = trim($_POST['title'] ?? '');
    $genre     = trim($_POST['genre'] ?? '');
    $year      = trim($_POST['year'] ?? '');
    $language  = trim($_POST['language'] ?? '');
    $director  = trim($_POST['director'] ?? '');
    $actor     = trim($_POST['actor'] ?? ''); // Maps to 'actor' column not 'actors' based on schema
    $magnet    = trim($_POST['magnet_link'] ?? '');
    $is_premium = isset($_POST['is_premium']) && $_POST['is_premium'] === 'true' ? 1 : 0;

    if (!$title || !$genre || !$year) {
        http_response_code(400);
        echo json_encode(["error" => "Title, Genre, and Year are required."]);
        exit();
    } 

    $poster = null;
    $video = null;
    
    // Define upload paths relative to the API file
    // api/admin/add_movie.php -> ../../uploads/
    $uploadDir = __DIR__ . '/../../uploads/';
    
    if (!file_exists($uploadDir . 'posters')) mkdir($uploadDir . 'posters', 0777, true);
    if (!file_exists($uploadDir . 'videos')) mkdir($uploadDir . 'videos', 0777, true);

    if (!empty($_FILES['poster']['name'])) {
        $posterName = uniqid('poster_') . '.jpg';
        if (move_uploaded_file($_FILES['poster']['tmp_name'], $uploadDir . "posters/$posterName")) {
            $poster = $posterName;
        } else {
             // Handle upload error if needed
        }
    }

    if (!empty($_FILES['video']['name'])) {
        $videoName = uniqid('video_') . '.mp4';
        if (move_uploaded_file($_FILES['video']['tmp_name'], $uploadDir . "videos/$videoName")) {
            $video = $videoName;
        } else {
            http_response_code(500);
            echo json_encode(["error" => "Failed to upload video file."]);
            exit();
        }
    } else {
        http_response_code(400);
        echo json_encode(["error" => "Video file is required."]);
        exit();
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO movies (title, genre, year, language, director, actor, poster, video, is_premium, magnet_link, views, rating, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0, 0, NOW())");
        $stmt->execute([$title, $genre, $year, $language, $director, $actor, $poster, $video, $is_premium, $magnet]);
        
        echo json_encode(["success" => true, "message" => "Movie uploaded successfully!"]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(["error" => "Database Error: " . $e->getMessage()]);
    }
}
?>
