<?php

header("Content-Type: application/json");

require_once "config.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode([
        "success" => false,
        "message" => "Only POST requests are allowed."
    ]);
    exit;
}

$input = json_decode(file_get_contents("php://input"), true);

if (!$input) {
    echo json_encode([
        "success" => false,
        "message" => "Invalid request data."
    ]);
    exit;
}

$from      = trim($input["from"] ?? "");
$to        = trim($input["to"] ?? "");
$people    = (int)($input["people"] ?? 1);
$distance  = (float)($input["distance"] ?? 0);
$path      = $input["path"] ?? [];
$weather   = trim($input["weather"] ?? "Unavailable");
$time      = trim($input["time"] ?? "Unknown");
$traffic   = trim($input["traffic"] ?? "Unknown");
$options   = $input["options"] ?? [];

if ($from === "" || $to === "") {
    echo json_encode([
        "success" => false,
        "message" => "Source and destination are required."
    ]);
    exit;
}

if ($people < 1) {
    $people = 1;
}


/* -------------------------------------------------
   GEMINI API KEY
------------------------------------------------- */

if (!isset($GEMINI_API_KEY) || trim($GEMINI_API_KEY) === "") {
    echo json_encode([
        "success" => false,
        "message" => "Gemini API key is missing in config.php."
    ]);
    exit;
}


/* -------------------------------------------------
   PREPARE ROUTE
------------------------------------------------- */

$pathText = "";

if (is_array($path) && count($path) > 0) {
    $pathText = implode(" → ", $path);
} else {
    $pathText = $from . " → " . $to;
}


/* -------------------------------------------------
   PREPARE TRANSPORT DATA
------------------------------------------------- */

$optionsText = "";

if (is_array($options) && count($options) > 0) {

    foreach ($options as $index => $option) {

        $name = $option["name"] ?? "Unknown";
        $cost = $option["cost"] ?? 0;
        $costPerson = $option["cost_per_person"] ?? 0;
        $emission = $option["emission"] ?? 0;
        $emissionPerson = $option["emission_per_person"] ?? 0;
        $comfort = $option["comfort"] ?? 0;
        $impact = $option["impact"] ?? "Unknown";
        $score = $option["score"] ?? 0;

        $optionsText .=
            ($index + 1) . ". " . $name .
            " | Total Cost: ₹" . $cost .
            " | Cost/Person: ₹" . $costPerson .
            " | Total Emission: " . $emission . " kg" .
            " | Emission/Person: " . $emissionPerson . " kg" .
            " | Comfort: " . $comfort . "/10" .
            " | Environmental Impact: " . $impact .
            " | Score: " . $score . "/10\n";
    }

} else {

    $optionsText = "No transport option data available.";
}


/* -------------------------------------------------
   GEMINI PROMPT
------------------------------------------------- */

$prompt = <<<PROMPT

You are an AI-based Smart Travel Recommendation System
for a college project called:

"Black Carbon Emission Monitoring System"

Your task is to analyze the journey and recommend the most
suitable transportation option.

JOURNEY DETAILS

Source: $from
Destination: $to
Shortest Route: $pathText
Distance: $distance km
Number of People: $people
Current Time: $time
Traffic Condition: $traffic
Weather: $weather


AVAILABLE TRANSPORT OPTIONS

$optionsText


IMPORTANT DECISION RULES

1. Consider the number of people travelling.

2. Cost per person is important.

3. Emission per person is important.

4. If many people are travelling together, a car can become
   a practical and environmentally reasonable option because
   the cost and emission are shared among passengers.

5. For example, if 5 people are travelling together, do NOT
   automatically reject the car just because a car has higher
   total emissions.

6. For one person, public transport such as train or bus may
   often be more practical.

7. Consider comfort as well as cost and environmental impact.

8. Consider traffic condition.

9. During peak traffic, public transport may be preferable
   if it has a better overall score.

10. Consider weather.

11. If weather is rainy or stormy, do not recommend walking
    or cycling as the main option.

12. If weather is suitable and the distance is short,
    walking or cycling can be considered.

13. Do not invent weather, traffic, distance, cost or emission
    values. Use only the information provided.

14. The final recommendation must be practical, not based only
    on minimum emission.

