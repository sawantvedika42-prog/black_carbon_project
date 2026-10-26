<?php
session_start();

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>AI Assistant | Black Carbon Monitoring System</title>

    <style>

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
        }

        body {
            background: #f5f8f6;
            color: #222;
        }


        /* =========================
           NAVBAR
        ========================= */

        .navbar {

            height: 65px;
            width: 100%;

            background: #176b45;

            display: flex;
            align-items: center;
            justify-content: space-between;

            padding: 0 35px;

            color: white;
        }

        .logo {

            font-size: 21px;
            font-weight: bold;

            white-space: nowrap;
        }

        .nav-links {

            display: flex;
            align-items: center;

            gap: 25px;

            white-space: nowrap;
        }

        .nav-links a {

            color: white;

            text-decoration: none;

            font-size: 15px;

            cursor: pointer;
        }

        .nav-links a:hover {

            text-decoration: underline;
        }

        .nav-links a.active {

            font-weight: bold;
        }


        /* =========================
           MAIN
        ========================= */

        .container {

            width: 90%;

            max-width: 950px;

            margin: 35px auto;
        }


        /* =========================
           TITLE
        ========================= */

        .title {

            text-align: center;

            margin-bottom: 25px;
        }

        .title h1 {

            color: #176b45;

            font-size: 30px;

            margin-bottom: 8px;
        }

        .title p {

            color: #666;

            font-size: 15px;
        }


        /* =========================
           CHAT CONTAINER
        ========================= */

        .chat-container {

            background: white;

            border-radius: 15px;

            box-shadow:
                0 4px 20px rgba(0, 0, 0, 0.08);

            overflow: hidden;
        }


        /* =========================
           CHAT HEADER
        ========================= */

        .chat-header {

            background: #176b45;

            color: white;

            padding: 18px 22px;

            font-size: 18px;

            font-weight: bold;
        }

        .chat-header span {

            font-size: 13px;

            font-weight: normal;

            opacity: 0.9;

            display: block;

            margin-top: 4px;
        }


        /* =========================
           CHAT MESSAGES
        ========================= */

        #chatBox {

            height: 430px;

            overflow-y: auto;

            padding: 25px;

            background: #f9fbfa;
        }

        .message {

            max-width: 75%;

            padding: 14px 17px;

            margin-bottom: 15px;

            border-radius: 12px;

            line-height: 1.6;

            font-size: 15px;

            white-space: pre-wrap;

            word-wrap: break-word;
        }

        .bot {

            background: #e4f2eb;

            color: #222;

            border-top-left-radius: 3px;
        }

        .user {

            background: #176b45;

            color: white;

            margin-left: auto;

            border-top-right-radius: 3px;
        }

        .error {

            background: #ffe8e8;

            color: #a00000;

            border-left: 4px solid #e00000;
        }


        /* =========================
           QUICK QUESTIONS
        ========================= */

        .quick-section {

            padding: 18px 22px 5px;

            background: white;
        }

        .quick-title {

            font-size: 14px;

            color: #555;

            margin-bottom: 10px;
        }

        .quick-buttons {

            display: flex;

            flex-wrap: wrap;

            gap: 8px;
        }

        .quick-buttons button {

            border: 1px solid #176b45;

            background: white;

            color: #176b45;

            padding: 9px 13px;

            border-radius: 20px;

            cursor: pointer;

            font-size: 13px;
        }

        .quick-buttons button:hover {

            background: #176b45;

            color: white;
        }


        /* =========================
           INPUT
        ========================= */

        .input-area {

            display: flex;

            gap: 10px;

            padding: 20px 22px;

            background: white;

            border-top: 1px solid #eee;
        }

        #messageInput {

            flex: 1;

            padding: 14px 16px;

            border: 1px solid #ccc;

            border-radius: 25px;

            outline: none;

            font-size: 15px;
        }

        #messageInput:focus {

            border-color: #176b45;
        }

        #sendButton {

            background: #176b45;

            color: white;

            border: none;

            padding: 0 25px;

            border-radius: 25px;

            cursor: pointer;

            font-size: 15px;

            font-weight: bold;
        }

        #sendButton:hover {

            background: #0f5636;
        }

        #sendButton:disabled {

            background: #999;

            cursor: not-allowed;
        }


        /* =========================
           FOOTER
        ========================= */

        .footer {

            text-align: center;

            color: #777;

            font-size: 13px;

            margin-top: 25px;
        }


        /* =========================
           MOBILE
        ========================= */

        @media (max-width: 850px) {

            .navbar {

                height: auto;

                min-height: 65px;

                padding: 15px 20px;

                flex-direction: column;

                gap: 12px;
            }

            .nav-links {

                gap: 15px;

                flex-wrap: wrap;

                justify-content: center;
            }

            .nav-links a {

                font-size: 13px;
            }

        }


        @media (max-width: 700px) {

            .container {

                width: 95%;
            }

            #chatBox {

                height: 400px;
            }

            .message {

                max-width: 90%;
            }

            .input-area {

                padding: 15px;
            }

            #sendButton {

                padding: 0 18px;
            }

        }

    </style>

