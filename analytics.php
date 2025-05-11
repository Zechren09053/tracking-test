<?php
session_start();

require 'db_connect.php'; // Include the DB connection file

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

$passes_sql = "SELECT COUNT(*) AS active_passes FROM passenger_id_pass WHERE is_active = 1 AND expires_at > NOW()";
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
    <title>Ferry Admin - Analytics</title>
    <link rel="stylesheet" href="Db.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        .main {
            height: 100%;
            max-height: 100vh;
            overflow: hidden;
            padding-bottom: 0;
            box-sizing: border-box;
        }
        .charts {
            width: 35%;
            float: left;
            display: flex;
            flex-direction: column;
            gap: 20px;
            padding-right: 10px;
            box-sizing: border-box;
        }

        .auditlog {
            width: 65%;
            float: right;
            background: #444;
            border-radius: 12px;
            padding: 20px;
            color: #fff;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
            box-sizing: border-box;
            overflow-y: auto;
            max-height: calc(100vh - 40px);
        }

        .chart-card {
            background: #444;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            padding: 20px;
            width: 100%;
            max-width: 500px;
            color: #fff;
        }

        .chart-card h3 {
            margin-bottom: 15px;
            font-size: 18px;
        }

        .chart-card canvas {
            width: 100% !important;
            height: 200px !important;
        }

        .info-box {
            background-color: #2a2f3a;
            padding: 20px;
            border-radius: 10px;
            color: #f1f1f1;
            margin-bottom: 10px;
        }

        .audit-box {
            background-color: #2a2f3a;
            padding: 20px;
            border-radius: 10px;
            color: #f1f1f1;
            margin-bottom: 10px;
            margin-top: 20px;
        }
        .info-box {
        background-color: #2a2f3a;
        padding: 20px;
        border-radius: 10px;
        color: #f1f1f1;
        margin-bottom: 10px;
        text-align: center;
        display: flex;
        flex-direction: row;  /* Arrange elements in a row */
        justify-content: space-between;  /* Distribute space evenly between elements */
        align-items: center;  /* Vertically center the elements */
        gap: 15px;  /* Add space between elements */
    }

    .info-box h3 {
        margin-bottom: 0;
        font-size: 22px;
    }

    .report-selector {
        display: flex;
        flex-direction: column;  /* Stack label and dropdown vertically */
        gap: 5px;
    }

    .report-selector label {
        font-size: 16px;
    }

    .report-selector select {
        padding: 8px;
        font-size: 16px;
        background-color: #444;
        color: #fff;
        border: 1px solid #555;
        border-radius: 5px;
        width: 200px;  /* Adjust width for a neat look */
    }

    .export-buttons {
        display: flex;
        gap: 10px;  /* Space between buttons */
    }

    .export-btn {
        background-color: #00b0ff;
        color: #fff;
        padding: 10px;
        font-size: 16px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        transition: background-color 0.3s ease;
    }

    .export-btn:hover {
        background-color: #008ac1;
    }
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
                            <li class="active"data-page="analytics">
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
            </div>

            <!-- Main Analytics Page -->
            <div class="main">
                <div class="header">
                    <h1>Analytics</h1>
                    <div id="clock" style="margin-bottom: 20px; font-size: 16px; color: #00b0ff;"></div>
                </div>

                <div class="stats">
                    <div class="stat-box">
                        <h2>Total Passengers</h2>
                        <p><?= $total_passengers ?></p>
                        <div class="stat-change">↑ based on records</div>
                    </div>
                    <div class="stat-box">
                        <h2>Active Passes</h2>
                        <p><?= $active_passes ?></p>
                        <div class="stat-change">Currently Valid</div>
                    </div>
                    <div class="stat-box">
                        <h2>Active Ferries</h2>
                        <p><?= $active_ferries ?></p>
                        <div class="stat-change">In Operation Now</div>
                    </div>
                    <div class="stat-box">
                        <h2>Average Occupancy</h2>
                        <p><?= $occupancy_percentage ?>%</p>
                        <div class="stat-change">Capacity Utilization</div>
                    </div>
                </div>

                <div class="charts">
                    <div class="chart-card">
                        <h3>Passenger Growth Over Time</h3>
                        <canvas id="passengerChart"></canvas>
                    </div>

                    <div class="chart-card">
                        <h3>Tickets Sold Over Time</h3>
                        <canvas id="ticketChart"></canvas>
                    </div>
                </div>

                <div class="auditlog">
                    <!-- Existing info box with Printout button -->
                    <div class="info-box">
    <h3>Export Report</h3>

    <!-- Dropdown for selecting report type -->
    <div class="report-selector">
        <label for="reportType">Report Type:</label>
        <select id="reportType" name="reportType">
            <option value="boatLogs">Boat Logs</option>
            <option value="boatMaintenance">Boat Maintenance</option>
            <option value="ticketLogs">Ticket Logs</option>
            <option value="repairLogs">Repair Logs</option>
        </select>
    </div>

    <!-- Export buttons -->
    <div class="export-buttons">
        <button class="export-btn" onclick="exportPDF()">Export as PDF</button>
        <button class="export-btn" onclick="exportExcel()">Export as Excel</button>
        <button class="export-btn" onclick="exportCSV()">Export as CSV</button>
        <button class="export-btn" onclick="exportPrint()">Print</button>
    </div>
