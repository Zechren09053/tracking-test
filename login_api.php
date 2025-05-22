<?php
header('Content-Type: application/json');
include 'db_connect.php';

$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';
$type = $_POST['type'] ?? ''; // 'staff' or 'user'

if (empty($email) || empty($password) || empty($type)) {
    echo json_encode(['status' => 'error', 'message' => 'Missing required fields']);
    exit;
}

$table = '';
$id_column = '';
$select_sql = '';
$update_sql = '';

if ($type === 'staff') {
    $table = 'staff_users';
    $id_column = 'staff_id';
    $select_sql = "SELECT * FROM $table WHERE email = ?";
} elseif ($type === 'user') {
    $table = 'users';
    $id_column = 'id';
    $select_sql = "SELECT * FROM $table WHERE email = ?";
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid user type']);
    exit;
}

// Prepare & execute SELECT
$stmt = mysqli_prepare($conn, $select_sql);
mysqli_stmt_bind_param($stmt, 's', $email);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if ($user = mysqli_fetch_assoc($result)) {
    if (password_verify($password, $user['password'])) {
        // Auth successful — update login count & timestamp
        $update_sql = "UPDATE $table SET login_count = login_count + 1, last_login = NOW() WHERE $id_column = ?";
        $update_stmt = mysqli_prepare($conn, $update_sql);
        mysqli_stmt_bind_param($update_stmt, 'i', $user[$id_column]);
        mysqli_stmt_execute($update_stmt);

        echo json_encode([
            'status' => 'success',
            'message' => 'Login successful',
            'user' => [
                'id' => $user[$id_column],
                'name' => $type === 'staff' ? $user['first_name'] . ' ' . $user['last_name'] : $user['full_name'],
                'email' => $user['email'],
                'role' => $user['role'] ?? 'user',
                'login_count' => $user['login_count'] + 1,
                'last_login' => date('Y-m-d H:i:s')
            ]
        ]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid password']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'User not found']);
}
?>
