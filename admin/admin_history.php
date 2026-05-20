<?php
session_start();
include "../conn.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// FIX: ORDER BY deleted_at DESC with fallback; COALESCE handles both column name variants
$sql = "SELECT * FROM deleted_bookings ORDER BY COALESCE(deleted_at, deletion_date) DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Archived History | Avianna's Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root { --primary-green: #1e4d40; --accent-teal: #2c7a7b; --sidebar-width: 260px; --cancel-red: #c53030; }
        body { font-family: 'Inter', sans-serif; background-color: #fcfcfc; margin: 0; }
        .sidebar { height: 100vh; background: linear-gradient(180deg, var(--primary-green) 0%, #0a1a16 100%); color: white; position: fixed; width: var(--sidebar-width); padding: 25px 20px; box-shadow: 4px 0 10px rgba(0,0,0,0.1); z-index: 1000; }
        .nav-link { color: rgba(255,255,255,0.7); margin-bottom: 8px; padding: 12px 15px; border-radius: 8px; transition: all 0.2s ease; text-decoration: none; display: block; }
        .nav-link:hover { color: white; background: rgba(255,255,255,0.1); }
        .nav-link.active { color: white; background: var(--accent-teal); }
        .main-content { margin-left: var(--sidebar-width); padding: 40px; min-height: 100vh; }
        .header-box { background: #fff; padding: 20px; border-radius: 12px; border-left: 6px solid var(--cancel-red); box-shadow: 0 2px 10px rgba(0,0,0,0.03); margin-bottom: 30px; }
        .header-box h2 { color: var(--primary-green); font-weight: 700; margin: 0; }
        .table-card { background: white; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); padding: 25px; }
        .badge-cancelled { color: var(--cancel-red); font-weight: 600; background: #fff5f5; border: 1px solid #fed7d7; padding: 6px 12px; border-radius: 50px; font-size: 0.8rem; }
    </style>
</head>
<body>

<div class="sidebar">
    <h4 class="fw-bold text-center mb-4">Avianna's Admin</h4>
    <hr class="text-white-50">
    <nav class="nav flex-column">
        <a class="nav-link" href="admin.php">Pending Bookings</a>
        <a class="nav-link" href="approve.php">Approved / Booked</a>
        <a class="nav-link active" href="admin_history.php">Archived History</a>
        <a class="nav-link" href="admin_analytics.php">Analytics</a>
        <hr class="text-white-50">
        <a class="nav-link text-warning" href="../index.php" target="_blank">← View Website</a>
        <a class="nav-link text-danger" href="logout.php">Logout</a>
    </nav>
</div>

<div class="main-content">
    <div class="header-box">
        <h2>🗑 Archived & Cancelled Bookings</h2>
    </div>
    <div class="table-card">
        <table class="table align-middle m-0">
            <thead>
                <tr>
                    <th>Guest Details</th>
                    <th>Room Type</th>
                    <th>Booking Window</th>
                    <th>Archived On</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result && $result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td>
                            <strong class="d-block"><?= htmlspecialchars($row['name']) ?></strong>
                            <small class="text-muted"><?= htmlspecialchars($row['email']) ?></small>
                        </td>
                        <td><?= htmlspecialchars($row['room_type'] ?? 'N/A') ?></td>
                        <td>
                            <small class="text-secondary fw-medium">
                                <?php
                                // FIXED: handle both possible column names for dates
                                $ci = $row['checkin_date'] ?? $row['checkin'] ?? null;
                                $co = $row['checkout_date'] ?? $row['checkout'] ?? null;
                                if ($ci && $co) {
                                    echo date('M d', strtotime($ci)) . " — " . date('M d, Y', strtotime($co));
                                } else {
                                    echo '<span class="text-muted">N/A</span>';
                                }
                                ?>
                            </small>
                        </td>
                        <td>
                            <?php
                            // FIXED: handle both column names for deletion timestamp
                            $ts = $row['deletion_date'] ?? $row['deleted_at'] ?? null;
                            ?>
                            <span class="badge-cancelled">
                                🗑 <?= $ts ? date('M d, Y | h:i A', strtotime($ts)) : 'N/A' ?>
                            </span>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" class="text-center py-5 text-muted">No archived records found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>