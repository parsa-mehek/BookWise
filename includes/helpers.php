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

<<<<<<< HEAD
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

/**
 * Check whether a table exists in the current database.
 *
 * @param mysqli $conn
 * @param string $table
 * @return bool
 */
function table_exists(mysqli $conn, string $table): bool {
    $tableEscaped = $conn->real_escape_string($table);
    $res = $conn->query("SHOW TABLES LIKE '{$tableEscaped}'");
    return (bool)($res && $res->num_rows > 0);
}

/**
 * Render a small status page for review/comment actions.
 */
function render_action_notice_page(string $title, string $heading, string $message, string $primaryHref, string $primaryLabel, string $secondaryHref = '', string $secondaryLabel = '', bool $warning = false): void {
    $iconClass = $warning ? 'fa-solid fa-triangle-exclamation' : 'fa-solid fa-circle-check';
    $iconClassName = $warning ? 'warning-icon' : 'success-icon';
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?php echo htmlspecialchars($title, ENT_QUOTES, 'UTF-8'); ?></title>
        <link rel="stylesheet" href="../assets/css/style.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    </head>
    <body>
    <div class="review-success-page">
        <div class="review-success-card">
            <div class="<?php echo $iconClassName; ?>">
                <i class="<?php echo $iconClass; ?>" aria-hidden="true"></i>
            </div>
            <h1><?php echo htmlspecialchars($heading, ENT_QUOTES, 'UTF-8'); ?></h1>
            <p><?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?></p>
            <div class="success-actions">
                <a href="<?php echo htmlspecialchars($primaryHref, ENT_QUOTES, 'UTF-8'); ?>" class="primary-btn"><?php echo htmlspecialchars($primaryLabel, ENT_QUOTES, 'UTF-8'); ?></a>
                <?php if ($secondaryHref !== '' && $secondaryLabel !== ''): ?>
                    <a href="<?php echo htmlspecialchars($secondaryHref, ENT_QUOTES, 'UTF-8'); ?>" class="secondary-btn"><?php echo htmlspecialchars($secondaryLabel, ENT_QUOTES, 'UTF-8'); ?></a>
                <?php endif; ?>
            </div>
        </div>
    </div>
    </body>
    </html>
    <?php
    exit();
}

=======
>>>>>>> parent of efb6876 ( Bug Fixed)
?>
