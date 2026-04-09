<?php
/**
 * Admin Login Endpoint
 * POST: { email, password }
 * Returns admin user data + token on success
 */
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { exit; }

require_once 'db_connect.php';

$data = json_decode(file_get_contents("php://input"));

if (!$data) {
    echo json_encode(['success' => false, 'message' => 'Invalid input']);
    exit;
}

$email = trim(strtolower($data->email ?? ''));
$password = $data->password ?? '';

if (empty($email) || empty($password)) {
    echo json_encode(['success' => false, 'message' => 'Please enter email and password']);
    exit;
}

try {
    // Check is_admin column exists
    try {
        $stmt = $pdo->prepare("SELECT id, name, email, password, is_admin FROM users WHERE email = ?");
        $stmt->execute([$email]);
    } catch (PDOException $colErr) {
        // Fallback if is_admin column doesn't exist yet
        $stmt = $pdo->prepare("SELECT id, name, email, password FROM users WHERE email = ?");
        $stmt->execute([$email]);
    }
    
    $userRow = $stmt->fetch();

    if (!$userRow) {
        echo json_encode(['success' => false, 'message' => 'Account not found.']);
        exit;
    }

    // Check admin status
    $isAdmin = isset($userRow['is_admin']) ? (bool)$userRow['is_admin'] : ($userRow['email'] === 'admin@cineticket.com');
    
    if (!$isAdmin) {
        echo json_encode(['success' => false, 'message' => 'Access denied. This account does not have admin privileges.']);
        exit;
    }

    // Verify password
    if (password_verify($password, $userRow['password']) || $password === $userRow['password']) {
        // Generate a simple token
        $token = bin2hex(random_bytes(32));
        
        $admin = [
            'id' => $userRow['id'],
            'name' => $userRow['name'],
            'email' => $userRow['email'],
            'isAdmin' => true,
            'token' => $token
        ];

        echo json_encode(['success' => true, 'message' => 'Admin login successful', 'admin' => $admin]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Incorrect password.']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
