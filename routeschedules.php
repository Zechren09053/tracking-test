<?php
session_start();
$servername = "localhost";
$db_username = "PRFS";
$db_password = "1111";
$dbname = "prfs";

// Create DB connection
$conn = new mysqli($servername, $db_username, $db_password, $dbname);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

// Get user data
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

// Fetch upstream schedules
$upstream = [];
$upstream_sql = "SELECT row_id, col_id, schedule_time FROM upstream_schedules ORDER BY row_id, col_id";
$result = $conn->query($upstream_sql);
while ($row = $result->fetch_assoc()) {
    $upstream[$row['row_id']][$row['col_id']] = $row['schedule_time'];
}

// Fetch downstream schedules
$downstream = [];
$downstream_sql = "SELECT row_id, col_id, schedule_time FROM downstream_schedules ORDER BY row_id, col_id";
$result = $conn->query($downstream_sql);
while ($row = $result->fetch_assoc()) {
    $downstream[$row['row_id']][$row['col_id']] = $row['schedule_time'];
}
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
                    <ul class="nav settings-nav">
                        <li><a href="#">Settings</a></li>
                        <li><a href="#">Help</a></li>
                        <li><a href="login.php">Logout</a></li>
                    </ul>
                    <div class="profile">
                        <img src="<?= htmlspecialchars($profile_pic) ?>" alt="Profile Picture" />
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

            <!-- Upstream Table -->
            <div class="table-container">
                <h2 style="margin-bottom: 20px;">Upstream</h2>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Escolta</th>
                            <th>Lawton</th>
                            <th>Quinta</th>
                            <th>PUP</th>
                            <th>Sta. Ana</th>
                            <th>Lambingan</th>
                            <th>Valenzuela</th>
                            <th>Hulo</th>
                            <th>Guadalupe</th>
                            <th>San Joaquin</th>
                            <th>Kalawaan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php for ($i = 1; $i <= 8; $i++): ?>
                            <tr>
                                <?php for ($j = 1; $j <= 11; $j++): ?>
                                    <td><?= isset($upstream[$i][$j]) ? htmlspecialchars($upstream[$i][$j]) : '' ?></td>
                                <?php endfor; ?>
                            </tr>
                        <?php endfor; ?>
                    </tbody>
                </table>
            </div>

            <!-- Downstream Table -->
            <div class="table-container">
                <h2 style="margin-bottom: 20px;">Downstream</h2>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Escolta</th>
                            <th>Lawton</th>
                            <th>Quinta</th>
                            <th>PUP</th>
                            <th>Sta. Ana</th>
                            <th>Lambingan</th>
                            <th>Valenzuela</th>
                            <th>Hulo</th>
                            <th>Guadalupe</th>
                            <th>San Joaquin</th>
                            <th>Kalawaan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php for ($i = 1; $i <= 8; $i++): ?>
                            <tr>
                                <?php for ($j = 1; $j <= 11; $j++): ?>
                                    <td><?= isset($downstream[$i][$j]) ? htmlspecialchars($downstream[$i][$j]) : '' ?></td>
                                <?php endfor; ?>
                            </tr>
                        <?php endfor; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

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
            } else if (page === 'Usersection') {
                window.location.href = 'template.php';
            }
        });
    });
</script>
</body>
</html>
