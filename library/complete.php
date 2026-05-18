<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: /book-review/auth/login.php');
    exit();
}

include('../config/db.php');

$user_id = (int)$_SESSION['user_id'];
$book_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($book_id > 0) {
    $sql = "UPDATE user_library SET status = 'completed', completed_at = NOW() WHERE user_id = ? AND book_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ii', $user_id, $book_id);
    $stmt->execute();
}

header('Location: /book-review/user/my-library.php');
exit();
