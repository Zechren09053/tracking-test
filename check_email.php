<?php
// Enable error reporting for debugging (remove in production)
//error_reporting(E_ALL);
//ini_set('display_errors', 1);

// Start session
session_start();

// Database connection
require_once 'db_connect.php';

// Get email from request
$email = isset($_GET['email']) ? trim($_GET['email']) : '';

// Validate email
if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['exists' => false, 'message' => 'Invalid email format']);
    exit;
}

try {
    // Check if email exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
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