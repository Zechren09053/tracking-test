<?php

include 'db_connect.php';

// Start the session
session_start();

// Database-based online user tracking
// This avoids file permission issues with reading session files

// Update user's last activity timestamp when they're active
function updateUserActivity($userId, $userType) {
    global $conn;
    
    $table = ($userType == 'staff') ? 'staff_users' : 'users';
    $idField = ($userType == 'staff') ? 'staff_id' : 'id';
    
    $query = "UPDATE $table SET last_activity = NOW() WHERE $idField = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $userId);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}

// Check if a user is online based on database timestamp
function isOnline($userId, $userType) {
    global $conn;
    
    $table = ($userType == 'staff') ? 'staff_users' : 'users';
    $idField = ($userType == 'staff') ? 'staff_id' : 'id';
    
    // Consider users active if they had activity in the last 15 minutes
    $query = "SELECT 1 FROM $table WHERE $idField = ? AND last_activity > NOW() - INTERVAL 15 MINUTE";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $userId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $isOnline = (mysqli_num_rows($result) > 0);
    mysqli_stmt_close($stmt);
    
    return $isOnline;
}

// If user is logged in, update their activity timestamp
if (isset($_SESSION['logged_in_staff_id'])) {
    updateUserActivity($_SESSION['logged_in_staff_id'], 'staff');
} elseif (isset($_SESSION['logged_in_user_id'])) {
    updateUserActivity($_SESSION['logged_in_user_id'], 'user');
}

// Handle AJAX requests for data updates
if (isset($_GET['ajax']) && $_GET['ajax'] == '1') {
    header('Content-Type: application/json');
    
    // Fetch staff user logins
    $staffQuery = "SELECT staff_id, CONCAT(first_name, ' ', last_name) AS name, email, login_count, last_login, last_activity FROM staff_users";
    $staffResult = mysqli_query($conn, $staffQuery);

    // Fetch ticket holder user logins
    $userQuery = "SELECT id, full_name AS name, email, login_count, last_login, last_activity FROM users";
    $userResult = mysqli_query($conn, $userQuery);

    // Online counter
    $onlineStaff = 0;
    $onlineUsers = 0;

    // Store staff users for display
    $staffUsers = [];
    while($row = mysqli_fetch_assoc($staffResult)) {
        $isUserOnline = isOnline($row['staff_id'], 'staff');
        $row['is_online'] = $isUserOnline;
        $staffUsers[] = $row;
        if ($isUserOnline) {
            $onlineStaff++;
        }
    }

    // Store regular users for display
    $regularUsers = [];
    while($row = mysqli_fetch_assoc($userResult)) {
        $isUserOnline = isOnline($row['id'], 'user');
        $row['is_online'] = $isUserOnline;
        $regularUsers[] = $row;
        if ($isUserOnline) {
            $onlineUsers++;
        }
    }
    
    // Return JSON data
    echo json_encode([
        'onlineStaff' => $onlineStaff,
        'onlineUsers' => $onlineUsers,
        'staffUsers' => $staffUsers,
        'regularUsers' => $regularUsers,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    exit;
}

// Initial data load for non-AJAX requests
// Fetch staff user logins
$staffQuery = "SELECT staff_id, CONCAT(first_name, ' ', last_name) AS name, email, login_count, last_login, last_activity FROM staff_users";
$staffResult = mysqli_query($conn, $staffQuery);

// Fetch ticket holder user logins
$userQuery = "SELECT id, full_name AS name, email, login_count, last_login, last_activity FROM users";
$userResult = mysqli_query($conn, $userQuery);

// Online counter
$onlineStaff = 0;
$onlineUsers = 0;

// Store staff users for display
$staffUsers = [];
while($row = mysqli_fetch_assoc($staffResult)) {
    $isUserOnline = isOnline($row['staff_id'], 'staff');
    $row['is_online'] = $isUserOnline;
    $staffUsers[] = $row;
    if ($isUserOnline) {
        $onlineStaff++;
    }
}

// Store regular users for display
$regularUsers = [];
while($row = mysqli_fetch_assoc($userResult)) {
    $isUserOnline = isOnline($row['id'], 'user');
    $row['is_online'] = $isUserOnline;
    $regularUsers[] = $row;
    if ($isUserOnline) {
        $onlineUsers++;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login Records - Live</title>
    <style>
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, Arial, sans-serif; 
            padding: 20px; 
            background: #1a1d23; 
            color: #e1e5e9;
            margin: 0;
            line-height: 1.6;
        }
        h2 { 
            color: #ffffff; 
            margin-top: 0;
            font-weight: 600;
        }
        h3 {
            color: #ffffff;
            margin-top: 0;
            font-weight: 600;
        }
        .summary { 
            margin-bottom: 30px;
            background: #242832;
            padding: 20px;
            border-radius: 4px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.3);
            border: 1px solid #3a3f4b;
            border-left: 4px solid #4a90e2;
        }
        .status-info {
            float: right;
            font-size: 0.9em;
            color: #b0b7c3;
        }
        .auto-refresh-toggle {
            margin-left: 15px;
        }
        .auto-refresh-toggle input {
            margin-right: 5px;
            accent-color: #4a90e2;
        }
        .auto-refresh-toggle label {
            color: #b0b7c3;
            cursor: pointer;
        }
        table { 
            border-collapse: collapse; 
            width: 100%; 
            margin-bottom: 40px; 
            background: #242832; 
            box-shadow: 0 2px 8px rgba(0,0,0,0.3);
            border-radius: 4px;
            overflow: hidden;
            border: 1px solid #3a3f4b;
        }
        th, td { 
            border: 1px solid #3a3f4b; 
            padding: 12px 16px; 
            text-align: left; 
        }
        th { 
            background: #2c313c;
            color: #ffffff; 
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.85em;
            letter-spacing: 0.5px;
        }
        tr:nth-child(even) { 
            background-color: #2a2f3a; 
        }
        tr:nth-child(odd) {
            background-color: #242832;
        }
        tr:hover {
            background-color: #2e3441;
            transition: background-color 0.15s ease;
        }
        .online-indicator {
            display: inline-block;
            width: 10px;
            height: 10px;
            border-radius: 50%;
            margin-right: 8px;
        }
        .online {
            background-color: #28a745;
        }
        .offline {
            background-color: #6c757d;
        }
        .timestamp {
            color: #b0b7c3;
            font-size: 0.9em;
        }
        .updating {
            opacity: 0.6;
            transition: opacity 0.3s;
        }
        .refresh-button {
            background: #4a90e2;
            color: white;
            border: none;
            padding: 10px 18px;
            border-radius: 4px;
            cursor: pointer;
            margin-right: 10px;
            font-weight: 500;
            transition: background-color 0.2s ease;
        }
        .refresh-button:hover {
            background: #3a7bc8;
        }
        .refresh-button:disabled {
            background: #495057;
            cursor: not-allowed;
        }
        
        /* Clean professional dark styling */
        strong {
            color: #4a90e2;
            font-weight: 600;
        }
        
        #lastUpdate {
            margin-top: 8px;
            font-size: 0.85em;
            color: #8e95a3;
        }
        
        /* Scrollbar styling for dark theme */
        ::-webkit-scrollbar {
            width: 8px;
        }
        
        ::-webkit-scrollbar-track {
            background: #1a1d23;
        }
        
        ::-webkit-scrollbar-thumb {
            background: #495057;
            border-radius: 4px;
        }
        
        ::-webkit-scrollbar-thumb:hover {
            background: #5a6268;
        }
        
        /* Professional focus states */
        .refresh-button:focus,
        .auto-refresh-toggle input:focus {
            outline: 2px solid #4a90e2;
            outline-offset: 2px;
        }
    </style>
</head>
<body>

<div class="summary">
    <div class="status-info">
        <button id="refreshBtn" class="refresh-button" onclick="updateData()">Refresh Now</button>
        <span class="auto-refresh-toggle">
            <input type="checkbox" id="autoRefresh" checked>
            <label for="autoRefresh">Auto-refresh (10   s)</label>
        </span>
        <div id="lastUpdate">Last updated: <?= date('M j, Y g:i:s a') ?></div>
    </div>
    <h3>Currently Online</h3>
    <p>Staff Members: <strong id="onlineStaffCount"><?= $onlineStaff ?></strong> | Ticket Holders: <strong id="onlineUsersCount"><?= $onlineUsers ?></strong></p>
</div>

<h2>Staff Login Records</h2>
<table id="staffTable">
    <thead>
        <tr>
            <th>Status</th>
            <th>ID</th>
            <th>Name</th>
            <th>Email</th>
            <th>Login Count</th>
            <th>Last Login</th>
            <th>Last Activity</th>
        </tr>
    </thead>
    <tbody id="staffTableBody">
        <?php foreach($staffUsers as $row): ?>
        <tr>
            <td><span class="online-indicator <?= $row['is_online'] ? 'online' : 'offline' ?>"></span> <?= $row['is_online'] ? 'Online' : 'Offline' ?></td>
            <td><?= $row['staff_id'] ?></td>
            <td><?= htmlspecialchars($row['name']) ?></td>
            <td><?= htmlspecialchars($row['email']) ?></td>
            <td><?= $row['login_count'] ?></td>
            <td>
                <?php if($row['last_login']): ?>
                    <span class="timestamp"><?= date('M j, Y g:i a', strtotime($row['last_login'])) ?></span>
                <?php else: ?>
                    <span class="timestamp">Never</span>
                <?php endif; ?>
            </td>
            <td>
                <?php if($row['last_activity']): ?>
                    <span class="timestamp"><?= date('M j, Y g:i a', strtotime($row['last_activity'])) ?></span>
                <?php else: ?>
                    <span class="timestamp">Never</span>
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<h2>Ticket Holder Login Records</h2>
<table id="usersTable">
    <thead>
        <tr>
            <th>Status</th>
            <th>ID</th>
            <th>Full Name</th>
            <th>Email</th>
            <th>Login Count</th>
            <th>Last Login</th>
            <th>Last Activity</th>
        </tr>
    </thead>
    <tbody id="usersTableBody">
        <?php foreach($regularUsers as $row): ?>
        <tr>
            <td><span class="online-indicator <?= $row['is_online'] ? 'online' : 'offline' ?>"></span> <?= $row['is_online'] ? 'Online' : 'Offline' ?></td>
            <td><?= $row['id'] ?></td>
            <td><?= htmlspecialchars($row['name']) ?></td>
            <td><?= htmlspecialchars($row['email']) ?></td>
            <td><?= $row['login_count'] ?></td>
            <td>
                <?php if($row['last_login']): ?>
                    <span class="timestamp"><?= date('M j, Y g:i a', strtotime($row['last_login'])) ?></span>
                <?php else: ?>
                    <span class="timestamp">Never</span>
                <?php endif; ?>
            </td>
            <td>
                <?php if($row['last_activity']): ?>
                    <span class="timestamp"><?= date('M j, Y g:i a', strtotime($row['last_activity'])) ?></span>
                <?php else: ?>
                    <span class="timestamp">Never</span>
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<script>
let autoRefreshInterval;
let isUpdating = false;

// Format date function
function formatDate(dateString) {
    if (!dateString) return 'Never';
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', { 
        month: 'short', 
        day: 'numeric', 
        year: 'numeric',
        hour: 'numeric',
        minute: '2-digit',
        hour12: true
    });
}

