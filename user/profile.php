<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: /book-review/auth/login.php');
    exit();
}

require_once __DIR__ . '/../config/db.php';

$user_id = (int) $_SESSION['user_id'];
$current_year = (int) date('Y');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['goal_count'])) {
    $goal_count = max(1, (int) $_POST['goal_count']);
    $save_goal = $conn->prepare('INSERT INTO reading_goals (user_id, goal_year, goal_count) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE goal_count = VALUES(goal_count)');
    $save_goal->bind_param('iii', $user_id, $current_year, $goal_count);
    $save_goal->execute();
}

$user_stmt = $conn->prepare('SELECT name, email, created_at FROM users WHERE id = ?');
$user_stmt->bind_param('i', $user_id);
$user_stmt->execute();
$user = $user_stmt->get_result()->fetch_assoc() ?: [];

$stats_stmt = $conn->prepare('SELECT COUNT(*) AS total_reviews, AVG(rating) AS avg_rating, COUNT(DISTINCT book_id) AS books_reviewed FROM reviews WHERE user_id = ?');
$stats_stmt->bind_param('i', $user_id);
$stats_stmt->execute();
$stats = $stats_stmt->get_result()->fetch_assoc() ?: [];

$goal_stmt = $conn->prepare('SELECT goal_count FROM reading_goals WHERE user_id = ? AND goal_year = ? LIMIT 1');
$goal_stmt->bind_param('ii', $user_id, $current_year);
$goal_stmt->execute();
$goal_row = $goal_stmt->get_result()->fetch_assoc() ?: [];
$goal_count = (int) ($goal_row['goal_count'] ?? 10);

$completed_stmt = $conn->prepare("SELECT COUNT(*) AS total FROM user_library WHERE user_id = ? AND status = 'completed' AND YEAR(completed_at) = ?");
$completed_stmt->bind_param('ii', $user_id, $current_year);
$completed_stmt->execute();
$completed_row = $completed_stmt->get_result()->fetch_assoc() ?: [];
$completed_books = (int) ($completed_row['total'] ?? 0);
$remaining_books = max(0, $goal_count - $completed_books);
$member_since = !empty($user['created_at']) ? date('Y', strtotime($user['created_at'])) : $current_year;

include __DIR__ . '/../includes/header.php';

$user_name = $user['name'] ?? ($_SESSION['user_name'] ?? 'Reader');
$user_email = $user['email'] ?? ($_SESSION['user_email'] ?? '');
$avg_rating = round((float) ($stats['avg_rating'] ?? 0), 1);
?>

<style>
.profile-page {
  width: 90%;
  max-width: 1100px;
  margin: 40px auto;
}

.goal-card,
.account-card,
.stat-box {
  background: #ffffff;
  box-shadow: 0 12px 28px rgba(15, 23, 42, 0.06);
}

