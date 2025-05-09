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

// Fetch user details
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
    $profile_pic = $user['profile_pic'] ?? 'uploads/default.png';
} else {
    $name = 'Unknown User';
    $email = 'unknown@email.com';
    $profile_pic = 'uploads/default.png';
}

$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Ferry Tracker</title>
    <link rel="stylesheet" href="Db.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        #map {
            height: 70vh;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.15);
        } 
        .ferry-label {
            background-color: #fff;
            border-radius: 4px;
            padding: 2px 6px;
            font-weight: bold;
            font-size: 12px;
            color: #333;
            border: 1px solid #aaa;
            white-space: pre;
            cursor: pointer;
        }
        #simulateBtn {
            padding: 8px 16px;
            background-color: seagreen;
            color: white;
            border: none;
            border-radius: 5px;
            margin: 15px 0;
            cursor: pointer;
        }
        #simulateBtn:hover {
            background-color: darkgreen;
        }
    </style>
</head>
<body>
<div class="main-container">
    <div class="container">
        <div class="sidebar-wrapper">
            <div class="sidebar">
                <div>
                    <div class="logo">
                        <img src="PasigRiverFerryServiceLogo.png" alt="Logo" style="width: 30px; height: 30px;">
                        PRFS MANAGEMENT
                    </div>
                    <ul class="nav">
                        <li data-page="dashboard">Dashboard</li>
                        <li data-page="analytics">Analytics</li>
                        <li class="active" data-page="tracking">Tracking</li>
                        <li data-page="ferrymngt">Ferry Management</li>
                        <li data-page="routeschedules">Route and Schedules</li>
                        <li data-page="Usersection">User Section</li>
                    </ul>
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
        
        <!-- Main Content: Map and Button -->
        <div style="flex: 1; padding: 20px;">
        <h2>Ferry Tracking View</h2>
            <button id="simulateBtn">Start Simulation</button>
            <div id="map">
                <iframe id="ferryMap" src="vgps.php" style="width: 100%; height: 70vh; border: none; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.15);"></iframe>
            </div>
        </div>
    </div>
</div>

<!-- Navigation Script -->
<script>
    const navItems = document.querySelectorAll('.nav li');
    navItems.forEach(item => {
        item.addEventListener('click', function() {
            navItems.forEach(item => item.classList.remove('active'));
            item.classList.add('active');
            const page = item.getAttribute('data-page');
            if (page === 'dashboard') window.location.href = 'Dashboard.php';
            else if (page === 'analytics') window.location.href = 'analytics.php';
            else if (page === 'tracking') window.location.href = 'tracking.php';
            else if (page === 'ferrymngt') window.location.href = 'ferrymngt.php';
            else if (page === 'routeschedules') window.location.href = 'routeschedules.php';
            else if (page === 'Usersection') window.location.href = 'template.php';
        });
    });

    // Toggle simulation iframe
    let simActive = false;
    document.getElementById("simulateBtn").addEventListener("click", () => {
        const iframe = document.getElementById("ferryMap");
        simActive = !simActive;

        iframe.src = simActive ? "gpsfleet.php" : "vgps.php";
        document.getElementById("simulateBtn").textContent = simActive ? "Stop Simulation" : "Start Simulation";
    });
</script>
</body>
</html>
