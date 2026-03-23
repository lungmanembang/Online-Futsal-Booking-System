<?php
require_once 'db_connect.php';
session_start();

$action = $_POST['action'] ?? '';

if ($action == 'register') {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $pass = $_POST['password'] ?? '';

    if(empty($name) || empty($email) || empty($phone) || empty($pass)) {
        echo "All fields (including Phone) are required.";
        exit;
    }

    $hashed_pass = password_hash($pass, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO users (full_name, email, phone, password) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $name, $email, $phone, $hashed_pass);
    
    if ($stmt->execute()) {
        $_SESSION['user_id'] = $stmt->insert_id;
        $_SESSION['user_name'] = $name;
        echo "success";
    } else {
        echo "Error: Registration failed. Phone or Email may already be in use.";
    }
}

if ($action == 'login') {
    // FIXED: Changed from email to phone to match your modal input ID 'loginPhno'
    $phone = $_POST['phone'] ?? ''; 
    $password = $_POST['password'] ?? '';

    $stmt = $conn->prepare("SELECT id, full_name, password FROM users WHERE phone = ?");
    $stmt->bind_param("s", $phone);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($user = $result->fetch_assoc()) {
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['full_name'];
            echo "success";
        } else {
            echo "Incorrect password.";
        }
    } else {
        echo "User not found with this phone number.";
    }
}
?>