<?php

$url = "http://localhost/black_carbon_project/ai_chat.php";

$data = json_encode([
    "message" => "What is black carbon?"
]);

$ch = curl_init($url);

curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Content-Type: application/json"
]);

$response = curl_exec($ch);

if ($response === false) {
    echo "CURL ERROR: " . curl_error($ch);
} else {

    echo "<h2>Response from ai_chat.php:</h2>";

    echo "<pre>";
    echo htmlspecialchars($response);
    echo "</pre>";
}

curl_close($ch);

?>