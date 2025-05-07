<?php
session_start();

// Database connection details
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

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize and get form data
    $ferry_name = mysqli_real_escape_string($conn, $_POST['ferry_name']);
    $ferry_operator = mysqli_real_escape_string($conn, $_POST['ferry_operator']);
    $ferry_max_capacity = (int)$_POST['ferry_max_capacity'];

    // Prepare the SQL statement to insert the new ferry
    $sql = "INSERT INTO ferries (name, operator, max_capacity) 
            VALUES ('$ferry_name', '$ferry_operator', '$ferry_max_capacity')";

    if ($conn->query($sql) === TRUE) {
        echo "New ferry registered successfully!";
        header("Location: ferrymngt.php"); // Redirect to ferry management page after successful registration
        exit();
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}

// Close the connection
$conn->close();
?>
