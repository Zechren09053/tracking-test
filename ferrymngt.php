<?php
session_start();
require_once 'db_connect.php';
require_once 'session_handler.php';
requireLogin();
// Fetch user details from the database
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

// Fetch ferry data
$sql = "SELECT * FROM ferries";
$result = $conn->query($sql);
$ferries = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $ferries[] = $row;
    }
}

// Close the connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Ferry MNGMT</title>
    <link rel="stylesheet" href="Db.css">
    <link rel="stylesheet" href="fmg.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <!-- Main container with rounded edges -->
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
                            <li class="active"data-page="ferrymngt">
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

        </div>
            <!-- Main Dashboard -->
            <div class="main">
                <div class="header">
                    <h1>Ferry Management</h1>
                    <div id="clock" style="margin-bottom: 10px; font-size: 16px; color: #00b0ff;"></div>
                </div>
                <div class="register-new-ferry" style="text-align: left; margin-top: 30px;">
                    <button id="open-registration-form" class="btn" style="background-color:#00b0ff; color: white; padding: 12px 20px; border: none; border-radius: 4px; font-size: 16px; cursor: pointer; width: auto; margin: 10px 0;">
                        Register New Ferry
                    </button>
                </div>

                <!-- Modified layout with two-column view -->
                <div class="management-container">
                    <!-- Left side: Ferry list -->
                    <div class="ferry-list-container">
                        <div class="list-box">
                            <div class="ferry-management" id="ferry-list2">
                                <?php foreach ($ferries as $ferry): ?>
                                    <div class="ferry-card" id="ferry-row-<?= $ferry['id'] ?>" data-ferry-id="<?= $ferry['id'] ?>">
                                        <div class="ferry-info">
                                            <strong><?= htmlspecialchars($ferry['name']) ?></strong><br>
                                            <span>Operator: <?= htmlspecialchars($ferry['operator']) ?></span><br>
                                            <span>Active Time: <span id="active-time-<?= $ferry['id'] ?>"><?= $ferry['active_time'] ?></span> mins</span>
                                        </div>
                                        <div class="ferry-status">
                                            <label class="switch">
                                                <input type="checkbox" data-ferry-id="<?= $ferry['id'] ?>" class="status-switch" <?= $ferry['status'] == 'active' ? 'checked' : '' ?>>
                                                <span class="slider"></span>
                                            </label>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Right side: Ferry info preview -->
                    <div class="ferry-info-preview" id="ferry-info-container">
                        <div class="no-ferry-selected">
                            <p>Select a ferry from the list to view details</p>
                        </div>
                    </div>
                </div>

               <!-- Enhanced Ferry Registration Form -->
