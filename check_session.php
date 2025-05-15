<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set proper content type for JSON response
header('Content-Type: application/json');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

// Generate CSRF token if it doesn't exist
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Set a session activity timestamp
$_SESSION['last_activity'] = time();

// Regenerate session ID periodically for security
if (!isset($_SESSION['last_regeneration']) || (time() - $_SESSION['last_regeneration']) > 300) {
    // Regenerate session ID every 5 minutes
    session_regenerate_id(true);
    $_SESSION['last_regeneration'] = time();
}

// Check if the user is already logged in
$logged_in = isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);

// Get user role if available
$user_role = isset($_SESSION['user_role']) ? $_SESSION['user_role'] : 'guest';

// Get user ID if logged in
$user_id = $logged_in ? $_SESSION['user_id'] : null;

// Check if this is an AJAX long-polling request
$is_long_polling = isset($_GET['polling']) && $_GET['polling'] === 'true';

if ($is_long_polling) {
    // For long-polling requests, wait for a short time to see if the session changes
    $timeout = 20; // 20 seconds timeout
    $start_time = time();
    $session_file = session_save_path() . '/sess_' . session_id();
    $last_modified = filemtime($session_file);
    
    // Store current login status
    $current_status = $logged_in;
    
    // Close session to allow other scripts to modify it
    session_write_close();
    
    while (time() - $start_time < $timeout) {
        clearstatcache(true, $session_file);
        $new_modified = filemtime($session_file);
        
        // If session file was modified, recheck login status
        if ($new_modified > $last_modified) {
            // Restart session to get new data
            session_start();
            $new_logged_in = isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
            
            // If status changed, return immediately
            if ($new_logged_in !== $current_status) {
                echo json_encode([
                    'logged_in' => $new_logged_in,
                    'user_id' => $new_logged_in ? $_SESSION['user_id'] : null,
                    'csrf_token' => $_SESSION['csrf_token'],
                    'user_role' => isset($_SESSION['user_role']) ? $_SESSION['user_role'] : 'guest',
                    'status_changed' => true
                ]);
                exit;
            }
            
            session_write_close();
            $last_modified = $new_modified;
        }
        
        // Sleep for a short time to prevent CPU usage
        usleep(500000); // 0.5 seconds
    }
    
    // If timeout reached with no changes, return current status
    echo json_encode([
        'logged_in' => $current_status,
        'user_id' => $user_id,
        'csrf_token' => $_SESSION['csrf_token'],
        'user_role' => $user_role,
        'status_changed' => false
    ]);
} else {
    // For regular requests, return the current session status
    echo json_encode([
        'logged_in' => $logged_in,
        'user_id' => $user_id,
        'csrf_token' => $_SESSION['csrf_token'],
        'user_role' => $user_role,
        'session_expires' => isset($_SESSION['last_activity']) ? ($_SESSION['last_activity'] + ini_get('session.gc_maxlifetime')) : null
    ]);
}
?>
