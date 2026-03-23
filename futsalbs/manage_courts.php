<?php
session_start();
if(!isset($_SESSION['admin'])) header("Location: login.php");
require_once 'db_connect.php';

// Handle Add Court
if (isset($_POST['add_court'])) {
    $name = $_POST['court_name'];
    $price = $_POST['price'];
    $stmt = $conn->prepare("INSERT INTO courts (name, price_per_hour) VALUES (?, ?)");
    $stmt->bind_param("sd", $name, $price);
    $stmt->execute();
}

// Handle Delete Court
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $conn->query("DELETE FROM courts WHERE id = $id");
}

$courts = $conn->query("SELECT * FROM courts");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Courts</title>
    <style>
        body { font-family: sans-serif; background: #f8f9fa; padding: 20px; }
        .container { max-width: 800px; margin: auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        .form-group { margin-bottom: 15px; }
        input { padding: 8px; width: 200px; margin-right: 10px; }
        .btn { padding: 8px 15px; cursor: pointer; border: none; border-radius: 4px; color: white; text-decoration: none; }
        .btn-add { background: #28a745; }
        .btn-del { background: #dc3545; font-size: 0.8rem; }
        .back-link { display: inline-block; margin-bottom: 20px; color: #007bff; text-decoration: none; }
    </style>
</head>
<body>
<div class="container">
    <a href="admin.php" class="back-link">← Back to Dashboard</a>
    <h2>Manage Futsal Courts</h2>

    <form method="POST" style="background: #f1f3f5; padding: 15px; border-radius: 5px;">
        <h4>Add New Court</h4>
        <input type="text" name="court_name" placeholder="Court Name (e.g. Court A)" required>
        <input type="number" name="price" placeholder="Price per Hour (Rs.)" required>
        <button type="submit" name="add_court" class="btn btn-add">Add Court</button>
    </form>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Court Name</th>
                <th>Price (Rs.)</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php while($row = $courts->fetch_assoc()): ?>
            <tr>
                <td><?php echo $row['id']; ?></td>
                <td><?php echo $row['name']; ?></td>
                <td><?php echo $row['price_per_hour']; ?></td>
                <td>
                    <a href="?delete=<?php echo $row['id']; ?>" class="btn btn-del" onclick="return confirm('Delete this court?')">Remove</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>
</body>
</html>