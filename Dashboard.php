<?php
session_start();
require_once 'session_handler.php';
requireLogin();

require 'db_connect.php';
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

// Live stats
$passenger_sql = "SELECT SUM(current_capacity) AS total_passengers FROM ferries";
$passenger_result = $conn->query($passenger_sql);
$total_passengers = $passenger_result->fetch_assoc()['total_passengers'] ?? 0;

// Change here: Using users table instead of passenger_id_pass
$passes_sql = "SELECT COUNT(*) AS active_passes FROM users WHERE is_active = 1 AND expires_at > NOW()";
$passes_result = $conn->query($passes_sql);
$active_passes = $passes_result->fetch_assoc()['active_passes'] ?? 0;

$ferry_sql = "SELECT COUNT(*) AS active_ferries FROM ferries WHERE status = 'active'";
$ferry_result = $conn->query($ferry_sql);
$active_ferries = $ferry_result->fetch_assoc()['active_ferries'] ?? 0;

$occupancy_sql = "SELECT AVG(current_capacity / max_capacity) AS avg_occupancy FROM ferries WHERE max_capacity > 0";
$occupancy_result = $conn->query($occupancy_sql);
$avg_occupancy = $occupancy_result->fetch_assoc()['avg_occupancy'] ?? 0;
$occupancy_percentage = round($avg_occupancy * 100, 1);

$conn->close();
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Ferry Admin Dashboard</title>
    <link rel="stylesheet" href="Db.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

</head>
<body>
    <!-- Main container with rounded edges -->
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
                            <li class="active" data-page="dashboard">
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
                            <li><a href="settings.php"><div class="nav-item-content"><i class="fas fa-cog"></i><span class="settings-text">Settings</span></div></a></li>
                            <li><a href="mail.php"><div class="nav-item-content"><i class="fa-solid fa-envelope"></i></i><span class="settings-text">Mail</div></a></li>
                            <li><a href="logout.php"><div class="nav-item-content"><i class="fas fa-sign-out-alt"></i><span class="settings-text">Logout</span></div></a></li>
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
            <!-- Main Dashboard -->
            <div class="main">
                <div class="header">
                    <h1>Dashboard</h1>
                    <div id="clock" style="margin-bottom: 20px; font-size: 16px; color: #00b0ff;"></div>
                </div>
<div class="stats">
                    <div class="stat-box">
                        <h2><i class="fas fa-users"></i> Total Passengers</h2>
                        <p><?= $total_passengers ?></p>
                        <div class="stat-change">↑ based on records</div>
                    </div>
                    <div class="stat-box">
                        <h2><i class="fas fa-id-card"></i> Active Passes</h2>
                        <p><?= $active_passes ?></p>
                        <div class="stat-change">Currently Valid</div>
                    </div>
                    <div class="stat-box">
                        <h2><i class="fas fa-ship"></i> Active Ferries</h2>
                        <p><?= $active_ferries ?></p>
                        <div class="stat-change">In Operation Now</div>
                    </div>
                    <div class="stat-box">
                        <h2><i class="fas fa-percentage"></i> Average Occupancy</h2>
                        <p><?= $occupancy_percentage ?>%</p>
                        <div class="stat-change">Capacity Utilization</div>
                    </div>
                </div>

<!-- Tracking section with ferry list and map side by side -->
<div class="tracking">
    <!-- Left panel: Ferry list only -->
    <div class="left-panel">
        <div class="boat-list">
            <div class="boat-list-header">Ferry List</div>
            <div id="ferry-list" class="boat-list-body"></div>
        </div>
    </div>

    <!-- Right panel: Map -->
    <div class="map">
        <div class="map-box">
            <div id="map"></div>
        </div>
    </div>
</div>

