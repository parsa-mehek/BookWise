<?php
require_once(__DIR__ . '/../../config/db.php');
require_once(__DIR__ . '/../../includes/helpers.php');

// Run via CLI: php database/seeders/seed_demo_data.php
// or open in browser: /book-review/database/seeders/seed_demo_data.php

function columnExists(mysqli $conn, string $table, string $column): bool {
    $tableEscaped = $conn->real_escape_string($table);
    $columnEscaped = $conn->real_escape_string($column);
    $result = $conn->query("SHOW COLUMNS FROM {$tableEscaped} LIKE '{$columnEscaped}'");
    return $result && $result->num_rows > 0;
}

function hasUniqueSlug(mysqli $conn, string $slug): bool {
    $stmt = $conn->prepare("SELECT id FROM books WHERE slug = ? LIMIT 1");
    $stmt->bind_param("s", $slug);
    $stmt->execute();
    $found = $stmt->get_result()->fetch_assoc();
    return !empty($found);
}

function indexExists(mysqli $conn, string $table, string $indexName): bool {
    $tableEscaped = $conn->real_escape_string($table);
    $indexEscaped = $conn->real_escape_string($indexName);
    $result = $conn->query("SHOW INDEX FROM {$tableEscaped} WHERE Key_name = '{$indexEscaped}'");
    return $result && $result->num_rows > 0;
}

$statements = [
    "CREATE TABLE IF NOT EXISTS genres (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        slug VARCHAR(120) NOT NULL UNIQUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )",
];

foreach ($statements as $statement) {
    if (!$conn->query($statement)) {
        die('Setup failed: ' . $conn->error);
    }
}

if (!columnExists($conn, 'books', 'genre')) {
    $conn->query("ALTER TABLE books ADD COLUMN genre VARCHAR(100) NOT NULL DEFAULT 'Fiction'");
}
if (!columnExists($conn, 'books', 'slug')) {
    $conn->query("ALTER TABLE books ADD COLUMN slug VARCHAR(191) NULL");
}
if (!columnExists($conn, 'books', 'cover_image')) {
    $conn->query("ALTER TABLE books ADD COLUMN cover_image VARCHAR(255) NULL");
}

$genres = [
    ['name' => 'Fiction', 'slug' => 'fiction'],
    ['name' => 'Fantasy', 'slug' => 'fantasy'],
    ['name' => 'Mystery', 'slug' => 'mystery'],
    ['name' => 'Self-help', 'slug' => 'self-help'],
    ['name' => 'Science', 'slug' => 'science'],
];

$genreStmt = $conn->prepare("INSERT INTO genres (name, slug) VALUES (?, ?) ON DUPLICATE KEY UPDATE name = VALUES(name)");
foreach ($genres as $genre) {
    $genreStmt->bind_param('ss', $genre['name'], $genre['slug']);
    $genreStmt->execute();
}

$books = [
    ['Harry Potter and the Sorcerer\'s Stone', 'J.K. Rowling', 'A magical adventure at Hogwarts School of Witchcraft and Wizardry.', 'Fantasy', 'https://images.unsplash.com/photo-1512820790803-83ca734da794?auto=format&fit=crop&w=700&q=80'],
    ['The Silent Patient', 'Alex Michaelides', 'A psychological mystery around a famous painter who stops speaking.', 'Mystery', 'https://images.unsplash.com/photo-1544947950-fa07a98d237f?auto=format&fit=crop&w=700&q=80'],
    ['Atomic Habits', 'James Clear', 'Practical framework for building good habits and breaking bad ones.', 'Self-help', 'https://images.unsplash.com/photo-1495446815901-a7297e633e8d?auto=format&fit=crop&w=700&q=80'],
    ['A Brief History of Time', 'Stephen Hawking', 'A concise exploration of cosmology, black holes, and the universe.', 'Science', 'https://images.unsplash.com/photo-1455885666463-9b1eb39f3f59?auto=format&fit=crop&w=700&q=80'],
    ['The Alchemist', 'Paulo Coelho', 'A fiction classic about purpose, dreams, and destiny.', 'Fiction', 'https://images.unsplash.com/photo-1515098506762-79e1384e9d8e?auto=format&fit=crop&w=700&q=80'],
    ['The Hobbit', 'J.R.R. Tolkien', 'Bilbo Baggins goes on a fantasy quest with dwarves and a wizard.', 'Fantasy', 'https://images.unsplash.com/photo-1529148482759-b35b25c5f217?auto=format&fit=crop&w=700&q=80'],
    ['The Girl with the Dragon Tattoo', 'Stieg Larsson', 'A gripping mystery investigation led by an unlikely duo.', 'Mystery', 'https://images.unsplash.com/photo-1495640388908-05fa85288e61?auto=format&fit=crop&w=700&q=80'],
    ['Deep Work', 'Cal Newport', 'Strategies to improve focus and produce high-value work.', 'Self-help', 'https://images.unsplash.com/photo-1516979187457-637abb4f9353?auto=format&fit=crop&w=700&q=80'],
    ['Cosmos', 'Carl Sagan', 'A science journey across stars, galaxies, and human curiosity.', 'Science', 'https://images.unsplash.com/photo-1462331940025-496dfbfc7564?auto=format&fit=crop&w=700&q=80'],
    ['The Kite Runner', 'Khaled Hosseini', 'A moving fiction novel about friendship and redemption.', 'Fiction', 'https://images.unsplash.com/photo-1507842217343-583bb7270b66?auto=format&fit=crop&w=700&q=80'],
    ['Project Hail Mary', 'Andy Weir', 'A science-driven space survival story full of problem-solving.', 'Science', 'https://images.unsplash.com/photo-1465101046530-73398c7f28ca?auto=format&fit=crop&w=700&q=80'],
    ['Think and Grow Rich', 'Napoleon Hill', 'A self-help classic about mindset and achievement.', 'Self-help', 'https://images.unsplash.com/photo-1519681393784-d120267933ba?auto=format&fit=crop&w=700&q=80'],
];

