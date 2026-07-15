-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Jul 16, 2026 at 12:28 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `construction_management`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity_logs`
--

CREATE TABLE `activity_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `action` varchar(255) NOT NULL,
  `module` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `old_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`old_values`)),
  `new_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`new_values`)),
  `ip_address` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `actual_costs`
--

CREATE TABLE `actual_costs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `project_id` bigint(20) UNSIGNED NOT NULL,
  `boq_item_id` bigint(20) UNSIGNED DEFAULT NULL,
  `cost_type` varchar(255) NOT NULL,
  `description` varchar(255) NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `cost_date` date NOT NULL,
  `vendor` varchar(255) DEFAULT NULL,
  `invoice_number` varchar(255) DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `actual_costs`
--

INSERT INTO `actual_costs` (`id`, `project_id`, `boq_item_id`, `cost_type`, `description`, `amount`, `cost_date`, `vendor`, `invoice_number`, `remarks`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 1, 3, 'equipment', 'gggggggggggg', 500.00, '2026-07-13', NULL, NULL, NULL, 1, '2026-07-13 19:18:12', '2026-07-13 19:18:12'),
(2, 1, 4, 'equipment', 'sum ting', 10000.00, '2026-07-14', NULL, NULL, NULL, 1, '2026-07-14 01:59:17', '2026-07-14 01:59:17');

-- --------------------------------------------------------

--
-- Table structure for table `boq_items`
--

CREATE TABLE `boq_items` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `project_id` bigint(20) UNSIGNED NOT NULL,
  `cost_category_id` bigint(20) UNSIGNED DEFAULT NULL,
  `parent_id` bigint(20) UNSIGNED DEFAULT NULL,
  `item_number` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `unit` varchar(255) NOT NULL,
  `quantity` decimal(15,4) NOT NULL DEFAULT 0.0000,
  `unit_rate` decimal(15,2) NOT NULL DEFAULT 0.00,
  `revenue_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `duration_days` int(11) DEFAULT NULL,
  `planned_start_date` date DEFAULT NULL,
  `planned_end_date` date DEFAULT NULL,
  `display_order` int(11) NOT NULL DEFAULT 0,
  `is_parent` tinyint(1) NOT NULL DEFAULT 0,
  `status` enum('pending','in_progress','completed') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `boq_items`
--

