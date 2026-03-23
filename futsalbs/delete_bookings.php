<?php
session_start();
// Security Check: Only admins can delete
if (!isset($_SESSION['admin'])) {
    exit("Unauthorized access.");
}

require_once 'db_connect.php';

if (isset($_POST['id'])) {
    $id = intval($_POST['id']);

    $stmt = $conn->prepare("DELETE FROM bookings WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        echo "Deleted";
    } else {
        echo "Error deleting record.";
    }
    
    $stmt->close();
}
$conn->close();
?>