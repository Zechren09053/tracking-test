<?php
// Enable error reporting for debugging (remove in production)
//error_reporting(E_ALL);
//ini_set('display_errors', 1);

// Start session
session_start();

// Database connection
require_once 'db_connect.php';

// Get user ID from request
$user_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// If user ID is not provided and user is logged in, get ID from session
if ($user_id === 0 && isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
}

// Validate user ID
if ($user_id <= 0) {
    echo json_encode(['error' => 'Invalid user ID']);
    exit;
}

try {
    // Get user data
    $stmt = $conn->prepare("SELECT id, full_name, email, phone_number, birth_date, profile_image, qr_code_data, issued_at, expires_at, is_active, last_used FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
        // Convert birth_date, issued_at, and expires_at to proper format if needed
        if ($user['birth_date']) {
            $user['birth_date'] = date('Y-m-d', strtotime($user['birth_date']));
        }
        
        if ($user['issued_at']) {
            $user['issued_at'] = date('Y-m-d H:i:s', strtotime($user['issued_at']));
        }
        
        if ($user['expires_at']) {
            $user['expires_at'] = date('Y-m-d', strtotime($user['expires_at']));
        }
        
        // Return user data
        echo json_encode($user);
    } else {
        // User not found
        echo json_encode(['error' => 'User not found']);
    }
    
    // Close statement
    $stmt->close();
} catch (Exception $e) {
    echo json_encode(['error' => 'An error occurred: ' . $e->getMessage()]);
}

// Close database connection
$conn->close();
?>