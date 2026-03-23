<?php
session_start();
if(!isset($_SESSION['admin'])) exit("Unauthorized");
require_once 'db_connect.php';

if(isset($_POST['id'])) {
    $id = intval($_POST['id']);
    // Update both payment and status when verified
    $stmt = $conn->prepare("UPDATE bookings SET payment_status = 'Paid', status = 'Confirmed' WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if($stmt->execute()) {
        echo "success";
    } else {
        echo "error";
    }
}
?>