<?php
require "header_admin.php";   
require_once __DIR__ . "/../config/db.php";

// Fetch Stats with Error Handling
try {
    $totalMovies = $pdo->query("SELECT COUNT(*) FROM movies")->fetchColumn();
    $totalUsers  = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    $admins      = $pdo->query("SELECT COUNT(*) FROM users WHERE role='admin'")->fetchColumn();
    $normalUsers = $pdo->query("SELECT COUNT(*) FROM users WHERE role='user'")->fetchColumn();
    
    // Check if views column exists in movies or use history table
    $watchCount = $pdo->query("SELECT SUM(views) FROM movies")->fetchColumn() ?: 0;
} catch (Exception $e) {
    $watchCount = 0; // Fallback if table doesn't exist
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel | MovieBox</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary: #E50914;
            --bg: #0b0b0b;
            --card: #1a1a1a;
        }
        body { 
            font-family: 'Poppins', sans-serif; 
            background: var(--bg); 
            color: white; 
            margin: 0; 
        }
        .container { width: 90%; max-width: 1200px; margin: auto; padding: 40px 0; }
        
        h1 { font-weight: 600; letter-spacing: -1px; }
        .welcome-msg { color: #aaa; margin-bottom: 40px; }

        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
        }
        .box {
            background: var(--card);
            padding: 30px;
            border-radius: 12px;
            text-align: center;
            border: 1px solid #333;
            transition: 0.3s;
        }
        .box:hover { border-color: var(--primary); transform: translateY(-5px); }
        
        .num {
            font-size: 36px;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 5px;
        }
        .label {
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #888;
        }

        .btn-container { 
            margin-top: 50px; 
            display: flex; 
            gap: 15px; 
            flex-wrap: wrap; 
        }
        .btn {
            padding: 14px 28px;
            background: #222;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
            border: 1px solid #444;
            transition: 0.3s;
        }
        .btn:hover { background: var(--primary); border-color: var(--primary); }
        .btn-main { background: var(--primary); border-color: var(--primary); }
    </style>
</head>

<body>

<?php admin_nav(); ?>

<div class="container">
    <h1><i class="fa fa-gauge-high"></i> Admin Dashboard</h1>
    <p class="welcome-msg">Welcome back, <strong><?= htmlspecialchars($_SESSION['user']['username']) ?></strong> (Administrator)</p>

    

    <div class="grid">
        <div class="box">
            <div class="num"><?= $totalMovies ?></div>
            <div class="label">Total Movies</div>
        </div>

        <div class="box">
            <div class="num"><?= $totalUsers ?></div>
            <div class="label">Total Users</div>
        </div>

        <div class="box">
            <div class="num"><?= $admins ?></div>
            <div class="label">Admins</div>
        </div>

        <div class="box">
            <div class="num"><?= $watchCount ?></div>
            <div class="label">Total Stream Views</div>
        </div>
    </div>

    <div class="btn-container">
        <a class="btn btn-main" href="admin_add_movie.php"><i class="fa fa-plus"></i> Add New Movie</a>
        <a class="btn" href="movies.php"><i class="fa fa-film"></i> Manage Movies</a>
        <a class="btn" href="users.php"><i class="fa fa-users"></i> Manage Users</a>
        <a class="btn" href="../index.php" style="margin-left: auto;"><i class="fa fa-eye"></i> View Site</a>
    </div>
</div>

</body>
</html>