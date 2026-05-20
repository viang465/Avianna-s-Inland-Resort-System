<?php
/**
 * smtp_test.php
 * ─────────────────────────────────────────────────────────────────────────────
 * SMTP Diagnostic Tool for Avianna's Inland Resort
 *
 * HOW TO USE:
 *   1. Place this file in your project root (same folder as PHPMailer.php)
 *   2. Open in browser: http://localhost/your-project/smtp_test.php
 *   3. Click "Run SMTP Test" — it will show the exact error if mail fails
 *   4. DELETE this file from your server after fixing (it exposes credentials)
 * ─────────────────────────────────────────────────────────────────────────────
 */

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/PHPMailer.php';
require __DIR__ . '/Exception.php';
require __DIR__ . '/SMTP.php';

// ── CONFIG — edit these to match your settings ────────────────────────────
$SMTP_USER   = 'aviannasinlandresort@gmail.com';
$SMTP_PASS   = str_replace(' ', '', 'dmhk acwo qpej zxyy'); // App Password, spaces stripped
$SEND_TO     = 'aviannasinlandresort@gmail.com'; // test recipient (can be same as sender)
// ─────────────────────────────────────────────────────────────────────────

$result  = null;
$log     = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Override recipient if provided in form
    $SEND_TO = filter_input(INPUT_POST, 'send_to', FILTER_SANITIZE_EMAIL) ?: $SEND_TO;

    $mail = new PHPMailer(true);

    // Capture SMTP transcript into $log array
    $mail->SMTPDebug  = SMTP::DEBUG_SERVER;
    $mail->Debugoutput = function(string $str, int $level) use (&$log): void {
        $log[] = htmlspecialchars(trim($str));
    };

    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = $SMTP_USER;
        $mail->Password   = $SMTP_PASS;
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

        $mail->setFrom($SMTP_USER, "Avianna's Resort - SMTP Test");
        $mail->addAddress($SEND_TO);
        $mail->isHTML(true);
        $mail->Subject = 'SMTP Test - Avianna Resort System';
        $mail->Body    = '<p>This is a <strong>test email</strong> from the Avianna Resort booking system. If you see this, SMTP is working correctly!</p>';
        $mail->AltBody = 'SMTP test successful. Your email system is working.';

        $mail->send();
        $success = true;
        $result  = "SUCCESS — Email sent to {$SEND_TO}";

    } catch (Exception $e) {
        $success = false;
        $result  = "FAILED — " . $mail->ErrorInfo;
    }
}

