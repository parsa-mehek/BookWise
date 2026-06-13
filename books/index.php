
<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: /book-review/auth/login.php");
    exit();
}

include("../config/db.php");
include("../includes/helpers.php");

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'] ?? 'Reader';

$user_books_sql = "SELECT COUNT(*) as total_read FROM reviews WHERE user_id = ?";
$stmt = $conn->prepare($user_books_sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_stats = $stmt->get_result()->fetch_assoc();
$books_read = $user_stats['total_read'] ?? 0;

$top_books_sql = "SELECT books.*, AVG(reviews.rating) AS avg_rating, COUNT(reviews.id) as review_count
                  FROM books
                  LEFT JOIN reviews ON books.id = reviews.book_id AND reviews.status = 'approved'
                  GROUP BY books.id
                  ORDER BY avg_rating DESC
                  LIMIT 5";
$top_books_result = $conn->query($top_books_sql);

$reviews_sql = "SELECT reviews.*, users.name as user_name, books.title as book_title
                FROM reviews
                LEFT JOIN users ON reviews.user_id = users.id
                LEFT JOIN books ON reviews.book_id = books.id
                WHERE reviews.status = 'approved'
                ORDER BY reviews.id DESC
                LIMIT 3";
$reviews_result = $conn->query($reviews_sql);

$goal_year = (int) date('Y');
$goal_query = $conn->prepare("SELECT goal_count FROM reading_goals WHERE user_id = ? AND goal_year = ? LIMIT 1");
$goal_query->bind_param("ii", $user_id, $goal_year);
$goal_query->execute();
$goal_row = $goal_query->get_result()->fetch_assoc();
$goal_count = (int) ($goal_row['goal_count'] ?? 10);

$completed_query = $conn->prepare("SELECT COUNT(*) AS total FROM user_library WHERE user_id = ? AND status = 'completed' AND YEAR(completed_at) = ?");
$completed_query->bind_param("ii", $user_id, $goal_year);
$completed_query->execute();
$completed_row = $completed_query->get_result()->fetch_assoc();
$completed_books = (int) ($completed_row['total'] ?? 0);

$remaining_books = max(0, $goal_count - $completed_books);
$progress_percent = $goal_count > 0 ? min(100, (int) round(($completed_books / $goal_count) * 100)) : 0;

$page_title = 'Dashboard - Bookwise';
include("../includes/header.php");
?>

<style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', 'Inter', system-ui, sans-serif;
            background: #f3efe8;
            color: #333;
        }

        .dashboard-main {
            padding: 40px 20px;
            background: #f3efe8;
            min-height: calc(100vh - 100px);
        }

        .dashboard-container {
            width: 95%;
            max-width: 1600px;
            margin: 0 auto;
        }

        /* ========== HERO SECTION ========== */
        .hero-section {
            background: white;
            border-radius: 20px;
            padding: 40px 50px;
            margin-bottom: 40px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
            display: grid;
            grid-template-columns: 1fr auto;
            gap: 50px;
            align-items: center;
        }

        .hero-content h1 {
            font-size: 42px;
            font-weight: 800;
            color: #1a1a1a;
            margin-bottom: 20px;
            line-height: 1.2;
        }

        .hero-content p {
            font-size: 18px;
            color: #666;
            margin-bottom: 30px;
            line-height: 1.6;
        }

        .hero-content p strong {
            color: #7b5cff;
            font-weight: 700;
        }

        .hero-buttons {
            display: flex;
            gap: 16px;
        }

        .btn {
            padding: 12px 20px;
            border: none;
            border-radius: 25px;
            font-weight: 600;
            cursor: pointer;
            font-size: 15px;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
        }

        .btn-primary {
            background: linear-gradient(45deg, #7b5cff, #9f7bff);
            color: white;
            box-shadow: 0 8px 20px rgba(123, 92, 255, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 28px rgba(123, 92, 255, 0.4);
        }

        .btn-secondary {
            background: #eee;
            color: #333;
            border: 1px solid #e0e0e0;
        }

        .btn-secondary:hover {
            background: #e0e0e0;
            transform: translateY(-3px);
        }

        .hero-stats {
            display: flex;
            gap: 15px;
        }

        .stat-box {
            background: white;
            padding: 24px 20px;
            border-radius: 15px;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            width: 110px;
            border: 1px solid #f0f0f0;
            transition: all 0.3s ease;
        }

        .stat-box:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 20px rgba(123, 92, 255, 0.12);
        }

        .stat-box h2 {
            font-size: 32px;
            font-weight: 800;
            color: #7b5cff;
            margin-bottom: 4px;
        }

        .stat-box p {
            font-size: 13px;
            color: #999;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* ========== SECTION TITLE ========== */
        .section-title {
            font-size: 28px;
            font-weight: 800;
            color: #1a1a1a;
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .view-all-link {
            color: #7b5cff;
            text-decoration: none;
            font-weight: 700;
            font-size: 15px;
            transition: gap 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .view-all-link:hover {
            gap: 12px;
        }

        /* ========== TOP RATED BOOKS ========== */
        .books-section {
            margin-bottom: 40px;
        }

        .books-list {
            display: flex;
            flex-wrap: nowrap;
            gap: 24px;
            align-items: stretch;
            width: 100%;
            margin-bottom: 0;
            padding-bottom: 0;
            overflow-x: auto;
            scrollbar-width: thin;
        }

        .book-card {
            background: white;
            padding: 0;
            border-radius: 15px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.05);
            border: 1px solid #f0f0f0;
            transition: all 0.3s ease;
            cursor: pointer;
            display: flex;
            flex-direction: column;
            flex: 0 0 220px;
            width: 220px;
            min-height: 100%;
        }

        .book-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 32px rgba(123, 92, 255, 0.15);
            border-color: rgba(123, 92, 255, 0.2);
        }

        .book-cover {
            width: 100%;
            aspect-ratio: 4 / 5;
            background: linear-gradient(135deg, #7b5cff 0%, #9f7bff 100%);
            border-radius: 10px 10px 0 0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 42px;
            color: white;
            font-weight: 800;
            position: relative;
            overflow: hidden;
            margin-bottom: 0;
        }

        .book-cover::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255, 255, 255, 0.1);
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .book-card:hover .book-cover::after {
            opacity: 1;
        }

        .rating-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            background: white;
            padding: 4px 10px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 700;
            color: #f59e0b;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .book-info {
            padding: 18px;
            display: flex;
            flex-direction: column;
            gap: 10px;
            flex-grow: 1;
            justify-content: center;
        }

        .book-title {
            font-size: 20px;
            font-weight: 700;
            color: #1a1a1a;
            margin-bottom: 4px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .book-author {
            font-size: 12px;
            color: #999;
            font-weight: 500;
        }

        /* ========== COMMUNITY REVIEWS ========== */
        .reviews-section {
            margin-bottom: 40px;
        }

        .reviews-list {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 24px;
        }

        .review-card {
            background: white;
            border-radius: 15px;
            padding: 24px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.05);
            border: 1px solid #f0f0f0;
            transition: all 0.3s ease;
        }

        .review-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 12px 32px rgba(123, 92, 255, 0.12);
            border-color: rgba(123, 92, 255, 0.15);
        }

        .review-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 16px;
        }

        .reviewer-avatar {
            width: 48px;
            height: 48px;
            border-radius: 10px;
            background: linear-gradient(135deg, #7b5cff 0%, #9f7bff 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
            font-size: 18px;
            flex-shrink: 0;
            box-shadow: 0 4px 12px rgba(123, 92, 255, 0.2);
        }

        .reviewer-info h4 {
            font-size: 14px;
            font-weight: 700;
            color: #1a1a1a;
            margin-bottom: 2px;
        }

        .reviewer-book {
            font-size: 11px;
            color: #7b5cff;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .review-rating {
            display: flex;
            gap: 3px;
            font-size: 15px;
            margin-bottom: 12px;
        }

        .review-text {
            font-size: 14px;
            color: #666;
            line-height: 1.6;
            margin-bottom: 16px;
        }

        .review-footer {
            display: flex;
            gap: 16px;
            border-top: 1px solid #f0f0f0;
            padding-top: 12px;
        }

        .review-action {
            display: flex;
            align-items: center;
            gap: 5px;
            font-size: 12px;
            color: #999;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .review-action:hover {
            color: #7b5cff;
            transform: translateX(3px);
        }

        /* ========== RESPONSIVE ========== */
        @media (max-width: 1024px) {
            .hero-section {
                grid-template-columns: 1fr;
                gap: 30px;
                padding: 40px;
            }

            .hero-content h1 {
                font-size: 36px;
            }

            .reviews-list {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 768px) {
            .dashboard-main {
                padding: 30px 16px;
            }

            .hero-section {
                padding: 32px;
                gap: 20px;
            }

            .hero-content h1 {
                font-size: 28px;
            }

            .hero-buttons {
                flex-direction: column;
            }

            .btn {
                width: 100%;
                justify-content: center;
            }

            .books-list {
                gap: 16px;
            }

            .book-card {
                flex: 0 0 200px;
                width: 200px;
            }

            .reviews-list {
                grid-template-columns: 1fr;
            }

            .section-title {
                font-size: 22px;
            }

        }

        @media (max-width: 480px) {
            .hero-section {
                padding: 24px;
                gap: 20px;
            }

            .hero-content h1 {
                font-size: 24px;
            }

            .stat-box {
                width: 100%;
            }

            .hero-stats {
                width: 100%;
                flex-wrap: wrap;
            }

            .section-title {
                font-size: 20px;
            }

            .review-card {
                padding: 16px;
            }
        }

    </style>
    <div class="dashboard-main">
        <div class="dashboard-container">

            <!-- HERO SECTION -->
            <div class="hero-section">
                <div class="hero-content">
                    <h1>Welcome back, <?php echo htmlspecialchars($user_name); ?>!</h1>
                    <p>You've read <strong><?php echo $completed_books; ?></strong> book<?php echo $completed_books != 1 ? 's' : ''; ?> this year. <strong><?php echo $remaining_books; ?> more to reach your <?php echo $goal_year; ?> goal!</strong></p>
                    <div class="hero-buttons">
                        <a href="/book-review/books/browse.php" class="btn btn-primary"><i class="fa-solid fa-book-open" aria-hidden="true"></i> Browse Books</a>
                        <a href="/book-review/user/my-library.php" class="btn btn-secondary"><i class="fa-solid fa-book-reader" aria-hidden="true"></i> My Library</a>
                    </div>
                </div>
                <div class="hero-stats">
                    <div class="stat-box">
                        <h2><?php echo $completed_books; ?></h2>
                        <p>READ</p>
                    </div>
                    <div class="stat-box">
                        <h2><?php echo $goal_count; ?></h2>
                        <p>GOAL</p>
                    </div>
                    <div class="stat-box">
                        <h2><?php echo $progress_percent; ?>%</h2>
                        <p>PROGRESS</p>
                    </div>
                </div>
            </div>

            <!-- TOP RATED BOOKS SECTION -->
            <div class="books-section">
                <h2 class="section-title">
                  Top Rated Books
                  <a href="/book-review/books/top.php" class="view-all-link">View all →</a>
                </h2>
                <div class="books-list">
                    <?php 
                    if ($top_books_result->num_rows > 0) {
                        while ($book = $top_books_result->fetch_assoc()) {
                            $rating = round($book['avg_rating'], 1) ?: 'N/A';
                            $initials = strtoupper(substr($book['title'], 0, 2));
                            echo "
                            <div class='book-card'>
                                <div class='book-cover'>
                                    {$initials}
                                    <div class='rating-badge'>{$rating} <i class=\"fa-solid fa-star\"></i></div>
                                </div>
                                <div class='book-info'>
                                    <div class='book-title'>" . htmlspecialchars($book['title']) . "</div>
                                    <div class='book-author'>" . htmlspecialchars($book['author'] ?? 'Unknown') . "</div>
                                </div>
                            </div>
                            ";
                        }
                    }
                    ?>
                </div>
            </div>

            <!-- COMMUNITY REVIEWS SECTION -->
            <div class="reviews-section">
                <h2 class="section-title">
                    Community Reviews
                    <a href="#" class="view-all-link">View all →</a>
                </h2>
                <div class="reviews-list">
                    <?php 
                    if ($reviews_result->num_rows > 0) {
                        while ($review = $reviews_result->fetch_assoc()) {
                            $initials = strtoupper(substr($review['user_name'] ?? 'User', 0, 1));
                            $stars = str_repeat('<i class="fa-solid fa-star" aria-hidden="true"></i>', $review['rating'] ?? 0);
                            echo "
                            <div class='review-card'>
                                <div class='review-header'>
                                    <div class='reviewer-avatar'>{$initials}</div>
                                    <div class='reviewer-info'>
                                        <h4>" . htmlspecialchars($review['user_name'] ?? 'Anonymous') . "</h4>
                                        <div class='reviewer-book'>" . htmlspecialchars(substr($review['book_title'] ?? 'Unknown', 0, 30)) . "</div>
                                    </div>
                                </div>
                                <div class='review-rating'>{$stars}</div>
                                <div class='review-text'>" . htmlspecialchars(substr($review['comment'] ?? 'No comment', 0, 150)) . "...</div>
                                <div class='review-footer'>
                                    <div class='review-action'><i class=\"fa-solid fa-thumbs-up\"></i> 34</div>
                                    <div class='review-action'><i class=\"fa-solid fa-comment\"></i> 12</div>
                                    <div class='review-action'><i class=\"fa-solid fa-share\"></i> Share</div>
                                </div>
                            </div>
                            ";
                        }
                    }
                    ?>
                </div>
            </div>

        </div>
    </div>

    <?php include("../includes/footer.php"); ?>