// Update data function
function updateData() {
    if (isUpdating) return;
    
    isUpdating = true;
    const refreshBtn = document.getElementById('refreshBtn');
    refreshBtn.disabled = true;
    refreshBtn.textContent = 'Updating...';
    
    // Add updating class to tables
    document.getElementById('staffTable').classList.add('updating');
    document.getElementById('usersTable').classList.add('updating');
    
    fetch(window.location.href + '?ajax=1')
        .then(response => response.json())
        .then(data => {
            // Update online counters
            document.getElementById('onlineStaffCount').textContent = data.onlineStaff;
            document.getElementById('onlineUsersCount').textContent = data.onlineUsers;
            
            // Update staff table
            updateTable('staffTableBody', data.staffUsers, 'staff');
            
            // Update users table
            updateTable('usersTableBody', data.regularUsers, 'user');
            
            // Update last update time
            const updateTime = new Date(data.timestamp);
            document.getElementById('lastUpdate').textContent = 'Last updated: ' + updateTime.toLocaleDateString('en-US', { 
                month: 'short', 
                day: 'numeric', 
                year: 'numeric',
                hour: 'numeric',
                minute: '2-digit',
                second: '2-digit',
                hour12: true
            });
        })
        .catch(error => {
            console.error('Error updating data:', error);
            document.getElementById('lastUpdate').textContent = 'Error updating data';
        })
        .finally(() => {
            isUpdating = false;
            refreshBtn.disabled = false;
            refreshBtn.textContent = 'Refresh Now';
            
            // Remove updating class
            document.getElementById('staffTable').classList.remove('updating');
            document.getElementById('usersTable').classList.remove('updating');
        });
}

