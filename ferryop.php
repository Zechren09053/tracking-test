<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>PRFS Ferry Operator App</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
  <meta name="theme-color" content="#0066CC">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
  
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  
  <style>
    :root {
      --primary-color: #0066CC;
      --accent-color: #00AAFF;
      --danger-color: #FF3B30;
      --success-color: #34C759;
      --warning-color: #FFCC00;
      --text-color: #333333;
      --text-secondary: #666666;
      --bg-color: #F2F2F7;
      --card-bg: #FFFFFF;
    }
    
    * {
      box-sizing: border-box;
      -webkit-tap-highlight-color: transparent;
    }
    
    body, html {
      margin: 0;
      padding: 0;
      height: 100%;
      font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
      background-color: var(--bg-color);
      color: var(--text-color);
      overflow: hidden;
    }
    
    #app-container {
      display: flex;
      flex-direction: column;
      height: 100%;
      width: 100%;
      position: relative;
    }
    
    #map {
      flex-grow: 1;
      width: 100%;
      height: calc(100% - 60px);
      z-index: 1;
    }
    
    .app-header {
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      height: 60px;
      background-color: var(--primary-color);
      color: white;
      display: flex;
      align-items: center;
      padding: 0 16px;
      z-index: 1000;
      box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    .app-title {
      font-size: 18px;
      font-weight: 600;
      flex-grow: 1;
    }
    
    .header-button {
      width: 40px;
      height: 40px;
      display: flex;
      align-items: center;
      justify-content: center;
      border-radius: 50%;
      background-color: rgba(255,255,255,0.2);
      margin-left: 10px;
      cursor: pointer;
      transition: background-color 0.2s;
    }
    
    .header-button:active {
      background-color: rgba(255,255,255,0.3);
    }
    
    .bottom-panel {
      position: fixed;
      bottom: 0;
      left: 0;
      right: 0;
      background-color: var(--card-bg);
      border-radius: 20px 20px 0 0;
      box-shadow: 0 -2px 10px rgba(0,0,0,0.1);
      z-index: 100;
      padding: 20px;
      transform: translateY(85%);
      transition: transform 0.3s ease-out;
      height: 70%;
      overflow-y: auto;
    }
    
    .bottom-panel.expanded {
      transform: translateY(0);
    }
    
    .panel-handle {
      width: 40px;
      height: 5px;
      background-color: #DDDDDD;
      border-radius: 3px;
      margin: -5px auto 15px;
    }
    
    .info-card {
      background-color: var(--card-bg);
      border-radius: 10px;
      padding: 15px;
      margin-bottom: 10px;
      box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }
    
    .card-title {
      font-weight: 600;
      margin-bottom: 10px;
      color: var(--primary-color);
      font-size: 16px;
    }
    
    .info-row {
      display: flex;
      justify-content: space-between;
      margin-bottom: 8px;
    }
    
    .info-label {
      color: var(--text-secondary);
      font-size: 14px;
    }
    
    .info-value {
      font-weight: 500;
      font-size: 14px;
    }
    
    .status-indicator {
      display: inline-block;
      width: 10px;
      height: 10px;
      border-radius: 50%;
      margin-right: 5px;
    }
    
    .status-active {
      background-color: var(--success-color);
    }
    
    .status-warning {
      background-color: var(--warning-color);
    }
    
    .status-offline {
      background-color: var(--danger-color);
    }
    
    .button {
      background-color: var(--primary-color);
      color: white;
      border: none;
      border-radius: 8px;
      padding: 12px 16px;
      font-size: 16px;
      font-weight: 500;
      width: 100%;
      cursor: pointer;
      margin-top: 10px;
      transition: background-color 0.2s;
    }
    
    .button:active {
      background-color: #0055AA;
    }
    
    .tab-buttons {
      display: flex;
      margin-bottom: 15px;
    }
    
    .tab-button {
      flex: 1;
      padding: 10px;
      text-align: center;
      background-color: #EEEEEE;
      cursor: pointer;
    }
    
    .tab-button:first-child {
      border-radius: 8px 0 0 8px;
    }
    
    .tab-button:last-child {
      border-radius: 0 8px 8px 0;
    }
    
    .tab-button.active {
      background-color: var(--primary-color);
      color: white;
    }
    
    .tab-content {
      display: none;
    }
    
    .tab-content.active {
      display: block;
    }
    
    .ferry-list {
      list-style: none;
      padding: 0;
      margin: 0;
    }
    
    .ferry-item {
      display: flex;
      align-items: center;
      padding: 12px;
      border-bottom: 1px solid #EEEEEE;
    }
    
    .ferry-icon {
      width: 40px;
      height: 40px;
      background-color: var(--primary-color);
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      color: white;
      margin-right: 12px;
    }
    
    .ferry-info {
      flex-grow: 1;
    }
    
    .ferry-code {
      font-weight: 600;
      font-size: 16px;
    }
    
    .ferry-status {
      color: var(--text-secondary);
      font-size: 14px;
    }
    
    .ferry-action {
      padding: 8px;
      border-radius: 50%;
      background-color: #EEEEEE;
    }
    
    #floating-info {
      position: fixed;
      top: 70px;
      left: 10px;
      background-color: white;
      border-radius: 8px;
      padding: 10px;
      box-shadow: 0 2px 10px rgba(0,0,0,0.1);
      font-size: 14px;
      z-index: 10;
      max-width: 200px;
      opacity: 0.9;
    }
    
    .alert {
      position: fixed;
      top: 70px;
      left: 50%;
      transform: translateX(-50%);
      padding: 10px 15px;
      border-radius: 8px;
      background-color: var(--success-color);
      color: white;
      z-index: 1000;
      font-weight: 500;
      box-shadow: 0 2px 10px rgba(0,0,0,0.2);
      opacity: 0;
      transition: opacity 0.3s;
    }
    
    .alert.show {
      opacity: 1;
    }
    
    .modal {
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background-color: rgba(0,0,0,0.5);
      display: flex;
      align-items: center;
      justify-content: center;
      z-index: 2000;
      opacity: 0;
      pointer-events: none;
      transition: opacity 0.3s;
    }
    
    .modal.active {
      opacity: 1;
      pointer-events: auto;
    }
    
    .modal-content {
      background-color: white;
      border-radius: 16px;
      padding: 20px;
      width: 300px;
      max-width: 90%;
    }
    
    .modal-title {
      font-size: 18px;
      font-weight: 600;
      margin-bottom: 15px;
    }
    
    .form-group {
      margin-bottom: 15px;
    }
    
    .form-label {
      display: block;
      margin-bottom: 5px;
      font-weight: 500;
    }
    
    .form-input {
      width: 100%;
      padding: 12px;
      border: 1px solid #DDDDDD;
      border-radius: 8px;
      font-size: 16px;
    }
    
    .modal-buttons {
      display: flex;
      justify-content: space-between;
    }
    
    .modal-button {
      flex: 1;
      padding: 12px;
      margin: 0 5px;
      border-radius: 8px;
      border: none;
      font-size: 16px;
      font-weight: 500;
      cursor: pointer;
    }
    
    .modal-button.cancel {
      background-color: #EEEEEE;
      color: var(--text-color);
    }
    
    .modal-button.confirm {
      background-color: var(--primary-color);
      color: white;
    }
    
    /* Speed Dial Menu */
    .speed-dial {
      position: fixed;
      bottom: 20px;
      right: 20px;
      z-index: 50;
    }
    
    .speed-dial-button {
      width: 56px;
      height: 56px;
      border-radius: 50%;
      background-color: var(--primary-color);
      color: white;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 24px;
      box-shadow: 0 2px 10px rgba(0,0,0,0.2);
      cursor: pointer;
      transition: transform 0.3s;
    }
    
    .speed-dial-items {
      position: absolute;
      bottom: 65px;
      right: 5px;
      display: flex;
      flex-direction: column;
      align-items: flex-end;
      opacity: 0;
      pointer-events: none;
      transition: opacity 0.3s;
    }
    
    .speed-dial.active .speed-dial-button {
      transform: rotate(45deg);
      background-color: var(--danger-color);
    }
    
    .speed-dial.active .speed-dial-items {
      opacity: 1;
      pointer-events: auto;
    }
    
    .speed-dial-item {
      display: flex;
      align-items: center;
      margin-bottom: 10px;
    }
    
    .speed-dial-item-button {
      width: 46px;
      height: 46px;
      border-radius: 50%;
      background-color: white;
      color: var(--primary-color);
      display: flex;
      align-items: center;
      justify-content: center;
      box-shadow: 0 2px 5px rgba(0,0,0,0.2);
      margin-left: 10px;
    }
    
    .speed-dial-item-label {
      background-color: rgba(0,0,0,0.7);
      color: white;
      padding: 5px 10px;
      border-radius: 4px;
      font-size: 14px;
    }
  </style>
