<?php
require_once("../config/db.php");
require_once("../includes/helpers.php");

$page_title = 'Search Results';
$search = trim($_GET['search'] ?? '');
$books = [];

if ($search !== '') {
    $search_term = '%' . $search . '%';

    $sql = "SELECT
                books.id,
                books.title,
                books.author,
                books.cover_image,
                books.slug,
                COALESCE(AVG(reviews.rating), 0) AS average_rating,
                COUNT(reviews.id) AS review_count
            FROM books
            LEFT JOIN reviews ON books.id = reviews.book_id
            WHERE books.title LIKE ?
               OR books.author LIKE ?
               OR books.description LIKE ?
               OR books.genre LIKE ?
            GROUP BY books.id
            ORDER BY average_rating DESC, review_count DESC, books.title ASC";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssss", $search_term, $search_term, $search_term, $search_term);
    $stmt->execute();

    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $books[] = $row;
    }
}

include("../includes/header.php");
?>
<style>
    body {
        background: #f3efe8;
    }

    .search-results-page {
        max-width: 1280px;
        margin: 0 auto;
        padding: 44px 24px 64px;
    }

    .search-results-hero {
        display: flex;
        justify-content: space-between;
        gap: 20px;
        align-items: end;
        padding: 28px;
        margin-bottom: 28px;
        background: linear-gradient(135deg, rgba(255, 255, 255, 0.95), rgba(245, 239, 232, 0.92));
        border: 1px solid rgba(124, 92, 255, 0.12);
        border-radius: 28px;
        box-shadow: 0 16px 40px rgba(15, 23, 42, 0.06);
    }

    .search-results-hero h1 {
        margin: 0 0 10px;
        color: #111827;
        font-size: clamp(28px, 4vw, 44px);
        line-height: 1.05;
    }

    .search-results-hero p {
        margin: 0;
        color: #4b5563;
        line-height: 1.6;
        max-width: 60ch;
    }

    .search-results-count {
        flex-shrink: 0;
        padding: 14px 18px;
        border-radius: 999px;
        background: #111827;
        color: #fff;
        font-weight: 700;
        white-space: nowrap;
    }

    .search-empty {
        background: #fff;
        border-radius: 24px;
        padding: 32px;
        box-shadow: 0 12px 30px rgba(15, 23, 42, 0.06);
        text-align: center;
    }

    .search-empty h2 {
        margin: 0 0 10px;
        color: #111827;
    }

    .search-empty p {
        margin: 0;
        color: #6b7280;
    }

    .search-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
        gap: 24px;
    }

    .search-card {
        background: #fff;
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 10px 26px rgba(15, 23, 42, 0.08);
        transition: transform 0.25s ease, box-shadow 0.25s ease;
    }

    .search-card:hover {
        transform: translateY(-6px);
        box-shadow: 0 18px 34px rgba(15, 23, 42, 0.12);
    }

    .search-cover {
        aspect-ratio: 4 / 5;
        background: linear-gradient(135deg, #1f2937 0%, #7c3aed 100%);
        position: relative;
        overflow: hidden;
    }

    .search-cover img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }

    .search-cover-fallback {
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-size: 56px;
        font-weight: 800;
        letter-spacing: 2px;
    }

    .search-card-body {
        padding: 18px;
    }

    .search-rating {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        color: #f59e0b;
        font-weight: 700;
        margin-bottom: 10px;
    }

    .search-card h3 {
        margin: 0 0 8px;
        color: #111827;
        font-size: 1.15rem;
        line-height: 1.35;
    }

    .search-author {
        margin: 0 0 16px;
        color: #6b7280;
    }

    .search-card .btn-primary {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 100%;
        text-decoration: none;
    }

    @media (max-width: 640px) {
        .search-results-page {
            padding: 28px 16px 48px;
        }

        .search-results-hero {
            padding: 20px;
            border-radius: 22px;
            flex-direction: column;
            align-items: start;
        }

        .search-results-count {
            width: 100%;
            text-align: center;
        }
    }
</style>

<main class="search-results-page">
    <section class="search-results-hero">
        <div>
            <h1>Search Results</h1>
            <?php if ($search !== ''): ?>
                <p>Showing books that match "<?php echo sanitize($search); ?>" across titles, authors, descriptions, and genres.</p>
            <?php else: ?>
                <p>Enter a search term to find books by title, author, description, or genre.</p>
            <?php endif; ?>
        </div>
        <div class="search-results-count"><?php echo count($books); ?> results</div>
    </section>

    <?php if ($search === ''): ?>
        <div class="search-empty">
            <h2>No search term yet</h2>
            <p>Use the search bar in the header to look up books.</p>
        </div>
    <?php elseif (count($books) === 0): ?>
        <div class="search-empty">
            <h2>No books found</h2>
            <p>Try another title, author, or genre.</p>
        </div>
    <?php else: ?>
        <div class="search-grid">
            <?php foreach ($books as $book): ?>
                <article class="search-card">
                    <div class="search-cover">
                        <?php if (!empty($book['cover_image'])): ?>
                            <img src="<?php echo sanitize($book['cover_image']); ?>" alt="<?php echo sanitize($book['title']); ?> cover">
                        <?php else: ?>
                            <div class="search-cover-fallback"><?php echo sanitize(strtoupper(substr((string)$book['title'], 0, 2))); ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="search-card-body">
                        <div class="search-rating">
                            <i class="fa-solid fa-star" aria-hidden="true"></i>
                            <?php echo number_format((float)$book['average_rating'], 1); ?>
                        </div>

                        <h3><?php echo sanitize($book['title']); ?></h3>
                        <p class="search-author"><?php echo sanitize($book['author'] ?: 'Unknown Author'); ?></p>

                        <a class="btn-primary" href="/book-review/books/view.php?id=<?php echo (int)$book['id']; ?>">View Details</a>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</main>

<?php include("../includes/footer.php"); ?>