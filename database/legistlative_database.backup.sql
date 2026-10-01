-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 05, 2026 at 05:03 AM
-- Server version: 10.4.21-MariaDB
-- PHP Version: 8.0.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `legislative_management_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity_logs`
--

CREATE TABLE `activity_logs` (
  `id` bigint(20) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `system_id` int(11) DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `details` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(500) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `activity_logs`
--

INSERT INTO `activity_logs` (`id`, `user_id`, `system_id`, `action`, `details`, `ip_address`, `user_agent`, `created_at`) VALUES
(1, 1, NULL, 'Login', 'User logged in successfully.', NULL, NULL, '2026-08-01 06:15:40'),
(2, 1, NULL, 'Logout', 'User logged out.', NULL, NULL, '2026-08-01 13:17:18'),
(3, 1, NULL, 'Login', 'User logged in successfully.', NULL, NULL, '2026-08-01 13:17:31'),
(4, 1, NULL, 'Logout', 'User logged out.', NULL, NULL, '2026-08-01 13:29:15'),
(5, 1, NULL, 'Login', 'Signed in to the Ordinance and Resolution Life Cycle Management System.', NULL, NULL, '2026-08-01 13:31:23'),
(6, 1, NULL, 'Login', 'User logged in successfully.', NULL, NULL, '2026-08-03 08:36:55'),
(7, 1, NULL, 'Logout', 'User logged out.', NULL, NULL, '2026-08-03 08:45:59'),
(8, 1, NULL, 'Login', 'User logged in successfully.', NULL, NULL, '2026-08-03 08:46:37'),
(9, 1, NULL, 'Logout', 'User logged out.', NULL, NULL, '2026-08-03 08:46:44');

-- --------------------------------------------------------

--
-- Table structure for table `attendance`
--

CREATE TABLE `attendance` (
  `id` int(11) NOT NULL,
  `stakeholder_id` int(11) NOT NULL,
  `hearing_id` int(11) NOT NULL,
  `registration_id` int(11) DEFAULT NULL,
  `status` varchar(50) DEFAULT 'Present',
  `checked_in_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `attendance_type` varchar(50) DEFAULT 'On-site',
  `check_in_method` varchar(50) DEFAULT 'Manual',
  `checked_out_at` datetime DEFAULT NULL,
  `verified_by` int(11) DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `attendance_logs`
--

CREATE TABLE `attendance_logs` (
  `id` int(11) NOT NULL,
  `stakeholder_id` int(11) NOT NULL,
  `hearing_id` int(11) NOT NULL,
  `action` varchar(100) NOT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `audit_logs`
--

CREATE TABLE `audit_logs` (
  `id` bigint(20) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `system_id` int(11) DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `entity_type` varchar(150) NOT NULL,
  `entity_id` varchar(100) DEFAULT NULL,
  `old_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`old_values`)),
  `new_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`new_values`)),
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(500) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `committees`
--

CREATE TABLE `committees` (
  `id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `office_id` int(11) DEFAULT NULL,
  `status` varchar(30) DEFAULT 'Active',
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `committee_members`
--

