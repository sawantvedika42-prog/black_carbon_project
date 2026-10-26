<?php
include "db.php";
$total_records = 0;
$total_emission = 0;
$total_sources = 0;
$result = mysqli_query($conn, "SELECT COUNT(*) AS total FROM emissions");
if ($result) {
    $row = mysqli_fetch_assoc($result);
    $total_records = $row['total'];
}
$result = mysqli_query($conn, "SELECT COALESCE(SUM(emission),0) AS total FROM emissions");
if ($result) {
    $row = mysqli_fetch_assoc($result);
    $total_emission = $row['total'];
}
$result = mysqli_query($conn, "SELECT COUNT(DISTINCT source) AS total FROM emissions");
if ($result) {
    $row = mysqli_fetch_assoc($result);
    $total_sources = $row['total'];
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Black Carbon Statistics</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f7f2;
            margin: 0;
            padding: 40px;
        }

        h1 {
            text-align: center;
            color: #195c3a;
        }

        .container {
            max-width: 1000px;
            margin: 40px auto;
        }

        .cards {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 25px;
        }

        .card {
            background: white;
            padding: 30px;
            border-radius: 15px;
            text-align: center;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
        }

        .icon {
            font-size: 40px;
        }

        .number {
            font-size: 35px;
            font-weight: bold;
            color: #195c3a;
            margin: 15px;
        }

        .back {
            display: block;
            width: fit-content;
            margin: 40px auto;
            padding: 12px 25px;
            background: #195c3a;
            color: white;
            text-decoration: none;
            border-radius: 8px;
        }

        @media(max-width: 700px) {
            .cards {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>

<div class="container">

    <h1>🌿 Black Carbon Statistics</h1>

    <div class="cards">

        <div class="card">
            <div class="icon">📊</div>

            <h2>Total Records</h2>

            <div class="number">
                <?php echo $total_records; ?>
            </div>
        </div>


        <div class="card">
            <div class="icon">🌫️</div>

            <h2>Total Emission</h2>

            <div class="number">
                <?php echo number_format($total_emission, 2); ?>
            </div>

            <p>Recorded emission</p>
        </div>


        <div class="card">
            <div class="icon">📍</div>

            <h2>Sources</h2>

            <div class="number">
                <?php echo $total_sources; ?>
            </div>
        </div>

    </div>

    <a href="dashboard.php" class="back">
        ← Back to Dashboard
    </a>

</div>

</body>
</html>