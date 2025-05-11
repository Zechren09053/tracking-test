
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

// Fetch ferry details
$sql = "SELECT * FROM ferries WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $ferryId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $ferryData = $result->fetch_assoc();
    echo json_encode($ferryData);
} else {
    echo json_encode(['error' => 'Ferry not found']);
}

$stmt->close();
$conn->close();
?>
