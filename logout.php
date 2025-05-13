<?php
session_start();
require 'db_connect.php';

// Update user's active status based on user type
if (isset($_SESSION['user_id'])) {
    try {
        if ($_SESSION['user_type'] === 'staff') {
            // For staff users, we'll just update the timestamp
            $stmt = $pdo->prepare("
                UPDATE staff_users 
                SET 
                    updated_at = NOW() 
                WHERE staff_id = :user_id
            ");
            $stmt->execute(['user_id' => $_SESSION['user_id']]);
        } elseif ($_SESSION['user_type'] === 'regular') {
            // For regular users, update last_used
            $stmt = $pdo->prepare("
                UPDATE users 
                SET 
                    last_used = NOW() 
                WHERE id = :user_id
            ");
            $stmt->execute(['user_id' => $_SESSION['user_id']]);
        }
    } catch (PDOException $e) {
        // Log the error (in a real-world scenario, log to a file)
        error_log("Logout update error: " . $e->getMessage());
    }
}

// Destroy the session
session_unset();
session_destroy();

// Redirect to login page
header("Location: login.php");
exit();