<!-- Enhanced Ferry Registration Form -->
<div id="registration-form" class="modal">
    <div class="modal-content">
        <h3>Register New Ferry</h3>
        <form action="registerFerry.php" method="POST" enctype="multipart/form-data">
            <!-- Basic Information Section -->
            <div class="form-section">
                <h4 class="section-title">Basic Information</h4>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="ferry-name">Ferry Name <span class="required">*</span></label>
                        <input type="text" id="ferry-name" name="ferry_name" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="ferry-code">Ferry Code <span class="required">*</span></label>
                        <input type="text" id="ferry-code" name="ferry_code" placeholder="e.g. PRFS-001" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="ferry-operator">Operator <span class="required">*</span></label>
                        <input type="text" id="ferry-operator" name="ferry_operator" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="ferry-type">Ferry Type</label>
                        <select id="ferry-type" name="ferry_type">
                            <option value="passenger">Passenger Ferry</option>
                            <option value="cargo">Cargo Ferry</option>
                            <option value="mixed">Mixed Use</option>
                        </select>
                    </div>
                </div>
            </div>
            
            <!-- Capacity & Technical Specifications -->
            <div class="form-section">
                <h4 class="section-title">Capacity & Technical Specifications</h4>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="ferry-max-capacity">Max Passenger Capacity <span class="required">*</span></label>
                        <input type="number" id="ferry-max-capacity" name="ferry_max_capacity" min="1" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="ferry-cargo-capacity">Cargo Capacity (kg)</label>
                        <input type="number" id="ferry-cargo-capacity" name="ferry_cargo_capacity" min="0">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="ferry-length">Length (meters)</label>
                        <input type="number" id="ferry-length" name="ferry_length" step="0.01" min="0">
                    </div>
                    
                    <div class="form-group">
                        <label for="ferry-width">Width (meters)</label>
                        <input type="number" id="ferry-width" name="ferry_width" step="0.01" min="0">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="ferry-max-speed">Maximum Speed (knots)</label>
                        <input type="number" id="ferry-max-speed" name="ferry_max_speed" step="0.1" min="0">
                    </div>
                    
                    <div class="form-group">
                        <label for="fuel-type">Fuel Type</label>
                        <select id="fuel-type" name="fuel_type">
                            <option value="diesel">Diesel</option>
                            <option value="gasoline">Gasoline</option>
                            <option value="electric">Electric</option>
                            <option value="hybrid">Hybrid</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="engine-power">Engine Power (HP)</label>
                        <input type="number" id="engine-power" name="engine_power" min="0">
                    </div>
                    
                    <div class="form-group">
                        <label for="engine-count">Number of Engines</label>
                        <input type="number" id="engine-count" name="engine_count" min="1" value="1">
                    </div>
                </div>
            </div>
            
            <!-- Manufacturer & Build Information -->
            <div class="form-section">
                <h4 class="section-title">Manufacturer & Build Information</h4>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="manufacturer">Manufacturer</label>
                        <input type="text" id="manufacturer" name="manufacturer">
                    </div>
                    
                    <div class="form-group">
                        <label for="model">Model</label>
                        <input type="text" id="model" name="model">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="year-built">Year Built</label>
                        <input type="number" id="year-built" name="year_built" min="1900" max="2030">
                    </div>
                    
                    <div class="form-group">
                        <label for="hull-material">Hull Material</label>
                        <select id="hull-material" name="hull_material">
                            <option value="steel">Steel</option>
                            <option value="aluminum">Aluminum</option>
                            <option value="fiberglass">Fiberglass</option>
                            <option value="wood">Wood</option>
                            <option value="composite">Composite</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                </div>
            </div>
            
            <!-- Safety & Registration -->
            <div class="form-section">
                <h4 class="section-title">Safety & Registration</h4>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="registration-number">Registration Number</label>
                        <input type="text" id="registration-number" name="registration_number">
                    </div>
                    
                    <div class="form-group">
                        <label for="registration-date">Registration Date</label>
                        <input type="date" id="registration-date" name="registration_date">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="last-inspection-date">Last Inspection Date</label>
                        <input type="date" id="last-inspection-date" name="last_inspection_date">
                    </div>
                    
                    <div class="form-group">
                        <label for="next-inspection-date">Next Inspection Date</label>
                        <input type="date" id="next-inspection-date" name="next_inspection_date">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="safety-equipment">Safety Equipment</label>
                        <div class="checkbox-group">
                            <label><input type="checkbox" name="safety_equipment[]" value="life_jackets"> Life Jackets</label>
                            <label><input type="checkbox" name="safety_equipment[]" value="life_rafts"> Life Rafts</label>
                            <label><input type="checkbox" name="safety_equipment[]" value="fire_extinguishers"> Fire Extinguishers</label>
                            <label><input type="checkbox" name="safety_equipment[]" value="first_aid"> First Aid Kit</label>
                            <label><input type="checkbox" name="safety_equipment[]" value="emergency_radio"> Emergency Radio</label>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Images & Documentation -->
            <div class="form-section">
                <h4 class="section-title">Images & Documentation</h4>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="ferry-image">Ferry Image</label>
                        <input type="file" id="ferry-image" name="ferry_image" accept="image/*">
                        <small>Upload a clear image of the ferry (max 5MB)</small>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="registration-documents">Registration Documents</label>
                        <input type="file" id="registration-documents" name="registration_documents" accept=".pdf,.doc,.docx">
                        <small>Upload registration certificates (max 10MB)</small>
                    </div>
                </div>
            </div>
            
            <!-- Additional Information -->
            <div class="form-section">
                <h4 class="section-title">Additional Information</h4>
                
                <div class="form-row full-width">
                    <div class="form-group">
                        <label for="notes">Notes & Special Features</label>
                        <textarea id="notes" name="notes" rows="4"></textarea>
                    </div>
                </div>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn-primary">Register Ferry</button>
                <button type="button" class="btn-secondary" id="close-registration-form">Cancel</button>
            </div>
        </form>
    </div>
