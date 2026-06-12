<?php
require_once("../config/db.php");
require_once("../includes/helpers.php");

$page_title = 'Search Results';
$search = trim($_GET['search'] ?? '');
$page = max(1, (int)($_GET['page'] ?? 1));
$per_page = 12;
$offset = ($page - 1) * $per_page;
$books = [];

if ($search !== '') {
    $search_term = '%' . $search . '%';

    // count total matching books (group by handled in subquery)
    $count_sql = "SELECT COUNT(*) AS total FROM (
        SELECT books.id FROM books
        WHERE books.title LIKE ? OR books.author LIKE ? OR books.description LIKE ? OR books.genre LIKE ?
        GROUP BY books.id
    ) tmp";
    $count_stmt = $conn->prepare($count_sql);
    $count_stmt->bind_param("ssss", $search_term, $search_term, $search_term, $search_term);
    $count_stmt->execute();
    $count_row = $count_stmt->get_result()->fetch_assoc();
    $total_books = (int)($count_row['total'] ?? 0);
    $total_pages = max(1, (int)ceil($total_books / $per_page));

    $has_review_status = column_exists($conn, 'reviews', 'status');
    $reviews_join = $has_review_status
        ? "LEFT JOIN reviews ON books.id = reviews.book_id AND reviews.status = 'approved'"
        : "LEFT JOIN reviews ON books.id = reviews.book_id";

    $sql = "SELECT
                books.id,
                books.title,
                books.author,
                books.cover_image,
                books.slug,
                COALESCE(AVG(reviews.rating), 0) AS average_rating,
                COUNT(reviews.id) AS review_count
            FROM books
                {$reviews_join}
            WHERE books.title LIKE ?
               OR books.author LIKE ?
               OR books.description LIKE ?
               OR books.genre LIKE ?
            GROUP BY books.id
            ORDER BY average_rating DESC, review_count DESC, books.title ASC
            LIMIT ? OFFSET ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssii", $search_term, $search_term, $search_term, $search_term, $per_page, $offset);
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

                <?php if (!empty($total_pages) && $total_pages > 1): ?>
                    <div class="pagination" style="margin-top:20px;display:flex;gap:8px;align-items:center;justify-content:center">
                        <?php
                            $base_params = ['search' => $search];
                            $prev_page = max(1, $page - 1);
                            $next_page = min($total_pages, $page + 1);
                        ?>
                        <a class="page-link <?php echo $page <= 1 ? 'disabled' : ''; ?>" href="?<?php echo htmlspecialchars(http_build_query(array_merge($base_params, ['page' => $prev_page]))); ?>">&lsaquo;</a>
                        <?php
                            $window_start = max(1, $page - 2);
                            $window_end = min($total_pages, $page + 2);
                            for ($i = $window_start; $i <= $window_end; $i++):
                                $page_params = $base_params + ['page' => $i];
                        ?>
                            <a class="page-link <?php echo $i === $page ? 'active' : ''; ?>" href="?<?php echo htmlspecialchars(http_build_query($page_params)); ?>"><?php echo $i; ?></a>
                        <?php endfor; ?>
                        <a class="page-link <?php echo $page >= $total_pages ? 'disabled' : ''; ?>" href="?<?php echo htmlspecialchars(http_build_query(array_merge($base_params, ['page' => $next_page]))); ?>">&rsaquo;</a>
                    </div>
                <?php endif; ?>
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

                        <?php
                            $book_slug = trim((string)($book['slug'] ?? ''));
                            if ($book_slug === '') {
                                $book_slug = preg_replace('/[^a-z0-9]+/i', '-', strtolower((string)$book['title']));
                                $book_slug = trim($book_slug, '-');
                            }
                            $book_url = '/book-review/books/' . rawurlencode($book_slug);
                        ?>
                        <a class="btn-primary" href="<?php echo sanitize($book_url); ?>">View Details</a>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</main>

<?php include("../includes/footer.php"); ?>