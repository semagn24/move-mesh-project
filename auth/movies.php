<?php
session_start();
require_once __DIR__ . '/header_admin.php';
require_once __DIR__ . '/../config/db.php';

/* ================= ADMIN PROTECTION ================= */
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

/* ================= SEARCH ================= */
$search = $_GET['search'] ?? '';

$seedCfg = @include __DIR__ . '/../config/seed_config.php';
$p2pEnabled = !empty($seedCfg['enabled']);

$sql = "SELECT id, title, genre, year, views, rating, created_at, is_premium, magnet_link
        FROM movies
        WHERE title LIKE :search OR genre LIKE :search OR year LIKE :search
        ORDER BY created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute([
    ':search' => "%$search%"
]);
$movies = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<style>
.container { padding:20px; color:#fff; }
h2 { margin-bottom:15px; }

.search-box { margin-bottom:15px; }

table {
    width:100%;
    border-collapse: collapse;
    background:#111;
}

th, td {
    padding:10px;
    border-bottom:1px solid #333;
    text-align:left;
}

th {
    background:#222;
}

.btn {
    padding:6px 10px;
    border-radius:6px;
    font-size:0.8rem;
    border:none;
    cursor:pointer;
}

.btn-edit { background:#17a2b8; color:#fff; }
.btn-delete { background:#dc3545; color:#fff; }
.btn-edit:hover, .btn-delete:hover { opacity:0.85; }

.badge {
    padding:4px 8px;
    border-radius:10px;
    font-size:0.75rem;
}

.badge-views { background:#6c757d; }
.badge-rating { background:#28a745; }
</style>

<?php admin_nav(); ?>

<div class="container">
    <h2>🎬 Movies Management</h2>

    <!-- SEARCH -->
    <form class="search-box" method="get">
        <input type="text" name="search" placeholder="Search title / genre / year"
               value="<?= htmlspecialchars($search) ?>">
        <button class="btn">Search</button>
        <a href="admin_add_movie.php" class="btn btn-edit">➕ Add Movie</a>
    </form>

    <?php if (empty($movies)): ?>
        <p>No movies found.</p>
    <?php else: ?>
        <table>
            <tr>
                <th>ID</th>
                <th>Title</th>
                <th>Genre</th>
                <th>Year</th>
                <th>Stats</th>
                <th>Created</th>
                <th>Actions</th>
            </tr>

            <?php foreach ($movies as $m): ?>
                <tr>
                    <td><?= $m['id'] ?></td>
                    <td>
                        <?= htmlspecialchars($m['title']) ?>
                        <?php if (!empty($m['is_premium'])): ?>
                            <span class="badge" style="background:#e6b800;color:#111;margin-left:8px;">💎 Premium</span>
                        <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars($m['genre']) ?></td>
                    <td><?= htmlspecialchars($m['year']) ?></td>
                    <td>
                        <span class="badge badge-views">👁 <?= $m['views'] ?></span>
                        <span class="badge badge-rating">⭐ <?= $m['rating'] ?></span>
                    </td>
                    <td><?= date('M d, Y', strtotime($m['created_at'])) ?></td>
                    <td>
                        <a href="edit_movie.php?id=<?= $m['id'] ?>" class="btn btn-edit">
                            ✏ Edit
                        </a>

                        <form method="post" action="delete_movie.php" style="display:inline;"
                              onsubmit="return confirm('Delete this movie permanently?');">
                            <input type="hidden" name="movie_id" value="<?= $m['id'] ?>">
                            <button class="btn btn-delete">🗑 Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>
</div>

</body>
</html>
