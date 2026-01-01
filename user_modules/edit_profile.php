<?php
session_start();
if (!isset($_SESSION['id'])) {
    header("Location: ../login_modules/login.php");
    exit();
}
include('../config.php');
$id = $_SESSION['id'];

/* Use Prepared Statement for Security */
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile | MySocial</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        :root {
            --bg-dark: #051115;
            --card-bg: #112229;
            --accent-blue: #4db6ff;
            --text-white: #ffffff;
            --text-gray: #94a3b8;
            --border-color: #2a3f47;
            --input-bg: rgba(0, 0, 0, 0.3);
        }

        * {
            box-sizing: border-box;
        }

        body {
            background: var(--bg-dark);
            color: white;
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
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
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.5);
        }

        /* Enhanced Header / Visual Section */
        .header-visuals {
            position: relative;
            height: 220px;
            margin-bottom: 70px;
        }

        .cover-wrapper {
            width: 100%;
            height: 100%;
            background: #1a2a30;
            position: relative;
            cursor: pointer;
        }

        .cover-wrapper:hover .overlay-icon {
            opacity: 1;
        }

        #c-prev {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: filter 0.3s;
        }

        .profile-wrapper {
            position: absolute;
            bottom: -50px;
            left: 30px;
            width: 120px;
            height: 120px;
            border-radius: 35px;
            /* Matched the profile card style */
            border: 6px solid var(--card-bg);
            background: var(--card-bg);
            overflow: hidden;
            cursor: pointer;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.3);
        }

        .profile-wrapper:hover .overlay-icon {
            opacity: 1;
        }

        #p-prev {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .overlay-icon {
            position: absolute;
            inset: 0;
            background: rgba(0, 0, 0, 0.4);
            display: flex;
            justify-content: center;
            align-items: center;
            opacity: 0;
            transition: opacity 0.3s;
            color: white;
            font-size: 24px;
        }

        /* Form Styling */
        .form-content {
            padding: 0 40px 40px 40px;
        }

        h2 {
            margin: 0 0 10px 0;
            font-size: 24px;
            font-weight: 800;
        }

        .subtitle {
            color: var(--text-gray);
            font-size: 14px;
            margin-bottom: 30px;
        }

        .form-group {
            margin-bottom: 22px;
        }

        label {
            display: block;
            margin-bottom: 10px;
            color: var(--text-gray);
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        input[type="text"],
        textarea {
            width: 100%;
            padding: 16px;
            background: var(--input-bg);
            border: 1px solid var(--border-color);
            border-radius: 14px;
            color: white;
            outline: none;
            font-size: 15px;
            transition: all 0.3s;
        }

        input:focus,
        textarea:focus {
            border-color: var(--accent-blue);
            background: rgba(77, 182, 255, 0.05);
            box-shadow: 0 0 0 4px rgba(77, 182, 255, 0.15);
        }

        /* Hidden actual file inputs, triggered by wrappers */
        .hidden-file {
            display: none;
        }

        .save-btn {
            background: var(--accent-blue);
            color: #051115;
            border: none;
            padding: 16px;
            border-radius: 16px;
            font-weight: 800;
            cursor: pointer;
            width: 100%;
            font-size: 16px;
            margin-top: 15px;
            transition: all 0.3s;
            box-shadow: 0 4px 15px rgba(77, 182, 255, 0.3);
        }

        .save-btn:hover {
            background: #70c4ff;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(77, 182, 255, 0.4);
        }

        .back-link {
            display: block;
            text-align: center;
            margin-top: 25px;
            color: var(--text-gray);
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            transition: color 0.3s;
        }

        .back-link:hover {
            color: var(--accent-blue);
        }
    </style>
</head>

<body>

    <div class="edit-container">
        <form action="update_profile.php" method="POST" enctype="multipart/form-data">

            <div class="header-visuals">
                <div class="cover-wrapper" onclick="document.getElementById('coverInput').click()">
                    <img src="../uploads/<?= htmlspecialchars($user['cover_picture'] ?? 'default_cover.jpg') ?>"
                        id="c-prev">
                    <div class="overlay-icon"><i class="fa-solid fa-camera"></i></div>
                </div>
                <div class="profile-wrapper" onclick="document.getElementById('profileInput').click()">
                    <img src="../uploads/<?= htmlspecialchars($user['picture'] ?? 'default.png') ?>" id="p-prev">
                    <div class="overlay-icon"><i class="fa-solid fa-camera"></i></div>
                </div>
            </div>

            <div class="form-content">
                <h2>Edit Profile</h2>
                <p class="subtitle">Update your personal information and profile visuals.</p>

                <input type="file" id="profileInput" name="picture" class="hidden-file" accept="image/*"
                    onchange="preview(this, 'p-prev')">
                <input type="file" id="coverInput" name="cover_picture" class="hidden-file" accept="image/*"
                    onchange="preview(this, 'c-prev')">

                <div class="form-group">
                    <label>Username</label>
                    <input type="text" name="username" value="<?= htmlspecialchars($user['username']) ?>"
                        placeholder="Choose a unique username" required>
                </div>

                <div class="form-group">
                    <label>Bio</label>
                    <textarea name="bio" rows="4"
                        placeholder="Tell the world about yourself..."><?= htmlspecialchars($user['bio']) ?></textarea>
                </div>

                <button type="submit" class="save-btn">Update Profile</button>
                <a href="profile.php" class="back-link">Cancel changes</a>
            </div>
        </form>
    </div>

    <script>
        function preview(input, id) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function (e) {
                    document.getElementById(id).src = e.target.result;
                    // Add a small pop effect when image updates
                    document.getElementById(id).style.transform = "scale(1.02)";
                    setTimeout(() => {
                        document.getElementById(id).style.transform = "scale(1)";
                    }, 200);
                }
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>

</body>

</html>