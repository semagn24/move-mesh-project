<?php
session_start();
require_once "../config/db.php";

// 1. If user is already logged in, send them to their dashboard
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'admin') {
        header("Location: ../admin/admin_dashboard.php");
    } else {
        header("Location: ../index.php");
    }
    exit();
}

$error = "";
$success = "";

// Generate CSRF Token for security
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Check for success messages from redirects
if (isset($_GET['reset']) && $_GET['reset'] == 'success') {
    $success = "Password updated! Please sign in.";
}
if (isset($_GET['msg'])) {
    $success = htmlspecialchars($_GET['msg']);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 2. CSRF Validation
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error = "Security token mismatch. Please try again.";
    } else {
        $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
        $password = $_POST['password'];

        if (empty($email) || empty($password)) {
            $error = "Please fill in all fields.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "Invalid email format.";
        } else {
            // 3. Find the user
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                // 4. Regenerate Session ID to prevent session fixation attacks
                session_regenerate_id(true);
                
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role']; 

                // 5. Redirect based on role
                if ($user['role'] === 'admin') {
                    header("Location: ../admin/admin_dashboard.php");
                } else {
                    header("Location: ../index.php");
                }
                exit();
            } else {
                $error = "Invalid email or password.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In | MovieStream</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #e50914;
            --error: #e87c03;
            --success: #2ecc71;
        }

        body {
            background: linear-gradient(rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.7)), 
                        url('https://images.unsplash.com/photo-1489599849927-2ee91cede3ba?auto=format&fit=crop&w=1920&q=80');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            font-family: 'Poppins', sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            color: #fff;
        }

        .login-box {
            background: rgba(0, 0, 0, 0.85);
            padding: 60px 50px;
            width: 100%;
            max-width: 450px;
            border-radius: 8px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.5);
            box-sizing: border-box;
        }

        h2 { margin: 0 0 25px; font-size: 2rem; font-weight: 600; text-align: left; }

        .msg {
            padding: 12px 15px;
            border-radius: 4px;
            font-size: 0.85rem;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
        }
        .error { background: rgba(232, 124, 3, 0.2); border: 1px solid var(--error); color: var(--error); }
        .success { background: rgba(46, 204, 113, 0.2); border: 1px solid var(--success); color: var(--success); }

        .input-group { margin-bottom: 15px; }
        
        input {
            width: 100%;
            padding: 14px 20px;
            background: #333;
            border: none;
            border-radius: 4px;
            color: white;
            font-size: 1rem;
            box-sizing: border-box;
            transition: background 0.3s ease;
        }
        input:focus { background: #454545; outline: none; border-bottom: 2px solid var(--primary); }

        button {
            width: 100%;
            padding: 14px;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            margin-top: 25px;
            transition: background 0.2s ease;
        }
        button:hover { background: #f40612; }

        .footer-links { 
            margin-top: 30px; 
            font-size: 1rem; 
            color: #737373; 
            text-align: left;
        }
        .footer-links a { color: #fff; text-decoration: none; font-weight: 400; }
        .footer-links a:hover { text-decoration: underline; }

        /* Responsive Fix for Mobile Bezel-to-Bezel */
        @media (max-width: 500px) {
            body { background: #000; } /* Flat black for performance on mobile */
            .login-box { 
                padding: 40px 25px; 
                max-width: 100%;
                min-height: 100vh;
                display: flex;
                flex-direction: column;
                justify-content: center;
                border-radius: 0;
            }
        }
    </style>
</head>
<body>

<div class="login-box">
    <h2>Sign In</h2>

    <?php if (!empty($error)): ?>
        <div class="msg error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if (!empty($success)): ?>
        <div class="msg success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <form method="POST" action="">
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

        <div class="input-group">
            <input type="email" name="email" placeholder="Email Address" required autocomplete="email">
        </div>
        <div class="input-group">
            <input type="password" name="password" placeholder="Password" required autocomplete="current-password">
        </div>
        <button type="submit">Sign In</button>
    </form>

    <div class="footer-links">
        <div style="margin-bottom: 10px;">
            <a href="forgot_password.php" style="font-size: 0.9rem; color: #b3b3b3;">Forgot Password?</a>
        </div>
        New to MovieStream? <a href="register.php">Sign up now.</a>
    </div>
</div>

</body>
</html>