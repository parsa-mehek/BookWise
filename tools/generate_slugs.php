<?php
// Simple script to generate unique slugs for books and save them to the DB.
// Usage: php tools/generate_slugs.php

require_once(__DIR__ . '/../config/db.php');

function make_slug(string $text) {
    $text = strtolower($text);
    $text = preg_replace('/[^a-z0-9\s-]/', '', $text);
    $text = preg_replace('/\s+/', '-', trim($text));
    return $text ?: null;
}

$sql = "SELECT id, title, slug FROM books";
$res = $conn->query($sql);
if (!$res) {
    echo "Error fetching books: " . $conn->error . PHP_EOL;
    exit(1);
}

while ($row = $res->fetch_assoc()) {
    $id = (int)$row['id'];
    $title = $row['title'] ?? '';
    $existing = trim((string)$row['slug']);
    if ($existing !== '') {
        echo "Skipping #$id (already has slug: $existing)" . PHP_EOL;
        continue;
    }

    $base = make_slug($title) ?: 'book-' . $id;
    $slug = $base;
    $i = 1;
    // ensure uniqueness
    $check = $conn->prepare("SELECT COUNT(*) AS cnt FROM books WHERE slug = ?");
    while (true) {
        $check->bind_param('s', $slug);
        $check->execute();
        $c = $check->get_result()->fetch_assoc();
        if ((int)$c['cnt'] === 0) break;
        $slug = $base . '-' . $i; $i++;
    }

    $u = $conn->prepare("UPDATE books SET slug = ? WHERE id = ?");
    $u->bind_param('si', $slug, $id);
    if ($u->execute()) {
        echo "Updated #$id -> $slug" . PHP_EOL;
    } else {
        echo "Failed to update #$id: " . $conn->error . PHP_EOL;
    }
}

// After running this, run the SQL to add the unique index if desired.
echo "Done.\n";
