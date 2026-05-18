<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include("../config/db.php");

// login check
if (!isset($_SESSION['user_id'])) {
    die("Please login first!");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = (int) $_SESSION['user_id'];
    $book_id = (int) ($_POST['book_id'] ?? 0);

    // Check if user already reviewed this book
    $check_sql = "SELECT * FROM reviews WHERE user_id=? AND book_id=?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("ii", $user_id, $book_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows > 0) {
        $book_view_url = '../books/view.php?id=' . $book_id;
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Already Reviewed</title>
            <link rel="stylesheet" href="../assets/css/style.css">
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
        </head>
        <body>
        <div class="review-success-page">
            <div class="review-success-card">
                <div class="warning-icon">
                    <i class="fa-solid fa-star" aria-hidden="true"></i>
                </div>
                <h1>Already Reviewed</h1>
                <p>You have already submitted a review for this book.</p>
                <div class="success-actions">
                    <a href="<?php echo htmlspecialchars($book_view_url, ENT_QUOTES, 'UTF-8'); ?>" class="primary-btn">Back To Book</a>
                </div>
            </div>
        </div>
        </body>
        </html>
        <?php
        exit();
    }

    $rating = (int) ($_POST['rating'] ?? 0);
    $comment = trim($_POST['comment'] ?? '');

    $sql = "INSERT INTO reviews (user_id, book_id, rating, comment) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iiis", $user_id, $book_id, $rating, $comment);

    if ($stmt->execute()) {
        $updateAvg = "\nUPDATE books b\nSET average_rating = (\n    SELECT AVG(r.rating)\n    FROM reviews r\n    WHERE r.book_id = b.id\n)\nWHERE b.id = '" . $book_id . "'\n";
        mysqli_query($conn, $updateAvg);

        $book_view_url = '../books/view.php?id=' . $book_id;
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Review Submitted</title>
            <meta http-equiv="refresh" content="5;url=<?php echo htmlspecialchars($book_view_url, ENT_QUOTES, 'UTF-8'); ?>">
            <link rel="stylesheet" href="../assets/css/style.css">
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
        </head>
        <body>
        <div class="review-success-page">
            <div class="review-success-card">
                <div class="success-icon">
                    <i class="fa-solid fa-circle-check" aria-hidden="true"></i>
                </div>
                <h1>Review Submitted!</h1>
                <p>Your review has been posted successfully. Thank you for sharing your feedback.</p>
                <div class="success-actions">
                    <a href="<?php echo htmlspecialchars($book_view_url, ENT_QUOTES, 'UTF-8'); ?>" class="primary-btn">Back To Book</a>
                    <a href="../books/index.php" class="secondary-btn">Browse Books</a>
                </div>
            </div>
        </div>
        </body>
        </html>
        <?php
        exit();
    }

    echo "Error adding review!";
    exit();
}
?>
