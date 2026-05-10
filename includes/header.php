<?php
session_start();
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
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
      <div class="navbar-search d-flex justify-content-center">
        <svg class="search-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <circle cx="11" cy="11" r="8"></circle>
          <path d="m21 21-4.35-4.35"></path>
        </svg>
        <input type="text" class="search-input" placeholder="Search books, authors, users...">
      </div>
      <?php endif; ?>
      
      <!-- RIGHT ICONS -->
      <div class="navbar-actions d-flex align-items-center <?php echo (!isset($_SESSION['user_id']) || isset($is_landing_page)) ? 'navbar-actions--guest' : ''; ?>">
        <?php if (isset($_SESSION['user_id']) && !isset($is_landing_page)): ?>
          <button class="action-icon notification-icon" title="Notifications">
            <i class="fa-solid fa-bell" aria-hidden="true"></i>
          </button>
          
          <div class="profile-dropdown">
            <button class="action-icon profile-btn" onclick="toggleProfileMenu()" title="Profile">
              <div class="profile-avatar"><?php echo strtoupper(substr($_SESSION['user_name'] ?? 'U', 0, 1)); ?></div>
            </button>
            <div class="profile-menu" id="profileMenu">
              <a href="/book-review/profile.php" class="profile-menu-item"><i class="fa-solid fa-user" aria-hidden="true"></i> Profile</a>
              <div class="profile-menu-divider"></div>
              <a href="/book-review/auth/logout.php" class="profile-menu-item logout-item"><i class="fa-solid fa-right-from-bracket" aria-hidden="true"></i> Logout</a>
            </div>
          </div>
        <?php else: ?>
          <a href="/book-review/auth/login.php" class="navbar-btn navbar-btn-login">Login</a>
          <a href="/book-review/auth/register.php" class="navbar-btn navbar-btn-signup">Sign Up</a>
        <?php endif; ?>
      </div>
      
    </div>
  </div>
</nav>

<div class="container">
