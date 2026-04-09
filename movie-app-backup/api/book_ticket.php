<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); 
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'db_connect.php';

// Get JSON input
$data = json_decode(file_get_contents("php://input"));

if (!$data) {
    echo json_encode(['success' => false, 'message' => 'Invalid booking data']);
    exit;
}

$booking_id = $data->bookingId ?? '';
$user_email = $data->userId ?? ''; // Assuming userId holds email
$movie_title = $data->movieTitle ?? '';
$showtime = $data->showtime ?? '';
$theatre = $data->theatre ?? '';
$seats = implode(', ', $data->seats ?? []);
$grand_total = $data->grandTotal ?? 0;
// Strip any non-numeric characters for int validation if needed, but we save as INT so we cast
$grand_total = (int) preg_replace('/[^0-9]/', '', (string)$grand_total);
$booking_date = $data->date ?? '';

if (empty($booking_id) || empty($user_email)) {
    echo json_encode(['success' => false, 'message' => 'Missing critical user or booking parameters']);
    exit;
}

try {
    // Automatically guarantee the table exists and has the exactly correct columns
    $pdo->exec("CREATE TABLE IF NOT EXISTS bookings (
        id INT AUTO_INCREMENT PRIMARY KEY,
        booking_id VARCHAR(50),
        user_email VARCHAR(150),
        movie_title VARCHAR(150),
        showtime VARCHAR(50),
        theatre VARCHAR(100),
        seats TEXT,
        grand_total INT,
        booking_date VARCHAR(50)
    )");

    $stmt = $pdo->prepare("INSERT INTO bookings (booking_id, user_email, movie_title, showtime, theatre, seats, grand_total, booking_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    
    // Safety check if prepare still fails
    if (!$stmt) {
        echo json_encode(['success' => false, 'message' => 'Failed to prepare query: ' . print_r($pdo->errorInfo(), true)]);
        exit;
    }

    if ($stmt->execute([$booking_id, $user_email, $movie_title, $showtime, $theatre, $seats, $grand_total, $booking_date])) {

        // --- Append to CSV (never breaks DB flow) ---
        try {
            $csvFile = __DIR__ . '/../bookings.csv';
            $writeHeader = !file_exists($csvFile);
            $fp = fopen($csvFile, 'a');
            if ($fp) {
                if ($writeHeader) {
                    fputcsv($fp, ['Booking ID', 'Movie Name', 'User Email', 'Seats', 'Date', 'Time', 'Total Price']);
                }
                fputcsv($fp, [$booking_id, $movie_title, $user_email, $seats, $booking_date, $showtime, $grand_total]);
                fclose($fp);
            }
        } catch (Exception $csvErr) {
            // Silently ignore CSV errors — DB booking is already saved
        }
        // --- End CSV ---

        echo json_encode(['success' => true, 'message' => 'Ticket successfully booked in database!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to save ticket into the database: ' . print_r($stmt->errorInfo(), true)]);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
