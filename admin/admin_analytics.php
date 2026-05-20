<?php
session_start();
include "../conn.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// CSV Export
if (isset($_GET['export']) && $_GET['export'] == 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=Avianna_Report_' . date('Y-m-d') . '.csv');
    $output = fopen('php://output', 'w');
    fputcsv($output, ['Week', 'Bookings', 'Revenue (PHP)', 'Expenses (PHP)', 'Net Profit (PHP)']);

    $weeklyExpenseEstimate = 5000;
    $exportStats = $conn->query("
        SELECT FLOOR((DAY(checkin)-1)/7)+1 as week_num,
               COUNT(*) as total_bookings,
               SUM(total_price) as week_inflow
        FROM bookings
        WHERE MONTH(checkin) = MONTH(CURDATE())
          AND status IN ('Approved','Booked')
        GROUP BY week_num ORDER BY week_num ASC
    ");
    if ($exportStats && $exportStats->num_rows > 0) {
        while ($row = $exportStats->fetch_assoc()) {
            $inflow = $row['week_inflow'] ?? 0;
            fputcsv($output, [
                'Week ' . $row['week_num'],
                $row['total_bookings'],
                number_format($inflow, 2),
                number_format($weeklyExpenseEstimate, 2),
                number_format($inflow - $weeklyExpenseEstimate, 2)
            ]);
        }
    }
    fclose($output);
    exit();
}

// Stats
$totalActive = 0;
$res = $conn->query("SELECT COUNT(*) as total FROM bookings");
if ($res) $totalActive = $res->fetch_assoc()['total'] ?? 0;

$pendingCount = 0;
$res = $conn->query("SELECT COUNT(*) as total FROM bookings WHERE status = 'Pending' OR status IS NULL");
if ($res) $pendingCount = $res->fetch_assoc()['total'] ?? 0;

$approvedCount = 0;
$res = $conn->query("SELECT COUNT(*) as total FROM bookings WHERE status IN ('Approved','Booked')");
if ($res) $approvedCount = $res->fetch_assoc()['total'] ?? 0;

$archivedCount = 0;
$res = $conn->query("SELECT COUNT(*) as total FROM deleted_bookings");
if ($res) $archivedCount = $res->fetch_assoc()['total'] ?? 0;

// FIXED: Use actual total_price column (not calculated per night) for accurate revenue
$totalRevenue = 0;
$res = $conn->query("SELECT SUM(total_price) as total_rev FROM bookings WHERE status IN ('Approved','Booked')");
if ($res) $totalRevenue = $res->fetch_assoc()['total_rev'] ?? 0;

$thisMonthRevenue = 0;
$res = $conn->query("
    SELECT SUM(total_price) as month_rev FROM bookings
    WHERE MONTH(checkin) = MONTH(CURDATE()) AND YEAR(checkin) = YEAR(CURDATE())
      AND status IN ('Approved','Booked')
");
if ($res) $thisMonthRevenue = $res->fetch_assoc()['month_rev'] ?? 0;

// Weekly breakdown using actual total_price
$weeklyStats = $conn->query("
    SELECT FLOOR((DAY(checkin)-1)/7)+1 as week_num,
           COUNT(*) as total_bookings,
           SUM(total_price) as week_inflow
    FROM bookings
    WHERE MONTH(checkin) = MONTH(CURDATE()) AND status IN ('Approved','Booked')
    GROUP BY week_num ORDER BY week_num ASC
");

// Top locations
$addressResult = $conn->query("
    SELECT address, COUNT(*) as count FROM bookings
    GROUP BY address ORDER BY count DESC LIMIT 5
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analytics | Avianna's Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root { --primary-green: #1e4d40; --accent-teal: #2c7a7b; --sidebar-width: 260px; --soft-bg: #f0f4f3; }
        body { font-family: 'Inter', sans-serif; background-color: var(--soft-bg); margin: 0; }
        .sidebar { height: 100vh; background: linear-gradient(180deg, var(--primary-green) 0%, #0a1a16 100%); color: white; position: fixed; width: var(--sidebar-width); padding: 25px 20px; z-index: 1000; box-shadow: 4px 0 10px rgba(0,0,0,0.1); }
        .nav-link { color: rgba(255,255,255,0.7); margin-bottom: 8px; padding: 12px 15px; text-decoration: none; display: block; border-radius: 8px; transition: 0.3s; }
        .nav-link:hover { color: white; background: rgba(255,255,255,0.1); }
        .nav-link.active { background: var(--accent-teal); color: white; }
        .main-content { margin-left: var(--sidebar-width); padding: 40px; }
        .stat-card { background: white; padding: 25px; border-radius: 15px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); margin-bottom: 20px; border-left: 5px solid transparent; }
        .stat-card.revenue { border-color: #27ae60; }
        .stat-card.bookings { border-color: var(--accent-teal); }
        .stat-card.pending { border-color: #f59e0b; }
        .stat-card.archived { border-color: #e53e3e; }
        .section-card { background: white; border-radius: 15px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); padding: 25px; margin-bottom: 25px; }
    </style>
</head>
<body>

<div class="sidebar">
    <h4 class="text-center fw-bold mb-4">Avianna's Admin</h4>
    <hr class="text-white-50">
    <nav class="nav flex-column">
        <a class="nav-link" href="admin.php">Pending Bookings</a>
        <a class="nav-link" href="approve.php">Approved / Booked</a>
        <a class="nav-link" href="admin_history.php">Archived History</a>
        <a class="nav-link active" href="admin_analytics.php">Analytics</a>
        <hr class="text-white-50">
        <a class="nav-link text-warning" href="../index.php" target="_blank">← View Website</a>
        <a class="nav-link text-danger" href="logout.php">Logout</a>
    </nav>
</div>

<div class="main-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="text-dark fw-bold m-0">📊 Performance Insights</h2>
        <a href="admin_analytics.php?export=csv" class="btn btn-success px-4">
            <i class="bi bi-file-earmark-spreadsheet"></i> Export CSV
        </a>
    </div>

    <!-- KPI Cards -->
    <div class="row">
        <div class="col-md-3">
            <div class="stat-card revenue">
                <div class="text-muted small fw-bold mb-1">TOTAL REVENUE</div>
                <h3 class="text-success fw-bold">₱<?= number_format($totalRevenue, 2) ?></h3>
                <small class="text-muted">All approved bookings</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card revenue">
                <div class="text-muted small fw-bold mb-1">THIS MONTH'S REVENUE</div>
                <h3 class="text-success fw-bold">₱<?= number_format($thisMonthRevenue, 2) ?></h3>
                <small class="text-muted"><?= date('F Y') ?></small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card pending">
                <div class="text-muted small fw-bold mb-1">PENDING</div>
                <h3 class="text-warning fw-bold"><?= $pendingCount ?></h3>
                <small class="text-muted">Awaiting approval</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card bookings">
                <div class="text-muted small fw-bold mb-1">APPROVED / BOOKED</div>
                <h3 class="text-dark fw-bold"><?= $approvedCount ?></h3>
                <small class="text-muted">Confirmed reservations</small>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-3">
            <div class="stat-card archived">
                <div class="text-muted small fw-bold mb-1">ARCHIVED / CANCELLED</div>
                <h3 class="text-danger fw-bold"><?= $archivedCount ?></h3>
                <small class="text-muted">Total cancelled</small>
            </div>
        </div>
    </div>

    <!-- Weekly Revenue Table -->
    <div class="section-card">
        <h5 class="fw-bold mb-3">📅 Weekly Breakdown — <?= date('F Y') ?></h5>
        <?php if ($weeklyStats && $weeklyStats->num_rows > 0): ?>
        <table class="table table-sm align-middle">
            <thead>
                <tr>
                    <th>Week</th>
                    <th>Bookings</th>
                    <th>Revenue</th>
                    <th>Est. Expenses</th>
                    <th>Net Profit</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $weeklyExpenseEstimate = 5000;
                while ($row = $weeklyStats->fetch_assoc()):
                    $inflow = $row['week_inflow'] ?? 0;
                    $net    = $inflow - $weeklyExpenseEstimate;
                ?>
                <tr>
                    <td>Week <?= $row['week_num'] ?></td>
                    <td><?= $row['total_bookings'] ?></td>
                    <td class="text-success fw-semibold">₱<?= number_format($inflow, 2) ?></td>
                    <td class="text-danger">₱<?= number_format($weeklyExpenseEstimate, 2) ?></td>
                    <td class="fw-bold <?= $net >= 0 ? 'text-success' : 'text-danger' ?>">
                        ₱<?= number_format($net, 2) ?>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        <?php else: ?>
            <p class="text-muted">No approved bookings this month yet.</p>
        <?php endif; ?>
    </div>

    <!-- Top Locations -->
    <div class="section-card">
        <h5 class="fw-bold mb-3">📍 Top Guest Locations</h5>
        <?php if ($addressResult && $addressResult->num_rows > 0): ?>
        <table class="table table-sm">
            <thead><tr><th>Address</th><th>Bookings</th></tr></thead>
            <tbody>
                <?php while ($row = $addressResult->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['address']) ?></td>
                    <td><span class="badge bg-secondary"><?= $row['count'] ?></span></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        <?php else: ?>
            <p class="text-muted">No location data yet.</p>
        <?php endif; ?>
    </div>
</div>

</body>
</html>