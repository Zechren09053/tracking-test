<?php
session_start();
if (!isset($_SESSION['staff_id'], $_SESSION['2fa_verified']) || $_SESSION['2fa_verified'] !== true) {
    header("Location: login.php");
    exit();
}

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
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Ferry Admin - Analytics</title>
    <link rel="stylesheet" href="Db.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .main { height: 100%; max-height: 100vh; overflow: hidden; padding-bottom: 0; box-sizing: border-box; }
        .charts { width: 35%; float: left; display: flex; flex-direction: column; gap: 20px; padding-right: 10px; box-sizing: border-box; }
        .auditlog { width: 65%; float: right; background: #444; border-radius: 12px; padding: 20px; color: #fff; box-shadow: 0 2px 8px rgba(0,0,0,0.2); box-sizing: border-box; overflow-y: auto; max-height: calc(100vh - 40px); }
        .chart-card { background: #444; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.2); padding: 20px; width: 100%; max-width: 500px; color: #fff; position: relative; transition: transform 0.3s ease, box-shadow 0.3s ease; }
        .chart-card:hover { transform: translateY(-5px); box-shadow: 0 4px 12px rgba(0,0,0,0.3); }
        .chart-card h3 { margin-bottom: 15px; font-size: 18px; display: flex; align-items: center; gap: 8px; }
        .chart-card h3 i { color: #00b0ff; }
        .chart-card canvas { width: 100% !important; height: 180px !important; }
        .chart-legend { display: flex; justify-content: center; gap: 15px; margin-top: 10px; font-size: 14px; }
        .legend-item { display: flex; align-items: center; gap: 5px; }
        .legend-color { width: 12px; height: 12px; border-radius: 50%; }
        .info-box, .audit-box { background-color: #2a2f3a; padding: 20px; border-radius: 10px; color: #f1f1f1; margin-bottom: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.2); transition: all 0.3s ease; }
        .audit-box { margin-top: 20px; }
        .info-box:hover, .audit-box:hover { box-shadow: 0 4px 12px rgba(0,0,0,0.3); }
        .info-box { text-align: center; display: flex; flex-direction: row; justify-content: space-between; align-items: center; gap: 15px; }
        .info-box h3 { margin-bottom: 0; font-size: 22px; display: flex; align-items: center; gap: 8px; }
        .info-box h3 i { color: #00b0ff; }
        .report-selector { display: flex; flex-direction: column; gap: 5px; }
        .report-selector label { font-size: 16px; }
        .report-selector select { padding: 8px; font-size: 16px; background-color: #444; color: #fff; border: 1px solid #555; border-radius: 5px; width: 200px; cursor: pointer; transition: all 0.2s ease; }
        .report-selector select:hover { border-color: #00b0ff; }
        .export-buttons { display: flex; gap: 10px; }
        .export-btn { background-color: #00b0ff; color: #fff; padding: 10px; font-size: 16px; border: none; border-radius: 5px; cursor: pointer; transition: background-color 0.3s ease; display: flex; align-items: center; gap: 5px; }
        .export-btn:hover { background-color: #008ac1; }
        .audit-table { width: 100%; border-collapse: collapse; margin-top: 20px; border-radius: 5px; overflow: hidden; }
        .audit-table th, .audit-table td { padding: 12px; text-align: left; border: 1px solid #555; }
        .audit-table th { background-color: #1e222c; color: #00b0ff; }
        .audit-table tr:nth-child(even) { background-color: #333942; }
        .audit-table tr:hover { background-color: #464f5e; }
        .stat-box { transition: transform 0.3s ease, box-shadow 0.3s ease; }
        .stat-box:hover { transform: translateY(-5px); box-shadow: 0 8px 15px rgba(0,0,0,0.2); }
        .chart-filter { margin-bottom: 10px; display: flex; justify-content: flex-end; gap: 10px; }
        .time-filter { background: #2a2f3a; border: 1px solid #555; color: white; padding: 5px 10px; border-radius: 5px; cursor: pointer; transition: all 0.2s ease; }
        .time-filter.active { background: #00b0ff; border-color: #00b0ff; }
        .time-filter:hover:not(.active) { border-color: #00b0ff; }
        .loader { display: none; border: 3px solid #f3f3f3; border-radius: 50%; border-top: 3px solid #00b0ff; width: 20px; height: 20px; animation: spin 1s linear infinite; margin: 0 auto; position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); }
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
        .chart-card.loading canvas { opacity: 0.3; }
        .chart-card.loading .loader { display: block; }
        .audit-box { max-height: 430px; overflow-y: auto; overflow-x: hidden; padding: 10px; border: 1px solid #ccc; border-radius: 6px; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1); margin-top: 20px; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
.audit-box::-webkit-scrollbar { width: 8px; }
.audit-box::-webkit-scrollbar-track { background: #f1f1f1; }
.audit-box::-webkit-scrollbar-thumb { background: #888; border-radius: 4px; }
.audit-box::-webkit-scrollbar-thumb:hover { background: #555; }

    </style>
</head>
<body>
    <div class="main-container">
        <div class="container">
            <div class="sidebar-wrapper">
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
                                <li class="active" data-page="analytics">
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
                                <li data-page="User section">
                                    <div class="nav-item-content">
                                        <i class="fas fa-users"></i>
                                        <span class="nav-text">User  Section</span>
                                    </div>
                                </li>
                            </ul>
                        </div>
                        <div class="settings-profile-container">
                        <ul class="nav settings-nav">
                            <li><a href="settings.php"><div class="nav-item-content"><i class="fas fa-cog"></i><span class="settings-text">Settings</span></div></a></li>
                            <li><a href="mail.php"><div class="nav-item-content"><i class="fa-solid fa-envelope"></i></i><span class="settings-text">Mail</div></a></li>
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
                <div class="sidebar-toggle" id="sidebar-toggle">
                    <i class="fas fa-chevron-left" id="toggle-icon"></i>
                </div>
            </div>
            <div class="main">
                <div class="header">
                    <h1>Analytics</h1>
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
                <div class="charts">
                    <div class="chart-card" id="passengerChartCard">
                        <h3><i class="fas fa-chart-line"></i> Passenger Growth Over Time</h3>
                        <div class="chart-filter">
                            <button class="time-filter active" data-period="month">Monthly</button>
                            <button class="time-filter" data-period="week">Weekly</button>
                            <button class="time-filter" data-period="day">Daily</button>
                        </div>
                        <canvas id="passengerChart"></canvas>
                        <div class="loader"></div>
                    </div>
                    <div class="chart-card" id="ticketChartCard">
                        <h3><i class="fas fa-ticket-alt"></i> Tickets Sold Over Time</h3>
                        <div class="chart-filter">
                            <button class="time-filter active" data-period="month">Monthly</button>
                            <button class="time-filter" data-period="week">Weekly</button>
                            <button class="time-filter" data-period="day">Daily</button>
                        </div>
                        <canvas id="ticketChart"></canvas>
                        <div class="loader"></div>
                    </div>
                </div>
                <div class="auditlog">
                    <div class="info-box">
                        <h3><i class="fas fa-file-export"></i> Export Report</h3>
<div class="report-selector">
  <label for="reportType">Select Report Type:</label>
  <select id="reportType">
    <option value="ferry_logs">Ferry Logs</option>
    <option value="tickets">Tickets</option>
    <option value="repair_logs">Repair Logs</option>
    <option value="boat_maintenance">Boat Maintenance</option>
  </select>
</div>
                        <div class="export-buttons">
                            <button class="export-btn" onclick="exportPDF()"><i class="fas fa-file-pdf"></i> PDF</button>
                            <button class="export-btn" onclick="exportExcel()"><i class="fas fa-file-excel"></i> Excel</button>
                            <button class="export-btn" onclick="exportCSV()"><i class="fas fa-file-csv"></i> CSV</button>
                            <button class="export-btn" onclick="exportPrint()"><i class="fas fa-print"></i> Print</button>
                        </div>
                    </div>
                    <div id="auditLogDisplay" class="audit-box">
                        
                        <div class="loader" id="auditLoader"></div>
                        </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.1/xlsx.full.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
  const reportTypeSelect = document.getElementById('reportType');
  const auditLogDisplay = document.getElementById('auditLogDisplay');
  const loader = document.getElementById('auditLoader');

  function loadAuditLog(type) {
    loader.style.display = 'inline-block';
    fetch('fetch_audit_log.php?type=' + type)
      .then(response => response.text())
      .then(data => {
        loader.style.display = 'none';
        auditLogDisplay.innerHTML = data;
      })
      .catch(error => {
        loader.style.display = 'none';
        auditLogDisplay.innerHTML = '<p style="color:red;">Failed to load audit log.</p>';
        console.error('Fetch error:', error);
      });
  }

  reportTypeSelect.addEventListener('change', function () {
    loadAuditLog(this.value);
  });

  // Load initial selection
  loadAuditLog(reportTypeSelect.value);
});
        $(document).ready(function() {
            $("#sidebar-toggle").click(function() {
                $("#sidebar").toggleClass("sidebar-collapsed");
                $("#main-content").toggleClass("content-expanded");
                if ($("#sidebar").hasClass("sidebar-collapsed")) {
                    $("#toggle-icon").removeClass(" fa-chevron-left").addClass("fa-chevron-right");
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
                    else if (page === 'User section') window.location.href = 'template.php';
                });
            });
            $('.time-filter').click(function() {
                const $this = $(this);
                const $card = $this.closest('.chart-card');
                const chartId = $card.find('canvas').attr('id');
                const period = $this.data('period');
                $card.find('.time-filter').removeClass('active');
                $this.addClass('active');
                $card.addClass('loading');
                setTimeout(() => {
                    loadChartData(chartId, period);
                    $card.removeClass('loading');
                }, 800);
            });
            initCharts();
            updateClock();
            setInterval(updateClock, 1000);
            fetchStatsData();
            setInterval(fetchStatsData, 5000);
            fetchAuditLog();
        });
        let passengerChart;
        let ticketChart;
        function initCharts() {
            Chart.defaults.font.family = "'Arial', sans-serif";
            Chart.defaults.font.size = 14;
            Chart.defaults.color = "#c0c0c0";
            Chart.defaults.elements.line.borderWidth = 3;
            Chart.defaults.elements.point.radius = 4;
            Chart.defaults.elements.point.hoverRadius = 6;
            Chart.defaults.plugins.tooltip.backgroundColor = "rgba(20, 20, 20, 0.9)";
            Chart.defaults.plugins.tooltip.padding = 10;
            Chart.defaults.plugins.tooltip.cornerRadius = 4;
            Chart.defaults.plugins.tooltip.titleFont.size = 16;
            Chart.defaults.plugins.legend.display = false;
            const passengerChartConfig = {
                type: 'line',
                data: {
                    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                    datasets: [{
                        label: 'Passengers',
                        data: [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
                        borderColor: '#36a2eb',
                        backgroundColor: 'rgba(54, 162, 235, 0.2)',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.3,
                        pointBackgroundColor: '#ffffff',
                        pointBorderColor: '#36a2eb'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        mode: 'index',
                        intersect: false,
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(255, 255, 255, 0.1)'
                            },
                            ticks: {
                                callback: function(value) {
                                    if (value >= 1000) {
                                        return value / 1000 + 'k';
                                    }
                                    return value;
                                }
                            }
                        },
                        x: {
                            grid: {
                                color: 'rgba(255, 255, 255, 0.1)'
                            }
                        }
                    },
                    plugins: {
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    let label = context.dataset.label || '';
                                    if (label) {
                                        label += ': ';
                                    }
                                    label += new Intl.NumberFormat().format(context.parsed.y);
                                    return label;
                                }
                            }
                        }
                    },
                    animation: {
                        duration: 1500,
                        easing: 'easeOutQuart'
                    }
                }
            };
            const ticketChartConfig = {
                type: 'bar',
                data: {
                    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                    datasets: [{
                        label: 'Tickets Sold',
                        data: [0, 0, 0, 0,  0, 0, 0, 0, 0, 0, 0, 0],
                        backgroundColor: '#4bc0c0',
                        borderColor: '#4bc0c0',
                        borderWidth: 1,
                        borderRadius: 5,
                        hoverBackgroundColor: '#3da8a8'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(255, 255, 255, 0.1)'
                            },
                            ticks: {
                                callback: function(value) {
                                    if (value >= 1000) {
                                        return value / 1000 + 'k';
                                    }
                                    return value;
                                }
                            }
                        },
                        x: {
                            grid: {
                                color: 'rgba(255, 255, 255, 0.1)'
                            }
                        }
                    },
                    plugins: {
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    let label = context.dataset.label || '';
                                    if (label) {
                                        label += ': ';
                                    }
                                    label += new Intl.NumberFormat().format(context.parsed.y);
                                    return label;
                                }
                            }
                        }
                    },
                    animation: {
                        duration: 1500,
                        easing: 'easeOutQuart'
                    }
                }
            };
            passengerChart = new Chart(
                document.getElementById('passengerChart').getContext('2d'),
                passengerChartConfig
            );
            ticketChart = new Chart(
                document.getElementById('ticketChart').getContext('2d'),
                ticketChartConfig
            );
            loadChartData('passengerChart', 'month');
            loadChartData('ticketChart', 'month');
        }
        function loadChartData(chartId, period) {
            $(`#${chartId}`).closest('.chart-card').addClass('loading');
            const sampleData = generateSampleData(period);
            fetch('getChartData.php?period=' + period)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    updateChart(chartId, data, period);
                })
                .catch(error => {
                    console.error('Could not fetch chart data:', error);
                    updateChart(chartId, sampleData, period);
                })
                .finally(() => {
                    $(`#${chartId}`).closest('.chart-card').removeClass('loading');
                });
        }
        function generateSampleData(period) {
            let labels = [];
            let passengerData = [];
            let ticketData = [];
            if (period === 'month') {
                labels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
                passengerData = [2800, 3200, 2950, 3600, 4100, 4500, 5200, 4800, 5100, 5600, 6200, 5800];
                ticketData = [1200, 1350, 1280, 1500, 1700, 1850, 2100, 1950, 2050, 2200, 2400, 2250];
            } else if (period === 'week') {
                labels = ['Week 1', 'Week 2', 'Week 3', 'Week 4'];
                passengerData = [1200, 1450, 1380, 1650];
                ticketData = [520, 580, 540, 620];
            } else if (period === 'day') {
                labels = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
                passengerData = [520, 580, 610, 550, 690, 780, 410];
                ticketData = [210, 250, 270, 240, 310, 350, 180];
            }
            return {
                passengers: { 
                    labels: labels,
                    data: passengerData
                },
                tickets: {
                    labels: labels,
                    data: ticketData
                }
            };
        }
        function updateChart(chartId, data, period) {
            if (chartId === 'passengerChart') {
                const chartData = data.passengers || data.passengers;
                passengerChart.data.labels = chartData.labels || chartData.map(p => p.month || p.label);
                passengerChart.data.datasets[0].data = chartData.data || chartData.map(p => p.passengers || p.value);
                let title = 'Passenger Growth ';
                if (period === 'month') title += '(Monthly)';
                else if (period === 'week') title += '(Weekly)';
                else if (period === 'day') title += '(Daily)';
                passengerChart.options.plugins.title = {
                    display: true,
                    text: title,
                    font: { size: 16 },
                    color: '#ffffff',
                    padding: { bottom: 10 }
                };
                passengerChart.update();
            } else if (chartId === 'ticketChart') {
                const chartData = data.tickets || data.tickets;
                ticketChart.data.labels = chartData.labels || chartData.map(t => t.month || t.label);
                ticketChart.data.datasets[0].data = chartData.data || chartData.map(t => t.tickets || t.value);
                let title = 'Tickets Sold ';
                if (period === 'month') title += '(Monthly)';
                else if (period === 'week') title += '(Weekly)';
                else if (period === 'day') title += '(Daily)';
                ticketChart.options.plugins.title = {
                    display: true,
                    text: title,
                    font: { size: 16 },
                    color: '#ffffff',
                    padding: { bottom: 10 }
                };
                ticketChart.update();
            }
        }
        function fetchStatsData() {
            $.ajax({
                url: 'getStats.php',
                method: 'GET',
                dataType: 'json',
                success: function(data) {
                    const statBoxes = document.querySelectorAll('.stat-box');
                    if (statBoxes.length >= 4) {
                        animateValue(statBoxes[0].querySelector('p'), parseInt(statBoxes[0].querySelector('p').textContent), data.total_passengers || parseInt(statBoxes[0].querySelector('p').textContent), 1000);
                        animateValue(statBoxes[1].querySelector('p'), parseInt(statBoxes[1].querySelector('p').textContent), data.active_passes || parseInt(statBoxes[1].querySelector('p').textContent), 1000);
                        animateValue(statBoxes[2].querySelector('p'), parseInt(statBoxes[2].querySelector('p').textContent), data.active_ferries || parseInt(statBoxes[2].querySelector('p').textContent), 1000);
                        const currentOccupancy = parseFloat(statBoxes[3].querySelector('p').textContent);
                        const newOccupancy = data.occupancy_percentage || currentOccupancy;
                        animateValue(statBoxes[3].querySelector('p'), currentOccupancy, newOccupancy, 1000, '%');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Failed to fetch stats:', error);
                }
            });
        }
        function animateValue(element, start, end, duration, suffix = '') {
            let startTimestamp = null;
            const step = (timestamp) => {
                if (!startTimestamp) startTimestamp = timestamp;
                const progress = Math.min((timestamp - startTimestamp) / duration, 1);
                const current = Math.floor(progress * (end - start) + start);
                element.textContent = current + suffix;
                if (progress < 1) {
                    window.requestAnimationFrame(step);
                }
            };
            window.requestAnimationFrame(step);
        }
        function fetchAuditLog() {
            setTimeout(() => {
                console.log('Audit log refreshed');
            }, 500);
        }
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
        function exportPDF() {
            const reportType = document.getElementById('reportType').value;
            const { jsPDF } = window.jspdf;
            if (!jsPDF) {
                alert('PDF library not loaded. Please try again later.');
                return;
            }
            try {
                const doc = new jsPDF();
                doc.setFontSize(22);
                doc.setTextColor(0, 127, 255);
                doc.text(`Pasig River Ferry Service`, 105, 20, { align: 'center' });
                doc.setFontSize(16);
                doc.setTextColor(0, 0, 0);
                doc.text(`${reportType.replace(/([A-Z])/g, ' $1').replace(/^./, str => str.toUpperCase())} Report`, 105, 30, { align: 'center' });
                doc.setFontSize(12);
                doc.text(`Generated on: ${new Date().toLocaleString()}`, 105, 40, { align: 'center' });
                doc.setFontSize(12);
                doc.setTextColor(0, 127, 255);
                doc.text("ID", 20, 60);
                doc.text("Name", 40, 60);
                doc.text("Date", 100, 60);
                doc.text("Status", 160, 60);
                doc.setLineWidth(0.5);
                doc.line(20, 65, 190, 65);
                doc.setTextColor(0, 0, 0);
                doc.text("1", 20, 75);
                doc.text("Boat 1", 40, 75);
                doc.text("2025-05-10", 100, 75);
                doc.text("Active", 160, 75);
                doc.text("2", 20, 85);
                doc.text("Boat 2", 40, 85);
                doc.text("2025-05-11", 100, 85);
                doc.text("Inactive", 160, 85);
                doc.save(`${reportType}_Report.pdf`);
            } catch (error) {
                console.error('Error generating PDF:', error);
                alert('Failed to generate PDF. Please try again.');
            }
        }
        function exportExcel() {
            const reportType = document.getElementById('reportType').value;
            if (!XLSX) {
                alert('Excel library not loaded. Please try again later.');
                return;
            }
            try {
                const data = [
                    ["ID", "Name", "Date", "Status"],
                    [1, "Boat 1", "2025-05-10", "Active"],
                    [2, "Boat 2", "2025-05-11", "Inactive"],
                    [3, "Boat 3", "2025-05-12", "Maintenance"],
                    [4, "Boat 4", "2025-05-13", "Active"],
                    [5, "Boat 5", "2025-05-14", "Active"]
                ];
                const ws = XLSX.utils.aoa_to_sheet(data);
                const wb = XLSX.utils.book_new();
                XLSX.utils.book_append_sheet(wb, ws, `${reportType} Report`);
                const headerStyle = {
                    font: { bold: true, color: { rgb: "0066CC" } },
                    fill: { fgColor: { rgb: "EEEEEE" } }
                };
                for (let i = 0; i < data[0].length; i++) {
                    const cellRef = XLSX.utils.encode_cell({r: 0, c: i});
                    if (!ws[cellRef].s) ws[cellRef].s = {};
                    Object.assign(ws[cellRef].s, headerStyle);
                }
                XLSX.writeFile(wb, `${reportType}_Report.xlsx`);
            } catch (error) {
                console.error('Error generating Excel:', error);
                alert('Failed to generate Excel file. Please try again.');
            }
        }
        function exportCSV() {
            const reportType = document.getElementById('reportType').value;
            try {
                const data = [
                    ["ID", "Name", "Date", "Status"],
                    [1, "Boat 1", "2025-05-10", "Active"],
                    [2, "Boat 2", "2025-05-11", "Inactive"],
                    [3, "Boat 3", "2025-05-12", "Maintenance"],
                    [4, "Boat 4", "2025-05-13", "Active"],
                    [5, "Boat 5", "2025-05-14", "Active"]
                ];
                let csvContent = "data:text/csv;charset=utf-8,";
                data.forEach((rowArray) => {
                    const row = rowArray.join(",");
                    csvContent += row + "\r\n";
                });
                const encodedUri = encodeURI(csvContent);
                const link = document.createElement("a");
                link.setAttribute("href", encodedUri);
                link.setAttribute("download", `${reportType}_Report.csv`);
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            } catch (error) {
                console.error('Error generating CSV:', error);
                alert('Failed to generate CSV file. Please try again.');
            }
        }
        function exportPrint() {
    const reportType = document.getElementById('reportType').value;
    const printContents = document.getElementById('auditLogDisplay').innerHTML;
    
    if (!printContents.trim()) {
        alert('No report data to print.');
        return;
    }

    const originalContents = document.body.innerHTML;
    const printWindow = window.open('', '', 'height=600,width=800');
    printWindow.document.write(`
        <html>
        <head>
            <title>${reportType} Report</title>
            <style>
                body { font-family: Arial, sans-serif; color: #000; padding: 20px; }
                table { width: 100%; border-collapse: collapse; }
                th, td { border: 1px solid #000; padding: 8px; text-align: left; }
                h2 { text-align: center; color: #004080; }
            </style>
        </head>
        <body>
            <h2>Pasig River Ferry Service - ${reportType.charAt(0).toUpperCase() + reportType.slice(1)} Report</h2>
            ${printContents}
        </body>
        </html>
    `);
    printWindow.document.close();
    printWindow.focus();
    printWindow.print();
    printWindow.close();
}

    </script>
</body>
</html>