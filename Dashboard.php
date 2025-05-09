<?php
session_start();

require 'db_connect.php'; // Use external DB connection

$username = $_SESSION['username'] ?? null;
if ($username) {
    $sql = "SELECT first_name, last_name, email, profile_pic FROM staff_users WHERE username = ?";
    $stmt = $conn->prepare($sql);   
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $name = $user['first_name'] . ' ' . $user['last_name'];
        $email = $user['email'];
        $profile_pic = $user['profile_pic'] ?? 'uploads/default.png';
    } else {
        $name = 'Unknown User';
        $email = 'unknown@email.com';
        $profile_pic = 'uploads/default.png';
    }

    $stmt->close();
} else {
    $name = 'Guest';
    $email = 'guest@email.com';
    $profile_pic = 'uploads/default.png';
}

// Live stats
$passenger_sql = "SELECT SUM(current_capacity) AS total_passengers FROM ferries";
$passenger_result = $conn->query($passenger_sql);
$total_passengers = $passenger_result->fetch_assoc()['total_passengers'] ?? 0;

$passes_sql = "SELECT COUNT(*) AS active_passes FROM passenger_id_pass WHERE is_active = 1 AND expires_at > NOW()";
$passes_result = $conn->query($passes_sql);
$active_passes = $passes_result->fetch_assoc()['active_passes'] ?? 0;

$ferry_sql = "SELECT COUNT(*) AS active_ferries FROM ferries WHERE status = 'active'";
$ferry_result = $conn->query($ferry_sql);
$active_ferries = $ferry_result->fetch_assoc()['active_ferries'] ?? 0;

$occupancy_sql = "SELECT AVG(current_capacity / max_capacity) AS avg_occupancy FROM ferries WHERE max_capacity > 0";
$occupancy_result = $conn->query($occupancy_sql);
$avg_occupancy = $occupancy_result->fetch_assoc()['avg_occupancy'] ?? 0;
$occupancy_percentage = round($avg_occupancy * 100, 1);

$conn->close();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Ferry Admin Dashboard</title>
    <link rel="stylesheet" href="Db.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
</head>
<body>
<div class="main-container">
    <div class="container">
        <div class="sidebar-wrapper">
        <div class="sidebar">
    <!-- Top content: logo and main nav -->
    <div class="sidebar-top">
        <div class="logo">
            <img src="PasigRiverFerryServiceLogo.png" alt="Logo" style="width: 30px; height: 30px;">
            PRFS MANAGEMENT
        </div>

        <ul class="nav main-nav">
            <li class="active" data-page="dashboard">Dashboard</li>
            <li data-page="analytics">Analytics</li>
            <li data-page="tracking">Tracking</li>
            <li data-page="ferrymngt">Ferry Management</li>
            <li data-page="routeschedules">Route and Schedules</li>
            <li data-page="Usersection">User Section</li>
        </ul>
    </div>

    <!-- Bottom content: settings + profile -->
    <div class="sidebar-bottom">
        <ul class="nav settings-nav">
            <li><a href="#">Settings</a></li>
            <li><a href="#">Mail</a></li>
            <li><a href="login.php">Logout</a></li>
        </ul>

        <div class="profile">
            <img src="<?php echo htmlspecialchars($profile_pic); ?>" alt="Profile Picture" />
            <div class="profile-info">
                <strong class="profile-name" title="<?= htmlspecialchars($name) ?>"><?= htmlspecialchars($name) ?></strong>
                <span class="profile-email" title="<?= htmlspecialchars($email) ?>"><?= htmlspecialchars($email) ?></span>
            </div>
        </div>
    </div>
</div>

        </div>
        <div class="main">
            <div class="header">
                <h1>Dashboard</h1>
                <div id="clock" style="margin-bottom: 20px; font-size: 16px; color: #00b0ff;"></div>
            </div>

            <div class="stats">
                <div class="stat-box">
                    <h2>Total Passengers</h2>
                    <p><?= $total_passengers ?></p>
                    <div class="stat-change">↑ based on records</div>
                </div>
                <div class="stat-box">
                    <h2>Active Passes</h2>
                    <p><?= $active_passes ?></p>
                    <div class="stat-change">Currently Valid</div>
                </div>
                <div class="stat-box">
                    <h2>Active Ferries</h2>
                    <p><?= $active_ferries ?></p>
                    <div class="stat-change">In Operation Now</div>
                </div>
                <div class="stat-box">
                    <h2>Average Occupancy</h2>
                    <p><?= $occupancy_percentage ?>%</p>
                    <div class="stat-change">Capacity Utilization</div>
                </div>
            </div>

            <div class="tracking">
                <div class="boat-list">
                    <div class="boat-list-header">Ferry List</div>
                    <div id="ferry-list" class="boat-list-body">
                        <!-- Ferry data will be dynamically loaded here -->
                    </div>
                </div>
                <div class="map">
                    <div class="map-box">
                        <div id="map" style="height: 100%;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
