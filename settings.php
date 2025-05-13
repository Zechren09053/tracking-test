<?php
session_start();
$servername = "localhost";
$db_username = "PRFS";
$db_password = "1111";
$dbname = "prfs";

// Enable MySQLi exceptions
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$conn = new mysqli($servername, $db_username, $db_password, $dbname);

// Define app version
define('APP_VERSION', '1.2.5');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$username = $_SESSION['username'];
$message = '';
$messageType = '';

// Handle profile picture upload
if (isset($_POST['upload_picture'])) {
    $target_dir = "uploads/";
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    $target_file = $target_dir . basename($_FILES["profile_pic"]["name"]);
    $uploadOk = 1;
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    if (isset($_FILES["profile_pic"])) {
        $check = getimagesize($_FILES["profile_pic"]["tmp_name"]);
        if ($check !== false) {
            $uploadOk = 1;
        } else {
            $message = "File is not an image.";
            $messageType = "error";
            $uploadOk = 0;
        }
    }

    if ($_FILES["profile_pic"]["size"] > 5000000) {
        $message = "Sorry, your file is too large.";
        $messageType = "error";
        $uploadOk = 0;
    }

    if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif") {
        $message = "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
        $messageType = "error";
        $uploadOk = 0;
    }

    $new_filename = uniqid('profile_') . '.' . $imageFileType;
    $target_file = $target_dir . $new_filename;

    if ($uploadOk == 1) {
        if (move_uploaded_file($_FILES["profile_pic"]["tmp_name"], $target_file)) {
            $profile_pic_path = $target_file;
            $update_sql = "UPDATE staff_users SET profile_pic = ?, updated_at = NOW() WHERE username = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("ss", $profile_pic_path, $username);

            if ($update_stmt->execute()) {
                $message = "Profile picture updated successfully.";
                $messageType = "success";
            } else {
                $message = "Error updating profile picture in database: " . $conn->error;
                $messageType = "error";
            }
            $update_stmt->close();
        } else {
            $message = "Sorry, there was an error uploading your file.";
            $messageType = "error";
        }
    }
}

// Handle email update
if (isset($_POST['update_email'])) {
    $new_email = $_POST['new_email'];
    $password = $_POST['password'];

    if (!filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
        $message = "Invalid email format";
        $messageType = "error";
    } else {
        $verify_sql = "SELECT password FROM staff_users WHERE username = ?";
        $verify_stmt = $conn->prepare($verify_sql);
        $verify_stmt->bind_param("s", $username);
        $verify_stmt->execute();
        $verify_result = $verify_stmt->get_result();

        if ($verify_result->num_rows > 0) {
            $user_data = $verify_result->fetch_assoc();
            $stored_password = $user_data['password'];

            if (password_verify($password, $stored_password)) {
                try {
                    $update_sql = "UPDATE staff_users SET email = ?, updated_at = NOW() WHERE username = ?";
                    $update_stmt = $conn->prepare($update_sql);
                    $update_stmt->bind_param("ss", $new_email, $username);
                    $update_stmt->execute();
                    $message = "Email updated successfully.";
                    $messageType = "success";
                    $update_stmt->close();
                } catch (mysqli_sql_exception $e) {
                    if ($e->getCode() == 1062) {
                        $message = "This email is already in use.";
                    } else {
                        $message = "Database error: " . $e->getMessage();
                    }
                    $messageType = "error";
                }
            } else {
                $message = "Incorrect password. Email update failed.";
                $messageType = "error";
            }
        } else {
            $message = "User not found.";
            $messageType = "error";
        }
        $verify_stmt->close();
    }
}

// Fetch user data
$sql = "SELECT staff_id, username, email, role, first_name, middle_name, last_name, position, profile_pic 
        FROM staff_users 
        WHERE username = ? AND is_active = 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    $name = $user['first_name'] . ' ' . $user['last_name'];
    $email = $user['email'];
    $role = $user['role'];
    $position = $user['position'];
    $profile_pic = $user['profile_pic'] ?? 'uploads/default.png';
    $staff_id = $user['staff_id'];
} else {
    $name = 'Unknown User';
    $email = 'unknown@email.com';
    $role = '';
    $position = '';
    $profile_pic = 'uploads/default.png';
    $staff_id = 0;
}

$stmt->close();
$conn->close();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - PRFS Management</title>
    <link rel="stylesheet" href="Db.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
