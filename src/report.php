<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

include 'pdo.php'; // DB connection
include 'User1.php';
$user = new User();

// Total counts
$stats = [
    'users' => $conn->query("SELECT COUNT(*) FROM users")->fetchColumn(),
    'projects' => $conn->query("SELECT COUNT(*) FROM project")->fetchColumn(),
    'tasks' => $conn->query("SELECT COUNT(*) FROM task")->fetchColumn(),
    'comments' => $conn->query("SELECT COUNT(*) FROM task_comments")->fetchColumn(),
];

// Task status counting
$statusSql = "SELECT status, COUNT(*) AS total FROM task GROUP BY status";
$statusStmt = $conn->query($statusSql);
$taskStatus = $statusStmt->fetchAll(PDO::FETCH_KEY_PAIR);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Project Report</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body { background: url("images/bg.jpg") no-repeat center fixed; background-size: cover; color: white; }
        .container {
            background: rgba(0, 0, 0, 0.6); backdrop-filter: blur(8px);
            width: 80%; margin: 30px auto; padding: 20px; border-radius: 10px;
        }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { padding: 8px; border: 1px solid #ddd; }
        th { background: rgba(0, 0, 0, 0.4); }
        a.btn { background: #007bff; padding: 7px 14px; border-radius: 6px; color: white; text-decoration: none; }
        a.btn:hover { opacity: 0.9; }
    </style>
</head>
<body>
<div class="container">
    <h1 align="center">📊 Project Report Summary</h1>
    <br>

    <a href="dashbord.php" class="btn">⬅ Back to Dashboard</a>
    <br><br>

    <h2>Total System Statistics</h2>
    <table>
        <tr><th>Users</th><td><?= $stats['users'] ?></td></tr>
        <tr><th>Projects</th><td><?= $stats['projects'] ?></td></tr>
        <tr><th>Total Tasks</th><td><?= $stats['tasks'] ?></td></tr>
        <tr><th>Total Comments</th><td><?= $stats['comments'] ?></td></tr>
    </table>

    <br>
    <h2>📌 Task Status Overview</h2>
    <table>
        <tr>
            <th>Backlog</th><th>To Do</th><th>In Progress</th>
            <th>In Review</th><th>Done</th><th>Cancelled</th>
        </tr>
        <tr>
            <td><?= $taskStatus['Backlog'] ?? 0 ?></td>
            <td><?= $taskStatus['To Do'] ?? 0 ?></td>
            <td><?= $taskStatus['In Progress'] ?? 0 ?></td>
            <td><?= $taskStatus['In Review'] ?? 0 ?></td>
            <td><?= $taskStatus['Done'] ?? 0 ?></td>
            <td><?= $taskStatus['Cancelled'] ?? 0 ?></td>
        </tr>
    </table>

    <br>
    <h3>📝 Submission Note</h3>
    <p>
        This report shows complete statistical overview of the project management system developed using
        PHP (PDO), MySQL, and web technologies. It includes user, project, task, and comments tracking.
        This file helps during project submission & viva explanation.
    </p>

</div>
</body>
</html>
