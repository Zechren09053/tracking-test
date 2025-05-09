<?php
session_start();
require 'db_connect.php'; // Centralized DB connection

$success = "";
$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
    } elseif (strlen($password) < 12 || !preg_match('/[^a-zA-Z0-9]/', $password)) {
        $error = "Password must be at least 12 characters and include a symbol.";
    } else {
        // Check if username already exists
        $check_stmt = $conn->prepare("SELECT staff_id FROM staff_users WHERE username = ?");
        $check_stmt->bind_param("s", $username);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();

        if ($check_result->num_rows > 0) {
            $error = "Username already exists.";
        } else {
            $hashed_pw = password_hash($password, PASSWORD_DEFAULT);
            $insert_stmt = $conn->prepare("INSERT INTO staff_users (username, password, first_name, last_name, email, role, is_active) VALUES (?, ?, ?, ?, ?, ?, 1)");
            $insert_stmt->bind_param("ssssss", $username, $hashed_pw, $first_name, $last_name, $email, $role);
            
            if ($insert_stmt->execute()) {
                $success = "Registration successful. You can now <a href='login.php'>log in</a>.";
            } else {
                $error = "Registration failed. Please try again.";
            }

            $insert_stmt->close();
        }

        $check_stmt->close();
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
            height: 100vh;
            margin: 0;
        }

        .register-container {
            background-color: #2c2c2c;
            padding: 40px;
            border-radius: 16px;
            box-shadow: 0 0 10px rgba(0,0,0,0.5);
            width: 100%;
            max-width: 450px;
        }

        .register-container h2 {
            text-align: center;
            margin-bottom: 20px;
            color: #00bcd4;
        }

        .register-container input,
        .register-container select {
            width: 100%;
            padding: 12px;
            margin-bottom: 10px;
            border: none;
            border-radius: 8px;
            background-color: #3a3a3a;
            color: white;
        }

        .register-container input[type="submit"] {
            background-color: #00bcd4;
            font-weight: bold;
            cursor: pointer;
        }

        .register-container input[type="submit"]:hover {
            background-color: #00acc1;
        }

        .message {
            text-align: center;
            margin-top: 10px;
        }

        .message.success {
            color: lightgreen;
        }

        .message.error {
            color: red;
        }

        #password-feedback,
        #confirm-feedback {
            font-size: 12px;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <form class="register-container" method="POST">
        <h2>Create New Staff Account</h2>
        <input type="text" name="first_name" placeholder="First Name" required>
        <input type="text" name="last_name" placeholder="Last Name" required>
        <input type="email" name="email" placeholder="Email Address" required>

        <input type="text" name="username" placeholder="Username" required>

        <input type="password" name="password" id="password" placeholder="Password (min 12 chars, include symbol)" required>
        <div id="password-feedback"></div>

        <input type="password" name="confirm_password" id="confirm_password" placeholder="Confirm Password" required>
        <div id="confirm-feedback"></div>

        <select name="role">
            <option value="employee">Employee</option>
            <option value="admin">Admin</option>
        </select>
        <input type="submit" value="Register">

        <?php if ($error): ?>
            <div class="message error"><?= $error ?></div>
        <?php elseif ($success): ?>
            <div class="message success"><?= $success ?></div>
        <?php endif; ?>
    </form>

    <script>
        const passwordInput = document.getElementById('password');
        const confirmInput = document.getElementById('confirm_password');
        const passwordFeedback = document.getElementById('password-feedback');
        const confirmFeedback = document.getElementById('confirm-feedback');

        function validatePasswordStrength(password) {
            const hasSymbol = /[^a-zA-Z0-9]/.test(password);
            const isLongEnough = password.length >= 12;
            return {
                isValid: hasSymbol && isLongEnough,
                hasSymbol,
                isLongEnough
            };
        }

        passwordInput.addEventListener('input', () => {
            const val = passwordInput.value;
            const check = validatePasswordStrength(val);

            if (!check.isLongEnough || !check.hasSymbol) {
                passwordFeedback.style.color = "orange";
                passwordFeedback.textContent = "❌ Password must be at least 12 characters and include at least one symbol.";
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
    </script>
</body>
</html>
