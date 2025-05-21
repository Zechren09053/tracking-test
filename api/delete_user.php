<?php
// This script deletes a user
header('Content-Type: application/json');
require_once 'db_connect.php'; // Use your database connection file

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get and validate user input
    $user_id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
    
    // Validation
    if (!$user_id) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Invalid user ID'
        ]);
        exit;
    }
    
    // Get user profile image before deletion
    $get_image_sql = "SELECT profile_image FROM users WHERE id = ?";
    $stmt = $conn->prepare($get_image_sql);
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
    
    $user_data = $result->fetch_assoc();
    $profile_image = $user_data['profile_image'];
    
    // Delete user
    $delete_sql = "DELETE FROM users WHERE id = ?";
    $stmt = $conn->prepare($delete_sql);
    $stmt->bind_param('i', $user_id);
    
    if ($stmt->execute()) {
        // Delete profile image if it exists and is not the default image
        if (!empty($profile_image) && $profile_image !== 'uploads/default.png' && file_exists('../' . $profile_image)) {
            unlink('../' . $profile_image);
        }
        
        echo json_encode([
            'status' => 'success',
            'message' => 'User deleted successfully'
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Failed to delete user: ' . $stmt->error
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