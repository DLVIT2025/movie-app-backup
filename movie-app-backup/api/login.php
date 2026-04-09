<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); // For development
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'db_connect.php';

// Get JSON input
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
    // Try with is_admin column first (after seed script runs)
    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $userRow = $stmt->fetch();
    } catch (PDOException $e) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $userRow = $stmt->fetch();
    }

    if (!$userRow) {
        echo json_encode(['success' => false, 'message' => 'Account not found. Please sign up.']);
        exit;
    }

    // Verify Password
    // Support both hashed passwords and plain-text (for manually entered DB records)
    if (password_verify($password, $userRow['password']) || $password === $userRow['password']) {
        // Check admin status from DB column or fallback to email check
        $isAdmin = isset($userRow['is_admin']) ? (bool)$userRow['is_admin'] : ($userRow['email'] === 'admin@cineticket.com');
        
        $user = [
            'id' => $userRow['id'],
            'name' => $userRow['name'],
            'email' => $userRow['email'],
            'isAdmin' => $isAdmin
        ];
        
        echo json_encode(['success' => true, 'message' => 'Login successful', 'user' => $user]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Incorrect password. Please try again.']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
