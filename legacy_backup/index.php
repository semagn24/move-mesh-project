<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

error_reporting(E_ALL);
ini_set('display_errors', 1);
// ... the rest of your code
// 1. IMPORT DYNAMIC PATH CONFIG
// This file MUST define BASE_URL (e.g., http://192.168.137.83:3000/movie_stream/)
require_once __DIR__ . "/config/app.php";

// Use ROOT_PATH for server-side includes
$nav_path = ROOT_PATH . 'users/navibar.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    
    <title>MovieBox | Home</title>
    
    <link rel="stylesheet" href="<?= BASE_URL ?>css/style.css?v=<?= time(); ?>">
    
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        :root {
            --netflix-red: #E50914;
            --dark-bg: #0b0b0b;
            --sidebar-width: 240px;
        }

        body {
            background-color: var(--dark-bg);
            color: white;
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 0;
            overflow-x: hidden;
            width: 100%;
        }

        .hero-banner {
            position: relative;
            height: 100vh;
            width: 100%;
            background: linear-gradient(rgba(0,0,0,0.5), rgba(11,11,11,1)), 
                        url('https://images.unsplash.com/photo-1626814026160-2237a95fc5a0?q=80&w=2070&auto=format&fit=crop');
            background-size: cover;
            background-position: center;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
        }

        .main-content {
            margin-left: var(--sidebar-width);
            transition: all 0.3s ease;
            min-height: 100vh;
        }

        .hero-text {
            max-width: 800px;
            padding: 20px;
            z-index: 5;
        }

        .hero-text h1 {
            font-size: clamp(2rem, 8vw, 4.5rem);
            margin: 0;
            font-weight: 700;
            line-height: 1.1;
        }

        .hero-text p {
            font-size: clamp(0.9rem, 3vw, 1.2rem);
            color: #ccc;
            margin: 20px 0 40px;
        }

        .cta-buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn {
            padding: 12px 35px;
            border-radius: 4px;
            text-decoration: none;
            font-weight: 600;
            transition: 0.3s;
            display: inline-block;
            text-align: center;
        }

        .btn-red { background: var(--netflix-red); color: white; }
        .btn-red:hover { background: #b20710; transform: translateY(-2px); }

        .btn-outline {
            background: rgba(255,255,255,0.1);
            color: white;
            border: 1px solid white;
            backdrop-filter: blur(5px);
        }

        .btn-outline:hover { background: white; color: black; }

        .top-auth {
            position: absolute;
            top: 25px;
            right: 30px;
            z-index: 100;
        }

        .user-greeting {
            background: rgba(0,0,0,0.7);
            padding: 8px 18px;
            border-radius: 20px;
            border: 1px solid rgba(255,255,255,0.2);
            font-size: 0.9rem;
        }

        .mobile-toggle {
            display: none;
            position: fixed;
            top: 15px;
            left: 15px;
            z-index: 2000;
            background: var(--netflix-red);
            border: none;
            color: white;
            padding: 10px 14px;
            border-radius: 4px;
            cursor: pointer;
            box-shadow: 0 2px 10px rgba(0,0,0,0.5);
        }

        /* --- RESPONSIVE FIXES --- */
        @media (max-width: 992px) {
            .main-content { 
                margin-left: 0 !important; 
                width: 100%;
            }
            .mobile-toggle { display: block; }
            
            .top-auth { 
                top: 15px; 
                right: 15px; 
            }

            .hero-text h1 { font-size: 2.2rem; }
            
            .cta-buttons { 
                flex-direction: column; 
                align-items: center;
                width: 100%;
                padding: 0 20px;
                box-sizing: border-box;
            }
            
            .btn { 
                width: 100%; 
                max-width: 300px;
                margin-bottom: 5px; 
            }
        }
    </style>
</head>
<body>

<button class="mobile-toggle" onclick="toggleMenu()">
    <i class="fa fa-bars"></i>
</button>

<?php 
if (file_exists($nav_path)) {
    include $nav_path; 
}
?>

<div class="main-content" id="content">
    <div class="top-auth">
        <?php if(isset($_SESSION['user'])): ?>
            <div class="user-greeting">
                <i class="fa fa-user-circle"></i> <strong><?= htmlspecialchars($_SESSION['user']['username']) ?></strong>
            </div>
        <?php else: ?>
            <a href="<?= BASE_URL ?>auth/login.php" class="btn btn-red" style="padding: 6px 20px; font-size: 0.85rem;">Sign In</a>
        <?php endif; ?>
    </div>

    <section class="hero-banner">
        <div class="hero-text">
            <h1>Unlimited movies, TV shows, and more.</h1>
            <p>Ready to watch? Explore the latest blockbusters and exclusive originals.</p>
            
            <div class="cta-buttons">
                <a href="<?= BASE_URL ?>movies/catalog.php" class="btn btn-red">
                    <i class="fa fa-play"></i> Browse Catalog
                </a>
                
                <?php if(!isset($_SESSION['user'])): ?>
                    <a href="<?= BASE_URL ?>auth/register.php" class="btn btn-outline">Create Account</a>
                <?php else: ?>
                    <a href="<?= BASE_URL ?>users/profile.php" class="btn btn-outline">My Library</a>
                <?php endif; ?>
            </div>
        </div>
    </section>
</div>

<?php include ROOT_PATH . 'users/footer.php'; ?>

<script>
    function toggleMenu() {
        // This looks for the .sidebar class usually found in your navibar.php
        const sidebar = document.querySelector('.sidebar');
        if (sidebar) {
            sidebar.classList.toggle('active');
        }
    }
</script>

</body>
</html>