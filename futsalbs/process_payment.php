<?php
session_start();
require_once 'db_connect.php';
$booking_id = $_GET['id'] ?? 0;
?>

<div class="payment-card">
    <h3>Upload Payment Receipt</h3>
    <p>Please upload a screenshot of your Bank Transfer / QR Payment for Booking #<?php echo $booking_id; ?></p>
    
    <form action="upload_handler.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="booking_id" value="<?php echo $booking_id; ?>">
        <input type="file" name="screenshot" accept="image/*" required>
        <button type="submit" class="btn-primary">Upload & Notify Admin</button>
    </form>
</div>