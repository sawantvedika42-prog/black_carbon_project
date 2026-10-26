
<?php
session_start();

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("Location: login.php");
    exit;
}
?>
<?php

include "db.php";

/* =========================================================
   AUTOMATIC DISTANCE SEARCH
========================================================= */

if (isset($_GET['get_distance'])) {

    header("Content-Type: application/json");

    $from = trim($_GET['from'] ?? "");
    $to   = trim($_GET['to'] ?? "");

    if ($from === "" || $to === "") {

        echo json_encode([
            "success" => false,
            "message" => "Please enter both locations."
        ]);

        exit;
    }

    if (strcasecmp($from, $to) === 0) {

        echo json_encode([
            "success" => false,
            "message" => "Source and destination cannot be the same."
        ]);

        exit;
    }

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

    $stmt = mysqli_prepare($conn, $sql);

    if (!$stmt) {

        echo json_encode([
            "success" => false,
            "message" => "Database query error."
        ]);

        exit;
    }

    mysqli_stmt_bind_param(
        $stmt,
        "ssss",
        $from,
        $to,
        $to,
        $from
    );

    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);

    if ($result && mysqli_num_rows($result) > 0) {

        $row = mysqli_fetch_assoc($result);

        echo json_encode([
            "success" => true,
            "distance" => floatval($row["distance"]),
            "source" => $row["source"],
            "destination" => $row["destination"]
        ]);

    } else {

        echo json_encode([
            "success" => false,
            "message" => "Route not found in database."
        ]);
    }

    mysqli_stmt_close($stmt);

    exit;
}

?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
      content="width=device-width, initial-scale=1.0">

<title>Black Carbon - Emissions Calculator</title>

<style>

/* =========================================================
   GENERAL
========================================================= */

* {
    box-sizing: border-box;
}

body {

    margin: 0;

    font-family: Arial, sans-serif;

    background: #faf9f6;

    color: #24372f;
}


/* =========================================================
   NAVBAR
========================================================= */

nav {

    height: 85px;

    background: white;

    border-bottom: 1px solid #ddd;

    display: flex;

    align-items: center;

    justify-content: space-between;

    padding: 0 8%;
}

.logo {

    display: flex;

    align-items: center;

    font-family: Georgia, serif;

    font-size: 22px;

    font-weight: bold;
}

.logo-icon {

    width: 42px;

    height: 42px;

    background: #195c3a;

    color: white;

    border-radius: 50%;

    display: flex;

    align-items: center;

    justify-content: center;

    margin-right: 10px;
}

nav a {

    text-decoration: none;

    color: #61756d;

    margin-left: 25px;

    transition: 0.2s;
}

nav a:hover {

    color: #195c3a;
}

.active {

    color: #195c3a;

    font-weight: bold;
}


/* =========================================================
   HEADER
========================================================= */

.header {

    text-align: center;

    padding: 55px 20px 30px;
}

.header h1 {

    font-family: Georgia, serif;

    font-size: 45px;

    margin-bottom: 15px;
}

.header p {

    color: #71847c;

    font-size: 18px;
}


/* =========================================================
   MAIN
========================================================= */

.main {

    max-width: 1100px;

    margin: 20px auto 70px;

    display: grid;

    grid-template-columns: 1fr 1fr;

    gap: 35px;

    padding: 20px;
}


/* =========================================================
   CALCULATOR
========================================================= */

.calculator {

    background: white;

    border: 1px solid #ddd;

    border-radius: 25px;

    padding: 30px;

    box-shadow:
        0 5px 15px rgba(0,0,0,0.06);
}

.calculator h2 {

    font-family: Georgia, serif;

    margin-top: 0;
}

label {

    display: block;

    margin-top: 20px;

    margin-bottom: 8px;

    color: #60756c;
}

input,
select {

    width: 100%;

    padding: 14px;

    border: 1px solid #d5ddd8;

    border-radius: 12px;

    font-size: 16px;
}

input:focus,
select:focus {

    outline: none;

    border-color: #195c3a;
}


/* =========================================================
   DISTANCE RESULT
========================================================= */

.distance-box {

    margin-top: 18px;

    padding: 15px;

    background: #f3f2ed;

    border-radius: 12px;

    color: #195c3a;

    font-weight: bold;

    text-align: center;
}

.distance-loading {

    color: #8a6d3b;
}

