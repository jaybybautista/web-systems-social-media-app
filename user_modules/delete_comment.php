<?php
session_start();
include('../config.php');
header('Content-Type: application/json');

$user_id = $_SESSION['id'] ?? null;
$comment_id = intval($_POST['comment_id'] ?? 0);

if (!$user_id || !$comment_id) {
    echo json_encode(['success' => false]);
    exit;
}

/* VERIFY OWNERSHIP */
$stmt = $conn->prepare("SELECT user_id FROM comments WHERE id = ?");
$stmt->bind_param("i", $comment_id);
$stmt->execute();
$stmt->bind_result($owner_id);
$stmt->fetch();
$stmt->close();

if ($owner_id != $user_id) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

/* DELETE */
$stmt = $conn->prepare("DELETE FROM comments WHERE id = ?");
$stmt->bind_param("i", $comment_id);
$stmt->execute();
$stmt->close();

echo json_encode(['success' => true]);