CREATE TABLE `committee_members` (
  `committee_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `position` varchar(100) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `assigned_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `departments`
--

CREATE TABLE `departments` (
  `id` int(11) NOT NULL,
  `office_id` int(11) DEFAULT NULL,
  `code` varchar(50) DEFAULT NULL,
  `name` varchar(200) NOT NULL,
  `status` varchar(30) DEFAULT 'Active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `documents`
--

CREATE TABLE `documents` (
  `id` int(11) NOT NULL,
  `system_id` int(11) DEFAULT NULL,
  `legislative_item_id` int(11) DEFAULT NULL,
  `document_type` varchar(100) NOT NULL,
  `title` varchar(255) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_path` varchar(500) NOT NULL,
  `version_number` int(11) DEFAULT 1,
  `visibility` varchar(30) DEFAULT 'Internal',
  `uploaded_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `feedback`
--

CREATE TABLE `feedback` (
  `id` int(11) NOT NULL,
  `hearing_id` int(11) DEFAULT NULL,
  `legislative_item_id` int(11) DEFAULT NULL,
  `stakeholder_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `name` varchar(150) NOT NULL,
  `email` varchar(150) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `message` text NOT NULL,
  `status` varchar(50) DEFAULT 'New',
  `submitted_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `feedback_position` varchar(50) DEFAULT NULL,
  `is_anonymous` tinyint(1) DEFAULT 0,
  `visibility` varchar(30) DEFAULT 'Internal',
  `validated_by` int(11) DEFAULT NULL,
  `validated_at` datetime DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `feedback_categories`
--

CREATE TABLE `feedback_categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `hearings`
--

CREATE TABLE `hearings` (
  `id` int(11) NOT NULL,
  `legislative_item_id` int(11) DEFAULT NULL,
  `reference_number` varchar(100) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `hearing_type_id` int(11) DEFAULT NULL,
  `committee_id` int(11) DEFAULT NULL,
  `venue` varchar(255) DEFAULT NULL,
  `hearing_date` date NOT NULL,
  `end_date` date DEFAULT NULL,
  `hearing_time` time NOT NULL,
  `end_time` time DEFAULT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'Upcoming',
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `registration_deadline` datetime DEFAULT NULL,
  `maximum_participants` int(11) DEFAULT NULL,
  `meeting_link` varchar(500) DEFAULT NULL,
  `visibility` varchar(30) DEFAULT 'Public',
  `created_by` int(11) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `cancelled_at` datetime DEFAULT NULL,
  `cancellation_reason` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `hearing_actions`
--

CREATE TABLE `hearing_actions` (
  `id` int(11) NOT NULL,
  `issue_id` int(11) DEFAULT NULL,
  `reference_number` varchar(100) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `assigned_office_id` int(11) DEFAULT NULL,
  `assigned_user_id` int(11) DEFAULT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'Pending',
  `deadline` date DEFAULT NULL,
  `completed_at` datetime DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `hearing_action_assignments`
--

CREATE TABLE `hearing_action_assignments` (
  `id` bigint(20) NOT NULL,
  `action_id` int(11) NOT NULL,
  `assigned_office_id` int(11) DEFAULT NULL,
  `assigned_user_id` int(11) DEFAULT NULL,
  `assigned_by` int(11) DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `assigned_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `hearing_action_documents`
--

CREATE TABLE `hearing_action_documents` (
  `id` int(11) NOT NULL,
  `action_id` int(11) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_path` varchar(500) NOT NULL,
  `document_type` varchar(100) NOT NULL DEFAULT 'Supporting Document',
  `description` text DEFAULT NULL,
  `visibility` varchar(30) NOT NULL DEFAULT 'Internal',
  `uploaded_by` int(11) DEFAULT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `hearing_action_updates`
--

CREATE TABLE `hearing_action_updates` (
  `id` bigint(20) NOT NULL,
  `action_id` int(11) NOT NULL,
  `previous_status` varchar(50) DEFAULT NULL,
  `new_status` varchar(50) DEFAULT NULL,
  `update_text` text NOT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `hearing_documents`
--

CREATE TABLE `hearing_documents` (
  `id` int(11) NOT NULL,
  `hearing_id` int(11) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_path` varchar(500) NOT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `document_type` varchar(100) DEFAULT 'Supporting Document',
  `description` text DEFAULT NULL,
  `version_number` int(11) DEFAULT 1,
  `visibility` varchar(30) DEFAULT 'Internal',
  `uploaded_by` int(11) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `hearing_issues`
--

CREATE TABLE `hearing_issues` (
  `id` int(11) NOT NULL,
  `hearing_id` int(11) DEFAULT NULL,
  `legislative_item_id` int(11) DEFAULT NULL,
  `feedback_id` int(11) DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `reference_number` varchar(100) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `priority` varchar(30) NOT NULL DEFAULT 'Medium',
  `status` varchar(50) NOT NULL DEFAULT 'Open',
  `assigned_office_id` int(11) DEFAULT NULL,
  `assigned_user_id` int(11) DEFAULT NULL,
  `due_at` datetime DEFAULT NULL,
  `closed_at` datetime DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `hearing_issue_assignments`
--

CREATE TABLE `hearing_issue_assignments` (
  `id` bigint(20) NOT NULL,
  `issue_id` int(11) NOT NULL,
  `assigned_office_id` int(11) DEFAULT NULL,
  `assigned_user_id` int(11) DEFAULT NULL,
  `assigned_by` int(11) DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `assigned_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `hearing_issue_categories`
--

CREATE TABLE `hearing_issue_categories` (
  `id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `hearing_issue_categories`
--

INSERT INTO `hearing_issue_categories` (`id`, `name`, `description`, `created_at`) VALUES
(1, 'Policy Concern', 'Concerns regarding proposed policies, ordinances or resolutions.', '2026-08-01 04:21:35'),
(2, 'Public Safety', 'Issues involving safety, security and community protection.', '2026-08-01 04:21:35'),
(3, 'Budget and Finance', 'Concerns involving funding, expenses and financial allocation.', '2026-08-01 04:21:35'),
(4, 'Environment', 'Environmental concerns raised during public consultation.', '2026-08-01 04:21:35'),
(5, 'Infrastructure', 'Issues involving roads, facilities, utilities and public infrastructure.', '2026-08-01 04:21:35'),
(6, 'Public Services', 'Concerns regarding government services and service delivery.', '2026-08-01 04:21:35'),
(7, 'Legal and Compliance', 'Issues involving legal requirements and regulatory compliance.', '2026-08-01 04:21:35'),
(8, 'Other', 'Issues that do not fall under another available category.', '2026-08-01 04:21:35');

-- --------------------------------------------------------

--
-- Table structure for table `hearing_issue_history`
--

CREATE TABLE `hearing_issue_history` (
  `id` bigint(20) NOT NULL,
  `issue_id` int(11) NOT NULL,
  `note` text NOT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `hearing_responses`
--

CREATE TABLE `hearing_responses` (
  `id` int(11) NOT NULL,
  `issue_id` int(11) NOT NULL,
  `response_text` text NOT NULL,
  `visibility` varchar(30) NOT NULL DEFAULT 'Internal',
  `prepared_by` int(11) DEFAULT NULL,
  `approved_by` int(11) DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  `published_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `hearing_types`
--

CREATE TABLE `hearing_types` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `invitations`
--

CREATE TABLE `invitations` (
  `id` int(11) NOT NULL,
  `stakeholder_id` int(11) NOT NULL,
  `hearing_id` int(11) DEFAULT NULL,
  `status` varchar(50) DEFAULT 'Pending',
  `invitation_code` varchar(100) DEFAULT NULL,
  `sent_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `invited_by` int(11) DEFAULT NULL,
  `responded_at` datetime DEFAULT NULL,
  `expires_at` datetime DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `legislative_items`
--

CREATE TABLE `legislative_items` (
  `id` int(11) NOT NULL,
  `public_id` char(36) DEFAULT NULL,
  `reference_number` varchar(100) NOT NULL,
  `item_type_id` int(11) NOT NULL,
  `origin_system_id` int(11) DEFAULT NULL,
  `originating_office_id` int(11) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `summary` text DEFAULT NULL,
  `current_status` varchar(100) NOT NULL DEFAULT 'Draft',
  `priority_level` varchar(50) DEFAULT 'Normal',
  `visibility` varchar(30) DEFAULT 'Internal',
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `legislative_item_status_history`
--

CREATE TABLE `legislative_item_status_history` (
  `id` int(11) NOT NULL,
  `legislative_item_id` int(11) NOT NULL,
  `previous_status` varchar(100) DEFAULT NULL,
  `new_status` varchar(100) NOT NULL,
  `remarks` text DEFAULT NULL,
  `changed_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `legislative_item_types`
--

CREATE TABLE `legislative_item_types` (
  `id` int(11) NOT NULL,
  `code` varchar(50) NOT NULL,
  `name` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `legislative_item_types`
--

INSERT INTO `legislative_item_types` (`id`, `code`, `name`, `description`, `created_at`) VALUES
(1, 'ordinance', 'Ordinance', NULL, '2026-08-01 04:21:34'),
(2, 'resolution', 'Resolution', NULL, '2026-08-01 04:21:34'),
(3, 'proposal', 'Citizen Proposal', NULL, '2026-08-01 04:21:34'),
(4, 'petition', 'Petition', NULL, '2026-08-01 04:21:34'),
(5, 'executive_request', 'Executive Request', NULL, '2026-08-01 04:21:34'),
(6, 'policy_matter', 'Policy Matter', NULL, '2026-08-01 04:21:34'),
(7, 'public_issue', 'Public Issue', NULL, '2026-08-01 04:21:34');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` bigint(20) NOT NULL,
  `user_id` int(11) NOT NULL,
  `system_id` int(11) DEFAULT NULL,
  `notification_type` varchar(100) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `target_url` varchar(500) DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `read_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `offices`
--

CREATE TABLE `offices` (
  `id` int(11) NOT NULL,
  `code` varchar(50) DEFAULT NULL,
  `name` varchar(200) NOT NULL,
  `office_type` varchar(100) DEFAULT NULL,
  `status` varchar(30) DEFAULT 'Active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `offices`
--

INSERT INTO `offices` (`id`, `code`, `name`, `office_type`, `status`, `created_at`, `updated_at`) VALUES
(1, 'LEG-OFFICE', 'Office of the City Council', 'Legislative', 'Active', '2026-08-01 04:21:35', '2026-08-01 04:21:35'),
(2, 'CITY-LEGAL', 'City Legal Office', 'Executive', 'Active', '2026-08-01 04:21:35', '2026-08-01 04:21:35'),
(3, 'CITY-BUDGET', 'City Budget Office', 'Executive', 'Active', '2026-08-01 04:21:35', '2026-08-01 04:21:35'),
(4, 'CITY-PLANNING', 'City Planning and Development Office', 'Executive', 'Active', '2026-08-01 04:21:35', '2026-08-01 04:21:35'),
(5, 'PUBLIC-INFO', 'Public Information Office', 'Executive', 'Active', '2026-08-01 04:21:35', '2026-08-01 04:21:35'),
(6, 'ENVI-OFFICE', 'Environment and Natural Resources Office', 'Executive', 'Active', '2026-08-01 04:21:35', '2026-08-01 04:21:35');

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

CREATE TABLE `permissions` (
  `id` int(11) NOT NULL,
  `system_id` int(11) DEFAULT NULL,
  `code` varchar(150) NOT NULL,
  `name` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `permissions`
--

INSERT INTO `permissions` (`id`, `system_id`, `code`, `name`, `description`, `created_at`) VALUES
(1, 1, 'ordinance.access', 'Ordinance and Resolution Life Cycle Management System Access', 'Allows access to the Ordinance and Resolution Life Cycle Management System.', '2026-08-01 04:21:34'),
(2, 2, 'agenda.access', 'Legislative Agenda and Calendar Management System Access', 'Allows access to the Legislative Agenda and Calendar Management System.', '2026-08-01 04:21:34'),
(3, 3, 'voting.access', 'Voting, Quorum, and Decision Support System Access', 'Allows access to the Voting, Quorum, and Decision Support System.', '2026-08-01 04:21:34'),
(4, 4, 'hearing.access', 'Public Hearing and Consultation Management System Access', 'Allows access to the Public Hearing and Consultation Management System.', '2026-08-01 04:21:34'),
(5, 5, 'citizen.access', 'Citizen Engagement and Public Feedback Management System Access', 'Allows access to the Citizen Engagement and Public Feedback Management System.', '2026-08-01 04:21:34');

-- --------------------------------------------------------

--
-- Table structure for table `qr_codes`
--

CREATE TABLE `qr_codes` (
  `id` int(11) NOT NULL,
  `stakeholder_id` int(11) NOT NULL,
  `registration_id` int(11) DEFAULT NULL,
  `hearing_id` int(11) DEFAULT NULL,
  `code_value` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` varchar(30) DEFAULT 'Active',
  `expires_at` datetime DEFAULT NULL,
  `used_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `registrations`
--

CREATE TABLE `registrations` (
  `id` int(11) NOT NULL,
  `stakeholder_id` int(11) NOT NULL,
  `hearing_id` int(11) DEFAULT NULL,
  `registered_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `registration_code` varchar(100) DEFAULT NULL,
  `registration_status` varchar(50) DEFAULT 'Pending',
  `attendance_type` varchar(50) DEFAULT 'On-site',
  `approved_by` int(11) DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  `rejection_reason` text DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `name`, `created_at`) VALUES
(1, 'Administrator', '2026-08-01 04:21:34'),
(2, 'Legislative Staff', '2026-08-01 04:21:34'),
(3, 'Committee Member', '2026-08-01 04:21:34'),
(4, 'Registered Stakeholder', '2026-08-01 04:21:34'),
(5, 'Public User', '2026-08-01 04:21:34');

-- --------------------------------------------------------

--
-- Table structure for table `role_permissions`
--

CREATE TABLE `role_permissions` (
  `role_id` int(11) NOT NULL,
  `permission_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `role_permissions`
--

INSERT INTO `role_permissions` (`role_id`, `permission_id`, `created_at`) VALUES
(1, 1, '2026-08-01 04:21:34'),
(1, 2, '2026-08-01 04:21:34'),
(1, 3, '2026-08-01 04:21:34'),
(1, 4, '2026-08-01 04:21:34'),
(1, 5, '2026-08-01 04:21:34');

-- --------------------------------------------------------

--
-- Table structure for table `stakeholders`
--

CREATE TABLE `stakeholders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `full_name` varchar(150) NOT NULL,
  `email` varchar(150) NOT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `organization` varchar(255) DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `status` varchar(50) DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `address` text DEFAULT NULL,
  `sector` varchar(150) DEFAULT NULL,
  `verified_at` datetime DEFAULT NULL,
  `verified_by` int(11) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `stakeholder_categories`
--

CREATE TABLE `stakeholder_categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `surveys`
--

CREATE TABLE `surveys` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `status` varchar(50) DEFAULT 'Active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `hearing_id` int(11) DEFAULT NULL,
  `legislative_item_id` int(11) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `opens_at` datetime DEFAULT NULL,
  `closes_at` datetime DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `survey_answers`
--

CREATE TABLE `survey_answers` (
  `id` int(11) NOT NULL,
  `submission_id` int(11) NOT NULL,
  `question_id` int(11) NOT NULL,
  `option_id` int(11) DEFAULT NULL,
  `answer_text` text DEFAULT NULL,
  `numeric_value` decimal(12,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `survey_questions`
--

CREATE TABLE `survey_questions` (
  `id` int(11) NOT NULL,
  `survey_id` int(11) NOT NULL,
  `question_text` text NOT NULL,
  `question_type` varchar(50) NOT NULL,
  `is_required` tinyint(1) DEFAULT 0,
  `sequence_number` int(11) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `survey_question_options`
--

CREATE TABLE `survey_question_options` (
  `id` int(11) NOT NULL,
  `question_id` int(11) NOT NULL,
  `option_text` varchar(255) NOT NULL,
  `sequence_number` int(11) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `survey_responses`
--

CREATE TABLE `survey_responses` (
  `id` int(11) NOT NULL,
  `survey_id` int(11) NOT NULL,
  `respondent_name` varchar(150) DEFAULT NULL,
  `respondent_email` varchar(150) DEFAULT NULL,
  `response_text` text NOT NULL,
  `submitted_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `survey_submissions`
--

CREATE TABLE `survey_submissions` (
  `id` int(11) NOT NULL,
  `survey_id` int(11) NOT NULL,
  `stakeholder_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `respondent_name` varchar(150) DEFAULT NULL,
  `respondent_email` varchar(150) DEFAULT NULL,
  `submitted_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `systems`
--

CREATE TABLE `systems` (
  `id` int(11) NOT NULL,
  `code` varchar(50) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `base_url` varchar(255) DEFAULT NULL,
  `status` varchar(30) NOT NULL DEFAULT 'Active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `systems`
--

INSERT INTO `systems` (`id`, `code`, `name`, `description`, `base_url`, `status`, `created_at`, `updated_at`) VALUES
(1, 'ordinance', 'Ordinance and Resolution Life Cycle Management System', 'Manages ordinances and resolutions from drafting through implementation and revision.', 'http://localhost/orlms', 'Active', '2026-08-01 04:21:33', '2026-08-01 07:06:35'),
(2, 'agenda', 'Legislative Agenda and Calendar Management System', 'Manages legislative priorities, schedules, meetings and deadlines.', NULL, 'Planned', '2026-08-01 04:21:33', '2026-08-01 04:21:34'),
(3, 'voting', 'Voting, Quorum, and Decision Support System', 'Manages quorum verification, voting, tallying and legislative decisions.', NULL, 'Planned', '2026-08-01 04:21:33', '2026-08-01 04:21:34'),
(4, 'hearing', 'Public Hearing and Consultation Management System', 'Manages hearings, stakeholders, attendance, feedback, issues and actions.', 'http://localhost/lph', 'Active', '2026-08-01 04:21:33', '2026-08-01 04:21:34'),
(5, 'citizen', 'Citizen Engagement and Public Feedback Management System', 'Manages public feedback, citizen proposals, complaints and official responses.', NULL, 'Planned', '2026-08-01 04:21:33', '2026-08-01 04:21:34');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(100) DEFAULT NULL,
  `full_name` varchar(150) NOT NULL,
  `email` varchar(150) NOT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role_id` int(11) NOT NULL,
  `office_id` int(11) DEFAULT NULL,
  `department_id` int(11) DEFAULT NULL,
  `status` varchar(50) DEFAULT 'Active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_login_at` datetime DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `full_name`, `email`, `phone`, `password`, `role_id`, `office_id`, `department_id`, `status`, `created_at`, `last_login_at`, `updated_at`, `deleted_at`) VALUES
(1, 'admin', 'System Administrator', 'admin@legislative.local', NULL, '$2y$12$7vQcfuIlcWb8KFRnrslnWObtFwcOcDaaorJHmGNdGARDMVvJSxyOS', 1, NULL, NULL, 'Active', '2026-08-01 04:21:34', '2026-08-01 21:31:23', '2026-08-01 13:31:23', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `user_roles`
--

CREATE TABLE `user_roles` (
  `user_id` int(11) NOT NULL,
  `role_id` int(11) NOT NULL,
  `is_primary` tinyint(1) NOT NULL DEFAULT 0,
  `assigned_by` int(11) DEFAULT NULL,
  `assigned_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `user_roles`
--

INSERT INTO `user_roles` (`user_id`, `role_id`, `is_primary`, `assigned_by`, `assigned_at`) VALUES
(1, 1, 1, 1, '2026-08-01 04:21:34');

-- --------------------------------------------------------

--
-- Table structure for table `user_system_access`
--

CREATE TABLE `user_system_access` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `system_id` int(11) NOT NULL,
  `access_level` varchar(50) DEFAULT 'Standard',
  `status` varchar(30) DEFAULT 'Active',
  `granted_by` int(11) DEFAULT NULL,
  `granted_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `user_system_access`
--

INSERT INTO `user_system_access` (`id`, `user_id`, `system_id`, `access_level`, `status`, `granted_by`, `granted_at`, `updated_at`) VALUES
(1, 1, 2, 'Administrator', 'Active', 1, '2026-08-01 04:21:34', '2026-08-01 04:21:34'),
(2, 1, 5, 'Administrator', 'Active', 1, '2026-08-01 04:21:34', '2026-08-01 04:21:34'),
(3, 1, 4, 'Administrator', 'Active', 1, '2026-08-01 04:21:34', '2026-08-01 04:21:34'),
(4, 1, 1, 'Administrator', 'Active', 1, '2026-08-01 04:21:34', '2026-08-01 04:21:34'),
(5, 1, 3, 'Administrator', 'Active', 1, '2026-08-01 04:21:34', '2026-08-01 04:21:34');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_activity_logs_user` (`user_id`),
  ADD KEY `idx_activity_logs_system` (`system_id`),
  ADD KEY `idx_activity_logs_action` (`action`),
  ADD KEY `idx_activity_logs_created_at` (`created_at`);

--
-- Indexes for table `attendance`
--
ALTER TABLE `attendance`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_attendance_stakeholder_hearing` (`stakeholder_id`,`hearing_id`),
  ADD KEY `hearing_id` (`hearing_id`),
  ADD KEY `fk_attendance_registration` (`registration_id`),
  ADD KEY `fk_attendance_verified_by` (`verified_by`);

--
-- Indexes for table `attendance_logs`
--
ALTER TABLE `attendance_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `stakeholder_id` (`stakeholder_id`),
  ADD KEY `hearing_id` (`hearing_id`);

--
-- Indexes for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_audit_entity` (`entity_type`,`entity_id`),
  ADD KEY `idx_audit_user` (`user_id`),
  ADD KEY `idx_audit_created_at` (`created_at`),
  ADD KEY `fk_audit_system` (`system_id`);

--
-- Indexes for table `committees`
--
ALTER TABLE `committees`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_committees_office` (`office_id`);

--
-- Indexes for table `committee_members`
--
ALTER TABLE `committee_members`
  ADD PRIMARY KEY (`committee_id`,`user_id`),
  ADD KEY `fk_committee_members_user` (`user_id`);

--
-- Indexes for table `departments`
--
ALTER TABLE `departments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`),
  ADD KEY `fk_departments_office` (`office_id`);

--
-- Indexes for table `documents`
--
ALTER TABLE `documents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_documents_system` (`system_id`),
  ADD KEY `fk_documents_legislative_item` (`legislative_item_id`),
  ADD KEY `fk_documents_uploaded_by` (`uploaded_by`);

--
-- Indexes for table `feedback`
--
ALTER TABLE `feedback`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category_id` (`category_id`),
  ADD KEY `fk_feedback_hearing` (`hearing_id`),
  ADD KEY `fk_feedback_legislative_item` (`legislative_item_id`),
  ADD KEY `fk_feedback_stakeholder` (`stakeholder_id`),
  ADD KEY `fk_feedback_user` (`user_id`),
  ADD KEY `fk_feedback_validated_by` (`validated_by`);

--
-- Indexes for table `feedback_categories`
--
ALTER TABLE `feedback_categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `hearings`
--
ALTER TABLE `hearings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_hearings_reference_number` (`reference_number`),
  ADD KEY `hearing_type_id` (`hearing_type_id`),
  ADD KEY `committee_id` (`committee_id`),
  ADD KEY `fk_hearings_legislative_item` (`legislative_item_id`),
  ADD KEY `fk_hearings_created_by` (`created_by`);

--
-- Indexes for table `hearing_actions`
--
ALTER TABLE `hearing_actions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `reference_number` (`reference_number`),
  ADD KEY `idx_hearing_actions_issue` (`issue_id`),
  ADD KEY `idx_hearing_actions_status` (`status`),
  ADD KEY `idx_hearing_actions_deadline` (`deadline`),
  ADD KEY `idx_hearing_actions_office` (`assigned_office_id`),
  ADD KEY `fk_hearing_actions_assigned_user` (`assigned_user_id`),
  ADD KEY `fk_hearing_actions_created_by` (`created_by`);

--
-- Indexes for table `hearing_action_assignments`
--
ALTER TABLE `hearing_action_assignments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_hearing_action_assignments_action` (`action_id`,`assigned_at`),
  ADD KEY `fk_hearing_action_assignments_office` (`assigned_office_id`),
  ADD KEY `fk_hearing_action_assignments_user` (`assigned_user_id`),
  ADD KEY `fk_hearing_action_assignments_assigned_by` (`assigned_by`);

--
-- Indexes for table `hearing_action_documents`
--
ALTER TABLE `hearing_action_documents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_hearing_action_documents_action` (`action_id`,`uploaded_at`),
  ADD KEY `fk_hearing_action_documents_user` (`uploaded_by`);

--
-- Indexes for table `hearing_action_updates`
--
ALTER TABLE `hearing_action_updates`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_hearing_action_updates_action` (`action_id`,`created_at`),
  ADD KEY `fk_hearing_action_updates_user` (`updated_by`);

--
-- Indexes for table `hearing_documents`
--
ALTER TABLE `hearing_documents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `hearing_id` (`hearing_id`),
  ADD KEY `fk_hearing_documents_uploaded_by` (`uploaded_by`);

--
-- Indexes for table `hearing_issues`
--
ALTER TABLE `hearing_issues`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `reference_number` (`reference_number`),
  ADD KEY `idx_hearing_issues_hearing` (`hearing_id`),
  ADD KEY `idx_hearing_issues_legislative_item` (`legislative_item_id`),
  ADD KEY `idx_hearing_issues_feedback` (`feedback_id`),
  ADD KEY `idx_hearing_issues_category` (`category_id`),
  ADD KEY `idx_hearing_issues_status` (`status`),
  ADD KEY `idx_hearing_issues_priority` (`priority`),
  ADD KEY `idx_hearing_issues_office` (`assigned_office_id`),
  ADD KEY `fk_hearing_issues_assigned_user` (`assigned_user_id`),
  ADD KEY `fk_hearing_issues_created_by` (`created_by`);

--
-- Indexes for table `hearing_issue_assignments`
--
ALTER TABLE `hearing_issue_assignments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_hearing_issue_assignments_issue` (`issue_id`,`assigned_at`),
  ADD KEY `fk_hearing_issue_assignments_office` (`assigned_office_id`),
  ADD KEY `fk_hearing_issue_assignments_user` (`assigned_user_id`),
  ADD KEY `fk_hearing_issue_assignments_assigned_by` (`assigned_by`);

--
-- Indexes for table `hearing_issue_categories`
--
ALTER TABLE `hearing_issue_categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `hearing_issue_history`
--
ALTER TABLE `hearing_issue_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_hearing_issue_history_issue` (`issue_id`,`created_at`),
  ADD KEY `fk_hearing_issue_history_user` (`created_by`);

--
-- Indexes for table `hearing_responses`
--
ALTER TABLE `hearing_responses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_hearing_responses_issue` (`issue_id`),
  ADD KEY `fk_hearing_responses_prepared_by` (`prepared_by`),
  ADD KEY `fk_hearing_responses_approved_by` (`approved_by`);

--
-- Indexes for table `hearing_types`
--
ALTER TABLE `hearing_types`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `invitations`
--
ALTER TABLE `invitations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `invitation_code` (`invitation_code`),
  ADD UNIQUE KEY `uq_invitation_stakeholder_hearing` (`stakeholder_id`,`hearing_id`),
  ADD KEY `hearing_id` (`hearing_id`),
  ADD KEY `fk_invitations_invited_by` (`invited_by`);

--
-- Indexes for table `legislative_items`
--
ALTER TABLE `legislative_items`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `reference_number` (`reference_number`),
  ADD UNIQUE KEY `public_id` (`public_id`),
  ADD KEY `fk_legislative_items_type` (`item_type_id`),
  ADD KEY `fk_legislative_items_system` (`origin_system_id`),
  ADD KEY `fk_legislative_items_office` (`originating_office_id`),
  ADD KEY `fk_legislative_items_created_by` (`created_by`);

--
-- Indexes for table `legislative_item_status_history`
--
ALTER TABLE `legislative_item_status_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_legislative_status_item` (`legislative_item_id`),
  ADD KEY `fk_legislative_status_user` (`changed_by`);

--
-- Indexes for table `legislative_item_types`
--
ALTER TABLE `legislative_item_types`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_notifications_user` (`user_id`),
  ADD KEY `fk_notifications_system` (`system_id`);

--
-- Indexes for table `offices`
--
ALTER TABLE `offices`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Indexes for table `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`),
  ADD KEY `fk_permissions_system` (`system_id`);

--
-- Indexes for table `qr_codes`
--
ALTER TABLE `qr_codes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code_value` (`code_value`),
  ADD KEY `stakeholder_id` (`stakeholder_id`),
  ADD KEY `fk_qr_registration` (`registration_id`),
  ADD KEY `fk_qr_hearing` (`hearing_id`);

--
-- Indexes for table `registrations`
--
ALTER TABLE `registrations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_registration_code` (`registration_code`),
  ADD UNIQUE KEY `uq_registration_stakeholder_hearing` (`stakeholder_id`,`hearing_id`),
  ADD KEY `hearing_id` (`hearing_id`),
  ADD KEY `fk_registrations_approved_by` (`approved_by`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `role_permissions`
--
ALTER TABLE `role_permissions`
  ADD PRIMARY KEY (`role_id`,`permission_id`),
  ADD KEY `fk_role_permissions_permission` (`permission_id`);

--
-- Indexes for table `stakeholders`
--
ALTER TABLE `stakeholders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `category_id` (`category_id`),
  ADD KEY `fk_stakeholders_user` (`user_id`),
  ADD KEY `fk_stakeholders_verified_by` (`verified_by`);

--
-- Indexes for table `stakeholder_categories`
--
ALTER TABLE `stakeholder_categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `surveys`
--
ALTER TABLE `surveys`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_surveys_hearing` (`hearing_id`),
  ADD KEY `fk_surveys_legislative_item` (`legislative_item_id`),
  ADD KEY `fk_surveys_created_by` (`created_by`);

--
-- Indexes for table `survey_answers`
--
ALTER TABLE `survey_answers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_survey_answers_submission` (`submission_id`),
  ADD KEY `fk_survey_answers_question` (`question_id`),
  ADD KEY `fk_survey_answers_option` (`option_id`);

--
-- Indexes for table `survey_questions`
--
ALTER TABLE `survey_questions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_survey_questions_survey` (`survey_id`);

--
-- Indexes for table `survey_question_options`
--
ALTER TABLE `survey_question_options`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_survey_options_question` (`question_id`);

--
-- Indexes for table `survey_responses`
--
ALTER TABLE `survey_responses`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `survey_submissions`
--
ALTER TABLE `survey_submissions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_survey_submissions_survey` (`survey_id`),
  ADD KEY `fk_survey_submissions_stakeholder` (`stakeholder_id`),
  ADD KEY `fk_survey_submissions_user` (`user_id`);

--
-- Indexes for table `systems`
--
ALTER TABLE `systems`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `uq_users_username` (`username`),
  ADD KEY `role_id` (`role_id`),
  ADD KEY `fk_users_office` (`office_id`),
  ADD KEY `fk_users_department` (`department_id`);

--
-- Indexes for table `user_roles`
--
ALTER TABLE `user_roles`
  ADD PRIMARY KEY (`user_id`,`role_id`),
  ADD KEY `fk_user_roles_role` (`role_id`),
  ADD KEY `fk_user_roles_assigned_by` (`assigned_by`);

--
-- Indexes for table `user_system_access`
--
ALTER TABLE `user_system_access`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_user_system` (`user_id`,`system_id`),
  ADD KEY `fk_user_system_access_system` (`system_id`),
  ADD KEY `fk_user_system_access_granted_by` (`granted_by`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `attendance`
--
ALTER TABLE `attendance`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `attendance_logs`
--
ALTER TABLE `attendance_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `committees`
--
ALTER TABLE `committees`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `departments`
--
ALTER TABLE `departments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `documents`
--
ALTER TABLE `documents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `feedback`
--
ALTER TABLE `feedback`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `feedback_categories`
--
ALTER TABLE `feedback_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hearings`
--
ALTER TABLE `hearings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hearing_actions`
--
ALTER TABLE `hearing_actions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hearing_action_assignments`
--
ALTER TABLE `hearing_action_assignments`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hearing_action_documents`
--
ALTER TABLE `hearing_action_documents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hearing_action_updates`
--
ALTER TABLE `hearing_action_updates`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hearing_documents`
--
ALTER TABLE `hearing_documents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hearing_issues`
--
ALTER TABLE `hearing_issues`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hearing_issue_assignments`
--
ALTER TABLE `hearing_issue_assignments`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hearing_issue_categories`
--
ALTER TABLE `hearing_issue_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `hearing_issue_history`
--
ALTER TABLE `hearing_issue_history`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hearing_responses`
--
ALTER TABLE `hearing_responses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hearing_types`
--
ALTER TABLE `hearing_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `invitations`
--
ALTER TABLE `invitations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `legislative_items`
--
ALTER TABLE `legislative_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `legislative_item_status_history`
--
ALTER TABLE `legislative_item_status_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `legislative_item_types`
--
ALTER TABLE `legislative_item_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `offices`
--
ALTER TABLE `offices`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `qr_codes`
--
ALTER TABLE `qr_codes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `registrations`
--
ALTER TABLE `registrations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `stakeholders`
--
ALTER TABLE `stakeholders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `stakeholder_categories`
--
ALTER TABLE `stakeholder_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `surveys`
--
ALTER TABLE `surveys`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `survey_answers`
--
ALTER TABLE `survey_answers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `survey_questions`
--
ALTER TABLE `survey_questions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `survey_question_options`
--
ALTER TABLE `survey_question_options`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `survey_responses`
--
ALTER TABLE `survey_responses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `survey_submissions`
--
ALTER TABLE `survey_submissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `systems`
--
ALTER TABLE `systems`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `user_system_access`
--
ALTER TABLE `user_system_access`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD CONSTRAINT `fk_activity_logs_system` FOREIGN KEY (`system_id`) REFERENCES `systems` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_activity_logs_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `attendance`
--
ALTER TABLE `attendance`
  ADD CONSTRAINT `attendance_ibfk_1` FOREIGN KEY (`stakeholder_id`) REFERENCES `stakeholders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `attendance_ibfk_2` FOREIGN KEY (`hearing_id`) REFERENCES `hearings` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_attendance_registration` FOREIGN KEY (`registration_id`) REFERENCES `registrations` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_attendance_verified_by` FOREIGN KEY (`verified_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `attendance_logs`
--
ALTER TABLE `attendance_logs`
  ADD CONSTRAINT `attendance_logs_ibfk_1` FOREIGN KEY (`stakeholder_id`) REFERENCES `stakeholders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `attendance_logs_ibfk_2` FOREIGN KEY (`hearing_id`) REFERENCES `hearings` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD CONSTRAINT `fk_audit_system` FOREIGN KEY (`system_id`) REFERENCES `systems` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_audit_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `committees`
--
ALTER TABLE `committees`
  ADD CONSTRAINT `fk_committees_office` FOREIGN KEY (`office_id`) REFERENCES `offices` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `committee_members`
--
ALTER TABLE `committee_members`
  ADD CONSTRAINT `fk_committee_members_committee` FOREIGN KEY (`committee_id`) REFERENCES `committees` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_committee_members_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `departments`
--
ALTER TABLE `departments`
  ADD CONSTRAINT `fk_departments_office` FOREIGN KEY (`office_id`) REFERENCES `offices` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `documents`
--
ALTER TABLE `documents`
  ADD CONSTRAINT `fk_documents_legislative_item` FOREIGN KEY (`legislative_item_id`) REFERENCES `legislative_items` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_documents_system` FOREIGN KEY (`system_id`) REFERENCES `systems` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_documents_uploaded_by` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `feedback`
--
ALTER TABLE `feedback`
  ADD CONSTRAINT `feedback_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `feedback_categories` (`id`),
  ADD CONSTRAINT `fk_feedback_hearing` FOREIGN KEY (`hearing_id`) REFERENCES `hearings` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_feedback_legislative_item` FOREIGN KEY (`legislative_item_id`) REFERENCES `legislative_items` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_feedback_stakeholder` FOREIGN KEY (`stakeholder_id`) REFERENCES `stakeholders` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_feedback_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_feedback_validated_by` FOREIGN KEY (`validated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `hearings`
--
ALTER TABLE `hearings`
  ADD CONSTRAINT `fk_hearings_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_hearings_legislative_item` FOREIGN KEY (`legislative_item_id`) REFERENCES `legislative_items` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `hearings_ibfk_1` FOREIGN KEY (`hearing_type_id`) REFERENCES `hearing_types` (`id`),
  ADD CONSTRAINT `hearings_ibfk_2` FOREIGN KEY (`committee_id`) REFERENCES `committees` (`id`);

--
-- Constraints for table `hearing_actions`
--
ALTER TABLE `hearing_actions`
  ADD CONSTRAINT `fk_hearing_actions_assigned_user` FOREIGN KEY (`assigned_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_hearing_actions_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_hearing_actions_issue` FOREIGN KEY (`issue_id`) REFERENCES `hearing_issues` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_hearing_actions_office` FOREIGN KEY (`assigned_office_id`) REFERENCES `offices` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `hearing_action_assignments`
--
ALTER TABLE `hearing_action_assignments`
  ADD CONSTRAINT `fk_hearing_action_assignments_action` FOREIGN KEY (`action_id`) REFERENCES `hearing_actions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_hearing_action_assignments_assigned_by` FOREIGN KEY (`assigned_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_hearing_action_assignments_office` FOREIGN KEY (`assigned_office_id`) REFERENCES `offices` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_hearing_action_assignments_user` FOREIGN KEY (`assigned_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `hearing_action_documents`
--
ALTER TABLE `hearing_action_documents`
  ADD CONSTRAINT `fk_hearing_action_documents_action` FOREIGN KEY (`action_id`) REFERENCES `hearing_actions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_hearing_action_documents_user` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `hearing_action_updates`
--
ALTER TABLE `hearing_action_updates`
  ADD CONSTRAINT `fk_hearing_action_updates_action` FOREIGN KEY (`action_id`) REFERENCES `hearing_actions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_hearing_action_updates_user` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `hearing_documents`
--
ALTER TABLE `hearing_documents`
  ADD CONSTRAINT `fk_hearing_documents_uploaded_by` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `hearing_documents_ibfk_1` FOREIGN KEY (`hearing_id`) REFERENCES `hearings` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `hearing_issues`
--
ALTER TABLE `hearing_issues`
  ADD CONSTRAINT `fk_hearing_issues_assigned_user` FOREIGN KEY (`assigned_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_hearing_issues_category` FOREIGN KEY (`category_id`) REFERENCES `hearing_issue_categories` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_hearing_issues_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_hearing_issues_feedback` FOREIGN KEY (`feedback_id`) REFERENCES `feedback` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_hearing_issues_hearing` FOREIGN KEY (`hearing_id`) REFERENCES `hearings` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_hearing_issues_legislative_item` FOREIGN KEY (`legislative_item_id`) REFERENCES `legislative_items` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_hearing_issues_office` FOREIGN KEY (`assigned_office_id`) REFERENCES `offices` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `hearing_issue_assignments`
--
ALTER TABLE `hearing_issue_assignments`
  ADD CONSTRAINT `fk_hearing_issue_assignments_assigned_by` FOREIGN KEY (`assigned_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_hearing_issue_assignments_issue` FOREIGN KEY (`issue_id`) REFERENCES `hearing_issues` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_hearing_issue_assignments_office` FOREIGN KEY (`assigned_office_id`) REFERENCES `offices` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_hearing_issue_assignments_user` FOREIGN KEY (`assigned_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `hearing_issue_history`
--
ALTER TABLE `hearing_issue_history`
  ADD CONSTRAINT `fk_hearing_issue_history_issue` FOREIGN KEY (`issue_id`) REFERENCES `hearing_issues` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_hearing_issue_history_user` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `hearing_responses`
--
ALTER TABLE `hearing_responses`
  ADD CONSTRAINT `fk_hearing_responses_approved_by` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_hearing_responses_issue` FOREIGN KEY (`issue_id`) REFERENCES `hearing_issues` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_hearing_responses_prepared_by` FOREIGN KEY (`prepared_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `invitations`
--
ALTER TABLE `invitations`
  ADD CONSTRAINT `fk_invitations_invited_by` FOREIGN KEY (`invited_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `invitations_ibfk_1` FOREIGN KEY (`stakeholder_id`) REFERENCES `stakeholders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `invitations_ibfk_2` FOREIGN KEY (`hearing_id`) REFERENCES `hearings` (`id`);

--
-- Constraints for table `legislative_items`
--
ALTER TABLE `legislative_items`
  ADD CONSTRAINT `fk_legislative_items_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_legislative_items_office` FOREIGN KEY (`originating_office_id`) REFERENCES `offices` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_legislative_items_system` FOREIGN KEY (`origin_system_id`) REFERENCES `systems` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_legislative_items_type` FOREIGN KEY (`item_type_id`) REFERENCES `legislative_item_types` (`id`);

--
-- Constraints for table `legislative_item_status_history`
--
ALTER TABLE `legislative_item_status_history`
  ADD CONSTRAINT `fk_legislative_status_item` FOREIGN KEY (`legislative_item_id`) REFERENCES `legislative_items` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_legislative_status_user` FOREIGN KEY (`changed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `fk_notifications_system` FOREIGN KEY (`system_id`) REFERENCES `systems` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_notifications_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `permissions`
--
ALTER TABLE `permissions`
  ADD CONSTRAINT `fk_permissions_system` FOREIGN KEY (`system_id`) REFERENCES `systems` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `qr_codes`
--
ALTER TABLE `qr_codes`
  ADD CONSTRAINT `fk_qr_hearing` FOREIGN KEY (`hearing_id`) REFERENCES `hearings` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_qr_registration` FOREIGN KEY (`registration_id`) REFERENCES `registrations` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `qr_codes_ibfk_1` FOREIGN KEY (`stakeholder_id`) REFERENCES `stakeholders` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `registrations`
--
ALTER TABLE `registrations`
  ADD CONSTRAINT `fk_registrations_approved_by` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `registrations_ibfk_1` FOREIGN KEY (`stakeholder_id`) REFERENCES `stakeholders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `registrations_ibfk_2` FOREIGN KEY (`hearing_id`) REFERENCES `hearings` (`id`);

--
-- Constraints for table `role_permissions`
--
ALTER TABLE `role_permissions`
  ADD CONSTRAINT `fk_role_permissions_permission` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_role_permissions_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `stakeholders`
--
ALTER TABLE `stakeholders`
  ADD CONSTRAINT `fk_stakeholders_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_stakeholders_verified_by` FOREIGN KEY (`verified_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `stakeholders_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `stakeholder_categories` (`id`);

--
-- Constraints for table `surveys`
--
ALTER TABLE `surveys`
  ADD CONSTRAINT `fk_surveys_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_surveys_hearing` FOREIGN KEY (`hearing_id`) REFERENCES `hearings` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_surveys_legislative_item` FOREIGN KEY (`legislative_item_id`) REFERENCES `legislative_items` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `survey_answers`
--
ALTER TABLE `survey_answers`
  ADD CONSTRAINT `fk_survey_answers_option` FOREIGN KEY (`option_id`) REFERENCES `survey_question_options` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_survey_answers_question` FOREIGN KEY (`question_id`) REFERENCES `survey_questions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_survey_answers_submission` FOREIGN KEY (`submission_id`) REFERENCES `survey_submissions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `survey_questions`
--
ALTER TABLE `survey_questions`
  ADD CONSTRAINT `fk_survey_questions_survey` FOREIGN KEY (`survey_id`) REFERENCES `surveys` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `survey_question_options`
--
ALTER TABLE `survey_question_options`
  ADD CONSTRAINT `fk_survey_options_question` FOREIGN KEY (`question_id`) REFERENCES `survey_questions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `survey_submissions`
--
ALTER TABLE `survey_submissions`
  ADD CONSTRAINT `fk_survey_submissions_stakeholder` FOREIGN KEY (`stakeholder_id`) REFERENCES `stakeholders` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_survey_submissions_survey` FOREIGN KEY (`survey_id`) REFERENCES `surveys` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_survey_submissions_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `fk_users_department` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_users_office` FOREIGN KEY (`office_id`) REFERENCES `offices` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`);

--
-- Constraints for table `user_roles`
--
ALTER TABLE `user_roles`
  ADD CONSTRAINT `fk_user_roles_assigned_by` FOREIGN KEY (`assigned_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_user_roles_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_user_roles_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_system_access`
--
ALTER TABLE `user_system_access`
  ADD CONSTRAINT `fk_user_system_access_granted_by` FOREIGN KEY (`granted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_user_system_access_system` FOREIGN KEY (`system_id`) REFERENCES `systems` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_user_system_access_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
