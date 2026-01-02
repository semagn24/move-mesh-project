<?php
require_once '../config/cors.php';
require_once '../config/database.php';

session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(["error" => "Unauthorized"]);
    exit();
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $search = $_GET['search'] ?? '';
    
    try {
        $sql = "SELECT id, username, email, role FROM users WHERE username LIKE :search OR email LIKE :search ORDER BY id DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':search' => "%$search%"]);
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode(["success" => true, "users" => $users]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(["error" => $e->getMessage()]);
    }
} elseif ($method === 'POST') {
    $data = json_decode(file_get_contents("php://input"));
    $action = $data->action ?? '';
    $userId = $data->user_id ?? 0;
    
    if (!$userId) {
        http_response_code(400);
        echo json_encode(["error" => "User ID required"]);
        exit();
    }

    try {
        if ($action === 'delete') {
            if ($userId == $_SESSION['user_id']) {
                http_response_code(400);
                echo json_encode(["error" => "Cannot delete yourself"]);
                exit();
            }
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            echo json_encode(["success" => true, "message" => "User deleted"]);
        } elseif ($action === 'toggle_role') {
            // Get current role to toggle
            $stmt = $pdo->prepare("SELECT role FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $currentRole = $stmt->fetchColumn();
            
            $newRole = ($currentRole === 'admin') ? 'user' : 'admin';
            
            $update = $pdo->prepare("UPDATE users SET role = ? WHERE id = ?");
            $update->execute([$newRole, $userId]);
            
            echo json_encode(["success" => true, "message" => "User role updated to $newRole"]);
        } else {
            http_response_code(400);
            echo json_encode(["error" => "Invalid action"]);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(["error" => $e->getMessage()]);
    }
}
?>
