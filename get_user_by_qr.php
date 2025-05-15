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
//this part of the code was changed to use the ferry_stations table to get 
// Fixed version with proper SQL syntax
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
        
        // Get all stations
        $stationsStmt = $conn->prepare("
            SELECT id, station_name
            FROM ferry_stations
            ORDER BY station_name ASC
        ");
        
        $stationsStmt->execute();
        $stationsResult = $stationsStmt->get_result();
        
        $stations = [];
        while ($row = $stationsResult->fetch_assoc()) {
            $stations[] = $row;
        }
        
        return [
            'success' => true,
            'routes' => $routes,
            'stations' => $stations
        ];
        
    } catch (Exception $e) {
        return [
            'success' => false,
            'message' => 'Database error: ' . $e->getMessage()
        ];
    }
}

//adjust this part of the code to match the new ferry_stations table
// Save ticket
function saveTicket($ticketData) {
    global $conn;
    
    try {
        // Begin transaction
        $conn->begin_transaction();
        
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
        
        $stmt->bind_param(
            "iisdssii", 
            $ticketData['user_id'],
            $ticketData['ferry_id'],
            $ticketData['ticket_type'],
            $ticketData['amount'],
            $ticketData['valid_until'],
            $ticketData['origin_id'],
            $ticketData['destination_id']
        );
        
        $stmt->execute();
        
        // Get the inserted ticket ID
        $ticketId = $conn->insert_id;
        
        // If a ferry is full, you might check capacity here
        // And update the current_capacity of the ferry
        
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
        
        return [
            'success' => false,
            'message' => 'Database error: ' . $e->getMessage()
        ];
    }
}

// Get ticket details for receipt
function getTicketDetails($ticketId, $routeId) {
    global $conn;
    
    try {
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
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            return null;
        }
        
        return $result->fetch_assoc();
        
    } catch (Exception $e) {
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
        
    case 'get_ferries':
        $response = getActiveFerries();
        echo json_encode($response);
        break;
        
    case 'get_routes':
        $response = getFerryRoutes();
        echo json_encode($response);
        break;
        
        case 'save_ticket':
            // Validate inputs
            if (
                !isset($_POST['userId']) || 
                !isset($_POST['ferryId']) ||
                !isset($_POST['ticketType']) ||
                !isset($_POST['amount']) ||
                !isset($_POST['validUntil']) ||
                !isset($_POST['route_id']) ||
                !isset($_POST['origin_id']) ||
                !isset($_POST['destination_id'])
            ) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Missing required fields'
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
            
            $response = saveTicket($ticketData);
            echo json_encode($response);
            break;
    default:
        echo json_encode([
            'success' => false,
            'message' => 'Invalid action specified'
        ]);
}