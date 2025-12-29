<?php
session_start();
include('../config.php');

// Security Check
if (!isset($_SESSION['id'])) exit;
$admin_id = $_SESSION['id'];
$check = mysqli_query($conn, "SELECT type FROM users WHERE id = $admin_id");
$u = mysqli_fetch_assoc($check);
if ($u['type'] !== 'admin') exit;

$action = $_POST['action'] ?? '';

// --- ACTION: DELETE POST ---
if ($action == 'delete_post') {
    $post_id = $_POST['post_id'];
    $report_id = $_POST['report_id'];
    
    // Delete post (this will cascade delete likes/comments if your DB has Foreign Keys)
    mysqli_query($conn, "DELETE FROM posts WHERE id = $post_id");
    mysqli_query($conn, "DELETE FROM reports WHERE id = $report_id");
    echo json_encode(['success' => true]);
}

// --- ACTION: DELETE USER ---
if ($action == 'delete_user') {
    $user_id = mysqli_real_escape_string($conn, $_POST['user_id']);
    
    // Prevent admin from deleting themselves
    if ($user_id != $admin_id) {
        mysqli_query($conn, "DELETE FROM users WHERE id = $user_id");
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Cannot delete your own account.']);
    }
}

// --- ACTION: EDIT USER (New Feature) ---
if ($action == 'edit_user') {
    $user_id = mysqli_real_escape_string($conn, $_POST['user_id']);
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $type = mysqli_real_escape_string($conn, $_POST['type']);

    $sql = "UPDATE users SET 
            username = '$username', 
            email = '$email', 
            type = '$type' 
            WHERE id = $user_id";

    if (mysqli_query($conn, $sql)) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => mysqli_error($conn)]);
    }
}
?>