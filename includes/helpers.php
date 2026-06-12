<?php

// Generate SEO friendly slug from title
/**
 * Generate a URL-friendly slug from a string.
 *
 * @param string $text Input text (title)
 * @return string Slugified text
 */
function slugify($text) {
    // Convert to lowercase
    $text = strtolower($text);
    
    // Replace spaces with hyphens
    $text = str_replace(' ', '-', $text);
    
    // Remove special characters
    $text = preg_replace('/[^a-z0-9-]/', '', $text);
    
    // Remove multiple consecutive hyphens
    $text = preg_replace('/-+/', '-', $text);
    
    // Remove hyphens from start and end
    $text = trim($text, '-');
    
    return $text;
}

// Sanitize input
/**
 * Sanitize a value for safe HTML output.
 *
 * @param mixed $input Input value (will be cast to string)
 * @return string Safe HTML-escaped string
 */
function sanitize($input) {
    return htmlspecialchars((string)$input, ENT_QUOTES, 'UTF-8');
}

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Get current user ID
function getUserId() {
    return isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
}

// Format rating with stars
/**
 * Render rating as star icons and a numeric value.
 *
 * @param float|int|null $rating Numeric rating (0-5)
 * @return string HTML-safe string containing star icons and numeric rating or "No rating"
 */
function displayStars($rating) {
    if (!$rating) return "No rating";
    $rounded = round($rating);
    return str_repeat('<i class="fa-solid fa-star" aria-hidden="true"></i>', $rounded) . " (" . round($rating, 1) . "/5)";
}

// Check whether a column exists on a table (returns bool)
/**
 * @param mysqli $conn
 * @param string $table
 * @param string $column
 * @return bool
 */
function column_exists(mysqli $conn, string $table, string $column): bool {
    $tableEscaped = $conn->real_escape_string($table);
    $columnEscaped = $conn->real_escape_string($column);
    $res = $conn->query("SHOW COLUMNS FROM {$tableEscaped} LIKE '{$columnEscaped}'");
    return (bool)($res && $res->num_rows > 0);
}

?>
