<?php
session_start();
require 'Config.php';

if (!isset($_SESSION['forgot_email'])) {
    header("Location: forgot_password.php");
    exit;
}

$email = $_SESSION['forgot_email'];
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $otp = trim($_POST['otp'] ?? '');

    if (!empty($otp)) {

        $stmt = $conn->prepare("
            SELECT id, reset_otp, reset_expires 
            FROM users 
            WHERE email = ?
            LIMIT 1
        ");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->bind_result($user_id, $db_otp, $db_expires);
        $rowExists = $stmt->fetch();
        $stmt->close();

        if ($rowExists) {
            if (empty($db_otp)) {
                $message = "OTP not generated. Please try again.";
            } elseif ($otp !== $db_otp) {
                $message = "Invalid OTP.";
            } else {
                $now = time();
                $expTime = strtotime($db_expires);

                if ($expTime !== false && $expTime < $now) {
                    $message = "OTP expired. Please request a new one.";
                } else {
                    $_SESSION['reset_allowed'] = true;
                    header("Location: reset_password.php");
                    exit;
                }
            }
        } else {
            $message = "User not found. Try again.";
        }
    } else {
        $message = "Please enter OTP.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Verify OTP</title>

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

        .otp-wrapper {
            width: 100%;
            max-width: 430px;
            padding: 20px;
        }

        .otp-card {
            background: rgba(255, 255, 255, 0.12);
            border-radius: 16px;
            padding: 26px 24px 22px;
            box-shadow: 0 18px 45px rgba(0, 0, 0, 0.35);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.24);
        }

        .otp-title {
            font-size: 24px;
            font-weight: 600;
            color: #ffffff;
            margin-bottom: 4px;
            text-align: center;
        }

        .otp-subtitle {
            font-size: 13px;
            color: #f3f3f3;
            text-align: center;
            margin-bottom: 18px;
        }

        .otp-subtitle strong {
            font-weight: 600;
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

        .form-control::placeholder {
            color: #cfd8dc;
            font-size: 13px;
        }

        .form-control:focus {
            border-color: #42a5f5;
            box-shadow: 0 0 0 1px rgba(66, 165, 245, 0.5);
            background: rgba(0, 0, 0, 0.4);
        }

        .btn {
            width: 100%;
            padding: 9px 12px;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
            transition: transform 0.15s ease, box-shadow 0.15s ease, background 0.15s ease;
            background: #1e88e5;
            color: #fff;
            box-shadow: 0 8px 18px rgba(30, 136, 229, 0.35);
            margin-top: 4px;
        }

        .btn:hover {
            background: #1565c0;
            transform: translateY(-1px);
            box-shadow: 0 10px 22px rgba(21, 101, 192, 0.45);
        }

        .otp-footer {
            margin-top: 16px;
            text-align: center;
            font-size: 12px;
            color: #eceff1;
        }

        .otp-footer a {
            color: #90caf9;
            text-decoration: none;
        }

        .otp-footer a:hover {
            text-decoration: underline;
        }

        .note-text {
            font-size: 11px;
            color: #e0f2f1;
            margin-top: 6px;
            text-align: center;
        }

        @media (max-width: 480px) {
            .otp-card {
                padding: 22px 18px 18px;
            }
            .otp-title {
                font-size: 20px;
            }
            .otp-wrapper {
                padding: 14px;
            }
        }
    </style>
</head>
<body>
    <div class="otp-wrapper">
        <div class="otp-card">
            <h2 class="otp-title">Verify OTP</h2>
            <p class="otp-subtitle">
                We have sent a 6-digit OTP to<br>
                <strong><?php echo htmlspecialchars($email); ?></strong>
            </p>

            <?php if (!empty($message)): ?>
                <div class="alert">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <form method="post">
                <div class="form-group">
                    <label class="form-label" for="otp">Enter OTP</label>
                    <input type="text" name="otp" id="otp" maxlength="6"
                           class="form-control" placeholder="Enter 6-digit OTP" required>
                </div>

                <button type="submit" class="btn">Verify OTP</button>
            </form>

            <p class="note-text">
                Didn’t receive OTP or expired? You can request a new OTP.
            </p>

            <div class="otp-footer">
                <a href="forgot_password.php">← Resend OTP</a>
            </div>
        </div>
    </div>
</body>
</html>
