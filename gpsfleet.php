<?php error_reporting(E_ALL); ini_set('display_errors', 1); ?>
<!DOCTYPE html>
<html>
<head>
  <title>Fake Ferry Fleet GPS</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
  <style>
    #map { height: 90vh; }
    #info { padding: 10px; font-family: sans-serif; background: #f0f0f0; }
    .ferry-label {
  background-color: #fff;
  border-radius: 4px;
  padding: 2px 6px;
  font-weight: bold;
  font-size: 12px;
  color: #333;
  border: 1px solid #aaa;
}

  </style>
</head>
<body>

<div id="map"></div>
<div id="info">
  <strong>Fake Fleet Active:</strong> 3 Ferries<br>
  <em>They loop continuously on different tracks.</em>
</div>

<script>
  const map = L.map('map').setView([14.5900, 121.0400], 14);
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; OpenStreetMap contributors'
  }).addTo(map);

  // Fleet markers
  const ferry1 = L.marker([0, 0], { title: "Ferry 1" })
  .addTo(map)
  .bindTooltip("Ferry 1", { permanent: true, direction: "right", className: "ferry-label" });

const ferry2 = L.marker([0, 0], {
    title: "Ferry 2",
    icon: L.icon({
      iconUrl: 'https://cdn-icons-png.flaticon.com/512/684/684908.png',
      iconSize: [25, 25]
    })
  })
  .addTo(map)
  .bindTooltip("Ferry 2", { permanent: true, direction: "right", className: "ferry-label" });

const ferry3 = L.marker([0, 0], {
    title: "Ferry 3",
    icon: L.icon({
      iconUrl: 'https://cdn-icons-png.flaticon.com/512/684/684912.png',
      iconSize: [25, 25]
    })
  })
  .addTo(map)
  .bindTooltip("Ferry 3", { permanent: true, direction: "right", className: "ferry-label" });

  // Fake routes
  const routes = [
    [ // Ferry 1
      [14.5896, 121.0359],
      [14.5905, 121.0380],
      [14.5910, 121.0400],
      [14.5920, 121.0420],
      [14.5935, 121.0450]
    ],
    [ // Ferry 2
      [14.5950, 121.0500],
      [14.5960, 121.0530],
      [14.5968, 121.0550],
      [14.5975, 121.0580],
      [14.5980, 121.0600]
    ],
    [ // Ferry 3
      [14.5910, 121.0370],
      [14.5922, 121.0385],
      [14.5933, 121.0401],
      [14.5944, 121.0417],
      [14.5955, 121.0433]
    ]
  ];

  let i1 = 0, i2 = 0, i3 = 0;

  function moveFerry(marker, route, iVar) {
    if (iVar >= route.length) iVar = 0;
    const [lat, lng] = route[iVar];
    marker.setLatLng([lat, lng]);
    return iVar + 1;
  }

  setInterval(() => { i1 = moveFerry(ferry1, routes[0], i1); }, 2000);
  setInterval(() => { i2 = moveFerry(ferry2, routes[1], i2); }, 3000);
  setInterval(() => { i3 = moveFerry(ferry3, routes[2], i3); }, 2500);
</script>

</body>
</html>
