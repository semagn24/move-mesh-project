<?php
try {
    $pdo = new PDO("mysql:host=localhost", "root", "");
    echo "SUCCESS: Connected to local MySQL";
    $stmt = $pdo->query("SHOW DATABASES");
    $dbs = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "\nDatabases: " . implode(", ", $dbs);
} catch (PDOException $e) {
    echo "ERROR: " . $e->getMessage();
}
