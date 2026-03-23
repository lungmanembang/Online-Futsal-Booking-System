<?php
require_once 'db_connect.php';

// 1. Total Bookings
$total_query = "SELECT COUNT(*) as total FROM bookings";
$total_result = $conn->query($total_query)->fetch_assoc();

// 2. Pending Bookings
$pending_query = "SELECT COUNT(*) as pending FROM bookings WHERE status = 'Pending'";
$pending_result = $conn->query($pending_query)->fetch_assoc();

// 3. Estimated Revenue (Summing prices from courts table)
$revenue_query = "SELECT SUM(c.price_per_hour) as revenue 
                  FROM bookings b 
                  JOIN courts c ON b.court_id = c.id 
                  WHERE b.status = 'Confirmed'";
$revenue_result = $conn->query($revenue_query)->fetch_assoc();

$stats = [
    'total' => $total_result['total'],
    'pending' => $pending_result['pending'],
    'revenue' => number_format($revenue_result['revenue'] ?? 0, 2)
];

echo json_encode($stats);
?>