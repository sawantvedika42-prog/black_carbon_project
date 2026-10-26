<?php
require_once __DIR__ . "/auth.php";
?>
<?php

date_default_timezone_set('Asia/Kolkata');

require_once "db.php";
require_once "config.php";


/* =========================================================
   WEATHER
   ========================================================= */

function getWeather($city)
{
    $url = "weather.php?city=" . urlencode($city);

    $response = @file_get_contents($url);

    if ($response === false) {
        return [
            "condition" => "Unknown",
            "temperature" => 0
        ];
    }

    $data = json_decode($response, true);

    if (!$data) {
        return [
            "condition" => "Unknown",
            "temperature" => 0
        ];
    }

    return [
        "condition" => $data["condition"] ?? "Unknown",
        "temperature" => $data["temperature"] ?? 0
    ];
}


/* =========================================================
   SHORTEST PATH
   ========================================================= */

function findShortestPath($conn, $source, $destination)
{
    $source = trim($source);
    $destination = trim($destination);

    if ($source === "" || $destination === "") {
        return [
            "path" => [],
            "distance" => 0
        ];
    }

    if (strcasecmp($source, $destination) === 0) {
        return [
            "path" => [$source],
            "distance" => 0
        ];
    }

    $graph = [];

    $sql = "
        SELECT source, destination, distance
        FROM routes
    ";

    $result = mysqli_query($conn, $sql);

    if (!$result) {
        return [
            "path" => [],
            "distance" => 0
        ];
    }

    while ($row = mysqli_fetch_assoc($result)) {

        $from = strtolower(trim($row["source"]));
        $to = strtolower(trim($row["destination"]));
        $distance = floatval($row["distance"]);

        if (!isset($graph[$from])) {
            $graph[$from] = [];
        }

        if (!isset($graph[$to])) {
            $graph[$to] = [];
        }

        $graph[$from][] = [
            "node" => $to,
            "distance" => $distance
        ];

        $graph[$to][] = [
            "node" => $from,
            "distance" => $distance
        ];
    }


    $start = strtolower($source);
    $end = strtolower($destination);

    if (!isset($graph[$start]) || !isset($graph[$end])) {
        return [
            "path" => [$source, $destination],
            "distance" => 0
        ];
    }


    $distances = [];
    $previous = [];
    $visited = [];

    foreach ($graph as $node => $edges) {
        $distances[$node] = INF;
        $previous[$node] = null;
        $visited[$node] = false;
    }

    $distances[$start] = 0;


    while (true) {

        $current = null;
        $currentDistance = INF;

        foreach ($distances as $node => $distance) {

            if (!$visited[$node] && $distance < $currentDistance) {
                $current = $node;
                $currentDistance = $distance;
            }
        }

        if ($current === null) {
            break;
        }

        if ($current === $end) {
            break;
        }

        $visited[$current] = true;


        foreach ($graph[$current] as $edge) {

            $neighbor = $edge["node"];
            $newDistance =
                $distances[$current] + $edge["distance"];

            if ($newDistance < $distances[$neighbor]) {

                $distances[$neighbor] = $newDistance;
                $previous[$neighbor] = $current;
            }
        }
    }


    if ($distances[$end] === INF) {

        return [
            "path" => [$source, $destination],
            "distance" => 0
        ];
    }


    $path = [];

    $current = $end;

    while ($current !== null) {

        $path[] = $current;
        $current = $previous[$current];
    }

    $path = array_reverse($path);


    foreach ($path as &$place) {

        if ($place === $start) {
            $place = $source;
        }

        elseif ($place === $end) {
            $place = $destination;
        }
        else {
            $place = ucwords($place);
        }
    }

    return [
        "path" => $path,
        "distance" => round($distances[$end], 2)
    ];
}


/* =========================================================
   TIME / TRAFFIC
   ========================================================= */