.distance-error {

    background: #f8e8e8;

    color: #a33a3a;
}


/* =========================================================
   TRANSPORT
========================================================= */

.transport-title {

    margin-top: 25px;

    margin-bottom: 12px;

    font-weight: bold;
}

.transport-grid {

    display: grid;

    grid-template-columns: repeat(4, 1fr);

    gap: 8px;
}

.transport {

    border: 1px solid #ddd;

    background: #f4f2ee;

    padding: 12px 5px;

    border-radius: 12px;

    text-align: center;

    cursor: pointer;

    font-size: 13px;
}

.transport:hover {

    border-color: #195c3a;
}

.transport.selected {

    border: 2px solid #195c3a;

    background: #e8f2ec;
}

.emoji {

    display: block;

    font-size: 25px;

    margin-bottom: 5px;
}


/* =========================================================
   PASSENGERS
========================================================= */

.passenger {

    display: flex;

    align-items: center;

    gap: 10px;
}

.passenger input {

    width: 90px;
}


/* =========================================================
   BUTTONS
========================================================= */

button {

    width: 100%;

    margin-top: 20px;

    padding: 15px;

    border: none;

    border-radius: 12px;

    background: #195c3a;

    color: white;

    font-size: 17px;

    font-weight: bold;

    cursor: pointer;
}

button:hover {

    background: #12472c;
}

.save-button {

    background: #8a6d3b;
}

.save-button:hover {

    background: #6e542d;
}


/* =========================================================
   RESULT
========================================================= */

.result {

    background: white;

    border-radius: 25px;

    border: 1px solid #ddd;

    border-top: 8px solid #195c3a;

    padding: 30px;

    text-align: center;

    box-shadow:
        0 5px 15px rgba(0,0,0,0.06);
}

.result-title {

    color: #71847c;

    font-size: 14px;

    letter-spacing: 1px;
}

.emission {

    font-family: Georgia, serif;

    font-size: 55px;

    font-weight: bold;

    margin: 20px 0 5px;
}

.unit {

    color: #71847c;

    font-size: 20px;
}


/* =========================================================
   MEANING
========================================================= */

.meaning {

    background: #f3f2ed;

    padding: 20px;

    border-radius: 15px;

    text-align: left;

    margin-top: 25px;
}

.meaning h3 {

    margin-top: 0;
}

.meaning li {

    margin-bottom: 10px;

    color: #71847c;
}


/* =========================================================
   ADVICE
========================================================= */

.message {

    margin-top: 20px;

    padding: 15px;

    border-radius: 12px;

    background: #e8f2ec;

    color: #195c3a;

    font-weight: bold;
}


/* =========================================================
   CHART
========================================================= */

.chart-box {

    margin-top: 25px;

    padding: 20px;

    background: #f8f7f3;

    border-radius: 18px;

    text-align: left;
}

.chart-box h3 {

    font-family: Georgia, serif;

    margin-top: 0;
}

.bar {

    display: grid;

    grid-template-columns: 90px 1fr 60px;

    align-items: center;

    gap: 8px;

    margin: 13px 0;

    font-size: 13px;
}

.bar-bg {

    height: 14px;

    background: #e5e5df;

    border-radius: 10px;

    overflow: hidden;
}

.bar-fill {

    height: 100%;

    border-radius: 10px;

    transition: width 0.8s;
}

.walking {

    background: #9bbcae;

    width: 0%;
}

.bicycle {

    background: #9bbcae;

    width: 0%;
}

.metro {

    background: #8fb7a5;

    width: 24%;
}

.bus {

    background: #d5a85f;

    width: 52%;
}

.auto {

    background: #8fb7a5;

    width: 59%;
}

.car {

    background: #6d9b87;

    width: 100%;
}

.scooter {

    background: #c77b7b;

    width: 35%;
}


/* =========================================================
   FOOTER
========================================================= */

footer {

    height: 130px;

    background: #195c3a;
}


/* =========================================================
   MOBILE
========================================================= */

@media(max-width: 750px) {

    nav {

        height: auto;

        padding: 20px;

        flex-direction: column;

        gap: 20px;
    }

    nav a {

        margin: 8px;
    }

    .main {

        grid-template-columns: 1fr;
    }

    .transport-grid {

        grid-template-columns: repeat(2, 1fr);
    }

}

