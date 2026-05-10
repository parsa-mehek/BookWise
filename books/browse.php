<?php
require_once("../config/db.php");
require_once("../includes/helpers.php");

$include_bootstrap = true;

$column_exists = function ($table, $column) use ($conn) {
    $table_escaped = $conn->real_escape_string($table);
    $column_escaped = $conn->real_escape_string($column);
    $check = $conn->query("SHOW COLUMNS FROM {$table_escaped} LIKE '{$column_escaped}'");
    return $check && $check->num_rows > 0;
};

$has_genre_column = $column_exists('books', 'genre');
$has_slug_column = $column_exists('books', 'slug');
$has_cover_image_column = $column_exists('books', 'cover_image');

$genre_options = [
    'fiction' => 'Fiction',
    'fantasy' => 'Fantasy',
    'mystery' => 'Mystery',
    'self-help' => 'Self-help',
    'science' => 'Science',
];

$genre_slug_input = strtolower(trim($_GET['genre_slug'] ?? ($_GET['genre'] ?? '')));
$selected_genre_slug = array_key_exists($genre_slug_input, $genre_options) ? $genre_slug_input : '';
$selected_genre_name = $selected_genre_slug !== '' ? $genre_options[$selected_genre_slug] : '';

$sort = strtolower(trim($_GET['sort'] ?? 'popularity'));
$allowed_sorts = ['popularity', 'rating', 'newest'];
if (!in_array($sort, $allowed_sorts, true)) {
    $sort = 'popularity';
}

$page = max(1, (int)($_GET['page'] ?? 1));
$items_per_page = 12;
$offset = ($page - 1) * $items_per_page;

$genre_expr = $has_genre_column
    ? "COALESCE(NULLIF(TRIM(b.genre), ''), 'Fiction')"
    : "CASE
        WHEN LOWER(CONCAT_WS(' ', b.title, b.author, COALESCE(b.description, ''))) REGEXP 'fantasy|magic|wizard|dragon|kingdom|myth' THEN 'Fantasy'
        WHEN LOWER(CONCAT_WS(' ', b.title, b.author, COALESCE(b.description, ''))) REGEXP 'mystery|detective|crime|case|thriller|secret' THEN 'Mystery'
        WHEN LOWER(CONCAT_WS(' ', b.title, b.author, COALESCE(b.description, ''))) REGEXP 'self-help|self help|mindset|habit|productivity|success' THEN 'Self-help'
        WHEN LOWER(CONCAT_WS(' ', b.title, b.author, COALESCE(b.description, ''))) REGEXP 'science|physics|biology|chemistry|space|quantum' THEN 'Science'
        ELSE 'Fiction'
    END";

$where = "WHERE 1=1";
if ($selected_genre_name !== '') {
    $safe_genre = $conn->real_escape_string($selected_genre_name);
    $where .= " AND {$genre_expr} = '{$safe_genre}'";
}

$count_sql = "SELECT COUNT(*) AS total FROM books b {$where}";
$count_result = $conn->query($count_sql);
$total_books = 0;
if ($count_result) {
    $count_row = $count_result->fetch_assoc();
    $total_books = (int)($count_row['total'] ?? 0);
}

$total_pages = max(1, (int)ceil($total_books / $items_per_page));
if ($page > $total_pages) {
    $page = $total_pages;
    $offset = ($page - 1) * $items_per_page;
}

switch ($sort) {
    case 'rating':
        $order_by = "avg_rated DESC, review_count DESC, b.id DESC";
        break;
    case 'newest':
        $order_by = "b.id DESC";
        break;
    default:
        $order_by = "review_count DESC, avg_rated DESC, b.id DESC";
        break;
}

$slug_select = $has_slug_column ? "b.slug AS book_slug" : "'' AS book_slug";
$cover_select = $has_cover_image_column ? "COALESCE(b.cover_image, '') AS cover_image" : "'' AS cover_image";

$sql = "SELECT
            b.id,
            b.title,
            b.author,
            b.description,
            {$genre_expr} AS genre_name,
            {$slug_select},
            {$cover_select},
            COALESCE(AVG(r.rating), 0) AS avg_rated,
            COUNT(r.id) AS review_count
        FROM books b
        LEFT JOIN reviews r ON b.id = r.book_id
        {$where}
        GROUP BY b.id
        ORDER BY {$order_by}
        LIMIT {$items_per_page} OFFSET {$offset}";

$books_result = $conn->query($sql);
$books = [];
if ($books_result) {
    while ($book = $books_result->fetch_assoc()) {
        $books[] = $book;
    }
}

$base_path = $selected_genre_slug !== ''
    ? '/book-review/genre/' . urlencode($selected_genre_slug)
    : '/book-review/books';

$build_url = function ($path, $params = []) {
    $filtered = [];
    foreach ($params as $key => $value) {
        if ($value !== '' && $value !== null) {
            $filtered[$key] = $value;
        }
    }
    $query = http_build_query($filtered);
    return $query ? ($path . '?' . $query) : $path;
};

include("../includes/header.php");
?>

