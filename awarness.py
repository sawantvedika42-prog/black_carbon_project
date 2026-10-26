<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Awareness - Black Carbon</title>

    <style>

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #faf9f6;
            color: #24372f;
        }

        /* NAVBAR */

        nav {
            height: 85px;
            background: white;
            border-bottom: 1px solid #ddd;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 9%;
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
            font-size: 22px;
        }

        nav a {
            text-decoration: none;
            color: #61756d;
            margin-left: 30px;
            font-size: 15px;
        }

        nav a:hover {
            color: #195c3a;
        }

        nav .active {
            color: #195c3a;
            font-weight: bold;
        }

        /* HERO */

        .hero {
            text-align: center;
            padding: 75px 20px 70px;
            border-bottom: 1px solid #ddd;
        }

        .hero h1 {
            font-family: Georgia, serif;
            font-size: 56px;
            margin: 0 0 25px;
            color: #24372f;
        }

        .hero p {
            max-width: 700px;
            margin: auto;
            font-size: 20px;
            line-height: 1.7;
            color: #70857c;
        }

        /* CARDS */

        .cards-section {
            max-width: 1000px;
            margin: 70px auto;
            padding: 0 20px;
        }

        .cards {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
        }

        .card {
            background: white;
            min-height: 260px;
            padding: 32px;
            border: 1px solid #d9dfdb;
            border-radius: 24px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.08);
        }

        .icon {
            width: 50px;
            height: 50px;
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 25px;
            background: #edf2ed;
            margin-bottom: 25px;
        }

        .card h2 {
            font-family: Georgia, serif;
            font-size: 27px;
            margin: 0 0 20px;
            color: #24372f;
        }

        .card p {
            color: #71847c;
            font-size: 16px;
            line-height: 1.7;
            margin: 0;
        }

        /* DIFFERENT ICON COLORS */

        .orange {
            background: #fff2