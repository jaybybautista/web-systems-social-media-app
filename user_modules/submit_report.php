<?php
session_start();
include('../config.php');

if (!isset($_SESSION['id']) || !isset($_POST['post_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$user_id = $_SESSION['id'];
$post_id = intval($_POST['post_id']);
$reason = mysqli_real_escape_string($conn, $_POST['reason']);
$date = date('Y-m-d H:i:s');

// Insert into the reports table based on your schema
$sql = "INSERT INTO reports (post_id, user_id, reason, created_at) 
        VALUES ($post_id, $user_id, '$reason', '$date')";

if (mysqli_query($conn, $sql)) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => mysqli_error($conn)]);
}
?>