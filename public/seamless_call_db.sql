-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Jan 29, 2026 at 05:48 PM
-- Server version: 8.4.3
-- PHP Version: 8.3.26

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `seamless_call_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity_log`
--

CREATE TABLE `activity_log` (
  `id` int UNSIGNED NOT NULL,
  `user_id` int NOT NULL,
  `action` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `description` text COLLATE utf8mb4_general_ci,
  `ip_address` varchar(45) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_general_ci,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `activity_log`
--

INSERT INTO `activity_log` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES
(1, 31, 'check out profile', 'yes', NULL, NULL, '2026-01-17 14:49:01');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `description` text COLLATE utf8mb4_general_ci,
  `status` enum('active','inactive') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'active',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `description`, `status`, `created_at`, `updated_at`) VALUES
(1, 'plumbing', 'hhhhh', 'active', '2026-01-17 16:16:08', '2026-01-17 16:16:08'),
(2, 'electrical', 'ljiojioji', 'active', '2026-01-17 16:16:08', '2026-01-17 19:36:52'),
(3, 'Carpentary', 'bgsgs', 'active', '2026-01-17 16:34:37', '2026-01-17 16:34:37'),
(4, 'Cleaning Services', 'Offices and homes', 'active', '2026-01-17 20:00:06', '2026-01-18 06:29:28');

-- --------------------------------------------------------

--
-- Table structure for table `earnings`
--

