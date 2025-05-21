<?php error_reporting(E_ALL); ini_set('display_errors', 1); ?>
<!DOCTYPE html>
<html>
<head>
  <title>Pasig River Ferry Tracker - Reverse Route</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
  <style>
    #map { width: 100%; height: 100vh; }
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
const map = L.map('map').setView([14.5900, 121.0400], 13);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
  attribution: '&copy; OpenStreetMap contributors'
}).addTo(map);

const pasigRiverRoute = [
  [14.5528,121.0875],[14.5528,121.0847],[14.5533,121.0806],[14.5533,121.0764],[14.5542,121.0750],
  [14.5550,121.0731],[14.5559,121.0711],[14.5565,121.0687],[14.5567,121.0680],[14.5581,121.0669],
  [14.5597,121.0664],[14.5610,121.0650],[14.5614,121.0631],[14.5622,121.0614],[14.5644,121.0592],
  [14.5653,121.0558],[14.5661,121.0536],[14.5678,121.0511],[14.5683,121.0492],[14.5683,121.0470],
  [14.5686,121.0447],[14.5686,121.0422],[14.5675,121.0378],[14.5672,121.0347],[14.5683,121.0325],
  [14.5744,121.0256],[14.5792,121.0175],[14.5808,121.0169],[14.5831,121.0190],[14.5856,121.0203],
  [14.5870,121.0190],[14.5856,121.0142],[14.5828,121.0114],[14.5817,121.0092],[14.5828,121.0075],
  [14.5844,121.0067],[14.5864,121.0070],[14.5878,121.0086],[14.5881,121.0114],[14.5911,121.0156],
  [14.5925,121.0158],[14.5939,121.0122],[14.5964,121.0075],[14.5972,121.0044],[14.5956,121.0017],
  [14.5967,120.9983],[14.5939,120.9950],[14.5911,120.9928],[14.5897,120.9900],[14.5897,120.9872],
  [14.5911,120.9858],[14.5928,120.9836],[14.5961,120.9814],[14.5967,120.9794],[14.5953,120.9761],
  [14.5956,120.9703],[14.5958,120.9636]
];

const pasigRiverRoute2 = [
  [14.5581,121.0669],[14.5581,121.0681],[14.5586,121.0703],[14.5597,121.0721],[14.5620,121.0732],
  [14.5667,121.0736],[14.5700,121.0739],[14.5714,121.0743],[14.5754,121.0775],[14.5778,121.0803],
  [14.5803,121.0819],[14.5833,121.0828],[14.5833,121.0828],[14.5872,121.0833],[14.5928,121.0822],
  [14.5978,121.0825],[14.6025,121.0825]
];

const ferryStations = [
  { name: "Pinagbuhatan", coords: [14.5972, 121.0825] },
  { name: "Kalawaan", coords: [14.5914, 121.0825] },
  { name: "San Joaquin", coords: [14.5581, 121.0669] },
  { name: "Maybunga", coords: [14.5760, 121.0785] },
  { name: "Guadalupe", coords: [14.5672, 121.0347] },
  { name: "Hulo", coords: [14.5744, 121.0256] },
  { name: "Valenzuela", coords: [14.5835, 121.0190] },
  { name: "Lambingan", coords: [14.5869, 121.0190] },
  { name: "Santa Ana", coords: [14.5900, 121.0142] },
  { name: "PUP", coords: [14.5968, 121.0035] },
  { name: "Lawton", coords: [14.5935, 120.9838] },
  { name: "Escolta", coords: [14.5965, 120.9790] },
  { name: "Plaza Mexico", coords: [14.5957, 120.9745] }
];

ferryStations.forEach(station => {
  const marker = L.circleMarker(station.coords, {
    radius: 6,
    fillColor: "#0066ff",
    color: "#003366",
    weight: 1,
    opacity: 1,
    fillOpacity: 0.9
  }).addTo(map);
  marker.bindTooltip(station.name, {
    permanent: true,
    direction: 'top',
    className: 'ferry-label'
  });
});

const allRoutes = [pasigRiverRoute, pasigRiverRoute2];
const blinkColors = ['green', 'blue'];
let currentBlinkIndex = 0;

const routePolylines = allRoutes.map(route => L.polyline(route, {
  color: blinkColors[0],
  weight: 4,
  opacity: 0.8,
  dashArray: '5'
}).addTo(map));

setInterval(() => {
  currentBlinkIndex = (currentBlinkIndex + 1) % blinkColors.length;
  routePolylines.forEach(poly => poly.setStyle({ color: blinkColors[currentBlinkIndex] }));
}, 700);

