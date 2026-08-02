<?php
session_start();
include "../conn.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'reception') {
    header("Location: login.php");
    exit();
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $postedId = intval($_POST['booking_id'] ?? 0);

    $stmt = $conn->prepare("SELECT name, email, room_type, checkin_date FROM bookings WHERE id = ?");
    $stmt->bind_param("i", $postedId);
    $stmt->execute();
    $guest = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($guest) {
        $update = $conn->prepare("UPDATE bookings SET status='Approved' WHERE id=?");
        $update->bind_param("i", $postedId);

        if ($update->execute()) {
            $to = $guest['email'];
            $subject = "Reservation Confirmed - Avianna's Inland Resort";

            $headers  = "MIME-Version: 1.0\r\n";
            $headers .= "Content-type:text/html;charset=UTF-8\r\n";
            $headers .= "From: reservations@aviannasresort.com\r\n";

            $message = "
            <html>
            <body style='font-family: Arial, sans-serif;'>
                <h2>Reservation Confirmed!</h2>
                <p>Dear " . htmlspecialchars($guest['name']) . ",</p>
                <p>Our front desk team has confirmed your booking for a <strong>" . htmlspecialchars($guest['room_type']) . "</strong> on <strong>" . htmlspecialchars($guest['checkin_date']) . "</strong>.</p>
                <p>We look forward to welcoming you to Avianna's Inland Resort!</p>
            </body>
            </html>";

            mail($to, $subject, $message, $headers);
        }
        $update->close();
    }

    header("Location: dashboard.php?confirmed=success");
    exit();
}

$stmt = $conn->prepare("SELECT * FROM bookings WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$booking = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$booking) {
    header("Location: dashboard.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirm Customer | Avianna's Reception</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f4f7f6;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
        }
        .confirm-card {
            background: white;
            max-width: 480px;
            width: 90%;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.08);
            text-align: center;
        }
        .confirm-card h2 { color: #1e4d40; font-weight: 700; margin-bottom: 10px; }
        .detail-table { text-align: left; margin: 20px 0; width: 100%; }
        .detail-table td { padding: 6px 0; }
        .detail-table td:first-child { font-weight: 600; color: #1e4d40; width: 40%; }
        .btn-confirm {
            background-color: #27ae60;
            color: white;
            border: none;
            border-radius: 8px;
            padding: 12px 20px;
            font-weight: 600;
            width: 100%;
        }
        .btn-confirm:hover { background-color: #219150; color: white; }
    </style>
</head>
<body>

<div class="confirm-card">
    <h2>Confirm Customer</h2>
    <p class="text-muted">Confirming will notify the guest by email.</p>

    <table class="detail-table">
        <tr><td>Guest</td><td><?php echo htmlspecialchars($booking['name']); ?></td></tr>
        <tr><td>Email</td><td><?php echo htmlspecialchars($booking['email']); ?></td></tr>
        <tr><td>Room Type</td><td><?php echo htmlspecialchars($booking['room_type']); ?></td></tr>
        <tr><td>Check-in</td><td><?php echo date('M d, Y', strtotime($booking['checkin_date'])); ?></td></tr>
        <tr><td>Check-out</td><td><?php echo date('M d, Y', strtotime($booking['checkout_date'])); ?></td></tr>
    </table>

    <form method="POST">
        <input type="hidden" name="booking_id" value="<?php echo (int)$booking['id']; ?>">
        <button type="submit" class="btn-confirm">Confirm and Notify Guest</button>
    </form>
    <br>
    <a href="dashboard.php" class="text-muted small">Cancel, back to dashboard</a>
</div>

</body>
</html>