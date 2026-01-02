<?php
require "header_admin.php";   
require_once __DIR__ . "/../config/db.php";

// Fetch Stats with Error Handling
try {
    $totalMovies = $pdo->query("SELECT COUNT(*) FROM movies")->fetchColumn();
    $totalUsers  = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    $admins      = $pdo->query("SELECT COUNT(*) FROM users WHERE role='admin'")->fetchColumn();
    $normalUsers = $pdo->query("SELECT COUNT(*) FROM users WHERE role='user'")->fetchColumn();
    
    // Check if views column exists
    $watchCount = $pdo->query("SELECT SUM(views) FROM movies")->fetchColumn() ?: 0;

    /* ================= NEW REVENUE & SUBSCRIPTION STATS ================= */
    $totalRevenue = $pdo->query("SELECT SUM(amount) FROM payments WHERE status='success'")->fetchColumn() ?: 0;
    $premiumMembers = $pdo->query("SELECT COUNT(*) FROM users WHERE subscription_status='premium'")->fetchColumn();

} catch (Exception $e) {
    $totalMovies = $totalUsers = $admins = $watchCount = $totalRevenue = $premiumMembers = 0;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | MovieBox</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary: #E50914;
            --bg: #0b0b0b;
            --card: #181818;
            --success: #2ecc71;
            --info: #3498db;
        }

        body { 
            font-family: 'Poppins', sans-serif; 
            background: var(--bg); 
            color: white; 
            margin: 0; 
            padding-bottom: 50px;
        }

        .container { 
            width: 95%; 
            max-width: 1200px; 
            margin: auto; 
            padding: 20px 0; 
        }

        .header-section {
            margin-bottom: 40px;
            padding: 0 10px;
        }

        h1 { font-size: 1.8rem; margin-bottom: 5px; }
        .welcome-msg { color: #888; font-size: 0.9rem; }

        /* Grid System */
        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
            padding: 10px;
        }

        /* Modern Box Styling */
        .box {
            background: var(--card);
            padding: 25px;
            border-radius: 16px;
            position: relative;
            overflow: hidden;
            border: 1px solid #222;
            transition: all 0.3s ease;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .box:hover {
            transform: translateY(-8px);
            border-color: #444;
            box-shadow: 0 10px 20px rgba(0,0,0,0.4);
        }

        .box i.bg-icon {
            position: absolute;
            right: -10px;
            bottom: -10px;
            font-size: 80px;
            opacity: 0.05;
            color: white;
        }

        .num {
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 2px;
            z-index: 1;
        }

        .label {
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #888;
            font-weight: 500;
            z-index: 1;
        }

        /* Button Menu */
        .action-menu {
            margin-top: 40px;
            padding: 0 10px;
        }

        .btn-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 15px;
        }

        .btn {
            background: #222;
            color: white;
            text-decoration: none;
            padding: 16px;
            border-radius: 12px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 12px;
            border: 1px solid #333;
            transition: 0.3s;
        }

        .btn:hover {
            background: #282828;
            border-color: var(--primary);
        }

        .btn-main {
            background: var(--primary);
            border-color: var(--primary);
        }

        .btn-main:hover {
            background: #b20710;
        }

        /* Mobile Adjustments */
        @media (max-width: 600px) {
            .grid { grid-template-columns: 1fr; }
            .btn-grid { grid-template-columns: 1fr; }
            h1 { font-size: 1.5rem; }
        }
    </style>
</head>

<body>

<?php admin_nav(); ?>

<div class="container">
    <div class="header-section">
        <h1><i class="fa fa-chart-line" style="color:var(--primary)"></i> Analytics Overview</h1>
        <p class="welcome-msg">Logged in as: <strong><?= htmlspecialchars($_SESSION['username'] ?? 'Admin') ?></strong></p>
    </div>

    <div class="grid">
        <div class="box" style="border-left: 4px solid var(--success);">
            <i class="fa fa-money-bill-wave bg-icon"></i>
            <div class="num" style="color: var(--success);"><?= number_format($totalRevenue, 2) ?> <small style="font-size: 12px;">ETB</small></div>
            <div class="label">Total Revenue</div>
        </div>

        <div class="box" style="border-left: 4px solid var(--info);">
            <i class="fa fa-crown bg-icon"></i>
            <div class="num" style="color: var(--info);"><?= $premiumMembers ?></div>
            <div class="label">Premium Members</div>
        </div>

        <div class="box">
            <i class="fa fa-film bg-icon"></i>
            <div class="num"><?= $totalMovies ?></div>
            <div class="label">Library Movies</div>
        </div>

        <div class="box">
            <i class="fa fa-users bg-icon"></i>
            <div class="num"><?= $totalUsers ?></div>
            <div class="label">Total Registered</div>
        </div>

        <div class="box">
            <i class="fa fa-play-circle bg-icon"></i>
            <div class="num"><?= $watchCount ?></div>
            <div class="label">Total Streamed</div>
        </div>

        <div class="box">
            <i class="fa fa-user-shield bg-icon"></i>
            <div class="num"><?= $admins ?></div>
            <div class="label">System Admins</div>
        </div>
    </div>

    <div class="action-menu">
        <h3 style="margin-bottom: 20px; font-weight: 500; color: #bbb;">Quick Actions</h3>
        <div class="btn-grid">
            <a class="btn btn-main" href="admin_add_movie.php"><i class="fa fa-plus-square"></i> Upload Content</a>
            <a class="btn" href="movies.php"><i class="fa fa-edit"></i> Edit Movies</a>
            <a class="btn" href="users.php"><i class="fa fa-user-cog"></i> User Management</a>
            <a class="btn" href="admin_transactions.php"><i class="fa fa-history"></i> Payments</a>
            <a class="btn" href="../index.php" style="background: rgba(255,255,255,0.05);"><i class="fa fa-external-link-alt"></i> Preview Site</a>
        </div>
    </div>
</div>

</body>
</html>