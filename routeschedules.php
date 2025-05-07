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
    <style>
        .edit-wrapper {
            display: flex;
            align-items: center;
        }
        .edit-input {
            flex: 1;
            padding: 2px 4px;
            font-size: 14px;
            width: 90px;
            min-width: 70px;
        }
        .save-btn {
            margin-left: 4px;
            cursor: pointer;
            padding: 2px 6px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 3px;
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
</script>
</body>
</html>
