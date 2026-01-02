<?php
// header_admin.php - session & role check + admin_nav() helper
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

$role = $_SESSION['role'] ?? ($_SESSION['user']['role'] ?? '');
$role = strtolower($role);

if ($role !== 'admin') {
    http_response_code(403);
    echo "<h2 style='color:red;text-align:center;margin-top:50px;'>Access Denied</h2>";
    exit;
}

/**
 * Echo the admin navbar. Call this from inside your page <body>.
 */
function admin_nav() {
    $username = htmlspecialchars($_SESSION['username'] ?? ($_SESSION['user']['username'] ?? 'Admin'));
    // Output admin navbar
    ?>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <style>
    /* GLOBAL (admin base) */
    :root { --admin-primary: #1e3a8a; --primary-accent: #E50914; --bg: #0b0b0b; }
    body { margin: 0; font-family: 'Poppins', 'Segoe UI', Tahoma, sans-serif; background: var(--bg); color: #fff; }
    .container { max-width: 1100px; margin: 40px auto; padding: 0 20px; }

    /* NAVBAR (scoped) */
    .admin-nav { background: var(--admin-primary); padding: 14px 30px; display: flex; align-items: center; box-shadow: 0 4px 15px rgba(0,0,0,0.4); }
    .admin-nav a { color: #ffffff; margin-right: 25px; font-weight: 600; text-decoration: none; letter-spacing: 0.3px; }
    .admin-nav a:hover { text-decoration: underline; }
    .admin-nav .right { margin-left: auto; display: flex; align-items: center; gap: 10px; font-weight: 500; }
    .admin-nav .right a { color: #fff; text-decoration: none; }
    .admin-nav .right a:hover { text-decoration: underline; }
    </style>

    <div class="admin-nav">
        <a href="admin_dashboard.php">Dashboard</a>
        <a href="admin_add_movie.php">Add Movie</a>
        <a href="movies.php">Movies</a>
        <a href="users.php">Users</a>
        <?php
            $seedCfg = @include __DIR__ . '/../config/seed_config.php';
            if (!empty($seedCfg['enabled'])) {
                echo '<a href="seeding.php">Seeding</a>';
            }
        ?>

        <div class="right">
            👤 <?= $username ?> |
            <a href="../auth/logout.php">Logout</a>
        </div>
    </div>
    <?php
}

