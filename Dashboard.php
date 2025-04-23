<?php
session_start();
// Database connection details
$servername = "localhost";
$db_username = "PRFS";
$db_password = "1111";
$dbname = "prfs";

// Create a connection
$conn = new mysqli($servername, $db_username, $db_password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch user details from the database
$username = $_SESSION['username'];
$sql = "SELECT first_name, last_name, email, profile_pic FROM staff_users WHERE username = ?";
$stmt = $conn->prepare($sql);   
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    $name = $user['first_name'] . ' ' . $user['last_name'];
    $email = $user['email'];
    $profile_pic = $user['profile_pic'] ?? 'uploads/default.png'; // Fallback to default if not set
} else {
    $name = 'Unknown User';
    $email = 'unknown@email.com';
    $profile_pic = 'uploads/default.png'; // Default profile picture
}

$stmt->close();
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
    <!-- Main container with rounded edges -->
    <div class="main-container">
        <div class="container">
            <!-- Sidebar -->
            <div class="sidebar">
                <div>
                    <div class="logo">
                        <img src="PasigRiverFerryServiceLogo.png" alt="Logo" style="width: 30px; height: 30px;">
                        PRFS MANAGEMENT
                    </div>

                    <div class="search-bar">
                        <input type="text" placeholder="Search">
                    </div>

                    <ul class="nav">
                        <li class="active" data-page="dashboard">Dashboard</li>
                        <li data-page="analytics">Analytics</li>
                        <li data-page="tracking">Tracking</li>
                        <li data-page="ferrymngt">Ferry Management</li>
                        <li data-page="routeschedules">Route and Schedules</li>
                        <li data-page="tickets">User Section</li>
                    </ul>

                    <!-- Settings, Help, and Logout Section -->
                    <ul class="nav settings-nav">
                        <li><a href="#">Settings</a></li>
                        <li><a href="#">Help</a></li>
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

            <!-- Main Dashboard -->
            <div class="main">
                <div class="header">
                    <h1>Dashboard</h1>
                </div>

                <div class="stats">
                    <div class="stat-box">
                        <h2>Total Passengers</h2>
                        <p>10,342</p>
                        <div class="stat-change">↑ 12% From last month</div>
                    </div>
                    <div class="stat-box">
                        <h2>Tickets Sold</h2>
                        <p>8,912</p>
                        <div class="stat-change">↑ 9% From last month</div>
                    </div>
                    <div class="stat-box">
                        <h2>Total Expenses</h2>
                        <p>$3,219</p>
                        <div class="stat-change">↓ 4% From last month</div>
                    </div>
                    <div class="stat-box">
                        <h2>Total Income</h2>
                        <p>$12,450</p>
                        <div class="stat-change">↑ 15% From last month</div>
                    </div>
                </div>

                <div class="tracking">
                    <div class="boat-list">
                        <div class="boat-list-header">Ferry List</div>
                        <div id="ferry-list" class="boat-list-body">
                            <!-- Ferry data will be dynamically loaded here -->
                        </div>
                    </div>

                    <!-- Map Section -->
                    <div class="map">
                        <div id="map" style="width: 100%; height: 100%;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add your script tags for your custom JavaScript -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
    


    <script>
    var map = L.map('map').setView([14.5896, 121.0360], 13);
    var markers = {};

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(map);

    var pasigRiverRoute = [
    [14.5550, 121.0731],
    [14.5559, 121.0711],
    [14.5565, 121.0687],
    [14.5567, 121.0680],
    [14.5581, 121.0669],
    
    [14.5597, 121.0664],
    [14.5610, 121.0650],
    [14.5614, 121.0631],
    [14.5622, 121.0614],
    [14.5644, 121.0592],
    [14.5653, 121.0558],
    [14.5661, 121.0536],
    [14.5678, 121.0511],
    [14.5683, 121.0492],
    [14.5683, 121.0470]

];
var pasigRiverRoute2 = [
    [14.5581, 121.0669],
    [14.5581, 121.0681],
    [14.5586, 121.0703],
    [14.5597, 121.0721],
    [14.5620, 121.0732],
    [14.5667, 121.0736],
    [14.5700, 121.0739],
    [14.5714, 121.0743],
    [14.5754, 121.0775],
    [14.5778, 121.0803]
];

    const riverRoute = L.polyline(pasigRiverRoute, {
        color: 'blue',
        weight: 4,
        opacity: 0.7,
        smoothFactor: 1
    }).addTo(map);

    const riverRoute2 = L.polyline(pasigRiverRoute2, {
    color: 'green',  // You can change the color if you like
    weight: 4,
    opacity: 0.7,
    smoothFactor: 1
}).addTo(map);

    // Only fit once when map initializes
    map.fitBounds([riverRoute.getBounds(), riverRoute2.getBounds()]);

    function fetchFerryData() {
        $.ajax({
            url: 'getFerries.php',
            method: 'GET',
            dataType: 'json',
            success: function(data) {
                $('#ferry-list').empty();
                if (data.length === 0) {
                    $('#ferry-list').append('<p>No ferries are currently available.</p>');
                }

                data.forEach(function(ferry) {
                    const statusClass = ferry.status.toLowerCase() === 'active' ? 'status-active' : 'status-inactive';

const ferryElement = `
    <div class="boat-card" data-lat="${ferry.latitude}" data-lng="${ferry.longitude}">
        <div class="status-indicator ${statusClass}"></div>
        <div class="top"><strong>${ferry.name}</strong></div>
        <div class="bottom">
            Active Time: ${ferry.active_time} mins<br>
            Status: ${ferry.status}<br>
            Operator: ${ferry.operator}
        </div>
        <div class="coordinates">
            <span>Longitude: ${ferry.longitude} | Latitude: ${ferry.latitude}</span>
        </div>
    </div>
`;


                    $('#ferry-list').append(ferryElement);

                    if (ferry.latitude && ferry.longitude) {
                        if (!markers[ferry.name]) {
                            addFerryMarker(ferry.latitude, ferry.longitude, ferry.name);
                        } else {
                            markers[ferry.name].setLatLng([ferry.latitude, ferry.longitude]);
                        }
                    }
                });
            },
            error: function() {
                $('#ferry-list').html('<p>Sorry, there was an error loading the ferry data.</p>');
            }
        });
    }

    setInterval(fetchFerryData, 5000);
    fetchFerryData();

    function addFerryMarker(latitude, longitude, ferryName) {
        const marker = L.marker([latitude, longitude]).addTo(map)
            .bindPopup(ferryName); // Removed .openPopup() to stop unwanted camera jumps
        markers[ferryName] = marker;
    }

    $(document).on('click', '.boat-card', function() {
        const lat = $(this).data('lat');
        const lng = $(this).data('lng');
        const name = $(this).find('.top strong').text();

        map.setView([lat, lng], 15);

        if (!markers[name]) {
            addFerryMarker(lat, lng, name);
        }
    });

    document.getElementById('map').style.borderRadius = '24px';
    document.getElementById('map').style.overflow = 'hidden';

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
                window.location.href = 'tracking.html';
            } else if (page === 'ferrymngt') {
                window.location.href = 'ferrymngt.php';
            } else if (page === 'routeschedules') {
                window.location.href = 'routeschedules.html';
            } else if (page === 'tickets') {
                window.location.href = 'tickets.html';
            }
        });
    });
    
</script>


</body>
</html>
