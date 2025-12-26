<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create Account - MySocial</title>
    <script src="../jquery-3.7.1.js"></script>

    <style>
        * { box-sizing:border-box; margin:0; padding:0; font-family:system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif; }
        body { display:flex; justify-content:center; align-items:center; min-height:100vh; background:#e0f2f1; }

        .register-card {
            background:#fff;
            padding:40px 30px;
            border-radius:14px;
            max-width:400px;
            width:100%;
            box-shadow:0 8px 25px rgba(0,0,0,0.08);
            text-align:center;
        }

        .register-card h2 {
            color:#00796b;
            margin-bottom:25px;
        }

        .register-card input {
            width:100%;
            padding:12px 15px;
            margin-bottom:15px;
            border:1px solid #b2dfdb;
            border-radius:6px;
            font-size:14px;
        }

        .register-card button {
            width:100%;
            padding:12px;
            background:#009688;
            border:none;
            border-radius:6px;
            color:#fff;
            font-size:16px;
            cursor:pointer;
        }

        .register-card button:hover { background:#00796b; }

        #msg { margin-bottom:15px; font-size:14px; }
    </style>
</head>
<body>

<div class="register-card">
    <h2>Create Account</h2>

    <p id="msg"></p>

    <form id="registerForm" enctype="multipart/form-data">
        <input type="text" name="username" placeholder="Username" required>
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="password" placeholder="Password" required>
        <input type="password" name="confirm_password" placeholder="Confirm Password" required>
        <input type="file" name="picture">
        <button type="submit">Register</button>
    </form>

    <p>Already have an account? <a href="login.php">Login</a></p>
</div>

<script>
$("#registerForm").on("submit", function(e){
    e.preventDefault();

    $("#msg").html("Registering...");

    $.ajax({
        url: "ajax_register.php",
        type: "POST",
        data: new FormData(this),
        contentType: false,
        processData: false,
        success: function(res){
            if (res.trim() === "success") {
                alert("Registration successful!");
                window.location.href = "login.php";
            } else {
                $("#msg").html("<span style='color:red'>" + res + "</span>");
            }
        }
    });
});
</script>

</body>
</html>
