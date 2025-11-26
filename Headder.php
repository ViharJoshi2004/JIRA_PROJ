<?php
// header.php
session_start();

// Redirect to login if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>JIRA Project</title>
  <link rel="stylesheet" href="style.css">
  <style>
    /* ----- HEADER STYLES ----- */
    header {
      background: rgba(0, 0, 0, 0.6);
      color: white;
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 12px 30px;
      backdrop-filter: blur(10px);
      position: fixed;
      top: 0;
      width: 100%;
      z-index: 1000;
      font-family: Arial, sans-serif;
    }

    .logo {
      font-size: 22px;
      font-weight: bold;
      letter-spacing: 1px;
    }

    nav a {
      color: white;
      text-decoration: none;
      margin: 0 15px;
      font-size: 15px;
      transition: 0.3s;
    }

    nav a:hover {
      color: #00bfff;
    }

    .user-section {
      display: flex;
      align-items: center;
      gap: 15px;
    }

    .logout-btn {
      background: #00bfff;
      color: white;
      border: none;
      padding: 5px 12px;
      border-radius: 5px;
      cursor: pointer;
      transition: 0.3s;
    }

    .logout-btn:hover {
      background: #007acc;
    }

    body {
      margin-top: 70px; /* To prevent content hiding under header */
    }
  </style>
</head>
<body>

<header>
  <div class="logo">My JIRA Project</div>
  <nav>
    <a href="dashbord.php">Dashboard</a>
    <a href="project.php">Projects</a>
    <a href="task.php">Tasks</a>
    <a href="role.php">Roles</a>
    <a href="membership.php">Manage membership</a>
  </nav>
  <div class="user-section">
    <span>👤 <?php echo $_SESSION['username'] ?? 'User'; ?></span>
    <form action="logout.php" method="POST" style="display:inline;">
      <button class="logout-btn" type="submit">Logout</button>
    </form>
  </div>
</header>
