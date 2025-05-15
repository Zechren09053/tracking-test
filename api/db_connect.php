<?php
// Database connection configuration file
$servername = "localhost";
$username = "PRFS";
$password = "1111";
$dbname = "prfs";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}