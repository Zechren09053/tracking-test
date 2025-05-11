<?php
header('Content-Type: application/json');
require_once 'db_connection.php'; // make sure this file sets up your $conn or $pdo

$period = isset($_GET['period']) ? $_GET['period'] : 'month';

$passengerData = [];
$ticketData = [];

if ($period === 'month') {
    $query = "SELECT MONTHNAME(date) as label, 
                     SUM(passengers) as passengers, 
                     SUM(tickets_sold) as tickets 
              FROM trip_data 
              GROUP BY MONTH(date)
              ORDER BY MONTH(date)";
} elseif ($period === 'week') {
    $query = "SELECT CONCAT('Week ', WEEK(date) - WEEK(DATE_SUB(date, INTERVAL DAY(date)-1 DAY)) + 1) as label,
                     SUM(passengers) as passengers, 
                     SUM(tickets_sold) as tickets 
              FROM trip_data 
              WHERE date >= CURDATE() - INTERVAL 1 MONTH
              GROUP BY WEEK(date)
              ORDER BY WEEK(date)";
} elseif ($period === 'day') {
    $query = "SELECT DATE_FORMAT(date, '%a') as label,
                     SUM(passengers) as passengers, 
                     SUM(tickets_sold) as tickets 
              FROM trip_data 
              WHERE WEEK(date) = WEEK(CURDATE())
              GROUP BY DAYOFWEEK(date)
              ORDER BY DAYOFWEEK(date)";
} else {
    echo json_encode(['error' => 'Invalid period']);
    exit;
}

$result = $conn->query($query);

$passengerLabels = [];
$passengerValues = [];
$ticketLabels = [];
$ticketValues = [];

while ($row = $result->fetch_assoc()) {
    $passengerLabels[] = $row['label'];
    $passengerValues[] = (int)$row['passengers'];
    $ticketLabels[] = $row['label'];
    $ticketValues[] = (int)$row['tickets'];
}

echo json_encode([
    'passengers' => [
        'labels' => $passengerLabels,
        'data' => $passengerValues
    ],
    'tickets' => [
        'labels' => $ticketLabels,
        'data' => $ticketValues
    ]
]);
?>