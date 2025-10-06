-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Oct 06, 2025 at 09:39 PM
-- Server version: 8.0.30
-- PHP Version: 8.3.6

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_laravel_curahhujan_hw`
--

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint UNSIGNED NOT NULL,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `forecast_results`
--

CREATE TABLE `forecast_results` (
  `id` bigint UNSIGNED NOT NULL,
  `forecast_date` date NOT NULL,
  `forecast_value` float NOT NULL,
  `model_used` enum('additive','multiplicative') NOT NULL,
  `alpha` float DEFAULT NULL,
  `beta` float DEFAULT NULL,
  `gamma` float DEFAULT NULL,
  `mape` float DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint UNSIGNED NOT NULL,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint UNSIGNED NOT NULL,
  `reserved_at` int UNSIGNED DEFAULT NULL,
  `available_at` int UNSIGNED NOT NULL,
  `created_at` int UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int UNSIGNED NOT NULL,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2025_09_28_042354_add_role_to_users_table', 2);

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `rainfall_data`
--

CREATE TABLE `rainfall_data` (
  `id` varchar(10) NOT NULL,
  `date` date NOT NULL,
  `rainfall_amount` decimal(10,2) NOT NULL,
  `rain_days` int NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `rainfall_data`
--

INSERT INTO `rainfall_data` (`id`, `date`, `rainfall_amount`, `rain_days`, `created_at`, `updated_at`) VALUES
('Apr-2021', '2021-04-01', '81.00', 15, '2025-10-04 03:55:10', '2025-10-04 03:55:10'),
('Apr-2022', '2022-04-01', '261.00', 19, '2025-10-05 23:34:48', '2025-10-05 23:34:48'),
('Apr-2023', '2023-04-01', '203.00', 18, '2025-10-06 01:28:31', '2025-10-06 01:28:31'),
('Apr-2024', '2024-04-01', '259.00', 17, '2025-10-06 01:32:38', '2025-10-06 01:32:38'),
('Aug-2021', '2021-08-01', '3.00', 2, '2025-10-04 03:56:42', '2025-10-04 03:56:42'),
('Aug-2022', '2022-08-01', '18.00', 7, '2025-10-05 23:40:45', '2025-10-05 23:40:45'),
('Aug-2023', '2023-08-01', '0.00', 0, '2025-10-06 01:30:04', '2025-10-06 01:30:04'),
('Aug-2024', '2024-08-01', '13.00', 2, '2025-10-06 01:34:16', '2025-10-06 01:34:16'),
('Dec-2021', '2021-12-01', '178.00', 22, '2025-10-04 03:57:46', '2025-10-04 03:57:46'),
('Dec-2022', '2022-12-01', '144.00', 20, '2025-10-05 23:38:04', '2025-10-05 23:38:04'),
('Dec-2023', '2023-12-01', '183.00', 18, '2025-10-06 01:31:04', '2025-10-06 01:31:04'),
('Dec-2024', '2024-12-01', '312.00', 24, '2025-10-06 01:35:21', '2025-10-06 01:35:21'),
('Feb-2021', '2021-02-01', '286.00', 26, '2025-10-04 03:54:21', '2025-10-04 03:54:21'),
('Feb-2022', '2022-02-01', '160.00', 12, '2025-10-05 23:34:04', '2025-10-05 23:34:04'),
('Feb-2023', '2023-02-01', '164.00', 19, '2025-10-06 01:27:49', '2025-10-06 01:27:49'),
('Feb-2024', '2024-02-01', '226.00', 18, '2025-10-06 01:32:10', '2025-10-06 01:33:00'),
('Jan-2021', '2021-01-01', '361.00', 29, '2025-10-04 03:53:59', '2025-10-04 03:53:59'),
('Jan-2022', '2022-01-01', '97.00', 14, '2025-10-05 23:33:37', '2025-10-05 23:33:37'),
('Jan-2023', '2023-01-01', '143.00', 18, '2025-10-06 01:27:26', '2025-10-06 01:27:26'),
('Jan-2024', '2024-01-01', '176.00', 16, '2025-10-06 01:31:59', '2025-10-06 01:31:59'),
('Jul-2021', '2021-07-01', '1.00', 2, '2025-10-04 03:56:17', '2025-10-04 03:56:17'),
('Jul-2022', '2022-07-01', '20.00', 5, '2025-10-05 23:35:45', '2025-10-05 23:35:45'),
('Jul-2023', '2023-07-01', '7.00', 4, '2025-10-06 01:29:49', '2025-10-06 01:29:49'),
('Jul-2024', '2024-07-01', '40.00', 3, '2025-10-06 01:33:50', '2025-10-06 01:33:50'),
('Jun-2021', '2021-06-01', '65.00', 11, '2025-10-04 03:55:59', '2025-10-04 03:55:59'),
('Jun-2022', '2022-06-01', '128.00', 11, '2025-10-05 23:35:24', '2025-10-05 23:36:10'),
('Jun-2023', '2023-06-01', '37.00', 3, '2025-10-06 01:29:25', '2025-10-06 01:29:25'),
('Jun-2024', '2024-06-01', '20.00', 2, '2025-10-06 01:33:37', '2025-10-06 01:33:37'),
('Mar-2021', '2021-03-01', '246.00', 22, '2025-10-04 03:54:53', '2025-10-04 03:54:53'),
('Mar-2022', '2022-03-01', '148.00', 18, '2025-10-05 23:34:29', '2025-10-05 23:34:29'),
('Mar-2023', '2023-03-01', '126.00', 14, '2025-10-06 01:28:13', '2025-10-06 01:28:13'),
('Mar-2024', '2024-03-01', '217.00', 22, '2025-10-06 01:32:23', '2025-10-06 01:32:23'),
('May-2021', '2021-05-01', '28.00', 8, '2025-10-04 03:55:44', '2025-10-04 03:55:44'),
('May-2022', '2022-05-01', '41.00', 7, '2025-10-05 23:35:11', '2025-10-05 23:35:11'),
('May-2023', '2023-05-01', '8.00', 2, '2025-10-06 01:28:55', '2025-10-06 01:28:55'),
('May-2024', '2024-05-01', '1.00', 1, '2025-10-06 01:33:26', '2025-10-06 01:33:26'),
('Nov-2021', '2021-11-01', '308.00', 25, '2025-10-04 03:57:26', '2025-10-04 03:57:26'),
('Nov-2022', '2022-11-01', '275.00', 27, '2025-10-05 23:37:37', '2025-10-05 23:37:37'),
('Nov-2023', '2023-11-01', '121.00', 12, '2025-10-06 01:30:51', '2025-10-06 01:30:51'),
('Nov-2024', '2024-11-01', '65.00', 9, '2025-10-06 01:35:10', '2025-10-06 01:35:10'),
('Oct-2021', '2021-10-01', '55.00', 8, '2025-10-04 03:57:11', '2025-10-04 03:57:11'),
('Oct-2022', '2022-10-01', '244.00', 26, '2025-10-05 23:37:17', '2025-10-05 23:37:17'),
('Oct-2023', '2023-10-01', '4.00', 1, '2025-10-06 01:30:37', '2025-10-06 01:30:37'),
('Oct-2024', '2024-10-01', '24.00', 5, '2025-10-06 01:34:49', '2025-10-06 01:34:49'),
('Sep-2021', '2021-09-01', '51.00', 4, '2025-10-05 23:32:03', '2025-10-05 23:32:03'),
('Sep-2022', '2022-09-01', '73.00', 12, '2025-10-05 23:36:30', '2025-10-05 23:40:27'),
('Sep-2023', '2023-09-01', '0.00', 0, '2025-10-06 01:30:23', '2025-10-06 01:30:23'),
('Sep-2024', '2024-09-01', '54.00', 2, '2025-10-06 01:34:36', '2025-10-06 01:34:36');

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'user',
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `email_verified_at`, `password`, `role`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 'admin123', 'admin123@gmail.com', NULL, '$2y$12$B.WT9cOzeH.wMBRPcimCau173ZmidE44n8VHXkHCDiVVD3dwFUJC6', 'admin', 'Lu7yVFeKuYAAlT0coesSyzuEEmEdRZjKIawnIQ4yXW8mFDsTTZnCoRmlXylX', '2025-09-26 18:13:00', '2025-09-26 18:13:00'),
(3, 'abc', 'abc123@gmail.com', NULL, '$2y$12$CI2t1zFh0lAyLJBjuaTFpuIyD8DtrHQh.SZoamyiFDnjKYXmxPVt6', 'user', NULL, '2025-10-03 22:36:17', '2025-10-03 22:36:17');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `forecast_results`
--
ALTER TABLE `forecast_results`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indexes for table `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `rainfall_data`
--
ALTER TABLE `rainfall_data`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `forecast_results`
--
ALTER TABLE `forecast_results`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
