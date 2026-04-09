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

$name = trim($data->name ?? '');
$email = trim(strtolower($data->email ?? ''));
$password = $data->password ?? '';

if (empty($name) || empty($email) || empty($password)) {
    echo json_encode(['success' => false, 'message' => 'Please fill all fields']);
    exit;
}

if (strlen($password) < 6) {
    echo json_encode(['success' => false, 'message' => 'Password must be at least 6 characters']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Please enter a valid email address']);
    exit;
}

// Check if email exists
$stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
$stmt->execute([$email]);
if ($stmt->fetch()) {
    echo json_encode(['success' => false, 'message' => 'Email already registered! Please sign in.']);
    exit;
}

// Hash password - the JS sends it plain or btoa hashed?
// If JS sends btoa(pw), let's decode it first just in case, or change JS to send plain.
// We will change auth.js to send plain password, so just hash it directly here.
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

// Insert user
try {
    $id = mt_rand(1, 99999999);
    $stmt = $pdo->prepare("INSERT INTO users (id, name, email, password) VALUES (?, ?, ?, ?)");
    if ($stmt->execute([$id, $name, $email, $hashedPassword])) {
        // Fetch the user back to return
        $userId = $id;
        
        $user = [
            'id' => $userId,
            'name' => $name,
            'email' => $email,
            'joined' => date('Y-m-d\TH:i:s\Z'),
            'isAdmin' => false
        ];

        // --- Append to users CSV (never breaks registration) ---
        try {
            $csvFile = __DIR__ . '/../users.csv';
            $writeHeader = !file_exists($csvFile);
            $fp = fopen($csvFile, 'a');
            if ($fp) {
                if ($writeHeader) {
                    fputcsv($fp, ['ID', 'Name', 'Email', 'Joined Date']);
                }
                fputcsv($fp, [$userId, $name, $email, date('Y-m-d H:i:s')]);
                fclose($fp);
            }
        } catch (Exception $csvErr) {
            // Silently ignore — registration already succeeded
        }
        // --- End CSV ---
        
        echo json_encode(['success' => true, 'message' => 'Account created successfully', 'user' => $user]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Registration failed']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
