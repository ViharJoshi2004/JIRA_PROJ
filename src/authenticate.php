<?php
//authenticate.php
session_start();
require 'Config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = trim($_POST['user'] ?? '');
    $pass = $_POST['pass'] ?? '';

    if ($user === '' || $pass === '') {
        header("Location: login.php?error=1");
        exit;
    }

    $sql = "SELECT id, Username, email, psw, dp_name, phone FROM users WHERE Username = ? OR email = ? LIMIT 1";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        // prepare failed - redirect with generic error
        header("Location:login.php?error=1");
        exit;
    }
    // Bind same $user to both placeholders (search by username OR email)
    $stmt->bind_param("ss", $user, $user);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        if (password_verify($pass, $row['psw'])) {
            // success - set session
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['Username'] = $row['Username'];
            $_SESSION['email'] = $row['email'];
            $_SESSION['dp_name'] = $row['dp_name'];
            $_SESSION['phone'] = $row['phone'];
            header("Location:dashbord.php");
            exit;
        }
    }

    // generic error
    header("Location:login.php?error=1");
    exit;
}
header("Location:login.php");
exit;
