<?php
// CLI script to queue movies without magnet links for seeding
// Usage: php tools/seed_db_queue.php
// It will write tasks to uploads/to_seed/<movie_id>.json for movies with video file present and no magnet_link

require_once __DIR__ . '/../config/db.php';
$config = require_once __DIR__ . '/../config/seed_config.php';
if (empty($config['enabled'])) {
    echo "Seeding disabled in configuration. Exiting.\n";
    exit;
}

$uploadDir = realpath(__DIR__ . '/../uploads/videos');
$taskDir = __DIR__ . '/../uploads/to_seed';
if (!is_dir($taskDir)) mkdir($taskDir, 0755, true);

$stmt = $pdo->query("SELECT id, video FROM movies WHERE (magnet_link IS NULL OR magnet_link = '') AND video IS NOT NULL");
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

$queued = 0;
foreach ($rows as $r) {
    $id = (int)$r['id'];
    $video = $r['video'];

    $videoPath = realpath(__DIR__ . '/../uploads/videos/' . $video);
    if (!$videoPath || !file_exists($videoPath)) continue; // skip missing files

    $taskFile = $taskDir . '/' . $id . '.json';
    if (file_exists($taskFile)) {
        // already queued
        continue;
    }

    $task = [ 'movie_id' => $id, 'video_path' => $videoPath ];
    file_put_contents($taskFile, json_encode($task));
    $queued++;
}

echo "Queued $queued movies for seeding.\n";
