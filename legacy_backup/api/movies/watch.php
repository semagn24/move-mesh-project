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
$movie_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$movie_id) {
    http_response_code(400);
    echo json_encode(["error" => "Movie ID required"]);
    exit();
}

try {
    // 1. Fetch Movie Details
    $stmt = $pdo->prepare("SELECT * FROM movies WHERE id = ?");
    $stmt->execute([$movie_id]);
    $movie = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$movie) {
        http_response_code(404);
        echo json_encode(["error" => "Movie not found"]);
        exit();
    }

    // 2. Increment View Count (Simple increment)
    $pdo->prepare("UPDATE movies SET views = views + 1 WHERE id = ?")->execute([$movie_id]);

    // 3. Check Favorite Status
    $favStmt = $pdo->prepare("SELECT 1 FROM favorites WHERE user_id = ? AND movie_id = ?");
    $favStmt->execute([$user_id, $movie_id]);
    $is_favorited = (bool)$favStmt->fetchColumn();

    // 4. Fetch Last Watch Progress
    $progStmt = $pdo->prepare("SELECT progress FROM history WHERE user_id = ? AND movie_id = ? ORDER BY watched_at DESC LIMIT 1");
    $progStmt->execute([$user_id, $movie_id]);
    $progress = $progStmt->fetchColumn() ?: 0;

    // 5. Construct Video URL (with base fallback)
    $videoFile = str_replace(' ', '%20', $movie['video']);
    $posterFile = str_replace(' ', '%20', $movie['poster']);
    
    $videoUrl = defined('BASE_URL') ? BASE_URL . "uploads/videos/" . $videoFile : "/uploads/videos/" . $videoFile;
    $posterUrl = defined('BASE_URL') ? BASE_URL . "uploads/posters/" . $posterFile : "/uploads/posters/" . $posterFile;

    echo json_encode([
        "success" => true,
        "movie" => [
            "id" => $movie['id'],
            "title" => $movie['title'],
            "genre" => $movie['genre'],
            "description" => $movie['description'] ?? "",
            "video_url" => $videoUrl,
            "poster_url" => $posterUrl,
            "rating" => $movie['rating']
        ],
        "is_favorited" => $is_favorited,
        "progress" => (float)$progress
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => $e->getMessage()]);
}
?>
