<?php
session_start();
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo isset($page_title) ? htmlspecialchars((string)$page_title, ENT_QUOTES, 'UTF-8') : 'Bookwise'; ?></title>
<link rel="stylesheet" href="/book-review/assets/css/style.css">
<?php if (!empty($include_bootstrap)): ?>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
<?php endif; ?>
<link rel="stylesheet"
  href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>

<nav class="navbar">
  <div class="navbar-wrapper">
    <div class="navbar-container d-flex align-items-center">
      
      <!-- LOGO -->
      <div class="navbar-brand flex-shrink-0">
        <a href="/book-review/index.php" class="logo">
          <span class="logo-icon"><i class="fa-solid fa-book-open" aria-hidden="true"></i></span>
          <span class="logo-text">Bookwise</span>
        </a>
      </div>
      
      <!-- SEARCH BAR (centered, only for logged-in users) -->
      <?php if (isset($_SESSION['user_id']) && !isset($is_landing_page)): ?>
      <form class="navbar-search d-flex justify-content-center" action="/book-review/books/search.php" method="GET">
        <svg class="search-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
          <circle cx="11" cy="11" r="8"></circle>
          <path d="m21 21-4.35-4.35"></path>
        </svg>
        <input type="text" class="search-input" name="search" placeholder="Search books, authors..." required>
      </form>
      <?php endif; ?>
      
      <!-- RIGHT ICONS -->
      <div class="navbar-actions d-flex align-items-center <?php echo (!isset($_SESSION['user_id']) || isset($is_landing_page)) ? 'navbar-actions--guest' : ''; ?>">
        <?php if (isset($_SESSION['user_id']) && !isset($is_landing_page)): ?>
          <a href="/book-review/user/profile.php" class="profile-btn" title="Profile" aria-label="Profile">
            <div class="profile-avatar"><?php echo strtoupper(substr($_SESSION['user_name'] ?? 'U', 0, 1)); ?></div>
          </a>
        <?php else: ?>
          <a href="/book-review/auth/login.php" class="navbar-btn navbar-btn-login">Login</a>
          <a href="/book-review/auth/register.php" class="navbar-btn navbar-btn-signup">Sign Up</a>
        <?php endif; ?>
      </div>
      
    </div>
  </div>
</nav>

<div class="container">
