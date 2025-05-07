// ferryStations.js

var map = L.map('map').setView([14.58, 121.02], 13);

var markers = {};
var ferryStations = [
    { name: "Pinagbuhatan", lat: 14.5874, lng: 121.0897 },
    { name: "Maybunga", lat: 14.5776, lng: 121.0952 },
    { name: "San Joaquin", lat: 14.5787, lng: 121.0816 },
    { name: "Kalawaan", lat: 14.5659, lng: 121.0665 },
    { name: "Guadalupe", lat: 14.5611, lng: 121.0458 },
    { name: "Valenzuela", lat: 14.5577, lng: 121.0399 },
    { name: "Hulo", lat: 14.5683, lng: 121.0366 },
    { name: "Lambingan", lat: 14.5889, lng: 121.0077 },
    { name: "Sta. Ana", lat: 14.5871, lng: 121.0048 },
    { name: "PUP", lat: 14.5985, lng: 121.0038 },
    { name: "Lawton", lat: 14.5907, lng: 120.9839 },
    { name: "Quinta", lat: 14.5981, lng: 120.9832 },
    { name: "Escolta", lat: 14.5992, lng: 120.9796 }
];

// Add OpenStreetMap tiles
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; OpenStreetMap contributors'
}).addTo(map);

// Plot markers
ferryStations.forEach(station => {
    let marker = L.marker([station.lat, station.lng]).addTo(map)
        .bindPopup(`<strong>${station.name}</strong>`);
    markers[station.name] = marker;
});

// Draw route line connecting stations
let routeCoords = ferryStations.map(station => [station.lat, station.lng]);
let routeLine = L.polyline(routeCoords, {
    color: 'dodgerblue',
    weight: 4,
    opacity: 0.8
}).addTo(map);

// Fit the map view to the route bounds
map.fitBounds(routeLine.getBounds());
