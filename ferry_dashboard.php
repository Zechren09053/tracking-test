<?php
// Database connection
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
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Pasig River Ferry Tracker - Party Mode</title>
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <style>
    #map { height: 90vh; }
    .ferry-label {
      background-color: #fff;
      border-radius: 4px;
      padding: 2px 6px;
      font-weight: bold;
      font-size: 12px;
      color: #333;
      border: 1px solid #aaa;
      white-space: pre;
      cursor: pointer;
    }
  </style>
</head>
<body>
  <div id="map"></div>

  <script>
    // Initialize the map
    const map = L.map('map').setView([14.5900, 121.0400], 13);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    // Store ferry markers and data
    const ferries = {};

    // Function to fetch ferry data from the server every second using AJAX
    function refreshFerries() {
      $.ajax({
        url: 'ferry_data.php',  // File that fetches ferry data
        type: 'GET',
        dataType: 'json',
        success: function(data) {
          // Update ferries based on fetched data
          data.forEach(ferry => {
            if (!ferries[ferry.id]) {
              // Create new marker for the ferry
              const label = `${ferry.name} - ${ferry.status}`;
              const icon = L.icon({
                iconUrl: 'ship.png',  // Ensure you have the ship icon in the same directory
                iconSize: [30, 30],
                iconAnchor: [15, 15]
              });

              const marker = L.marker([ferry.latitude, ferry.longitude], { icon, title: label }).addTo(map);
              const tooltipContent = `
                ${label}
                <br>Capacity: ${ferry.current_capacity}/${ferry.max_capacity}
                <br>Speed: ${ferry.speed} km/h
              `;
              const tooltip = marker.bindTooltip(tooltipContent, {
                permanent: true,
                direction: "right",
                className: "ferry-label"
              }).getTooltip();

              ferries[ferry.id] = { marker, lat: ferry.latitude, lng: ferry.longitude, speed: ferry.speed, status: ferry.status };
            } else {
              // Update position of existing marker
              const ferryObj = ferries[ferry.id];
              if (ferryObj) {
                ferryObj.lat = ferry.latitude;
                ferryObj.lng = ferry.longitude;
                ferryObj.speed = ferry.speed;
                ferryObj.status = ferry.status;

                // Update the ferry marker's position
                ferryObj.marker.setLatLng([ferry.latitude, ferry.longitude]);

                // Update ferry tooltip content (status and speed)
                const tooltipContent = `
                  ${ferry.name} - ${ferry.status}
                  <br>Capacity: ${ferry.current_capacity}/${ferry.max_capacity}
                  <br>Speed: ${ferry.speed} km/h
                `;
                ferryObj.marker.getTooltip().setContent(tooltipContent);
              }
            }
          });
        },
        error: function() {
          console.log("Error fetching ferry data.");
        }
      });
    }

    // Refresh ferry data every 1 second
    setInterval(refreshFerries, 1000);

    // Simulate ferry movement based on speed (update coordinates)
    function simulateMovement() {
      Object.keys(ferries).forEach(id => {
        const ferry = ferries[id];
        if (ferry.status === 'active') {
          const deltaLat = (Math.random() * 0.0001) - 0.00005;  // Small random change for lat
          const deltaLng = (Math.random() * 0.0001) - 0.00005;  // Small random change for lon

          ferry.lat += deltaLat;
          ferry.lng += deltaLng;

          // Move marker on the map
          ferry.marker.setLatLng([ferry.lat, ferry.lng]);
        }
      });
    }

    // Simulate movement every second
    setInterval(simulateMovement, 1000);
  </script>

</body>
</html>
