<?php
session_start();
require_once __DIR__ . '/header_admin.php';
require_once __DIR__ . '/../config/db.php';

/* ================= ADMIN PROTECTION ================= */
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

/* ================= SEARCH LOGIC ================= */
$search = $_GET['search'] ?? '';
$sql = "SELECT id, title, genre, year, views, rating, created_at, is_premium, magnet_link
        FROM movies
        WHERE title LIKE :search OR genre LIKE :search OR year LIKE :search
        ORDER BY created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute([':search' => "%$search%"]);
$movies = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Movies | MovieBox</title>
    <style>
        :root {
            --primary: #E50914;
            --bg: #0b0b0b;
            --card: #141414;
            --border: #333;
            --text-dim: #999;
        }

        body { background: var(--bg); color: #fff; font-family: 'Poppins', sans-serif; margin: 0; }
        .container { width: 95%; max-width: 1200px; margin: auto; padding: 20px 0; }

        /* Header & Search */
        .header-flex { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px; margin-bottom: 20px; padding: 0 10px; }
        .search-form { display: flex; gap: 5px; background: #1a1a1a; padding: 5px; border-radius: 8px; border: 1px solid var(--border); flex-grow: 1; max-width: 600px; margin: 0 10px 20px 10px; }
        .search-form input { background: transparent; border: none; color: white; padding: 10px; width: 100%; outline: none; }

        /* Table Design for Desktop */
        .movie-table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .movie-table th { text-align: left; padding: 15px; color: var(--text-dim); font-size: 0.85rem; text-transform: uppercase; border-bottom: 2px solid var(--border); }
        .movie-table td { padding: 15px; border-bottom: 1px solid #222; vertical-align: middle; }

        /* Badges & Buttons */
        .badge { padding: 4px 8px; border-radius: 4px; font-size: 0.7rem; font-weight: bold; }
        .badge-premium { background: #ffc107; color: #000; margin-left: 5px; }
        .badge-stats { background: #222; color: #2ecc71; margin-right: 5px; border: 1px solid #444; display: inline-flex; align-items: center; gap: 4px; }
        
        .btn { padding: 8px 16px; border-radius: 6px; text-decoration: none; font-size: 0.85rem; cursor: pointer; border: none; display: inline-flex; align-items: center; gap: 5px; font-weight: 500; }
        .btn-add { background: var(--primary); color: #fff; }
        .btn-edit { background: #333; color: #fff; border: 1px solid #444; }
        .btn-delete { background: transparent; color: #ff4d4d; border: 1px solid #ff4d4d; }

        /* MOBILE OPTIMIZED LAYOUT (Fixes the Left Space Issue) */
        @media (max-width: 768px) {
            .movie-table thead { display: none; }
            .movie-table, .movie-table tbody, .movie-table tr, .movie-table td { display: block; width: 100%; box-sizing: border-box; }

            .movie-table tr { 
                background: var(--card); 
                margin: 0 auto 20px auto; 
                border: 1px solid var(--border); 
                border-radius: 12px;
                overflow: hidden;
                width: calc(100% - 20px); /* Centers the card and leaves equal margins */
            }

            .movie-table td { 
                display: flex; /* Use flex to align label and content */
                justify-content: space-between;
                align-items: center;
                text-align: right;
                padding: 12px 15px;
                border-bottom: 1px solid #222;
                min-height: 45px;
            }

            .movie-table td:last-child { border-bottom: none; background: rgba(255,255,255,0.02); }

            .movie-table td::before {
                content: attr(data-label);
                text-align: left;
                font-weight: 700;
                color: var(--text-dim);
                font-size: 0.75rem;
                text-transform: uppercase;
                flex: 1; /* Takes up half the space */
            }

            .movie-table td > * {
                flex: 1; /* The actual data takes up the other half */
            }

            .header-flex { flex-direction: column; text-align: center; }
            .btn-add { width: 90%; margin: 0 auto; }
        }
    </style>
</head>
<body>

<?php admin_nav(); ?>

<div class="container">
    <div class="header-flex">
        <h2><i class="fa fa-film" style="color:var(--primary)"></i> Movie Library</h2>
        <a href="admin_add_movie.php" class="btn btn-add">Add New Movie</a>
    </div>

    <form class="search-form" method="get">
        <input type="text" name="search" placeholder="Search title, genre, or year..." value="<?= htmlspecialchars($search) ?>">
        <button type="submit" class="btn btn-add" style="border-radius:6px;">Search</button>
    </form>

    <table class="movie-table">
        <thead>
            <tr>
                <th>Title</th>
                <th>Genre</th>
                <th>Year</th>
                <th>Stats</th>
                <th>Created</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($movies as $m): ?>
                <tr>
                    <td data-label="Title">
                        <div>
                            <strong><?= htmlspecialchars($m['title']) ?></strong>
                            <?php if ($m['is_premium']): ?>
                                <span class="badge badge-premium">PREMIUM</span>
                            <?php endif; ?>
                        </div>
                    </td>
                    <td data-label="Genre"><span><?= htmlspecialchars($m['genre']) ?></span></td>
                    <td data-label="Year"><span><?= $m['year'] ?></span></td>
                    <td data-label="Stats">
                        <div>
                            <span class="badge badge-stats">👁 <?= $m['views'] ?></span>
                            <span class="badge badge-stats">⭐ <?= $m['rating'] ?></span>
                        </div>
                    </td>
                    <td data-label="Created"><span><?= date('M d', strtotime($m['created_at'])) ?></span></td>
                    <td data-label="Actions">
                        <div style="display: flex; gap: 8px; justify-content: flex-end; width:100%;">
                            <a href="edit_movie.php?id=<?= $m['id'] ?>" class="btn btn-edit">Edit</a>
                            <form method="post" action="delete_movie.php" onsubmit="return confirm('Delete permanently?');" style="margin:0;">
                                <input type="hidden" name="movie_id" value="<?= $m['id'] ?>">
                                <button type="submit" class="btn btn-delete">Del</button>
                            </form>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

</body>
</html>