<?php
session_start();
include("../config/db.php");
include("../includes/helpers.php");

$message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    if ($name && $email && $password) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $check->bind_param("s", $email);
        $check->execute();
        
        if ($check->get_result()->num_rows > 0) {
            $message = '<div class="error-message">Email already exists! Please use a different email.</div>';
        } else {
            $sql = "INSERT INTO users (name, email, password) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sss", $name, $email, $hashed_password);

            if ($stmt->execute()) {
                $message = '<div class="success-message">Registration successful! <a href="login.php" style="color:#059669; font-weight:600;">Login here</a></div>';
            } else {
                $message = '<div class="error-message">Registration failed. Please try again.</div>';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sign Up - Bookwise</title>
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    html, body {
      height: 100%;
      width: 100%;
    }

    body {
      font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', 'Oxygen', 'Ubuntu', 'Cantarell', sans-serif;
      background: #fff;
    }

    /* MAIN CONTAINER */
    .container {
      display: flex;
      height: 100vh;
      width: 100%;
      overflow: hidden;
    }

    /* LEFT SIDE - GRADIENT BACKGROUND */
    .left {
      flex: 1;
      background: linear-gradient(135deg, #7c3aed 0%, #a855f7 50%, #c084fc 100%);
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: flex-start;
      padding: 60px;
      color: white;
      position: relative;
      overflow: hidden;
    }

    .left::before {
      content: "";
      position: absolute;
      width: 500px;
      height: 500px;
      background: radial-gradient(circle, rgba(255, 255, 255, 0.15) 0%, transparent 70%);
      border-radius: 50%;
      bottom: -150px;
      right: -150px;
      z-index: 1;
    }

    .left h1 {
      font-size: 48px;
      font-weight: 700;
      line-height: 1.2;
      margin: 0;
      position: relative;
      z-index: 2;
      max-width: 500px;
      letter-spacing: -0.02em;
    }

    .left p {
      font-size: 16px;
      font-weight: 400;
      line-height: 1.6;
      opacity: 0.95;
      margin-top: 18px;
      max-width: 500px;
      position: relative;
      z-index: 2;
    }

    /* RIGHT SIDE */
    .right {
      flex: 1;
      background: linear-gradient(135deg, #f3efe8 0%, #f9f5f0 100%);
      display: flex;
      justify-content: center;
      align-items: center;
      padding: 40px 20px;
    }

    /* CARD */
    .card {
      width: 100%;
      max-width: 380px;
      background: white;
      padding: 45px;
      border-radius: 20px;
      box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08);
    }

    .card h2 {
      font-size: 30px;
      font-weight: 700;
      color: #1a1a1a;
      margin-bottom: 10px;
      letter-spacing: -0.01em;
    }

    .card-subtitle {
      font-size: 14px;
      color: #666;
      margin-bottom: 25px;
      font-weight: 500;
    }

    .success-message {
      background: #f0fdf4;
      color: #166534;
      padding: 12px 14px;
      border-radius: 12px;
      font-size: 14px;
      margin-bottom: 20px;
      border-left: 3px solid #22c55e;
    }

    .error-message {
      background: #fef2f2;
      color: #991b1b;
      padding: 12px 14px;
      border-radius: 12px;
      font-size: 14px;
      margin-bottom: 20px;
      border-left: 3px solid #dc2626;
    }

    /* FORM INPUTS */
    .input-box {
      position: relative;
      margin-bottom: 16px;
    }

    .input-box input {
      width: 100%;
      padding: 14px 14px;
      border: none;
      border-radius: 12px;
      background: #f1f2f6;
      font-size: 15px;
      font-weight: 500;
      transition: all 0.3s ease;
      color: #1a1a1a;
    }

    .input-box input::placeholder {
      color: #999;
    }

    .input-box input:focus {
      outline: none;
      background: #f9fafb;
      box-shadow: 0 0 0 3px rgba(124, 58, 237, 0.1), 0 0 0 1px rgba(124, 58, 237, 0.2);
    }

    .eye-toggle {
      position: absolute;
      right: 14px;
      top: 50%;
      transform: translateY(-50%);
      cursor: pointer;
      font-size: 18px;
      user-select: none;
      transition: opacity 0.2s;
    }

    .eye-toggle:hover {
      opacity: 0.7;
    }

    /* CHECKBOX */
    .card label {
      display: flex;
      align-items: center;
      gap: 10px;
      margin-bottom: 24px;
      font-size: 14px;
      color: #333;
      cursor: pointer;
      font-weight: 500;
    }

    .card input[type="checkbox"] {
      width: 18px;
      height: 18px;
      cursor: pointer;
      accent-color: #7c3aed;
    }

    /* BUTTON */
    .btn {
      width: 100%;
      padding: 14px 16px;
      border: none;
      border-radius: 12px;
      background: linear-gradient(135deg, #7c3aed 0%, #a855f7 100%);
      color: white;
      font-size: 16px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s ease;
      box-shadow: 0 4px 15px rgba(124, 58, 237, 0.3);
      margin-bottom: 20px;
    }

    .btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 25px rgba(124, 58, 237, 0.4);
    }

    .btn:active {
      transform: translateY(0);
    }

    /* DIVIDER */
    .divider {
      text-align: center;
      margin: 20px 0;
      font-size: 13px;
      color: #999;
      display: flex;
      align-items: center;
      gap: 12px;
      font-weight: 500;
    }

    .divider::before, .divider::after {
      content: "";
      flex: 1;
      height: 1px;
      background: #e5e7eb;
    }

    /* SOCIAL BUTTONS */
    .social {
      display: flex;
      gap: 12px;
      margin-bottom: 24px;
    }

    .social button {
      flex: 1;
      padding: 12px 14px;
      border: 1px solid #e5e7eb;
      border-radius: 12px;
      background: white;
      font-size: 15px;
      font-weight: 500;
      color: #333;
      cursor: pointer;
      transition: all 0.2s ease;
    }

    .social button:hover {
      background: #f9fafb;
      border-color: #d1d5db;
      transform: translateY(-1px);
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
    }

    .social-btn {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
    }

    .social-btn svg {
      width: 20px;
      height: 20px;
    }

    /* CARD LINK */
    .card-link {
      text-align: center;
      font-size: 14px;
      color: #666;
    }

    .card-link a {
      color: #7c3aed;
      text-decoration: none;
      font-weight: 600;
      transition: color 0.2s;
    }

    .card-link a:hover {
      color: #6d28d9;
    }

    /* RESPONSIVE */
    @media (max-width: 1024px) {
      .container {
        flex-direction: column;
      }

      .left, .right {
        flex: 1;
        width: 100%;
      }

      .left {
        padding: 40px;
        justify-content: flex-start;
        padding-top: 60px;
      }

      .left h1 {
        font-size: 36px;
      }
    }

    @media (max-width: 600px) {
      .left h1 {
        font-size: 28px;
      }

      .card {
        padding: 30px;
      }

      .card h2 {
        font-size: 24px;
      }
    }
  </style>
</head>
<body>

<div class="container">

  <!-- LEFT -->
  <div class="left">
    <h1>Join our community of book lovers.</h1>
    <p>Discover amazing books, share your reviews, and connect with readers worldwide.</p>
  </div>

  <!-- RIGHT -->
  <div class="right">
    <div class="card">

      <h2>Create Account</h2>
      <p class="card-subtitle">Join Bookwise today and start reading</p>

      <?php echo $message; ?>

      <form method="POST" action="register.php">

        <div class="input-box">
          <input type="text" name="name" placeholder="Full name" required>
        </div>

        <div class="input-box">
          <input type="email" name="email" placeholder="Email address" required>
        </div>

        <div class="input-box">
          <input type="password" id="pass" name="password" placeholder="Create password" required>
          <span class="eye-toggle" onclick="togglePassword('pass')"><i class="fa-solid fa-eye"></i></span>
        </div>

        <label>
          <input type="checkbox" name="terms" required> I agree to Terms & Privacy
        </label>

        <button type="submit" class="btn">Create Account</button>

      </form>

      <div class="divider">or sign up with</div>

      <div class="social">
        <button type="button" class="social-btn google-btn">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
            <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
            <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/>
            <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
          </svg>
          Google
        </button>
        <button type="button" class="social-btn facebook-btn">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z" fill="#1877F2"/>
          </svg>
          Facebook
        </button>
      </div>

      <p class="card-link">
        Already have an account? <a href="login.php">Sign in</a>
      </p>

    </div>
  </div>

</div>

<script>
function togglePassword(id) {
  const input = document.getElementById(id);
  if (input.type === 'password') {
    input.type = 'text';
  } else {
    input.type = 'password';
  }
}
</script>

</body>
</html>
