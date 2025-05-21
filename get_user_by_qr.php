<?php
// get_user_by_qr.php - The main API endpoint for the ticket system
// This file processes requests from the frontend and calls the appropriate functions

// Include database connection
require_once 'db_connect.php';

// Process QR code and get user data
function getUserByQRCode($qrCodeData) {
    global $conn;
    
    try {
        // Trim whitespace
        $qrCodeData = trim($qrCodeData);
        
        // Store original QR data for debugging
        $originalQrData = $qrCodeData;
        
        // Debug array to track processing steps
        $debugInfo = [];
        $debugInfo['original_qr'] = $originalQrData;
        
        // Check if the QR code data is in JSON format
        $decodedData = json_decode($qrCodeData, true);
        
        // If it's valid JSON and contains qr_code_data field, extract it
        if (json_last_error() === JSON_ERROR_NONE && isset($decodedData['qr_code_data'])) {
            $qrCodeData = $decodedData['qr_code_data'];
            $debugInfo['format_detected'] = 'json';
            $debugInfo['extracted_qr'] = $qrCodeData;
        }
        
        // Clean up any potential formatting issues
        $qrCodeData = trim($qrCodeData);
        
        // Try searching with both the original and processed QR codes
        $stmt = $conn->prepare("
            SELECT id, full_name, email, phone_number, expires_at, is_active
            FROM users 
            WHERE (qr_code_data = ? OR qr_code_data = ?) AND is_active = 1
        ");
        
        $stmt->bind_param("ss", $qrCodeData, $originalQrData);
        $stmt->execute();
        
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            // No user found with this QR code
            return [
                'success' => false,
                'message' => 'Invalid QR code or user not found',
                'debug' => $debugInfo
            ];
        }
        
        $user = $result->fetch_assoc();
        
        // Check if ID has expired
        $currentDate = new DateTime();
        $expiryDate = new DateTime($user['expires_at']);
        
        if ($currentDate > $expiryDate) {
            return [
                'success' => false,
                'message' => 'User ID has expired',
                'user' => $user
            ];
        }
        
        return [
            'success' => true,
            'user' => $user
        ];
        
    } catch (Exception $e) {
        return [
            'success' => false,
            'message' => 'Database error: ' . $e->getMessage(),
            'debug' => $debugInfo ?? []
        ];
    }
}