.goal-card {
  background: linear-gradient(135deg, #f8f5ff, #ffffff 55%, #f4edff);
  border: 1px solid #e8ddff;
  padding: 40px;
  border-radius: 24px;
  color: #1f2937;
  margin-bottom: 35px;
}

.goal-card h2 {
  font-size: 38px;
  margin-bottom: 15px;
  color: #111827;
}

.goal-card p {
  font-size: 18px;
  margin-bottom: 25px;
  color: #4b5563;
}

.goal-card span {
  font-weight: 700;
  color: #7c3aed;
}

.goal-card form {
  display: flex;
  gap: 15px;
}

.goal-card input {
  flex: 1;
  height: 58px;
  border: 1px solid #ddd6fe;
  border-radius: 14px;
  padding: 0 20px;
  font-size: 16px;
  background: #fff;
  color: #111827;
}

.goal-card input::placeholder {
  color: #9ca3af;
}

.goal-card input:focus {
  outline: none;
  border-color: #7c3aed;
  box-shadow: 0 0 0 3px rgba(124, 58, 237, 0.12);
}

.goal-card button {
  width: 180px;
  border: none;
  border-radius: 14px;
  background: linear-gradient(135deg, #7c3aed, #9f7bff);
  color: #fff;
  font-weight: 700;
  cursor: pointer;
  box-shadow: 0 10px 20px rgba(124, 58, 237, 0.18);
}

.goal-card button:hover {
  transform: translateY(-1px);
  box-shadow: 0 12px 24px rgba(124, 58, 237, 0.24);
}

.profile-stats {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 25px;
  margin-bottom: 35px;
}

.stat-box {
  padding: 35px;
  border-radius: 20px;
  text-align: center;
}

.stat-box h3 {
  font-size: 42px;
  color: #7c3aed;
  margin-bottom: 10px;
}

.stat-box p {
  font-size: 14px;
  font-weight: 600;
  color: #475569;
  letter-spacing: 0.04em;
  text-transform: uppercase;
}

.account-card {
  padding: 35px;
  border-radius: 20px;
  margin-bottom: 30px;
}

.account-card h2 {
  margin-bottom: 25px;
  color: #111827;
}

.info-row {
  display: flex;
  justify-content: space-between;
  gap: 16px;
  padding: 18px 0;
  border-bottom: 1px solid #eee;
}

.info-row:last-child {
  border-bottom: none;
}

.info-row span {
  color: #6b7280;
}

.info-row strong {
  color: #111827;
}

.logout-btn {
  width: 100%;
  height: 58px;
  border: none;
  border-radius: 14px;
  background: #ef4444;
  color: #fff;
  font-size: 16px;
  font-weight: 600;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  text-decoration: none;
}

@media (max-width: 768px) {
  .profile-page {
    width: 94%;
    margin: 24px auto;
  }

  .goal-card,
  .account-card {
    padding: 24px;
    border-radius: 18px;
  }

  .goal-card h2 {
    font-size: 28px;
  }

  .goal-card form,
  .info-row {
    flex-direction: column;
  }

  .goal-card button {
    width: 100%;
  }

  .profile-stats {
    grid-template-columns: 1fr;
  }
}
</style>

<div class="profile-page">
  <div class="goal-card">
    <h2><?php echo $current_year; ?> Reading Goal</h2>
    <p>
      You've read
      <span><?php echo $completed_books; ?></span>
      books this year.
      <span><?php echo $remaining_books; ?> more</span>
      to reach your goal of <?php echo $goal_count; ?> books.
    </p>
    <form action="/book-review/user/save-goal.php" method="POST">
      <input type="number" name="goal_count" min="1" value="<?php echo $goal_count; ?>" placeholder="Update yearly goal" required>
      <button type="submit">Save Goal</button>
    </form>
  </div>

  <div class="profile-stats">
    <div class="stat-box">
      <h3><?php echo (int) ($stats['books_reviewed'] ?? 0); ?></h3>
      <p>Books Reviewed</p>
    </div>
    <div class="stat-box">
      <h3><?php echo (int) ($stats['total_reviews'] ?? 0); ?></h3>
      <p>Total Reviews</p>
    </div>
    <div class="stat-box">
      <h3><?php echo $avg_rating; ?></h3>
      <p>Avg. Rating</p>
    </div>
  </div>

  <div class="account-card">
    <h2>Account Information</h2>
    <div class="info-row">
      <span>Email Address</span>
      <strong><?php echo htmlspecialchars($user_email, ENT_QUOTES, 'UTF-8'); ?></strong>
    </div>
    <div class="info-row">
      <span>Full Name</span>
      <strong><?php echo htmlspecialchars($user_name, ENT_QUOTES, 'UTF-8'); ?></strong>
    </div>
    <div class="info-row">
      <span>Member Since</span>
      <strong><?php echo htmlspecialchars((string) $member_since, ENT_QUOTES, 'UTF-8'); ?></strong>
    </div>
  </div>

  <a href="/book-review/auth/logout.php" class="logout-btn">Logout</a>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>