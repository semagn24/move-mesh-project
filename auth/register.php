<?php
require_once "../config/db.php";

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST"){

    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("INSERT INTO users (username,email,password) VALUES (?,?,?)");
    
    if ($stmt->execute([$username, $email, $password])) {
        $message = "Registration successful! <a href='login.php'>Login</a>";
    } else {
        $message = "Error during registration!";
    }
}
?>

<!DOCTYPE html>
<html>
<body>
<h2>Register</h2>

<form method="POST">
    Username: <br><input type="text" name="username" required><br><br>
    Email: <br><input type="email" name="email" required><br><br>
    Password: <br><input type="password" name="password" required><br><br>
    <button type="submit">Register</button>
</form>

<p><?= $message ?></p>

</body>
</html>