<!-- Bottom panel: Calendar + Event details -->
<div class="calendar-container">
    <div class="calendar-section">
        <div class="calendar-header">
            <h3>Calendar</h3>
            <div class="calendar-nav">
                <button id="prev-month"><i class="fas fa-chevron-left"></i></button>
                <span id="current-month-display">May 2025</span>
                <button id="next-month"><i class="fas fa-chevron-right"></i></button>
            </div>
        </div>
        <div class="calendar" id="announcements-calendar">
            <div class="calendar-weekdays">
                <div>S</div><div>M</div><div>T</div><div>W</div><div>T</div><div>F</div><div>S</div>
            </div>
            <div class="calendar-days" id="calendar-days">
                <!-- Days will be filled in dynamically -->
            </div>
        </div>
    </div>

    <div id="event-details" class="event-details">
        <h4>Announcement Details</h4>
        <div id="selected-event-details">
            <p>Select a day with announcements to view details</p>
        </div>
    </div>
</div>


            </div>
        </div>
    </div>

    <!-- Add your script tags for your custom JavaScript -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
    


    <script>
        
    var map = L.map('map').setView([14.5896, 121.0360], 13);
    var markers = {};

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(map);

    const pasigRiverRoute = [
  [14.5528,121.0875],[14.5528,121.0847],[14.5533,121.0806],[14.5533,121.0764],[14.5542,121.0750],
  [14.5550,121.0731],[14.5559,121.0711],[14.5565,121.0687],[14.5567,121.0680],[14.5581,121.0669],
  [14.5597,121.0664],[14.5610,121.0650],[14.5614,121.0631],[14.5622,121.0614],[14.5644,121.0592],
  [14.5653,121.0558],[14.5661,121.0536],[14.5678,121.0511],[14.5683,121.0492],[14.5683,121.0470],
  [14.5686,121.0447],[14.5686,121.0422],[14.5675,121.0378],[14.5672,121.0347],[14.5683,121.0325],
  [14.5744,121.0256],[14.5792,121.0175],[14.5808,121.0169],[14.5831,121.0190],[14.5856,121.0203],
  [14.5870,121.0190],[14.5856,121.0142],[14.5828,121.0114],[14.5817,121.0092],[14.5828,121.0075],
  [14.5844,121.0067],[14.5864,121.0070],[14.5878,121.0086],[14.5881,121.0114],[14.5911,121.0156],
  [14.5925,121.0158],[14.5939,121.0122],[14.5964,121.0075],[14.5972,121.0044],[14.5956,121.0017],
  [14.5967,120.9983],[14.5939,120.9950],[14.5911,120.9928],[14.5897,120.9900],[14.5897,120.9872],
  [14.5911,120.9858],[14.5928,120.9836],[14.5961,120.9814],[14.5967,120.9794],[14.5953,120.9761],
  [14.5956,120.9703],[14.5958,120.9636]
];

const pasigRiverRoute2 = [
  [14.5581,121.0669],[14.5581,121.0681],[14.5586,121.0703],[14.5597,121.0721],[14.5620,121.0732],
  [14.5667,121.0736],[14.5700,121.0739],[14.5714,121.0743],[14.5754,121.0775],[14.5778,121.0803],
  [14.5803,121.0819],[14.5833,121.0828],[14.5833,121.0828],[14.5872,121.0833],[14.5928,121.0822],
  [14.5978,121.0825],[14.6025,121.0825]
];
const ferryStations = [
  { name: "Pinagbuhatan", coords: [14.5972, 121.0825] },
  { name: "Kalawaan", coords: [14.5914, 121.0825] },
  { name: "San Joaquin", coords: [14.5581, 121.0669] },
  { name: "Maybunga", coords: [14.5760, 121.0785] },
  { name: "Guadalupe", coords: [14.5672, 121.0347] },
  { name: "Hulo", coords: [14.5744, 121.0256] },
  { name: "Valenzuela", coords: [14.5835, 121.0190] },
  { name: "Lambingan", coords: [14.5869, 121.0190] },
  { name: "Santa Ana", coords: [14.5900, 121.0142] },
  { name: "PUP", coords: [14.5968, 121.0035] },
  { name: "Lawton", coords: [14.5935, 120.9838] },
  { name: "Escolta", coords: [14.5965, 120.9790] },
  { name: "Plaza Mexico", coords: [14.5957, 120.9745] }
];

