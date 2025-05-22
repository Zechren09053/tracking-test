<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include database connection
require 'db_connect.php';

// Set proper content type for JSON response
header('Content-Type: application/json');

// Update user's offline status before destroying session
if (isset($_SESSION['logged_in_user_id'])) {
    try {
        // Mark user as offline by setting last_activity to NULL
        $query = "UPDATE users SET last_activity = NULL WHERE id = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "i", $_SESSION['logged_in_user_id']);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    } catch (Exception $e) {
        error_log("Client logout update error: " . $e->getMessage());
    }
}

// Clear all session variables
$_SESSION = array();

// If it's desired to kill the session cookie, do this
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destroy the session
$destroyed = session_destroy();

// Return success response
echo json_encode([
    'success' => $destroyed, 
    'message' => $destroyed ? 'Logged out successfully' : 'Error during logout'
]);
?>