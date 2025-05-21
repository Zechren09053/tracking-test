<?php
header('Content-Type: application/json');

// Database connection
$servername = "localhost";
$db_username = "PRFS";
$db_password = "1111";
$dbname = "prfs";

$conn = new mysqli($servername, $db_username, $db_password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$data = json_decode(file_get_contents("php://input"), true);

$id = $data['id'];
$latitude = $data['latitude'];
$longitude = $data['longitude'];
$speed = $data['speed'];

$sql = "UPDATE ferries SET latitude = ?, longitude = ?, speed = ?, last_updated = NOW() WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("dddi", $latitude, $longitude, $speed, $id);

if ($stmt->execute()) {
    echo json_encode(["status" => "success"]);
} else {
    echo json_encode(["status" => "error"]);
}

$stmt->close();
$conn->close();
?>
