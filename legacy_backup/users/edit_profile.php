<?php
session_start();
require_once __DIR__ . "/../config/app.php";
require_once ROOT_PATH . "config/db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$success = "";
$error = "";

// Fetch current user data
$stmt = $pdo->prepare("SELECT username, email, profile_pic, password FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $new_username = trim($_POST['username']);
    $new_email = trim($_POST['email']);
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    $profile_pic = $user['profile_pic'];

    // 1️⃣ Validate Current Password for any password change
    if (!empty($new_password) || !empty($confirm_password)) {
        if (empty($current_password)) {
            $error = "Please enter your current password to update.";
        } elseif (!password_verify($current_password, $user['password'])) {
            $error = "Current password is incorrect.";
        } elseif ($new_password !== $confirm_password) {
            $error = "New passwords do not match.";
        } elseif (strlen($new_password) < 6) {
            $error = "New password must be at least 6 characters.";
        } else {
            // Update password
            $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
            $pw_stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            $pw_stmt->execute([$hashed_password, $user_id]);
            $password_updated = true;
        }
    }

    // 2️⃣ Profile Picture Upload
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        $ext = strtolower(pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, $allowed)) {
            $new_filename = "user_" . $user_id . "_" . time() . "." . $ext;
            move_uploaded_file($_FILES['profile_image']['tmp_name'], "../uploads/profiles/" . $new_filename);
            $profile_pic = $new_filename;
        } else {
            $error = "Invalid image format! (Allowed: jpg, jpeg, png, webp)";
        }
    }

    // 3️⃣ Update Username / Email / Profile
    if (empty($error)) {
        $update = $pdo->prepare("UPDATE users SET username = ?, email = ?, profile_pic = ? WHERE id = ?");
        if ($update->execute([$new_username, $new_email, $profile_pic, $user_id])) {
            $_SESSION['username'] = $new_username;
            $success = isset($password_updated) ? "Profile & Password updated successfully!" : "Profile updated!";
            header("Refresh: 2; url=" . BASE_URL . "users/profile.php");
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Account Settings | MOVIESTREAM</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<style>
    body { background: #141414; color: white; font-family: 'Segoe UI', sans-serif; margin: 0; padding:20px;}
    .settings-card { max-width: 600px; margin: auto; background: #000; padding: 40px; border-radius: 10px; border: 1px solid #333; }
    .header { display: flex; align-items: center; gap: 15px; margin-bottom: 30px; border-bottom: 1px solid #333; padding-bottom: 15px; }
    .header i { color: #e50914; font-size: 24px; }

    .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
    .full-width { grid-column: span 2; }
    label { display:block; margin-bottom:8px; color:#ccc; }
    input { width: 100%; padding: 12px; background: #333; border:1px solid #444; border-radius:5px; color:white; }
    input:focus { border-color:#e50914; outline:none; }

    .profile-upload { text-align:center; margin-bottom:30px; }
    .preview-img { width:120px; height:120px; border-radius:50%; object-fit:cover; border:3px solid #e50914; margin-bottom:10px; }

    .btn-save { background:#e50914; border:none; width:100%; padding:15px; border-radius:5px; margin-top:20px; font-weight:bold; cursor:pointer; }
    .btn-save:hover { background:#ff0a16; }

    .alert { padding:10px; border-radius:5px; margin:10px 0; text-align:center; }
    .alert-success { background:#1f4022; color:#7aff52; }
    .alert-error { background:#3b0d0d; color:#ff6b6b; }

    .section-title { grid-column:span 2; color:#e50914; font-size:15px; margin-top:10px; border-bottom:1px solid #333; padding-bottom:5px; }

    /* 📱 Mobile Responsive */
    @media (max-width: 600px) {
        .form-grid { grid-template-columns: 1fr; }
        .section-title { grid-column:span 1; }
        .settings-card { padding:20px; }
    }
</style>
</head>
<body>

<div class="settings-card">
    <div class="header">
        <i class="fa-solid fa-gear"></i>
        <h2>Account Settings</h2>
    </div>

    <?php if ($success): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>
    <?php if ($error): ?><div class="alert alert-error"><?= $error ?></div><?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <div class="profile-upload">
            <img id="profilePreview" src="<?= BASE_URL ?>uploads/profiles/<?= htmlspecialchars($user['profile_pic']) ?>" class="preview-img">
            <label for="file-input" style="color:#e50914;cursor:pointer;">Change Profile Picture</label>
            <input type="file" id="file-input" name="profile_image" style="display:none;">
        </div>

        <div class="form-grid">
            <div class="section-title">Personal Information</div>

            <div>
                <label>Username</label>
                <input type="text" name="username" value="<?= htmlspecialchars($user['username']) ?>" required>
            </div>
            <div>
                <label>Email Address</label>
                <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
            </div>

            <div class="section-title">Security</div>

            <div>
                <label>Current Password</label>
                <input type="password" name="current_password" placeholder="Required to change password">
            </div>
            <div>
                <label>New Password</label>
                <input type="password" name="new_password" placeholder="Leave blank to keep current">
            </div>
            <div>
                <label>Confirm New Password</label>
                <input type="password" name="confirm_password" placeholder="Confirm password">
            </div>
        </div>

        <button type="submit" class="btn-save">Save Changes</button>
        <p style="margin-top:15px;text-align:center;">
            <a href="<?= BASE_URL ?>users/profile.php" style="color:#bbb;text-decoration:none;">Back to Profile</a>
        </p>
    </form>
</div>

<script>
// 🌟 Live Image Preview
document.getElementById("file-input").addEventListener("change", function(){
    const file = this.files[0];
    if(file){
        document.getElementById("profilePreview").src = URL.createObjectURL(file);
    }
});
</script>

</body>
</html>