function getTravelTimeInfo($travelDate, $travelTime)
{
    $timestamp = strtotime($travelDate . " " . $travelTime);

    if (!$timestamp) {
        return [
            "traffic" => "Moderate",
            "period" => "Unknown"
        ];
    }

    $hour = intval(date("H", $timestamp));

    if (($hour >= 7 && $hour <= 10) ||
        ($hour >= 17 && $hour <= 21)) {

        $traffic = "Peak";

    } elseif ($hour >= 11 && $hour <= 16) {

        $traffic = "Moderate";

    } else {

        $traffic = "Low";
    }


    if ($hour >= 5 && $hour < 12) {
        $period = "Morning";
    }

    elseif ($hour >= 12 && $hour < 17) {
        $period = "Afternoon";
    }

    elseif ($hour >= 17 && $hour < 21) {
        $period = "Evening";
    }

    else {
        $period = "Night";
    }


    return [
        "traffic" => $traffic,
        "period" => $period
    ];
}


/* =========================================================
   COST CALCULATION
   ========================================================= */

function calculateCost($mode, $distance, $people)
{
    $people = max(1, intval($people));
    $distance = max(0, floatval($distance));


    switch ($mode) {

        case "train":

            // Approximate public transport fare
            $totalCost = max(10, $distance * 2.5);

            break;


        case "bus":

            // Approximate bus fare
            $totalCost = max(10, $distance * 1.8);

            break;


        case "car":

            // Approximate petrol cost
            $fuelPrice = 105;
            $mileage = 15;

            if ($distance <= 0) {
                $totalCost = 0;
            }
            else {
                $fuelUsed = $distance / $mileage;
                $totalCost = $fuelUsed * $fuelPrice;
            }

            break;


        case "auto":

            $totalCost = 30 + ($distance * 20);

            break;


        case "walking":
        case "bicycle":

            $totalCost = 0;

            break;


        default:

            $totalCost = 0;
    }


    $costPerPerson = $totalCost / $people;


    return [
        "total" => round($totalCost, 2),
        "per_person" => round($costPerPerson, 2)
    ];
}


/* =========================================================
   EMISSION
   ========================================================= */

function calculateEmission($mode, $distance, $people)
{
    $people = max(1, intval($people));

    $factors = [

        "train" => 41,

        "bus" => 88,

        "car" => 170,

        "auto" => 100,

        "walking" => 0,

        "bicycle" => 0
    ];


    $factor = $factors[$mode] ?? 0;

    $totalEmission =
        $distance * $factor;


    $perPerson =
        $totalEmission / $people;


    return [
        "total" => round($totalEmission, 2),
        "per_person" => round($perPerson, 2)
    ];
}


/* =========================================================
   TRAVEL TIME
   ========================================================= */

function calculateTravelTime($mode, $distance)
{
    $distance = max(0, floatval($distance));


    switch ($mode) {

        case "train":

            // Average including stops/waiting
            $speed = 45;

            break;


        case "bus":

            $speed = 30;

            break;


        case "car":

            $speed = 40;

            break;


        case "auto":

            $speed = 30;

            break;


        case "walking":

            $speed = 5;

            break;


        case "bicycle":

            $speed = 15;

            break;


        default:

            $speed = 30;
    }


    if ($distance <= 0) {
        return 0;
    }


    $minutes =
        ($distance / $speed) * 60;


    return max(1, round($minutes));
}


/* =========================================================
   COMFORT
   ========================================================= */

function getComfort($mode)
{
    switch ($mode) {

        case "train":
            return 7;

        case "bus":
            return 6;

        case "car":
            return 9;

        case "auto":
            return 7;

        case "walking":
            return 5;

        case "bicycle":
            return 6;

        default:
            return 5;
    }
}


/* =========================================================
   SCORE CALCULATION
   ========================================================= */

