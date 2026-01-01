<?php
session_start();
include('../config.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SESSION['id'])) {
    $id = $_SESSION['id'];
    $username = $_POST['username'];
    $bio = $_POST['bio'];

    // Initialize arrays for dynamic query building
    $update_fields = ["username = ?", "bio = ?"];
    $params = [$username, $bio];
    $types = "ss";

    // Handle Profile Picture Upload
    if (!empty($_FILES['picture']['name'])) {
        $picExtension = pathinfo($_FILES['picture']['name'], PATHINFO_EXTENSION);
        $picName = time() . '_profile.' . $picExtension;

        if (move_uploaded_file($_FILES['picture']['tmp_name'], "../uploads/" . $picName)) {
            $update_fields[] = "picture = ?";
            $params[] = $picName;
            $types .= "s";
        }
    }

    // Handle Cover Picture Upload
    if (!empty($_FILES['cover_picture']['name'])) {
        $coverExtension = pathinfo($_FILES['cover_picture']['name'], PATHINFO_EXTENSION);
        $coverName = time() . '_cover.' . $coverExtension;

        if (move_uploaded_file($_FILES['cover_picture']['tmp_name'], "../uploads/" . $coverName)) {
            $update_fields[] = "cover_picture = ?";
            $params[] = $coverName;
            $types .= "s";
        }
    }

    // Add User ID to parameters
    $params[] = $id;
    $types .= "i";

    // Build the dynamic prepared statement
    $sql = "UPDATE users SET " . implode(", ", $update_fields) . " WHERE id = ?";

    $stmt = $conn->prepare($sql);

    if ($stmt) {
        $stmt->bind_param($types, ...$params);

        if ($stmt->execute()) {
            header("Location: profile.php?msg=updated");
            exit();
        } else {
            echo "Error executing update: " . $stmt->error;
        }
        $stmt->close();
    } else {
        echo "Error preparing statement: " . $conn->error;
    }
} else {
    header("Location: profile.php");
    exit();
}
?>