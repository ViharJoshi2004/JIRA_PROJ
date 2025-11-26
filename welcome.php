<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// 2 second me dashboard.php pe redirect
header("Refresh: 3; URL=dashbord.php");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Welcome</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .welcome-box {
            width: 450px;
            background: rgba(14, 2, 2, 0.15);
            backdrop-filter: blur(8px);
            padding: 30px;
            border-radius: 10px;
            text-align: center;
            color: Black;
            margin: 15% auto;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3);
            animation: fadeIn 1.5s ease-in-out;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>
    <div class="welcome-box">
        <h1>Welcome, <?php echo $_SESSION['username'] ?? 'User'; ?>!</h1>
        <p>Redirecting to dashboard...</p>
    </div>
</body>
</html>
