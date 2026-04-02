<?php
require "header_admin.php"; // Keeps navigation and admin session check consistent
require_once __DIR__ . "/../config/db.php";

/* ================= VALIDATE MOVIE ID ================= */
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if ($id === false || $id === null || $id <= 0) {
    header("Location: movies.php?error=invalid_id");
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM movies WHERE id=?");
$stmt->execute([$id]);
$movie = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$movie) {
    header("Location: movies.php?error=not_found");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit: <?= htmlspecialchars($movie['title']) ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary: #E50914; /* Netflix Red from your login */
            --bg: #0b0b0b;
            --card: rgba(255, 255, 255, 0.05);
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: var(--bg);
            color: white;
            margin: 0;
            padding-bottom: 50px;
        }

        .container {
            max-width: 900px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .header-flex {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .btn-back {
            text-decoration: none;
            color: #aaa;
            font-size: 0.9rem;
            transition: 0.3s;
        }

        .btn-back:hover { color: var(--primary); }

        /* Form Card Styling */
        .edit-card {
            background: var(--card);
            backdrop-filter: blur(15px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 25px 50px rgba(0,0,0,0.5);
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 25px;
        }

        .form-group { margin-bottom: 20px; }
        .full-width { grid-column: span 2; }

        label {
            display: block;
            margin-bottom: 8px;
            font-size: 0.85rem;
            color: #bbb;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        input[type="text"], input[type="number"], select {
            width: 100%;
            padding: 12px 15px;
            background: rgba(0,0,0,0.3);
            border: 1px solid #444;
            border-radius: 8px;
            color: white;
            font-size: 1rem;
            transition: 0.3s;
        }

        input:focus {
            border-color: var(--primary);
            outline: none;
            background: rgba(0,0,0,0.5);
        }

        /* Poster Preview */
        .poster-preview-wrapper {
            display: flex;
            gap: 20px;
            align-items: center;
            background: rgba(0,0,0,0.2);
            padding: 15px;
            border-radius: 12px;
            border: 1px dashed #555;
        }

        .poster-img {
            width: 100px;
            height: 140px;
            object-fit: cover;
            border-radius: 8px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.5);
        }

        /* Styled File Input */
        input[type="file"] {
            font-size: 0.8rem;
            color: #888;
        }

        .btn-save {
            width: 100%;
            padding: 16px;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: 0.3s;
            margin-top: 20px;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
        }

        .btn-save:hover {
            background: #b20710;
            transform: translateY(-2px);
        }

        @media (max-width: 768px) {
            .form-grid { grid-template-columns: 1fr; }
            .full-width { grid-column: span 1; }
        }
    </style>
</head>
<body>

<?php admin_nav(); ?>

<div class="container">
    <div class="header-flex">
        <h1><i class="fa fa-edit" style="color:var(--primary)"></i> Edit Movie</h1>
        <a href="movies.php" class="btn-back"><i class="fa fa-arrow-left"></i> Back to List</a>
    </div>

    <div class="edit-card">
        <form method="post" action="edit_movie_save.php" enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?= $movie['id'] ?>">

            <div class="form-grid">
                <div class="form-group full-width">
                    <label>Movie Title</label>
                    <input type="text" name="title" value="<?= htmlspecialchars($movie['title']) ?>" required placeholder="Enter movie title">
                </div>

                <div class="form-group">
                    <label>Genre</label>
                    <input type="text" name="genre" value="<?= htmlspecialchars($movie['genre']) ?>" placeholder="e.g. Action, Sci-Fi">
                </div>

                <div class="form-group">
                    <label>Release Year</label>
                    <input type="number" name="year" value="<?= htmlspecialchars($movie['year']) ?>" placeholder="e.g. 2024">
                </div>

                <div class="form-group full-width">
                    <label>Movie Poster</label>
                    <div class="poster-preview-wrapper">
                        <?php 
                        $posterFile = $movie['poster'] ?: 'default.png';
                        $posterPath = "../uploads/posters/" . $posterFile; // Standardized path
                        ?>
                        <img src="<?= $posterPath ?>" alt="Current Poster" class="poster-img">
                        <div>
                            <p style="margin: 0 0 10px 0; font-size: 0.8rem;">Replace current poster:</p>
                            <input type="file" name="poster" accept="image/*">
                        </div>
                    </div>
                </div>

                <div class="form-group full-width">
                    <label><i class="fa fa-video"></i> Replace Video File (MP4)</label>
                    <input type="file" name="video" accept="video/mp4" style="margin-top:10px;">
                    <p style="font-size: 0.7rem; color: #666; margin-top: 5px;">Current file: <?= htmlspecialchars($movie['video']) ?></p>
                </div>

                <div class="form-group">
                    <label style="display:flex;align-items:center;gap:8px;cursor:pointer;">
                        <input type="checkbox" name="is_premium" value="1" <?= !empty($movie['is_premium']) ? 'checked' : '' ?> style="width:auto;">
                        <span>💎 Premium (Subscription Required)</span>
                    </label>
                </div>

                <div class="form-group full-width">
                    <label>Magnet Link (P2P)</label>
                    <input type="text" name="magnet_link" value="<?= htmlspecialchars($movie['magnet_link'] ?? '') ?>" placeholder="magnet:?xt=urn:btih:...">
                    <small style="color:#aaa">Paste a magnet link here if you already have one, otherwise leave blank.</small>
                </div>
            </div>

            <button type="submit" class="btn-save">
                <i class="fa fa-save"></i> Save Changes
            </button>
        </form>
    </div>
</div>

</body>
</html>