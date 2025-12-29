<?php
session_start();
if (!isset($_SESSION['id'])) {
    header("Location: ../login_modules/login.php");
    exit();
}
include('../config.php');
$id = $_SESSION['id'];

$sql = "SELECT * FROM users WHERE id = $id";
$result = mysqli_query($conn, $sql);
$user = mysqli_fetch_assoc($result);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile | MySocial</title>
    <style>
        :root {
            --bg-dark: #051115;
            --card-bg: #112229;
            --accent-blue: #4db6ff;
            --text-white: #ffffff;
            --text-gray: #94a3b8;
            --border-color: #2a3f47;
            --input-bg: rgba(0, 0, 0, 0.2);
        }

        body {
            background: var(--bg-dark);
            color: white;
            font-family: 'Inter', system-ui, sans-serif;
            margin: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }

        .edit-container {
            background: var(--card-bg);
            width: 100%;
            max-width: 550px;
            border-radius: 28px;
            border: 1px solid var(--border-color);
            overflow: hidden;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.3);
        }

        /* Header / Visual Section */
        .header-visuals {
            position: relative;
            height: 200px;
            margin-bottom: 60px;
        }

        .cover-wrapper {
            width: 100%;
            height: 100%;
            background: #1a2a30;
            position: relative;
        }

        #c-prev {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .profile-wrapper {
            position: absolute;
            bottom: -50px;
            left: 30px;
            width: 110px;
            height: 110px;
            border-radius: 50%;
            border: 5px solid var(--card-bg);
            background: var(--card-bg);
            overflow: hidden;
        }

        #p-prev {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        /* Form Styling */
        .form-content {
            padding: 0 30px 30px 30px;
        }

        h2 {
            margin: 0 0 25px 0;
            font-size: 22px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            color: var(--text-gray);
            font-size: 13px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        input[type="text"],
        textarea {
            width: 100%;
            padding: 14px;
            background: var(--input-bg);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            color: white;
            outline: none;
            font-size: 15px;
            transition: border-color 0.3s, box-shadow 0.3s;
        }

        input:focus,
        textarea:focus {
            border-color: var(--accent-blue);
            box-shadow: 0 0 0 3px rgba(77, 182, 255, 0.1);
        }

        /* Custom File Input Styling */
        .file-input-wrapper {
            margin-top: 8px;
        }

        input[type="file"] {
            font-size: 12px;
            color: var(--text-gray);
        }

        input[type="file"]::file-selector-button {
            background: rgba(255, 255, 255, 0.05);
            color: white;
            border: 1px solid var(--border-color);
            padding: 6px 12px;
            border-radius: 8px;
            cursor: pointer;
            margin-right: 10px;
            transition: 0.3s;
        }

        input[type="file"]::file-selector-button:hover {
            background: rgba(255, 255, 255, 0.1);
        }

        .save-btn {
            background: var(--accent-blue);
            color: #051115;
            border: none;
            padding: 15px;
            border-radius: 50px;
            font-weight: 800;
            cursor: pointer;
            width: 100%;
            font-size: 16px;
            margin-top: 10px;
            transition: transform 0.2s, background 0.2s;
        }

        .save-btn:hover {
            background: #70c4ff;
            transform: translateY(-2px);
        }

        .back-link {
            display: block;
            text-align: center;
            margin-top: 20px;
            color: var(--text-gray);
            text-decoration: none;
            font-size: 14px;
            transition: color 0.3s;
        }

        .back-link:hover {
            color: white;
        }
    </style>
</head>

<body>

    <div class="edit-container">
        <form action="update_profile.php" method="POST" enctype="multipart/form-data">

            <div class="header-visuals">
                <div class="cover-wrapper">
                    <img src="../uploads/<?= htmlspecialchars($user['cover_picture'] ?? 'default_cover.jpg') ?>"
                        id="c-prev">
                </div>
                <div class="profile-wrapper">
                    <img src="../uploads/<?= htmlspecialchars($user['picture'] ?? 'default.png') ?>" id="p-prev">
                </div>
            </div>

            <div class="form-content">
                <h2>Edit Profile</h2>

                <div class="form-group">
                    <label>Change Profile Photo</label>
                    <div class="file-input-wrapper">
                        <input type="file" name="picture" accept="image/*" onchange="preview(this, 'p-prev')">
                    </div>
                </div>

                <div class="form-group">
                    <label>Change Cover Photo</label>
                    <div class="file-input-wrapper">
                        <input type="file" name="cover_picture" accept="image/*" onchange="preview(this, 'c-prev')">
                    </div>
                </div>

                <div class="form-group">
                    <label>Username</label>
                    <input type="text" name="username" value="<?= htmlspecialchars($user['username']) ?>"
                        placeholder="Enter username" required>
                </div>

                <div class="form-group">
                    <label>Bio</label>
                    <textarea name="bio" rows="3"
                        placeholder="Tell us about yourself..."><?= htmlspecialchars($user['bio']) ?></textarea>
                </div>

                <button type="submit" class="save-btn">Save Changes</button>
                <a href="profile.php" class="back-link">Cancel and Go Back</a>
            </div>
        </form>
    </div>

    <script>
        function preview(input, id) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function (e) {
                    document.getElementById(id).src = e.target.result;
                }
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>

</body>

</html>