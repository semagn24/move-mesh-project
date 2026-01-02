<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once dirname(__DIR__) . "/config/app.php";
require_once ROOT_PATH . "config/db.php";

$view_mode = isset($_GET['view']) ? $_GET['view'] : 'all';
$search = isset($_GET['q']) ? trim($_GET['q']) : '';

/** * FETCH LOGIC 
 */
try {
    if ($view_mode === 'trending') {
        // We use INNER JOIN to link with history and HAVING to filter out low-view content
        // This ensures the Trending tab does NOT just show every movie.
        $stmt = $pdo->prepare("
            SELECT m.*, COUNT(h.movie_id) as view_count 
            FROM movies m 
            INNER JOIN history h ON m.id = h.movie_id 
            WHERE h.watched_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            GROUP BY m.id 
            HAVING view_count >= 1
            ORDER BY view_count DESC 
            LIMIT 12
        ");
        $stmt->execute();
        $movies = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
        $query = "SELECT * FROM movies WHERE 1=1";
        $params = [];
        
        if ($search !== '') {
            $query .= " AND (LOWER(title) LIKE ? OR LOWER(actor) LIKE ?)";
            $search_param = "%" . strtolower($search) . "%";
            $params[] = $search_param;
            $params[] = $search_param;
        }
        
        $query .= " ORDER BY created_at DESC";
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        $movies = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (Exception $e) {
    $movies = [];
    $error_msg = "Error fetching movies: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Browse Movies | MovieBox</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>css/style.css">
    <style>
        :root { --netflix-red: #E50914; --dark-bg: #0b0b0b; }
        body { background: var(--dark-bg); color: white; font-family: 'Poppins', sans-serif; margin: 0; }
        
        .content-wrapper { margin-left: 240px; transition: 0.3s; padding-bottom: 50px; }
        
        .catalog-tabs { display: flex; gap: 30px; padding: 20px 5%; background: #141414; border-bottom: 1px solid #222; }
        .tab-link { color: #888; text-decoration: none; font-weight: 600; padding-bottom: 10px; border-bottom: 3px solid transparent; transition: 0.3s; }
        .tab-link.active { color: white; border-bottom-color: var(--netflix-red); }

        .movie-grid { 
            display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); 
            gap: 20px; padding: 20px 5%; 
        }

        /* Responsive Movie Card */
        .movie-card { position: relative; background: #1a1a1a; border-radius: 8px; overflow: visible; transition: 0.3s; }
        .movie-card:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.5); }
        .movie-card img { width: 100%; height: 260px; object-fit: cover; border-radius: 8px 8px 0 0; display: block; }

        /* Functional Three-Dot Menu */
        .card-options { position: absolute; top: 10px; right: 10px; z-index: 100; }
        .three-dot-btn { 
            background: rgba(0,0,0,0.7); border: none; color: white; 
            width: 32px; height: 32px; border-radius: 50%; cursor: pointer;
            display: flex; align-items: center; justify-content: center;
            backdrop-filter: blur(5px);
        }
        
        .options-menu { 
            display: none; position: absolute; right: 0; top: 40px; 
            background: #222; border: 1px solid #444; border-radius: 4px; 
            width: 160px; box-shadow: 0 10px 25px rgba(0,0,0,0.8); overflow: hidden;
        }
        .options-menu.active { display: block; }
        .options-menu a { display: block; color: white; padding: 12px 15px; text-decoration: none; font-size: 0.85rem; transition: 0.2s; }
        .options-menu a i { margin-right: 10px; width: 15px; text-align: center; }
        .options-menu a:hover { background: var(--netflix-red); }

        .view-badge { font-size: 0.75rem; color: #aaa; margin-top: 5px; display: block; }

        @media (max-width: 992px) {
            .content-wrapper { margin-left: 0; }
            .movie-grid { grid-template-columns: repeat(2, 1fr); gap: 15px; padding: 15px; }
            .movie-card img { height: 220px; }
        }
        }
        
        /* Toast Notification */
        #toast {
            visibility: hidden;
            min-width: 250px;
            background-color: #333;
            color: #fff;
            text-align: center;
            border-radius: 8px;
            padding: 16px;
            position: fixed;
            z-index: 1000;
            left: 50%;
            bottom: 30px;
            transform: translateX(-50%);
            box-shadow: 0 10px 30px rgba(0,0,0,0.5);
            border-left: 4px solid var(--netflix-red);
        }
        #toast.show { visibility: visible; animation: fadein 0.5s, fadeout 0.5s 2.5s; }
        @keyframes fadein { from {bottom: 0; opacity: 0;} to {bottom: 30px; opacity: 1;} }
        @keyframes fadeout { from {bottom: 30px; opacity: 1;} to {bottom: 0; opacity: 0;} }
    </style>
</head>
<body>

<?php include ROOT_PATH . "users/navibar.php"; ?>

<div class="content-wrapper">
    <div class="catalog-tabs">
        <a href="?view=all" class="tab-link <?= $view_mode !== 'trending' ? 'active' : '' ?>">
            <i class="fa fa-th-large"></i> All Movies
        </a>
        <a href="?view=trending" class="tab-link <?= $view_mode === 'trending' ? 'active' : '' ?>">
            <i class="fa fa-fire"></i> Trending
        </a>
    </div>

    <div style="padding: 25px 5% 10px;">
        <h2 style="margin: 0; font-size: 1.5rem;">
            <?= $view_mode === 'trending' ? '🔥 Trending Now' : 'Latest Uploads' ?>
        </h2>
        <p style="color: #666; font-size: 0.9rem; margin-top: 5px;">
            <?= $view_mode === 'trending' ? 'The most watched movies this week.' : 'Explore our full library of content.' ?>
        </p>
    </div>

    <main class="movie-grid">
        <?php if (empty($movies)): ?>
            <div style="grid-column: 1/-1; text-align: center; padding: 100px 0; color: #555;">
                <i class="fa fa-film" style="font-size: 3rem; margin-bottom: 15px;"></i>
                <p>No movies found in this section.</p>
            </div>
        <?php else: ?>
            <?php foreach ($movies as $m): 
                $poster = BASE_URL . "uploads/posters/" . ($m['poster'] ?: 'default.png');
            ?>
                <div class="movie-card">
                    <div class="card-options">
                        <button class="three-dot-btn" onclick="toggleMenu(event, <?= $m['id'] ?>)">
                            <i class="fa fa-ellipsis-v"></i>
                        </button>
                        <div id="menu-<?= $m['id'] ?>" class="options-menu">
                            <a href="<?= BASE_URL ?>movies/watch.php?id=<?= $m['id'] ?>"><i class="fa fa-play"></i> Watch Now</a>
                            <a href="#" onclick="addToPlaylist(event, <?= $m['id'] ?>)"><i class="fa fa-plus"></i> My List</a>
                            <a href="#" onclick="shareMovie(event, <?= $m['id'] ?>)"><i class="fa fa-share"></i> Share</a>
                        </div>
                    </div>

                    <a href="<?= BASE_URL ?>movies/watch.php?id=<?= $m['id'] ?>">
                        <img src="<?= $poster ?>" alt="<?= htmlspecialchars($m['title']) ?>">
                    </a>
                    
                    <div style="padding: 12px;">
                        <p style="margin:0; font-weight: 600; font-size: 0.9rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                            <?= htmlspecialchars($m['title']) ?>
                        </p>
                        
                        <?php if ($view_mode === 'trending'): ?>
                            <span class="view-badge">
                                <i class="fa fa-eye"></i> <?= number_format($m['view_count']) ?> views
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </main>
</div>

<script>
    function toggleMenu(event, id) {
        event.preventDefault();
        event.stopPropagation();
        
        // Close all other menus first
        document.querySelectorAll('.options-menu').forEach(m => {
            if(m.id !== 'menu-'+id) m.classList.remove('active');
        });

        const menu = document.getElementById('menu-' + id);
        menu.classList.toggle('active');
    }

    // Close menu when clicking anywhere else on the document
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.card-options')) {
            document.querySelectorAll('.options-menu').forEach(m => m.classList.remove('active'));
        }
    });

    // Placeholder functions for list and share
    function addToPlaylist(e, id) {
        e.preventDefault();
        fetch('<?= BASE_URL ?>users/toggle_favorite.php?id=' + id)
        .then(r => r.json())
        .then(res => {
            if(res.status === 'added') {
                showToast("Added to My List! ❤️");
            } else if(res.status === 'removed') {
                showToast("Removed from My List.");
            } else {
                showToast("Error: " + (res.message || "Update failed."));
            }
        }).catch(err => {
            showToast("Please login to save movies.");
        });
    }

    function showToast(msg) {
        const x = document.getElementById("toast");
        x.innerHTML = msg;
        x.className = "show";
        setTimeout(function(){ x.className = x.className.replace("show", ""); }, 3000);
    }

    function shareMovie(e, id) {
        e.preventDefault();
        const url = "<?= BASE_URL ?>movies/watch.php?id=" + id;
        navigator.clipboard.writeText(url).then(() => {
            showToast("Watch link copied to clipboard!");
        });
    }
</script>

<div id="toast"></div>

</body>
</html>