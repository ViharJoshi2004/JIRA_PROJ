<?php
session_start();
require 'Config.php';

if (
    !isset($_SESSION['forgot_email']) ||
    !isset($_SESSION['reset_allowed']) ||
    $_SESSION['reset_allowed'] !== true
) {
    header("Location: forgot_password.php");
    exit;
}

$email = $_SESSION['forgot_email'];
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';

    if (empty($password) || empty($confirm)) {
        $message = "Please fill all fields.";
    } elseif ($password !== $confirm) {
        $message = "Passwords do not match.";
    } elseif (strlen($password) < 6) {
        $message = "Password must be at least 6 characters.";
    } else {
        $hash = password_hash($password, PASSWORD_BCRYPT);

        $stmt = $conn->prepare("
            UPDATE users 
            SET psw = ?, reset_otp = NULL, reset_expires = NULL 
            WHERE email = ?
        ");
        $stmt->bind_param("ss", $hash, $email);

        if ($stmt->execute()) {
            unset($_SESSION['forgot_email'], $_SESSION['reset_allowed']);
            $message = "Password reset successful. <a href='login.php'>Login now</a>.";
        } else {
            $message = "Something went wrong. Try again.";
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reset Password</title>

    <!-- CSS directly inside the file -->
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        body {
            font-family: "Poppins", Arial, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: url("images/bg.jpg") no-repeat center center fixed;
            background-size: cover;
            position: relative;
        }
        body::before {
            content: "";
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.45);
            z-index: -1;
        }
        .reset-wrapper {
            width: 100%;
            max-width: 430px;
            padding: 20px;
        }
        .reset-card {
            background: rgba(255, 255, 255, 0.12);
            border-radius: 16px;
            padding: 26px 24px 22px;
            box-shadow: 0 18px 45px rgba(0, 0, 0, 0.35);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.24);
        }
        .reset-title {
            font-size: 24px;
            font-weight: 600;
            color: #ffffff;
            margin-bottom: 4px;
            text-align: center;
        }
        .reset-subtitle {
            font-size: 13px;
            color: #f3f3f3;
            text-align: center;
            margin-bottom: 18px;
        }
        .alert {
            padding: 9px 10px;
            border-radius: 8px;
            font-size: 13px;
            margin-bottom: 14px;
            background: rgba(0, 0, 0, 0.35);
            border: 1px solid rgba(255, 255, 255, 0.35);
            color: #fbe9e7;
        }
        .form-group {
            margin-bottom: 14px;
        }
        .form-label {
            display: block;
            font-size: 13px;
            margin-bottom: 4px;
            color: #f5f5f5;
        }
        .form-control {
            width: 100%;
            padding: 9px 10px;
            font-size: 14px;
            border-radius: 8px;
            border: 1px solid rgba(255, 255, 255, 0.35);
            background: rgba(0, 0, 0, 0.25);
            color: #ffffff;
            outline: none;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }
        .form-control:focus {
            border-color: #42a5f5;
            box-shadow: 0 0 0 1px rgba(66, 165, 245, 0.5);
        }
        .btn {
            width: 100%;
            padding: 9px 12px;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
            transition: transform 0.15s ease, box-shadow 0.15s ease;
            background: #1e88e5;
            color: #fff;
            box-shadow: 0 8px 18px rgba(30, 136, 229, 0.35);
        }
        .btn:hover {
            background: #1565c0;
            transform: translateY(-1px);
            box-shadow: 0 10px 22px rgba(21, 101, 192, 0.45);
        }
        .reset-footer {
            margin-top: 16px;
            text-align: center;
            font-size: 12px;
        }
        .reset-footer a {
            color: #90caf9;
            text-decoration: none;
        }
        .reset-footer a:hover {
            text-decoration: underline;
        }
    </style>
</head>

<body>
    <div class="reset-wrapper">
        <div class="reset-card">
            <h2 class="reset-title">Reset Password</h2>
            <p class="reset-subtitle">
                Enter a new password for <strong><?php echo htmlspecialchars($email); ?></strong>
            </p>

            <?php if (!empty($message)): ?>
                <div class="alert"><?php echo $message; ?></div>
            <?php endif; ?>

            <form method="post">
                <div class="form-group">
                    <label class="form-label">New Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Confirm Password</label>
                    <input type="password" name="confirm_password" class="form-control" required>
                </div>

                <button type="submit" class="btn">Reset Password</button>
            </form>

            <div class="reset-footer">
                <a href="login.php">← Back to Login</a>
            </div>
        </div>
    </div>
</body>
</html>