</div>
                    
                    <!-- New Audit Box -->
                    <div class="audit-box">
                        <h3>Audit Log</h3>
                        <p>Audit table is to be displayed here</p>
                        <!-- Audit Table (for illustration, you can populate this with dynamic data) -->
                        <table border="1" style="width: 100%; margin-top: 20px;">
                            <tr>
                                <th>ID</th>
                                <th>Action</th>
                                <th>Timestamp</th>
                            </tr>
                            <tr>
                                <td>1</td>
                                <td>Login</td>
                                <td>2025-05-10 12:34:56</td>
                            </tr>
                            <tr>
                                <td>2</td>
                                <td>Updated Ferry Status</td>
                                <td>2025-05-10 12:40:22</td>
                            </tr>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- JS Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.1/xlsx.full.min.js"></script>


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

        fetch('getChartData.php')
    .then(res => res.json())
    .then(data => {
        const months = data.passengers.map(p => p.month);
        const passengerCounts = data.passengers.map(p => p.passengers);
        const ticketCounts = data.tickets.map(t => t.tickets);

        new Chart(document.getElementById('passengerChart'), {
            type: 'line',
            data: {
                labels: months,
                datasets: [{
                    label: 'Passengers',
                    data: passengerCounts,
                    borderColor: '#36a2eb',
                    backgroundColor: 'rgba(54, 162, 235, 0.2)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.3
                }]
            }
        });

        new Chart(document.getElementById('ticketChart'), {
            type: 'bar',
            data: {
                labels: months,
                datasets: [{
                    label: 'Tickets Sold',
                    data: ticketCounts,
                    backgroundColor: '#4bc0c0'
                }]
            }
        });
    })
    .catch(err => console.error('Chart fetch error:', err));

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
    const timeString = now.toLocaleTimeString();
    const dateString = now.toLocaleDateString();
    document.getElementById("clock").textContent = `${dateString} | ${timeString}`;
  }
  function exportPDF() {
    const reportType = document.getElementById('reportType').value;
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF();
    
    doc.text(`Exporting ${reportType} as PDF`, 20, 20);
    // Add more content to the PDF as needed, such as table data, etc.
    
    doc.save(`${reportType}_Report.pdf`);
}


    function exportExcel() {
    const reportType = document.getElementById('reportType').value;

    // Example data structure (you would replace this with actual data)
    const data = [
        ["ID", "Name", "Date", "Status"],
        [1, "Boat 1", "2025-05-10", "Active"],
        [2, "Boat 2", "2025-05-11", "Inactive"]
    ];

    const ws = XLSX.utils.aoa_to_sheet(data);
    const wb = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(wb, ws, `${reportType} Report`);
    
    XLSX.writeFile(wb, `${reportType}_Report.xlsx`);
}

    function exportCSV() {
    const reportType = document.getElementById('reportType').value;

    // Example data structure (you would replace this with actual data)
    const data = [
        ["ID", "Name", "Date", "Status"],
        [1, "Boat 1", "2025-05-10", "Active"],
        [2, "Boat 2", "2025-05-11", "Inactive"]
    ];

    let csvContent = "data:text/csv;charset=utf-8,";

    data.forEach((rowArray) => {
        const row = rowArray.join(",");
        csvContent += row + "\r\n"; // Add each row to the CSV string
    });

    const encodedUri = encodeURI(csvContent);
    const link = document.createElement("a");
    link.setAttribute("href", encodedUri);
    link.setAttribute("download", `${reportType}_Report.csv`);
    link.click();
}

    function exportPrint() {
        const reportType = document.getElementById('reportType').value;
        alert(`Printing ${reportType}`);
        window.print();
    }
  setInterval(updateClock, 1000);
  updateClock(); // run once on load
    </script>
</body>
</html>
