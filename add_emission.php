<?php
include "db.php";

$message = "";

if(isset($_POST['submit']))
{
    $source = $_POST['source'];
    $fuel_type = $_POST['fuel_type'];
    $quantity = $_POST['quantity'];
    $date = $_POST['date'];

    // Calculate Black Carbon Emission
    $emission = $quantity * 0.15;

    $sql = "INSERT INTO emissions(source, fuel_type, quantity, emission, date)
            VALUES('$source','$fuel_type','$quantity','$emission','$date')";

    if(mysqli_query($conn, $sql))
    {
        $message = "Record added successfully!";
    }
    else
    {
        $message = "Error: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Emission</title>

    <style>
        body{
            font-family:Arial;
            background:#f2f2f2;
        }

        .container{
            width:450px;
            margin:40px auto;
            background:white;
            padding:20px;
            border-radius:8px;
            box-shadow:0px 0px 10px gray;
        }

        input, select{
            width:100%;
            padding:10px;
            margin:10px 0;
        }

        input[type=submit]{
            background:green;
            color:white;
            border:none;
            cursor:pointer;
        }

        input[type=submit]:hover{
            background:darkgreen;
        }

        .msg{
            color:green;
            font-weight:bold;
        }
    </style>
</head>

<body>

<div class="container">

<h2>Add Emission Record</h2>

<p class="msg"><?php echo $message; ?></p>

<form method="POST">

<label>Source</label>
<select name="source" required>
    <option value="">Select</option>
    <option>Vehicle</option>
    <option>Factory</option>
    <option>Biomass Burning</option>
    <option>Diesel Generator</option>
</select>

<label>Fuel Type</label>
<input type="text" name="fuel_type" required>

<label>Quantity (Litres)</label>
<input type="number" step="0.01" name="quantity" required>

<label>Date</label>
<input type="date" name="date" required>

<input type="submit" name="submit" value="Save Record">

</form>

<br>

<a href="dashboard.php">⬅ Back to Dashboard</a>

</div>

</body>
</html>