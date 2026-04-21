-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Oct 16, 2025 at 04:28 AM
-- Server version: 10.6.18-MariaDB
-- PHP Version: 8.1.28

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `menswebg_smartcast`
--

-- --------------------------------------------------------

--
-- Table structure for table `audit_logs`
--

CREATE TABLE `audit_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(255) NOT NULL,
  `details` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `audit_logs`
--

INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `details`, `ip_address`, `created_at`) VALUES
(196, NULL, 'login_failed', '{\"email\":\"ekowmee@gmail.com\",\"ip\":\"::1\"}', '::1', '2025-10-10 20:15:59'),
(197, NULL, 'user_registered', '{\"tenant_id\":\"20\",\"plan_id\":4,\"plan_name\":\"Enterprise\"}', '::1', '2025-10-10 20:17:31'),
(198, 3, 'tenant_rejected', '{\"tenant_id\":\"21\",\"tenant_name\":\"Pending Organization 202330\",\"tenant_email\":\"pending202330@example.com\",\"reason\":\"suspicious_activity: Suspect\"}', '::1', '2025-10-10 20:24:23'),
(199, 3, 'tenant_rejected', '{\"tenant_id\":\"20\",\"tenant_name\":\"Hope For All\",\"tenant_email\":\"ekowmee@gmail.com\",\"reason\":\"business_not_eligible: Paaa\"}', '::1', '2025-10-10 20:25:56'),
(200, 3, 'tenant_approved', '{\"tenant_id\":\"19\",\"tenant_name\":\"Pending Organization 201340\",\"tenant_email\":\"pending201340@example.com\"}', '::1', '2025-10-10 20:26:44'),
(201, 3, 'logout', '[]', '::1', '2025-10-10 20:27:40'),
(202, NULL, 'login_failed', '{\"email\":\"ekowme@gmail.comm\",\"ip\":\"::1\"}', '::1', '2025-10-10 20:27:43'),
(203, NULL, 'login', '{\"email\":\"ekowme@gmail.comm\",\"success\":true}', '::1', '2025-10-10 20:28:05'),
(204, 3, 'login', '{\"email\":\"ekowme@gmail.com\",\"success\":true}', '::1', '2025-10-10 20:31:21'),
(205, NULL, 'login_failed', '{\"email\":\"ekowmee@gmail.com\",\"ip\":\"::1\"}', '::1', '2025-10-10 20:31:30'),
(206, 17, 'user_registered', '{\"tenant_id\":\"22\",\"plan_id\":1,\"plan_name\":\"Free Starter\"}', '::1', '2025-10-10 20:31:58'),
(207, 3, 'tenant_approved', '{\"tenant_id\":\"22\",\"tenant_name\":\"Hope For All\",\"tenant_email\":\"ekowmee@gmail.com\"}', '::1', '2025-10-10 20:32:28'),
(208, 17, 'login', '{\"email\":\"ekowmee@gmail.com\",\"success\":true}', '::1', '2025-10-10 20:32:41'),
(209, NULL, 'vote_cast', '{\"transaction_id\":53,\"event_id\":38,\"contestant_id\":85,\"amount\":\"1.00\"}', '::1', '2025-10-10 20:51:10'),
(210, NULL, 'vote_cast', '{\"transaction_id\":54,\"event_id\":38,\"contestant_id\":85,\"amount\":\"1.00\"}', '::1', '2025-10-10 21:27:20'),
(211, NULL, 'vote_cast', '{\"transaction_id\":56,\"event_id\":38,\"contestant_id\":79,\"amount\":\"1.00\"}', '::1', '2025-10-10 21:38:07'),
(212, NULL, 'vote_cast', '{\"transaction_id\":57,\"event_id\":38,\"contestant_id\":79,\"amount\":\"1.00\"}', '::1', '2025-10-10 21:44:20'),
(213, NULL, 'vote_cast', '{\"transaction_id\":58,\"event_id\":38,\"contestant_id\":78,\"amount\":\"100.00\"}', '::1', '2025-10-10 21:50:48'),
(214, NULL, 'vote_cast', '{\"transaction_id\":59,\"event_id\":38,\"contestant_id\":76,\"amount\":\"1000.00\"}', '::1', '2025-10-10 22:17:48'),
(215, NULL, 'vote_cast', '{\"transaction_id\":60,\"event_id\":38,\"contestant_id\":76,\"amount\":\"1.00\"}', '::1', '2025-10-10 22:36:22'),
(216, 3, 'logout', '[]', '::1', '2025-10-10 22:43:28'),
(217, 17, 'login', '{\"email\":\"ekowmee@gmail.com\",\"success\":true}', '::1', '2025-10-10 22:43:35'),
(218, 17, 'logout', '[]', '::1', '2025-10-10 22:44:45'),
(219, 3, 'login', '{\"email\":\"ekowme@gmail.com\",\"success\":true}', '::1', '2025-10-10 22:44:48'),
(220, NULL, 'vote_cast', '{\"transaction_id\":62,\"event_id\":38,\"contestant_id\":76,\"amount\":\"1.00\"}', '::1', '2025-10-10 22:52:15'),
(221, NULL, 'vote_cast', '{\"transaction_id\":66,\"event_id\":38,\"contestant_id\":76,\"amount\":\"1.00\"}', '::1', '2025-10-10 23:38:21'),
(222, NULL, 'vote_cast', '{\"transaction_id\":67,\"event_id\":38,\"contestant_id\":85,\"amount\":\"8.00\"}', '::1', '2025-10-10 23:41:45'),
(223, 3, 'plan_updated', '{\"plan_id\":\"5\",\"plan_name\":\"Pro\",\"changes_applied_to_subscribers\":true}', '::1', '2025-10-11 00:01:44'),
(224, NULL, 'vote_cast', '{\"transaction_id\":68,\"event_id\":38,\"contestant_id\":86,\"amount\":\"1.00\"}', '::1', '2025-10-11 00:14:21'),
(225, NULL, 'vote_cast', '{\"transaction_id\":69,\"event_id\":38,\"contestant_id\":79,\"amount\":\"1.00\"}', '::1', '2025-10-11 00:16:53'),
(226, NULL, 'vote_cast', '{\"transaction_id\":68,\"event_id\":38,\"contestant_id\":86,\"amount\":\"1.00\"}', '::1', '2025-10-11 00:21:27'),
(227, NULL, 'vote_cast', '{\"transaction_id\":68,\"event_id\":38,\"contestant_id\":86,\"amount\":\"1.00\"}', '::1', '2025-10-11 00:21:48'),
(228, NULL, 'vote_cast', '{\"transaction_id\":68,\"event_id\":38,\"contestant_id\":86,\"amount\":\"1.00\"}', '::1', '2025-10-11 00:22:13'),
(229, NULL, 'vote_cast', '{\"transaction_id\":68,\"event_id\":38,\"contestant_id\":86,\"amount\":\"1.00\"}', '::1', '2025-10-11 00:22:48'),
(230, NULL, 'vote_cast', '{\"transaction_id\":71,\"event_id\":38,\"contestant_id\":76,\"amount\":\"2.00\"}', '::1', '2025-10-11 00:28:08'),
(231, NULL, 'vote_cast', '{\"transaction_id\":72,\"event_id\":38,\"contestant_id\":85,\"amount\":\"1.00\"}', '::1', '2025-10-11 00:30:27'),
(232, NULL, 'vote_cast', '{\"transaction_id\":73,\"event_id\":38,\"contestant_id\":79,\"amount\":\"1.00\"}', '::1', '2025-10-11 00:35:46'),
(233, 3, 'logout', '[]', '::1', '2025-10-12 10:33:19'),
(234, 3, 'login', '{\"email\":\"ekowme@gmail.com\",\"success\":true}', '::1', '2025-10-12 10:33:21'),
(235, 3, 'logout', '[]', '::1', '2025-10-12 10:33:25'),
(236, NULL, 'login_failed', '{\"email\":\"ekowmee@gmail.com\",\"ip\":\"::1\"}', '::1', '2025-10-12 10:33:30'),
(237, NULL, 'login_failed', '{\"email\":\"ekowmee@gmail.com\",\"ip\":\"::1\"}', '::1', '2025-10-12 10:33:40'),
(238, 17, 'login', '{\"email\":\"ekowmee@gmail.com\",\"success\":true}', '::1', '2025-10-12 10:33:47'),
(239, NULL, 'vote_cast', '{\"transaction_id\":79,\"event_id\":38,\"contestant_id\":76,\"amount\":\"4.50\"}', '::1', '2025-10-12 11:32:28'),
(240, NULL, 'vote_cast', '{\"transaction_id\":80,\"event_id\":38,\"contestant_id\":76,\"amount\":\"8.00\"}', '::1', '2025-10-12 11:38:38'),
(241, NULL, 'vote_cast', '{\"transaction_id\":81,\"event_id\":38,\"contestant_id\":76,\"amount\":\"8.00\"}', '::1', '2025-10-12 11:39:56'),
(242, NULL, 'vote_cast', '{\"transaction_id\":82,\"event_id\":39,\"contestant_id\":88,\"amount\":\"17.50\"}', '::1', '2025-10-12 12:09:34'),
(243, NULL, 'vote_cast', '{\"transaction_id\":83,\"event_id\":39,\"contestant_id\":89,\"amount\":\"8.00\"}', '::1', '2025-10-12 12:52:44'),
(244, NULL, 'vote_cast', '{\"transaction_id\":84,\"event_id\":39,\"contestant_id\":89,\"amount\":\"10.00\"}', '::1', '2025-10-12 13:01:16'),
(245, NULL, 'vote_cast', '{\"transaction_id\":85,\"event_id\":39,\"contestant_id\":88,\"amount\":\"1.00\"}', '::1', '2025-10-12 13:21:03'),
(246, NULL, 'vote_cast', '{\"transaction_id\":86,\"event_id\":38,\"contestant_id\":86,\"amount\":\"1.00\"}', '::1', '2025-10-12 13:25:21'),
(247, NULL, 'vote_cast', '{\"transaction_id\":87,\"event_id\":39,\"contestant_id\":89,\"amount\":\"26.00\"}', '::1', '2025-10-12 13:27:22'),
(248, NULL, 'vote_cast', '{\"transaction_id\":89,\"event_id\":38,\"contestant_id\":76,\"amount\":\"1.00\"}', '::1', '2025-10-12 13:47:01'),
(249, NULL, 'vote_cast', '{\"transaction_id\":90,\"event_id\":39,\"contestant_id\":88,\"amount\":\"1.00\"}', '::1', '2025-10-12 13:56:59'),
(250, NULL, 'vote_cast', '{\"transaction_id\":92,\"event_id\":39,\"contestant_id\":88,\"amount\":\"1.00\"}', '::1', '2025-10-12 13:59:57'),
(251, NULL, 'vote_cast', '{\"transaction_id\":93,\"event_id\":39,\"contestant_id\":88,\"amount\":\"1.00\"}', '::1', '2025-10-12 14:01:17'),
(252, NULL, 'vote_cast', '{\"transaction_id\":94,\"event_id\":39,\"contestant_id\":88,\"amount\":\"1.00\"}', '::1', '2025-10-12 14:05:12'),
(253, NULL, 'vote_cast', '{\"transaction_id\":95,\"event_id\":38,\"contestant_id\":78,\"amount\":\"17.50\"}', '::1', '2025-10-12 14:06:13'),
(254, NULL, 'vote_cast', '{\"transaction_id\":96,\"event_id\":38,\"contestant_id\":76,\"amount\":\"9.00\"}', '::1', '2025-10-12 14:11:40'),
(255, 3, 'login', '{\"email\":\"ekowme@gmail.com\",\"success\":true}', '::1', '2025-10-12 14:27:24'),
(256, 3, 'plan_updated', '{\"plan_id\":\"5\",\"plan_name\":\"Free\",\"changes_applied_to_subscribers\":true}', '::1', '2025-10-12 14:54:07'),
(257, 3, 'plan_updated', '{\"plan_id\":\"8\",\"plan_name\":\"Diamond Plan\",\"changes_applied_to_subscribers\":true}', '::1', '2025-10-12 15:01:08'),
(258, 3, 'plan_updated', '{\"plan_id\":\"6\",\"plan_name\":\"Silver Plan\",\"changes_applied_to_subscribers\":true}', '::1', '2025-10-12 15:08:07'),
(259, 3, 'plan_updated', '{\"plan_id\":\"6\",\"plan_name\":\"Silver Plan\",\"changes_applied_to_subscribers\":true}', '::1', '2025-10-12 15:08:50'),
(260, 3, 'login', '{\"email\":\"ekowme@gmail.com\",\"success\":true}', '::1', '2025-10-13 05:14:56'),
(261, 3, 'plan_updated', '{\"plan_id\":\"6\",\"plan_name\":\"Silver Plan\",\"changes_applied_to_subscribers\":true}', '::1', '2025-10-13 05:48:41'),
(262, NULL, 'vote_cast', '{\"transaction_id\":97,\"event_id\":39,\"contestant_id\":89,\"amount\":\"5.00\"}', '::1', '2025-10-13 06:33:27'),
(263, NULL, 'vote_cast', '{\"transaction_id\":98,\"event_id\":39,\"contestant_id\":89,\"amount\":\"1.00\"}', '::1', '2025-10-13 06:51:58'),
(264, 3, 'logout', '[]', '::1', '2025-10-13 09:17:16'),
(265, 3, 'login', '{\"email\":\"ekowme@gmail.com\",\"success\":true}', '::1', '2025-10-13 09:19:27'),
(266, NULL, 'vote_cast', '{\"transaction_id\":100,\"event_id\":41,\"contestant_id\":207,\"amount\":\"7.00\"}', '::1', '2025-10-13 10:15:18'),
(267, NULL, 'login_failed', '{\"email\":\"ekowme@gmail.com\",\"ip\":\"154.161.157.28\"}', '154.161.157.28', '2025-10-13 15:35:11'),
(268, 3, 'login', '{\"email\":\"ekowme@gmail.com\",\"success\":true}', '154.161.157.28', '2025-10-13 15:35:16'),
(269, NULL, 'login_failed', '{\"email\":\"ekowme@gmail.com\",\"ip\":\"154.161.171.75\"}', '154.161.171.75', '2025-10-13 15:47:39'),
(270, NULL, 'login_failed', '{\"email\":\"ekowme@gmail.com\",\"ip\":\"154.161.171.75\"}', '154.161.171.75', '2025-10-13 15:48:10'),
(271, NULL, 'login_failed', '{\"email\":\"ekowme@gmail.com\",\"ip\":\"154.161.171.75\"}', '154.161.171.75', '2025-10-13 15:49:52'),
(272, NULL, 'vote_cast', '{\"transaction_id\":101,\"event_id\":41,\"contestant_id\":207,\"amount\":\"4000.00\"}', '154.161.171.75', '2025-10-13 15:54:48'),
(273, NULL, 'vote_cast', '{\"transaction_id\":102,\"event_id\":41,\"contestant_id\":208,\"amount\":\"10000.00\"}', '154.161.171.75', '2025-10-13 15:56:43'),
(274, 3, 'login', '{\"email\":\"ekowme@gmail.com\",\"success\":true}', '154.161.171.75', '2025-10-13 16:03:24'),
(275, NULL, 'login_failed', '{\"email\":\"ekowme@gmail.com\",\"ip\":\"154.160.27.52\"}', '154.160.27.52', '2025-10-13 17:00:29'),
(276, NULL, 'login_failed', '{\"email\":\"ekowme@gmail.com\",\"ip\":\"154.160.27.52\"}', '154.160.27.52', '2025-10-13 17:00:35'),
(277, NULL, 'login_failed', '{\"email\":\"ekowme@gmail.com\",\"ip\":\"154.160.27.52\"}', '154.160.27.52', '2025-10-13 17:00:48'),
(278, NULL, 'vote_cast', '{\"transaction_id\":103,\"event_id\":41,\"contestant_id\":207,\"amount\":\"1.00\"}', '154.160.27.52', '2025-10-13 17:01:58'),
(279, NULL, 'vote_cast', '{\"transaction_id\":104,\"event_id\":41,\"contestant_id\":208,\"amount\":\"1.00\"}', '154.160.27.52', '2025-10-13 17:03:44'),
(280, NULL, 'login_failed', '{\"email\":\"ekowme@gmail.com\",\"ip\":\"154.160.4.234\"}', '154.160.4.234', '2025-10-13 21:51:47'),
(281, NULL, 'login_failed', '{\"email\":\"ekowme@gmail.com\",\"ip\":\"154.160.4.234\"}', '154.160.4.234', '2025-10-13 21:52:00'),
(282, 3, 'login', '{\"email\":\"ekowme@gmail.com\",\"success\":true}', '154.160.4.234', '2025-10-13 21:52:25'),
(283, NULL, 'login_failed', '{\"email\":\"ekowme@gmail.com\",\"ip\":\"154.161.145.215\"}', '154.161.145.215', '2025-10-14 03:57:32'),
(284, NULL, 'login_failed', '{\"email\":\"vincent5@gmail.com\",\"ip\":\"154.161.145.215\"}', '154.161.145.215', '2025-10-14 03:57:53'),
(285, NULL, 'login_failed', '{\"email\":\"vincent5@gmail.com\",\"ip\":\"154.161.145.215\"}', '154.161.145.215', '2025-10-14 03:57:59'),
(286, NULL, 'login_failed', '{\"email\":\"vincent5@gmail.com\",\"ip\":\"154.161.145.215\"}', '154.161.145.215', '2025-10-14 03:58:04'),
(287, NULL, 'login_failed', '{\"email\":\"ekowme@gmail.com\",\"ip\":\"154.161.1.141\"}', '154.161.1.141', '2025-10-14 08:25:39'),
(288, 3, 'login', '{\"email\":\"ekowme@gmail.com\",\"success\":true}', '154.161.1.141', '2025-10-14 08:25:49'),
(289, 17, 'login', '{\"email\":\"ekowmee@gmail.com\",\"success\":true}', '154.160.27.2', '2025-10-14 11:19:00'),
(290, 3, 'login', '{\"email\":\"ekowme@gmail.com\",\"success\":true}', '154.160.27.2', '2025-10-14 11:23:05'),
(291, 17, 'login', '{\"email\":\"ekowmee@gmail.com\",\"success\":true}', '154.160.27.2', '2025-10-14 16:35:58'),
(292, NULL, 'vote_completion', '{\"transaction_id\":105,\"vote_id\":85,\"event_id\":43,\"contestant_id\":215,\"amount\":\"3.00\",\"vote_count\":null,\"sms_sent\":false,\"sms_error\":\"No active SMS gateway configured\"}', '154.161.43.144', '2025-10-14 21:52:17'),
(293, NULL, 'vote_cast', '{\"transaction_id\":105,\"event_id\":43,\"contestant_id\":215,\"amount\":\"3.00\"}', '154.161.43.144', '2025-10-14 21:52:17'),
(294, NULL, 'vote_completion', '{\"transaction_id\":106,\"vote_id\":86,\"event_id\":43,\"contestant_id\":215,\"amount\":\"4.00\",\"vote_count\":null,\"sms_sent\":true,\"sms_error\":null}', '154.161.43.144', '2025-10-14 21:54:41'),
(295, NULL, 'vote_cast', '{\"transaction_id\":106,\"event_id\":43,\"contestant_id\":215,\"amount\":\"4.00\"}', '154.161.43.144', '2025-10-14 21:54:41'),
(296, NULL, 'login_failed', '{\"email\":\"ekowme@gmail.com\",\"ip\":\"154.160.27.2\"}', '154.160.27.2', '2025-10-14 21:59:12'),
(297, 3, 'login', '{\"email\":\"ekowme@gmail.com\",\"success\":true}', '154.160.27.2', '2025-10-14 21:59:17'),
(298, 3, 'plan_updated', '{\"plan_id\":\"5\",\"plan_name\":\"Free\",\"changes_applied_to_subscribers\":true}', '154.160.27.2', '2025-10-14 22:00:08'),
(299, NULL, 'login_failed', '{\"email\":\"ekowme@gmail.com\",\"ip\":\"154.161.43.144\"}', '154.161.43.144', '2025-10-14 22:03:56'),
(300, 3, 'login', '{\"email\":\"ekowme@gmail.com\",\"success\":true}', '154.161.43.144', '2025-10-14 22:04:08'),
(301, 3, 'logout', '[]', '154.161.43.144', '2025-10-14 22:05:19'),
(302, 17, 'login', '{\"email\":\"ekowmee@gmail.com\",\"success\":true}', '154.161.43.144', '2025-10-14 22:05:24'),
(303, NULL, 'login_failed', '{\"email\":\"ekowme@gmail.com\",\"ip\":\"154.160.27.2\"}', '154.160.27.2', '2025-10-15 13:58:48'),
(304, 3, 'login', '{\"email\":\"ekowme@gmail.com\",\"success\":true}', '154.160.27.2', '2025-10-15 13:58:52'),
(305, 17, 'login', '{\"email\":\"ekowmee@gmail.com\",\"success\":true}', '154.160.27.2', '2025-10-15 22:55:39'),
(306, NULL, 'vote_completion', '{\"transaction_id\":117,\"vote_id\":87,\"event_id\":43,\"contestant_id\":214,\"amount\":\"1.00\",\"vote_count\":null,\"sms_sent\":true,\"sms_error\":null}', '154.160.27.2', '2025-10-15 23:02:56'),
(307, NULL, 'vote_cast', '{\"transaction_id\":117,\"event_id\":43,\"contestant_id\":214,\"amount\":\"1.00\"}', '154.160.27.2', '2025-10-15 23:02:56'),
(308, NULL, 'vote_completion', '{\"transaction_id\":148,\"vote_id\":98,\"event_id\":43,\"contestant_id\":210,\"amount\":\"1.00\",\"vote_count\":null,\"sms_sent\":true,\"sms_error\":null}', '154.160.27.2', '2025-10-16 00:33:49'),
(309, NULL, 'vote_cast', '{\"transaction_id\":148,\"event_id\":43,\"contestant_id\":210,\"amount\":\"1.00\"}', '154.160.27.2', '2025-10-16 00:33:49'),
(310, NULL, 'vote_completion', '{\"transaction_id\":151,\"vote_id\":100,\"event_id\":43,\"contestant_id\":210,\"amount\":\"1.00\",\"vote_count\":null,\"sms_sent\":true,\"sms_error\":null}', '154.160.27.2', '2025-10-16 00:38:37'),
(311, NULL, 'vote_cast', '{\"transaction_id\":151,\"event_id\":43,\"contestant_id\":210,\"amount\":\"1.00\"}', '154.160.27.2', '2025-10-16 00:38:37'),
(312, NULL, 'vote_completion', '{\"transaction_id\":149,\"vote_id\":102,\"event_id\":43,\"contestant_id\":210,\"amount\":\"1.00\",\"vote_count\":null,\"sms_sent\":true,\"sms_error\":null}', '154.160.27.2', '2025-10-16 00:47:46'),
(313, NULL, 'vote_cast', '{\"transaction_id\":149,\"event_id\":43,\"contestant_id\":210,\"amount\":\"1.00\"}', '154.160.27.2', '2025-10-16 00:47:46'),
(314, NULL, 'login_failed', '{\"email\":\"ekowme@gmail.com\",\"ip\":\"154.161.58.235\"}', '154.161.58.235', '2025-10-16 01:13:43'),
(315, 3, 'login', '{\"email\":\"ekowme@gmail.com\",\"success\":true}', '154.161.58.235', '2025-10-16 01:13:52'),
(316, NULL, 'vote_completion', '{\"transaction_id\":155,\"vote_id\":104,\"event_id\":43,\"contestant_id\":210,\"amount\":\"1.00\",\"vote_count\":null,\"sms_sent\":true,\"sms_error\":null}', '154.160.27.2', '2025-10-16 11:53:56'),
(317, NULL, 'vote_cast', '{\"transaction_id\":155,\"event_id\":43,\"contestant_id\":210,\"amount\":\"1.00\"}', '154.160.27.2', '2025-10-16 11:53:56'),
(318, 17, 'login', '{\"email\":\"ekowmee@gmail.com\",\"success\":true}', '154.160.27.2', '2025-10-16 11:57:44'),
(319, NULL, 'vote_completion', '{\"transaction_id\":157,\"vote_id\":105,\"event_id\":43,\"contestant_id\":210,\"amount\":\"1.00\",\"vote_count\":null,\"sms_sent\":true,\"sms_error\":null}', '154.160.27.2', '2025-10-16 12:11:10'),
(320, NULL, 'vote_cast', '{\"transaction_id\":157,\"event_id\":43,\"contestant_id\":210,\"amount\":\"1.00\"}', '154.160.27.2', '2025-10-16 12:11:10'),
(321, 3, 'login', '{\"email\":\"ekowme@gmail.com\",\"success\":true}', '154.160.27.2', '2025-10-16 12:22:59');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `tenant_id` int(11) NOT NULL DEFAULT 1,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `display_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `contestants`
--

