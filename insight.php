
<?php
session_start();

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("Location: login.php");
    exit;
}
?>
<?php
include "db.php";

/* ==============================
   GET EMISSION DATA
   ============================== */

$totalEmission = 0;
$totalDistance = 0;
$totalTrips = 0;

$trips = array();

$sql = "SELECT * FROM emissions";
$result = mysqli_query($conn, $sql);

if ($result) {

    while ($row = mysqli_fetch_assoc($result)) {

        $trips[] = $row;

        $totalTrips++;

        $totalEmission += floatval($row['emission']);

        $totalDistance += floatval($row['quantity']);
    }
}


/* ==============================
   CONVERT GRAMS TO KG
   ============================== */

$totalEmissionKg = $totalEmission / 1000;


/* ==============================
   EMISSION INDICATOR
   ============================== */

if ($totalEmissionKg < 5) {

    $indicator = "LOW";
    $indicatorIcon = "🟢";
    $indicatorColor = "#4caf50";

    $indicatorText =
        "Your emissions are low. Great job! Keep choosing eco-friendly transport.";

    $recommendation =
        "Continue using walking, cycling and public transport whenever possible.";

}
elseif ($totalEmissionKg < 15) {

    $indicator = "MODERATE";
    $indicatorIcon = "🟡";
    $indicatorColor = "#f0ad4e";

    $indicatorText =
        "Your emissions are moderate. There is an opportunity to reduce your environmental impact.";

    $recommendation =
        "Try carpooling or public transport for some of your regular journeys.";

}
else {

    $indicator = "HIGH";
    $indicatorIcon = "🔴";
    $indicatorColor = "#d9534f";

    $indicatorText =
        "Your emissions are high. Consider changing some travel habits to reduce your impact.";

    $recommendation =
        "Use public transport, carpooling, cycling or walking whenever practical.";
}


/* ==============================
   SAVINGS POTENTIAL
   ============================== */

$savingsPotential = $totalEmissionKg * 0.30;


/* ==============================
   RECENT TRIPS
   ============================== */

$recentTrips = array_reverse($trips);

?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
      content="width=device-width, initial-scale=1.0">

<title>Black Carbon - Insights</title>


<style>

/* ==============================
   GENERAL
   ============================== */

* {
    box-sizing: border-box;
}

body {

    margin: 0;

    font-family: Arial, sans-serif;

    background: #faf9f6;

    color: #24372f;
}


/* ==============================
   NAVIGATION
   ============================== */

nav {

    height: 65px;

    background: white;

    border-bottom: 1px solid #ddd;

    display: flex;

    align-items: center;

    justify-content: space-between;

    padding: 0 50px;
}

.logo {

    font-family: Arial, sans-serif;

    font-size: 24px;

    font-weight: bold;

    color: #111;
}

.nav-links {

    display: flex;

    align-items: center;

    gap: 28px;
}

nav a {

    text-decoration: none;

    color: #222;

    font-size: 16px;

    margin-left: 0;
}

nav a:hover {

    color: #195c3a;
}

nav a.active {

    color: #195c3a;

    font-weight: bold;
}


/* ==============================
   HEADER
   ============================== */

.header {

    text-align: center;

    padding: 50px 20px 30px;
}

.header h1 {

    font-family: Georgia, serif;

    font-size: 45px;

    margin-bottom: 10px;
}

.header p {

    color: #71847c;

    font-size: 17px;
}


/* ==============================
   CONTAINER
   ============================== */

.container {

    max-width: 1100px;

    margin: auto;

    padding: 20px;
}


/* ==============================
   STATISTICS
   ============================== */

.stats {

    display: grid;

    grid-template-columns: repeat(3, 1fr);

    gap: 20px;
}

.card {

    background: white;

    border: 1px solid #ddd;

    border-radius: 20px;

    padding: 25px;

    box-shadow:
        0 5px 15px rgba(0,0,0,0.05);
}

.card-title {

    color: #71847c;

    font-size: 14px;

    margin-bottom: 12px;
}

.card-value {

    font-family: Georgia, serif;

    font-size: 35px;

    font-weight: bold;

    color: #195c3a;
}


/* ==============================
   EMISSION INDICATOR
   ============================== */

.indicator-card {

    background: white;

    border: 1px solid #ddd;

    border-radius: 25px;

    padding: 30px;

    margin-top: 30px;

    box-shadow:
        0 5px 15px rgba(0,0,0,0.05);
}

.indicator-title {

    font-family: Georgia, serif;

    font-size: 25px;

    margin-bottom: 25px;
}

.indicator-content {

    display: flex;

    align-items: center;

    gap: 25px;
}

.indicator-circle {

    width: 95px;

    height: 95px;

    border-radius: 50%;

    display: flex;

    align-items: center;

    justify-content: center;

    font-size: 45px;
}