<script>
var map = L.map('map').setView([14.5896, 121.0360], 13);
var markers = {};

L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; OpenStreetMap contributors'
}).addTo(map);

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

function fetchFerryData() {
    const ferryList = $('#ferry-list');
    const scrollPos = ferryList.scrollTop();

    $.ajax({
        url: 'getFerries.php',
        method: 'GET',
        dataType: 'json',
        success: function(data) {
            ferryList.empty();

            if (data.length === 0) {
                ferryList.append('<p>No ferries are currently available.</p>');
            }

            // Clear old markers
            for (let name in markers) {
                map.removeLayer(markers[name]);
            }
            markers = {};

            data.forEach(function(ferry) {
                const statusClass = ferry.status.toLowerCase() === 'active' ? 'status-active' : 'status-inactive';

                const ferryElement = `
                    <div class="boat-card" data-lat="${ferry.latitude}" data-lng="${ferry.longitude}">
                        <div class="status-indicator ${statusClass}"></div>
                        <div class="top"><strong>${ferry.name}</strong></div>
                        <div class="middle-row">
                            <div class="left-info">
                                Active Time: ${ferry.active_time} mins<br>
                                Status: ${ferry.status}<br>
                                Operator: ${ferry.operator}
                            </div>
                            <div class="capacity-info">
                                Capacity: ${ferry.current_capacity} / ${ferry.max_capacity}
                            </div>
                        </div>
                        <div class="coordinates">
                            Longitude: ${ferry.longitude} | Latitude: ${ferry.latitude}
                        </div>
                    </div>
                `;
                ferryList.append(ferryElement);

                // Only add marker if ferry is active
                if (ferry.status.toLowerCase() === 'active' && ferry.latitude && ferry.longitude) {
                    addFerryMarker(ferry.latitude, ferry.longitude, ferry.name);
                }
            });

            ferryList.scrollTop(scrollPos);
        },
        error: function() {
            ferryList.html('<p>Error loading ferry data.</p>');
        }
    });
}

function addFerryMarker(latitude, longitude, ferryName) {
    const ferryIcon = L.icon({
        iconUrl: 'ship.png',
        iconSize: [32, 32],
        iconAnchor: [16, 16],
        popupAnchor: [0, -16]
    });

    const marker = L.marker([latitude, longitude], { icon: ferryIcon }).addTo(map)
        .bindTooltip(ferryName, {
            permanent: true,
            direction: 'top',
            className: 'ferry-label'
        });

    markers[ferryName] = marker;
}

setInterval(fetchFerryData, 5000);
fetchFerryData();

$(document).on('click', '.boat-card', function() {
    const lat = $(this).data('lat');
    const lng = $(this).data('lng');
    const name = $(this).find('.top strong').text();

    map.setView([lat, lng], 15);
});

function updateClock() {
    const now = new Date();
    const timeString = now.toLocaleTimeString();
    const dateString = now.toLocaleDateString();
    document.getElementById("clock").textContent = `${dateString} | ${timeString}`;
}
const navItems = document.querySelectorAll('.nav li');

    navItems.forEach(item => {
        item.addEventListener('click', function() {
            navItems.forEach(item => item.classList.remove('active'));
            item.classList.add('active');
            const page = item.getAttribute('data-page');
            if (page === 'dashboard') {
                window.location.href = 'Dashboard.php';
            } else if (page === 'analytics') {
                window.location.href = 'analytics.php';
            } else if (page === 'tracking') {
                window.location.href = 'tracking.php';
            } else if (page === 'ferrymngt') {
                window.location.href = 'ferrymngt.php';
            } else if (page === 'routeschedules') {
                window.location.href = 'routeschedules.php';
            } else if (page === 'Users') {
                window.location.href = 'template.php';
            }
        });
    });
setInterval(updateClock, 1000);
updateClock();
</script>
</body>
</html>