15. Select the option that provides the best overall balance
    of:
    - Cost
    - Cost per person
    - Emission
    - Emission per person
    - Comfort
    - Traffic
    - Weather
    - Number of passengers


OUTPUT FORMAT

Give the answer in exactly this structure:

🤖 AI Travel Recommendation

Best Option:
Name the recommended transport option.

Why This Option:
Explain clearly why this option is best considering people,
cost, emission, comfort, weather and traffic.

Cost Analysis:
Mention the total cost and cost per person.

Environmental Impact:
Mention total emission and emission per person.

Weather & Traffic:
Explain how weather and traffic affect the recommendation.

Better Alternatives:
1. Mention one alternative and why it may be suitable.
2. Mention another alternative and why it may be suitable.

Final Recommendation:
Give a short practical recommendation for the user.

Keep the answer simple, clear and suitable for a college
mini-project. Do not use unnecessary technical language.

PROMPT;


/* -------------------------------------------------
   GEMINI MODEL
------------------------------------------------- */

/*
   Keep the same model that is already working in your
   test_gemini.php.

   If your config.php contains $GEMINI_MODEL, it will use it.
*/

$geminiModel = "gemini-3.5-flash";

if (isset($GEMINI_MODEL) && trim($GEMINI_MODEL) !== "") {
    $geminiModel = trim($GEMINI_MODEL);
}


/* -------------------------------------------------
   GEMINI INTERACTIONS API
------------------------------------------------- */

$url = "https://generativelanguage.googleapis.com/v1beta/interactions";


$data = [
    "model" => $geminiModel,
    "input" => $prompt
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

$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

$curlError = curl_error($ch);

curl_close($ch);


/* -------------------------------------------------
   CURL ERROR
------------------------------------------------- */

if ($response === false || $curlError !== "") {

    echo json_encode([
        "success" => false,
        "message" => "Gemini connection failed: " . $curlError
    ]);

    exit;
}


/* -------------------------------------------------
   DECODE GEMINI RESPONSE
------------------------------------------------- */

$result = json_decode($response, true);

if (!$result) {

    echo json_encode([
        "success" => false,
        "message" => "Invalid response received from Gemini."
    ]);

    exit;
}


/* -------------------------------------------------
   CHECK API ERROR
------------------------------------------------- */

if ($httpCode < 200 || $httpCode >= 300) {

    $errorMessage = "Gemini API error.";

    if (isset($result["error"]["message"])) {
        $errorMessage = $result["error"]["message"];
    }

    echo json_encode([
        "success" => false,
        "message" => $errorMessage
    ]);

    exit;
}


/* -------------------------------------------------
   EXTRACT TEXT
------------------------------------------------- */

$reply = "";


/*
   Interactions API response can contain output.
*/

if (isset($result["outputs"]) && is_array($result["outputs"])) {

    foreach ($result["outputs"] as $output) {

        if (isset($output["text"])) {
            $reply .= $output["text"];
        }

        if (isset($output["content"]) && is_array($output["content"])) {

            foreach ($output["content"] as $content) {

                if (isset($content["text"])) {
                    $reply .= $content["text"];
                }
            }
        }
    }
}


/*
   Fallback for another possible response structure.
*/

if ($reply === "" && isset($result["output"])) {

    if (is_string($result["output"])) {
        $reply = $result["output"];
    }

    if (is_array($result["output"])) {

        foreach ($result["output"] as $output) {

            if (isset($output["text"])) {
                $reply .= $output["text"];
            }

            if (isset($output["content"]) && is_array($output["content"])) {

                foreach ($output["content"] as $content) {

                    if (isset($content["text"])) {
                        $reply .= $content["text"];
                    }
                }
            }
        }
    }
}


/*
   Final fallback.
*/

if ($reply === "" && isset($result["candidates"][0]["content"]["parts"])) {

    foreach ($result["candidates"][0]["content"]["parts"] as $part) {

        if (isset($part["text"])) {
            $reply .= $part["text"];
        }
    }
}


/* -------------------------------------------------
   FINAL RESPONSE
------------------------------------------------- */

if (trim($reply) === "") {

    echo json_encode([
        "success" => false,
        "message" => "Gemini returned an empty response."
    ]);

    exit;
}


echo json_encode([
    "success" => true,
    "reply" => trim($reply)
]);

exit;

?>