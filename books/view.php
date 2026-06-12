<?php
include("../config/db.php");
include("../includes/helpers.php");

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$slug = isset($_GET['slug']) ? trim((string)$_GET['slug']) : '';
$book = null;

if ($slug !== '') {
  $slug_column_result = $conn->query("SHOW COLUMNS FROM books LIKE 'slug'");
  $has_slug_column = $slug_column_result && $slug_column_result->num_rows > 0;

  if ($has_slug_column) {
    $sql = "SELECT * FROM books WHERE slug = ? LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $slug);
    $stmt->execute();
    $result = $stmt->get_result();
    $book = $result->fetch_assoc();
  } else {
    $sql = "SELECT * FROM books
            WHERE LOWER(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(title, ' ', '-'), '''', ''), ',', ''), '.', ''), ':', '')) = ?
            LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $slug);
    $stmt->execute();
    $result = $stmt->get_result();
    $book = $result->fetch_assoc();
  }
}

if (!$book && $id > 0) {
  $sql = "SELECT * FROM books WHERE id = ? LIMIT 1";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("i", $id);
  $stmt->execute();
  $result = $stmt->get_result();
  $book = $result->fetch_assoc();
}

include("../includes/header.php");

if (!$book) {
?>
<div class="card">
  <h2>Book not found</h2>
  <p>The requested book does not exist or has been removed.</p>
  <a href="/book-review/books" class="btn">Back to Books</a>
</div>
<?php
  include("../includes/footer.php");
  exit();
}

// Calculate average rating and total reviews; only filter by status if column exists
$has_review_status = column_exists($conn, 'reviews', 'status');
$avg_sql = "SELECT AVG(rating) AS avg_rating, COUNT(*) AS total_reviews FROM reviews WHERE book_id = ?";
if ($has_review_status) {
  $avg_sql .= " AND status = 'approved'";
}
$avg_stmt = $conn->prepare($avg_sql);
$avg_stmt->bind_param("i", $book['id']);
$avg_stmt->execute();
$avg_result = $avg_stmt->get_result()->fetch_assoc();
$avg_rating = $avg_result['avg_rating'];
$total_reviews = $avg_result['total_reviews'];

$cover_image = trim((string)($book['cover_image'] ?? $book['image'] ?? ''));
$book_genre = trim((string)($book['genre'] ?? 'Fiction'));

$reviews_sql = "SELECT reviews.*, users.name FROM reviews JOIN users ON reviews.user_id = users.id WHERE book_id = ?";
if ($has_review_status) {
  $reviews_sql .= " AND reviews.status = 'approved'";
}
$reviews_sql .= " ORDER BY reviews.id DESC";
$reviews_stmt = $conn->prepare($reviews_sql);
$reviews_stmt->bind_param("i", $book['id']);
$reviews_stmt->execute();
$reviews_result = $reviews_stmt->get_result();

// Auto-generate slug if missing and books table has slug column
$slug_check = $conn->query("SHOW COLUMNS FROM books LIKE 'slug'");
if ($slug_check && $slug_check->num_rows > 0) {
  if (empty($book['slug'])) {
    $generated = preg_replace('/[^a-z0-9\s-]/i', '', strtolower($book['title']));
    $generated = preg_replace('/\s+/', '-', trim($generated));
    $slug = $generated ?: ('book-' . $book['id']);

    // ensure uniqueness
    $uniq_sql = "SELECT COUNT(*) AS cnt FROM books WHERE slug = ?";
    $uniq_stmt = $conn->prepare($uniq_sql);
    $test_slug = $slug;
    $i = 1;
    while (true) {
      $uniq_stmt->bind_param("s", $test_slug);
      $uniq_stmt->execute();
      $res = $uniq_stmt->get_result()->fetch_assoc();
      if ((int)$res['cnt'] === 0) break;
      $test_slug = $slug . '-' . $i;
      $i++;
    }

    $update_sql = "UPDATE books SET slug = ? WHERE id = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("si", $test_slug, $book['id']);
    $update_stmt->execute();
    $book['slug'] = $test_slug;
  }
}
?>
<style>
.details-page {
  width: 100%;
  max-width: 1320px;
  margin: 44px auto 56px;
}

.details-layout {
  display: grid;
  grid-template-columns: 340px 1fr;
  gap: 28px;
  margin-bottom: 28px;
}

.surface-card {
  background: #fff;
  border: 1px solid #ece8f8;
  border-radius: 22px;
  box-shadow: 0 10px 26px rgba(35, 25, 80, 0.06);
}

.details-image-card {
  padding: 18px;
}

.details-image {
  width: 100%;
  aspect-ratio: 4 / 5;
  border-radius: 16px;
  background: linear-gradient(135deg, #7b5cff 0%, #9f7bff 100%);
  color: #fff;
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;
}

.details-image img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.details-initials {
  font-size: 74px;
  font-weight: 800;
  letter-spacing: 3px;
}

.details-info-card {
  padding: 32px;
}

.genre-tag {
  display: inline-flex;
  align-items: center;
  width: fit-content;
  margin-bottom: 18px;
  padding: 8px 14px;
  border-radius: 999px;
  background: rgba(123, 92, 255, 0.12);
  color: #6f42ea;
  font-size: 13px;
  font-weight: 700;
}

.details-title {
  font-size: clamp(30px, 3.4vw, 48px);
  line-height: 1.05;
  letter-spacing: -0.02em;
  color: #151323;
  margin: 0 0 10px;
}

.details-author {
  margin: 0 0 20px;
  color: #4b5563;
  font-size: 20px;
  font-weight: 600;
}

.details-rating {
  display: flex;
  align-items: center;
  flex-wrap: wrap;
  gap: 10px;
  margin-bottom: 18px;
  color: #f59e0b;
  font-size: 17px;
  font-weight: 700;
}

.rating-meta {
  color: #6b7280;
  font-size: 14px;
  font-weight: 600;
}

.details-description {
  margin: 0 0 28px;
  color: #4b5563;
  line-height: 1.75;
  max-width: 70ch;
}

.details-actions {
  display: flex;
  flex-wrap: wrap;
  gap: 12px;
}

.details-actions .btn-primary,
.details-actions .btn-secondary {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  border: 0;
  cursor: pointer;
}

.review-box {
  margin-bottom: 28px;
  padding: 28px;
}

.section-heading {
  margin: 0 0 18px;
  font-size: 25px;
  font-weight: 800;
  color: #151323;
}

.rating-select {
  width: 220px;
  border: 1px solid #e4e7ec;
  border-radius: 12px;
  padding: 11px 12px;
  margin-bottom: 16px;
  background: #fff;
}

.review-textarea {
  width: 100%;
  min-height: 150px;
  border: 1px solid #e4e7ec;
  border-radius: 16px;
  padding: 16px 18px;
  resize: vertical;
  margin-bottom: 16px;
}

.review-textarea:focus,
.rating-select:focus {
  outline: none;
  border-color: #7c5cff;
  box-shadow: 0 0 0 3px rgba(124, 92, 255, 0.14);
}

.login-note {
  margin: 0;
  color: #4b5563;
}

.reviews-wrap {
  padding: 28px;
}

.review-list {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.review-card {
  border: 1px solid #ece8f8;
  border-radius: 18px;
  padding: 20px;
  background: #fff;
}

.review-top {
  display: flex;
  justify-content: space-between;
  gap: 12px;
  align-items: center;
  margin-bottom: 10px;
}

.review-user {
  display: flex;
  align-items: center;
  gap: 12px;
}

.review-avatar {
  width: 42px;
  height: 42px;
  border-radius: 999px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(135deg, #7b5cff 0%, #9f7bff 100%);
  color: #fff;
  font-weight: 800;
}

.review-user h4 {
  margin: 0 0 2px;
  color: #111827;
  font-size: 15px;
}

.review-user p {
  margin: 0;
  color: #6b7280;
  font-size: 12px;
}

.review-stars {
  color: #f59e0b;
  font-size: 14px;
}

.review-comment {
  margin: 0;
  color: #374151;
  line-height: 1.6;
}

.review-actions {
  margin-top: 10px;
}

.review-delete {
  font-size: 13px;
  font-weight: 600;
  color: #dc2626;
  text-decoration: none;
}

.review-delete:hover {
  text-decoration: underline;
}

@media (max-width: 992px) {
  .details-layout {
    grid-template-columns: 1fr;
  }

  .details-image-card {
    max-width: 440px;
  }
}

@media (max-width: 640px) {
  .details-page {
    margin: 24px auto 40px;
  }

  .details-info-card,
  .review-box,
  .reviews-wrap {
    padding: 20px;
  }

  .details-author {
    font-size: 18px;
  }

  .section-heading {
    font-size: 22px;
  }

  .rating-select {
    width: 100%;
  }
}
</style>

<div class="details-page">
  <div class="details-layout">
    <div class="surface-card details-image-card">
      <div class="details-image">
        <?php if ($cover_image !== ''): ?>
          <img src="<?php echo sanitize($cover_image); ?>" alt="<?php echo sanitize($book['title']); ?> cover">
        <?php else: ?>
          <span class="details-initials"><?php echo sanitize(strtoupper(substr((string)$book['title'], 0, 2))); ?></span>
        <?php endif; ?>
      </div>
    </div>

    <div class="surface-card details-info-card">
      <span class="genre-tag"><?php echo sanitize($book_genre); ?></span>
      <h1 class="details-title"><?php echo sanitize($book['title']); ?></h1>
      <p class="details-author">by <?php echo sanitize($book['author'] ?: 'Unknown Author'); ?></p>

      <div class="details-rating">
        <?php if ($avg_rating): ?>
          <?php echo str_repeat('<i class="fa-solid fa-star" aria-hidden="true"></i>', (int)round($avg_rating)); ?>
          <span><?php echo sanitize(number_format((float)$avg_rating, 1)); ?></span>
          <span class="rating-meta">(<?php echo (int)$total_reviews; ?> review<?php echo ((int)$total_reviews !== 1) ? 's' : ''; ?>)</span>
        <?php else: ?>
          <span class="rating-meta">No ratings yet</span>
        <?php endif; ?>
      </div>

      <p class="details-description"><?php echo sanitize($book['description'] ?: 'No description available for this book yet.'); ?></p>

      <div class="details-actions">
        <?php if (isset($_SESSION['user_id'])): ?>
          <a href="#write-review" class="btn-primary"><i class="fa-solid fa-pen" aria-hidden="true"></i>&nbsp; Add Review</a>
          <a href="/book-review/library/add.php?id=<?php echo (int)$book['id']; ?>" class="btn-secondary"><i class="fa-solid fa-bookmark" aria-hidden="true"></i>&nbsp; Add To Library</a>
        <?php else: ?>
          <a href="/book-review/auth/login.php" class="btn-primary"><i class="fa-solid fa-right-to-bracket" aria-hidden="true"></i>&nbsp; Login to Review</a>
          <a href="/book-review/books/browse.php" class="btn-secondary">Browse Books</a>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <div id="write-review" class="surface-card review-box">
    <?php if (isset($_SESSION['user_id'])): ?>
      <h2 class="section-heading">Write Review</h2>
      <form action="/book-review/reviews/add_review.php" method="POST">
        <input type="hidden" name="book_id" value="<?php echo (int)$book['id']; ?>">

        <select class="rating-select" name="rating" required>
          <option value="">Select rating</option>
          <option value="1">1 star</option>
          <option value="2">2 stars</option>
          <option value="3">3 stars</option>
          <option value="4">4 stars</option>
          <option value="5">5 stars</option>
        </select>

        <textarea class="review-textarea" name="comment" placeholder="Write your review..." required></textarea>
        <button class="btn-primary" type="submit">Submit Review</button>
      </form>
    <?php else: ?>
      <h2 class="section-heading">Write Review</h2>
      <p class="login-note">Please <a href="/book-review/auth/login.php">login</a> to add a review.</p>
    <?php endif; ?>
  </div>

  <div class="surface-card reviews-wrap">
    <h2 class="section-heading">Reviews</h2>

    <?php if ($reviews_result->num_rows > 0): ?>
      <div class="review-list">
        <?php while ($row = $reviews_result->fetch_assoc()): ?>
          <article class="review-card">
            <div class="review-top">
              <div class="review-user">
                <div class="review-avatar"><?php echo sanitize(strtoupper(substr((string)$row['name'], 0, 1))); ?></div>
                <div>
                  <h4><?php echo sanitize($row['name']); ?></h4>
                  <p><?php echo sanitize(date('M j, Y', strtotime((string)$row['created_at']))); ?></p>
                </div>
              </div>
              <div class="review-stars"><?php echo str_repeat('<i class="fa-solid fa-star" aria-hidden="true"></i>', (int)$row['rating']); ?></div>
            </div>

            <p class="review-comment"><?php echo nl2br(sanitize($row['comment'])); ?></p>

          </article>
        <?php endwhile; ?>
      </div>
    <?php else: ?>
      <p class="login-note">No reviews yet. Be the first to review this book.</p>
    <?php endif; ?>
  </div>
</div>

<?php include("../includes/footer.php"); ?>
