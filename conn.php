<?php
// ── Database Configuration ────────────────────────────────────────────────
// Edit these values to match your hosting environment
$servername = "localhost";
$username   = "root";         // Change to your DB username
$password   = "";             // Change to your DB password
$dbname     = "avianna_resort";

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $conn = new mysqli($servername, $username, $password, $dbname);
    $conn->set_charset("utf8mb4");
} catch (Exception $e) {
    error_log("DB Connection Error: " . $e->getMessage());
    exit('Database connection failed. Please contact the administrator.');
}
?>
