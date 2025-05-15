<?php
// Enable error reporting for debugging (remove in production)
//error_reporting(E_ALL);
//ini_set('display_errors', 1);

// Start session
session_start();

// Database connection
require_once 'db_connect.php';

// Get phone from request
$phone = isset($_GET['phone']) ? trim($_GET['phone']) : '';

// Validate phone (basic validation)
if (empty($phone)) {
    echo json_encode(['exists' => false, 'message' => 'Phone number is required']);
    exit;
}

try {
    // Check if phone exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE phone_number = ?");
    $stmt->bind_param("s", $phone);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        echo json_encode(['exists' => true]);
    } else {
        echo json_encode(['exists' => false]);
    }
    
    // Close statement
    $stmt->close();
} catch (Exception $e) {
    echo json_encode(['error' => 'An error occurred: ' . $e->getMessage()]);
}

// Close database connection
$conn->close();
?>