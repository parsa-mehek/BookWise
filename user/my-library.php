<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: /book-review/auth/login.php');
    exit();
}

include('../config/db.php');
include('../includes/helpers.php');

$page_title = 'My Library';
$user_id = (int)$_SESSION['user_id'];
$current_year = (int)date('Y');

$library_sql = "SELECT books.*, user_library.status, user_library.added_at, user_library.completed_at
                FROM user_library
                JOIN books ON user_library.book_id = books.id
                WHERE user_library.user_id = ?
                ORDER BY user_library.added_at DESC";
$library_stmt = $conn->prepare($library_sql);
$library_stmt->bind_param('i', $user_id);
$library_stmt->execute();
$library_result = $library_stmt->get_result();

$goal_stmt = $conn->prepare("SELECT goal_count FROM reading_goals WHERE user_id = ? AND goal_year = ? LIMIT 1");
$goal_stmt->bind_param('ii', $user_id, $current_year);
$goal_stmt->execute();
$goal_row = $goal_stmt->get_result()->fetch_assoc();
$goal_count = (int)($goal_row['goal_count'] ?? 10);

$completed_stmt = $conn->prepare("SELECT COUNT(*) AS total FROM user_library WHERE user_id = ? AND status = 'completed' AND YEAR(completed_at) = ?");
$completed_stmt->bind_param('ii', $user_id, $current_year);
$completed_stmt->execute();
$completed_row = $completed_stmt->get_result()->fetch_assoc();
$completed_books = (int)($completed_row['total'] ?? 0);
$remaining_books = max(0, $goal_count - $completed_books);
$progress_percent = $goal_count > 0 ? min(100, (int)round(($completed_books / $goal_count) * 100)) : 0;

include('../includes/header.php');
?>
<style>
body {
  background: #f3efe8;
}

.library-page {
  max-width: 1280px;
  margin: 0 auto;
  padding: 40px 24px 64px;
}

.library-hero,
.library-summary {
  background: #fff;
  border-radius: 24px;
  box-shadow: 0 16px 40px rgba(15, 23, 42, 0.06);
  border: 1px solid rgba(124, 92, 255, 0.08);
}

.library-hero {
  padding: 28px;
  margin-bottom: 24px;
}

.library-hero h1 {
  margin: 0 0 10px;
  font-size: clamp(28px, 4vw, 42px);
  color: #111827;
}

.library-hero p {
  margin: 0;
  color: #4b5563;
  line-height: 1.6;
}

.library-summary {
  padding: 24px;
  margin-bottom: 28px;
  background: linear-gradient(135deg, #f8f5ff, #ffffff 55%, #f4edff);
  color: #111827;
  border-color: #e8ddff;
}

.library-summary h2 {
  margin: 0 0 8px;
  color: #111827;
}

.library-summary p {
  color: #4b5563;
  margin: 0 0 14px;
}

.progress-bar {
  height: 12px;
  background: #e9d5ff;
  border-radius: 999px;
  overflow: hidden;
  margin-bottom: 12px;
}

.progress-bar-fill {
  height: 100%;
  background: linear-gradient(90deg, #7c3aed 0%, #9f7bff 100%);
}

.library-summary strong {
  color: #7c3aed;
}

.library-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
  gap: 24px;
}

.library-card {
  background: #fff;
  border-radius: 20px;
  overflow: hidden;
  box-shadow: 0 10px 26px rgba(15, 23, 42, 0.08);
}

.library-cover {
  aspect-ratio: 4 / 5;
  background: linear-gradient(135deg, #1f2937 0%, #7c3aed 100%);
}

.library-cover img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.library-fallback {
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #fff;
  font-size: 56px;
  font-weight: 800;
}

.library-body {
  padding: 18px;
}

.library-status {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  font-size: 12px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  margin-bottom: 10px;
  color: #7c3aed;
}

.library-card h3 {
  margin: 0 0 8px;
  color: #111827;
}

.library-card p {
  margin: 0 0 14px;
  color: #6b7280;
}

.library-actions {
  display: flex;
  gap: 10px;
  flex-wrap: wrap;
}

.completed-btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 100%;
  border: 0;
  border-radius: 12px;
  padding: 12px 16px;
  background: #dcfce7;
  color: #166534;
  font-weight: 700;
}

.empty-state {
  background: #fff;
  border-radius: 24px;
  padding: 32px;
  text-align: center;
  box-shadow: 0 10px 26px rgba(15, 23, 42, 0.08);
}

@media (max-width: 640px) {
  .library-page {
    padding: 28px 16px 48px;
  }
}
</style>

<main class="library-page">
  <section class="library-hero">
    <h1>My Library</h1>
    <p>Track the books you are reading, mark finished titles, and keep your yearly reading goal on target.</p>
  </section>

  <section class="library-summary">
    <h2><?php echo $completed_books; ?> books read this year</h2>
    <p><?php echo $remaining_books; ?> more to reach your <?php echo $current_year; ?> goal of <?php echo $goal_count; ?> books.</p>
    <div class="progress-bar" aria-label="Reading progress">
      <div class="progress-bar-fill" style="width: <?php echo $progress_percent; ?>%;"></div>
    </div>
    <strong><?php echo $progress_percent; ?>% complete</strong>
  </section>

  <?php if ($library_result->num_rows > 0): ?>
    <div class="library-grid">
      <?php while ($book = $library_result->fetch_assoc()): ?>
        <article class="library-card">
          <div class="library-cover">
            <?php if (!empty($book['cover_image'])): ?>
              <img src="<?php echo sanitize($book['cover_image']); ?>" alt="<?php echo sanitize($book['title']); ?> cover">
            <?php else: ?>
              <div class="library-fallback"><?php echo sanitize(strtoupper(substr((string)$book['title'], 0, 2))); ?></div>
            <?php endif; ?>
          </div>
          <div class="library-body">
            <div class="library-status">
              <i class="fa-solid fa-bookmark" aria-hidden="true"></i>
              <?php echo $book['status'] === 'completed' ? 'Completed' : 'Reading'; ?>
            </div>
            <h3><?php echo sanitize($book['title']); ?></h3>
            <p><?php echo sanitize($book['author'] ?: 'Unknown Author'); ?></p>
            <div class="library-actions">
              <?php
                $book_slug = trim((string)($book['slug'] ?? ''));
                if ($book_slug === '') {
                  $book_slug = preg_replace('/[^a-z0-9]+/i', '-', strtolower((string)$book['title']));
                  $book_slug = trim($book_slug, '-');
                }
                $book_url = '/book-review/books/' . rawurlencode($book_slug);
              ?>
              <a class="btn-primary" href="<?php echo sanitize($book_url); ?>">View Details</a>
              <?php if ($book['status'] === 'reading'): ?>
                <a class="btn-secondary" href="/book-review/library/complete.php?id=<?php echo (int)$book['id']; ?>">Mark As Done</a>
              <?php else: ?>
                <button class="completed-btn" type="button">Completed</button>
              <?php endif; ?>
            </div>
          </div>
        </article>
      <?php endwhile; ?>
    </div>
  <?php else: ?>
    <div class="empty-state">
      <h2>Your library is empty</h2>
      <p>Add books from any detail page to start tracking your reading progress.</p>
    </div>
  <?php endif; ?>
</main>

<?php include('../includes/footer.php'); ?>