body { margin: 0; font-family: Arial, sans-serif; background-color: #222; color: #f5f5f5; }
.main-container { display: flex; flex-direction: column; }
.container { display: flex; flex: 1; position: relative; }
.sidebar-wrapper { position: relative; }
.sidebar { width: 250px; transition: width 0.3s ease; overflow: hidden; background-color: #333; }
.sidebar-collapsed { width: 70px !important; }
.sidebar-collapsed .logo-text, .sidebar-collapsed .nav-text, .sidebar-collapsed .profile-info, .sidebar-collapsed .settings-text { display: none; }
.sidebar-collapsed .nav li, .sidebar-collapsed .settings-nav li { text-align: center; padding: 15px; }
.sidebar-toggle { position: absolute; top: 20px; left: 250px; background: #00b0ff; width: 15px; height: 40px; display: flex; align-items: center; justify-content: center; color: white; cursor: pointer; z-index: 1000; box-shadow: 0 2px 5px rgba(0,0,0,0.2); transition: left 0.3s ease; border-top-right-radius: 15px; border-bottom-right-radius: 15px; }
.sidebar-collapsed ~ .sidebar-toggle { left: 70px; }
.nav, .settings-nav { list-style: none; padding: 0; margin: 0; }
.nav li, .settings-nav li { padding: 15px; cursor: pointer; }
.nav li:hover, .settings-nav li:hover { background-color: #444; }
.nav-item-content { display: flex; align-items: center; }
.nav-text, .settings-text { margin-left: 10px; }
.main { flex: 1; padding: 20px; transition: margin-left 0.3s ease; margin-left: 25px; background-color: #222; overflow-y: auto; border-radius: 10px; }
.content-expanded { margin-left: 70px !important; }
.header h1 { margin: 0 0 10px; color: #f5f5f5; }
.sidebar-top { display: flex; flex-direction: column; height: 100%; justify-content: space-between; }
.main-nav-container { flex-grow: 0; }
.settings-section { background-color: #444; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.3); margin-bottom: 20px; }
.settings-section h2 { margin-top: 0; color: #f5f5f5; border-bottom: 1px solid #555; padding-bottom: 10px; }
.form-group { margin-bottom: 20px; }
.form-group label { display: block; margin-bottom: 5px; font-weight: bold; color: #f5f5f5; }
.form-control { width: 100%; padding: 8px; border: 1px solid #555; border-radius: 4px; box-sizing: border-box; background-color: #333; color: #f5f5f5; }
.btn { padding: 8px 16px; border: none; border-radius: 4px; cursor: pointer; font-weight: bold; }
.btn-primary { background-color: #00b0ff; color: white; }
.btn-primary:hover { background-color: #0099e6; }
.profile-preview { text-align: center; margin-bottom: 20px; }
.profile-preview img { width: 150px; height: 150px; border-radius: 50%; object-fit: cover; border: 3px solid #00b0ff; }
.alert { padding: 10px; border-radius: 4px; margin-bottom: 15px; }
.alert-success { background-color: #265735; color: #d4edda; border: 1px solid #c3e6cb; }
.alert-error { background-color: #5c2029; color: #f8d7da; border: 1px solid #f5c6cb; }
.version-info { text-align: right; color: #aaa; font-size: 0.8em; margin-top: 30px; }
.main::-webkit-scrollbar { width: 12px; }
.main::-webkit-scrollbar-track { background: #333; border-radius: 10px; }
.main::-webkit-scrollbar-thumb { background-color: #00b0ff; border-radius:  10px; border: 3px solid #333; }
.main::-webkit-scrollbar-thumb:hover { background: #0099e6; }
.main { scrollbar-width: thin; scrollbar-color: #00b0ff #333; }
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
                            <li data-page="Usersection">
                                <div class="nav-item-content">
                                    <i class="fas fa-users"></i>
                                    <span class="nav-text">User Section</span>
                                </div>
                            </li>
                        </ul>
                    </div>
                    <div class="settings-profile-container">
                        <ul class="nav settings-nav">
                            <li class="active"><a href="settings.php"><div class="nav-item-content"><i class="fas fa-cog"></i><span class="settings-text">Settings</span></div></a></li>
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
                    <h1>Settings</h1>
                </div>
                <div class="content">
                    <?php if (!empty($message)): ?>
                        <div class="alert alert-<?= $messageType ?>">
                            <?= $message ?>
                        </div>
                    <?php endif; ?>
                    
                    <div class="settings-section">
                        <h2>Profile Picture</h2>
                        <div class="profile-preview">
                            <img src="<?php echo htmlspecialchars($profile_pic); ?>" alt="Profile Picture" />
                        </div>
                        <form action="settings.php" method="post" enctype="multipart/form-data">
                            <div class="form-group">
                                <label for="profile_pic">Select New Profile Picture:</label>
                                <input type="file" id="profile_pic" name="profile_pic" class="form-control" accept="image/*" required>
                            </div>
                            <button type="submit" name="upload_picture" class="btn btn-primary">Upload Picture</button>
                        </form>
                    </div>
                    
                    <div class="settings-section">
                        <h2>Email Settings</h2>
                        <form action="settings.php" method="post">
                            <div class="form-group">
                                <label for="current_email">Current Email:</label>
                                <input type="email" id="current_email" value="<?= htmlspecialchars($email) ?>" class="form-control" readonly>
                            </div>
                            <div class="form-group">
                                <label for="new_email">New Email:</label>
                                <input type="email" id="new_email" name="new_email" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="password">Enter Your Password to Confirm Changes:</label>
                                <input type="password" id="password" name="password" class="form-control" required>
                            </div>
                            <button type="submit" name="update_email" class="btn btn-primary">Update Email</button>
                        </form>
                    </div>
                    
                    <div class="settings-section">
                        <h2>Account Information</h2>
                        <p><strong>Staff ID:</strong> <?= htmlspecialchars($staff_id) ?></p>
                        <p><strong>Username:</strong> <?= htmlspecialchars($username) ?></p>
                        <p><strong>Name:</strong> <?= htmlspecialchars($name) ?></p>
                        <p><strong>Role:</strong> <?= htmlspecialchars(ucfirst($role)) ?></p>
                        <p><strong>Position:</strong> <?= htmlspecialchars($position) ?></p>
                    </div>
                    
                    <div class="version-info">
                        <p>Application Version: <?= APP_VERSION ?></p>
                    </div>
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