CREATE TABLE `earnings` (
  `id` int UNSIGNED NOT NULL,
  `provider_id` int NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `description` text COLLATE utf8mb4_general_ci,
  `job_id` int UNSIGNED DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `earnings`
--

INSERT INTO `earnings` (`id`, `provider_id`, `amount`, `description`, `job_id`, `created_at`) VALUES
(1, 33, 20000.00, 'huhuhu', 21, '2026-01-17 15:34:38');

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` int UNSIGNED NOT NULL,
  `customer_id` int NOT NULL,
  `provider_id` int DEFAULT NULL,
  `service_id` int UNSIGNED NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `description` text COLLATE utf8mb4_general_ci,
  `status` enum('pending','active','scheduled','completed','cancelled','escalated') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'pending',
  `scheduled_time` datetime NOT NULL,
  `completed_at` datetime DEFAULT NULL,
  `cancelled_at` datetime DEFAULT NULL,
  `assigned_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `escalation_reason` text COLLATE utf8mb4_general_ci,
  `escalated_at` datetime DEFAULT NULL,
  `escalated_by` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `jobs`
--

INSERT INTO `jobs` (`id`, `customer_id`, `provider_id`, `service_id`, `title`, `description`, `status`, `scheduled_time`, `completed_at`, `cancelled_at`, `assigned_at`, `created_at`, `updated_at`, `escalation_reason`, `escalated_at`, `escalated_by`) VALUES
(1, 40, 35, 1, 'I need borehole', 'yes bohe hole in U Dosa', 'cancelled', '2026-01-21 18:59:33', NULL, '2026-01-26 16:19:32', '2026-01-25 12:32:24', '2026-01-21 20:00:56', '2026-01-26 16:19:32', 'reason', '2026-01-26 16:16:11', 34),
(2, 31, 35, 5, 'Chairs', 'yes chairs in U Sarki', 'active', '2026-01-21 18:59:33', NULL, NULL, '2026-01-26 16:20:27', '2026-01-21 20:00:56', '2026-01-26 16:20:27', NULL, NULL, NULL),
(3, 31, 37, 5, 'Chairs', 'yes chairs in U Sarki', 'active', '2026-01-21 18:59:33', NULL, NULL, '2026-01-26 14:55:01', '2026-01-21 20:00:56', '2026-01-26 14:55:01', NULL, NULL, NULL),
(4, 31, 31, 3, 'Chairs', 'yes chairs in U Sarki', 'cancelled', '2026-01-21 18:59:33', NULL, '2026-01-26 21:23:03', NULL, '2026-01-21 20:00:56', '2026-01-26 21:23:03', 'client not paying up balance', '2026-01-26 15:57:06', 34),
(5, 40, 33, 4, 'power', 'yes chairs in U Sarki', 'active', '2026-01-21 18:59:33', NULL, NULL, NULL, '2026-01-21 20:00:56', '2026-01-26 16:15:26', NULL, NULL, NULL),
(6, 41, 35, 5, 'tables', 'yes chairs in U Sunusi', 'active', '2026-01-21 18:59:33', NULL, NULL, '2026-01-26 21:25:04', '2026-01-21 20:00:56', '2026-01-26 21:25:04', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `ledger`
--

CREATE TABLE `ledger` (
  `id` int UNSIGNED NOT NULL,
  `user_id` int NOT NULL,
  `transaction_type` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `description` text COLLATE utf8mb4_general_ci,
  `reference` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ledger`
--

INSERT INTO `ledger` (`id`, `user_id`, `transaction_type`, `amount`, `description`, `reference`, `created_at`) VALUES
(1, 31, 'service payment', 30000.00, 'Job well done', 'qwe23533reeded', '2026-01-15 23:17:19'),
(2, 33, 'service payment', 30000.00, 'Job well done', 'qwe23533reeded', '2026-01-15 23:17:19');

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` bigint UNSIGNED NOT NULL,
  `version` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `class` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `group` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `namespace` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `time` int NOT NULL,
  `batch` int UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `version`, `class`, `group`, `namespace`, `time`, `batch`) VALUES
(1, '2026-01-10-130114', 'App\\Database\\Migrations\\AddPurposeToOtpsTable', 'default', 'App', 1768050121, 1),
(2, '2026-01-11-111128', 'App\\Database\\Migrations\\AddUsedAtToOtpsTable', 'default', 'App', 1768129919, 2),
(3, '2026-01-11-111518', 'App\\Database\\Migrations\\AddUpdatedAtToOtpsTable', 'default', 'App', 1768130144, 3),
(4, '2026-01-11-111943', 'App\\Database\\Migrations\\RenameOtpToOtpHashInOtpsTable', 'default', 'App', 1768130410, 4),
(5, '2026-01-11-112306', 'App\\Database\\Migrations\\AddIpAddressToOtpsTable', 'default', 'App', 1768130609, 5),
(6, '2026-01-15-220000', 'App\\Database\\Migrations\\CreateLedgerTable', 'default', 'App', 1768515178, 6),
(7, '2026-01-15-220001', 'App\\Database\\Migrations\\CreateRefundsTable', 'default', 'App', 1768515310, 7),
(8, '2026-01-15-220002', 'App\\Database\\Migrations\\CreateActivityLogTable', 'default', 'App', 1768515310, 7),
(9, '2026-01-16-000000', 'App\\Database\\Migrations\\CreateEarningsTable', 'default', 'App', 1768519793, 8),
(10, '2026-01-16-000001', 'App\\Database\\Migrations\\CreatePayoutsTable', 'default', 'App', 1768519793, 8),
(11, '2026-01-16-010000', 'App\\Database\\Migrations\\CreateCategoriesTable', 'default', 'App', 1768523763, 9),
(12, '2026-01-16-010001', 'App\\Database\\Migrations\\CreateServicesTable', 'default', 'App', 1768523764, 9),
(13, '2026-01-18-134113', 'App\\Database\\Migrations\\CreateRolesAndPermissionsTables', 'default', 'App', 1768743695, 10),
(14, '2026-01-18-151031', 'App\\Database\\Migrations\\CreateUserRolesTable', 'default', 'App', 1768749074, 11),
(15, '2026-01-18-202850', 'App\\Database\\Migrations\\CreateJobsTable', 'default', 'App', 1768768256, 12),
(17, '2026-01-27-193113', 'App\\Database\\Migrations\\CreateVerificationCases', 'default', 'App', 1769546899, 13);

-- --------------------------------------------------------

--
-- Table structure for table `otps`
--

CREATE TABLE `otps` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `otp_hash` varchar(255) NOT NULL,
  `channel` enum('email','whatsapp') NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `used_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `purpose` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `otps`
--

INSERT INTO `otps` (`id`, `user_id`, `otp_hash`, `channel`, `expires_at`, `created_at`, `used_at`, `updated_at`, `ip_address`, `purpose`) VALUES
(1, 34, '$2y$10$Sy.hlCgTKLnaSdLfhxPzHuteIfZW/0h/UvHsN7VSVZt4MUqD6W59y', 'whatsapp', '2026-01-11 11:28:48', '2026-01-11 10:23:48', NULL, '2026-01-11 11:23:48', '10.56.2.59', 'login'),
(2, 34, '$2y$10$o3mHkNY2Ao6LUUPeZPlDFODeXBxrv9IUfZ8n7/lw20irp7FoN9qKO', 'whatsapp', '2026-01-11 12:57:13', '2026-01-11 11:52:13', NULL, '2026-01-11 12:52:13', '10.56.2.59', 'login'),
(3, 34, '$2y$10$MIeziW.SedRjxdlolPrniuLavsd7Q7xzy8etmY1wj5ifK7U1JNRui', 'email', '2026-01-11 15:51:43', '2026-01-11 14:46:43', '2026-01-11 15:46:43', '2026-01-11 15:46:43', '10.56.2.59', 'login'),
(4, 34, '$2y$10$E81JoY4M2XbVab/.oZEkT.QaL.VZMHKHqCmN34zjYP8E8indHsN.u', 'whatsapp', '2026-01-11 15:51:43', '2026-01-11 14:46:43', '2026-01-11 15:50:24', '2026-01-11 15:50:24', '10.56.2.59', 'login'),
(5, 35, '$2y$10$BH5a94ccsErHPQ4aMh5nXOI/r4VOyvcSP3s56mTVcd0NM9MpKFcTq', 'email', '2026-01-11 15:56:55', '2026-01-11 14:51:55', '2026-01-11 15:51:55', '2026-01-11 15:51:55', '10.56.2.59', 'login'),
(6, 35, '$2y$10$VRy7j4YZuqf204A52VdJyu46bg9KjjcpG6n1LR.Ud.ZK0BeLB6O0S', 'whatsapp', '2026-01-11 15:56:55', '2026-01-11 14:51:55', '2026-01-11 15:52:43', '2026-01-11 15:52:43', '10.56.2.59', 'login'),
(7, 34, '$2y$10$bgDiVRw8jyFOLTCAGTz08.qZw4sYzhqMoHHzG42DLFy025zEttnA2', 'email', '2026-01-11 16:03:40', '2026-01-11 14:58:40', '2026-01-11 15:58:40', '2026-01-11 15:58:40', '10.56.2.59', 'login'),
(8, 34, '$2y$10$.56.FC6z80drINxj3YG67uP8fVomwgcVDqBTdt7vUKqWYuN1K0APO', 'whatsapp', '2026-01-11 16:03:40', '2026-01-11 14:58:40', NULL, '2026-01-11 15:58:40', '10.56.2.59', 'login'),
(9, 34, '$2y$10$EOD.DJKCazXrDKrF4DceteWEWHaIQQXasB/bhKRRVpTGPU70QDDf2', 'email', '2026-01-11 16:32:30', '2026-01-11 15:27:30', '2026-01-11 16:27:30', '2026-01-11 16:27:30', '10.56.2.59', 'login'),
(10, 34, '$2y$10$Hv7uIxoMRznjbgPzYB9Ot.eqxTLaaR6mc8vVUKwYCkiSlftj3nDQO', 'whatsapp', '2026-01-11 16:32:30', '2026-01-11 15:27:30', NULL, '2026-01-11 16:27:30', '10.56.2.59', 'login'),
(11, 34, '$2y$10$8NJqt8BlbImVxcqEY.A1EOCp5uLLExuaApkAB5/Yh.SCYe0EUe8zq', 'email', '2026-01-11 16:45:35', '2026-01-11 15:40:35', '2026-01-11 16:40:35', '2026-01-11 16:40:35', '10.56.2.59', 'login'),
(12, 34, '$2y$10$eEZtR46OPh240dcggVcni.Rzy/oMjed7oXbVJWmJWcYwJs3JzHLRe', 'whatsapp', '2026-01-11 16:45:35', '2026-01-11 15:40:35', '2026-01-11 16:42:56', '2026-01-11 16:42:56', '10.56.2.59', 'login'),
(13, 34, '$2y$10$weC7Ty7tpeND.WhNF1NczeP8QaV4JAn4CHaZ6zzmX07lTTSS3xch.', 'email', '2026-01-11 16:47:56', '2026-01-11 15:42:56', '2026-01-11 16:42:56', '2026-01-11 16:42:56', '10.56.2.59', 'login'),
(14, 34, '$2y$10$Yvt2gKdHMsgY3LIBsHua7e6cSnLXaFsOqrzxSP602kiUommtusKG2', 'whatsapp', '2026-01-11 16:47:56', '2026-01-11 15:42:56', NULL, '2026-01-11 16:42:56', '10.56.2.59', 'login'),
(15, 34, '$2y$10$R7kb6JHp91C9IjC8UWNnyuhzLgs/v5ebHdWiK4QTsFoIm8SVnvIRC', 'email', '2026-01-11 19:38:32', '2026-01-11 18:33:32', '2026-01-11 19:33:32', '2026-01-11 19:33:32', '10.56.2.59', 'login'),
(16, 34, '$2y$10$rdn/KIugIvptG/LfkPhHJ.oelXhRWzApGMzLUSnwtfnSdEV0h3hHO', 'whatsapp', '2026-01-11 19:38:32', '2026-01-11 18:33:32', '2026-01-11 19:34:58', '2026-01-11 19:34:58', '10.56.2.59', 'login'),
(17, 34, '$2y$10$OKPTjzyFALvGSwVBRMIEs.gYolt.4kxdngwHd8mEfArtVX0Z3Yz6y', 'email', '2026-01-11 19:39:58', '2026-01-11 18:34:58', '2026-01-11 19:34:58', '2026-01-11 19:34:58', '10.56.2.59', 'login'),
(18, 34, '$2y$10$0UxhGcL0xEYaXze/1YMj5uZLsDFC7agiaQ26nRLqKUCyDPGXawoGW', 'whatsapp', '2026-01-11 19:39:58', '2026-01-11 18:34:58', '2026-01-11 19:37:25', '2026-01-11 19:37:25', '10.56.2.59', 'login'),
(19, 34, '$2y$10$s49WaMMvSeUEGA2ciSolYOaXyFlgc79DMrMmy6blBNJUmFPto0ARq', 'email', '2026-01-11 19:42:25', '2026-01-11 18:37:25', '2026-01-11 19:37:25', '2026-01-11 19:37:25', '10.56.2.59', 'login'),
(20, 34, '$2y$10$0OR6zpEoiIXE.xk/rG0tjuOrc9jbWFl/8d/6gWsWc1NkbdiZ.Y2Ki', 'whatsapp', '2026-01-11 19:42:25', '2026-01-11 18:37:25', '2026-01-11 19:39:02', '2026-01-11 19:39:02', '10.56.2.59', 'login'),
(21, 34, '$2y$10$Th9y5ak.iSXKJ5j375kB8.lj7S4n7b2GFqAaTqBSMc9xwuJf5fBiW', 'email', '2026-01-11 19:44:03', '2026-01-11 18:39:03', '2026-01-11 19:39:03', '2026-01-11 19:39:03', '10.56.2.59', 'login'),
(22, 34, '$2y$10$ZqjZL3/FhZUSQ9gQUL59FeRev.VLuA5jXcf7g2hickqo.rkkM9.Vy', 'whatsapp', '2026-01-11 19:44:03', '2026-01-11 18:39:03', '2026-01-11 19:39:48', '2026-01-11 19:39:48', '10.56.2.59', 'login'),
(23, 34, '$2y$10$Y.4/qvw0IzyqIyUgXJhPYugPFcA4w.y3651F1jl8xyfeyV0VP8DJy', 'email', '2026-01-12 11:05:07', '2026-01-12 10:00:07', '2026-01-12 11:00:07', '2026-01-12 11:00:07', '192.168.1.221', 'login'),
(24, 34, '$2y$10$g1Xn/R5ZB.5Yn5LPLkhJo.G4.f9enAz47lVWQRj01ASuminDsPF9O', 'whatsapp', '2026-01-12 11:05:08', '2026-01-12 10:00:08', '2026-01-12 11:00:51', '2026-01-12 11:00:51', '192.168.1.221', 'login'),
(25, 34, '$2y$10$EYovpp8AAQgHHkFTiHwHb.bqzqnksKJWIXLP5Q2UTuk/B9Daak8l6', 'email', '2026-01-12 11:13:22', '2026-01-12 10:08:22', '2026-01-12 11:08:22', '2026-01-12 11:08:22', '192.168.1.61', 'login'),
(26, 34, '$2y$10$GqBnG/5QD7hZ9A5P0MwNUOkdXV0i4CBvuqTQOhC/VaEufPDu.j.OO', 'whatsapp', '2026-01-12 11:13:22', '2026-01-12 10:08:22', '2026-01-12 11:09:27', '2026-01-12 11:09:27', '192.168.1.61', 'login'),
(27, 31, '$2y$10$d/POL8kNsc8pHqEfK0EPWuPHm4uyq8rSqhfiF4rU8BVN7oifxFS52', 'email', '2026-01-12 11:18:42', '2026-01-12 10:13:42', '2026-01-12 11:13:42', '2026-01-12 11:13:42', '192.168.1.61', 'login'),
(28, 31, '$2y$10$xhWpyeoRBG5f3Xdg0Q6r9.Px50UIoB.xsdSWlJcr2DsYtgivdVrKW', 'whatsapp', '2026-01-12 11:18:42', '2026-01-12 10:13:42', '2026-01-12 11:14:06', '2026-01-12 11:14:06', '192.168.1.61', 'login'),
(29, 31, '$2y$10$5/uwN.k4G.Nm/pLWWKj2BOvE/eM30ROZZEEudT44kXOc74I26NE8W', 'email', '2026-01-12 11:20:02', '2026-01-12 10:15:02', '2026-01-12 11:15:02', '2026-01-12 11:15:02', '192.168.1.221', 'login'),
(30, 31, '$2y$10$S80MvSKvlJf8ycZx.PIetedTnoAEu7TbBvlpmyHNreIVkXnX37h0a', 'whatsapp', '2026-01-12 11:20:02', '2026-01-12 10:15:02', '2026-01-12 11:15:37', '2026-01-12 11:15:37', '192.168.1.221', 'login'),
(31, 31, '$2y$10$2.3FiSPGG6FSyAW3nuisTuBnHN.jmRlM4HmTjfeoqjOdGzOwDUqV2', 'email', '2026-01-12 11:20:52', '2026-01-12 10:15:52', '2026-01-12 11:15:52', '2026-01-12 11:15:52', '192.168.1.61', 'login'),
(32, 31, '$2y$10$DCPx6bQZ2zKy6yy0QjaWxO8Cut0pwUV.wthcosym5Rk9EC7sBbP6K', 'whatsapp', '2026-01-12 11:20:52', '2026-01-12 10:15:52', '2026-01-12 11:16:51', '2026-01-12 11:16:51', '192.168.1.61', 'login'),
(33, 34, '$2y$10$Bq0bDeKlNvJzo.jwdYEPVuF4y.SNxzAQMJyV6Jha7vDNiCijErbBW', 'email', '2026-01-13 18:19:04', '2026-01-13 17:14:04', '2026-01-13 18:14:04', '2026-01-13 18:14:04', '10.88.93.59', 'login'),
(34, 34, '$2y$10$ln9/lyUPaLk.iFLNekmAFuPOQPqG7unMzAv8g5va36omIQRr7Aqrq', 'whatsapp', '2026-01-13 18:19:04', '2026-01-13 17:14:04', NULL, '2026-01-13 18:14:04', '10.88.93.59', 'login'),
(35, 34, '$2y$10$pKyPDFnxWfRaBufZQHUhZ..DWg3zumtKjcI5bCgeNRM.xeW0PtIIS', 'email', '2026-01-13 18:30:50', '2026-01-13 17:25:50', '2026-01-13 18:25:50', '2026-01-13 18:25:50', '10.88.93.59', 'login'),
(36, 34, '$2y$10$n8auQmaO9WlCg1IWyulCs.MDg5z7Oc7ATSyQtEV4mJsZKt.t82kRC', 'whatsapp', '2026-01-13 18:30:50', '2026-01-13 17:25:50', '2026-01-13 18:26:23', '2026-01-13 18:26:23', '10.88.93.59', 'login'),
(37, 35, '$2y$10$vFMTU7qo5eNiVO6q5qrwxuL7d4PZwlNLJqy/K6Qa3OzR1EkwkjeGy', 'email', '2026-01-13 18:47:34', '2026-01-13 17:42:34', '2026-01-13 18:42:34', '2026-01-13 18:42:34', '10.88.93.59', 'login'),
(38, 35, '$2y$10$LfPbaEGq96NrDv.C7yfXjO/033BXW0/nSA6jldWCojaf51HEhvl/O', 'whatsapp', '2026-01-13 18:47:34', '2026-01-13 17:42:34', NULL, '2026-01-13 18:42:34', '10.88.93.59', 'login'),
(39, 34, '$2y$10$2R6N0ho/KygXkPm6XUoEbuVNBT7ajfLkl5JLCixpwiIvVBQRh9hiu', 'email', '2026-01-13 18:49:31', '2026-01-13 17:44:31', '2026-01-13 18:44:31', '2026-01-13 18:44:31', '10.88.93.59', 'login'),
(40, 34, '$2y$10$MaXq7Unevb7Dvl4nunC0weFMPuNhJD4M9Ud/5sKMSCOw1y1lWiX7.', 'whatsapp', '2026-01-13 18:49:32', '2026-01-13 17:44:32', NULL, '2026-01-13 18:44:32', '10.88.93.59', 'login'),
(41, 34, '$2y$10$9tZnos/Om0MV9JXNfaC4E.6MhdIS.wbdOLc0xCFbYmbLtTt3FRyUy', 'email', '2026-01-14 06:31:28', '2026-01-14 05:26:28', '2026-01-14 06:26:28', '2026-01-14 06:26:28', '10.88.93.59', 'login'),
(42, 34, '$2y$10$YPANx5gvgn8faY2vuNICr.vJn8bH8CHpYWGdHVTYzC6X9Iabz0/GG', 'whatsapp', '2026-01-14 06:31:28', '2026-01-14 05:26:28', '2026-01-14 06:27:17', '2026-01-14 06:27:17', '10.88.93.59', 'login'),
(43, 34, '$2y$10$Xt0BuAtMEnGLHiHSMlkw9OQNP7eo7oCWDXz.RrhWfl.fDU6nDbd12', 'email', '2026-01-14 07:05:45', '2026-01-14 06:00:45', '2026-01-14 07:00:45', '2026-01-14 07:00:45', '10.88.93.59', 'login'),
(44, 34, '$2y$10$JrSyU1k4uIUmCowL2q9Wpu/tvl6MMZmTunbc5lDUp7CHqZsrQ5tz.', 'whatsapp', '2026-01-14 07:05:45', '2026-01-14 06:00:45', '2026-01-14 07:01:42', '2026-01-14 07:01:42', '10.88.93.59', 'login'),
(45, 34, '$2y$10$o8lbTS5Xgj2rxDZOqnV1duCuX2b683H5IS/cOd2CkYd/MTuN40a..', 'email', '2026-01-14 07:10:11', '2026-01-14 06:05:11', '2026-01-14 07:05:11', '2026-01-14 07:05:11', '10.88.93.59', 'login'),
(46, 34, '$2y$10$2ey/UNIg1CLS8elLBzOZPeRvjl5zcCqQyODyS7fyELao0JpYhPZyC', 'whatsapp', '2026-01-14 07:10:11', '2026-01-14 06:05:11', '2026-01-14 07:05:52', '2026-01-14 07:05:52', '10.88.93.59', 'login'),
(47, 34, '$2y$10$uikg/Xnuxt3EiK3KKmPguuSuD69qnqG3vWG5in5VUqoGfrxDwBxE6', 'email', '2026-01-14 08:51:44', '2026-01-14 07:46:44', '2026-01-14 08:46:44', '2026-01-14 08:46:44', '10.88.93.59', 'login'),
(48, 34, '$2y$10$oftMrQDKk9L2DIsryTwNh.X8OBb2k1xesc2AahcJOrEdmo0pq78I6', 'whatsapp', '2026-01-14 08:51:44', '2026-01-14 07:46:44', NULL, '2026-01-14 08:46:44', '10.88.93.59', 'login'),
(49, 34, '$2y$10$QscMfCQGSwKY/ZNhyp0I6uXnJI56K96VqHO4ypjveSDPdUvj.kQ6y', 'email', '2026-01-14 09:04:24', '2026-01-14 07:59:24', '2026-01-14 08:59:24', '2026-01-14 08:59:24', '10.88.93.59', 'login'),
(50, 34, '$2y$10$BC14Nu.XD3tL0CI9eZzZSeuIlJQMiQVABMr0ZcJrs1L.wvjWaofc.', 'whatsapp', '2026-01-14 09:04:24', '2026-01-14 07:59:24', '2026-01-14 09:02:11', '2026-01-14 09:02:11', '10.88.93.59', 'login'),
(51, 34, '$2y$10$XW8a6vgOMo/ZgxsynUFRn.fVrSLwF50.e60r0IXqNBDm5kNilpr8G', 'email', '2026-01-14 09:07:11', '2026-01-14 08:02:11', '2026-01-14 09:02:11', '2026-01-14 09:02:11', '10.88.93.59', 'login'),
(52, 34, '$2y$10$dFrF4lcKFHf1IOrSFrIl2ut5Y1AZKavhbGOyodn2cbt7r9xQxrPbm', 'whatsapp', '2026-01-14 09:07:11', '2026-01-14 08:02:11', NULL, '2026-01-14 09:02:11', '10.88.93.59', 'login'),
(53, 34, '$2y$10$143sCZJTwbYfEyc5Toh8UuqaIACYyJx4fvjuQ4nI.BwVIGxsiIa7e', 'email', '2026-01-14 09:12:48', '2026-01-14 08:07:48', '2026-01-14 09:07:48', '2026-01-14 09:07:48', '10.88.93.59', 'login'),
(54, 34, '$2y$10$5ojtsmCw.mA5APeRsGhaQe9XuA99atmkwWJ0PVwNxYjuHXK/bHTG2', 'whatsapp', '2026-01-14 09:12:48', '2026-01-14 08:07:48', NULL, '2026-01-14 09:07:48', '10.88.93.59', 'login'),
(55, 34, '$2y$10$FnWujtbe0yKfWmVO2f747eeEVdevczPHtEDd7.s/6M8ltyHRhe2Iu', 'email', '2026-01-14 09:32:38', '2026-01-14 08:27:38', '2026-01-14 09:27:38', '2026-01-14 09:27:38', '10.88.93.59', 'login'),
(56, 34, '$2y$10$EvAH3zSV85gVMf2xazoV7.0ukc8oRvS0JzDaKs5zKJpFgZNodqCKG', 'whatsapp', '2026-01-14 09:32:38', '2026-01-14 08:27:38', NULL, '2026-01-14 09:27:38', '10.88.93.59', 'login'),
(57, 34, '$2y$10$15BLRqQDxxhc9VWCykuM5.AkwVn1WldU3bnJDHtXIKImVLjM2obkq', 'email', '2026-01-14 09:48:20', '2026-01-14 08:43:20', '2026-01-14 09:43:20', '2026-01-14 09:43:20', '10.88.93.59', 'login'),
(58, 34, '$2y$10$Rq1ik43BQGMojgEQHaBBOuvHCJAmHd5ZaFgVJLUQQbzt81zORpYs6', 'whatsapp', '2026-01-14 09:48:20', '2026-01-14 08:43:20', '2026-01-14 09:44:41', '2026-01-14 09:44:41', '10.88.93.59', 'login'),
(59, 34, '$2y$10$pU1Ww5FcE65IF2Rd85MKMeGo8E/hIj76ZAw/SXTnGXBV/HFvT3Yie', 'email', '2026-01-14 09:58:31', '2026-01-14 08:53:31', '2026-01-14 09:53:31', '2026-01-14 09:53:31', '10.88.93.59', 'login'),
(60, 34, '$2y$10$jqHEzmBGlIifkr4sEQzZRulXca9NYSTfWjLC7Uee0vWjif3nCzqTi', 'whatsapp', '2026-01-14 09:58:31', '2026-01-14 08:53:31', '2026-01-14 09:53:54', '2026-01-14 09:53:54', '10.88.93.59', 'login'),
(61, 34, '$2y$10$FNaxZbjM52gDmW84RUnZg.oxQSk1YJtQkEFmLHG9I6LrljoDQ4E7m', 'email', '2026-01-14 10:08:36', '2026-01-14 09:03:36', '2026-01-14 10:03:36', '2026-01-14 10:03:36', '10.88.93.59', 'login'),
(62, 34, '$2y$10$uLOPNu4kkzsxnLGN9jb1fOo6smGfmYYI0TOY.AfAYDeJOgW/M14tC', 'whatsapp', '2026-01-14 10:08:36', '2026-01-14 09:03:36', NULL, '2026-01-14 10:03:36', '10.88.93.59', 'login'),
(63, 34, '$2y$10$4edr9Ne/lRXG1e.ROV8gNOnBLlRXuxJpOfpekY9XF8RwtGVkC.sgy', 'email', '2026-01-14 10:19:31', '2026-01-14 09:14:31', '2026-01-14 10:14:31', '2026-01-14 10:14:31', '10.88.93.187', 'login'),
(64, 34, '$2y$10$6nFR8RPryOkU6XnnRioa0ucFsfvQMpBp4eqYhqICNDdcgcpw/BI76', 'whatsapp', '2026-01-14 10:19:31', '2026-01-14 09:14:31', '2026-01-14 10:15:21', '2026-01-14 10:15:21', '10.88.93.187', 'login'),
(65, 34, '$2y$10$vDn5ot2tfoNWkEgCrSjLKu99MsH45Cf/wu4Q38kIE4yBecEdt6QSK', 'email', '2026-01-14 10:21:28', '2026-01-14 09:16:28', '2026-01-14 10:16:28', '2026-01-14 10:16:28', '10.88.93.59', 'login'),
(66, 34, '$2y$10$gTHKtVR4XTWc6yeZyMyV8eBMxmjPvu/Xs6hQ0Zbyks.YiDRiHtDim', 'whatsapp', '2026-01-14 10:21:28', '2026-01-14 09:16:28', '2026-01-14 10:17:15', '2026-01-14 10:17:15', '10.88.93.59', 'login'),
(67, 35, '$2y$10$erR37iOkB2CutoiRjLAa/Ooui4TiHyP3myKloqeHSCBCv52/THvga', 'email', '2026-01-14 16:47:09', '2026-01-14 15:42:09', '2026-01-14 16:42:09', '2026-01-14 16:42:09', '10.56.2.59', 'login'),
(68, 35, '$2y$10$q2mm6J85sB3JJIFtpjiyVOwwOdwe2ZP.h.tT32mbloSJhAFBMNU72', 'whatsapp', '2026-01-14 16:47:09', '2026-01-14 15:42:09', '2026-01-14 16:42:58', '2026-01-14 16:42:58', '10.56.2.59', 'login'),
(69, 34, '$2y$10$F87LdvTfq0hyhajZO5htdet6/7Cs/aPiem4Rn6We7otGNboqcC68.', 'email', '2026-01-14 16:48:41', '2026-01-14 15:43:41', '2026-01-14 16:43:41', '2026-01-14 16:43:41', '10.56.2.59', 'login'),
(70, 34, '$2y$10$ErlAXRE8RvM.YQXaVdOUEOqwUgRgQYbME4.G0BcAGkevWr/ZByClG', 'whatsapp', '2026-01-14 16:48:41', '2026-01-14 15:43:41', '2026-01-14 16:44:11', '2026-01-14 16:44:11', '10.56.2.59', 'login'),
(71, 34, '$2y$10$sb/ABs6BvFfCUgS5z9hdA.0XEXrX0H9ILfLH.oqyKPrEvRO.xP/Nq', 'email', '2026-01-14 17:09:10', '2026-01-14 16:04:10', '2026-01-14 17:04:10', '2026-01-14 17:04:10', '10.88.93.59', 'login'),
(72, 34, '$2y$10$LmWpjkD5PIZpWENz1paN0eFeTcgTwRPxJjX5wQ4rh4Z2GqRV2VMFi', 'whatsapp', '2026-01-14 17:09:10', '2026-01-14 16:04:10', '2026-01-14 17:06:41', '2026-01-14 17:06:41', '10.88.93.59', 'login'),
(73, 34, '$2y$10$SLmsm9XvsYnS5fj5n6NiDe6FccQoiGHHyb9.2Bm9Trg9Yi9HEoAZW', 'email', '2026-01-14 17:26:01', '2026-01-14 16:21:01', '2026-01-14 17:21:01', '2026-01-14 17:21:01', '10.88.93.59', 'login'),
(74, 34, '$2y$10$/q4aoMFA/OkDJ4LW.70HJuT7qsLf.CAG8Oz5MYbnbSdrEqYngo3Bu', 'whatsapp', '2026-01-14 17:26:02', '2026-01-14 16:21:02', '2026-01-14 17:21:44', '2026-01-14 17:21:44', '10.88.93.59', 'login'),
(75, 34, '$2y$10$aErcoXiLP9igErv4Eqsddug3QjUw4vSiTqvVRL6pczccr9Gmv1Ot6', 'email', '2026-01-14 17:56:02', '2026-01-14 16:51:02', '2026-01-14 17:51:02', '2026-01-14 17:51:02', '10.88.93.59', 'login'),
(76, 34, '$2y$10$NdOqk1THN2bt.m8v4csTK.QImzqjJHj.5IY0HdQpMVEdgEMU16A76', 'whatsapp', '2026-01-14 17:56:02', '2026-01-14 16:51:02', '2026-01-14 17:51:38', '2026-01-14 17:51:38', '10.88.93.59', 'login'),
(77, 34, '$2y$10$VbakLptn/EAWGqmPpdDSKeOQwo2BL5jm9t2da2SDOlkOVUkjtA6lm', 'email', '2026-01-14 18:42:28', '2026-01-14 17:37:28', '2026-01-14 18:37:28', '2026-01-14 18:37:28', '10.88.93.59', 'login'),
(78, 34, '$2y$10$EsxZ0FcTTNYiYHbVxyq/Cun4A2oQa4YQJagc/Iyn1jcRplQIWBHSq', 'whatsapp', '2026-01-14 18:42:28', '2026-01-14 17:37:28', '2026-01-14 18:37:51', '2026-01-14 18:37:51', '10.88.93.59', 'login'),
(79, 34, '$2y$10$OdRwELv2jH89rg/zSxKPgOrbHyGgtA0z/kwULMdDU6bl7cRrZAXgS', 'email', '2026-01-14 18:44:46', '2026-01-14 17:39:46', '2026-01-14 18:39:46', '2026-01-14 18:39:46', '10.88.93.59', 'login'),
(80, 34, '$2y$10$uYUh94kKEp.o9rZTWpzMB.9IfR13dT.tPBrTxJFTndBsP7bRuMoQG', 'whatsapp', '2026-01-14 18:44:46', '2026-01-14 17:39:46', '2026-01-14 18:40:10', '2026-01-14 18:40:10', '10.88.93.59', 'login'),
(81, 34, '$2y$10$Q6CtfvyGYHKN4APuI0SigO3eqPYj5.pW68bqfCLQGMvSD1dP3wX9S', 'email', '2026-01-14 22:23:22', '2026-01-14 21:18:22', '2026-01-14 22:18:22', '2026-01-14 22:18:22', '10.88.93.59', 'login'),
(82, 34, '$2y$10$eYmFn66MdC3/wmZmuMA.tua1r3uVsENWLihOqbZQVzx1JCOPupqqq', 'whatsapp', '2026-01-14 22:23:22', '2026-01-14 21:18:22', '2026-01-14 22:19:00', '2026-01-14 22:19:00', '10.88.93.59', 'login'),
(83, 34, '$2y$10$pejHc319kBq3giWivH9Iwu0AdAwf8RUIz9enYtc/rtjjUSpBy5L7O', 'email', '2026-01-14 22:24:48', '2026-01-14 21:19:48', '2026-01-14 22:19:48', '2026-01-14 22:19:48', '10.88.93.59', 'login'),
(84, 34, '$2y$10$CqWO5ZVd7M6qjv6RiSYUdukrNo0OPX1ToCL7WYjksrm.aFbx/k1Ru', 'whatsapp', '2026-01-14 22:24:48', '2026-01-14 21:19:48', NULL, '2026-01-14 22:19:48', '10.88.93.59', 'login'),
(85, 34, '$2y$10$1FY6lfKsYIHUVwQ0sjcoW.KuS4rVwM.VhXKqI16bTy7Ok/vSdtb52', 'email', '2026-01-14 23:09:17', '2026-01-14 22:04:17', '2026-01-14 23:04:17', '2026-01-14 23:04:17', '10.88.93.59', 'login'),
(86, 34, '$2y$10$BQrjllNuAdF/YAjWgrxAEOqtlFATpjQTgN88HKnzVZWCVi15BXZda', 'whatsapp', '2026-01-14 23:09:18', '2026-01-14 22:04:18', NULL, '2026-01-14 23:04:18', '10.88.93.59', 'login'),
(87, 34, '$2y$10$r7HoSynYcu4iPos.vn6AOuDYhSFcMhL/lj4/918Kn4XtcqOzk0isS', 'email', '2026-01-14 23:17:41', '2026-01-14 22:12:41', '2026-01-14 23:12:41', '2026-01-14 23:12:41', '10.88.93.59', 'login'),
(88, 34, '$2y$10$B67ATpITjz2/iWVLyFqzkuMS56F86AdwTvwBnh.mMvXKAR4Ei9H5i', 'whatsapp', '2026-01-14 23:17:42', '2026-01-14 22:12:42', '2026-01-14 23:13:09', '2026-01-14 23:13:09', '10.88.93.59', 'login'),
(89, 34, '$2y$10$/XCQKo550hGHgt.aV8yO8OQH8gjAm1pQr/T74xc1By0CcKzkKo/OW', 'email', '2026-01-15 14:58:15', '2026-01-15 13:53:15', '2026-01-15 14:53:15', '2026-01-15 14:53:15', '10.88.93.59', 'login'),
(90, 34, '$2y$10$pM942mEL7C2H8AOFdAOdweQss1X3m96b5FgTn8Mo2tSpc4emTDbJS', 'whatsapp', '2026-01-15 14:58:15', '2026-01-15 13:53:15', NULL, '2026-01-15 14:53:15', '10.88.93.59', 'login'),
(91, 34, '$2y$10$yVDDIyrwqHoHrO0ocpETMewa3C459yh73dvkU8shiNmCGkRjpZCy6', 'email', '2026-01-15 18:19:37', '2026-01-15 17:14:37', '2026-01-15 18:14:37', '2026-01-15 18:14:37', '10.88.93.59', 'login'),
(92, 34, '$2y$10$6PxxCmQ4T8nK.sPFXc/z4uEpISq.b/SwvbIn.l3eN/467WOrBsx4.', 'whatsapp', '2026-01-15 18:19:37', '2026-01-15 17:14:37', '2026-01-15 18:15:53', '2026-01-15 18:15:53', '10.88.93.59', 'login'),
(93, 34, '$2y$10$R9K//UnHH3NuvvadaL6/k.ZgWTi4we/MPYy3M5b1un2F/WcRn49AG', 'email', '2026-01-15 18:40:21', '2026-01-15 17:35:21', '2026-01-15 18:35:21', '2026-01-15 18:35:21', '10.88.93.59', 'login'),
(94, 34, '$2y$10$J6igJSA16q9sPLnaC4357OeBTufYnB1DhEtYaBBWPu1QpbZPDI7AC', 'whatsapp', '2026-01-15 18:40:21', '2026-01-15 17:35:21', '2026-01-15 18:36:00', '2026-01-15 18:36:00', '10.88.93.59', 'login'),
(95, 34, '$2y$10$aVG0TaT5ejU2EbRvuUtJeupUCQhbCqO7Sj0xfSXH492WucQWoh8YK', 'email', '2026-01-15 18:41:00', '2026-01-15 17:36:00', '2026-01-15 18:36:00', '2026-01-15 18:36:00', '10.88.93.59', 'login'),
(96, 34, '$2y$10$6xudySKEEcVSt1epmbEwAuKEDY8KiaQ4QH9brf9XBxQYrC6HLUJt6', 'whatsapp', '2026-01-15 18:41:00', '2026-01-15 17:36:00', '2026-01-15 18:39:20', '2026-01-15 18:39:20', '10.88.93.59', 'login'),
(97, 34, '$2y$10$nSEgOAcrXVeN4tW.nHVlauqk4x3v1pDqm4dcJRMFKKBv27AJaHCnW', 'email', '2026-01-15 19:29:30', '2026-01-15 18:24:30', '2026-01-15 19:24:30', '2026-01-15 19:24:30', '10.88.93.59', 'login'),
(98, 34, '$2y$10$plOPyQDV3L3phJ8/dCJNgu1gRNOgtQCtCEpFamXUPVla90tL9ikoi', 'whatsapp', '2026-01-15 19:29:31', '2026-01-15 18:24:31', '2026-01-15 19:24:51', '2026-01-15 19:24:51', '10.88.93.59', 'login'),
(99, 34, '$2y$10$87RZYu/yOPVfvUX6SQE7iuFrRf.6aQImuhg9EFLhsjZbWZPtnKqNa', 'email', '2026-01-15 19:36:26', '2026-01-15 18:31:26', '2026-01-15 19:31:27', '2026-01-15 19:31:27', '10.88.93.59', 'login'),
(100, 34, '$2y$10$71MnpRb/LvKOA4lBv4kLYufiW31Rjq0w3PhS3x26Vza6gllR1yaJa', 'whatsapp', '2026-01-15 19:36:27', '2026-01-15 18:31:27', '2026-01-15 19:32:18', '2026-01-15 19:32:18', '10.88.93.59', 'login'),
(101, 34, '$2y$10$fpRQhpZ7sIC1jKAiimW.bOceVozS1ecBoZgu0tGy.jSUWE5VZvOUC', 'email', '2026-01-15 20:27:09', '2026-01-15 19:22:09', '2026-01-15 20:22:09', '2026-01-15 20:22:09', '10.88.93.59', 'login'),
(102, 34, '$2y$10$Ng5qejapXKbbLi2R2XoiCONFUAOqh/L8nM1I2sPbtt1dzwQH5079W', 'whatsapp', '2026-01-15 20:27:09', '2026-01-15 19:22:09', '2026-01-15 20:22:46', '2026-01-15 20:22:46', '10.88.93.59', 'login'),
(103, 38, '$2y$10$Qk7.wd4tIAgrLEk/wO8ghOvqC7Yd.e61iZXAI7WMxprPyrFXePVKO', 'email', '2026-01-15 20:38:46', '2026-01-15 19:33:46', '2026-01-15 20:33:46', '2026-01-15 20:33:46', '10.88.93.59', 'login'),
(104, 38, '$2y$10$5/GiXzL3CcXaaZ6E.70F8O7sfqWj8e8Ksl5HzKh4zMe.yE7by/Hya', 'whatsapp', '2026-01-15 20:38:46', '2026-01-15 19:33:46', NULL, '2026-01-15 20:33:46', '10.88.93.59', 'login'),
(105, 38, '$2y$10$pRVXSCuYmT2PLAAgsi8lmuLP8CBQeQUnIIXbycLvUPdv6xSufu/CO', 'email', '2026-01-15 20:46:21', '2026-01-15 19:41:21', '2026-01-15 20:41:21', '2026-01-15 20:41:21', '10.88.93.59', 'login'),
(106, 38, '$2y$10$x0rWh0W0tUqjQcOeGCufkuL3bOTKzDFCgXed4yzlv0rHaJVbPXP8W', 'whatsapp', '2026-01-15 20:46:21', '2026-01-15 19:41:21', '2026-01-15 20:41:45', '2026-01-15 20:41:45', '10.88.93.59', 'login'),
(107, 34, '$2y$10$.EmbjYhdgfysSOXtE./ziOlB8X/ozZGZcmFXo6e2YjxgKk17QVMI6', 'email', '2026-01-15 20:51:36', '2026-01-15 19:46:36', '2026-01-15 20:46:36', '2026-01-15 20:46:36', '10.88.93.59', 'login'),
(108, 34, '$2y$10$AzilxEbpbYeoNKk/xyVGVuFlHQqXNpsafrWKS/NgtKTpJNJXk2B.S', 'whatsapp', '2026-01-15 20:51:36', '2026-01-15 19:46:36', '2026-01-15 20:47:02', '2026-01-15 20:47:02', '10.88.93.59', 'login'),
(109, 40, '$2y$10$7ratDEOpyK4kYptLTOyub.6OE0GPRiDqH/fDw4TUNoWc5th0fcQ5i', 'email', '2026-01-15 20:53:27', '2026-01-15 19:48:27', '2026-01-15 20:48:27', '2026-01-15 20:48:27', '10.88.93.59', 'login'),
(110, 40, '$2y$10$xpu5ZUGFVEaND/UHz1udg.LbT/Ufqw7sfRVUQikUTZ1KjaE1fj/W.', 'whatsapp', '2026-01-15 20:53:27', '2026-01-15 19:48:27', '2026-01-15 20:49:59', '2026-01-15 20:49:59', '10.88.93.59', 'login'),
(111, 34, '$2y$10$pjKwq5fW8a72OnhG8FBAR.wzCo1Sv6NDcjCpNA9xWkh6N3Ypo68SG', 'email', '2026-01-15 21:03:47', '2026-01-15 19:58:47', '2026-01-15 20:58:47', '2026-01-15 20:58:47', '10.88.93.59', 'login'),
(112, 34, '$2y$10$nbCYbt4kJdzHnr6eY2v3FuQD7cZjBEPvXQ0O8dLZFswS./vs2bezO', 'whatsapp', '2026-01-15 21:03:47', '2026-01-15 19:58:47', '2026-01-15 20:59:56', '2026-01-15 20:59:56', '10.88.93.59', 'login'),
(113, 34, '$2y$10$/T5bVKtPB8ucFH.xBQy6CeHDB0dY9BuK/597gVSQ3jx9jA9F0Vlji', 'email', '2026-01-15 21:57:56', '2026-01-15 20:52:56', '2026-01-15 21:52:56', '2026-01-15 21:52:56', '10.88.93.59', 'login'),
(114, 34, '$2y$10$aFc2lnYu9/cR4vGBOQauO.T2zjvQt3Y6tOYwWYerbw3hGzbN6JzSW', 'whatsapp', '2026-01-15 21:57:56', '2026-01-15 20:52:56', '2026-01-15 21:53:18', '2026-01-15 21:53:18', '10.88.93.59', 'login'),
(115, 34, '$2y$10$qq8Mf15vdMHtlRCqQl2S8OxUZBTsUe2QxCmsQqqCQxMLjMyCiR9ne', 'email', '2026-01-15 22:00:13', '2026-01-15 20:55:13', '2026-01-15 21:55:13', '2026-01-15 21:55:13', '10.88.93.59', 'login'),
(116, 34, '$2y$10$oDXZyPloy9WUSiyBvbRSJur7qnvKZvxURXZtvW6vBjKniDWuwpPhK', 'whatsapp', '2026-01-15 22:00:13', '2026-01-15 20:55:13', '2026-01-15 21:55:35', '2026-01-15 21:55:35', '10.88.93.59', 'login'),
(117, 34, '$2y$10$VOxfauywZ0rttS1rHBqVjuFybWRaQM.15p7pQB1Il7FsHNerdIIkq', 'email', '2026-01-15 22:41:51', '2026-01-15 21:36:51', '2026-01-15 22:36:51', '2026-01-15 22:36:51', '10.88.93.59', 'login'),
(118, 34, '$2y$10$WFaR/g/tM5cFrQ9OoFZnHuEm0kI90vmcqOGTqqmg6IlL2KaUlr556', 'whatsapp', '2026-01-15 22:41:51', '2026-01-15 21:36:51', '2026-01-15 22:38:15', '2026-01-15 22:38:15', '10.88.93.59', 'login'),
(119, 34, '$2y$10$.iO0l3vT4ruTrMsY6HRM5ONeXU5FpIve4jISerxbR1Op3Ca3laKb2', 'email', '2026-01-15 23:10:05', '2026-01-15 22:05:05', '2026-01-15 23:05:05', '2026-01-15 23:05:05', '10.88.93.59', 'login'),
(120, 34, '$2y$10$MboR9z0QX/FNd01BPczzRuNIAHHlkLlXLy0V7iZNsHWa3Rk8dFldq', 'whatsapp', '2026-01-15 23:10:05', '2026-01-15 22:05:05', '2026-01-15 23:05:29', '2026-01-15 23:05:29', '10.88.93.59', 'login'),
(121, 34, '$2y$10$IX9cMLsFAntLOsGXUHFZi.f.jYXMfHLA2hgkAVYl38DnkzCPC0PDu', 'email', '2026-01-15 23:40:40', '2026-01-15 22:35:40', '2026-01-15 23:35:40', '2026-01-15 23:35:40', '10.88.93.59', 'login'),
(122, 34, '$2y$10$Js1X3UQP5pyIT5opxxym5uxFG9dWNfkefvE5ax3s0yH9jn6LL986q', 'whatsapp', '2026-01-15 23:40:40', '2026-01-15 22:35:40', '2026-01-15 23:36:00', '2026-01-15 23:36:00', '10.88.93.59', 'login'),
(123, 34, '$2y$10$lmwQl97WC97zHD1WeohcaugNk1hrr2K8AAC4rlxjilMzrPpgL0zy2', 'email', '2026-01-15 23:46:59', '2026-01-15 22:41:59', '2026-01-15 23:41:59', '2026-01-15 23:41:59', '10.88.93.59', 'login'),
(124, 34, '$2y$10$gkaFLZRf37M4uOpzedJRy.y12BuGTFQFfy36TfJN5bXzhxMwsCR.e', 'whatsapp', '2026-01-15 23:46:59', '2026-01-15 22:41:59', '2026-01-15 23:42:23', '2026-01-15 23:42:23', '10.88.93.59', 'login'),
(125, 34, '$2y$10$4fwxYdj0/1oBBq/tFeWC9.NKVLTXSUY36dfZWdOAcRX8zaxBl/Fge', 'email', '2026-01-16 00:51:17', '2026-01-15 23:46:17', '2026-01-16 00:46:17', '2026-01-16 00:46:17', '10.88.93.59', 'login'),
(126, 34, '$2y$10$TLaeTSl5sKyULBTpHDIQQ./Rkx2MgnWh/YCfMS86KvxpWsw.woSoK', 'whatsapp', '2026-01-16 00:51:17', '2026-01-15 23:46:17', '2026-01-16 00:46:39', '2026-01-16 00:46:39', '10.88.93.59', 'login'),
(127, 34, '$2y$10$nWGs7BxdtF3B59A8w/hWE.oc4rs6G/QToHEKqWc5pZNb7RZObeLx.', 'email', '2026-01-16 01:39:51', '2026-01-16 00:34:51', '2026-01-16 01:34:51', '2026-01-16 01:34:51', '10.88.93.59', 'login'),
(128, 34, '$2y$10$iwueqp2aOfpTCZkyK4/5AuJqM7SjUCpMEQhHgJW2sxJ5b44Fz6moG', 'whatsapp', '2026-01-16 01:39:51', '2026-01-16 00:34:51', '2026-01-16 01:35:14', '2026-01-16 01:35:14', '10.88.93.59', 'login'),
(129, 34, '$2y$10$sozpZLZH0HWrxfDlPqg7KeLVX3vjwxVU.pqk3psJ0Kffvj5W58Dp2', 'email', '2026-01-16 02:10:56', '2026-01-16 01:05:56', '2026-01-16 02:05:56', '2026-01-16 02:05:56', '10.88.93.59', 'login'),
(130, 34, '$2y$10$OZZBnC7jPh4/ZOrZIaKpYOBSTbkrRM28gahIvBd7AJam5u1WIa/7S', 'whatsapp', '2026-01-16 02:10:57', '2026-01-16 01:05:57', '2026-01-16 02:06:17', '2026-01-16 02:06:17', '10.88.93.59', 'login'),
(131, 34, '$2y$10$bKOl5KVstwiGdTmjJteu0.UHIY31WKsVANCsiFbrqykYy5zM6c722', 'email', '2026-01-16 02:23:19', '2026-01-16 01:18:19', '2026-01-16 02:18:19', '2026-01-16 02:18:19', '10.88.93.59', 'login'),
(132, 34, '$2y$10$DcN2VW0HZy8FiSi4FJFU/.rsdDHl4PNg5abLQs1/v50wvzZq3kFJu', 'whatsapp', '2026-01-16 02:23:19', '2026-01-16 01:18:19', '2026-01-16 02:18:38', '2026-01-16 02:18:38', '10.88.93.59', 'login'),
(133, 34, '$2y$10$XWOkAaVbhYhYNPGTIrrZvORyFfH8/OaHrYhnopP32ZDY72Qnr/3ci', 'email', '2026-01-16 13:07:44', '2026-01-16 12:02:44', '2026-01-16 13:02:44', '2026-01-16 13:02:44', '10.88.93.59', 'login'),
(134, 34, '$2y$10$WLwWh.Bn/x68WahnNzeMYOdMnHTEf63OlDeHUStMUQ1Y71QZRtpmy', 'whatsapp', '2026-01-16 13:07:44', '2026-01-16 12:02:44', '2026-01-16 13:03:10', '2026-01-16 13:03:10', '10.88.93.59', 'login'),
(135, 34, '$2y$10$YtO2umJQQvDrEZ7zck7lEeZS6gzbnaoTKOy92M7xFL2Sfa0878yPu', 'email', '2026-01-16 13:23:14', '2026-01-16 12:18:14', '2026-01-16 13:18:14', '2026-01-16 13:18:14', '10.88.93.59', 'login'),
(136, 34, '$2y$10$C3gftTohnn6.k77sfygfRuRvm1xnHN744e44fQvKdn6L2HGRcY0Ke', 'whatsapp', '2026-01-16 13:23:14', '2026-01-16 12:18:14', '2026-01-16 13:19:44', '2026-01-16 13:19:44', '10.88.93.59', 'login'),
(137, 34, '$2y$10$1BnBF0ZvQsMFKtYg6fQFkehQCvH8Q.wcWzuu.k/jFwNrFfSpYet9O', 'email', '2026-01-16 14:04:40', '2026-01-16 12:59:40', '2026-01-16 13:59:40', '2026-01-16 13:59:40', '10.88.93.59', 'login'),
(138, 34, '$2y$10$HrD7SuxKzX15.trgKurhLuhT1h2YRAqyDFc2HwAHXFzUo7Ed3fjge', 'whatsapp', '2026-01-16 14:04:40', '2026-01-16 12:59:40', '2026-01-16 14:00:02', '2026-01-16 14:00:02', '10.88.93.59', 'login'),
(139, 40, '$2y$10$G2PMXMKokJV4gzZAVZphTegpYWzK37LeMO9KUa52svrXAXRIGj1r2', 'email', '2026-01-16 14:12:37', '2026-01-16 13:07:37', '2026-01-16 14:07:37', '2026-01-16 14:07:37', '10.88.93.59', 'login'),
(140, 40, '$2y$10$O/Zmwt12PyyE5krwkz8Xfez1C98KPEQpLEZy.uoDlm9LeKqDbzg7K', 'whatsapp', '2026-01-16 14:12:37', '2026-01-16 13:07:37', '2026-01-16 14:08:23', '2026-01-16 14:08:23', '10.88.93.59', 'login'),
(141, 34, '$2y$10$uTg8hrtlmfKazmfsilcOFOkE8PLtxRn1V6nZUv8l2BIK0323Z6bB6', 'email', '2026-01-16 17:06:54', '2026-01-16 16:01:54', '2026-01-16 17:01:54', '2026-01-16 17:01:54', '10.88.93.187', 'login'),
(142, 34, '$2y$10$Y1q/syQQAEIcYsb62Nmoi.5TdhiHcbFTZ3vRyQNo8ilY/r0B5I1.K', 'whatsapp', '2026-01-16 17:06:54', '2026-01-16 16:01:54', '2026-01-16 17:02:53', '2026-01-16 17:02:53', '10.88.93.187', 'login'),
(143, 34, '$2y$10$21UVgsoRv0DBytM0SNL3FOHsJuyEVoKNOKD8acH9pyTSJtimX1/d.', 'email', '2026-01-17 12:42:51', '2026-01-17 11:37:51', '2026-01-17 12:37:51', '2026-01-17 12:37:51', '10.185.59.24', 'login'),
(144, 34, '$2y$10$DaI52BLDhoKHPRccyIqiAelwhdm/v5iM5BOI1MGJ2HQTCh5j/.L5O', 'whatsapp', '2026-01-17 12:42:51', '2026-01-17 11:37:51', '2026-01-17 12:38:34', '2026-01-17 12:38:34', '10.185.59.24', 'login'),
(145, 34, '$2y$10$ZBzIYDEAEzYTsH/vPbSBqO/FWXCVUbAYcOawbBX1jvgFPz/ZPqGKm', 'email', '2026-01-17 13:31:26', '2026-01-17 12:26:26', '2026-01-17 13:26:26', '2026-01-17 13:26:26', '10.185.59.59', 'login'),
(146, 34, '$2y$10$eb5I0L3G8MlcHfxDFIXFNeX2QSrr8lVyBag.3IFcbz0QbBrIBjntS', 'whatsapp', '2026-01-17 13:31:27', '2026-01-17 12:26:27', '2026-01-17 13:29:01', '2026-01-17 13:29:01', '10.185.59.59', 'login'),
(147, 34, '$2y$10$ymlyDnd4xQGCfWEKH9Ydwe0WIxwSGI4MC9GLwJANkhC9qkaeu9nPu', 'email', '2026-01-17 13:43:08', '2026-01-17 12:38:08', '2026-01-17 13:38:08', '2026-01-17 13:38:08', '10.185.59.59', 'login'),
(148, 34, '$2y$10$fLGxNMODDlthVgcul3gvo.xZtomET9A2PmEKvDaAXyL/jOjRo1q5e', 'whatsapp', '2026-01-17 13:43:09', '2026-01-17 12:38:09', '2026-01-17 13:39:10', '2026-01-17 13:39:10', '10.185.59.59', 'login'),
(149, 34, '$2y$10$iJkTsJU4kAQWS4723meGou55W1Qft26MhFj8vl8lJ3EkFWCXmjvlm', 'email', '2026-01-17 14:50:20', '2026-01-17 13:45:20', '2026-01-17 14:45:20', '2026-01-17 14:45:20', '10.185.59.59', 'login'),
(150, 34, '$2y$10$lwTRu3jZlY7uTN.bcT4.suPSB42DaIItE6eejwntLi/LvPf7yJ7uC', 'whatsapp', '2026-01-17 14:50:20', '2026-01-17 13:45:20', '2026-01-17 14:45:40', '2026-01-17 14:45:40', '10.185.59.59', 'login'),
(151, 34, '$2y$10$Kk.9MSjuFknXPh7HxWrE..ZQDGK5JuS.776bDCQYOo6cnEmXJ0wxW', 'email', '2026-01-17 15:52:23', '2026-01-17 14:47:23', '2026-01-17 15:47:23', '2026-01-17 15:47:23', '10.185.59.59', 'login'),
(152, 34, '$2y$10$NJdnik7IS2SknN5mNmNvTuxzTxqomNTKTPo1j4WAji7Pj8pRrwEiy', 'whatsapp', '2026-01-17 15:52:23', '2026-01-17 14:47:23', '2026-01-17 15:52:09', '2026-01-17 15:52:09', '10.185.59.59', 'login'),
(153, 34, '$2y$10$bYnjMoVkhNpCwLZO7kh9peZvT2W9TKwxiF/cJxBCtejPi5iwWCGbm', 'email', '2026-01-17 15:57:09', '2026-01-17 14:52:09', '2026-01-17 15:52:09', '2026-01-17 15:52:09', '10.185.59.59', 'login'),
(154, 34, '$2y$10$N2juvmRbweTrMMlz/SHCpeoidvwYmyvBjiEixpv1Wtrgr.DFvk9yS', 'whatsapp', '2026-01-17 15:57:09', '2026-01-17 14:52:09', '2026-01-17 15:52:37', '2026-01-17 15:52:37', '10.185.59.59', 'login'),
(155, 34, '$2y$10$HaZbbNORQ9cPeiZpo56SauqIAOIAuoCu05TUlNi3DGsl0YE1OdqaK', 'email', '2026-01-17 17:43:09', '2026-01-17 16:38:09', '2026-01-17 17:38:09', '2026-01-17 17:38:09', '192.168.37.59', 'login'),
(156, 34, '$2y$10$EU/8uMUSGimgajLQu9pJJuFS.OJgTX88pfU.aEpbJWfZQj1IgVdyG', 'whatsapp', '2026-01-17 17:43:09', '2026-01-17 16:38:09', '2026-01-17 17:38:35', '2026-01-17 17:38:35', '192.168.37.59', 'login'),
(157, 34, '$2y$10$hN1BKejfOsGPrIkOg/WpiekdzIj7IyFbtcCtu4TjTdV2q/crP6KVa', 'email', '2026-01-17 18:04:41', '2026-01-17 16:59:41', '2026-01-17 17:59:41', '2026-01-17 17:59:41', '192.168.37.59', 'login'),
(158, 34, '$2y$10$i9fg8.BJUgERjJriYY.NzeRSxiBKYs3WnnqIncme0toUGsaY1ZmKC', 'whatsapp', '2026-01-17 18:04:41', '2026-01-17 16:59:41', '2026-01-17 18:00:46', '2026-01-17 18:00:46', '192.168.37.59', 'login'),
(159, 34, '$2y$10$v3KkxRF/dYZ1XRofqb0EKebRauzFmb4CXXC.X0Ptc5hHSh8nN8.u2', 'email', '2026-01-17 18:05:46', '2026-01-17 17:00:46', '2026-01-17 18:00:46', '2026-01-17 18:00:46', '192.168.37.59', 'login'),
(160, 34, '$2y$10$eFn8i/l.SOxy847XqzTVRe/D99t.aeJyVGoRJpGcLXEH4QHsOq5De', 'whatsapp', '2026-01-17 18:05:46', '2026-01-17 17:00:46', '2026-01-17 18:03:37', '2026-01-17 18:03:37', '192.168.37.59', 'login'),
(161, 34, '$2y$10$J6MlqPvXoTvsXZB65HjKHur8d3DvzH/iry1/6.vj1Dcwae5VJOO5O', 'email', '2026-01-17 18:11:11', '2026-01-17 17:06:11', '2026-01-17 18:06:11', '2026-01-17 18:06:11', '192.168.37.59', 'login'),
(162, 34, '$2y$10$zhJBuwD1mHTG5El2aBEdq.YYd7olP8ukfgSVGqsc0s9ObJpzniqIa', 'whatsapp', '2026-01-17 18:11:11', '2026-01-17 17:06:11', '2026-01-17 18:06:47', '2026-01-17 18:06:47', '192.168.37.59', 'login'),
(163, 34, '$2y$10$owZS5eg5nOhnICqxp9.WPuZBVLlsAgx6Zzpd2rD.fr30eqLtpYKIe', 'email', '2026-01-17 18:15:57', '2026-01-17 17:10:57', '2026-01-17 18:10:57', '2026-01-17 18:10:57', '192.168.37.59', 'login'),
(164, 34, '$2y$10$fqaJo20Wg1BkypinZDQh5OIJPpc5a8luwdvw7UNxJZ5j8MDVVBJl6', 'whatsapp', '2026-01-17 18:15:57', '2026-01-17 17:10:57', '2026-01-17 18:11:52', '2026-01-17 18:11:52', '192.168.37.59', 'login'),
(165, 34, '$2y$10$f/VzQ.3QfBIP0THOpPoLnOPGP.bGrPPx78usZH6x22KcfhqsYPz8G', 'email', '2026-01-17 18:24:14', '2026-01-17 17:19:14', '2026-01-17 18:19:14', '2026-01-17 18:19:14', '192.168.37.59', 'login'),
(166, 34, '$2y$10$BWMjijOuQC.CDfedEM6bq.6m3vt4Gl8AVi6bxJ5AF7UiZ4HetZiDu', 'whatsapp', '2026-01-17 18:24:14', '2026-01-17 17:19:14', '2026-01-17 18:19:40', '2026-01-17 18:19:40', '192.168.37.59', 'login'),
(167, 34, '$2y$10$ed4nOFDVc/NnT8U6pMc7KewBKN8iSZAm52XeEf7gK.4bTfwCMRNca', 'email', '2026-01-17 19:11:31', '2026-01-17 18:06:31', '2026-01-17 19:06:31', '2026-01-17 19:06:31', '192.168.37.59', 'login'),
(168, 34, '$2y$10$NmxTLprZcjvehhAyNrOcguiHLVCTbImBDkxt7qLvqUXroeJIdSZrK', 'whatsapp', '2026-01-17 19:11:31', '2026-01-17 18:06:31', '2026-01-17 19:07:02', '2026-01-17 19:07:02', '192.168.37.59', 'login'),
(169, 34, '$2y$10$Va1vkQAdAe6YVMSqKxZLHOCEzNY5MbZEiOawChVouJVm80nse9jRS', 'email', '2026-01-17 19:19:52', '2026-01-17 18:14:52', '2026-01-17 19:14:52', '2026-01-17 19:14:52', '192.168.37.59', 'login'),
(170, 34, '$2y$10$I1kjBBxguyuc/WbzAV9fpe9xuJuD2HMWNw9vWAfR.dd4lcWUndALq', 'whatsapp', '2026-01-17 19:19:52', '2026-01-17 18:14:52', '2026-01-17 19:15:34', '2026-01-17 19:15:34', '192.168.37.59', 'login'),
(171, 34, '$2y$10$AWlTaoWmeVrpHWHbGgErvuV37FMxBlMmVLbYyOv6tXxwQd9SdfixC', 'email', '2026-01-18 06:20:57', '2026-01-18 05:15:57', '2026-01-18 06:15:57', '2026-01-18 06:15:57', '192.168.37.59', 'login'),
(172, 34, '$2y$10$WUJ6DvmcwkJPpKQsO91RSujbpj.LeAVY7C2iUt7pw4NKc2oZFCwcm', 'whatsapp', '2026-01-18 06:20:57', '2026-01-18 05:15:57', '2026-01-18 06:16:55', '2026-01-18 06:16:55', '192.168.37.59', 'login'),
(173, 34, '$2y$10$W8Ud1JpimS.p1Ttwb7bIk.HyOJZ5wPAOAClZ/CZpaxEyuGMXEWUBC', 'email', '2026-01-18 06:26:10', '2026-01-18 05:21:10', '2026-01-18 06:21:10', '2026-01-18 06:21:10', '192.168.37.59', 'login'),
(174, 34, '$2y$10$yHZjOzN/9rxC4N/DDEZwpO2pg7MEkXgu03Ey4THWCsM4aDpTH9h/6', 'whatsapp', '2026-01-18 06:26:10', '2026-01-18 05:21:10', '2026-01-18 06:21:29', '2026-01-18 06:21:29', '192.168.37.59', 'login'),
(175, 34, '$2y$10$Hfeir7PDT.8e7gZZpi6bGeOgtNiS8asEIHUZK.k7OLwxr9HLL1JRe', 'email', '2026-01-18 07:08:15', '2026-01-18 06:03:15', '2026-01-18 07:03:15', '2026-01-18 07:03:15', '192.168.37.59', 'login'),
(176, 34, '$2y$10$pFEtiVPjHA5Y9VUpRl0y0OT8VA1GJ4msqumgir6s64cD.4lCYGSPC', 'whatsapp', '2026-01-18 07:08:15', '2026-01-18 06:03:15', '2026-01-18 07:04:45', '2026-01-18 07:04:45', '192.168.37.59', 'login'),
(177, 34, '$2y$10$HM8RKTxnyQtIQPInuB4v2eo84MPx7mjuZ7uxYOqJgSYb2Jl3PaM5i', 'email', '2026-01-18 07:32:17', '2026-01-18 06:27:17', '2026-01-18 07:27:17', '2026-01-18 07:27:17', '192.168.37.59', 'login'),
(178, 34, '$2y$10$J3PYe0gdAfTY2chWKCrc9usJFbWqCAAq0Se1i6/9p7.w9Km9xNQWC', 'whatsapp', '2026-01-18 07:32:17', '2026-01-18 06:27:17', '2026-01-18 07:27:38', '2026-01-18 07:27:38', '192.168.37.59', 'login'),
(179, 34, '$2y$10$MQ/hnAon/dvGEj0To6akiONxwHJwmavL/3nDWzLpQyp8udbc45Awm', 'email', '2026-01-18 10:31:08', '2026-01-18 09:26:08', '2026-01-18 10:26:08', '2026-01-18 10:26:08', '10.219.25.59', 'login'),
(180, 34, '$2y$10$hIcpSxjlWzCsiQGZnglFQOWD3eRXRE1wLcZYPBL1ahG7bZwwrjB.W', 'whatsapp', '2026-01-18 10:31:09', '2026-01-18 09:26:09', NULL, '2026-01-18 10:26:09', '10.219.25.59', 'login'),
(181, 34, '$2y$10$2JZmo1i.K//G2ysn/NN.nOtH4QRhmO5qP/xGX2lnBgeCIIwzFqZvu', 'email', '2026-01-18 10:38:41', '2026-01-18 09:33:41', '2026-01-18 10:33:41', '2026-01-18 10:33:41', '10.219.25.59', 'login'),
(182, 34, '$2y$10$pIdhXrz0qqFum4AnfOxHA.Tg9lAi63Vcc7MavE1zOQdX4fS7yViIu', 'whatsapp', '2026-01-18 10:38:41', '2026-01-18 09:33:41', '2026-01-18 10:37:58', '2026-01-18 10:37:58', '10.219.25.59', 'login'),
(183, 34, '$2y$10$nOEAmOAcr1TXtx1vj/9KFeV5Y0GU7vGF/kPKPZx9KMhLzvV8b51Ye', 'email', '2026-01-18 10:42:58', '2026-01-18 09:37:58', '2026-01-18 10:37:58', '2026-01-18 10:37:58', '10.219.25.59', 'login'),
(184, 34, '$2y$10$yJfMDmZo20kn5cwCuyYC5u2PjtkJi4Tg.ewQIlHVznjf/mz2r3nXq', 'whatsapp', '2026-01-18 10:42:58', '2026-01-18 09:37:58', '2026-01-18 10:38:25', '2026-01-18 10:38:25', '10.219.25.59', 'login'),
(185, 34, '$2y$10$ebme2SgnxZmR0xfPVI/Wh.wXYFznVYmYxcQLsz0SbAVmpAWQ1JQKq', 'email', '2026-01-18 11:29:34', '2026-01-18 10:24:34', '2026-01-18 11:24:34', '2026-01-18 11:24:34', '10.219.25.59', 'login'),
(186, 34, '$2y$10$psuvsdvT0xrPgkUDCAp0b.Gp3jKT1rxQy.I5T7h/tTJcgSfNvhPxS', 'whatsapp', '2026-01-18 11:29:35', '2026-01-18 10:24:35', '2026-01-18 11:25:11', '2026-01-18 11:25:11', '10.219.25.59', 'login'),
(187, 34, '$2y$10$msjaOYb1WF.QFSfST5c74.j0pXsHT5nmrG.MEsVnLIABt3OnBs5eu', 'email', '2026-01-18 12:54:08', '2026-01-18 11:49:08', '2026-01-18 12:49:08', '2026-01-18 12:49:08', '10.219.25.59', 'login'),
(188, 34, '$2y$10$HEYsA9fTqPVjeEgn.AA3OOeV..lGQxGliqcaA7oAktRIdFmBOiWSm', 'whatsapp', '2026-01-18 12:54:08', '2026-01-18 11:49:08', '2026-01-18 12:50:40', '2026-01-18 12:50:40', '10.219.25.59', 'login'),
(189, 34, '$2y$10$csF/jTP6g/.rBnCSESfHAuC6LkvTLGseORVfasYe2l5QnbxqTf8yC', 'email', '2026-01-18 12:59:35', '2026-01-18 11:54:35', '2026-01-18 12:54:35', '2026-01-18 12:54:35', '10.219.25.59', 'login'),
(190, 34, '$2y$10$TG.bwyl1kYUqqTWf90M8/eFLlPDA1/aa1NFdAtofZd1v95dRMVvXW', 'whatsapp', '2026-01-18 12:59:35', '2026-01-18 11:54:35', '2026-01-18 12:55:00', '2026-01-18 12:55:00', '10.219.25.59', 'login'),
(191, 34, '$2y$10$LI7ekxPvvRmYOMmOT9Kt/uyU2T0Sg3/IfDliARDicmjBDFoi7Yja.', 'email', '2026-01-18 14:00:27', '2026-01-18 12:55:27', '2026-01-18 13:55:27', '2026-01-18 13:55:27', '10.219.25.59', 'login'),
(192, 34, '$2y$10$peIL4uI1s2JZphv3yE6QL.H1YYvGDELal9dnF3r1b6vMlu.g/BZfu', 'whatsapp', '2026-01-18 14:00:27', '2026-01-18 12:55:27', '2026-01-18 13:55:49', '2026-01-18 13:55:49', '10.219.25.59', 'login'),
(193, 34, '$2y$10$EYv.mM27ys9m2q3YgEfAl.Mc4Ts4sGoW8Bcjid3.f6p.KMz6u/a/u', 'email', '2026-01-18 14:01:54', '2026-01-18 12:56:54', '2026-01-18 13:56:54', '2026-01-18 13:56:54', '10.219.25.59', 'login'),
(194, 34, '$2y$10$i2O7YkcCnQMnaSDw4HyszerwUOYcChwK9nf/P4tXkvand1C9xX4Fm', 'whatsapp', '2026-01-18 14:01:55', '2026-01-18 12:56:55', '2026-01-18 13:57:21', '2026-01-18 13:57:21', '10.219.25.59', 'login'),
(195, 34, '$2y$10$zqObguZFj9MKcUqPH/0VbOzii2Y6osPHu3HqeQDRaso2cJ3Z/NGQ2', 'email', '2026-01-18 14:36:36', '2026-01-18 13:31:36', '2026-01-18 14:31:36', '2026-01-18 14:31:36', '10.219.25.59', 'login'),
(196, 34, '$2y$10$OZbWKy3nq8iSoxqaPk1OsO8d2B62hs.5nfYa3kJuKlry.iAcgiIDe', 'whatsapp', '2026-01-18 14:36:36', '2026-01-18 13:31:36', '2026-01-18 14:33:46', '2026-01-18 14:33:46', '10.219.25.59', 'login'),
(197, 34, '$2y$10$6UmaHeZE2aCnpyRFnYS16uro.bJE/Mzw9frVUs/Y6H0HguIZapl/a', 'email', '2026-01-18 14:40:57', '2026-01-18 13:35:57', '2026-01-18 14:35:57', '2026-01-18 14:35:57', '10.219.25.59', 'login'),
(198, 34, '$2y$10$reZO71JdwYnhJUqaGZP.5e02kT/WvjsuQmbREWMn7dBywzMxgVEZy', 'whatsapp', '2026-01-18 14:40:58', '2026-01-18 13:35:58', '2026-01-18 14:36:18', '2026-01-18 14:36:18', '10.219.25.59', 'login'),
(199, 34, '$2y$10$O7oJBsb7.O6yexT54HTIUu1uqKU058qsCELn0NM6twEDyh.CqCbDG', 'email', '2026-01-18 15:05:18', '2026-01-18 14:00:18', '2026-01-18 15:00:18', '2026-01-18 15:00:18', '10.219.25.59', 'login'),
(200, 34, '$2y$10$LgGmgiegnfnJ8gSDmjRB0O1uAflsBcqUphQlHkBFaTaUIETsrhY2i', 'whatsapp', '2026-01-18 15:05:18', '2026-01-18 14:00:18', NULL, '2026-01-18 15:00:18', '10.219.25.59', 'login'),
(201, 34, '$2y$10$CsQH0iVBH8viXGSJo5cyxeBAgmitARfgMMqR983DWF3J8vWFlPJ2W', 'email', '2026-01-18 20:10:42', '2026-01-18 19:05:42', '2026-01-18 20:05:42', '2026-01-18 20:05:42', '192.168.37.59', 'login'),
(202, 34, '$2y$10$d92fY84.TsN8d0EwwxTc1.WwGPFbyWADVWbb82rNppyS2Otv0Srne', 'whatsapp', '2026-01-18 20:10:42', '2026-01-18 19:05:42', '2026-01-18 20:06:54', '2026-01-18 20:06:54', '192.168.37.59', 'login'),
(203, 34, '$2y$10$fY3dkIXCazWg877NBN2....SzIQQ3LhMtRpq0XQ1/9OVOYIhBrCyi', 'email', '2026-01-18 20:54:10', '2026-01-18 19:49:10', '2026-01-18 20:49:10', '2026-01-18 20:49:10', '192.168.37.59', 'login'),
(204, 34, '$2y$10$cBNFqaSUnm0VH3VKLMQdvOAfQuYyvtIQWHEVSCWCofpgymz8shw6W', 'whatsapp', '2026-01-18 20:54:10', '2026-01-18 19:49:10', '2026-01-18 20:49:38', '2026-01-18 20:49:38', '192.168.37.59', 'login'),
(205, 34, '$2y$10$Pnhvt733hA.fIfclADpzjOUywI4UU04eGh0fBGr2YABqYg86P8i1m', 'email', '2026-01-20 19:03:53', '2026-01-20 17:58:53', '2026-01-20 18:58:53', '2026-01-20 18:58:53', '10.98.133.59', 'login'),
(206, 34, '$2y$10$7YiDHNLD4klUQzJFiTJv0Ojo.VQWLZlbpOoc/A5.G2O0IlETrwG7m', 'whatsapp', '2026-01-20 19:03:53', '2026-01-20 17:58:53', '2026-01-20 18:59:32', '2026-01-20 18:59:32', '10.98.133.59', 'login'),
(207, 34, '$2y$10$HkC.gzzvf9tEh4BISCuYN.9wCMXR/P33s7t4sHzrcyPuQFVWxG51u', 'email', '2026-01-21 18:14:15', '2026-01-21 17:09:15', '2026-01-21 18:09:15', '2026-01-21 18:09:15', '10.136.238.59', 'login'),
(208, 34, '$2y$10$kcjbrjWEGJJAz.7B6D6G3.sm/D57Vo5VZidRM.kIePMk5xVN/.YhC', 'whatsapp', '2026-01-21 18:14:15', '2026-01-21 17:09:15', '2026-01-21 18:10:03', '2026-01-21 18:10:03', '10.136.238.59', 'login'),
(209, 34, '$2y$10$4WRD0Lwk7q33riRygNxAV.2K/BaX56LnrrAXtOPrMoYHKjOCtNdLS', 'email', '2026-01-21 18:21:49', '2026-01-21 17:16:49', '2026-01-21 18:16:49', '2026-01-21 18:16:49', '10.136.238.59', 'login'),
(210, 34, '$2y$10$.Z3ZXu3LJ98Wp3bjW2ueP.CGH3/6vTymT4TFhJbfggGmmx2DQ5bKC', 'whatsapp', '2026-01-21 18:21:49', '2026-01-21 17:16:49', '2026-01-21 18:17:29', '2026-01-21 18:17:29', '10.136.238.59', 'login'),
(211, 34, '$2y$10$kAZq6FdL0S3vKaQ4INVZB.C8qA9YetitAKkHsSfxgzd3grSaCln9S', 'email', '2026-01-21 18:27:37', '2026-01-21 17:22:37', '2026-01-21 18:22:37', '2026-01-21 18:22:37', '10.136.238.59', 'login'),
(212, 34, '$2y$10$flZjY.Hg81DwQ9JM8g6H4eM5jWXel9kq1ckQCqrPhGtfwXZbcUwfO', 'whatsapp', '2026-01-21 18:27:37', '2026-01-21 17:22:37', '2026-01-21 18:23:04', '2026-01-21 18:23:04', '10.136.238.59', 'login'),
(213, 34, '$2y$10$oRDG9DOMQALOYcwfDY1wyu2JVIx5aVDlHtAnNsOFmm/kjfFH6XgmK', 'email', '2026-01-21 18:36:45', '2026-01-21 17:31:45', '2026-01-21 18:31:45', '2026-01-21 18:31:45', '10.136.238.59', 'login'),
(214, 34, '$2y$10$t1VZCjgpVZHjF1b0K7FUnuRhVCNV5zL7pwWFkW6BWnOGgMMfKrjK.', 'whatsapp', '2026-01-21 18:36:45', '2026-01-21 17:31:45', '2026-01-21 18:32:15', '2026-01-21 18:32:15', '10.136.238.59', 'login'),
(215, 34, '$2y$10$vtx1/Cy/J8A9YZ9wuMvPP.ypYoKqNbaEXCGSLAyL4O.nbdc.zAtr2', 'email', '2026-01-21 18:43:26', '2026-01-21 17:38:26', '2026-01-21 18:38:26', '2026-01-21 18:38:26', '10.136.238.59', 'login'),
(216, 34, '$2y$10$81bPpKFpJ10UkfreXyc5vO3LILbkqHg.x9I39fA7nixn8aee.9NXC', 'whatsapp', '2026-01-21 18:43:26', '2026-01-21 17:38:26', '2026-01-21 18:38:49', '2026-01-21 18:38:49', '10.136.238.59', 'login'),
(217, 34, '$2y$10$YWnFGuz0oDeIQOeO4EZMIuhQezswS8amzeVOqglirxkTqtjTp2jX.', 'email', '2026-01-21 19:32:04', '2026-01-21 18:27:04', '2026-01-21 19:27:04', '2026-01-21 19:27:04', '10.136.238.59', 'login'),
(218, 34, '$2y$10$xS4nD5cHe1C/1fRlh86kMODnSMuV8ou7CgScv92HtD447fQUfKj6O', 'whatsapp', '2026-01-21 19:32:04', '2026-01-21 18:27:04', '2026-01-21 19:27:43', '2026-01-21 19:27:43', '10.136.238.59', 'login'),
(219, 34, '$2y$10$Hfu2nwkRVpf7BVTGuSdKH.M6CJF7xGeVKJkhbuWqrxbFHhSxLB86.', 'email', '2026-01-21 19:41:05', '2026-01-21 18:36:05', '2026-01-21 19:36:05', '2026-01-21 19:36:05', '10.136.238.59', 'login'),
(220, 34, '$2y$10$UmhG9aVovoYMED4Yk9T9t.EsxxdI6EfrPWPPHZbMni7tgPzm74B/m', 'whatsapp', '2026-01-21 19:41:06', '2026-01-21 18:36:06', '2026-01-21 19:37:34', '2026-01-21 19:37:34', '10.136.238.59', 'login'),
(221, 34, '$2y$10$6qsaJTumQgiXFNC4uZYRme0nAz1EcWUkT8xExgFdA4DJ/wQ.b9GUu', 'email', '2026-01-21 20:06:58', '2026-01-21 19:01:58', '2026-01-21 20:01:58', '2026-01-21 20:01:58', '10.136.238.59', 'login'),
(222, 34, '$2y$10$7XUvCmFE7r.fj8WZdjpWleqZkBOTSbU7e1ioP6PUP/soxA51edgSi', 'whatsapp', '2026-01-21 20:06:59', '2026-01-21 19:01:59', NULL, '2026-01-21 20:01:59', '10.136.238.59', 'login'),
(223, 34, '$2y$10$h3ASIjvnjlyuBYbg0.6mS.ZsCPg4Pa1m75g1xJNFTXYqJd7ykKXLu', 'email', '2026-01-21 20:17:57', '2026-01-21 19:12:57', '2026-01-21 20:12:57', '2026-01-21 20:12:57', '10.136.238.59', 'login'),
(224, 34, '$2y$10$K7QXPBEd71bjVS45UXzq7OLmLRYY20zvJXMGXNez9OnwnLwn2Z2.a', 'whatsapp', '2026-01-21 20:17:57', '2026-01-21 19:12:57', '2026-01-21 20:16:25', '2026-01-21 20:16:25', '10.136.238.59', 'login'),
(225, 34, '$2y$10$cFmKFIl5xGC4dwMjsPBvmu1Bnex/MwkY18HVFYHYLhN/YQ7p96B2u', 'email', '2026-01-21 21:12:23', '2026-01-21 20:07:23', '2026-01-21 21:07:23', '2026-01-21 21:07:23', '10.136.238.59', 'login'),
(226, 34, '$2y$10$RHSGD7nxQN13cVYlIbX9SO67mZe.lZfEmJxjSmNkNbbI0LyfBDkFq', 'whatsapp', '2026-01-21 21:12:23', '2026-01-21 20:07:23', '2026-01-21 21:07:46', '2026-01-21 21:07:46', '10.136.238.59', 'login'),
(227, 34, '$2y$10$CZsQ3329n/iKpV/HykJs6.azGvkyc1LEXTDpOJBPtWO36jblInZTO', 'email', '2026-01-22 13:54:23', '2026-01-22 12:49:23', '2026-01-22 13:49:23', '2026-01-22 13:49:23', '192.168.1.221', 'login'),
(228, 34, '$2y$10$UMBkyxYOgZ6VmqceljjiSe8R0udmbYxvYEWarlM18oUhr8ozGz19.', 'whatsapp', '2026-01-22 13:54:23', '2026-01-22 12:49:23', '2026-01-22 13:50:00', '2026-01-22 13:50:00', '192.168.1.221', 'login'),
(229, 34, '$2y$10$hkTv7TjL5tiVcOnINVb4eupSFUJ8ZJ.jbZK.1V3gkGjXNbUJlUiDy', 'email', '2026-01-22 14:27:14', '2026-01-22 13:22:14', '2026-01-22 14:22:14', '2026-01-22 14:22:14', '192.168.1.221', 'login'),
(230, 34, '$2y$10$460/ZYLc81VBtunY.3K11ulpdBJhFwEAB7obyP0ve58ojlbQ9lrhu', 'whatsapp', '2026-01-22 14:27:15', '2026-01-22 13:22:15', '2026-01-22 14:23:55', '2026-01-22 14:23:55', '192.168.1.221', 'login'),
(231, 34, '$2y$10$YUA98.mVHKMM.mukVGRiY.i3uMiyy1BdPzCZeS3cdpJggATGNZJom', 'email', '2026-01-22 14:28:55', '2026-01-22 13:23:55', '2026-01-22 14:23:55', '2026-01-22 14:23:55', '192.168.1.221', 'login'),
(232, 34, '$2y$10$d1YjyISS1VeUDI4rVOMoT.YG.PQfVgR0pQ2iobLU9ACk/1dif2BcC', 'whatsapp', '2026-01-22 14:28:55', '2026-01-22 13:23:55', '2026-01-22 14:24:18', '2026-01-22 14:24:18', '192.168.1.221', 'login'),
(233, 34, '$2y$10$WjcOCH.E7MowcW.3dmz3zOHdZev/80wt8AUxUKCx3vPfWuHL4opIi', 'email', '2026-01-23 13:17:55', '2026-01-23 12:12:55', '2026-01-23 13:12:55', '2026-01-23 13:12:55', '10.186.11.59', 'login'),
(234, 34, '$2y$10$k/PaB8DU0TEO4DAEUZRegu66Dgx.WNWjHCgrBYTMFE4U45fuBgqRe', 'whatsapp', '2026-01-23 13:17:55', '2026-01-23 12:12:55', '2026-01-23 13:13:19', '2026-01-23 13:13:19', '10.186.11.59', 'login'),
(235, 34, '$2y$10$bY8UJQLwOEjJzs7IdWhIdeXiiglwnYLdYyNl5S7eN7CyhcEW2DrSO', 'email', '2026-01-23 19:33:35', '2026-01-23 18:28:35', '2026-01-23 19:28:35', '2026-01-23 19:28:35', '10.186.11.59', 'login'),
(236, 34, '$2y$10$quZ1HxQ0bZn7hSbOp29eGOT9c3OAot3mlJuv3/ED.ecHhQ6x3Cqje', 'whatsapp', '2026-01-23 19:33:35', '2026-01-23 18:28:35', '2026-01-23 19:31:27', '2026-01-23 19:31:27', '10.186.11.59', 'login'),
(237, 34, '$2y$10$zf9rZ0dDP8pXAHzu2DetsOfiCH886M5LV7KatJPvf3iabHmHXYUqu', 'email', '2026-01-24 08:30:05', '2026-01-24 07:25:05', '2026-01-24 08:25:05', '2026-01-24 08:25:05', '192.168.105.187', 'login'),
(238, 34, '$2y$10$OxwqvtDC0du3Dguy1NgLses6fnRoyC3jXG9hHoCyZK1SjLcssw1f6', 'whatsapp', '2026-01-24 08:30:06', '2026-01-24 07:25:06', NULL, '2026-01-24 08:25:06', '192.168.105.187', 'login'),
(239, 34, '$2y$10$ziqKdj5n421UmB5Y/SG89eCSW5NXNB.cMhYLDVdY.OJVSm1cqzXny', 'email', '2026-01-24 08:37:14', '2026-01-24 07:32:14', '2026-01-24 08:32:14', '2026-01-24 08:32:14', '192.168.105.187', 'login'),
(240, 34, '$2y$10$3oXtZti9n5bYbYCa4H7ZM.gGW59CJ44q2d0ipZ.oTyjMt31KKvuHq', 'whatsapp', '2026-01-24 08:37:14', '2026-01-24 07:32:14', NULL, '2026-01-24 08:32:14', '192.168.105.187', 'login'),
(241, 34, '$2y$10$wrr2zj.h7yThChQPbFm0T.nUwuiRNEzjiFXmBfT0DWUbn5RiTFfKy', 'email', '2026-01-24 09:59:11', '2026-01-24 08:54:11', '2026-01-24 09:54:11', '2026-01-24 09:54:11', '192.168.105.187', 'login'),
(242, 34, '$2y$10$4qRpmVJACzCCxiPk2q5yTOQZokyapINFQpZ48Kv920tPY1hRUNhQC', 'whatsapp', '2026-01-24 09:59:11', '2026-01-24 08:54:11', '2026-01-24 09:56:51', '2026-01-24 09:56:51', '192.168.105.187', 'login'),
(243, 34, '$2y$10$r7zl8n7CIWuLZMdCYFSnG.JXb7XUBTDM5eSMmtjVXFqDDmG1o5lA2', 'email', '2026-01-24 10:01:51', '2026-01-24 08:56:51', '2026-01-24 09:56:51', '2026-01-24 09:56:51', '192.168.105.187', 'login'),
(244, 34, '$2y$10$Z8osURPcHQtmVO5mT7CL3eCAcPHhDG9KV97QkIC/iXa8MinY/d6AC', 'whatsapp', '2026-01-24 10:01:52', '2026-01-24 08:56:52', NULL, '2026-01-24 09:56:52', '192.168.105.187', 'login'),
(245, 34, '$2y$10$tgPPVIEUs9ZT/nwQoDMC3OYVa.WX0qbbYf4Pd8C5KlwHVC9AqDlhW', 'email', '2026-01-24 10:11:46', '2026-01-24 09:06:46', '2026-01-24 10:06:46', '2026-01-24 10:06:46', '192.168.105.187', 'login'),
(246, 34, '$2y$10$zEg2B1CufTe3hMU.OPn2MO3uhm35ETQke08iTObuEMbzStOIxlhx6', 'whatsapp', '2026-01-24 10:11:46', '2026-01-24 09:06:46', '2026-01-24 10:07:23', '2026-01-24 10:07:23', '192.168.105.187', 'login'),
(247, 34, '$2y$10$Ubxr91bSoh4qc8yGSIt1yuLQ7oec3mGeBIjqiCUXWyD5rOenFL.UC', 'email', '2026-01-24 10:21:00', '2026-01-24 09:16:00', '2026-01-24 10:16:00', '2026-01-24 10:16:00', '10.186.11.59', 'login'),
(248, 34, '$2y$10$r872BbAM/4PJaAEfhXfPHu4n012sspfvcaueuihXjtviaMvdcfEMy', 'whatsapp', '2026-01-24 10:21:00', '2026-01-24 09:16:00', '2026-01-24 10:17:34', '2026-01-24 10:17:34', '10.186.11.59', 'login'),
(249, 34, '$2y$10$fLTUSgcwBA2rs/ixsGzpbew8VGO5GQ4K/3X586qPDLKJHWEF0Hyj2', 'email', '2026-01-24 12:16:26', '2026-01-24 11:11:26', '2026-01-24 12:11:26', '2026-01-24 12:11:26', '192.168.105.187', 'login'),
(250, 34, '$2y$10$5/cwHdvnb/lVtCM5pdQLoevPXr6tMKSwVbtU9Ogw3..xJeeepKE.i', 'whatsapp', '2026-01-24 12:16:26', '2026-01-24 11:11:26', NULL, '2026-01-24 12:11:26', '192.168.105.187', 'login'),
(251, 34, '$2y$10$H43XaHEnq7TiqYww6M9sDeKgvihUNIbpoLZ2GuemrH2iulFr96eE6', 'email', '2026-01-24 12:26:32', '2026-01-24 11:21:32', '2026-01-24 12:21:32', '2026-01-24 12:21:32', '192.168.105.187', 'login');
INSERT INTO `otps` (`id`, `user_id`, `otp_hash`, `channel`, `expires_at`, `created_at`, `used_at`, `updated_at`, `ip_address`, `purpose`) VALUES
(252, 34, '$2y$10$.Jstp0Smba3FpsNykWocb.NzRpuS5T9389YsBjfJgOfzdpwJFBkke', 'whatsapp', '2026-01-24 12:26:32', '2026-01-24 11:21:32', '2026-01-24 12:22:23', '2026-01-24 12:22:23', '192.168.105.187', 'login'),
(253, 34, '$2y$10$i.6V8Wtp.DgHqicTaFv7XOJah/QMZl//tvoCIuh6K3YauunxwTof.', 'email', '2026-01-24 12:27:23', '2026-01-24 11:22:23', '2026-01-24 12:22:23', '2026-01-24 12:22:23', '192.168.105.187', 'login'),
(254, 34, '$2y$10$n8Rvnd8WAwMYaPYx8zjF0.LW.PImvMHum5ZMoebI6EH0xSdQhbwMq', 'whatsapp', '2026-01-24 12:27:23', '2026-01-24 11:22:23', '2026-01-24 12:22:49', '2026-01-24 12:22:49', '192.168.105.187', 'login'),
(255, 34, '$2y$10$PPtSskHIgmBkkS6W6r5ghOGJimrZESuR3NF0qD3du068frppafOUq', 'email', '2026-01-24 12:55:34', '2026-01-24 11:50:34', '2026-01-24 12:50:34', '2026-01-24 12:50:34', '192.168.105.187', 'login'),
(256, 34, '$2y$10$BswQOwPd59Y8g21nmGqqceSrHUbrGuzcUMzaR5420hYZWZo35i97e', 'whatsapp', '2026-01-24 12:55:34', '2026-01-24 11:50:34', '2026-01-24 12:51:17', '2026-01-24 12:51:17', '192.168.105.187', 'login'),
(257, 34, '$2y$10$2aFiulYkTrfdpPFWhft/o.DNwE2YuO3ehijYHLbrW.XnVGawbq3DG', 'email', '2026-01-24 14:08:07', '2026-01-24 13:03:07', '2026-01-24 14:03:07', '2026-01-24 14:03:07', '192.168.105.187', 'login'),
(258, 34, '$2y$10$HZh.ncW/zwKCQ4JMUzdpM.Ut6tLU9a3sPLCI2oDiTcFjBesoTMZIW', 'whatsapp', '2026-01-24 14:08:07', '2026-01-24 13:03:07', '2026-01-24 14:03:31', '2026-01-24 14:03:31', '192.168.105.187', 'login'),
(259, 34, '$2y$10$N0pmBW.F5h6AGXC8D.A3AewQeeVIQwZ8NkUr02UDey03vaoiQzO1G', 'email', '2026-01-24 15:28:38', '2026-01-24 14:23:38', '2026-01-24 15:23:38', '2026-01-24 15:23:38', '192.168.105.187', 'login'),
(260, 34, '$2y$10$ulAAW1mWiPN5wV2w9HVfPeN4jtWen8Y8l4z5bJulPSTwr9AA1sb8.', 'whatsapp', '2026-01-24 15:28:38', '2026-01-24 14:23:38', '2026-01-24 15:24:55', '2026-01-24 15:24:55', '192.168.105.187', 'login'),
(261, 34, '$2y$10$.JG.eKKmqqEPv73rk3Q8A.OiaodpVOgT6vlu3zx51pKI/cs.W/zkO', 'email', '2026-01-24 16:16:56', '2026-01-24 15:11:56', '2026-01-24 16:11:56', '2026-01-24 16:11:56', '192.168.105.187', 'login'),
(262, 34, '$2y$10$5ySL63ZU12gCy6aMe0.Ux.mgQ3WHRuRnWqtzR89.gZy2WucwktsC6', 'whatsapp', '2026-01-24 16:16:56', '2026-01-24 15:11:56', '2026-01-24 16:12:43', '2026-01-24 16:12:43', '192.168.105.187', 'login'),
(263, 34, '$2y$10$CN25p.5aFqijsqS0z1Zl4O/WgAKC5ASQ.xb1k2hSP6kXr2tIW5Oki', 'email', '2026-01-24 16:30:34', '2026-01-24 15:25:34', '2026-01-24 16:25:34', '2026-01-24 16:25:34', '192.168.105.187', 'login'),
(264, 34, '$2y$10$RlLf5/RoCNtsDUkv3Qumv.IpnELhlRejfaj4M7.NWXHam9gWsYYvO', 'whatsapp', '2026-01-24 16:30:34', '2026-01-24 15:25:34', '2026-01-24 16:25:59', '2026-01-24 16:25:59', '192.168.105.187', 'login'),
(265, 34, '$2y$10$V0x842L5vFSAmkVCoB2x/.smqr5EejanF.0FliAT1hp76sa0bVlv2', 'email', '2026-01-24 17:06:17', '2026-01-24 16:01:17', '2026-01-24 17:01:17', '2026-01-24 17:01:17', '192.168.105.187', 'login'),
(266, 34, '$2y$10$dlPo..VHMffhAQpd1UTPm.3QzZ/4k/R6jxWHcuaDww5iMtPBE8FNy', 'whatsapp', '2026-01-24 17:06:17', '2026-01-24 16:01:17', '2026-01-24 17:02:07', '2026-01-24 17:02:07', '192.168.105.187', 'login'),
(267, 34, '$2y$10$Fjqe/1diyj9jQwH7g/adc.rJH5OmQntu/3hlVsvRs1LGUmtQn1FjS', 'email', '2026-01-24 18:25:55', '2026-01-24 17:20:55', '2026-01-24 18:20:55', '2026-01-24 18:20:55', '192.168.105.187', 'login'),
(268, 34, '$2y$10$dPUiFh/wD5RGtJW.X4vNmOOnhXXnrtx8eZfqI2UIjJz92ha5x1W/i', 'whatsapp', '2026-01-24 18:25:56', '2026-01-24 17:20:56', '2026-01-24 18:21:22', '2026-01-24 18:21:22', '192.168.105.187', 'login'),
(269, 34, '$2y$10$Xebj5ALrbiJ8sdYacpJL0OI.U.kAv7L4JpoHa8hc9P3UAxDnxKh7W', 'email', '2026-01-25 08:04:31', '2026-01-25 06:59:31', '2026-01-25 07:59:31', '2026-01-25 07:59:31', '10.186.11.59', 'login'),
(270, 34, '$2y$10$y0jbDM5E4WOyp3BC4iRBR.NtO4M1lWCqr7OGW55hwad.zrRPJW2Ey', 'whatsapp', '2026-01-25 08:04:31', '2026-01-25 06:59:31', '2026-01-25 08:00:06', '2026-01-25 08:00:06', '10.186.11.59', 'login'),
(271, 34, '$2y$10$a9vCJa866uNJtjZaV4kB8.KB42u5QFA3UpybUhFpyDzvuTobaqSDS', 'email', '2026-01-25 09:04:43', '2026-01-25 07:59:43', '2026-01-25 08:59:43', '2026-01-25 08:59:43', '10.186.11.59', 'login'),
(272, 34, '$2y$10$ulrOVBF3m0GxdYzmLx7Hm.K4H5vwao2DNpxVrz/opUIzg27lpNPEe', 'whatsapp', '2026-01-25 09:04:43', '2026-01-25 07:59:43', '2026-01-25 09:00:40', '2026-01-25 09:00:40', '10.186.11.59', 'login'),
(273, 34, '$2y$10$TM0MJJ7.aIva.D/fNf/OieV0YgGu/IV/ZnIHcezkxbtIxF7hF2/2S', 'email', '2026-01-25 09:30:44', '2026-01-25 08:25:44', '2026-01-25 09:25:44', '2026-01-25 09:25:44', '10.186.11.59', 'login'),
(274, 34, '$2y$10$KWbMbsLiGKjF0B5ErCfqTuAxeJs9BTwUwnVtK.wJNYEvf2NnpCA9K', 'whatsapp', '2026-01-25 09:30:45', '2026-01-25 08:25:45', '2026-01-25 09:26:16', '2026-01-25 09:26:16', '10.186.11.59', 'login'),
(275, 34, '$2y$10$qq5Gx0Tu6afw7orAmkkSKe9AxSy4EymUuKuwDSMvMBtmwfn/YjOAa', 'email', '2026-01-25 11:09:58', '2026-01-25 10:04:58', '2026-01-25 11:04:58', '2026-01-25 11:04:58', '10.186.11.59', 'login'),
(276, 34, '$2y$10$i/W6r/eFToVAPRHtUnapkubvOITZQIPx/8ZRYpHPxtkGqjFrqhsWG', 'whatsapp', '2026-01-25 11:09:58', '2026-01-25 10:04:58', '2026-01-25 11:07:14', '2026-01-25 11:07:14', '10.186.11.59', 'login'),
(277, 34, '$2y$10$0JbBSR5zzRM34PvEuyPVwOU4Rzsnb7hNFBsO6zKMSxc5vlqkSLCvK', 'email', '2026-01-25 11:12:14', '2026-01-25 10:07:14', '2026-01-25 11:07:14', '2026-01-25 11:07:14', '10.186.11.59', 'login'),
(278, 34, '$2y$10$aae7OP9n9fgMDKTF/6zcJ.GkbG7l3vOagiUkWRV.uq0gTwNzKw8Cy', 'whatsapp', '2026-01-25 11:12:14', '2026-01-25 10:07:14', '2026-01-25 11:07:41', '2026-01-25 11:07:41', '10.186.11.59', 'login'),
(279, 34, '$2y$10$xBZZJ1zwFgqtMMas/DCPd.LWpFBb0UMNrKq0Vqo96bX4LHpP8YL3W', 'email', '2026-01-25 12:20:05', '2026-01-25 11:15:05', '2026-01-25 12:15:05', '2026-01-25 12:15:05', '10.186.11.59', 'login'),
(280, 34, '$2y$10$6fHUJaftUmxZAN7SQ/c.hOPtCuMK3ahRSVKeAAo7bZMSTjjmhnVQK', 'whatsapp', '2026-01-25 12:20:05', '2026-01-25 11:15:05', '2026-01-25 12:16:37', '2026-01-25 12:16:37', '10.186.11.59', 'login'),
(281, 34, '$2y$10$PLTgmKbF9bTJxEMFySnBg.IhNpIcOEaxYm.YpQLjSYq6nGBdlrMGq', 'email', '2026-01-25 16:23:30', '2026-01-25 15:18:30', '2026-01-25 16:18:30', '2026-01-25 16:18:30', '10.210.87.59', 'login'),
(282, 34, '$2y$10$04fvkL64bXOesji2DI3Ms.uOwkJeiLNJQqiglpVCP0eQmW7HOZyLm', 'whatsapp', '2026-01-25 16:23:31', '2026-01-25 15:18:31', '2026-01-25 16:20:07', '2026-01-25 16:20:07', '10.210.87.59', 'login'),
(283, 34, '$2y$10$.mHBD2eFtnyHnu6ptpOTRuGH82YVXACOQmTBUrqmvbGHs7LQrWi0C', 'email', '2026-01-25 16:25:07', '2026-01-25 15:20:07', '2026-01-25 16:20:07', '2026-01-25 16:20:07', '10.210.87.59', 'login'),
(284, 34, '$2y$10$kua8jN42bEtWbRtqAdj3yuC8IvIH3S2PfD2jzDsBM5IazIcc6/lQm', 'whatsapp', '2026-01-25 16:25:07', '2026-01-25 15:20:07', NULL, '2026-01-25 16:20:07', '10.210.87.59', 'login'),
(285, 34, '$2y$10$04TIAVYnrGuG9btln/jE.e7AV7PUTq42tNy.nywh0252BhyheijJ6', 'email', '2026-01-25 18:48:11', '2026-01-25 17:43:11', '2026-01-25 18:43:11', '2026-01-25 18:43:11', '10.15.157.59', 'login'),
(286, 34, '$2y$10$.60hqbu2eo.DRQC2Uku3ru1Qgo3Uz4d0/E3mQmDBEt0fkibgCeisK', 'whatsapp', '2026-01-25 18:48:11', '2026-01-25 17:43:11', '2026-01-25 18:43:40', '2026-01-25 18:43:40', '10.15.157.59', 'login'),
(287, 34, '$2y$10$8qFYk6ivGGwZwoALOmW2e.ouaiIVFifjfztBidP6S1GIPgNxmBTlm', 'email', '2026-01-25 18:49:27', '2026-01-25 17:44:27', '2026-01-25 18:44:27', '2026-01-25 18:44:27', '10.15.157.59', 'login'),
(288, 34, '$2y$10$rLVoeIXIvqCnyqW/.0m4Wu659EQBM9grLXMO3FfgBW/NFhgTKmDF6', 'whatsapp', '2026-01-25 18:49:27', '2026-01-25 17:44:27', '2026-01-25 18:45:28', '2026-01-25 18:45:28', '10.15.157.59', 'login'),
(289, 34, '$2y$10$RbwMiK0Z2dQtMsjzdWKCsum/PQY9HnRJE/yw4JZqNeA9JfcxG2MIW', 'email', '2026-01-25 19:09:40', '2026-01-25 18:04:40', '2026-01-25 19:04:40', '2026-01-25 19:04:40', '10.15.157.59', 'login'),
(290, 34, '$2y$10$a0LwkSLeQGTRP/fip4nA0uBpnRPJfkWVPO47QzNxWlx765NoKgMX.', 'whatsapp', '2026-01-25 19:09:40', '2026-01-25 18:04:40', '2026-01-25 19:05:03', '2026-01-25 19:05:03', '10.15.157.59', 'login'),
(291, 34, '$2y$10$0solSQQX8tOmqCzGvBYMoOfX8G8uDc12PEbcjRlT8GZ9KKUKzwiAS', 'email', '2026-01-25 19:27:16', '2026-01-25 18:22:16', '2026-01-25 19:22:16', '2026-01-25 19:22:16', '10.15.157.59', 'login'),
(292, 34, '$2y$10$hHEPWh9QVgZwbm7AKdvx8eSm9kL/7Xs0oH3P2x9iuAXd46ad1qf9a', 'whatsapp', '2026-01-25 19:27:17', '2026-01-25 18:22:17', '2026-01-25 19:22:50', '2026-01-25 19:22:50', '10.15.157.59', 'login'),
(293, 34, '$2y$10$bGfca3mEs52ehurZaiDUr.DogcMKkb17iZb5we9IOG7ozbi/eyp5i', 'email', '2026-01-25 19:53:30', '2026-01-25 18:48:30', '2026-01-25 19:48:30', '2026-01-25 19:48:30', '10.15.157.59', 'login'),
(294, 34, '$2y$10$cXiPdTcu8/UTXSaHDsNkSe787Tdl1Boc.1wnDE7fvMnGDOqU4XSxG', 'whatsapp', '2026-01-25 19:53:30', '2026-01-25 18:48:30', '2026-01-25 19:49:17', '2026-01-25 19:49:17', '10.15.157.59', 'login'),
(295, 34, '$2y$10$hbt3g.q22BhT2Rz5v2PBPeE/heKq1yBtUOJ743Fe1gNd28tTVSFGy', 'email', '2026-01-26 14:12:10', '2026-01-26 13:07:10', '2026-01-26 14:07:10', '2026-01-26 14:07:10', '192.168.1.221', 'login'),
(296, 34, '$2y$10$tPWVglx2JvYYNtRGEQ42UObPXXBpnj1O0gxCf1Tjs6SWGtsS60j2K', 'whatsapp', '2026-01-26 14:12:10', '2026-01-26 13:07:10', '2026-01-26 14:10:05', '2026-01-26 14:10:05', '192.168.1.221', 'login'),
(297, 34, '$2y$10$rjvgewefcgp8VHazqQUPOe3tFlJP/XYLXyuzdkX/vvXtqgueNSvNW', 'email', '2026-01-26 14:49:42', '2026-01-26 13:44:42', '2026-01-26 14:44:42', '2026-01-26 14:44:42', '192.168.1.221', 'login'),
(298, 34, '$2y$10$3fo67EIp960aoZkZL/WsbuH4.rr5PMCMGyMgiwXnluO4ps6fZJBa6', 'whatsapp', '2026-01-26 14:49:42', '2026-01-26 13:44:42', '2026-01-26 14:45:08', '2026-01-26 14:45:08', '192.168.1.221', 'login'),
(299, 34, '$2y$10$aWXDWbCDXFIrjmu/7pQltul4nTJt84lycKkscGtinc26rGxd/TFDu', 'email', '2026-01-26 16:00:52', '2026-01-26 14:55:52', '2026-01-26 15:55:52', '2026-01-26 15:55:52', '192.168.1.221', 'login'),
(300, 34, '$2y$10$HH/MEwhGEXMrswwBmilFAeIXvU0vQzeGc9.8s2C8gngZgm0SOTuli', 'whatsapp', '2026-01-26 16:00:52', '2026-01-26 14:55:52', '2026-01-26 15:56:17', '2026-01-26 15:56:17', '192.168.1.221', 'login'),
(301, 34, '$2y$10$iWwv3n16/RDEsoMri6Pn.OSB2DrptUeEwkJCp5Y9S36QcATz74Ztq', 'email', '2026-01-26 17:08:54', '2026-01-26 16:03:54', '2026-01-26 17:03:54', '2026-01-26 17:03:54', '192.168.1.221', 'login'),
(302, 34, '$2y$10$wHRIdgYETD6QyJmUuZLDkO770hdAZgUg12mruCmrcM7kCJ/E3JOPa', 'whatsapp', '2026-01-26 17:08:54', '2026-01-26 16:03:54', '2026-01-26 17:04:19', '2026-01-26 17:04:19', '192.168.1.221', 'login'),
(303, 34, '$2y$10$giQFf.ltPKslJYm1NxaOnuPSrgXZnR/1pguJWMZCqvObCpeAarZqC', 'email', '2026-01-26 20:01:13', '2026-01-26 18:56:13', '2026-01-26 19:56:13', '2026-01-26 19:56:13', '10.74.169.59', 'login'),
(304, 34, '$2y$10$CH3VipHOvneL0znM8kWlDOqD/SMze7DFBoC6WgjDxr4OWdVfxrVea', 'whatsapp', '2026-01-26 20:01:13', '2026-01-26 18:56:13', '2026-01-26 19:57:17', '2026-01-26 19:57:17', '10.74.169.59', 'login'),
(305, 34, '$2y$10$sjxHVNN/kb8QoAsudBdGTOapJugnq4aQiCib7qUiJp/t9HslkEm0e', 'email', '2026-01-26 21:26:13', '2026-01-26 20:21:13', '2026-01-26 21:21:13', '2026-01-26 21:21:13', '10.74.169.59', 'login'),
(306, 34, '$2y$10$6w15j93WKpG2aYe6Vw7OHuFCYZdK5xrWk1yXpBuad64Se.Ojlyg3m', 'whatsapp', '2026-01-26 21:26:13', '2026-01-26 20:21:13', '2026-01-26 21:21:46', '2026-01-26 21:21:46', '10.74.169.59', 'login'),
(307, 34, '$2y$10$zDjRk.TJwywPuXKVHqBP7euX1ZVpMopTszJygQuLIxGW45C7XwzTG', 'email', '2026-01-26 21:31:26', '2026-01-26 20:26:26', '2026-01-26 21:26:26', '2026-01-26 21:26:26', '10.74.169.59', 'login'),
(308, 34, '$2y$10$U2XPkjAMvFImR09ZgEUb9eAtntRtbbvScKYxxFbKB8vrzU8pLk6sG', 'whatsapp', '2026-01-26 21:31:26', '2026-01-26 20:26:26', '2026-01-26 21:27:38', '2026-01-26 21:27:38', '10.74.169.59', 'login'),
(309, 34, '$2y$10$s3p8YkxramDRYK1403nx9.TSNDVq0nTpnajM8Bu1rpsBKuODdNf/S', 'email', '2026-01-26 21:32:38', '2026-01-26 20:27:38', '2026-01-26 21:27:38', '2026-01-26 21:27:38', '10.74.169.59', 'login'),
(310, 34, '$2y$10$DIS0GpKG8lI7GkuF/.TLiegEha1A.RGs4yMpIeWZfJzWLaZJSJG5O', 'whatsapp', '2026-01-26 21:32:39', '2026-01-26 20:27:39', NULL, '2026-01-26 21:27:39', '10.74.169.59', 'login'),
(311, 34, '$2y$10$uMLMjv.6oIsv4srQ1250Fu/s86XTWVYCf7CIQuxslfLAeBKBdtZ3W', 'email', '2026-01-27 18:17:00', '2026-01-27 17:12:00', '2026-01-27 18:12:00', '2026-01-27 18:12:00', '10.211.141.59', 'login'),
(312, 34, '$2y$10$HoaqBmM7GLIEcXd7imaB3.YkqbnU8M1a4RqsrzIzDXGE5sVYesYMq', 'whatsapp', '2026-01-27 18:17:00', '2026-01-27 17:12:00', '2026-01-27 18:12:35', '2026-01-27 18:12:35', '10.211.141.59', 'login'),
(313, 34, '$2y$10$30cvvjo/SGs/cwdZw94zaehsAtLZPPstRpWGoNqU4F8PKbJ3yjuBu', 'email', '2026-01-27 20:08:03', '2026-01-27 19:03:03', '2026-01-27 20:03:03', '2026-01-27 20:03:03', '10.211.141.59', 'login'),
(314, 34, '$2y$10$pBZyAbjwzaSBrI4JS.qVOOUWjUZX.J08JGES8wrThfIQ.Ti.BHNzm', 'whatsapp', '2026-01-27 20:08:03', '2026-01-27 19:03:03', '2026-01-27 20:04:02', '2026-01-27 20:04:02', '10.211.141.59', 'login'),
(315, 34, '$2y$10$gHHF5LBbIMKAY2KMu2JbyuXO.zwlCc4reRnZRcD7AGRiHT0vdAKs6', 'email', '2026-01-27 20:16:02', '2026-01-27 19:11:02', '2026-01-27 20:11:02', '2026-01-27 20:11:02', '10.211.141.59', 'login'),
(316, 34, '$2y$10$0QF9TfLU.qkEn5qnW5o0.O.oihVC09PrJs8jW50xNoyvC/owgfrqy', 'whatsapp', '2026-01-27 20:16:02', '2026-01-27 19:11:02', '2026-01-27 20:12:41', '2026-01-27 20:12:41', '10.211.141.59', 'login'),
(317, 34, '$2y$10$1AJdkzD6Ey8mSxvKUrkGYe6AcbKjLIs5oI5JM.1ajiq6dJtaEfBGy', 'email', '2026-01-27 21:32:09', '2026-01-27 20:27:09', '2026-01-27 21:27:09', '2026-01-27 21:27:09', '10.211.141.59', 'login'),
(318, 34, '$2y$10$W8RO5vbNXz8CsIlzeBF1weiILG/GE45UrP8ibxJJR0D0hrgRsjl/6', 'whatsapp', '2026-01-27 21:32:09', '2026-01-27 20:27:09', '2026-01-27 21:28:24', '2026-01-27 21:28:24', '10.211.141.59', 'login'),
(319, 34, '$2y$10$CBUGLOmbcp1aDtSo0omB1eMvtRUL1anbmdU1v2C4Xpl.kJZOmEWP.', 'email', '2026-01-27 21:59:36', '2026-01-27 20:54:36', '2026-01-27 21:54:36', '2026-01-27 21:54:36', '10.211.141.59', 'login'),
(320, 34, '$2y$10$bzoH.3tCjX6dMEzQfbv44ewXLD0xZwxD2YrQ5DDDb4FzMUIQgTPXS', 'whatsapp', '2026-01-27 21:59:37', '2026-01-27 20:54:37', '2026-01-27 21:55:19', '2026-01-27 21:55:19', '10.211.141.59', 'login'),
(321, 34, '$2y$10$o0PTPKFfonZ6a3zTSoNdgOP5x0TsdeIsXnpFHbYqs4rhgo8dCwY/a', 'email', '2026-01-27 22:00:20', '2026-01-27 20:55:20', '2026-01-27 21:55:20', '2026-01-27 21:55:20', '10.211.141.59', 'login'),
(322, 34, '$2y$10$9ll0pHA9Jn6W1RSRG2NVx.T15oLBkekyrI1i2w6JQ2Kk/dXpikyNC', 'whatsapp', '2026-01-27 22:00:20', '2026-01-27 20:55:20', '2026-01-27 21:58:36', '2026-01-27 21:58:36', '10.211.141.59', 'login'),
(323, 34, '$2y$10$dB7N7LbY6Bt6kZ5X6M1F7uJPNr6W3Ybal.6US.EnkqjFNj6LIevZ2', 'email', '2026-01-28 09:59:50', '2026-01-28 08:54:50', '2026-01-28 09:54:50', '2026-01-28 09:54:50', '192.168.1.221', 'login'),
(324, 34, '$2y$10$Nuu.iNnLH118mhKuZRILLeoNbsmFgqtR9i8fgg.ilizRrdCpWF.lG', 'whatsapp', '2026-01-28 09:59:50', '2026-01-28 08:54:50', '2026-01-28 09:55:27', '2026-01-28 09:55:27', '192.168.1.221', 'login'),
(325, 34, '$2y$10$JpQ1Si/AmPLy8dekPUfc1.Ug4HoPgxSsIQ1eFukCCBOHPKbhD.CG2', 'email', '2026-01-28 10:23:22', '2026-01-28 09:18:22', '2026-01-28 10:18:22', '2026-01-28 10:18:22', '192.168.1.221', 'login'),
(326, 34, '$2y$10$4PkP.e/D9Q9brOcGkDv1Qufr5rbDTl7XO0ucswiyjDSTqDhZDWjjG', 'whatsapp', '2026-01-28 10:23:23', '2026-01-28 09:18:23', '2026-01-28 10:19:13', '2026-01-28 10:19:13', '192.168.1.221', 'login'),
(327, 34, '$2y$10$bSb9HxzLdh/Gu94SzfLKneRS/0EpFnMRgs/6hC466sEHULiQbTwIq', 'email', '2026-01-28 10:50:11', '2026-01-28 09:45:11', '2026-01-28 10:45:11', '2026-01-28 10:45:11', '192.168.1.221', 'login'),
(328, 34, '$2y$10$vnWqjt9yrkxFxC.Jg4JzqO4bcZPl4WmxIEYILz7jw7tPyod8rh9Z6', 'whatsapp', '2026-01-28 10:50:12', '2026-01-28 09:45:12', '2026-01-28 10:45:34', '2026-01-28 10:45:34', '192.168.1.221', 'login'),
(329, 34, '$2y$10$UAuH/2iDF3daeRe.EGmaZut/GDC4yLrY.C85WH5sEXBShj4ZgMkLW', 'email', '2026-01-28 11:56:07', '2026-01-28 10:51:07', '2026-01-28 11:51:07', '2026-01-28 11:51:07', '192.168.1.221', 'login'),
(330, 34, '$2y$10$nx8uWHXbAI6murnt6uBu1uWcZn.v8i20HfB1FGfRfdlXmIomgLsDu', 'whatsapp', '2026-01-28 11:56:08', '2026-01-28 10:51:08', NULL, '2026-01-28 11:51:08', '192.168.1.221', 'login'),
(331, 34, '$2y$10$7z5vZOwqT0xwKuar3OCnSu.oUiKEW23OJNJjLv3LIJngUXatm7Iz.', 'email', '2026-01-28 12:33:31', '2026-01-28 11:28:31', '2026-01-28 12:28:31', '2026-01-28 12:28:31', '192.168.1.221', 'login'),
(332, 34, '$2y$10$CUdCUn2Q2QouNFycHzrgKumUeFCUA213txXdPsbXf01deYU7NtpXm', 'whatsapp', '2026-01-28 12:33:32', '2026-01-28 11:28:32', '2026-01-28 12:28:55', '2026-01-28 12:28:55', '192.168.1.221', 'login'),
(333, 34, '$2y$10$ZpiRDG1NMor1Z44GvIxt9evzbdDTj90B49bnb00WPn.87H/z2Jipe', 'email', '2026-01-28 12:33:55', '2026-01-28 11:28:55', '2026-01-28 12:28:55', '2026-01-28 12:28:55', '192.168.1.221', 'login'),
(334, 34, '$2y$10$/qW/jKFGIJxBli0793RXJOSU4mgEBRtz/El5Gu6y/BdYVHnKSsJf6', 'whatsapp', '2026-01-28 12:33:55', '2026-01-28 11:28:55', '2026-01-28 12:29:26', '2026-01-28 12:29:26', '192.168.1.221', 'login'),
(335, 34, '$2y$10$zBaezW2kRxor.laVRTbHDeDcMsB3JTgGT0GCaJl.c0z536Q35trde', 'email', '2026-01-28 13:33:36', '2026-01-28 12:28:36', '2026-01-28 13:28:36', '2026-01-28 13:28:36', '192.168.1.221', 'login'),
(336, 34, '$2y$10$9uueu29BIn0ijXlILU2eEOeslzUgJuPMKJOYjiBRFWWj48F9m8gJW', 'whatsapp', '2026-01-28 13:33:36', '2026-01-28 12:28:36', '2026-01-28 13:28:59', '2026-01-28 13:28:59', '192.168.1.221', 'login'),
(337, 31, '$2y$10$Tmdv1C4IY4gsRtI9u9ZZkevGs/tDmYNaEiz/r7kDLP6XigJcYrWHC', 'email', '2026-01-28 13:34:23', '2026-01-28 12:29:23', '2026-01-28 13:29:23', '2026-01-28 13:29:23', '192.168.1.221', 'login'),
(338, 31, '$2y$10$CPmLNlDjsaA8n2xReiu/.uh3gXjSvD3N5T8feAOSjOM8vOeulabkS', 'whatsapp', '2026-01-28 13:34:24', '2026-01-28 12:29:24', '2026-01-28 13:29:52', '2026-01-28 13:29:52', '192.168.1.221', 'login'),
(339, 31, '$2y$10$lJ/fCrrzT.ET05f7tJI5EuWXZdED.7FzIunkA73LPJDkdNsrFzKcK', 'email', '2026-01-28 13:34:52', '2026-01-28 12:29:52', '2026-01-28 13:29:52', '2026-01-28 13:29:52', '192.168.1.221', 'login'),
(340, 31, '$2y$10$PAepYBDwr/DB6E0ATbq6o.xSnKWiFFhvvndHM.ugnbntnG15vvfIK', 'whatsapp', '2026-01-28 13:34:52', '2026-01-28 12:29:52', NULL, '2026-01-28 13:29:52', '192.168.1.221', 'login'),
(341, 34, '$2y$10$3CS7ICyY3WEQ2SgU74G3Ne3ti9Qa8VPfCdezCRaWJgmOCYgEcnFY2', 'email', '2026-01-28 14:12:08', '2026-01-28 13:07:08', '2026-01-28 14:07:08', '2026-01-28 14:07:08', '192.168.1.221', 'login'),
(342, 34, '$2y$10$J75jjOKcJm12en.wm8QvNuOV3TE5cwHBTQd8SwvtccHFou4ygPUPa', 'whatsapp', '2026-01-28 14:12:09', '2026-01-28 13:07:09', '2026-01-28 14:07:50', '2026-01-28 14:07:50', '192.168.1.221', 'login'),
(343, 34, '$2y$10$LLGOgi4becrjt/v3YmhOCOSlbtk1bhCsOMIXZ7K4927GEAXepwu96', 'email', '2026-01-28 14:24:07', '2026-01-28 13:19:07', '2026-01-28 14:19:07', '2026-01-28 14:19:07', '192.168.1.221', 'login'),
(344, 34, '$2y$10$RoetNDogJm4gprj47EK25O8rXElSWzfXMx4DU.YLv5rxnNZ4lJ2Pi', 'whatsapp', '2026-01-28 14:24:07', '2026-01-28 13:19:07', '2026-01-28 14:19:31', '2026-01-28 14:19:31', '192.168.1.221', 'login'),
(345, 34, '$2y$10$Exb20IDWArASNaGOm35ABueWzq1LCb9O/dBW8Rs7LJxs2Y0T06KuK', 'email', '2026-01-29 14:37:53', '2026-01-29 13:32:53', '2026-01-29 14:32:53', '2026-01-29 14:32:53', '10.227.175.59', 'login'),
(346, 34, '$2y$10$wORDQgelZ0oNiGIXH2GZKOd1jeDW0AjPNuEdWcG/me5gC8Y6L48GG', 'whatsapp', '2026-01-29 14:37:53', '2026-01-29 13:32:53', '2026-01-29 14:33:20', '2026-01-29 14:33:20', '10.227.175.59', 'login'),
(347, 34, '$2y$10$JrVrDxbw/17PSRq208S1zuG21igfSnX42CxOCg7haL15er6SlD10O', 'email', '2026-01-29 16:02:06', '2026-01-29 14:57:06', '2026-01-29 15:57:06', '2026-01-29 15:57:06', '10.227.175.59', 'login'),
(348, 34, '$2y$10$R6MMCxOK6TX6MH3DYGX5Eeu/sHB5/C27GGAglc0MPqhd.0.NFGAiC', 'whatsapp', '2026-01-29 16:02:06', '2026-01-29 14:57:06', '2026-01-29 15:58:11', '2026-01-29 15:58:11', '10.227.175.59', 'login'),
(349, 34, '$2y$10$UXeTHBTLIgZPmhnu1oA6TunejCZemuRgg4SR1Sj3WVy0klAhO0g/C', 'email', '2026-01-29 17:12:42', '2026-01-29 16:07:42', '2026-01-29 17:07:42', '2026-01-29 17:07:42', '10.227.175.59', 'login'),
(350, 34, '$2y$10$c/lOWNw2imiD4PNLrHWVteKUmsggu4u1GDNRcVFmqlpTPcVqAJNW6', 'whatsapp', '2026-01-29 17:12:42', '2026-01-29 16:07:42', '2026-01-29 17:08:56', '2026-01-29 17:08:56', '10.227.175.59', 'login');

-- --------------------------------------------------------

--
-- Table structure for table `payouts`
--

CREATE TABLE `payouts` (
  `id` int UNSIGNED NOT NULL,
  `provider_id` int NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `status` enum('pending','processed','failed') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'pending',
  `payment_method` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `transaction_id` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `requested_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `processed_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payouts`
--

INSERT INTO `payouts` (`id`, `provider_id`, `amount`, `status`, `payment_method`, `transaction_id`, `requested_at`, `processed_at`) VALUES
(1, 33, 20000.00, 'pending', 'yugygy', 'tftyftyft', '2026-01-17 15:35:21', '2026-01-17 15:34:52');

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

CREATE TABLE `permissions` (
  `id` int UNSIGNED NOT NULL,
  `group_name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `permission_name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `description` text COLLATE utf8mb4_general_ci,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `permissions`
--

INSERT INTO `permissions` (`id`, `group_name`, `permission_name`, `description`, `created_at`, `updated_at`) VALUES
(1, 'Users', 'view-users', 'View list of users', NULL, NULL),
(2, 'Users', 'create-users', 'Create a new user', NULL, NULL),
(3, 'Users', 'edit-users', 'Edit user details', NULL, NULL),
(4, 'Users', 'delete-users', 'Delete a user', NULL, NULL),
(5, 'Providers', 'view-providers', 'View list of providers', NULL, NULL),
(6, 'Providers', 'approve-providers', 'Approve or reject provider applications', NULL, NULL),
(7, 'System', 'manage-roles', 'Manage roles and permissions', NULL, NULL),
(8, 'Reports', 'view-reports', 'View system reports', NULL, NULL),
(9, 'Finance', 'view-ledger', 'View financial ledger', NULL, NULL),
(10, 'Finance', 'manage-payouts', 'Manage provider payouts', NULL, NULL),
(11, 'Finance', 'manage-refunds', 'Manage customer refunds', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `refunds`
--

CREATE TABLE `refunds` (
  `id` int UNSIGNED NOT NULL,
  `user_id` int NOT NULL,
  `transaction_id` int UNSIGNED DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `reason` text COLLATE utf8mb4_general_ci NOT NULL,
  `status` enum('pending','approved','rejected') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'pending',
  `submitted_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `processed_by` int DEFAULT NULL,
  `processed_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `refunds`
--

INSERT INTO `refunds` (`id`, `user_id`, `transaction_id`, `amount`, `reason`, `status`, `submitted_at`, `processed_by`, `processed_at`) VALUES
(1, 31, 1, 30000.00, 'Bad service', 'pending', '2026-01-15 23:19:19', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` int UNSIGNED NOT NULL,
  `role_name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `description` text COLLATE utf8mb4_general_ci,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `role_name`, `description`, `created_at`, `updated_at`) VALUES
(1, 'costomer care', '', '2026-01-18 14:36:57', '2026-01-18 14:36:57'),
(2, 'HR', '', '2026-01-18 14:49:37', '2026-01-18 14:49:37');

-- --------------------------------------------------------

--
-- Table structure for table `role_permissions`
--

CREATE TABLE `role_permissions` (
  `id` int UNSIGNED NOT NULL,
  `role_id` int UNSIGNED NOT NULL,
  `permission_id` int UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `role_permissions`
--

INSERT INTO `role_permissions` (`id`, `role_id`, `permission_id`) VALUES
(1, 1, 1),
(2, 1, 2),
(3, 1, 5),
(4, 1, 7),
(5, 1, 8),
(6, 1, 9),
(7, 1, 11);

-- --------------------------------------------------------

--
-- Table structure for table `services`
--

CREATE TABLE `services` (
  `id` int UNSIGNED NOT NULL,
  `category_id` int UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `description` text COLLATE utf8mb4_general_ci,
  `status` enum('active','inactive') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'active',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `services`
--

INSERT INTO `services` (`id`, `category_id`, `name`, `description`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, 'Bore Hole', 'office or home', 'active', '2026-01-17 19:23:03', '2026-01-18 07:31:48'),
(2, 1, 'Overhead tank', 'fixing', 'active', '2026-01-17 18:39:14', '2026-01-17 18:39:14'),
(3, 2, 'renewable', 'solar', 'active', '2026-01-17 18:39:14', '2026-01-17 18:39:14'),
(4, 2, 'grid', 'grid', 'active', '2026-01-17 19:40:32', '2026-01-17 19:40:32'),
(5, 3, 'furniture', 'all kinds', 'active', '2026-01-17 19:59:15', '2026-01-17 19:59:15'),
(6, 4, 'Residential', 'Home cleaning and chores', 'active', '2026-01-18 10:43:45', '2026-01-18 10:43:45'),
(7, 2, 'wiring', 'house or office', 'active', '2026-01-25 12:50:22', '2026-01-25 12:50:22');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(15) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('Admin','Provider','Customer') NOT NULL DEFAULT 'Customer',
  `kyc_status` enum('Pending','Verified','Rejected') NOT NULL DEFAULT 'Pending',
  `decision_reason` text NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `status` enum('pending','active','suspended') DEFAULT 'active',
  `is_provider` tinyint(1) NOT NULL DEFAULT '0',
  `provider_status` enum('pending','approved','rejected') DEFAULT NULL,
  `approved_by` int DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  `company_name` varchar(150) DEFAULT NULL,
  `is_company` tinyint(1) NOT NULL,
  `location` varchar(150) DEFAULT NULL,
  `services` text,
  `provider_applied_at` datetime DEFAULT NULL,
  `google_id` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `phone`, `password`, `role`, `kyc_status`, `decision_reason`, `created_at`, `updated_at`, `status`, `is_provider`, `provider_status`, `approved_by`, `approved_at`, `company_name`, `is_company`, `location`, `services`, `provider_applied_at`, `google_id`) VALUES
(31, 'Sadiya Hassan', 'seamlesscallservices@gmail.com', '+2348012345678', '$2y$10$zgn3k397wtXhx601lwmd/u5UtMCuoIxMcX/CmatO0XWuzpq9OOx/q', 'Provider', 'Rejected', '', '2026-01-07 12:43:24', '2026-01-29 16:45:33', 'active', 1, 'approved', 34, '2026-01-28 13:31:16', 'msnsnsn', 0, 'sgsgsgs', NULL, '2026-01-28 12:51:13', NULL),
(33, 'Provider Gunnu', 'prov@gmail.com', '+2348088888888', '$2y$10$c6A/P9..w81BpCB1e/G75upaJhlpkvnheXZnP1K9YkqzWCmutu40a', 'Provider', 'Pending', '', '2026-01-07 23:08:15', '2026-01-29 17:44:05', 'pending', 1, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL),
(34, 'IDRIS BELLO', 'idrisalibello@gmail.com', '+2348036967483', '$2y$10$iA4amEDQUAW7Z6LWf5bAJuwPxPm16is/lJPXidVfpbmcBHFslPqPq', 'Admin', 'Pending', '', '2026-01-10 12:44:34', '2026-01-11 12:51:46', 'active', 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL),
(35, 'Khadijah Ismail', 'luvlykhad@gmail.com', '+2348064684365', '$2y$10$MLdtvvhnEVk6KxoqNvYGte559X/U/lAFAlXIiTIOem9E7dj1AFuAq', 'Provider', 'Pending', '', '2026-01-11 11:51:20', '2026-01-29 17:44:52', 'active', 1, 'approved', 34, '2026-01-15 19:29:49', 'Home Services', 0, 'Kaduna', NULL, '2026-01-15 14:49:56', NULL),
(36, 'Sumaya Ayuba', 'seamlesscallapp1@gmail.com', '+2348022222222', '$2y$10$2hEmC1qb/L2cp6mxaxXL1OtnmwAS5anZj86s.dAVovpQiREYsuzQe', 'Customer', 'Pending', '', '2026-01-12 10:20:35', '2026-01-15 20:24:28', 'active', 0, 'rejected', 34, '2026-01-15 19:30:32', '', 0, 'Kaduna', NULL, '2026-01-15 18:17:50', NULL),
(37, 'Sumaya Ayuba', 'seamlesscallap@gmail.com', '+2348022222334', '$2y$10$2hEmC1qb/L2cp6mxaxXL1OtnmwAS5anZj86s.dAVovpQiREYsuzQe', 'Provider', 'Pending', '', '2026-01-12 10:20:35', '2026-01-12 10:20:35', 'active', 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL),
(38, 'Ismail Ali Bello', 'seammlesscallapp1@gmail.com', '+2348044444444', '$2y$10$zgn3k397wtXhx601lwmd/u5UtMCuoIxMcX/CmatO0XWuzpq9OOx/q', 'Admin', 'Pending', '', '2026-01-15 19:27:57', '2026-01-15 20:43:54', 'active', 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL),
(40, 'Aisha Abdullahi', 'seamlesscallapp@gmail.com', '+2348077777777', '$2y$10$O7b6KH5qYQsAl0sa/Eqv.ed7gXuuWQezVX86Xed001xUEgO3/ZQm.', 'Admin', 'Pending', '', '2026-01-15 19:48:09', '2026-01-15 19:48:09', 'active', 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL),
(41, 'Hafsat Salis', 'hs@gmail.com', '+2348034234321', '$2y$10$inQlO3UkYMo4MDs/YWafhepVgXnOFv8vYpTnSP.ylPbBqEReLlyui', 'Admin', 'Pending', '', '2026-01-18 12:32:11', '2026-01-18 12:32:11', 'active', 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `user_roles`
--

CREATE TABLE `user_roles` (
  `id` int UNSIGNED NOT NULL,
  `user_id` int NOT NULL,
  `role_id` int UNSIGNED NOT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `verification_cases`
--

CREATE TABLE `verification_cases` (
  `id` int UNSIGNED NOT NULL,
  `user_id` int UNSIGNED NOT NULL,
  `status` enum('pending','verified','rejected','escalated') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'pending',
  `decision_reason` text COLLATE utf8mb4_general_ci,
  `escalation_reason` text COLLATE utf8mb4_general_ci,
  `decided_by` int UNSIGNED DEFAULT NULL,
  `decided_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `verification_cases`
--

INSERT INTO `verification_cases` (`id`, `user_id`, `status`, `decision_reason`, `escalation_reason`, `decided_by`, `decided_at`, `created_at`, `updated_at`) VALUES
(1, 33, 'pending', 'helhele', 'blo blo', 1, NULL, NULL, '2026-01-28 13:36:11');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_log`
--
ALTER TABLE `activity_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `activity_log_user_id_foreign` (`user_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `earnings`
--
ALTER TABLE `earnings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `earnings_provider_id_foreign` (`provider_id`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_customer_id_foreign` (`customer_id`),
  ADD KEY `jobs_provider_id_foreign` (`provider_id`),
  ADD KEY `jobs_service_id_foreign` (`service_id`);

--
-- Indexes for table `ledger`
--
ALTER TABLE `ledger`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ledger_user_id_foreign` (`user_id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `otps`
--
ALTER TABLE `otps`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `payouts`
--
ALTER TABLE `payouts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `payouts_provider_id_foreign` (`provider_id`);

--
-- Indexes for table `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `refunds`
--
ALTER TABLE `refunds`
  ADD PRIMARY KEY (`id`),
  ADD KEY `refunds_user_id_foreign` (`user_id`),
  ADD KEY `refunds_transaction_id_foreign` (`transaction_id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `role_name` (`role_name`);

--
-- Indexes for table `role_permissions`
--
ALTER TABLE `role_permissions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `role_permissions_role_id_foreign` (`role_id`),
  ADD KEY `role_permissions_permission_id_foreign` (`permission_id`);

--
-- Indexes for table `services`
--
ALTER TABLE `services`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`),
  ADD KEY `services_category_id_foreign` (`category_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `phone` (`phone`);

--
-- Indexes for table `user_roles`
--
ALTER TABLE `user_roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id_role_id` (`user_id`,`role_id`),
  ADD KEY `user_roles_role_id_foreign` (`role_id`);

--
-- Indexes for table `verification_cases`
--
ALTER TABLE `verification_cases`
  ADD PRIMARY KEY (`id`),
  ADD KEY `provider_id` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_log`
--
ALTER TABLE `activity_log`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `earnings`
--
ALTER TABLE `earnings`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `ledger`
--
ALTER TABLE `ledger`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `otps`
--
ALTER TABLE `otps`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=351;

--
-- AUTO_INCREMENT for table `payouts`
--
ALTER TABLE `payouts`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `refunds`
--
ALTER TABLE `refunds`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `role_permissions`
--
ALTER TABLE `role_permissions`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `services`
--
ALTER TABLE `services`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

--
-- AUTO_INCREMENT for table `user_roles`
--
ALTER TABLE `user_roles`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `verification_cases`
--
ALTER TABLE `verification_cases`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `activity_log`
--
ALTER TABLE `activity_log`
  ADD CONSTRAINT `activity_log_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `earnings`
--
ALTER TABLE `earnings`
  ADD CONSTRAINT `earnings_provider_id_foreign` FOREIGN KEY (`provider_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `jobs`
--
ALTER TABLE `jobs`
  ADD CONSTRAINT `jobs_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `jobs_provider_id_foreign` FOREIGN KEY (`provider_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE SET NULL,
  ADD CONSTRAINT `jobs_service_id_foreign` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `ledger`
--
ALTER TABLE `ledger`
  ADD CONSTRAINT `ledger_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `otps`
--
ALTER TABLE `otps`
  ADD CONSTRAINT `otps_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `payouts`
--
ALTER TABLE `payouts`
  ADD CONSTRAINT `payouts_provider_id_foreign` FOREIGN KEY (`provider_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `refunds`
--
ALTER TABLE `refunds`
  ADD CONSTRAINT `refunds_transaction_id_foreign` FOREIGN KEY (`transaction_id`) REFERENCES `ledger` (`id`) ON DELETE CASCADE ON UPDATE SET NULL,
  ADD CONSTRAINT `refunds_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `role_permissions`
--
ALTER TABLE `role_permissions`
  ADD CONSTRAINT `role_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `role_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `services`
--
ALTER TABLE `services`
  ADD CONSTRAINT `services_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `user_roles`
--
ALTER TABLE `user_roles`
  ADD CONSTRAINT `user_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `user_roles_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
