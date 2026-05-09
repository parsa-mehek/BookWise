<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: /book-review/auth/login.php");
    exit();
}

include("config/db.php");

$user_id = $_SESSION['user_id'];

// Get user data
$sql = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Get user's reading stats
$stats_sql = "SELECT 
              COUNT(*) as total_reviews,
              AVG(rating) as avg_rating,
              COUNT(DISTINCT book_id) as books_reviewed
              FROM reviews WHERE user_id = ?";
$stats_stmt = $conn->prepare($stats_sql);
$stats_stmt->bind_param("i", $user_id);
$stats_stmt->execute();
$stats = $stats_stmt->get_result()->fetch_assoc();

include("includes/header.php");
?>

<style>
.profile-page {
  padding: 40px 20px;
  background: linear-gradient(135deg, #f5efe6 0%, #f9f5f0 100%);
  min-height: calc(100vh - 200px);
}

.profile-container {
  max-width: 800px;
  margin: 0 auto;
}

.profile-card {
  background: white;
  border-radius: 16px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
  overflow: hidden;
}

.profile-header {
  background: linear-gradient(135deg, #7c3aed 0%, #a855f7 100%);
  padding: 40px 30px;
  text-align: center;
  color: white;
}

.profile-avatar-large {
  width: 100px;
  height: 100px;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.3);
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto 20px;
  font-size: 48px;
  font-weight: bold;
  border: 3px solid white;
}

.profile-name {
  font-size: 28px;
  font-weight: 700;
  margin-bottom: 8px;
}

.profile-email {
  font-size: 14px;
  opacity: 0.95;
}

.profile-content {
  padding: 40px 30px;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 24px;
  margin-bottom: 40px;
}

.stat-box {
  background: #f9fafb;
  padding: 24px;
  border-radius: 12px;
  text-align: center;
  border: 1px solid #e5e7eb;
}

.stat-value {
  font-size: 32px;
  font-weight: 700;
  color: #7c3aed;
  margin-bottom: 8px;
}

.stat-label {
  font-size: 14px;
  color: #666;
  font-weight: 500;
}

.profile-section {
  margin-bottom: 32px;
}

.section-title {
  font-size: 18px;
  font-weight: 700;
  color: #1a1a1a;
  margin-bottom: 16px;
  padding-bottom: 12px;
  border-bottom: 2px solid #e5e7eb;
}

.info-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 12px 0;
  border-bottom: 1px solid #f0f0f0;
}

.info-row:last-child {
  border-bottom: none;
}

.info-label {
  font-size: 14px;
  color: #666;
  font-weight: 500;
}

.info-value {
  font-size: 14px;
  color: #1a1a1a;
  font-weight: 600;
}

.action-buttons {
  display: flex;
  gap: 12px;
  margin-top: 30px;
}

.btn {
  padding: 12px 24px;
  border: none;
  border-radius: 10px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
  font-size: 14px;
}

.btn-primary {
  background: linear-gradient(135deg, #7c3aed 0%, #a855f7 100%);
  color: white;
  box-shadow: 0 4px 12px rgba(124, 58, 237, 0.3);
}

.btn-primary:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 16px rgba(124, 58, 237, 0.4);
}

.btn-secondary {
  background: transparent;
  color: #666;
  border: 1px solid #d1d5db;
}

.btn-secondary:hover {
  background: #f9fafb;
  border-color: #7c3aed;
  color: #7c3aed;
}

@media (max-width: 600px) {
  .profile-header {
    padding: 30px 20px;
  }

  .profile-content {
    padding: 30px 20px;
  }

  .stats-grid {
    grid-template-columns: 1fr;
  }

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

<?php include("includes/footer.php"); ?>
