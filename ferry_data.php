<?php
// Database connection This file fetches the ferry data from your database and returns it as a JSON response for the AJAX call.
$servername = "localhost";
$db_username = "PRFS";
$db_password = "1111";
$dbname = "prfs";

// Create connection
try {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $db_username, $db_password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
    exit;
}

// Fetch ferry data
$stmt = $pdo->query("SELECT id, name, latitude, longitude, status, current_capacity, max_capacity, speed FROM ferries");
$ferries = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Return the data as JSON
header('Content-Type: application/json');
echo json_encode($ferries);
?>