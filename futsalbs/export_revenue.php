<?php
session_start();
if(!isset($_SESSION['admin'])) exit("Unauthorized");
require_once 'db_connect.php';

if (isset($_GET['type']) && $_GET['type'] == 'excel') {
    $selected_year = $_GET['year'] ?? date('Y');
    $selected_month = $_GET['month'] ?? ''; 

    // Set dynamic filename
    $display_time = !empty($selected_month) ? $selected_month : $selected_year;
    $filename = "Revenue_Report_" . $display_time . ".csv";
    
    // Headers for CSV download
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=' . $filename);
    
    $output = fopen('php://output', 'w');
    
    // Column Headers in Excel
    $header_label = !empty($selected_month) ? 'Date' : 'Month';
    fputcsv($output, array($header_label, 'Total Bookings', 'Revenue (Rs.)'));
    
    if (!empty($selected_month)) {
        // Daily View for a specific month
        $query = "SELECT 
                    DATE_FORMAT(b.booking_date, '%d %M %Y') as display_date, 
                    COUNT(b.id) as total_bookings, 
                    SUM(c.price_per_hour) as earned 
                  FROM bookings b
                  JOIN courts c ON b.court_id = c.id 
                  WHERE b.payment_status = 'Paid' 
                  AND DATE_FORMAT(b.booking_date, '%Y-%m') = ?
                  GROUP BY b.booking_date 
                  ORDER BY b.booking_date ASC";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $selected_month);
    } else {
        // Monthly View for a specific year
        $query = "SELECT 
                    MONTHNAME(b.booking_date) as display_date, 
                    COUNT(b.id) as total_bookings, 
                    SUM(c.price_per_hour) as earned 
                  FROM bookings b
                  JOIN courts c ON b.court_id = c.id 
                  WHERE b.payment_status = 'Paid' 
                  AND YEAR(b.booking_date) = ?
                  GROUP BY MONTH(b.booking_date) 
                  ORDER BY MONTH(b.booking_date) ASC";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $selected_year);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    $grand_total = 0;
    while ($row = $result->fetch_assoc()) {
        $grand_total += $row['earned'];
        fputcsv($output, array(
            $row['display_date'], 
            $row['total_bookings'], 
            number_format($row['earned'], 2, '.', '') // Remove commas for better Excel compatibility
        ));
    }

    // Add a Grand Total row at the bottom
    fputcsv($output, array('GRAND TOTAL', '', number_format($grand_total, 2, '.', '')));

    fclose($output);
    exit();
}
?>