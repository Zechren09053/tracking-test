<?php
/**
 * Database connection test script
 * This file helps diagnose database connection issues
 * Save as db_test.php and upload to your server
 */

// Include database connection
require_once 'db_connect.php';

// Function to check database connection
function testDatabaseConnection() {
    global $conn;
    
    echo "<h2>Database Connection Test</h2>";
    
    if (!$conn) {
        echo "<p style='color: red;'>❌ Connection failed: " . mysqli_connect_error() . "</p>";
        return false;
    } else {
        echo "<p style='color: green;'>✅ Database connection successful!</p>";
        return true;
    }
}

// Function to test if tables exist
function testTables() {
    global $conn;
    
    echo "<h2>Database Table Test</h2>";
    
    $requiredTables = [
        'users', 
        'tickets', 
        'ferries', 
        'ferry_routes', 
        'ferry_stations'
    ];
    
    $missingTables = [];
    
    foreach ($requiredTables as $table) {
        $result = $conn->query("SHOW TABLES LIKE '$table'");
        if ($result->num_rows == 0) {
            $missingTables[] = $table;
        }
    }
    
    if (empty($missingTables)) {
        echo "<p style='color: green;'>✅ All required tables exist</p>";
    } else {
        echo "<p style='color: red;'>❌ Missing tables: " . implode(", ", $missingTables) . "</p>";
    }
    
    // Check table structures
    if (!in_array('tickets', $missingTables)) {
        echo "<h3>Tickets Table Structure</h3>";
        $result = $conn->query("DESCRIBE tickets");
        if ($result) {
            echo "<pre>";
            while ($row = $result->fetch_assoc()) {
                echo $row['Field'] . " - " . $row['Type'] . " - " . ($row['Null'] == 'YES' ? 'NULL' : 'NOT NULL') . "\n";
            }
            echo "</pre>";
        }
    }
}

// Function to test insertion into tickets table
function testTicketInsertion() {
    global $conn;
    
    echo "<h2>Test Ticket Insertion</h2>";
    
    // First check if we have a valid user and ferry
    $userResult = $conn->query("SELECT id FROM users LIMIT 1");
    $ferryResult = $conn->query("SELECT id FROM ferries LIMIT 1");
    $originResult = $conn->query("SELECT id FROM ferry_stations LIMIT 1");
    $destResult = $conn->query("SELECT id FROM ferry_stations ORDER BY id DESC LIMIT 1");
    
    if ($userResult->num_rows == 0) {
        echo "<p style='color: red;'>❌ No users found in database. Please add a user first.</p>";
        return;
    }
    
    if ($ferryResult->num_rows == 0) {
        echo "<p style='color: red;'>❌ No ferries found in database. Please add a ferry first.</p>";
        return;
    }
    
    if ($originResult->num_rows == 0 || $destResult->num_rows == 0) {
        echo "<p style='color: red;'>❌ No stations found in database. Please add stations first.</p>";
        return;
    }
    
    $userId = $userResult->fetch_assoc()['id'];
    $ferryId = $ferryResult->fetch_assoc()['id'];
    $originId = $originResult->fetch_assoc()['id'];
    $destinationId = $destResult->fetch_assoc()['id'];
    
    // Prepare test ticket data
    $ticketType = "Test Ticket";
    $amount = 100.00;
    $validUntil = date('Y-m-d H:i:s', strtotime('+24 hours'));
    
    try {
        // Begin transaction
        $conn->begin_transaction();
        
        // Insert test ticket
        $stmt = $conn->prepare("
            INSERT INTO tickets (
                user_id, 
                ferry_id, 
                ticket_type, 
                amount, 
                purchase_date,
                valid_until,
                origin_station_id,
                destination_station_id
            ) VALUES (?, ?, ?, ?, NOW(), ?, ?, ?)
        ");
        
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        
        $stmt->bind_param(
            "iisdsis", 
            $userId,
            $ferryId,
            $ticketType,
            $amount,
            $validUntil,
            $originId,
            $destinationId
        );
        
        $result = $stmt->execute();
        
        if (!$result) {
            throw new Exception("Execute failed: " . $stmt->error);
        }
        
        $ticketId = $conn->insert_id;
        
        echo "<p style='color: green;'>✅ Test ticket inserted successfully! Ticket ID: $ticketId</p>";
        
        // Rollback the test ticket - we don't want to actually add it to the database
        $conn->rollback();
        echo "<p>Test transaction rolled back (no actual data was inserted)</p>";
        
    } catch (Exception $e) {
        $conn->rollback();
        echo "<p style='color: red;'>❌ Error inserting test ticket: " . $e->getMessage() . "</p>";
    }
}

// Function to check database error log
function checkErrorLog() {
    echo "<h2>Recent MySQL Errors</h2>";
    
    // This might work on some servers depending on configuration
    $logFile = '/var/log/mysql/error.log'; // common location
    
    if (file_exists($logFile) && is_readable($logFile)) {
        $logContent = shell_exec("tail -n 20 $logFile");
        echo "<pre>$logContent</pre>";
    } else {
        echo "<p>Cannot access MySQL error log directly.</p>";
    }
    
    echo "<p>Check your server's error logs for more information.</p>";
}

// Run tests
echo "<html><head><title>Ferry Ticket System Database Test</title>";
echo "<style>body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }";
echo "h1, h2 { color: #333; } pre { background: #f4f4f4; padding: 10px; border-radius: 4px; }</style>";
echo "</head><body>";
echo "<h1>Ferry Ticket System Database Test</h1>";

$connectionSuccess = testDatabaseConnection();

if ($connectionSuccess) {
    testTables();
    testTicketInsertion();
    checkErrorLog();
}

echo "<h2>PHP Configuration</h2>";
echo "<p>PHP Version: " . phpversion() . "</p>";
echo "<p>Loaded Extensions: " . implode(", ", get_loaded_extensions()) . "</p>";

echo "</body></html>";
?>