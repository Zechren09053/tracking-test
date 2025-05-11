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

// Fetch repair records
$sql = "SELECT * FROM repair_logs 
        WHERE ferry_id = ? 
        ORDER BY reported_at DESC 
        LIMIT 10";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $ferryId);
$stmt->execute();
$result = $stmt->get_result();

$repairData = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $repairData[] = $row;
    }
}

echo json_encode($repairData);

$stmt->close();
$conn->close();
?>