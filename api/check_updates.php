<?php
// check_updates.php
header('Content-Type: application/json');
require_once 'db_connect.php';

// Get parameters
$lastUpdate = isset($_GET['lastUpdate']) ? (int)$_GET['lastUpdate'] : 0;
$search = isset($_GET['search']) ? $_GET['search'] : '';
$status = isset($_GET['status']) ? $_GET['status'] : 'all';

// Convert timestamp to MySQL format
$lastUpdateTime = date('Y-m-d H:i:s', $lastUpdate/1000);

try {
    $query = "SELECT MAX(last_modified) as latest_update FROM users";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $latestUpdate = strtotime($result['latest_update']) * 1000;
    
    // Check if there are any updates since last check
    $hasUpdates = $latestUpdate > $lastUpdate;
    
    echo json_encode([
        'status' => 'success',
        'hasUpdates' => $hasUpdates,
        'latestUpdate' => $latestUpdate
    ]);
} catch (PDOException $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>