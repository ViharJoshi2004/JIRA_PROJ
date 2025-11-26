<?php
// login.php
session_start();
if (isset($_SESSION['user_id'])) {
    header("Location:dashbord.php");
    exit;
}
?>
<!doctype html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Login</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="overlay"></div>

    <div class="form-container">
      <form action="authenticate.php" method="POST">

        <!-- ===== LOGO START ===== -->
        <div class="logo-box">
            <!-- yaha image ka naam/path apne project ke hisab se change kar sakte ho -->
            <img src="logo_1.png" alt="Project Logo">
        </div>
        <!-- ===== LOGO END ===== -->

        <h2>Login</h2>

        <?php if (isset($_GET['error'])): ?>
          <div class="error">Invalid username or password</div>
        <?php endif; ?>

        <?php if (isset($_GET['success'])): ?>
          <div class="success">Account created! Please login.</div>
        <?php endif; ?>

        <label>Username or Email</label>
        <input type="text" name="user" required>

        <label>Password</label>
        <input type="password" name="pass" required>

        <button type="submit">Login</button>

        <p>Don't have an account? <a href="register.php">Register</a></p>
        <p><a href="forgot_password.php">Forgot Password?</a></p>
      
      </form>
    </div> 
  
</body>
</html>
