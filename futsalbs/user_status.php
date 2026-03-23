<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Check Booking Status</title>
    <style>
        body { font-family: sans-serif; background: #f4f7f6; display: flex; justify-content: center; padding-top: 50px; }
        .search-container { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); width: 400px; }
        input { width: 100%; padding: 10px; margin: 10px 0; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        button { width: 100%; padding: 10px; background: #007bff; color: white; border: none; cursor: pointer; border-radius: 4px; }
        #results { margin-top: 20px; }
        .booking-card { border-bottom: 1px solid #eee; padding: 10px 0; }
        .status-Pending { color: orange; font-weight: bold; }
        .status-Confirmed { color: green; font-weight: bold; }
        .status-Pending { color: #f6ad55; font-weight: bold; }
.status-Confirmed { color: #48bb78; font-weight: bold; }

.booking-card { 
    background: #fdfdfd; 
    border: 1px solid #edf2f7; 
    padding: 15px; 
    margin-bottom: 10px; 
    border-radius: 6px; 
}
    </style>
</head>
<body>

<div class="search-container">
    <h3>Check Your Booking</h3>
    <p>Enter your name to see your current status.</p>
    <input type="text" id="search_name" placeholder="Enter Full Name">
    <button onclick="checkStatus()">Search Status</button>

    <div id="results"></div>
</div>

<script>
function checkStatus() {
    const name = document.getElementById('search_name').value;
    if(name == "") return alert("Please enter a name.");

    const xhr = new XMLHttpRequest();
    xhr.open("POST", "fetch_user_status.php", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xhr.onload = function() {
        if (this.status === 200) {
            document.getElementById('results').innerHTML = this.responseText;
        }
    };
    xhr.send("user_name=" + encodeURIComponent(name));
}
</script>

</body>
</html>