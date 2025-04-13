<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Ferry Admin Dashboard</title>
    <link rel="stylesheet" href="Db.css">
</head>
<body>
    <div class="container">

        <!-- Sidebar -->
        <div class="sidebar">
            <div>
                <div class="logo">
                    <img src="logo.png" alt="Logo" style="width: 30px; height: 30px;">
                    PRFS MANAGEMENT
                </div>

                <div class="search-bar">
                    <input type="text" placeholder="Search">
                </div>

                <ul class="nav">
                    <li class="active">Dashboard</li>
                    <li>Analytics</li>
                    <li>Tracking</li>
                    <li>Ferry Management</li>
                    <li>Route and Schedules</li>
                    <li>Tickets / Reservations</li>
                </ul>

                <div class="settings">
                    Settings (Language, time zone, etc.)<br>
                    Help<br>
                    <a href="#">Logout</a>
                </div>
            </div>

            <div class="profile">
                <img src="profile.png" alt="Profile" style="width: 40px; height: 40px; border-radius: 50%;">
                <div>
                    <strong>Username</strong><br>
                    user@email.com
                </div>
            </div>
        </div>

        <!-- Main Dashboard -->
        <div class="main">
            <div class="header">
                <h1>Dashboard</h1>
            </div>

            <div class="stats">
                <div class="stat-box">
                    <h2>Total Passengers</h2>
                    <p>10,342</p>
                    <div class="stat-change">↑ 12% From last month</div>
                </div>
                <div class="stat-box">
                    <h2>Tickets Sold</h2>
                    <p>8,912</p>
                    <div class="stat-change">↑ 9% From last month</div>
                </div>
                <div class="stat-box">
                    <h2>Total Expenses</h2>
                    <p>$3,219</p>
                    <div class="stat-change">↓ 4% From last month</div>
                </div>
                <div class="stat-box">
                    <h2>Total Income</h2>
                    <p>$12,450</p>
                    <div class="stat-change">↑ 15% From last month</div>
                </div>
            </div>

            <div class="tracking">
                <!-- Boat List -->
                <div class="boat-list">
                    <h2>Ferry Tracking</h2>

                    <h2>Ferry Tracking</h2>

                    <div class="boat-card">
                        <div class="top">
                            <strong>Ferry #B1023</strong>
                            <span>View Location</span>
                        </div>
                        <div class="bottom">
                            Active Time: 02:15 hrs<br>
                            Status: In Operation<br>
                            Operator: John Doe
                        </div>
                    </div>

                    <div class="boat-card">
                        <div class="top">
                            <strong>Ferry #B2041</strong>
                            <span>View Location</span>
                        </div>
                        <div class="bottom">
                            Active Time: 01:05 hrs<br>
                            Status: Docked<br>
                            Operator: Jane Smith
                        </div>
                    </div>

                </div>

                <!-- Map Section -->
                <div class="map">
                    <p style="text-align: center; padding-top: 180px;">[ Map will be displayed here ]</p>
                </div>
            </div>
        </div>

    </div>
</body>
</html>
