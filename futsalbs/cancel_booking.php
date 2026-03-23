<?php
session_start();
require_once 'db_connect.php';

if (isset($_POST['booking_id']) && isset($_SESSION['user_id'])) {
    $booking_id = $_POST['booking_id'];
    $user_id = $_SESSION['user_id'];

    // Security: Only the user who made the booking can delete it
    $stmt = $conn->prepare("DELETE FROM bookings WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $booking_id, $user_id);
    $stmt->execute();
    echo "success";
}
?>