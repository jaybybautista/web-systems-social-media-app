<?php
session_start();
include('../config.php');

// Set response header to JSON
header('Content-Type: application/json');

// Ensure user is logged in and following_id is provided
if (!isset($_SESSION['id']) || !isset($_POST['following_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized or missing ID']);
    exit();
}

$follower_id = $_SESSION['id'];
$following_id = (int) $_POST['following_id'];

// Prevent following yourself (Safety check)
if ($follower_id == $following_id) {
    echo json_encode(['success' => false, 'message' => 'You cannot follow yourself.']);
    exit();
}

/* 1. CHECK IF ALREADY FOLLOWING */
$check_sql = "SELECT 1 FROM follows WHERE follower_id = ? AND following_id = ?";
$stmt = mysqli_prepare($conn, $check_sql);
mysqli_stmt_bind_param($stmt, "ii", $follower_id, $following_id);
mysqli_stmt_execute($stmt);
$check_result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($check_result) > 0) {
    /* 2. ALREADY FOLLOWING -> UNFOLLOW */
    $delete_sql = "DELETE FROM follows WHERE follower_id = ? AND following_id = ?";
    $del_stmt = mysqli_prepare($conn, $delete_sql);
    mysqli_stmt_bind_param($del_stmt, "ii", $follower_id, $following_id);

    if (mysqli_stmt_execute($del_stmt)) {
        echo json_encode(['success' => true, 'status' => 'unfollowed']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error during unfollow']);
    }
} else {
    /* 3. NOT FOLLOWING -> FOLLOW */
    $insert_sql = "INSERT INTO follows (follower_id, following_id) VALUES (?, ?)";
    $ins_stmt = mysqli_prepare($conn, $insert_sql);
    mysqli_stmt_bind_param($ins_stmt, "ii", $follower_id, $following_id);

    if (mysqli_stmt_execute($ins_stmt)) {
        echo json_encode(['success' => true, 'status' => 'followed']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error during follow']);
    }
}
?>