</head>
<body>

<div id="app-container">
  <div class="app-header">
    <div class="app-title">PRFS Ferry Operator</div>
    <div class="header-button" id="refresh-button">
      <i class="fas fa-sync-alt"></i>
    </div>
    <div class="header-button" id="settings-button">
      <i class="fas fa-cog"></i>
    </div>
  </div>
  
  <div id="map"></div>
  
  <div id="floating-info">
    <b>Your Location</b><br>
    Lat: <span id="lat">---</span><br>
    Lng: <span id="lng">---</span><br>
    Speed: <span id="speed">0</span> km/h
  </div>
  
  <div class="bottom-panel" id="bottom-panel">
    <div class="panel-handle"></div>
    
    <div class="tab-buttons">
      <div class="tab-button active" data-tab="status">Status</div>
      <div class="tab-button" data-tab="ferries">Ferries</div>
      <div class="tab-button" data-tab="routes">Routes</div>
    </div>
    
    <div class="tab-content active" id="status-tab">
      <div class="info-card">
        <div class="card-title">Ferry Status</div>
        <div class="info-row">
          <div class="info-label">Ferry Code</div>
          <div class="info-value" id="ferry-code-display">PRFS001</div>
        </div>
        <div class="info-row">
          <div class="info-label">Status</div>
          <div class="info-value"><span class="status-indicator status-active"></span> Active</div>
        </div>
        <div class="info-row">
          <div class="info-label">Last Updated</div>
          <div class="info-value" id="last-updated">Just now</div>
        </div>
      </div>
      
      <div class="info-card">
        <div class="card-title">Current Trip</div>
        <div class="info-row">
          <div class="info-label">Route</div>
          <div class="info-value">Port A → Port B</div>
        </div>
        <div class="info-row">
          <div class="info-label">ETA</div>
          <div class="info-value">25 min</div>
        </div>
        <div class="info-row">
          <div class="info-label">Distance</div>
          <div class="info-value">4.3 km</div>
        </div>
        <div class="info-row">
          <div class="info-label">Passengers</div>
          <div class="info-value">24/30</div>
        </div>
        
        <button class="button" id="end-trip-button">End Current Trip</button>
      </div>
    </div>
    
    <div class="tab-content" id="ferries-tab">
      <div class="info-card">
        <div class="card-title">Nearby Ferries</div>
        <ul class="ferry-list" id="nearby-ferries">
          <!-- Ferries will be populated here -->
        </ul>
      </div>
    </div>
    
    <div class="tab-content" id="routes-tab">
      <div class="info-card">
        <div class="card-title">Available Routes</div>
        <div class="info-row">
          <div class="info-label">Port A → Port B</div>
          <div class="info-value">4.3 km</div>
        </div>
        <div class="info-row">
          <div class="info-label">Port B → Port C</div>
          <div class="info-value">3.7 km</div>
        </div>
        <div class="info-row">
          <div class="info-label">Port C → Port A</div>
          <div class="info-value">5.2 km</div>
        </div>
      </div>
      
      <button class="button" id="start-trip-button">Start New Trip</button>
    </div>
  </div>
  
  <div class="speed-dial" id="speed-dial">
    <div class="speed-dial-items">
      <div class="speed-dial-item">
        <div class="speed-dial-item-label">Emergency</div>
        <div class="speed-dial-item-button" id="emergency-button">
          <i class="fas fa-exclamation-triangle"></i>
        </div>
      </div>
      <div class="speed-dial-item">
        <div class="speed-dial-item-label">Report Issue</div>
        <div class="speed-dial-item-button" id="report-button">
          <i class="fas fa-flag"></i>
        </div>
      </div>
      <div class="speed-dial-item">
        <div class="speed-dial-item-label">Weather</div>
        <div class="speed-dial-item-button" id="weather-button">
          <i class="fas fa-cloud"></i>
        </div>
      </div>
    </div>
    <div class="speed-dial-button" id="speed-dial-trigger">
      <i class="fas fa-plus"></i>
    </div>
  </div>
  
  <div class="alert" id="alert">Location updated successfully</div>
  
  <div class="modal" id="settings-modal">
    <div class="modal-content">
      <div class="modal-title">Ferry Settings</div>
      <div class="form-group">
        <label class="form-label">Ferry Code</label>
        <input type="text" class="form-input" id="ferry-code-input" placeholder="Enter ferry code">
      </div>
      <div class="form-group">
        <label class="form-label">Operator Name</label>
        <input type="text" class="form-input" id="operator-name-input" placeholder="Enter operator name">
      </div>
      <div class="form-group">
        <label class="form-label">Max Capacity</label>
        <input type="number" class="form-input" id="max-capacity-input" placeholder="Enter max capacity">
      </div>
      <div class="modal-buttons">
        <button class="modal-button cancel" id="cancel-settings">Cancel</button>
        <button class="modal-button confirm" id="save-settings">Save</button>
      </div>
    </div>
  </div>
  
  <div class="modal" id="emergency-modal">
    <div class="modal-content">
      <div class="modal-title">Emergency Alert</div>
      <p>This will send an emergency alert to all nearby vessels and port authorities.</p>
      <div class="form-group">
        <label class="form-label">Emergency Type</label>
        <select class="form-input" id="emergency-type">
          <option value="mechanical">Mechanical Failure</option>
          <option value="medical">Medical Emergency</option>
          <option value="weather">Severe Weather</option>
          <option value="collision">Collision Risk</option>
          <option value="other">Other</option>
        </select>
      </div>
      <div class="form-group">
        <label class="form-label">Details</label>
        <textarea class="form-input" id="emergency-details" rows="3" placeholder="Enter emergency details"></textarea>
      </div>
      <div class="modal-buttons">
        <button class="modal-button cancel" id="cancel-emergency">Cancel</button>
        <button class="modal-button confirm" style="background-color: var(--danger-color);" id="send-emergency">Send Alert</button>
      </div>
    </div>
  </div>
