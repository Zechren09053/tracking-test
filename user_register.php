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

// Collect and sanitize form data
$full_name = isset($_POST['full_name']) ? trim($_POST['full_name']) : '';
$birth_date = isset($_POST['birth_date']) ? trim($_POST['birth_date']) : '';
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$phone_number = isset($_POST['phone_number']) ? trim($_POST['phone_number']) : '';
$password = isset($_POST['password']) ? $_POST['password'] : '';
$qr_code_data = isset($_POST['qr_code_data']) ? trim($_POST['qr_code_data']) : '';
$expires_at = isset($_POST['expires_at']) ? trim($_POST['expires_at']) : '';

// Initialize errors array
$errors = [];

// Validate form data
if (empty($full_name)) {
    $errors['name'] = 'Full name is required';
}

if (empty($birth_date)) {
    $errors['birth-date'] = 'Date of birth is required';
} else {
    // Check if user is at least 13 years old
    $today = new DateTime();
    $birth_date_obj = new DateTime($birth_date);
    $age = $today->diff($birth_date_obj)->y;
    
    if ($age < 13) {
        $errors['birth-date'] = 'You must be at least 13 years old';
    }
}

if (empty($email)) {
    $errors['email'] = 'Email is required';
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors['email'] = 'Please enter a valid email address';
} else {
    // Check if email already exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $errors['email'] = 'This email is already registered';
    }
    
    $stmt->close();
}

if (empty($phone_number)) {
    $errors['phone'] = 'Phone number is required';
} elseif (!preg_match('/^\+?[0-9]{6,15}$/', $phone_number)) {
    $errors['phone'] = 'Please enter a valid phone number';
} else {
    // Check if phone number already exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE phone_number = ?");
    $stmt->bind_param("s", $phone_number);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $errors['phone'] = 'This phone number is already registered';
    }
    
    $stmt->close();
}

if (empty($password)) {
    $errors['password'] = 'Password is required';
} elseif (strlen($password) < 8) {
    $errors['password'] = 'Password must be at least 8 characters';
}

if (empty($qr_code_data)) {
    $qr_code_data = 'TIX-' . bin2hex(random_bytes(16)); // Generate random QR code data if not provided
}

if (empty($expires_at)) {
    // Set expiration to 1 year from now by default
    $expires_at = date('Y-m-d', strtotime('+1 year'));
}

// Process profile image if uploaded
$profile_image_path = null;
if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] == 0) {
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
    $max_size = 2 * 1024 * 1024; // 2MB
    
    if (!in_array($_FILES['profile_image']['type'], $allowed_types)) {
        $errors['image'] = 'Only JPEG, PNG, and GIF images are allowed';
    } elseif ($_FILES['profile_image']['size'] > $max_size) {
        $errors['image'] = 'Image size should not exceed 2MB';
    } else {
        // Create uploads directory if it doesn't exist
        $upload_dir = 'uploads/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        // Generate unique filename
        $filename = uniqid() . '_' . basename($_FILES['profile_image']['name']);
        $target_file = $upload_dir . $filename;
        
        // Move uploaded file
        if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $target_file)) {
            $profile_image_path = $target_file;
        } else {
            $errors['image'] = 'Failed to upload image';
        }
    }
}

// If there are validation errors, return them
if (!empty($errors)) {
    echo json_encode(['success' => false, 'errors' => $errors]);
    exit;
}

// Hash the password for security
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// Current timestamp for issued_at
$issued_at = date('Y-m-d H:i:s');

// Set default values
$is_active = 1; // Active by default
$last_used = null; // Never used yet

try {
    // Begin transaction
    $conn->begin_transaction();
    
    // Insert the new user into the database
    $stmt = $conn->prepare("INSERT INTO users (full_name, birth_date, email, phone_number, password, profile_image, qr_code_data, issued_at, expires_at, is_active, last_used) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssssssss", $full_name, $birth_date, $email, $phone_number, $hashed_password, $profile_image_path, $qr_code_data, $issued_at, $expires_at, $is_active, $last_used);
    
    if ($stmt->execute()) {
        $user_id = $conn->insert_id;
        
        // Commit transaction
        $conn->commit();
        
        // Return success response with user ID
        echo json_encode(['success' => true, 'message' => 'Registration successful', 'user_id' => $user_id]);
    } else {
        // Rollback transaction on error
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => 'Registration failed: ' . $stmt->error]);
    }
    
    // Close statement
    $stmt->close();
} catch (Exception $e) {
    // Rollback transaction on exception
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()]);
}

// Close database connection
$conn->close();
?>