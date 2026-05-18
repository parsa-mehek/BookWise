<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: /book-review/auth/login.php');
    exit();
}

include('../config/db.php');

$user_id = (int)$_SESSION['user_id'];
$goal_count = max(1, (int)($_POST['goal_count'] ?? 0));
$goal_year = (int)date('Y');

$sql = "INSERT INTO reading_goals (user_id, goal_year, goal_count) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE goal_count = VALUES(goal_count)";
$stmt = $conn->prepare($sql);
$stmt->bind_param('iii', $user_id, $goal_year, $goal_count);
$stmt->execute();

header('Location: /book-review/user/profile.php');
exit();
