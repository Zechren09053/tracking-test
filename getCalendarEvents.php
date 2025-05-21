<?php
header('Content-Type: application/json');
$conn = new mysqli("localhost", "PRFS", "1111", "prfs");
if ($conn->connect_error) {
    die(json_encode([]));
}

$sql = "SELECT ferry_name, departure_time, arrival_time FROM ferry_schedules"; // Adjust to your table
$result = $conn->query($sql);
$events = [];

while ($row = $result->fetch_assoc()) {
    $events[] = [
        'title' => $row['ferry_name'],
        'start' => $row['departure_time'],
        'end' => $row['arrival_time']
    ];
}

echo json_encode($events);
$conn->close();
?>
