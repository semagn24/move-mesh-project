<?php
require "../config/db.php";

$message = "";

if($_SERVER["REQUEST_METHOD"] == "POST"){
    $email = $_POST["email"];
    $newPass = password_hash("123456", PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("UPDATE users SET password=? WHERE email=?");
    if ($stmt->execute([$newPass, $email])){
        $message = "Password reset to default: 123456";
    } else {
        $message = "Error resetting password!";
    }
}
?>
<!DOCTYPE html>
<html>
<body>

<h2>Reset Password</h2>

<form method="POST">
    Enter Email:<br>
    <input type="email" name="email" required><br><br>
    <button>Reset</button>
</form>

<p><?= $message ?></p>

</body>
</html>
