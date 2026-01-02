<?php
session_start();
require_once __DIR__ . '/../config/app.php';
require_once ROOT_PATH . 'config/db.php';

// Get search query
$q = isset($_GET["q"]) ? trim($_GET["q"]) : "";
$q_lower = strtolower($q);

// Prepare case-insensitive query using LOWER() for utf8mb4
$stmt = $pdo->prepare("SELECT * FROM movies
    WHERE LOWER(title) LIKE ?
       OR LOWER(actor) LIKE ?
       OR LOWER(director) LIKE ?
       OR LOWER(genre) LIKE ?
       OR LOWER(language) LIKE ?
       OR CAST(year AS CHAR) LIKE ?");
$stmt->execute([
    "%$q_lower%",
    "%$q_lower%",
    "%$q_lower%",
    "%$q_lower%",
    "%$q_lower%",
    "%$q_lower%"
]);

$results = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Results | MovieBox</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: 'Poppins', sans-serif; background: #0b0b0b; color: white; margin: 0; }
        .container { max-width: 1200px; margin: auto; padding: 20px; }
        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        .card {
            background: #1a1a1a;
            border-radius: 8px;
            padding: 15px;
            text-align: center;
            transition: 0.3s;
        }
        .card:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.5); }
        .card img {
            width: 100%;
            height: 280px;
            object-fit: cover;
            border-radius: 6px;
        }
        .btn {
            display: inline-block;
            padding: 8px 15px;
            margin: 5px 0;
            border-radius: 4px;
            text-decoration: none;
            color: white;
            font-size: 0.9rem;
            font-weight: 600;
        }
        .favorite { background: #E50914; }
        .watch { background: #333; }
        .btn:hover { opacity: 0.9; }
        
        .header-controls { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .back-link { color: #E50914; text-decoration: none; font-weight: 600; }
    </style>
</head>
<body>

<?php include ROOT_PATH . "users/navibar.php"; ?>

<div class="container">
    <div class="header-controls">
        <h2>Search Results for: "<?= htmlspecialchars($q) ?>"</h2>
        <a href="<?= BASE_URL ?>movies/catalog.php" class="back-link">← Back to Catalog</a>
    </div>
    <hr style="border: 0; border-top: 1px solid #333;">

    <?php if (count($results) == 0): ?>
        <div style="text-align: center; padding: 100px 0; color: #555;">
            <i class="fa fa-search" style="font-size: 3rem; margin-bottom: 15px;"></i>
            <p>No movies found for "<?= htmlspecialchars($q) ?>"</p>
        </div>
    <?php else: ?>
        <div class="grid">
            <?php foreach ($results as $m): 
                $poster = BASE_URL . "uploads/posters/" . ($m['poster'] ?: 'default.png');
            ?>
                <div class="card">
                    <img src="<?= $poster ?>" alt="<?= htmlspecialchars($m['title']) ?>">

                    <h3 style="margin: 15px 0 5px; font-size: 1.1rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                        <?= htmlspecialchars($m['title']) ?>
                    </h3>
                    <p style="color: #888; font-size: 0.85rem; margin-bottom: 15px;">
                        <?= htmlspecialchars($m['genre']) ?> • <?= htmlspecialchars($m['year']) ?>
                    </p>

                    <a class="btn favorite" href="<?= BASE_URL ?>users/profile.php?add_fav=<?= $m['id'] ?>">
                        <i class="fa fa-heart"></i> Favorite
                    </a>
                    <a class="btn watch" href="<?= BASE_URL ?>movies/watch.php?id=<?= $m['id'] ?>">
                        <i class="fa fa-play"></i> Watch
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php include ROOT_PATH . "users/footer.php"; ?>

</body>
</html>
