<?php
session_start();
if(!isset($_SESSION['admin'])) header("Location: login.php");
require_once 'db_connect.php';

// --- 1. SET VARIABLES & FILTERS ---
$selected_year = $_GET['year'] ?? date('Y');
$selected_month_filter = $_GET['filter_month'] ?? ''; // Format: YYYY-MM

// --- 2. CALCULATE RUNNING MONTH REVENUE ---
$current_m = date('m');
$current_y = date('Y');
$run_q = "SELECT SUM(c.price_per_hour) as total FROM bookings b 
          JOIN courts c ON b.court_id = c.id 
          WHERE b.payment_status = 'Paid' AND MONTH(b.booking_date) = ? AND YEAR(b.booking_date) = ?";
$stmt_run = $conn->prepare($run_q);
$stmt_run->bind_param("ii", $current_m, $current_y);
$stmt_run->execute();
$running_revenue = $stmt_run->get_result()->fetch_assoc()['total'] ?? 0;

// --- 3. FETCH REPORT DATA ---
if (!empty($selected_month_filter)) {
    // Specific Month View
    $query = "SELECT DATE_FORMAT(b.booking_date, '%d %M') as month_name, 
                     COUNT(b.id) as total_bookings, 
                     SUM(c.price_per_hour) as monthly_earned 
              FROM bookings b
              JOIN courts c ON b.court_id = c.id 
              WHERE b.payment_status = 'Paid' AND DATE_FORMAT(b.booking_date, '%Y-%m') = ?
              GROUP BY b.booking_date ORDER BY b.booking_date ASC";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $selected_month_filter);
} else {
    // General Year View
    $query = "SELECT MONTHNAME(b.booking_date) as month_name, 
                     MONTH(b.booking_date) as m_num,
                     COUNT(b.id) as total_bookings, 
                     SUM(c.price_per_hour) as monthly_earned 
              FROM bookings b
              JOIN courts c ON b.court_id = c.id 
              WHERE b.payment_status = 'Paid' AND YEAR(b.booking_date) = ?
              GROUP BY MONTH(b.booking_date) ORDER BY m_num ASC";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $selected_year);
}
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Revenue Report | Admin</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f4f7f6; padding: 40px; color: #333; }
        .container { max-width: 1000px; margin: auto; background: white; padding: 35px; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.08); }
        
        .header-flex { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; }
        .back-link { text-decoration: none; color: #3498db; font-weight: 500; display: inline-block; margin-bottom: 15px; }
        
        /* Stats Highlight */
        .highlight-box { background: #ebf9f0; border-left: 5px solid #2ecc71; padding: 20px; border-radius: 8px; margin-bottom: 25px; display: flex; justify-content: space-between; align-items: center; }
        .highlight-box b { color: #27ae60; font-size: 1.1rem; }
        .highlight-box span { font-size: 1.5rem; font-weight: bold; color: #2c3e50; }

        /* Filters */
        .filter-section { background: #f8f9fa; padding: 20px; border-radius: 10px; margin-bottom: 25px; display: flex; gap: 20px; align-items: center; border: 1px solid #eee; }
        .filter-group { display: flex; flex-direction: column; gap: 5px; }
        select, input[type="month"] { padding: 10px; border-radius: 6px; border: 1px solid #ddd; outline: none; transition: 0.3s; }
        select:focus, input:focus { border-color: #3498db; }

        /* Buttons */
        .btn-group { display: flex; gap: 12px; }
        .btn { padding: 10px 22px; border-radius: 8px; text-decoration: none; font-weight: 600; cursor: pointer; border: none; font-size: 14px; transition: 0.2s; display: flex; align-items: center; gap: 8px; }
        .btn-excel { background: #1D6F42; color: white; }
        .btn-excel:hover { background: #155231; }
        .btn-pdf { background: #E74C3C; color: white; }
        .btn-pdf:hover { background: #c0392b; }

        /* Table */
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th { text-align: left; background: #fdfdfd; color: #7f8c8d; text-transform: uppercase; font-size: 0.85rem; letter-spacing: 1px; padding: 15px; border-bottom: 2px solid #eee; }
        td { padding: 18px 15px; border-bottom: 1px solid #f1f1f1; }
        .grand-total-row { background: #fcfcfc; font-size: 1.1rem; }

        @media print { .no-print { display: none !important; } .container { box-shadow: none; border: none; } }
    </style>
</head>
<body>

<div class="container">
    <a href="admin.php" class="back-link no-print">← Back to Dashboard</a>
    
    <div class="header-flex">
        <h1 style="margin:0;">Revenue Report</h1>
        <div class="btn-group no-print">
            <a href="export_revenue.php?type=excel&year=<?php echo $selected_year; ?>&month=<?php echo $selected_month_filter; ?>" class="btn btn-excel">📥 Export Excel</a>
            <button onclick="window.print()" class="btn btn-pdf">📄 Save PDF</button>
        </div>
    </div>

    <div class="highlight-box">
        <div>
            <b>Running Month (<?php echo date('F'); ?>)</b><br>
            <small style="color:#666">Live earnings so far</small>
        </div>
        <span>Rs. <?php echo number_format($running_revenue, 2); ?></span>
    </div>

    <form method="GET" class="filter-section no-print">
        <div class="filter-group">
            <label style="font-size: 0.8rem; font-weight: bold;">View by Year</label>
            <select name="year" onchange="this.form.submit()">
                <?php 
                for($i = date('Y'); $i >= 2023; $i--) {
                    $sel = ($i == $selected_year) ? 'selected' : '';
                    echo "<option value='$i' $sel>$i</option>";
                }
                ?>
            </select>
        </div>
        <div style="width: 1px; height: 40px; background: #ddd;"></div>
        <div class="filter-group">
            <label style="font-size: 0.8rem; font-weight: bold;">Pick Specific Month</label>
            <div style="display: flex; align-items: center; gap: 10px;">
                <input type="month" name="filter_month" value="<?php echo $selected_month_filter; ?>" onchange="this.form.submit()">
                <?php if(!empty($selected_month_filter)): ?>
                    <a href="revenue_report.php" style="color: #e74c3c; font-size: 0.8rem; text-decoration:none;">Clear</a>
                <?php endif; ?>
            </div>
        </div>
    </form>

    <table>
        <thead>
            <tr>
                <th><?php echo !empty($selected_month_filter) ? 'Date' : 'Month'; ?></th>
                <th>Total Bookings</th>
                <th style="text-align: right;">Revenue (NPR)</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $grand_total = 0;
            if($result && $result->num_rows > 0):
                while($row = $result->fetch_assoc()): 
                    $grand_total += $row['monthly_earned'];
            ?>
            <tr>
                <td><strong><?php echo $row['month_name']; ?></strong></td>
                <td><?php echo $row['total_bookings']; ?> Sessions</td>
                <td style="text-align: right; color: #27ae60; font-weight: bold;">Rs. <?php echo number_format($row['monthly_earned'], 2); ?></td>
            </tr>
            <?php endwhile; else: ?>
                <tr><td colspan="3" style="text-align:center; padding: 40px; color: #999;">No data found for this selection.</td></tr>
            <?php endif; ?>
        </tbody>
        <tfoot>
            <tr class="grand-total-row">
                <td colspan="2" style="text-align: right; font-weight: bold; border:none;">Grand Total:</td>
                <td style="text-align: right; font-weight: bold; color: #2c3e50; border:none;">Rs. <?php echo number_format($grand_total, 2); ?></td>
            </tr>
        </tfoot>
    </table>
</div>

</body>
</html>