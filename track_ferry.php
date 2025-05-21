<?php
session_start();
if (!isset($_SESSION['staff_id'], $_SESSION['2fa_verified']) || $_SESSION['2fa_verified'] !== true) {
    header("Location: login.php");
    exit();
}

require 'db_connect.php';

// Ensure only operators can access this page
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'operator') {
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>PRFS Ferry Tracking System</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
       body { font-family: Arial, sans-serif; margin: 0; padding: 0; display: flex; height: 100vh; background-color: #f4f4f4; }
#tracking-container { display: flex; width: 100%; }
#sidebar { width: 300px; padding: 15px; background: #2c2c2c; color: white; border-right: 1px solid #444; }
#map { flex-grow: 1; height: 100vh; }
.ferry-item { cursor: pointer; padding: 10px; border-bottom: 1px solid #444; transition: background 0.3s ease; }
.ferry-item:hover { background: #3a3a3a; }
.ferry-item.active { background: #00bcd4; color: white; }
#logout-btn { width: 100%; padding: 10px; margin-top: 15px; background-color: #f44336; color: white; border: none; border-radius: 5px; cursor: pointer; }
#logout-btn:hover { background-color: #d32f2f; }
#user-info { margin-bottom: 15px; padding: 10px; background: #3a3a3a; border-radius: 5px; }
#status-display { margin-top: 15px; padding: 10px; background: #3a3a3a; border-radius: 5px; text-align: center; }

    </style>
</head>
<body>
    <div id="tracking-container">
        <div id="sidebar">
            <div id="user-info">
                <h3>Operator Tracking</h3>
                <p>Welcome, <?= htmlspecialchars($_SESSION['name']) ?></p>
            </div>
            <h4>Assigned Ferries</h4>
            <div id="ferry-list"></div>
            <div id="status-display">
                <p id="location-status">No ferry selected</p>
            </div>
            <button id="logout-btn">Logout</button>
        </div>
        <div id="map"></div>
    </div>

    <script>
    $(document).ready(function() {
        let map, selectedFerry = null, currentMarker = null;
        const ferryIcon = L.icon({
            iconUrl: 'pin.png',
            iconSize: [30, 30],
            iconAnchor: [15, 30],
            popupAnchor: [0, -30]
        });

        // Logout functionality
        $('#logout-btn').on('click', function() {
            window.location.href = 'logout.php';
        });

        // Initialize map
        map = L.map('map').setView([14.5896, 121.0359], 10);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);

        // Fetch assigned ferries
        $.ajax({
            url: 'get_assigned_ferries.php',
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    const ferryList = $('#ferry-list');
                    ferryList.empty();

                    response.ferries.forEach(ferry => {
                        const ferryItem = $(`
                            <div class="ferry-item" data-ferry-id="${ferry.id}">
                                <strong>${ferry.name}</strong><br>
                                Code: ${ferry.ferry_code}<br>
                                Type: ${ferry.ferry_type}<br>
                                Status: ${ferry.status}
                            </div>
                        `).appendTo(ferryList);

                        // Ferry selection
                        ferryItem.on('click', function() {
                            $('.ferry-item').removeClass('active');
                            $(this).addClass('active');
                            selectedFerry = ferry.id;
                            $('#location-status').text(`Selected: ${ferry.name}`);
                            startTracking();
                        });
                    });
                } else {
                    $('#ferry-list').html('<p>No ferries assigned</p>');
                }
            },
            error: function() {
                $('#ferry-list').html('<p>Error loading ferries</p>');
            }
        });

        function startTracking() {
            // Start GPS tracking
            if (navigator.geolocation) {
                navigator.geolocation.watchPosition(
                    updateFerryLocation,
                    handleLocationError,
                    { 
                        enableHighAccuracy: true, 
                        maximumAge: 0, 
                        timeout: 10000 
                    }
                );
            } else {
                alert("Geolocation is not supported by this browser.");
            }
        }

        function updateFerryLocation(position) {
            // Only update if a ferry is selected
            if (!selectedFerry) {
                $('#location-status').text('Please select a ferry first');
                return;
            }

            const lat = position.coords.latitude;
            const lng = position.coords.longitude;
            const speed = position.coords.speed || 0;

            // Update location status
            $('#location-status').text(`Updating location for ferry: Lat ${lat.toFixed(6)}, Lng ${lng.toFixed(6)}`);

            // Optional: Update map marker
            if (currentMarker) {
                map.removeLayer(currentMarker);
            }
            currentMarker = L.marker([lat, lng], {icon: ferryIcon})
                .addTo(map)
                .bindPopup(`Current Location<br>Lat: ${lat.toFixed(6)}<br>Lng: ${lng.toFixed(6)}`);
            
            map.setView([lat, lng], map.getZoom());

            // Send location update
            $.ajax({
                url: 'update_ferry_location.php',
                method: 'POST',
                data: {
                    ferry_id: selectedFerry,
                    latitude: lat,
                    longitude: lng,
                    speed: speed
                },
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        $('#location-status').text('Location updated successfully');
                    } else {
                        $('#location-status').text('Failed to update location');
                    }
                },
                error: function() {
                    $('#location-status').text('Error updating location');
                }
            });
        }

        function handleLocationError(error) {
            let errorMessage = '';
            switch(error.code) {
                case error.PERMISSION_DENIED:
                    errorMessage = "User denied the request for Geolocation.";
                    break;
                case error.POSITION_UNAVAILABLE:
                    errorMessage = "Location information is unavailable.";
                    break;
                case error.TIMEOUT:
                    errorMessage = "The request to get user location timed out.";
                    break;
                case error.UNKNOWN_ERROR:
                    errorMessage = "An unknown error occurred.";
                    break;
            }
            $('#location-status').text(errorMessage);
            alert(errorMessage);
        }
    });
    </script>
</body>
</html>