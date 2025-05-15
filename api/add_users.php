<?php
// This script adds a new user
header('Content-Type: application/json');
require_once 'db_connect.php'; // Use your database connection file

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get and sanitize user input
    $full_name = filter_input(INPUT_POST, 'full_name', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $phone_number = filter_input(INPUT_POST, 'phone_number', FILTER_SANITIZE_STRING);
    $birth_date = filter_input(INPUT_POST, 'birth_date', FILTER_SANITIZE_STRING);
    $expires_at = filter_input(INPUT_POST, 'expires_at', FILTER_SANITIZE_STRING);
    $is_active = filter_input(INPUT_POST, 'is_active', FILTER_VALIDATE_INT);
    $password = $_POST['password']; // Will be hashed, so no sanitization
    
    // Validation
    if (empty($full_name) || empty($email) || empty($phone_number) || empty($birth_date) || 
        empty($expires_at) || empty($password)) {
        echo json_encode([
            'status' => 'error',
            'message' => 'All required fields must be filled'
        ]);
        exit;
    }
    
    // Check if email already exists
    $check_email = "SELECT id FROM users WHERE email = ?";
    $stmt = $conn->prepare($check_email);
    $stmt->bind_param('s', $email);
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
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Failed to upload image'
            ]);
            exit;
        }
    }
    
    // Generate QR code data (unique identifier)
    $qr_code_data = 'PRFS_' . time() . '_' . uniqid();
    
    // Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    // Prepare SQL and bind parameters
    $sql = "INSERT INTO users (full_name, birth_date, profile_image, email, phone_number, password, 
            qr_code_data, expires_at, is_active) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ssssssssi', 
        $full_name, 
        $birth_date, 
        $profile_image,
        $email,
        $phone_number,
        $hashed_password,
        $qr_code_data,
        $expires_at,
        $is_active
    );
    
    // Execute the query
    if ($stmt->execute()) {
        $user_id = $stmt->insert_id;
        echo json_encode([
            'status' => 'success',
            'message' => 'User added successfully',
            'user_id' => $user_id
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Failed to add user: ' . $stmt->error
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