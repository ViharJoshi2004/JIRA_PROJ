<?php
// authenticate.php
session_start();
require 'Config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = trim($_POST['user'] ?? '');
    $pass = $_POST['pass'] ?? '';

    // Empty fields check
    if ($user === '' || $pass === '') {
        header("Location: login.php?error=1");
        exit;
    }

    $sql = "SELECT id, Username, email, psw, dp_name, phone 
            FROM users 
            WHERE Username = ? OR email = ? 
            LIMIT 1";
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        // prepare failed
        header("Location: login.php?error=1");
        exit;
    }

    // Bind same $user to both placeholders
    $stmt->bind_param("ss", $user, $user);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        // Password verify
        if (password_verify($pass, $row['psw'])) {
            // ✅ Login successful – set session
            $_SESSION['user_id']   = $row['id'];
            $_SESSION['username']  = $row['Username'];  // small 'username'
            $_SESSION['email']     = $row['email'];
            $_SESSION['dp_name']   = $row['dp_name'];
            $_SESSION['phone']     = $row['phone'];

            // Pehle welcome page, fir dashboard
            header("Location: welcome.php");
            exit;
        }
    }

    // ❌ Invalid login
    header("Location: login.php?error=1");
    exit;
}

// Direct access (GET)
header("Location: login.php");
exit;
