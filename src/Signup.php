<?php
require 'Config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $username = trim($_POST['username']);
  $email = trim($_POST['email']);
  $phone = trim($_POST['phone']);
  $password = $_POST['psw'];

  if ($username && $email && $phone && $password) {
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO login page (username, email, phone, psw) VALUES (?, ?, ?, ?)");
    try {
      $stmt->execute([$username, $email, $phone, $hash]);
      header('Location:login.php?success=1');
      exit;
    } catch (PDOException $e) {
      $error = "Username or Email already exists!";
    }
  } else {
    $error = "All fields are required.";
  }
}
?>

<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Register</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <div class="overlay"></div>
  <div class="form-container">
    <form action="" method="POST">
      <h2>Create Account</h2>
      <?php if (!empty($error)) echo "<p class='error'>$error</p>"; ?>

      <label>Username</label>
      <input type="text" name="username" required>

      <label>Email</label>
      <input type="email" name="email" required>

      <label>Phone</label>
      <input type="text" name="phone" required>

      <label>Password</label>
      <input type="password" name="password" required>

      <button type="submit">Register</button>
      <p>Already have an account? <a href="login.php">Login</a></p>
    </form>
  </div>
</body>
</html>