ferryStations.forEach(station => {
  const marker = L.circleMarker(station.coords, {
    radius: 6,
    fillColor: "#0066ff",
    color: "#003366",
    weight: 1,
    opacity: 1,
    fillOpacity: 0.9
  }).addTo(map);
  marker.bindTooltip(station.name, {
    permanent: true,
    direction: 'top',
    className: 'ferry-label'
  });
});

    const riverRoute = L.polyline(pasigRiverRoute, {
        color: 'blue',
        weight: 4,
        opacity: 0.7,
        smoothFactor: 1
    }).addTo(map);

    const riverRoute2 = L.polyline(pasigRiverRoute2, {
    color: 'blue',
    weight: 4,
    opacity: 0.7,
    smoothFactor: 1
}).addTo(map);

    // Only fit once when map initializes
    map.fitBounds([riverRoute.getBounds(), riverRoute2.getBounds()]);

    function fetchFerryData() {
    const ferryList = $('#ferry-list');
    const scrollPos = ferryList.scrollTop(); // Save scroll position

    $.ajax({
        url: 'getFerries.php',
        method: 'GET',
        dataType: 'json',
        success: function(data) {
            ferryList.empty();

            if (data.length === 0) {
                ferryList.append('<p>No ferries are currently available.</p>');
            }

            data.forEach(function(ferry) {
                const statusClass = ferry.status.toLowerCase() === 'active' ? 'status-active' : 'status-inactive';
                const ferryElement = `
                    <div class="boat-card" data-lat="${ferry.latitude}" data-lng="${ferry.longitude}">
                        <div class="status-indicator ${statusClass}"></div>
                        <div class="top"><strong>${ferry.name}</strong></div>
                        
                        <div class="middle-row">
                            <div class="left-info">
                                Active Time: ${ferry.active_time} mins<br>
                                Status: ${ferry.status}<br>
                                Operator: ${ferry.operator}
                            </div>
                            <div class="capacity-info">
                                Capacity: ${ferry.current_capacity} / ${ferry.max_capacity}
                            </div>
                        </div>

                        <div class="coordinates">
                            Longitude: ${ferry.longitude} | Latitude: ${ferry.latitude}
                        </div>
                    </div>
                `;

                ferryList.append(ferryElement);

                if (ferry.latitude && ferry.longitude) {
                    if (!markers[ferry.name]) {
                        addFerryMarker(ferry.latitude, ferry.longitude, ferry.name);
                    } else {
                        markers[ferry.name].setLatLng([ferry.latitude, ferry.longitude]);
                    }
                }
            });

            ferryList.scrollTop(scrollPos); // Restore scroll position
        },
        error: function() {
            ferryList.html('<p>Sorry, there was an error loading the ferry data.</p>');
        }
    });
}


    setInterval(fetchFerryData, 5000);
    fetchFerryData();

    function addFerryMarker(latitude, longitude, ferryName) {
    const ferryIcon = L.icon({
        iconUrl: 'ship.png', // Replace with your own icon path
        iconSize: [32, 32], // Resize to fit your style
        iconAnchor: [16, 16], // Anchor in the center
        popupAnchor: [0, -16]
    });

    const marker = L.marker([latitude, longitude], { icon: ferryIcon }).addTo(map)
        .bindTooltip(ferryName, {
            permanent: true,
            direction: 'top',
            className: 'ferry-label'
        });

    markers[ferryName] = marker;
}


    $(document).on('click', '.boat-card', function() {
        const lat = $(this).data('lat');
        const lng = $(this).data('lng');
        const name = $(this).find('.top strong').text();

        map.setView([lat, lng], 15);

        if (!markers[name]) {
            addFerryMarker(lat, lng, name);
        }
    });

    document.getElementById('map').style.borderRadius = '24px';
    document.getElementById('map').style.overflow = 'hidden';



    function fetchStatsData() {
    $.ajax({
        url: 'getStats.php',
        method: 'GET',
        dataType: 'json',
        success: function(data) {
            const statBoxes = document.querySelectorAll('.stat-box');

            if (statBoxes.length >= 4) {
                statBoxes[0].querySelector('p').textContent = data.total_passengers;
                statBoxes[1].querySelector('p').textContent = data.active_passes;
                statBoxes[2].querySelector('p').textContent = data.active_ferries;
                statBoxes[3].querySelector('p').textContent = data.occupancy_percentage + '%';
            }
        },
        error: function() {
            console.error('Failed to fetch stats');
        }
    });
}

