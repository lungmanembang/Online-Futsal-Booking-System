<?php
session_start();
// Security Check: Only admins can view this
if(!isset($_SESSION['admin'])) { 
    header("Location: login.php"); 
    exit(); 
}
require_once 'db_connect.php';

// Handle Search
$search = $_GET['search'] ?? '';
$search_param = "%$search%";

// 1. UPDATED QUERY: Ensure 'phone' is included in the SELECT statement
$query = "SELECT id, full_name, email, phone, created_at FROM users 
          WHERE full_name LIKE ? OR email LIKE ? OR phone LIKE ? 
          ORDER BY created_at DESC";

$stmt = $conn->prepare($query);
$stmt->bind_param("sss", $search_param, $search_param, $search_param);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Users | Admin</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .admin-container { max-width: 1100px; margin: 40px auto; background: white; padding: 30px; border-radius: 12px; box-shadow: 0 5px 20px rgba(0,0,0,0.1); }
.search-bar {
  display: flex;
  align-items: center;
  gap: 12px;
  width: 100%;
  max-width: 700px;
  margin-bottom: 20px;
  flex-wrap: wrap;
}

.search-bar input {
  flex: 1 1 260px;
  min-width: 0;
  padding: 12px 14px;
  border: 1px solid #dcdfe4;
  border-radius: 8px;
  font-size: 1rem;
  box-sizing: border-box;
}

.btn-search {
  flex-shrink: 0;
  padding: 12px 18px;
  background: #3498db;
  color: #fff;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  font-size: 0.95rem;
  white-space: nowrap;
}

.btn-search:hover {
  background: #2d84bf;
}

.clear-search {
  flex-shrink: 0;
  text-decoration: none;
  padding: 12px 14px;
  border-radius: 8px;
  background: #f3f4f6;
  color: #333;
  font-size: 0.95rem;
  white-space: nowrap;
}

.clear-search:hover {
  background: #e5e7eb;
}

@media (max-width: 600px) {
  .search-bar {
    flex-direction: column;
    align-items: stretch;
  }

  .btn-search,
  .clear-search {
    width: 100%;
    text-align: center;
  }
}
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 15px; text-align: left; border-bottom: 1px solid #eee; }
        th { background-color: #f8f9fa; color: #333; }
        .btn-delete { color: #e74c3c; text-decoration: none; font-weight: bold; font-size: 0.9rem; }
        .contact-info { font-size: 0.9rem; color: #555; }
        .phone-tag { background: #e3f2fd; color: #1976d2; padding: 2px 6px; border-radius: 4px; font-size: 0.8rem; font-weight: bold; }
    </style>
</head>
<body>

    <div class="admin-container">
        <h2>Manage Registered Players</h2>
        
      <form class="search-bar" method="GET">
    <input 
        type="text" 
        name="search" 
        placeholder="Search name, email, or phone..." 
        value="<?php echo htmlspecialchars($search); ?>"
    >

    <button type="submit" class="btn-search">Search</button>

    <?php if($search): ?>
        <a href="admin_users.php" class="clear-search">Clear</a>
    <?php endif; ?>
</form>

        <table>
            <thead>
                <tr>
                    <th>Player Name</th>
                    <th>Email Address</th>
                    <th>Phone Number</th> <th>Joined Date</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = $result->fetch_assoc()): ?>
                <tr>
                    <td>
                        <strong><?php echo htmlspecialchars($row['full_name']); ?></strong>
                        <br><small style="color: #999;">ID: #<?php echo $row['id']; ?></small>
                    </td>
                    <td class="contact-info"><?php echo htmlspecialchars($row['email']); ?></td>
                    
                    <td>
                        <span class="phone-tag">
                            <?php echo !empty($row['phone']) ? htmlspecialchars($row['phone']) : 'N/A'; ?>
                        </span>
                    </td>

                    <td><?php echo date("M d, Y", strtotime($row['created_at'])); ?></td>
                    <td>
                        <a href="user_activity.php?id=<?php echo $row['id']; ?>" 
       style="color: #3498db; text-decoration: none; font-weight: bold; margin-right: 10px;">
       View Activity
    </a>
                        <a href="delete_user.php?id=<?php echo $row['id']; ?>" class="btn-delete" onclick="return confirm('Delete this user? This cannot be undone.')">Delete</a>
                    </td>
                </tr>
                <?php endwhile; ?>
                
                <?php if($result->num_rows == 0): ?>
                <tr>
                    <td colspan="5" style="text-align:center; padding: 30px; color: #999;">No players found.</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
        
        <div style="margin-top: 20px;">
            <a href="admin.php" style="text-decoration: none; color: #666;">&larr; Back to Dashboard</a>
        </div>
    </div>

</body>
</html>