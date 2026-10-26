<?php
require_once "db.php";

/* =========================
   ROUTE SEARCH
========================= */

$searched = false;
$error = "";
$from = "";
$to = "";
$distance = 0;

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $from = trim($_POST["from"] ?? "");
    $to = trim($_POST["to"] ?? "");

    if ($from === "" || $to === "") {
        $error = "Please enter both source and destination.";
    } elseif (strcasecmp($from, $to) === 0) {
        $error = "Source and destination cannot be the same.";
    } else {

        $sql = "
            SELECT source, destination, distance
            FROM routes
            WHERE
                (
                    LOWER(TRIM(source)) = LOWER(TRIM(?))
                    AND
                    LOWER(TRIM(destination)) = LOWER(TRIM(?))
                )
                OR
                (
                    LOWER(TRIM(source)) = LOWER(TRIM(?))
                    AND
                    LOWER(TRIM(destination)) = LOWER(TRIM(?))
                )
            LIMIT 1
        ";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssss", $from, $to, $from, $to);
        $stmt->execute();

        $result = $stmt->get_result();

        if ($result->num_rows > 0) {

            $row = $result->fetch_assoc();
            $distance = (float)$row["distance"];
            $searched = true;

        } else {
            $error = "Route not available in our database.";
        }

        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Transport Finder</title>

<style>

* {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
    font-family: Arial, sans-serif;
}

body {
    background: #f5f7fa;
    color: #222;
}

/* NAVBAR */

.navbar {
    background: #ffffff;
    padding: 18px 7%;
    display: flex;
    justify-content: space-between;
    align-items: center;
    box-shadow: 0 2px 10px rgba(0,0,0,0.08);
}

.logo {
    font-size: 25px;
    font-weight: bold;
    color: #173f7a;
}

.logo span {
    color: #d40000;
}

.navbar a {
    text-decoration: none;
    color: #333;
    margin-left: 22px;
    font-weight: 500;
}

.navbar a:hover {
    color: #173f7a;
}

/* MAIN */

.container {
    width: 90%;
    max-width: 1000px;
    margin: 40px auto;
}

.title {
    text-align: center;
    margin-bottom: 30px;
}

.title h1 {
    font-size: 34px;
    margin-bottom: 8px;
}

.title p {
    color: #666;
}

/* SEARCH */

.search-box {
    background: white;
    padding: 30px;
    border-radius: 15px;
    box-shadow: 0 5px 25px rgba(0,0,0,0.08);
    margin-bottom: 30px;
}

.search-box h2 {
    margin-bottom: 20px;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr auto;
    gap: 15px;
}

input {
    width: 100%;
    padding: 14px;
    border: 1px solid #ccc;
    border-radius: 8px;
    font-size: 15px;
}

button {
    padding: 14px 25px;
    background: #173f7a;
    color: white;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-size: 15px;
    font-weight: bold;
}

button:hover {
    background: #0f2e5c;
}

/* ERROR */

.error {
    background: #ffe5e5;
    color: #b00000;
    padding: 12px;
    border-radius: 8px;
    margin-top: 15px;
}

/* ROUTE */

.route-info {
    background: white;
    padding: 22px;
    border-radius: 12px;
    margin-bottom: 20px;
    box-shadow: 0 3px 15px rgba(0,0,0,0.07);
}

.route-info h2 {
    margin-bottom: 10px;
}

.route-line {
    color: #666;
    font-size: 16px;
}

.distance {
    margin-top: 10px;
    font-size: 18px;
    font-weight: bold;
}

/* OPTIONS */

.options-title {
    margin: 25px 0 15px;
}

.option {
    background: white;
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 15px;
    display: grid;
    grid-template-columns: 70px 1fr 130px;
    align-items: center;
    box-shadow: 0 3px 15px rgba(0,0,0,0.07);
}

.icon {
    font-size: 38px;
}

.option h3 {
    margin-bottom: 7px;
}

.details {
    color: #666;
    line-height: 1.6;
}

.emission {
    text-align: right;
}

.emission strong {
    display: block;
    font-size: 18px;
    margin-bottom: 5px;
}

.low {
    color: #188038;
}

.medium {
    color: #d17b00;
}

.high {
    color: #d00000;
}

/* AI */

.ai-box {
    background: #eef5ff;
    border-left: 5px solid #173f7a;
    padding: 20px;
    margin-top: 25px;
    border-radius: 8px;
}

.ai-box h3 {
    margin-bottom: 10px;
}

/* MOBILE */

@media(max-width: 700px) {

    .navbar {
        flex-direction: column;
        gap: 15px;
    }

    .navbar a {
        margin: 5px;
    }

    .form-row {
        grid-template-columns: 1fr;
    }

    .option {
        grid-template-columns: 60px 1fr;
    }

    .emission {
        grid-column: 2;
        text-align: left;
        margin-top: 10px;
    }

}

