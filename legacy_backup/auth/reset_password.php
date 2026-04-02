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
</html><?php
require_once "../config/db.php";

$token = $_GET['token'] ?? '';
$message = "";

// 1. Validate the token immediately
$stmt = $pdo->prepare("SELECT * FROM users WHERE reset_token = ? AND reset_expires > NOW()");
$stmt->execute([$token]);
$user = $stmt->fetch();

if (!$user) {
    die("Invalid or expired token. <a href='forgot_password.php'>Request a new one.</a>");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $new_password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // 2. Update password and CLEAR the token so it can't be used again
    $update = $pdo->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_expires = NULL WHERE id = ?");
    if ($update->execute([$new_password, $user['id']])) {
        header("Location: login.php?msg=Password updated successfully!");
        exit();
    }
}
?>

<div class="register-card">
    <h2>New Password</h2>
    <form method="POST">
        <input type="password" name="password" placeholder="Enter new password" required>
        <button type="submit">Update Password</button>
    </form>
</div>
