<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php'; // make sure this is correct for your setup

function send_2fa_code($toEmail, $code) {
    $mail = new PHPMailer(true);

    try {
        // Enable verbose debug output (set to 0 in production)
        $mail->SMTPDebug = 0; // Set to 2 or 3 to debug
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'severinokenji@gmail.com';        // ✅ your Gmail
        $mail->Password = 'eglq chmf jjtg lkrf';           // 🔐 your App Password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;

        $mail->Port = 587;

        // Recipients
        $mail->setFrom('severinokenji@gmail.com', 'Pasig Ferry System');

        $mail->addAddress($toEmail);

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Your 2FA Code';
        $mail->Body    = "<p>Hello!</p><p>Your 2FA code is: <strong>$code</strong></p><p>This code will expire in 5 minutes.</p>";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Mailer Error: " . $mail->ErrorInfo);
        return false;
    }
}
