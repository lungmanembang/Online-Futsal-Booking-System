<?php
$conn = new mysqli("localhost", "root", "", "futsal_db");
$sql = "SELECT b.id, b.user_name, c.name, b.booking_date, b.start_time, b.status 
        FROM bookings b JOIN courts c ON b.court_id = c.id ORDER BY b.id DESC";
$result = $conn->query($sql);

while($row = $result->fetch_assoc()) {
    $statusClass = ($row['status'] == 'Pending') ? 'status-pending' : 'status-confirmed';
    echo "<tr>
            <td>{$row['user_name']}</td>
            <td>{$row['name']}</td>
            <td>{$row['booking_date']} {$row['start_time']}</td>
            <td class='$statusClass'>{$row['status']}</td>
            <td>";
    if($row['status'] == 'Pending') {
        echo "<button class='btn btn-confirm' onclick='updateStatus({$row['id']}, \"confirm\")'>Confirm</button>";
    }
    echo "<button class='btn btn-cancel' onclick='updateStatus({$row['id']}, \"delete\")'>Cancel</button>
            </td>
          </tr>";
}
?>