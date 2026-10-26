<?php
ini_set('display_errors', '1');
error_reporting(E_ALL);
set_time_limit(10);

echo "STEP 1: PHP started<br>";
flush();

require_once __DIR__ . "/db.php";

echo "STEP 2: Database connected<br>";
?>