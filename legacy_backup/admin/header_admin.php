<?php
// header_admin.php - session & role check + admin_nav() helper
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Security Check
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

$role = $_SESSION['role'] ?? ($_SESSION['user']['role'] ?? '');
$role = strtolower($role);

if ($role !== 'admin') {
    http_response_code(403);
    echo "<div style='background:#0b0b0b; height:100vh; display:flex; align-items:center; justify-content:center; flex-direction:column; color:white; font-family:sans-serif;'>
            <h1 style='color:#E50914; font-size:4rem; margin:0;'>403</h1>
            <p style='font-size:1.2rem;'>Access Denied. Admins Only.</p>
            <a href='../index.php' style='color:#aaa; text-decoration:none; border:1px solid #444; padding:10px 20px; border-radius:5px;'>Go Back</a>
          </div>";
    exit;
}

/**
 * Echo the admin navbar. Call this from inside your page <body>.
 */
function admin_nav() {
    $username = htmlspecialchars($_SESSION['username'] ?? ($_SESSION['user']['username'] ?? 'Admin'));
    $currentPage = basename($_SERVER['PHP_SELF']);
    ?>
    
    <style>
    /* Admin Navbar Scoped Styles */
    :root { 
        --admin-bg: rgba(15, 15, 15, 0.95);
        --accent: #E50914;
        --nav-text: #ffffff;
    }

    .admin-header {
        background: var(--admin-bg);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        border-bottom: 1px solid rgba(255,255,255,0.1);
        padding: 0 5%;
        position: sticky;
        top: 0;
        z-index: 1000;
        display: flex;
        justify-content: space-between;
        align-items: center;
        height: 70px;
    }

    .admin-logo {
        font-weight: 700;
        color: var(--accent);
        text-decoration: none;
        font-size: 1.4rem;
        letter-spacing: -1px;
    }

    .admin-links {
        display: flex;
        gap: 20px;
        align-items: center;
    }

    .admin-links a {
        color: #bbb;
        text-decoration: none;
        font-size: 0.9rem;
        font-weight: 500;
        transition: 0.3s;
        padding: 5px 0;
        border-bottom: 2px solid transparent;
    }

    .admin-links a:hover, .admin-links a.active {
        color: #fff;
        border-bottom: 2px solid var(--accent);
    }

    .admin-profile {
        display: flex;
        align-items: center;
        gap: 15px;
        color: #fff;
        font-size: 0.9rem;
    }

    .logout-pill {
        background: rgba(255,255,255,0.1);
        padding: 6px 15px;
        border-radius: 20px;
        color: #fff !important;
        font-size: 0.8rem !important;
        border: none !important;
    }

    .logout-pill:hover {
        background: var(--accent);
    }

    /* Mobile Responsive Logic */
    @media (max-width: 850px) {
        .admin-header {
            flex-direction: column;
            height: auto;
            padding: 15px;
        }
        .admin-links {
            margin-top: 15px;
            width: 100%;
            overflow-x: auto;
            padding-bottom: 10px;
            justify-content: flex-start;
            white-space: nowrap;
        }
        .admin-profile {
            display: none; /* Hide profile name on very small mobile to save space */
        }
    }
    </style>

    <header class="admin-header">
        <a href="admin_dashboard.php" class="admin-logo">
            <i class="fa fa-shield-halved"></i> MOVIEBOX <span style="font-weight:300; color:#fff; font-size:0.8rem;">ADMIN</span>
        </a>

        <nav class="admin-links">
            <a href="admin_dashboard.php" class="<?= $currentPage == 'admin_dashboard.php' ? 'active' : '' ?>">
                <i class="fa fa-home"></i> Dashboard
            </a>
            <a href="admin_add_movie.php" class="<?= $currentPage == 'admin_add_movie.php' ? 'active' : '' ?>">
                <i class="fa fa-plus-circle"></i> Add Movie
            </a>
            <a href="movies.php" class="<?= $currentPage == 'movies.php' ? 'active' : '' ?>">
                <i class="fa fa-film"></i> Movies
            </a>
            <a href="users.php" class="<?= $currentPage == 'users.php' ? 'active' : '' ?>">
                <i class="fa fa-users"></i> Users
            </a>

            <?php
                $seedCfg = @include __DIR__ . '/../config/seed_config.php';
                if (!empty($seedCfg['enabled'])) {
                    $active = ($currentPage == 'seeding.php') ? 'active' : '';
                    echo "<a href='seeding.php' class='$active'><i class='fa fa-server'></i> Seeding</a>";
                }
            ?>
        </nav>

        <div class="admin-profile">
            <span><i class="fa fa-user-circle"></i> <?= $username ?></span>
            <a href="../auth/logout.php" class="logout-pill">Logout</a>
        </div>
    </header>
    <?php
}