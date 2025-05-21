<?php
require 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'] ?? '';
    $message = $_POST['message'] ?? '';
    $display_from = $_POST['display_from'] ?? '';
    $display_duration = intval($_POST['display_duration'] ?? 1);

    if (!empty($title) && !empty($message) && !empty($display_from) && $display_duration > 0) {
        $stmt = $conn->prepare("INSERT INTO announcements (title, message, display_from, display_duration) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("sssi", $title, $message, $display_from, $display_duration);
        if ($stmt->execute()) {
            header("Location: routeschedules.php?success=1");
            exit();
        } else {
            echo "Error: " . $stmt->error;
        }
        $stmt->close();
    } else {
        echo "Please fill in all fields.";
    }
}

$conn->close();
?>
