<?php

include "db.php";

$source = $_GET['source'];
$fuel_type = $_GET['fuel_type'];
$quantity = $_GET['quantity'];
$emission = $_GET['emission'];

$sql = "INSERT INTO emissions
        (source, fuel_type, quantity, emission, date)
        VALUES
        ('$source', '$fuel_type', '$quantity', '$emission', NOW())";

if (mysqli_query($conn, $sql)) {

    echo "<h2>Trip Saved Successfully!</h2>";

    echo "<p>Journey: $source</p>";
    echo "<p>Transport: $fuel_type</p>";
    echo "<p>Distance: $quantity km</p>";
    echo "<p>Emission: $emission grams CO₂</p>";

    echo '<br><a href="calculator.php">Back to Calculator</a>';
    echo '<br><a href="view_emission.php">View All Trips</a>';

} else {

    echo "Error: " . mysqli_error($conn);

}

?>