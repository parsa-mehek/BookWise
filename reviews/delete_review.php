<?php
session_start();
include("../config/db.php");

if (!isset($_SESSION['user_id'])) {
    die("Login required!");
}

$review_id = $_GET['id'];
$user_id = $_SESSION['user_id'];

// Verify review belongs to logged-in user before deleting
$verify_sql = "SELECT book_id FROM reviews WHERE id=? AND user_id=?";
$verify_stmt = $conn->prepare($verify_sql);
$verify_stmt->bind_param("ii", $review_id, $user_id);
$verify_stmt->execute();
$verify_result = $verify_stmt->get_result();

if ($verify_result->num_rows == 0) {
    die("You can only delete your own reviews!");
}

$verify_row = $verify_result->fetch_assoc();
$book_id = $verify_row['book_id'];

// Delete the review
$sql = "DELETE FROM reviews WHERE id=? AND user_id=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $review_id, $user_id);

if ($stmt->execute()) {
    echo "Review deleted! <a href='../books/view.php?id=" . $book_id . "'>Go back</a>";
} else {
    echo "Error deleting review!";
}
