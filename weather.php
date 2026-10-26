<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

header("Content-Type: application/json");

$city = trim($_GET["city"] ?? "");

if ($city == "") {
    echo json_encode([
        "success" => false,
        "message" => "City is required"
    ]);
    exit;
}


/* =========================
   1. GEOCODING API
========================= */

$geoUrl =
    "https://geocoding-api.open-meteo.com/v1/search" .
    "?name=" . urlencode($city) .
    "&count=1" .
    "&language=en" .
    "&format=json";


$ch = curl_init();

curl_setopt($ch, CURLOPT_URL, $geoUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 15);

$geoResponse = curl_exec($ch);

if ($geoResponse === false) {

    echo json_encode([
        "success" => false,
        "message" => "Geocoding API connection failed",
        "error" => curl_error($ch)
    ]);

    curl_close($ch);
    exit;
}

curl_close($ch);


$geoData = json_decode($geoResponse, true);


if (
    !isset($geoData["results"]) ||
    count($geoData["results"]) == 0
) {

    echo json_encode([
        "success" => false,
        "message" => "Location not found: " . $city
    ]);

    exit;
}


$location = $geoData["results"][0];

$latitude = $location["latitude"];
$longitude = $location["longitude"];


/* =========================
   2. WEATHER API
========================= */

$weatherUrl =
    "https://api.open-meteo.com/v1/forecast" .
    "?latitude=" . $latitude .
    "&longitude=" . $longitude .
    "&current=temperature_2m,relative_humidity_2m,apparent_temperature,precipitation,rain,weather_code,wind_speed_10m" .
    "&timezone=auto";


$ch = curl_init();

curl_setopt($ch, CURLOPT_URL, $weatherUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 15);

$weatherResponse = curl_exec($ch);

if ($weatherResponse === false) {

    echo json_encode([
        "success" => false,
        "message" => "Weather API connection failed",
        "error" => curl_error($ch)
    ]);

    curl_close($ch);
    exit;
}

curl_close($ch);


$weatherData = json_decode($weatherResponse, true);


if (!isset($weatherData["current"])) {

    echo json_encode([
        "success" => false,
        "message" => "Weather data not available",
        "api_response" => $weatherData
    ]);

    exit;
}


$current = $weatherData["current"];


/* =========================
   3. WEATHER CONDITION
========================= */

$code = intval($current["weather_code"]);

$condition = "Unknown";


if ($code == 0) {

    $condition = "Clear sky";

} elseif ($code >= 1 && $code <= 3) {

    $condition = "Partly cloudy";

} elseif ($code >= 45 && $code <= 48) {

    $condition = "Foggy";

} elseif ($code >= 51 && $code <= 57) {

    $condition = "Drizzle";

} elseif ($code >= 61 && $code <= 67) {

    $condition = "Rain";

} elseif ($code >= 71 && $code <= 77) {

    $condition = "Snow";

} elseif ($code >= 80 && $code <= 82) {

    $condition = "Rain showers";

} elseif ($code >= 95 && $code <= 99) {

    $condition = "Thunderstorm";
}


/* =========================
   4. TRAVEL CONDITION
========================= */

$rain = floatval($current["rain"] ?? 0);

$precipitation =
    floatval($current["precipitation"] ?? 0);


if ($rain > 0 || $precipitation > 0) {

    $travelCondition = "Rainy weather";

} elseif ($code >= 95) {

    $travelCondition = "Unsafe weather";

} else {

    $travelCondition = "Suitable weather";
}


/* =========================
   5. SEND JSON
========================= */

echo json_encode([

    "success" => true,

    "location" => $location["name"],

    "latitude" => $latitude,

    "longitude" => $longitude,

    "temperature" =>
        $current["temperature_2m"],

    "feels_like" =>
        $current["apparent_temperature"],

    "humidity" =>
        $current["relative_humidity_2m"],

    "rain" =>
        $current["rain"],

    "precipitation" =>
        $current["precipitation"],

    "wind_speed" =>
        $current["wind_speed_10m"],

    "weather_code" =>
        $code,

    "condition" =>
        $condition,

    "travel_condition" =>
        $travelCondition,

    "time" =>
        $current["time"]

]);

?>