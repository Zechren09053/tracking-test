<?php
// Enable error reporting for debugging (remove in production)
//error_reporting(E_ALL);
//ini_set('display_errors', 1);

// Start session
session_start();

// Database connection
require_once 'db_connect.php';

// Check if the request is a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Get data from the request
$data = json_decode(file_get_contents('php://input'), true);

// Validate required fields
if (!isset($data['username']) || !isset($data['password'])) {
    echo json_encode(['success' => false, 'message' => 'Username and password are required']);
    exit;
}

$username = trim($data['username']);
$password = $data['password'];

// Determine if username is email or phone number
$is_email = filter_var($username, FILTER_VALIDATE_EMAIL);

try {
    if ($is_email) {
        // Login with email
        $stmt = $conn->prepare("SELECT id, full_name, password FROM users WHERE email = ?");
    } else {
        // Login with phone number
        $stmt = $conn->prepare("SELECT id, full_name, password FROM users WHERE phone_number = ?");
    }
    
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
        // Verify password
        if (password_verify($password, $user['password'])) {
            // Set session variables (including the one needed for online tracking)
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['logged_in'] = true;
            $_SESSION['logged_in_user_id'] = $user['id']; // Added for online tracking consistency
            
            // Update login tracking and activity timestamps
            $update_stmt = $conn->prepare("UPDATE users SET 
                                          last_used = NOW(), 
                                          login_count = login_count + 1, 
                                          last_login = NOW(),
                                          last_activity = NOW() 
                                          WHERE id = ?");
            $update_stmt->bind_param("i", $user['id']);
            $update_stmt->execute();
            $update_stmt->close();
            
            // Return success response
            echo json_encode(['success' => true, 'message' => 'Login successful', 'user_id' => $user['id']]);
        } else {
            // Password is incorrect
            echo json_encode(['success' => false, 'message' => 'Invalid credentials']);
        }
    } else {
        // User not found
        echo json_encode(['success' => false, 'message' => 'Invalid credentials']);
    }
    
    // Close statement
    $stmt->close();
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()]);
}

// Close database connection
$conn->close();
?>