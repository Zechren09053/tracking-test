<?php
// This file fetches users with pagination, search and filter functionality
header('Content-Type: application/json');
require_once 'db_connect.php'; // Use your database connection file

// Set default values
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
$search = isset($_GET['search']) ? $_GET['search'] : '';
$status = isset($_GET['status']) ? $_GET['status'] : 'all';

// Calculate offset
$offset = ($page - 1) * $limit;

// Build the query
$whereClause = [];
$params = [];
$types = '';

// Add search conditions if provided
if (!empty($search)) {
    $searchTerm = "%$search%";
    $whereClause[] = "(full_name LIKE ? OR email LIKE ? OR phone_number LIKE ?)";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $types .= 'sss';
}

// Add status filter if provided
if ($status !== 'all') {
    if ($status === 'active') {
        $whereClause[] = "(is_active = 1 AND expires_at > NOW())";
    } else if ($status === 'expired') {
        $whereClause[] = "(is_active = 0 OR expires_at <= NOW())";
    }
}

// Combine where clauses
$whereSQL = '';
if (!empty($whereClause)) {
    $whereSQL = 'WHERE ' . implode(' AND ', $whereClause);
}

// Count total records matching the criteria
$countQuery = "SELECT COUNT(*) as total FROM users $whereSQL";
$stmt = $conn->prepare($countQuery);

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();
$totalRows = $result->fetch_assoc()['total'];
$totalPages = ceil($totalRows / $limit);

// Get the user data with pagination
$query = "SELECT id, full_name, email, phone_number, birth_date, profile_image, issued_at, expires_at, is_active, last_used, qr_code_data 
          FROM users $whereSQL 
          ORDER BY full_name ASC 
          LIMIT ?, ?";

$stmt = $conn->prepare($query);

// Add limit parameters
$paramsCopy = $params;
$paramsCopy[] = $offset;
$paramsCopy[] = $limit;
$typesCopy = $types . 'ii';

$stmt->bind_param($typesCopy, ...$paramsCopy);
$stmt->execute();
$result = $stmt->get_result();

$users = [];
while ($row = $result->fetch_assoc()) {
    $users[] = $row;
}

// Return JSON response
echo json_encode([
    'status' => 'success',
    'data' => [
        'users' => $users,
        'total' => $totalRows,
        'page' => $page,
        'limit' => $limit,
        'pages' => $totalPages
    ]
]);

$stmt->close();
$conn->close();