function calculateOptionScore(
    $option,
    $allOptions,
    $people,
    $traffic,
    $weather
) {

    $mode = $option["mode"];


    /* -----------------------------------------------------
       GET VALUES FOR NORMALIZATION
       ----------------------------------------------------- */

    $emissions = [];
    $costs = [];
    $times = [];


    foreach ($allOptions as $item) {

        $emissions[] =
            $item["emission_per_person"];

        $costs[] =
            $item["cost_per_person"];

        $times[] =
            $item["time_minutes"];
    }


    $minEmission = min($emissions);
    $maxEmission = max($emissions);

    $minCost = min($costs);
    $maxCost = max($costs);

    $minTime = min($times);
    $maxTime = max($times);


    /* -----------------------------------------------------
       EMISSION SCORE
       ----------------------------------------------------- */

    if ($maxEmission == $minEmission) {

        $emissionScore = 100;

    } else {

        $emissionScore =
            100 *
            (
                ($maxEmission - $option["emission_per_person"]) /
                ($maxEmission - $minEmission)
            );
    }


    /* -----------------------------------------------------
       COST SCORE
       ----------------------------------------------------- */

    if ($maxCost == $minCost) {

        $costScore = 100;

    } else {

        $costScore =
            100 *
            (
                ($maxCost - $option["cost_per_person"]) /
                ($maxCost - $minCost)
            );
    }


    /* -----------------------------------------------------
       TIME SCORE
       ----------------------------------------------------- */

    if ($maxTime == $minTime) {

        $timeScore = 100;

    } else {

        $timeScore =
            100 *
            (
                ($maxTime - $option["time_minutes"]) /
                ($maxTime - $minTime)
            );
    }


    /* -----------------------------------------------------
       TRAFFIC
       ----------------------------------------------------- */

    $trafficScore = 100;


    if ($traffic === "Peak") {

        if ($mode === "train") {
            $trafficScore = 90;
        }

        elseif ($mode === "bus") {
            $trafficScore = 60;
        }

        elseif ($mode === "car") {
            $trafficScore = 55;
        }

        elseif ($mode === "auto") {
            $trafficScore = 55;
        }

    }

    elseif ($traffic === "Moderate") {

        if ($mode === "train") {
            $trafficScore = 90;
        }

        elseif ($mode === "bus") {
            $trafficScore = 75;
        }

        elseif ($mode === "car") {
            $trafficScore = 75;
        }

        elseif ($mode === "auto") {
            $trafficScore = 70;
        }
    }


    /* -----------------------------------------------------
       WEATHER
       ----------------------------------------------------- */

    $weatherScore = 100;

    $weatherText = strtolower($weather);


    if (
        strpos($weatherText, "rain") !== false ||
        strpos($weatherText, "storm") !== false ||
        strpos($weatherText, "thunder") !== false
    ) {

        if ($mode === "car") {
            $weatherScore = 95;
        }

        elseif ($mode === "train") {
            $weatherScore = 85;
        }

        elseif ($mode === "bus") {
            $weatherScore = 80;
        }

        elseif ($mode === "auto") {
            $weatherScore = 70;
        }

        elseif ($mode === "walking" ||
                $mode === "bicycle") {

            $weatherScore = 30;
        }
    }


    /* -----------------------------------------------------
       GROUP SUITABILITY
       ----------------------------------------------------- */

    $groupScore = 50;


    if ($people == 1) {

        if ($mode === "train") {
            $groupScore = 90;
        }

        elseif ($mode === "bus") {
            $groupScore = 82;
        }

        elseif ($mode === "auto") {
            $groupScore = 75;
        }

        elseif ($mode === "car") {
            $groupScore = 55;
        }
    }


    elseif ($people == 2) {

        if ($mode === "train") {
            $groupScore = 85;
        }

        elseif ($mode === "bus") {
            $groupScore = 80;
        }

        elseif ($mode === "auto") {
            $groupScore = 80;
        }

        elseif ($mode === "car") {
            $groupScore = 75;
        }
    }


    elseif ($people == 3) {

        if ($mode === "train") {
            $groupScore = 75;
        }

        elseif ($mode === "bus") {
            $groupScore = 78;
        }

        elseif ($mode === "auto") {
            $groupScore = 60;
        }

        elseif ($mode === "car") {
            $groupScore = 88;
        }
    }


    elseif ($people >= 4) {

        if ($mode === "train") {
            $groupScore = 65;
        }

        elseif ($mode === "bus") {
            $groupScore = 78;
        }

        elseif ($mode === "auto") {
            $groupScore = 45;
        }

        elseif ($mode === "car") {
            $groupScore = 100;
        }
    }


    /* -----------------------------------------------------
       FINAL WEIGHTS
       -----------------------------------------------------

       Emission = 20%
       Cost     = 25%
       Time     = 20%
       Comfort  = 15%
       Traffic  = 10%
       Group    = 10%
       ----------------------------------------------------- */

    $score =
        ($emissionScore * 0.20) +
        ($costScore * 0.25) +
        ($timeScore * 0.20) +
        (($option["comfort"] * 10) * 0.15) +
        ($trafficScore * 0.10) +
        ($groupScore * 0.10);


    /* -----------------------------------------------------
       WEATHER PENALTY
       ----------------------------------------------------- */

    if (
        (
            strpos($weatherText, "rain") !== false ||
            strpos($weatherText, "storm") !== false
        )
        &&
        (
            $mode === "walking" ||
            $mode === "bicycle"
        )
    ) {

        $score -= 20;
    }


    /* -----------------------------------------------------
       KEEP SCORE BETWEEN 0 AND 100
       ----------------------------------------------------- */

    $score =
        max(0, min(100, $score));


    return round($score, 1);
}


