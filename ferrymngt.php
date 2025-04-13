<?php
// Database connection settings
$servername = "localhost";
$username = "PRFS";
$password = "1111";
$dbname = "prfs";

// Create a new MySQLi connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check for connection errors
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Fetch ferry data
$sql = "SELECT * FROM ferries";
$result = $conn->query($sql);
$ferries = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $ferries[] = $row;
    }
}

// Close the connection after fetching data
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Ferry Admin Dashboard</title>
    <link rel="stylesheet" href="Db.css">
    <link rel="stylesheet" href="ferrymanagement.css"> <!-- External CSS for styling -->
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
                        <li><a href="Dashboard.php">Dashboard</a></li>
                        <li>Analytics</li>
                        <li>Tracking</li>
                        <li class="active">Ferry Management</li>
                        <li>Route and Schedules</li>
                        <li>Tickets / Reservations</li>
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
                    <h1>Ferry Management</h1>
                </div>

                <div class="ferry-management" id="ferry-list">
                    <!-- Ferry cards will be dynamically populated here -->
                    <?php foreach ($ferries as $ferry): ?>
                        <div class="ferry-card" id="ferry-row-<?= $ferry['id'] ?>">
                            <div class="ferry-info">
                                <strong><?= htmlspecialchars($ferry['name']) ?></strong><br>
                                <span>Operator: <?= htmlspecialchars($ferry['operator']) ?></span><br>
                                <span>Active Time: <span id="active-time-<?= $ferry['id'] ?>"><?= $ferry['active_time'] ?></span> mins</span>
                            </div>

                            <!-- Toggle Switch for Active/Inactive -->
                            <div class="ferry-status">
                                <label class="switch">
                                    <input type="checkbox" data-ferry-id="<?= $ferry['id'] ?>" class="status-switch" <?= $ferry['status'] == 'active' ? 'checked' : '' ?>>
                                    <span class="slider"></span>
                                </label>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        // Fetch ferry data and update the list dynamically
        function fetchFerryData() {
            $.ajax({
                url: 'getFerries.php', // Backend PHP to fetch ferry data
                method: 'GET',
                dataType: 'json',
                success: function(data) {
                    let ferryListHtml = '';
                    if (data.length > 0) {
                        data.forEach(function(ferry) {
                            ferryListHtml += `
                                <div class="ferry-card" id="ferry-row-${ferry.id}">
                                    <div class="ferry-info">
                                        <strong>${ferry.name}</strong><br>
                                        <span>Operator: ${ferry.operator}</span><br>
                                        <span>Active Time: <span id="active-time-${ferry.id}">${ferry.active_time}</span> mins</span>
                                    </div>
                                    <div class="ferry-status">
                                        <label class="switch">
                                            <input type="checkbox" data-ferry-id="${ferry.id}" class="status-switch" ${ferry.status == 'active' ? 'checked' : ''}>
                                            <span class="slider"></span>
                                        </label>
                                    </div>
                                </div>
                            `;
                        });
                    } else {
                        ferryListHtml = '<p>No ferries available.</p>';
                    }
                    $('#ferry-list').html(ferryListHtml);
                },
                error: function() {
                    alert('Error fetching ferry data');
                }
            });
        }

        // Fetch ferry data every 5 seconds to update the list
        setInterval(fetchFerryData, 5000);
        
        // Initial fetch
        $(document).ready(function() {
            fetchFerryData();
        });

        // Function to update ferry status via AJAX
        $(document).on('change', '.status-switch', function() {
            var ferryId = $(this).data('ferry-id');
            var newStatus = $(this).prop('checked') ? 'active' : 'inactive';

            $.ajax({
                url: 'updateFerryStatus.php', // File to update ferry status
                method: 'POST',
                data: { ferry_id: ferryId, status: newStatus },
                success: function(response) {
                    fetchActiveTime(ferryId); // Refresh active time after updating status
                },
                error: function() {
                    alert('Error updating ferry status');
                }
            });
        });

        // Function to fetch active time for each ferry
        function fetchActiveTime(ferryId) {
            $.ajax({
                url: 'getActiveTime.php', // File to fetch active time
                method: 'GET',
                data: { ferry_id: ferryId },
                dataType: 'json',
                success: function(data) {
                    // Update the active time in the UI dynamically
                    $('#active-time-' + ferryId).text(data.active_time + ' minutes');
                },
                error: function() {
                    console.log('Error fetching active time for ferry ' + ferryId);
                }
            });
        }
    </script>
</body>
</html>
