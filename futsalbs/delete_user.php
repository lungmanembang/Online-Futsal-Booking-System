<?php
session_start();
if(!isset($_SESSION['admin'])) { exit("Unauthorized access."); }
require_once 'db_connect.php';

if(isset($_GET['id'])) {
    $user_id = intval($_GET['id']);
    
    // We only delete the user, not their bookings (to keep financial history)
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    
    if($stmt->execute()) {
        header("Location: admin_users.php?status=deleted");
    } else {
        echo "Error: Could not delete user.";
    }
}
?>