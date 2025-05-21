
<?php
// Include database connection file
require_once 'db_connect.php';

// Function to get active announcements
function getActiveAnnouncements() {
    global $conn;
    
    $sql = "SELECT * FROM `announcements` 
            WHERE CURDATE() BETWEEN `display_from` AND DATE_ADD(`display_from`, INTERVAL `display_duration` DAY) 
            ORDER BY `created_at` DESC 
            LIMIT 3";
    
    $result = $conn->query($sql);
    
    $announcements = array();
    
    if ($result && $result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $announcements[] = $row;
        }
    }
    
    return $announcements;
}
?>