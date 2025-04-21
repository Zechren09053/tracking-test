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
                        <li><a href="#">Logout</a></li>
                    </ul>

                    <div class="profile">
    <img src="testprofile.png" alt="Profile">
    <div class="profile-info">
    <strong class="profile-name" title="Jong Pantry The Fourth Of His Name">Jong Pantry The Fourth Of His Name</strong>
<span class="profile-email" title="Pasigriverboatlongemailthatkeepsgoing@email.com">Pasigriverboatlongemailthatkeepsgoing@email.com</span>

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

    const pasigRiverRoute = [
        [14.5896, 121.0360],
        [14.5910, 121.0390],
        [14.5930, 121.0420],
        [14.5950, 121.0450],
        [14.5970, 121.0480],
        [14.5990, 121.0500],
        [14.6010, 121.0530],
        [14.6030, 121.0550],
    ];

    const riverRoute = L.polyline(pasigRiverRoute, {
        color: 'blue',
        weight: 4,
        opacity: 0.7,
        smoothFactor: 1
    }).addTo(map);

    // Only fit once when map initializes
    map.fitBounds(riverRoute.getBounds());

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
                    const ferryElement = `
                        <div class="boat-card" data-lat="${ferry.latitude}" data-lng="${ferry.longitude}">
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
