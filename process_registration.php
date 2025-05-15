<?php
// Process registration and create a new user
require_once "db_connect.php";

// Check if form submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $full_name = $conn->real_escape_string($_POST['full_name']);
    $birth_date = $conn->real_escape_string($_POST['birth_date']);
    $email = $conn->real_escape_string($_POST['email']);
    $phone_number = $conn->real_escape_string($_POST['phone_number']);
    
    // Check if email already exists
    $check_query = "SELECT id FROM users WHERE email = '$email'";
    $result = $conn->query($check_query);
    
    if ($result->num_rows > 0) {
        // Email already exists, redirect back with error
        header("Location: index.html?error=email_exists");
        exit();
    }
    
    // Generate QR code data (unique identifier)
    $qr_code_data = uniqid() . bin2hex(random_bytes(8));
    
    // Set expiration date to 1 year from now
    $expires_at = date('Y-m-d H:i:s', strtotime('+1 year'));
    
    // Handle profile image upload
    $profile_image_path = '';
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] == 0) {
        $upload_dir = 'uploads/';
        
        // Create directory if it doesn't exist
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $file_name = time() . '_' . basename($_FILES['profile_image']['name']);
        $target_file = $upload_dir . $file_name;
        
        // Move uploaded file
        if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $target_file)) {
            $profile_image_path = $target_file;
        }
    }
    
    // Insert new user
    $query = "INSERT INTO users 
              (full_name, birth_date, profile_image, email, phone_number, qr_code_data, expires_at) 
              VALUES 
              ('$full_name', '$birth_date', '$profile_image_path', '$email', '$phone_number', '$qr_code_data', '$expires_at')";
    
    if ($conn->query($query) === TRUE) {
        $user_id = $conn->insert_id;
        // Redirect to success page with user ID
        header("Location: QR.php?registration=$user_id");
        exit();
    } else {
        echo "Error: " . $query . "<br>" . $conn->error;
    }
    
    $conn->close();
}
?>