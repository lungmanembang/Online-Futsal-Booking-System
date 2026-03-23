<?php
session_start();
if(!isset($_SESSION['admin'])) exit("Unauthorized");
require_once 'db_connect.php';

if(isset($_POST['id'])) {
    $id = intval($_POST['id']);
    
    // This SQL sets the status to Completed
    $stmt = $conn->prepare("UPDATE bookings SET status = 'Completed' WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if($stmt->execute()) {
        echo "success"; 
    } else {
        echo "error";
    }
    $stmt->close();
}
?>