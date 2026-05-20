<?php
// cancel_booking.php — Admin: archive a booking to deleted_bookings
session_start();
include "../conn.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    exit("Unauthorized Access");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['booking_id'])) {
    $id = intval($_POST['booking_id']);

    // Copy to archive — include all guest/booking fields so history shows full detail
    $copySql = "INSERT INTO deleted_bookings
                    (name, email, contact, address, room_type, cottage_type, pax,
                     payment_method, total_price,
                     checkin_date, checkout_date, deletion_date, deleted_at)
                SELECT name, email, contact, address, room_type, cottage_type, pax,
                       payment_method, total_price,
                       checkin, checkout, NOW(), NOW()
                FROM bookings WHERE id = ?";
    $stmt = $conn->prepare($copySql);
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        $delStmt = $conn->prepare("DELETE FROM bookings WHERE id = ?");
        $delStmt->bind_param("i", $id);
        if ($delStmt->execute()) {
            header("Location: admin.php?cancel=success");
            exit();
        } else {
            $error = "Error removing booking: " . $conn->error;
        }
    } else {
        $error = "Error archiving booking: " . $conn->error;
    }
} else {
    header("Location: admin.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Error - Avianna's Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background:#f4f7f6; height:100vh; display:flex; align-items:center; justify-content:center; font-family:'Segoe UI',sans-serif; }
        .card { padding:40px; border-radius:20px; box-shadow:0 15px 35px rgba(0,0,0,0.1); text-align:center; max-width:420px; width:90%; border:none; }
    </style>
</head>
<body>
<div class="card">
    <div class="text-danger mb-3" style="font-size:3rem;">⚠️</div>
    <h4>Archive Error</h4>
    <p class="text-muted"><?= htmlspecialchars($error ?? 'Unknown error.') ?></p>
    <a href="admin.php" class="btn btn-dark mt-2">Return to Dashboard</a>
</div>
</body>
</html>