<style>
:root {
  --primary: #7C3AED;
  --primary-light: #F5F3FF;
  --card-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
  --card-shadow-hover: 0 8px 24px rgba(124, 58, 237, 0.15);
}

.browse-container {
  display: grid;
  grid-template-columns: 260px 1fr;
  gap: 40px;
  max-width: calc(1280px + 120px);
  width: 100%;
  margin: 0 auto;
  padding: 60px 60px;
  box-sizing: border-box;
}

.page-header {
  grid-column: 1 / -1;
  margin-bottom: 24px;
}

.page-header h1 {
  font-size: 2rem;
  font-weight: 800;
  color: #1f2937;
  margin-bottom: 12px;
}

.page-header p {
  font-size: 1rem;
  color: #6B7280;
  line-height: 1.6;
}

.sidebar {
  height: fit-content;
  position: sticky;
  top: 100px;
}

.sidebar-title {
  font-size: 0.875rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  color: #6B7280;
  margin-bottom: 16px;
}

.genre-list {
  list-style: none;
  padding: 0;
  margin: 0;
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.genre-item {
  margin: 0;
}

.genre-link {
  display: flex;
  align-items: center;
  padding: 12px 16px;
  border-radius: 10px;
  border: 1.5px solid transparent;
  background: #FFFFFF;
  color: #4B5563;
  font-weight: 500;
  font-size: 0.95rem;
  text-decoration: none;
  transition: all 0.2s ease;
  cursor: pointer;
}

.genre-link:hover {
  background: var(--primary-light);
  color: var(--primary);
  border-color: var(--primary);
}

.genre-link.active {
  background: var(--primary);
  color: #FFFFFF;
  border-color: var(--primary);
  font-weight: 600;
}

.content-area {
  display: flex;
  flex-direction: column;
  gap: 32px;
}

.books-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
  gap: 28px;
  width: 100%;
}

.book-card {
  display: flex;
  flex-direction: column;
  background: #FFFFFF;
  border-radius: 14px;
  overflow: hidden;
  box-shadow: var(--card-shadow);
  transition: all 0.3s ease;
  border: 1px solid #F3F4F6;
}

.book-card:hover {
  transform: translateY(-6px);
  box-shadow: var(--card-shadow-hover);
  border-color: rgba(124, 58, 237, 0.2);
}

.book-cover {
  width: 100%;
  height: 240px;
  background: linear-gradient(135deg, #153677 0%, #4e085f 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  color: #FFFFFF;
  font-size: 3rem;
  flex-shrink: 0;
}

.book-cover img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.book-body {
  padding: 16px;
  flex: 1;
  display: flex;
  flex-direction: column;
}

.book-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 8px;
  margin-bottom: 12px;
}

.genre-badge {
  display: inline-block;
  padding: 4px 10px;
  background: var(--primary-light);
  color: var(--primary);
  border-radius: 20px;
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.3px;
  white-space: nowrap;
}

.book-rating {
  display: flex;
  gap: 4px;
  align-items: center;
  justify-content: center;
  padding: 4px 8px;
  background: var(--primary-light);
  border-radius: 6px;
  font-weight: 600;
  font-size: 0.85rem;
  color: var(--primary);
}

.book-rating i {
  font-size: 0.8rem;
}

