<?php
require_once "config/app.php";
echo "<h1>Connection Debug</h1>";
echo "1. Your Laptop IP & Port: " . $_SERVER['HTTP_HOST'] . "<br>";
echo "2. The CSS Link your phone is trying to use:<br>";
echo "<code>" . BASE_URL . "css/style.css</code><br><br>";
echo "<a href='" . BASE_URL . "css/style.css'>Click here to see if CSS file opens</a>";
?>