<?php header('Location: /book-review/user/profile.php'); exit(); /*

  .action-buttons {
    flex-direction: column;
  }

  .btn {
    width: 100%;
  }
}
</style>

<div class="profile-page">
  <div class="profile-container">
    
    <div class="profile-card">
      
      <!-- PROFILE HEADER -->
      <div class="profile-header">
        <div class="profile-avatar-large">
          <?php echo strtoupper(substr($user['name'], 0, 1)); ?>
        </div>
        <div class="profile-name"><?php echo htmlspecialchars($user['name']); ?></div>
        <div class="profile-email"><?php echo htmlspecialchars($user['email']); ?></div>
      </div>

      <!-- PROFILE CONTENT -->
      <div class="profile-content">

        <div class="goal-card">
          <h3><?php echo $current_year; ?> Reading Goal</h3>
          <p>You've read <strong><?php echo $completed_books; ?></strong> books this year. <strong><?php echo $remaining_books; ?> more</strong> to reach your goal of <?php echo $goal_count; ?> books.</p>
          <form class="goal-form" action="/book-review/user/save-goal.php" method="POST">
            <input type="number" name="goal_count" min="1" value="<?php echo $goal_count; ?>" placeholder="Books this year" required>
            <button class="btn btn-primary" type="submit">Save Goal</button>
          </form>
        </div>
        
        <!-- READING STATS -->
        <div class="stats-grid">
          <div class="stat-box">
            <div class="stat-value"><?php echo $stats['books_reviewed'] ?? 0; ?></div>
            <div class="stat-label">Books Reviewed</div>
          </div>
          <div class="stat-box">
            <div class="stat-value"><?php echo $stats['total_reviews'] ?? 0; ?></div>
            <div class="stat-label">Total Reviews</div>
          </div>
          <div class="stat-box">
            <div class="stat-value"><?php echo round($stats['avg_rating'] ?? 0, 1); ?></div>
            <div class="stat-label">Avg. Rating</div>
          </div>
        </div>

        <!-- ACCOUNT INFO -->
        <div class="profile-section">
          <div class="section-title">Account Information</div>
          <div class="info-row">
            <span class="info-label">Email Address</span>
            <span class="info-value"><?php echo htmlspecialchars($user['email']); ?></span>
          </div>
          <div class="info-row">
            <span class="info-label">Full Name</span>
            <span class="info-value"><?php echo htmlspecialchars($user['name']); ?></span>
          </div>
          <div class="info-row">
            <span class="info-label">Member Since</span>
            <span class="info-value">2026</span>
          </div>
        </div>

        <!-- ACTION BUTTONS -->
        <div class="action-buttons">
          <button class="btn btn-primary">Edit Profile</button>
          <a href="/book-review/auth/logout.php" class="btn btn-secondary">Logout</a>
        </div>

      </div>
    </div>

  </div>
</div>

*/
