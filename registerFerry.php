<?php
session_start();
require 'db_connect.php'; // Include your DB connection

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Prepare variables and sanitize inputs
    $ferry_name = trim(htmlspecialchars($_POST['ferry_name']));
    $ferry_code = trim(htmlspecialchars($_POST['ferry_code']));
    $ferry_operator = trim(htmlspecialchars($_POST['ferry_operator']));
    $ferry_type = $_POST['ferry_type'];
    $max_capacity = (int)$_POST['ferry_max_capacity'];
    $cargo_capacity = isset($_POST['ferry_cargo_capacity']) ? (int)$_POST['ferry_cargo_capacity'] : 0;
    
    // Technical specifications
    $length = isset($_POST['ferry_length']) ? (float)$_POST['ferry_length'] : null;
    $width = isset($_POST['ferry_width']) ? (float)$_POST['ferry_width'] : null;
    $max_speed = isset($_POST['ferry_max_speed']) ? (float)$_POST['ferry_max_speed'] : null;
    $fuel_type = $_POST['fuel_type'];
    $engine_power = isset($_POST['engine_power']) ? (int)$_POST['engine_power'] : null;
    $engine_count = isset($_POST['engine_count']) ? (int)$_POST['engine_count'] : 1;
    
    // Manufacturer info
    $manufacturer = isset($_POST['manufacturer']) ? trim(htmlspecialchars($_POST['manufacturer'])) : null;
    $model = isset($_POST['model']) ? trim(htmlspecialchars($_POST['model'])) : null;
    $year_built = isset($_POST['year_built']) ? (int)$_POST['year_built'] : null;
    $hull_material = isset($_POST['hull_material']) ? $_POST['hull_material'] : null;
    
    // Registration info
    $registration_number = isset($_POST['registration_number']) ? trim(htmlspecialchars($_POST['registration_number'])) : null;
    $registration_date = !empty($_POST['registration_date']) ? $_POST['registration_date'] : null;
    $last_inspection_date = !empty($_POST['last_inspection_date']) ? $_POST['last_inspection_date'] : null;
    $next_inspection_date = !empty($_POST['next_inspection_date']) ? $_POST['next_inspection_date'] : null;
    
    // Additional info
    $notes = isset($_POST['notes']) ? trim(htmlspecialchars($_POST['notes'])) : null;
    
    // Default values
    $status = 'inactive';
    $latitude = null;
    $longitude = null;
    
    // Safety equipment
    $safety_equipment = isset($_POST['safety_equipment']) ? $_POST['safety_equipment'] : [];
    
    // Handle file uploads
    $image_path = null;
    $registration_document_path = null;
    
    // Process ferry image upload
    if (isset($_FILES['ferry_image']) && $_FILES['ferry_image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'uploads/ferry_images/';
        
        // Create directory if it doesn't exist
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        $file_name = time() . '_' . basename($_FILES['ferry_image']['name']);
        $target_file = $upload_dir . $file_name;
        
        // Check if image file is a actual image
        $check = getimagesize($_FILES['ferry_image']['tmp_name']);
        if ($check !== false) {
            // Limit to 5MB
            if ($_FILES['ferry_image']['size'] < 5000000) {
                if (move_uploaded_file($_FILES['ferry_image']['tmp_name'], $target_file)) {
                    $image_path = $target_file;
                }
            }
        }
    }
    
    // Process registration documents upload
    if (isset($_FILES['registration_documents']) && $_FILES['registration_documents']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'uploads/ferry_documents/';
        
        // Create directory if it doesn't exist
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        $file_name = time() . '_' . basename($_FILES['registration_documents']['name']);
        $target_file = $upload_dir . $file_name;
        
        // Limit to 10MB
        if ($_FILES['registration_documents']['size'] < 10000000) {
            $allowed_types = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
            if (in_array($_FILES['registration_documents']['type'], $allowed_types)) {
                if (move_uploaded_file($_FILES['registration_documents']['tmp_name'], $target_file)) {
                    $registration_document_path = $target_file;
                }
            }
        }
    }
    
    // Insert ferry information into the database
    $sql = "INSERT INTO ferries (
                name, ferry_code, operator, ferry_type, 
                max_capacity, cargo_capacity, status, 
                length, width, speed, max_speed, 
                fuel_type, engine_power, engine_count, 
                manufacturer, model, year_built, hull_material, 
                registration_number, registration_date, 
                last_inspection_date, next_inspection_date, 
                notes, image_path, registration_document_path,
                latitude, longitude, last_updated
            ) VALUES (
                ?, ?, ?, ?, 
                ?, ?, ?, 
                ?, ?, 0, ?, 
                ?, ?, ?, 
                ?, ?, ?, ?, 
                ?, ?, 
                ?, ?, 
                ?, ?, ?,
                ?, ?, NOW()
            )";
            
    $stmt = $conn->prepare($sql);
    $stmt->bind_param(
        "ssssiiiddsdiisissssssssdd",
        $ferry_name, $ferry_code, $ferry_operator, $ferry_type,
        $max_capacity, $cargo_capacity, $status,
        $length, $width, $max_speed,
        $fuel_type, $engine_power, $engine_count,
        $manufacturer, $model, $year_built, $hull_material,
        $registration_number, $registration_date,
        $last_inspection_date, $next_inspection_date,
        $notes, $image_path, $registration_document_path,
        $latitude, $longitude
    );
    
    if ($stmt->execute()) {
        $ferry_id = $stmt->insert_id;
        
        // Insert safety equipment
        if (!empty($safety_equipment)) {
            foreach ($safety_equipment as $equipment) {
                $sql = "INSERT INTO ferry_safety_equipment (ferry_id, equipment_type) VALUES (?, ?)";
                $equip_stmt = $conn->prepare($sql);
                $equip_stmt->bind_param("is", $ferry_id, $equipment);
                $equip_stmt->execute();
                $equip_stmt->close();
            }
        }
        
        // Set success message and redirect
        $_SESSION['success_message'] = "Ferry '{$ferry_name}' registered successfully!";
        header('Location: ferrymngt.php');
        exit();
    } else {
        $_SESSION['error_message'] = "Error registering ferry: " . $stmt->error;
        header('Location: ferrymngt.php');
        exit();
    }
    
    $stmt->close();
} else {
    // If not a POST request, redirect to management page
    header('Location: ferrymngt.php');
    exit();
}
?>
