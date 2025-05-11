<?php
session_start();
$servername = "localhost";
$db_username = "PRFS";
$db_password = "1111";
$dbname = "prfs";
$conn = new mysqli($servername, $db_username, $db_password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ferry Admin Dashboard</title>
    <link rel="stylesheet" href="Db.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { margin: 0; font-family: Arial, sans-serif; }
.main-container { display: flex; flex-direction: column; }
.container { display: flex; flex: 1; position: relative; }
.sidebar-wrapper { position: relative; }
.sidebar { width: 250px; transition: width 0.3s ease; overflow: hidden; }
.sidebar-collapsed { width: 70px !important; }
.sidebar-collapsed .logo-text, .sidebar-collapsed .nav-text, .sidebar-collapsed .profile-info, .sidebar-collapsed .settings-text { display: none; }
.sidebar-collapsed .nav li, .sidebar-collapsed .settings-nav li { text-align: center; padding: 15px 15px; }
.sidebar-toggle { position: absolute; top: 20px; left: 250px; background: #00b0ff; width: 15px; height: 40px; display: flex; align-items: center; justify-content: center; color: white; cursor: pointer; z-index: 1000; box-shadow: 0 2px 5px rgba(0,0,0,0.2); transition: left 0.3s ease; border-top-right-radius: 15px; border-bottom-right-radius: 15px; }
.sidebar-collapsed ~ .sidebar-toggle { left: 70px; }
.nav, .settings-nav { list-style: none; padding: 0; margin: 0; }
.nav li, .settings-nav li { padding: 15px; cursor: pointer; }
.nav li:hover, .settings-nav li:hover { background-color: #333; }
.nav-item-content { display: flex; align-items: center; }
.nav-text, .settings-text { margin-left: 10px; }
.main { flex: 1; padding: 20px; transition: margin-left 0.3s ease; margin-left: 25px; }
.content-expanded { margin-left: 70px !important; }
.header h1 { margin: 0 0 10px; }
.sidebar-top { display: flex; flex-direction: column; height: 100%; justify-content: space-between; }
.main-nav-container { flex-grow: 0; }
    </style>
</head>
<body>
    <div class="main-container">
        <div class="container">
            <!-- Toggle button now outside sidebar wrapper -->
            <div class="sidebar" id="sidebar">
                <div class="sidebar-top">
                    <div class="main-nav-container">
                        <div class="logo">
                            <img src="PasigRiverFerryServiceLogo.png" alt="Logo" style="width: 30px; height: 30px;">
                            <span class="logo-text">PRFS MANAGEMENT</span>
                        </div>
                        <ul class="nav">
                            <li data-page="dashboard">
                                <div class="nav-item-content">
                                    <i class="fas fa-tachometer-alt"></i>
                                    <span class="nav-text">Dashboard</span>
                                </div>
                            </li>
                            <li data-page="analytics">
                                <div class="nav-item-content">
                                    <i class="fas fa-chart-line"></i>
                                    <span class="nav-text">Analytics</span>
                                </div>
                            </li>
                            <li data-page="tracking">
                                <div class="nav-item-content">
                                    <i class="fas fa-map-marker-alt"></i>
                                    <span class="nav-text">Tracking</span>
                                </div>
                            </li>
                            <li data-page="ferrymngt">
                                <div class="nav-item-content">
                                    <i class="fas fa-ship"></i>
                                    <span class="nav-text">Ferry Management</span>
                                </div>
                            </li>
                            <li data-page="routeschedules">
                                <div class="nav-item-content">
                                    <i class="fas fa-route"></i>
                                    <span class="nav-text">Route and Schedules</span>
                                </div>
                            </li>
                            <li class="active" data-page="Usersection">
                                <div class="nav-item-content">
                                    <i class="fas fa-users"></i>
                                    <span class="nav-text">User Section</span>
                                </div>
                            </li>
                        </ul>
                    </div>
                    <div class="settings-profile-container">
                        <ul class="nav settings-nav">
                            <li><a href="#"><div class="nav-item-content"><i class="fas fa-cog"></i><span class="settings-text">Settings</span></div></a></li>
                            <li><a href="#"><div class="nav-item-content"><i class="fas fa-question-circle"></i><span class="settings-text">Help</span></div></a></li>
                            <li><a href="login.php"><div class="nav-item-content"><i class="fas fa-sign-out-alt"></i><span class="settings-text">Logout</span></div></a></li>
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

            <!-- Toggle button outside sidebar -->
            <div class="sidebar-toggle" id="sidebar-toggle">
                <i class="fas fa-chevron-left" id="toggle-icon"></i>
            </div>

            <!-- Main content -->
            <div class="main" id="main-content">
                <div class="header">
                    <h1>User Section</h1>
                </div>
                <div class="content">
                    <p>Welcome to the User Section. Content will be added here.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            $("#sidebar-toggle").click(function() {
                $("#sidebar").toggleClass("sidebar-collapsed");
                $("#main-content").toggleClass("content-expanded");

                if ($("#sidebar").hasClass("sidebar-collapsed")) {
                    $("#toggle-icon").removeClass("fa-chevron-left").addClass("fa-chevron-right");
                } else {
                    $("#toggle-icon").removeClass("fa-chevron-right").addClass("fa-chevron-left");
                }
            });

            const navItems = document.querySelectorAll('.nav li');
            navItems.forEach(item => {
                item.addEventListener('click', function() {
                    navItems.forEach(nav => nav.classList.remove('active'));
                    item.classList.add('active');
                    const page = item.getAttribute('data-page');
                    if (page === 'dashboard') window.location.href = 'Dashboard.php';
                    else if (page === 'analytics') window.location.href = 'analytics.php';
                    else if (page === 'tracking') window.location.href = 'Tracking.php';
                    else if (page === 'ferrymngt') window.location.href = 'ferrymngt.php';
                    else if (page === 'routeschedules') window.location.href = 'routeschedules.php';
                    else if (page === 'Usersection') window.location.href = 'template.php';
                });
            });
        });
    </script>
</body>
</html>
