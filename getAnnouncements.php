<?php
// Create getAnnouncements.php to fetch events for the calendar

// First, create a new file called getAnnouncements.php with this content:
/**
 * File: getAnnouncements.php
 * Purpose: Fetches active announcements for the calendar
 */

session_start();
$servername = "localhost";
$db_username = "PRFS";
$db_password = "1111";
$dbname = "prfs";

$conn = new mysqli($servername, $db_username, $db_password, $dbname);
if ($conn->connect_error) {
    die(json_encode(['error' => "Connection failed: " . $conn->connect_error]));
}

// Get the month and year from request (default to current month/year)
$month = isset($_GET['month']) ? intval($_GET['month']) : date('n');
$year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');

// Calculate the first and last day of the selected month
$firstDayOfMonth = date('Y-m-01', strtotime("$year-$month-01"));
$lastDayOfMonth = date('Y-m-t', strtotime("$year-$month-01"));

// Query to get announcements that are active in the selected month
$sql = "SELECT id, title, message, created_at, display_from, display_duration 
        FROM announcements 
        WHERE 
        (display_from BETWEEN ? AND ?) OR 
        (DATE_ADD(display_from, INTERVAL display_duration DAY) BETWEEN ? AND ?) OR
        (display_from <= ? AND DATE_ADD(display_from, INTERVAL display_duration DAY) >= ?)";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ssssss", $firstDayOfMonth, $lastDayOfMonth, $firstDayOfMonth, $lastDayOfMonth, $firstDayOfMonth, $lastDayOfMonth);
$stmt->execute();
$result = $stmt->get_result();

$announcements = [];
while ($row = $result->fetch_assoc()) {
    // Calculate end date
    $endDate = date('Y-m-d', strtotime($row['display_from'] . ' + ' . $row['display_duration'] . ' days'));
    $row['end_date'] = $endDate;
    $announcements[] = $row;
}

$stmt->close();
$conn->close();

header('Content-Type: application/json');
echo json_encode($announcements);
?>