/* =========================================================
   GET INPUT
   ========================================================= */

$source = trim($_POST["source"] ?? "");
$destination = trim($_POST["destination"] ?? "");

$people =
    max(
        1,
        intval($_POST["people"] ?? 1)
    );

$travelDate =
    $_POST["travel_date"] ??
    date("Y-m-d");

$travelTime =
    $_POST["travel_time"] ??
    date("H:i");


$showResults = false;

$route = [];
$distance = 0;

$options = [];

$weather = [
    "condition" => "Unknown",
    "temperature" => 0
];

$traffic = "Moderate";
$period = "Unknown";

$aiRecommendation = "";


/* =========================================================
   ANALYZE
   ========================================================= */

if (
    $source !== "" &&
    $destination !== ""
) {

    $showResults = true;


    /* ROUTE */

    $routeResult =
        findShortestPath(
            $conn,
            $source,
            $destination
        );


    $route =
        $routeResult["path"];

    $distance =
        $routeResult["distance"];


    /* WEATHER */

    $weather =
        getWeather($destination);


    /* TRAFFIC */

    $travelInfo =
        getTravelTimeInfo(
            $travelDate,
            $travelTime
        );


    $traffic =
        $travelInfo["traffic"];

    $period =
        $travelInfo["period"];


    /* -----------------------------------------------------
       TRANSPORT OPTIONS
       ----------------------------------------------------- */

    $modes = [

        "train" => [
            "name" => "Local Train",
            "icon" => "🚆"
        ],

        "bus" => [
            "name" => "Public Bus",
            "icon" => "🚌"
        ],

        "car" => [
            "name" => "Private Car",
            "icon" => "🚗"
        ],

        "auto" => [
            "name" => "Auto Rickshaw",
            "icon" => "🛺"
        ]
    ];


    foreach ($modes as $mode => $details) {

        $cost =
            calculateCost(
                $mode,
                $distance,
                $people
            );


        $emission =
            calculateEmission(
                $mode,
                $distance,
                $people
            );


        $time =
            calculateTravelTime(
                $mode,
                $distance
            );


        $comfort =
            getComfort($mode);


        $options[] = [

            "mode" => $mode,

            "name" =>
                $details["name"],

            "icon" =>
                $details["icon"],

            "distance" =>
                $distance,

            "time_minutes" =>
                $time,

            "cost_total" =>
                $cost["total"],

            "cost_per_person" =>
                $cost["per_person"],

            "emission_total" =>
                $emission["total"],

            "emission_per_person" =>
                $emission["per_person"],

            "comfort" =>
                $comfort
        ];
    }


    /* -----------------------------------------------------
       ADD WALKING / BICYCLE ONLY FOR SHORT TRIPS
       ----------------------------------------------------- */

    if ($distance > 0 && $distance <= 5) {

        $cost =
            calculateCost(
                "walking",
                $distance,
                $people
            );

        $emission =
            calculateEmission(
                "walking",
                $distance,
                $people
            );

        $time =
            calculateTravelTime(
                "walking",
                $distance
            );


        $options[] = [

            "mode" => "walking",

            "name" => "Walking",

            "icon" => "🚶",

            "distance" => $distance,

            "time_minutes" => $time,

            "cost_total" =>
                $cost["total"],

            "cost_per_person" =>
                $cost["per_person"],

            "emission_total" =>
                $emission["total"],

            "emission_per_person" =>
                $emission["per_person"],

            "comfort" =>
                getComfort("walking")
        ];
    }


    if ($distance > 0 && $distance <= 15) {

        $cost =
            calculateCost(
                "bicycle",
                $distance,
                $people
            );

        $emission =
            calculateEmission(
                "bicycle",
                $distance,
                $people
            );

        $time =
            calculateTravelTime(
                "bicycle",
                $distance
            );


        $options[] = [

            "mode" => "bicycle",

            "name" => "Bicycle",

            "icon" => "🚲",

            "distance" => $distance,

            "time_minutes" => $time,

            "cost_total" =>
                $cost["total"],

            "cost_per_person" =>
                $cost["per_person"],

            "emission_total" =>
                $emission["total"],

            "emission_per_person" =>
                $emission["per_person"],

            "comfort" =>
                getComfort("bicycle")
        ];
    }


    /* -----------------------------------------------------
       CALCULATE SCORES
       ----------------------------------------------------- */

    foreach ($options as &$option) {

        $option["score"] =
            calculateOptionScore(
                $option,
                $options,
                $people,
                $traffic,
                $weather["condition"]
            );
    }

    unset($option);


    /* -----------------------------------------------------
       SORT BEST FIRST
       ----------------------------------------------------- */

    usort(
        $options,
        function ($a, $b) {

            return
                $b["score"] <=> $a["score"];
        }
    );


    /* -----------------------------------------------------
       BEST OPTION
       ----------------------------------------------------- */

    $bestOption =
        $options[0] ?? null;


    /* -----------------------------------------------------
       AI RECOMMENDATION
       ----------------------------------------------------- */

    if ($bestOption) {

        $aiRecommendation =
            $bestOption["name"] .
            " is recommended for this trip because it provides the best overall balance of cost, travel time, emissions, comfort, traffic and group suitability for " .
            $people .
            " passenger(s).";
    }
}


