<?php error_reporting(E_ALL); ini_set('display_errors', 1); ?>
<!DOCTYPE html>
<html>
<head>
  <title>Fake Ferry GPS Movement</title>
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

  const fakeRoute = [
    [14.5896, 121.0359],
    [14.5905, 121.0380],
    [14.5910, 121.0400],
    [14.5920, 121.0420],
    [14.5935, 121.0450],
    [14.5945, 121.0470],
    [14.5950, 121.0500],
    [14.5960, 121.0530],
    [14.5968, 121.0550],
    [14.5975, 121.0580]
  ];

  let index = 0;

  function updateFakeGPS() {
    if (index >= fakeRoute.length) index = 0;

    const [lat, lng] = fakeRoute[index];
    latEl.textContent = lat.toFixed(6);
    lngEl.textContent = lng.toFixed(6);
    marker.setLatLng([lat, lng]);
    map.setView([lat, lng], 16);

    index++;
  }

  updateFakeGPS(); // init
  setInterval(updateFakeGPS, 2000);
</script>

</body>
</html>
