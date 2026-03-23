<?php
session_start();
require_once 'db_connect.php';
// Set variable for JS to check login status
$isLoggedIn = isset($_SESSION['user_id']) ? 'true' : 'false';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Futsal Arena | Home</title>
    <link rel="stylesheet" href="style.css">
    <style>
        /* Modern Hero and Layout Styling */
        .hero {
            background: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)), url('https://images.unsplash.com/photo-1574629810360-7efbbe195018?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80');
            background-size: cover; background-position: center; height: 80vh;
            display: flex; align-items: center; justify-content: center; text-align: center; color: white;
        }
        .cta-button {
            padding: 15px 40px; background: #27ae60; color: white; text-decoration: none;
            font-weight: bold; border-radius: 30px; font-size: 1.1rem; transition: 0.3s; cursor: pointer; border: none;
        }
        
        /* Auth Modal Styling (Copied from your index.php logic) */
        .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.7); backdrop-filter: blur(4px); }
        .modal-content { background: white; margin: 10% auto; padding: 30px; border-radius: 12px; width: 360px; position: relative; color: #333; }
        .close-btn { position: absolute; right: 20px; top: 15px; cursor: pointer; font-size: 24px; color: #666; }
        .auth-input { width: 100%; margin-bottom: 15px; padding: 12px; border: 1px solid #ddd; border-radius: 6px; box-sizing: border-box; }
        .auth-toggle { color: #3498db; cursor: pointer; font-weight: bold; }
        .hidden { display: none; }
    </style>
</head>
<body>

<div class="navbar">
    <div class="logo"><a href="home.php" style="text-decoration: none; color: #ddd;">⚽ Futsal Arena</a></div>
    <div class="nav-links">
        <a href="home.php">Home</a>
        <a href="index.php">Book Now</a>
        <a href="about.php">About Us</a>
        <a href="contact.php">Contact Us</a>
        <?php if(isset($_SESSION['user_id'])): ?>
            <span style="color: #27ae60; margin-left:10px;">Hi, <?php echo $_SESSION['user_name']; ?></span>
            <a href="logout.php" style="color: #e74c3c; margin-left: 10px;">Logout</a>
        <?php else: ?>
            <a href="javascript:void(0)" onclick="openAuthModal()">Login / Signup</a>
        <?php endif; ?>
    </div>
</div>

<div class="hero">
    <div class="hero-content">
        <h1>Your Arena, Your Rules</h1>
        <p>Book the best futsal courts in town.</p>
        <?php if(isset($_SESSION['user_id'])): ?>
            <a href="index.php" class="cta-button">Book Now</a>
        <?php else: ?>
            <button onclick="openAuthModal()" class="cta-button">Login to Start Booking</button>
        <?php endif; ?>
    </div>
</div>

<div id="authModal" class="modal">
    <div class="modal-content">
        <span class="close-btn" onclick="closeAuthModal()">&times;</span>
        
        <div id="loginSection">
            <h3 style="margin-top: 0;">Welcome Back</h3>
            <input type="text" id="loginPhno" class="auth-input" placeholder="Phone Number" required>
            <input type="password" id="loginPass" class="auth-input" placeholder="Password">
            <button onclick="handleAuth('login')" style="width:100%; padding:12px; background:#2ecc71; color:white; border:none; border-radius:6px; cursor:pointer;">Login</button>
            <p style="font-size: 0.9rem; margin-top: 15px;">New player? <span class="auth-toggle" onclick="toggleAuth()">Create account</span></p>
        </div>

        <div id="signupSection" class="hidden">
            <h3 style="margin-top: 0;">Join the Arena</h3>
            <input type="text" id="regName" class="auth-input" placeholder="Full Name">
            <input type="email" id="regEmail" class="auth-input" placeholder="Email Address">
            <input type="password" id="regPass" class="auth-input" placeholder="Password">
            <input type="text" id="regPhone" class="auth-input" placeholder="Phone Number">
            <button onclick="handleAuth('register')" style="width:100%; padding:12px; background:#3498db; color:white; border:none; border-radius:6px; cursor:pointer;">Sign Up</button>
            <p style="font-size: 0.9rem; margin-top: 15px;">Have an account? <span class="auth-toggle" onclick="toggleAuth()">Back to Login</span></p>
        </div>
        <div id="authError" style="color:red; margin-top:10px; font-size: 0.85rem; text-align: center;"></div>
    </div>
</div>

<script>
// Authentication Logic (Shared with index.php)
function openAuthModal() { document.getElementById('authModal').style.display = 'block'; }
function closeAuthModal() { document.getElementById('authModal').style.display = 'none'; }
function toggleAuth() {
    document.getElementById('loginSection').classList.toggle('hidden');
    document.getElementById('signupSection').classList.toggle('hidden');
}

function handleAuth(type) {
    const errorDiv = document.getElementById('authError');
    let params = new URLSearchParams();
    params.append('action', type);
    
    if(type === 'login') {
        params.append('phone', document.getElementById('loginPhno').value);
        params.append('password', document.getElementById('loginPass').value);
    } else {
        params.append('name', document.getElementById('regName').value);
        params.append('email', document.getElementById('regEmail').value);
        params.append('phone', document.getElementById('regPhone').value);
        params.append('password', document.getElementById('regPass').value);
    }

    fetch('auth_process.php', { method: 'POST', body: params })
        .then(res => res.text())
        .then(text => {
            if (text.trim() === 'success') { 
                // After successful login on home page, redirect to the booking page (index.php)
                window.location.href = 'index.php'; 
            } else { 
                errorDiv.innerText = text; 
            }
        });
}
</script>

</body>
</html>