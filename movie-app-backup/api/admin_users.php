<?php
/**
 * Admin Users Endpoint
 * GET    — list all users
 * DELETE — delete a user by id
 */
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { exit; }

require_once 'db_connect.php';

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        try {
            // Try with is_admin column first
            try {
                $rows = $pdo->query("SELECT id, name, email, is_admin FROM users ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                $rows = $pdo->query("SELECT id, name, email FROM users ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
            }
            echo json_encode(['success' => true, 'users' => $rows]);
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        break;

    case 'DELETE':
        $data = json_decode(file_get_contents("php://input"));
        if (!$data || empty($data->id)) {
            echo json_encode(['success' => false, 'message' => 'User ID is required']);
            exit;
        }

        try {
            // Don't allow deleting admin
            $stmt = $pdo->prepare("SELECT email FROM users WHERE id = ?");
            $stmt->execute([$data->id]);
            $user = $stmt->fetch();
            if ($user && $user['email'] === 'admin@cineticket.com') {
                echo json_encode(['success' => false, 'message' => 'Cannot delete the admin account']);
                exit;
            }

            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$data->id]);
            echo json_encode(['success' => true, 'message' => 'User deleted successfully']);
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Failed to delete user: ' . $e->getMessage()]);
        }
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
}
?>
