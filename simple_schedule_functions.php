<?php
// Function to get schedule data in a more usable format
function getScheduleData($scheduleType) {
    global $conn;
    $table = ($scheduleType === 'downstream') ? 'downstream_schedules' : 'upstream_schedules';
    
    // First, get all distinct stations ordered by column ID
    $stationsSql = "SELECT DISTINCT station_name, col_id FROM {$table} ORDER BY col_id";
    $stationsResult = $conn->query($stationsSql);
    
    $stations = [];
    if ($stationsResult && $stationsResult->num_rows > 0) {
        while($row = $stationsResult->fetch_assoc()) {
            $stations[$row['col_id']] = $row['station_name'];
        }
    }
    
    // Next, get all data grouped by row (trip) and column (station)
    $dataSql = "SELECT row_id, col_id, schedule_time FROM {$table} ORDER BY row_id, col_id";
    $dataResult = $conn->query($dataSql);
    
    $scheduleData = [];
    if ($dataResult && $dataResult->num_rows > 0) {
        while($row = $dataResult->fetch_assoc()) {
            // Format the time
            $formattedTime = date('h:i A', strtotime($row['schedule_time']));
            $scheduleData[$row['row_id']][$row['col_id']] = $formattedTime;
        }
    }
    
    return [
        'stations' => $stations,
        'data' => $scheduleData
    ];
}

// Connect to the database
require_once 'db_connect.php';

// Get schedule data
$upstreamData = getScheduleData('upstream');
$downstreamData = getScheduleData('downstream');

// Get the maximum number of trips for both directions
$maxUpstreamTrips = !empty($upstreamData['data']) ? max(array_keys($upstreamData['data'])) : 0;
$maxDownstreamTrips = !empty($downstreamData['data']) ? max(array_keys($downstreamData['data'])) : 0;
?>