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

// Fetch ferry data
$sql = "SELECT * FROM ferries";
$result = $conn->query($sql);
$ferries = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $ferries[] = $row;
    }
}

// Close the connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Ferry Admin - Analytics</title>
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
                        <li data-page="dashboard">Dashboard</li>
                        <li class="active" data-page="analytics">Analytics</li>
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
                    <img src="<?php echo htmlspecialchars($profile_pic); ?>" alt="Profile Picture" />
    <div class="profile-info">
    <strong class="profile-name" title="<?= htmlspecialchars($name) ?>"><?= htmlspecialchars($name) ?></strong>
<span class="profile-email" title="<?= htmlspecialchars($email) ?>"><?= htmlspecialchars($email) ?></span>

    </div>
</div>
                   
                </div>
            </div>

            <!-- Main Analytics Page -->
            <div class="main">
                <div class="header">
                    <h1>Analytics</h1>
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

                <div class="charts">
                    <!-- Add any charts or graphs you want to display analytics -->
                    <div class="chart">
                        <h3>Passenger Growth Over Time</h3>
                        <!-- You can replace this with an actual chart library like Chart.js or something else -->
                        <div class="chart-placeholder">Chart Placeholder</div>
                    </div>
                    <div class="chart">
                        <h3>Tickets Sold Over Time</h3>
                        <!-- Replace with chart -->
                        <div class="chart-placeholder">Chart Placeholder</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add your script tags for your custom JavaScript -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script>
        // Sidebar Navigation
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
                    window.location.href = 'gpsfleet.php';
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
