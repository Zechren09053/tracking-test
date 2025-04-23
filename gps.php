<?php error_reporting(E_ALL); ini_set('display_errors', 1); ?>
<!DOCTYPE html>
<html>
<head>
  <title>Ferry GPS Tracker</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
  <style>
    #map { height: 90vh; }
    #info { padding: 10px; font-family: sans-serif; background: #f0f0f0; }
  </style>
</head>
<body>

<div id="map"></div>
<div id="info">
  <strong>Latitude:</strong> <span id="lat">...</span><br>
  <strong>Longitude:</strong> <span id="lng">...</span>
</div>

<script>
  const map = L.map('map').setView([14.5896, 121.0359], 14);
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; OpenStreetMap contributors'
  }).addTo(map);

  const marker = L.marker([0, 0]).addTo(map);
  const latEl = document.getElementById("lat");
  const lngEl = document.getElementById("lng");

  if ("geolocation" in navigator) {
    navigator.geolocation.watchPosition(
      (pos) => {
        const lat = pos.coords.latitude;
        const lng = pos.coords.longitude;
        latEl.textContent = lat.toFixed(6);
        lngEl.textContent = lng.toFixed(6);
        marker.setLatLng([lat, lng]);
        map.setView([lat, lng], 16);
      },
      (err) => {
        alert("GPS Error: " + err.message);
      },
      {
        enableHighAccuracy: true,
        maximumAge: 0,
        timeout: 10000
      }
    );
  } else {
    alert("Your browser doesn't support GPS.");
  }
</script>

</body>
</html>