</head>


<body>


    <!-- =========================
         NAVBAR
    ========================= -->

    <div class="navbar">

        <div class="logo">

            🌱 Black Carbon Monitoring

        </div>


        <div class="nav-links">

            <a href="/black_carbon_project/index.php">
                Home
            </a>

            <a href="/black_carbon_project/calculator.php">
                Calculator
            </a>

            <a href="/black_carbon_project/insight.php">
                Insights
            </a>

            <a href="/black_carbon_project/indicator.php">
                AI Indicator
            </a>

            <a
                href="/black_carbon_project/chatbot.php"
                class="active"
            >
                AI Assistant
            </a>

        </div>

    </div>


    <!-- =========================
         MAIN CONTENT
    ========================= -->

    <div class="container">


        <!-- TITLE -->

        <div class="title">

            <h1>
                🤖 AI Assistant
            </h1>

            <p>
                Ask questions about black carbon,
                emissions and sustainable transportation.
            </p>

        </div>


        <!-- =========================
             CHAT
        ========================= -->

        <div class="chat-container">


            <!-- CHAT HEADER -->

            <div class="chat-header">

                🌱 AI Environmental Assistant

                <span>
                    Ask me anything about black carbon
                    and sustainable travel.
                </span>

            </div>


            <!-- =========================
                 CHAT MESSAGES
            ========================= -->

            <div id="chatBox">

                <div class="message bot">

                    👋 Hello! I am your AI Assistant.

                    I can help you understand:
                    • Black carbon
                    • Carbon emissions
                    • Eco-friendly transportation
                    • Public transport
                    • Sustainable travel
                    • Ways to reduce emissions

                    Ask me a question below! 😊

                </div>

            </div>


            <!-- =========================
                 QUICK QUESTIONS
            ========================= -->

            <div class="quick-section">

                <div class="quick-title">

                    Quick Questions

                </div>


                <div class="quick-buttons">


                    <button
                        onclick="askQuestion(
                            'What is black carbon?'
                        )"
                    >

                        What is black carbon?

                    </button>


                    <button
                        onclick="askQuestion(
                            'Which transport is most eco-friendly?'
                        )"
                    >

                        Eco-friendly transport

                    </button>


                    <button
                        onclick="askQuestion(
                            'How can I reduce my emissions?'
                        )"
                    >

                        Reduce emissions

                    </button>


                    <button
                        onclick="askQuestion(
                            'What is a carbon footprint?'
                        )"
                    >

                        Carbon footprint

                    </button>


                    <button
                        onclick="askQuestion(
                            'Why should I use public transport?'
                        )"
                    >

                        Public transport

                    </button>


                </div>

            </div>


            <!-- =========================
                 INPUT
            ========================= -->

            <div class="input-area">


                <input
                    type="text"
                    id="messageInput"
                    placeholder="Ask your question..."
                    autocomplete="off"
                >


                <button
                    id="sendButton"
                    onclick="sendMessage()"
                >

                    Send

                </button>


            </div>


        </div>


        <!-- FOOTER -->

        <div class="footer">

            AI Assistant • Black Carbon
            Emission Monitoring System

        </div>


    </div>


    <!-- =========================
         JAVASCRIPT
    ========================= -->

    <script>


        const chatBox =
            document.getElementById("chatBox");

        const messageInput =
            document.getElementById("messageInput");

        const sendButton =
            document.getElementById("sendButton");


        /* =========================
           ADD MESSAGE
        ========================= */

        function addMessage(message, type) {

            const div =
                document.createElement("div");

            div.className =
                "message " + type;

            div.textContent =
                message;

            chatBox.appendChild(div);

            chatBox.scrollTop =
                chatBox.scrollHeight;
        }


        /* =========================
           QUICK QUESTION
        ========================= */

        function askQuestion(question) {

            messageInput.value =
                question;

            sendMessage();
        }


        /* =========================
           SEND MESSAGE
        ========================= */

        async function sendMessage() {


            const message =
                messageInput.value.trim();


            if (message === "") {

                return;
            }


            /* Show user message */

            addMessage(
                message,
                "user"
            );


            /* Clear input */

            messageInput.value = "";


            /* Disable button */

            sendButton.disabled =
                true;

            sendButton.textContent =
                "Thinking...";


            /* Loading message */

            const loading =
                document.createElement("div");

            loading.className =
                "message bot";

            loading.textContent =
                "🤖 Thinking...";

            loading.id =
                "loadingMessage";

            chatBox.appendChild(
                loading
            );

            chatBox.scrollTop =
                chatBox.scrollHeight;


            try {


                /* =========================
                   CALL AI PHP
                ========================= */

                const response =
                    await fetch(
                        "/black_carbon_project/ai_chat.php",
                        {
                            method: "POST",

                            headers: {
                                "Content-Type":
                                    "application/json"
                            },

                            body:
                                JSON.stringify({
                                    message:
                                        message
                                })
                        }
                    );


                /* Get response as text */

                const responseText =
                    await response.text();


                /* Remove loading */

                const loadingMessage =
                    document.getElementById(
                        "loadingMessage"
                    );

                if (loadingMessage) {

                    loadingMessage.remove();

                }


                /* =========================
                   HTTP ERROR
                ========================= */

                if (!response.ok) {

                    addMessage(

                        "❌ Server error (" +
                        response.status +
                        "). Please try again.",

                        "error"

                    );

                    console.log(
                        "Server response:",
                        responseText
                    );

                    return;
                }


                /* =========================
                   JSON PARSE
                ========================= */

                let data;


                try {

                    data =
                        JSON.parse(
                            responseText
                        );

                }

                catch (jsonError) {

                    addMessage(

                        "❌ The server returned invalid JSON.\n\n" +
                        responseText,

                        "error"

                    );

                    console.log(
                        "Invalid JSON received:",
                        responseText
                    );

                    return;
                }


                /* =========================
                   AI RESPONSE
                ========================= */

                if (
                    data.success === true
                ) {

                    addMessage(

                        data.reply,

                        "bot"

                    );

                }

                else {

                    addMessage(

                        "❌ " +
                        (
                            data.message ||
                            "The AI could not generate a response."
                        ),

                        "error"

                    );

                }


            }

            catch (error) {


                /* Remove loading */

                const loadingMessage =
                    document.getElementById(
                        "loadingMessage"
                    );

                if (loadingMessage) {

                    loadingMessage.remove();

                }


                addMessage(

                    "❌ Connection error.\n\n" +
                    "Please make sure XAMPP Apache is running.",

                    "error"

                );


                console.error(
                    "Fetch error:",
                    error
                );

            }


            finally {


                sendButton.disabled =
                    false;

                sendButton.textContent =
                    "Send";

                messageInput.focus();

            }

        }


        /* =========================
           ENTER KEY
        ========================= */

        messageInput.addEventListener(

            "keydown",

            function(event) {

                if (
                    event.key === "Enter"
                ) {

                    event.preventDefault();

                    sendMessage();

                }

            }

        );

    </script>


</body>

</html>