// Update table function
function updateTable(tableBodyId, users, userType) {
    const tbody = document.getElementById(tableBodyId);
    tbody.innerHTML = '';
    
    users.forEach(user => {
        const row = document.createElement('tr');
        const idField = userType === 'staff' ? 'staff_id' : 'id';
        
        row.innerHTML = `
            <td>
                <span class="online-indicator ${user.is_online ? 'online' : 'offline'}"></span> 
                ${user.is_online ? 'Online' : 'Offline'}
            </td>
            <td>${user[idField]}</td>
            <td>${escapeHtml(user.name)}</td>
            <td>${escapeHtml(user.email)}</td>
            <td>${user.login_count}</td>
            <td><span class="timestamp">${formatDate(user.last_login)}</span></td>
            <td><span class="timestamp">${formatDate(user.last_activity)}</span></td>
        `;
        
        tbody.appendChild(row);
    });
}

// Escape HTML function
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Auto-refresh functionality
function startAutoRefresh() {
    if (autoRefreshInterval) {
        clearInterval(autoRefreshInterval);
    }
    autoRefreshInterval = setInterval(updateData, 10000); // 1 second
}

function stopAutoRefresh() {
    if (autoRefreshInterval) {
        clearInterval(autoRefreshInterval);
        autoRefreshInterval = null;
    }
}

// Auto-refresh toggle event
document.getElementById('autoRefresh').addEventListener('change', function() {
    if (this.checked) {
        startAutoRefresh();
    } else {
        stopAutoRefresh();
    }
});

// Start auto-refresh by default
startAutoRefresh();

// Update data when page becomes visible (user returns to tab)
document.addEventListener('visibilitychange', function() {
    if (!document.hidden && document.getElementById('autoRefresh').checked) {
        updateData();
    }
});
</script>

</body>
</html>