<?php
// Set the content type to JSON
header('Content-Type: application/json');

// Database credentials
$servername = "localhost";
$username = "PRFS"; // Default username for XAMPP
$password = "1111"; // Default password for XAMPP
$dbname = "prfs"; // The name of your database

// Create connection to the MySQL database
$conn = new mysqli($servername, $username, $password, $dbname);

// Check if the connection is successful
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// SQL query to fetch ferry data from the ferries table
$sql = "SELECT id, name, latitude, longitude, last_updated FROM ferries";
$result = $conn->query($sql);

// Initialize an empty array to hold the ferry data
$ferries = [];

// Check if there are rows returned from the query
if ($result->num_rows > 0) {
    // Fetch each row and add it to the $ferries array
    while ($row = $result->fetch_assoc()) {
        $ferries[] = $row;
    }
}

// Close the database connection
$conn->close();

// Return the ferry data as JSON
echo json_encode($ferries);
?>
