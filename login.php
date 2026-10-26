
<?php
session_start();
require_once __DIR__ . "/db.php";

$error = "";

if (!empty($_SESSION["loggedin"])) {
    header("Location: /black_carbon_project/index.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $loginUsername = trim($_POST["username"] ?? "");
    $loginPassword = $_POST["password"] ?? "";

    if ($loginUsername === "" || $loginPassword === "") {
        $error = "Please enter your username and password.";
    } else {
        $stmt = $conn->prepare(
            "SELECT id, username, password FROM users WHERE username = ? LIMIT 1"
        );

        if ($stmt) {
            $stmt->bind_param("s", $loginUsername);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();

            if ($user && password_verify($loginPassword, $user["password"])) {
                session_regenerate_id(true);
                $_SESSION["loggedin"] = true;
                $_SESSION["user_id"] = $user["id"];
                $_SESSION["username"] = $user["username"];

                header("Location: /black_carbon_project/index.php");
                exit;
            }

            $stmt->close();
        }

        $error = "Invalid username or password.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login | Black Carbon</title>

<style>
* {
    box-sizing: border-box;
}

body {
    margin: 0;
    min-height: 100vh;
    padding: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-family: Arial, sans-serif;
    background: #f1f4f2;
    color: #20352c;
}

.login-card {
    width: 100%;
    max-width: 400px;
    padding: 40px;
    background: white;
    border-radius: 19px;
    box-shadow: 0 15px 40px rgba(0, 0, 0, 0.12);
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
    font-size: 29px;
}

.subtitle {
    margin: 10px 0 30px;
    text-align: center;
    color: #718078;
    font-size: 14px;
}

label {
    display: block;
    margin: 18px 0 8px;
    font-size: 14px;
    font-weight: bold;
}

input {
    display: block;
    width: 100%;
    padding: 14px;
    border: 1px solid #ccd4cf;
    border-radius: 8px;
    font-size: 14px;
    background: #f7f9f8;
}

input:focus {
    outline: 2px solid #b8d8c1;
    border-color: #287b3b;
}

button {
    width: 100%;
    padding: 14px;
    margin-top: 26px;
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

.error {
    padding: 12px;
    margin-bottom: 15px;
    border-radius: 7px;
    background: #ffeded;
    color: #a52222;
    font-size: 14px;
}

.register {
    margin-top: 22px;
    text-align: center;
    font-size: 14px;
    line-height: 1.8;
}

.register a {
    color: #287d36;
    font-weight: bold;
    text-decoration: none;
}

.register a:hover {
    text-decoration: underline;
}

.bottom {
    margin-top: 18px;
    text-align: center;
    font-size: 12px;
    color: #89938d;
}

.bottom a {
    color: #287d36;
    text-decoration: none;
}

@media (max-width: 480px) {
    .login-card {
        padding: 30px 25px;
    }
}
</style>
</head>

<body>

<div class="login-card">

    <div class="logo">🌱</div>

    <h1>Welcome Back</h1>

    <p class="subtitle">
        Black Carbon Emission Monitoring System
    </p>

    <?php if ($error !== ""): ?>
        <div class="error">
            <?= htmlspecialchars($error, ENT_QUOTES, "UTF-8") ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="">
        <label for="username">Username</label>
        <input
            type="text"
            id="username"
            name="username"
            required
            autocomplete="username"
            value="<?= htmlspecialchars($_POST["username"] ?? "", ENT_QUOTES, "UTF-8") ?>"
        >

        <label for="password">Password</label>
        <input
            type="password"
            id="password"
            name="password"
            required
            autocomplete="current-password"
        >

        <button type="submit">Login</button>
    </form>

    <div class="register">
        Don't have an account?
        <a href="/black_carbon_project/register.php">
            Register here
        </a>
        <br>
        <a href="/black_carbon_project/index.php">Back to Home</a>
    </div>

    <div class="bottom">
        Sustainable Travel &amp; Emission Monitoring
    </div>

</div>

</body>
</html>
