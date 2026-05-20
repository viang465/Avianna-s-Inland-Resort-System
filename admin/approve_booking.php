<?php
// approve_booking.php — Admin: approve a pending booking
session_start();
include "../conn.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    exit("Unauthorized");
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['booking_id'])) {
    header("Location: admin.php");
    exit();
}

$id = intval($_POST['booking_id']);

// Fetch booking BEFORE updating
$stmt = $conn->prepare("SELECT * FROM bookings WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$booking = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$booking) {
    header("Location: admin.php?approved=fail");
    exit();
}

// Update status → Approved
$upd = $conn->prepare("UPDATE bookings SET status = 'Approved' WHERE id = ?");
$upd->bind_param("i", $id);
$updated = $upd->execute();
$upd->close();

if (!$updated) {
    header("Location: admin.php?approved=fail");
    exit();
}

// Try to send approval email via PHPMailer (loaded from parent directory)
$emailSent = false;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../PHPMailer.php';
require_once __DIR__ . '/../Exception.php';
require_once __DIR__ . '/../SMTP.php';

function sendApprovalEmail(array $b): bool {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'aviannasinlandresort@gmail.com';
        $mail->Password   = 'dmhkacwoqpejzxyy';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        $mail->Timeout    = 20;
        $mail->CharSet    = 'UTF-8';
        $mail->SMTPOptions = [
            'ssl' => [
                'verify_peer'       => false,
                'verify_peer_name'  => false,
                'allow_self_signed' => true,
            ],
        ];

        $mail->setFrom('aviannasinlandresort@gmail.com', "Avianna's Inland Resort");
        $mail->addAddress(trim($b['email']), trim($b['name']));
        $mail->isHTML(true);
        $mail->Subject = "Booking Approved - Avianna's Inland Resort";

        $name     = htmlspecialchars($b['name']           ?? '');
        $contact  = htmlspecialchars($b['contact']        ?? 'N/A');
        $address  = htmlspecialchars($b['address']        ?? 'N/A');
        $room     = htmlspecialchars($b['room_type']      ?? 'None');
        $cottage  = htmlspecialchars($b['cottage_type']   ?? 'None');
        $pax      = htmlspecialchars($b['pax']            ?? 'N/A');
        $payment  = htmlspecialchars($b['payment_method'] ?? 'N/A');
        $checkin  = date('F d, Y', strtotime($b['checkin']));
        $checkout = date('F d, Y', strtotime($b['checkout']));
        $total    = '₱' . number_format((float)($b['total_price'] ?? 0), 2);

        $mail->Body = "
        <div style='font-family:Arial,sans-serif;max-width:640px;margin:0 auto;border:1px solid #ddd;border-radius:12px;overflow:hidden;'>
            <div style='background:#1e4d40;padding:30px;text-align:center;'>
                <div style='font-size:50px;'>&#10003;</div>
                <h1 style='color:#fff;margin:8px 0 0;font-size:1.7rem;'>Booking Approved!</h1>
                <p style='color:rgba(255,255,255,.75);margin:6px 0 0;'>Avianna's Inland Resort</p>
            </div>
            <div style='padding:28px 32px;color:#333;line-height:1.75;'>
                <p>Dear <strong>{$name}</strong>,</p>
                <p>Your reservation at <strong>Avianna's Inland Resort</strong> has been <strong style='color:#1e4d40;'>officially approved</strong>. We look forward to welcoming you!</p>
                <div style='background:#f0fdf4;border:1px solid #bbf7d0;border-radius:10px;padding:20px 24px;margin:22px 0;'>
                    <h3 style='color:#1e4d40;margin:0 0 14px;font-size:1rem;text-transform:uppercase;letter-spacing:1px;'>Confirmed Booking Details</h3>
                    <table style='width:100%;border-collapse:collapse;font-size:.93rem;'>
                        <tr style='border-bottom:1px solid #d1fae5;'><td style='padding:7px 0;color:#555;width:42%;'>Guest Name</td><td style='font-weight:600;'>{$name}</td></tr>
                        <tr style='border-bottom:1px solid #d1fae5;'><td style='padding:7px 0;color:#555;'>Contact</td><td>{$contact}</td></tr>
                        <tr style='border-bottom:1px solid #d1fae5;'><td style='padding:7px 0;color:#555;'>Address</td><td>{$address}</td></tr>
                        <tr style='border-bottom:1px solid #d1fae5;'><td style='padding:7px 0;color:#555;'>Room Type</td><td>{$room}</td></tr>
                        <tr style='border-bottom:1px solid #d1fae5;'><td style='padding:7px 0;color:#555;'>Cottage</td><td>{$cottage}</td></tr>
                        <tr style='border-bottom:1px solid #d1fae5;'><td style='padding:7px 0;color:#555;'>Guests</td><td>{$pax}</td></tr>
                        <tr style='border-bottom:1px solid #d1fae5;'><td style='padding:7px 0;color:#555;'>Check-in</td><td style='font-weight:600;color:#1e4d40;'>{$checkin}</td></tr>
                        <tr style='border-bottom:1px solid #d1fae5;'><td style='padding:7px 0;color:#555;'>Check-out</td><td style='font-weight:600;color:#1e4d40;'>{$checkout}</td></tr>
                        <tr style='border-bottom:1px solid #d1fae5;'><td style='padding:7px 0;color:#555;'>Payment Method</td><td>{$payment}</td></tr>
                        <tr><td style='padding:10px 0;font-weight:700;'>Total Amount Due</td><td style='font-size:1.15rem;font-weight:700;color:#16a34a;'>{$total}</td></tr>
                    </table>
                </div>
                <div style='background:#fffbeb;border-left:4px solid #f59e0b;padding:14px 18px;border-radius:6px;margin-bottom:22px;font-size:.9rem;color:#555;'>
                    <strong>Important Reminders:</strong>
                    <ul style='margin:8px 0 0;padding-left:18px;'>
                        <li>Bring a valid government-issued ID upon check-in.</li>
                        <li>Arrive on or before your scheduled check-in date.</li>
                        <li>Full payment is due upon arrival unless pre-arranged.</li>
                        <li>For cancellations, notify us at least 24 hours in advance.</li>
                    </ul>
                </div>
                <p>Questions? Email: <a href='mailto:aviannasinlandresort@gmail.com' style='color:#1e4d40;'>aviannasinlandresort@gmail.com</a></p>
                <p>Location: Zone 6 Cabugao Sur Sta. Barbara, Iloilo City, Philippines</p>
            </div>
            <div style='background:#0e2a1d;padding:18px;text-align:center;color:rgba(255,255,255,.5);font-size:.82rem;'>
                &copy; 2026 Avianna's Inland Resort. All rights reserved.
            </div>
        </div>";

        $mail->AltBody = "Your booking at Avianna's Inland Resort is APPROVED.\nName: {$name}\nCheck-in: {$checkin}\nCheck-out: {$checkout}\nTotal: {$total}";
        $mail->send();
        return true;

    } catch (Exception $e) {
        error_log("Approval email failed for booking ID {$b['id']}: " . $mail->ErrorInfo);
        return false;
    }
}

$emailSent = sendApprovalEmail($booking);

header("Location: approve.php?approved=success&emailed=" . ($emailSent ? '1' : '0') . "&new_id=" . $id);
exit();
