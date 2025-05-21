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

// Create 'last_updated' if not present
$conn->query("ALTER TABLE ferry_locations ADD COLUMN IF NOT EXISTS last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP");

// Fetch active ferries
if (isset($_GET['fetch']) && $_GET['fetch'] == '1') {
    $result = $conn->query("SELECT code, latitude, longitude FROM ferry_locations WHERE last_updated > (NOW() - INTERVAL 10 SECOND)");
    $locations = [];
    while ($row = $result->fetch_assoc()) {
        $locations[] = $row;
    }
    header('Content-Type: application/json');
    echo json_encode($locations);
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
  <title>Live Ferry Monitor</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <style>
    html, body { margin: 0; padding: 0; height: 100%; }
    #map { width: 100%; height: 100vh; }
  </style>
</head>
<body>

<div id="map"></div>

<script>
// Set up Leaflet map
const map = L.map('map').setView([14.5896, 121.0359], 14);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
  attribution: '© OpenStreetMap contributors'
}).addTo(map);

// Custom ferry icon
const ferryIcon = L.icon({
  iconUrl: 'pin.png',
  iconSize: [30, 30],
  iconAnchor: [15, 30],
  popupAnchor: [0, -30]
});

// Track all ferry markers
const ferryMarkers = {};

// Update or add ferry marker
function updateFerryMarker(code, lat, lng) {
  if (!ferryMarkers[code]) {
    const marker = L.marker([lat, lng], {icon: ferryIcon}).addTo(map);
    marker.bindPopup(`Ferry: ${code}`);
    ferryMarkers[code] = marker;
  } else {
    ferryMarkers[code].setLatLng([lat, lng]);
  }
}

// Remove offline ferries
function cleanupMarkers(activeCodes) {
  for (const code in ferryMarkers) {
    if (!activeCodes.includes(code)) {
      map.removeLayer(ferryMarkers[code]);
      delete ferryMarkers[code];
    }
  }
}

// Fetch ferry data every 2s
setInterval(() => {
  $.getJSON('?fetch=1', data => {
    const activeCodes = [];
    data.forEach(ferry => {
      updateFerryMarker(ferry.code, ferry.latitude, ferry.longitude);
      activeCodes.push(ferry.code);
    });
    cleanupMarkers(activeCodes);
  });
}, 2000);
</script>

</body>
</html>
