<?php
function trackVisit($page_name, $conn) {
    $stmt = $conn->prepare("INSERT INTO site_analytics (page_name, visit_time, ip_address) VALUES (?, NOW(), ?)");
    $ip = $_SERVER['REMOTE_ADDR'];
    $stmt->bind_param("ss", $page_name, $ip);
    $stmt->execute();
    $stmt->close();
}
?>