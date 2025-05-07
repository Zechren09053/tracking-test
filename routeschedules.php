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
// Fetch announcements
$announcements = [];
$announcements_sql = "SELECT * FROM announcements ORDER BY created_at DESC";
$result = $conn->query($announcements_sql);
while ($row = $result->fetch_assoc()) {
    $announcements[] = $row;
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Ferry RSA</title>
    <link rel="stylesheet" href="Db.css">
    
</head>
<body>
<div class="main-container">
    <div class="container">
        <div class="sidebar-wrapper">
            <div class="sidebar">
                <!-- Sidebar content remains unchanged -->
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

        <!-- Content Area (tables) -->
        <div class="content-area">
            <h2>Ferry Route and Schedules</h2>
            <div id="clock" style="margin-bottom: 20px; font-size: 16px; color: #00b0ff;"></div>
            <!-- Upstream Table -->
            <div class="table-container">
                <h2 style="margin-bottom: 20px;">Upstream</h2>
                <table class="data-table" id="upstream-table">
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
                                    <td class="editable" data-row="<?= $i ?>" data-col="<?= $j ?>"><?= isset($upstream[$i][$j]) ? htmlspecialchars($upstream[$i][$j]) : '' ?></td>
                                <?php endfor; ?>
                            </tr>
                        <?php endfor; ?>
                    </tbody>
                </table>
            </div>

            <!-- Downstream Table -->
            <div class="table-container">
                <h2 style="margin-bottom: 20px;">Downstream</h2>
                <table class="data-table" id="downstream-table">
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
                                    <td class="editable" data-row="<?= $i ?>" data-col="<?= $j ?>"><?= isset($downstream[$i][$j]) ? htmlspecialchars($downstream[$i][$j]) : '' ?></td>
                                <?php endfor; ?>
                            </tr>
                        <?php endfor; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Right Container for Announcements and Other Info -->
        <div class="right-container">
        <div class="card">
    <h3>Add New Announcement</h3>
    <form action="add_announcement.php" method="POST">
        <div>
            <label for="title">Title</label>
            <input type="text" id="title" name="title" required>
        </div>
        <div>
            <label for="message">Message</label>
            <textarea id="message" name="message" required></textarea>
        </div>
        <div>
            <button type="submit">Add Announcement</button>
        </div>
    </form>
</div>
<div class="cardlog"> 
    <h3>Announcement Log</h3>
    
    <!-- Search Bar for Date -->
    <input type="date" id="announcementDateFilter" placeholder="Filter by Date">
    
    <?php if (empty($announcements)): ?>
        <p>No announcements yet.</p>
    <?php else: ?>
        <ul>
            <?php foreach ($announcements as $announcement): ?>
                <li><strong><?= htmlspecialchars($announcement['title']) ?></strong>
                    <p><?= htmlspecialchars($announcement['message']) ?></p>
                    <small><?= $announcement['created_at'] ?></small>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</div>



        </div>  
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
   $('.editable').on('dblclick', function () {
        var td = $(this);
        if (td.find('input').length > 0) return;

        var originalContent = td.text().trim();
        var row = td.data('row');
        var col = td.data('col');
        var route = td.closest('table').attr('id') === 'upstream-table' ? 'upstream' : 'downstream';

        // Add seconds if missing
        if (!originalContent.includes(':')) {
            originalContent += ':00'; // Ensure the format is HH:mm:ss
        }

        var input = $('<input type="time" class="edit-input"/>').val(originalContent.substring(0, 5)); // Grab just HH:mm
        var saveBtn = $('<button class="save-btn">Save</button>');

        var wrapper = $('<div class="edit-wrapper"></div>').append(input).append(saveBtn);
        td.empty().append(wrapper);

        input.focus();

        function saveEdit() {
            var updatedContent = input.val().trim() + ":00"; // Add seconds part
            if (updatedContent !== originalContent) {
                $.post('update_schedule.php', {
                    row: row,
                    col: col,
                    schedule_time: updatedContent,
                    route: route
                }, function (response) {
                    if (response === 'success') {
                        td.text(updatedContent);
                    } else {
                        td.text(originalContent);
                    }
                });
            } else {
                td.text(originalContent);
            }
        }

        saveBtn.on('click', saveEdit);

        input.on('keydown', function (e) {
            if (e.key === 'Enter') saveEdit();
            if (e.key === 'Escape') td.text(originalContent);
        });

        input.on('blur', function () {
            setTimeout(function () {
                if (!td.find(':focus').length) td.text(originalContent);
            }, 100);
        });
    });

    const navItems = document.querySelectorAll('.nav li');
    navItems.forEach(item => {
        item.addEventListener('click', function () {
            navItems.forEach(item => item.classList.remove('active'));
            item.classList.add('active');
            const page = item.getAttribute('data-page');
            const pages = {
                dashboard: 'Dashboard.php',
                analytics: 'analytics.php',
                tracking: 'Tracking.php',
                ferrymngt: 'ferrymngt.php',
                routeschedules: 'routeschedules.php',
                Usersection: 'template.php'
            };
            if (pages[page]) window.location.href = pages[page];
        });
    });
    document.getElementById('announcementDateFilter').addEventListener('change', function() {
    const filterDate = this.value;
    const items = document.querySelectorAll('.cardlog ul li');
    
    items.forEach(item => {
        const announcementDate = item.querySelector('small').innerText;
        
        // Convert announcement date to a comparable format (e.g., YYYY-MM-DD)
        const announcementDateFormatted = new Date(announcementDate).toISOString().split('T')[0];
        
        if (filterDate && announcementDateFormatted !== filterDate) {
            item.style.display = 'none'; // Hide the item if the dates don't match
        } else {
            item.style.display = ''; // Show the item if the dates match or no date is selected
        }
    });
});
function updateClock() {
    const now = new Date();
    const timeString = now.toLocaleTimeString();
    const dateString = now.toLocaleDateString();
    document.getElementById("clock").textContent = `${dateString} | ${timeString}`;
  }

  setInterval(updateClock, 1000);
  updateClock(); // run once on load
</script>
</body>
</html>
