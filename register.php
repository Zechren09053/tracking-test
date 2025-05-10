<?php
session_start();
require 'db_connect.php'; // Centralized DB connection

// Developer mode - show errors (REMOVE IN PRODUCTION)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// CSRF protection
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Security headers
header("X-Frame-Options: DENY");
header("X-Content-Type-Options: nosniff");
header("Referrer-Policy: no-referrer");
header("Content-Security-Policy: default-src 'self'; style-src 'self' 'unsafe-inline'; script-src 'self' 'unsafe-inline';");

$success = "";
$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Debug information for developer mode
    error_log("Form submitted. POST data: " . print_r($_POST, true));
    error_log("Session CSRF: " . $_SESSION['csrf_token']);
    error_log("POST CSRF: " . ($_POST['csrf_token'] ?? 'Not provided'));
    
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $error = "Invalid CSRF token. Please refresh the page and try again.";
    } else {
        $username   = trim($_POST['username']);
        $password   = $_POST['password'];
        $confirm_pw = $_POST['confirm_password'];
        $first_name = trim($_POST['first_name']);
        $last_name  = trim($_POST['last_name']);
        $email = trim($_POST['email']);
        $role       = $_POST['role'] ?? 'employee'; // Default role

        // Server-side validation
        if ($password !== $confirm_pw) {
            $error = "Passwords do not match.";
        } elseif (strlen($password) < 12) {
            $error = "Password must be at least 12 characters.";
        } elseif (!preg_match('/[^a-zA-Z0-9]/', $password)) {
            $error = "Password must include at least one symbol.";
        } elseif (!preg_match('/[A-Z]/', $password)) {
            $error = "Password must include at least one uppercase letter.";
        } elseif (!preg_match('/[a-z]/', $password)) {
            $error = "Password must include at least one lowercase letter.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "Please enter a valid email address.";
        } elseif (strlen($username) < 4) {
            $error = "Username must be at least 4 characters.";
        } else {
            // Check if username already exists
            $check_stmt = $conn->prepare("SELECT staff_id FROM staff_users WHERE username = ?");
            $check_stmt->bind_param("s", $username);
            $check_stmt->execute();
            $check_result = $check_stmt->get_result();

            // Check if email already exists
            $email_check = $conn->prepare("SELECT staff_id FROM staff_users WHERE email = ?");
            $email_check->bind_param("s", $email);
            $email_check->execute();
            $email_result = $email_check->get_result();

            if ($check_result->num_rows > 0) {
                $error = "Username already exists.";
            } elseif ($email_result->num_rows > 0) {
                $error = "Email already registered.";
            } else {
                $hashed_pw = password_hash($password, PASSWORD_DEFAULT);
                $insert_stmt = $conn->prepare("INSERT INTO staff_users (username, password, first_name, last_name, email, role, is_active) VALUES (?, ?, ?, ?, ?, ?, 1)");
                $insert_stmt->bind_param("ssssss", $username, $hashed_pw, $first_name, $last_name, $email, $role);
                
                if ($insert_stmt->execute()) {
                    $success = "Registration successful. You can now <a href='login.php'>log in</a>.";
                    
                    // Log the registration
                    $log_message = "New user registered: $username (role: $role)";
                    error_log($log_message);
                } else {
                    $error = "Registration failed: " . $conn->error;
                }

                $insert_stmt->close();
            }

            $check_stmt->close();
            $email_check->close();
        }
    }

    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register | Ferry Admin</title>
    <link rel="stylesheet" href="Db.css">
    <style>
        body {
            background-color: #1f1f1f;
            color: white;
            font-family: Arial, sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
            padding: 20px 0;
        }

        .register-container {
            background-color: #2c2c2c;
            padding: 40px;
            border-radius: 16px;
            width: 100%;
            max-width: 450px;
            box-shadow: 0 0 20px rgba(0, 188, 212, 0.7);
            border: 2px solid #00bcd4;
            animation: subtle-pulse 4s infinite;
        }

        @keyframes subtle-pulse {
            0% { box-shadow: 0 0 15px rgba(0, 188, 212, 0.6); }
            50% { box-shadow: 0 0 25px rgba(0, 188, 212, 0.8); }
            100% { box-shadow: 0 0 15px rgba(0, 188, 212, 0.6); }
        }

        .register-container h2 {
            text-align: center;
            margin-bottom: 30px;
            color: #00bcd4;
            font-size: 24px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 6px;
            font-size: 14px;
            color: #ccc;
        }

        .input-group {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }

        .input-group .form-group {
            flex: 1;
            margin-bottom: 0;
        }

        .register-container input,
        .register-container select {
            width: 100%;
            padding: 12px;
            border: none;
            border-radius: 8px;
            background-color: #3a3a3a;
            color: white;
            font-size: 15px;
            transition: all 0.3s ease;
        }

        .register-container input:focus,
        .register-container select:focus {
            outline: none;
            box-shadow: 0 0 0 2px rgba(0, 188, 212, 0.5);
            background-color: #444;
        }

        .register-container input[type="submit"] {
            background-color: #00bcd4;
            font-weight: bold;
            cursor: pointer;
            padding: 14px;
            margin-top: 10px;
            font-size: 16px;
            transition: all 0.3s ease;
        }

        .register-container input[type="submit"]:hover {
            background-color: #00acc1;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        .register-container input[type="submit"]:active {
            transform: translateY(0);
        }

        .message {
            text-align: center;
            margin-top: 20px;
            padding: 10px;
            border-radius: 8px;
        }

        .message.success {
            background-color: rgba(0, 128, 0, 0.2);
            color: lightgreen;
        }

        .message.error {
            background-color: rgba(255, 0, 0, 0.2);
            color: #ff6b6b;
        }

        .feedback {
            font-size: 12px;
            margin-top: 5px;
            min-height: 18px;
            transition: all 0.3s ease;
        }

        .strength-meter {
            height: 4px;
            width: 100%;
            background-color: #444;
            margin-top: 8px;
            border-radius: 2px;
            position: relative;
            overflow: hidden;
        }

        .strength-meter .fill {
            height: 100%;
            transition: width 0.3s ease, background-color 0.3s ease;
            width: 0;
            background-color: red;
        }

        .login-link {
            text-align: center;
            margin-top: 20px;
            font-size: 14px;
            color: #ccc;
        }

        .login-link a {
            color: #00bcd4;
            text-decoration: none;
        }

        .login-link a:hover {
            text-decoration: underline;
        }
        
        /* Developer mode indicator */
        .dev-mode {
            position: fixed;
            top: 10px;
            right: 10px;
            background-color: #ff5722;
            color: white;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
            z-index: 1000;
        }
    </style>