// Get all active ferries
function getActiveFerries() {
    global $conn;
    
    try {
        $stmt = $conn->prepare("
            SELECT id, name, ferry_code
            FROM ferries
            WHERE status = 'active'
            ORDER BY name ASC
        ");
        
        $stmt->execute();
        $result = $stmt->get_result();
        
        $ferries = [];
        while ($row = $result->fetch_assoc()) {
            $ferries[] = $row;
        }
        
        return [
            'success' => true,
            'ferries' => $ferries
        ];
        
    } catch (Exception $e) {
        return [
            'success' => false,
            'message' => 'Database error: ' . $e->getMessage()
        ];
    }
}

// Get all ferry stations
function getAllFerryStations() {
    global $conn;
    
    try {
        $stmt = $conn->prepare("
            SELECT id, station_name
            FROM ferry_stations
            ORDER BY station_name ASC
        ");
        
        $stmt->execute();
        $result = $stmt->get_result();
        
        $stations = [];
        while ($row = $result->fetch_assoc()) {
            $stations[] = $row;
        }
        
        return [
            'success' => true,
            'stations' => $stations
        ];
        
    } catch (Exception $e) {
        return [
            'success' => false,
            'message' => 'Database error: ' . $e->getMessage()
        ];
    }
}

// Get ferry routes
function getFerryRoutes() {
    global $conn;
    
    try {
        // First check if ferry_stations table exists
        $tableCheckStmt = $conn->prepare("SHOW TABLES LIKE 'ferry_stations'");
        $tableCheckStmt->execute();
        $tableExists = $tableCheckStmt->get_result()->num_rows > 0;
        
        if (!$tableExists) {
            // Fall back to original implementation if the new table doesn't exist
            $stmt = $conn->prepare("
                SELECT id, route_name, origin, destination
                FROM ferry_routes
                ORDER BY route_name ASC
            ");
            
            $stmt->execute();
            $result = $stmt->get_result();
            
            $routes = [];
            while ($row = $result->fetch_assoc()) {
                $routes[] = $row;
            }
            
            return [
                'success' => true,
                'routes' => $routes
            ];
        }
        
        // Both tables exist with expected structure
        $routesStmt = $conn->prepare("
            SELECT 
                fr.id, 
                fr.route_name, 
                origin.station_name as origin_name, 
                destination.station_name as destination_name,
                fr.origin as origin_id,
                fr.destination as destination_id
            FROM ferry_routes fr
            JOIN ferry_stations origin ON fr.origin = origin.id
            JOIN ferry_stations destination ON fr.destination = destination.id
            ORDER BY fr.route_name ASC
        ");
        
        $routesStmt->execute();
        $routesResult = $routesStmt->get_result();
        
        if ($routesResult === false) {
            // Query failed, check for errors
            return [
                'success' => false,
                'message' => 'Database query error: ' . $conn->error
            ];
        }
        
        $routes = [];
        while ($row = $routesResult->fetch_assoc()) {
            // Create a properly formatted route object for the frontend
            $routes[] = [
                'id' => $row['id'],
                'name' => $row['route_name'],
                'from' => $row['origin_name'],
                'to' => $row['destination_name'],
                'origin_id' => $row['origin_id'],
                'destination_id' => $row['destination_id']
            ];
        }
        
        return [
            'success' => true,
            'routes' => $routes
        ];
        
    } catch (Exception $e) {
        return [
            'success' => false,
            'message' => 'Database error: ' . $e->getMessage()
        ];
    }
}

// Save ticket
function saveTicket($ticketData) {
    global $conn;
    
    try {
        // Begin transaction
        $conn->begin_transaction();
        
        // Debug log
        error_log("Starting ticket save process with data: " . json_encode($ticketData));
        
        // Validate required fields
        if (!isset($ticketData['user_id']) || !isset($ticketData['ferry_id']) || 
            !isset($ticketData['ticket_type']) || !isset($ticketData['amount']) || 
            !isset($ticketData['valid_until'])) {
            throw new Exception("Missing required ticket data fields");
        }
        
        // Convert to proper types
        $userId = (int)$ticketData['user_id'];
        $ferryId = (int)$ticketData['ferry_id'];
        $ticketType = $ticketData['ticket_type'];
        $amount = (float)$ticketData['amount'];
        $validUntil = $ticketData['valid_until'];
        $originId = (int)$ticketData['origin_id'];
        $destinationId = (int)$ticketData['destination_id'];
        
        // Insert ticket record
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
        
        error_log("Prepared statement for ticket insert");
        
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
            throw new Exception("Failed to execute ticket insert: " . $stmt->error);
        }
        
        // Get the inserted ticket ID
        $ticketId = $conn->insert_id;
        
        error_log("Ticket inserted with ID: " . $ticketId);
        
        // Commit transaction
        $conn->commit();
        
        // Get ticket details for receipt
        $ticketDetails = getTicketDetails($ticketId, $ticketData['route_id']);
        
        return [
            'success' => true,
            'ticket_id' => $ticketId,
            'ticket_details' => $ticketDetails
        ];
        
    } catch (Exception $e) {
        // Roll back transaction on error
        $conn->rollback();
        
        error_log("Error in saveTicket: " . $e->getMessage());
        
        return [
            'success' => false,
            'message' => 'Database error: ' . $e->getMessage()
        ];
    }
}

// Updated getTicketDetails function with more robust error handling
function getTicketDetails($ticketId, $routeId) {
    global $conn;
    
    try {
        error_log("Getting ticket details for ticket ID: $ticketId and route ID: $routeId");
        
        $stmt = $conn->prepare("
            SELECT 
                t.id, 
                t.ticket_type, 
                t.amount, 
                t.purchase_date, 
                t.valid_until,
                u.full_name,
                u.email,
                u.phone_number,
                f.name as ferry_name,
                f.ferry_code,
                fr.route_name,
                origin.station_name as origin_name,
                destination.station_name as destination_name
            FROM tickets t
            JOIN users u ON t.user_id = u.id
            JOIN ferries f ON t.ferry_id = f.id
            JOIN ferry_routes fr ON fr.id = ?
            JOIN ferry_stations origin ON t.origin_station_id = origin.id
            JOIN ferry_stations destination ON t.destination_station_id = destination.id
            WHERE t.id = ?
        ");
        
        $stmt->bind_param("ii", $routeId, $ticketId);
        $result = $stmt->execute();
        
        if (!$result) {
            throw new Exception("Failed to execute query: " . $stmt->error);
        }
        
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            error_log("No ticket details found for ID: $ticketId");
            return null;
        }
        
        $details = $result->fetch_assoc();
        error_log("Retrieved ticket details: " . json_encode($details));
        
        return $details;
        
    } catch (Exception $e) {
        error_log("Error in getTicketDetails: " . $e->getMessage());
        return null;
    }
}
// Main request handler
header('Content-Type: application/json');

// For debugging
$debug_mode = isset($_GET['debug']) && $_GET['debug'] == 1;

// Get the request type
$action = $_GET['action'] ?? '';

switch ($action) {
    case 'get_user':
        $qrCode = $_GET['qr_code'] ?? '';
        $response = getUserByQRCode($qrCode);
        
        // In production, remove debug info unless debug mode is enabled
        if (!$debug_mode && isset($response['debug'])) {
            unset($response['debug']);
        }
        
        echo json_encode($response);
        break;

    case 'get_stations':
        $response = getAllFerryStations();
        echo json_encode($response);
        break;
        
    case 'get_ferries':
        $response = getActiveFerries();
        echo json_encode($response);
        break;
        
    case 'get_routes':
        $response = getFerryRoutes();
        echo json_encode($response);
        break;
        
   case 'save_ticket':
    // Enable detailed error reporting for debugging
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
    
    // Log the incoming data
    error_log("Received save_ticket request: " . json_encode($_POST));
    
    // Validate inputs with detailed error messages
    $missingFields = [];
    if (!isset($_POST['userId'])) $missingFields[] = 'userId';
    if (!isset($_POST['ferryId'])) $missingFields[] = 'ferryId';
    if (!isset($_POST['ticketType'])) $missingFields[] = 'ticketType';
    if (!isset($_POST['amount'])) $missingFields[] = 'amount';
    if (!isset($_POST['validUntil'])) $missingFields[] = 'validUntil';
    if (!isset($_POST['route_id'])) $missingFields[] = 'route_id';
    if (!isset($_POST['origin_id'])) $missingFields[] = 'origin_id';
    if (!isset($_POST['destination_id'])) $missingFields[] = 'destination_id';
    
    if (!empty($missingFields)) {
        echo json_encode([
            'success' => false,
            'message' => 'Missing required fields: ' . implode(', ', $missingFields)
        ]);
        break;
    }
    
    $ticketData = [
        'user_id' => (int)$_POST['userId'],
        'ferry_id' => (int)$_POST['ferryId'],
        'ticket_type' => $_POST['ticketType'],
        'amount' => (float)$_POST['amount'],
        'valid_until' => $_POST['validUntil'],
        'route_id' => (int)$_POST['route_id'],
        'origin_id' => (int)$_POST['origin_id'],
        'destination_id' => (int)$_POST['destination_id']
    ];
    
    error_log("Ticket data prepared: " . json_encode($ticketData));
    
    $response = saveTicket($ticketData);
    echo json_encode($response);
    break;
    default:
        echo json_encode([
            'success' => false,
            'message' => 'Invalid action specified'
        ]);
}
