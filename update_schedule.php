<?php
$servername = "localhost";
$db_username = "PRFS";
$db_password = "1111";
$dbname = "prfs";

// Create DB connection
$conn = new mysqli($servername, $db_username, $db_password, $dbname);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

// Get the posted data
$row = $_POST['row'];
$col = $_POST['col'];
$schedule_time = $_POST['schedule_time'];
$route = $_POST['route']; // upstream or downstream

// Update the schedule
$table = $route === 'upstream' ? 'upstream_schedules' : 'downstream_schedules';
$sql = "UPDATE $table SET schedule_time = ? WHERE row_id = ? AND col_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sii", $schedule_time, $row, $col);
$stmt->execute();

if ($stmt->affected_rows > 0) {
    echo 'success';
} else {
    echo 'failure';
}

$stmt->close();
$conn->close();
?>
