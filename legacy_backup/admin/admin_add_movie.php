<?php
require_once __DIR__ . '/../middleware/admin_only.php';
require_once __DIR__ . '/../config/db.php';
$seedConfig = require_once __DIR__ . '/../config/seed_config.php';
$p2pEnabled = !empty($seedConfig['enabled']);
require "header_admin.php"; 

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

    if (!$title || !$genre || !$year) {
        $error = "Please fill all required fields.";
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

        $is_premium = isset($_POST['is_premium']) ? 1 : 0;
        
        // Database Insertion Logic
        try {
            $stmt = $pdo->prepare("INSERT INTO movies (title, genre, year, language, director, actor, poster, video, is_premium, magnet_link, views, rating, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0, 0, NOW())");
            $stmt->execute([$title, $genre, $year, $language, $director, $actor, $poster, $video, $is_premium, $magnet]);
            $message = "🎉 Movie uploaded successfully!";
        } catch (Exception $e) {
            $error = "Database Error: " . $e->getMessage();
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
            --primary: #E50914;
            --bg: #0b0b0b;
            --card: #181818;
            --text-muted: #aaaaaa;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: var(--bg);
            color: white;
            margin: 0;
            padding: 20px;
        }

        .container {
            max-width: 900px;
            margin: 0 auto;
        }

        .header-flex {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        .upload-card {
            background: var(--card);
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.5);
            border: 1px solid #222;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }

        /* Responsive stack for mobile */
        @media (max-width: 768px) {
            .form-grid { grid-template-columns: 1fr; }
            .header-flex { flex-direction: column; gap: 15px; text-align: center; }
        }

        .form-group label {
            font-size: 0.8rem;
            color: var(--text-muted);
            margin-bottom: 8px;
            display: block;
            text-transform: uppercase;
        }

        input, select {
            width: 100%;
            padding: 12px;
            background: #222;
            border: 1px solid #333;
            border-radius: 8px;
            color: white;
            box-sizing: border-box;
        }

        input:focus { border-color: var(--primary); outline: none; }

        .full-width { grid-column: span 2; }
        @media (max-width: 768px) { .full-width { grid-column: span 1; } }

        /* Preview Box */
        #poster-preview {
            width: 100%;
            height: 150px;
            border: 2px dashed #333;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-top: 10px;
            overflow: hidden;
            background-size: cover;
            background-position: center;
        }

        .btn-upload {
            background: var(--primary);
            color: white;
            border: none;
            padding: 15px;
            width: 100%;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            margin-top: 30px;
            font-size: 1rem;
        }

        .btn-upload:disabled { background: #555; }

        .premium-switch {
            display: flex;
            align-items: center;
            gap: 10px;
            background: #222;
            padding: 15px;
            border-radius: 8px;
            border: 1px solid #333;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="header-flex">
        <h1><i class="fa fa-film"></i> New Movie</h1>
        <a href="admin_dashboard.php" style="color:#666; text-decoration:none;"><i class="fa fa-chevron-left"></i> Back</a>
    </div>

    <div class="upload-card">
        <form id="uploadForm" method="POST" enctype="multipart/form-data">
            <div class="form-grid">
                <div class="form-group full-width">
                    <label>Movie Title *</label>
                    <input name="title" required placeholder="Enter title">
                </div>

                <div class="form-group">
                    <label>Genre</label>
                    <input name="genre" required placeholder="Action, Drama...">
                </div>

                <div class="form-group">
                    <label>Year</label>
                    <input type="number" name="year" required value="<?= date('Y') ?>">
                </div>

                <div class="form-group">
                    <label>Poster (Cover)</label>
                    <input type="file" name="poster" id="posterInput" accept="image/*">
                    <div id="poster-preview"><i class="fa fa-image"></i></div>
                </div>

                <div class="form-group">
                    <label>Video File (MP4) *</label>
                    <input type="file" name="video" accept="video/mp4" required>
                    <small style="color: #666;">Max size: 500MB (Depends on InfinityFree limits)</small>
                </div>

                <div class="form-group full-width">
                    <div class="premium-switch">
                        <input type="checkbox" name="is_premium" style="width:auto;">
                        <span>Mark as 💎 Premium Content</span>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn-upload" id="submitBtn">
                <i class="fa fa-cloud-upload-alt"></i> Start Uploading
            </button>
        </form>
    </div>
</div>

<script>
    // Image Preview Logic
    document.getElementById('posterInput').onchange = evt => {
        const [file] = evt.target.files;
        if (file) {
            const preview = document.getElementById('poster-preview');
            preview.style.backgroundImage = `url(${URL.createObjectURL(file)})`;
            preview.innerHTML = '';
        }
    }

    // Prevent double submission
    document.getElementById('uploadForm').onsubmit = function() {
        const btn = document.getElementById('submitBtn');
        btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Uploading... Please Wait';
        btn.disabled = true;
    };
</script>

</body>
</html>