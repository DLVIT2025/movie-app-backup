<?php
/**
 * Admin Bookings Endpoint
 * GET    — list all bookings
 * DELETE — delete a booking by id
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
            $rows = $pdo->query("SELECT id, booking_id, user_email, movie_title, showtime, theatre, seats, grand_total, booking_date FROM bookings ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['success' => true, 'bookings' => $rows]);
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        break;

    case 'DELETE':
        $data = json_decode(file_get_contents("php://input"));
        if (!$data || empty($data->id)) {
            echo json_encode(['success' => false, 'message' => 'Booking ID is required']);
            exit;
        }

        try {
            $stmt = $pdo->prepare("DELETE FROM bookings WHERE id = ?");
            $stmt->execute([$data->id]);
            echo json_encode(['success' => true, 'message' => 'Booking deleted successfully']);
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Failed to delete booking: ' . $e->getMessage()]);
        }
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
}
?>