INSERT INTO `boq_items` (`id`, `project_id`, `cost_category_id`, `parent_id`, `item_number`, `description`, `unit`, `quantity`, `unit_rate`, `revenue_amount`, `duration_days`, `planned_start_date`, `planned_end_date`, `display_order`, `is_parent`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, 1, NULL, '1', 'Excavation & Earthwork', 'LS', 120.0000, 150000.00, 18000000.00, 12, NULL, NULL, 1, 1, 'in_progress', '2026-07-13 17:04:07', '2026-07-15 19:18:02', NULL),
(2, 1, 1, NULL, '2', 'Water Proofing Works', 'LS', 1.0000, 50000.00, 50000.00, 1000000, NULL, NULL, 2, 1, 'pending', '2026-07-13 17:04:07', '2026-07-15 18:53:39', NULL),
(3, 1, 1, 1, '1.01', 'Site Clearance to remove top soil 30cm thick from NGL', 'm2', 5234.0000, 75.00, 392550.00, 5, '2023-01-15', '2023-01-20', 0, 0, 'completed', '2026-07-13 17:04:07', '2026-07-13 17:04:07', NULL),
(4, 1, 1, 2, '2.01', 'Supply and Apply 4mm thick APP-Modified water proofing membrane (under Foundation)', 'm2', 5000.0000, 1150.00, 5750000.00, 30, '2023-06-15', '2023-07-15', 0, 0, 'in_progress', '2026-07-13 17:04:07', '2026-07-13 17:04:07', NULL),
(5, 1, 1, 1, '1.02', 'Backfill under mat foundation compacted, 95% MDD per AASHTO T-180', 'm3', 5351.0000, 125.00, 668875.00, 2, '2023-02-05', '2023-02-07', 0, 0, 'completed', '2026-07-13 17:04:07', '2026-07-13 17:04:07', NULL),
(6, 2, 4, NULL, '1', 'Foundation Works', 'LS', 1.0000, 0.00, 0.00, NULL, NULL, NULL, 1, 1, 'in_progress', '2026-07-13 17:04:07', '2026-07-15 19:18:11', NULL),
(7, 2, 4, 6, '1.01', 'Reinforced Concrete Foundation Grade Beam', 'm3', 450.0000, 8500.00, 3825000.00, 45, '2023-03-15', '2023-04-30', 0, 0, 'completed', '2026-07-13 17:04:07', '2026-07-13 17:04:07', NULL),
(8, 2, 5, NULL, '2', 'Electrical Works', 'LS', 1.0000, 0.00, 0.00, NULL, NULL, NULL, 2, 1, 'pending', '2026-07-13 17:04:07', '2026-07-13 17:04:07', NULL),
(9, 2, 5, 8, '2.01', 'Electrical Conduit and Wiring Installation', 'm', 2500.0000, 450.00, 1125000.00, 60, '2023-07-01', '2023-08-30', 0, 0, 'in_progress', '2026-07-13 17:04:07', '2026-07-13 17:04:07', NULL),
(10, 1, 2, NULL, '3', 'ggggggggggggggggggg', 'm2', 2.0000, 20.00, 40.00, 222, '2026-02-03', '2027-05-03', 0, 0, 'pending', '2026-07-15 18:56:23', '2026-07-15 18:56:23', NULL),
(11, 2, 4, NULL, '3', 'Reinforced Concrete Foundation Footing', 'm2', 120.0000, 80500.00, 9660000.00, 12, '2026-07-13', '2026-07-24', 0, 0, 'pending', '2026-07-15 19:10:49', '2026-07-15 19:16:39', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` bigint(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` bigint(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cost_categories`
--

CREATE TABLE `cost_categories` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `project_id` bigint(20) UNSIGNED NOT NULL,
  `code` varchar(255) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `display_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cost_categories`
--

INSERT INTO `cost_categories` (`id`, `project_id`, `code`, `name`, `description`, `display_order`, `created_at`, `updated_at`) VALUES
(1, 1, 'A', 'SUB-STRUCTURE', NULL, 1, '2026-07-13 17:04:07', '2026-07-13 17:04:07'),
(2, 1, 'B', 'SUPER-STRUCTURE', NULL, 2, '2026-07-13 17:04:07', '2026-07-13 17:04:07'),
(3, 1, 'C', 'FINISHING WORKS', NULL, 3, '2026-07-13 17:04:07', '2026-07-13 17:04:07'),
(4, 2, 'A', 'SUB-STRUCTURE', NULL, 1, '2026-07-13 17:04:07', '2026-07-13 17:04:07'),
(5, 2, 'B', 'SUPER-STRUCTURE', NULL, 2, '2026-07-13 17:04:07', '2026-07-13 17:04:07');

-- --------------------------------------------------------

--
-- Table structure for table `documents`
--

CREATE TABLE `documents` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `file_type` varchar(255) DEFAULT NULL,
  `file_size` bigint(20) DEFAULT NULL,
  `documentable_type` varchar(255) NOT NULL,
  `documentable_id` bigint(20) UNSIGNED NOT NULL,
  `uploaded_by` bigint(20) UNSIGNED DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `documents`
--

INSERT INTO `documents` (`id`, `name`, `file_path`, `file_type`, `file_size`, `documentable_type`, `documentable_id`, `uploaded_by`, `description`, `created_at`, `updated_at`) VALUES
(1, 'Manager', 'documents/2026/07/mM8dtODnva0gyvaYRqVrdx01a9rQ7xExaCICWApg.png', 'png', 162059, 'App\\Models\\Project', 1, 1, NULL, '2026-07-13 18:09:53', '2026-07-13 18:09:53'),
(2, 'Koye Face', 'documents/2026/07/HbqkcNDct32iPBPwTMaXZxMvsW1cK2rPm2AyzPl1.png', 'png', 1180607, 'App\\Models\\Project', 1, 1, NULL, '2026-07-13 18:10:56', '2026-07-13 18:10:56');

-- --------------------------------------------------------

--
-- Table structure for table `equipment_resources`
--

CREATE TABLE `equipment_resources` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `boq_item_id` bigint(20) UNSIGNED NOT NULL,
  `description` varchar(255) NOT NULL,
  `duration_days` decimal(10,2) DEFAULT NULL,
  `number_of_units` int(11) NOT NULL DEFAULT 1,
  `total_hours` decimal(10,2) NOT NULL DEFAULT 0.00,
  `rate_per_hour` decimal(15,2) NOT NULL DEFAULT 0.00,
  `amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `equipment_resources`
--

INSERT INTO `equipment_resources` (`id`, `boq_item_id`, `description`, `duration_days`, `number_of_units`, `total_hours`, `rate_per_hour`, `amount`, `created_at`, `updated_at`) VALUES
(1, 3, 'Excavator (1.15m3 capacity)', 5.00, 1, 40.00, 1500.00, 60000.00, '2026-07-13 17:04:07', '2026-07-13 17:04:07'),
(2, 4, 'Torch Set', 30.00, 10, 2400.00, 50.00, 120000.00, '2026-07-13 17:04:07', '2026-07-13 17:04:07'),
(3, 5, 'Loader', 2.00, 1, 16.00, 2500.00, 40000.00, '2026-07-13 17:04:07', '2026-07-13 17:04:07'),
(4, 7, 'Concrete Pump', 10.00, 1, 80.00, 3000.00, 240000.00, '2026-07-13 17:04:07', '2026-07-13 17:04:07'),
(5, 10, 'hhh', 1.00, 1, 8.00, 5.00, 40.00, '2026-07-15 18:56:23', '2026-07-15 18:56:23'),
(10, 11, 'Excavator', 4.00, 1, 32.00, 3500.00, 112000.00, '2026-07-15 19:16:39', '2026-07-15 19:16:39'),
(11, 11, 'Concrete Mixer', 8.00, 2, 128.00, 900.00, 115200.00, '2026-07-15 19:16:39', '2026-07-15 19:16:39'),
(12, 11, 'Plate Compactor', 3.00, 1, 24.00, 650.00, 15600.00, '2026-07-15 19:16:39', '2026-07-15 19:16:39'),
(13, 11, 'Water Pump', 5.00, 1, 40.00, 500.00, 20000.00, '2026-07-15 19:16:39', '2026-07-15 19:16:39');

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` varchar(255) NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ipcs`
--

CREATE TABLE `ipcs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `project_id` bigint(20) UNSIGNED NOT NULL,
  `subcontractor_id` bigint(20) UNSIGNED NOT NULL,
  `ipc_number` varchar(255) NOT NULL,
  `issue_number` int(11) NOT NULL DEFAULT 1,
  `ipc_date` date NOT NULL,
  `period_start_date` date NOT NULL,
  `period_end_date` date NOT NULL,
  `total_previous_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `total_current_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `total_to_date_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `retention_percentage` decimal(5,2) NOT NULL DEFAULT 5.00,
  `retention_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `net_payment_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `remarks` text DEFAULT NULL,
  `status` varchar(20) DEFAULT 'draft',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `prepared_by` varchar(255) DEFAULT NULL,
  `prepared_at` timestamp NULL DEFAULT NULL,
  `checked_by` varchar(255) DEFAULT NULL,
  `checked_at` timestamp NULL DEFAULT NULL,
  `submitted_by` varchar(255) DEFAULT NULL,
  `submitted_at` timestamp NULL DEFAULT NULL,
  `approved_by` varchar(255) DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `paid_by` varchar(255) DEFAULT NULL,
  `paid_at` timestamp NULL DEFAULT NULL,
  `rejected_by` varchar(255) DEFAULT NULL,
  `rejected_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `ipcs`
--

INSERT INTO `ipcs` (`id`, `project_id`, `subcontractor_id`, `ipc_number`, `issue_number`, `ipc_date`, `period_start_date`, `period_end_date`, `total_previous_amount`, `total_current_amount`, `total_to_date_amount`, `retention_percentage`, `retention_amount`, `net_payment_amount`, `remarks`, `status`, `created_at`, `updated_at`, `deleted_at`, `prepared_by`, `prepared_at`, `checked_by`, `checked_at`, `submitted_by`, `submitted_at`, `approved_by`, `approved_at`, `paid_by`, `paid_at`, `rejected_by`, `rejected_at`) VALUES
(1, 1, 1, 'IPC-01', 1, '2023-07-31', '2023-07-01', '2023-07-31', 0.00, 2300000.00, 2300000.00, 5.00, 115000.00, 2185000.00, '40% Completed', 'paid', '2026-07-13 17:04:07', '2026-07-15 17:44:52', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2, 1, 1, 'IPC-02', 2, '2023-09-30', '2023-09-01', '2023-09-30', 2300000.00, 1725000.00, 4025000.00, 5.00, 201250.00, 3823750.00, '70% Completed', 'paid', '2026-07-13 17:04:07', '2026-07-15 17:48:19', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(3, 2, 3, 'IPC-01', 1, '2023-08-31', '2023-08-01', '2023-08-31', 0.00, 562500.00, 562500.00, 5.00, 28125.00, 534375.00, '50% Completed', 'paid', '2026-07-13 17:04:07', '2026-07-13 18:29:34', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(4, 1, 1, 'IPC-004', 1, '2026-07-13', '2025-01-14', '2028-05-15', 0.00, 0.00, 0.00, 5.00, 0.00, 0.00, NULL, 'paid', '2026-07-13 18:46:47', '2026-07-13 18:52:31', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(6, 2, 3, 'IPC-005', 1, '2026-07-15', '2026-07-01', '2026-07-31', 0.00, 0.00, 0.00, 5.00, 0.00, 0.00, NULL, 'approved', '2026-07-15 17:57:59', '2026-07-15 18:30:29', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `ipc_items`
--

CREATE TABLE `ipc_items` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `ipc_id` bigint(20) UNSIGNED NOT NULL,
  `boq_item_id` bigint(20) UNSIGNED NOT NULL,
  `contract_quantity` decimal(15,4) NOT NULL DEFAULT 0.0000,
  `contract_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `previous_quantity` decimal(15,4) NOT NULL DEFAULT 0.0000,
  `previous_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `current_quantity` decimal(15,4) NOT NULL DEFAULT 0.0000,
  `current_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `to_date_quantity` decimal(15,4) NOT NULL DEFAULT 0.0000,
  `to_date_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `percentage_complete` decimal(5,2) NOT NULL DEFAULT 0.00,
  `remark` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `ipc_items`
--

INSERT INTO `ipc_items` (`id`, `ipc_id`, `boq_item_id`, `contract_quantity`, `contract_amount`, `previous_quantity`, `previous_amount`, `current_quantity`, `current_amount`, `to_date_quantity`, `to_date_amount`, `percentage_complete`, `remark`, `created_at`, `updated_at`) VALUES
(1, 1, 4, 5000.0000, 5750000.00, 0.0000, 0.00, 2000.0000, 2300000.00, 2000.0000, 2300000.00, 40.00, '40% Paid', '2026-07-13 17:04:07', '2026-07-13 17:04:07'),
(2, 2, 4, 5000.0000, 5750000.00, 2000.0000, 2300000.00, 1500.0000, 1725000.00, 3500.0000, 4025000.00, 70.00, '70% Paid', '2026-07-13 17:04:07', '2026-07-13 17:04:07'),
(3, 3, 9, 2500.0000, 1125000.00, 0.0000, 0.00, 1250.0000, 562500.00, 1250.0000, 562500.00, 50.00, 'First payment', '2026-07-13 17:04:07', '2026-07-13 17:04:07'),
(4, 4, 3, 5234.0000, 392550.00, 0.0000, 0.00, 0.0000, 0.00, 0.0000, 0.00, 0.00, NULL, '2026-07-13 18:46:47', '2026-07-13 18:46:47'),
(5, 4, 5, 5351.0000, 668875.00, 0.0000, 0.00, 0.0000, 0.00, 0.0000, 0.00, 0.00, NULL, '2026-07-13 18:46:47', '2026-07-13 18:46:47'),
(6, 4, 4, 5000.0000, 5750000.00, 0.0000, 0.00, 0.0000, 0.00, 0.0000, 0.00, 0.00, NULL, '2026-07-13 18:46:47', '2026-07-13 18:46:47'),
(7, 6, 7, 450.0000, 3825000.00, 0.0000, 0.00, 0.0000, 0.00, 0.0000, 0.00, 0.00, NULL, '2026-07-15 17:57:59', '2026-07-15 17:57:59'),
(8, 6, 9, 2500.0000, 1125000.00, 0.0000, 0.00, 0.0000, 0.00, 0.0000, 0.00, 0.00, NULL, '2026-07-15 17:57:59', '2026-07-15 17:57:59');

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` smallint(5) UNSIGNED NOT NULL,
  `reserved_at` int(10) UNSIGNED DEFAULT NULL,
  `available_at` int(10) UNSIGNED NOT NULL,
  `created_at` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `labor_resources`
--

CREATE TABLE `labor_resources` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `boq_item_id` bigint(20) UNSIGNED NOT NULL,
  `trade_name` varchar(255) NOT NULL,
  `number_of_workers` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total_hours` decimal(10,2) NOT NULL DEFAULT 0.00,
  `wage_per_day` decimal(10,2) NOT NULL DEFAULT 0.00,
  `amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `labor_resources`
--

INSERT INTO `labor_resources` (`id`, `boq_item_id`, `trade_name`, `number_of_workers`, `total_hours`, `wage_per_day`, `amount`, `created_at`, `updated_at`) VALUES
(1, 3, 'Equipment Operator', 1.00, 40.00, 800.00, 4000.00, '2026-07-13 17:04:07', '2026-07-13 17:04:07'),
(2, 3, 'Grease Boy', 1.00, 40.00, 400.00, 2000.00, '2026-07-13 17:04:07', '2026-07-13 17:04:07'),
(3, 4, 'Water Proofing Applicator', 10.00, 2400.00, 800.00, 240000.00, '2026-07-13 17:04:07', '2026-07-13 17:04:07'),
(4, 4, 'Assistant', 5.00, 1200.00, 500.00, 75000.00, '2026-07-13 17:04:07', '2026-07-13 17:04:07'),
(5, 5, 'Assistant Foreman', 1.00, 16.00, 600.00, 1200.00, '2026-07-13 17:04:07', '2026-07-13 17:04:07'),
(6, 7, 'Carpenter', 8.00, 2880.00, 700.00, 252000.00, '2026-07-13 17:04:07', '2026-07-13 17:04:07'),
(7, 7, 'Steel Fixer', 6.00, 2160.00, 750.00, 202500.00, '2026-07-13 17:04:07', '2026-07-13 17:04:07'),
(8, 9, 'Electrician', 4.00, 1920.00, 900.00, 216000.00, '2026-07-13 17:04:07', '2026-07-13 17:04:07'),
(9, 10, 'rzsggggg', 1.00, 8.00, 1000.00, 1000.00, '2026-07-15 18:56:23', '2026-07-15 18:56:23'),
(14, 11, 'Cement OPCMason', 6.00, 576.00, 900.00, 388800.00, '2026-07-15 19:16:39', '2026-07-15 19:16:39'),
(15, 11, 'Carpenter', 4.00, 384.00, 950.00, 182400.00, '2026-07-15 19:16:39', '2026-07-15 19:16:39'),
(16, 11, 'Steel Fixer', 5.00, 480.00, 1000.00, 300000.00, '2026-07-15 19:16:39', '2026-07-15 19:16:39'),
(17, 11, 'General Laborer', 8.00, 768.00, 700.00, 537600.00, '2026-07-15 19:16:39', '2026-07-15 19:16:39'),
(20, 1, 'Cement OPCMason', 1.00, 8.00, 500.00, 500.00, '2026-07-15 19:18:02', '2026-07-15 19:18:02');

-- --------------------------------------------------------

--
-- Table structure for table `material_resources`
--

CREATE TABLE `material_resources` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `boq_item_id` bigint(20) UNSIGNED NOT NULL,
  `description` varchar(255) NOT NULL,
  `unit` varchar(255) NOT NULL,
  `quantity` decimal(15,4) NOT NULL DEFAULT 0.0000,
  `unit_rate` decimal(15,2) NOT NULL DEFAULT 0.00,
  `amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `material_resources`
--

INSERT INTO `material_resources` (`id`, `boq_item_id`, `description`, `unit`, `quantity`, `unit_rate`, `amount`, `created_at`, `updated_at`) VALUES
(1, 4, 'APP Modified Water Proofing Membrane (4mm)', 'roll', 250.0000, 15000.00, 3750000.00, '2026-07-13 17:04:07', '2026-07-13 17:04:07'),
(2, 4, 'Primer', 'liter', 1000.0000, 350.00, 350000.00, '2026-07-13 17:04:07', '2026-07-13 17:04:07'),
(3, 4, 'Gas for torch', 'cylinder', 50.0000, 2500.00, 125000.00, '2026-07-13 17:04:07', '2026-07-13 17:04:07'),
(4, 5, 'Select Material', 'm3', 5725.5700, 460.00, 2633762.20, '2026-07-13 17:04:07', '2026-07-13 17:04:07'),
(5, 7, 'Ready Mix Concrete C-25', 'm3', 450.0000, 5500.00, 2475000.00, '2026-07-13 17:04:07', '2026-07-13 17:04:07'),
(6, 7, 'Reinforcement Steel', 'kg', 45000.0000, 85.00, 3825000.00, '2026-07-13 17:04:07', '2026-07-13 17:04:07'),
(7, 9, 'PVC Conduit 20mm', 'm', 2500.0000, 45.00, 112500.00, '2026-07-13 17:04:07', '2026-07-13 17:04:07'),
(8, 9, 'Electrical Wire 2.5mm²', 'm', 5000.0000, 35.00, 175000.00, '2026-07-13 17:04:07', '2026-07-13 17:04:07'),
(9, 10, 'gggg', '', 1.0000, 5.00, 5.00, '2026-07-15 18:56:23', '2026-07-15 18:56:23'),
(15, 11, 'Cement OPC', 'bag', 800.0000, 1150.00, 920000.00, '2026-07-15 19:16:39', '2026-07-15 19:16:39'),
(16, 11, 'Sand', 'm2', 60.0000, 1900.00, 114000.00, '2026-07-15 19:16:39', '2026-07-15 19:16:39'),
(17, 11, 'Crushed Aggregate', 'm2', 90.0000, 2300.00, 207000.00, '2026-07-15 19:16:39', '2026-07-15 19:16:39'),
(18, 11, 'Reinforcement Steel 16mm', 'kg', 950.0000, 108.00, 102600.00, '2026-07-15 19:16:39', '2026-07-15 19:16:39'),
(19, 11, 'Binding Wire', 'kg', 150.0000, 170.00, 25500.00, '2026-07-15 19:16:39', '2026-07-15 19:16:39');

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2024_01_01_000011_add_fields_to_users_table', 1),
(5, '2024_01_01_000012_create_activity_logs_table', 1),
(6, '2024_01_01_000013_create_settings_table', 1),
(7, '2024_01_01_000030_create_roles_table', 1),
(8, '2024_01_01_000031_add_profile_fields_to_users', 1),
(9, '2026_07_13_182620_create_projects_table', 1),
(10, '2026_07_13_182715_create_subcontractors_table', 1),
(11, '2026_07_13_182734_create_cost_categories_table', 1),
(12, '2026_07_13_182751_create_boq_items_table', 1),
(13, '2026_07_13_182813_create_labor_resources_table', 1),
(14, '2026_07_13_182827_create_material_resources_table', 1),
(15, '2026_07_13_182852_create_equipment_resources_table', 1),
(16, '2026_07_13_182907_create_ipcs_table', 1),
(17, '2026_07_13_182923_create_ipc_items_table', 1),
(18, '2026_07_13_182941_create_project_subcontractor_table', 1),
(19, '2024_01_01_000040_create_tax_settings_table', 2),
(20, '2024_01_01_000050_create_actual_costs_table', 3),
(21, '2024_01_01_000060_create_documents_table', 4),
(22, '2024_01_01_000070_create_notifications_table', 5),
(23, '2024_01_01_000080_add_approval_fields_to_ipcs', 6),
(24, '2024_01_01_000090_update_ipc_status_enum', 7),
(25, '2024_01_01_000100_add_rejected_fields_to_ipcs', 8),
(26, '2024_01_01_000110_create_workflow_permissions_table', 9),
(27, '2024_01_01_000120_create_project_user_table', 10);

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `type` varchar(255) NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `icon` varchar(255) NOT NULL DEFAULT 'bell',
  `color` varchar(255) NOT NULL DEFAULT 'primary',
  `link` varchar(255) DEFAULT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `type`, `title`, `message`, `icon`, `color`, `link`, `is_read`, `read_at`, `created_at`, `updated_at`) VALUES
(1, 1, 'ipc_submitted', 'IPC-001 Submitted for Approval', 'Water proofing IPC has been submitted and requires your approval.', 'file-invoice', 'warning', '/ipcs/1', 1, '2026-07-13 18:15:06', '2026-07-13 18:07:01', '2026-07-13 18:15:06'),
(2, 1, 'budget_exceeded', 'Budget Alert: Site Clearance', 'Actual cost has exceeded budget for Site Clearance item.', 'exclamation-triangle', 'danger', '/boq-items/1', 1, '2026-07-13 18:13:16', '2026-07-13 18:07:01', '2026-07-13 18:13:16'),
(3, 1, 'deadline', 'Project Deadline Approaching', 'Megenagna Bus Terminal project due in 15 days.', 'clock', 'info', '/projects/1', 1, '2026-07-13 18:13:22', '2026-07-13 18:07:01', '2026-07-13 18:13:22'),
(4, 1, 'ipc_submitted', 'IPC-001 Submitted for Approval', 'Water proofing IPC has been submitted and requires your approval.', 'file-invoice', 'warning', '/ipcs/1', 1, '2026-07-15 17:32:58', '2026-07-13 18:14:53', '2026-07-15 17:32:58'),
(5, 1, 'budget_exceeded', 'Budget Alert: Site Clearance', 'Actual cost has exceeded budget for Site Clearance item.', 'exclamation-triangle', 'danger', '/boq-items/1', 1, '2026-07-13 18:15:04', '2026-07-13 18:14:53', '2026-07-13 18:15:04'),
(6, 1, 'deadline', 'Project Deadline Approaching', 'Megenagna Bus Terminal project due in 15 days.', 'clock', 'info', '/projects/1', 1, '2026-07-13 19:18:55', '2026-07-13 18:14:53', '2026-07-13 19:18:55'),
(7, 2, 'ipc_submitted', 'IPC Submitted', 'IPC IPC-004 submitted by System Administrator', 'file-invoice', 'warning', 'http://127.0.0.1:8001/ipcs/4', 1, '2026-07-15 19:20:14', '2026-07-13 18:52:19', '2026-07-15 19:20:14'),
(8, 1, 'ipc_approved', 'IPC Approved', 'IPC IPC-004 approved by System Administrator', 'check-circle', 'success', 'http://127.0.0.1:8001/ipcs/4', 1, '2026-07-13 18:53:29', '2026-07-13 18:52:26', '2026-07-13 18:53:29'),
(9, 2, 'ipc_approved', 'IPC Approved', 'IPC IPC-004 approved by System Administrator', 'check-circle', 'success', 'http://127.0.0.1:8001/ipcs/4', 1, '2026-07-15 19:20:14', '2026-07-13 18:52:26', '2026-07-15 19:20:14'),
(10, 3, 'ipc_approved', 'IPC Approved', 'IPC IPC-004 approved by System Administrator', 'check-circle', 'success', 'http://127.0.0.1:8001/ipcs/4', 1, '2026-07-13 19:19:56', '2026-07-13 18:52:26', '2026-07-13 19:19:56'),
(11, 4, 'ipc_approved', 'IPC Approved', 'IPC IPC-004 approved by System Administrator', 'check-circle', 'success', 'http://127.0.0.1:8001/ipcs/4', 0, NULL, '2026-07-13 18:52:26', '2026-07-13 18:52:26'),
(12, 5, 'ipc_approved', 'IPC Approved', 'IPC IPC-004 approved by System Administrator', 'check-circle', 'success', 'http://127.0.0.1:8001/ipcs/4', 0, NULL, '2026-07-13 18:52:26', '2026-07-13 18:52:26'),
(13, 2, 'ipc_submitted', 'Certificate Submitted', 'IPC IPC-005 submitted for approval', 'file-invoice', 'warning', 'http://127.0.0.1:8000/ipcs/6', 1, '2026-07-15 19:20:14', '2026-07-15 18:04:04', '2026-07-15 19:20:14'),
(14, 1, 'ipc_approved', 'Certificate Approved', 'IPC IPC-005 has been approved', 'check-circle', 'success', 'http://127.0.0.1:8000/ipcs/6', 1, '2026-07-15 18:30:41', '2026-07-15 18:30:29', '2026-07-15 18:30:41'),
(15, 2, 'ipc_approved', 'Certificate Approved', 'IPC IPC-005 has been approved', 'check-circle', 'success', 'http://127.0.0.1:8000/ipcs/6', 1, '2026-07-15 19:20:19', '2026-07-15 18:30:29', '2026-07-15 19:20:19'),
(16, 3, 'ipc_approved', 'Certificate Approved', 'IPC IPC-005 has been approved', 'check-circle', 'success', 'http://127.0.0.1:8000/ipcs/6', 0, NULL, '2026-07-15 18:30:29', '2026-07-15 18:30:29'),
(17, 4, 'ipc_approved', 'Certificate Approved', 'IPC IPC-005 has been approved', 'check-circle', 'success', 'http://127.0.0.1:8000/ipcs/6', 0, NULL, '2026-07-15 18:30:29', '2026-07-15 18:30:29'),
(18, 5, 'ipc_approved', 'Certificate Approved', 'IPC IPC-005 has been approved', 'check-circle', 'success', 'http://127.0.0.1:8000/ipcs/6', 0, NULL, '2026-07-15 18:30:29', '2026-07-15 18:30:29');

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `projects`
--

CREATE TABLE `projects` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `client_name` varchar(255) DEFAULT NULL,
  `contractor_name` varchar(255) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `contract_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `description` text DEFAULT NULL,
  `status` enum('active','completed','on_hold','cancelled') NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `projects`
--

INSERT INTO `projects` (`id`, `name`, `client_name`, `contractor_name`, `start_date`, `end_date`, `contract_amount`, `description`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Addis Ababa Corridor Development – Megenagna Bus & Taxi Terminal', 'Addis Ababa City Administration', 'TNT Construction and Trading', '2023-01-15', '2024-06-30', 50000000.00, 'Construction of Megenagna Bus & Taxi Terminal including foundation, superstructure, finishing works, and external works.', 'active', '2026-07-13 17:04:07', '2026-07-13 17:04:07', NULL),
(2, 'Axolon Engineering Head Office Building', 'Axolon Engineering PLC', 'Axolon Construction', '2023-03-01', '2024-09-30', 35000000.00, 'Design and construction of 5-story office building with basement parking.', 'active', '2026-07-13 17:04:07', '2026-07-13 17:04:07', NULL),
(3, 'Bole Residential Apartments', 'Bole Homes PLC', 'TNT Construction and Trading', '2022-06-01', '2023-12-31', 25000000.00, 'Construction of 3 residential apartment blocks.', 'completed', '2026-07-13 17:04:07', '2026-07-13 17:04:07', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `project_subcontractor`
--

CREATE TABLE `project_subcontractor` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `project_id` bigint(20) UNSIGNED NOT NULL,
  `subcontractor_id` bigint(20) UNSIGNED NOT NULL,
  `contract_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `contract_start_date` date DEFAULT NULL,
  `contract_end_date` date DEFAULT NULL,
  `scope_of_work` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `project_subcontractor`
--

INSERT INTO `project_subcontractor` (`id`, `project_id`, `subcontractor_id`, `contract_amount`, `contract_start_date`, `contract_end_date`, `scope_of_work`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 5750000.00, '2023-06-01', '2024-06-30', 'Water proofing works for foundation and basement', '2026-07-13 17:04:07', '2026-07-13 17:04:07'),
(3, 2, 3, 4500000.00, '2023-06-01', '2024-08-31', 'Complete electrical installation', '2026-07-13 17:04:07', '2026-07-13 17:04:07'),
(4, 2, 4, 2800000.00, '2023-08-01', '2024-07-31', 'Plastering and finishing works', '2026-07-13 17:04:07', '2026-07-13 17:04:07'),
(5, 1, 2, 5000000.00, NULL, NULL, NULL, '2026-07-13 19:03:31', '2026-07-13 19:03:49');

-- --------------------------------------------------------

--
-- Table structure for table `project_user`
--

CREATE TABLE `project_user` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `project_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `role` varchar(255) NOT NULL,
  `assigned_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `responsibilities` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `project_user`
--

INSERT INTO `project_user` (`id`, `project_id`, `user_id`, `role`, `assigned_date`, `end_date`, `is_active`, `responsibilities`, `created_at`, `updated_at`) VALUES
(1, 1, 2, 'project_manager', '2026-07-15', NULL, 1, NULL, '2026-07-15 19:18:52', '2026-07-15 19:18:52');

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `display_name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `permissions` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`permissions`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `name`, `display_name`, `description`, `permissions`, `created_at`, `updated_at`) VALUES
(1, 'admin', 'Administrator', 'Full system access', '[\"ipc.approve\"]', '2026-07-13 17:04:06', '2026-07-13 18:49:18'),
(2, 'manager', 'Project Manager', 'Can manage projects, BOQ, IPCs, and subcontractors', '[\"projects.view\",\"projects.create\",\"projects.edit\",\"projects.delete\",\"boq.view\",\"boq.create\",\"boq.edit\",\"boq.delete\",\"ipc.view\",\"ipc.create\",\"ipc.approve\",\"subcontractors.view\",\"subcontractors.create\",\"subcontractors.edit\",\"cost-categories.view\",\"cost-categories.create\",\"cost-categories.edit\",\"reports.view\",\"reports.export\",\"users.view\"]', '2026-07-13 17:04:06', '2026-07-13 17:04:06'),
(3, 'engineer', 'Engineer / QS', 'Can create BOQ items and IPCs', '[\"projects.view\",\"boq.view\",\"boq.create\",\"boq.edit\",\"ipc.view\",\"ipc.create\",\"subcontractors.view\",\"cost-categories.view\",\"reports.view\"]', '2026-07-13 17:04:06', '2026-07-13 17:04:06'),
(4, 'finance', 'Finance Officer', 'Can view and approve IPCs, access reports', '[\"projects.view\",\"boq.view\",\"ipc.view\",\"ipc.approve\",\"reports.view\",\"reports.export\"]', '2026-07-13 17:04:06', '2026-07-13 17:04:06'),
(5, 'viewer', 'Viewer', 'Read-only access', '[\"projects.view\",\"boq.view\",\"ipc.view\",\"reports.view\"]', '2026-07-13 17:04:06', '2026-07-13 17:04:06');

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('ZFZU8Lv7V4JP4dCPTySJaGqnSMzrfshIoORCZ4I6', 1, '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'eyJfdG9rZW4iOiJGUmhUaDFLVG5FQ3JZcGVzNGJRc3hCWTBrVUtOM1Y0aHBIUHlBVW14IiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4MDAwXC9wcm9qZWN0cyIsInJvdXRlIjoicHJvamVjdHMuaW5kZXgifSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119LCJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI6MX0=', 1784154452);

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `key` varchar(255) NOT NULL,
  `value` text DEFAULT NULL,
  `group` varchar(255) NOT NULL DEFAULT 'general',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `subcontractors`
--

CREATE TABLE `subcontractors` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `contact_person` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `tax_id` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `subcontractors`
--

INSERT INTO `subcontractors` (`id`, `name`, `contact_person`, `email`, `phone`, `address`, `tax_id`, `is_active`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Amare Water Proofing PLC', 'Amare Abebe', 'amare@waterproofing.com', '+251911234567', 'Bole Sub City, Addis Ababa', 'TIN-001234567', 1, '2026-07-13 17:04:07', '2026-07-13 17:04:07', NULL),
(2, 'Ethio Steel Works', 'Solomon Haile', 'solomon@ethiosteel.com', '+251922345678', 'Akaki Kality, Addis Ababa', 'TIN-002345678', 1, '2026-07-13 17:04:07', '2026-07-13 17:04:07', NULL),
(3, 'Mekonnen Electrical Installation', 'Mekonnen Tesfaye', 'mekonnen@electrical.com', '+251933456789', 'Megenagna, Addis Ababa', 'TIN-003456789', 1, '2026-07-13 17:04:07', '2026-07-13 17:04:07', NULL),
(4, 'Addis Plastering & Finishing', 'Dawit Girma', 'dawit@plastering.com', '+251944567890', 'CMC, Addis Ababa', 'TIN-004567890', 1, '2026-07-13 17:04:07', '2026-07-13 17:04:07', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `tax_settings`
--

CREATE TABLE `tax_settings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `key` varchar(255) NOT NULL,
  `display_name` varchar(255) NOT NULL,
  `rate` decimal(8,2) NOT NULL DEFAULT 0.00,
  `type` varchar(255) NOT NULL DEFAULT 'percentage',
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tax_settings`
--

INSERT INTO `tax_settings` (`id`, `key`, `display_name`, `rate`, `type`, `description`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'vat', 'VAT (Value Added Tax)', 15.00, 'percentage', 'Standard VAT rate applied to all certificates', 1, '2026-07-13 17:39:51', '2026-07-13 17:39:51'),
(2, 'retention', 'Retention Fee', 5.00, 'percentage', 'Retention percentage held from each payment', 1, '2026-07-13 17:39:51', '2026-07-13 17:39:51'),
(3, 'withholding_tax', 'Withholding Tax', 3.00, 'percentage', 'Tax withheld at source', 1, '2026-07-13 17:39:51', '2026-07-15 18:08:02'),
(4, 'service_charge', 'Service Charge', 0.00, 'percentage', 'Additional service charge', 1, '2026-07-13 17:39:51', '2026-07-15 18:08:13');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `role_id` bigint(20) UNSIGNED DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `username` varchar(255) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `role` varchar(255) NOT NULL DEFAULT 'engineer',
  `department` varchar(255) DEFAULT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `position` varchar(255) DEFAULT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `last_login_at` timestamp NULL DEFAULT NULL,
  `last_login_ip` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `role_id`, `name`, `username`, `email`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`, `role`, `department`, `phone`, `is_active`, `deleted_at`, `position`, `avatar`, `last_login_at`, `last_login_ip`) VALUES
(1, 1, 'System Administrator', NULL, 'admin@cms.com', NULL, '$2y$12$DCvhWevR2Gp/AjoxCk8WDuPgX.D4C5g6FXjaoJmKlEFdcwHRqgBv2', NULL, '2026-07-13 17:04:07', '2026-07-15 19:21:42', 'engineer', 'IT Department', NULL, 1, NULL, 'System Administrator', 'avatars/oXS2CBcJmrbY1Jnsy8LkkPSjE6MaENBlzwYS5nfd.png', '2026-07-15 19:21:42', '127.0.0.1'),
(2, 2, 'Abebe Kebede', NULL, 'manager@cms.com', NULL, '$2y$12$Poh0vYLFZMSYnSoxJVd3zuhj3/YJ.bmb/Z/OZ7Tvo3Q8RHZ7uUNqK', NULL, '2026-07-13 17:04:07', '2026-07-15 19:19:50', 'engineer', 'Project Management', NULL, 1, NULL, 'Senior Project Manager', NULL, '2026-07-15 19:19:50', '127.0.0.1'),
(3, 3, 'Tigist Haile', NULL, 'engineer@cms.com', NULL, '$2y$12$7beEP9daZ/WiLLCzL5/1BuSfjtni3C7A3bv3weEWQP8sNrrwhZDnm', NULL, '2026-07-13 17:04:07', '2026-07-13 19:19:50', 'engineer', 'Engineering', NULL, 1, NULL, 'Quantity Surveyor', NULL, '2026-07-13 19:19:50', '127.0.0.1'),
(4, 4, 'Meron Alemu', NULL, 'finance@cms.com', NULL, '$2y$12$MJRqwScC8.Zr1BTb.AaKZuNfVrN8MJm7l.6exl99n1MzYKAaXaYB6', NULL, '2026-07-13 17:04:07', '2026-07-13 17:04:07', 'engineer', 'Finance', NULL, 1, NULL, 'Finance Officer', NULL, NULL, NULL),
(5, 5, 'Bereket Tadesse', NULL, 'viewer@cms.com', NULL, '$2y$12$3Jr1HJQRBUY74H2UdCTgD.ChQVAh7gxUkM20r7K1mrVaXGNk2nlRe', NULL, '2026-07-13 17:04:07', '2026-07-13 17:04:07', 'engineer', 'Management', NULL, 1, NULL, 'Stakeholder', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `workflow_permissions`
--

CREATE TABLE `workflow_permissions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `workflow_step` varchar(255) NOT NULL,
  `can_act` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `workflow_permissions`
--

INSERT INTO `workflow_permissions` (`id`, `user_id`, `workflow_step`, `can_act`, `created_at`, `updated_at`) VALUES
(78, 1, 'prepare', 1, '2026-07-15 18:46:19', '2026-07-15 18:46:19'),
(79, 1, 'check', 1, '2026-07-15 18:46:19', '2026-07-15 18:46:19'),
(80, 1, 'submit', 1, '2026-07-15 18:46:19', '2026-07-15 18:46:19'),
(81, 1, 'approve', 1, '2026-07-15 18:46:19', '2026-07-15 18:46:19'),
(82, 1, 'reject', 1, '2026-07-15 18:46:19', '2026-07-15 18:46:19'),
(83, 1, 'pay', 1, '2026-07-15 18:46:19', '2026-07-15 18:46:19'),
(84, 2, 'prepare', 1, '2026-07-15 18:46:19', '2026-07-15 18:46:19'),
(85, 2, 'check', 1, '2026-07-15 18:46:19', '2026-07-15 18:46:19'),
(86, 2, 'submit', 1, '2026-07-15 18:46:19', '2026-07-15 18:46:19'),
(87, 3, 'prepare', 1, '2026-07-15 18:46:19', '2026-07-15 18:46:19'),
(88, 3, 'check', 1, '2026-07-15 18:46:19', '2026-07-15 18:46:19'),
(89, 3, 'submit', 1, '2026-07-15 18:46:19', '2026-07-15 18:46:19'),
(90, 4, 'approve', 1, '2026-07-15 18:46:19', '2026-07-15 18:46:19'),
(91, 4, 'reject', 1, '2026-07-15 18:46:19', '2026-07-15 18:46:19'),
(92, 4, 'pay', 1, '2026-07-15 18:46:19', '2026-07-15 18:46:19');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `activity_logs_user_id_foreign` (`user_id`);

--
-- Indexes for table `actual_costs`
--
ALTER TABLE `actual_costs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `actual_costs_project_id_foreign` (`project_id`),
  ADD KEY `actual_costs_boq_item_id_foreign` (`boq_item_id`),
  ADD KEY `actual_costs_created_by_foreign` (`created_by`);

--
-- Indexes for table `boq_items`
--
ALTER TABLE `boq_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `boq_items_project_id_foreign` (`project_id`),
  ADD KEY `boq_items_cost_category_id_foreign` (`cost_category_id`),
  ADD KEY `boq_items_parent_id_foreign` (`parent_id`);

--
-- Indexes for table `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_expiration_index` (`expiration`);

--
-- Indexes for table `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_locks_expiration_index` (`expiration`);

--
-- Indexes for table `cost_categories`
--
ALTER TABLE `cost_categories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cost_categories_project_id_foreign` (`project_id`);

--
-- Indexes for table `documents`
--
ALTER TABLE `documents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `documents_documentable_type_documentable_id_index` (`documentable_type`,`documentable_id`),
  ADD KEY `documents_uploaded_by_foreign` (`uploaded_by`);

--
-- Indexes for table `equipment_resources`
--
ALTER TABLE `equipment_resources`
  ADD PRIMARY KEY (`id`),
  ADD KEY `equipment_resources_boq_item_id_foreign` (`boq_item_id`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`),
  ADD KEY `failed_jobs_connection_queue_failed_at_index` (`connection`,`queue`,`failed_at`);

--
-- Indexes for table `ipcs`
--
ALTER TABLE `ipcs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ipcs_project_id_foreign` (`project_id`),
  ADD KEY `ipcs_subcontractor_id_foreign` (`subcontractor_id`);

--
-- Indexes for table `ipc_items`
--
ALTER TABLE `ipc_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ipc_items_ipc_id_foreign` (`ipc_id`),
  ADD KEY `ipc_items_boq_item_id_foreign` (`boq_item_id`);

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
-- Indexes for table `labor_resources`
--
ALTER TABLE `labor_resources`
  ADD PRIMARY KEY (`id`),
  ADD KEY `labor_resources_boq_item_id_foreign` (`boq_item_id`);

--
-- Indexes for table `material_resources`
--
ALTER TABLE `material_resources`
  ADD PRIMARY KEY (`id`),
  ADD KEY `material_resources_boq_item_id_foreign` (`boq_item_id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `notifications_user_id_foreign` (`user_id`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `projects`
--
ALTER TABLE `projects`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `project_subcontractor`
--
ALTER TABLE `project_subcontractor`
  ADD PRIMARY KEY (`id`),
  ADD KEY `project_subcontractor_project_id_foreign` (`project_id`),
  ADD KEY `project_subcontractor_subcontractor_id_foreign` (`subcontractor_id`);

--
-- Indexes for table `project_user`
--
ALTER TABLE `project_user`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `project_user_project_id_user_id_role_unique` (`project_id`,`user_id`,`role`),
  ADD KEY `project_user_user_id_foreign` (`user_id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `roles_name_unique` (`name`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `settings_key_unique` (`key`);

--
-- Indexes for table `subcontractors`
--
ALTER TABLE `subcontractors`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tax_settings`
--
ALTER TABLE `tax_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `tax_settings_key_unique` (`key`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`),
  ADD UNIQUE KEY `users_username_unique` (`username`),
  ADD KEY `users_role_id_foreign` (`role_id`);

--
-- Indexes for table `workflow_permissions`
--
ALTER TABLE `workflow_permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `workflow_permissions_user_id_workflow_step_unique` (`user_id`,`workflow_step`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `actual_costs`
--
ALTER TABLE `actual_costs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `boq_items`
--
ALTER TABLE `boq_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `cost_categories`
--
ALTER TABLE `cost_categories`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `documents`
--
ALTER TABLE `documents`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `equipment_resources`
--
ALTER TABLE `equipment_resources`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ipcs`
--
ALTER TABLE `ipcs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `ipc_items`
--
ALTER TABLE `ipc_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `labor_resources`
--
ALTER TABLE `labor_resources`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `material_resources`
--
ALTER TABLE `material_resources`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `projects`
--
ALTER TABLE `projects`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `project_subcontractor`
--
ALTER TABLE `project_subcontractor`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `project_user`
--
ALTER TABLE `project_user`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `subcontractors`
--
ALTER TABLE `subcontractors`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `tax_settings`
--
ALTER TABLE `tax_settings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `workflow_permissions`
--
ALTER TABLE `workflow_permissions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=93;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD CONSTRAINT `activity_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `actual_costs`
--
ALTER TABLE `actual_costs`
  ADD CONSTRAINT `actual_costs_boq_item_id_foreign` FOREIGN KEY (`boq_item_id`) REFERENCES `boq_items` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `actual_costs_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `actual_costs_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `boq_items`
--
ALTER TABLE `boq_items`
  ADD CONSTRAINT `boq_items_cost_category_id_foreign` FOREIGN KEY (`cost_category_id`) REFERENCES `cost_categories` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `boq_items_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `boq_items` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `boq_items_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `cost_categories`
--
ALTER TABLE `cost_categories`
  ADD CONSTRAINT `cost_categories_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `documents`
--
ALTER TABLE `documents`
  ADD CONSTRAINT `documents_uploaded_by_foreign` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `equipment_resources`
--
ALTER TABLE `equipment_resources`
  ADD CONSTRAINT `equipment_resources_boq_item_id_foreign` FOREIGN KEY (`boq_item_id`) REFERENCES `boq_items` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `ipcs`
--
ALTER TABLE `ipcs`
  ADD CONSTRAINT `ipcs_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `ipcs_subcontractor_id_foreign` FOREIGN KEY (`subcontractor_id`) REFERENCES `subcontractors` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `ipc_items`
--
ALTER TABLE `ipc_items`
  ADD CONSTRAINT `ipc_items_boq_item_id_foreign` FOREIGN KEY (`boq_item_id`) REFERENCES `boq_items` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `ipc_items_ipc_id_foreign` FOREIGN KEY (`ipc_id`) REFERENCES `ipcs` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `labor_resources`
--
ALTER TABLE `labor_resources`
  ADD CONSTRAINT `labor_resources_boq_item_id_foreign` FOREIGN KEY (`boq_item_id`) REFERENCES `boq_items` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `material_resources`
--
ALTER TABLE `material_resources`
  ADD CONSTRAINT `material_resources_boq_item_id_foreign` FOREIGN KEY (`boq_item_id`) REFERENCES `boq_items` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `project_subcontractor`
--
ALTER TABLE `project_subcontractor`
  ADD CONSTRAINT `project_subcontractor_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `project_subcontractor_subcontractor_id_foreign` FOREIGN KEY (`subcontractor_id`) REFERENCES `subcontractors` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `project_user`
--
ALTER TABLE `project_user`
  ADD CONSTRAINT `project_user_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `project_user_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `workflow_permissions`
--
ALTER TABLE `workflow_permissions`
  ADD CONSTRAINT `workflow_permissions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
