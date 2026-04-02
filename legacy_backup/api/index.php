<?php
require_once 'config/cors.php';
require_once 'config/database.php';

echo json_encode([
    "message" => "Welcome to MovieBox API",
    "status" => "running",
    "timestamp" => time()
]);
?>