CREATE TABLE `contestants` (
  `id` int(11) NOT NULL,
  `tenant_id` int(11) NOT NULL DEFAULT 1,
  `event_id` int(11) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `contestant_code` varchar(20) DEFAULT NULL,
  `image_url` varchar(500) DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `display_order` int(11) DEFAULT 0,
  `active` tinyint(1) DEFAULT 1,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `contestant_categories`
--

CREATE TABLE `contestant_categories` (
  `id` int(11) NOT NULL,
  `contestant_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `short_code` varchar(10) NOT NULL,
  `display_order` int(11) DEFAULT 0,
  `active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `events`
--

CREATE TABLE `events` (
  `id` int(11) NOT NULL,
  `tenant_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `code` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `featured_image` varchar(500) DEFAULT NULL,
  `start_date` datetime DEFAULT NULL,
  `end_date` datetime DEFAULT NULL,
  `vote_price` decimal(10,2) DEFAULT 0.50 COMMENT 'Price per vote in USD',
  `active` tinyint(1) DEFAULT 1,
  `status` enum('draft','active','suspended','closed','archived') DEFAULT 'draft',
  `visibility` enum('private','public','unlisted') DEFAULT 'private',
  `admin_status` enum('approved','pending','rejected','under_review') DEFAULT 'pending',
  `admin_notes` text DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `suspended_reason` text DEFAULT NULL,
  `suspended_by` int(11) DEFAULT NULL,
  `suspended_at` timestamp NULL DEFAULT NULL,
  `closed_at` timestamp NULL DEFAULT NULL,
  `archived_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `results_visible` tinyint(1) DEFAULT 1 COMMENT 'Controls whether results/leaderboard is visible to public',
  `deactivated_at` timestamp NULL DEFAULT NULL,
  `deactivated_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `event_drafts`
--

CREATE TABLE `event_drafts` (
  `id` int(11) NOT NULL,
  `tenant_id` int(11) NOT NULL,
  `draft_name` varchar(255) NOT NULL,
  `draft_data` text NOT NULL,
  `step` int(11) DEFAULT 1,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `event_status_history`
--

CREATE TABLE `event_status_history` (
  `id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `old_status` varchar(50) DEFAULT NULL,
  `new_status` varchar(50) NOT NULL,
  `old_admin_status` varchar(50) DEFAULT NULL,
  `new_admin_status` varchar(50) NOT NULL,
  `changed_by` int(11) NOT NULL,
  `change_reason` text DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `fee_rules`
--

CREATE TABLE `fee_rules` (
  `id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL COMMENT 'Human-readable name for the fee rule',
  `description` text DEFAULT NULL COMMENT 'Detailed description of when this rule applies',
  `tenant_id` int(11) DEFAULT NULL,
  `event_id` int(11) DEFAULT NULL,
  `rule_type` enum('percentage','fixed','blend') DEFAULT 'percentage',
  `percentage_rate` decimal(5,2) DEFAULT NULL,
  `fixed_amount` decimal(10,2) DEFAULT NULL,
  `min_amount` decimal(10,2) DEFAULT NULL COMMENT 'Minimum fee amount (overrides calculated fee if lower)',
  `max_amount` decimal(10,2) DEFAULT NULL COMMENT 'Maximum fee amount (caps calculated fee if higher)',
  `active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `fee_rules`
--

INSERT INTO `fee_rules` (`id`, `name`, `description`, `tenant_id`, `event_id`, `rule_type`, `percentage_rate`, `fixed_amount`, `min_amount`, `max_amount`, `active`, `created_at`, `updated_at`) VALUES
(9, 'Global PERCENTAGE Rule - 35.00%', 'Global fee rule that applies to all tenants unless they have specific rules', NULL, NULL, 'percentage', 35.00, NULL, NULL, NULL, 1, '2025-10-10 21:26:43', '2025-10-10 22:36:57'),
(10, 'Verified Plan A', 'Tenant-specific fee rule for tenant ID 22', NULL, NULL, 'percentage', 12.00, NULL, NULL, NULL, 1, '2025-10-10 22:16:01', '2025-10-12 15:08:30'),
(11, 'Gold Plan', '', NULL, NULL, 'percentage', 5.00, NULL, NULL, NULL, 1, '2025-10-10 23:19:29', '2025-10-12 14:56:55'),
(12, 'Diamond', '', NULL, NULL, 'percentage', 1.50, NULL, NULL, NULL, 1, '2025-10-12 15:00:48', '2025-10-12 15:00:48');

-- --------------------------------------------------------

--
-- Table structure for table `fraud_events`
--

CREATE TABLE `fraud_events` (
  `id` int(11) NOT NULL,
  `tenant_id` int(11) DEFAULT NULL,
  `event_type` varchar(100) NOT NULL,
  `details` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `leaderboard_cache`
--

CREATE TABLE `leaderboard_cache` (
  `id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `contestant_id` int(11) NOT NULL,
  `category_id` int(10) UNSIGNED DEFAULT NULL,
  `total_votes` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `otp_requests`
--

CREATE TABLE `otp_requests` (
  `id` int(11) NOT NULL,
  `msisdn` varchar(20) NOT NULL,
  `otp` varchar(255) NOT NULL,
  `expires_at` datetime NOT NULL,
  `consumed` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payment_gateways`
--

CREATE TABLE `payment_gateways` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `provider` varchar(50) NOT NULL,
  `config` text NOT NULL COMMENT 'JSON configuration including API keys, URLs, etc.',
  `supported_methods` text NOT NULL COMMENT 'JSON array of supported payment methods',
  `is_active` tinyint(1) NOT NULL DEFAULT 0,
  `is_default` tinyint(1) NOT NULL DEFAULT 0,
  `priority` int(11) NOT NULL DEFAULT 1 COMMENT 'Lower number = higher priority',
  `tenant_id` int(11) DEFAULT NULL COMMENT 'NULL for global gateways',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `payment_gateways`
--

INSERT INTO `payment_gateways` (`id`, `name`, `provider`, `config`, `supported_methods`, `is_active`, `is_default`, `priority`, `tenant_id`, `created_at`, `updated_at`) VALUES
(1, 'Paystack', 'paystack', '{\"public_key\":\"pk_live_8ae357bd04d06ce84d9f29d6ed4fb4936a8f3866\",\"secret_key\":\"sk_live_fe4a5e50d0bd6f168dc3c024ebf8aac63e08b0be\",\"webhook_secret\":\"\",\"base_url\":\"https:\\/\\/api.paystack.co\",\"currency\":\"GHS\"}', '[\"mobile_money\", \"card\", \"bank_transfer\"]', 1, 1, 1, NULL, '2025-10-14 19:10:37', '2025-10-15 02:29:25'),
(2, 'Hubtel', 'hubtel', '{\"client_id\": \"\", \"client_secret\": \"\", \"merchant_account\": \"\", \"base_url\": \"https://api.hubtel.com\", \"currency\": \"GHS\"}', '[\"mobile_money\", \"card\"]', 0, 0, 2, NULL, '2025-10-14 19:10:37', '2025-10-14 19:10:37');

-- --------------------------------------------------------

--
-- Table structure for table `payment_transactions`
--

CREATE TABLE `payment_transactions` (
  `id` int(11) NOT NULL,
  `reference` varchar(100) NOT NULL COMMENT 'Internal payment reference',
  `gateway_id` int(11) NOT NULL,
  `gateway_reference` varchar(255) DEFAULT NULL COMMENT 'Gateway transaction ID',
  `amount` decimal(10,2) NOT NULL,
  `currency` varchar(3) NOT NULL DEFAULT 'GHS',
  `payment_method` enum('mobile_money','card','bank_transfer','other') NOT NULL,
  `phone_number` varchar(20) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `customer_name` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `metadata` text DEFAULT NULL COMMENT 'JSON metadata',
  `status` enum('pending','success','failed','cancelled','expired') NOT NULL DEFAULT 'pending',
  `gateway_response` text DEFAULT NULL COMMENT 'JSON response from gateway',
  `webhook_verified` tinyint(1) NOT NULL DEFAULT 0,
  `tenant_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `related_type` enum('vote','subscription','bundle','other') DEFAULT NULL,
  `related_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payment_webhook_logs`
--

CREATE TABLE `payment_webhook_logs` (
  `id` int(11) NOT NULL,
  `gateway_provider` varchar(50) NOT NULL,
  `event_type` varchar(100) DEFAULT NULL,
  `payload` text NOT NULL,
  `signature` varchar(255) DEFAULT NULL,
  `processed` tinyint(1) NOT NULL DEFAULT 0,
  `processing_result` text DEFAULT NULL,
  `transaction_reference` varchar(100) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(500) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payouts`
--

CREATE TABLE `payouts` (
  `id` int(11) NOT NULL,
  `tenant_id` int(11) NOT NULL,
  `initiated_by` int(11) DEFAULT NULL,
  `approved_by` int(11) DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `payout_id` varchar(50) DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `processing_fee` decimal(10,2) DEFAULT 0.00,
  `net_amount` decimal(10,2) DEFAULT 0.00,
  `payout_method` enum('bank_transfer','mobile_money','paypal') DEFAULT 'bank_transfer',
  `payout_type` enum('manual','automatic','instant') DEFAULT 'manual',
  `payout_method_id` int(11) DEFAULT NULL,
  `recipient_details` text NOT NULL,
  `status` enum('pending','approved','processing','paid','failed','rejected','cancelled','queued','success') DEFAULT NULL,
  `provider_reference` varchar(100) DEFAULT NULL,
  `failure_reason` text DEFAULT NULL,
  `processed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payout_methods`
--

CREATE TABLE `payout_methods` (
  `id` int(11) NOT NULL,
  `tenant_id` int(11) NOT NULL,
  `method_type` enum('bank_transfer','mobile_money','paypal','stripe') NOT NULL,
  `method_name` varchar(100) NOT NULL,
  `account_details` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`account_details`)),
  `is_default` tinyint(1) DEFAULT 0,
  `is_verified` tinyint(1) DEFAULT 0,
  `verification_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`verification_data`)),
  `active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payout_schedules`
--

CREATE TABLE `payout_schedules` (
  `id` int(11) NOT NULL,
  `tenant_id` int(11) NOT NULL,
  `frequency` enum('manual','daily','weekly','monthly') DEFAULT 'monthly',
  `minimum_amount` decimal(10,2) DEFAULT 10.00,
  `auto_payout_enabled` tinyint(1) DEFAULT 0,
  `instant_payout_threshold` decimal(10,2) DEFAULT 1000.00,
  `next_payout_date` date DEFAULT NULL,
  `payout_day` int(2) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payout_schedules`
--

INSERT INTO `payout_schedules` (`id`, `tenant_id`, `frequency`, `minimum_amount`, `auto_payout_enabled`, `instant_payout_threshold`, `next_payout_date`, `payout_day`, `created_at`, `updated_at`) VALUES
(4, 4, 'monthly', 10.00, 0, 1000.00, NULL, 1, '2025-10-10 13:13:17', '2025-10-10 13:13:17'),
(5, 5, 'monthly', 10.00, 0, 1000.00, NULL, 1, '2025-10-10 13:13:17', '2025-10-10 13:13:17'),
(6, 22, 'monthly', 10.00, 0, 1000.00, '2025-11-01', 1, '2025-10-12 15:10:08', '2025-10-12 15:10:08');

-- --------------------------------------------------------

--
-- Table structure for table `plan_features`
--

CREATE TABLE `plan_features` (
  `id` int(11) NOT NULL,
  `plan_id` int(11) NOT NULL,
  `feature_key` varchar(100) NOT NULL,
  `feature_name` varchar(255) NOT NULL,
  `feature_value` varchar(255) DEFAULT NULL,
  `is_boolean` tinyint(1) DEFAULT 0 COMMENT '1 if feature is yes/no, 0 if has value',
  `sort_order` int(3) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `plan_features`
--

INSERT INTO `plan_features` (`id`, `plan_id`, `feature_key`, `feature_name`, `feature_value`, `is_boolean`, `sort_order`) VALUES
(1, 1, 'events', 'Events', '1', 0, 1),
(2, 1, 'contestants', 'Contestants per Event', '10', 0, 2),
(3, 1, 'votes', 'Votes per Event', '1,000', 0, 3),
(4, 1, 'storage', 'Storage', '100 MB', 0, 4),
(5, 1, 'custom_branding', 'Custom Branding', NULL, 1, 5),
(6, 1, 'analytics', 'Analytics', 'Basic', 0, 6),
(7, 1, 'support', 'Support', 'Community', 0, 7),
(8, 2, 'events', 'Events', '5', 0, 1),
(9, 2, 'contestants', 'Contestants per Event', '50', 0, 2),
(10, 2, 'votes', 'Votes per Event', '10,000', 0, 3),
(11, 2, 'storage', 'Storage', '1 GB', 0, 4),
(12, 2, 'custom_branding', 'Custom Branding', '1', 1, 5),
(13, 2, 'analytics', 'Analytics', 'Standard', 0, 6),
(14, 2, 'support', 'Support', 'Email', 0, 7),
(15, 2, 'api_access', 'API Access', NULL, 1, 8),
(16, 3, 'events', 'Events', 'Unlimited', 0, 1),
(17, 3, 'contestants', 'Contestants per Event', 'Unlimited', 0, 2),
(18, 3, 'votes', 'Votes per Event', 'Unlimited', 0, 3),
(19, 3, 'storage', 'Storage', '5 GB', 0, 4),
(20, 3, 'custom_branding', 'Custom Branding', '1', 1, 5),
(21, 3, 'analytics', 'Analytics', 'Advanced', 0, 6),
(22, 3, 'support', 'Support', 'Priority', 0, 7),
(23, 3, 'api_access', 'API Access', '1', 1, 8),
(24, 3, 'webhooks', 'Webhooks', '1', 1, 9),
(25, 4, 'events', 'Events', 'Unlimited', 0, 1),
(26, 4, 'contestants', 'Contestants per Event', 'Unlimited', 0, 2),
(27, 4, 'votes', 'Votes per Event', 'Unlimited', 0, 3),
(28, 4, 'storage', 'Storage', '20 GB', 0, 4),
(29, 4, 'custom_branding', 'Custom Branding', '1', 1, 5),
(30, 4, 'analytics', 'Analytics', 'Premium', 0, 6),
(31, 4, 'support', 'Support', 'Dedicated', 0, 7),
(32, 4, 'api_access', 'API Access', '1', 1, 8),
(33, 4, 'webhooks', 'Webhooks', '1', 1, 9),
(34, 4, 'white_label', 'White Label', '1', 1, 10);

-- --------------------------------------------------------

--
-- Table structure for table `platform_revenue`
--

CREATE TABLE `platform_revenue` (
  `id` int(11) NOT NULL,
  `transaction_id` int(11) DEFAULT NULL,
  `revenue_type` enum('platform_fee','processing_fee','subscription_fee','other') DEFAULT 'platform_fee',
  `amount` decimal(10,2) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`metadata`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `rate_limits`
--

CREATE TABLE `rate_limits` (
  `id` int(11) NOT NULL,
  `key` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `rate_limits`
--

INSERT INTO `rate_limits` (`id`, `key`, `created_at`) VALUES
(26, 'login_attempt_::1', '2025-10-08 17:25:58');

-- --------------------------------------------------------

--
-- Table structure for table `revenue_shares`
--

CREATE TABLE `revenue_shares` (
  `id` int(11) NOT NULL,
  `transaction_id` int(11) NOT NULL,
  `tenant_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `revenue_type` enum('platform_fee','tenant_share','referrer_commission','processing_fee') DEFAULT 'platform_fee',
  `percentage_applied` decimal(5,2) DEFAULT NULL,
  `original_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `description` varchar(255) DEFAULT NULL,
  `fee_rule_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `revenue_transactions`
--

CREATE TABLE `revenue_transactions` (
  `id` int(11) NOT NULL,
  `transaction_id` int(11) NOT NULL,
  `tenant_id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `gross_amount` decimal(10,2) NOT NULL,
  `platform_fee` decimal(10,2) NOT NULL,
  `processing_fee` decimal(10,2) DEFAULT 0.00,
  `referrer_commission` decimal(10,2) DEFAULT 0.00,
  `net_tenant_amount` decimal(10,2) NOT NULL,
  `fee_rule_snapshot` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`fee_rule_snapshot`)),
  `distribution_status` enum('pending','completed','failed') DEFAULT 'completed',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `risk_blocks`
--

CREATE TABLE `risk_blocks` (
  `id` int(11) NOT NULL,
  `tenant_id` int(11) DEFAULT NULL,
  `block_type` enum('ip','msisdn','device') NOT NULL,
  `block_value` varchar(255) NOT NULL,
  `reason` text DEFAULT NULL,
  `active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sms_gateways`
--

CREATE TABLE `sms_gateways` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `type` enum('mnotify','hubtel') NOT NULL,
  `api_key` varchar(255) NOT NULL,
  `client_id` varchar(255) DEFAULT NULL,
  `client_secret` varchar(255) DEFAULT NULL,
  `sender_id` varchar(50) NOT NULL,
  `base_url` varchar(255) DEFAULT NULL,
  `test_phone` varchar(20) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `priority` int(11) DEFAULT 1,
  `config` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`config`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sms_gateways`
--

INSERT INTO `sms_gateways` (`id`, `name`, `type`, `api_key`, `client_id`, `client_secret`, `sender_id`, `base_url`, `test_phone`, `is_active`, `priority`, `config`, `created_at`, `updated_at`) VALUES
(1, 'mNotify', 'mnotify', 'sOc85q3BJOCmGfuaqAxg4SWYg', '', '', 'Mensweb', NULL, '233545644749', 1, 1, NULL, '2025-10-14 18:21:19', '2025-10-14 19:19:27');

-- --------------------------------------------------------

--
-- Table structure for table `sms_logs`
--

CREATE TABLE `sms_logs` (
  `id` int(11) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `message` text NOT NULL,
  `gateway_id` int(11) DEFAULT NULL,
  `gateway_type` varchar(20) NOT NULL,
  `status` enum('pending','sent','failed','delivered','undelivered') DEFAULT 'pending',
  `response` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`response`)),
  `vote_id` int(11) DEFAULT NULL,
  `transaction_id` int(11) DEFAULT NULL,
  `retry_count` int(11) DEFAULT 0,
  `last_retry_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sms_logs`
--

INSERT INTO `sms_logs` (`id`, `phone`, `message`, `gateway_id`, `gateway_type`, `status`, `response`, `vote_id`, `transaction_id`, `retry_count`, `last_retry_at`, `created_at`) VALUES
(1, '0545644749', 'Message generation failed', NULL, 'unknown', 'failed', '{\"error\":\"No active SMS gateway configured\"}', NULL, NULL, 0, NULL, '2025-10-14 21:52:17'),
(2, '0545644749', 'Thank you for voting!\n\nNominee: MANSAH\nEvent: MISS BREMAN ODWIRA\nCategory: CONTESTANTS\nVotes: 4\nAmount: GH₵4.00\nReceipt: XR2APNC6\n\nThank you for your participation!', 1, 'mnotify', 'sent', '{\"success\":true,\"http_code\":200,\"response\":{\"status\":\"success\",\"code\":\"2000\",\"message\":\"messages sent successfully\",\"summary\":{\"_id\":\"7E80E387-2CD6-4F17-BA5A-C5BD68725A9F\",\"message_id\":\"20251014233545644749V2\",\"type\":\"API QUICK SMS\",\"total_sent\":1,\"contacts\":1,\"total_rejected\":0,\"numbers_sent\":[\"+233545644749\"],\"credit_used\":3,\"credit_left\":881,\"wallet_used\":0}},\"raw_response\":\"{\\\"status\\\":\\\"success\\\",\\\"code\\\":\\\"2000\\\",\\\"message\\\":\\\"messages sent successfully\\\",\\\"summary\\\":{\\\"_id\\\":\\\"7E80E387-2CD6-4F17-BA5A-C5BD68725A9F\\\",\\\"message_id\\\":\\\"20251014233545644749V2\\\",\\\"type\\\":\\\"API QUICK SMS\\\",\\\"total_sent\\\":1,\\\"contacts\\\":1,\\\"total_rejected\\\":0,\\\"numbers_sent\\\":[\\\"+233545644749\\\"],\\\"credit_used\\\":3,\\\"credit_left\\\":881,\\\"wallet_used\\\":0}}\"}', NULL, NULL, 0, NULL, '2025-10-14 21:54:41'),
(3, '0545644749', 'Thank you for voting!\n\nNominee: JESSICA\nEvent: MISS BREMAN ODWIRA\nCategory: CONTESTANTS\nVotes: 1\nAmount: GH₵1.00\nReceipt: GAN7O6V9\n\nThank you for your participation!', 1, 'mnotify', 'sent', '{\"success\":true,\"http_code\":200,\"response\":{\"status\":\"success\",\"code\":\"2000\",\"message\":\"messages sent successfully\",\"summary\":{\"_id\":\"F92DF8F6-20DC-492B-B7C4-4315837C4118\",\"message_id\":\"20251015233545644749V2\",\"type\":\"API QUICK SMS\",\"total_sent\":1,\"contacts\":1,\"total_rejected\":0,\"numbers_sent\":[\"+233545644749\"],\"credit_used\":3,\"credit_left\":851,\"wallet_used\":0}},\"raw_response\":\"{\\\"status\\\":\\\"success\\\",\\\"code\\\":\\\"2000\\\",\\\"message\\\":\\\"messages sent successfully\\\",\\\"summary\\\":{\\\"_id\\\":\\\"F92DF8F6-20DC-492B-B7C4-4315837C4118\\\",\\\"message_id\\\":\\\"20251015233545644749V2\\\",\\\"type\\\":\\\"API QUICK SMS\\\",\\\"total_sent\\\":1,\\\"contacts\\\":1,\\\"total_rejected\\\":0,\\\"numbers_sent\\\":[\\\"+233545644749\\\"],\\\"credit_used\\\":3,\\\"credit_left\\\":851,\\\"wallet_used\\\":0}}\"}', NULL, NULL, 0, NULL, '2025-10-15 23:02:56'),
(4, '0545644749', 'Thank you for voting!\n\nNominee: NANA ADJOA\nEvent: MISS BREMAN ODWIRA\nCategory: CONTESTANTS\nVotes: 1\nAmount: GH₵1.00\nReceipt: U405YOFR\n\nThank you for your participation!', 1, 'mnotify', 'sent', '{\"success\":true,\"http_code\":200,\"response\":{\"status\":\"success\",\"code\":\"2000\",\"message\":\"messages sent successfully\",\"summary\":{\"_id\":\"F917EE3D-9950-462A-AFB2-8B857425C680\",\"message_id\":\"20251015233545644749V2\",\"type\":\"API QUICK SMS\",\"total_sent\":1,\"contacts\":1,\"total_rejected\":0,\"numbers_sent\":[\"+233545644749\"],\"credit_used\":3,\"credit_left\":848,\"wallet_used\":0}},\"raw_response\":\"{\\\"status\\\":\\\"success\\\",\\\"code\\\":\\\"2000\\\",\\\"message\\\":\\\"messages sent successfully\\\",\\\"summary\\\":{\\\"_id\\\":\\\"F917EE3D-9950-462A-AFB2-8B857425C680\\\",\\\"message_id\\\":\\\"20251015233545644749V2\\\",\\\"type\\\":\\\"API QUICK SMS\\\",\\\"total_sent\\\":1,\\\"contacts\\\":1,\\\"total_rejected\\\":0,\\\"numbers_sent\\\":[\\\"+233545644749\\\"],\\\"credit_used\\\":3,\\\"credit_left\\\":848,\\\"wallet_used\\\":0}}\"}', NULL, NULL, 0, NULL, '2025-10-16 00:33:49'),
(5, '0545644749', 'Thank you for voting!\n\nNominee: NANA ADJOA\nEvent: MISS BREMAN ODWIRA\nCategory: CONTESTANTS\nVotes: 1\nAmount: GH₵1.00\nReceipt: EWG6YNRQ\n\nThank you for your participation!', 1, 'mnotify', 'sent', '{\"success\":true,\"http_code\":200,\"response\":{\"status\":\"success\",\"code\":\"2000\",\"message\":\"messages sent successfully\",\"summary\":{\"_id\":\"00C2FD4C-F55F-4E27-8680-5284D37E8F80\",\"message_id\":\"20251015233545644749V2\",\"type\":\"API QUICK SMS\",\"total_sent\":1,\"contacts\":1,\"total_rejected\":0,\"numbers_sent\":[\"+233545644749\"],\"credit_used\":3,\"credit_left\":845,\"wallet_used\":0}},\"raw_response\":\"{\\\"status\\\":\\\"success\\\",\\\"code\\\":\\\"2000\\\",\\\"message\\\":\\\"messages sent successfully\\\",\\\"summary\\\":{\\\"_id\\\":\\\"00C2FD4C-F55F-4E27-8680-5284D37E8F80\\\",\\\"message_id\\\":\\\"20251015233545644749V2\\\",\\\"type\\\":\\\"API QUICK SMS\\\",\\\"total_sent\\\":1,\\\"contacts\\\":1,\\\"total_rejected\\\":0,\\\"numbers_sent\\\":[\\\"+233545644749\\\"],\\\"credit_used\\\":3,\\\"credit_left\\\":845,\\\"wallet_used\\\":0}}\"}', NULL, NULL, 0, NULL, '2025-10-16 00:38:37'),
(6, '0545644749', 'Thank you for voting!\n\nNominee: NANA ADJOA\nEvent: MISS BREMAN ODWIRA\nCategory: CONTESTANTS\nVotes: 1\nAmount: GH₵1.00\nReceipt: O4KI8CVF\n\nThank you for your participation!', 1, 'mnotify', 'sent', '{\"success\":true,\"http_code\":200,\"response\":{\"status\":\"success\",\"code\":\"2000\",\"message\":\"messages sent successfully\",\"summary\":{\"_id\":\"7BC27736-0BD3-42F3-B9FC-E6DB3D6AD7E6\",\"message_id\":\"20251015233545644749V2\",\"type\":\"API QUICK SMS\",\"total_sent\":1,\"contacts\":1,\"total_rejected\":0,\"numbers_sent\":[\"+233545644749\"],\"credit_used\":3,\"credit_left\":842,\"wallet_used\":0}},\"raw_response\":\"{\\\"status\\\":\\\"success\\\",\\\"code\\\":\\\"2000\\\",\\\"message\\\":\\\"messages sent successfully\\\",\\\"summary\\\":{\\\"_id\\\":\\\"7BC27736-0BD3-42F3-B9FC-E6DB3D6AD7E6\\\",\\\"message_id\\\":\\\"20251015233545644749V2\\\",\\\"type\\\":\\\"API QUICK SMS\\\",\\\"total_sent\\\":1,\\\"contacts\\\":1,\\\"total_rejected\\\":0,\\\"numbers_sent\\\":[\\\"+233545644749\\\"],\\\"credit_used\\\":3,\\\"credit_left\\\":842,\\\"wallet_used\\\":0}}\"}', NULL, NULL, 0, NULL, '2025-10-16 00:47:46'),
(7, '0545644749', 'Thank you for voting!\n\nNominee: NANA ADJOA\nEvent: MISS BREMAN ODWIRA\nCategory: CONTESTANTS\nVotes: 1\nAmount: GH₵1.00\nReceipt: C8K65NB0\n\nThank you for your participation!', 1, 'mnotify', 'sent', '{\"success\":true,\"http_code\":200,\"response\":{\"status\":\"success\",\"code\":\"2000\",\"message\":\"messages sent successfully\",\"summary\":{\"_id\":\"DFF5CD63-0F4D-4203-89B4-65A7F9C372CA\",\"message_id\":\"20251016233545644749V2\",\"type\":\"API QUICK SMS\",\"total_sent\":1,\"contacts\":1,\"total_rejected\":0,\"numbers_sent\":[\"+233545644749\"],\"credit_used\":3,\"credit_left\":839,\"wallet_used\":0}},\"raw_response\":\"{\\\"status\\\":\\\"success\\\",\\\"code\\\":\\\"2000\\\",\\\"message\\\":\\\"messages sent successfully\\\",\\\"summary\\\":{\\\"_id\\\":\\\"DFF5CD63-0F4D-4203-89B4-65A7F9C372CA\\\",\\\"message_id\\\":\\\"20251016233545644749V2\\\",\\\"type\\\":\\\"API QUICK SMS\\\",\\\"total_sent\\\":1,\\\"contacts\\\":1,\\\"total_rejected\\\":0,\\\"numbers_sent\\\":[\\\"+233545644749\\\"],\\\"credit_used\\\":3,\\\"credit_left\\\":839,\\\"wallet_used\\\":0}}\"}', NULL, NULL, 0, NULL, '2025-10-16 11:53:56'),
(8, '0545644749', 'Thank you for voting!\n\nNominee: NANA ADJOA\nEvent: MISS BREMAN ODWIRA\nCategory: CONTESTANTS\nVotes: 1\nAmount: GH₵1.00\nReceipt: SDWFL0TE\n\nThank you for your participation!', 1, 'mnotify', 'sent', '{\"success\":true,\"http_code\":200,\"response\":{\"status\":\"success\",\"code\":\"2000\",\"message\":\"messages sent successfully\",\"summary\":{\"_id\":\"F9D2A63F-931D-4D07-BEFC-542EB9B2F853\",\"message_id\":\"20251016233545644749V2\",\"type\":\"API QUICK SMS\",\"total_sent\":1,\"contacts\":1,\"total_rejected\":0,\"numbers_sent\":[\"+233545644749\"],\"credit_used\":3,\"credit_left\":836,\"wallet_used\":0}},\"raw_response\":\"{\\\"status\\\":\\\"success\\\",\\\"code\\\":\\\"2000\\\",\\\"message\\\":\\\"messages sent successfully\\\",\\\"summary\\\":{\\\"_id\\\":\\\"F9D2A63F-931D-4D07-BEFC-542EB9B2F853\\\",\\\"message_id\\\":\\\"20251016233545644749V2\\\",\\\"type\\\":\\\"API QUICK SMS\\\",\\\"total_sent\\\":1,\\\"contacts\\\":1,\\\"total_rejected\\\":0,\\\"numbers_sent\\\":[\\\"+233545644749\\\"],\\\"credit_used\\\":3,\\\"credit_left\\\":836,\\\"wallet_used\\\":0}}\"}', NULL, NULL, 0, NULL, '2025-10-16 12:11:10');

-- --------------------------------------------------------

--
-- Table structure for table `sms_templates`
--

CREATE TABLE `sms_templates` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `type` enum('vote_confirmation','payment_receipt','event_reminder','custom') NOT NULL,
  `template` text NOT NULL,
  `variables` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`variables`)),
  `is_active` tinyint(1) DEFAULT 1,
  `tenant_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sms_templates`
--

INSERT INTO `sms_templates` (`id`, `name`, `type`, `template`, `variables`, `is_active`, `tenant_id`, `created_at`, `updated_at`) VALUES
(1, 'Default Vote Confirmation', 'vote_confirmation', 'Thank you for voting!\n\nNominee: {nominee_name}\nEvent: {event_name}\nCategory: {category_name}\nVotes: {vote_count}\nAmount: {amount}\nReceipt: {receipt_number}\n\nThank you for your participation!', '[\"nominee_name\", \"event_name\", \"category_name\", \"vote_count\", \"amount\", \"receipt_number\"]', 1, NULL, '2025-10-14 13:17:26', '2025-10-14 13:17:26'),
(2, 'Payment Receipt', 'payment_receipt', 'Payment Successful!\n\nAmount: {amount}\nTransaction ID: {transaction_id}\nDate: {date}\nMethod: {payment_method}\n\nThank you!', '[\"amount\", \"transaction_id\", \"date\", \"payment_method\"]', 1, NULL, '2025-10-14 13:17:26', '2025-10-14 13:17:26'),
(3, 'Event Reminder', 'event_reminder', 'Reminder: {event_name} voting is now live!\n\nVote for your favorite nominees now.\nEvent ends: {end_date}\n\nVote now!', '[\"event_name\", \"end_date\"]', 1, NULL, '2025-10-14 13:17:26', '2025-10-14 13:17:26');

-- --------------------------------------------------------

--
-- Table structure for table `subscription_plans`
--

CREATE TABLE `subscription_plans` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `billing_cycle` enum('monthly','yearly','lifetime','free') DEFAULT 'monthly',
  `max_events` int(11) DEFAULT NULL COMMENT 'NULL means unlimited',
  `max_contestants_per_event` int(11) DEFAULT NULL COMMENT 'NULL means unlimited',
  `max_votes_per_event` int(11) DEFAULT NULL COMMENT 'NULL means unlimited',
  `features` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Additional features as JSON' CHECK (json_valid(`features`)),
  `fee_rule_id` int(11) DEFAULT NULL COMMENT 'Default fee rule for this plan',
  `is_popular` tinyint(1) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `sort_order` int(3) DEFAULT 0,
  `trial_days` int(3) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `subscription_plans`
--

INSERT INTO `subscription_plans` (`id`, `name`, `slug`, `description`, `price`, `billing_cycle`, `max_events`, `max_contestants_per_event`, `max_votes_per_event`, `features`, `fee_rule_id`, `is_popular`, `is_active`, `sort_order`, `trial_days`, `created_at`, `updated_at`) VALUES
(5, 'Free', 'free', 'Totally FREE!', 0.00, 'lifetime', NULL, NULL, NULL, NULL, 10, 1, 1, 0, 0, '2025-10-10 22:11:57', '2025-10-14 22:00:08');

-- --------------------------------------------------------

--
-- Table structure for table `tenants`
--

CREATE TABLE `tenants` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `website` varchar(255) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `plan` enum('free','basic','premium','enterprise') DEFAULT 'basic',
  `current_plan_id` int(11) DEFAULT NULL,
  `subscription_status` enum('active','trial','expired','cancelled','suspended') DEFAULT 'trial',
  `subscription_expires_at` timestamp NULL DEFAULT NULL,
  `trial_ends_at` timestamp NULL DEFAULT NULL,
  `active` tinyint(1) DEFAULT 1,
  `verified` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tenants`
--

INSERT INTO `tenants` (`id`, `name`, `email`, `phone`, `website`, `address`, `plan`, `current_plan_id`, `subscription_status`, `subscription_expires_at`, `trial_ends_at`, `active`, `verified`, `created_at`, `updated_at`) VALUES
(22, 'Hope For All', 'ekowmee@gmail.com', NULL, NULL, NULL, 'free', 5, 'trial', NULL, NULL, 1, 1, '2025-10-10 20:31:58', '2025-10-14 18:00:08');

-- --------------------------------------------------------

--
-- Table structure for table `tenant_balances`
--

CREATE TABLE `tenant_balances` (
  `id` int(11) NOT NULL,
  `tenant_id` int(11) NOT NULL,
  `available` decimal(10,2) DEFAULT 0.00,
  `pending` decimal(10,2) DEFAULT 0.00,
  `on_hold` decimal(10,2) DEFAULT 0.00,
  `total_earned` decimal(10,2) DEFAULT 0.00,
  `total_paid` decimal(10,2) DEFAULT 0.00,
  `last_payout_at` timestamp NULL DEFAULT NULL,
  `last_payout_amount` decimal(10,2) DEFAULT 0.00,
  `payout_count` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `pending_approval` decimal(10,2) DEFAULT 0.00,
  `approved_pending` decimal(10,2) DEFAULT 0.00,
  `processing` decimal(10,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tenant_plan_history`
--

CREATE TABLE `tenant_plan_history` (
  `id` int(11) NOT NULL,
  `tenant_id` int(11) NOT NULL,
  `old_plan_id` int(11) DEFAULT NULL,
  `new_plan_id` int(11) NOT NULL,
  `changed_by` int(11) DEFAULT NULL COMMENT 'User ID who made the change',
  `change_reason` varchar(255) DEFAULT NULL,
  `effective_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tenant_plan_history`
--

INSERT INTO `tenant_plan_history` (`id`, `tenant_id`, `old_plan_id`, `new_plan_id`, `changed_by`, `change_reason`, `effective_date`, `created_at`) VALUES
(4, 22, 5, 5, NULL, 'Automatic plan update', '2025-10-11 00:01:44', '2025-10-11 00:01:44'),
(5, 22, 5, 5, NULL, 'Automatic plan update', '2025-10-12 14:54:07', '2025-10-12 14:54:07'),
(6, 22, 5, 5, NULL, 'Automatic plan update', '2025-10-14 18:00:08', '2025-10-14 18:00:08');

-- --------------------------------------------------------

--
-- Table structure for table `tenant_settings`
--

CREATE TABLE `tenant_settings` (
  `id` int(11) NOT NULL,
  `tenant_id` int(11) NOT NULL,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tenant_settings`
--

INSERT INTO `tenant_settings` (`id`, `tenant_id`, `setting_key`, `setting_value`, `created_at`, `updated_at`) VALUES
(46, 22, 'otp_required', 'false', '2025-10-10 20:31:58', '2025-10-10 20:31:58'),
(47, 22, 'leaderboard_lag_seconds', '30', '2025-10-10 20:31:58', '2025-10-10 20:31:58'),
(48, 22, 'theme_json', '{\"primary_color\":\"#007bff\",\"secondary_color\":\"#6c757d\",\"success_color\":\"#28a745\",\"danger_color\":\"#dc3545\"}', '2025-10-10 20:31:58', '2025-10-10 20:31:58'),
(49, 22, 'max_votes_per_msisdn', '10000', '2025-10-10 20:31:58', '2025-10-10 20:31:58'),
(50, 22, 'fraud_detection_enabled', 'true', '2025-10-10 20:31:58', '2025-10-10 20:31:58'),
(51, 22, 'webhook_enabled', 'false', '2025-10-10 20:31:58', '2025-10-10 20:31:58'),
(52, 22, 'email_notifications_enabled', 'true', '2025-10-10 20:31:58', '2025-10-10 20:31:58'),
(53, 22, 'sms_notifications_enabled', 'false', '2025-10-10 20:31:58', '2025-10-10 20:31:58'),
(54, 22, 'auto_approve_events', 'false', '2025-10-10 20:31:58', '2025-10-10 20:31:58'),
(55, 22, 'minimum_payout_amount', '10', '2025-10-10 20:31:58', '2025-10-10 20:31:58');

-- --------------------------------------------------------

--
-- Table structure for table `tenant_subscriptions`
--

CREATE TABLE `tenant_subscriptions` (
  `id` int(11) NOT NULL,
  `tenant_id` int(11) NOT NULL,
  `plan_id` int(11) NOT NULL,
  `status` enum('active','expired','cancelled','suspended') DEFAULT 'active',
  `started_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `expires_at` timestamp NULL DEFAULT NULL,
  `cancelled_at` timestamp NULL DEFAULT NULL,
  `auto_renew` tinyint(1) DEFAULT 1,
  `payment_method` varchar(50) DEFAULT NULL,
  `last_payment_at` timestamp NULL DEFAULT NULL,
  `next_payment_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tenant_subscriptions`
--

INSERT INTO `tenant_subscriptions` (`id`, `tenant_id`, `plan_id`, `status`, `started_at`, `expires_at`, `cancelled_at`, `auto_renew`, `payment_method`, `last_payment_at`, `next_payment_at`, `created_at`, `updated_at`) VALUES
(14, 22, 1, 'cancelled', '2025-10-10 20:31:58', NULL, '2025-10-10 22:16:01', 1, NULL, NULL, NULL, '2025-10-10 20:31:58', '2025-10-10 22:16:01'),
(15, 22, 5, 'active', '2025-10-10 22:16:01', '2025-11-10 22:16:01', NULL, 1, NULL, NULL, '2025-11-10 22:16:01', '2025-10-10 22:16:01', '2025-10-10 22:16:01');

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `id` int(11) NOT NULL,
  `tenant_id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `contestant_id` int(11) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `bundle_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `msisdn` varchar(20) DEFAULT NULL,
  `status` enum('pending','success','failed') DEFAULT 'pending',
  `provider` varchar(50) DEFAULT NULL,
  `provider_reference` varchar(100) DEFAULT NULL,
  `coupon_code` varchar(50) DEFAULT NULL,
  `referral_code` varchar(50) DEFAULT NULL,
  `failure_reason` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `tenant_id` int(11) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('platform_admin','owner','manager','staff') DEFAULT 'staff',
  `active` tinyint(1) DEFAULT 1,
  `last_login` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `tenant_id`, `email`, `password_hash`, `role`, `active`, `last_login`, `created_at`, `updated_at`) VALUES
(3, NULL, 'ekowme@gmail.com', '$2y$10$IdVuCAw45Y7kkjsiqzBwIO99rvD6Fe0oSDQMAzL9DeJ7jzFUpEMFu', 'platform_admin', 1, '2025-10-16 08:22:59', '2025-10-03 11:36:30', '2025-10-16 08:22:59'),
(17, 22, 'ekowmee@gmail.com', '$2y$10$VVxsBptQb2P1E51R.kV6wuxE82yQnZgoMkaHiW0H.OGpCLLoXJlJa', 'owner', 1, '2025-10-16 07:57:44', '2025-10-10 20:31:58', '2025-10-16 07:57:44');

-- --------------------------------------------------------

--
-- Table structure for table `user_subscriptions`
--

CREATE TABLE `user_subscriptions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `tenant_id` int(11) DEFAULT NULL,
  `plan_id` int(11) NOT NULL,
  `payment_transaction_id` int(11) DEFAULT NULL,
  `status` enum('active','cancelled','expired','suspended','pending') NOT NULL DEFAULT 'pending',
  `starts_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `cancelled_at` timestamp NULL DEFAULT NULL,
  `auto_renew` tinyint(1) NOT NULL DEFAULT 1,
  `metadata` text DEFAULT NULL COMMENT 'JSON metadata',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_vote_bundles`
--

CREATE TABLE `user_vote_bundles` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `tenant_id` int(11) DEFAULT NULL,
  `bundle_id` int(11) NOT NULL,
  `payment_transaction_id` int(11) DEFAULT NULL,
  `votes_purchased` int(11) NOT NULL,
  `votes_used` int(11) NOT NULL DEFAULT 0,
  `votes_remaining` int(11) NOT NULL,
  `status` enum('active','expired','used_up') NOT NULL DEFAULT 'active',
  `expires_at` timestamp NULL DEFAULT NULL,
  `metadata` text DEFAULT NULL COMMENT 'JSON metadata',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ussd_sessions`
--

CREATE TABLE `ussd_sessions` (
  `id` int(11) NOT NULL,
  `session_id` varchar(100) NOT NULL,
  `msisdn` varchar(20) NOT NULL,
  `state` varchar(50) NOT NULL,
  `data` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `votes`
--

CREATE TABLE `votes` (
  `id` int(11) NOT NULL,
  `transaction_id` int(11) NOT NULL,
  `tenant_id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `contestant_id` int(11) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `quantity` int(10) UNSIGNED NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `vote_bundles`
--

CREATE TABLE `vote_bundles` (
  `id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `votes` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `vote_ledger`
--

CREATE TABLE `vote_ledger` (
  `id` int(11) NOT NULL,
  `vote_id` int(11) NOT NULL,
  `transaction_id` int(11) NOT NULL,
  `tenant_id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `contestant_id` int(11) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `hash` varchar(64) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `vote_receipts`
--

CREATE TABLE `vote_receipts` (
  `id` int(11) NOT NULL,
  `transaction_id` int(11) NOT NULL,
  `short_code` varchar(20) NOT NULL,
  `public_hash` varchar(64) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `webhook_endpoints`
--

CREATE TABLE `webhook_endpoints` (
  `id` int(11) NOT NULL,
  `tenant_id` int(11) NOT NULL,
  `url` varchar(500) NOT NULL,
  `secret` varchar(255) DEFAULT NULL,
  `event_types` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`event_types`)),
  `retry_count` int(2) DEFAULT 3,
  `timeout_seconds` int(3) DEFAULT 10,
  `active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `webhook_events`
--

CREATE TABLE `webhook_events` (
  `id` int(11) NOT NULL,
  `endpoint_id` int(11) NOT NULL,
  `event` varchar(100) NOT NULL,
  `payload` text NOT NULL,
  `status` enum('queued','sent','failed') DEFAULT 'queued',
  `attempts` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_action` (`user_id`,`action`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `event_id` (`event_id`);

--
-- Indexes for table `contestants`
--
ALTER TABLE `contestants`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `contestant_code` (`contestant_code`),
  ADD KEY `idx_contestant_code` (`contestant_code`),
  ADD KEY `idx_contestants_tenant_active` (`tenant_id`,`active`);

--
-- Indexes for table `contestant_categories`
--
ALTER TABLE `contestant_categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_contestant_category` (`contestant_id`,`category_id`),
  ADD UNIQUE KEY `unique_category_code` (`category_id`,`short_code`),
  ADD KEY `idx_contestant` (`contestant_id`),
  ADD KEY `idx_category` (`category_id`),
  ADD KEY `idx_short_code` (`short_code`),
  ADD KEY `idx_contestant_categories_active` (`category_id`,`active`,`display_order`);

--
-- Indexes for table `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`),
  ADD KEY `idx_tenant_active` (`tenant_id`,`active`),
  ADD KEY `idx_code` (`code`),
  ADD KEY `fk_events_suspended_by` (`suspended_by`),
  ADD KEY `idx_events_results_visible` (`results_visible`),
  ADD KEY `idx_events_deactivated_at` (`deactivated_at`);

--
-- Indexes for table `event_drafts`
--
ALTER TABLE `event_drafts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_tenant_id` (`tenant_id`),
  ADD KEY `idx_created_by` (`created_by`);

--
-- Indexes for table `event_status_history`
--
ALTER TABLE `event_status_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_event_status_history_event_id` (`event_id`),
  ADD KEY `idx_event_status_history_changed_by` (`changed_by`),
  ADD KEY `idx_event_status_history_created_at` (`created_at`);

--
-- Indexes for table `fee_rules`
--
ALTER TABLE `fee_rules`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tenant_id` (`tenant_id`),
  ADD KEY `event_id` (`event_id`);

--
-- Indexes for table `fraud_events`
--
ALTER TABLE `fraud_events`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tenant_id` (`tenant_id`);

--
-- Indexes for table `leaderboard_cache`
--
ALTER TABLE `leaderboard_cache`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_event_contestant_category` (`event_id`,`contestant_id`,`category_id`),
  ADD KEY `contestant_id` (`contestant_id`),
  ADD KEY `idx_category` (`category_id`);

--
-- Indexes for table `otp_requests`
--
ALTER TABLE `otp_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_msisdn_expires` (`msisdn`,`expires_at`);

--
-- Indexes for table `payment_gateways`
--
ALTER TABLE `payment_gateways`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_provider_tenant` (`provider`,`tenant_id`),
  ADD KEY `idx_active_priority` (`is_active`,`priority`),
  ADD KEY `idx_tenant` (`tenant_id`);

--
-- Indexes for table `payment_transactions`
--
ALTER TABLE `payment_transactions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_reference` (`reference`),
  ADD KEY `idx_gateway_reference` (`gateway_reference`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_gateway` (`gateway_id`),
  ADD KEY `idx_tenant` (`tenant_id`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_related` (`related_type`,`related_id`),
  ADD KEY `idx_phone` (`phone_number`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `payment_webhook_logs`
--
ALTER TABLE `payment_webhook_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_provider` (`gateway_provider`),
  ADD KEY `idx_processed` (`processed`),
  ADD KEY `idx_transaction_reference` (`transaction_reference`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `payouts`
--
ALTER TABLE `payouts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `payout_id` (`payout_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_tenant_status` (`tenant_id`,`status`),
  ADD KEY `idx_created_at` (`created_at`),
  ADD KEY `payout_method_id` (`payout_method_id`),
  ADD KEY `initiated_by` (`initiated_by`),
  ADD KEY `approved_by` (`approved_by`);

--
-- Indexes for table `payout_methods`
--
ALTER TABLE `payout_methods`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_tenant_default` (`tenant_id`,`is_default`);

--
-- Indexes for table `payout_schedules`
--
ALTER TABLE `payout_schedules`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_tenant_schedule` (`tenant_id`);

--
-- Indexes for table `plan_features`
--
ALTER TABLE `plan_features`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_plan_feature` (`plan_id`,`feature_key`),
  ADD KEY `idx_plan_sort` (`plan_id`,`sort_order`);

--
-- Indexes for table `platform_revenue`
--
ALTER TABLE `platform_revenue`
  ADD PRIMARY KEY (`id`),
  ADD KEY `transaction_id` (`transaction_id`),
  ADD KEY `idx_type_date` (`revenue_type`,`created_at`),
  ADD KEY `idx_amount_date` (`amount`,`created_at`);

--
-- Indexes for table `rate_limits`
--
ALTER TABLE `rate_limits`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_key_time` (`key`,`created_at`);

--
-- Indexes for table `revenue_shares`
--
ALTER TABLE `revenue_shares`
  ADD PRIMARY KEY (`id`),
  ADD KEY `transaction_id` (`transaction_id`),
  ADD KEY `tenant_id` (`tenant_id`),
  ADD KEY `fee_rule_id` (`fee_rule_id`);

--
-- Indexes for table `revenue_transactions`
--
ALTER TABLE `revenue_transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `transaction_id` (`transaction_id`),
  ADD KEY `idx_tenant_date` (`tenant_id`,`created_at`),
  ADD KEY `idx_event_date` (`event_id`,`created_at`),
  ADD KEY `idx_distribution_status` (`distribution_status`);

--
-- Indexes for table `risk_blocks`
--
ALTER TABLE `risk_blocks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tenant_id` (`tenant_id`),
  ADD KEY `idx_type_value_active` (`block_type`,`block_value`,`active`);

--
-- Indexes for table `sms_gateways`
--
ALTER TABLE `sms_gateways`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_type_active` (`type`,`is_active`),
  ADD KEY `idx_priority` (`priority`),
  ADD KEY `idx_sms_gateways_active_priority` (`is_active`,`priority`);

--
-- Indexes for table `sms_logs`
--
ALTER TABLE `sms_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_phone` (`phone`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_gateway` (`gateway_id`),
  ADD KEY `idx_vote` (`vote_id`),
  ADD KEY `idx_transaction` (`transaction_id`),
  ADD KEY `idx_created_at` (`created_at`),
  ADD KEY `idx_sms_logs_phone_status` (`phone`,`status`),
  ADD KEY `idx_sms_logs_created_status` (`created_at`,`status`);

--
-- Indexes for table `sms_templates`
--
ALTER TABLE `sms_templates`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_type_active` (`type`,`is_active`),
  ADD KEY `idx_tenant` (`tenant_id`);

--
-- Indexes for table `subscription_plans`
--
ALTER TABLE `subscription_plans`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `fee_rule_id` (`fee_rule_id`),
  ADD KEY `idx_active_sort` (`is_active`,`sort_order`),
  ADD KEY `idx_slug` (`slug`);

--
-- Indexes for table `tenants`
--
ALTER TABLE `tenants`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_active` (`active`),
  ADD KEY `idx_plan` (`plan`),
  ADD KEY `current_plan_id` (`current_plan_id`);

--
-- Indexes for table `tenant_balances`
--
ALTER TABLE `tenant_balances`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `tenant_id` (`tenant_id`);

--
-- Indexes for table `tenant_plan_history`
--
ALTER TABLE `tenant_plan_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `old_plan_id` (`old_plan_id`),
  ADD KEY `new_plan_id` (`new_plan_id`),
  ADD KEY `changed_by` (`changed_by`),
  ADD KEY `idx_tenant_date` (`tenant_id`,`effective_date`);

--
-- Indexes for table `tenant_settings`
--
ALTER TABLE `tenant_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_tenant_setting` (`tenant_id`,`setting_key`);

--
-- Indexes for table `tenant_subscriptions`
--
ALTER TABLE `tenant_subscriptions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `plan_id` (`plan_id`),
  ADD KEY `idx_tenant_status` (`tenant_id`,`status`),
  ADD KEY `idx_expires_at` (`expires_at`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `event_id` (`event_id`),
  ADD KEY `contestant_id` (`contestant_id`),
  ADD KEY `bundle_id` (`bundle_id`),
  ADD KEY `idx_provider_ref` (`provider`,`provider_reference`),
  ADD KEY `idx_tenant_status` (`tenant_id`,`status`),
  ADD KEY `idx_created_at` (`created_at`),
  ADD KEY `idx_transactions_category` (`category_id`),
  ADD KEY `idx_transactions_event_category` (`event_id`,`category_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `tenant_id` (`tenant_id`);

--
-- Indexes for table `user_subscriptions`
--
ALTER TABLE `user_subscriptions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_tenant` (`tenant_id`),
  ADD KEY `idx_plan` (`plan_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_expires_at` (`expires_at`),
  ADD KEY `idx_payment_transaction` (`payment_transaction_id`);

--
-- Indexes for table `user_vote_bundles`
--
ALTER TABLE `user_vote_bundles`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_tenant` (`tenant_id`),
  ADD KEY `idx_bundle` (`bundle_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_payment_transaction` (`payment_transaction_id`);

--
-- Indexes for table `ussd_sessions`
--
ALTER TABLE `ussd_sessions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `session_id` (`session_id`),
  ADD KEY `idx_session_id` (`session_id`);

--
-- Indexes for table `votes`
--
ALTER TABLE `votes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `transaction_id` (`transaction_id`),
  ADD KEY `tenant_id` (`tenant_id`),
  ADD KEY `event_id` (`event_id`),
  ADD KEY `idx_contestant_votes` (`contestant_id`,`created_at`),
  ADD KEY `idx_votes_category` (`category_id`),
  ADD KEY `idx_votes_event_category_contestant` (`event_id`,`category_id`,`contestant_id`);

--
-- Indexes for table `vote_bundles`
--
ALTER TABLE `vote_bundles`
  ADD PRIMARY KEY (`id`),
  ADD KEY `event_id` (`event_id`);

--
-- Indexes for table `vote_ledger`
--
ALTER TABLE `vote_ledger`
  ADD PRIMARY KEY (`id`),
  ADD KEY `vote_id` (`vote_id`),
  ADD KEY `idx_tenant_hash` (`tenant_id`,`hash`),
  ADD KEY `idx_vote_ledger_category` (`category_id`);

--
-- Indexes for table `vote_receipts`
--
ALTER TABLE `vote_receipts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `short_code` (`short_code`),
  ADD KEY `transaction_id` (`transaction_id`),
  ADD KEY `idx_short_code` (`short_code`);

--
-- Indexes for table `webhook_endpoints`
--
ALTER TABLE `webhook_endpoints`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tenant_id` (`tenant_id`);

--
-- Indexes for table `webhook_events`
--
ALTER TABLE `webhook_events`
  ADD PRIMARY KEY (`id`),
  ADD KEY `endpoint_id` (`endpoint_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=322;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=167;

--
-- AUTO_INCREMENT for table `contestants`
--
ALTER TABLE `contestants`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=216;

--
-- AUTO_INCREMENT for table `contestant_categories`
--
ALTER TABLE `contestant_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=239;

--
-- AUTO_INCREMENT for table `events`
--
ALTER TABLE `events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;

--
-- AUTO_INCREMENT for table `event_drafts`
--
ALTER TABLE `event_drafts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `event_status_history`
--
ALTER TABLE `event_status_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `fee_rules`
--
ALTER TABLE `fee_rules`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `fraud_events`
--
ALTER TABLE `fraud_events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `leaderboard_cache`
--
ALTER TABLE `leaderboard_cache`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=130;

--
-- AUTO_INCREMENT for table `otp_requests`
--
ALTER TABLE `otp_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payment_gateways`
--
ALTER TABLE `payment_gateways`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `payment_transactions`
--
ALTER TABLE `payment_transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- AUTO_INCREMENT for table `payment_webhook_logs`
--
ALTER TABLE `payment_webhook_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payouts`
--
ALTER TABLE `payouts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `payout_methods`
--
ALTER TABLE `payout_methods`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `payout_schedules`
--
ALTER TABLE `payout_schedules`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `plan_features`
--
ALTER TABLE `plan_features`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT for table `platform_revenue`
--
ALTER TABLE `platform_revenue`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `rate_limits`
--
ALTER TABLE `rate_limits`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `revenue_shares`
--
ALTER TABLE `revenue_shares`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=60;

--
-- AUTO_INCREMENT for table `revenue_transactions`
--
ALTER TABLE `revenue_transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=151;

--
-- AUTO_INCREMENT for table `risk_blocks`
--
ALTER TABLE `risk_blocks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sms_gateways`
--
ALTER TABLE `sms_gateways`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `sms_logs`
--
ALTER TABLE `sms_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `sms_templates`
--
ALTER TABLE `sms_templates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `subscription_plans`
--
ALTER TABLE `subscription_plans`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `tenants`
--
ALTER TABLE `tenants`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `tenant_balances`
--
ALTER TABLE `tenant_balances`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT for table `tenant_plan_history`
--
ALTER TABLE `tenant_plan_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `tenant_settings`
--
ALTER TABLE `tenant_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=56;

--
-- AUTO_INCREMENT for table `tenant_subscriptions`
--
ALTER TABLE `tenant_subscriptions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=158;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `user_subscriptions`
--
ALTER TABLE `user_subscriptions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user_vote_bundles`
--
ALTER TABLE `user_vote_bundles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ussd_sessions`
--
ALTER TABLE `ussd_sessions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `votes`
--
ALTER TABLE `votes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=106;

--
-- AUTO_INCREMENT for table `vote_bundles`
--
ALTER TABLE `vote_bundles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- AUTO_INCREMENT for table `vote_ledger`
--
ALTER TABLE `vote_ledger`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=85;

--
-- AUTO_INCREMENT for table `vote_receipts`
--
ALTER TABLE `vote_receipts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=97;

--
-- AUTO_INCREMENT for table `webhook_endpoints`
--
ALTER TABLE `webhook_endpoints`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `webhook_events`
--
ALTER TABLE `webhook_events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD CONSTRAINT `audit_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `categories`
--
ALTER TABLE `categories`
  ADD CONSTRAINT `categories_ibfk_1` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `contestant_categories`
--
ALTER TABLE `contestant_categories`
  ADD CONSTRAINT `contestant_categories_ibfk_1` FOREIGN KEY (`contestant_id`) REFERENCES `contestants` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `contestant_categories_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `events`
--
ALTER TABLE `events`
  ADD CONSTRAINT `events_ibfk_1` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_events_suspended_by` FOREIGN KEY (`suspended_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `event_drafts`
--
ALTER TABLE `event_drafts`
  ADD CONSTRAINT `event_drafts_ibfk_1` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `event_drafts_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `event_status_history`
--
ALTER TABLE `event_status_history`
  ADD CONSTRAINT `event_status_history_ibfk_1` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `event_status_history_ibfk_2` FOREIGN KEY (`changed_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `fee_rules`
--
ALTER TABLE `fee_rules`
  ADD CONSTRAINT `fee_rules_ibfk_1` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fee_rules_ibfk_2` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `fraud_events`
--
ALTER TABLE `fraud_events`
  ADD CONSTRAINT `fraud_events_ibfk_1` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `leaderboard_cache`
--
ALTER TABLE `leaderboard_cache`
  ADD CONSTRAINT `leaderboard_cache_ibfk_1` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `leaderboard_cache_ibfk_2` FOREIGN KEY (`contestant_id`) REFERENCES `contestants` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `payment_transactions`
--
ALTER TABLE `payment_transactions`
  ADD CONSTRAINT `fk_payment_transactions_gateway` FOREIGN KEY (`gateway_id`) REFERENCES `payment_gateways` (`id`);

--
-- Constraints for table `payouts`
--
ALTER TABLE `payouts`
  ADD CONSTRAINT `payouts_ibfk_1` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `payouts_ibfk_2` FOREIGN KEY (`payout_method_id`) REFERENCES `payout_methods` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `payouts_ibfk_3` FOREIGN KEY (`initiated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `payouts_ibfk_4` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `payout_methods`
--
ALTER TABLE `payout_methods`
  ADD CONSTRAINT `payout_methods_ibfk_1` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `payout_schedules`
--
ALTER TABLE `payout_schedules`
  ADD CONSTRAINT `payout_schedules_ibfk_1` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `plan_features`
--
ALTER TABLE `plan_features`
  ADD CONSTRAINT `plan_features_ibfk_1` FOREIGN KEY (`plan_id`) REFERENCES `subscription_plans` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `platform_revenue`
--
ALTER TABLE `platform_revenue`
  ADD CONSTRAINT `platform_revenue_ibfk_1` FOREIGN KEY (`transaction_id`) REFERENCES `transactions` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `revenue_shares`
--
ALTER TABLE `revenue_shares`
  ADD CONSTRAINT `revenue_shares_ibfk_1` FOREIGN KEY (`transaction_id`) REFERENCES `transactions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `revenue_shares_ibfk_2` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `revenue_shares_ibfk_3` FOREIGN KEY (`fee_rule_id`) REFERENCES `fee_rules` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `revenue_transactions`
--
ALTER TABLE `revenue_transactions`
  ADD CONSTRAINT `revenue_transactions_ibfk_1` FOREIGN KEY (`transaction_id`) REFERENCES `transactions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `revenue_transactions_ibfk_2` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `revenue_transactions_ibfk_3` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `risk_blocks`
--
ALTER TABLE `risk_blocks`
  ADD CONSTRAINT `risk_blocks_ibfk_1` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `sms_logs`
--
ALTER TABLE `sms_logs`
  ADD CONSTRAINT `sms_logs_ibfk_1` FOREIGN KEY (`gateway_id`) REFERENCES `sms_gateways` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `sms_logs_ibfk_2` FOREIGN KEY (`vote_id`) REFERENCES `votes` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `sms_logs_ibfk_3` FOREIGN KEY (`transaction_id`) REFERENCES `transactions` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `sms_templates`
--
ALTER TABLE `sms_templates`
  ADD CONSTRAINT `sms_templates_ibfk_1` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `subscription_plans`
--
ALTER TABLE `subscription_plans`
  ADD CONSTRAINT `subscription_plans_ibfk_1` FOREIGN KEY (`fee_rule_id`) REFERENCES `fee_rules` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `tenants`
--
ALTER TABLE `tenants`
  ADD CONSTRAINT `tenants_ibfk_1` FOREIGN KEY (`current_plan_id`) REFERENCES `subscription_plans` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `tenant_balances`
--
ALTER TABLE `tenant_balances`
  ADD CONSTRAINT `tenant_balances_ibfk_1` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `tenant_plan_history`
--
ALTER TABLE `tenant_plan_history`
  ADD CONSTRAINT `tenant_plan_history_ibfk_1` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `tenant_plan_history_ibfk_2` FOREIGN KEY (`old_plan_id`) REFERENCES `subscription_plans` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `tenant_plan_history_ibfk_3` FOREIGN KEY (`new_plan_id`) REFERENCES `subscription_plans` (`id`),
  ADD CONSTRAINT `tenant_plan_history_ibfk_4` FOREIGN KEY (`changed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `tenant_settings`
--
ALTER TABLE `tenant_settings`
  ADD CONSTRAINT `tenant_settings_ibfk_1` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `tenant_subscriptions`
--
ALTER TABLE `tenant_subscriptions`
  ADD CONSTRAINT `tenant_subscriptions_ibfk_1` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `tenant_subscriptions_ibfk_2` FOREIGN KEY (`plan_id`) REFERENCES `subscription_plans` (`id`);

--
-- Constraints for table `transactions`
--
ALTER TABLE `transactions`
  ADD CONSTRAINT `fk_transactions_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `transactions_ibfk_1` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `transactions_ibfk_2` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `transactions_ibfk_3` FOREIGN KEY (`contestant_id`) REFERENCES `contestants` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `transactions_ibfk_4` FOREIGN KEY (`bundle_id`) REFERENCES `vote_bundles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_subscriptions`
--
ALTER TABLE `user_subscriptions`
  ADD CONSTRAINT `fk_user_subscriptions_payment` FOREIGN KEY (`payment_transaction_id`) REFERENCES `payment_transactions` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_user_subscriptions_plan` FOREIGN KEY (`plan_id`) REFERENCES `subscription_plans` (`id`);

--
-- Constraints for table `user_vote_bundles`
--
ALTER TABLE `user_vote_bundles`
  ADD CONSTRAINT `fk_user_vote_bundles_bundle` FOREIGN KEY (`bundle_id`) REFERENCES `vote_bundles` (`id`),
  ADD CONSTRAINT `fk_user_vote_bundles_payment` FOREIGN KEY (`payment_transaction_id`) REFERENCES `payment_transactions` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `votes`
--
ALTER TABLE `votes`
  ADD CONSTRAINT `fk_votes_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `votes_ibfk_1` FOREIGN KEY (`transaction_id`) REFERENCES `transactions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `votes_ibfk_2` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `votes_ibfk_3` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `votes_ibfk_4` FOREIGN KEY (`contestant_id`) REFERENCES `contestants` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `vote_bundles`
--
ALTER TABLE `vote_bundles`
  ADD CONSTRAINT `vote_bundles_ibfk_1` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `vote_ledger`
--
ALTER TABLE `vote_ledger`
  ADD CONSTRAINT `fk_vote_ledger_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `vote_ledger_ibfk_1` FOREIGN KEY (`vote_id`) REFERENCES `votes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `vote_receipts`
--
ALTER TABLE `vote_receipts`
  ADD CONSTRAINT `vote_receipts_ibfk_1` FOREIGN KEY (`transaction_id`) REFERENCES `transactions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `webhook_endpoints`
--
ALTER TABLE `webhook_endpoints`
  ADD CONSTRAINT `webhook_endpoints_ibfk_1` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `webhook_events`
--
ALTER TABLE `webhook_events`
  ADD CONSTRAINT `webhook_events_ibfk_1` FOREIGN KEY (`endpoint_id`) REFERENCES `webhook_endpoints` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
