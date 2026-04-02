<?php
// Define the Root Path for server-side includes
define('ROOT_PATH', dirname(__DIR__) . DIRECTORY_SEPARATOR);

// Dynamically detect the protocol and host
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
$host = $_SERVER['HTTP_HOST'];

// Calculate BASE_URL relative to DOCUMENT_ROOT
$docRoot = str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT']));
$projectRoot = str_replace('\\', '/', realpath(ROOT_PATH));

$baseDir = str_replace($docRoot, '', $projectRoot);
if ($baseDir !== '' && !str_starts_with($baseDir, '/')) {
    $baseDir = '/' . $baseDir;
}
if (!str_ends_with($baseDir, '/')) {
    $baseDir .= '/';
}
$baseDir = str_replace(' ', '%20', $baseDir);
define('BASE_URL', $protocol . $host . $baseDir);

// Fault Tolerance: List of available stream servers
// For testing on phone/other PC, we use the current host IP dynamically.
define('STREAM_SERVERS', [
    BASE_URL,
    $protocol . $host . $baseDir . "?failover=1", // Simulating a second logic path
    "http://127.0.0.1" . $baseDir                // Local backup
]);
?>