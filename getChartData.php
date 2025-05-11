<?php
header('Content-Type: application/json');
require_once 'db_connect.php';

$period = isset($_GET['period']) ? $_GET['period'] : 'month';

$passengerData = [];
$ticketData = [];

if ($period === 'month') {
    $passengerQuery = "SELECT MONTHNAME(trip_date) AS label, SUM(passenger_count) AS total
                       FROM ferry_logs
                       GROUP BY MONTH(trip_date)
                       ORDER BY MONTH(trip_date)";
    $ticketQuery = "SELECT MONTHNAME(purchase_date) AS label, COUNT(*) AS total
                    FROM tickets
                    GROUP BY MONTH(purchase_date)
                    ORDER BY MONTH(purchase_date)";
} elseif ($period === 'week') {
    $passengerQuery = "SELECT CONCAT('Week ', WEEK(trip_date)) AS label, SUM(passenger_count) AS total
                       FROM ferry_logs
                       WHERE trip_date >= CURDATE() - INTERVAL 1 MONTH
                       GROUP BY WEEK(trip_date)
                       ORDER BY WEEK(trip_date)";
    $ticketQuery = "SELECT CONCAT('Week ', WEEK(purchase_date)) AS label, COUNT(*) AS total
                    FROM tickets
                    WHERE purchase_date >= CURDATE() - INTERVAL 1 MONTH
                    GROUP BY WEEK(purchase_date)
                    ORDER BY WEEK(purchase_date)";
} elseif ($period === 'day') {
    $passengerQuery = "SELECT DATE_FORMAT(trip_date, '%a') AS label, SUM(passenger_count) AS total
                       FROM ferry_logs
                       WHERE WEEK(trip_date) = WEEK(CURDATE())
                       GROUP BY DAYOFWEEK(trip_date)
                       ORDER BY DAYOFWEEK(trip_date)";
    $ticketQuery = "SELECT DATE_FORMAT(purchase_date, '%a') AS label, COUNT(*) AS total
                    FROM tickets
                    WHERE WEEK(purchase_date) = WEEK(CURDATE())
                    GROUP BY DAYOFWEEK(purchase_date)
                    ORDER BY DAYOFWEEK(purchase_date)";
} else {
    echo json_encode(['error' => 'Invalid period']);
    exit;
}

// Fetch and process passenger data
$passengerLabels = [];
$passengerValues = [];
$ticketLabels = [];
$ticketValues = [];

$passengerResult = $conn->query($passengerQuery);
while ($row = $passengerResult->fetch_assoc()) {
    $passengerLabels[] = $row['label'];
    $passengerValues[] = (int)$row['total'];
}

$ticketResult = $conn->query($ticketQuery);
while ($row = $ticketResult->fetch_assoc()) {
    $ticketLabels[] = $row['label'];
    $ticketValues[] = (int)$row['total'];
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
