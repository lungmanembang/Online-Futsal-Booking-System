<?php
session_start();
require_once 'db_connect.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Contact Us | Futsal Arena</title>
    <link rel="stylesheet" href="style.css">
    <style>
        /* Your existing styles */
        .contact-flex { display: flex; gap: 50px; padding: 60px 20px; flex-wrap: wrap; }
        .contact-info { flex: 1; min-width: 300px; }
        
        .form-input { width: 100%; padding: 12px; margin-bottom: 20px; border: 1px solid #ddd; border-radius: 6px; box-sizing: border-box; }
        .contact-item { margin-bottom: 20px; }
        .contact-item strong { color: #27ae60; display: block; }

        /* --- NEW MAP STYLES --- */
        .map-container {
            width: 100%;
            height: 350px;
            margin-top: 25px;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            border: 1px solid #eee;
        }
        .map-container iframe {
            width: 100%;
            height: 100%;
            border: none;
        }

        /* Auth Modal Styles */
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
    <div class="logo"><a href="home.php" style="text-decoration: none; color: #ddd;" >⚽ Futsal Arena</a></div>
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

<div class="container">
    <div class="contact-flex">
        <div class="contact-info">
            <h1>Get in Touch</h1>
            <p>Have questions about bookings or corporate events? Call us on the number given below.</p>
            
            <div class="contact-item">
                <strong>📍 Location</strong>
                Main Road, City Center (Near Stadium)
            </div>

            <div class="map-container">
    <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d56524.76515175475!2d85.2413763486328!3d27.692643!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x39eb19b26c5a3df7%3A0xd49ea11f56ff8ac5!2sEverest%20College!5e0!3m2!1sen!2snp!4v1774101997357!5m2!1sen!2snp" width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade">

    </iframe>
            </div>

            <div class="contact-item" style="margin-top: 25px;">
                <strong>📞 Phone</strong>
                +977-9824983876
            </div>
            <div class="contact-item">
                <strong>✉️ Email</strong>
                lungmalimbu05@gmail.com
            </div>
        </div>
    </div>
</div>

<div id="authModal" class="modal">
    <div class="modal-content">
        <span class="close-btn" onclick="closeAuthModal()">&times;</span>
        <div id="loginSection">
            <h3>Login</h3>
            <input type="text" id="loginPhno" class="auth-input" placeholder="Phone Number">
            <input type="password" id="loginPass" class="auth-input" placeholder="Password">
            <button onclick="handleAuth('login')" style="width:100%; padding:12px; background:#2ecc71; color:white; border:none; border-radius:6px; cursor:pointer;">Login</button>
            <p style="font-size: 0.9rem; margin-top: 15px;">New player? <span class="auth-toggle" onclick="toggleAuth()">Signup</span></p>
        </div>
        <div id="signupSection" class="hidden">
            <h3>Signup</h3>
            <input type="text" id="regName" class="auth-input" placeholder="Full Name">
            <input type="email" id="regEmail" class="auth-input" placeholder="Email">
            <input type="password" id="regPass" class="auth-input" placeholder="Password">
            <input type="text" id="regPhone" class="auth-input" placeholder="Phone Number">
            <button onclick="handleAuth('register')" style="width:100%; padding:12px; background:#3498db; color:white; border:none; border-radius:6px; cursor:pointer;">Sign Up</button>
        </div>
        <div id="authError" style="color:red; margin-top:10px; font-size: 0.85rem; text-align: center;"></div>
    </div>
</div>

<script>
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
            if (text.trim() === 'success') { window.location.reload(); } 
            else { errorDiv.innerText = text; }
        });
}
</script>
</body>
</html>