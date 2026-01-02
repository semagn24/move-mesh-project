<?php
// Include app.php to get BASE_URL and ROOT_PATH
ob_start();
include_once dirname(__DIR__, 2) . '/config/app.php';
ob_end_clean();

$host = "localhost";
$db   = "movie_stream";
$user = "root";
$pass = "";
$charset = "utf8mb4";

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

try {
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    die("DB Connection failed: " . $e->getMessage());
}

