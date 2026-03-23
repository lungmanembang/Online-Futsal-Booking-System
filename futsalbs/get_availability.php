<?php
require_once 'db_connect.php';

$date = $_GET['date'] ?? date('Y-m-d');
$court_id = $_GET['court_id'] ?? 1;

// Define the standard operating hours
$time_slots = [
    "07:00:00", "08:00:00", "09:00:00", "10:00:00", "11:00:00",
    "12:00:00", "13:00:00", "14:00:00", "15:00:00", "16:00:00",
    "17:00:00", "18:00:00", "19:00:00", "20:00:00"
];

// Fetch existing bookings
$stmt = $conn->prepare("SELECT start_time, status FROM bookings WHERE booking_date = ? AND court_id = ?");
$stmt->bind_param("si", $date, $court_id);
$stmt->execute();
$result = $stmt->get_result();

$booked_slots = [];
while ($row = $result->fetch_assoc()) {
    $booked_slots[$row['start_time']] = $row['status'];
}

$response = [];
foreach ($time_slots as $slot) {
    $status = $booked_slots[$slot] ?? 'Available';

    // --- THE FIX: Convert "Completed" to "Available" ---
    if ($status === 'Completed') {
        $status = 'Available';
    }

    // Standardize 'Confirmed' to 'Booked' for your CSS
    if ($status === 'Confirmed') {
        $status = 'Booked';
    }

    $response[] = [
        'time_label' => date("h:i A", strtotime($slot)),
        'raw_time' => $slot,
        'status' => $status
    ];
}

header('Content-Type: application/json');
echo json_encode($response);
?>