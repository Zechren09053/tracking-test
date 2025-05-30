<?php
session_start();
require 'db_connect.php';

define('DEVELOPMENT_MODE', true);

if (!DEVELOPMENT_MODE && (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] === "off")) {
    header("Location: https://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
    exit();
}

header("X-Frame-Options: DENY");
header("X-Content-Type-Options: nosniff");
header("Referrer-Policy: no-referrer");
header("Content-Security-Policy: default-src 'self'; style-src 'self' 'unsafe-inline'; script-src 'self' 'unsafe-inline';");

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$error = "";
$lockout_seconds_remaining = 0;

if (!isset($_SESSION['login_attempts'])) {
    $_SESSION['login_attempts'] = 0;
    $_SESSION['last_attempt'] = time();
}

if ($_SESSION['login_attempts'] >= 3) {
    $elapsed = time() - $_SESSION['last_attempt'];
    $lockout_seconds_remaining = max(0, 10 - $elapsed);
    if ($lockout_seconds_remaining > 0) {
        $error = "Too many failed login attempts. Please wait.";
    } else {
        $_SESSION['login_attempts'] = 0;
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['username'], $_POST['password'], $_POST['csrf_token'])) {

    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die("Invalid CSRF token.");
    }

    $user = trim($_POST['username']);
    $pass = $_POST['password'];

    // Prepare query to fetch user details
    $stmt = $conn->prepare("SELECT staff_id, username, password, role, first_name, last_name, email FROM staff_users WHERE username = ? AND is_active = 1");

    $stmt->bind_param("s", $user);
    $stmt->execute();
    $result = $stmt->get_result();

    $row = $result->fetch_assoc();
    $valid = $row && password_verify($pass, $row['password']);

    if ($valid) {
        session_regenerate_id(true);

        // Store user details in session
        $_SESSION['staff_id'] = $row['staff_id'];
        $_SESSION['username'] = $row['username'];
        $_SESSION['role'] = $row['role'];
        $_SESSION['name'] = $row['first_name'] . " " . $row['last_name'];
        $_SESSION['email'] = $row['email']; // for sending 2FA code

        $_SESSION['login_attempts'] = 0;

        switch ($_SESSION['role']) {
            case 'super_admin':
                header("Location: 2fa.php");;
                break;
            case 'admin':
                header("Location: 2fa.php");;
                break;
            case 'operator':
                header("Location: 2fa.php");;
                break;
            case 'Auditor':
                header("Location: 2fa.php"); // You can set this to the appropriate page for auditors
                break;
            default:
            header("Location: 2fa.php");
        }
        exit();
        
    } else {
        $error = "Invalid username or password.";
        $_SESSION['login_attempts']++;
        $_SESSION['last_attempt'] = time();
    }

    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Pasig Ferry Admin - Login</title>
    <link rel="stylesheet" href="Db.css">
    <style>
      body { background-color: #1f1f1f; color: white; font-family: Arial, sans-serif; display: flex; align-items: center; justify-content: center; height: 100vh; margin: 0; }
.login-container { background-color: #2c2c2c; padding: 40px; border-radius: 16px; width: 100%; max-width: 400px; transition: all 0.5s ease; position: relative; overflow: hidden; }
.login-container { box-shadow: 0 0 20px rgba(0, 188, 212, 0.7); border: 2px solid #00bcd4; }
.login-container.lockout { box-shadow: 0 0 20px rgba(255, 0, 0, 0.7); border: 2px solid #ff0000; opacity: 0.8; pointer-events: none; }
.login-container.unlocked { box-shadow: 0 0 30px rgba(0, 255, 0, 0.9); border: 2px solid #00ff00; animation: pulse 1.5s infinite; }
.lockout-overlay { position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: linear-gradient(to bottom, rgba(255, 0, 0, 0.2) 0%, rgba(255, 0, 0, 0.15) 50%, rgba(255, 0, 0, 0.1) 100%); pointer-events: none; z-index: 10; transition: transform 1s linear; transform-origin: top; }
.login-container h2 { text-align: center; margin-bottom: 20px; color: #00bcd4; }
.login-container input[type="text"], .login-container input[type="password"] { width: 100%; padding: 12px; margin-bottom: 16px; border: none; border-radius: 8px; background-color: #3a3a3a; color: white; }
.login-container input[type="submit"] { width: 100%; padding: 12px; background-color: #00bcd4; border: none; border-radius: 8px; color: #fff; font-weight: bold; cursor: pointer; }
.login-container input[type="submit"]:hover { background-color: #00acc1; }
.error { color: red; text-align: center; margin-top: 10px; }
@media (max-width: 500px) { .login-container { padding: 25px; } }
@keyframes pulse { 0% { box-shadow: 0 0 20px rgba(0, 255, 0, 0.7); } 50% { box-shadow: 0 0 40px rgba(0, 255, 0, 1); } 100% { box-shadow: 0 0 20px rgba(0, 255, 0, 0.7); } }


    </style>
</head>
<body>
    <form class="login-container<?= $lockout_seconds_remaining ? ' lockout' : '' ?>" method="POST" action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>" id="loginForm">
        <?php if ($lockout_seconds_remaining): ?>
            <div class="lockout-overlay" id="lockoutOverlay"></div>
        <?php endif; ?>
        <h2>Ferry Admin Login</h2>
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
        <input type="text" name="username" placeholder="Username" required autofocus <?= $lockout_seconds_remaining ? 'disabled' : '' ?>>
        <input type="password" name="password" placeholder="Password" required <?= $lockout_seconds_remaining ? 'disabled' : '' ?>>
        <input type="submit" value="Log In" <?= $lockout_seconds_remaining ? 'disabled' : '' ?>>
        <?php if ($error): ?>
            <div class='error'><?= $error ?></div>
        <?php endif; ?>
    </form>

    <?php if ($lockout_seconds_remaining): ?>
    <script>
        const totalLockout = <?= $lockout_seconds_remaining ?>;
        let countdown = totalLockout;
        const formEl = document.getElementById('loginForm');
        const overlayEl = document.getElementById('lockoutOverlay');
        
        // Set initial scale for the overlay
        overlayEl.style.transform = `scaleY(1)`;

        const interval = setInterval(() => {
            countdown--;
            
            // Update draining effect - gradually drain from top to bottom
            const remainingPercent = countdown / totalLockout;
            overlayEl.style.transform = `scaleY(${remainingPercent})`;
            
            if (countdown <= 0) {
                clearInterval(interval);
                
                // Remove red overlay
                if (overlayEl) overlayEl.remove();
                
                // Add green glow effect
                formEl.classList.remove('lockout');
                formEl.classList.add('unlocked');
                
                // Enable form fields
                document.querySelector('input[name="username"]').disabled = false;
                document.querySelector('input[name="password"]').disabled = false;
                document.querySelector('input[type="submit"]').disabled = false;
                
                // Reload after a short delay to show the green effect
                setTimeout(() => {
                    location.reload();
                }, 1500);
            }
        }, 1000);
    </script>
    <?php endif; ?>

    <!-- Konami Code Script -->
    <script>
        // Initialize Konami Code sequence
        const konamiCode = ['ArrowUp', 'ArrowUp', 'ArrowDown', 'ArrowDown', 'ArrowLeft', 'ArrowRight', 'ArrowLeft', 'ArrowRight', 'b', 'a'];
        let konamiPosition = 0;
        
        document.addEventListener('keydown', function(e) {
            // Get the key name
            const key = e.key;
            
            // Get the expected key at the current position
            const expectedKey = konamiCode[konamiPosition];
            
            // Check if the key matches what we expect (case insensitive for letters)
            if (key.toLowerCase() === expectedKey.toLowerCase()) {
                // Move to the next position in the sequence
                konamiPosition++;
                
                // If the konami code is complete
                if (konamiPosition === konamiCode.length) {
                    // Reset position
                    konamiPosition = 0;
                    
                    // Secret action - redirect to register.php
                    window.location.href = 'register.php';
                }
            } else {
                // Reset position if wrong key is pressed
                konamiPosition = 0;
            }
        });
    </script>
</body>
</html>