</div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
    
        // Fetch ferry data and update the list dynamically
        function fetchFerryData() {
            $.ajax({
                url: 'getFerries.php', // Backend PHP to fetch ferry data
                method: 'GET',
                dataType: 'json',
                success: function(data) {
                    let ferryListHtml = '';
                    if (data.length > 0) {
                        data.forEach(function(ferry) {
                            const isSelected = $('.ferry-card.selected').data('ferry-id') === ferry.id;
                            ferryListHtml += `
                                <div class="ferry-card ${isSelected ? 'selected' : ''}" id="ferry-row-${ferry.id}" data-ferry-id="${ferry.id}">
                                    <div class="ferry-info">
                                        <strong>${ferry.name}</strong><br>
                                        <span>Operator: ${ferry.operator}</span><br>
                                        <span>Active Time: <span id="active-time-${ferry.id}">${ferry.active_time}</span> mins</span><br>
                                        <span>Capacity: ${ferry.current_capacity} / ${ferry.max_capacity}</span>
                                    </div>
                                    <div class="ferry-status">
                                        <label class="switch">
                                            <input type="checkbox" data-ferry-id="${ferry.id}" class="status-switch" ${ferry.status == 'active' ? 'checked' : ''}>
                                            <span class="slider"></span>
                                        </label>
                                    </div>
                                </div>
                            `;
                        });
                    } else {
                        ferryListHtml = '<p>No ferries available.</p>';
                    }
                    $('#ferry-list2').html(ferryListHtml);
                    
                    // Restore the selected state if any
                    const selectedFerryId = sessionStorage.getItem('selectedFerryId');
                    if (selectedFerryId) {
                        $(`#ferry-row-${selectedFerryId}`).addClass('selected');
                    }
                },
                error: function() {
                    alert('Error fetching ferry data');
                }
            });
        }

        // Fetch ferry data every 5 seconds to update the list
        setInterval(fetchFerryData, 5000);
        
        // Function to get ferry details and maintenance/repair info
        function getFerryDetails(ferryId) {
            // Mark selected ferry
            $('.ferry-card').removeClass('selected');
            $(`#ferry-row-${ferryId}`).addClass('selected');
            sessionStorage.setItem('selectedFerryId', ferryId);
            
            // Show loading state
            $('#ferry-info-container').html('<div style="text-align: center; padding: 20px;">Loading ferry details...</div>');
            
            // Fetch ferry details
            $.ajax({
                url: 'getFerryDetails.php', // You'll need to create this file
                method: 'GET',
                data: { ferry_id: ferryId },
                dataType: 'json',
                success: function(ferryData) {
                    // Now fetch maintenance history
                    $.ajax({
                        url: 'getFerryMaintenance.php', // You'll need to create this file
                        method: 'GET',
                        data: { ferry_id: ferryId },
                        dataType: 'json',
                        success: function(maintenanceData) {
                            // Then fetch repair history
                            $.ajax({
                                url: 'getFerryRepairs.php', // You'll need to create this file
                                method: 'GET',
                                data: { ferry_id: ferryId },
                                dataType: 'json',
                                success: function(repairData) {
                                    // Now create the full ferry info display with all the data
                                    createFerryInfoDisplay(ferryData, maintenanceData, repairData);
                                },
                                error: function() {
                                    // Still display ferry info even if repair data fails
                                    createFerryInfoDisplay(ferryData, maintenanceData, []);
                                }
                            });
                        },
                        error: function() {
                            // Still display ferry info even if maintenance data fails
                            createFerryInfoDisplay(ferryData, [], []);
                        }
                    });
                },
                error: function() {
                    $('#ferry-info-container').html('<div class="no-ferry-selected"><p>Error loading ferry details. Please try again.</p></div>');
                }
            });
        }
        
        // Function to create the ferry info display
        function createFerryInfoDisplay(ferryData, maintenanceData, repairData) {
            const regDate = ferryData.registration_date ? new Date(ferryData.registration_date).toLocaleDateString() : 'N/A';
            const lastInspection = ferryData.last_inspection_date ? new Date(ferryData.last_inspection_date).toLocaleDateString() : 'N/A';
            
            let maintenanceHtml = '';
            if (maintenanceData.length > 0) {
                maintenanceData.forEach(item => {
                    const maintDate = new Date(item.maintenance_date).toLocaleDateString();
                    const nextDueDate = item.next_due_date ? new Date(item.next_due_date).toLocaleDateString() : 'N/A';
                    maintenanceHtml += `
                        <div class="history-item">
                            <strong>${item.maintenance_type}</strong> on ${maintDate}<br>
                            <small>Performed by: ${item.performed_by || 'N/A'}</small><br>
                            <small>Next due: ${nextDueDate}</small>
                            ${item.notes ? '<br><small>Notes: ' + item.notes + '</small>' : ''}
                        </div>
                    `;
                });
            } else {
                maintenanceHtml = '<p>No maintenance records found.</p>';
            }
            
            let repairHtml = '';
            if (repairData.length > 0) {
                repairData.forEach(item => {
                    const reportedDate = new Date(item.reported_at).toLocaleDateString();
                    const repairDate = item.repair_date ? new Date(item.repair_date).toLocaleDateString() : 'Pending';
                    repairHtml += `
                        <div class="history-item">
                            <strong>${item.issue}</strong> (${item.status})<br>
                            <small>Reported: ${reportedDate}, Repaired: ${repairDate}</small><br>
                            ${item.repair_action ? '<small>Action: ' + item.repair_action + '</small><br>' : ''}
                            ${item.cost ? '<small>Cost: $' + item.cost + '</small>' : ''}
                        </div>
                    `;
                });
            } else {
                repairHtml = '<p>No repair records found.</p>';
            }
            
            const ferryInfoHtml = `
                <div class="ferry-license">
                    <div class="watermark">PRFS</div>
                    <div class="ferry-license-header">
                        <h3>${ferryData.name} - Official Ferry Information</h3>
                    </div>
                    
                    <div class="license-section">
                        <span class="license-section-title">Basic Information</span>
                        <div class="license-info">
                            <span class="info-label">Ferry ID:</span>
                            <span>${ferryData.id}</span>
                        </div>
                        <div class="license-info">
                            <span class="info-label">Name:</span>
                            <span>${ferryData.name}</span>
                        </div>
                        <div class="license-info">
                            <span class="info-label">Operator:</span>
                            <span>${ferryData.operator}</span>
                        </div>
                        <div class="license-info">
                            <span class="info-label">Status:</span>
                            <span>${ferryData.status}</span>
                        </div>
                    </div>
                    
                    <div class="license-section">
                        <span class="license-section-title">Capacity & Usage</span>
                        <div class="license-info">
                            <span class="info-label">Max Capacity:</span>
                            <span>${ferryData.max_capacity} passengers</span>
                        </div>
                        <div class="license-info">
                            <span class="info-label">Current Capacity:</span>
                            <span>${ferryData.current_capacity} passengers</span>
                        </div>
                        <div class="license-info">
                            <span class="info-label">Active Time:</span>
                            <span>${ferryData.active_time} minutes</span>
                        </div>
                    </div>
                    
                    <div class="license-section">
                        <span class="license-section-title">Registration Information</span>
                        <div class="license-info">
                            <span class="info-label">Registration Date:</span>
                            <span>${regDate}</span>
                        </div>
                        <div class="license-info">
                            <span class="info-label">Last Inspection:</span>
                            <span>${lastInspection}</span>
                        </div>
                    </div>
                    
                    <div class="license-section">
                        <span class="license-section-title">Maintenance History</span>
                        <div class="maintenance-history">
                            ${maintenanceHtml}
                        </div>
                    </div>
                    
                    <div class="license-section">
                        <span class="license-section-title">Repair History</span>
                        <div class="repair-history">
                            ${repairHtml}
                        </div>
                    </div>
                </div>
            `;
            
            $('#ferry-info-container').html(ferryInfoHtml);
        }
        
        // Initial fetch
        $(document).ready(function() {
            fetchFerryData();
            
            // Ferry card click handler (using delegation for dynamically created elements)
            $(document).on('click', '.ferry-card', function(e) {
                // Only process clicks on the card itself, not on the switch
                if (!$(e.target).hasClass('status-switch') && 
                    !$(e.target).hasClass('slider')) {
                    const ferryId = $(this).data('ferry-id');
                    getFerryDetails(ferryId);
                }
            });
            
            // Restore previously selected ferry if any
            const selectedFerryId = sessionStorage.getItem('selectedFerryId');
            if (selectedFerryId) {
                setTimeout(() => {
                    getFerryDetails(selectedFerryId);
                }, 500); // Short delay to let the ferry list load first
            }
        });

        // Function to update ferry status via AJAX
        $(document).on('change', '.status-switch', function(e) {
            // Stop the event from bubbling up to the ferry card
            e.stopPropagation();
            
            var ferryId = $(this).data('ferry-id');
            var newStatus = $(this).prop('checked') ? 'active' : 'inactive';

            $.ajax({
                url: 'updateFerryStatus.php', // File to update ferry status
                method: 'POST',
                data: { ferry_id: ferryId, status: newStatus },
                success: function(response) {
                    fetchActiveTime(ferryId); // Refresh active time after updating status
                    
                    // If this ferry is currently selected, refresh its details
                    if ($(`#ferry-row-${ferryId}`).hasClass('selected')) {
                        getFerryDetails(ferryId);
                    }
                },
                error: function() {
                    alert('Error updating ferry status');
                }
            });
        });

        // Function to fetch active time for each ferry
        function fetchActiveTime(ferryId) {
            $.ajax({
                url: 'getActiveTime.php', // File to fetch active time
                method: 'GET',
                data: { ferry_id: ferryId },
                dataType: 'json',
                success: function(data) {
                    // Update the active time in the UI dynamically
                    $('#active-time-' + ferryId).text(data.active_time);
                },
                error: function() {
                    console.log('Error fetching active time for ferry ' + ferryId);
                }
            });
        }

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
        
        // Toggle form visibility
        document.getElementById("open-registration-form").addEventListener("click", function() {
            document.getElementById("registration-form").style.display = "block";
        });

        document.getElementById("close-registration-form").addEventListener("click", function() {
            document.getElementById("registration-form").style.display = "none";
        });

        // Close the modal when the user clicks anywhere outside of it
        window.onclick = function(event) {
            if (event.target == document.getElementById("registration-form")) {
                document.getElementById("registration-form").style.display = "none";
            }
        };
        
    </script>
</body>
</html>