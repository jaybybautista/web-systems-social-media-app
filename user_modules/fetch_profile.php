<?php
session_start();
include '../config.php';

if (!isset($_SESSION['id'])) {
    exit;
}

$userId = $_SESSION['id'];

$stmt = $conn->prepare("SELECT username, picture FROM users WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

echo json_encode($result->fetch_assoc());
