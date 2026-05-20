<?php
include "conn.php";
$message      = "";
$status_class = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message      = "Please enter a valid email address.";
        $status_class = "alert-danger";
    } else {
        // Check if booking exists
        $stmt = $conn->prepare("SELECT * FROM bookings WHERE email = ? LIMIT 1");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // Archive to deleted_bookings using correct column names
            $copySql = "INSERT INTO deleted_bookings
                            (name, email, address, room_type, checkin_date, checkout_date, deletion_date, deleted_at)
                        SELECT name, email, address, room_type, checkin, checkout, NOW(), NOW()
                        FROM bookings WHERE email = ? LIMIT 1";
            $stmt_copy = $conn->prepare($copySql);
            $stmt_copy->bind_param("s", $email);

            if ($stmt_copy->execute()) {
                $stmt_del = $conn->prepare("DELETE FROM bookings WHERE email = ? LIMIT 1");
                $stmt_del->bind_param("s", $email);
                $stmt_del->execute();
                $message      = "Your reservation has been successfully cancelled.";
                $status_class = "alert-success";
            } else {
                $message      = "An error occurred while cancelling your booking. Please try again.";
                $status_class = "alert-danger";
                error_log("Cancel booking error: " . $stmt_copy->error);
            }
        } else {
            $message      = "No reservation found with that email address.";
            $status_class = "alert-danger";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cancel Reservation - Avianna's Inland Resort</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root { --dark: #1e4d40; }
        body { background: #f8fafc; display: flex; align-items: center; min-height: 100vh; }
        .cancel-card {
            max-width: 450px; width: 90%; margin: auto;
            background: white; padding: 40px;
            border-radius: 15px; box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }
        .btn-cancel {
            background: #e53e3e; color: white; border: none;
            padding: 12px; width: 100%; border-radius: 8px; font-weight: bold;
            transition: background 0.3s ease; cursor: pointer;
        }
        .btn-cancel:hover { background: #c53030; }
    </style>
</head>
<body>
    <div class="cancel-card text-center">
        <h2 class="fw-bold mb-3" style="color: var(--dark);">Cancel Booking</h2>
        <p class="text-muted mb-4">Enter the email address used during your reservation to cancel your stay.</p>

        <?php if ($message): ?>
            <div class="alert <?php echo $status_class; ?> small"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <?php if ($status_class !== 'alert-success'): ?>
        <form method="POST">
            <div class="mb-3 text-start">
                <label class="form-label fw-bold small">Email Address</label>
                <input type="email" name="email" class="form-control" placeholder="example@mail.com" required>
            </div>
            <button type="submit" class="btn-cancel mb-3"
                    onclick="return confirm('Are you sure you want to cancel your reservation? This cannot be undone.');">
                Confirm Cancellation
            </button>
        </form>
        <?php endif; ?>

        <a href="index.php" class="text-decoration-none small text-secondary d-block mt-2">← Back to Home</a>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
