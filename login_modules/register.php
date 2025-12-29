<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account - MySocial</title>
    <script src="../jquery-3.7.1.js"></script>

    <style>
        :root {
            --bg-dark: #051115;
            --card-bg: #112229;
            --accent-blue: #4db6ff;
            --accent-hover: #70c4ff;
            --text-white: #ffffff;
            --text-gray: #94a3b8;
            --border-color: #2a3f47;
            --error-red: #ff4d4d;
        }

        /* RESET & BASE */
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
        }

        body {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background-color: var(--bg-dark);
            background-image: radial-gradient(circle at 90% 10%, rgba(77, 182, 255, 0.05) 0%, transparent 50%);
            padding: 20px;
        }

        /* DARK REGISTER CARD */
        .register-card {
            background: var(--card-bg);
            padding: 45px 35px;
            border-radius: 30px;
            max-width: 420px;
            width: 100%;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            border: 1px solid var(--border-color);
            text-align: center;
        }

        .register-card h2 {
            color: var(--text-white);
            font-size: 32px;
            font-weight: 800;
            margin-bottom: 8px;
            letter-spacing: -1px;
        }

        .subtitle {
            display: block;
            color: var(--text-gray);
            font-size: 14px;
            margin-bottom: 30px;
        }

        /* FORM INPUTS */
        .register-card input:not([type="file"]) {
            width: 100%;
            padding: 14px 18px;
            margin-bottom: 15px;
            background: rgba(0, 0, 0, 0.2);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            color: var(--text-white);
            font-size: 14px;
            transition: all 0.3s ease;
            outline: none;
        }

        .register-card input:not([type="file"]):focus {
            border-color: var(--accent-blue);
            box-shadow: 0 0 0 4px rgba(77, 182, 255, 0.1);
        }

        /* FILE INPUT STYLING */
        .file-group {
            text-align: left;
            margin-bottom: 20px;
        }

        .file-label {
            display: block;
            font-size: 12px;
            color: var(--text-gray);
            margin-bottom: 8px;
            text-transform: uppercase;
            font-weight: 600;
        }

        input[type="file"] {
            color: var(--text-gray);
            font-size: 13px;
        }

        input[type="file"]::file-selector-button {
            background: rgba(255, 255, 255, 0.05);
            color: white;
            border: 1px solid var(--border-color);
            padding: 8px 16px;
            border-radius: 8px;
            cursor: pointer;
            margin-right: 15px;
            transition: 0.3s;
        }

        /* BUTTON */
        .register-card button {
            width: 100%;
            padding: 15px;
            background: var(--accent-blue);
            border: none;
            border-radius: 50px;
            color: #051115;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s ease;
            margin-top: 10px;
        }

        .register-card button:hover {
            background: var(--accent-hover);
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(77, 182, 255, 0.2);
        }

        #msg {
            margin-bottom: 15px;
            font-size: 14px;
            min-height: 20px;
            color: var(--text-gray);
        }

        .register-card p {
            margin-top: 25px;
            font-size: 14px;
            color: var(--text-gray);
        }

        .register-card a {
            color: var(--accent-blue);
            text-decoration: none;
            font-weight: 600;
        }

        .register-card a:hover {
            text-decoration: underline;
        }
    </style>
</head>

<body>

    <div class="register-card">
        <h2>Join MySocial</h2>
        <span class="subtitle">Create your account to start sharing.</span>

        <div id="msg"></div>

        <form id="registerForm" enctype="multipart/form-data">
            <input type="text" name="username" placeholder="Username" required>
            <input type="email" name="email" placeholder="Email Address" required>
            <input type="password" name="password" placeholder="Password" required>
            <input type="password" name="confirm_password" placeholder="Confirm Password" required>

            <div class="file-group">
                <span class="file-label">Profile Picture (Optional)</span>
                <input type="file" name="picture" accept="image/*">
            </div>

            <button type="submit">Register</button>
        </form>

        <p>Already have an account? <a href="login.php">Login</a></p>
    </div>

    <script>
        $("#registerForm").on("submit", function (e) {
            e.preventDefault();

            $("#msg").html("<span style='color: var(--accent-blue)'>Registering...</span>");

            $.ajax({
                url: "ajax_register.php",
                type: "POST",
                data: new FormData(this),
                contentType: false,
                processData: false,
                success: function (res) {
                    if (res.trim() === "success") {
                        alert("Registration successful!");
                        window.location.href = "login.php";
                    } else {
                        $("#msg").html("<span style='color:var(--error-red)'>" + res + "</span>");
                    }
                }
            });
        });
    </script>

</body>

</html>