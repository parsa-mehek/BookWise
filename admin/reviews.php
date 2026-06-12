<?php
session_start();
include("../config/db.php");
include("../includes/helpers.php");

// If the reviews.status column does not exist, moderation features are unavailable.
if (!column_exists($conn, 'reviews', 'status')) {
  include("../includes/header.php");
  echo '<div class="container" style="max-width:1100px;margin:44px auto;">';
  echo '<h1>Pending Reviews</h1>';
  echo '<p style="color:#b91c1c">Review moderation is disabled because the <strong>reviews.status</strong> column is missing in the database.</p>';
  echo '<p>Please run the required database migration to add the status column, then reload this page.</p>';
  echo '</div>';
  include("../includes/footer.php");
  exit();
}

// Basic admin guard: set $_SESSION['is_admin']=1 for access during development
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header('Location: ../auth/login.php');
    exit();
}

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && isset($_POST['review_id'])) {
        $action = $_POST['action'];
        $review_id = (int)$_POST['review_id'];

        if ($action === 'approve') {
            $app_sql = "UPDATE reviews SET status='approved' WHERE id=?";
            $app_stmt = $conn->prepare($app_sql);
            $app_stmt->bind_param("i", $review_id);
            if ($app_stmt->execute()) {
                // recalc average for the book
                $b_sql = "SELECT book_id FROM reviews WHERE id = ? LIMIT 1";
                $b_stmt = $conn->prepare($b_sql);
                $b_stmt->bind_param("i", $review_id);
                $b_stmt->execute();
                $b_row = $b_stmt->get_result()->fetch_assoc();
                $book_id = (int)$b_row['book_id'];

                $updateAvg = "UPDATE books b SET average_rating = (
                    SELECT IFNULL(AVG(r.rating),0) FROM reviews r WHERE r.book_id = ? AND r.status = 'approved'
                ) WHERE b.id = ?";
                $u_stmt = $conn->prepare($updateAvg);
                $u_stmt->bind_param("ii", $book_id, $book_id);
                $u_stmt->execute();
            }
        }

        if ($action === 'delete') {
            // fetch book_id for recalculation
            $bk_sql = "SELECT book_id FROM reviews WHERE id = ? LIMIT 1";
            $bk_stmt = $conn->prepare($bk_sql);
            $bk_stmt->bind_param("i", $review_id);
            $bk_stmt->execute();
            $bk_row = $bk_stmt->get_result()->fetch_assoc();
            $book_id = (int)($bk_row['book_id'] ?? 0);

            $del_sql = "DELETE FROM reviews WHERE id = ?";
            $del_stmt = $conn->prepare($del_sql);
            $del_stmt->bind_param("i", $review_id);
            if ($del_stmt->execute() && $book_id > 0) {
                $updateAvg = "UPDATE books b SET average_rating = (
                    SELECT IFNULL(AVG(r.rating),0) FROM reviews r WHERE r.book_id = ? AND r.status = 'approved'
                ) WHERE b.id = ?";
                $u_stmt = $conn->prepare($updateAvg);
                $u_stmt->bind_param("ii", $book_id, $book_id);
                $u_stmt->execute();
            }
        }
    }
    header('Location: reviews.php');
    exit();
}

// Fetch pending reviews
$sql = "SELECT r.id, r.user_id, r.book_id, r.comment, r.rating, u.name AS user_name, b.title AS book_title
        FROM reviews r
        JOIN users u ON r.user_id = u.id
        JOIN books b ON r.book_id = b.id
        WHERE r.status = 'pending'
        ORDER BY r.id DESC";
$result = $conn->query($sql);

include("../includes/header.php");
?>
<div class="container" style="max-width:1100px;margin:44px auto;">
  <h1>Pending Reviews</h1>
  <?php if ($result && $result->num_rows > 0): ?>
    <table style="width:100%;border-collapse:collapse;margin-top:18px">
      <thead>
        <tr>
          <th style="text-align:left;padding:8px;border-bottom:1px solid #e6e6e6">ID</th>
          <th style="text-align:left;padding:8px;border-bottom:1px solid #e6e6e6">User</th>
          <th style="text-align:left;padding:8px;border-bottom:1px solid #e6e6e6">Book</th>
          <th style="text-align:left;padding:8px;border-bottom:1px solid #e6e6e6">Rating</th>
          <th style="text-align:left;padding:8px;border-bottom:1px solid #e6e6e6">Comment</th>
          <th style="padding:8px;border-bottom:1px solid #e6e6e6">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
          <td style="padding:8px;border-bottom:1px solid #f3f3f3"><?php echo (int)$row['id']; ?></td>
          <td style="padding:8px;border-bottom:1px solid #f3f3f3"><?php echo sanitize($row['user_name']); ?></td>
          <td style="padding:8px;border-bottom:1px solid #f3f3f3"><?php echo sanitize($row['book_title']); ?></td>
          <td style="padding:8px;border-bottom:1px solid #f3f3f3"><?php echo (int)$row['rating']; ?></td>
          <td style="padding:8px;border-bottom:1px solid #f3f3f3"><?php echo nl2br(sanitize($row['comment'])); ?></td>
          <td style="padding:8px;border-bottom:1px solid #f3f3f3">
            <form method="POST" style="display:inline-block;margin-right:6px">
              <input type="hidden" name="review_id" value="<?php echo (int)$row['id']; ?>">
              <input type="hidden" name="action" value="approve">
              <button type="submit" class="btn-primary">Approve</button>
            </form>
            <form method="POST" style="display:inline-block">
              <input type="hidden" name="review_id" value="<?php echo (int)$row['id']; ?>">
              <input type="hidden" name="action" value="delete">
              <button type="submit" class="btn-danger">Delete</button>
            </form>
          </td>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  <?php else: ?>
    <p>No pending reviews.</p>
  <?php endif; ?>
</div>

<?php include("../includes/footer.php"); ?>
