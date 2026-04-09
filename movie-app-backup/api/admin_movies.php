<?php
/**
 * Admin Movies CRUD Endpoint
 * GET    — list all movies
 * POST   — add a new movie
 * PUT    — update a movie
 * DELETE — delete a movie by id
 */
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { exit; }

require_once 'db_connect.php';

// Ensure movies table exists
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS movies (
        id VARCHAR(10) PRIMARY KEY,
        title VARCHAR(200) NOT NULL,
        language VARCHAR(50),
        genre VARCHAR(50),
        rating VARCHAR(10),
        duration VARCHAR(20),
        poster_url TEXT,
        backdrop_url TEXT,
        cast_json TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
} catch (PDOException $e) {
    // Table likely already exists
}

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        try {
            $rows = $pdo->query("SELECT * FROM movies ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['success' => true, 'movies' => $rows]);
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        break;

    case 'POST':
        $data = json_decode(file_get_contents("php://input"));
        if (!$data || empty($data->title)) {
            echo json_encode(['success' => false, 'message' => 'Movie title is required']);
            exit;
        }

        $id = 'm' . str_pad(mt_rand(100, 99999), 5, '0', STR_PAD_LEFT);
        // Check for custom ID
        if (!empty($data->id)) { $id = $data->id; }

        try {
            $stmt = $pdo->prepare("INSERT INTO movies (id, title, language, genre, rating, duration, poster_url, backdrop_url, cast_json) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $id,
                $data->title,
                $data->language ?? '',
                $data->genre ?? '',
                $data->rating ?? '0',
                $data->duration ?? '',
                $data->poster_url ?? '',
                $data->backdrop_url ?? '',
                $data->cast_json ?? '[]'
            ]);
            echo json_encode(['success' => true, 'message' => 'Movie added successfully', 'id' => $id]);
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Failed to add movie: ' . $e->getMessage()]);
        }
        break;

    case 'PUT':
        $data = json_decode(file_get_contents("php://input"));
        if (!$data || empty($data->id)) {
            echo json_encode(['success' => false, 'message' => 'Movie ID is required']);
            exit;
        }

        try {
            $stmt = $pdo->prepare("UPDATE movies SET title=?, language=?, genre=?, rating=?, duration=?, poster_url=?, backdrop_url=?, cast_json=? WHERE id=?");
            $stmt->execute([
                $data->title ?? '',
                $data->language ?? '',
                $data->genre ?? '',
                $data->rating ?? '0',
                $data->duration ?? '',
                $data->poster_url ?? '',
                $data->backdrop_url ?? '',
                $data->cast_json ?? '[]',
                $data->id
            ]);
            echo json_encode(['success' => true, 'message' => 'Movie updated successfully']);
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Failed to update movie: ' . $e->getMessage()]);
        }
        break;

    case 'DELETE':
        $data = json_decode(file_get_contents("php://input"));
        if (!$data || empty($data->id)) {
            echo json_encode(['success' => false, 'message' => 'Movie ID is required']);
            exit;
        }

        try {
            $stmt = $pdo->prepare("DELETE FROM movies WHERE id = ?");
            $stmt->execute([$data->id]);
            echo json_encode(['success' => true, 'message' => 'Movie deleted successfully']);
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Failed to delete movie: ' . $e->getMessage()]);
        }
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
}
?>
