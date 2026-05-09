<?php
require_once("../config/db.php");

$hide_navbar_search = true;
$search = trim($_GET['search'] ?? '');
$genre = trim($_GET['genre'] ?? 'All');
$time_period = trim($_GET['time_period'] ?? 'all');
$sort = trim($_GET['sort'] ?? 'rating');
$page = max(1, (int)($_GET['page'] ?? 1));
$per_page = 12;
$offset = ($page - 1) * $per_page;

$allowed_genres = ['All', 'Fiction', 'Non-Fiction', 'Mystery', 'Sci-Fi', 'Biography', 'History'];
if (!in_array($genre, $allowed_genres, true)) {
    $genre = 'All';
}

$allowed_sorts = ['rating', 'most_reviewed', 'newest', 'title'];
if (!in_array($sort, $allowed_sorts, true)) {
    $sort = 'rating';
}

$allowed_time_periods = ['all', 'week', 'month'];
if (!in_array($time_period, $allowed_time_periods, true)) {
  $time_period = 'all';
}

$genre_case = "CASE
    WHEN LOWER(CONCAT_WS(' ', b.title, b.author, COALESCE(b.description, ''))) REGEXP 'mystery|thriller|detective|crime|secret|case' THEN 'Mystery'
    WHEN LOWER(CONCAT_WS(' ', b.title, b.author, COALESCE(b.description, ''))) REGEXP 'sci[- ]?fi|science fiction|space|alien|robot|future' THEN 'Sci-Fi'
    WHEN LOWER(CONCAT_WS(' ', b.title, b.author, COALESCE(b.description, ''))) REGEXP 'biography|memoir|life story|personal|author\'s life' THEN 'Biography'
    WHEN LOWER(CONCAT_WS(' ', b.title, b.author, COALESCE(b.description, ''))) REGEXP 'history|historical|war|empire|ancient|chronicle' THEN 'History'
    WHEN LOWER(CONCAT_WS(' ', b.title, b.author, COALESCE(b.description, ''))) REGEXP 'non-fiction|nonfiction|fact|essay|guide|how to|self-help|reference' THEN 'Non-Fiction'
    ELSE 'Fiction'
END";

$inner_sql = "SELECT
        b.id,
        b.title,
        b.author,
        b.description,
        COALESCE(AVG(r.rating), 0) AS avg_rating,
        COUNT(r.id) AS review_count,
        $genre_case AS derived_genre
    FROM books b
    LEFT JOIN reviews r ON b.id = r.book_id
    GROUP BY b.id";

$filters = [];
if ($genre !== 'All') {
    $filters[] = "derived_genre = '" . $conn->real_escape_string($genre) . "'";
}
if ($search !== '') {
    $escaped_search = $conn->real_escape_string($search);
    $filters[] = "(title LIKE '%{$escaped_search}%' OR author LIKE '%{$escaped_search}%')";
}
$where_clause = $filters ? 'WHERE ' . implode(' AND ', $filters) : '';

$count_sql = "SELECT COUNT(*) AS total FROM ($inner_sql) ranked $where_clause";
$count_result = $conn->query($count_sql);
$total_books = 0;
if ($count_result) {
    $count_row = $count_result->fetch_assoc();
    $total_books = (int)($count_row['total'] ?? 0);
}
$total_pages = max(1, (int)ceil($total_books / $per_page));

switch ($sort) {
  case 'most_reviewed':
    $order_by = 'review_count DESC, avg_rating DESC, title ASC';
    break;
    case 'newest':
        $order_by = 'id DESC';
        break;
    case 'title':
        $order_by = 'title ASC';
        break;
    default:
        $order_by = 'avg_rating DESC, review_count DESC, title ASC';
        break;
}

$data_sql = "SELECT * FROM ($inner_sql) ranked $where_clause ORDER BY $order_by LIMIT $per_page OFFSET $offset";
$data_result = $conn->query($data_sql);
$books = [];
if ($data_result) {
    while ($row = $data_result->fetch_assoc()) {
        $books[] = $row;
    }
}

?>
<?php include("../includes/header.php"); ?>

<style>
html, body {
  min-height: 100%;
}

body {
  background: #f3efe8;
  display: flex;
  flex-direction: column;
}

.container {
  flex: 1;
  display: flex;
  flex-direction: column;
  width: 100%;
  max-width: none;
  margin: 0;
  padding: 0;
}

.top-rated-page {
  flex: 1;
  max-width: 1200px;
  width: calc(100% - 32px);
  margin: 0 auto;
  padding: 40px 0 56px;
}

.top-shell {
  display: grid;
  grid-template-columns: 260px minmax(0, 1fr);
  gap: 28px;
  align-items: start;
}

.page-hero {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 20px;
  margin-bottom: 24px;
}

