<?php
$input = json_decode(file_get_contents("php://input"), true);

$conn = new mysqli("localhost", "PRFS", "1111", "prfs");
if ($conn->connect_error) {
    echo json_encode(["message" => "Database connection failed."]);
    exit;
}

$stmt = $conn->prepare("UPDATE ferry_routes SET route_name = ?, origin_station_id = ?, destination_station_id = ?, departure_time = ?, arrival_time = ? WHERE route_id = ?");
$stmt->bind_param("sssssi", 
    $input['route_name'],
    $input['origin_station_id'],
    $input['destination_station_id'],
    $input['departure_time'],
    $input['arrival_time'],
    $input['route_id']
);

if ($stmt->execute()) {
    echo json_encode(["message" => "Route updated successfully."]);
} else {
    echo json_encode(["message" => "Failed to update route."]);
}

$stmt->close();
$conn->close();
?>
