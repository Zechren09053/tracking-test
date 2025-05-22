<?php
// session_handler.php - Include this at the top of all protected pages

// Connect to database
require_once 'db_connect.php';



// Function to update user activity timestamp
function updateActivityTimestamp($conn) {
    // For regular users
    if (isset($_SESSION['logged_in_user_id'])) {
        $query = "UPDATE users SET last_activity = NOW() WHERE id = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "i", $_SESSION['logged_in_user_id']);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
    
    // For staff users
    if (isset($_SESSION['logged_in_staff_id'])) {
        $query = "UPDATE staff_users SET last_activity = NOW() WHERE staff_id = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "i", $_SESSION['logged_in_staff_id']);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
}

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['logged_in_user_id']) || isset($_SESSION['logged_in_staff_id']);
}

// Function to enforce login requirement
function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: login.php");
        exit;
    }
}

// Update activity timestamp if logged in
if (isLoggedIn()) {
    updateActivityTimestamp($conn);
}
?>