</div>

<script>
// Set up the map
const map = L.map('map', {
  zoomControl: false, // Hide default zoom controls
  attributionControl: false // Hide attribution
}).setView([14.5896, 121.0359], 14);

// Add attribution in a minimized way
L.control.attribution({
  position: 'bottomright',
  prefix: ''
}).addCustomAttribution('© OpenStreetMap').addTo(map);

// Add zoom control to the top right
L.control.zoom({
  position: 'topright'
}).addTo(map);

// Add tiles
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
  attribution: '© OpenStreetMap contributors'
}).addTo(map);

// Custom icons
const ferryIcon = L.divIcon({
  className: 'custom-ferry-icon',
  html: '<div style="background-color: #0066CC; width: 20px; height: 20px; border-radius: 50%; border: 3px solid white; box-shadow: 0 0 5px rgba(0,0,0,0.5);"></div>',
  iconSize: [20, 20],
  iconAnchor: [10, 10]
});

const myFerryIcon = L.divIcon({
  className: 'my-ferry-icon',
  html: '<div style="background-color: #34C759; width: 24px; height: 24px; border-radius: 50%; border: 3px solid white; box-shadow: 0 0 5px rgba(0,0,0,0.5);"></div>',
  iconSize: [24, 24],
  iconAnchor: [12, 12]
});

