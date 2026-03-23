<?php
session_start();
require_once 'db_connect.php';
$isLoggedIn = isset($_SESSION['user_id']) ? 'true' : 'false';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Futsal Arena | Live Booking</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .availability-section { margin-bottom: 30px; padding: 20px; background: #fff; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        .availability-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(130px, 1fr)); gap: 12px; margin-top: 15px; }
        .slot-card { padding: 15px; border-radius: 8px; text-align: center; font-weight: bold; border: 2px solid #ddd; transition: 0.2s; }
        .status-available { background: #27ae60; color: white; border-color: #219150; cursor: pointer; }
        .status-booked { background: #e74c3c; color: white; border-color: #c0392b; cursor: not-allowed; opacity: 0.8; }
        
        /* Auth Modal Styles */
        .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.7); backdrop-filter: blur(4px); }
        .modal-content { background: white; margin: 10% auto; padding: 30px; border-radius: 12px; width: 360px; position: relative; }
        .close-btn { position: absolute; right: 20px; top: 15px; cursor: pointer; font-size: 24px; color: #666; }
        .auth-input { width: 100%; margin-bottom: 15px; padding: 12px; border: 1px solid #ddd; border-radius: 6px; box-sizing: border-box; }
        .auth-toggle { color: #3498db; cursor: pointer; font-weight: bold; }
        .hidden { display: none; }

        /* Payment Modal Specific Styles */
        #paymentModal .modal-inner { background: white; width: 90%; max-width: 400px; margin: 10% auto; padding: 25px; border-radius: 12px; text-align: center; position: relative; box-shadow: 0 10px 30px rgba(0,0,0,0.3);
    .modal {
    display: none;
    align-items: center;
    justify-content: center;
} }
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
            <a href="profile.php" style="text-decoration: none; color: white;">Hi, <strong><?php echo htmlspecialchars($_SESSION['user_name']); ?></strong> (View History) </a>
            <a href="logout.php" style="color: #e74c3c; margin-left: 15px; border: 1px solid #e74c3c;">Logout</a>
        <?php else: ?>
            <a href="javascript:void(0)" onclick="openAuthModal()">Login / Signup</a>
        <?php endif; ?>
    </div>
</div>

<div class="container">
    <div class="availability-section">
        <h3>Live Court Status</h3>
        <p style="font-size: 0.9rem; color: #666;">Green = Available | Red = Booked</p>
        <div id="availabilityGrid" class="availability-grid"><p>Loading slots...</p></div>
    </div>

    <div class="booking-card">
        <h2>Reserve Your Match</h2>
        <form id="bookingForm">
            <div class="form-group">
                <label>Select Court</label>
                <select id="court_id" onchange="loadAvailability()" required>
                    <?php
                    $courts = $conn->query("SELECT * FROM courts");
                    while($c = $courts->fetch_assoc()) {
                        echo "<option value='{$c['id']}'>".htmlspecialchars($c['name'])." (Rs. {$c['price_per_hour']})</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="form-group">
                <label>Date</label>
                <input type="date" id="booking_date" value="<?php echo date('Y-m-d'); ?>" min="<?php echo date('Y-m-d'); ?>" onchange="loadAvailability()" required>
            </div>
            <div class="form-group">
                <label>Time Slot</label>
                <select id="start_time" required>
                    <option value="07:00:00">07:00 AM - 08:00 AM</option>
                    <option value="08:00:00">08:00 AM - 09:00 AM</option>
                    <option value="09:00:00">09:00 AM - 10:00 AM</option>
                    <option value="10:00:00">10:00 AM - 11:00 AM</option>
                    <option value="11:00:00">11:00 AM - 12:00 PM</option>
                    <option value="12:00:00">12:00 PM - 01:00 PM</option>
                    <option value="13:00:00">01:00 PM - 02:00 PM</option>
                    <option value="14:00:00">02:00 PM - 03:00 PM</option>
                    <option value="15:00:00">03:00 PM - 04:00 PM</option>
                    <option value="16:00:00">04:00 PM - 05:00 PM</option>
                    <option value="17:00:00">05:00 PM - 06:00 PM</option>
                    <option value="18:00:00">06:00 PM - 07:00 PM</option>
                    <option value="19:00:00">07:00 PM - 08:00 PM</option>
                    <option value="20:00:00">08:00 PM - 09:00 PM</option>
                </select>
            </div>
            <button type="submit" class="submit-btn" style="width: 100%; padding: 15px; background: #28a745; color: white; border: none; border-radius: 8px; font-size: 1.1rem; cursor: pointer; font-weight: bold;">Book & Pay Now</button>
        </form>
        <div id="response" style="margin-top: 15px; text-align: center;"></div>
    </div>
</div>

<div id="authModal" class="modal">
    <div class="modal-content">
        <span class="close-btn" onclick="closeAuthModal()">&times;</span>
        <div id="loginSection">
            <h3 style="margin-top: 0;">Login to Book</h3>
            <input type="text" id="loginPhno" class="auth-input" placeholder="Phone Number (e.g. 98XXXXXXXX)" required>
            <input type="password" id="loginPass" class="auth-input" placeholder="Password">
            <button onclick="handleAuth('login')" style="width:100%; padding:12px; background:#2ecc71; color:white; border:none; border-radius:6px; cursor:pointer;">Login</button>
            <p style="font-size: 0.9rem; margin-top: 15px;">New player? <span class="auth-toggle" onclick="toggleAuth()">Create account</span></p>
        </div>
        <div id="signupSection" class="hidden">
            <h3 style="margin-top: 0;">Register Player</h3>
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

<div id="paymentModal" class="modal" style="display:none;">
    <div class="modal-inner">
        <h2 class="modal-title">Slot Reserved!</h2>
        <p class="modal-booking-text">Booking ID: <strong id="displayBookingId"></strong></p>
        
        <div class="qr-box">
            <p class="qr-label">Scan to Pay</p>
            <img src="qr_code.png" alt="Payment QR" class="qr-image">
        </div>

        <form id="paymentUploadForm">
            <input type="hidden" id="modalBookingId" name="booking_id">
            
            <label for="screenshotFile" class="upload-label">Upload Payment Screenshot:</label>
            <input type="file" name="screenshot" id="screenshotFile" accept="image/*" required class="file-input">
            
            <button type="button" onclick="uploadReceipt()" class="upload-btn">
                Confirm & Upload
            </button>
        </form>

        <div class="modal-actions">
            <button type="button" class="cancel-btn" onclick="closePaymentModal()">Cancel</button>
            <a href="index.php" class="pay-later-link">Pay later and continue &rarr;</a>
        </div>
    </div>
</div>

<script>
const isLoggedIn = <?php echo $isLoggedIn; ?>;

function loadAvailability() {
    const courtId = document.getElementById('court_id').value;
    const date = document.getElementById('booking_date').value;
    const grid = document.getElementById('availabilityGrid');
    
    fetch(`get_availability.php?court_id=${courtId}&date=${date}`)
        .then(res => res.json())
        .then(data => {
            grid.innerHTML = ''; 
           data.forEach(slot => {
    const div = document.createElement('div');
    
    // Only 'Booked' and 'Pending' will show as Red
    const isUnavailable = (slot.status === 'Booked' || slot.status === 'Pending');
    
    div.className = `slot-card ${isUnavailable ? 'status-booked' : 'status-available'}`;
    div.innerHTML = `${slot.time_label}<br><span style="font-size:0.7rem;">${slot.status.toUpperCase()}</span>`;

    // If it's Available (even if it was 'Completed' in the DB), make it clickable
    if (!isUnavailable) {
        div.onclick = () => selectTimeSlot(slot.raw_time);
    }

    grid.appendChild(div);
});
        })
        .catch(err => { 
            console.error(err);
            grid.innerHTML = '<p>Error loading live status.</p>'; 
        });
}
window.onload = loadAvailability;

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
            if (text.trim() === 'success') { location.reload(); } 
            else { errorDiv.innerText = text; }
        });
}

// Booking Logic
document.getElementById('bookingForm').addEventListener('submit', function(e) {
    e.preventDefault();
    if (!isLoggedIn) { openAuthModal(); return; }
    
    const responseDiv = document.getElementById('response');
    responseDiv.innerHTML = "Processing...";
    
    const formData = new URLSearchParams();
    formData.append('court_id', document.getElementById('court_id').value);
    formData.append('booking_date', document.getElementById('booking_date').value);
    formData.append('start_time', document.getElementById('start_time').value);

    fetch('book.php', { method: 'POST', body: formData })
        .then(res => res.text())
        .then(res => {
            if (res.includes("SUCCESS_ID:")) {
                const bookingId = res.split(":")[1];
                responseDiv.innerHTML = "<span style='color:green;'>Booking Successful! Please complete payment below.</span>";
                showPaymentModal(bookingId);
            } else { 
                responseDiv.innerHTML = res; 
            }
        });
});

// Modal Logic
function showPaymentModal(bookingId) {
    document.getElementById('displayBookingId').innerText = "#" + bookingId;
    document.getElementById('modalBookingId').value = bookingId;
    document.getElementById('paymentModal').style.display = 'block';
}

function uploadReceipt() {
    const fileInput = document.getElementById('screenshotFile');
    if (fileInput.files.length === 0) {
        alert("Please select a screenshot first.");
        return;
    }

    const formData = new FormData();
    formData.append('booking_id', document.getElementById('modalBookingId').value);
    formData.append('screenshot', fileInput.files[0]);

    fetch('upload_handler.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.text())
    .then(data => {
        if(data.trim() === 'success') {
            alert("Payment proof uploaded successfully!");
            window.location.href = "index.php"; 
        } else {
            alert("Upload failed: " + data);
        }
    });
}

function selectTimeSlot(time) {
    document.getElementById('start_time').value = time;
    document.querySelector('.booking-card').scrollIntoView({ behavior: 'smooth' });
}
function closePaymentModal() {
    const bookingId = document.getElementById('modalBookingId').value;

    if (bookingId) {
        // Send request to delete the booking
        fetch('cancel_booking.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `booking_id=${bookingId}`
        })
        .then(res => res.text())
        .then(data => {
            if (data.trim() === 'success') {
                // Hide modal and refresh the grid to show slot as Green/Available
                document.getElementById("paymentModal").style.display = "none";
                document.getElementById('response').innerHTML = "<span style='color:orange;'>Booking cancelled.</span>";
                loadAvailability(); 
            } else {
                alert("Error cancelling booking: " + data);
            }
        });
    } else {
        document.getElementById("paymentModal").style.display = "none";
    }
}
</script>
</body>
</html>