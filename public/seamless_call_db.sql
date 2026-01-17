-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Jan 17, 2026 at 02:15 PM
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
(1, 31, 'service payment', 30000.00, 'Job well done', 'qwe23533reeded', '2026-01-15 23:17:19');

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
(12, '2026-01-16-010001', 'App\\Database\\Migrations\\CreateServicesTable', 'default', 'App', 1768523764, 9);

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
(148, 34, '$2y$10$fLGxNMODDlthVgcul3gvo.xZtomET9A2PmEKvDaAXyL/jOjRo1q5e', 'whatsapp', '2026-01-17 13:43:09', '2026-01-17 12:38:09', '2026-01-17 13:39:10', '2026-01-17 13:39:10', '10.185.59.59', 'login');

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

INSERT INTO `users` (`id`, `name`, `email`, `phone`, `password`, `role`, `kyc_status`, `created_at`, `updated_at`, `status`, `is_provider`, `provider_status`, `approved_by`, `approved_at`, `company_name`, `is_company`, `location`, `services`, `provider_applied_at`, `google_id`) VALUES
(31, 'Sadiya Hassan', 'seamlesscallservices@gmail.com', '+2348012345678', '$2y$10$zgn3k397wtXhx601lwmd/u5UtMCuoIxMcX/CmatO0XWuzpq9OOx/q', 'Customer', 'Pending', '2026-01-07 12:43:24', '2026-01-14 07:07:47', 'active', 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL),
(33, 'Provider', 'prov@gmail.com', '+2348088888888', '$2y$10$c6A/P9..w81BpCB1e/G75upaJhlpkvnheXZnP1K9YkqzWCmutu40a', 'Provider', 'Pending', '2026-01-07 23:08:15', '2026-01-14 08:39:58', 'active', 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL),
(34, 'IDRIS BELLO', 'idrisalibello@gmail.com', '+2348036967483', '$2y$10$iA4amEDQUAW7Z6LWf5bAJuwPxPm16is/lJPXidVfpbmcBHFslPqPq', 'Admin', 'Pending', '2026-01-10 12:44:34', '2026-01-11 12:51:46', 'active', 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL),
(35, 'Khadijah Ismail', 'luvlykhad@gmail.com', '+2348064684365', '$2y$10$MLdtvvhnEVk6KxoqNvYGte559X/U/lAFAlXIiTIOem9E7dj1AFuAq', 'Provider', 'Pending', '2026-01-11 11:51:20', '2026-01-15 18:29:49', 'active', 1, 'approved', 34, '2026-01-15 19:29:49', 'Home Services', 0, 'Kaduna', NULL, '2026-01-15 14:49:56', NULL),
(36, 'Sumaya Ayuba', 'seamlesscallapp1@gmail.com', '+2348022222222', '$2y$10$2hEmC1qb/L2cp6mxaxXL1OtnmwAS5anZj86s.dAVovpQiREYsuzQe', 'Customer', 'Pending', '2026-01-12 10:20:35', '2026-01-15 20:24:28', 'active', 0, 'rejected', 34, '2026-01-15 19:30:32', '', 0, 'Kaduna', NULL, '2026-01-15 18:17:50', NULL),
(37, 'Sumaya Ayuba', 'seamlesscallap@gmail.com', '+2348022222334', '$2y$10$2hEmC1qb/L2cp6mxaxXL1OtnmwAS5anZj86s.dAVovpQiREYsuzQe', 'Provider', 'Pending', '2026-01-12 10:20:35', '2026-01-12 10:20:35', 'active', 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL),
(38, 'Ismail Ali Bello', 'seammlesscallapp1@gmail.com', '+2348044444444', '$2y$10$zgn3k397wtXhx601lwmd/u5UtMCuoIxMcX/CmatO0XWuzpq9OOx/q', 'Admin', 'Pending', '2026-01-15 19:27:57', '2026-01-15 20:43:54', 'active', 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL),
(40, 'Aisha Abdullahi', 'seamlesscallapp@gmail.com', '+2348077777777', '$2y$10$O7b6KH5qYQsAl0sa/Eqv.ed7gXuuWQezVX86Xed001xUEgO3/ZQm.', 'Admin', 'Pending', '2026-01-15 19:48:09', '2026-01-15 19:48:09', 'active', 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL);

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
-- Indexes for table `refunds`
--
ALTER TABLE `refunds`
  ADD PRIMARY KEY (`id`),
  ADD KEY `refunds_user_id_foreign` (`user_id`),
  ADD KEY `refunds_transaction_id_foreign` (`transaction_id`);

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
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `earnings`
--
ALTER TABLE `earnings`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ledger`
--
ALTER TABLE `ledger`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `otps`
--
ALTER TABLE `otps`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=149;

--
-- AUTO_INCREMENT for table `payouts`
--
ALTER TABLE `payouts`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `refunds`
--
ALTER TABLE `refunds`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `services`
--
ALTER TABLE `services`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

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
-- Constraints for table `services`
--
ALTER TABLE `services`
  ADD CONSTRAINT `services_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
