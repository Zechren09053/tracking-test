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
                        <li data-page="tickets">Tickets / Reservations</li>
                    </ul>

                    <!-- Settings, Help, and Logout Section -->
                    <ul class="nav settings-nav">
                        <li><a href="#">Settings</a></li>
                        <li><a href="#">Help</a></li>
                        <li><a href="#">Logout</a></li>
                    </ul>

                    <!-- Profile Section -->
                    <div class="profile">
                        <img src="profile.png" alt="Profile" style="width: 40px; height: 40px; border-radius: 50%;">
                        <div>
                            <strong>Username</strong><br>
                            user@email.com
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
    // Initialize the map
    var map = L.map('map').setView([14.5896, 121.0360], 13); // Initial coordinates near Pasig River
    var markers = {};  // Store the ferry markers to avoid duplicates

    // Tile Layer
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(map);

    // Predefined route coordinates along the Pasig River (example coordinates, adjust to actual path)
    const pasigRiverRoute = [
        [14.5896, 121.0360], // Example point 1 (near station in Pasig)
        [14.5910, 121.0390], // Example point 2
        [14.5930, 121.0420], // Example point 3
        [14.5950, 121.0450], // Example point 4
        [14.5970, 121.0480], // Example point 5
        [14.5990, 121.0500], // Example point 6
        [14.6010, 121.0530], // Example point 7
        [14.6030, 121.0550], // Example point 8
        // Add more coordinates as needed to reflect the Pasig River path
    ];

    // Draw the predefined Pasig River ferry route line on the map
    const riverRoute = L.polyline(pasigRiverRoute, {
        color: 'blue',
        weight: 4,
        opacity: 0.7,
        smoothFactor: 1
    }).addTo(map);

    // Optional: fit the map view to the route
    map.fitBounds(riverRoute.getBounds());

    // Function to fetch ferry data from the backend (getFerries.php)
    function fetchFerryData() {
        $.ajax({
            url: 'getFerries.php', // The PHP file you created
            method: 'GET',
            dataType: 'json',
            success: function(data) {
                $('#ferry-list').empty(); // Clear the list before adding new data
                if (data.length === 0) {
                    $('#ferry-list').append('<p>No ferries are currently available.</p>');
                }
                data.forEach(function(ferry) {
                    // Display the ferry details in the list, including latitude and longitude
                    const ferryElement = `
                        <div class="boat-card" data-lat="${ferry.latitude}" data-lng="${ferry.longitude}">
                            <div class="top"><strong>${ferry.name}</strong>
                            </div>
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

                    // Add ferry marker to map only if not already added
                    if (ferry.latitude && ferry.longitude) {
    if (!markers[ferry.name]) {
        addFerryMarker(ferry.latitude, ferry.longitude, ferry.name);
    } else {
        // Update position if marker already exists
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

    // Call the function every 5 seconds to update the ferry data in real-time
    setInterval(fetchFerryData, 5000);

    // Initial fetch
    fetchFerryData();

    // Function to add ferry markers to the map
    function addFerryMarker(latitude, longitude, ferryName) {
        // Add marker to map
        const marker = L.marker([latitude, longitude]).addTo(map)
            .bindPopup(ferryName)
            .openPopup();
        markers[ferryName] = marker; // Store the marker in the markers object
    }

    // JavaScript to handle the click functionality for ferry cards
    $(document).on('click', '.boat-card', function() {
        const lat = $(this).data('lat');
        const lng = $(this).data('lng');
        const name = $(this).find('.top strong').text(); // Get ferry name from card

        // Pan to the ferry's location and zoom in
        map.setView([lat, lng], 15); // Zoom level 15 for better visibility

        // Optionally add a marker at the ferry's location
        if (!markers[name]) {
            addFerryMarker(lat, lng, name); // Only add the marker if not already on the map
        }
    });

    // Ensure the map container respects the border radius
    document.getElementById('map').style.borderRadius = '24px';
    document.getElementById('map').style.overflow = 'hidden';

    // JavaScript to handle the click functionality for li elements
    const navItems = document.querySelectorAll('.nav li');

    navItems.forEach(item => {
        item.addEventListener('click', function() {
            // Remove the 'active' class from all items
            navItems.forEach(item => item.classList.remove('active'));

            // Add the 'active' class to the clicked item
            item.classList.add('active');

            // Handle the page navigation based on the clicked list item's data-page attribute
            const page = item.getAttribute('data-page');
            if (page === 'dashboard') {
                window.location.href = 'dashboard.html';  // Update with actual path
            } else if (page === 'analytics') {
                window.location.href = 'analytics.html';  // Update with actual path
            } else if (page === 'tracking') {
                window.location.href = 'tracking.html';  // Update with actual path
            } else if (page === 'ferrymngt') {
                window.location.href = 'ferrymngt.php';  // Update with actual path
            } else if (page === 'routeschedules') {
                window.location.href = 'routeschedules.html';  // Update with actual path
            } else if (page === 'tickets') {
                window.location.href = 'tickets.html';  // Update with actual path
            }
        });
    });
</script>

</body>
</html>
