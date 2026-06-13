<?php
session_start();
include("../config/db.php");
include("../includes/helpers.php");

if (!isset($_SESSION['user_id'])) {
    header('Location: /book-review/auth/login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    render_action_notice_page(
        'Comment Rejected',
        'Invalid Request',
        'Comments must be submitted from a review form.',
        '/book-review/books/index.php',
        'Browse Books',
        '/book-review/user/profile.php',
        'Profile',
        true
    );
}

$userId = (int) $_SESSION['user_id'];
$reviewId = (int) ($_POST['review_id'] ?? 0);
$comment = trim((string) ($_POST['comment'] ?? ''));

if ($reviewId <= 0) {
    render_action_notice_page(
        'Comment Rejected',
        'Missing Review',
        'The review you tried to comment on could not be found.',
        '/book-review/books/index.php',
        'Browse Books',
        '/book-review/user/profile.php',
        'Profile',
        true
    );
}

if ($comment === '') {
    render_action_notice_page(
        'Comment Rejected',
        'Empty Comment',
        'Please write a comment before submitting.',
        '/book-review/books/index.php',
        'Browse Books',
        '/book-review/user/profile.php',
        'Profile',
        true
    );
}

if (!table_exists($conn, 'review_comments')) {
    render_action_notice_page(
        'Comment Rejected',
        'Comments Unavailable',
        'The comment system is not available yet.',
        '/book-review/books/index.php',
        'Browse Books',
        '/book-review/user/profile.php',
        'Profile',
        true
    );
}

$reviewSql = 'SELECT r.id, r.book_id, r.status, b.slug, b.title
              FROM reviews r
              INNER JOIN books b ON b.id = r.book_id
              WHERE r.id = ?
              LIMIT 1';
$reviewStmt = $conn->prepare($reviewSql);
$reviewStmt->bind_param('i', $reviewId);
$reviewStmt->execute();
$reviewRow = $reviewStmt->get_result()->fetch_assoc();

if (!$reviewRow) {
    render_action_notice_page(
        'Comment Rejected',
        'Review Not Found',
        'The review you tried to comment on no longer exists.',
        '/book-review/books/index.php',
        'Browse Books',
        '/book-review/user/profile.php',
        'Profile',
        true
    );
}

if (column_exists($conn, 'reviews', 'status') && ($reviewRow['status'] ?? '') !== 'approved') {
    render_action_notice_page(
        'Comment Rejected',
        'Review Pending',
        'Comments are only available on approved reviews.',
        '/book-review/books/index.php',
        'Browse Books',
        '/book-review/user/profile.php',
        'Profile',
        true
    );
}

$bookSlug = trim((string) ($reviewRow['slug'] ?? ''));
if ($bookSlug === '') {
    $generatedSlug = preg_replace('/[^a-z0-9]+/i', '-', strtolower((string) ($reviewRow['title'] ?? '')));
    $bookSlug = trim($generatedSlug, '-');
}
if ($bookSlug === '') {
    $bookSlug = 'book-' . (int) ($reviewRow['book_id'] ?? 0);
}

$insertSql = 'INSERT INTO review_comments (review_id, user_id, parent_id, comment) VALUES (?, ?, NULL, ?)';
$insertStmt = $conn->prepare($insertSql);
$insertStmt->bind_param('iis', $reviewId, $userId, $comment);

if ($insertStmt->execute()) {
    header('Location: /book-review/books/' . rawurlencode($bookSlug) . '#review-' . $reviewId);
    exit();
}

render_action_notice_page(
    'Comment Rejected',
    'Could Not Save Comment',
    'Please try again in a moment.',
    '/book-review/books/' . rawurlencode($bookSlug) . '#review-' . $reviewId,
    'Back To Review',
    '/book-review/books/index.php',
    'Browse Books',
    true
);