/* =========================================================
   HTML
   ========================================================= */
?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
      content="width=device-width, initial-scale=1.0">

<title>AI Travel Indicator</title>


<style>

* {
    box-sizing: border-box;
}


body {

    margin: 0;

    font-family:
        Arial,
        Helvetica,
        sans-serif;

    background: #f5f7f6;

    color: #222;
}


/* NAVBAR */

nav {

    height: 70px;

    background: #ffffff;

    display: flex;

    align-items: center;

    justify-content: space-between;

    padding: 0 7%;

    box-shadow:
        0 2px 10px rgba(0,0,0,0.06);
}


.logo {

    font-size: 21px;

    font-weight: bold;

    color: #1d3b2a;
}


.nav-links {

    display: flex;

    gap: 25px;
}


.nav-links a {

    text-decoration: none;

    color: #444;

    font-size: 15px;
}


.nav-links a:hover,
.nav-links a.active {

    color: #1b7a43;

    font-weight: 600;
}


/* MAIN */

.container {

    width: 90%;

    max-width: 1250px;

    margin: 35px auto;
}


/* HEADER */

.page-title {

    text-align: center;

    margin-bottom: 25px;
}


.page-title h1 {

    margin-bottom: 8px;

    color: #1c3d2a;
}


.page-title p {

    color: #666;

    margin: 0;
}


