<?php
session_start();
include "../conn.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'reception') {
    header("Location: login.php");
    exit();
}

$sql = "SELECT * FROM bookings WHERE (status != 'Approved' OR status IS NULL) ORDER BY checkin_date ASC";
$result = $conn->query($sql);

$bannerMsg = "";
if (isset($_GET['confirmed']) && $_GET['confirmed'] === 'success') $bannerMsg = "Booking confirmed and guest notified.";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pending Confirmations | Avianna's Reception</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary-green: #1e4d40;
            --accent-teal: #2c7a7b;
            --sidebar-width: 260px;
            --bg-light: #f4f7f6;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-light);
            margin: 0;
        }

        #scrollUp, .scroll-to-top, .back-to-top, [id*="scroll"], .tp-top-arrow, button[title*="top"], .scrollup {
            display: none !important;
            visibility: hidden !important;
            opacity: 0 !important;
            pointer-events: none !important;
        }

        .sidebar {
            height: 100vh;
            background: linear-gradient(180deg, var(--primary-green) 0%, #0a1a16 100%);
            color: white;
            position: fixed;
            width: var(--sidebar-width);
            padding: 25px 20px;
            box-shadow: 4px 0 10px rgba(0,0,0,0.1);
            z-index: 1000;
        }

        .sidebar h4 {
            font-weight: 700;
            text-align: center;
            margin-bottom: 30px;
        }

        .nav-link {
            color: rgba(255,255,255,0.7);
            margin-bottom: 8px;
            padding: 12px 15px;
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.3s ease;
            text-decoration: none;
            display: block;
        }

        .nav-link:hover {
            color: white;
            background: rgba(255,255,255,0.1);
            transform: translateX(5px);
        }

        .nav-link.active {
            color: white;
            background: var(--accent-teal);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }

        .main-content {
            margin-left: var(--sidebar-width);
            padding: 40px;
            min-height: 100vh;
        }

        .header-title {
            color: var(--primary-green);
            border-left: 6px solid var(--accent-teal);
            padding-left: 20px;
            font-weight: 700;
            margin-bottom: 30px;
        }

        .table-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            overflow: hidden;
            border: none;
        }

        .table thead { background-color: #f8f9fa; }

        .table thead th {
            border: none;
            color: #6c757d;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            padding: 20px;
        }

        .table tbody td {
            padding: 20px;
            border-bottom: 1px solid #f1f1f1;
            vertical-align: middle;
        }

        .guest-name { display: block; font-weight: 600; color: #2d3748; }
        .guest-email { font-size: 0.85rem; color: #718096; }

        .badge-payment {
            background: #e6fffa;
            color: #234e52;
            border: 1px solid #b2f5ea;
            font-weight: 600;
            padding: 6px 12px;
            border-radius: 6px;
        }

        @media (max-width: 992px) {
            .sidebar { width: 80px; padding: 20px 10px; }
            .sidebar h4, .nav-link span { display: none; }
            .main-content { margin-left: 80px; }
        }
    </style>
</head>
<body>

<div class="sidebar">
    <h4>Avianna's Reception</h4>
    <hr style="border-color: rgba(255,255,255,0.1);">
    <nav class="nav flex-column">
        <a class="nav-link active" href="dashboard.php"><span>Pending Confirmations</span></a>
        <a class="nav-link" href="confirmed.php"><span>Confirmed Bookings</span></a>
        <hr style="border-color: rgba(255,255,255,0.1); margin: 20px 0;">
        <a class="nav-link text-warning" href="../index.php" target="_blank"><span>← View Website</span></a>
        <a class="nav-link text-danger" href="logout.php"><span>Logout</span></a>
    </nav>
</div>

<div class="main-content">
    <h2 class="header-title">Pending Reservations</h2>

    <?php if (!empty($bannerMsg)): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> <?php echo htmlspecialchars($bannerMsg); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="table-card">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>Guest Details</th>
                    <th>Room Type</th>
                    <th>Check-in</th>
                    <th>Payment</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result && $result->num_rows > 0): ?>
                    <?php while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td>
                            <span class="guest-name"><?php echo htmlspecialchars($row['name']); ?></span>
                            <span class="guest-email"><?php echo htmlspecialchars($row['email']); ?></span>
                        </td>
                        <td>
                            <span class="fw-medium"><?php echo htmlspecialchars($row['room_type']); ?></span>
                        </td>
                        <td>
                            <span class="text-muted"><?php echo date('M d, Y', strtotime($row['checkin_date'])); ?></span>
                        </td>
                        <td>
                            <span class="badge-payment">
                                <?php echo htmlspecialchars($row['payment_method'] ?? 'N/A'); ?>
                            </span>
                        </td>
                        <td>
                            <a href="confirm.php?id=<?php echo $row['id']; ?>" class="btn btn-success btn-sm px-3">Confirm Customer</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="text-center py-5">
                            <p class="text-muted mb-0">No pending reservations found.</p>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>