<?php
include '../config.php';
session_start();

$error = "";

if (isset($_POST['submit'])) {

    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if (empty($email) || empty($password)) {
        $error = "Please enter your login credentials";
    } else {
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $row = $result->fetch_assoc();

            if (password_verify($password, $row['password_hash'])) {
                $_SESSION['id'] = $row['id'];
                $_SESSION['type'] = $row['type'];

                if ($row['type'] === "user") {
                    header("Location: ../dashboard_modules/user_dashboard.php");
                    exit();
                } elseif ($row['type'] === "admin") {
                    header("Location: ../dashboard_modules/admin_dashboard.php");
                    exit();
                }

            } else {
                $error = "Wrong login credentials";
            }
        } else {
            $error = "Wrong login credentials";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login - MySocial</title>
    <style>
        * { box-sizing:border-box; margin:0; padding:0; font-family:system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif; }
        body { display:flex; justify-content:center; align-items:center; min-height:100vh; background:#e0f2f1; }

        .login-card {
            background:#ffffff;
            padding:40px 30px;
            border-radius:14px;
            max-width:400px;
            width:100%;
            box-shadow:0 8px 25px rgba(0,0,0,0.08);
            text-align:center;
        }

        .login-card h2 {
            color:#00796b;
            margin-bottom:25px;
        }

        .login-card input[type="email"], .login-card input[type="password"] {
            width:100%;
            padding:12px 15px;
            margin-bottom:15px;
            border:1px solid #b2dfdb;
            border-radius:6px;
            font-size:14px;
        }

        .login-card input[type="submit"] {
            width:100%;
            padding:12px 15px;
            background:#009688;
            border:none;
            border-radius:6px;
            color:#fff;
            font-size:16px;
            cursor:pointer;
            transition:0.2s;
        }

        .login-card input[type="submit"]:hover {
            background:#00796b;
        }

        .login-card p {
            margin-top:15px;
            font-size:14px;
        }

        .login-card a {
            color:#00796b;
            text-decoration:none;
        }

        .login-card a:hover {
            text-decoration:underline;
        }

        .error-msg {
            color:red;
            margin-bottom:15px;
            font-size:14px;
        }

        @media(max-width:500px){
            .login-card { padding:30px 20px; }
        }
    </style>
</head>
<body>

<div class="login-card">
    <h2>Login to MySocial</h2>

    <?php if (!empty($error)): ?>
        <p class="error-msg"><?php echo $error; ?></p>
    <?php endif; ?>

    <form method="post">
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="password" placeholder="Password" required>
        <input type="submit" name="submit" value="Login">
    </form>

    <p>No account? <a href="register.php">Sign-up</a></p>
</div>

</body>
</html>
