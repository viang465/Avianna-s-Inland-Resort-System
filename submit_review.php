<?php
/**
 * submit_review.php
 * Legacy endpoint — review submission is handled directly in reviews.php.
 * This file redirects to prevent duplicate inserts if linked directly.
 */
header("Location: reviews.php");
exit();
?>
