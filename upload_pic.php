<?php
session_start();
$user_id = $_SESSION['staff_id']; // make sure user is logged in

$target_dir = "uploads/";
$target_file = $target_dir . basename($_FILES["profile_pic"]["name"]);

if (move_uploaded_file($_FILES["profile_pic"]["tmp_name"], $target_file)) {
    $conn = new mysqli("localhost", "PRFS", "1111", "prfs");
    $stmt = $conn->prepare("UPDATE staff_users SET profile_pic = ? WHERE staff_id = ?");
    $stmt->bind_param("si", $target_file, $user_id);
    $stmt->execute();
    echo "Profile picture updated!";
} else {
    echo "Upload failed 😭";
}
?>
<form action="upload_pic.php" method="POST" enctype="multipart/form-data">
    <input type="file" name="profile_pic" required>
    <input type="submit" value="Upload">
</form>
