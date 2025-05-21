<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set proper content type for JSON response
header('Content-Type: application/json');

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