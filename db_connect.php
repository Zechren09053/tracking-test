<?php
$servername = "localhost";
$db_username = "PRFS";
$db_password = "1111";
$dbname = "prfs";

// Create a connection
$conn = new mysqli($servername, $db_username, $db_password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
