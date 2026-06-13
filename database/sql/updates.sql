-- ALTER statements to apply to an existing BookWise database
-- SAFE MIGRATION SQL: add slug column, populate unique slugs, set review default, and recalc averages.

-- 1) Add `slug` column if it does not exist
ALTER TABLE books
  ADD COLUMN IF NOT EXISTS slug VARCHAR(255) DEFAULT NULL;

-- 2) Populate slugs for existing rows (id-appended to guarantee uniqueness).
--    This creates SEO-friendly slugs based on the title and book id.
UPDATE books
SET slug = CONCAT(
    -- basic cleanup: lowercase, common punctuation removal, spaces -> hyphens
    LOWER(
      REPLACE(
        REPLACE(
          REPLACE(
            REPLACE(
              REPLACE(
                REPLACE(
                  REPLACE(
                    REPLACE(
                      REPLACE(COALESCE(title, ''), "'", ''), '"', ''), ',', ''), '.', ''), ':', ''), ';', ''), '/', ''), '\\', ''), '&', 'and'), '  ', ' '
      )
    ),
    '-', id
)
WHERE slug IS NULL OR TRIM(slug) = '';

-- 3) Add unique index for slug (will fail if duplicates exist; above ensures uniqueness by appending id)
ALTER TABLE books
  ADD UNIQUE KEY IF NOT EXISTS `slug` (`slug`(191));

-- 4) Ensure reviews default to 'approved' in the schema (idempotent)
ALTER TABLE reviews
  MODIFY COLUMN status ENUM('pending','approved') DEFAULT 'approved';

-- 5) Create review_comments table for review discussion threads
CREATE TABLE IF NOT EXISTS review_comments (
  id INT NOT NULL AUTO_INCREMENT,
  review_id INT NOT NULL,
  user_id INT NOT NULL,
  parent_id INT DEFAULT NULL,
  comment TEXT NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY review_id (review_id),
  KEY parent_id (parent_id),
  KEY user_id (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 6) Recalculate all book average ratings from approved reviews
UPDATE books b
SET average_rating = (
  SELECT IFNULL(AVG(r.rating), 0) FROM reviews r WHERE r.book_id = b.id AND r.status = 'approved'
);

-- After running this migration you can optionally run `php tools/generate_slugs.php`
-- to produce nicer slugs (the PHP script will skip books which already have slugs).