/* FORM */

.search-box {

    background: white;

    padding: 25px;

    border-radius: 15px;

    box-shadow:
        0 4px 20px rgba(0,0,0,0.06);
}


.form-grid {

    display: grid;

    grid-template-columns:
        1.4fr
        1.4fr
        0.8fr
        1fr
        1fr;

    gap: 15px;

    align-items: end;
}


.form-group label {

    display: block;

    margin-bottom: 7px;

    font-weight: 600;

    font-size: 14px;
}


.form-group input {

    width: 100%;

    padding: 12px;

    border: 1px solid #d7d7d7;

    border-radius: 8px;

    font-size: 14px;

    outline: none;
}


.form-group input:focus {

    border-color: #26834b;
}


.analyze-button {

    margin-top: 20px;

    width: 100%;

    border: none;

    background: #1d7d46;

    color: white;

    padding: 14px;

    border-radius: 9px;

    font-size: 16px;

    font-weight: 600;

    cursor: pointer;
}


.analyze-button:hover {

    background: #176638;
}


/* SUMMARY */

.summary {

    margin-top: 25px;

    background: white;

    border-radius: 15px;

    padding: 20px;

    box-shadow:
        0 4px 20px rgba(0,0,0,0.05);
}


.summary-grid {

    display: grid;

    grid-template-columns:
        repeat(4, 1fr);

    gap: 15px;
}


.summary-item {

    background: #f7faf8;

    padding: 15px;

    border-radius: 10px;
}


.summary-item span {

    display: block;

    font-size: 13px;

    color: #777;

    margin-bottom: 6px;
}


.summary-item strong {

    font-size: 17px;
}


/* ROUTE */

.route-box {

    margin-top: 20px;

    padding: 18px;

    background: #ffffff;

    border-radius: 15px;

    box-shadow:
        0 4px 20px rgba(0,0,0,0.05);
}


.route-line {

    color: #333;

    font-weight: 500;

    line-height: 1.8;
}


/* BEST */

.best-box {

    margin-top: 25px;

    background: #eaf6ee;

    border: 1px solid #b9dec5;

    padding: 20px;

    border-radius: 15px;
}


.best-box h2 {

    margin-top: 0;

    color: #176638;
}


.best-name {

    font-size: 22px;

    font-weight: bold;

    margin-bottom: 8px;
}


/* TRANSPORT GRID */

.transport-grid {

    display: grid;

    grid-template-columns:
        repeat(4, minmax(0, 1fr));

    gap: 16px;

    margin-top: 25px;

    width: 100%;
}


.transport-card {

    background: white;

    border-radius: 15px;

    padding: 20px;

    border: 1px solid #e5e5e5;

    box-shadow:
        0 4px 18px rgba(0,0,0,0.05);

    min-width: 0;
}


.transport-card.best {

    border:
        2px solid #1d7d46;
}


.transport-title {

    display: flex;

    align-items: center;

    gap: 9px;

    font-size: 18px;

    font-weight: bold;

    margin-bottom: 15px;
}


.transport-icon {

    font-size: 25px;
}


.score {

    font-size: 30px;

    font-weight: bold;

    color: #1d7d46;

    margin-bottom: 15px;
}


.detail {

    display: flex;

    justify-content: space-between;

    padding: 8px 0;

    border-bottom: 1px solid #eeeeee;

    font-size: 13px;
}


