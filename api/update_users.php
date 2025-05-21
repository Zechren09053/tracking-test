<?php
// This script updates an existing user
header('Content-Type: application/json');
require_once 'db_connect.php'; // Use your database connection file

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get and sanitize user input
    $user_id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
    $full_name = filter_input(INPUT_POST, 'full_name', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $phone_number = filter_input(INPUT_POST, 'phone_number', FILTER_SANITIZE_STRING);
    $birth_date = filter_input(INPUT_POST, 'birth_date', FILTER_SANITIZE_STRING);
    $expires_at = filter_input(INPUT_POST, 'expires_at', FILTER_SANITIZE_STRING);
    $is_active = filter_input(INPUT_POST, 'is_active', FILTER_VALIDATE_INT);
    
    // Validation
    if (!$user_id || empty($full_name) || empty($email) || empty($phone_number) || 
        empty($birth_date) || empty($expires_at)) {
        echo json_encode([
            'status' => 'error',
            'message' => 'All required fields must be filled'
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
    
    // Check if email already exists for a different user
    $check_email = "SELECT id FROM users WHERE email = ? AND id != ?";
    $stmt = $conn->prepare($check_email);
    $stmt->bind_param('si', $email, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        echo json_encode([
            'status' => 'error',
            'field' => 'email',
            'message' => 'Email already exists'
        ]);
        exit;
    }
    
    // Handle profile image upload
    $profile_image = null;
    $update_image = false;
    
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === 0) {
        $upload_dir = '../uploads/';
        
        // Create upload directory if it doesn't exist
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        // Generate unique filename
        $file_extension = pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION);
        $file_name = uniqid('user_') . '.' . $file_extension;
        $upload_path = $upload_dir . $file_name;
        
        // Check file type
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
        if (!in_array(strtolower($file_extension), $allowed_types)) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Only JPG, JPEG, PNG, and GIF files are allowed'
            ]);
            exit;
        }
        
        // Move uploaded file
        if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $upload_path)) {
            $profile_image = 'uploads/' . $file_name;
            $update_image = true;
            
            // Get old image path to delete after successful update
            $get_old_image = "SELECT profile_image FROM users WHERE id = ?";
            $stmt = $conn->prepare($get_old_image);
            $stmt->bind_param('i', $user_id);
            $stmt->execute();
            $old_image_result = $stmt->get_result();
            $old_image = $old_image_result->fetch_assoc()['profile_image'];
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Failed to upload image'
            ]);
            exit;
        }
    }
    
    // Start building the UPDATE query
    $sql_parts = [];
    $params = [];
    $types = '';
    
    // Base fields to update
    $sql_parts[] = "full_name = ?";
    $sql_parts[] = "birth_date = ?";
    $sql_parts[] = "email = ?";
    $sql_parts[] = "phone_number = ?";
    $sql_parts[] = "expires_at = ?";
    $sql_parts[] = "is_active = ?";
    
    $params[] = $full_name;
    $params[] = $birth_date;
    $params[] = $email;
    $params[] = $phone_number;
    $params[] = $expires_at;
    $params[] = $is_active;
    $types .= 'sssssi';
    
    // Add profile image if updated
    if ($update_image) {
        $sql_parts[] = "profile_image = ?";
        $params[] = $profile_image;
        $types .= 's';
    }
    
    // Add password if provided
    if (!empty($_POST['password'])) {
        $password = $_POST['password'];
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $sql_parts[] = "password = ?";
        $params[] = $hashed_password;
        $types .= 's';
    }
    
    // Finalize the query
    $sql = "UPDATE users SET " . implode(", ", $sql_parts) . " WHERE id = ?";
    $params[] = $user_id;
    $types .= 'i';
    
    // Prepare and execute the query
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    
    if ($stmt->execute()) {
        // Delete old image if a new one was uploaded
        if ($update_image && !empty($old_image) && $old_image !== 'uploads/default.png' && file_exists('../' . $old_image)) {
            unlink('../' . $old_image);
        }
        
        echo json_encode([
            'status' => 'success',
            'message' => 'User updated successfully'
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Failed to update user: ' . $stmt->error
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