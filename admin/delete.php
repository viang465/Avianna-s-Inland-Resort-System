<?php
session_start();

include "../conn.php";


if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// FIX: Use POST instead of GET to prevent CSRF and accidental deletion via browser link
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id = intval($_POST['id']);

    $stmt = $conn->prepare("DELETE FROM bookings WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
    
        header("Location: admin.php?delete=success");
        exit();
    } else {
        echo "Error deleting record: " . htmlspecialchars($conn->error);
    }
    $stmt->close();
} else {
    
    header("Location: admin.php");
    exit();
}
?>