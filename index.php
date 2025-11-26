<?php

header("Refresh: 5; URL=login.php");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JIRA Project - Welcome</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .welcome-box {
            width: 450px;
            background: rgba(19, 17, 17, 0.15);
            backdrop-filter: blur(8px);
            padding: 30px;
            border-radius: 10px;
            text-align: center;
            color: Black;
            margin: 15% auto;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3);
            animation: fadeIn 2.5s ease-in-out;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        h1 { font-size: 24px; font-weight: bold; }
        p { font-size: 16px; margin-top: 5px; }
    </style>
</head>
<body>
    <div class="welcome-box">
        <h1>Welcome to JIRA Project Management System</h1>
        <p>Loading, please wait...</p>
    </div>
</body>
</html>
