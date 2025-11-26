<?php
// dashboard.php
session_start();
if (!isset($_SESSION['user_id']))
   {
    header("Location: login.php");
    exit;
}
$dp = htmlspecialchars($_SESSION['dp_name'] ?? $_SESSION['Username'] ?? 'User', ENT_QUOTES, 'UTF-8');
$email = htmlspecialchars($_SESSION['email'] ?? '', ENT_QUOTES, 'UTF-8');
$phone = htmlspecialchars($_SESSION['phone'] ?? '', ENT_QUOTES, 'UTF-8');
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Dashboard</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <div class="form-container">
    <h1>Welcome To Jira Project</h1>
    <br>
    <h3><strong>Username:</strong> <?= $dp ?></h3>
    <h3><strong>Email:</strong> <?= $email ?></h3>
    <h3><strong>Phone:</strong> <?= $phone ?></h3>
    <br>
    <h3>Here you can create tasks and projects.</h3>
    <h2>What would you like to do?</h2>
    <h3 align="center"><a href="task.php">Create Task</a></h3>
    <br>
    <h3 align="center"><a href="project.php">Create Project</a></h3>
    <br>
    <h3><a href="logout.php">Logout</a></h3>
  </div>
</body>
</html>
