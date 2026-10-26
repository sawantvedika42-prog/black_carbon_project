<?php
include "db.php";

if(isset($_GET['id']))
{
    $id = $_GET['id'];

    $sql = "DELETE FROM emissions WHERE id='$id'";

    if(mysqli_query($conn,$sql))
    {
        header("Location: view_emission.php");
        exit();
    }
    else
    {
        echo "Error deleting record.";
    }
}
?>