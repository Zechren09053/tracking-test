<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Connect to DB
$servername = "localhost";
$username = "PRFS";
$password = "1111";
$dbname = "prfs";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Make sure 'last_updated' exist
$conn->query("ALTER TABLE ferry_locations ADD COLUMN IF NOT EXISTS last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP");

// Handle location updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['latitude'], $_POST['longitude'], $_POST['code'])) {
    $latitude = floatval($_POST['latitude']);
    $longitude = floatval($_POST['longitude']);
    $code = $conn->real_escape_string($_POST['code']);

    // Check if ferry location already exists
    $check = $conn->query("SELECT id FROM ferry_locations WHERE code='$code'");
    
    // Update if ferry exists, otherwise insert new record
    if ($check && $check->num_rows > 0) {
        // Update existing ferry location
        $conn->query("UPDATE ferry_locations SET latitude='$latitude', longitude='$longitude', last_updated=NOW() WHERE code='$code'");
    } else {
        // Insert new ferry location if it doesn't exist
        $conn->query("INSERT INTO ferry_locations (code, latitude, longitude) VALUES ('$code', '$latitude', '$longitude')");
    }
    exit;
}

// Fetch active ferries
if (isset($_GET['fetch']) && $_GET['fetch'] == '1') {
    // Get ferries that were updated within the last 10 seconds
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
  <title>PRFS Ferry Tracker</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <style>
    body, html { margin: 0; padding: 0; height: 100%; }
    #map { width: 100%; height: 100vh; }
    #info { position: fixed; top: 10px; left: 10px; background: rgba(255,255,255,0.8); padding: 10px; border-radius: 8px; font-family: sans-serif; }
  </style>
</head>
<body>

<div id="map"></div>
<div id="info">
  <b>Your Location</b><br>
  Latitude: <span id="lat">---</span><br>
  Longitude: <span id="lng">---</span>
</div>

<script>
// Set up the map
const map = L.map('map').setView([14.5896, 121.0359], 14);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
  attribution: '© OpenStreetMap contributors'
}).addTo(map);

// Adjust the icon size to match your image's aspect ratio
const ferryIcon = L.icon({
  iconUrl: 'pin.png',
  iconSize: [30, 30],  // Adjust size to prevent stretching
  iconAnchor: [15, 30], // This anchors the marker's point to the bottom
  popupAnchor: [0, -30]
});


// Your ferry code
const ferryCode = prompt("Enter your Ferry Code:", "PRFS001") || "PRFS001";

// Your marker
let myMarker = L.marker([0, 0], {icon: ferryIcon}).addTo(map).bindPopup("You").openPopup();

// Others markers
let otherMarkers = {};

const latEl = document.getElementById('lat');
const lngEl = document.getElementById('lng');

let lastLat = null, lastLng = null;

// Update your location
function updateLocation(lat, lng) {
  $.post('', {
    latitude: lat,
    longitude: lng,
    code: ferryCode
  }).fail(() => {
    console.error("Failed to update location.");
  });
}

// Create or move a marker for other ferries
function updateFerryMarker(code, lat, lng) {
  if (!otherMarkers[code]) {
    const marker = L.marker([lat, lng], {icon: ferryIcon}).addTo(map);
    marker.bindPopup(`Ferry: ${code}`);
    otherMarkers[code] = marker;
  } else {
    otherMarkers[code].setLatLng([lat, lng]);
  }
}

// Remove offline ferries
function cleanupMarkers(activeCodes) {
  for (const code in otherMarkers) {
    if (!activeCodes.includes(code)) {
      map.removeLayer(otherMarkers[code]);
      delete otherMarkers[code];
    }
  }
}

// Start GPS tracking
if (navigator.geolocation) {
  navigator.geolocation.watchPosition(
    pos => {
      const lat = pos.coords.latitude;
      const lng = pos.coords.longitude;
      latEl.textContent = lat.toFixed(6);
      lngEl.textContent = lng.toFixed(6);

      myMarker.setLatLng([lat, lng]).bindPopup(`You (${ferryCode})<br>Lat: ${lat.toFixed(6)}<br>Lng: ${lng.toFixed(6)}`).openPopup();


      // Automatically center the map on the user's location
      map.setView([lat, lng], map.getZoom(), { animate: true });

      if (lastLat !== lat || lastLng !== lng) {
        lastLat = lat;
        lastLng = lng;
        updateLocation(lat, lng);
      }
    },
    err => alert("GPS Error: " + err.message),
    { enableHighAccuracy: true, maximumAge: 0, timeout: 10000 }
  );
} else {
  alert("Geolocation is not supported.");
}

// Poll every 2 seconds
setInterval(() => {
  if (lastLat !== null && lastLng !== null) {
    updateLocation(lastLat, lastLng);
  }

  $.getJSON('?fetch=1', data => {
    const activeCodes = [];
    data.forEach(ferry => {
      if (ferry.code !== ferryCode) { // Don't create a second marker for yourself
        updateFerryMarker(ferry.code, ferry.latitude, ferry.longitude);
      }
      activeCodes.push(ferry.code);
    });
    cleanupMarkers(activeCodes);
  });
}, 2000);
</script>

</body>
</html>
