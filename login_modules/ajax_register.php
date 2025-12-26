<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

/* CONFIG */
require_once __DIR__ . '/../config.php';

/* REQUEST CHECK */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    exit('Invalid request');
}

/* INPUT */
$username = trim($_POST['username'] ?? '');
$email    = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$confirm  = $_POST['confirm_password'] ?? '';
$type     = 'user';

if (!$username || !$email || !$password || !$confirm) {
    exit('All fields are required');
}

if ($password !== $confirm) {
    exit('Passwords do not match');
}

/* CHECK EMAIL */
$check = $conn->prepare("SELECT id FROM users WHERE email = ?");
$check->bind_param("s", $email);
$check->execute();
$check->store_result();

if ($check->num_rows > 0) {
    exit('Email already registered');
}
$check->close();

/* PASSWORD HASH */
$passwordHash = password_hash($password, PASSWORD_DEFAULT);

/* IMAGE UPLOAD */
$pictureName = 'default.png';
$uploadPath = __DIR__ . '/../uploads/';

if (!is_dir($uploadPath)) {
    mkdir($uploadPath, 0777, true);
}

if (!empty($_FILES['picture']['name'])) {
    $ext = pathinfo($_FILES['picture']['name'], PATHINFO_EXTENSION);
    $pictureName = time() . "_" . uniqid() . "." . $ext;

    if (!move_uploaded_file($_FILES['picture']['tmp_name'], $uploadPath . $pictureName)) {
        exit('Image upload failed');
    }
}

/* INSERT USER */
$stmt = $conn->prepare("
    INSERT INTO users (email, password_hash, username, picture, type)
    VALUES (?, ?, ?, ?, ?)
");

if (!$stmt) {
    exit('Prepare failed: ' . $conn->error);
}

$stmt->bind_param(
    "sssss",
    $email,
    $passwordHash,
    $username,
    $pictureName,
    $type
);

if ($stmt->execute()) {
    echo 'success';
} else {
    echo 'Database error: ' . $stmt->error;
}
