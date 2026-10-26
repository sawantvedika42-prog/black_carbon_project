<?php
?>
<!DOCTYPE html>
<html>
<head>
    <title>Dashboard</title>
    <style>
        body{
            font-family: Arial, sans-serif;
            background:#f4f4f4;
            text-align:center;
            padding:50px;
        }
        .box{
            width:500px;
            margin:auto;
            background:white;
            padding:30px;
            border-radius:10px;
            box-shadow:0 0 10px gray;
        }
        a{
            display:block;
            margin:15px;
            text-decoration:none;
            color:white;
            background:green;
            padding:10px;
            border-radius:5px;
        }
        a:hover{
            background:darkgreen;
        }
    </style>
</head>
<body>

<div class="box">
    <h1>Black Carbon Emission Monitoring System</h1>
    <h2>Welcome, Admin!</h2>

    <a href="add_emission.php">Add Emission Data</a>
    <a href="view_emission.php">View Emission Records</a>
    <a href="logout.php">Logout</a>
</div>

</body>
</html>