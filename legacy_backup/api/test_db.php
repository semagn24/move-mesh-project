<?php
require_once 'config/database.php';
try {
    echo "Database: Connected Successfully.";
} catch (Exception $e) {
    echo "Database: Error - " . $e->getMessage();
}
?>
