<?php
ini_set('display_errors', '1');
error_reporting(E_ALL);
set_time_limit(10);

echo "STEP 1: PHP started<br>";
flush();

$conn = mysqli_init();
mysqli_options($conn, MYSQLI_OPT_CONNECT_TIMEOUT, 5);

echo "STEP 2: Connecting to MySQL...<br>";
flush();

$result = mysqli_real_connect(
    $conn,
    '127.0.0.1',
    'root',
    '',
    'black_carbon',
    3306
);

if (!$result) {
    die("CONNECTION ERROR: " . mysqli_connect_error());
}

echo "STEP 3: Database connected successfully!";
mysqli_close($conn);
?>