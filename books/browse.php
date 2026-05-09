<?php
include("../includes/header.php");
require_once("../config/db.php");
require_once("../includes/helpers.php");

$genre = isset($_GET['genre']) ? $_GET['genre'] : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'popularity';
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;

$items_per_page = 12;
$offset = ($page - 1) * $items_per_page;

$where = "WHERE 1=1";
if ($genre) {
    $where .= " AND b.genre = '" . $conn->real_escape_string($genre) . "'";
}

$count_sql = "SELECT COUNT(*) as total FROM books b $where";
$count_result = $conn->query($count_sql);
$total_books = 0;
if ($count_result) {
    $count_row = $count_result->fetch_assoc();
    $total_books = $count_row['total'] ?? 0;
}
$total_pages = $total_books > 0 ? ceil($total_books / $items_per_page) : 1;

$order_by = "b.id DESC";
if ($sort === 'rating') {
    $order_by = "avg_rated DESC";
}

$sql = "SELECT b.*, 
        COALESCE(AVG(r.rating), 0) as avg_rated,
        COUNT(r.id) as review_count
        FROM books b 
        LEFT JOIN reviews r ON b.id = r.book_id
        $where
        GROUP BY b.id
        ORDER BY $order_by
        LIMIT $items_per_page OFFSET $offset";

$books_result = $conn->query($sql);
$books = [];
if ($books_result) {
    while ($book = $books_result->fetch_assoc()) {
        $books[] = $book;
    }
}
?>

<div class="books-page">

  <!-- SIDEBAR -->
  <aside class="sidebar">
    <h3>Genres</h3>

    <form method="GET">
      <label>
        <input type="radio" name="genre" value="" <?php echo ($genre === '') ? 'checked' : ''; ?>>
        All Genres
      </label>
      <?php foreach (['Fiction', 'Non-Fiction', 'Mystery', 'Sci-Fi', 'Biography', 'History'] as $g): ?>
      <label>
        <input type="radio" name="genre" value="<?php echo $g; ?>" <?php echo ($genre === $g) ? 'checked' : ''; ?> onchange="this.form.submit()">
        <?php echo $g; ?>
      </label>
      <?php endforeach; ?>
    </form>
  </aside>

  <!-- MAIN CONTENT -->
  <main class="books-content">

    <!-- TOP BAR -->
    <div class="books-header">
      <div>
        <h1>Browse Books</h1>
      </div>

      <div class="sort-box">
        <span>Sort by</span>
        <form method="GET" style="display: inline;">
          <input type="hidden" name="genre" value="<?php echo htmlspecialchars($genre); ?>">
          <select name="sort" onchange="this.form.submit()">
            <option value="popularity" <?php echo ($sort === 'popularity') ? 'selected' : ''; ?>>Popularity</option>
            <option value="rating" <?php echo ($sort === 'rating') ? 'selected' : ''; ?>>Top Rated</option>
            <option value="newest" <?php echo ($sort === 'newest') ? 'selected' : ''; ?>>Newest</option>
          </select>
        </form>
      </div>
    </div>

    <!-- BOOK GRID -->
    <?php if (count($books) > 0): ?>
    <div class="books-grid">
      <?php foreach ($books as $book): ?>
      <div class="book-card">
        <div style="width: 100%; height: 320px; background: linear-gradient(135deg, #7c5cff 0%, #a855f7 100%); display: flex; align-items: center; justify-content: center; font-size: 48px; color: white;">
          <i class="fa-solid fa-book" aria-hidden="true"></i>
        </div>

        <div class="book-info">
          <div class="book-rating">
            <i class="fa-solid fa-star"></i>
            <?php echo round($book['avg_rated'], 1); ?>
          </div>

          <h3><?php echo htmlspecialchars($book['title']); ?></h3>
          <p><?php echo htmlspecialchars($book['author']); ?></p>

          <a href="/book-review/books/view.php?id=<?php echo $book['id']; ?>" class="btn">View Details</a>
        </div>
      </div>
      <?php endforeach; ?>
    </div>

    <?php if ($total_pages > 1): ?>
    <div class="pagination">
      <?php if ($page > 1): ?>
        <a href="?page=1&genre=<?php echo urlencode($genre); ?>&sort=<?php echo $sort; ?>">&laquo;</a>
        <a href="?page=<?php echo $page - 1; ?>&genre=<?php echo urlencode($genre); ?>&sort=<?php echo $sort; ?>">&lsaquo;</a>
      <?php else: ?>
        <span class="disabled">&laquo;</span>
        <span class="disabled">&lsaquo;</span>
      <?php endif; ?>

      <?php
      $start = max(1, $page - 2);
      $end = min($total_pages, $page + 2);
      for ($i = $start; $i <= $end; $i++):
      ?>
        <?php if ($i === $page): ?>
          <span class="active"><?php echo $i; ?></span>
        <?php else: ?>
          <a href="?page=<?php echo $i; ?>&genre=<?php echo urlencode($genre); ?>&sort=<?php echo $sort; ?>"><?php echo $i; ?></a>
        <?php endif; ?>
      <?php endfor; ?>

      <?php if ($page < $total_pages): ?>
        <a href="?page=<?php echo $page + 1; ?>&genre=<?php echo urlencode($genre); ?>&sort=<?php echo $sort; ?>">&rsaquo;</a>
        <a href="?page=<?php echo $total_pages; ?>&genre=<?php echo urlencode($genre); ?>&sort=<?php echo $sort; ?>">&raquo;</a>
      <?php else: ?>
        <span class="disabled">&rsaquo;</span>
        <span class="disabled">&raquo;</span>
      <?php endif; ?>
    </div>
    <?php endif; ?>

    <?php else: ?>
    <div style="text-align: center; padding: 60px 20px; color: #6b7280;">
      <h2>No books found</h2>
      <p>Try adjusting your filters or search terms</p>
    </div>
    <?php endif; ?>

  </main>

</div>

<?php include("../includes/footer.php"); ?>
