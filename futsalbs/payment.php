<?php
session_start();
require_once 'db_connect.php';

$booking_id = isset($_GET['booking_id']) ? intval($_GET['booking_id']) : 0;
if ($booking_id == 0) {
    header("Location: index.php");
    exit();
}

// 1. Fetch price from database
$stmt = $conn->prepare("SELECT b.*, c.price_per_hour FROM bookings b 
                        JOIN courts c ON b.court_id = c.id 
                        WHERE b.id = ?");
$stmt->bind_param("i", $booking_id);
$stmt->execute();
$booking_data = $stmt->get_result()->fetch_assoc();

// 2. AUTO-DETECT QR IMAGE (Fixes the broken icon)
$extensions = ['jpeg', 'jpg', 'png', 'JPEG', 'JPG', 'PNG'];
$qr_path = "futsalbs/qr.jpeg"; // Default path
foreach ($extensions as $ext) {
    if (file_exists("futsalbs/qr.$ext")) {
        $qr_path = "futsalbs/qr.$ext";
        break;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Payment | Futsal Arena</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f4f7f6; display: flex; justify-content: center; padding: 40px; }
        .card { background: white; padding: 30px; border-radius: 15px; box-shadow: 0 8px 30px rgba(0,0,0,0.1); text-align: center; width: 400px; }
        .qr-section { background: #fafafa; border: 2px dashed #ddd; padding: 20px; border-radius: 10px; margin: 20px 0; min-height: 200px; }
        .qr-image { width: 100%; max-width: 250px; height: auto; display: block; margin: 0 auto; }
        .error-msg { color: #e74c3c; background: #fff5f5; padding: 15px; border: 1px solid #e74c3c; border-radius: 5px; font-size: 0.85rem; }
        .price { font-size: 1.5rem; color: #2ecc71; font-weight: bold; margin: 15px 0; }
        .btn { background: #2ecc71; color: white; border: none; padding: 12px; width: 100%; border-radius: 6px; font-weight: bold; cursor: pointer; }
    </style>
</head>
<body>

<div class="card">
    <h2>Booking #<?php echo $booking_id; ?></h2>
    
    <div class="qr-section">
        <?php if ($qr_path != ""): ?>
            <img src="<?php echo $qr_path; ?>?v=<?php echo time(); ?>" alt="QR Code" class="qr-image">
        <?php else: ?>
            <div class="error-msg">
                <strong>QR IMAGE NOT FOUND!</strong><br>
                Please put your photo in the <b>futsalbs</b> folder and rename it to <b>qr.jpeg</b>
            </div>
        <?php endif; ?>
    </div>

    <div class="price">Rs. <?php echo number_format($booking_data['price_per_hour'] ?? 0, 2); ?></div>

    <form action="upload_handler.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="booking_id" value="<?php echo $booking_id; ?>">
        <p style="text-align: left; font-size: 0.9rem;"><b>Step 2:</b> Upload Screenshot</p>
        <input type="file" name="screenshot" accept="image/*" required style="width: 100%; margin-bottom: 20px;">
        <button type="submit" class="btn">Confirm Payment</button>
    </form>
</div>

</body>
</html>