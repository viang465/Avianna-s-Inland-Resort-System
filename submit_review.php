<?php
include "conn.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name        = htmlspecialchars(trim($_POST['name']        ?? ''), ENT_QUOTES, 'UTF-8');
    $rating      = (int)($_POST['rating'] ?? 0);
    $review_text = htmlspecialchars(trim($_POST['review_text'] ?? ''), ENT_QUOTES, 'UTF-8');
    $photo_name  = "";

    if ($rating < 1 || $rating > 5) $rating = 3;

    if (isset($_FILES['review_photo']) && $_FILES['review_photo']['error'] === 0) {
        $upload_dir = __DIR__ . "/uploads/";
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
        $file_ext = strtolower(pathinfo($_FILES['review_photo']['name'], PATHINFO_EXTENSION));
        $allowed  = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        if (in_array($file_ext, $allowed)) {
            $photo_name  = time() . "_" . uniqid() . "." . $file_ext;
            if (!move_uploaded_file($_FILES['review_photo']['tmp_name'], $upload_dir . $photo_name)) {
                $photo_name = "";
            }
        }
    }

    $stmt = $conn->prepare("INSERT INTO reviews (name, rating, review_text, photo_path, submission_date) VALUES (?, ?, ?, ?, NOW())");
    $stmt->bind_param("siss", $name, $rating, $review_text, $photo_name);

    if ($stmt->execute()) {
        header("Location: reviews.php?success=1");
    } else {
        error_log("Review insert error: " . $stmt->error);
        header("Location: reviews.php?error=1");
    }
    $stmt->close();
    exit();
} else {
    header("Location: reviews.php");
    exit();
}
?>
