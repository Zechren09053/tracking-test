<?php
session_start();
require 'db_connect.php';

// Update user's offline status based on user type
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['user_type'] === 'staff') {
        // For staff users, mark as offline by setting last_activity to NULL
        $query = "UPDATE staff_users SET last_activity = NULL, updated_at = NOW() WHERE staff_id = ?";
        $stmt = mysqli_prepare($conn, $query);
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "i", $_SESSION['user_id']);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
    } elseif ($_SESSION['user_type'] === 'regular') {
        // For regular users, mark as offline by setting last_activity to NULL
        $query = "UPDATE users SET last_activity = NULL, last_used = NOW() WHERE id = ?";
        $stmt = mysqli_prepare($conn, $query);
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "i", $_SESSION['user_id']);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
    }
}

// Also handle the session variable names used in your login records system
if (isset($_SESSION['logged_in_staff_id'])) {
    // Mark staff as offline
    $query = "UPDATE staff_users SET last_activity = NULL, updated_at = NOW() WHERE staff_id = ?";
    $stmt = mysqli_prepare($conn, $query);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $_SESSION['logged_in_staff_id']);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
}

if (isset($_SESSION['logged_in_user_id'])) {
    // Mark user as offline
    $query = "UPDATE users SET last_activity = NULL, last_used = NOW() WHERE id = ?";
    $stmt = mysqli_prepare($conn, $query);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $_SESSION['logged_in_user_id']);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
}

// Destroy the session
session_unset();
session_destroy();

// Redirect to login page
header("Location: login.php");
exit();
?>