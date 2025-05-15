<?php
// Verify QR code and update last used timestamp
require_once "db_connect.php";

// Get JSON data
$data = json_decode(file_get_contents('php://input'), true);
$response = ['valid' => false, 'user' => null];

if ($data && isset($data['id']) && isset($data['qr_code_data'])) {
    $id = intval($data['id']);
    $qr_code_data = $conn->real_escape_string($data['qr_code_data']);
    
    // Find user with matching ID and QR code data
    $query = "SELECT * FROM users WHERE id = $id AND qr_code_data = '$qr_code_data'";
    $result = $conn->query($query);
    
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
        // Update last_used timestamp
        $update = "UPDATE users SET last_used = NOW() WHERE id = $id";
        $conn->query($update);
        
        // Get updated user data
        $query = "SELECT * FROM users WHERE id = $id";
        $result = $conn->query($query);
        $updated_user = $result->fetch_assoc();
        
        $response['valid'] = true;
        $response['user'] = $updated_user;
    }
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);
$conn->close();
?>