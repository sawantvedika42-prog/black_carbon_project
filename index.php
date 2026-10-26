
<?php
session_start();

$isLoggedIn = !empty($_SESSION["loggedin"]);
$username = $_SESSION["username"] ?? "";
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Black Carbon | Emission Monitor</title>

<style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: Arial, sans-serif;
    background: #f7f5ef;
    color: #20352c;
}

/* NAVBAR */
nav {
    min-height: 80px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 20px;
    padding: 15px 8%;
    background: #fff;
    border-bottom: 1px solid #ddd;
}

.logo {
    display: flex;
    align-items: center;
    gap: 12px;
    font-size: 24px;
    font-weight: bold;
    white-space: nowrap;
}

.logo-circle {
    width: 42px;
    height: 42px;
    background: #1e5b45;
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 22px;
}

nav ul {
    display: flex;
    list-style: none;
    gap: 25px;
    align-items: center;
    flex-wrap: wrap;
}

nav a {
    text-decoration: none;
    color: #49665b;
    font-size: 15px;
}

nav a:hover {
    color: #1e5b45;
}

.nav-user {
    color: #1e5b45;
    font-weight: bold;
    font-size: 14px;
}

/* HERO */
.hero {
    min-height: 570px;
    padding: 100px 9%;
    background: linear-gradient(120deg, #f7f5ef, #edf2ed);
}

.tag {
    display: inline-block;
    padding: 10px 18px;
    border: 1px solid #d6ddd7;
    border-radius: 25px;
    margin-bottom: 35px;
    color: #315e4c;
    background: white;
}

.hero h1 {
    font-family: Georgia, serif;
    font-size: clamp(42px, 6vw, 70px);
    line-height: 1.1;
    max-width: 850px;
    font-weight: normal;
}

.hero h1 span {
    color: #176044;
}

.hero p {
    margin-top: 30px;
    font-size: 19px;
    line-height: 1.7;
    color: #6c8178;
    max-width: 700px;
}

.buttons {
    margin-top: 35px;
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
}

.btn {
    display: inline-block;
    padding: 15px 25px;
    border-radius: 8px;
    text-decoration: none;
    font-weight: bold;
    border: 1px solid #1e5b45;
    transition: 0.2s;
}

.primary {
    background: #1e5b45;
    color: white;
}

.secondary {
    color: #1e5b45;
    background: transparent;
}

.primary:hover {
    background: #164936;
}

.secondary:hover {
    background: #1e5b45;
    color: white;
}

/* GENERAL SECTIONS */
.about {
    padding: 90px 9%;
    background: white;
}

.about-container {
    display: grid;
    grid-template-columns: 1.2fr 1fr;
    gap: 50px;
    align-items: center;
}

.about h2,
.features h2 {
    font-family: Georgia, serif;
    font-size: clamp(32px, 4vw, 48px);
    font-weight: normal;
    margin-bottom: 25px;
}

.about p {
    color: #6d8178;
    line-height: 1.8;
    font-size: 17px;
    margin-bottom: 20px;
}

/* STATS */
.stats {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 18px;
}

.card {
    padding: 28px;
    border: 1px solid #d9e0db;
    border-radius: 18px;
    background: #fafcf9;
}

.card h3 {
    font-family: Georgia, serif;
    font-size: 32px;
    margin: 18px 0 10px;
}

.card p {
    font-size: 15px;
}

/* FEATURES */
.features {
    padding: 90px 9%;
    text-align: center;
}

.features h2 {
    margin-bottom: 45px;
}

.feature-container {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 22px;
}

.feature {
    background: white;
    padding: 30px;
    border-radius: 18px;
    text-align: left;
    border: 1px solid #e0e4e1;
}

.feature h3 {
    margin: 15px 0;
    font-size: 21px;
}

.feature p {
    color: #71847c;
    line-height: 1.7;
}

.feature-link {
    display: inline-block;
    margin-top: 18px;
    color: #1e5b45;
    font-weight: bold;
    text-decoration: none;
}

/* FOOTER */
footer {
    background: #18392e;
    color: white;
    padding: 35px 9%;
    text-align: center;
}

footer p {
    color: #bdd0c8;
    margin-top: 10px;
}

/* MOBILE */
@media (max-width: 900px) {
    nav {
        align-items: flex-start;
        flex-direction: column;
    }

    nav ul {
        gap: 15px;
    }

    .about-container,
    .feature-container {
        grid-template-columns: 1fr;
    }

    .stats {
        grid-template-columns: 1fr 1fr;
    }

    .hero {
        padding: 70px 7%;
    }

    .about,
    .features {
        padding: 65px 7%;
    }
}

@media (max-width: 480px) {
    .stats {
        grid-template-columns: 1fr;
    }

    nav ul {
        gap: 12px;
    }

    .logo {
        font-size: 21px;
    }

    .btn {
        padding: 13px 18px;
    }
}
</style>
</head>

<body>

<!-- NAVBAR -->
<nav>
    <div class="logo">
        <div class="logo-circle">♧</div>
        Black Carbon
    </div>

    <ul>
        <li><a href="/black_carbon_project/index.php">Home</a></li>
        <li><a href="/black_carbon_project/calculator.php">Calculator</a></li>
        <li><a href="/black_carbon_project/insight.php">Insights</a></li>
        <li><a href="/black_carbon_project/chatbot.php">AI Assistant</a></li>
        <li><a href="/black_carbon_project/indicator.php">Indicator</a></li>

        <?php if ($isLoggedIn): ?>
            <li class="nav-user">
                Hi, <?= htmlspecialchars($username, ENT_QUOTES, 'UTF-8') ?>
            </li>
            <li>
                <a href="/black_carbon_project/logout.php">Logout</a>
            </li>
        <?php endif; ?>
    </ul>
</nav>

<!-- HERO -->
<section class="hero">
    <div class="tag">🌿 Make the invisible visible</div>

    <h1>
        Every emission leaves a mark.
        <br>
        <span>Measure yours.</span>
    </h1>

    <p>
        Black Carbon helps you understand the environmental
        impact of transportation and fuel consumption.
        Simple, grounded data for a clearer conscience.
    </p>

    <div class="buttons">
        <a href="/black_carbon_project/calculator.php"
           class="btn primary">
            Calculate Emission
        </a>

        <?php if (!$isLoggedIn): ?>
            <a href="/black_carbon_project/login.php"
               class="btn secondary">
                Login
            </a>

            <a href="/black_carbon_project/register.php"
               class="btn secondary">
                Register
            </a>
        <?php endif; ?>
    </div>
</section>

<!-- ABOUT -->
<section class="about" id="about">
    <div class="about-container">
        <div>
            <h2>What is Black Carbon?</h2>

            <p>
                Black carbon is a sooty black material produced by
                incomplete combustion of fuels. It can come from
                vehicles, diesel engines, biomass burning, industries
                and other combustion sources.
            </p>

            <p>
                Unlike carbon dioxide, black carbon is a short-lived
                climate pollutant. Reducing black carbon emissions can
                help improve air quality and reduce climate impacts.
            </p>
        </div>

        <div class="stats">
            <div class="card">
                <div>🚗</div>
                <h3>Transport</h3>
                <p>
                    Vehicle emissions are one source of black carbon.
                    Cleaner transport can help reduce pollution.
                </p>
            </div>

            <div class="card">
                <div>📊</div>
                <h3>Short-lived</h3>
                <p>
                    Black carbon remains in the atmosphere for a
                    much shorter time than carbon dioxide.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- FEATURES -->
<section class="features" id="features">
    <h2>Understand Your Impact</h2>

    <div class="feature-container">
        <div class="feature">
            <div>🧮</div>
            <h3>Emission Calculator</h3>
            <p>
                Enter your fuel usage and calculate estimated emissions.
            </p>
            <a class="feature-link"
               href="/black_carbon_project/calculator.php">
                Calculate now →
            </a>
        </div>

        <div class="feature">
            <div>📈</div>
            <h3>Emission Insights</h3>
            <p>
                View stored emission records and understand
                your environmental impact.
            </p>
            <a class="feature-link"
               href="/black_carbon_project/insight.php">
                View insights →
            </a>
        </div>

        <div class="feature">
            <div>🤖</div>
            <h3>AI Assistant</h3>
            <p>
                Explore assistance and guidance related to
                your emission-monitoring project.
            </p>
            <a class="feature-link"
               href="/black_carbon_project/chatbot.php">
                Open assistant →
            </a>
        </div>
    </div>
</section>

<!-- AWARENESS -->
<section class="about" id="awareness">
    <div class="about-container">
        <div>
            <h2>Small Changes Matter</h2>

            <p>
                Using public transportation, maintaining vehicles
                properly, reducing unnecessary fuel consumption and
                choosing cleaner energy sources can help reduce emissions.
            </p>

            <a href="/black_carbon_project/calculator.php"
               class="btn primary">
                Start Measuring
            </a>
        </div>

        <div class="card">
            <h3>🌍 Our Goal</h3>
            <p>
                Make emission data simple, understandable and useful
                for students and communities.
            </p>
        </div>
    </div>
</section>

<!-- FOOTER -->
<footer>
    <h3>Black Carbon</h3>
    <p>BSCCS Project | Black Carbon Emission Monitoring System</p>
</footer>

</body>
</html>
