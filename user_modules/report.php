<?php
// Report handler for HTML form POST
// Expects POST fields: post_id, reason
session_start();
include('../config.php');

if (!isset($_SESSION['id'])) {
  die('You must be logged in to report a post.');
}

$post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
// Default reason if one isn't provided via a form (useful for quick-reports)
$reason = isset($_POST['reason']) ? trim($_POST['reason']) : 'Reported by user via profile menu';
$user_id = $_SESSION['id'];

if ($post_id <= 0) {
  echo '<script>alert("Invalid post ID."); window.history.back();</script>';
  exit();
}

// Limit the reason length for database performance
$reason = substr($reason, 0, 1000);
$reason_safe = mysqli_real_escape_string($conn, $reason);

// Check if the post actually exists before reporting
$post_check = mysqli_query($conn, "SELECT id FROM posts WHERE id = $post_id");
if (!$post_check || mysqli_num_rows($post_check) === 0) {
  echo '<script>alert("Post not found."); window.history.back();</script>';
  exit();
}

// Check if user has already reported this specific post to avoid duplicates
$duplicate_check = mysqli_query($conn, "SELECT id FROM reports WHERE post_id = $post_id AND user_id = $user_id");
if (mysqli_num_rows($duplicate_check) > 0) {
  echo '<script>alert("You have already reported this post."); window.history.back();</script>';
  exit();
}

// Insert the report into the reports table
// Note: Ensure your 'reports' table has these columns: id (AI), post_id, user_id, reason, created_at
$insert_sql = "INSERT INTO reports (post_id, user_id, reason, created_at) VALUES ($post_id, $user_id, '$reason_safe', NOW())";
$insert_result = mysqli_query($conn, $insert_sql);

if ($insert_result) {
  echo '<script>alert("Report submitted successfully. We will review it shortly."); window.history.back();</script>';
} else {
  // If the database insert fails (likely due to missing table or column mismatch)
  echo '<script>alert("Error: Failed to submit report. Please try again later."); window.history.back();</script>';
}
?>