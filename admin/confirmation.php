<?php
include "conn.php"; // adjust path if this file is placed in a different folder

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$booking = null;

if ($id > 0) {
    $stmt = $conn->prepare("SELECT * FROM bookings WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $booking = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

if (!$booking) {
    http_response_code(404);
}

// Simple reference code so guests have something to quote at the front desk
$refCode = $booking ? 'AIR-' . str_pad($booking['id'], 5, '0', STR_PAD_LEFT) : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Confirmation | Avianna's Inland Resort</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary-green: #1e4d40;
            --accent-teal: #2c7a7b;
            --bg-light: #f4f7f6;
        }
        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg-light);
            margin: 0;
            padding: 40px 15px;
        }
        .receipt-card {
            max-width: 650px;
            margin: 0 auto;
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
            overflow: hidden;
        }
        .receipt-header {
            background: linear-gradient(135deg, var(--primary-green), #0a1a16);
            color: #fff;
            padding: 30px;
            text-align: center;
        }
        .receipt-header h2 { margin: 0; font-weight: 700; }
        .receipt-header .ref { color: #9be3d4; letter-spacing: 1px; font-size: 0.9rem; margin-top: 6px; }
        .status-pill {
            display: inline-block;
            margin-top: 14px;
            padding: 6px 18px;
            border-radius: 50px;
            font-weight: 600;
            font-size: 0.85rem;
        }
        .status-Approved, .status-Booked { background: #eefdf5; color: #27ae60; border: 1px solid #c6f6d5; }
        .status-Pending { background: #fffbea; color: #b7791f; border: 1px solid #fbd38d; }
        .receipt-body { padding: 30px; }
        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid #f1f1f1;
        }
        .detail-row:last-child { border-bottom: none; }
        .detail-label { color: #718096; font-weight: 500; }
        .detail-value { color: #2d3748; font-weight: 600; text-align: right; }
        .total-row { background: #f4f7f6; border-radius: 10px; padding: 16px 20px; margin-top: 10px; }
        .total-row .detail-value { color: var(--primary-green); font-size: 1.2rem; }
        .actions { padding: 0 30px 30px; display: flex; gap: 10px; }
        .btn-print { background: var(--primary-green); color: #fff; border: none; border-radius: 8px; padding: 10px 22px; font-weight: 600; }
        .btn-print:hover { background: var(--accent-teal); color: #fff; }

        @media print {
            body { background: #fff; padding: 0; }
            .no-print { display: none !important; }
            .receipt-card { box-shadow: none; }
        }
    </style>
</head>
<body>

<div class="receipt-card">
    <?php if ($booking): ?>
        <div class="receipt-header">
            <h2>Booking Confirmation</h2>
            <div class="ref">Reference No. <?php echo $refCode; ?></div>
            <div class="status-pill status-<?php echo htmlspecialchars($booking['status'] ?? 'Pending'); ?>">
                <?php echo htmlspecialchars($booking['status'] ?? 'Pending'); ?>
            </div>
        </div>
        <div class="receipt-body">
            <div class="detail-row">
                <span class="detail-label">Guest Name</span>
                <span class="detail-value"><?php echo htmlspecialchars($booking['name']); ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Email</span>
                <span class="detail-value"><?php echo htmlspecialchars($booking['email']); ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Room Type</span>
                <span class="detail-value"><?php echo htmlspecialchars($booking['room_type']); ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Check-in</span>
                <span class="detail-value"><?php echo date('M d, Y', strtotime($booking['checkin_date'])); ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Check-out</span>
                <span class="detail-value"><?php echo date('M d, Y', strtotime($booking['checkout_date'])); ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Payment Method</span>
                <span class="detail-value"><?php echo htmlspecialchars($booking['payment_method'] ?? 'N/A'); ?></span>
            </div>
            <div class="detail-row total-row">
                <span class="detail-label">Total Amount</span>
                <span class="detail-value">₱<?php echo number_format($booking['total_price'] ?? 0, 2); ?></span>
            </div>
        </div>
        <div class="actions no-print">
            <button class="btn btn-print" onclick="window.print()"><i class="bi bi-printer"></i> Print / Save as PDF</button>
        </div>
    <?php else: ?>
        <div class="receipt-body text-center py-5">
            <h4 class="text-muted">Booking not found.</h4>
            <p class="text-muted small">Please check the link or contact the resort for assistance.</p>
        </div>
    <?php endif; ?>
</div>

</body>
</html>