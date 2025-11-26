<?php
session_start();
require 'Config.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/vendor/autoload.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);

    if (!empty($email)) {
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows === 1) {
            $stmt->bind_result($user_id);
            $stmt->fetch();
            $stmt->close();

            $otp = random_int(100000, 999999);
            $expires = date('Y-m-d H:i:s', time() + (15 * 60));

            $upd = $conn->prepare("UPDATE users SET reset_otp = ?, reset_expires = ? WHERE id = ?");
            $upd->bind_param("ssi", $otp, $expires, $user_id);
            $upd->execute();
            $upd->close();

            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = 'vihar7336@gmail.com';
                $mail->Password   = 'iundnbapzefnxxpu';
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = 587;

                $mail->setFrom('vihar7336@gmail.com', 'Vihar Joshi');
                $mail->addAddress($email);

                $mail->isHTML(true);
                $mail->Subject = 'Your Password Reset OTP';
                $mail->Body = "
<div style='font-family: Arial, sans-serif; padding: 20px;'>
    <h2 style='color: #4CAF50;'>Password Reset Request</h2>
    <p>Hello <strong>User</strong>,</p>
    <p>Please use the OTP below to reset your password:</p>
    <h1 style='background: #f2f2f2; padding: 10px; border-radius: 6px; text-align: center; display: inline-block; letter-spacing: 5px;'>
        <strong>{$otp}</strong>
    </h1>
    <p>OTP valid for <strong>15 minutes</strong>.</p>
</div>
";

                $mail->send();
                $_SESSION['forgot_email'] = $email;
                header("Location: verify_otp.php");
                exit;
            } catch (Exception $e) {
                $message = "Email sending error: " . $mail->ErrorInfo;
            }
        } else {
            $message = "If this email exists, OTP will be sent.";
        }
    } else {
        $message = "Enter email.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Forgot Password</title>

    <style>
        body {
            font-family: "Poppins", Arial, sans-serif;
            background: url("images/bg.jpg") no-repeat center center fixed;
            background-size: cover;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 0;
            position: relative;
        }
        body::before {
            content: "";
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.45);
            z-index: -1;
        }
        .forgot-container {
            max-width: 430px;
            width: 100%;
            padding: 20px;
        }
        .forgot-box {
            background: rgba(255, 255, 255, 0.12);
            padding: 26px;
            border-radius: 16px;
            box-shadow: 0 18px 45px rgba(0, 0, 0, 0.35);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.24);
        }
        .forgot-title {
            font-size: 24px;
            font-weight: 600;
            color: #fff;
            margin-bottom: 5px;
            text-align: center;
        }
        .forgot-subtitle {
            font-size: 13px;
            color: #f3f3f3;
            text-align: center;
            margin-bottom: 18px;
        }
        .alert {
            background: rgba(0, 0, 0, 0.35);
            padding: 8px;
            border-radius: 6px;
            color: #fbe9e7;
            font-size: 13px;
            margin-bottom: 14px;
            border: 1px solid rgba(255, 255, 255, 0.35);
        }
        .form-control {
            width: 100%;
            padding: 10px;
            border-radius: 8px;
            border: 1px solid rgba(255, 255, 255, 0.35);
            background: rgba(0, 0, 0, 0.25);
            color: white;
            font-size: 14px;
            margin-bottom: 14px;
            outline: none;
        }
        .form-control:focus {
            border-color: #42a5f5;
            box-shadow: 0 0 0 1px rgba(66, 165, 245, 0.5);
        }
        .btn {
            width: 100%;
            padding: 10px;
            background: #1e88e5;
            border-radius: 8px;
            border: none;
            color: white;
            cursor: pointer;
            font-size: 15px;
            transition: 0.2s;
        }
        .btn:hover {
            background: #1565c0;
            transform: translateY(-2px);
        }
        .forgot-footer {
            font-size: 12px;
            text-align: center;
            margin-top: 12px;
        }
        .forgot-footer a {
            color: #90caf9;
            text-decoration: none;
        }
        .forgot-footer a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

    <div class="forgot-container">
        <div class="forgot-box">
            <h2 class="forgot-title">Forgot Password</h2>
            <p class="forgot-subtitle">Enter your registered email address</p>

            <?php if (!empty($message)): ?>
                <div class="alert"><?= $message ?></div>
            <?php endif; ?>

            <form method="post">
                <input type="email" name="email" class="form-control" placeholder="Enter email" required>
                <button type="submit" class="btn">Send OTP</button>
            </form>

            <div class="forgot-footer">
                <a href="login.php">← Back to Login</a>
            </div>
        </div>
    </div>

</body>
</html>
