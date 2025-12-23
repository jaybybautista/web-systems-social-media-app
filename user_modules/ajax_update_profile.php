<?php
session_start();
include '../config.php';

if (!isset($_SESSION['id'])) {
    echo "User not logged in";
    exit();
}

$userId = $_SESSION['id'];

$stmt = $conn->prepare("SELECT picture, cover_picture FROM users WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$currentProfile = $user['picture'] ?? 'default.png';
$currentCover = $user['cover_picture'] ?? 'default.png';

$username = trim($_POST['username']);
$bio = trim($_POST['bio']);

$newProfile = $currentProfile;
if(isset($_FILES['picture']) && $_FILES['picture']['name'] != ""){
    
    if($currentProfile != 'default.png' && file_exists("../uploads/".$currentProfile)){
        unlink("../uploads/".$currentProfile);
    }
    $newProfile = time().'_'.basename($_FILES['picture']['name']);
    move_uploaded_file($_FILES['picture']['tmp_name'], "../uploads/".$newProfile);
}

$newCover = $currentCover;
if(isset($_FILES['cover_picture']) && $_FILES['cover_picture']['name'] != ""){
    if($currentCover != 'default.png' && file_exists("../uploads/".$currentCover)){
        unlink("../uploads/".$currentCover);
    }
    $newCover = time().'_'.basename($_FILES['cover_picture']['name']);
    move_uploaded_file($_FILES['cover_picture']['tmp_name'], "../uploads/".$newCover);
}

$stmt = $conn->prepare("
    UPDATE users 
    SET username = ?, bio = ?, picture = ?, cover_picture = ?
    WHERE id = ?
");
$stmt->bind_param("ssssi", $username, $bio, $newProfile, $newCover, $userId);

if($stmt->execute()){
    echo "success";
}else{
    echo "Failed to update profile. Try again.";
}
