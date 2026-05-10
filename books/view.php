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

$avg_sql = "SELECT AVG(rating) AS avg_rating, COUNT(*) AS total_reviews FROM reviews WHERE book_id = ?";
$avg_stmt = $conn->prepare($avg_sql);
$avg_stmt->bind_param("i", $book['id']);
$avg_stmt->execute();
$avg_result = $avg_stmt->get_result()->fetch_assoc();
$avg_rating = $avg_result['avg_rating'];
$total_reviews = $avg_result['total_reviews'];
?>

<div class="card">
  <h2><?php echo sanitize($book['title']); ?></h2>
  <p><b>Author:</b> <?php echo sanitize($book['author']); ?></p>
  <p><?php echo sanitize($book['description']); ?></p>
  <p><b>Rating:</b> 
  <?php 
    if ($avg_rating) {
      echo str_repeat('<i class="fa-solid fa-star" aria-hidden="true"></i>', round($avg_rating)) . " (" . round($avg_rating, 1) . "/5 - " . $total_reviews . " reviews)";
    } else {
      echo "No ratings yet";
    }
  ?>
  </p>
</div>

<?php
if (isset($_SESSION['user_id'])) {
?>
<div class="card">
  <h3>Add Review</h3>
  <form action="/book-review/reviews/add_review.php" method="POST">
    <input type="hidden" name="book_id" value="<?php echo $book['id']; ?>">
    
    <select name="rating" required>
      <option value="">Select rating</option>
      <option value="1">1 star</option>
      <option value="2">2 stars</option>
      <option value="3">3 stars</option>
      <option value="4">4 stars</option>
      <option value="5">5 stars</option>
    </select>
    
    <textarea name="comment" placeholder="Write review" required></textarea>
    <button>Submit</button>
  </form>
</div>
<?php
} else {
  echo '<div class="card"><p>Please <a href="/book-review/auth/login.php">login</a> to add a review.</p></div>';
}
?>

<div class="card">
  <h3>Reviews</h3>
  <?php
  $sql = "SELECT reviews.*, users.name FROM reviews JOIN users ON reviews.user_id = users.id WHERE book_id = ? ORDER BY reviews.id DESC";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("i", $book['id']);
  $stmt->execute();
  $result = $stmt->get_result();
  
  if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
      echo "<div style='border-bottom:1px solid #ddd; padding: 10px 0;'>";
      echo "<b>" . sanitize($row['name']) . "</b> - ";
      echo str_repeat('<i class="fa-solid fa-star" aria-hidden="true"></i>', $row['rating']);
      echo "<p>" . sanitize($row['comment']) . "</p>";
      
      if(isset($_SESSION['user_id']) && $row['user_id'] == $_SESSION['user_id']) {
        echo "<a href='/book-review/reviews/delete_review.php?id=" . $row['id'] . "' onclick=\"return confirm('Delete?');\">Delete</a>";
      }
      echo "</div>";
    }
  } else {
    echo "<p>No reviews yet.</p>";
  }
  ?>
</div>

<?php include("../includes/footer.php"); ?>
