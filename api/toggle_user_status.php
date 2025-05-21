<?php
// This script toggles user active status
header('Content-Type: application/json');
require_once 'db_connect.php'; // Use your database connection file

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get and validate user input
    $user_id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
    $is_active = filter_input(INPUT_POST, 'is_active', FILTER_VALIDATE_INT);
    
    // Validation
    if (!$user_id || ($is_active !== 0 && $is_active !== 1)) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Invalid parameters'
        ]);
        exit;
    }
    
    // Check if user exists
    $check_user = "SELECT id FROM users WHERE id = ?";
    $stmt = $conn->prepare($check_user);
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode([
            'status' => 'error',
            'message' => 'User not found'
        ]);
        exit;
    }
    
    // Update user active status
    $sql = "UPDATE users SET is_active = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ii', $is_active, $user_id);
    
    if ($stmt->execute()) {
        echo json_encode([
            'status' => 'success',
            'message' => 'User status updated successfully'
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Failed to update user status: ' . $stmt->error
        ]);
    }
    
    $stmt->close();
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid request method'
    ]);
}

$conn->close();