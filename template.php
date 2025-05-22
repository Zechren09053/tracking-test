<?php
session_start();

require_once 'session_handler.php';
requireLogin();
require 'db_connect.php';

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

// Get user statistics
$total_users = 0;
$active_users = 0;
$expired_users = 0;
$today_users = 0;

$stats_query = "SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN is_active = 1 AND expires_at > NOW() THEN 1 ELSE 0 END) as active,
    SUM(CASE WHEN is_active = 0 OR expires_at <= NOW() THEN 1 ELSE 0 END) as expired,
    SUM(CASE WHEN DATE(created_at) = CURDATE() THEN 1 ELSE 0 END) as today
FROM users";

$stats_result = $conn->query($stats_query);
if ($stats_result && $stats_result->num_rows > 0) {
    $stats = $stats_result->fetch_assoc();
    $total_users = $stats['total'];
    $active_users = $stats['active'];
    $expired_users = $stats['expired'];
    $today_users = $stats['today'];
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management - Ferry Admin Dashboard</title>
    <link rel="stylesheet" href="Db.css">
    <link rel="stylesheet" href="usertem.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
</head>
<body>
    <div class="main-container">
        <div class="container">
            <!-- Sidebar -->
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
                            <li><a href="settings.php"><div class="nav-item-content"><i class="fas fa-cog"></i><span class="settings-text">Settings</span></div></a></li>
                            <li><a href="mail.php"><div class="nav-item-content"><i class="fas fa-question-circle"></i><span class="settings-text">Help</span></div></a></li>
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

            <!-- Main content -->
            <div class="main" id="main-content">
                <div class="header">
                    <h1>User Management System</h1>
                </div>
                
                <!-- User Stats -->
                <div class="stats-container">
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="stat-info">
                            <h3>Total Users</h3>
                            <p><?php echo $total_users; ?></p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon active">
                            <i class="fas fa-user-check"></i>
                        </div>
                        <div class="stat-info">
                            <h3>Active Users</h3>
                            <p><?php echo $active_users; ?></p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon expired">
                            <i class="fas fa-user-times"></i>
                        </div>
                        <div class="stat-info">
                            <h3>Expired Users</h3>
                            <p><?php echo $expired_users; ?></p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon today">
                            <i class="fas fa-user-plus"></i>
                        </div>
                        <div class="stat-info">
                            <h3>New Today</h3>
                            <p><?php echo $today_users; ?></p>
                        </div>
                    </div>
                </div>
                
                <div class="tabs">
                    <div class="tab active" onclick="openTab('view')">View Users</div>
                    <div class="tab" onclick="openTab('add')">Add User</div>

                </div>
                
                <!-- View Users Tab -->
                <div id="view" class="tab-content active">
                    <div class="search-container">
                        <input type="text" id="search-input" placeholder="Search by name, email or phone..." onkeyup="searchUsers()">
                        <button id="search-btn"><i class="fas fa-search"></i></button>
                        
                        <div class="filter-container">
                            <select id="status-filter" onchange="filterUsers()">
                                <option value="all">All Status</option>
                                <option value="active">Active</option>
                                <option value="expired">Expired</option>
                            </select>
                            <button id="refresh-btn" onclick="refreshUsers()"><i class="fas fa-sync-alt"></i></button>
                        </div>
                    </div>
                    
                    <div class="users-table-container">
                        <table id="users-table">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Issued</th>
                                    <th>Expires</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="users-table-body">
                                <!-- User rows will be loaded here -->
                                <tr>
                                    <td colspan="7" class="table-loading">Loading users...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="pagination">
                        <button id="prev-page" onclick="prevPage()" disabled>
                            <i class="fas fa-chevron-left"></i> Previous
                        </button>
                        <span id="page-info">Page 1 of 1</span>
                        <button id="next-page" onclick="nextPage()" disabled>
                            Next <i class="fas fa-chevron-right"></i>
                        </button>
                    </div>
                </div>
 
                <!-- Add User Tab -->
                <div id="add" class="tab-content">
                    
                    <div class="form-container">
                        <form id="user-form">
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="full_name">Full Name <span class="required">*</span></label>
                                    <input type="text" id="full_name" name="full_name" required>
                                </div>
                                <div class="form-group">
                                    <label for="birth_date">Date of Birth <span class="required">*</span></label>
                                    <input type="date" id="birth_date" name="birth_date" required>
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="email">Email Address <span class="required">*</span></label>
                                    <input type="email" id="email" name="email" required>
                                    <div id="email-error" class="error"></div>
                                </div>
                                <div class="form-group">
                                    <label for="phone_number">Phone Number <span class="required">*</span></label>
                                    <input type="tel" id="phone_number" name="phone_number" required>
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="password">Password <span class="required">*</span></label>
                                    <input type="password" id="password" name="password" required>
                                </div>
                                <div class="form-group">
                                    <label for="confirm_password">Confirm Password <span class="required">*</span></label>
                                    <input type="password" id="confirm_password" name="confirm_password" required>
                                    <div id="password-error" class="error"></div>
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="expires_at">Expiry Date <span class="required">*</span></label>
                                    <input type="date" id="expires_at" name="expires_at" required>
                                </div>
                                <div class="form-group">
                                    <label for="is_active">Status</label>
                                    <select id="is_active" name="is_active">
                                        <option value="1">Active</option>
                                        <option value="0">Inactive</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="profile_image">Profile Image</label>
                                <input type="file" id="profile_image" name="profile_image" accept="image/*">
                                <div class="image-preview" id="image-preview">
                                    <span>No image selected</span>
                                </div>
                            </div>
                            
                            <div class="form-buttons">
                                <button type="button" onclick="resetForm()">Reset</button>
                                <button type="button" id="submit-btn" onclick="submitForm()">Add User</button>
                            </div>
                        </form>
                    </div>
                </div>

                
            </div>
        </div>
    </div>
    
    <!-- User Details Modal -->
    <div id="user-modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>User Details</h2>
                <span class="close">&times;</span>
            </div>
            <div class="modal-body">
                <div class="user-details">
                    <div class="user-profile">
                        <img id="modal-user-image" src="/api/placeholder/150/150" alt="User Profile">
                        <div id="user-qrcode"></div>
                    </div>
                    <div class="user-info">
                        <div class="info-row">
                            <span class="info-label">Name:</span>
                            <span id="modal-user-name"></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Email:</span>
                            <span id="modal-user-email"></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Phone:</span>
                            <span id="modal-user-phone"></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Birth Date:</span>
                            <span id="modal-user-dob"></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Issued:</span>
                            <span id="modal-user-issued"></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Expires:</span>
                            <span id="modal-user-expires"></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Last Used:</span>
                            <span id="modal-user-last-used"></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Status:</span>
                            <span id="modal-user-status" class="status"></span>
                        </div>
                    </div>
                </div>
                <div class="modal-actions">
                    <button id="print-card-btn" onclick="printUserCard()">
                        <i class="fas fa-print"></i> Print ID Card
                    </button>
                    <button id="edit-user-btn" onclick="editUser()">
                        <i class="fas fa-edit"></i> Edit
                    </button>
                    <button id="toggle-status-btn">
                        <i class="fas fa-exchange-alt"></i> <span id="toggle-status-text">Deactivate</span>
                    </button>
                    <button id="delete-user-btn" class="delete-btn">
                        <i class="fas fa-trash-alt"></i> Delete
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Edit User Modal -->
    <div id="edit-modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Edit User</h2>
                <span class="close">&times;</span>
            </div>
            <div class="modal-body">
                <form id="edit-user-form">
                    <input type="hidden" id="edit-user-id">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="edit-full-name">Full Name <span class="required">*</span></label>
                            <input type="text" id="edit-full-name" name="edit-full-name" required>
                        </div>
                        <div class="form-group">
                            <label for="edit-birth-date">Date of Birth <span class="required">*</span></label>
                            <input type="date" id="edit-birth-date" name="edit-birth-date" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="edit-email">Email Address <span class="required">*</span></label>
                            <input type="email" id="edit-email" name="edit-email" required>
                            <div id="edit-email-error" class="error"></div>
                        </div>
                        <div class="form-group">
                            <label for="edit-phone">Phone Number <span class="required">*</span></label>
                            <input type="tel" id="edit-phone" name="edit-phone" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="edit-password">New Password</label>
                            <input type="password" id="edit-password" name="edit-password" placeholder="Leave blank to keep current password">
                        </div>
                        <div class="form-group">
                            <label for="edit-confirm-password">Confirm Password</label>
                            <input type="password" id="edit-confirm-password" name="edit-confirm-password">
                            <div id="edit-password-error" class="error"></div>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="edit-expires">Expiry Date <span class="required">*</span></label>
                            <input type="date" id="edit-expires" name="edit-expires" required>
                        </div>
                        <div class="form-group">
                            <label for="edit-status">Status</label>
                            <select id="edit-status" name="edit-status">
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="edit-profile-image">Profile Image</label>
                        <input type="file" id="edit-profile-image" name="edit-profile-image" accept="image/*">
                        <div class="image-preview" id="edit-image-preview">
                        </div>
                    </div>
                    
                    <div class="form-buttons">
                        <button type="button" id="update-btn" onclick="updateUser()">Update User</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Delete User Confirmation Modal -->
    <div id="delete-modal" class="modal">
        <div class="modal-content delete-confirm">
            <div class="modal-header">
                <h2>Confirm Deletion</h2>
                <span class="close">&times;</span>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete the user <strong id="delete-user-name"></strong>?</p>
                <p class="warning">This action cannot be undone.</p>
                <div class="form-buttons">
                    <button type="button" id="cancel-delete-btn">Cancel</button>
                    <button type="button" id="confirm-delete-btn" class="delete-btn">Delete</button>
                </div>
            </div>
        </div>
    </div>
    
    <script src="user-section.js"></script>
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
            
            // Initial load of user data
            loadUsers();
        });
    </script>
</body>
</html>