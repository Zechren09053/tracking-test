<?php
session_start();

$servername = "localhost";
$db_username = "PRFS";
$db_password = "1111";
$dbname = "prfs";

$conn = new mysqli($servername, $db_username, $db_password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$error = "";

if (isset($_POST['username']) && isset($_POST['password'])) {
    $user = $_POST['username'];
    $pass = $_POST['password'];

    $stmt = $conn->prepare("SELECT staff_id, username, password, role, first_name, last_name FROM staff_users WHERE username = ? AND is_active = 1");
    $stmt->bind_param("s", $user);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();

        // ⚠️ DEV ONLY: comparing raw passwords instead of hashed
        if ($pass === $row['password']) {
            $_SESSION['staff_id'] = $row['staff_id'];
            $_SESSION['username'] = $row['username'];
            $_SESSION['role'] = $row['role'];
            $_SESSION['name'] = $row['first_name'] . " " . $row['last_name'];

            switch ($row['role']) {
                case 'super_admin':
                case 'admin':
                    header("Location: Dashboard.php");
                    break;
                case 'employee':
                    header("Location: employee_panel.php");
                    break;
                default:
                    header("Location: Dashboard.php");
            }
            exit();
        } else {
            $error = "Invalid password.";
        }
    } else {
        $error = "User not found or inactive.";
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

        .login-container {
            background-color: #2c2c2c;
            padding: 40px;
            border-radius: 16px;
            box-shadow: 0 0 10px rgba(0,0,0,0.5);
            width: 100%;
            max-width: 400px;
        }

        .login-container h2 {
            text-align: center;
            margin-bottom: 20px;
            color: #00bcd4;
        }

        .login-container input[type="text"],
        .login-container input[type="password"] {
            width: 100%;
            padding: 12px;
            margin-bottom: 16px;
            border: none;
            border-radius: 8px;
            background-color: #3a3a3a;
            color: white;
        }

        .login-container input[type="submit"] {
            width: 100%;
            padding: 12px;
            background-color: #00bcd4;
            border: none;
            border-radius: 8px;
            color: #fff;
            font-weight: bold;
            cursor: pointer;
        }

        .login-container input[type="submit"]:hover {
            background-color: #00acc1;
        }

        .error {
            color: red;
            text-align: center;
            margin-top: 10px;
        }

        @media (max-width: 500px) {
            .login-container {
                padding: 25px;
            }
        }
    </style>
</head>
<body>
    <form class="login-container" method="POST" action="">
        <h2>Ferry Admin Login</h2>
        <input type="text" name="username" placeholder="Username" required autofocus>
        <input type="password" name="password" placeholder="Password" required>
        <input type="submit" value="Log In">
        <?php if (isset($error)) echo "<div class='error'>$error</div>"; ?>
    </form>
</body>
</html>
