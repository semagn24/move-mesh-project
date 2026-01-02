<?php
require_once "../config/db.php";

$message = "";
$status = ""; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // 1. Basic Validation
    if ($password !== $confirm_password) {
        $message = "Passwords do not match!";
        $status = "error";
    } 
    // 2. Strong Password Validation (Regex)
    // At least 8 chars, 1 Uppercase, 1 Lowercase, 1 Number, 1 Special Char
    elseif (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/', $password)) {
        $message = "Password must be at least 8 characters long and include uppercase, lowercase, a number, and a special character.";
        $status = "error";
    } 
    else {
        // 3. Check if email already exists
        $check = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $check->execute([$email]);
        
        if ($check->rowCount() > 0) {
            $message = "This email is already registered. Try logging in!";
            $status = "error";
        } else {
            // 4. Hash and Insert
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, 'user')");
            
            if ($stmt->execute([$username, $email, $hashed_password])) {
                $message = "Account created successfully! <a href='login.php' style='color:#fff; font-weight:bold;'>Login here</a>";
                $status = "success";
            } else {
                $message = "Registration failed. Please try again.";
                $status = "error";
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
    <title>Create Account | MovieStream</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-red: #e50914;
            --hover-red: #ff0a16;
            --glass-bg: rgba(0, 0, 0, 0.85);
            --input-bg: #333;
        }

        body {
            margin: 0;
            padding: 0;
            font-family: 'Poppins', sans-serif;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            background: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.9)), 
                        url('https://images.unsplash.com/photo-1517604931442-7e0c8ed2963c?auto=format&fit=crop&w=1920&q=80');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            color: #fff;
        }

        header { padding: 20px 50px; }
        header .logo { color: var(--primary-red); font-size: 2rem; font-weight: 700; text-transform: uppercase; text-decoration: none; }

        .wrapper { flex: 1; display: flex; justify-content: center; align-items: center; padding: 40px 20px; }

        .register-card {
            background-color: var(--glass-bg);
            padding: 50px;
            width: 100%;
            max-width: 450px;
            border-radius: 8px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.6);
        }

        h2 { font-size: 2rem; margin-bottom: 25px; margin-top: 0; }

        .msg {
            padding: 12px;
            border-radius: 4px;
            font-size: 0.85rem;
            margin-bottom: 20px;
            line-height: 1.4;
        }
        .msg.error { background: rgba(232, 124, 3, 0.2); border: 1px solid #e87c03; color: #ffa033; }
        .msg.success { background: rgba(92, 184, 92, 0.2); border: 1px solid #5cb85c; color: #a9ffad; }

        .input-container { margin-bottom: 15px; }

        input {
            width: 100%;
            height: 55px;
            padding: 0 20px;
            background: var(--input-bg);
            border: none;
            border-radius: 4px;
            color: white;
            font-size: 1rem;
            box-sizing: border-box;
            transition: 0.3s;
        }

        input:focus { background: #454545; outline: none; border-bottom: 3px solid var(--primary-red); }

        .pass-hint { font-size: 0.75rem; color: #8c8c8c; margin-top: 5px; margin-bottom: 15px; display: block; }

        button {
            width: 100%;
            height: 55px;
            background: var(--primary-red);
            color: #fff;
            border: none;
            border-radius: 4px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: 0.2s;
        }

        button:hover { background: var(--hover-red); }

        .card-footer { margin-top: 30px; color: #737373; font-size: 1rem; }
        .card-footer a { color: #fff; text-decoration: none; }
        .card-footer a:hover { text-decoration: underline; }

        @media (max-width: 500px) {
            header { padding: 20px; text-align: center; }
            .register-card { padding: 40px 20px; background: #000; min-height: 100vh; border-radius: 0; }
            .wrapper { padding: 0; }
        }
    </style>
</head>
<body>

<header>
    <a href="../index.php" class="logo">MOVIESTREAM</a>
</header>

<div class="wrapper">
    <div class="register-card">
        <h2>Sign Up</h2>

        <?php if ($message): ?>
            <div class="msg <?= $status ?>"><?= $message ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="input-container">
                <input type="text" name="username" placeholder="Full Name" required value="<?= isset($_POST['username']) ? htmlspecialchars($_POST['username']) : '' ?>">
            </div>
            
            <div class="input-container">
                <input type="email" name="email" placeholder="Email Address" required value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>">
            </div>

            <div class="input-container">
                <input type="password" name="password" placeholder="Password" required>
                <span class="pass-hint">Use 8+ characters with mixed case, numbers & symbols.</span>
            </div>

            <div class="input-container">
                <input type="password" name="confirm_password" placeholder="Confirm Password" required>
            </div>

            <button type="submit">Get Started</button>
        </form>

        <div class="card-footer">
            Already have an account? <a href="login.php">Sign in now.</a>
        </div>
    </div>
</div>

</body>
</html>