<?php
session_start();
require_once 'db_connect.php';

// Redirect to index if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];

// Fetch all bookings for this user
$query = "SELECT b.*, c.name as court_name, c.price_per_hour 
          FROM bookings b 
          JOIN courts c ON b.court_id = c.id 
          WHERE b.user_id = ? 
          ORDER BY b.booking_date DESC, b.start_time DESC";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Profile | Futsal Arena</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .profile-container { max-width: 900px; margin: 50px auto; padding: 20px; background: white; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
        .booking-table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .booking-table th, .booking-table td { padding: 12px; border-bottom: 1px solid #eee; text-align: left; }
        .status-badge { padding: 5px 10px; border-radius: 4px; font-size: 0.8rem; font-weight: bold; }
        .paid { background: #d4edda; color: #155724; }
        .unpaid { background: #f8d7da; color: #721c24; }
        .header-flex { display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #f4f4f4; padding-bottom: 15px; }
    </style>
</head>
<body>
    <div class="navbar">
        <div class="logo">⚽ Futsal Arena</div>
        <div class="nav-links">
            <a href="index.php">Book New Slot</a>
            <a href="logout.php" style="color: #e74c3c;">Logout</a>
        </div>
    </div>

    <div class="profile-container">
        <div class="header-flex">
            <h2>Welcome, <?php echo htmlspecialchars($user_name); ?></h2>
            <p>User ID: #<?php echo $user_id; ?></p>
        </div>

        <h3>Your Booking History</h3>
        <table class="booking-table">
            <thead>
                <tr>
                    <th>Court</th>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Payment</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php while($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><strong><?php echo $row['court_name']; ?></strong></td>
                            <td><?php echo $row['booking_date']; ?></td>
                            <td><?php echo date("g:i A", strtotime($row['start_time'])); ?></td>
                            <td>
                                <span class="status-badge <?php echo strtolower($row['payment_status']); ?>">
                                    <?php echo $row['payment_status']; ?>
                                </span>
                            </td>
                            <td><?php echo $row['status']; ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="5" style="text-align:center;">No bookings found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>