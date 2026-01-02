<?php
require_once __DIR__ . '/../middleware/admin_only.php';
require_once __DIR__ . '/../config/db.php';
$seedConfig = require_once __DIR__ . '/../config/seed_config.php';
$p2pEnabled = !empty($seedConfig['enabled']);
require "header_admin.php"; // Consistency in navigation

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title     = trim($_POST['title'] ?? '');
    $genre     = trim($_POST['genre'] ?? '');
    $year      = trim($_POST['year'] ?? '');
    $language  = trim($_POST['language'] ?? '');
    $director  = trim($_POST['director'] ?? '');
    $actor     = trim($_POST['actor'] ?? '');
    $magnet    = trim($_POST['magnet_link'] ?? '');

    // Basic validation
    if (!$title || !$genre || !$year) {
        $error = "Please fill all required fields.";
    } elseif ($magnet !== '' && stripos($magnet, 'magnet:') !== 0) {
        $error = "Invalid magnet link format.";
    } else {
        $poster = null;
        if (!empty($_FILES['poster']['name'])) {
            $poster = uniqid('poster_') . '.jpg';
            move_uploaded_file($_FILES['poster']['tmp_name'], "../uploads/posters/$poster");
        }

        $video = null;
        if (!empty($_FILES['video']['name'])) {
            $video = uniqid('video_') . '.mp4';
            move_uploaded_file($_FILES['video']['tmp_name'], "../uploads/videos/$video");
        }

        // Premium flag
        $is_premium = isset($_POST['is_premium']) ? 1 : 0;

        // If the DB doesn't have is_premium yet (migration not run), fallback to older insert
        $colCheck = $pdo->query("SHOW COLUMNS FROM movies LIKE 'is_premium'")->fetch();
        if ($colCheck) {
            $stmt = $pdo->prepare("
                INSERT INTO movies 
                (title, genre, year, language, director, actor, poster, video, is_premium, magnet_link, views, rating, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0, 0, NOW())
            ");
            $stmt->execute([$title, $genre, $year, $language, $director, $actor, $poster, $video, $is_premium, $magnet]);
        } else {
            $stmt = $pdo->prepare("
                INSERT INTO movies 
                (title, genre, year, language, director, actor, poster, video, magnet_link, views, rating, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 0, 0, NOW())
            ");
            $stmt->execute([$title, $genre, $year, $language, $director, $actor, $poster, $video, $magnet]);
        }

        // Queue seeding task so an external seeder can create a torrent & magnet link, only when enabled, we don't already have a magnet, and we uploaded a video
        $movie_id = (int)$pdo->lastInsertId();
        if ($p2pEnabled && $magnet === '' && $video) {
            $toSeedDir = __DIR__ . '/../uploads/to_seed';
            if (!is_dir($toSeedDir)) mkdir($toSeedDir, 0755, true);
            $task = [
                'movie_id' => $movie_id,
                'video_path' => realpath(__DIR__ . '/../uploads/videos/' . $video)
            ];
            file_put_contents($toSeedDir . '/' . $movie_id . '.json', json_encode($task));
            $message = "🎉 Movie uploaded successfully! It has been queued for seeding; a magnet link will be added automatically when available.";
        } else {
            $message = "🎉 Movie uploaded successfully!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Movie | Admin Panel</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        :root {
            --primary: #E50914; /* Netflix Red */
            --bg: #0b0b0b;
            --card: rgba(255, 255, 255, 0.05); /* Glass effect */
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: var(--bg);
            color: white;
            margin: 0;
            padding-bottom: 50px;
        }

        .container {
            max-width: 1000px;
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

        /* Form Card Styling matching Dashboard boxes */
        .upload-card {
            background: var(--card);
            backdrop-filter: blur(15px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 25px 50px rgba(0,0,0,0.5);
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 25px;
        }

        .form-group { margin-bottom: 20px; }
        .full-width { grid-column: 1 / -1; }

        label {
            display: block;
            margin-bottom: 8px;
            font-size: 0.85rem;
            color: #bbb;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        input {
            width: 100%;
            padding: 12px 15px;
            background: rgba(0,0,0,0.3);
            border: 1px solid #444;
            border-radius: 8px;
            color: white;
            font-size: 1rem;
            box-sizing: border-box;
            transition: 0.3s;
        }

        input:focus {
            border-color: var(--primary);
            outline: none;
            background: rgba(0,0,0,0.5);
        }

        /* File Upload Custom Look */
        .file-input-wrapper {
            background: rgba(0,0,0,0.2);
            padding: 15px;
            border-radius: 12px;
            border: 1px dashed #555;
            text-align: center;
            transition: 0.3s;
        }

        .file-input-wrapper:hover { border-color: var(--primary); }

        .btn-upload {
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

        .btn-upload:hover {
            background: #b20710;
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(229, 9, 20, 0.3);
        }

        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 25px;
            font-size: 0.9rem;
        }
        .success { background: rgba(40, 167, 69, 0.2); border: 1px solid #28a745; color: #2ecc71; }
        .error { background: rgba(220, 53, 69, 0.2); border: 1px solid #dc3545; color: #ff7675; }

        @media (max-width: 600px) {
            .form-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

<?php admin_nav(); ?>

<div class="container">
    <div class="header-flex">
        <h1><i class="fa fa-plus-circle" style="color:var(--primary)"></i> Add New Movie</h1>
        <a href="admin_dashboard.php" class="btn-back"><i class="fa fa-arrow-left"></i> Dashboard</a>
    </div>

    <?php if($message): ?>
        <div class="alert success"><i class="fa fa-check-circle"></i> <?= $message ?></div>
    <?php endif; ?>

    <?php if($error): ?>
        <div class="alert error"><i class="fa fa-exclamation-triangle"></i> <?= $error ?></div>
    <?php endif; ?>

    <div class="upload-card">
        <form method="POST" enctype="multipart/form-data">
            <div class="form-grid">
                <div class="form-group">
                    <label>Movie Title *</label>
                    <input name="title" required placeholder="e.g. Interstellar">
                </div>

                <div class="form-group">
                    <label>Genre *</label>
                    <input name="genre" required placeholder="e.g. Action, Sci-Fi">
                </div>

                <div class="form-group">
                    <label>Release Year *</label>
                    <input type="number" name="year" required placeholder="2024">
                </div>

                <div class="form-group">
                    <label>Language</label>
                    <input name="language" placeholder="English">
                </div>

                <div class="form-group">
                    <label>Director</label>
                    <input name="director" placeholder="Director Name">
                </div>

                <div class="form-group">
                    <label>Main Actor</label>
                    <input name="actor" placeholder="Lead Actor Name">
                </div>

                <div class="form-group" style="display:flex;align-items:center;gap:10px;">
                    <label style="display:flex;align-items:center;gap:8px;cursor:pointer;">
                        <input type="checkbox" name="is_premium" value="1" style="width:auto;">
                        <span>💎 Premium (Subscription Required)</span>
                    </label>
                </div>

                <div class="form-group">
                    <label>Poster Image (JPG/PNG)</label>
                    <div class="file-input-wrapper">
                        <input type="file" name="poster" accept="image/*">
                    </div>
                </div>

                <div class="form-group">
                    <label>Video File * (MP4)</label>
                    <div class="file-input-wrapper">
                        <input type="file" name="video" accept="video/mp4" required>
                    </div>
                </div>

                <div class="form-group full-width">
                    <label>Magnet Link (optional)</label>
                    <input name="magnet_link" placeholder="magnet:?xt=urn:btih:..." value="<?= htmlspecialchars($_POST['magnet_link'] ?? '') ?>">
                    <small style="color:#aaa">Paste a magnet link if you already have one (optional).</small>
                </div>
            </div>

            <button type="submit" class="btn-upload">
                <i class="fa fa-cloud-upload-alt"></i> Upload to Catalog
            </button>
        </form>
    </div>
</div>

</body>
</html>