.indicator-info h2 {

    margin: 0 0 8px;

    font-family: Georgia, serif;
}

.indicator-info p {

    margin: 0;

    color: #71847c;

    line-height: 1.6;
}


/* ==============================
   INDICATOR SCALE
   ============================== */

.indicator-scale {

    display: flex;

    gap: 30px;

    margin-top: 25px;

    color: #71847c;

    font-size: 14px;
}

.indicator-scale div {

    display: flex;

    align-items: center;

    gap: 7px;
}

.indicator-scale span {

    width: 13px;

    height: 13px;

    border-radius: 50%;

    display: inline-block;
}

.low {

    background: #4caf50;
}

.moderate {

    background: #f0ad4e;
}

.high {

    background: #d9534f;
}


/* ==============================
   RECOMMENDATION
   ============================== */

.recommendation {

    margin-top: 25px;

    padding: 18px;

    border-radius: 15px;

    background: #eef6f1;

    color: #195c3a;
}

.recommendation p {

    margin-bottom: 0;

    color: #71847c;

    line-height: 1.5;
}


/* ==============================
   SECTIONS
   ============================== */

.section {

    background: white;

    border: 1px solid #ddd;

    border-radius: 25px;

    padding: 30px;

    margin-top: 30px;
}

.section h2 {

    font-family: Georgia, serif;
}


/* ==============================
   PAST 7 DAYS GRAPH
   ============================== */

.graph {

    height: 180px;

    display: flex;

    align-items: flex-end;

    justify-content: space-around;

    border-bottom: 1px solid #ccc;

    padding-top: 20px;
}

.graph-item {

    display: flex;

    flex-direction: column;

    align-items: center;

    justify-content: flex-end;

    height: 100%;

    gap: 8px;
}

.graph-bar {

    width: 35px;

    background: #195c3a;

    border-radius: 8px 8px 0 0;

    min-height: 5px;
}

.graph-item span {

    font-size: 12px;

    color: #71847c;
}


/* ==============================
   RECENT TRIPS
   ============================== */

.trip {

    display: flex;

    justify-content: space-between;

    align-items: center;

    padding: 18px 0;

    border-bottom: 1px solid #eee;
}

.trip:last-child {

    border-bottom: none;
}

.trip-name {

    font-weight: bold;
}

.trip-date {

    color: #71847c;

    font-size: 13px;

    margin-top: 5px;
}

.trip-emission {

    font-weight: bold;

    color: #195c3a;
}


/* ==============================
   CLEAR HISTORY
   ============================== */

.clear-button {

    display: inline-block;

    margin-top: 25px;

    padding: 12px 20px;

    background: #9b4b4b;

    color: white;

    text-decoration: none;

    border-radius: 10px;
}

.clear-button:hover {

    background: #7d3838;
}


/* ==============================
   MOBILE
   ============================== */

@media(max-width:750px) {

    nav {

        height: auto;

        padding: 20px;

        flex-direction: column;

        gap: 15px;
    }

    .nav-links {

        flex-wrap: wrap;

        justify-content: center;

        gap: 12px;
    }

    nav a {

        margin: 0;
    }

    .stats {

        grid-template-columns: 1fr;
    }

    .indicator-content {

        flex-direction: column;

        text-align: center;
    }

    .indicator-scale {

        flex-direction: column;

        gap: 10px;
    }

    .trip {

        gap: 15px;
    }

}

</style>

</head>


<body>


<!-- ==============================
     NAVIGATION
     ============================== -->

<nav>

    <div class="logo">

        Black Carbon Monitor

    </div>


    <div class="nav-links">

        <a href="index.php">
            Home
        </a>

        <a href="calculator.php">
            Calculator
        </a>

        <a href="insight.php"
           class="active">
            Insights
        </a>

        <a href="chatbot.php">
            AI Assistant
        </a>

        <a href="indicator.php">
            AI Indicator
        </a>

    </div>

</nav>


<!-- ==============================
     HEADER
     ============================== -->

<div class="header">

    <h1>
        Your Insights
    </h1>

    <p>
        Understand your travel emissions
        and discover ways to reduce them.
    </p>

</div>


<div class="container">


<!-- ==============================
     STAT CARDS
     ============================== -->

<div class="stats">


    <div class="card">

        <div class="card-title">
            TOTAL FOOTPRINT
        </div>

        <div class="card-value">

            <?php

            echo number_format(
                $totalEmissionKg,
                2
            );

            ?>

            kg

        </div>

    </div>


    <div class="card">

        <div class="card-title">
            DISTANCE LOGGED
        </div>

        <div class="card-value">

            <?php

            echo number_format(
                $totalDistance,
                1
            );

            ?>

            km

        </div>

    </div>


    <div class="card">

        <div class="card-title">
            SAVINGS POTENTIAL
        </div>

        <div class="card-value">

            <?php

            echo number_format(
                $savingsPotential,
                2
            );

            ?>

            kg

        </div>

    </div>