// Get ferryCode from localStorage or prompt
let ferryCode = localStorage.getItem('ferryCode') || "PRFS001";
if (!localStorage.getItem('ferryCode')) {
  localStorage.setItem('ferryCode', ferryCode);
}

// Update the display
document.getElementById('ferry-code-display').textContent = ferryCode;
document.getElementById('ferry-code-input').value = ferryCode;

// Initialize variables
let myMarker = L.marker([0, 0], {icon: myFerryIcon}).addTo(map);
let otherMarkers = {};
let lastLat = null, lastLng = null;
let lastSpeed = 0;
let lastUpdateTime = new Date();

// DOM elements
const latEl = document.getElementById('lat');
const lngEl = document.getElementById('lng');
const speedEl = document.getElementById('speed');
const lastUpdatedEl = document.getElementById('last-updated');
const bottomPanel = document.getElementById('bottom-panel');
const tabButtons = document.querySelectorAll('.tab-button');
const tabContents = document.querySelectorAll('.tab-content');
const nearbyFerriesList = document.getElementById('nearby-ferries');
const speedDial = document.getElementById('speed-dial');
const alert = document.getElementById('alert');
const settingsModal = document.getElementById('settings-modal');
const emergencyModal = document.getElementById('emergency-modal');

// Update your location
function updateLocation(lat, lng, speed = 0) {
  $.post('', {
    latitude: lat,
    longitude: lng,
    code: ferryCode
  })
  .done(() => {
    lastUpdateTime = new Date();
    updateLastUpdatedTime();
    showAlert('Location updated');
  })
  .fail(() => {
    console.error("Failed to update location.");
    showAlert('Failed to update location', 'error');
  });
}

