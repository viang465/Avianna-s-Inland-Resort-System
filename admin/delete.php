<?php
session_start();

include "../conn.php";


if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}


if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    
    
    $stmt = $conn->prepare("DELETE FROM bookings WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
    
        header("Location: admin.php?delete=success");
        exit();
    } else {
        echo "Error deleting record: " . $conn->error;
    }
    $stmt->close();
} else {
    
    header("Location: admin.php");
    exit();
}
?>