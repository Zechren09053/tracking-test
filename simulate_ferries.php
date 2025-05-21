<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$servername = "localhost";
$username = "PRFS";
$password = "1111";
$dbname = "prfs";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Define route
$route = [
    [14.5528, 121.0875],[14.5528,121.0847],[14.5533,121.0806],[14.5533,121.0764],
    [14.5542,121.0750],[14.5550,121.0731],[14.5559,121.0711],[14.5565,121.0687],
    [14.5567,121.0680],[14.5581,121.0669],[14.5597,121.0664],[14.5610,121.0650],
    [14.5614,121.0631],[14.5622,121.0614],[14.5644,121.0592],[14.5653,121.0558]
];

// Random start indexes saved per ferry
$ferry_positions = [];

// Fetch active ferries
$sql = "SELECT id FROM ferries WHERE status = 'active'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($ferry = $result->fetch_assoc()) {
        $ferry_id = $ferry['id'];

        // Each ferry gets their own position session
        $pos_file = __DIR__ . "/pos_$ferry_id.json";

        if (!file_exists($pos_file)) {
            $start_index = rand(0, count($route) - 2);
            file_put_contents($pos_file, json_encode($start_index));
        } else {
            $start_index = json_decode(file_get_contents($pos_file), true);
            $start_index = ($start_index + 1) % count($route);
            file_put_contents($pos_file, json_encode($start_index));
        }

        // Calculate new position
        $newLat = $route[$start_index][0];
        $newLng = $route[$start_index][1];

        // Update ferry position in database
        $update = $conn->prepare("UPDATE ferries SET latitude = ?, longitude = ?, last_updated = NOW() WHERE id = ?");
        $update->bind_param("ddi", $newLat, $newLng, $ferry_id);
        $update->execute();

        echo "Ferry ID: $ferry_id moved to: $newLat, $newLng<br>";
    }
} else {
    echo "No active ferries.";
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pasig River Ferry Tracker</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <style>
        #map { height: 90vh; }
    </style>
</head>
<body>
    <div id="map"></div>
    <script>
        const map = L.map('map').setView([14.5900, 121.0400], 13);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);

        const ferries = [];

        // Function to fetch and update ferry positions
        function updateFerries() {
            fetch('<?= $_SERVER['PHP_SELF'] ?>') // This calls the current PHP file
                .then(response => response.text())
                .then(data => {
                    console.log('Ferries updated:', data);
                    // Optionally, you could update the ferry markers on the map here
                    // by fetching updated positions from the DB and moving the markers
                    // For example, you could use AJAX to get updated data if needed
                })
                .catch(error => console.error('Error updating ferries:', error));
        }

        // Update ferries every 5 seconds (5000 ms)
        setInterval(updateFerries, 5000);  // Adjust this interval as needed

    </script>
</body>
</html>
