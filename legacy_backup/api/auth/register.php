<?php
require_once '../config/cors.php';
require_once '../config/database.php';

$data = json_decode(file_get_contents("php://input"));

if (!isset($data->username) || !isset($data->email) || !isset($data->password) || !isset($data->confirm_password)) {
    http_response_code(400);
    echo json_encode(["error" => "All fields are required"]);
    exit();
}

$username = trim($data->username);
$email = trim($data->email);
$password = $data->password;
$confirm_password = $data->confirm_password;

// Validation
if ($password !== $confirm_password) {
    http_response_code(400);
    echo json_encode(["error" => "Passwords do not match"]);
    exit();
}

// Regex for password strength
if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/', $password)) {
    http_response_code(400);
    echo json_encode(["error" => "Password must be strong (8+ chars, uppercase, lowercase, number, special char)"]);
    exit();
}

try {
    // Check if email exists
    $check = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $check->execute([$email]);

    if ($check->rowCount() > 0) {
        http_response_code(409); // Conflict
        echo json_encode(["error" => "Email already registered"]);
        exit();
    }

    // Insert new user
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, 'user')");

    if ($stmt->execute([$username, $email, $hashed_password])) {
        http_response_code(201);
        echo json_encode(["success" => true, "message" => "Registration successful"]);
    } else {
        http_response_code(500);
        echo json_encode(["error" => "Registration failed"]);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => $e->getMessage()]);
}
?>