.page-copy h1 {
  font-size: clamp(2.4rem, 4vw, 3.4rem);
  line-height: 1.05;
  letter-spacing: -0.05em;
  color: #151515;
  margin-bottom: 10px;
}

.page-copy p {
  color: #6b7280;
  font-size: 1rem;
  line-height: 1.7;
  max-width: 56ch;
}

.page-count {
  align-self: center;
  padding: 12px 16px;
  border-radius: 16px;
  background: rgba(255,255,255,0.7);
  border: 1px solid rgba(124, 58, 237, 0.08);
  color: #7c3aed;
  font-weight: 700;
  box-shadow: 0 8px 24px rgba(0,0,0,0.04);
  white-space: nowrap;
}

.search-filter-bar {
  background: rgba(255,255,255,0.92);
  border: 1px solid rgba(255,255,255,0.65);
  border-radius: 24px;
  padding: 18px;
  box-shadow: 0 16px 40px rgba(17, 24, 39, 0.05);
  margin-bottom: 24px;
}

.filter-bar-form {
  display: flex;
  flex-wrap: wrap;
  gap: 14px;
  align-items: center;
}

.search-input-wrap {
  position: relative;
  flex: 1 1 260px;
  min-width: 250px;
}

.search-input-wrap svg {
  position: absolute;
  left: 16px;
  top: 50%;
  transform: translateY(-50%);
  color: #9ca3af;
}

.search-input-wrap input {
  width: 100%;
  border: 1px solid #e5e7eb;
  background: #f9fafb;
  border-radius: 16px;
  padding: 14px 16px 14px 48px;
  font-size: 15px;
  color: #111827;
  transition: all 0.2s ease;
}

.search-input-wrap input:focus {
  outline: none;
  background: #fff;
  border-color: #7c3aed;
  box-shadow: 0 0 0 4px rgba(124,58,237,0.1);
}

.filter-select {
  min-width: 170px;
  border: 1px solid #e5e7eb;
  background: #fff;
  border-radius: 16px;
  padding: 14px 16px;
  font-weight: 600;
  color: #374151;
  cursor: pointer;
}

.filter-select:focus {
  outline: none;
  border-color: #7c3aed;
  box-shadow: 0 0 0 4px rgba(124,58,237,0.1);
}

.filter-pills {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
  margin-top: 16px;
}

.filter-pill {
  border: 1px solid #e5e7eb;
  background: #fff;
  color: #374151;
  border-radius: 999px;
  padding: 10px 14px;
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
  text-decoration: none;
  display: inline-flex;
  align-items: center;
  gap: 8px;
}

.filter-pill:hover {
  transform: translateY(-1px);
  border-color: rgba(124,58,237,0.25);
  color: #7c3aed;
}

.filter-pill.active {
  background: linear-gradient(135deg, #7c3aed 0%, #a855f7 100%);
  color: white;
  border-color: transparent;
  box-shadow: 0 10px 24px rgba(124, 58, 237, 0.22);
}

.sidebar-card {
  background: rgba(255,255,255,0.92);
  border-radius: 24px;
  padding: 22px;
  border: 1px solid rgba(255,255,255,0.75);
  box-shadow: 0 16px 40px rgba(17, 24, 39, 0.05);
  position: sticky;
  top: 92px;
}

.sidebar-card h3 {
  font-size: 18px;
  color: #111827;
  margin-bottom: 14px;
}

.sidebar-note {
  color: #6b7280;
  font-size: 13px;
  margin-bottom: 18px;
}

.genre-list {
  display: grid;
  gap: 10px;
}

.genre-option {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 12px 14px;
  border-radius: 14px;
  background: #fafafa;
  border: 1px solid transparent;
  color: #374151;
  font-weight: 600;
  text-decoration: none;
  transition: all 0.2s ease;
}

.genre-option:hover {
  border-color: rgba(124,58,237,0.14);
  transform: translateX(2px);
}

.genre-option.active {
  background: #f6f0ff;
  border-color: rgba(124,58,237,0.16);
  color: #7c3aed;
}

.genre-count {
  font-size: 12px;
  color: #9ca3af;
}

.book-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
  gap: 24px;
}

.book-card {
  background: rgba(255,255,255,0.95);
  border-radius: 20px;
  overflow: hidden;
  box-shadow: 0 10px 30px rgba(17,24,39,0.06);
  border: 1px solid rgba(255,255,255,0.8);
  transition: transform 0.25s ease, box-shadow 0.25s ease;
  display: flex;
  flex-direction: column;
  min-height: 100%;
}

.book-card:hover {
  transform: translateY(-6px);
  box-shadow: 0 18px 38px rgba(17,24,39,0.12);
}