</style>

</head>


<body>


<!-- =====================================================
     NAVIGATION
===================================================== -->

<nav>

    <div class="logo">

        <div class="logo-icon">
            🍃
        </div>

        Black Carbon

    </div>


    <div>

        <!-- SAME NAVIGATION AS AI ASSISTANT -->

        <a href="index.php">
            Home
        </a>

        <a href="calculator.php"
           class="active">
            Calculator
        </a>

        <a href="insight.php">
            Insights
        </a>

        <a href="indicator.php">
            AI Indicator
        </a>

        <a href="chatbot.php">
            AI Assistant
        </a>

    </div>

</nav>


<!-- =====================================================
     HEADER
===================================================== -->

<section class="header">

    <h1>
        Emissions Calculator
    </h1>

    <p>
        See the carbon cost of your journey, instantly.
    </p>

</section>


<!-- =====================================================
     MAIN
===================================================== -->

<div class="main">


<!-- =====================================================
     LEFT CALCULATOR
===================================================== -->

<div class="calculator">

    <h2>
        The Journey
    </h2>


    <!-- FROM -->

    <label>
        📍 From
    </label>

    <input
        type="text"
        id="from"
        placeholder="Enter starting location"
        oninput="clearDistance()"
    >


    <!-- TO -->

    <label>
        📍 To
    </label>

    <input
        type="text"
        id="to"
        placeholder="Enter destination"
        oninput="clearDistance()"
    >


    <!-- AUTOMATIC DISTANCE -->

    <div
        id="distanceBox"
        class="distance-box"
    >
        📏 Enter From and To to calculate distance
    </div>


    <!-- TRANSPORT -->

    <div class="transport-title">

        🚗 Transport Mode

    </div>


    <div class="transport-grid">


        <div class="transport"
             onclick="selectTransport(this, 0, 'Walking')">

            <span class="emoji">
                🚶
            </span>

            Walking

        </div>


        <div class="transport"
             onclick="selectTransport(this, 0, 'Bicycle')">

            <span class="emoji">
                🚲
            </span>

            Bicycle

        </div>


        <div class="transport"
             onclick="selectTransport(this, 41, 'Metro / Train')">

            <span class="emoji">
                🚇
            </span>

            Metro

        </div>


        <div class="transport"
             onclick="selectTransport(this, 88, 'Bus')">

            <span class="emoji">
                🚌
            </span>

            Bus

        </div>


        <div class="transport"
             onclick="selectTransport(this, 100, 'Auto-rickshaw')">

            <span class="emoji">
                🛺
            </span>

            Auto

        </div>


        <div class="transport"
             onclick="selectTransport(this, 170, 'Car')">

            <span class="emoji">
                🚗
            </span>

            Car

        </div>


        <div class="transport"
             onclick="selectTransport(this, 60, 'Scooter')">

            <span class="emoji">
                🛵
            </span>

            Scooter

        </div>


    </div>


    <!-- PASSENGERS -->

    <label>

        👥 Occupancy / Passengers

    </label>


    <div class="passenger">

        <input
            type="number"
            id="passengers"
            value="1"
            min="1"
        >

        <span>
            person(s)
        </span>

    </div>


    <!-- CALCULATE -->

    <button
        onclick="calculateEmission()">

        Calculate Impact

    </button>


    <!-- SAVE -->

    <button
        class="save-button"
        onclick="saveTrip()">

        💾 Save Trip

    </button>

</div>


<!-- =====================================================
     RESULT
===================================================== -->

