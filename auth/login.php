<?php
// Start session if not started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include database connection
require_once __DIR__ . '/../config/db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if ($email && $password) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            // Set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];

            // Redirect by role
            if ($user['role'] === 'admin') {
                header("Location: ../admin/admin_dashboard.php");
            } else {
                header("Location: ../index.php");
            }
            exit;
        } else {
            $error = "Invalid email or password!";
        }
    } else {
        $error = "Please enter email and password!";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <style>
        body {
            background:#f2f2f2;
            font-family: Arial, sans-serif;
            display:flex;
            justify-content:center;
            align-items:center;
            height:100vh;
            margin:0;
        }
        .login-box {
            background:white;
            padding:30px;
            width:350px;
            border-radius:10px;
            box-shadow:0 0 10px #ccc;
        }
        input {
            width:100%;
            padding:10px;
            margin:10px 0;
            box-sizing: border-box;
        }
        button {
            width:100%;
            padding:10px;
            background:#2b6cff;
            color:white;
            border:none;
            cursor:pointer;
        }
        .error {
            color:red;
            text-align:center;
            margin-bottom:10px;
        }
    </style>
</head>
<body>

<div class="login-box">
    <h2>Login</h2>

    <?php if (!empty($error)): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="">
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit">Login</button>
    </form>
</div>

</body>
</html>
