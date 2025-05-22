<?php
// This file should be included in your login process

// Assuming this is part of your login.php or similar file
// Make sure you have proper validation before this point

// For staff user login example:
function handleStaffLogin($email, $password) {
    global $conn; // Your database connection
    
    // Sanitize input
    $email = mysqli_real_escape_string($conn, $email);
    
    // Get user from database
    $query = "SELECT staff_id, password_hash, first_name, last_name FROM staff_users WHERE email = '$email'";
    $result = mysqli_query($conn, $query);
    
    if (mysqli_num_rows($result) == 1) {
        $user = mysqli_fetch_assoc($result);
        
        // Verify password (using password_verify if you're using PHP's password_hash)
        if (password_verify($password, $user['password_hash'])) {
            // Start session if not already started
            if (session_status() == PHP_SESSION_NONE) {
                session_start();
            }
            
            // Update login count and time
            $updateQuery = "UPDATE staff_users SET 
                login_count = login_count + 1, 
                last_login = NOW() 
                WHERE staff_id = {$user['staff_id']}";
            mysqli_query($conn, $updateQuery);
            
            // Set session variables
            $_SESSION['logged_in'] = true;
            $_SESSION['logged_in_staff_id'] = $user['staff_id'];
            $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
            $_SESSION['user_type'] = 'staff';
            
            return true;
        }
    }
    
    return false;
}

// For regular user login example:
function handleUserLogin($email, $password) {
    global $conn; // Your database connection
    
    // Sanitize input
    $email = mysqli_real_escape_string($conn, $email);
    
    // Get user from database
    $query = "SELECT id, password_hash, full_name FROM users WHERE email = '$email'";
    $result = mysqli_query($conn, $query);
    
    if (mysqli_num_rows($result) == 1) {
        $user = mysqli_fetch_assoc($result);
        
        // Verify password (using password_verify if you're using PHP's password_hash)
        if (password_verify($password, $user['password_hash'])) {
            // Start session if not already started
            if (session_status() == PHP_SESSION_NONE) {
                session_start();
            }
            
            // Update login count and time
            $updateQuery = "UPDATE users SET 
                login_count = login_count + 1, 
                last_login = NOW() 
                WHERE id = {$user['id']}";
            mysqli_query($conn, $updateQuery);
            
            // Set session variables
            $_SESSION['logged_in'] = true;
            $_SESSION['logged_in_user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['full_name'];
            $_SESSION['user_type'] = 'user';
            
            return true;
        }
    }
    
    return false;
}

// Example of usage on login form processing:
/*
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];
    $user_type = $_POST['user_type']; // 'staff' or 'user'
    
    if ($user_type == 'staff') {
        if (handleStaffLogin($email, $password)) {
            header('Location: dashboard.php');
            exit;
        }
    } else {
        if (handleUserLogin($email, $password)) {
            header('Location: user_dashboard.php');
            exit;
        }
    }
    
    // If we reach here, login failed
    $error = "Invalid email or password";
}
*/
?>