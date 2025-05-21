<?php
// Get all users from the database
require_once "db_connect.php";

$query = "SELECT id, full_name, email, profile_image, is_active, expires_at FROM users ORDER BY created_at DESC";
$result = $conn->query($query);

$users = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($users);
$conn->close();
?>