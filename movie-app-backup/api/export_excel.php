<?php
/**
 * Export Bookings + Users as a multi-sheet Excel file (XML Spreadsheet 2003).
 * No external libraries needed — Excel, LibreOffice, and Google Sheets all open this format.
 * 
 * Usage: Open http://localhost/movie-app/api/export_excel.php in your browser.
 */

require_once 'db_connect.php';

// --- Fetch data from DB ---
$bookings = $pdo->query("SELECT booking_id, movie_title, user_email, seats, booking_date, showtime, grand_total FROM bookings ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
$users = $pdo->query("SELECT id, name, email FROM users ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);

// --- Output Excel XML ---
$filename = 'CineTicket_Data_' . date('Y-m-d') . '.xls';
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: max-age=0');

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<?mso-application progid="Excel.Sheet"?>' . "\n";
?>
<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet"
 xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet">

 <Styles>
  <Style ss:ID="Header">
   <Font ss:Color="#FFFFFF" ss:Bold="1" ss:Size="11"/>
   <Interior ss:Color="#E50914" ss:Pattern="Solid"/>
  </Style>
  <Style ss:ID="Default"/>
 </Styles>

 <!-- ========== SHEET 1: Bookings ========== -->
 <Worksheet ss:Name="Bookings">
  <Table>
   <Column ss:Width="100"/>
   <Column ss:Width="150"/>
   <Column ss:Width="180"/>
   <Column ss:Width="100"/>
   <Column ss:Width="100"/>
   <Column ss:Width="80"/>
   <Column ss:Width="80"/>
   <Row ss:StyleID="Header">
    <Cell><Data ss:Type="String">Booking ID</Data></Cell>
    <Cell><Data ss:Type="String">Movie Name</Data></Cell>
    <Cell><Data ss:Type="String">User Email</Data></Cell>
    <Cell><Data ss:Type="String">Seats</Data></Cell>
    <Cell><Data ss:Type="String">Date</Data></Cell>
    <Cell><Data ss:Type="String">Time</Data></Cell>
    <Cell><Data ss:Type="String">Total Price</Data></Cell>
   </Row>
<?php foreach ($bookings as $b): ?>
   <Row>
    <Cell><Data ss:Type="String"><?= htmlspecialchars($b['booking_id']) ?></Data></Cell>
    <Cell><Data ss:Type="String"><?= htmlspecialchars($b['movie_title']) ?></Data></Cell>
    <Cell><Data ss:Type="String"><?= htmlspecialchars($b['user_email']) ?></Data></Cell>
    <Cell><Data ss:Type="String"><?= htmlspecialchars($b['seats']) ?></Data></Cell>
    <Cell><Data ss:Type="String"><?= htmlspecialchars($b['booking_date']) ?></Data></Cell>
    <Cell><Data ss:Type="String"><?= htmlspecialchars($b['showtime']) ?></Data></Cell>
    <Cell><Data ss:Type="Number"><?= (int)$b['grand_total'] ?></Data></Cell>
   </Row>
<?php endforeach; ?>
  </Table>
 </Worksheet>

 <!-- ========== SHEET 2: Users ========== -->
 <Worksheet ss:Name="Users">
  <Table>
   <Column ss:Width="80"/>
   <Column ss:Width="150"/>
   <Column ss:Width="200"/>
   <Row ss:StyleID="Header">
    <Cell><Data ss:Type="String">ID</Data></Cell>
    <Cell><Data ss:Type="String">Name</Data></Cell>
    <Cell><Data ss:Type="String">Email</Data></Cell>
   </Row>
<?php foreach ($users as $u): ?>
   <Row>
    <Cell><Data ss:Type="Number"><?= (int)$u['id'] ?></Data></Cell>
    <Cell><Data ss:Type="String"><?= htmlspecialchars($u['name']) ?></Data></Cell>
    <Cell><Data ss:Type="String"><?= htmlspecialchars($u['email']) ?></Data></Cell>
   </Row>
<?php endforeach; ?>
  </Table>
 </Worksheet>

</Workbook>