<div class="result">


    <div class="result-title">

        TRIP FOOTPRINT

    </div>


    <div
        class="emission"
        id="result">

        0.00

    </div>


    <div class="unit">

        kg CO₂

    </div>


    <div class="meaning">

        <h3>
            What does that mean?
        </h3>

        <ul>

            <li id="comparison">

                Enter your journey to calculate.

            </li>

            <li id="distanceMessage">

                Your result will appear here.

            </li>

        </ul>

    </div>


    <div
        class="message"
        id="advice">

        🌱 Choose a transport mode
        and calculate your impact.

    </div>


    <!-- =================================================
         COMPARISON CHART
    ================================================= -->

    <div class="chart-box">

        <h3>
            Compare Transport Emissions
        </h3>


        <div class="bar">

            <span>
                Walking
            </span>

            <div class="bar-bg">

                <div
                    class="bar-fill walking"
                    id="walkingBar">
                </div>

            </div>

            <b id="walkingValue">
                0
            </b>

        </div>


        <div class="bar">

            <span>
                Bicycle
            </span>

            <div class="bar-bg">

                <div
                    class="bar-fill bicycle"
                    id="bicycleBar">
                </div>

            </div>

            <b id="bicycleValue">
                0
            </b>

        </div>


        <div class="bar">

            <span>
                Metro
            </span>

            <div class="bar-bg">

                <div
                    class="bar-fill metro"
                    id="metroBar">
                </div>

            </div>

            <b>
                41
            </b>

        </div>


        <div class="bar">

            <span>
                Bus
            </span>

            <div class="bar-bg">

                <div
                    class="bar-fill bus"
                    id="busBar">
                </div>

            </div>

            <b>
                88
            </b>

        </div>


        <div class="bar">

            <span>
                Auto
            </span>

            <div class="bar-bg">

                <div
                    class="bar-fill auto"
                    id="autoBar">
                </div>

            </div>

            <b>
                100
            </b>

        </div>


        <div class="bar">

            <span>
                Car
            </span>

            <div class="bar-bg">

                <div
                    class="bar-fill car"
                    id="carBar">
                </div>

            </div>

            <b>
                170
            </b>

        </div>


        <div class="bar">

            <span>
                Scooter
            </span>

            <div class="bar-bg">

                <div
                    class="bar-fill scooter"
                    id="scooterBar">
                </div>

            </div>

            <b>
                60
            </b>

        </div>


        <p style="
            font-size:12px;
            color:#71847c;
        ">

            Estimated values shown for comparison.

        </p>

    </div>


</div>


</div>


<footer></footer>


<script>

/* =========================================================
   DEFAULT VALUES
========================================================= */

let selectedEmission = 170;

let selectedTransport = "Car";

let currentDistance = 0;


/* =========================================================
   SELECT TRANSPORT
========================================================= */

function selectTransport(element, emission, name) {

    let all =
        document.querySelectorAll(".transport");

    all.forEach(function(item) {

        item.classList.remove("selected");

    });

    element.classList.add("selected");

    selectedEmission = emission;

    selectedTransport = name;
}


/* =========================================================
   CLEAR DISTANCE
========================================================= */

function clearDistance() {

    currentDistance = 0;

    document.getElementById("distanceBox").className =
        "distance-box";

    document.getElementById("distanceBox").innerHTML =
        "📏 Distance will be calculated automatically";
}


/* =========================================================
   GET AUTOMATIC DISTANCE
========================================================= */

async function getDistance() {

    let from =
        document.getElementById("from").value.trim();

    let to =
        document.getElementById("to").value.trim();


    if (from === "" || to === "") {

        document.getElementById("distanceBox").className =
            "distance-box distance-error";

        document.getElementById("distanceBox").innerHTML =
            "⚠️ Please enter both From and To.";

        return false;
    }


    document.getElementById("distanceBox").className =
        "distance-box distance-loading";

    document.getElementById("distanceBox").innerHTML =
        "⏳ Finding route distance...";


    try {

        let response =
            await fetch(
                "calculator.php?get_distance=1&from=" +
                encodeURIComponent(from) +
                "&to=" +
                encodeURIComponent(to)
            );


        let data =
            await response.json();


        if (data.success) {

            currentDistance =
                parseFloat(data.distance);


            document.getElementById("distanceBox").className =
                "distance-box";


            document.getElementById("distanceBox").innerHTML =
                "📏 Distance: " +
                currentDistance.toFixed(2) +
                " km";


            return true;

        } else {

            currentDistance = 0;


            document.getElementById("distanceBox").className =
                "distance-box distance-error";


            document.getElementById("distanceBox").innerHTML =
                "⚠️ " +
                data.message;


            return false;
        }


    } catch (error) {

        console.error(error);


        document.getElementById("distanceBox").className =
            "distance-box distance-error";


        document.getElementById("distanceBox").innerHTML =
            "❌ Unable to calculate distance.";


        return false;
    }

}


/* =========================================================
   CALCULATE EMISSION
========================================================= */

