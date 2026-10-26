<?php

header("Content-Type: application/json");

require_once "config.php";

if (empty($GEMINI_API_KEY)) {
    echo json_encode([
        "success" => false,
        "message" => "Gemini API key is missing"
    ]);
    exit;
}

$url = "https://generativelanguage.googleapis.com/v1beta/interactions";

$data = [
    "model" => "gemini-3.8-flash",
    "input" => "Say only: Gemini AI is working"
];

$ch = curl_init($url);

curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);

curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Content-Type: application/json",
    "x-goog-api-key: " . $GEMINI_API_KEY
]);

curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

$response = curl_exec($ch);

if ($response === false) {
    echo json_encode([
        "success" => false,
        "message" => curl_error($ch)
    ]);
    curl_close($ch);
    exit;
}

curl_close($ch);

$result = json_decode($response, true);

if (isset($result["steps"])) {

    $text = "";

    foreach ($result["steps"] as $step) {
        if (isset($step["content"])) {
            foreach ($step["content"] as $content) {
                if (isset($content["text"])) {
                    $text .= $content["text"];
                }
            }
        }
    }

    echo json_encode([
        "success" => true,
        "message" => $text
    ]);

} else {

    echo json_encode([
        "success" => false,
        "response" => $result
    ]);
}
?>