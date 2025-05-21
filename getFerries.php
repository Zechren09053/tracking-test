<?php
// Set the content type to JSON
header('Content-Type: application/json');

// Database credentials
$servername = "localhost";
$username = "PRFS"; // Default username for XAMPP
$password = "1111"; // Default password for XAMPP
$dbname = "prfs"; // The name of your database

// Create connection to the MySQL database
$conn = new mysqli($servername, $username, $password, $dbname);

// Check if the connection is successful
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// First, let's check if the status_history table exists
$tableCheckSql = "SHOW TABLES LIKE 'status_history'";
$tableCheckResult = $conn->query($tableCheckSql);
$statusHistoryExists = ($tableCheckResult && $tableCheckResult->num_rows > 0);

$statusHistory = [];
// If the status_history table exists, get the status history to detect status changes
if ($statusHistoryExists) {
    $statusHistorySql = "SELECT ferry_id, status, timestamp FROM status_history ORDER BY ferry_id, timestamp DESC";
    $statusHistoryResult = $conn->query($statusHistorySql);
    
    if ($statusHistoryResult && $statusHistoryResult->num_rows > 0) {
        while ($historyRow = $statusHistoryResult->fetch_assoc()) {
            $ferryId = $historyRow['ferry_id'];
            if (!isset($statusHistory[$ferryId])) {
                $statusHistory[$ferryId] = [];
            }
            $statusHistory[$ferryId][] = $historyRow;
        }
    }
}

// SQL query to fetch ferry data from the ferries table
$sql = "SELECT id, name, operator, status, active_time, latitude, longitude, last_updated, max_capacity, current_capacity FROM ferries";
$result = $conn->query($sql);

// Initialize an empty array to hold the ferry data
$ferries = [];

// Check if there are rows returned from the query
if ($result->num_rows > 0) {
    // Fetch each row and add it to the $ferries array
    while ($row = $result->fetch_assoc()) {
        $ferryId = $row['id'];
        $currentStatus = $row['status'];
        $currentTime = new DateTime();
        $lastUpdatedTime = new DateTime($row['last_updated']);
        
        // Let's store the current database value
        $storedActiveTime = intval($row['active_time']);
        
        // Ensure storedActiveTime is not negative
        if ($storedActiveTime < 0) {
            $storedActiveTime = 0;
            // Fix negative values in the database
            $fixNegativeSql = "UPDATE ferries SET active_time = 0 WHERE id = ? AND active_time < 0";
            $stmt = $conn->prepare($fixNegativeSql);
            $stmt->bind_param("i", $ferryId);
            $stmt->execute();
            $stmt->close();
        }
        
        // Check if status has changed since last update
        $statusChanged = false;
        $justBecameActive = false;
        
        // If status_history table exists and has data, check for status changes
        if ($statusHistoryExists && isset($statusHistory[$ferryId]) && count($statusHistory[$ferryId]) > 1) {
            $lastStatus = $statusHistory[$ferryId][0]['status'];
            $previousStatus = $statusHistory[$ferryId][1]['status'];
            if ($lastStatus != $previousStatus) {
                $statusChanged = true;
                if ($lastStatus == 'active' && $previousStatus != 'active') {
                    $justBecameActive = true;
                }
            }
        } else {
            // Without status history, we can detect status changes based on timestamps
            // If last_updated is very recent (within last minute) and status is active,
            // it might indicate a recent activation
            $timeSinceLastUpdate = $currentTime->getTimestamp() - $lastUpdatedTime->getTimestamp();
            if ($currentStatus == 'active' && $timeSinceLastUpdate < 60) {
                // This is potentially a fresh activation - check if active_time is 0 or very low
                if ($storedActiveTime == 0) {
                    $justBecameActive = true;
                }
            }
        }
        
        // Calculate active time in minutes if the ferry is active
        if ($currentStatus == 'active') {
            if ($justBecameActive) {
                // If ferry just became active, don't add any time yet
                // but do update the last_updated timestamp to start fresh
                $updateSql = "UPDATE ferries SET last_updated = NOW() WHERE id = ?";
                $stmt = $conn->prepare($updateSql);
                $stmt->bind_param("i", $ferryId);
                $stmt->execute();
                $stmt->close();
                
                // Use the stored active_time value without changes
                $row['active_time'] = $storedActiveTime;
            } else {
                // Calculate time difference in minutes
                $timeDiffInMinutes = floor(($currentTime->getTimestamp() - $lastUpdatedTime->getTimestamp()) / 60);
                
                // Safeguard against negative time differences (system clock issues or other anomalies)
                if ($timeDiffInMinutes < 0) {
                    $timeDiffInMinutes = 0;
                    // Update last_updated to current time to prevent future negative calculations
                    $updateSql = "UPDATE ferries SET last_updated = NOW() WHERE id = ?";
                    $stmt = $conn->prepare($updateSql);
                    $stmt->bind_param("i", $ferryId);
                    $stmt->execute();
                    $stmt->close();
                }
                
                // Only update if there's an actual time difference (avoid unnecessary updates)
                if ($timeDiffInMinutes > 0) {
                    // Add the difference to the existing active_time value
                    $newActiveTime = $storedActiveTime + $timeDiffInMinutes;
                    
                    // Ensure active_time is never negative
                    if ($newActiveTime < 0) {
                        $newActiveTime = 0;
                    }
                    
                    $row['active_time'] = $newActiveTime;
                    
                    // Update the active_time and last_updated in the database
                    $updateSql = "UPDATE ferries SET active_time = ?, last_updated = NOW() WHERE id = ?";
                    $stmt = $conn->prepare($updateSql);
                    $stmt->bind_param("ii", $newActiveTime, $ferryId);
                    $stmt->execute();
                    $stmt->close();
                } else {
                    // No time difference, keep the stored value
                    $row['active_time'] = $storedActiveTime;
                }
            }
            
            // Update last_updated for the response
            $row['last_updated'] = $currentTime->format('Y-m-d H:i:s');
        } else if ($statusChanged && $currentStatus != 'active') {
            // Ferry just became inactive - update the last_updated time
            // but keep the accumulated active_time
            $updateSql = "UPDATE ferries SET last_updated = NOW() WHERE id = ?";
            $stmt = $conn->prepare($updateSql);
            $stmt->bind_param("i", $ferryId);
            $stmt->execute();
            $stmt->close();
            
            // Update last_updated for the response
            $row['last_updated'] = $currentTime->format('Y-m-d H:i:s');
            // Keep existing active_time
            $row['active_time'] = $storedActiveTime;
        }

        // Add the ferry data to the ferries array
        $ferries[] = $row;
    }
}

// Close the database connection
$conn->close();

// Return the ferry data as JSON
echo json_encode($ferries);
?>