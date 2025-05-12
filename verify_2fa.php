<?php
session_start();
require 'db_connect.php';
require 'vendor/autoload.php'; // Assuming you'll use composer to install the TOTP library

use OTPHP\TOTP;

define('DEVELOPMENT_MODE', true);

// Force HTTPS in production
if (!DEVELOPMENT_MODE && (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] === "off")) {
    header("Location: https://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
    exit();
}

// Security headers
header("X-Frame-Options: DENY");
header("X-Content-Type-Options: nosniff");
header("Referrer-Policy: no-referrer");
header("Content-Security-Policy: default-src 'self'; style-src 'self' 'unsafe-inline'; script-src 'self' 'unsafe-inline';");

// Generate CSRF token if it doesn't exist
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Check if the user has completed first authentication step
if (!isset($_SESSION['2fa_temp'])) {
    header("Location: login.php");
    exit();
}

$error = "";
$max_attempts = 3;

// Initialize 2FA attempt tracking
if (!isset($_SESSION['2fa_attempts'])) {
    $_SESSION['2fa_attempts'] = 0;
}

// Process 2FA verification
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['code'], $_POST['csrf_token'])) {
    // Validate CSRF token
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die("Invalid CSRF token.");
    }
    
    // Check if max attempts reached
    if ($_SESSION['2fa_attempts'] >= $max_attempts) {
        // Log the excessive 2FA attempts
        logSecurityEvent(
            $_SESSION['2fa_temp']['staff_id'], 
            'excessive_2fa_attempts', 
            'Multiple failed 2FA attempts triggered lockout',
            $_SERVER['REMOTE_ADDR']
        );
        
        // Clear 2FA session data
        unset($_SESSION['2fa_temp']);
        unset($_SESSION['2fa_attempts']);
        
        // Redirect to login page with error
        $_SESSION['login_error'] = "Too many failed 2FA attempts. Please log in again.";
        header("Location: login.php");
        exit();
    }
    
    $submitted_code = $_POST['code'];
    $secret = $_SESSION['2fa_temp']['secret'];
    
    // Create TOTP object
    $totp = TOTP::create($secret);
    
    // Verify the code (with a 30-second window)
    if ($totp->verify($submitted_code)) {
        // Code is valid, complete login process
        session_regenerate_id(true);
        
        // Store user details in session
        $_SESSION['staff_id'] = $_SESSION['2fa_temp']['staff_id'];
        $_SESSION['username'] = $_SESSION['2fa_temp']['username'];
        $_SESSION['role'] = $_SESSION['2fa_temp']['role'];
        $_SESSION['name'] = $_SESSION['2fa_temp']['name'];
        
        // Handle "Remember Me" if selected
        if ($_SESSION['2fa_temp']['remember']) {
            createRememberMeToken($_SESSION['staff_id']);
        }
        
        // Log successful 2FA verification
        logSecurityEvent(
            $_SESSION['staff_id'], 
            '2fa_success', 
            '2FA verification successful',
            $_SERVER['REMOTE_ADDR']
        );
        
        // Clean up 2FA session data
        unset($_SESSION['2fa_temp']);
        unset($_SESSION['2fa_attempts']);
        
        // Redirect based on role
        redirectBasedOnRole($_SESSION['role']);
        
    } else {
        // Invalid code
        $_SESSION['2fa_attempts']++;
        $error = "Invalid verification code. Please try again.";
        
        // Log failed 2FA attempt
        logSecurityEvent(
            $_SESSION['2fa_temp']['staff_id'], 
            '2fa_failure', 
            'Failed 2FA verification attempt',
            $_SERVER['REMOTE_ADDR']
        );
    }
}

/**
 * Create and set a secure remember me token
 */
function createRememberMeToken($user_id) {
    global $conn;
    
    // Generate a random token
    $token = bin2hex(random_bytes(32));
    $hmac_secret = file_get_contents('/path/to/secret/key.txt'); // Store this outside web root
    
    // Store token in database
    $stmt = $conn->prepare("INSERT INTO auth_tokens (user_id, token, expires) VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 30 DAY))");
    $stmt->bind_param("is", $user_id, $token);
    $stmt->execute();
    $stmt->close();
    
    // Create a MAC for cookie integrity
    $hmac_data = $user_id . ':' . $token;
    $mac = hash_hmac('sha256', $hmac_data, $hmac_secret);
    $cookie_value = $user_id . ':' . $token . ':' . $mac;
    
    // Set secure cookie
    setcookie(
        'ferry_remember_token',
        $cookie_value,
        [
            'expires' => time() + (60 * 60 * 24 * 30), // 30 days
            'path' => '/',
            'secure' => !DEVELOPMENT_MODE,
            'httponly' => true,
            'samesite' => 'Strict'
        ]
    );
}

/**
 * Log security events for auditing
 */
