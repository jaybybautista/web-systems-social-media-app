<?php
session_start();
include '../config.php';

if (!isset($_SESSION['id'])) {
    header("Location: ../login_modules/login.php");
    exit();
}

$userId = $_SESSION['id'];

$stmt = $conn->prepare("
    SELECT email, username, picture, cover_picture, created_at, type, bio 
    FROM users 
    WHERE id = ?
");
$stmt->bind_param("i", $userId);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Profile</title>

    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        }

        body {
            background: #e0f2f1;
            display: flex;
            min-height: 100vh;
        }
        .sidebar-left {
            width: 230px;
            background: #ffffff;
            border-right: 1px solid #cfd8dc;
            padding: 20px;
        }

        .sidebar-left h3 {
            margin-bottom: 30px;
            font-size: 20px;
            color: #00796b;
        }

        .nav a {
            display: block;
            padding: 10px 12px;
            margin-bottom: 8px;
            text-decoration: none;
            color: #004d40;
            border-radius: 8px;
            transition: 0.2s;
        }

        .nav a:hover {
            background: #b2dfdb;
        }

        .content {
            flex: 1;
            padding: 40px;
        }

        .profile-card {
            background: #ffffff;
            border-radius: 14px;
            max-width: 850px;
            margin: auto;
            box-shadow: 0 8px 25px rgba(0,0,0,0.08);
            overflow: hidden;
        }

        .cover {
            width: 100%;
            height: 200px;
            background-image: url('../uploads/<?php echo htmlspecialchars($user['cover_picture'] ?? "default.png"); ?>');
            background-size: cover;
            background-position: center;
            position: relative;
        }

        .profile-info {
            display: flex;
            align-items: center;
            padding: 20px 30px;
        }

        .profile-info img {
            width: 130px;
            height: 130px;
            border-radius: 50%;
            border: 4px solid #e0f2f1;
            object-fit: cover;
            margin-right: 25px;
            position: relative;
            top: -80px; 
            background: #ffffff;
        }

        .profile-details h2 {
            color: #00796b;
            font-size: 26px;
            margin-bottom: 4px;
        }

        .profile-details p {
            color: #555;
            margin: 4px 0;
            font-size: 14px;

        }

        .profile-meta {
            margin-top: 12px;
        }

        .profile-meta strong {
            color: #004d40;
        }

        .actions {
            margin-top: 30px;
        }

        .actions a {
            text-decoration: none;
            padding: 8px 16px;
            background: #009688;
            color: white;
            border-radius: 6px;
            margin-right: 10px;
            transition: 0.2s;
        }

        .actions a:hover {
            background: #00796b;
        }

        .sidebar-right {
            width: 260px;
            background: #ffffff;
            border-left: 1px solid #cfd8dc;
            padding: 20px;
        }

        .sidebar-right h4 {
            margin-bottom: 15px;
            color: #00796b;
        }

        .follower {
            display: flex;
            align-items: center;
            margin-bottom: 12px;
        }

        .follower img {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            margin-right: 10px;
            object-fit: cover;
        }

        .follower span {
            font-size: 14px;
            color: #004d40;
        }

        @media (max-width: 900px) {
            .sidebar-right {
                display: none;
            }
        }

        @media (max-width: 700px) {
            .sidebar-left {
                display: none;
            }
        }
    </style>
</head>

<body>

<div class="sidebar-left">
    <h3>MySocial</h3>
    <div class="nav">
        <a href="profile.php">Profile</a>
        <a href="newsfeed.php">Newsfeed</a>
        <a href="followers.php">Followers</a>
        <a href="../logout.php">Logout</a>
    </div>
</div>

<div class="content">
    <div class="profile-card">
        <div class="cover"></div>

        <div class="profile-info">
            <img src="../uploads/<?php echo htmlspecialchars($user['picture']); ?>" alt="Profile Picture">

            <div class="profile-details">
                <h2><?php echo htmlspecialchars($user['username']); ?></h2>
                <p><?php echo htmlspecialchars($user['email']); ?></p>

                <div class="profile-meta">
                    <p><?php echo htmlspecialchars($user['bio']); ?></p>
                    <p><strong>Joined:</strong> <?php echo date("F d, Y", strtotime($user['created_at'])); ?></p>
                </div>
                <div class="actions">
                    <a href="user_profile.php">Edit Profile</a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- EDIT THIS INTO THE FOLLOWERS LIST -->
<div class="sidebar-right">
    <h4>Followers</h4>

    <div class="follower">
        <img src="../uploads/default.png">
        <span>jane_doe</span>
    </div>

    <div class="follower">
        <img src="../uploads/default.png">
        <span>john_smith</span>
    </div>

    <div class="follower">
        <img src="../uploads/default.png">
        <span>alex_dev</span>
    </div>

</div>

</body>
</html>
