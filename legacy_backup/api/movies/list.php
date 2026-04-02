<?php
require_once '../config/cors.php';
require_once '../config/database.php';

// Include legacy config for BASE_URL constant
// We need to suppress output just in case, though app.php should be clean
ob_start();
require_once '../../config/app.php';
ob_end_clean(); 

error_reporting(0);
ini_set('display_errors', 0);

$view_mode = isset($_GET['view']) ? $_GET['view'] : 'all';
$search = isset($_GET['q']) ? trim($_GET['q']) : '';

try {
    $movies = [];
    
    if ($view_mode === 'trending') {
        $stmt = $pdo->prepare("
            SELECT m.*, COUNT(h.movie_id) as view_count 
            FROM movies m 
            INNER JOIN history h ON m.id = h.movie_id 
            GROUP BY m.id 
            HAVING view_count > 0 
            ORDER BY view_count DESC 
            LIMIT 20
        ");
        $stmt->execute();
        $movies = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
        $query = "SELECT * FROM movies WHERE 1=1";
        $params = [];
        
        if ($search !== '') {
            $query .= " AND (LOWER(title) LIKE ? OR LOWER(actor) LIKE ?)";
            $search_param = "%" . strtolower($search) . "%";
            $params[] = $search_param;
            $params[] = $search_param;
        }
        
        $query .= " ORDER BY created_at DESC";
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        $movies = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Format Data (add full image URLs)
    $formattedMovies = array_map(function($m) {
        $posterUrl = defined('BASE_URL') ? BASE_URL . "uploads/posters/" . ($m['poster'] ?? 'default.png') : "/uploads/posters/" . ($m['poster'] ?? 'default.png');
        
        return [
            "id" => $m['id'],
            "title" => $m['title'],
            "description" => $m['description'] ?? '',
            "poster_url" => $posterUrl,
            "video_url" => defined('BASE_URL') ? BASE_URL . "uploads/videos/" . ($m['video'] ?? '') : "",
            "view_count" => isset($m['view_count']) ? $m['view_count'] : 0,
            "created_at" => $m['created_at'] ?? date('Y-m-d H:i:s')
        ];
    }, $movies);

    echo json_encode([
        "success" => true, 
        "data" => $formattedMovies
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => "Error fetching movies: " . $e->getMessage()]);
}
?>