.book-cover {
  position: relative;
  height: 260px;
  padding: 18px;
  background: linear-gradient(135deg, #7c3aed 0%, #9f7bff 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;
}

.book-cover::after {
  content: '';
  position: absolute;
  inset: 0;
  background: radial-gradient(circle at top right, rgba(255,255,255,0.18), transparent 45%);
}

.book-cover-mark {
  width: 92px;
  height: 92px;
  border-radius: 28px;
  background: rgba(255,255,255,0.16);
  border: 1px solid rgba(255,255,255,0.26);
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 2rem;
  font-weight: 800;
  backdrop-filter: blur(10px);
  z-index: 1;
}

.rating-badge {
  position: absolute;
  left: 16px;
  top: 16px;
  z-index: 2;
  background: rgba(255,255,255,0.95);
  color: #f59e0b;
  border-radius: 999px;
  padding: 8px 12px;
  font-size: 12px;
  font-weight: 800;
  box-shadow: 0 8px 18px rgba(0,0,0,0.08);
}

.genre-badge {
  position: absolute;
  right: 16px;
  top: 16px;
  z-index: 2;
  background: rgba(255,255,255,0.18);
  color: white;
  border: 1px solid rgba(255,255,255,0.2);
  border-radius: 999px;
  padding: 8px 12px;
  font-size: 12px;
  font-weight: 700;
  backdrop-filter: blur(10px);
}

.book-body {
  padding: 18px;
  display: flex;
  flex-direction: column;
  gap: 10px;
  flex: 1;
}

.book-title {
  font-size: 16px;
  font-weight: 800;
  color: #111827;
  line-height: 1.35;
  min-height: 44px;
}

.book-author {
  color: #6b7280;
  font-size: 14px;
}

.book-meta {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  color: #6b7280;
  font-size: 13px;
}

.view-btn {
  margin-top: auto;
  border-radius: 14px;
  background: linear-gradient(135deg, #7c3aed 0%, #a855f7 100%);
  color: white;
  text-decoration: none;
  text-align: center;
  padding: 12px 14px;
  font-weight: 700;
  box-shadow: 0 10px 22px rgba(124,58,237,0.2);
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.view-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 14px 26px rgba(124,58,237,0.28);
}

.empty-state {
  background: rgba(255,255,255,0.92);
  border-radius: 24px;
  padding: 56px 24px;
  text-align: center;
  color: #6b7280;
  border: 1px dashed rgba(124,58,237,0.18);
}

.pagination {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 8px;
  flex-wrap: wrap;
  margin-top: 30px;
}

.page-link {
  min-width: 42px;
  height: 42px;
  border-radius: 12px;
  background: rgba(255,255,255,0.92);
  border: 1px solid #e5e7eb;
  color: #374151;
  text-decoration: none;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 0 14px;
  font-weight: 700;
  transition: all 0.2s ease;
}

.page-link:hover {
  border-color: rgba(124,58,237,0.3);
  color: #7c3aed;
  transform: translateY(-1px);
}

.page-link.active {
  background: linear-gradient(135deg, #7c3aed 0%, #9f7bff 100%);
  border-color: transparent;
  color: white;
  box-shadow: 0 10px 22px rgba(124,58,237,0.22);
}

.page-link.disabled {
  opacity: 0.45;
  pointer-events: none;
}

.footer-spacer {
  height: 20px;
}

@media (max-width: 1024px) {
  .top-shell {
    grid-template-columns: 1fr;
  }

  .sidebar-card {
    position: static;
  }

  .page-hero {
    flex-direction: column;
  }

  .page-count {
    align-self: flex-start;
  }
}

@media (max-width: 768px) {
  .top-rated-page {
    width: calc(100% - 24px);
    padding-top: 28px;
  }

  .filter-bar-form {
    flex-direction: column;
    align-items: stretch;
  }

  .filter-select {
    width: 100%;
  }

  .book-grid {
    grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
  }
}

@media (max-width: 480px) {
  .search-filter-bar,
  .sidebar-card {
    padding: 16px;
    border-radius: 20px;
  }

  .book-cover {
    height: 220px;
  }

  .book-grid {
    grid-template-columns: 1fr;
  }
}
</style>

<main class="top-rated-page">
  <div class="page-hero">
    <div class="page-copy">
      <h1>Top Rated Books</h1>
      <p>Discover the literary masterpieces loved by our community. Search by title or author, filter by genre, and browse the highest-rated books in a premium layout.</p>
    </div>
    <div class="page-count"><?php echo number_format($total_books); ?> books found</div>
  </div>

  <div class="search-filter-bar">
    <form method="GET" action="" class="filter-bar-form">
      <input type="hidden" name="page" value="1">
      <div class="search-input-wrap">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
          <circle cx="11" cy="11" r="8"></circle>
          <path d="m21 21-4.35-4.35"></path>
        </svg>
        <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search books, authors, genres...">
      </div>
      <select class="filter-select" name="genre" onchange="this.form.submit()">
        <option value="All" <?php echo $genre === 'All' ? 'selected' : ''; ?>>All Genres</option>
        <option value="Fiction" <?php echo $genre === 'Fiction' ? 'selected' : ''; ?>>Fiction</option>
        <option value="Non-Fiction" <?php echo $genre === 'Non-Fiction' ? 'selected' : ''; ?>>Non-Fiction</option>
        <option value="Mystery" <?php echo $genre === 'Mystery' ? 'selected' : ''; ?>>Mystery</option>
        <option value="Sci-Fi" <?php echo $genre === 'Sci-Fi' ? 'selected' : ''; ?>>Sci-Fi</option>
        <option value="Biography" <?php echo $genre === 'Biography' ? 'selected' : ''; ?>>Biography</option>
        <option value="History" <?php echo $genre === 'History' ? 'selected' : ''; ?>>History</option>
      </select>
      <select class="filter-select" name="time_period" onchange="this.form.submit()">
        <option value="all" <?php echo $time_period === 'all' ? 'selected' : ''; ?>>All Time</option>
        <option value="week" <?php echo $time_period === 'week' ? 'selected' : ''; ?>>This Week</option>
        <option value="month" <?php echo $time_period === 'month' ? 'selected' : ''; ?>>This Month</option>
      </select>
      <select class="filter-select" name="sort" onchange="this.form.submit()">
        <option value="rating" <?php echo $sort === 'rating' ? 'selected' : ''; ?>>Sort: Highest Rated</option>
        <option value="most_reviewed" <?php echo $sort === 'most_reviewed' ? 'selected' : ''; ?>>Sort: Most Reviewed</option>
        <option value="newest" <?php echo $sort === 'newest' ? 'selected' : ''; ?>>Sort: Newest</option>
      </select>
    </form>
  </div>

  <section>
    <?php if (count($books) > 0): ?>
      <div class="book-grid">
        <?php foreach ($books as $book):
          $initials = strtoupper(substr(trim($book['title'] ?? 'Book'), 0, 2));
          $rating_value = number_format((float)$book['avg_rating'], 1);
          $review_count = (int)$book['review_count'];
          $book_genre = $book['derived_genre'] ?? 'Fiction';
        ?>
          <article class="book-card">
            <div class="book-cover">
              <div class="rating-badge"><i class="fa-solid fa-star" aria-hidden="true"></i> <?php echo $rating_value; ?></div>
              <div class="genre-badge"><?php echo htmlspecialchars($book_genre); ?></div>
              <div class="book-cover-mark"><?php echo htmlspecialchars($initials); ?></div>
            </div>
            <div class="book-body">
              <div class="book-title"><?php echo htmlspecialchars($book['title']); ?></div>
              <div class="book-author">by <?php echo htmlspecialchars($book['author'] ?? 'Unknown Author'); ?></div>
              <div class="book-meta">
                <span><?php echo $review_count; ?> review<?php echo $review_count === 1 ? '' : 's'; ?></span>
                <span>Top Rated</span>
              </div>
              <a class="view-btn" href="view.php?id=<?php echo (int)$book['id']; ?>">View Details</a>
            </div>
          </article>
        <?php endforeach; ?>
      </div>

      <div class="pagination">
        <?php
        $base_params = [
            'search' => $search,
            'genre' => $genre,
            'time_period' => $time_period,
            'sort' => $sort,
        ];
        $prev_params = $base_params + ['page' => max(1, $page - 1)];
        $next_params = $base_params + ['page' => min($total_pages, $page + 1)];
        ?>

        <a class="page-link <?php echo $page <= 1 ? 'disabled' : ''; ?>" href="?<?php echo htmlspecialchars(http_build_query($prev_params)); ?>">&lsaquo;</a>
        <?php
        $window_start = max(1, $page - 2);
        $window_end = min($total_pages, $page + 2);
        for ($i = $window_start; $i <= $window_end; $i++):
            $page_params = $base_params + ['page' => $i];
        ?>
          <a class="page-link <?php echo $i === $page ? 'active' : ''; ?>" href="?<?php echo htmlspecialchars(http_build_query($page_params)); ?>"><?php echo $i; ?></a>
        <?php endfor; ?>
        <a class="page-link <?php echo $page >= $total_pages ? 'disabled' : ''; ?>" href="?<?php echo htmlspecialchars(http_build_query($next_params)); ?>">&rsaquo;</a>
      </div>
    <?php else: ?>
      <div class="empty-state">
        <h2>No books found</h2>
        <p>Try a different search term or switch the genre filter to All Genres.</p>
      </div>
    <?php endif; ?>
  </section>

  <div class="footer-spacer"></div>
</main>

<?php include("../includes/footer.php"); ?>
