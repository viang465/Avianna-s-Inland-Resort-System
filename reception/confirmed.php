<?php
session_start();
include "../conn.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'reception') {
    header("Location: login.php");
    exit();
}

$sql = "SELECT * FROM bookings WHERE status = 'Approved' ORDER BY checkin_date DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmed Bookings | Avianna's Reception</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary-green: #1e4d40;
            --accent-teal: #2c7a7b;
            --sidebar-width: 260px;
            --bg-light: #f4f7f6;
            --success-green: #27ae60;
        }

        body { font-family: 'Inter', sans-serif; background-color: var(--bg-light); margin: 0; }

        #scrollUp, .scroll-to-top, .back-to-top, [id*="scroll"], .tp-top-arrow, button[title*="top"], .scrollup {
            display: none !important; visibility: hidden !important; opacity: 0 !important; pointer-events: none !important;
        }

        .sidebar {
            height: 100vh; background: linear-gradient(180deg, var(--primary-green) 0%, #0a1a16 100%);
            color: white; position: fixed; width: var(--sidebar-width); padding: 25px 20px;
            box-shadow: 4px 0 10px rgba(0,0,0,0.1); z-index: 1000;
        }
        .sidebar h4 { font-weight: 700; text-align: center; margin-bottom: 30px; }
        .nav-link { color: rgba(255,255,255,0.7); margin-bottom: 8px; padding: 12px 15px; border-radius: 8px; font-weight: 500; transition: all 0.3s ease; text-decoration: none; display: block; }
        .nav-link:hover { color: white; background: rgba(255,255,255,0.1); transform: translateX(5px); }
        .nav-link.active { color: white; background: var(--accent-teal); box-shadow: 0 4px 8px rgba(0,0,0,0.2); }

        .main-content { margin-left: var(--sidebar-width); padding: 40px; min-height: 100vh; }

        .header-title { color: var(--primary-green); border-left: 6px solid var(--success-green); padding-left: 20px; font-weight: 700; margin-bottom: 30px; }

        .table-card { background: white; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); overflow: hidden; border: none; }
        .table thead th { background-color: #f8f9fa; color: #6c757d; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 1px; padding: 20px; border: none; }
        .table tbody td { padding: 20px; border-bottom: 1px solid #f1f1f1; vertical-align: middle; }

        .guest-info strong { display: block; color: #2d3748; }
        .guest-info small { color: #718096; }

        .status-approved {
            color: var(--success-green); font-weight: 600; background: #eefdf5; padding: 6px 16px;
            border-radius: 50px; font-size: 0.85rem; border: 1px solid #c6f6d5; display: inline-block;
        }

        .date-badge { background: #edf2f7; padding: 4px 10px; border-radius: 6px; font-size: 0.9rem; color: #4a5568; }

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
        <a class="nav-link" href="dashboard.php"><span>Pending Confirmations</span></a>
        <a class="nav-link active" href="confirmed.php"><span>Confirmed Bookings</span></a>
        <hr style="border-color: rgba(255,255,255,0.1); margin: 20px 0;">
        <a class="nav-link text-warning" href="../index.php" target="_blank"><span>← View Website</span></a>
        <a class="nav-link text-danger" href="logout.php"><span>Logout</span></a>
    </nav>
</div>

<div class="main-content">
    <h2 class="header-title">Confirmed Reservations</h2>

    <div class="table-card">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>Guest Details</th>
                    <th>Room Type</th>
                    <th>Check-in</th>
                    <th>Check-out</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result && $result->num_rows > 0): ?>
                    <?php while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td class="guest-info">
                            <strong><?php echo htmlspecialchars($row['name']); ?></strong>
                            <small><?php echo htmlspecialchars($row['email']); ?></small>
                        </td>
                        <td><span class="fw-medium text-dark"><?php echo htmlspecialchars($row['room_type']); ?></span></td>
                        <td><span class="date-badge"><?php echo date('M d, Y', strtotime($row['checkin_date'])); ?></span></td>
                        <td><span class="date-badge"><?php echo date('M d, Y', strtotime($row['checkout_date'])); ?></span></td>
                        <td><span class="status-approved">✔ Confirmed</span></td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="text-center py-5 text-muted">No confirmed bookings yet.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>