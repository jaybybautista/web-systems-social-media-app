<?php
session_start();
include '../config.php';

if (!isset($_SESSION['id'])) {
    header("Location: ../login_modules/login.php");
    exit();
}

$userId = $_SESSION['id'];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Edit Profile</title>
    <script src="../jquery-3.7.1.js"></script>

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
            background: #fff;
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
            background: #fff;
            border-radius: 14px;
            max-width: 850px;
            margin: auto;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
            overflow: hidden;
        }

        .cover {
            width: 100%;
            height: 200px;
            background-size: cover;
            background-position: center;
            position: relative;
            
        }

        .profile-info {
            display: flex;
            align-items: flex-start;
            padding: 20px 30px;
            gap: 20px;
        }

        .profile-info img {
            width: 130px;
            height: 130px;
            border-radius: 50%;
            border: 4px solid #e0f2f1;
            object-fit: cover;
            margin-top: -60px;
            background: #fff;
        }


        .profile-details {
            flex: 1;
        }

        .profile-details h2 {
            color: #00796b;
            font-size: 24px;
            margin-bottom: 10px;
        }

        .profile-details label {
            font-weight: bold;
            color: #004d40;
            display: block;
            margin-top: 10px;
            margin-bottom: 5px;
        }

        .profile-details input[type="text"],
        .profile-details textarea,
        .profile-details input[type="file"] {
            width: 100%;
            padding: 8px 10px;
            border-radius: 6px;
            border: 1px solid #b2dfdb;
            margin-bottom: 10px;
            font-size: 14px;
        }

        .profile-details textarea {
            resize: vertical;
        }

        .profile-details button {
            padding: 10px 18px;
            background: #009688;
            color: #fff;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            margin-top: 10px;
            
        }

        .profile-details button:hover {
            background: #00796b;
        }


        .sidebar-right {
            width: 260px;
            background: #fff;
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


        @media(max-width:900px) {
            .sidebar-right {
                display: none;
            }
        }

        @media(max-width:700px) {
            .sidebar-left {
                display: none;
            }
        }
        #profilePic {
            z-index: 1000;
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
            <a href="logout.php">Logout</a>
        </div>
    </div>

    <div class="content">
        <div class="profile-card">
            <div class="cover" id="coverDiv"></div>
            <div class="profile-info">
                <img id="profilePic" src="" alt="Profile Picture">
                <div class="profile-details">
                    <h2 id="usernameHeader"></h2>

                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" required>

                    <label for="bio">Bio</label>
                    <textarea id="bio" name="bio" rows="3" placeholder="Write something about yourself..."></textarea>

                    <label for="picture">Profile Picture</label>
                    <input type="file" id="picture" name="picture">

                    <label for="cover_picture">Cover Picture</label>
                    <input type="file" id="cover_picture" name="cover_picture">

                    <button id="updateBtn">Update Profile</button>

                    <p id="msg"></p>
                </div>
            </div>
        </div>
    </div>

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

    <script>
        $(document).ready(function () {
            fetchProfile();

            $("#picture").on("change", function () {
                const [file] = this.files;
                if (file) { $("#profilePic").attr("src", URL.createObjectURL(file)); }
            });

            $("#cover_picture").on("change", function () {
                const [file] = this.files;
                if (file) { $("#coverDiv").css("background-image", "url(" + URL.createObjectURL(file) + ")"); }
            });

            $("#updateBtn").on("click", function (e) {
                e.preventDefault();
                var formData = new FormData();
                formData.append("username", $("#username").val());
                formData.append("bio", $("#bio").val());
                if ($("#picture")[0].files[0]) formData.append("picture", $("#picture")[0].files[0]);
                if ($("#cover_picture")[0].files[0]) formData.append("cover_picture", $("#cover_picture")[0].files[0]);

                $.ajax({
                    url: "ajax_update_profile.php",
                    type: "POST",
                    data: formData,
                    contentType: false,
                    processData: false,
                    success: function (response) {
                        if (response.trim() === "success") {
                            $("#msg").html("<span style='color:green'>Profile updated successfully!</span>");
                            fetchProfile();
                        } else {
                            $("#msg").html("<span style='color:red'>" + response + "</span>");
                        }
                    }
                });
            });
        });

        function fetchProfile() {
            $.get("fetch_profile.php", function (data) {
                let user = JSON.parse(data);
                $("#username").val(user.username);
                $("#usernameHeader").text(user.username);
                $("#bio").val(user.bio || "");
                $("#profilePic").attr("src", "../uploads/" + (user.picture || "default.png"));
                $("#coverDiv").css("background-image", "url('../uploads/" + (user.cover_picture || "default.png") + "')");
            });
        }
    </script>

</body>

</html>