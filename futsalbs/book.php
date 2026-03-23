<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    die("ERROR: You must be logged in to book.");
}

$user_id = $_SESSION['user_id'];
$court_id = $_POST['court_id'] ?? null;
$booking_date = $_POST['booking_date'] ?? null;
$start_time = $_POST['start_time'] ?? null;

// --- FIXED: Ignore both 'Cancelled' AND 'Completed' bookings ---
// This allows a new row to be inserted for the same time if the previous game is done.
$check = $conn->prepare("SELECT id FROM bookings WHERE court_id = ? AND booking_date = ? AND start_time = ? AND status NOT IN ('Cancelled', 'Completed')");
$check->bind_param("iss", $court_id, $booking_date, $start_time);
$check->execute();

if ($check->get_result()->num_rows > 0) {
    die("Error: This slot is already booked.");
}

// Insert the new booking
$stmt = $conn->prepare("INSERT INTO bookings (court_id, user_id, booking_date, start_time, payment_status, status) VALUES (?, ?, ?, ?, 'Unpaid', 'Pending')");
$stmt->bind_param("iiss", $court_id, $user_id, $booking_date, $start_time);

if ($stmt->execute()) {
    // Return the ID for the Payment Modal in index.php
    echo "SUCCESS_ID:" . $stmt->insert_id;
} else {
    echo "Error: Booking failed.";
}
?>