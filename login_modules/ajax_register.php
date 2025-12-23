<?php
include '../config.php';

function emailExists($conn, $email) {
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->num_rows > 0;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $username = trim($_POST['username']);
    $email    = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm  = $_POST['confirm_password'];
    $type     = "user"; 

    if (empty($username) || empty($email) || empty($password) || empty($confirm)) {
        echo "All fields are required";
        exit;
    }

    if ($password !== $confirm) {
        echo "Passwords do not match";
        exit;
    }

    if (emailExists($conn, $email)) {
        echo "Email already registered";
        exit;
    }

    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

    $pictureName = "default.png";
    if (!empty($_FILES['picture']['name'])) {
        $pictureName = time() . "_" . $_FILES['picture']['name'];
        move_uploaded_file(
            $_FILES['picture']['tmp_name'],
            "../uploads/" . $pictureName
        );
    }

    $stmt = $conn->prepare("
        INSERT INTO users 
        (email, password_hash, username, picture, created_at, type)
        VALUES (?, ?, ?, ?, NOW(), ?)
    ");

    $stmt->bind_param(
        "sssss",
        $email,
        $passwordHash,
        $username,
        $pictureName,
        $type
    );

    if ($stmt->execute()) {
        echo "success";
    } else {
        echo "Registration failed";
    }
}
?>
