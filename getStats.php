<?php
$servername = "localhost";
$db_username = "PRFS";
$db_password = "1111";
$dbname = "prfs";

$conn = new mysqli($servername, $db_username, $db_password, $dbname);
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(["error" => "Database connection failed"]);
    exit;
}

$response = [];

$passenger_sql = "SELECT SUM(current_capacity) AS total_passengers FROM ferries";
$passenger_result = $conn->query($passenger_sql);
$response['total_passengers'] = $passenger_result->fetch_assoc()['total_passengers'] ?? 0;

// Changed from passenger_id_pass to users table
$passes_sql = "SELECT COUNT(*) AS active_passes FROM users WHERE is_active = 1 AND expires_at > NOW()";
$passes_result = $conn->query($passes_sql);
$response['active_passes'] = $passes_result->fetch_assoc()['active_passes'] ?? 0;

$ferry_sql = "SELECT COUNT(*) AS active_ferries FROM ferries WHERE status = 'active'";
$ferry_result = $conn->query($ferry_sql);
$response['active_ferries'] = $ferry_result->fetch_assoc()['active_ferries'] ?? 0;

$occupancy_sql = "SELECT AVG(current_capacity / max_capacity) AS avg_occupancy FROM ferries WHERE max_capacity > 0";
$occupancy_result = $conn->query($occupancy_sql);
$avg_occupancy = $occupancy_result->fetch_assoc()['avg_occupancy'] ?? 0;
$response['occupancy_percentage'] = round($avg_occupancy * 100, 1);

$conn->close();

header('Content-Type: application/json');
echo json_encode($response);