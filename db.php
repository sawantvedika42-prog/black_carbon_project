<?php
$servername = "127.0.0.1";
$username = "root";
$password = "";
$database = "black_carbon";

mysqli_report(MYSQLI_REPORT_OFF);

$conn = mysqli_connect(
    $servername,
    $username,
    $password,
    $database,
    3306
);

if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

echo "";
?>