</div>


<!-- ==============================
     EMISSION INDICATOR
     ============================== -->

<div class="indicator-card">


    <div class="indicator-title">

        🌱 Your Emission Indicator

    </div>


    <div class="indicator-content">


        <div
            class="indicator-circle"
            style="
                background:
                <?php echo $indicatorColor; ?>20;
            "
        >

            <?php echo $indicatorIcon; ?>

        </div>


        <div class="indicator-info">

            <h2
                style="
                    color:
                    <?php echo $indicatorColor; ?>;
                "
            >

                <?php echo $indicator; ?>

            </h2>


            <p>

                <?php echo $indicatorText; ?>

            </p>

        </div>

    </div>


    <div class="indicator-scale">


        <div>

            <span class="low"></span>

            Low

        </div>


        <div>

            <span class="moderate"></span>

            Moderate

        </div>


        <div>

            <span class="high"></span>

            High

        </div>


    </div>


    <div class="recommendation">

        💡 <strong>
            Your Recommendation
        </strong>


        <p>

            <?php echo $recommendation; ?>

        </p>

    </div>


</div>


<!-- ==============================
     PAST 7 DAYS
     ============================== -->

<div class="section">

    <h2>
        Past 7 Days
    </h2>


    <div class="graph">

        <?php

        $graphTrips =
            array_slice(
                array_reverse($trips),
                0,
                7
            );


        if (
            count($graphTrips) > 0
        ) {


            $maxEmission = 1;


            foreach (
                $graphTrips as $trip
            ) {

                $value =
                    floatval(
                        $trip['emission']
                    );

                if (
                    $value >
                    $maxEmission
                ) {

                    $maxEmission =
                        $value;

                }

            }


            foreach (
                array_reverse(
                    $graphTrips
                ) as $trip
            ) {


                $value =
                    floatval(
                        $trip['emission']
                    );


                $height =
                    ($value /
                    $maxEmission) * 150;

                ?>


                <div class="graph-item">


                    <span>

                        <?php

                        echo number_format(
                            $value / 1000,
                            1
                        );

                        ?>

                        kg

                    </span>


                    <div
                        class="graph-bar"
                        style="
                            height:
                            <?php
                            echo $height;
                            ?>px;
                        "
                    >
                    </div>


                    <span>

                        <?php

                        if (
                            isset(
                                $trip['date']
                            )
                        ) {

                            echo date(
                                "d M",
                                strtotime(
                                    $trip['date']
                                )
                            );

                        }
                        else {

                            echo "-";

                        }

                        ?>

                    </span>


                </div>


                <?php

            }


        }
        else {

            ?>

            <p style="color:#71847c;">

                No trips recorded yet.

                Go to the Calculator
                to add your first trip.

            </p>

            <?php

        }

        ?>

    </div>

</div>


<!-- ==============================
     RECENT TRIPS
     ============================== -->

<div class="section">

    <h2>
        Recent Trips
    </h2>


    <?php


    if (
        count($recentTrips) > 0
    ) {


        $count = 0;


        foreach (
            $recentTrips as $trip
        ) {


            if (
                $count >= 5
            ) {

                break;

            }


            ?>


            <div class="trip">


                <div>


                    <div class="trip-name">

                        <?php

                        if (
                            isset(
                                $trip['source']
                            )
                        ) {

                            echo htmlspecialchars(
                                $trip['source']
                            );

                        }
                        else {

                            echo "Trip";

                        }

                        ?>

                    </div>


                    <div class="trip-date">


                        <?php

                        if (
                            isset(
                                $trip['date']
                            )
                        ) {

                            echo date(
                                "d M Y",
                                strtotime(
                                    $trip['date']
                                )
                            );

                        }

                        ?>


                        <?php

                        if (
                            isset(
                                $trip['fuel_type']
                            )
                        ) {

                            echo " • " .
                                 htmlspecialchars(
                                     $trip['fuel_type']
                                 );

                        }

                        ?>


                    </div>


                </div>


                <div class="trip-emission">


                    <?php

                    echo number_format(
                        floatval(
                            $trip['emission']
                        ) / 1000,
                        2
                    );

                    ?>

                    kg CO₂


                </div>


            </div>


            <?php


            $count++;


        }


    }
    else {


        ?>


        <p style="color:#71847c;">

            No trips saved yet.

            Go to the Calculator
            to add your first trip.

        </p>


        <?php

    }


    ?>


    <?php

    if (
        count($trips) > 0
    ) {

    ?>


        <a
            href="delete.php"
            class="clear-button"
            onclick="
                return confirm(
                    'Are you sure you want to clear your history?'
                );
            "
        >

            Clear History

        </a>


    <?php

    }

    ?>


</div>


</div>


</body>

</html>