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
</head>
<body>
    <!-- Main container with rounded edges -->
    <div class="main-container">
        <div class="container">
        <div class="sidebar-wrapper">
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
                        <li data-page="analytics">Analytics</li>
                        <li data-page="tracking">Tracking</li>
                        <li data-page="ferrymngt">Ferry Management</li>
                        <li class="active" data-page="routeschedules">Route and Schedules</li>
                        <li data-page="Usersection">User Section</li>
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
        </div>
           
            <div class="content-area">
    <h2>Ferry Route and Schedules</h2>

    <!-- First Table -->
    <div class="table-container">
        <table class="data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Route Name</th>
                    <th>Origin</th>
                    <th>Destination</th>
                    <th>Stops</th>
                    <th>Estimated Time</th>
                    <th>Schedule</th>
                    <th>Status</th>
                    <th>Capacity</th>
                    <th>Vessel</th>
                    <th>Operator</th>
                    <th>Departure</th>
                    <th>Arrival</th>
                </tr>
            </thead>
            <tbody>
                <?php
                for ($i = 0; $i < 8; $i++) {
                    echo "<tr>";
                    for ($j = 0; $j < 13; $j++) {
                        echo "<td>Row " . ($i + 1) . ", Col " . ($j + 1) . "</td>";
                    }
                    echo "</tr>";
                }
                ?>
            </tbody>
        </table>
    </div>

    <!-- Second Table -->
    <div class="table-container">
        <table class="data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Route Name</th>
                    <th>Origin</th>
                    <th>Destination</th>
                    <th>Stops</th>
                    <th>Estimated Time</th>
                    <th>Schedule</th>
                    <th>Status</th>
                    <th>Capacity</th>
                    <th>Vessel</th>
                    <th>Operator</th>
                    <th>Departure</th>
                    <th>Arrival</th>
                </tr>
            </thead>
            <tbody>
                <?php
                for ($i = 0; $i < 8; $i++) {
                    echo "<tr>";
                    for ($j = 0; $j < 13; $j++) {
                        echo "<td>Row " . ($i + 1) . ", Col " . ($j + 1) . "</td>";
                    }
                    echo "</tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>


        
            </div>
        </div>
    </div>

    <!-- Redirection JavaScript -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
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
                    window.location.href = 'Tracking.php';
                } else if (page === 'ferrymngt') {
                    window.location.href = 'ferrymngt.php';
                } else if (page === 'routeschedules') {
                    window.location.href = 'routeschedules.php';
                } else if (page === 'User Section') {
                    window.location.href = 'template.php';
                }
            });
        });
    </script>
</body>
</html>