.book-title {
  font-size: 0.95rem;
  font-weight: 700;
  color: #1F2937;
  line-height: 1.4;
  margin-bottom: 6px;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.book-author {
  font-size: 0.85rem;
  color: #6B7280;
  margin-bottom: 8px;
}

.book-reviews {
  font-size: 0.8rem;
  color: #9CA3AF;
  margin-bottom: 12px;
}

.book-footer {
  margin-top: auto;
}

.btn-view {
  width: 100%;
  padding: 10px 16px;
  background: var(--primary);
  color: #FFFFFF;
  border: none;
  border-radius: 8px;
  font-weight: 600;
  font-size: 0.85rem;
  text-decoration: none;
  display: inline-flex;
  justify-content: center;
  align-items: center;
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn-view:hover {
  background: #6D28D9;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(124, 58, 237, 0.3);
  color: #FFFFFF;
  text-decoration: none;
}

.no-books-state {
  grid-column: 1 / -1;
  text-align: center;
  padding: 80px 20px;
  color: #6B7280;
}

.no-books-state h2 {
  font-size: 1.5rem;
  font-weight: 700;
  color: #1F2937;
  margin-bottom: 8px;
}

.pagination {
  display: flex;
  gap: 8px;
  justify-content: center;
  margin-top: 40px;
}

@media (max-width: 1024px) {
  .browse-container {
    max-width: calc(100% + 80px);
    padding: 40px 40px;
  }

  .sidebar {
    position: static;
  }

  .books-grid {
    grid-template-columns: repeat(auto-fill, minmax(170px, 1fr));
  }
}

@media (max-width: 720px) {
  .browse-container {
    max-width: 100%;
    padding: 30px 12px;
    gap: 20px;
  }

  .page-header h1 {
    font-size: 1.75rem;
  }

  .genre-list {
    flex-direction: row;
    flex-wrap: wrap;
  }

  .genre-item {
    flex: 0 0 calc(50% - 4px);
  }

  .books-grid {
    grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
    gap: 16px;
  }

  .sidebar {
    top: 80px;
  }
}

@media (max-width: 480px) {
  .browse-container {
    max-width: 100%;
    padding: 20px 12px;
  }

  .page-header h1 {
    font-size: 1.5rem;
  }

  .books-grid {
    grid-template-columns: 1fr;
  }
}


</style>

<div class="browse-container">
  <div class="page-header">
    <h1>Browse Books</h1>
    <p><?php echo $selected_genre_name ? 'Discover amazing '.htmlspecialchars($selected_genre_name).' books' : 'Discover amazing books from all genres'; ?></p>
  </div>

  <aside class="sidebar">
    <h3 class="sidebar-title">Genres</h3>
    <ul class="genre-list">
      <li class="genre-item"><a class="genre-link <?php echo $selected_genre_slug === '' ? 'active' : ''; ?>" href="<?php echo htmlspecialchars($build_url('/book-review/books', ['sort'=>$sort])); ?>">All</a></li>
      <?php foreach($genre_options as $slug=>$name): ?>
        <li class="genre-item"><a class="genre-link <?php echo $selected_genre_slug === $slug ? 'active' : ''; ?>" href="<?php echo htmlspecialchars($build_url('/book-review/genre/'.$slug,['sort'=>$sort])); ?>"><?php echo htmlspecialchars($name); ?></a></li>
      <?php endforeach; ?>
    </ul>
  </aside>

  <div class="content-area">
    <?php if(count($books) > 0): ?>
      <div class="books-grid">
        <?php foreach($books as $book):
            $book_slug = trim((string)($book['book_slug'] ?? '')) ?: slugify($book['title']);
            $book_url = '/book-review/books/'.urlencode($book_slug);
            $cover = trim((string)($book['cover_image'] ?? ''));
            $avg_rating = round((float)$book['avg_rated'],1);
            $review_count = (int)($book['review_count'] ?? 0);
            $genre_badge = htmlspecialchars($book['genre_name'] ?? 'Fiction');
        ?>
          <div class="book-card">
            <div class="book-cover"><?php if($cover): ?><img src="<?php echo htmlspecialchars($cover); ?>" alt="<?php echo htmlspecialchars($book['title']); ?> cover"><?php else: ?><i class="fa-solid fa-book"></i><?php endif; ?></div>
            <div class="book-body">
              <div class="book-header"><span class="genre-badge"><?php echo $genre_badge; ?></span><div class="book-rating"><i class="fa-solid fa-star"></i><?php echo number_format($avg_rating,1); ?></div></div>
              <div><h3 class="book-title"><?php echo htmlspecialchars($book['title']); ?></h3><p class="book-author">by <?php echo htmlspecialchars($book['author']); ?></p><p class="book-reviews"><?php echo $review_count; ?> review<?php echo $review_count!==1?'s':''; ?></p></div>
              <div style="margin-top:auto"><a class="btn-view" href="<?php echo htmlspecialchars($book_url); ?>">View Details</a></div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>

      <?php if($total_pages>1): $start = max(1,$page-2); $end = min($total_pages,$page+2); ?>
        <nav aria-label="pagination"><ul class="pagination" style="display:flex; gap:8px; justify-content:center; margin-top:24px">
          <li class="page-item <?php echo $page<=1?'disabled':''; ?>"><a class="page-link" href="<?php echo htmlspecialchars($build_url($base_path,['sort'=>$sort,'page'=>1])); ?>">&laquo;</a></li>
          <li class="page-item <?php echo $page<=1?'disabled':''; ?>"><a class="page-link" href="<?php echo htmlspecialchars($build_url($base_path,['sort'=>$sort,'page'=>max(1,$page-1)])); ?>">&lsaquo;</a></li>
          <?php for($i=$start;$i<=$end;$i++): ?><li class="page-item <?php echo $i===$page?'active':''; ?>"><a class="page-link" href="<?php echo htmlspecialchars($build_url($base_path,['sort'=>$sort,'page'=>$i])); ?>"><?php echo $i; ?></a></li><?php endfor; ?>
          <li class="page-item <?php echo $page>=$total_pages?'disabled':''; ?>"><a class="page-link" href="<?php echo htmlspecialchars($build_url($base_path,['sort'=>$sort,'page'=>min($total_pages,$page+1)])); ?>">&rsaquo;</a></li>
          <li class="page-item <?php echo $page>=$total_pages?'disabled':''; ?>"><a class="page-link" href="<?php echo htmlspecialchars($build_url($base_path,['sort'=>$sort,'page'=>$total_pages])); ?>">&raquo;</a></li>
        </ul></nav>
      <?php endif; ?>

    <?php else: ?>
      <div class="no-books-state"><h2>No books found</h2><p>Try another genre filter.</p></div>
    <?php endif; ?>
  </div>

</div>

<?php include("../includes/footer.php"); ?>
