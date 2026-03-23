<?php
session_start();
require_once 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['screenshot'])) {
    $booking_id = intval($_POST['booking_id']);
    $target_dir = "uploads/payments/";
    
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    $file_ext = strtolower(pathinfo($_FILES["screenshot"]["name"], PATHINFO_EXTENSION));
    $new_filename = "pay_" . $booking_id . "_" . time() . "." . $file_ext;
    
    if (move_uploaded_file($_FILES["screenshot"]["tmp_name"], $target_dir . $new_filename)) {
        $stmt = $conn->prepare("UPDATE bookings SET payment_screenshot = ? WHERE id = ?");
        $stmt->bind_param("si", $new_filename, $booking_id);
        
        if($stmt->execute()) {
            echo "success";
        } else {
            echo "Database update failed.";
        }
    } else {
        echo "File upload error.";
    }
}
?>