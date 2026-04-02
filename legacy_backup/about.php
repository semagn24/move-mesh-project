<?php
session_start();
require_once __DIR__ . '/config/app.php';
$nav_path = __DIR__ . '/users/navibar.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About | MovieStream</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary: #E50914;
            --bg: #0b0b0b;
            --card: #141414;
            --border: rgba(255,255,255,0.1);
            --text-dim: #aaa;
        }

        body { font-family: 'Poppins', sans-serif; background: var(--bg); color: #fff; margin: 0; line-height: 1.6; }
        
        /* Hero Section */
        .about-header {
            padding: 100px 20px 60px;
            text-align: center;
            background: linear-gradient(to bottom, rgba(229, 9, 20, 0.1), transparent);
        }

        .about-header h1 { font-size: 3rem; margin: 0; font-weight: 700; letter-spacing: -1px; }
        .about-header p { color: var(--text-dim); max-width: 700px; margin: 15px auto; font-size: 1.1rem; }

        .container { max-width: 1100px; margin: 0 auto; padding: 0 20px 80px; }

        /* Feature Grid */
        .features {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 25px;
            margin-top: 40px;
        }

        .feature-card {
            background: var(--card);
            padding: 30px;
            border-radius: 15px;
            border: 1px solid var(--border);
            transition: transform 0.3s ease;
        }

        .feature-card:hover { transform: translateY(-5px); border-color: var(--primary); }
        .feature-card i { font-size: 2rem; color: var(--primary); margin-bottom: 20px; }
        .feature-card h3 { margin: 0 0 10px; font-size: 1.3rem; }
        .feature-card p { font-size: 0.95rem; color: var(--text-dim); margin: 0; }

        /* Social & Contact Section */
        .contact-box {
            background: linear-gradient(45deg, #141414, #1f1f1f);
            margin-top: 60px;
            padding: 40px;
            border-radius: 20px;
            text-align: center;
            border: 1px solid var(--border);
        }

        .social-links { display: flex; justify-content: center; gap: 20px; margin-top: 25px; }
        .social-links a {
            width: 50px;
            height: 50px;
            background: #222;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            text-decoration: none;
            font-size: 1.2rem;
            transition: 0.3s;
        }

        .social-links a:hover { background: var(--primary); transform: scale(1.1); }

        .back-home {
            display: inline-block;
            margin-top: 40px;
            color: var(--text-dim);
            text-decoration: none;
            font-size: 0.9rem;
            transition: 0.3s;
        }
        .back-home:hover { color: #fff; }

        /* Responsive */
        @media (max-width: 768px) {
            .about-header h1 { font-size: 2.2rem; }
            .about-header { padding: 60px 20px 40px; }
            .feature-card { padding: 20px; }
        }
    </style>
</head>
<body>

<?php if (file_exists($nav_path)) include $nav_path; ?>

<header class="about-header">
    <h1>MovieStream</h1>
    <p>Redefining how you discover and enjoy your favorite cinematic masterpieces.</p>
</header>

<main class="container">
    <div class="features">
        <div class="feature-card">
            <i class="fa fa-bolt"></i>
            <h3>Lightweight & Fast</h3>
            <p>Built with PHP + PDO for high performance and smooth database interactions, ensuring a lag-free experience.</p>
        </div>

        <div class="feature-card">
            <i class="fa fa-shield-halved"></i>
            <h3>Secure Admin</h3>
            <p>Complete control over your content with a robust admin dashboard to manage movies, users, and roles safely.</p>
        </div>

        <div class="feature-card">
            <i class="fa fa-mobile-screen-button"></i>
            <h3>Fully Responsive</h3>
            <p>Whether on a desktop, tablet, or smartphone, MovieStream adapts perfectly to your screen size.</p>
        </div>
    </div>

    <section class="contact-box">
        <h2>Get in Touch</h2>
        <p>MovieStream is a learning project built with passion. Follow the journey or contribute to the repository.</p>
        
        <div class="social-links">
            <a href="#" title="Facebook"><i class="fab fa-facebook-f"></i></a>
            <a href="#" title="Twitter/X"><i class="fab fa-x-twitter"></i></a>
            <a href="#" title="GitHub"><i class="fab fa-github"></i></a>
            <a href="#" title="Instagram"><i class="fab fa-instagram"></i></a>
            <a href="mailto:admin@moviestream.com" title="Email Us"><i class="fa fa-envelope"></i></a>
        </div>

        <p style="margin-top: 30px; font-size: 0.8rem; color: #666;">
            Version 1.0 — Proudly built with PHP & SQL
        </p>
    </section>

    <center>
        <a href="<?= BASE_URL ?>index.php" class="back-home">
            <i class="fa fa-arrow-left"></i> Back to Home
        </a>
    </center>
</main>

<?php 
$footer_path = __DIR__ . '/users/footer.php';
if (file_exists($footer_path)) include $footer_path; 
?>

</body>
</html>