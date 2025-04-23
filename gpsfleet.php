<?php error_reporting(E_ALL); ini_set('display_errors', 1); ?>
<!DOCTYPE html>
<html>
<head>
  <title>Pasig River Ferry Fleet Tracker</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
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
    }
  </style>
</head>
<body>

<div id="map"></div>
<script>
const map = L.map('map').setView([14.5900, 121.0400], 13);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
  attribution: '&copy; OpenStreetMap contributors'
}).addTo(map);

const pasigRoute = [
  [14.5528, 121.0875], [14.5528, 121.0847], [14.5533, 121.0806], [14.5533, 121.0764], [14.5542, 121.0750],
  [14.5550, 121.0731], [14.5559, 121.0711], [14.5565, 121.0687], [14.5567, 121.0680], [14.5581, 121.0669],
  [14.5597, 121.0664], [14.5610, 121.0650], [14.5614, 121.0631], [14.5622, 121.0614], [14.5644, 121.0592],
  [14.5653, 121.0558], [14.5661, 121.0536], [14.5678, 121.0511], [14.5683, 121.0492], [14.5683, 121.0470],
  [14.5686, 121.0447], [14.5686, 121.0422], [14.5675, 121.0378], [14.5672, 121.0347], [14.5683, 121.0325],
  [14.5744, 121.0256], [14.5792, 121.0175], [14.5808, 121.0169], [14.5831, 121.0190], [14.5856, 121.0203],
  [14.5870, 121.0190], [14.5856, 121.0142], [14.5828, 121.0114], [14.5817, 121.0092], [14.5828, 121.0075],
  [14.5844, 121.0067], [14.5864, 121.0070], [14.5878, 121.0086], [14.5881, 121.0114], [14.5911, 121.0156],
  [14.5925, 121.0158], [14.5939, 121.0122], [14.5964, 121.0075], [14.5972, 121.0044], [14.5956, 121.0017],
  [14.5967, 120.9983], [14.5939, 120.9950], [14.5911, 120.9928], [14.5897, 120.9900], [14.5897, 120.9872],
  [14.5911, 120.9858], [14.5928, 120.9836], [14.5961, 120.9814], [14.5967, 120.9794], [14.5953, 120.9761],
  [14.5956, 120.9703], [14.5958, 120.9636]
];

const iconUrls = [
  'https://cdn-icons-png.flaticon.com/512/684/684908.png',
  'https://cdn-icons-png.flaticon.com/512/684/684912.png',
  'https://cdn-icons-png.flaticon.com/512/684/684911.png',
  'https://cdn-icons-png.flaticon.com/512/684/684909.png',
  'https://cdn-icons-png.flaticon.com/512/684/684910.png',
  'https://cdn-icons-png.flaticon.com/512/684/684913.png'
];

const ferries = [];

for (let i = 0; i < 6; i++) {
  const label = `Ferry ${i + 1}`;
  const icon = L.icon({
    iconUrl: iconUrls[i],
    iconSize: [25, 25],
    iconAnchor: [12, 12]
  });

  const marker = L.marker(pasigRoute[0], {
    title: label,
    icon: icon
  }).addTo(map);

  const tooltip = marker.bindTooltip(`${label}\nLat: --\nLng: --`, {
    permanent: true,
    direction: "right",
    className: "ferry-label"
  }).getTooltip();

  const routePath = i < 3 ? [...pasigRoute] : [...pasigRoute].reverse();

  ferries.push({ marker, pos: 0, path: routePath, label, tooltip });
}

ferries.forEach((ferry, i) => {
  setInterval(() => {
    const coords = ferry.path[ferry.pos];
    ferry.marker.setLatLng(coords);
    const [lat, lng] = coords;
    ferry.tooltip.setContent(`${ferry.label}\nLat: ${lat.toFixed(4)}\nLng: ${lng.toFixed(4)}`);
    ferry.pos = (ferry.pos + 1) % ferry.path.length;
  }, 1000 + i * 400);
});
</script>

</body>
</html>
