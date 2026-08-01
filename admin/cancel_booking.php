<?php
session_start();
include "../conn.php"; 

// Check if user is logged in as admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    exit("Unauthorized Access");
}

$error_message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['booking_id'])) {
    $id = intval($_POST['booking_id']);

    /**
     * 0. Fetch guest details first so we can notify them after archiving
     */
    $guest = null;
    $lookup = $conn->prepare("SELECT name, email, room_type, checkin_date, checkout_date FROM bookings WHERE id = ?");
    $lookup->bind_param("i", $id);
    $lookup->execute();
    $guest = $lookup->get_result()->fetch_assoc();
    $lookup->close();

    /**
     * 1. Copy data to history table (deleted_bookings)
     */
    $copySql = "INSERT INTO deleted_bookings (name, email, address, room_type, checkin_date, checkout_date, deletion_date) 
                SELECT name, email, address, room_type, checkin_date, checkout_date, NOW() 
                FROM bookings WHERE id = ?";
    
    $stmt = $conn->prepare($copySql);
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        // 2. Delete the record from the active bookings table
        $delStmt = $conn->prepare("DELETE FROM bookings WHERE id = ?");
        $delStmt->bind_param("i", $id);
        
        if ($delStmt->execute()) {
            // 3. Notify the guest that their booking was cancelled
            if ($guest && !empty($guest['email'])) {
                $to = $guest['email'];
                $subject = "Reservation Cancelled - Avianna's Inland Resort";

                $headers  = "MIME-Version: 1.0\r\n";
                $headers .= "Content-type:text/html;charset=UTF-8\r\n";
                $headers .= "From: reservations@aviannasresort.com\r\n";

                $message = "
                <html>
                <body style='font-family: Arial, sans-serif; color:#2d3748;'>
                    <h2 style='color:#c53030;'>Reservation Cancelled</h2>
                    <p>Dear " . htmlspecialchars($guest['name']) . ",</p>
                    <p>This is to confirm that your reservation has been cancelled and archived. Details below:</p>
                    <table style='border-collapse: collapse; margin-top:10px;'>
                        <tr><td style='padding:4px 10px;font-weight:bold;'>Room Type:</td><td style='padding:4px 10px;'>" . htmlspecialchars($guest['room_type']) . "</td></tr>
                        <tr><td style='padding:4px 10px;font-weight:bold;'>Check-in:</td><td style='padding:4px 10px;'>" . htmlspecialchars($guest['checkin_date']) . "</td></tr>
                        <tr><td style='padding:4px 10px;font-weight:bold;'>Check-out:</td><td style='padding:4px 10px;'>" . htmlspecialchars($guest['checkout_date']) . "</td></tr>
                    </table>
                    <p style='margin-top:15px;'>If this was a mistake or you'd like to rebook, please contact us directly.</p>
                </body>
                </html>";

                mail($to, $subject, $message, $headers);
            }

            header("Location: admin.php?cancel=success");
            exit();
        } else {
            $error_message = "Error removing active booking: " . $conn->error;
        }
    } else {
        $error_message = "Error moving to cancellation history: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Processing Cancellation - Avianna's</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f4f7f6;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .process-card {
            background: white;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            text-align: center;
            max-width: 450px;
            width: 90%;
        }
        .loader {
            border: 4px solid #f3f3f3;
            border-top: 4px solid #dc3545;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            margin: 0 auto 20px;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        .btn-return {
            background-color: #1e4d40;
            color: white;
            border-radius: 10px;
            padding: 10px 25px;
            text-decoration: none;
            transition: opacity 0.3s;
        }
        .btn-return:hover {
            color: white;
            opacity: 0.9;
        }
    </style>
</head>
<body>

<div class="process-card">
    <?php if ($error_message): ?>
        <div class="text-danger mb-4">
            <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" fill="currentColor" class="bi bi-exclamation-triangle-fill mb-3" viewBox="0 0 16 16">
                <path d="M8.982 1.566a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767L8.982 1.566zM8 5c.535 0 .954.462.9.995l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 5.995A.905.905 0 0 1 8 5zm.002 6a1 1 0 1 1 0 2 1 1 0 0 1 0-2z"/>
            </svg>
            <h4>Processing Error</h4>
            <p class="text-muted"><?php echo $error_message; ?></p>
        </div>
        <a href="admin.php" class="btn-return">Return to Dashboard</a>
    <?php else: ?>
        <div class="loader"></div>
        <h4>Archiving Booking</h4>
        <p class="text-muted small">Moving record to cancellation history...</p>
        <script>
            // Fallback redirect if header fails
            setTimeout(function(){ window.location.href = 'admin.php'; }, 2000);
        </script>
    <?php endif; ?>
</div>

</body>
</html>