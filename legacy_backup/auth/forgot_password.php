<?php
// 1. Error Reporting (Disable in production)
ini_set('display_errors', 1);
error_reporting(E_ALL);

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendor/PHPMailer/Exception.php';
require '../vendor/PHPMailer/PHPMailer.php';
require '../vendor/PHPMailer/SMTP.php';
require_once "../config/app.php";
require_once ROOT_PATH . "config/db.php";

$message = "";
$status = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    
    $stmt = $pdo->prepare("SELECT username FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user) {
        $token = bin2hex(random_bytes(32));
        $expires = date("Y-m-d H:i:s", strtotime("+1 hour"));

        $update = $pdo->prepare("UPDATE users SET reset_token = ?, reset_expires = ? WHERE email = ?");
        $update->execute([$token, $expires, $email]);

        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'setemelese91@gmail.com'; 
            $mail->Password   = 'rjfq ttsj pwmp vtve';   
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            $mail->setFrom('setemelese91@gmail.com', 'MovieStream Support');
            $mail->addAddress($email);

            // Professional HTML Email Template
            $resetLink = BASE_URL . "users/reset_password.php?token=" . $token;
            $mail->isHTML(true);
            $mail->Subject = 'Reset Your MovieStream Password';
            $mail->Body    = "
                <div style='background: #141414; color: #ffffff; padding: 40px; font-family: Arial, sans-serif; border-radius: 10px;'>
                    <h2 style='color: #e50914;'>MovieStream</h2>
                    <p>Hi " . htmlspecialchars($user['username']) . ",</p>
                    <p>We received a request to reset your password. Click the button below to secure your account:</p>
                    <div style='text-align: center; margin: 30px 0;'>
                        <a href='$resetLink' style='background: #e50914; color: #fff; padding: 12px 25px; text-decoration: none; border-radius: 5px; font-weight: bold;'>Reset Password</a>
                    </div>
                    <p style='color: #aaa; font-size: 12px;'>This link will expire in 1 hour. If you did not request this, please ignore this email.</p>
                </div>";

            $mail->send();
            $status = "success";
            $message = "A reset link has been sent to your email!";
        } catch (Exception $e) {
            $status = "error";
            $message = "Mail error: {$mail->ErrorInfo}";
        }
    } else {
        $status = "error";
        $message = "No account found with that email.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password | MovieStream</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        :root { --primary: #e50914; --bg-overlay: rgba(0, 0, 0, 0.75); }

        body {
            margin: 0;
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(var(--bg-overlay), var(--bg-overlay)), 
                        url('https://images.unsplash.com/photo-1489599849927-2ee91cede3ba?auto=format&fit=crop&w=1920&q=80');
            background-size: cover;
            background-position: center;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            color: white;
        }

        .card {
            background: rgba(0, 0, 0, 0.85);
            padding: 50px 40px;
            border-radius: 8px;
            width: 100%;
            max-width: 400px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.5);
            box-sizing: border-box;
        }

        h2 { margin: 0 0 10px; font-size: 2rem; font-weight: 600; }
        p { color: #8c8c8c; font-size: 0.95rem; margin-bottom: 25px; }

        .msg { 
            padding: 12px; 
            border-radius: 4px; 
            margin-bottom: 20px; 
            font-size: 0.85rem; 
            text-align: left;
        }
        .success { background: rgba(46, 204, 113, 0.2); color: #2ecc71; border: 1px solid #2ecc71; }
        .error { background: rgba(232, 124, 3, 0.2); color: #e87c03; border: 1px solid #e87c03; }

        input {
            width: 100%;
            padding: 14px;
            margin-bottom: 15px;
            background: #333;
            border: none;
            color: white;
            border-radius: 4px;
            font-size: 1rem;
            box-sizing: border-box;
            transition: background 0.3s;
        }
        input:focus { background: #454545; outline: 2px solid #555; }

        button {
            width: 100%;
            padding: 14px;
            background: var(--primary);
            border: none;
            color: white;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            border-radius: 4px;
            margin-top: 10px;
            transition: 0.2s;
        }
        button:hover { background: #f40612; }

        .footer { margin-top: 25px; text-align: center; }
        .footer a { color: #b3b3b3; text-decoration: none; font-size: 0.9rem; transition: 0.3s; }
        .footer a:hover { text-decoration: underline; color: white; }
    </style>
</head>
<body>

<div class="card">
    <h2>Reset Password</h2>
    <p>We'll send you a link to reset your password and get back into your account.</p>

    <?php if ($message): ?>
        <div class="msg <?= $status ?>"><?= $message ?></div>
    <?php endif; ?>

    <form method="POST">
        <input type="email" name="email" placeholder="Email Address" required>
        <button type="submit">Email Me Reset Link</button>
    </form>

    <div class="footer">
        <a href="login.php">Remembered? Log In</a>
    </div>
</div>

</body>
</html>