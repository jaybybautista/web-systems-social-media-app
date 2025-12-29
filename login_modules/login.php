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
                } else {
                    header("Location: ../dashboard_modules/admin_dashboard.php");
                }
                exit();
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
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login – MySocial</title>

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

/* RESET */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", system-ui, sans-serif;
}

body {
    min-height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;
    background-color: var(--bg-dark);
    /* Subtle background glow to match the app vibe */
    background-image: radial-gradient(circle at 10% 20%, rgba(77, 182, 255, 0.05) 0%, transparent 50%);
}

/* DARK LOGIN CARD */
.login-card {
    width: 100%;
    max-width: 400px;
    padding: 50px 40px;
    border-radius: 30px;
    background: var(--card-bg);
    border: 1px solid var(--border-color);
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
    text-align: center;
}

/* BRANDING */
.login-card h2 {
    font-size: 32px;
    font-weight: 800;
    margin-bottom: 8px;
    color: var(--text-white);
    letter-spacing: -1px;
}

.login-card .subtitle {
    color: var(--text-gray);
    font-size: 14px;
    margin-bottom: 35px;
    display: block;
}

/* INPUTS */
.login-card input:not([type="submit"]) {
    width: 100%;
    padding: 16px 20px;
    margin-bottom: 16px;
    border-radius: 14px;
    border: 1px solid var(--border-color);
    background: rgba(0, 0, 0, 0.2);
    color: var(--text-white);
    font-size: 15px;
    transition: all 0.3s ease;
}

.login-card input:not([type="submit"]):focus {
    outline: none;
    border-color: var(--accent-blue);
    background: rgba(0, 0, 0, 0.3);
    box-shadow: 0 0 0 4px rgba(77, 182, 255, 0.1);
}

/* BUTTON */
.login-card input[type="submit"] {
    width: 100%;
    margin-top: 10px;
    padding: 16px;
    border-radius: 50px;
    border: none;
    font-size: 16px;
    font-weight: 700;
    color: #051115;
    cursor: pointer;
    background: var(--accent-blue);
    transition: all 0.2s ease;
}

.login-card input[type="submit"]:hover {
    background: var(--accent-hover);
    transform: translateY(-2px);
    box-shadow: 0 10px 20px rgba(77, 182, 255, 0.2);
}

/* ERROR MESSAGE */
.error-msg {
    margin-bottom: 20px;
    padding: 12px;
    border-radius: 10px;
    background: rgba(255, 77, 77, 0.1);
    border: 1px solid rgba(255, 77, 77, 0.2);
    font-size: 14px;
    color: var(--error-red);
}

/* FOOTER */
.login-card p {
    margin-top: 25px;
    font-size: 14px;
    color: var(--text-gray);
}

.login-card a {
    color: var(--accent-blue);
    text-decoration: none;
    font-weight: 600;
}

.login-card a:hover {
    color: var(--accent-hover);
    text-decoration: underline;
}

@media (max-width: 480px) {
    .login-card {
        margin: 20px;
        padding: 40px 25px;
    }
}
</style>
</head>

<body>

<div class="login-card">
    <h2>MySocial</h2>
    <span class="subtitle">Welcome back! Please enter your details.</span>

    <?php if (!empty($error)): ?>
        <div class="error-msg"><?php echo $error; ?></div>
    <?php endif; ?>

    <form method="post">
        <input type="email" name="email" placeholder="Email Address" required>
        <input type="password" name="password" placeholder="Password" required>
        <input type="submit" name="submit" value="Sign In">
    </form>

    <p>Don't have an account? <a href="register.php">Create account</a></p>
</div>

</body>
</html>