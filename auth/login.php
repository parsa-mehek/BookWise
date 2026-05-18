<?php
session_start();
include("../config/db.php");
include("../includes/helpers.php");

$redirect = isset($_GET['redirect']) ? $_GET['redirect'] : '/book-review/books/index.php';
$message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $sql = "SELECT * FROM users WHERE email=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($user = $result->fetch_assoc()) {
        $storedPassword = $user['password'];
        $passwordIsValid = false;
        $needsRehash = false;

        if (password_verify($password, $storedPassword)) {
            $passwordIsValid = true;
            $needsRehash = password_needs_rehash($storedPassword, PASSWORD_DEFAULT);
        } elseif ($storedPassword === $password) {
            $passwordIsValid = true;
            $needsRehash = true;
        }

        if ($passwordIsValid) {
            if ($needsRehash) {
                $newPasswordHash = password_hash($password, PASSWORD_DEFAULT);
                $updateStmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
                $updateStmt->bind_param("si", $newPasswordHash, $user['id']);
                $updateStmt->execute();
            }

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            header("Location: " . $redirect);
            exit();
        } else {
            $message = '<div class="error-message">Wrong password. Please try again.</div>';
        }
    } else {
        $message = '<div class="error-message">User not found. Please register first.</div>';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login | Bookwise</title>
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
    .auth {
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

    .left-content {
      position: relative;
      z-index: 2;
      max-width: 500px;
    }

    .left-content h1 {
      font-size: 48px;
      font-weight: 700;
      line-height: 1.2;
      margin-bottom: 20px;
      letter-spacing: -0.02em;
    }

    .left-content p {
      font-size: 16px;
      font-weight: 400;
      line-height: 1.6;
      opacity: 0.95;
    }

    .left-social-proof {
      position: absolute;
      bottom: 40px;
      left: 60px;
      display: flex;
      align-items: center;
      gap: 16px;
      z-index: 2;
    }

    .left-avatars {
      display: flex;
      gap: -8px;
    }

    .avatar {
      width: 40px;
      height: 40px;
      background: rgba(255, 255, 255, 0.2);
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 20px;
      border: 2px solid rgba(255, 255, 255, 0.3);
      margin-left: -8px;
    }

    .avatar:first-child {
      margin-left: 0;
    }

    .proof-text {
      display: flex;
      flex-direction: column;
      gap: 6px;
    }

    .rating {
      font-size: 14px;
      font-weight: 600;
    }

    .proof-text p {
      font-size: 12px;
      opacity: 0.85;
    }

    /* RIGHT SIDE - CARD CONTAINER */
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
      display: flex;
      align-items: center;
    }

    .input-icon {
      position: absolute;
      left: 14px;
      font-size: 18px;
      pointer-events: none;
    }

    .input-box input {
      width: 100%;
      padding: 14px 14px 14px 45px;
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

    .eye {
      position: absolute;
      right: 14px;
      cursor: pointer;
      font-size: 18px;
      user-select: none;
      transition: opacity 0.2s;
    }

    .eye:hover {
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

    /* BOTTOM LINK */
    .link {
      text-align: center;
      font-size: 14px;
      color: #666;
    }

    .link a {
      color: #7c3aed;
      text-decoration: none;
      font-weight: 600;
      transition: color 0.2s;
    }

    .link a:hover {
      color: #6d28d9;
    }

    /* RESPONSIVE */
    @media (max-width: 1024px) {
      .auth {
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

      .left-content h1 {
        font-size: 36px;
      }

      .left-social-proof {
        position: static;
        margin-top: 40px;
      }
    }

    @media (max-width: 600px) {
      .left-content h1 {
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
  <div class="auth">

    <div class="left">
      <div class="left-content">
        <h1>Discover your next favorite story.</h1>
        <p>Join thousands of readers and explore amazing books in our premium collection.</p>
      </div>

      <div class="left-social-proof">
        <div class="left-avatars">
            <div class="avatar"><i class="fa-solid fa-user" aria-hidden="true"></i></div>
            <div class="avatar"><i class="fa-solid fa-book" aria-hidden="true"></i></div>
            <div class="avatar"><i class="fa-solid fa-star" aria-hidden="true"></i></div>
          </div>
        <div class="proof-text">
          <div class="rating"><?php echo str_repeat('<i class="fa-solid fa-star" aria-hidden="true"></i>', 5); ?></div>
          <p>10k+ readers • 4.9 rating</p>
        </div>
      </div>
    </div>

    <div class="right">
      <div class="card">

        <h2>Welcome Back</h2>
        <p class="card-subtitle">Sign in to continue reading</p>

        <?php echo $message; ?>

        <form method="POST" action="login.php?redirect=<?php echo urlencode($redirect); ?>">

          <div class="input-box">
            <span class="input-icon"><i class="fa-solid fa-envelope"></i></span>
            <input type="email" name="email" placeholder="Your email" required>
          </div>

          <div class="input-box">
            <span class="input-icon"><i class="fa-solid fa-lock"></i></span>
            <input type="password" id="loginPass" name="password" placeholder="Password" required>
            <span class="eye" onclick="togglePass('loginPass')"><i class="fa-solid fa-eye"></i></span>
          </div>

          <label>
            <input type="checkbox" name="remember"> Remember me
          </label>

          <button class="btn" type="submit">Sign In</button>

        </form>

        <div class="divider">or continue with</div>

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

        <div class="link">
          Don't have an account? <a href="register.php">Create one</a>
        </div>

      </div>
    </div>

  </div>

  <script>
  function togglePass(id) {
    const input = document.getElementById(id);
    if (!input) return;
    input.type = input.type === 'password' ? 'text' : 'password';
  }
  </script>
</body>
</html>
