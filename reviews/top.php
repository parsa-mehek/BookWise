<?php
include("../includes/header.php");
include("../config/db.php");
include("../includes/helpers.php");

$page_title = "Top Rated Books";
?>

<h2>🏆 Top Rated Books</h2>

<?php
// Get top rated books with at least 1 review
$sql = "SELECT books.*, AVG(reviews.rating) AS avg_rating, COUNT(reviews.id) AS review_count
        FROM books
        JOIN reviews ON books.id = reviews.book_id
        GROUP BY books.id
        ORDER BY avg_rating DESC
        LIMIT 10";

$result = $conn->query($sql);

if ($result->num_rows > 0) {
    echo '<div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px;">';
    
    $rank = 1;
    while($row = $result->fetch_assoc()) {
        $slug = slugify($row['title']);
        $avg_rating = round($row['avg_rating'], 1);
        
        echo '<div class="card">';
        echo '<h3>🥇 #' . $rank . ' - ' . sanitize($row['title']) . '</h3>';
        echo '<p><strong>Author:</strong> ' . sanitize($row['author']) . '</p>';
        echo '<p class="rating">' . displayStars($row['avg_rating']) . ' - ' . $row['review_count'] . ' reviews</p>';
        echo '<a href="../book/' . $row['id'] . '/' . $slug . '" class="btn">View Details</a>';
        echo '</div>';
        
        $rank++;
    }
    
    echo '</div>';
} else {
    echo '<p style="text-align: center; color: #666;">No rated books yet. Be the first to rate!</p>';
}
?>

<?php include("../includes/footer.php"); ?>
