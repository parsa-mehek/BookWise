<?php
session_start();
include("../config/db.php");

// login check
if (!isset($_SESSION['user_id'])) {
    die("Please login first!");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_SESSION['user_id'];
    $book_id = $_POST['book_id'];

    // Check if user already reviewed this book
    $check_sql = "SELECT * FROM reviews WHERE user_id=? AND book_id=?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("ii", $user_id, $book_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows > 0) {
        echo "You already reviewed this book! <a href='../books/view.php?id=" . $book_id . "'>Go back</a>";
        die();
    }

    // No duplicate, proceed with insert
    $rating = $_POST['rating'];
    $comment = $_POST['comment'];

    $sql = "INSERT INTO reviews (user_id, book_id, rating, comment) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iiis", $user_id, $book_id, $rating, $comment);

    if ($stmt->execute()) {
        echo "Review added! <a href='../books/view.php?id=" . $book_id . "'>View book</a>";
    } else {
        echo "Error adding review!";
    }
}
?>
