<?php
session_start();
include "../conn.php";

// 1. Authentication
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// 2. Delete Announcement — POST only (GET removed to prevent CSRF via direct URL)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_announcement_id']) && is_numeric($_POST['delete_announcement_id'])) {
    $id = (int)$_POST['delete_announcement_id'];
    $stmt = $conn->prepare("DELETE FROM announcements WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        header("Location: admin.php?status=deleted");
        exit();
    }
}

// 3. Post Announcement
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['post_announcement'])) {
    $title   = trim($_POST['title']   ?? '');
    $message = trim($_POST['message'] ?? '');
    if (!empty($title) && !empty($message)) {
        $stmt = $conn->prepare("INSERT INTO announcements (title, message, created_at) VALUES (?, ?, NOW())");
        $stmt->bind_param("ss", $title, $message);
        if ($stmt->execute()) {
            header("Location: admin.php?status=posted");
            exit();
        }
    }
}

// 4. Pending Bookings
$result_bookings = $conn->query(
    "SELECT * FROM bookings WHERE status = 'Pending' OR status IS NULL ORDER BY checkin ASC"
);

// 5. Announcements list
$announcements_list = $conn->query("SELECT * FROM announcements ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Avianna's</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-green: #1e4d40;
            --accent-teal: #2c7a7b;
            --sidebar-width: 260px;
            --bg-light: #f4f7f6;
            --pending-yellow: #f59e0b;
        }
        body { font-family: 'Inter', sans-serif; background-color: var(--bg-light); margin: 0; }
        .sidebar {
            height: 100vh; background: linear-gradient(180deg, var(--primary-green) 0%, #0a1a16 100%);
            color: white; position: fixed; width: var(--sidebar-width);
            padding: 25px 20px; z-index: 1000; box-shadow: 4px 0 10px rgba(0,0,0,0.1);
        }
        .nav-link {
            color: rgba(255,255,255,0.7); margin-bottom: 8px; padding: 12px 15px;
            border-radius: 8px; text-decoration: none; display: block; transition: 0.3s;
        }
        .nav-link:hover { color: white; background: rgba(255,255,255,0.1); }
        .nav-link.active { color: white; background: var(--accent-teal); }
        .main-content { margin-left: var(--sidebar-width); padding: 40px; }
        .header-title {
            color: var(--primary-green); border-left: 6px solid var(--accent-teal);
            padding-left: 20px; font-weight: 700; margin-bottom: 30px;
        }
        .admin-card {
            background: white; border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05); padding: 25px; margin-bottom: 30px;
        }
        .table thead th {
            background: #f8f9fa; color: #6c757d;
            font-size: 0.78rem; text-transform: uppercase; border: none;
        }
        .guest-name { display: block; font-weight: 600; }
        .guest-email { font-size: 0.8rem; color: #718096; }
        .badge-pending { background-color: var(--pending-yellow); color: #fff; font-size: 0.75rem; padding: 0.35em 0.65em; }
        /* Modal detail rows */
        .detail-row { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #f0f0f0; }
        .detail-label { font-weight: 600; color: #555; font-size: 0.9rem; }
        .detail-value { color: #333; font-size: 0.9rem; }
    </style>
</head>
<body>

<div class="sidebar">
    <h4 class="text-center fw-bold mb-4">Avianna's Admin</h4>
    <hr class="text-white-50">
    <nav class="nav flex-column">
        <a class="nav-link active" href="admin.php">Pending Bookings</a>
        <a class="nav-link" href="approve.php">Approved / Booked</a>
        <a class="nav-link" href="admin_history.php">Archived History</a>
        <a class="nav-link" href="admin_analytics.php">Analytics</a>
        <hr class="text-white-50">
        <a class="nav-link text-warning" href="../index.php" target="_blank">← View Website</a>
        <a class="nav-link text-danger" href="logout.php">Logout</a>
    </nav>
</div>

<div class="main-content">
    <h2 class="header-title">Pending Reservations</h2>

    <?php if (isset($_GET['cancel']) && $_GET['cancel'] === 'success'): ?>
        <div class="alert alert-success alert-dismissible fade show">
            Booking archived to history successfully.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if (isset($_GET['delete']) && $_GET['delete'] === 'success'): ?>
        <div class="alert alert-info alert-dismissible fade show">
            Booking permanently deleted.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if (isset($_GET['approved']) && $_GET['approved'] === 'success'): ?>
        <div class="alert alert-success alert-dismissible fade show">
            ✅ Booking approved. <a href="approve.php" class="alert-link">View in Approved / Booked →</a>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if (isset($_GET['status'])): ?>
        <?php if ($_GET['status'] === 'posted'): ?>
            <div class="alert alert-success alert-dismissible fade show">
                Announcement posted.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php elseif ($_GET['status'] === 'deleted'): ?>
            <div class="alert alert-info alert-dismissible fade show">
                Announcement deleted.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <!-- Bookings Table -->
    <div class="admin-card">
        <table class="table align-middle table-hover">
            <thead>
                <tr>
                    <th>Guest Details</th>
                    <th>Room / Cottage</th>
                    <th>Pax</th>
                    <th>Check In / Out</th>
                    <th>Total</th>
                    <th>Payment</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result_bookings && $result_bookings->num_rows > 0): ?>
                    <?php while ($row = $result_bookings->fetch_assoc()): ?>
                    <tr>
                        <td>
                            <span class="guest-name"><?= htmlspecialchars($row['name']) ?></span>
                            <span class="guest-email"><?= htmlspecialchars($row['email']) ?></span>
                            <?php if (!empty($row['contact'])): ?>
                                <span class="guest-email"><?= htmlspecialchars($row['contact']) ?></span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <small class="d-block"><?= htmlspecialchars($row['room_type'] ?? 'None') ?></small>
                            <small class="text-muted"><?= htmlspecialchars($row['cottage_type'] ?? 'No Cottage') ?></small>
                        </td>
                        <td><small><?= htmlspecialchars($row['pax'] ?? 'N/A') ?></small></td>
                        <td>
                            <small class="fw-medium text-dark">
                                <?= date('M d, Y', strtotime($row['checkin'])) ?><br>
                                <span class="text-muted">to</span>
                                <?= date('M d, Y', strtotime($row['checkout'])) ?>
                            </small>
                        </td>
                        <td>
                            <strong class="text-success">
                                ₱<?= number_format($row['total_price'] ?? 0, 2) ?>
                            </strong>
                        </td>
                        <td><small><?= htmlspecialchars($row['payment_method'] ?? 'N/A') ?></small></td>
                        <td>
                            <span class="badge badge-pending rounded-pill">Pending</span>
                        </td>
                        <td>
                            <div class="d-flex gap-1 flex-wrap">
                                <!-- Approve -->
                                <form action="approve_booking.php" method="POST" class="d-inline"
                                      onsubmit="return confirm('Approve this booking?');">
                                    <input type="hidden" name="booking_id" value="<?= $row['id'] ?>">
                                    <button type="submit" class="btn btn-success btn-sm">✔ Approve</button>
                                </form>
                                <!-- View Details Modal Trigger -->
                                <button type="button" class="btn btn-outline-secondary btn-sm"
                                    data-bs-toggle="modal" data-bs-target="#viewModal"
                                    data-name="<?= htmlspecialchars($row['name']) ?>"
                                    data-email="<?= htmlspecialchars($row['email']) ?>"
                                    data-contact="<?= htmlspecialchars($row['contact'] ?? 'N/A') ?>"
                                    data-address="<?= htmlspecialchars($row['address']) ?>"
                                    data-room="<?= htmlspecialchars($row['room_type'] ?? 'None') ?>"
                                    data-cottage="<?= htmlspecialchars($row['cottage_type'] ?? 'None') ?>"
                                    data-pax="<?= htmlspecialchars($row['pax'] ?? 'N/A') ?>"
                                    data-checkin="<?= date('M d, Y', strtotime($row['checkin'])) ?>"
                                    data-checkout="<?= date('M d, Y', strtotime($row['checkout'])) ?>"
                                    data-payment="<?= htmlspecialchars($row['payment_method'] ?? 'N/A') ?>"
                                    data-total="<?= number_format($row['total_price'] ?? 0, 2) ?>">
                                    👁 View
                                </button>
                                <!-- Archive -->
                                <form action="cancel_booking.php" method="POST" class="d-inline"
                                      onsubmit="return confirm('Archive this booking?');">
                                    <input type="hidden" name="booking_id" value="<?= $row['id'] ?>">
                                    <button type="submit" class="btn btn-outline-danger btn-sm">🗑 Archive</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted">No pending reservations found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Announcement Management -->
    <h4 class="fw-bold mb-3" style="color: var(--primary-green);">📢 Manage Announcements</h4>
    <div class="admin-card">
        <form method="POST" class="row g-3 mb-4">
            <div class="col-md-4">
                <label class="form-label fw-bold small">Title</label>
                <input type="text" name="title" class="form-control" placeholder="Announcement title" required>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-bold small">Message</label>
                <input type="text" name="message" class="form-control" placeholder="Announcement content" required>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" name="post_announcement" class="btn btn-primary w-100">Post</button>
            </div>
        </form>

        <?php if ($announcements_list && $announcements_list->num_rows > 0): ?>
            <table class="table table-sm align-middle">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Message</th>
                        <th>Posted</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($ann = $announcements_list->fetch_assoc()): ?>
                    <tr>
                        <td class="fw-semibold"><?= htmlspecialchars($ann['title']) ?></td>
                        <td><?= htmlspecialchars($ann['message']) ?></td>
                        <td><small class="text-muted"><?= date('M d, Y', strtotime($ann['created_at'])) ?></small></td>
                        <td>
                            <form action="admin.php" method="POST" class="d-inline"
                                  onsubmit="return confirm('Delete this announcement?');">
                                <input type="hidden" name="delete_announcement_id" value="<?= $ann['id'] ?>">
                                <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="text-muted small">No announcements posted yet.</p>
        <?php endif; ?>
    </div>
</div>

<!-- View Booking Modal -->
<div class="modal fade" id="viewModal" tabindex="-1" aria-labelledby="viewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header" style="background: var(--primary-green); color: white;">
                <h5 class="modal-title" id="viewModalLabel">📋 Booking Details</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body px-4">
                <div class="detail-row"><span class="detail-label">Name</span><span class="detail-value" id="m-name"></span></div>
                <div class="detail-row"><span class="detail-label">Email</span><span class="detail-value" id="m-email"></span></div>
                <div class="detail-row"><span class="detail-label">Contact</span><span class="detail-value" id="m-contact"></span></div>
                <div class="detail-row"><span class="detail-label">Address</span><span class="detail-value" id="m-address"></span></div>
                <div class="detail-row"><span class="detail-label">Room</span><span class="detail-value" id="m-room"></span></div>
                <div class="detail-row"><span class="detail-label">Cottage</span><span class="detail-value" id="m-cottage"></span></div>
                <div class="detail-row"><span class="detail-label">Pax</span><span class="detail-value" id="m-pax"></span></div>
                <div class="detail-row"><span class="detail-label">Check-in</span><span class="detail-value" id="m-checkin"></span></div>
                <div class="detail-row"><span class="detail-label">Check-out</span><span class="detail-value" id="m-checkout"></span></div>
                <div class="detail-row"><span class="detail-label">Payment</span><span class="detail-value" id="m-payment"></span></div>
                <div class="detail-row" style="border:none;">
                    <span class="detail-label">Total Amount</span>
                    <span class="detail-value fw-bold text-success" id="m-total"></span>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Populate View Modal from data-* attributes
const viewModal = document.getElementById('viewModal');
viewModal.addEventListener('show.bs.modal', function (event) {
    const btn = event.relatedTarget;
    document.getElementById('m-name').textContent    = btn.dataset.name;
    document.getElementById('m-email').textContent   = btn.dataset.email;
    document.getElementById('m-contact').textContent = btn.dataset.contact;
    document.getElementById('m-address').textContent = btn.dataset.address;
    document.getElementById('m-room').textContent    = btn.dataset.room;
    document.getElementById('m-cottage').textContent = btn.dataset.cottage;
    document.getElementById('m-pax').textContent     = btn.dataset.pax;
    document.getElementById('m-checkin').textContent  = btn.dataset.checkin;
    document.getElementById('m-checkout').textContent = btn.dataset.checkout;
    document.getElementById('m-payment').textContent  = btn.dataset.payment;
    document.getElementById('m-total').textContent    = '₱' + btn.dataset.total;
});
</script>
</body>
</html>