setInterval(fetchStatsData, 1000); // every 5 seconds
fetchStatsData(); // also run immediately
function updateClock() {
            const now = new Date();
            const options = { 
                weekday: 'long',
                year: 'numeric', 
                month: 'long', 
                day: 'numeric',
                hour: '2-digit', 
                minute: '2-digit', 
                second: '2-digit' 
            };
            const timeString = now.toLocaleTimeString();
            const dateString = now.toLocaleDateString('en-US', options);
            document.getElementById("clock").innerHTML = `<i class="far fa-clock"></i> ${dateString}`;
        }

  setInterval(updateClock, 1000);
  updateClock(); // run once on load

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
        // Add this to your existing script section

// Calendar functionality
document.addEventListener('DOMContentLoaded', function() {
    let currentDate = new Date();
    let currentMonth = currentDate.getMonth();
    let currentYear = currentDate.getFullYear();
    let selectedDate = null;
    let announcements = [];

    // Initialize calendar
    updateCalendar(currentMonth, currentYear);

    // Navigation buttons
    document.getElementById('prev-month').addEventListener('click', function() {
        currentMonth--;
        if (currentMonth < 0) {
            currentMonth = 11;
            currentYear--;
        }
        updateCalendar(currentMonth, currentYear);
    });

    document.getElementById('next-month').addEventListener('click', function() {
        currentMonth++;
        if (currentMonth > 11) {
            currentMonth = 0;
            currentYear++;
        }
        updateCalendar(currentMonth, currentYear);
    });

    // Update the calendar view
    function updateCalendar(month, year) {
        const monthNames = [
            "January", "February", "March", "April", "May", "June", 
            "July", "August", "September", "October", "November", "December"
        ];
        
        document.getElementById('current-month-display').textContent = `${monthNames[month]} ${year}`;
        
        const firstDay = new Date(year, month, 1);
        const lastDay = new Date(year, month + 1, 0);
        const daysInMonth = lastDay.getDate();
        const startDayOfWeek = firstDay.getDay(); // 0 = Sunday
        
        // Clear calendar days
        const calendarDays = document.getElementById('calendar-days');
        calendarDays.innerHTML = '';
        
        // Fetch announcements for this month
        fetchAnnouncements(month + 1, year, function() {
            // Add previous month's days
            let dayCounter = 0;
            for (let i = 0; i < startDayOfWeek; i++) {
                const prevMonthLastDay = new Date(year, month, 0).getDate();
                const dayNum = prevMonthLastDay - startDayOfWeek + i + 1;
                
                const dayElement = createDayElement(dayNum, false);
                calendarDays.appendChild(dayElement);
                dayCounter++;
            }
            
            // Add current month's days
            for (let i = 1; i <= daysInMonth; i++) {
                const currentDateCheck = new Date();
                const isToday = i === currentDateCheck.getDate() && 
                                month === currentDateCheck.getMonth() && 
                                year === currentDateCheck.getFullYear();
                
                const dayElement = createDayElement(i, true, isToday);
                
                // Check if this day has announcements
                const dateStr = formatDate(new Date(year, month, i));
                const hasEvents = hasAnnouncementsOnDate(dateStr);
                
                if (hasEvents) {
                    dayElement.classList.add('has-event');
                    dayElement.setAttribute('data-date', dateStr);
                    dayElement.addEventListener('click', function() {
                        selectDate(dateStr, this);
                    });
                }
                
                calendarDays.appendChild(dayElement);
                dayCounter++;
            }
            
            // Add next month's days
            const remainingCells = 42 - dayCounter; // 6 rows × 7 days = 42 cells
            for (let i = 1; i <= remainingCells; i++) {
                const dayElement = createDayElement(i, false);
                calendarDays.appendChild(dayElement);
            }
        });
    }

    // Create a day element for the calendar
    function createDayElement(dayNum, isCurrentMonth, isToday = false) {
        const dayElement = document.createElement('div');
        dayElement.className = 'calendar-day';
        dayElement.textContent = dayNum;
        
        if (isCurrentMonth) {
            dayElement.classList.add('current-month');
        } else {
            dayElement.classList.add('other-month');
        }
        
        if (isToday) {
            dayElement.classList.add('today');
        }
        
        return dayElement;
    }

    // Format date as YYYY-MM-DD
    function formatDate(date) {
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    }

    // Check if there are announcements on a specific date
    function hasAnnouncementsOnDate(dateStr) {
        const date = new Date(dateStr);
        
        return announcements.some(announcement => {
            const startDate = new Date(announcement.display_from);
            const endDate = new Date(announcement.end_date);
            
            return date >= startDate && date <= endDate;
        });
    }

    // Select a date and show its announcements
    function selectDate(dateStr, element) {
        // Remove selection from previously selected element
        if (selectedDate) {
            const elements = document.querySelectorAll('.calendar-day.selected');
            elements.forEach(el => el.classList.remove('selected'));
        }
        
        // Add selection to new element
        element.classList.add('selected');
        selectedDate = dateStr;
        
        // Show announcements for this date
        showAnnouncementsForDate(dateStr);
    }

    // Show announcements for a specific date
    function showAnnouncementsForDate(dateStr) {
        const date = new Date(dateStr);
        const detailsContainer = document.getElementById('selected-event-details');
        detailsContainer.innerHTML = '';
        
        const dateAnnouncements = announcements.filter(announcement => {
            const startDate = new Date(announcement.display_from);
            const endDate = new Date(announcement.end_date);
            
            return date >= startDate && date <= endDate;
        });
        
        if (dateAnnouncements.length === 0) {
            detailsContainer.innerHTML = '<p>No announcements for this day</p>';
            return;
        }
        
        dateAnnouncements.forEach(announcement => {
            const eventItem = document.createElement('div');
            eventItem.className = 'event-item';
            
            const title = document.createElement('h5');
            title.textContent = announcement.title;
            
            const message = document.createElement('p');
            message.textContent = announcement.message.length > 100 
                ? announcement.message.substring(0, 100) + '...' 
                : announcement.message;
            
            const dateRange = document.createElement('p');
            dateRange.className = 'date-range';
            dateRange.textContent = `${formatReadableDate(announcement.display_from)} - ${formatReadableDate(announcement.end_date)}`;
            
            eventItem.appendChild(title);
            eventItem.appendChild(message);
            eventItem.appendChild(dateRange);
            
            detailsContainer.appendChild(eventItem);
        });
    }

    // Format date as readable string (May 11, 2025)
    function formatReadableDate(dateStr) {
        const date = new Date(dateStr);
        return date.toLocaleDateString('en-US', {
            month: 'short', 
            day: 'numeric', 
            year: 'numeric'
        });
    }

    // Fetch announcements from the server
    function fetchAnnouncements(month, year, callback) {
        $.ajax({
            url: 'getAnnouncements.php',
            method: 'GET',
            data: {
                month: month,
                year: year
            },
            dataType: 'json',
            success: function(data) {
                announcements = data;
                if (callback) callback();
            },
            error: function() {
                console.error('Failed to fetch announcements');
                announcements = [];
                if (callback) callback();
            }
        });
    }
});
</script>


</body>
</html>
