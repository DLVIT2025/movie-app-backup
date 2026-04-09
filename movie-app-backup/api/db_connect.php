<?php
$host = '127.0.0.1';
$db   = 'users_db';
$user = 'root'; // default XAMPP user
$pass = '';     // default XAMPP password
$charset = 'utf8mb4';

// Try multiple DB names depending on which tutorial step they followed
$dbnames = ['users_db', 'movie_db'];
$pdo = null;

foreach ($dbnames as $db) {
    if ($pdo) break;
    $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    try {
        $pdo = new PDO($dsn, $user, $pass, $options);
    } catch (\PDOException $e) {
        $pdo = null;
    }
}

if (!$pdo) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Database connection failed. Please ensure your database is running and named "users_db" or "movie_db".']);
    exit;
}
?>
