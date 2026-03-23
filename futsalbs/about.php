<?php
session_start();
require_once 'db_connect.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us | Futsal Arena</title>
    <link rel="stylesheet" href="style.css">
    <style>
        /* About Page Specific Styles */
        .about-section { max-width: 1000px; margin: 50px auto; padding: 20px; }
        .about-hero { background: linear-gradient(135deg, #e8f5e9, #f4fff6); border-radius: 18px; padding: 40px; box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08); margin-bottom: 35px; }
        .about-hero h1 { font-size: 2.5rem; margin-bottom: 15px; color: #1b4332; }
        .about-hero p { font-size: 1.05rem; line-height: 1.8; color: #444; max-width: 750px; }
        .about-image { width: 100%; height: 400px; object-fit: cover; border-radius: 18px; margin: 30px 0; box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1); }
        .about-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 24px; margin-top: 20px; }
        .info-card { background: #ffffff; border: 1px solid #e6efe7; border-radius: 16px; padding: 28px; box-shadow: 0 6px 18px rgba(0, 0, 0, 0.06); transition: transform 0.2s ease, box-shadow 0.2s ease; }
        .info-card:hover { transform: translateY(-4px); box-shadow: 0 10px 22px rgba(0, 0, 0, 0.08); }
        .info-card h3 { margin-bottom: 12px; color: #2d6a4f; font-size: 1.25rem; }
        .info-card p { color: #555; line-height: 1.7; }
        .highlight-box { background: #e8f5e9; border-left: 5px solid #2d6a4f; padding: 28px; border-radius: 14px; margin-top: 35px; box-shadow: 0 6px 18px rgba(0, 0, 0, 0.05); }
        .highlight-box h3 { margin-bottom: 10px; color: #1b4332; }
        .highlight-box p { color: #444; line-height: 1.8; }
        @media (max-width: 768px) { .about-hero { padding: 28px 22px; } .about-hero h1 { font-size: 2rem; } .about-image { height: 260px; } }

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

<div class="container">
    <section class="about-section">
        <div class="about-hero">
            <h1>Our Story</h1>
            <p>Founded in 2026, Futsal Arena was created by passionate sports enthusiasts who wanted to build more than just a playing space...</p>
        </div>

        <img src="futsalimg.png" class="about-image" alt="Futsal Court">

        <div class="about-grid">
            <div class="info-card">
                <h3>Who We Are</h3>
                <p>We are a community-driven futsal space dedicated to bringing players of all ages together.</p>
            </div>
            <div class="info-card">
                <h3>What We Offer</h3>
                <p>Our arena provides modern booking convenience and quality playing facilities.</p>
            </div>
            <div class="info-card">
                <h3>Why It Matters</h3>
                <p>We believe futsal builds teamwork, discipline, and community.</p>
            </div>
        </div>

        <div class="highlight-box">
            <h3>Our Mission</h3>
            <p>To provide high-quality, accessible, and safe sports facilities that encourage an active lifestyle.</p>
        </div>
    </section>
</div>

<div id="authModal" class="modal">
    <div class="modal-content">
        <span class="close-btn" onclick="closeAuthModal()">&times;</span>
        <div id="loginSection">
            <h3>Login</h3>
            <input type="text" id="loginPhno" class="auth-input" placeholder="Phone Number">
            <input type="password" id="loginPass" class="auth-input" placeholder="Password">
            <button onclick="handleAuth('login')" style="width:100%; padding:12px; background:#2ecc71; color:white; border:none; border-radius:6px; cursor:pointer;">Login</button>
            <p style="font-size: 0.9rem; margin-top: 15px;">New? <span class="auth-toggle" onclick="toggleAuth()">Signup</span></p>
        </div>
        <div id="signupSection" class="hidden">
            <h3>Signup</h3>
            <input type="text" id="regName" class="auth-input" placeholder="Full Name">
            <input type="email" id="regEmail" class="auth-input" placeholder="Email">
            <input type="password" id="regPass" class="auth-input" placeholder="Password">
            <input type="text" id="regPhone" class="auth-input" placeholder="Phone Number">
            <button onclick="handleAuth('register')" style="width:100%; padding:12px; background:#3498db; color:white; border:none; border-radius:6px; cursor:pointer;">Sign Up</button>
            <p style="font-size: 0.9rem; margin-top: 15px;">Have an account? <span class="auth-toggle" onclick="toggleAuth()">Login</span></p>
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