<?php
session_start();
require_once 'db_connect.php';
header('Content-Type: application/json');

if (isset($_POST['password']) && $_POST['password']) {
    $password = $_POST['password'];
    $username = $_SESSION['username'];
    
    try {
        $sql = "SELECT password FROM staff_users WHERE username = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false]);
            }
        } else {
            echo json_encode(['success' => false]);
        }
        
        $stmt->close();
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => 'Database error']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Password not provided']);
}

if (isset($conn)) {
    $conn->close();
}
?>