function getDistanceKm(lat1, lon1, lat2, lon2) {
  const R = 6371;
  const dLat = (lat2 - lat1) * Math.PI/180;
  const dLon = (lon2 - lon1) * Math.PI/180;
  const a = Math.sin(dLat/2)**2 + Math.cos(lat1*Math.PI/180) * Math.cos(lat2*Math.PI/180) * Math.sin(dLon/2)**2;
  return R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
}

function lerp(a, b, t) {
  return a + (b - a) * t;
}

function speedColor(speed) {
  if (speed > 30) return '#00ff00';
  if (speed > 20) return '#ffff00';
  return '#ff0000';
}

const ferries = [];

for (let i = 0; i < 12; i++) {
  const label = `Ferry ${i + 1}`;
  const icon = L.icon({
    iconUrl: 'ship.png',
    iconSize: [30, 30],
    iconAnchor: [15, 15]
  });

  const route = allRoutes[Math.floor(Math.random() * allRoutes.length)];
  const pos = Math.floor(Math.random() * (route.length - 1));

  const marker = L.marker(route[pos], { icon, title: label }).addTo(map);

  const tooltip = marker.bindTooltip(`${label}`, {
    permanent: true,
    direction: "right",
    className: "ferry-label"
  }).getTooltip();

  marker._tooltipExpanded = false;
  marker.on('click', () => {
    marker._tooltipExpanded = !marker._tooltipExpanded;
  });

  const trail = L.polyline([], { color: '#00ff00', weight: 3, opacity: 0.7 }).addTo(map);

  ferries.push({
    marker, pos, path: route, label, tooltip, t: 0,
    baseSpeed: 40 + Math.random() * 40,
    speed: 0,
    prev: route[pos],
    next: route[(pos + 1) % route.length],
    trail, isBoarding: false, boardingTimer: 0, movingForward: true
  });
}

function animateFerries(deltaTime) {
  ferries.forEach(ferry => {
    if (ferry.isBoarding) {
      ferry.speed = 0;
      if (ferry.boardingTimer < 5) {
        ferry.boardingTimer += deltaTime;
      } else {
        ferry.isBoarding = false;
        ferry.speed = Math.min(ferry.speed + 5, ferry.baseSpeed);
        ferry.boardingTimer = 0;
      }
    } else {
      if (ferry.speed < ferry.baseSpeed) {
        ferry.speed = Math.min(ferry.speed + 5 * deltaTime, ferry.baseSpeed);
      }
    }

    ferry.t += (ferry.speed * deltaTime) / (getDistanceKm(...ferry.prev, ...ferry.next) * 3600);

    if (ferry.t >= 1) {
      ferry.t = 0;

      if (ferry.movingForward) {
        ferry.pos = (ferry.pos + 1) % ferry.path.length;
      } else {
        ferry.pos = (ferry.pos - 1 + ferry.path.length) % ferry.path.length;
      }

      ferry.prev = ferry.path[ferry.pos];
      ferry.next = ferry.path[(ferry.pos + 1) % ferry.path.length];

      ferryStations.forEach(station => {
        const distance = getDistanceKm(ferry.prev[0], ferry.prev[1], station.coords[0], station.coords[1]);
        if (distance < 0.115 && !ferry.isBoarding) {
          if (distance < 0.102) {
            ferry.speed = 0;
            ferry.isBoarding = true;
            ferry.boardingTimer = 0;
          } else {
            const slowFactor = (0.015 - distance) / 0.013;
            ferry.speed = ferry.baseSpeed * (0.3 + 0.7 * slowFactor);
          }
        }
      });

      if (ferry.pos === 0 || ferry.pos === ferry.path.length - 1) {
        ferry.movingForward = !ferry.movingForward;
      }
    }

    const lat = lerp(ferry.prev[0], ferry.next[0], ferry.t);
    const lng = lerp(ferry.prev[1], ferry.next[1], ferry.t);

    ferry.marker.setLatLng([lat, lng]);

    const content = ferry.marker._tooltipExpanded
      ? `${ferry.label}\nLat: ${lat.toFixed(4)}\nLng: ${lng.toFixed(4)}\nSpeed: ${ferry.speed.toFixed(1)} km/h`
      : ferry.label;

    ferry.tooltip.setContent(content);

    ferry.trail.addLatLng([lat, lng]);
    const latlngs = ferry.trail.getLatLngs();
    if (latlngs.length > 100) latlngs.shift();
    ferry.trail.setLatLngs(latlngs);
    ferry.trail.setStyle({ color: speedColor(ferry.speed) });
  });
}

let lastTime = performance.now();
function frame(time) {
  const deltaTime = (time - lastTime) / 1000;
  animateFerries(deltaTime);
  lastTime = time;
  requestAnimationFrame(frame);
}
requestAnimationFrame(frame);
</script>

</body>
</html>
