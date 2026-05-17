-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: May 17, 2026 at 11:29 AM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `Book_Wise`
--

-- --------------------------------------------------------

--
-- Table structure for table `books`
--

CREATE TABLE `books` (
  `id` int(11) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `author` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `genre` varchar(100) NOT NULL DEFAULT 'Fiction',
  `slug` varchar(191) DEFAULT NULL,
  `cover_image` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `books`
--

INSERT INTO `books` (`id`, `title`, `author`, `description`, `created_at`, `genre`, `slug`, `cover_image`) VALUES
(1, 'Sample Book', 'Author Name', 'Test description', '2026-05-05 05:57:52', 'Fiction', NULL, NULL),
(2, 'Harry Potter and the Sorcerer\'s Stone', 'J.K. Rowling', 'A magical adventure at Hogwarts School of Witchcraft and Wizardry.', '2026-05-09 17:02:40', 'Fantasy', 'harry-potter-and-the-sorcerers-stone', 'https://images.unsplash.com/photo-1512820790803-83ca734da794?auto=format&fit=crop&w=700&q=80'),
(3, 'The Silent Patient', 'Alex Michaelides', 'A psychological mystery around a famous painter who stops speaking.', '2026-05-09 17:02:40', 'Mystery', 'the-silent-patient', 'https://images.unsplash.com/photo-1544947950-fa07a98d237f?auto=format&fit=crop&w=700&q=80'),
(4, 'Atomic Habits', 'James Clear', 'Practical framework for building good habits and breaking bad ones.', '2026-05-09 17:02:40', 'Self-help', 'atomic-habits', 'https://images.unsplash.com/photo-1495446815901-a7297e633e8d?auto=format&fit=crop&w=700&q=80'),
(5, 'A Brief History of Time', 'Stephen Hawking', 'A concise exploration of cosmology, black holes, and the universe.', '2026-05-09 17:02:40', 'Science', 'a-brief-history-of-time', 'https://images.unsplash.com/photo-1455885666463-9b1eb39f3f59?auto=format&fit=crop&w=700&q=80'),
(6, 'The Alchemist', 'Paulo Coelho', 'A fiction classic about purpose, dreams, and destiny.', '2026-05-09 17:02:40', 'Fiction', 'the-alchemist', 'https://images.unsplash.com/photo-1515098506762-79e1384e9d8e?auto=format&fit=crop&w=700&q=80'),
(7, 'The Hobbit', 'J.R.R. Tolkien', 'Bilbo Baggins goes on a fantasy quest with dwarves and a wizard.', '2026-05-09 17:02:40', 'Fantasy', 'the-hobbit', 'https://images.unsplash.com/photo-1529148482759-b35b25c5f217?auto=format&fit=crop&w=700&q=80'),
(8, 'The Girl with the Dragon Tattoo', 'Stieg Larsson', 'A gripping mystery investigation led by an unlikely duo.', '2026-05-09 17:02:40', 'Mystery', 'the-girl-with-the-dragon-tattoo', 'https://images.unsplash.com/photo-1495640388908-05fa85288e61?auto=format&fit=crop&w=700&q=80'),
(9, 'Deep Work', 'Cal Newport', 'Strategies to improve focus and produce high-value work.', '2026-05-09 17:02:40', 'Self-help', 'deep-work', 'https://images.unsplash.com/photo-1516979187457-637abb4f9353?auto=format&fit=crop&w=700&q=80'),
(10, 'Cosmos', 'Carl Sagan', 'A science journey across stars, galaxies, and human curiosity.', '2026-05-09 17:02:40', 'Science', 'cosmos', 'https://images.unsplash.com/photo-1462331940025-496dfbfc7564?auto=format&fit=crop&w=700&q=80'),
(11, 'The Kite Runner', 'Khaled Hosseini', 'A moving fiction novel about friendship and redemption.', '2026-05-09 17:02:40', 'Fiction', 'the-kite-runner', 'https://images.unsplash.com/photo-1507842217343-583bb7270b66?auto=format&fit=crop&w=700&q=80'),
(12, 'Project Hail Mary', 'Andy Weir', 'A science-driven space survival story full of problem-solving.', '2026-05-09 17:02:40', 'Science', 'project-hail-mary', 'https://images.unsplash.com/photo-1465101046530-73398c7f28ca?auto=format&fit=crop&w=700&q=80'),
(13, 'Think and Grow Rich', 'Napoleon Hill', 'A self-help classic about mindset and achievement.', '2026-05-09 17:02:40', 'Self-help', 'think-and-grow-rich', 'https://images.unsplash.com/photo-1519681393784-d120267933ba?auto=format&fit=crop&w=700&q=80');

-- --------------------------------------------------------

--
-- Table structure for table `genres`
--

CREATE TABLE `genres` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `slug` varchar(120) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `genres`
--

INSERT INTO `genres` (`id`, `name`, `slug`, `created_at`) VALUES
(1, 'Fiction', 'fiction', '2026-05-09 17:02:40'),
(2, 'Fantasy', 'fantasy', '2026-05-09 17:02:40'),
(3, 'Mystery', 'mystery', '2026-05-09 17:02:40'),
(4, 'Self-help', 'self-help', '2026-05-09 17:02:40'),
(5, 'Science', 'science', '2026-05-09 17:02:40');

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `book_id` int(11) DEFAULT NULL,
  `rating` int(11) DEFAULT NULL CHECK (`rating` between 1 and 5),
  `comment` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reviews`
--

INSERT INTO `reviews` (`id`, `user_id`, `book_id`, `rating`, `comment`, `created_at`) VALUES
(1, 3, 1, 5, 'This is an excellent book with great content!', '2026-05-05 06:49:40'),
(2, 1, 13, 5, 'Seeder demo review with rating 5/5', '2026-05-09 17:02:40'),
(3, 1, 12, 5, 'Seeder demo review with rating 5/5', '2026-05-09 17:02:40'),
(4, 1, 11, 3, 'Seeder demo review with rating 3/5', '2026-05-09 17:02:40'),
(5, 1, 10, 3, 'Seeder demo review with rating 3/5', '2026-05-09 17:02:40'),
(6, 1, 9, 3, 'Seeder demo review with rating 3/5', '2026-05-09 17:02:40'),
(7, 1, 8, 3, 'Seeder demo review with rating 3/5', '2026-05-09 17:02:40'),
(8, 1, 7, 3, 'Seeder demo review with rating 3/5', '2026-05-09 17:02:40'),
(9, 1, 6, 3, 'Seeder demo review with rating 3/5', '2026-05-09 17:02:40'),
(10, 1, 5, 4, 'Seeder demo review with rating 4/5', '2026-05-09 17:02:40'),
(11, 1, 4, 5, 'Seeder demo review with rating 5/5', '2026-05-09 17:02:40'),
(12, 1, 3, 4, 'Seeder demo review with rating 4/5', '2026-05-09 17:02:40'),
(13, 1, 2, 3, 'Seeder demo review with rating 3/5', '2026-05-09 17:02:40');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `created_at`) VALUES
(1, 'Test User', 'test@gmail.com', '123456', '2026-05-05 05:57:52'),
(2, 'Mehek', 'demo@internx.com', '$2y$10$qCvKTohqLLYvy6rN2I3je.rXKSdxI5JqhT/JagHqEkKQFXga5jEke', '2026-05-05 06:09:22'),
(3, 'John Doe', 'john@example.com', '$2y$10$S5rxbH1xyR./N9p4UeEa0.Hd5l4vnKG4p.wrCMY28xzHjLE/2ktPC', '2026-05-05 06:48:22'),
(4, 'Mehek', 'mparsaamin@gmail.com', '$2y$10$33VzbLXTOka.DRx7z1v2j.HDLPA0ROaVVnir5edQLXXh37H8cV8tq', '2026-05-05 18:13:04'),
(5, 'Test User', 'test@example.com', '$2y$10$GMExBWihKW9pYel14IQi4OQIkLqoO94/EHuDP4ASKh.xhI.zP1zx.', '2026-05-05 18:49:46');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `books`
--
ALTER TABLE `books`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `idx_books_slug` (`slug`);

--
-- Indexes for table `genres`
--
ALTER TABLE `genres`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`,`book_id`),
  ADD KEY `book_id` (`book_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `books`
--
ALTER TABLE `books`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- AUTO_INCREMENT for table `genres`
--
ALTER TABLE `genres`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`book_id`) REFERENCES `books` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