// ── Checklist: system environment ─────────────────────────────────────────
$checks = [
    'PHP Version'              => PHP_VERSION . (version_compare(PHP_VERSION, '7.4', '>=') ? ' ✅' : ' ❌ (need 7.4+)'),
    'OpenSSL Extension'        => extension_loaded('openssl')  ? '✅ Loaded' : '❌ Not loaded — required for TLS',
    'cURL Extension'           => extension_loaded('curl')     ? '✅ Loaded' : '⚠️ Not loaded',
    'allow_url_fopen'          => ini_get('allow_url_fopen')   ? '✅ On'     : '⚠️ Off',
    'SMTP (php.ini)'           => ini_get('SMTP')              ?: '(not set)',
    'smtp_port (php.ini)'      => ini_get('smtp_port')         ?: '(not set)',
    'PHPMailer.php found'      => file_exists(__DIR__ . '/PHPMailer.php') ? '✅ Yes' : '❌ Not found',
    'Exception.php found'      => file_exists(__DIR__ . '/Exception.php') ? '✅ Yes' : '❌ Not found',
    'SMTP.php found'           => file_exists(__DIR__ . '/SMTP.php')      ? '✅ Yes' : '❌ Not found',
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>SMTP Diagnostic — Avianna's Resort</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f0f4f3; padding: 40px 20px; }
        .card { border: none; border-radius: 16px; box-shadow: 0 8px 24px rgba(0,0,0,0.08); }
        .card-header { background: #1e4d40; color: white; border-radius: 16px 16px 0 0 !important; padding: 20px 24px; }
        pre { background: #0d1117; color: #58d68d; font-size: .78rem; padding: 16px; border-radius: 10px;
              max-height: 360px; overflow-y: auto; white-space: pre-wrap; word-break: break-all; }
        .check-row { display: flex; justify-content: space-between; padding: 7px 0;
                     border-bottom: 1px solid #f0f0f0; font-size: .9rem; }
        .delete-warning { background: #fff3cd; border-left: 4px solid #f59e0b;
                          padding: 12px 16px; border-radius: 8px; font-size: .88rem; }
    </style>
</head>
<body>
<div class="container" style="max-width:780px;">

    <div class="card mb-4">
        <div class="card-header">
            <h4 class="mb-0">🔧 SMTP Diagnostic Tool</h4>
            <small style="opacity:.75;">Avianna's Inland Resort — Booking Email System</small>
        </div>
        <div class="card-body p-4">

            <div class="delete-warning mb-4">
                ⚠️ <strong>Security notice:</strong> Delete <code>smtp_test.php</code> from your server
                after you finish testing. It exposes your email credentials.
            </div>

            <!-- Environment Checklist -->
            <h6 class="fw-bold text-uppercase mb-3" style="letter-spacing:1px;color:#555;">
                System Environment
            </h6>
            <?php foreach ($checks as $label => $value): ?>
            <div class="check-row">
                <span class="text-muted"><?= $label ?></span>
                <span><?= $value ?></span>
            </div>
            <?php endforeach; ?>

            <hr class="my-4">

            <!-- Test Form -->
            <h6 class="fw-bold text-uppercase mb-3" style="letter-spacing:1px;color:#555;">
                Send Test Email
            </h6>
            <form method="POST" class="row g-3">
                <div class="col-md-8">
                    <label class="form-label small fw-bold">Send test email to:</label>
                    <input type="email" name="send_to" class="form-control"
                           value="<?= htmlspecialchars($SEND_TO) ?>"
                           placeholder="recipient@example.com">
                    <div class="form-text">Leave as-is to send to the resort inbox, or enter any address.</div>
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn w-100"
                            style="background:#1e4d40;color:white;border-radius:10px;padding:11px;">
                        ▶ Run SMTP Test
                    </button>
                </div>
            </form>

            <?php if ($result !== null): ?>
            <hr class="my-4">
            <h6 class="fw-bold text-uppercase mb-3" style="letter-spacing:1px;color:#555;">
                Test Result
            </h6>
            <div class="alert <?= $success ? 'alert-success' : 'alert-danger' ?> rounded-3">
                <?= $success ? '✅' : '❌' ?> <strong><?= htmlspecialchars($result) ?></strong>
            </div>

            <?php if (!$success): ?>
            <!-- Common fix guide -->
            <div class="card bg-light border-0 mb-3">
                <div class="card-body">
                    <h6 class="fw-bold">Common causes & fixes:</h6>
                    <ul class="mb-0 small">
                        <li><strong>535 Authentication failed</strong> — App Password is wrong or has not been generated. Go to <a href="https://myaccount.google.com/apppasswords" target="_blank">myaccount.google.com/apppasswords</a>, create a new one for "Mail / Windows Computer", copy all 16 chars with no spaces.</li>
                        <li><strong>Connection timed out / could not connect</strong> — Your ISP or antivirus is blocking port 587. Try switching to port <strong>465</strong> with <code>ENCRYPTION_SMIME</code>, or use a local mail relay like <a href="https://mailtrap.io" target="_blank">Mailtrap</a> for testing.</li>
                        <li><strong>STARTTLS failed</strong> — OpenSSL extension is missing. In XAMPP: open <code>php.ini</code>, find <code>;extension=openssl</code>, remove the semicolon, restart Apache.</li>
                        <li><strong>2FA / Less secure app</strong> — Make sure 2-Step Verification is ON in your Google account. App Passwords only work when 2FA is enabled.</li>
                        <li><strong>Google blocked the sign-in</strong> — Check <a href="https://myaccount.google.com/security" target="_blank">myaccount.google.com/security</a> for any security alerts and allow the access.</li>
                    </ul>
                </div>
            </div>
            <?php endif; ?>

            <h6 class="fw-bold text-uppercase mb-2" style="letter-spacing:1px;color:#555;">
                SMTP Transcript
            </h6>
            <pre><?php
                if (empty($log)) {
                    echo '(no SMTP conversation — connection failed before handshake)';
                } else {
                    echo implode("\n", $log);
                }
            ?></pre>
            <?php endif; ?>

        </div>
    </div>

    <div class="text-center">
        <a href="admin/admin.php" class="text-muted text-decoration-none small">← Back to Admin Panel</a>
    </div>

</div>
</body>
</html>