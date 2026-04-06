<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include('includes/db.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    $sql = "SELECT * FROM admin WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $admin = $result->fetch_assoc();
        if (password_verify($password, $admin['password'])) {
            $_SESSION['admin_id'] = $admin['admin_id'];
            $_SESSION['username'] = $admin['username'];
            header("Location: dashboard.php");
            exit;
        } else {
            echo "<div style='color: red; text-align: center;'>❌ Invalid password.</div>";
        }
    } else {
        echo "<div style='color: red; text-align: center;'>❌ Email not found.</div>";
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title> Login Register form</title>
  <link rel="stylesheet" href="assets/css/stylees.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
</head>
<body>
  <div class="container" id="container">
    <div class="form-container sign-up">
      <form action="register.php" method="POST">
        <h1>Create Account</h1>
        <div class="social-icons">
          <a href="#" class="icon"><i class="fab fa-google"></i></a>
          <a href="#" class="icon"><i class="fab fa-facebook"></i></a>
        </div>
        <span>Register to be an Admin</span>
        <input type="text" name="username" placeholder="Username" required />
        <input type="email" name="email" placeholder="Email" required />
        <input type="password" name="password" placeholder="Password" required />
        <button type="submit">Sign Up</button>
      </form>
    </div>

    <div class="form-container sign-in">
      <form action="login.php" method="POST">
        <h1>Log in</h1>
        <div class="social-icons">
          <a href="#" class="icon"><i class="fab fa-google"></i></a>
          <a href="#" class="icon"><i class="fab fa-facebook"></i></a>
        </div>
        <span>Login to Admin Panel</span>
        <input type="email" name="email" placeholder="Email" required />
        <input type="password" name="password" placeholder="Password" required />
        <a href="#">Forgot Password?</a>
        <button type="submit">Sign In</button>
        <a href="index.php" style="display:inline-block; margin-top:10px; text-decoration:none;">Back to Home</a>
      </form>
    </div>

    <div class="toggle-container">
      <div class="toggle">
        <div class="toggle-panel toggle-left">
          <h1>Welcome Back!</h1>
          <p>Already registered? Sign in here</p>
          <button class="hidden" id="login">Sign In</button>
        </div>
        <div class="toggle-panel toggle-right">
          <h1>Hello, Teacher!</h1>
          <p>Don't have an account? Register here</p>
          <button class="hidden" id="register">Sign Up</button>
        </div>
      </div>
    </div>
  </div>
  
  <script src="assets/js/script.js"></script>
</body>
</html>