// Update the "last updated" time display
function updateLastUpdatedTime() {
  const now = new Date();
  const diff = Math.floor((now - lastUpdateTime) / 1000);
  
  if (diff < 10) {
    lastUpdatedEl.textContent = 'Just now';
  } else if (diff < 60) {
    lastUpdatedEl.textContent = `${diff} seconds ago`;
  } else {
    const mins = Math.floor(diff / 60);
    lastUpdatedEl.textContent = `${mins} minute${mins > 1 ? 's' : ''} ago`;
  }
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

// Update nearby ferries list
function updateNearbyFerriesList(ferries) {
  nearbyFerriesList.innerHTML = '';
  
  if (ferries.length === 0) {
    nearbyFerriesList.innerHTML = '<li class="ferry-item">No nearby ferries</li>';
    return;
  }
  
  ferries.forEach(ferry => {
    if (ferry.code === ferryCode) return; // Skip own ferry
    
    const item = document.createElement('li');
    item.className = 'ferry-item';
    item.innerHTML = `
      <div class="ferry-icon">
        <i class="fas fa-ship"></i>
      </div>
      <div class="ferry-info">
        <div class="ferry-code">${ferry.code}</div>
        <div class="ferry-status"><span class="status-indicator status-active"></span> Active</div>
      </div>
      <div class="ferry-action">
        <i class="fas fa-info-circle"></i>
      </div>
    `;
    
    // Add click event to show ferry details
    item.querySelector('.ferry-action').addEventListener('click', () => {
      map.setView([ferry.latitude, ferry.longitude], 16);
      bottomPanel.classList.remove('expanded');
    });
    
    nearbyFerriesList.appendChild(item);
  });
}

// Show alert message
function showAlert(message, type = 'success') {
  alert.textContent = message;
  alert.style.backgroundColor = type === 'success' ? 'var(--success-color)' : 'var(--danger-color)';
  alert.classList.add('show');
  
  setTimeout(() => {
    alert.classList.remove('show');
  }, 3000);
}

// Calculate distance between two coordinates
function calculateDistance(lat1, lon1, lat2, lon2) {
  const R = 6371; // Radius of the earth in km
  const dLat = deg2rad(lat2 - lat1);
  const dLon = deg2rad(lon2 - lon1);
  const a = 
    Math.sin(dLat/2) * Math.sin(dLat/2) +
    Math.cos(deg2rad(lat1)) * Math.cos(deg2rad(lat2)) * 
    Math.sin(dLon/2) * Math.sin(dLon/2); 
  const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a)); 
  const d = R * c; // Distance in km
  return d;
}

function deg2rad(deg) {
  return deg * (Math.PI/180);
}

// Initialize GPS tracking
if (navigator.geolocation) {
  navigator.geolocation.watchPosition(
    pos => {
      const lat = pos.coords.latitude;
      const lng = pos.coords.longitude;
      const speed = pos.coords.speed ? (pos.coords.speed * 3.6).toFixed(1) : lastSpeed; // Convert m/s to km/h
      
      latEl.textContent = lat.toFixed(6);
      lngEl.textContent = lng.toFixed(6);
      speedEl.textContent = speed;
      lastSpeed = speed;
      
      myMarker.setLatLng([lat, lng]);
      
      // Center map on user's location only on first load
      if (lastLat === null && lastLng === null) {
        map.setView([lat, lng], 15);
      }
      
      if (lastLat !== lat || lastLng !== lng) {
        lastLat = lat;
        lastLng = lng;
        updateLocation(lat, lng, speed);
      }
    },
    err => {
      console.error("GPS Error: " + err.message);
      showAlert("GPS Error: " + err.message, 'error');
    },
    { 
      enableHighAccuracy: true, 
      maximumAge: 0, 
      timeout: 10000 
    }
  );
} else {
  showAlert("Geolocation is not supported by this device.", 'error');
}

