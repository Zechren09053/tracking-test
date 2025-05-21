<?php
// Database connection settings
$servername = "localhost";
$username = "PRFS";
$password = "1111";
$dbname = "prfs";

// Create a new MySQLi connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check for connection errors
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Get the ferry ID and new status from the POST request
if (isset($_POST['ferry_id']) && isset($_POST['status'])) {
    $ferryId = $_POST['ferry_id'];
    $status = $_POST['status'];

    // Prepare the SQL query to update the ferry status
    $sql = "UPDATE ferries SET status = ? WHERE id = ?";

    // Initialize a prepared statement
    if ($stmt = $conn->prepare($sql)) {
        // Bind the parameters (status and ferry ID)
        $stmt->bind_param("si", $status, $ferryId);

        // Execute the query
        if ($stmt->execute()) {
            // Return success response
            echo json_encode(['status' => 'success', 'message' => 'Ferry status updated successfully']);
        } else {
            // Return failure response
            echo json_encode(['status' => 'error', 'message' => 'Failed to update ferry status']);
        }

        // Close the statement
        $stmt->close();
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to prepare the SQL query']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Missing ferry ID or status']);
}

// Close the connection
$conn->close();
?>
