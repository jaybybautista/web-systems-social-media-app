<?php
// Report handler for HTML form POST (not JSON)
// Expects POST fields: post_id, reason
session_start();
include('../config.php');

if (!isset($_SESSION['id'])) {
  die('You must be logged in to report a post.');
}

$post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
$reason = isset($_POST['reason']) ? trim($_POST['reason']) : '';
$user_id = $_SESSION['id'];

if ($post_id <= 0) {
  die('Invalid post ID.');
}

if ($reason === '') {
  die('Reason is required.');
}

$reason = substr($reason, 0, 1000);
$reason_safe = mysqli_real_escape_string($conn, $reason);

$post_check = mysqli_query($conn, "SELECT id FROM posts WHERE id = $post_id");
if (!$post_check || mysqli_num_rows($post_check) === 0) {
  die('Post not found.');
}

$insert_sql = "INSERT INTO reports (post_id, user_id, reason, created_at) VALUES ($post_id, $user_id, '$reason_safe', NOW())";
$insert_result = mysqli_query($conn, $insert_sql);

if ($insert_result) {
  echo '<script>alert("Report submitted. Thank you."); window.history.back();</script>';
} else {
  echo '<script>alert("Failed to submit report."); window.history.back();</script>';
}
