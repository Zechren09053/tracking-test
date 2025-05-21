<?php
session_start();
require 'db_connect.php';

// Ensure only logged-in operators can access this
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'operator') {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access']);
    exit();
}

// Fetch ferries assigned to the current staff member
$stmt = $conn->prepare("
    SELECT f.id, f.name, f.ferry_code, f.ferry_type, f.status 
    FROM ferry_crew fc
    JOIN ferries f ON fc.ferry_id = f.id
    WHERE fc.staff_id = ? AND fc.is_active = 1
");
$stmt->bind_param("i", $_SESSION['staff_id']);
$stmt->execute();
$result = $stmt->get_result();

$assigned_ferries = [];
while ($row = $result->fetch_assoc()) {
    $assigned_ferries[] = $row;
}

echo json_encode([
    'status' => 'success',
    'ferries' => $assigned_ferries
]);

$stmt->close();
$conn->close();