
<?php
session_start();
require_once __DIR__ . "/db.php";

$error = "";
$success = "";

if (!empty($_SESSION["loggedin"])) {
    header("Location: /black_carbon_project/index.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST["username"] ?? "");
    $password = $_POST["password"] ?? "";
    $confirmPassword = $_POST["confirm_password"] ?? "";

    if ($username === "" || $password === "" || $confirmPassword === "") {
        $error = "Please fill in all fields.";
    } elseif (strlen($username) < 3 || strlen($username) > 50) {
        $error = "Username must be between 3 and 50 characters.";
    } elseif (strlen($password) < 8) {
        $error = "Password must contain at least 8 characters.";
    } elseif ($password !== $confirmPassword) {
        $error = "Passwords do not match.";
    } else {
        $check = $conn->prepare(
            "SELECT id FROM users WHERE username = ? LIMIT 1"
        );

        if (!$check) {
            $error = "Database query failed. Please check your users table.";
        } else {
            $check->bind_param("s", $username);
            $check->execute();
            $result = $check->get_result();

            if ($result->num_rows > 0) {
                $error = "This username already exists. Choose another.";
            } else {
                $hashedPassword = password_hash(
                    $password,
                    PASSWORD_DEFAULT
                );

                $insert = $conn->prepare(
                    "INSERT INTO users (username, password) VALUES (?, ?)"
                );

                if (!$insert) {
                    $error = "Could not prepare registration.";
                } else {
                    $insert->bind_param(
                        "ss",
                        $username,
                        $hashedPassword
                    );

                    if ($insert->execute()) {
                        $success = "Registration successful! You can now log in.";
                    } else {
                        $error = "Registration failed. Please try again.";
                    }

                    $insert->close();
                }
            }

            $check->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Create Account | Black Carbon</title>

<style>
* {
    box-sizing: border-box;
}

body {
    margin: 0;
    min-height: 100vh;
    padding: 20px;
    display: flex;
    justify-content: center;
    align-items: center;
    font-family: Arial, sans-serif;
    background: #f1f4f2;
    color: #20352c;
}

.register-card {
    width: 100%;
    max-width: 400px;
    padding: 38px;
    background: #fff;
    border-radius: 19px;
    box-shadow: 0 15px 40px rgba(0, 0, 0, .12);
}

.logo {
    width: 70px;
    height: 70px;
    margin: 0 auto 18px;
    border-radius: 50%;
    background: #e8f5e9;
    display: flex;
    justify-content: center;
    align-items: center;
    font-size: 35px;
}

h1 {
    margin: 0;
    text-align: center;
    font-size: 28px;
}

.subtitle {
    margin: 10px 0 25px;
    text-align: center;
    color: #718078;
    font-size: 14px;
    line-height: 1.5;
}

label {
    display: block;
    margin: 17px 0 8px;
    font-size: 14px;
    font-weight: bold;
}

input {
    display: block;
    width: 100%;
    padding: 13px;
    border: 1px solid #ccd4cf;
    border-radius: 8px;
    font-size: 15px;
}

input:focus {
    outline: 2px solid #b8d8c1;
    border-color: #287d36;
}

button {
    width: 100%;
    padding: 14px;
    margin-top: 25px;
    border: none;
    border-radius: 8px;
    background: #287d36;
    color: white;
    font-size: 16px;
    font-weight: bold;
    cursor: pointer;
}

button:hover {
    background: #21662d;
}

.error,
.success {
    padding: 12px;
    margin-bottom: 15px;
    border-radius: 7px;
    font-size: 14px;
    line-height: 1.5;
}

.error {
    background: #ffeded;
    color: #a52222;
}

.success {
    background: #e8f5e9;
    color: #21662d;
}

.links {
    margin-top: 22px;
    text-align: center;
    font-size: 14px;
    line-height: 2;
}

a {
    color: #287d36;
    font-weight: bold;
    text-decoration: none;
}

a:hover {
    text-decoration: underline;
}

.footer {
    margin-top: 18px;
    text-align: center;
    font-size: 12px;
    color: #89938d;
}

@media (max-width: 480px) {
    .register-card {
        padding: 28px 24px;
    }
}
</style>
</head>

<body>

<div class="register-card">

    <div class="logo">🌱</div>

    <h1>Create Account</h1>

    <p class="subtitle">
        Join the Black Carbon Emission Monitoring System
    </p>

    <?php if ($error !== ""): ?>
        <div class="error">
            <?= htmlspecialchars($error, ENT_QUOTES, "UTF-8") ?>
        </div>
    <?php endif; ?>

    <?php if ($success !== ""): ?>
        <div class="success">
            <?= htmlspecialchars($success, ENT_QUOTES, "UTF-8") ?>
            <br>
            <a href="/black_carbon_project/login.php">Login now</a>
        </div>
    <?php endif; ?>

    <form method="POST"
          action="/black_carbon_project/register.php">

        <label for="username">Username</label>
        <input
            type="text"
            id="username"
            name="username"
            minlength="3"
            maxlength="50"
            autocomplete="username"
            required
            value="<?= htmlspecialchars($_POST["username"] ?? "", ENT_QUOTES, "UTF-8") ?>"
        >

        <label for="password">Password</label>
        <input
            type="password"
            id="password"
            name="password"
            minlength="8"
            autocomplete="new-password"
            required
        >

        <label for="confirm_password">Confirm Password</label>
        <input
            type="password"
            id="confirm_password"
            name="confirm_password"
            minlength="8"
            autocomplete="new-password"
            required
        >

        <button type="submit">Create Account</button>
    </form>

    <div class="links">
        Already have an account?
        <a href="/black_carbon_project/login.php">Login here</a>
        <br>
        <a href="/black_carbon_project/index.php">Back to Home</a>
    </div>

    <div class="footer">
        Sustainable Travel &amp; Emission Monitoring
    </div>

</div>

</body>
</html>