function logSecurityEvent($user_id, $event_type, $description, $ip_address) {
    global $conn;
    
    $stmt = $conn->prepare("INSERT INTO security_logs (user_id, event_type, description, ip_address, event_time) 
                          VALUES (?, ?, ?, ?, NOW())");
    $stmt->bind_param("isss", $user_id, $event_type, $description, $ip_address);
    $stmt->execute();
    $stmt->close();
}

/**
 * Handle redirects based on user role
 */
function redirectBasedOnRole($role) {
    switch ($role) {
        case 'super_admin':
            header("Location: Dashboard.php");
            break;
        case 'admin':
            header("Location: Dashboard.php");
            break;
        case 'operator':
            header("Location: track_ferry.php");
            break;
        case 'auditor':
            header("Location: audit_dashboard.php");
            break;
        default:
            header("Location: Dashboard.php");
    }
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Two-Factor Authentication - Pasig Ferry Admin</title>
    <link rel="stylesheet" href="Db.css">
    <style>
      body { background-color: #1f1f1f; color: white; font-family: Arial, sans-serif; display: flex; align-items: center; justify-content: center; height: 100vh; margin: 0; }
.login-container { background-color: #2c2c2c; padding: 40px; border-radius: 16px; width: 100%; max-width: 400px; box-shadow: 0 0 20px rgba(0, 188, 212, 0.7); border: 2px solid #00bcd4; }
.login-container h2 { text-align: center; margin-bottom: 20px; color: #00bcd4; }
.login-container p { text-align: center; margin-bottom: 20px; color: #ddd; }
.login-container input[type="text"] { width: 100%; padding: 12px; margin-bottom: 16px; border: none; border-radius: 8px; background-color: #3a3a3a; color: white; text-align: center; letter-spacing: 0.5em; font-size: 1.5em; }
.login-container input[type="submit"] { width: 100%; padding: 12px; background-color: #00bcd4; border: none; border-radius: 8px; color: #fff; font-weight: bold; cursor: pointer; }
.login-container input[type="submit"]:hover { background-color: #00acc1; }
.error { color: #ff5252; text-align: center; margin-top: 10px; }
.countdown-container { text-align: center; margin-top: 20px; }
.countdown { display: inline-block; position: relative; width: 60px; height: 60px; }
.countdown svg { position: absolute; top: 0; left: 0; width: 100%; height: 100%; transform: rotate(-90deg); }
.countdown circle { fill: none; stroke-width: 4; stroke-linecap: round; stroke: #00bcd4; stroke-dasharray: 283; stroke-dashoffset: 0; animation: countdown 30s linear forwards; }
.countdown-text { position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); font-size: 16px; color: #fff; }
.attempts-left { text-align: center; margin-top: 10px; color: #ff9800; }
@keyframes countdown { to { stroke-dashoffset: 283; } }
@media (max-width: 500px) { .login-container { padding: 25px; } }
    </style>
</head>
<body>
    <form class="login-container" method="POST" action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>" id="2faForm">
        <h2>Two-Factor Authentication</h2>
        <p>Enter the verification code from your authenticator app</p>
        
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
        
        <input type="text" name="code" id="code" placeholder="000000" maxlength="6" pattern="[0-9]*" inputmode="numeric" autofocus required>
        
        <input type="submit" value="Verify">
        
        <?php if ($error): ?>
            <div class='error'><?= $error ?></div>
        <?php endif; ?>
        
        <?php if ($_SESSION['2fa_attempts'] > 0): ?>
            <div class='attempts-left'>
                <?= $max_attempts - $_SESSION['2fa_attempts'] ?> attempt<?= ($max_attempts - $_SESSION['2fa_attempts'] !== 1) ? 's' : '' ?> remaining
            </div>
        <?php endif; ?>
        
        <div class="countdown-container">
            <div class="countdown">
                <svg>
                    <circle r="45" cx="30" cy="30"></circle>
                </svg>
                <div class="countdown-text" id="countdownText">30</div>
            </div>
        </div>
    </form>

    <script>
        // Auto-submit when all digits are entered
        document.getElementById('code').addEventListener('input', function() {
            if (this.value.length === 6) {
                document.getElementById('2faForm').submit();
            }
        });
        
        // TOTP code countdown timer
        let timeLeft = 30;
        const countdownEl = document.getElementById('countdownText');
        
        function updateCountdown() {
            countdownEl.textContent = timeLeft;
            
            if (timeLeft === 0) {
                // Reset timer
                timeLeft = 30;
            } else {
                timeLeft--;
            }
        }
        
        // Update every second
        setInterval(updateCountdown, 1000);
        
        // Get initial remaining seconds
        fetch('/api/totp_time_remaining')
            .then(response => response.json())
            .then(data => {
                if (data.remaining) {
                    timeLeft = data.remaining;
                    updateCountdown();
                }
            })
            .catch(() => {
                // Use default countdown if API fails
            });
    </script>
</body>
</html>