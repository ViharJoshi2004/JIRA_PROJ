<?php
// register.php
session_start();
require 'Config.php';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $dp_name  = trim($_POST['dp_name'] ?? '');
    $phone    = trim($_POST['phone'] ?? '');

    if ($username === '' || $email === '' || $password === '') {
        $errors[] = "Please fill required fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email address.";
    }

    // duplicate check
    if (empty($errors)) {
        $chk = $conn->prepare("SELECT id FROM users WHERE Username = ? OR email = ?");
        $chk->bind_param("ss", $username, $email);
        $chk->execute();
        $chk->store_result();
        if ($chk->num_rows > 0) {
            $errors[] = "Username or email already taken.";
        }
        $chk->close();
    }

    if (empty($errors)) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO users (Username, email, psw, dp_name, phone) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $username, $email, $hash, $dp_name, $phone);
        if ($stmt->execute()) {
            header("Location:login.php?success=1");
            exit;
        } else {
            $errors[] = "DB error: " . $stmt->error;
        }
        $stmt->close();
    }
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Register</title>
  <link rel="stylesheet" href="style.css">
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
  <div class="overlay"></div>
  <div class="form-container">
    <form method="POST" action="register.php" >
      <h2>Create Account</h2>

      <?php if (!empty($errors)): ?>
        <?php foreach ($errors as $e): ?>
          <div class="error"><?= htmlspecialchars($e) ?></div>
        <?php endforeach; ?>
      <?php endif; ?>

      <label>Username</label>
      <input type="text" name="username" required>

      <label>Email</label>
      <input type="email" name="email" required>

      <label>Password</label>
      <input type="password" name="password" id="pw" required>
      <div id="txt" style="color:red;"></div>

      <label>Display Name</label>
      <input type="text" name="dp_name">

      <label>Phone</label>
      <input type="text" name="phone">

      <button type="submit">Create Account</button>
      <p>Already have an account? <a href="login.php">Login</a></p>
    </form>
  </div>
  <script>
    $("document").ready(function()
    {
    
       $("#pw").keydown(function()
       {
  
        ln=$("#pw").val();

        if(ln.length<=5)
          {
           $("#txt").html("Password contains minimum 6 characters");
         }
        else
        {
            $("#txt").html(" ");
        }
        });
    });
    </script>
</body>
</html>
