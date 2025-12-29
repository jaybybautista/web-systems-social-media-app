<?php
session_start();
include('../config.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SESSION['id'])) {
    $id = $_SESSION['id'];
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $bio = mysqli_real_escape_string($conn, $_POST['bio']);

    // Handle Profile Picture Upload
    $picture_sql = "";
    if (!empty($_FILES['picture']['name'])) {
        $picName = time() . '_' . $_FILES['picture']['name'];
        move_uploaded_file($_FILES['picture']['tmp_name'], "../uploads/" . $picName);
        $picture_sql = ", picture='$picName'";
    }

    // Handle Cover Picture Upload
    $cover_sql = "";
    if (!empty($_FILES['cover_picture']['name'])) {
        $coverName = time() . '_cover_' . $_FILES['cover_picture']['name'];
        move_uploaded_file($_FILES['cover_picture']['tmp_name'], "../uploads/" . $coverName);
        $cover_sql = ", cover_picture='$coverName'";
    }

    $update_query = "UPDATE users SET 
                     username = '$username', 
                     bio = '$bio' 
                     $picture_sql 
                     $cover_sql 
                     WHERE id = $id";

    if (mysqli_query($conn, $update_query)) {
        header("Location: profile.php?msg=updated");
    } else {
        echo "Error updating profile: " . mysqli_error($conn);
    }
}
?>