// Poll every 2 seconds for ferry locations
setInterval(() => {
  if (lastLat !== null && lastLng !== null) {
    updateLocation(lastLat, lastLng, lastSpeed);
  }
  
  updateLastUpdatedTime();
  
  $.getJSON('?fetch=1', data => {
    const activeCodes = [];
    
    data.forEach(ferry => {
      if (ferry.code !== ferryCode) {
        updateFerryMarker(ferry.code, ferry.latitude, ferry.longitude);
      }
      activeCodes.push(ferry.code);
    });
    
    cleanupMarkers(activeCodes);
    updateNearbyFerriesList(data);
  })
  .fail(() => {
    console.error("Failed to fetch ferry locations");
  });
}, 2000);

// Bottom panel handle
document.querySelector('.panel-handle').addEventListener('touchstart', function(e) {
  e.preventDefault();
  bottomPanel.classList.toggle('expanded');
});

// Tab buttons
tabButtons.forEach(button => {
  button.addEventListener('click', () => {
    // Remove active class from all buttons
    tabButtons.forEach(btn => btn.classList.remove('active'));
    
    // Add active class to clicked button
    button.classList.add('active');
    
    // Hide all tab contents
    tabContents.forEach(content => content.classList.remove('active'));
    
    // Show selected tab content
    const tabId = button.getAttribute('data-tab');
    document.getElementById(`${tabId}-tab`).classList.add('active');
  });
});

// Speed dial
document.getElementById('speed-dial-trigger').addEventListener('click', () => {
  speedDial.classList.toggle('active');
});

// Settings button
document.getElementById('settings-button').addEventListener('click', () => {
  settingsModal.classList.add('active');
});

// Cancel settings
document.getElementById('cancel-settings').addEventListener('click', () => {
  settingsModal.classList.remove('active');
});

// Save settings
document.getElementById('save-settings').addEventListener('click', () => {
  const newFerryCode = document.getElementById('ferry-code-input').value;
  if (newFerryCode && newFerryCode !== ferryCode) {
    ferryCode = newFerryCode;
    localStorage.setItem('ferryCode', ferryCode);
    document.getElementById('ferry-code-display').textContent = ferryCode;
    showAlert('Settings saved');
  }
  
  settingsModal.classList.remove('active');
});

// Emergency button
document.getElementById('emergency-button').addEventListener('click', () => {
  emergencyModal.classList.add('active');
  speedDial.classList.remove('active');
});

// Cancel emergency
document.getElementById('cancel-emergency').addEventListener('click', () => {
  emergencyModal.classList.remove('active');
});

// Send emergency alert
document.getElementById('send-emergency').addEventListener('click', () => {
  const emergencyType = document.getElementById('emergency-type').value;
  showAlert('Emergency alert sent to authorities', 'success');
  emergencyModal.classList.remove('active');
  
  // In a real app, you would send this to a server endpoint
  console.log('Emergency alert:', {
    type: emergencyType,
    details: document.getElementById('emergency-details').value,
    location: {
      lat: lastLat,
      lng: lastLng
    },
    ferryCode: ferryCode,
    timestamp: new Date()
  });
});

// Weather button
document.getElementById('weather-button').addEventListener('click', () => {
  showAlert('Weather forecast: Clear skies, visibility good', 'success');
  speedDial.classList.remove('active');
});

// Report issue button
document.getElementById('report-button').addEventListener('click', () => {
  showAlert('Issue reporting form will appear here', 'success');
  speedDial.classList.remove('active');
});

// Refresh button
document.getElementById('refresh-button').addEventListener('click', () => {
  if (lastLat !== null && lastLng !== null) {
    updateLocation(lastLat, lastLng, lastSpeed);
    map.setView([lastLat, lastLng], map.getZoom());
    showAlert('Data refreshed');
  }
});

// End trip button
document.getElementById('end-trip-button').addEventListener('click', () => {
  showAlert('Trip ended successfully');
  // In a real app, you would send this to a server endpoint
});

// Start trip button
document.getElementById('start-trip-button').addEventListener('click', () => {
  showAlert('New trip started');
  // In a real app, you would send this to a server endpoint and switch tabs
  tabButtons[0].click(); // Switch to status tab
});

// Handle document clicks to close speed dial
document.addEventListener('click', (e) => {
  if (!speedDial.contains(e.target) && speedDial.classList.contains('active')) {
    speedDial.classList.remove('active');
  }
});

// Add service worker for offline functionality
if ('serviceWorker' in navigator) {
  window.addEventListener('load', () => {
    navigator.serviceWorker.register('service-worker.js')
      .then(registration => {
        console.log('ServiceWorker registered:', registration);
      })
      .catch(error => {
        console.log('ServiceWorker registration failed:', error);
      });
  });
}