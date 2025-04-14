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
                        <div class="boat-list-header">
                            <h2>Ferry Tracking</h2>
                        </div>
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
                        // Display the ferry details in the list
                        const ferryElement = `
                            <div class="boat-card">
                                <div class="top">
                                    <strong>${ferry.name}</strong>
                                    <span>View Location</span>
                                </div>
                                <div class="bottom">
                                    Active Time: ${ferry.active_time} mins<br>
                                    Status: ${ferry.status}<br>
                                    Operator: ${ferry.operator}
                                </div>
                            </div>
                        `;
                        $('#ferry-list').append(ferryElement);

                        // Add the ferry's location to the map
                        if (ferry.latitude && ferry.longitude) {
                            addFerryMarker(ferry.latitude, ferry.longitude, ferry.name);
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

        // Leaflet Map Initialization
        var map = L.map('map').setView([51.505, -0.09], 13); // Initial coordinates (can be updated dynamically)

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);

        // Function to add ferry markers to the map
        function addFerryMarker(latitude, longitude, ferryName) {
            L.marker([latitude, longitude]).addTo(map)
                .bindPopup(ferryName)  // Display ferry name in the popup
                .openPopup();
        }

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
