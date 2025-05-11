<?php
session_start();

require 'db_connect.php'; // Centralized DB connection

// Get user data
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

// Fetch announcements - IMPROVED QUERY
$announcements = [];
$announcements_sql = "SELECT * FROM announcements 
    WHERE (display_from <= CURDATE() AND 
           DATE_ADD(display_from, INTERVAL display_duration DAY) >= CURDATE())
    OR display_from > CURDATE()  
    ORDER BY created_at DESC 
    LIMIT 10";

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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>

.announcement-list { list-style: none; padding: 0; margin: 0; }
.announcement-list li { background-color: #444; border-radius: 8px; padding: 12px; margin-bottom: 10px; border-left: 4px solid #00b0ff; box-shadow: 0 2px 4px rgba(0,0,0,0.1); transition: all 0.3s ease; }
.announcement-list li:hover { background-color:rgb(124, 124, 124); }
.announcement-list li strong { display: block; font-size: 16px; margin-bottom: 5px; color: #333; }
.announcement-list li p { margin: 8px 0; color: white; }
.announcement-meta { display: flex; justify-content: space-between; margin-top: 8px; font-size: 12px; color: #777; }
.announcement-date, .announcement-duration { display: flex; align-items: center; }
.announcement-date i, .announcement-duration i { margin-right: 5px; }
.announcement-list li small { display: block; margin-top: 4px; color: #888; font-size: 11px; }
#announcementDateFilter { width: 100%; padding: 8px; margin: 10px 0; border: 1px solid #ddd; border-radius: 4px; }
.filter-container { display: flex; align-items: center; gap: 10px; }
.clear-filter-btn { background: #00b0ff; color: white; border: none; border-radius: 4px; padding: 8px 12px; cursor: pointer; transition: background-color 0.3s; }
.clear-filter-btn:hover { background: #0091ea; }
.no-announcements { text-align: center; padding: 20px; color: #666; font-style: italic; }

    </style>
</head>
<body>
<div class="main-container">
    <div class="container">
        <div class="sidebar-wrapper">
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
                            <li class="active"data-page="routeschedules">
                                <div class="nav-item-content">
                                    <i class="fas fa-route"></i>
                                    <span class="nav-text">Route and Schedules</span>
                                </div>
                            </li>
                            <li  data-page="Usersection">
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
                            <li><a href="#"><div class="nav-item-content"><i class="fa-solid fa-envelope"></i></i><span class="settings-text">Mail</div></a></li>
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
        </div>

        <!-- Content Area (tables) -->
        <div class="content-area">
        <div class="header">
            <h1>Ferry Route and Schedules</h1>
            <div id="clock" style="margin-bottom: 20px; font-size: 16px; color: #00b0ff;"></div>
        </div>

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
                        <?php for ($i = 1; $i <= 7; $i++): ?>
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
                        <?php for ($i = 1; $i <= 7; $i++): ?>
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
            <!-- Improved Announcement Display Section -->
            <div class="cardlog">
                <div class="announcement-header">
                    <h3>Announcement Log</h3>
                    <button class="add-announcement-btn" id="openAnnouncementForm">
                        <i class="fas fa-plus"></i> Add New
                    </button>
                </div>
                
                <!-- Search Bar for Date -->
                <div class="filter-container" style="display: flex; margin-bottom: 15px; align-items: center;">
                    <input type="date" id="announcementDateFilter" placeholder="Filter by Date" style="flex-grow: 1;">
                </div>
                
                <?php if (empty($announcements)): ?>
                    <p class="no-announcements">No announcements available at this time.</p>
                <?php else: ?>
                    <ul class="announcement-list">
                        <?php foreach ($announcements as $announcement): ?>
                            <?php 
                                // Calculate end date for display
                                $display_from = new DateTime($announcement['display_from']);
                                $display_to = clone $display_from;
                                $display_to->modify('+' . $announcement['display_duration'] . ' days');
                            ?>
                            <li>
                                <strong><?= htmlspecialchars($announcement['title']) ?></strong>
                                <p><?= htmlspecialchars($announcement['message']) ?></p>
                                <div class="announcement-meta">
                                    <span class="announcement-date">
                                        <i class="fas fa-calendar-alt"></i> 
                                        <?= date('M d, Y', strtotime($announcement['created_at'])) ?>
                                    </span>
                                    <span class="announcement-duration">
                                        <i class="fas fa-clock"></i> 
                                        <?= $announcement['display_duration'] ?> days
                                    </span>
                                </div>
                                <small>
                                    Displays: <?= $display_from->format('M d, Y') ?> - 
                                    <?= $display_to->format('M d, Y') ?>
                                </small>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Modal Popup for Add Announcement -->
<div id="announcementModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Add New Announcement</h3>
            <span class="close-modal">&times;</span>
        </div>
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
                <label for="display_from">Display From</label>
                <input type="date" id="display_from" name="display_from" required>
            </div>
            <div>
                <label for="display_duration">Display Duration (in days)</label>
                <input type="number" id="display_duration" name="display_duration" min="1" required>
            </div>
            <div>
                <button type="submit">Add Announcement</button>
            </div>
        </form>
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

    $(document).ready(function() {
        // Sidebar toggle
        $("#sidebar-toggle").click(function() {
            $("#sidebar").toggleClass("sidebar-collapsed");
            $("#main-content").toggleClass("content-expanded");

            if ($("#sidebar").hasClass("sidebar-collapsed")) {
                $("#toggle-icon").removeClass("fa-chevron-left").addClass("fa-chevron-right");
            } else {
                $("#toggle-icon").removeClass("fa-chevron-right").addClass("fa-chevron-left");
            }
        });

        // Navigation
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

        // Modal controls
        const modal = document.getElementById('announcementModal');
        const openBtn = document.getElementById('openAnnouncementForm');
        const closeBtn = document.querySelector('.close-modal');

        // Improved modal opening  
        openBtn.addEventListener('click', function() {
            modal.style.display = 'block';
            document.body.style.overflow = 'hidden'; // Prevent background scrolling
            // Auto-set date to today
            document.getElementById('display_from').valueAsDate = new Date();
        });

        // Improved modal closing
        function closeModal() {
            modal.style.display = 'none';
            document.body.style.overflow = ''; // Re-enable scrolling
        }

        closeBtn.addEventListener('click', closeModal);

        // Close when clicking outside the modal
        window.addEventListener('click', function(event) {
            if (event.target === modal) {
                closeModal();
            }
        });

        // Close on escape key
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape' && modal.style.display === 'block') {
                closeModal();
            }
        });
    });

    // Improved date filter for announcements
    document.getElementById('announcementDateFilter').addEventListener('change', function() {
        const filterDate = this.value ? new Date(this.value) : null;
        const items = document.querySelectorAll('.announcement-list li');
        let foundMatch = false;
        
        // Remove any existing "no announcements" message
        const existingMsg = document.querySelector('.cardlog > .no-announcements');
        if (existingMsg) {
            existingMsg.remove();
        }
        
        if (!filterDate) {
            // If no filter date selected, show all announcements
            items.forEach(item => {
                item.style.display = '';
            });
            return;
        }
        
        // Set time to midnight for proper date comparison
        filterDate.setHours(0, 0, 0, 0);
        
        items.forEach(item => {
            // Get the display range text from the announcement
            const displayRangeText = item.querySelector('small').textContent.trim();
            
            // Extract display date range from the text
            const startDateText = displayRangeText.split('Displays: ')[1].split(' - ')[0];
            const endDateText = displayRangeText.split(' - ')[1];
            
            // Parse dates properly
            const startDate = new Date(startDateText);
            const endDate = new Date(endDateText);
            
            // Set times to midnight for proper comparison
            startDate.setHours(0, 0, 0, 0);
            endDate.setHours(0, 0, 0, 0);
            
            // Check if filter date falls within announcement display period (inclusive)
            if (filterDate >= startDate && filterDate <= endDate) {
                item.style.display = ''; // Show the item
                foundMatch = true;
            } else {
                item.style.display = 'none'; // Hide the item
            }
        });
        
        // Show message if no matching announcements for the selected date
        if (!foundMatch && items.length > 0) {
            const msg = document.createElement('p');
            msg.className = 'no-announcements';
            msg.textContent = 'No announcements found for the selected date.';
            
            const cardlog = document.querySelector('.cardlog');
            const announcementList = document.querySelector('.announcement-list');
            if (announcementList) {
                cardlog.insertBefore(msg, announcementList);
            } else {
                cardlog.appendChild(msg);
            }
        }
    });
    
    // Add a clear filter button after the date filter
    const dateFilterInput = document.getElementById('announcementDateFilter');
    const clearBtn = document.createElement('button');
    clearBtn.textContent = 'Show All';
    clearBtn.className = 'clear-filter-btn';
    clearBtn.style.marginLeft = '10px';
    clearBtn.style.padding = '8px 12px';
    clearBtn.style.background = '#00b0ff';
    clearBtn.style.color = 'white';
    clearBtn.style.border = 'none';
    clearBtn.style.borderRadius = '4px';
    clearBtn.style.cursor = 'pointer';
    
    dateFilterInput.parentNode.insertBefore(clearBtn, dateFilterInput.nextSibling);
    
    clearBtn.addEventListener('click', function() {
        // Clear the date filter
        dateFilterInput.value = '';
        
        // Trigger the change event to show all announcements
        const event = new Event('change');
        dateFilterInput.dispatchEvent(event);
    });

    // Clock update
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