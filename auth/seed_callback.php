<?php
// Accepts POST from seed daemon with magnet_link and movie_id, updates DB
// This endpoint is authenticated using a shared token in config/seed_config.php

// Allow only POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid payload']);
    exit;
}

$token = $input['token'] ?? '';
$config = require_once __DIR__ . "/../config/seed_config.php";
if (empty($config['enabled'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Seeding is disabled']);
    exit;
}
if (empty($token) || $token !== ($config['token'] ?? '')) {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$movie_id = isset($input['movie_id']) ? (int)$input['movie_id'] : 0;
$magnet = trim($input['magnet_link'] ?? '');

if ($movie_id <= 0 || $magnet === '') {
    http_response_code(400);
    echo json_encode(['error' => 'Missing parameters']);
    exit;
}

require_once __DIR__ . '/../config/db.php';

$stmt = $pdo->prepare("UPDATE movies SET magnet_link = ? WHERE id = ?");
$updated = $stmt->execute([$magnet, $movie_id]);

if ($updated) {
    echo json_encode(['ok' => true, 'movie_id' => $movie_id, 'magnet' => $magnet]);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'DB update failed']);
}