.detail:last-child {

    border-bottom: none;
}


.detail span:first-child {

    color: #777;
}


.detail span:last-child {

    font-weight: 600;

    text-align: right;
}


.recommended {

    display: inline-block;

    background: #1d7d46;

    color: white;

    padding: 5px 9px;

    border-radius: 20px;

    font-size: 12px;

    margin-bottom: 10px;
}


/* AI */

.ai-box {

    margin-top: 25px;

    background: white;

    padding: 22px;

    border-radius: 15px;

    box-shadow:
        0 4px 20px rgba(0,0,0,0.05);
}


.ai-box h2 {

    margin-top: 0;

    color: #1d3d2b;
}


.ai-text {

    line-height: 1.7;

    color: #444;
}


/* RESPONSIVE */

@media (max-width: 1100px) {

    .form-grid {

        grid-template-columns:
            repeat(2, 1fr);
    }


    .transport-grid {

        grid-template-columns:
            repeat(2, 1fr);
    }


    .summary-grid {

        grid-template-columns:
            repeat(2, 1fr);
    }
}


@media (max-width: 700px) {

    nav {

        height: auto;

        padding: 15px;

        flex-direction: column;

        gap: 12px;
    }


    .nav-links {

        flex-wrap: wrap;

        justify-content: center;

        gap: 14px;
    }


    .form-grid {

        grid-template-columns: 1fr;
    }


    .transport-grid {

        grid-template-columns: 1fr;
    }


    .summary-grid {

        grid-template-columns: 1fr;
    }
}

</style>

</head>


<body>


<!-- NAVBAR -->

<nav>

    <div class="logo">
        Black Carbon
    </div>

    <div class="nav-links">

        <a href="index.php">
            Home
        </a>

        <a href="calculator.php">
            Calculator
        </a>

        <a href="insight.php">
            Insights
        </a>

        <a href="indicator.php"
           class="active">
            AI Indicator
        </a>

        <a href="chatbot.php">
            AI Assistant
        </a>

    </div>

</nav>


<div class="container">


<!-- TITLE -->

<div class="page-title">

    <h1>AI Travel Indicator</h1>

    <p>
        Compare travel options based on your trip conditions.
    </p>

</div>


<!-- FORM -->

<form method="POST"
      class="search-box">


    <div class="form-grid">


        <div class="form-group">

            <label>
                Source
            </label>

            <input
                type="text"
                name="source"
                placeholder="Enter source"
                value="<?php echo htmlspecialchars($source); ?>"
                required
            >

        </div>


        <div class="form-group">

            <label>
                Destination
            </label>

            <input
                type="text"
                name="destination"
                placeholder="Enter destination"
                value="<?php echo htmlspecialchars($destination); ?>"
                required
            >

        </div>


        <div class="form-group">

            <label>
                Number of People
            </label>

            <input
                type="number"
                name="people"
                min="1"
                max="20"
                value="<?php echo $people; ?>"
                required
            >

        </div>


        <div class="form-group">

            <label>
                Travel Date
            </label>

            <input
                type="date"
                name="travel_date"
                value="<?php echo htmlspecialchars($travelDate); ?>"
                required
            >

        </div>


        <div class="form-group">

            <label>
                Travel Time
            </label>

            <input
                type="time"
                name="travel_time"
                value="<?php echo htmlspecialchars($travelTime); ?>"
                required
            >

        </div>

    </div>


    <button
        type="submit"
        class="analyze-button">

        Analyze Travel Options

    </button>

</form>


<?php if ($showResults): ?>


<!-- SUMMARY -->

