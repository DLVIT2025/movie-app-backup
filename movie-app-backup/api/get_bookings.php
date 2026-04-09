<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); 
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'db_connect.php';

if (!isset($_GET['email'])) {
    echo json_encode(['success' => false, 'message' => 'Email is required']);
    exit;
}

$email = $_GET['email'];

try {
    $stmt = $pdo->prepare("SELECT * FROM bookings WHERE user_email = ? ORDER BY id DESC");
    $stmt->execute([$email]);
    $bookings = $stmt->fetchAll();

    // Reformat backend rows into the JSON format expected by the frontend UI
    $formatted_bookings = [];
    foreach ($bookings as $row) {
        $formatted_bookings[] = [
            'bookingId' => $row['booking_id'],
            'userId' => $row['user_email'],
            'movieTitle' => $row['movie_title'],
            'showtime' => $row['showtime'],
            'theatre' => $row['theatre'],
            'seats' => explode(', ', $row['seats']),
            'grandTotal' => $row['grand_total'],
            'date' => $row['booking_date'],
            // Add fallback blanks for optional ticket specifics to render nicely
            'seatTypes' => [], 
            'snacks' => []
        ];
    }
    
    echo json_encode(['success' => true, 'bookings' => $formatted_bookings]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
