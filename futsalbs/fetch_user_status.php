<?php
require_once 'db_connect.php';

if (isset($_POST['user_name'])) {
    $name = trim($_POST['user_name']);

    // JOIN users table because bookings stores user_id, not the name string
    $stmt = $conn->prepare("
        SELECT b.booking_date, b.start_time, b.status, c.name 
        FROM bookings b 
        JOIN courts c ON b.court_id = c.id 
        JOIN users u ON b.user_id = u.id 
        WHERE u.full_name = ?
    ");
    $stmt->bind_param("s", $name);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            echo "<div class='booking-card' style='padding:10px; border-bottom:1px solid #ddd;'>
                    <strong>Court:</strong> " . htmlspecialchars($row['name']) . " | 
                    <strong>Status:</strong> <span class='status-" . strtolower($row['status']) . "'>" . htmlspecialchars($row['status']) . "</span><br>
                    <small>" . htmlspecialchars($row['booking_date']) . " at " . date("g:i A", strtotime($row['start_time'])) . "</small>
                  </div>";
        }
    } else {
        echo "<div style='padding:15px; background:#fff5f5; border:1px solid #feb2b2; color:#c53030; border-radius:8px;'>
                <strong>No bookings found for this name.</strong>
              </div>";
    }
    $stmt->close();
}
$conn->close();
?>