$bookStmt = $conn->prepare(
    "INSERT INTO books (title, author, description, genre, slug, cover_image)
     VALUES (?, ?, ?, ?, ?, ?)
     ON DUPLICATE KEY UPDATE
       author = VALUES(author),
       description = VALUES(description),
       genre = VALUES(genre),
       cover_image = VALUES(cover_image)"
);

foreach ($books as $book) {
    [$title, $author, $description, $genre, $cover] = $book;
    $baseSlug = slugify($title);
    $slug = $baseSlug;
    $counter = 2;

    while (hasUniqueSlug($conn, $slug)) {
        $checkStmt = $conn->prepare("SELECT id FROM books WHERE slug = ? AND title = ? LIMIT 1");
        $checkStmt->bind_param('ss', $slug, $title);
        $checkStmt->execute();
        $sameBook = $checkStmt->get_result()->fetch_assoc();
        if ($sameBook) {
            break;
        }
        $slug = $baseSlug . '-' . $counter;
        $counter++;
    }

    $bookStmt->bind_param('ssssss', $title, $author, $description, $genre, $slug, $cover);
    $bookStmt->execute();
}

$userId = null;
$userResult = $conn->query("SELECT id FROM users ORDER BY id ASC LIMIT 1");
if ($userResult && $userResult->num_rows > 0) {
    $userRow = $userResult->fetch_assoc();
    $userId = (int)$userRow['id'];
}

if ($userId) {
    $bookIds = [];
    $bookIdResult = $conn->query("SELECT id FROM books ORDER BY id DESC LIMIT 12");
    if ($bookIdResult) {
        while ($row = $bookIdResult->fetch_assoc()) {
            $bookIds[] = (int)$row['id'];
        }
    }

    $reviewStmt = $conn->prepare("INSERT INTO reviews (user_id, book_id, rating, comment) VALUES (?, ?, ?, ?)");
    foreach ($bookIds as $bookId) {
        $existing = $conn->prepare("SELECT id FROM reviews WHERE user_id = ? AND book_id = ? LIMIT 1");
        $existing->bind_param('ii', $userId, $bookId);
        $existing->execute();
        $exists = $existing->get_result()->fetch_assoc();
        if ($exists) {
            continue;
        }

        $rating = rand(3, 5);
        $comment = 'Seeder demo review with rating ' . $rating . '/5';
        $reviewStmt->bind_param('iiis', $userId, $bookId, $rating, $comment);
        $reviewStmt->execute();
    }
}

$conn->query("UPDATE books SET slug = NULL WHERE slug = ''");

if (!indexExists($conn, 'books', 'idx_books_slug')) {
    if (!$conn->query("ALTER TABLE books ADD UNIQUE INDEX idx_books_slug (slug)")) {
        die('Failed to add books.slug unique index: ' . $conn->error);
    }
}

echo "Demo genres and books seeded successfully.";
