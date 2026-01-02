<?php
session_start();
require_once __DIR__ . '/../config/app.php';
require_once ROOT_PATH . 'config/db.php';

// --- Redirect if not logged in ---
if (!isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "auth/login.php?error=login_required");
    exit;
}

$user_id = $_SESSION['user_id'];
$movie_id = filter_input(INPUT_GET, 'movie_id', FILTER_VALIDATE_INT);

if (!$movie_id) {
    die("Invalid movie selected.");
}

// --- Fetch movie info ---
$movie = $pdo->prepare("SELECT * FROM movies WHERE id=?");
$movie->execute([$movie_id]);
$movie = $movie->fetch();

if (!$movie) {
    die("Movie not found");
}

// --- Handle form submission ---
$message = "";
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $comment = trim($_POST['comment'] ?? '');
    $rating = filter_input(INPUT_POST, 'rating', FILTER_VALIDATE_INT);

    if ($comment && $rating && $rating >= 1 && $rating <= 5) {

        // Limit comment length
        $comment = mb_substr($comment, 0, 2000);

        // Insert comment
        $stmt = $pdo->prepare("INSERT INTO comments (user_id,movie_id,comment,rating,created_at) VALUES (?,?,?,?,NOW())");
        $stmt->execute([$user_id, $movie_id, $comment, $rating]);

        // Update average rating
        $avg = $pdo->prepare("SELECT ROUND(AVG(rating),1) AS avg FROM comments WHERE movie_id=?");
        $avg->execute([$movie_id]);
        $avg = $avg->fetchColumn();

        $pdo->prepare("UPDATE movies SET rating = ? WHERE id = ?")->execute([$avg, $movie_id]);

        $message = "Your review has been added! ⭐";
    } else {
        $message = "❌ Please enter a valid rating (1-5) and a comment.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Review: <?= htmlspecialchars($movie['title']); ?></title>

<style>
body {
    font-family: Arial, sans-serif;
    background: #111;
    color: #fff;
    margin: 0;
    padding: 0;
}
.container {
    max-width: 600px;
    margin: 30px auto;
    background: #1b1b1b;
    padding: 20px;
    border-radius: 12px;
    box-shadow: 0 0 10px rgba(255,255,255,0.1);
}

h2 { text-align: center; margin-bottom: 10px; }

.movie-info {
    display: flex;
    gap: 15px;
    align-items: center;
}
.movie-info img {
    width: 100px; border-radius: 6px;
}

form {
    margin-top: 20px;
    display: flex;
    flex-direction: column;
    gap: 15px;
}
textarea {
    width: 100%;
    min-height: 120px;
    border-radius: 8px;
    padding: 10px;
    border: none;
    resize: vertical;
}
.rating-stars {
    display: flex;
    justify-content: center;
    gap: 5px;
}
.rating-stars input {
    display: none;
}
.rating-stars label {
    font-size: 30px;
    cursor: pointer;
    color: #777;
}
.rating-stars input:checked ~ label,
.rating-stars label:hover,
.rating-stars label:hover ~ label {
    color: gold;
}

.btn {
    padding: 12px;
    border-radius: 8px;
    border: none;
    background: gold;
    color: #000;
    font-weight: bold;
    cursor: pointer;
}
.btn:hover { opacity: 0.9; }

.message {
    margin: 10px 0;
    background: #222;
    padding: 10px;
    border-left: 4px solid gold;
}

/* 📱 Responsive */
@media (max-width: 600px) {
    .movie-info {
        flex-direction: column;
        text-align: center;
    }
    .movie-info img { width: 60%; }
}
</style>
</head>

<body>
<div class="container">

    <h2>Review: <?= htmlspecialchars($movie['title']); ?></h2>

    <div class="movie-info">
        <?php if ($movie['poster']): ?>
            <img src="<?= BASE_URL ?>uploads/posters/<?= $movie['poster']; ?>">
        <?php endif; ?>
        <div>
            <p><strong>Current Rating:</strong> ⭐ <?= $movie['rating']; ?>/5</p>
        </div>
    </div>

    <?php if ($message): ?>
        <div class="message"><?= $message; ?></div>
    <?php endif; ?>

    <form method="POST">

        <div class="rating-stars">
            <?php for ($i=5; $i>=1; $i--): ?>
                <input type="radio" name="rating" id="rating-<?= $i ?>" value="<?= $i ?>">
                <label for="rating-<?= $i ?>">⭐</label>
            <?php endfor; ?>
        </div>

        <textarea name="comment" placeholder="Write your thoughts... (max 2000 chars)" required></textarea>

        <button class="btn" type="submit">Submit Review ⭐</button>
    </form>

    <br>
    <a href="<?= BASE_URL ?>movies/movie.php?id=<?= $movie_id; ?>" style="color:gold;">⬅ Back to Movie</a>
</div>

</body>
</html>
