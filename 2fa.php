<?php
session_start();
if (!isset($_SESSION['username'], $_SESSION['email'], $_SESSION['staff_id'])) {
    header("Location: login.php");
    exit();
}

// Generate a 6-digit code
if (empty($_SESSION['2fa_code'])) {
    $_SESSION['2fa_code'] = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    $_SESSION['2fa_expiry'] = time() + 300; // expires in 5 mins

    // Send the code via email
    $to = $_SESSION['email'];
    $subject = "Your 2FA Code";
    $message = "Your 2FA code is: " . $_SESSION['2fa_code'];
    $headers = "From: noreply@yourdomain.com";

    require_once 'mail_send.php';
if (!send_2fa_code($to, $_SESSION['2fa_code'])) {
    die("Failed to send 2FA code. Please contact support.");
}

}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = trim($_POST['code'] ?? '');

    if (time() > $_SESSION['2fa_expiry']) {
        $error = "Code expired. Please log in again.";
        session_destroy();
        header("Location: login.php");
        exit();
    } elseif ($code === $_SESSION['2fa_code']) {
        $_SESSION['2fa_verified'] = true;
        unset($_SESSION['2fa_code'], $_SESSION['2fa_expiry']);

        // Redirect based on role
        switch ($_SESSION['role']) {
            case 'super_admin':
            case 'admin':
                header("Location: Dashboard.php");
                break;
            case 'operator':
                header("Location: track_ferry.php");
                break;
            case 'Auditor':
                header("Location: monitor_users.php");
                break;
            default:
                header("Location: Dashboard.php");
        }
        exit();
    } else {
        $error = "Invalid 2FA code.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>2FA Verification</title>
    <style>
        body { background-color: #121212; color: white; font-family: sans-serif; display: flex; justify-content: center; align-items: center; height: 100vh; }
        .box { background: #2c2c2c; padding: 30px; border-radius: 12px; text-align: center; box-shadow: 0 0 20px #00bcd4; }
        input[type="text"] { padding: 10px; width: 80%; margin: 10px 0; border: none; border-radius: 6px; background: #3a3a3a; color: white; }
        input[type="submit"] { background: #00bcd4; color: white; border: none; padding: 10px 20px; border-radius: 6px; cursor: pointer; }
        input[type="submit"]:hover { background: #0097a7; }
        .error { color: red; margin-top: 10px; }
    </style>
</head>
<body>
    <form method="POST" class="box">
        <h2>2FA Verification</h2>
        <?php
$email = $_SESSION['email'];
$atPos = strpos($email, '@');
$maskedEmail = substr($email, 0, 2) . str_repeat('*', $atPos - 2) . substr($email, $atPos);
?>
<p>A 6-digit code has been sent to <strong><?= htmlspecialchars($maskedEmail) ?></strong></p>


        <input type="text" name="code" placeholder="Enter code" maxlength="6" required>
        <input type="submit" value="Verify">
        <?php if ($error): ?><div class="error"><?= $error ?></div><?php endif; ?>
    </form>
</body>
</html>
            