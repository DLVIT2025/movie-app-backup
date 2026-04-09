<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); // For development
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'db_connect.php';

$data = json_decode(file_get_contents("php://input"));

if (!$data) {
    echo json_encode(['success' => false, 'message' => 'Invalid input']);
    exit;
}

$email = trim(strtolower($data->email ?? ''));
$newPassword = $data->newPassword ?? '';

if (empty($email) || empty($newPassword)) {
    echo json_encode(['success' => false, 'message' => 'Please fill all fields']);
    exit;
}

if (strlen($newPassword) < 6) {
    echo json_encode(['success' => false, 'message' => 'Password must be at least 6 characters']);
    exit;
}

try {
    // Check if email exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if (!$stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Email not found in our records.']);
        exit;
    }

    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
    
    $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE email = ?");
    if ($stmt->execute([$hashedPassword, $email])) {
        echo json_encode(['success' => true, 'message' => 'Password updated successfully! You can now login.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update password.']);
    }

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