<div class="summary">

    <div class="summary-grid">


        <div class="summary-item">

            <span>
                Route
            </span>

            <strong>
                <?php
                echo htmlspecialchars($source);
                ?>
                →
                <?php
                echo htmlspecialchars($destination);
                ?>
            </strong>

        </div>


        <div class="summary-item">

            <span>
                Distance
            </span>

            <strong>
                <?php
                echo number_format($distance, 1);
                ?>
                km
            </strong>

        </div>


        <div class="summary-item">

            <span>
                People
            </span>

            <strong>
                <?php
                echo $people;
                ?>
            </strong>

        </div>


        <div class="summary-item">

            <span>
                Traffic
            </span>

            <strong>
                <?php
                echo htmlspecialchars($traffic);
                ?>
            </strong>

        </div>

    </div>

</div>


<!-- ROUTE -->

<div class="route-box">

    <h3>
        Suggested Route
    </h3>

    <div class="route-line">

        <?php

        if (!empty($route)) {

            echo implode(
                " → ",
                array_map(
                    "htmlspecialchars",
                    $route
                )
            );

        } else {

            echo
                htmlspecialchars($source) .
                " → " .
                htmlspecialchars($destination);
        }

        ?>

    </div>

</div>


<!-- BEST -->

<?php if ($bestOption): ?>

<div class="best-box">

    <h2>
        Recommended Option
    </h2>

    <div class="best-name">

        <?php
        echo $bestOption["icon"] .
             " " .
             htmlspecialchars(
                 $bestOption["name"]
             );
        ?>

    </div>

    <p>

        Score:
        <strong>
            <?php
            echo $bestOption["score"];
            ?>/100
        </strong>

    </p>

    <p>

        Recommended for
        <strong>
            <?php echo $people; ?>
        </strong>
        passenger(s).

    </p>

</div>

<?php endif; ?>


<!-- TRANSPORT OPTIONS -->

<div class="transport-grid">


<?php foreach ($options as $option): ?>


<div class="transport-card
    <?php
    echo (
        $bestOption &&
        $option["mode"] ===
        $bestOption["mode"]
    )
    ? "best"
    : "";
    ?>">


    <?php

    if (
        $bestOption &&
        $option["mode"] ===
        $bestOption["mode"]
    ):

    ?>

        <div class="recommended">
            Recommended
        </div>

    <?php endif; ?>


    <div class="transport-title">

        <span class="transport-icon">

            <?php
            echo $option["icon"];
            ?>

        </span>

        <span>

            <?php
            echo htmlspecialchars(
                $option["name"]
            );
            ?>

        </span>

    </div>


    <div class="score">

        <?php
        echo $option["score"];
        ?>/100

    </div>


    <div class="detail">

        <span>
            Travel Time
        </span>

        <span>

            <?php
            echo $option["time_minutes"];
            ?>
            min

        </span>

    </div>


    <div class="detail">

        <span>
            Total Cost
        </span>

        <span>

            ₹<?php
            echo number_format(
                $option["cost_total"],
                0
            );
            ?>

        </span>

    </div>


    <div class="detail">

        <span>
            Cost / Person
        </span>

        <span>

            ₹<?php
            echo number_format(
                $option["cost_per_person"],
                0
            );
            ?>

        </span>

    </div>


    <div class="detail">

        <span>
            Emission / Person
        </span>

        <span>

            <?php
            echo number_format(
                $option["emission_per_person"],
                0
            );
            ?>
            g

        </span>

    </div>


    <div class="detail">

        <span>
            Comfort
        </span>

        <span>

            <?php
            echo $option["comfort"];
            ?>/10

        </span>

    </div>


    <div class="detail">

        <span>
            Traffic
        </span>

        <span>

            <?php
            echo htmlspecialchars(
                $traffic
            );
            ?>

        </span>

    </div>


</div>


<?php endforeach; ?>

</div>


<!-- AI EXPLANATION -->

<div class="ai-box">

    <h2>
        AI Travel Recommendation
    </h2>

    <div class="ai-text">

        <?php
        echo htmlspecialchars(
            $aiRecommendation
        );
        ?>

    </div>

</div>


<?php endif; ?>


</div>


</body>

</html>