</head>
<body>
    <!-- Developer mode indicator -->
    <div class="dev-mode">DEVELOPER MODE</div>
    
    <form class="register-container" method="POST">
        <h2>Create New Staff Account</h2>
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

        <div class="input-group">
            <div class="form-group">
                <label for="first_name">First Name</label>
                <input type="text" id="first_name" name="first_name" required value="<?= isset($_POST['first_name']) ? htmlspecialchars($_POST['first_name']) : '' ?>">
            </div>
            
            <div class="form-group">
                <label for="last_name">Last Name</label>
                <input type="text" id="last_name" name="last_name" required value="<?= isset($_POST['last_name']) ? htmlspecialchars($_POST['last_name']) : '' ?>">
            </div>
        </div>

        <div class="form-group">
            <label for="email">Email Address</label>
            <input type="email" id="email" name="email" required value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>">
            <div id="email-feedback" class="feedback"></div>
        </div>

        <div class="form-group">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" required minlength="4" value="<?= isset($_POST['username']) ? htmlspecialchars($_POST['username']) : '' ?>">
            <div id="username-feedback" class="feedback"></div>
        </div>

        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required minlength="12" 
                   placeholder="Min 12 chars with uppercase, lowercase & symbol">
            <div class="strength-meter">
                <div class="fill" id="strength-fill"></div>
            </div>
            <div id="password-feedback" class="feedback"></div>
        </div>

        <div class="form-group">
            <label for="confirm_password">Confirm Password</label>
            <input type="password" id="confirm_password" name="confirm_password" required>
            <div id="confirm-feedback" class="feedback"></div>
        </div>

        <div class="form-group">
            <label for="role">Role</label>
            <select id="role" name="role">
                <option value="employee" <?= (isset($_POST['role']) && $_POST['role'] === 'employee') ? 'selected' : '' ?>>Employee</option>
                <option value="admin" <?= (isset($_POST['role']) && $_POST['role'] === 'admin') ? 'selected' : '' ?>>Admin</option>
            </select>
        </div>

        <input type="submit" value="Register" id="submit-btn">

        <?php if ($error): ?>
            <div class="message error"><?= $error ?></div>
        <?php elseif ($success): ?>
            <div class="message success"><?= $success ?></div>
        <?php endif; ?>

        <div class="login-link">
            Already have an account? <a href="login.php">Log in</a>
        </div>
    </form>

    <script>
        const passwordInput = document.getElementById('password');
        const confirmInput = document.getElementById('confirm_password');
        const passwordFeedback = document.getElementById('password-feedback');
        const confirmFeedback = document.getElementById('confirm-feedback');
        const strengthFill = document.getElementById('strength-fill');
        const emailInput = document.getElementById('email');
        const emailFeedback = document.getElementById('email-feedback');
        const usernameInput = document.getElementById('username');
        const usernameFeedback = document.getElementById('username-feedback');
        const submitBtn = document.getElementById('submit-btn');

        function validatePasswordStrength(password) {
            let score = 0;
            let feedback = [];
            
            // Length check
            if (password.length < 8) {
                feedback.push("Too short (min 12 chars)");
            } else if (password.length >= 12) {
                score += 2;
            } else {
                score += 1;
            }
            
            // Complexity checks
            if (/[A-Z]/.test(password)) {
                score += 1;
            } else {
                feedback.push("Add uppercase letter");
            }
            
            if (/[a-z]/.test(password)) {
                score += 1;
            } else {
                feedback.push("Add lowercase letter");
            }
            
            if (/[0-9]/.test(password)) score += 1;
            
            if (/[^a-zA-Z0-9]/.test(password)) {
                score += 2;
            } else {
                feedback.push("Add a symbol");
            }
            
            // Pattern checks
            if (/(123|abc|qwe|password|admin)/i.test(password)) {
                score -= 1;
                feedback.push("Avoid common patterns");
            }
            
            // Calculate percentage
            let percentage = Math.min(100, (score / 7) * 100);
            
            // Determine color
            let color = 'red';
            if (percentage > 75) color = '#2ecc71';
            else if (percentage > 50) color = '#f39c12';
            else if (percentage > 25) color = '#e74c3c';
            
            return {
                score: score,
                percentage: percentage,
                color: color,
                feedback: feedback.join(", "),
                isValid: password.length >= 12 && /[^a-zA-Z0-9]/.test(password) && 
                         /[A-Z]/.test(password) && /[a-z]/.test(password)
            };
        }

        function validateEmail(email) {
            const re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
            return re.test(String(email).toLowerCase());
        }

        passwordInput.addEventListener('input', () => {
            const val = passwordInput.value;
            const check = validatePasswordStrength(val);
            
            // Update strength meter
            strengthFill.style.width = `${check.percentage}%`;
            strengthFill.style.backgroundColor = check.color;
            
            if (!check.isValid) {
                passwordFeedback.style.color = "orange";
                passwordFeedback.textContent = check.feedback || "Requirements not met";
            } else {
                passwordFeedback.style.color = "lightgreen";
                passwordFeedback.textContent = "✅ Strong password!";
            }

            // Re-validate confirmation
            if (confirmInput.value) {
                confirmInput.dispatchEvent(new Event('input'));
            }
        });

        confirmInput.addEventListener('input', () => {
            if (confirmInput.value !== passwordInput.value) {
                confirmFeedback.style.color = "orange";
                confirmFeedback.textContent = "❌ Passwords do not match.";
            } else {
                confirmFeedback.style.color = "lightgreen";
                confirmFeedback.textContent = "✅ Passwords match!";
            }
        });

        emailInput.addEventListener('input', () => {
            const isValid = validateEmail(emailInput.value);
            
            if (!isValid && emailInput.value.length > 0) {
                emailFeedback.style.color = "orange";
                emailFeedback.textContent = "❌ Please enter a valid email address.";
            } else if (isValid) {
                emailFeedback.style.color = "lightgreen";
                emailFeedback.textContent = "✅ Valid email format.";
            } else {
                emailFeedback.textContent = "";
            }
        });

        usernameInput.addEventListener('input', () => {
            if (usernameInput.value.length < 4) {
                usernameFeedback.style.color = "orange";
                usernameFeedback.textContent = "Username must be at least 4 characters.";
            } else {
                usernameFeedback.textContent = "";
            }
        });
    </script>
</body>
</html>