<?php
$is_landing_page = true;
include("includes/header.php");
require_once("config/db.php");
require_once("includes/helpers.php");

$total_books = 0;
$total_reviews = 0;
$total_users = 0;

if ($conn) {
    $result = $conn->query("SELECT COUNT(*) as count FROM books");
    if ($result) {
        $row = $result->fetch_assoc();
        $total_books = $row['count'] ?? 0;
    }
    
    $result = $conn->query("SELECT COUNT(*) as count FROM reviews");
    if ($result) {
        $row = $result->fetch_assoc();
        $total_reviews = $row['count'] ?? 0;
    }
    
    $result = $conn->query("SELECT COUNT(*) as count FROM users");
    if ($result) {
        $row = $result->fetch_assoc();
        $total_users = $row['count'] ?? 0;
    }
}
?>

<section class="hero">
  <div class="hero-left">
    <h1>Discover, Review &amp; <span>Rate</span> Your Favorite Books</h1>
    <p>Join a global community of readers. Explore millions of titles, share your honest thoughts, and find your next great read through data-driven recommendations.</p>
    <div class="hero-actions">
      <a href="/book-review/auth/login.php" class="btn-primary">Get Started</a>
      <?php if (isset($_SESSION['user_id'])): ?>
        <a href="/book-review/books/index.php" class="btn-secondary">Browse Books</a>
      <?php else: ?>
        <a href="/book-review/auth/login.php?redirect=/book-review/books/index.php" class="btn-secondary">Browse Books</a>
      <?php endif; ?>
    </div>
  </div>

  <div class="hero-right">
    <div class="hero-card hero-card-primary">
      <div class="hero-card-image"></div>
      <div class="hero-card-body">
        <p class="hero-card-label">“Life-changing read”</p>
        <p>Find the stories that stay with you long after the last page.</p>
      </div>
    </div>
    <div class="hero-card hero-card-secondary">
      <div class="hero-card-title">Bookwise Picks</div>
      <div class="hero-card-meta">A curated reading experience in every review.</div>
    </div>
  </div>
</section>

<section class="search-panel">
  <form action="<?php echo isset($_SESSION['user_id']) ? '/book-review/books/index.php' : '/book-review/auth/login.php?redirect=/book-review/books/index.php'; ?>" method="GET" class="search-form">
    <div class="search-input-wrap">
      <input type="text" name="search" placeholder="Search by title, author, or ISBN">
    </div>
    <select name="genre">
      <option value="">All Genres</option>
      <option value="fiction">Fiction</option>
      <option value="non-fiction">Non-fiction</option>
      <option value="mystery">Mystery</option>
      <option value="fantasy">Fantasy</option>
    </select>
    <button type="submit">Find Books</button>
  </form>
</section>

<section class="why-section">
  <div class="section-header center">
    <p class="section-label">Why Read with Bookwise?</p>
    <h2>We provide the tools you need to organize your reading life and connect with stories on a deeper level.</h2>
  </div>
  <div class="feature-grid">
    <article class="feature-card">
      <div class="feature-icon"><i class="fa-solid fa-book" aria-hidden="true"></i></div>
      <h3>Personalized Recommendations</h3>
      <p>Our AI analyzes your reading history and ratings to suggest books you’ll genuinely love.</p>
    </article>
    <article class="feature-card">
      <div class="feature-icon"><i class="fa-solid fa-hands-helping" aria-hidden="true"></i></div>
      <h3>Community Discussions</h3>
      <p>Join active book clubs and discussion threads to share insights with other readers globally.</p>
    </article>
    <article class="feature-card">
      <div class="feature-icon"><i class="fa-solid fa-chart-simple" aria-hidden="true"></i></div>
      <h3>Detailed Analytics</h3>
      <p>Track your reading habits, pages read, and genre distribution with beautiful visualizations.</p>
    </article>
  </div>
</section>

<section class="journey-section">
  <div class="section-header center">
    <p class="section-label">Start Your Journey</p>
    <h2>Getting the most out of Bookwise is simple.</h2>
  </div>
  <div class="journey-grid">
    <div class="journey-step">
      <span>1</span>
      <h4>Join the Club</h4>
      <p>Create your free profile and import your existing library.</p>
    </div>
    <div class="journey-step">
      <span>2</span>
      <h4>Share &amp; Review</h4>
      <p>Rate what you’ve read and write reviews for others to see.</p>
    </div>
    <div class="journey-step">
      <span>3</span>
      <h4>Explore &amp; Expand</h4>
      <p>Discover new genres and track your growth as a reader.</p>
    </div>
  </div>
</section>

<section class="testimonials-section">
  <div class="testimonial-banner">
    <div>
      <p class="section-label">What Our Readers Are Saying</p>
      <h2>Join over 10,000 satisfied bookworms who have transformed their reading habits with Bookwise.</h2>
      <p class="testimonial-rating">4.9/5 Average Rating</p>
    </div>
  </div>
  <div class="testimonial-grid">
    <article class="testimonial-card">
      <p class="quote">“Bookwise has completely changed how I track my reading. The personalized recommendations are so spot-on!”</p>
      <p class="author">Sarah Miller · Book Lover</p>
    </article>
    <article class="testimonial-card">
      <p class="quote">“I love the community features. Discussing my favorite sci-fi series with others has been so rewarding.”</p>
      <p class="author">James Wilson · Avid Reader</p>
    </article>
  </div>
</section>

<section class="cta-section">
  <div class="cta-card">
    <div class="cta-card-logo">
      <span><i class="fa-solid fa-book-open" aria-hidden="true"></i></span>
      <span>Bookwise</span>
    </div>
    <h2>Ready to Join Our Community?</h2>
    <p>Sign up today and get your first personalized reading list curated by our experts.</p>
    <a href="/book-review/auth/register.php" class="btn-primary">Create Your Account</a>
  </div>
</section>

<?php include("includes/footer.php"); ?>
