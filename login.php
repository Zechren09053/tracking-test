<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Ferry Admin Login</title>
    <link rel="stylesheet" href="db.css">
    <link rel="stylesheet" href="login-style.css">
</head>
<body>
    <div class="login-container">
        <h2>🚢 Ferry Admin Login</h2>
        <p>Please enter your credentials to continue</p>

        <form action="process_login.php" method="POST">
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>

            <button type="submit">Login</button>
        </form>

        <div class="forgot-password">
            <a href="forgot_password.php">Forgot Password?</a>
        </div>
    </div>
</body>
</html>
