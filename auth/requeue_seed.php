<?php
require_once __DIR__ . '/header_admin.php';
require_once __DIR__ . '/../config/db.php';
$config = require_once __DIR__ . '/../config/seed_config.php';
if (empty($config['enabled'])) { header('Location: seeding.php?error=' . urlencode('Seeding disabled in configuration')); exit; }

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: seeding.php'); exit;
}

$movie_id = isset($_POST['movie_id']) ? (int)$_POST['movie_id'] : 0;
if ($movie_id <= 0) {
    header('Location: seeding.php?error=' . urlencode('Invalid movie id')); exit;
}

// Fetch movie
$stmt = $pdo->prepare('SELECT id, video FROM movies WHERE id = ?');
$stmt->execute([$movie_id]);
$movie = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$movie) {
    header('Location: seeding.php?error=' . urlencode('Movie not found')); exit;
}

$videoPath = realpath(__DIR__ . '/../uploads/videos/' . $movie['video']);
if (!$videoPath || !file_exists($videoPath)) {
    header('Location: seeding.php?error=' . urlencode('Video file not found on disk')); exit;
}

$taskDir = __DIR__ . '/../uploads/to_seed';
if (!is_dir($taskDir)) mkdir($taskDir, 0755, true);
$taskFile = $taskDir . '/' . $movie_id . '.json';
$task = [ 'movie_id' => $movie_id, 'video_path' => $videoPath ];
file_put_contents($taskFile, json_encode($task));

header('Location: seeding.php?msg=' . urlencode('Queued for seeding'));
exit;