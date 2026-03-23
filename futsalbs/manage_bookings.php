<?php
session_start();
require_once 'db_connect.php';

// Security Check: Only admins can access
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}

// Fetch all bookings with Court names and User names
$query = "SELECT b.*, c.name as court_name, u.full_name as user_name, u.phone as user_phone 
          FROM bookings b 
          JOIN courts c ON b.court_id = c.id 
          JOIN users u ON b.user_id = u.id 
          ORDER BY b.booking_date DESC, b.start_time DESC";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Bookings | Admin</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .admin-container { max-width: 1200px; margin: 40px auto; background: white; padding: 30px; border-radius: 12px; box-shadow: 0 5px 20px rgba(0,0,0,0.1); }
        .data-table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .data-table th, .data-table td { padding: 15px; text-align: left; border-bottom: 1px solid #eee; vertical-align: middle; }
        
        /* Button Styles */
        .btn-verify { background: #2ecc71; color: white; border: none; padding: 8px 12px; border-radius: 4px; cursor: pointer; font-weight: bold; }
        .btn-complete { background: #3498db; color: white; border: none; padding: 8px 12px; border-radius: 4px; cursor: pointer; font-weight: bold; margin-left: 5px; }
        .btn-delete { color: #e74c3c; text-decoration: none; margin-left: 15px; font-size: 0.9rem; font-weight: bold; }
        
        /* Badge Styles */
        .badge-paid { background: #e1f7e7; color: #27ae60; padding: 5px 10px; border-radius: 4px; font-weight: bold; font-size: 0.85rem; }
        .badge-done { background: #ebf5ff; color: #2980b9; padding: 5px 10px; border-radius: 4px; font-weight: bold; font-size: 0.85rem; }
        
        .screenshot-link { color: #27ae60; text-decoration: underline; font-weight: 500; }
    </style>
</head>
<body>

<div class="admin-container">
    <div style="display: flex; justify-content: space-between; align-items: center;">
        <h2>Booking Management Panel</h2>
        <a href="admin.php" style="text-decoration: none; color: #666;">&larr; Back to Dashboard</a>
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th>Customer</th>
                <th>Court & Time</th>
                <th>Payment Receipt</th>
                <th>Status & Actions</th>
                <th>Manage</th>
            </tr>
        </thead>
        <tbody>
            <?php while($row = $result->fetch_assoc()): ?>
            <tr id="row-<?php echo $row['id']; ?>">
                <td>
                    <strong><?php echo htmlspecialchars($row['user_name']); ?></strong><br>
                    <small><?php echo htmlspecialchars($row['user_phone']); ?></small>
                </td>
                <td>
                    <?php echo date("M d, Y", strtotime($row['booking_date'])); ?><br>
                    <small><?php echo date("g:i A", strtotime($row['start_time'])); ?> - <?php echo htmlspecialchars($row['court_name']); ?></small>
                </td>

                <td>
                    <?php if(!empty($row['payment_screenshot'])): ?>
                        <a href="uploads/payments/<?php echo $row['payment_screenshot']; ?>" target="_blank" class="screenshot-link">View Receipt</a>
                    <?php else: ?>
                        <span style="color: #ccc;">No upload</span>
                    <?php endif; ?>
                </td>

                <td>
                    <?php if($row['payment_status'] !== 'Paid'): ?>
                        <button onclick="updateStatus(<?php echo $row['id']; ?>, 'verify')" class="btn-verify" id="vbtn-<?php echo $row['id']; ?>">Verify Pay</button>
                    <?php else: ?>
                        <span class="badge-paid">Paid</span>
                    <?php endif; ?>

                    <?php if($row['status'] !== 'Completed' && $row['payment_status'] === 'Paid'): ?>
                        <button onclick="updateStatus(<?php echo $row['id']; ?>, 'complete')" class="btn-complete" id="cbtn-<?php echo $row['id']; ?>">Mark Done</button>
                    <?php elseif($row['status'] === 'Completed'): ?>
                        <span class="badge-done">Completed</span>
                    <?php endif; ?>
                </td>

                <td>
                    <a href="javascript:void(0)" onclick="deleteBooking(<?php echo $row['id']; ?>)" class="btn-delete">Delete</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<script>
function updateStatus(bookingId, type) {
    const actionUrl = (type === 'verify') ? 'verify_payment.php' : 'complete_booking.php';
    const confirmMsg = (type === 'verify') ? "Confirm payment received?" : "Mark this booking as completed?";

    if(confirm(confirmMsg)) {
        fetch(actionUrl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'id=' + bookingId
        })
        .then(res => res.text())
        .then(data => {
            // Trim to handle hidden whitespace from PHP echos
            if(data.trim() === 'success') {
                location.reload(); 
            } else {
                alert("Update failed: " + data);
            }
        })
        .catch(err => {
            alert("Error connecting to server.");
            console.error(err);
        });
    }
}

function deleteBooking(bookingId) {
    if(confirm("Permanently delete this booking?")) {
        fetch('delete_bookings.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'id=' + bookingId
        })
        .then(res => res.text())
        .then(data => {
            if(data.trim() === 'Deleted') {
                document.getElementById('row-' + bookingId).remove();
            } else {
                alert("Delete failed.");
            }
        });
    }
}
</script>

</body>
</html>