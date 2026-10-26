<?php
include "db.php";

$search = "";

if(isset($_POST['search']))
{
    $search = $_POST['search'];

    $sql = "SELECT * FROM emissions
            WHERE source LIKE '%$search%'";
}
else
{
    $sql = "SELECT * FROM emissions";
}

$result = mysqli_query($conn, $sql);
?>