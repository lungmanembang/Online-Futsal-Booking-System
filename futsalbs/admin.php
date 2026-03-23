<?php
session_start();
require_once 'db_connect.php';

// Check if admin is logged in - adjust 'admin' key to match your login session name
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}

// 1. Fetch Total Bookings
$total_bookings = $conn->query("SELECT COUNT(*) as total FROM bookings")->fetch_assoc()['total'];

// 2. Fetch Total Revenue (Sum of court prices from successful bookings)
$revenue_query = "SELECT SUM(c.price_per_hour) as total_rev 
                  FROM bookings b 
                  JOIN courts c ON b.court_id = c.id 
                  WHERE b.payment_status = 'Paid'";
$total_revenue = $conn->query($revenue_query)->fetch_assoc()['total_rev'] ?? 0;

// 3. Fetch Total Registered Players
$total_users = $conn->query("SELECT COUNT(*) as total FROM users")->fetch_assoc()['total'];

// 4. Fetch Recent Bookings for the table
// Change 'b.created_at' to 'b.id'
$recent_bookings = $conn->query("SELECT b.*, c.name as court_name 
                                FROM bookings b 
                                JOIN courts c ON b.court_id = c.id 
                                ORDER BY b.id DESC LIMIT 5");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | Futsal Arena</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f0f2f5; margin: 0; display: flex; }
        
        /* Sidebar Navigation */
        .sidebar { width: 240px; height: 100vh; background: #2c3e50; color: white; padding: 20px; position: fixed; }
        .sidebar h2 { text-align: center; color: #3498db; margin-bottom: 30px; }
        .nav-link { display: block; color: #bdc3c7; padding: 12px; text-decoration: none; border-radius: 5px; margin-bottom: 10px; transition: 0.3s; }
        .nav-link:hover, .nav-link.active { background: #34495e; color: white; }
        .logout-link { color: #e74c3c; margin-top: 50px; border: 1px solid #e74c3c; text-align: center; }

        /* Main Content Area */
        .main-content { margin-left: 250px; flex: 1; padding: 30px; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
        
        /* Stats Grid */
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); border-top: 4px solid #3498db; }
        .stat-card h3 { margin: 0; color: #7f8c8d; font-size: 0.9rem; text-transform: uppercase; }
        .stat-card p { margin: 10px 0 0; font-size: 1.8rem; font-weight: bold; color: #2c3e50; }

        /* Recent Activity Table */
        .data-table { width: 100%; background: white; border-radius: 10px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        .data-table th, .data-table td { padding: 15px; text-align: left; border-bottom: 1px solid #eee; }
        .data-table th { background: #f8f9fa; color: #333; }
        .status { padding: 5px 10px; border-radius: 20px; font-size: 0.8rem; font-weight: bold; }
        .status-paid { background: #e1f7e7; color: #2ecc71; }
        .status-pending { background: #fff4e5; color: #f39c12; }
    </style>
</head>
<body>

<div class="sidebar">
    <h2>Admin Panel</h2>
    <a href="admin.php" class="nav-link active">Dashboard</a>
    <a href="admin_users.php" class="nav-link">Manage Users</a>
    <a href="revenue_report.php" class="nav-link">Revenue Report</a> 
    <a href="manage_bookings.php" class="nav-link">Manage Bookings</a>
    <a href="manage_courts.php" class="nav-link">Court Settings</a>
    <a href="logout.php" class="nav-link logout-link">Logout</a>
</div>

<div class="main-content">
    <div class="header">
        <h1>Overview</h1>
        <span>Welcome back, <strong>Admin</strong></span>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <h3>Total Bookings</h3>
            <p><?php echo $total_bookings; ?></p>
        </div>
        <div class="stat-card" style="border-top-color: #2ecc71;">
            <h3>Total Revenue</h3>
            <p>Rs. <?php echo number_format($total_revenue); ?></p>
        </div>
        <div class="stat-card" style="border-top-color: #f1c40f;">
            <h3>Registered Players</h3>
            <p><?php echo $total_users; ?></p>
        </div>
    </div>

    <div class="header">
        <h2>Recent Bookings</h2>
        <a href="manage_bookings.php" style="color: #3498db; text-decoration: none;">View All</a>
    </div>

    <table class="data-table">
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
            <?php while($row = $recent_bookings->fetch_assoc()): ?>
            <tr>
                <td><strong><?php echo $row['court_name']; ?></strong></td>
                <td><?php echo $row['booking_date']; ?></td>
                <td><?php echo date("g:i A", strtotime($row['start_time'])); ?></td>
                <td>
                    <span class="status <?php echo ($row['payment_status'] == 'Paid') ? 'status-paid' : 'status-pending'; ?>">
                        <?php echo $row['payment_status']; ?>
                    </span>
                </td>
                <td><?php echo $row['status']; ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

</body>
</html>