<?php
require_once __DIR__ . '/header_admin.php';
require_once __DIR__ . '/../config/db.php';
$config = require_once __DIR__ . '/../config/seed_config.php';
if (empty($config['enabled'])) { header('Location: seeding.php?error=' . urlencode('Seeding disabled in configuration')); exit; }

$taskDir = __DIR__ . '/../uploads/to_seed';
if (!is_dir($taskDir)) mkdir($taskDir, 0755, true);

$stmt = $pdo->query("SELECT id, video FROM movies WHERE (magnet_link IS NULL OR magnet_link = '') AND video IS NOT NULL");
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

$queued = 0;
foreach ($rows as $r) {
    $id = (int)$r['id'];
    $video = $r['video'];
    $videoPath = realpath(__DIR__ . '/../uploads/videos/' . $video);
    if (!$videoPath || !file_exists($videoPath)) continue;

    $taskFile = $taskDir . '/' . $id . '.json';
    if (file_exists($taskFile)) continue;

    $task = [ 'movie_id' => $id, 'video_path' => $videoPath ];
    file_put_contents($taskFile, json_encode($task));
    $queued++;
}

header('Location: seeding.php?msg=' . urlencode("Queued $queued movies for seeding"));
exit;