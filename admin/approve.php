<?php
session_start();
include "../conn.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Handle status change: mark Approved → Booked (guest has checked in)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mark_booked'])) {
    $id = intval($_POST['booking_id']);
    $stmt = $conn->prepare("UPDATE bookings SET status = 'Booked' WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    header("Location: approve.php?booked=success");
    exit();
}

$sql    = "SELECT * FROM bookings WHERE status IN ('Approved', 'Booked') ORDER BY checkin DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Approved & Booked | Avianna's Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root { --primary-green: #1e4d40; --accent-teal: #2c7a7b; --sidebar-width: 260px; --success-green: #27ae60; --info-blue: #17a2b8; }
        body { font-family: 'Inter', sans-serif; background-color: #f4f7f6; margin: 0; }
        .sidebar { height: 100vh; background: linear-gradient(180deg, var(--primary-green) 0%, #0a1a16 100%); color: white; position: fixed; width: var(--sidebar-width); padding: 25px 20px; z-index: 1000; box-shadow: 4px 0 10px rgba(0,0,0,0.1); }
        .nav-link { color: rgba(255,255,255,0.7); margin-bottom: 8px; padding: 12px 15px; border-radius: 8px; text-decoration: none; display: block; transition: 0.3s; }
        .nav-link:hover { color: white; background: rgba(255,255,255,0.1); }
        .nav-link.active { background: var(--accent-teal); color: white; }
        .main-content { margin-left: var(--sidebar-width); padding: 40px; }
        .header-section { border-left: 5px solid var(--success-green); padding-left: 20px; margin-bottom: 30px; }
        .header-section h2 { color: var(--primary-green); font-weight: 700; margin: 0; }
        .table-card { background: white; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); overflow: hidden; padding: 25px; }
        .status-approved { color: var(--success-green); font-weight: 600; background: #eefdf5; padding: 5px 14px; border-radius: 50px; font-size: 0.82rem; border: 1px solid #c6f6d5; }
        .status-booked { color: var(--info-blue); font-weight: 600; background: #e6f9fc; padding: 5px 14px; border-radius: 50px; font-size: 0.82rem; border: 1px solid #b3ecf5; }
        .row-new-highlight { animation: highlightFade 4s ease-out forwards; }
        @keyframes highlightFade {
            0%   { background-color: #fef9c3; }
            80%  { background-color: #fef9c3; }
            100% { background-color: transparent; }
        }
        @media print { .sidebar, .btn, hr, .text-muted { display: none !important; } .main-content { margin-left: 0 !important; padding: 0 !important; } .table-card { box-shadow: none !important; } }
    </style>
</head>
<body>

<div class="sidebar">
    <h4 class="text-center fw-bold mb-4">Avianna's Admin</h4>
    <hr class="text-white-50">
    <nav class="nav flex-column">
        <a class="nav-link" href="admin.php">Pending Bookings</a>
        <a class="nav-link active" href="approve.php">Approved / Booked</a>
        <a class="nav-link" href="admin_history.php">Archived History</a>
        <a class="nav-link" href="admin_analytics.php">Analytics</a>
        <hr class="text-white-50">
        <a class="nav-link text-warning" href="../index.php" target="_blank">← View Website</a>
        <a class="nav-link text-danger" href="logout.php">Logout</a>
    </nav>
</div>

<div class="main-content">
    <?php
    $newId = isset($_GET['new_id']) ? intval($_GET['new_id']) : 0;
    ?>

    <?php if (isset($_GET['approved']) && $_GET['approved'] === 'success'): ?>
        <?php if (isset($_GET['emailed']) && $_GET['emailed'] === '1'): ?>
        <div class="alert alert-success alert-dismissible fade show d-flex align-items-center gap-3">
            <span style="font-size:1.5rem;">✅</span>
            <div>
                <strong>Booking approved!</strong> The guest has been notified by email.
                <a href="admin.php" class="ms-3 btn btn-sm btn-outline-success">← Back to Pending</a>
            </div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
        </div>
        <?php else: ?>
        <div class="alert alert-warning alert-dismissible fade show d-flex align-items-center gap-3">
            <span style="font-size:1.5rem;">⚠️</span>
            <div>
                <strong>Booking approved</strong>, but the confirmation email <strong>could not be sent</strong>.
                Please notify the guest manually.
                <a href="admin.php" class="ms-3 btn btn-sm btn-outline-warning">← Back to Pending</a>
            </div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>
    <?php endif; ?>

    <?php if (isset($_GET['booked']) && $_GET['booked'] === 'success'): ?>
        <div class="alert alert-info alert-dismissible fade show">
            📌 Status updated to <strong>Booked</strong> — guest has checked in.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="header-section d-flex justify-content-between align-items-center">
        <h2>✔ Approved & Confirmed Bookings</h2>
        <button onclick="window.print()" class="btn btn-outline-primary btn-sm px-4">
            <i class="bi bi-printer"></i> Print Report
        </button>
    </div>

    <div class="table-card">
        <table class="table align-middle m-0 table-hover">
            <thead>
                <tr>
                    <th>Guest</th>
                    <th>Room / Cottage</th>
                    <th>Pax</th>
                    <th>Stay Duration</th>
                    <th>Total Price</th>
                    <th>Payment</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result && $result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                    <tr class="<?= ($newId && $row['id'] === $newId) ? 'row-new-highlight' : '' ?>">
                        <td>
                            <strong class="d-block text-dark"><?= htmlspecialchars($row['name']) ?></strong>
                            <small class="text-muted"><?= htmlspecialchars($row['email']) ?></small>
                        </td>
                        <td>
                            <small class="d-block"><?= htmlspecialchars($row['room_type'] ?? 'None') ?></small>
                            <small class="text-muted"><?= htmlspecialchars($row['cottage_type'] ?? 'No Cottage') ?></small>
                        </td>
                        <td><small><?= htmlspecialchars($row['pax'] ?? 'N/A') ?></small></td>
                        <td>
                            <small class="fw-medium">
                                <?= date('M d, Y', strtotime($row['checkin'])) ?>
                                — <?= date('M d, Y', strtotime($row['checkout'])) ?>
                            </small>
                        </td>
                        <td>
                            <?php if (!empty($row['total_price']) && $row['total_price'] > 0): ?>
                                <strong class="text-success">₱<?= number_format($row['total_price'], 2) ?></strong>
                            <?php else: ?>
                                <span class="text-muted">N/A</span>
                            <?php endif; ?>
                        </td>
                        <td><small><?= htmlspecialchars($row['payment_method'] ?? 'N/A') ?></small></td>
                        <td>
                            <?php if ($row['status'] === 'Approved'): ?>
                                <span class="status-approved">✔ Approved</span>
                            <?php else: ?>
                                <span class="status-booked">📌 Booked</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($row['status'] === 'Approved'): ?>
                                <!-- Mark as Booked (guest checked in) -->
                                <form action="approve.php" method="POST" class="d-inline">
                                    <input type="hidden" name="booking_id" value="<?= $row['id'] ?>">
                                    <button type="submit" name="mark_booked" class="btn btn-info btn-sm text-white">
                                        📌 Mark Booked
                                    </button>
                                </form>
                            <?php else: ?>
                                <span class="text-muted small">Checked In</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted">No Approved or Booked reservations found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>