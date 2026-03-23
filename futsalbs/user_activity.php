<?php
session_start();
if(!isset($_SESSION['admin'])) { header("Location: login.php"); exit(); }
require_once 'db_connect.php';

$user_id = intval($_GET['id'] ?? 0);

// Fetch user details
$user_stmt = $conn->prepare("SELECT full_name, email, phone FROM users WHERE id = ?");
$user_stmt->bind_param("i", $user_id);
$user_stmt->execute();
$user = $user_stmt->get_result()->fetch_assoc();

if (!$user) { die("User not found."); }

// Fetch booking activity
$query = "SELECT b.*, c.name as court_name 
          FROM bookings b 
          JOIN courts c ON b.court_id = c.id 
          WHERE b.user_id = ? 
          ORDER BY b.booking_date DESC, b.start_time DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$activity = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Activity | <?php echo htmlspecialchars($user['full_name']); ?></title>
    <link rel="stylesheet" href="style.css">
    <style>
        .activity-container { max-width: 1000px; margin: 40px auto; background: white; padding: 30px; border-radius: 12px; box-shadow: 0 5px 20px rgba(0,0,0,0.1); }
        .user-header { border-bottom: 2px solid #eee; padding-bottom: 20px; margin-bottom: 20px; }
        .status-badge { padding: 4px 8px; border-radius: 4px; font-size: 0.85rem; font-weight: bold; }
        .paid { background: #e1f7e7; color: #2ecc71; }
        .unpaid { background: #fee2e2; color: #ef4444; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #eee; }
    </style>
</head>
<body>
    <div class="activity-container">
        <div class="user-header">
            <h2>Activity Log: <?php echo htmlspecialchars($user['full_name']); ?></h2>
            <p>Email: <?php echo htmlspecialchars($user['email']); ?> | Phone: <?php echo htmlspecialchars($user['phone']); ?></p>
            <a href="admin_users.php" style="color: #3498db; text-decoration: none;">&larr; Back to Players List</a>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Court</th>
                    <th>Time Slot</th>
                    <th>Payment</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = $activity->fetch_assoc()): ?>
                <tr>
                    <td><?php echo date("M d, Y", strtotime($row['booking_date'])); ?></td>
                    <td><strong><?php echo htmlspecialchars($row['court_name']); ?></strong></td>
                    <td><?php echo date("g:i A", strtotime($row['start_time'])); ?></td>
                    <td>
                        <span class="status-badge <?php echo strtolower($row['payment_status']); ?>">
                            <?php echo $row['payment_status']; ?>
                        </span>
                    </td>
                    <td><?php echo $row['status']; ?></td>
                </tr>
                <?php endwhile; ?>
                
                <?php if($activity->num_rows == 0): ?>
                <tr>
                    <td colspan="5" style="text-align:center; padding: 40px; color: #999;">This player has no booking history yet.</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>