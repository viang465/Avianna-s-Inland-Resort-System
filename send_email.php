<?php
// send_email.php — standalone booking submission endpoint
// NOTE: book.php handles submissions directly. This is a legacy fallback only.
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/PHPMailer.php';
require __DIR__ . '/Exception.php';
require __DIR__ . '/SMTP.php';

include "conn.php";

function sendConfirmationEmail(string $email, string $name): bool {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'aviannasinlandresort@gmail.com';
        $mail->Password   = 'dmhkacwoqpejzxyy';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        $mail->Timeout    = 15;
        $mail->SMTPOptions = ['ssl' => ['verify_peer' => false, 'verify_peer_name' => false, 'allow_self_signed' => true]];
        $mail->setFrom('aviannasinlandresort@gmail.com', "Avianna's Resort");
        $mail->addAddress($email, $name);
        $mail->isHTML(true);
        $mail->Subject = "Booking Confirmation - Avianna's Resort";
        $mail->Body    = "<div style='font-family:Arial,sans-serif;max-width:600px;margin:0 auto;'>
            <h2 style='color:#1e4d40;'>Booking Received!</h2>
            <p>Dear <strong>" . htmlspecialchars($name) . "</strong>,</p>
            <p>Thank you for reserving your stay at Avianna's Inland Resort. We will review and approve your booking shortly.</p>
            <p>Questions? Email: aviannasinlandresort@gmail.com</p>
        </div>";
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Email send failed: " . $mail->ErrorInfo);
        return false;
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name        = filter_input(INPUT_POST, 'name',           FILTER_SANITIZE_SPECIAL_CHARS);
    $email       = filter_input(INPUT_POST, 'email',          FILTER_SANITIZE_EMAIL);
    $contact     = filter_input(INPUT_POST, 'contact_number', FILTER_SANITIZE_SPECIAL_CHARS);
    $address     = filter_input(INPUT_POST, 'address',        FILTER_SANITIZE_SPECIAL_CHARS);
    $room_type   = filter_input(INPUT_POST, 'room_type',      FILTER_SANITIZE_SPECIAL_CHARS);
    $cottage_type= filter_input(INPUT_POST, 'cottage_type',   FILTER_SANITIZE_SPECIAL_CHARS);
    $pax         = filter_input(INPUT_POST, 'pax',            FILTER_SANITIZE_SPECIAL_CHARS);
    $checkin     = $_POST['checkin']  ?? '';
    $checkout    = $_POST['checkout'] ?? '';
    $payment     = filter_input(INPUT_POST, 'payment_method', FILTER_SANITIZE_SPECIAL_CHARS);
    $total_price = isset($_POST['total_price']) && is_numeric($_POST['total_price']) ? (float)$_POST['total_price'] : 0.00;
    $status      = 'Pending';

    $stmt = $conn->prepare("INSERT INTO bookings (name,email,contact,address,room_type,cottage_type,pax,checkin,checkout,payment_method,total_price,status) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)");
    $stmt->bind_param("ssssssssssds", $name,$email,$contact,$address,$room_type,$cottage_type,$pax,$checkin,$checkout,$payment,$total_price,$status);

    if ($stmt->execute()) {
        sendConfirmationEmail($email, $name);
        header("Location: book.php?success=1");
        exit();
    } else {
        error_log("DB Error: " . $stmt->error);
        echo "There was an error saving your booking. Please try again.";
    }
    $stmt->close();
    $conn->close();
}
?>
