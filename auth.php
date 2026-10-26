
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION["loggedin"])) {
    header("Location: /black_carbon_project/login.php");
    exit;
}
?>
