<?php
session_start();

require 'db_connect.php'; // Assumes $conn is defined here and connected

$type = $_GET['type'] ?? '';

function escape($str) {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

switch ($type) {
    case 'ferry_logs':
        $query = "SELECT fl.id, f.name AS ferry_name, fl.trip_date, fl.passenger_count, fl.speed
                  FROM ferry_logs fl
                  JOIN ferries f ON fl.ferry_id = f.id
                  ORDER BY fl.trip_date DESC LIMIT 100";
        break;

    case 'tickets':
        $query = "SELECT t.id, u.full_name, f.name AS ferry_name, t.ticket_type, t.amount, t.purchase_date
                  FROM tickets t
                  JOIN users u ON t.user_id = u.id
                  JOIN ferries f ON t.ferry_id = f.id
                  ORDER BY t.purchase_date DESC LIMIT 100";
        break;

    case 'repair_logs':
        $query = "SELECT rl.id, f.name AS ferry_name, rl.reported_at, rl.issue, rl.status
                  FROM repair_logs rl
                  JOIN ferries f ON rl.ferry_id = f.id
                  ORDER BY rl.reported_at DESC LIMIT 100";
        break;

    case 'boat_maintenance':
        $query = "SELECT bm.id, f.name AS ferry_name, bm.maintenance_date, bm.maintenance_type, bm.next_due_date
                  FROM boat_maintenance bm
                  JOIN ferries f ON bm.ferry_id = f.id
                  ORDER BY bm.maintenance_date DESC LIMIT 100";
        break;

    default:
        echo "<p style='color:red;'>Invalid report type.</p>";
        exit;
}

$result = $conn->query($query);

if (!$result) {
    echo "<p style='color:red;'>Error running query: " . $conn->error . "</p>";
    exit;
}

// Output table headerS
switch ($type) {
    case 'ferry_logs':
        echo "<h3>Ferry Logs</h3><table class='audit-table'><tr><th>ID</th><th>Ferry</th><th>Date</th><th>Passengers</th></tr>";
        while ($row = $result->fetch_assoc()) {
            echo "<tr>
                    <td>{$row['id']}</td>
                    <td>" . escape($row['ferry_name']) . "</td>
                    <td>{$row['trip_date']}</td>
                    <td>{$row['passenger_count']}</td>
                  </tr>";
        }
        break;

    case 'tickets':
        echo "<h3>Tickets</h3><table class='audit-table'><tr><th>ID</th><th>User</th><th>Ferry</th><th>Type</th><th>Amount</th><th>Purchase Date</th></tr>";
        while ($row = $result->fetch_assoc()) {
            echo "<tr>
                    <td>{$row['id']}</td>
                    <td>" . escape($row['full_name']) . "</td>
                    <td>" . escape($row['ferry_name']) . "</td>
                    <td>{$row['ticket_type']}</td>
                    <td>{$row['amount']}</td>
                    <td>{$row['purchase_date']}</td>
                  </tr>";
        }
        break;

    case 'repair_logs':
        echo "<h3>Repair Logs</h3><table class='audit-table'><tr><th>ID</th><th>Ferry</th><th>Reported At</th><th>Issue</th><th>Status</th></tr>";
        while ($row = $result->fetch_assoc()) {
            echo "<tr>
                    <td>{$row['id']}</td>
                    <td>" . escape($row['ferry_name']) . "</td>
                    <td>{$row['reported_at']}</td>
                    <td>" . escape($row['issue']) . "</td>
                    <td>{$row['status']}</td>
                  </tr>";
        }
        break;

    case 'boat_maintenance':
        echo "<h3>Boat Maintenance</h3><table class='audit-table'><tr><th>ID</th><th>Ferry</th><th>Date</th><th>Type</th><th>Next Due</th></tr>";
        while ($row = $result->fetch_assoc()) {
            echo "<tr>
                    <td>{$row['id']}</td>
                    <td>" . escape($row['ferry_name']) . "</td>
                    <td>{$row['maintenance_date']}</td>
                    <td>" . escape($row['maintenance_type']) . "</td>
                    <td>{$row['next_due_date']}</td>
                  </tr>";
        }
        break;
}

echo "</table>";

$conn->close();
?>
