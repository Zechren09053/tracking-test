<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Ferry Map</title>
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
  <style>
    html, body { height: 100%; margin: 0; }
    #map { width: 100%; height: 100%; }
    .ferry-label { font-weight: bold; font-size: 12px; background: #fff; padding: 2px 4px; border-radius: 4px; }
  </style>
</head>
<body>
  <div id="map"></div>

  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
  <script>
    var map = L.map('map').setView([14.5896, 121.0360], 13);
    var markers = {};

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

    // Draw stations
    ferryStations.forEach(station => {
      const marker = L.circleMarker(station.coords, {
        radius: 6,
        fillColor: "#0066ff",
        color: "#003366",
        weight: 1,
        opacity: 1,
        fillOpacity: 0.9
      }).addTo(map);
      marker.bindTooltip(station.name, { permanent: true, direction: 'top', className: 'ferry-label' });
    });

    // Draw route
    L.polyline(pasigRiverRoute, { color: 'blue', weight: 4, opacity: 0.7 }).addTo(map);
    L.polyline(pasigRiverRoute2, { color: 'blue', weight: 4, opacity: 0.7 }).addTo(map);
    map.fitBounds([pasigRiverRoute, pasigRiverRoute2]);

    function addFerryMarker(lat, lng, name) {
      const ferryIcon = L.icon({
        iconUrl: 'ship.png',
        iconSize: [32, 32],
        iconAnchor: [16, 16],
        popupAnchor: [0, -16]
      });

      const marker = L.marker([lat, lng], { icon: ferryIcon }).addTo(map)
        .bindTooltip(name, { permanent: true, direction: 'top', className: 'ferry-label' });

      markers[name] = marker;
    }

    function fetchFerryData() {
  $.getJSON('getFerries.php', function(data) {
    const activeFerries = new Set();

    data.forEach(ferry => {
      if (ferry.status === "active" && ferry.latitude && ferry.longitude) {
        activeFerries.add(ferry.name);

        if (!markers[ferry.name]) {
          addFerryMarker(ferry.latitude, ferry.longitude, ferry.name);
        } else {
          markers[ferry.name].setLatLng([ferry.latitude, ferry.longitude]);
        }
      }
    });

    // Remove inactive ferries from map
    for (let name in markers) {
      if (!activeFerries.has(name)) {
        map.removeLayer(markers[name]);
        delete markers[name];
      }
    }
  });
}


    setInterval(fetchFerryData, 5000);
    fetchFerryData();
  </script>
</body>
</html>