async function calculateEmission() {

    let from =
        document.getElementById("from").value.trim();

    let to =
        document.getElementById("to").value.trim();


    let passengers =
        parseInt(
            document.getElementById("passengers").value
        );


    if (from === "" || to === "") {

        alert(
            "Please enter From and To."
        );

        return;
    }


    if (
        isNaN(passengers) ||
        passengers <= 0
    ) {

        alert(
            "Please enter a valid number of passengers."
        );

        return;
    }


    let found =
        await getDistance();


    if (!found) {

        return;
    }


    let grams =
        (
            currentDistance *
            selectedEmission
        )
        /
        passengers;


    let kg =
        grams / 1000;


    document.getElementById("result").innerHTML =
        kg.toFixed(2);


    document.getElementById("comparison").innerHTML =

        "Your " +
        selectedTransport +
        " journey produces approximately " +
        kg.toFixed(2) +
        " kg CO₂.";


    document.getElementById("distanceMessage").innerHTML =

        "Distance: " +
        currentDistance.toFixed(2) +
        " km • Passengers: " +
        passengers;


    let advice =
        document.getElementById("advice");


    if (
        selectedTransport === "Walking" ||
        selectedTransport === "Bicycle"
    ) {

        advice.innerHTML =
            "🌱 Excellent choice! This is one of the lowest-emission ways to travel.";

    }

    else if (kg < 1) {

        advice.innerHTML =
            "🟢 Low estimated emission. Keep choosing efficient transport.";

    }

    else if (kg < 3) {

        advice.innerHTML =
            "🟡 Moderate estimated emission. Consider carpooling or public transport.";

    }

    else {

        advice.innerHTML =
            "🔴 Higher estimated emission. Try public transport, carpooling or shorter trips.";

    }


    updateChart(
        currentDistance,
        passengers
    );

}


/* =========================================================
   UPDATE CHART
========================================================= */

function updateChart(distance, passengers) {

    let walking = 0;

    let bicycle = 0;

    let metro =
        (distance * 41) / passengers;

    let bus =
        (distance * 88) / passengers;

    let auto =
        (distance * 100) / passengers;

    let car =
        (distance * 170) / passengers;

    let scooter =
        (distance * 60) / passengers;


    let values = [
        walking,
        bicycle,
        metro,
        bus,
        auto,
        car,
        scooter
    ];


    let maxValue =
        Math.max(...values);


    if (maxValue === 0) {

        maxValue = 1;

    }


    document.getElementById("walkingBar")
        .style.width =
        (walking / maxValue * 100) + "%";


    document.getElementById("bicycleBar")
        .style.width =
        (bicycle / maxValue * 100) + "%";


    document.getElementById("metroBar")
        .style.width =
        (metro / maxValue * 100) + "%";


    document.getElementById("busBar")
        .style.width =
        (bus / maxValue * 100) + "%";


    document.getElementById("autoBar")
        .style.width =
        (auto / maxValue * 100) + "%";


    document.getElementById("carBar")
        .style.width =
        (car / maxValue * 100) + "%";


    document.getElementById("scooterBar")
        .style.width =
        (scooter / maxValue * 100) + "%";


    document.getElementById("walkingValue")
        .innerHTML =
        walking.toFixed(1);


    document.getElementById("bicycleValue")
        .innerHTML =
        bicycle.toFixed(1);

}


/* =========================================================
   SAVE TRIP
========================================================= */

async function saveTrip() {

    let from =
        document.getElementById("from").value.trim();


    let to =
        document.getElementById("to").value.trim();


    let passengers =
        parseInt(
            document.getElementById("passengers").value
        );


    if (from === "" || to === "") {

        alert(
            "Please enter From and To first."
        );

        return;
    }


    if (
        isNaN(passengers) ||
        passengers <= 0
    ) {

        alert(
            "Please enter valid passengers."
        );

        return;
    }


    let found =
        await getDistance();


    if (!found) {

        return;
    }


    let grams =

        (
            currentDistance *
            selectedEmission
        )
        /
        passengers;


    let url =

        "save_trip.php" +

        "?source=" +

        encodeURIComponent(
            from + " → " + to
        ) +

        "&fuel_type=" +

        encodeURIComponent(
            selectedTransport
        ) +

        "&quantity=" +

        encodeURIComponent(
            currentDistance
        ) +

        "&emission=" +

        encodeURIComponent(
            grams
        );


    window.location.href =
        url;

}

</script>

</body>

</html>