<?php
// Include database connection
require 'db_connect.php';

// Get ferry ID from request
$ferryId = $_GET['ferry_id'] ?? null;

// Check if ferry ID is provided
if (!$ferryId) {
    echo json_encode(['error' => 'Ferry ID not provided']);
    exit;
}

// Fetch maintenance records
$sql = "SELECT * FROM boat_maintenance 
        WHERE ferry_id = ? 
        ORDER BY maintenance_date DESC 
        LIMIT 10";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $ferryId);
$stmt->execute();
$result = $stmt->get_result();

$maintenanceData = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $maintenanceData[] = $row;
    }
}

echo json_encode($maintenanceData);

$stmt->close();
$conn->close();
?>