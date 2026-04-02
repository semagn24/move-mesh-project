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

$stmt = $pdo->prepare('UPDATE movies SET magnet_link = NULL WHERE id = ?');
$stmt->execute([$movie_id]);

// also remove any queued task
$taskFile = __DIR__ . '/../uploads/to_seed/' . $movie_id . '.json';
if (file_exists($taskFile)) unlink($taskFile);

header('Location: seeding.php?msg=' . urlencode('Magnet cleared')); exit;