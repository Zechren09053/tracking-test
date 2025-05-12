<?php
session_start();
require 'db_connect.php';

// Ensure only logged-in operators can access this
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'operator') {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access']);
    exit();
}

// Validate inputs
if (!isset($_POST['ferry_id'], $_POST['latitude'], $_POST['longitude'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Missing required parameters']);
    exit();
}

$ferry_id = intval($_POST['ferry_id']);
$latitude = floatval($_POST['latitude']);
$longitude = floatval($_POST['longitude']);
$speed = isset($_POST['speed']) ? floatval($_POST['speed']) : 0;

// Verify the ferry is actually assigned to this operator
$stmt = $conn->prepare("
    SELECT f.id 
    FROM ferry_crew fc
    JOIN ferries f ON fc.ferry_id = f.id
    WHERE fc.staff_id = ? AND fc.ferry_id = ? AND fc.is_active = 1
");
$stmt->bind_param("ii", $_SESSION['staff_id'], $ferry_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Ferry not assigned to you']);
    exit();
}

// Update ferry location
$update_stmt = $conn->prepare("
    UPDATE ferries 
    SET latitude = ?, longitude = ?, speed = ?, last_updated = NOW() 
    WHERE id = ?
");
$update_stmt->bind_param("dddi", $latitude, $longitude, $speed, $ferry_id);

// Log location
$log_stmt = $conn->prepare("
    INSERT INTO ferry_logs 
    (ferry_id, latitude, longitude, speed, trip_date) 
    VALUES (?, ?, ?, ?, NOW())
");
$log_stmt->bind_param("iddd", $ferry_id, $latitude, $longitude, $speed);

try {
    $conn->begin_transaction();
    
    $update_result = $update_stmt->execute();
    $log_result = $log_stmt->execute();
    
    if ($update_result && $log_result) {
        $conn->commit();
        echo json_encode(['status' => 'success', 'message' => 'Location updated']);
    } else {
        $conn->rollback();
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Failed to update location']);
    }
} catch (Exception $e) {
    $conn->rollback();
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Database error']);
}

$stmt->close();
$update_stmt->close();
$log_stmt->close();
$conn->close();