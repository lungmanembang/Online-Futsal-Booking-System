<?php
session_start();
if (isset($_POST['login'])) {
    // Simple hardcoded credentials for MVP
    if ($_POST['username'] == 'admin' && $_POST['password'] == 'futsal123') {
        $_SESSION['admin'] = true;
        header("Location: admin.php");
    } else {
        $error = "Invalid Credentials";
    }
}
?>
<!DOCTYPE html>
<html>
<head><title>Admin Login</title></head>
<style>
    body { font-family: sans-serif; display: flex; justify-content: center; align-items: center; height: 100vh; background: #f0f2f5; }
    form { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
    input { display: block; width: 250px; margin-bottom: 10px; padding: 10px; }
</style>
<body>
    <form method="POST">
        <h3>Admin Login</h3>
        <?php if(isset($error)) echo "<p style='color:red'>$error</p>"; ?>
        <input type="text" name="username" placeholder="Username" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit" name="login" style="width:100%; padding:10px; cursor:pointer;">Login</button>
    </form>
</body>
</html>