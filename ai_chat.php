<?php

header("Content-Type: application/json; charset=UTF-8");

require_once "config.php";


/* =====================================================
   ONLY POST REQUESTS
===================================================== */

if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    echo json_encode([
        "success" => false,
        "message" => "Only POST requests are allowed."
    ]);

    exit;
}


/* =====================================================
   READ USER MESSAGE
===================================================== */

$rawInput = file_get_contents("php://input");

$input = json_decode(
    $rawInput,
    true
);


if (!is_array($input)) {

    echo json_encode([
        "success" => false,
        "message" => "Invalid request."
    ]);

    exit;
}


$message = trim(
    $input["message"] ?? ""
);


if ($message === "") {

    echo json_encode([
        "success" => false,
        "message" => "Please enter a question."
    ]);

    exit;
}


/* =====================================================
   CHECK GEMINI KEY
===================================================== */

if (
    !isset($GEMINI_API_KEY)
    ||
    trim($GEMINI_API_KEY) === ""
) {

    echo json_encode([
        "success" => false,
        "message" => "Gemini API key is missing in config.php."
    ]);

    exit;
}


/* =====================================================
   GEMINI MODEL
===================================================== */

$model =
    $GEMINI_MODEL
    ??
    "gemini-3.5-flash";


/* =====================================================
   AI INSTRUCTIONS
===================================================== */

$prompt = "

You are the official AI Assistant for a Black Carbon Emission Monitoring System.

Your job is to help users understand:

1. Black carbon
2. Transportation emissions
3. Carbon footprint
4. Eco-friendly transportation
5. Sustainable travel
6. Ways to reduce emissions
7. Air pollution
8. Environmental impact of vehicles

Answer in simple student-friendly language.

You can discuss:

- Walking
- Bicycle
- Local train
- Metro
- Bus
- Auto-rickshaw
- Scooter
- Car
- Carpooling

When comparing transportation options, consider:

- Environmental impact
- Black carbon emissions
- Carbon emissions
- Cost
- Number of passengers
- Comfort
- Distance
- Traffic

Give practical suggestions.

Do not claim that you have access to live traffic, live weather, GPS,
or real-time information unless such information is explicitly provided
in the user's question.

Do not make up statistics.

Keep answers clear and reasonably short.

User question:

" . $message;


/* =====================================================
   GEMINI INTERACTIONS API
===================================================== */

$url =
    "https://generativelanguage.googleapis.com/v1beta/interactions";


$requestData = [

    "model" =>
        $model,

    "input" =>
        $prompt

];


$jsonRequest =
    json_encode(
        $requestData
    );


/* =====================================================
   CURL REQUEST
===================================================== */

$ch =
    curl_init(
        $url
    );


curl_setopt(
    $ch,
    CURLOPT_POST,
    true
);


curl_setopt(
    $ch,
    CURLOPT_POSTFIELDS,
    $jsonRequest
);


curl_setopt(
    $ch,
    CURLOPT_RETURNTRANSFER,
    true
);


curl_setopt(
    $ch,
    CURLOPT_HTTPHEADER,
    [

        "Content-Type: application/json",

        "x-goog-api-key: "
        .
        $GEMINI_API_KEY

    ]
);


curl_setopt(
    $ch,
    CURLOPT_TIMEOUT,
    60
);


$response =
    curl_exec(
        $ch
    );


$curlError =
    curl_error(
        $ch
    );


$httpCode =
    curl_getinfo(
        $ch,
        CURLINFO_HTTP_CODE
    );


curl_close(
    $ch
);


/* =====================================================
   CURL ERROR
===================================================== */

if (
    $response === false
) {

    echo json_encode([

        "success" => false,

        "message" =>
            "Could not connect to Gemini: "
            .
            $curlError

    ]);

    exit;
}


/* =====================================================
   HTTP ERROR
===================================================== */

if (
    $httpCode < 200
    ||
    $httpCode >= 300
) {

    $errorData =
        json_decode(
            $response,
            true
        );


    $errorMessage =
        $errorData["error"]["message"]
        ??
        "Gemini API error. HTTP status: "
        .
        $httpCode;


    echo json_encode([

        "success" => false,

        "message" =>
            $errorMessage

    ]);

    exit;
}


/* =====================================================
   DECODE GEMINI RESPONSE
===================================================== */

$result =
    json_decode(
        $response,
        true
    );


if (
    !is_array($result)
) {

    echo json_encode([

        "success" => false,

        "message" =>
            "Gemini returned invalid JSON."

    ]);

    exit;
}


/* =====================================================
   EXTRACT AI TEXT
===================================================== */

$reply = "";


/*
=========================================================
Gemini Interactions API response:

steps
   ↓
model_output
   ↓
content
   ↓
text
=========================================================
*/

if (
    isset(
        $result["steps"]
    )
    &&
    is_array(
        $result["steps"]
    )
) {

    foreach (
        $result["steps"]
        as $step
    ) {

        if (
            isset(
                $step["type"]
            )
            &&
            $step["type"] ===
            "model_output"
        ) {

            if (
                isset(
                    $step["content"]
                )
                &&
                is_array(
                    $step["content"]
                )
            ) {

                foreach (
                    $step["content"]
                    as $content
                ) {

                    if (
                        isset(
                            $content["type"]
                        )
                        &&
                        $content["type"] ===
                        "text"
                        &&
                        isset(
                            $content["text"]
                        )
                    ) {

                        $reply .=
                            $content["text"];

                    }

                }

            }

        }

    }

}


/* =====================================================
   CLEAN RESPONSE
===================================================== */

$reply =
    trim(
        $reply
    );


/* =====================================================
   EMPTY RESPONSE
===================================================== */

if (
    $reply === ""
) {

    echo json_encode([

        "success" => false,

        "message" =>
            "Gemini returned an empty response."

    ]);

    exit;
}


/* =====================================================
   SUCCESS
===================================================== */

echo json_encode([

    "success" => true,

    "reply" =>
        $reply

]);

exit;

?>