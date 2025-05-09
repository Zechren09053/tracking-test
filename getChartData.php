<?php
$mysqli = new mysqli("localhost", "PRFS", "1111", "prfs");
header('Content-Type: application/json');

// Fetch passenger growth
$passengerData = [];
$passenger_query = "SELECT MONTH(trip_date) AS month, YEAR(trip_date) AS year, SUM(passenger_count) AS total_passengers FROM ferry_logs GROUP BY YEAR(trip_date), MONTH(trip_date) ORDER BY year, month";
$res = $mysqli->query($passenger_query);
while ($row = $res->fetch_assoc()) {
    $passengerData[] = [
        'month' => "{$row['year']}-{$row['month']}",
        'passengers' => (int)$row['total_passengers']
    ];
}

// Fetch ticket sales
$ticketData = [];
$ticket_query = "SELECT MONTH(purchase_date) AS month, YEAR(purchase_date) AS year, COUNT(*) AS total_tickets FROM tickets GROUP BY YEAR(purchase_date), MONTH(purchase_date) ORDER BY year, month";
$res2 = $mysqli->query($ticket_query);
while ($row2 = $res2->fetch_assoc()) {
    $ticketData[] = [
        'month' => "{$row2['year']}-{$row2['month']}",
        'tickets' => (int)$row2['total_tickets']
    ];
}

echo json_encode([
    'passengers' => $passengerData,
    'tickets' => $ticketData
]);
