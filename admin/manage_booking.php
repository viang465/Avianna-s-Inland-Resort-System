<?php
session_start();
include "../conn.php"; //

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Fetch guest details for the email
    $stmt = $conn->prepare("SELECT name, email, room_type, checkin_date FROM bookings WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $guest = $stmt->get_result()->fetch_assoc();

    if ($guest) {
        // 2. Update status to Approved
        $update = $conn->prepare("UPDATE bookings SET status='Approved' WHERE id=?");
        $update->bind_param("i", $id);
        
        if ($update->execute()) {
            // 3. Automated Email Notification
            $to = $guest['email'];
            $subject = "Reservation Approved - Avianna's Inland Resort";
            
            $headers = "MIME-Version: 1.0" . "\r\n";
            $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
            $headers .= "From: reservations@aviannasresort.com" . "\r\n";

            $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
            $baseUrl  = $protocol . $_SERVER['HTTP_HOST'] . dirname(dirname($_SERVER['SCRIPT_NAME']));
            $confirmationLink = $baseUrl . '/confirmation.php?id=' . $id;

            $message = "
            <html>
            <body style='font-family: Arial, sans-serif;'>
                <h2>Reservation Confirmed!</h2>
                <p>Dear " . htmlspecialchars($guest['name']) . ",</p>
                <p>Your booking for a <strong>" . htmlspecialchars($guest['room_type']) . "</strong> on <strong>" . $guest['checkin_date'] . "</strong> has been officially approved.</p>
                <p>You can view or print your booking confirmation here: <a href='" . $confirmationLink . "'>View Confirmation</a></p>
                <p>We look forward to welcoming you to Avianna's Inland Resort!</p>
            </body>
            </html>";

            mail($to, $subject, $message, $headers);

            header("Location: admin.php?approve=success");
            exit();
        }
    }
}

// Re-fetch for display
$stmt = $conn->prepare("SELECT * FROM bookings WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$booking = $stmt->get_result()->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Confirm Approval</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="setup-card" style="max-width:500px; margin:50px auto; text-align:center; padding:30px; border:1px solid #ddd;">
        <h2>Approve Guest</h2>
        <p>Confirming this will send an email to: <br><strong><?php echo htmlspecialchars($booking['email']); ?></strong></p>
        <form method="POST">
            <button type="submit" style="background:#27ae60; color:white; padding:10px 20px; border:none; border-radius:5px; cursor:pointer; width:100%;">
                Confirm and Notify Guest
            </button>
            <br><br>
            <a href="admin.php">Back to Dashboard</a>
        </form>
    </div>
</body>
</html>