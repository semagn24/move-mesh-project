<?php
session_start();
?>
<!DOCTYPE html>
<html>
<head>
    <title>MovieStream</title>
    <style>
        body { font-family: Arial; padding:20px; }
        a { margin-right:20px; }
    </style>
</head>
<body>

<h1>Welcome to MovieStream 🎬</h1>

<?php if(isset($_SESSION['user'])): ?>
    <p>Hello, <?= $_SESSION['user']['username'] ?> | 
        <a href="users/profile.php">Profile</a> |
        <a href="auth/logout.php">Logout</a>
    </p>
<?php else: ?>
    <a href="auth/login.php">Login</a>
    <a href="auth/register.php">Register</a>
        <a href="auth/reset_password.php">Reset Password</a>
<?php endif; ?>

<a href="movies/catalog.php">Browse Movies</a>

</body>
</html>
