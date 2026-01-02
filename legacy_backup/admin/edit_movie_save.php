<?php
session_start();
require "../config/db.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    exit("Unauthorized");
}

// Validate inputs
$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    exit("Invalid movie ID");
}

$title = trim($_POST['title'] ?? '');
$genre = trim($_POST['genre'] ?? '');
$year  = trim($_POST['year'] ?? '');

/* ===== FETCH OLD FILES ===== */
$stmt = $pdo->prepare("SELECT poster, video FROM movies WHERE id=?");
$stmt->execute([$id]);
$old = $stmt->fetch(PDO::FETCH_ASSOC);

$posterName = $old['poster'];
$videoName  = $old['video'];

/* ===== POSTER UPLOAD ===== */
$uploadDirPosters = __DIR__ . '/../uploads/posters/';
if (!is_dir($uploadDirPosters)) mkdir($uploadDirPosters, 0755, true);

if (!empty($_FILES['poster']['name'])) {
    if (!isset($_FILES['poster']['error']) || $_FILES['poster']['error'] !== UPLOAD_ERR_OK) {
        exit("Poster upload failed (code: " . ($_FILES['poster']['error'] ?? 'unknown') . ")");
    }

    // Limit size to 5MB
    if ($_FILES['poster']['size'] > 5 * 1024 * 1024) {
        exit("Poster is too large (max 5MB)");
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($_FILES['poster']['tmp_name']);

    $ext = '';
    if ($mime === 'image/jpeg') $ext = 'jpg';
    if ($mime === 'image/png')  $ext = 'png';

    if (!$ext) {
        exit("Poster must be JPG or PNG");
    }

    $posterName = uniqid('p_') . '.' . $ext;

    if (!move_uploaded_file($_FILES['poster']['tmp_name'], $uploadDirPosters . $posterName)) {
        exit("Failed to save poster file");
    }

    if (!empty($old['poster']) && file_exists($uploadDirPosters . $old['poster'])) {
        @unlink($uploadDirPosters . $old['poster']);
    }
} 

/* ===== VIDEO UPLOAD ===== */
$uploadDirVideos  = __DIR__ . '/../uploads/videos/';
if (!is_dir($uploadDirVideos)) mkdir($uploadDirVideos, 0755, true);

if (!empty($_FILES['video']['name'])) {
    if (!isset($_FILES['video']['error']) || $_FILES['video']['error'] !== UPLOAD_ERR_OK) {
        exit("Video upload failed (code: " . ($_FILES['video']['error'] ?? 'unknown') . ")");
    }

    // Limit size to 1GB
    if ($_FILES['video']['size'] > 1024 * 1024 * 1024) {
        exit("Video is too large (max 1GB)");
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($_FILES['video']['tmp_name']);

    if ($mime !== 'video/mp4') {
        exit("Video must be an MP4 file (mime: {$mime})");
    }

    $videoName = uniqid('v_') . '.mp4';

    if (!move_uploaded_file($_FILES['video']['tmp_name'], $uploadDirVideos . $videoName)) {
        exit("Failed to save video file");
    }

    if (!empty($old['video']) && file_exists($uploadDirVideos . $old['video'])) {
        @unlink($uploadDirVideos . $old['video']);
    }
} 

/* ===== UPDATE MOVIE ===== */
$magnet = trim($_POST['magnet_link'] ?? '');
if ($magnet !== '' && stripos($magnet, 'magnet:') !== 0) {
    exit('Invalid magnet link');
}

// Premium flag
$is_premium = isset($_POST['is_premium']) ? 1 : 0;

$colCheck = $pdo->query("SHOW COLUMNS FROM movies LIKE 'is_premium'")->fetch();
if ($colCheck) {
    $pdo->prepare("(
        UPDATE movies
        SET title=?, genre=?, year=?, poster=?, video=?, is_premium=?, magnet_link=?
        WHERE id=?
    )")->execute([
        $title,
        $genre,
        $year,
        $posterName,
        $videoName,
        $is_premium,
        $magnet,
        $id
    ]);
} else {
    $pdo->prepare("(
        UPDATE movies
        SET title=?, genre=?, year=?, poster=?, video=?, magnet_link=?
        WHERE id=?
    )")->execute([
        $title,
        $genre,
        $year,
        $posterName,
        $videoName,
        $magnet,
        $id
    ]);
}

// If an admin provided a magnet, remove any queued seed task for this movie (no longer needed)
$taskFile = __DIR__ . '/../uploads/to_seed/' . $id . '.json';
if (file_exists($taskFile) && $magnet !== '') @unlink($taskFile);

header("Location: movies.php?msg=updated");
exit;