</style>

</head>

<body>

<!-- NAVBAR -->

<div class="navbar">

    <div class="logo">
        <span>m</span>-Transport
    </div>

    <div>
        <a href="index.php">Home</a>
        <a href="calculator.php">Calculator</a>
        <a href="transport.php">Transport Finder</a>
        <a href="insight.php">Insights</a>
        <a href="indicator.php">AI Indicator</a>
    </div>

</div>


<div class="container">

    <div class="title">

        <h1>🚆 Transport Finder</h1>

        <p>
            Find transport options between your source and destination
        </p>

    </div>


    <!-- SEARCH -->

    <div class="search-box">

        <h2>Search Route</h2>

        <form method="POST">

            <div class="form-row">

                <input
                    type="text"
                    name="from"
                    placeholder="From"
                    value="<?php echo htmlspecialchars($from); ?>"
                    required
                >

                <input
                    type="text"
                    name="to"
                    placeholder="To"
                    value="<?php echo htmlspecialchars($to); ?>"
                    required
                >

                <button type="submit">
                    Search
                </button>

            </div>

        </form>


        <?php if ($error !== ""): ?>

            <div class="error">
                <?php echo htmlspecialchars($error); ?>
            </div>

        <?php endif; ?>

    </div>


<?php if ($searched): ?>

    <!-- ROUTE -->

    <div class="route-info">

        <h2>
            <?php echo htmlspecialchars($from); ?>
            →
            <?php echo htmlspecialchars($to); ?>
        </h2>

        <div class="route-line">
            Available transport options
        </div>

        <div class="distance">
            📍 Distance: <?php echo $distance; ?> km
        </div>

    </div>


    <h2 class="options-title">
        Available Transport
    </h2>


    <!-- TRAIN -->

    <?php

    $trainTime = max(10, round($distance * 2));
    $busTime = max(15, round($distance * 3));
    $carTime = max(10, round($distance * 2.2));

    $trainEmission = $distance * 41;
    $busEmission = $distance * 88;
    $carEmission = $distance * 170;

    ?>

    <div class="option">

        <div class="icon">🚆</div>

        <div>

            <h3>Local Train / Metro</h3>

            <div class="details">
                ⏱ Estimated Time: <?php echo $trainTime; ?> min<br>
                💰 Approx. Fare: ₹<?php echo max(10, round($distance * 0.6)); ?>
            </div>

        </div>

        <div class="emission low">

            <strong>Low Impact</strong>

            <?php echo number_format($trainEmission / 1000, 2); ?> kg

        </div>

    </div>


    <!-- BUS -->

    <div class="option">

        <div class="icon">🚌</div>

        <div>

            <h3>Public Bus</h3>

            <div class="details">
                ⏱ Estimated Time: <?php echo $busTime; ?> min<br>
                💰 Approx. Fare: ₹<?php echo max(10, round($distance * 1.2)); ?>
            </div>

        </div>

        <div class="emission medium">

            <strong>Medium Impact</strong>

            <?php echo number_format($busEmission / 1000, 2); ?> kg

        </div>

    </div>


    <!-- CAR -->

    <div class="option">

        <div class="icon">🚗</div>

        <div>

            <h3>Car</h3>

            <div class="details">
                ⏱ Estimated Time: <?php echo $carTime; ?> min<br>
                💰 Approx. Travel Cost: ₹<?php echo max(50, round($distance * 12)); ?>
            </div>

        </div>

        <div class="emission high">

            <strong>High Impact</strong>

            <?php echo number_format($carEmission / 1000, 2); ?> kg

        </div>

    </div>


    <!-- WALKING -->

    <?php if ($distance <= 5): ?>

    <div class="option">

        <div class="icon">🚶</div>

        <div>

            <h3>Walking</h3>

            <div class="details">
                ⏱ Estimated Time:
                <?php echo max(5, round($distance * 12)); ?> min<br>
                💰 Fare: Free
            </div>

        </div>

        <div class="emission low">

            <strong>Zero Impact</strong>

            0 kg

        </div>

    </div>

    <?php endif; ?>


    <!-- AI RECOMMENDATION -->

    <div class="ai-box">

        <h3>🤖 AI Sustainable Travel Recommendation</h3>

        <?php if ($distance <= 5): ?>

            For this short journey, walking or cycling can be
            considered because they produce almost no direct
            transport emissions.

        <?php elseif ($distance <= 50): ?>

            Public transport such as local train/metro or bus
            is a more environmentally responsible option than
            travelling alone by car.

        <?php else: ?>

            For this longer journey, public transport is recommended
            where available because it can reduce emissions per
            passenger compared with travelling alone by car.

        <?php endif; ?>

    </div>


<?php endif; ?>

</div>

</body>
</html>