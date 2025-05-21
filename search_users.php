<?php
// Search users
require_once "db_connect.php";

$users = [];

if (isset($_GET['term'])) {
    $search_term = $conn->real_escape_string($_GET['term']);
    
    $query = "SELECT id, full_name, email, profile_image, is_active, expires_at 
              FROM users 
              WHERE full_name LIKE '%$search_term%' OR email LIKE '%$search_term%' 
              ORDER BY full_name ASC";
    $result = $conn->query($query);
    
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $users[] = $row;
        }
    }
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($users);
$conn->close();
?>