-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: legislative_management_db
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `activity_logs`
--

DROP TABLE IF EXISTS `activity_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `activity_logs` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `system_id` int(11) DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `details` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(500) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_activity_logs_user` (`user_id`),
  KEY `idx_activity_logs_system` (`system_id`),
  KEY `idx_activity_logs_action` (`action`),
  KEY `idx_activity_logs_created_at` (`created_at`),
  CONSTRAINT `fk_activity_logs_system` FOREIGN KEY (`system_id`) REFERENCES `systems` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_activity_logs_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `activity_logs`
--

LOCK TABLES `activity_logs` WRITE;
/*!40000 ALTER TABLE `activity_logs` DISABLE KEYS */;
INSERT INTO `activity_logs` VALUES (1,1,NULL,'Login','User logged in successfully.',NULL,NULL,'2026-08-01 06:15:40'),(2,1,NULL,'Logout','User logged out.',NULL,NULL,'2026-08-01 13:17:18'),(3,1,NULL,'Login','User logged in successfully.',NULL,NULL,'2026-08-01 13:17:31'),(4,1,NULL,'Logout','User logged out.',NULL,NULL,'2026-08-01 13:29:15'),(5,1,NULL,'Login','Signed in to the Ordinance and Resolution Life Cycle Management System.',NULL,NULL,'2026-08-01 13:31:23'),(6,1,NULL,'Login','User logged in successfully.',NULL,NULL,'2026-08-03 08:36:55'),(7,1,NULL,'Logout','User logged out.',NULL,NULL,'2026-08-03 08:45:59'),(8,1,NULL,'Login','User logged in successfully.',NULL,NULL,'2026-08-03 08:46:37'),(9,1,NULL,'Logout','User logged out.',NULL,NULL,'2026-08-03 08:46:44');
/*!40000 ALTER TABLE `activity_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `admins`
--

DROP TABLE IF EXISTS `admins`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `admins` (
  `admin_id` int(11) NOT NULL AUTO_INCREMENT,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('Super Admin','Administrator') DEFAULT 'Administrator',
  `status` enum('Active','Inactive') DEFAULT 'Active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`admin_id`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `admins`
--

LOCK TABLES `admins` WRITE;
/*!40000 ALTER TABLE `admins` DISABLE KEYS */;
INSERT INTO `admins` VALUES (1,'Admin User','admin@example.com','admin','$2y$10$mz.e3pYnFqjJcR6a.y2Ueu7w.jGvU0c5Kj.p8HwS3d8R1kQ2l7Oae','Administrator','Active','2026-08-09 07:23:24');
/*!40000 ALTER TABLE `admins` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `attendance`
--

DROP TABLE IF EXISTS `attendance`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `attendance` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_attendance_stakeholder_hearing` (`stakeholder_id`,`hearing_id`),
  KEY `hearing_id` (`hearing_id`),
  KEY `fk_attendance_registration` (`registration_id`),
  KEY `fk_attendance_verified_by` (`verified_by`),
  CONSTRAINT `attendance_ibfk_1` FOREIGN KEY (`stakeholder_id`) REFERENCES `stakeholders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `attendance_ibfk_2` FOREIGN KEY (`hearing_id`) REFERENCES `hearings` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_attendance_registration` FOREIGN KEY (`registration_id`) REFERENCES `registrations` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_attendance_verified_by` FOREIGN KEY (`verified_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `attendance`
--

LOCK TABLES `attendance` WRITE;
/*!40000 ALTER TABLE `attendance` DISABLE KEYS */;
/*!40000 ALTER TABLE `attendance` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `attendance_logs`
--

DROP TABLE IF EXISTS `attendance_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `attendance_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `stakeholder_id` int(11) NOT NULL,
  `hearing_id` int(11) NOT NULL,
  `action` varchar(100) NOT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `stakeholder_id` (`stakeholder_id`),
  KEY `hearing_id` (`hearing_id`),
  CONSTRAINT `attendance_logs_ibfk_1` FOREIGN KEY (`stakeholder_id`) REFERENCES `stakeholders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `attendance_logs_ibfk_2` FOREIGN KEY (`hearing_id`) REFERENCES `hearings` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `attendance_logs`
--

LOCK TABLES `attendance_logs` WRITE;
/*!40000 ALTER TABLE `attendance_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `attendance_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `audit_logs`
--

DROP TABLE IF EXISTS `audit_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `audit_logs` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `log_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `user` varchar(150) DEFAULT 'Administrator',
  `admin_id` int(11) DEFAULT NULL,
  `role` varchar(50) DEFAULT 'Admin',
  `module` varchar(100) DEFAULT 'System',
  `activity` varchar(255) DEFAULT NULL,
  `status` varchar(50) DEFAULT 'Completed',
  `system_id` int(11) DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `entity_type` varchar(150) NOT NULL,
  `entity_id` varchar(100) DEFAULT NULL,
  `old_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`old_values`)),
  `new_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`new_values`)),
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(500) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_audit_entity` (`entity_type`,`entity_id`),
  KEY `idx_audit_user` (`user_id`),
  KEY `idx_audit_created_at` (`created_at`),
  KEY `fk_audit_system` (`system_id`),
  CONSTRAINT `fk_audit_system` FOREIGN KEY (`system_id`) REFERENCES `systems` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_audit_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=214 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `audit_logs`
--

LOCK TABLES `audit_logs` WRITE;
/*!40000 ALTER TABLE `audit_logs` DISABLE KEYS */;
INSERT INTO `audit_logs` VALUES (1,NULL,NULL,'Admin',NULL,'Admin','Policy Records','Uploaded Urban Traffic Congestion Study','Completed',NULL,'','',NULL,NULL,NULL,NULL,NULL,'2026-05-14 02:15:00'),(2,NULL,NULL,'Researcher 01',NULL,'Staff','Research Data','Added Traffic Survey Dataset','Completed',NULL,'','',NULL,NULL,NULL,NULL,NULL,'2026-05-14 01:45:00'),(3,NULL,NULL,'Analyst 02',NULL,'Staff','Evaluations','Completed Traffic Policy Evaluation','Completed',NULL,'','',NULL,NULL,NULL,NULL,NULL,'2026-05-14 01:20:00'),(4,NULL,NULL,'Admin',NULL,'Admin','Policy Records','Generated AI Summary for Traffic Policy','Completed',NULL,'','',NULL,NULL,NULL,NULL,NULL,'2026-05-14 00:50:00'),(5,NULL,NULL,'Christian M. Caspe',NULL,'Councilor','System','User login','Completed',NULL,'','',NULL,NULL,NULL,NULL,NULL,'2026-05-13 08:30:00'),(6,NULL,NULL,'Quintanaasdsa',NULL,'Staff','System','User logout','Completed',NULL,'','',NULL,NULL,NULL,NULL,NULL,'2026-09-16 15:58:33'),(7,NULL,NULL,'Salim',NULL,'Staff','System','User login','Completed',NULL,'','',NULL,NULL,NULL,NULL,NULL,'2026-09-16 15:58:38'),(8,NULL,NULL,'Salim',NULL,'Staff','System','User logout','Completed',NULL,'','',NULL,NULL,NULL,NULL,NULL,'2026-09-16 15:58:45'),(9,NULL,NULL,'Quintanaasdsa',NULL,'Staff','System','User login','Completed',NULL,'','',NULL,NULL,NULL,NULL,NULL,'2026-09-16 15:59:20'),(10,NULL,NULL,'Admin',NULL,'Admin','User Directory','Updated account details for Quintanapangit (Password Reset)','Completed',NULL,'','',NULL,NULL,NULL,NULL,NULL,'2026-09-16 16:00:12'),(11,NULL,NULL,'Quintanaasdsa',NULL,'Staff','System','User logout','Completed',NULL,'','',NULL,NULL,NULL,NULL,NULL,'2026-09-16 16:00:25'),(12,NULL,NULL,'Quintanapangit',NULL,'Councilor','System','User login','Completed',NULL,'','',NULL,NULL,NULL,NULL,NULL,'2026-09-16 16:00:35'),(13,NULL,NULL,'Quintanapangit',NULL,'Councilor','System','User logout','Completed',NULL,'','',NULL,NULL,NULL,NULL,NULL,'2026-09-16 16:00:42'),(14,NULL,NULL,'Quintanaasdsa',NULL,'Staff','System','User login','Completed',NULL,'','',NULL,NULL,NULL,NULL,NULL,'2026-09-16 16:03:39'),(15,NULL,NULL,'Quintanaasdsa',NULL,'Staff','System','User login','Completed',NULL,'','',NULL,NULL,NULL,NULL,NULL,'2026-09-16 16:48:03'),(16,NULL,NULL,'Quintanaasdsa',NULL,'Staff','System','User login','Completed',NULL,'','',NULL,NULL,NULL,NULL,NULL,'2026-09-16 17:14:43'),(17,NULL,NULL,'Quintanaasdsa',NULL,'Staff','System','User login','Completed',NULL,'','',NULL,NULL,NULL,NULL,NULL,'2026-09-16 17:21:13'),(18,NULL,NULL,'Quintanaasdsa',NULL,'Staff','System','User logout','Completed',NULL,'','',NULL,NULL,NULL,NULL,NULL,'2026-09-17 03:49:03'),(19,NULL,NULL,'Salim',NULL,'Staff','System','User login','Completed',NULL,'','',NULL,NULL,NULL,NULL,NULL,'2026-09-17 03:49:10'),(20,NULL,NULL,'Salim',NULL,'Staff','System','User logout','Completed',NULL,'','',NULL,NULL,NULL,NULL,NULL,'2026-09-17 03:49:23'),(21,NULL,NULL,'Quintanaasdsa',NULL,'Staff','System','User login','Completed',NULL,'','',NULL,NULL,NULL,NULL,NULL,'2026-09-17 03:49:28'),(22,NULL,NULL,'Admin',NULL,'Admin','Evaluations','Evaluated policy: Improvement Strategy for Public Health Services in Manila City','Completed',NULL,'','',NULL,NULL,NULL,NULL,NULL,'2026-09-17 03:54:27'),(23,NULL,NULL,'Quintanaasdsa',NULL,'Staff','Evaluations','Approved impact evaluation for \"Improvement Strategy for Public Health Services in Manila City\"','Completed',NULL,'','',NULL,NULL,NULL,NULL,NULL,'2026-09-17 04:05:24'),(24,NULL,NULL,'Admin',NULL,'Admin','Evaluations','Evaluated policy: Improvement Strategy for Public Health Services in Manila City','Completed',NULL,'','',NULL,NULL,NULL,NULL,NULL,'2026-09-17 04:08:58'),(25,NULL,NULL,'Admin',NULL,'Admin','Policy Records','Uploaded Community Safety and Crime Prevention Strategy for Manila City','Completed',NULL,'','',NULL,NULL,NULL,NULL,NULL,'2026-09-17 04:10:02'),(26,NULL,NULL,'Admin',NULL,'Admin','Evaluations','Evaluated policy: Community Safety and Crime Prevention Strategy for Manila City','Completed',NULL,'','',NULL,NULL,NULL,NULL,NULL,'2026-09-17 04:10:15'),(27,NULL,NULL,'Admin',NULL,'Admin','Policy Records','Uploaded _______________________________   _______________________________ City Councilor, Sponsor   City Vice Mayor / Presiding Officer','Completed',NULL,'','',NULL,NULL,NULL,NULL,NULL,'2026-09-17 04:15:23'),(28,NULL,NULL,'Admin',NULL,'Admin','Evaluations','Evaluated policy: _______________________________   _______________________________ City Councilor, Sponsor   City Vice Mayor / Presiding Officer','Completed',NULL,'','',NULL,NULL,NULL,NULL,NULL,'2026-09-17 04:15:35'),(29,NULL,NULL,'Admin',NULL,'Admin','Evaluations','Evaluated policy: _______________________________   _______________________________ City Councilor, Sponsor   City Vice Mayor / Presiding Officer','Completed',NULL,'','',NULL,NULL,NULL,NULL,NULL,'2026-09-17 04:23:14'),(30,NULL,NULL,'Quintanaasdsa',NULL,'Staff','Evaluations','Approved impact evaluation for \"_______________________________   _______________________________ City Councilor, Sponsor   City Vice Mayor / Presiding Officer\"','Completed',NULL,'','',NULL,NULL,NULL,NULL,NULL,'2026-09-17 04:23:41'),(31,NULL,NULL,'Admin',NULL,'Admin','Policy Records','Uploaded _______________________________   _______________________________ City Councilor, Sponsor   City Vice Mayor / Presiding Officer','Completed',NULL,'','',NULL,NULL,NULL,NULL,NULL,'2026-09-17 04:47:10'),(32,NULL,NULL,'Admin',NULL,'Admin','Evaluations','Evaluated policy: _______________________________   _______________________________ City Councilor, Sponsor   City Vice Mayor / Presiding Officer','Completed',NULL,'','',NULL,NULL,NULL,NULL,NULL,'2026-09-17 04:47:41'),(33,NULL,NULL,'Quintanaasdsa',NULL,'Staff','Evaluations','Approved impact evaluation for \"_______________________________   _______________________________ City Councilor, Sponsor   City Vice Mayor / Presiding Officer\"','Completed',NULL,'','',NULL,NULL,NULL,NULL,NULL,'2026-09-17 04:47:46'),(34,NULL,NULL,'Admin',NULL,'Admin','Evaluations','Evaluated policy: _______________________________   _______________________________ City Councilor, Sponsor   City Vice Mayor / Presiding Officer','Completed',NULL,'','',NULL,NULL,NULL,NULL,NULL,'2026-09-17 04:49:56'),(35,NULL,NULL,'Quintanaasdsa',NULL,'Staff','Evaluations','Approved impact evaluation for \"_______________________________   _______________________________ City Councilor, Sponsor   City Vice Mayor / Presiding Officer\"','Completed',NULL,'','',NULL,NULL,NULL,NULL,NULL,'2026-09-17 04:49:59'),(36,NULL,NULL,'Admin',NULL,'Admin','Policy Records','Uploaded Manila Ordinance No 2026 045','Completed',NULL,'','',NULL,NULL,NULL,NULL,NULL,'2026-09-17 05:52:20'),(37,NULL,NULL,'Admin',NULL,'Admin','Evaluations','Evaluated policy: Manila Ordinance No 2026 045','Completed',NULL,'','',NULL,NULL,NULL,NULL,NULL,'2026-09-17 05:52:31'),(38,NULL,NULL,'Quintanaasdsa',NULL,'Staff','Evaluations','Approved impact evaluation for \"Manila Ordinance No 2026 045\"','Completed',NULL,'','',NULL,NULL,NULL,NULL,NULL,'2026-09-17 05:52:39'),(39,NULL,NULL,'Admin',NULL,'Admin','Evaluations','Evaluated policy: Manila Ordinance No 2026 045','Completed',NULL,'','',NULL,NULL,NULL,NULL,NULL,'2026-09-17 05:52:48'),(40,NULL,NULL,'Quintanaasdsa',NULL,'Staff','Evaluations','Approved impact evaluation for \"Manila Ordinance No 2026 045\"','Completed',NULL,'','',NULL,NULL,NULL,NULL,NULL,'2026-09-17 05:52:52'),(41,NULL,NULL,'Admin',NULL,'Admin','Evaluations','Evaluated policy: Manila Ordinance No 2026 045','Completed',NULL,'','',NULL,NULL,NULL,NULL,NULL,'2026-09-17 06:00:05'),(42,NULL,NULL,'Admin',NULL,'Admin','Policy Records','Uploaded Manila Ordinance No 2026 050','Completed',NULL,'','',NULL,NULL,NULL,NULL,NULL,'2026-09-17 06:19:54'),(43,NULL,NULL,'Admin',NULL,'Admin','Evaluations','Evaluated policy: Manila Ordinance No 2026 050','Completed',NULL,'','',NULL,NULL,NULL,NULL,NULL,'2026-09-17 06:40:54'),(44,NULL,NULL,'Admin',NULL,'Admin','Policy Records','Uploaded Manila Ordinance No 2026 053 (checked)','Completed',NULL,'','',NULL,NULL,NULL,NULL,NULL,'2026-09-17 06:48:08'),(45,NULL,NULL,'Admin',NULL,'Admin','Evaluations','Evaluated policy: Manila Ordinance No 2026 053 (checked)','Completed',NULL,'','',NULL,NULL,NULL,NULL,NULL,'2026-09-17 06:48:23'),(46,NULL,NULL,'Admin',NULL,'Admin','Policy Records','Uploaded Manila Ordinance No 2026 051 (checked)','Completed',NULL,'','',NULL,NULL,NULL,NULL,NULL,'2026-09-17 06:49:18'),(47,NULL,NULL,'Admin',NULL,'Admin','Evaluations','Evaluated policy: Manila Ordinance No 2026 051 (checked)','Completed',NULL,'','',NULL,NULL,NULL,NULL,NULL,'2026-09-17 06:49:27'),(48,NULL,NULL,'Admin',NULL,'Admin','Evaluations','Evaluated policy: Manila Ordinance No 2026 051 (checked)','Completed',NULL,'','',NULL,NULL,NULL,NULL,NULL,'2026-09-17 07:16:38'),(49,NULL,NULL,'Admin',NULL,'Admin','Evaluations','Evaluated policy: Manila Ordinance No 2026 051 (checked)','Completed',NULL,'','',NULL,NULL,NULL,NULL,NULL,'2026-09-17 07:18:07'),(50,NULL,NULL,'Admin',NULL,'Admin','Evaluations','Evaluated policy: Manila Ordinance No 2026 051 (checked)','Completed',NULL,'','',NULL,NULL,NULL,NULL,NULL,'2026-09-17 07:19:17'),(51,NULL,NULL,'Admin',NULL,'Admin','Policy Records','Uploaded Manila Ordinance No 2026 049','Completed',NULL,'','',NULL,NULL,NULL,NULL,NULL,'2026-09-17 07:20:10'),(52,NULL,NULL,'Admin',NULL,'Admin','Evaluations','Evaluated policy: Manila Ordinance No 2026 049','Completed',NULL,'','',NULL,NULL,NULL,NULL,NULL,'2026-09-17 07:20:20'),(53,NULL,NULL,'Quintanaasdsa',NULL,'Staff','System','User login','Completed',NULL,'','',NULL,NULL,NULL,NULL,NULL,'2026-09-17 09:22:18'),(54,NULL,NULL,'Admin',NULL,'Admin','System','User login','Completed',NULL,'','',NULL,NULL,NULL,NULL,NULL,'2026-09-17 09:44:40'),(55,NULL,NULL,'Admin',NULL,'Admin','System','User login','Completed',NULL,'','',NULL,NULL,NULL,NULL,NULL,'2026-09-17 09:56:13'),(56,NULL,NULL,'Admin',NULL,'Admin','System','User login','Completed',NULL,'','',NULL,NULL,NULL,NULL,NULL,'2026-09-17 10:05:21'),(57,NULL,NULL,'Quintanaasdsa',NULL,'Staff','System','User login','Completed',NULL,'','',NULL,NULL,NULL,NULL,NULL,'2026-09-17 10:12:28'),(58,NULL,NULL,'Admin',NULL,'Admin','Policy Records','Uploaded Manila Ordinance No 2026 047','Completed',NULL,'','',NULL,NULL,NULL,NULL,NULL,'2026-09-17 10:19:08'),(59,NULL,NULL,'Admin',NULL,'Admin','Evaluations','Evaluated policy: Manila Ordinance No 2026 047','Completed',NULL,'','',NULL,NULL,NULL,NULL,NULL,'2026-09-17 10:19:26'),(60,NULL,NULL,'Admin',NULL,'Admin','System','User login','Completed',NULL,'','',NULL,NULL,NULL,NULL,NULL,'2026-09-17 10:49:39'),(61,NULL,NULL,'Admin',NULL,'Admin','Policy Records','Uploaded Manila Ordinance No 2026 051 (checked)','Completed',NULL,'','',NULL,NULL,NULL,NULL,NULL,'2026-09-17 10:55:17'),(62,NULL,NULL,'Admin',NULL,'Admin','Evaluations','Evaluated policy: Manila Ordinance No 2026 051 (checked)','Completed',NULL,'','',NULL,NULL,NULL,NULL,NULL,'2026-09-17 10:55:49'),(63,NULL,NULL,'Admin',NULL,'Admin','Evaluations','Formally routed policy \"Manila Ordinance No 2026 051 (checked)\" to City Council with deliberation flags: Statutory/criteria findings attached','Completed',NULL,'','',NULL,NULL,NULL,NULL,NULL,'2026-09-17 11:27:18'),(64,NULL,NULL,'Admin',NULL,'Admin','Evaluations','Requested revision for policy \"Manila Ordinance No 2026 051 (checked)\" sent to sponsor/drafter citing: Evaluation criteria deficits','Completed',NULL,'','',NULL,NULL,NULL,NULL,NULL,'2026-09-17 11:27:50'),(65,NULL,NULL,'Admin',NULL,'Admin','Policy Records','Uploaded Manila Ordinance No 2026 050','Completed',NULL,'','',NULL,NULL,NULL,NULL,NULL,'2026-09-17 11:28:26'),(66,NULL,NULL,'Admin',NULL,'Admin','Evaluations','Evaluated policy: Manila Ordinance No 2026 050','Completed',NULL,'','',NULL,NULL,NULL,NULL,NULL,'2026-09-17 11:28:39'),(67,NULL,NULL,'Admin',NULL,'Admin','Evaluations','Evaluated policy: Manila Ordinance No 2026 050','Completed',NULL,'','',NULL,NULL,NULL,NULL,NULL,'2026-09-17 11:29:23'),(68,NULL,NULL,'Admin',NULL,'Admin','Evaluations','Requested revision for policy \"Manila Ordinance No 2026 051 (checked)\" sent to sponsor/drafter citing: Legal Compliance — Procedural Compliance: ','Completed',NULL,'','',NULL,NULL,NULL,NULL,NULL,'2026-09-17 11:30:05'),(69,NULL,NULL,'Admin',NULL,'Admin','Policy Records','Uploaded Manila Ordinance No 2026 053 (checked)','Completed',NULL,'','',NULL,NULL,NULL,NULL,NULL,'2026-09-17 11:33:58'),(70,NULL,NULL,'Admin',NULL,'Admin','Evaluations','Evaluated policy: Manila Ordinance No 2026 053 (checked)','Completed',NULL,'','',NULL,NULL,NULL,NULL,NULL,'2026-09-17 11:34:38'),(71,NULL,NULL,'Quintanaasdsa',NULL,'Staff','System','User login','Completed',NULL,'','',NULL,NULL,NULL,NULL,NULL,'2026-09-18 09:13:19'),(72,NULL,NULL,'Admin',NULL,'Admin','Policy Records','Archived policy record #47','Completed',NULL,'','',NULL,NULL,NULL,NULL,NULL,'2026-09-18 09:18:03'),(73,NULL,NULL,'Admin',NULL,'Admin','Policy Records','Archived policy record #49','Completed',NULL,'','',NULL,NULL,NULL,NULL,NULL,'2026-09-18 09:18:10'),(74,NULL,NULL,'Quintana',NULL,'Staff','System','User login','Completed',NULL,'','',NULL,NULL,NULL,NULL,NULL,'2026-09-18 13:39:04'),(75,NULL,NULL,'Admin',NULL,'Admin','System','User login','Completed',NULL,'','',NULL,NULL,NULL,NULL,NULL,'2026-09-18 15:00:24'),(76,NULL,NULL,'Admin',NULL,'Admin','System','User logout','Completed',NULL,'','',NULL,NULL,NULL,NULL,NULL,'2026-09-18 15:00:55'),(77,NULL,NULL,'Quintana',NULL,'Staff','System','User logout','Completed',NULL,'','',NULL,NULL,NULL,NULL,NULL,'2026-09-18 15:27:52'),(78,NULL,NULL,'Christian M. Caspe',NULL,'Admin','System','Admin login via official credentials','Completed',NULL,'','',NULL,NULL,NULL,NULL,NULL,'2026-09-18 15:28:01'),(79,NULL,NULL,'Christian M. Caspe',NULL,'Admin','System','User login','Completed',NULL,'','',NULL,NULL,NULL,NULL,NULL,'2026-09-18 15:28:03'),(80,NULL,NULL,'Christian',NULL,'Staff','System','User logout','Completed',NULL,'','',NULL,NULL,NULL,NULL,NULL,'2026-09-18 15:43:43'),(81,NULL,NULL,'Christian M. Caspe',NULL,'Admin','System','Generated 2FA login OTP for Administrator','Completed',NULL,'','',NULL,NULL,NULL,NULL,NULL,'2026-09-18 15:44:49'),(82,NULL,NULL,'Christian M. Caspe',NULL,'Admin','System','Generated 2FA login OTP for Administrator','Completed',NULL,'','',NULL,NULL,NULL,NULL,NULL,'2026-09-18 15:52:16'),(83,NULL,NULL,'Christian M. Caspe',NULL,'Admin','System','Generated 2FA login OTP for Administrator','Completed',NULL,'','',NULL,NULL,NULL,NULL,NULL,'2026-09-18 16:05:26'),(84,NULL,NULL,'Christian M. Caspe',NULL,'Admin','System','Generated 2FA login OTP for Administrator','Completed',NULL,'','',NULL,NULL,NULL,NULL,NULL,'2026-09-18 16:05:30'),(85,NULL,NULL,'Christian M. Caspe',NULL,'Admin','System','Generated 2FA login OTP for Administrator','Completed',NULL,'','',NULL,NULL,NULL,NULL,NULL,'2026-09-18 16:05:37'),(86,NULL,NULL,'Christian M. Caspe',NULL,'Admin','System','Generated 2FA login OTP for Administrator','Completed',NULL,'','',NULL,NULL,NULL,NULL,NULL,'2026-09-18 16:05:42'),(87,NULL,NULL,'Christian M. Caspe',NULL,'Admin','System','Generated 2FA login OTP for Administrator','Completed',NULL,'','',NULL,NULL,NULL,NULL,NULL,'2026-09-18 16:05:47'),(88,NULL,NULL,'Christian M. Caspe',NULL,'Admin','System','Generated 2FA login OTP for Administrator','Completed',NULL,'','',NULL,NULL,NULL,NULL,NULL,'2026-09-18 16:05:51'),(89,NULL,NULL,'Christian M. Caspe',NULL,'Admin','System','Generated 2FA login OTP for Administrator','Completed',NULL,'','',NULL,NULL,NULL,NULL,NULL,'2026-09-18 16:05:55'),(90,NULL,NULL,'Christian M. Caspe',NULL,'Admin','System','Generated 2FA login OTP for Administrator','Completed',NULL,'','',NULL,NULL,NULL,NULL,NULL,'2026-09-18 16:06:00'),(91,NULL,NULL,'Christian M. Caspe',NULL,'Admin','System','Completed 2FA OTP login verification','Completed',NULL,'','',NULL,NULL,NULL,NULL,NULL,'2026-09-18 16:06:32'),(92,NULL,NULL,'Christian M. Caspe',NULL,'Admin','System','User login','Completed',NULL,'','',NULL,NULL,NULL,NULL,NULL,'2026-09-18 16:06:34'),(93,NULL,NULL,'Christian M. Caspe',NULL,'Admin','System','Generated 2FA login OTP for Administrator','Completed',NULL,'','',NULL,NULL,NULL,NULL,NULL,'2026-09-18 16:13:51'),(94,NULL,NULL,'Christian M. Caspe',NULL,'Admin','System','Completed 2FA OTP login verification','Completed',NULL,'','',NULL,NULL,NULL,NULL,NULL,'2026-09-18 16:14:10'),(95,NULL,NULL,'Christian M. Caspe',NULL,'Admin','System','User login','Completed',NULL,'','',NULL,NULL,NULL,NULL,NULL,'2026-09-18 16:14:12'),(96,NULL,NULL,'Christian M. Caspe',NULL,'Admin','System','Generated 2FA login OTP for Administrator','Completed',NULL,'','',NULL,NULL,NULL,NULL,NULL,'2026-09-18 16:36:22'),(97,NULL,NULL,'Christian M. Caspe',NULL,'Admin','System','Completed 2FA OTP login verification','Completed',NULL,'','',NULL,NULL,NULL,NULL,NULL,'2026-09-18 16:37:00'),(98,NULL,NULL,'Christian M. Caspe',NULL,'Admin','System','User login','Completed',NULL,'','',NULL,NULL,NULL,NULL,NULL,'2026-09-18 16:37:03'),(99,NULL,NULL,'Admin',NULL,'Admin','System','User login','Completed',NULL,'','',NULL,NULL,NULL,NULL,NULL,'2026-09-19 03:37:16'),(100,NULL,NULL,'Admin',NULL,'Admin','System','User login','Completed',NULL,'','',NULL,NULL,NULL,NULL,NULL,'2026-09-19 03:37:23'),(101,NULL,NULL,'Admin',NULL,'Admin','System','User login','Completed',NULL,'','',NULL,NULL,NULL,NULL,NULL,'2026-09-19 03:37:33'),(102,NULL,NULL,'Christian M. Caspe',NULL,'Admin','System','Generated 2FA login OTP for Administrator','Completed',NULL,'','',NULL,NULL,NULL,NULL,NULL,'2026-09-19 03:39:20'),(103,NULL,NULL,'Christian M. Caspe',NULL,'Admin','System','Completed 2FA OTP login verification','Completed',NULL,'','',NULL,NULL,NULL,NULL,NULL,'2026-09-19 03:40:17'),(104,NULL,NULL,'Christian M. Caspe',NULL,'Admin','System','User login','Completed',NULL,'','',NULL,NULL,NULL,NULL,NULL,'2026-09-19 03:40:19'),(105,NULL,NULL,'Admin',NULL,'Admin','User Directory','Provisioned new account for renz','Completed',NULL,'','',NULL,NULL,NULL,NULL,NULL,'2026-09-19 05:12:40'),(106,NULL,NULL,'Christian',NULL,'Staff','System','User logout','Completed',NULL,'','',NULL,NULL,NULL,NULL,NULL,'2026-09-19 05:13:00'),(107,NULL,NULL,'renz',NULL,'Staff','System','Generated 2FA login OTP','Completed',NULL,'','',NULL,NULL,NULL,NULL,NULL,'2026-09-19 05:13:40'),(108,NULL,NULL,'renz',NULL,'Staff','System','Generated 2FA login OTP','Completed',NULL,'','',NULL,NULL,NULL,NULL,NULL,'2026-09-19 05:13:44'),(109,NULL,NULL,'renz',NULL,'Staff','System','Completed 2FA OTP login verification','Completed',NULL,'','',NULL,NULL,NULL,NULL,NULL,'2026-09-19 05:14:03'),(110,NULL,NULL,'renz',NULL,'Staff','System','User login','Completed',NULL,'','',NULL,NULL,NULL,NULL,NULL,'2026-09-19 05:14:05'),(111,NULL,NULL,'Christian',NULL,'Staff','System','User login','Completed',NULL,'','',NULL,NULL,NULL,NULL,NULL,'2026-09-19 07:07:14'),(112,NULL,NULL,'Salim',NULL,'Staff','System','User logout','Completed',NULL,'','',NULL,NULL,NULL,NULL,NULL,'2026-09-19 11:00:32'),(113,NULL,NULL,'Christian',NULL,'Staff','System','User login','Completed',NULL,'','',NULL,NULL,NULL,NULL,NULL,'2026-09-19 11:04:36'),(114,NULL,NULL,'Admin',NULL,'Admin','Policy Records','Archived policy record #91','Completed',NULL,'','',NULL,NULL,NULL,NULL,NULL,'2026-09-19 11:05:22'),(115,NULL,NULL,'Admin',NULL,'Admin','Policy Records','Archived policy record #90','Completed',NULL,'','',NULL,NULL,NULL,NULL,NULL,'2026-09-19 11:05:31'),(116,NULL,NULL,'Admin',NULL,'Admin','Policy Records','Archived policy record #89','Completed',NULL,'','',NULL,NULL,NULL,NULL,NULL,'2026-09-19 11:05:38'),(117,NULL,NULL,'Admin',NULL,'Admin','Policy Records','Archived policy record #88','Completed',NULL,'','',NULL,NULL,NULL,NULL,NULL,'2026-09-19 11:05:45'),(118,NULL,NULL,'Admin',NULL,'Admin','Policy Records','Archived policy record #87','Completed',NULL,'','',NULL,NULL,NULL,NULL,NULL,'2026-09-19 11:05:59'),(119,NULL,NULL,'Admin',NULL,'Admin','Policy Records','Archived policy record #86','Completed',NULL,'','',NULL,NULL,NULL,NULL,NULL,'2026-09-19 11:06:06'),(120,NULL,NULL,'Admin',NULL,'Admin','Policy Records','Archived policy record #85','Completed',NULL,'','',NULL,NULL,NULL,NULL,NULL,'2026-09-19 11:06:13'),(121,NULL,NULL,'Admin',NULL,'Admin','Policy Records','Archived policy record #84','Completed',NULL,'','',NULL,NULL,NULL,NULL,NULL,'2026-09-19 11:06:21'),(122,NULL,NULL,'Admin',NULL,'Admin','Policy Records','Archived policy record #83','Completed',NULL,'','',NULL,NULL,NULL,NULL,NULL,'2026-09-19 11:06:29'),(123,NULL,NULL,'Admin',NULL,'Admin','Policy Records','Archived policy record #82','Completed',NULL,'','',NULL,NULL,NULL,NULL,NULL,'2026-09-19 11:06:36'),(124,NULL,NULL,'Admin',NULL,'Admin','Policy Records','Archived policy record #81','Completed',NULL,'','',NULL,NULL,NULL,NULL,NULL,'2026-09-19 11:06:44'),(125,NULL,NULL,'Admin',NULL,'Admin','Policy Records','Archived policy record #80','Completed',NULL,'','',NULL,NULL,NULL,NULL,NULL,'2026-09-19 11:06:52'),(126,NULL,NULL,'Admin',NULL,'Admin','Policy Records','Archived policy record #79','Completed',NULL,'','',NULL,NULL,NULL,NULL,NULL,'2026-09-19 11:07:02'),(127,NULL,NULL,'Admin',NULL,'Admin','Policy Records','Archived policy record #78','Completed',NULL,'','',NULL,NULL,NULL,NULL,NULL,'2026-09-19 11:07:10'),(128,NULL,NULL,'Admin',NULL,'Admin','Policy Records','Archived policy record #77','Completed',NULL,'','',NULL,NULL,NULL,NULL,NULL,'2026-09-19 11:07:16'),(129,NULL,NULL,'Admin',NULL,'Admin','Policy Records','Archived policy record #76','Completed',NULL,'','',NULL,NULL,NULL,NULL,NULL,'2026-09-19 11:07:23'),(130,NULL,NULL,'Admin',NULL,'Admin','Policy Records','Archived policy record #75','Completed',NULL,'','',NULL,NULL,NULL,NULL,NULL,'2026-09-19 11:07:31'),(131,NULL,NULL,'Admin',NULL,'Admin','Policy Records','Archived policy record #62','Completed',NULL,'','',NULL,NULL,NULL,NULL,NULL,'2026-09-19 11:07:38'),(132,NULL,NULL,'Admin',NULL,'Admin','Policy Records','Archived policy record #63','Completed',NULL,'','',NULL,NULL,NULL,NULL,NULL,'2026-09-19 11:07:45'),(133,NULL,NULL,'Admin',NULL,'Admin','Policy Records','Archived policy record #64','Completed',NULL,'','',NULL,NULL,NULL,NULL,NULL,'2026-09-19 11:07:52'),(134,NULL,NULL,'Admin',NULL,'Admin','Policy Records','Archived policy record #61','Completed',NULL,'','',NULL,NULL,NULL,NULL,NULL,'2026-09-19 11:07:59'),(135,NULL,NULL,'Admin',NULL,'Admin','Policy Records','Archived policy record #60','Completed',NULL,'','',NULL,NULL,NULL,NULL,NULL,'2026-09-19 11:08:06'),(136,NULL,NULL,'Admin',NULL,'Admin','Policy Records','Archived policy record #59','Completed',NULL,'','',NULL,NULL,NULL,NULL,NULL,'2026-09-19 11:08:16'),(137,NULL,NULL,'Admin',NULL,'Admin','Policy Records','Archived policy record #50','Completed',NULL,'','',NULL,NULL,NULL,NULL,NULL,'2026-09-19 11:08:22'),(138,NULL,NULL,'Admin',NULL,'Admin','Policy Records','Permanently deleted policy \"Manila Ordinance No 2026 053 (checked)\" (ID #91)','Completed',NULL,'','',NULL,NULL,NULL,NULL,NULL,'2026-09-19 11:09:20'),(139,NULL,NULL,'Admin',NULL,'Admin','Policy Records','Permanently deleted policy \"Manila Ordinance No 2026 050\" (ID #90)','Completed',NULL,'','',NULL,NULL,NULL,NULL,NULL,'2026-09-19 11:09:25'),(140,NULL,NULL,'Admin',NULL,'Admin','Policy Records','Permanently deleted policy \"Manila Ordinance No 2026 051 (checked)\" (ID #89)','Completed',NULL,'','',NULL,NULL,NULL,NULL,NULL,'2026-09-19 11:09:29'),(141,NULL,NULL,'Admin',NULL,'Admin','Policy Records','Permanently deleted policy \"Manila Ordinance No 2026 047\" (ID #88)','Completed',NULL,'','',NULL,NULL,NULL,NULL,NULL,'2026-09-19 11:09:34'),(142,NULL,NULL,'Admin',NULL,'Admin','Policy Records','Permanently deleted policy \"Manila Ordinance No 2026 049\" (ID #87)','Completed',NULL,'','',NULL,NULL,NULL,NULL,NULL,'2026-09-19 11:09:39'),(143,NULL,NULL,'Admin',NULL,'Admin','Policy Records','Permanently deleted policy \"Manila Ordinance No 2026 051 (checked)\" (ID #86)','Completed',NULL,'','',NULL,NULL,NULL,NULL,NULL,'2026-09-19 11:09:44'),(144,NULL,NULL,'Admin',NULL,'Admin','Policy Records','Permanently deleted policy \"Manila Ordinance No 2026 053 (checked)\" (ID #85)','Completed',NULL,'','',NULL,NULL,NULL,NULL,NULL,'2026-09-19 11:09:56'),(145,NULL,NULL,'Admin',NULL,'Admin','Policy Records','Permanently deleted policy \"Manila Ordinance No 2026 050\" (ID #84)','Completed',NULL,'','',NULL,NULL,NULL,NULL,NULL,'2026-09-19 11:10:02'),(146,NULL,NULL,'Admin',NULL,'Admin','Policy Records','Permanently deleted policy \"Manila Ordinance No 2026 045\" (ID #83)','Completed',NULL,'','',NULL,NULL,NULL,NULL,NULL,'2026-09-19 11:10:12'),(147,NULL,NULL,'Admin',NULL,'Admin','Policy Records','Permanently deleted policy \"_______________________________   _______________________________ City Councilor, Sponsor   City Vice Mayor / Presiding Officer\" (ID #82)','Completed',NULL,'','',NULL,NULL,NULL,NULL,NULL,'2026-09-19 11:10:17'),(148,NULL,NULL,'Admin',NULL,'Admin','Policy Records','Permanently deleted policy \"_______________________________   _______________________________ City Councilor, Sponsor   City Vice Mayor / Presiding Officer\" (ID #81)','Completed',NULL,'','',NULL,NULL,NULL,NULL,NULL,'2026-09-19 11:10:24'),(149,NULL,NULL,'Admin',NULL,'Admin','Policy Records','Permanently deleted policy \"Community Safety and Crime Prevention Strategy for Manila City\" (ID #80)','Completed',NULL,'','',NULL,NULL,NULL,NULL,NULL,'2026-09-19 11:10:32'),(150,NULL,NULL,'Admin',NULL,'Admin','Policy Records','Permanently deleted policy \"Improvement Strategy for Public Health Services in Manila City\" (ID #79)','Completed',NULL,'','',NULL,NULL,NULL,NULL,NULL,'2026-09-19 11:10:40'),(151,NULL,NULL,'Admin',NULL,'Admin','Policy Records','Permanently deleted policy \"Improvement Strategy for Public Health Services in Manila City\" (ID #78)','Completed',NULL,'','',NULL,NULL,NULL,NULL,NULL,'2026-09-19 11:10:46'),(152,NULL,NULL,'Admin',NULL,'Admin','Policy Records','Permanently deleted policy \"Community Safety and Crime Prevention Strategy for Manila City\" (ID #77)','Completed',NULL,'','',NULL,NULL,NULL,NULL,NULL,'2026-09-19 11:10:51'),(153,NULL,NULL,'Admin',NULL,'Admin','Policy Records','Permanently deleted policy \"Improvement Strategy for Public Health Services in Manila City\" (ID #76)','Completed',NULL,'','',NULL,NULL,NULL,NULL,NULL,'2026-09-19 11:10:56'),(154,NULL,NULL,'Admin',NULL,'Admin','Policy Records','Permanently deleted policy \"Public Transportation Efficiency Improvement Plan for Manila City\" (ID #75)','Completed',NULL,'','',NULL,NULL,NULL,NULL,NULL,'2026-09-19 11:11:01'),(155,NULL,NULL,'Admin',NULL,'Admin','Policy Records','Permanently deleted policy \"QC Ordinance No. SP-2876: Comprehensive Single-Use Plastic Regulation & Recovery Framework\" (ID #62)','Completed',NULL,'','',NULL,NULL,NULL,NULL,NULL,'2026-09-19 11:11:07'),(156,NULL,NULL,'Admin',NULL,'Admin','Policy Records','Permanently deleted policy \"QC Ordinance No. SP-2350: Quezon City Green Building & Energy Efficiency Code\" (ID #63)','Completed',NULL,'','',NULL,NULL,NULL,NULL,NULL,'2026-09-19 11:11:13'),(157,NULL,NULL,'Admin',NULL,'Admin','Policy Records','Permanently deleted policy \"Pasig City Ordinance No. 12: People-Centric Mobility & Protected Bike Lane Network System\" (ID #64)','Completed',NULL,'','',NULL,NULL,NULL,NULL,NULL,'2026-09-19 11:11:22'),(158,NULL,NULL,'Admin',NULL,'Admin','Policy Records','Permanently deleted policy \"Public Transportation Efficiency Improvement Plan for Manila City\" (ID #61)','Completed',NULL,'','',NULL,NULL,NULL,NULL,NULL,'2026-09-19 11:11:26'),(159,NULL,NULL,'Admin',NULL,'Admin','Policy Records','Permanently deleted policy \"Improvement Strategy for Public Health Services in Manila City\" (ID #60)','Completed',NULL,'','',NULL,NULL,NULL,NULL,NULL,'2026-09-19 11:11:30'),(160,NULL,NULL,'Admin',NULL,'Admin','Policy Records','Permanently deleted policy \"Community Safety and Crime Prevention Strategy for Manila City\" (ID #59)','Completed',NULL,'','',NULL,NULL,NULL,NULL,NULL,'2026-09-19 11:11:34'),(161,NULL,NULL,'Admin',NULL,'Admin','Policy Records','Permanently deleted policy \"Urban Traffic Congestion Study in Manila City\" (ID #50)','Completed',NULL,'','',NULL,NULL,NULL,NULL,NULL,'2026-09-19 11:11:38'),(162,NULL,NULL,'Admin',NULL,'Admin','Policy Records','Permanently deleted policy \"Flood Risk Assessment and Drainage Improvement Plan for Manila City\" (ID #49)','Completed',NULL,'','',NULL,NULL,NULL,NULL,NULL,'2026-09-19 11:11:42'),(163,NULL,NULL,'Admin',NULL,'Admin','Policy Records','Permanently deleted policy \"National Clean Energy Grid Modernization Act: Economic and Environmental Impact Assessment\" (ID #47)','Completed',NULL,'','',NULL,NULL,NULL,NULL,NULL,'2026-09-19 11:11:46'),(164,NULL,NULL,'Admin',NULL,'Admin','Policy Records','Uploaded Manila Ordinance No 2026 053 (checked)','Completed',NULL,'','',NULL,NULL,NULL,NULL,NULL,'2026-09-19 11:21:23'),(165,NULL,NULL,'Admin',NULL,'Admin','Evaluations','Evaluated policy: Manila Ordinance No 2026 053 (checked)','Completed',NULL,'','',NULL,NULL,NULL,NULL,NULL,'2026-09-19 11:22:08'),(166,NULL,NULL,'Admin',NULL,'Admin','Policy Records','Uploaded Manila Ordinance No 2026 050','Completed',NULL,'','',NULL,NULL,NULL,NULL,NULL,'2026-09-19 12:53:10'),(167,NULL,NULL,'Admin',NULL,'Admin','Evaluations','Evaluated policy: Manila Ordinance No 2026 050','Completed',NULL,'','',NULL,NULL,NULL,NULL,NULL,'2026-09-19 12:53:41'),(168,NULL,NULL,'Admin',NULL,'Admin','Policy Records','Archived policy record #93','Completed',NULL,'','',NULL,NULL,NULL,NULL,NULL,'2026-09-19 12:54:30'),(169,NULL,NULL,'Admin',NULL,'Admin','Policy Records','Permanently deleted policy \"Manila Ordinance No 2026 050\" (ID #93)','Completed',NULL,'','',NULL,NULL,NULL,NULL,NULL,'2026-09-19 12:54:36'),(170,NULL,NULL,'Admin',NULL,'Admin','Policy Records','Uploaded Manila Ordinance No 2026 050','Completed',NULL,'','',NULL,NULL,NULL,NULL,NULL,'2026-09-19 12:55:37'),(171,NULL,NULL,'Admin',NULL,'Admin','Evaluations','Evaluated policy: Manila Ordinance No 2026 050','Completed',NULL,'','',NULL,NULL,NULL,NULL,NULL,'2026-09-19 12:55:53'),(172,NULL,NULL,'Admin',NULL,'Admin','Policy Records','Uploaded Manila Ordinance No 2026 048','Completed',NULL,'','',NULL,NULL,NULL,NULL,NULL,'2026-09-19 13:36:32'),(173,NULL,NULL,'Admin',NULL,'Admin','Evaluations','Evaluated policy: Manila Ordinance No 2026 048','Completed',NULL,'','',NULL,NULL,NULL,NULL,NULL,'2026-09-19 13:36:45'),(174,NULL,NULL,'Admin',NULL,'Admin','Policy Records','Archived policy record #95','Completed',NULL,'','',NULL,NULL,NULL,NULL,NULL,'2026-09-19 13:47:51'),(175,NULL,NULL,'Admin',NULL,'Admin','Policy Records','Archived policy record #95','Completed',NULL,'','',NULL,NULL,NULL,NULL,NULL,'2026-09-19 13:48:00'),(176,NULL,NULL,'Admin',NULL,'Admin','Policy Records','Archived policy record #95','Completed',NULL,'','',NULL,NULL,NULL,NULL,NULL,'2026-09-19 13:48:32'),(177,NULL,NULL,'Admin',NULL,'Admin','Policy Records','Archived policy record #94','Completed',NULL,'','',NULL,NULL,NULL,NULL,NULL,'2026-09-19 13:48:57'),(178,NULL,NULL,'Admin',NULL,'Admin','Policy Records','Archived policy record #92','Completed',NULL,'','',NULL,NULL,NULL,NULL,NULL,'2026-09-19 13:59:12'),(179,NULL,NULL,'Admin',NULL,'Admin','Policy Records','Permanently deleted policy \"Manila Ordinance No 2026 048\" (ID #95)','Completed',NULL,'','',NULL,NULL,NULL,NULL,NULL,'2026-09-19 13:59:21'),(180,NULL,NULL,'Admin',NULL,'Admin','Policy Records','Permanently deleted policy \"Manila Ordinance No 2026 050\" (ID #94)','Completed',NULL,'','',NULL,NULL,NULL,NULL,NULL,'2026-09-19 13:59:27'),(181,NULL,NULL,'Admin',NULL,'Admin','Policy Records','Permanently deleted policy \"Manila Ordinance No 2026 053 (checked)\" (ID #92)','Completed',NULL,'','',NULL,NULL,NULL,NULL,NULL,'2026-09-19 13:59:35'),(182,NULL,NULL,'Admin',NULL,'Admin','Policy Records','Uploaded Manila Ordinance No 2026 053 (checked)','Completed',NULL,'','',NULL,NULL,NULL,NULL,NULL,'2026-09-19 14:00:16'),(183,NULL,NULL,'Admin',NULL,'Admin','Policy Records','Uploaded Manila Ordinance No 2026 051 (checked)','Completed',NULL,'','',NULL,NULL,NULL,NULL,NULL,'2026-09-19 14:00:33'),(184,NULL,NULL,'Admin',NULL,'Admin','Evaluations','Evaluated policy: Manila Ordinance No 2026 051 (checked)','Completed',NULL,'','',NULL,NULL,NULL,NULL,NULL,'2026-09-19 14:00:45'),(185,NULL,NULL,'Admin',NULL,'Admin','Evaluations','Evaluated policy: Manila Ordinance No 2026 053 (checked)','Completed',NULL,'','',NULL,NULL,NULL,NULL,NULL,'2026-09-19 14:38:05'),(186,NULL,NULL,'Admin',NULL,'Admin','Policy Records','Uploaded Manila Ordinance No 2026 049','Completed',NULL,'','',NULL,NULL,NULL,NULL,NULL,'2026-09-19 14:38:28'),(187,NULL,NULL,'Admin',NULL,'Admin','Evaluations','Evaluated policy: Manila Ordinance No 2026 049','Completed',NULL,'','',NULL,NULL,NULL,NULL,NULL,'2026-09-19 14:38:48'),(188,NULL,NULL,'Admin',NULL,'Admin','Policy Records','Uploaded Manila Ordinance No 2026 046','Completed',NULL,'','',NULL,NULL,NULL,NULL,NULL,'2026-09-19 14:39:40'),(189,NULL,NULL,'Admin',NULL,'Admin','Evaluations','Evaluated policy: Manila Ordinance No 2026 046','Completed',NULL,'','',NULL,NULL,NULL,NULL,NULL,'2026-09-19 14:39:50'),(190,NULL,NULL,'Admin',NULL,'Admin','Policy Records','Uploaded Flood Risk Assessment and Drainage Improvement Plan for Manila City','Completed',NULL,'','',NULL,NULL,NULL,NULL,NULL,'2026-09-19 16:04:11'),(191,NULL,NULL,'Admin',NULL,'Admin','Evaluations','Evaluated policy: Flood Risk Assessment and Drainage Improvement Plan for Manila City','Completed',NULL,'','',NULL,NULL,NULL,NULL,NULL,'2026-09-19 16:04:22'),(192,NULL,NULL,'Admin',NULL,'Admin','Policy Records','Uploaded Improvement Strategy for Public Health Services in Manila City','Completed',NULL,'','',NULL,NULL,NULL,NULL,NULL,'2026-09-19 16:33:34'),(193,NULL,NULL,'Admin',NULL,'Admin','Evaluations','Evaluated policy: Improvement Strategy for Public Health Services in Manila City','Completed',NULL,'','',NULL,NULL,NULL,NULL,NULL,'2026-09-19 16:33:45'),(194,NULL,NULL,'Christian',NULL,'Staff','System','User logout','Completed',NULL,'','',NULL,NULL,NULL,NULL,NULL,'2026-09-19 16:35:09'),(195,NULL,NULL,'renz',NULL,'Staff','System','Generated 2FA login OTP','Completed',NULL,'','',NULL,NULL,NULL,NULL,NULL,'2026-09-19 16:35:34'),(196,NULL,NULL,'renz',NULL,'Staff','System','Completed 2FA OTP login verification','Completed',NULL,'','',NULL,NULL,NULL,NULL,NULL,'2026-09-19 16:35:53'),(197,NULL,NULL,'renz',NULL,'Staff','System','User login','Completed',NULL,'','',NULL,NULL,NULL,NULL,NULL,'2026-09-19 16:35:55'),(198,NULL,NULL,'Salim',NULL,'Staff','System','User logout','Completed',NULL,'','',NULL,NULL,NULL,NULL,NULL,'2026-09-19 17:58:18'),(199,NULL,NULL,'Christian',NULL,'Staff','System','User login','Completed',NULL,'','',NULL,NULL,NULL,NULL,NULL,'2026-09-19 17:58:25'),(200,NULL,NULL,'Admin',NULL,'Admin','Evaluations','Evaluated policy: Executive Helicopter Transit and VIP Luxury Fleet Acquisition Act of 2026','Completed',NULL,'','',NULL,NULL,NULL,NULL,NULL,'2026-09-19 18:14:00'),(201,NULL,NULL,'Admin',NULL,'Admin','User Directory','Provisioned new account for daniel','Completed',NULL,'','',NULL,NULL,NULL,NULL,NULL,'2026-09-19 18:16:31'),(202,NULL,NULL,'Christian',NULL,'Staff','System','User logout','Completed',NULL,'','',NULL,NULL,NULL,NULL,NULL,'2026-09-19 18:16:42'),(203,NULL,NULL,'daniel',NULL,'Staff','System','Generated 2FA login OTP','Completed',NULL,'','',NULL,NULL,NULL,NULL,NULL,'2026-09-19 18:17:39'),(204,NULL,NULL,'daniel',NULL,'Staff','System','Completed 2FA OTP login verification','Completed',NULL,'','',NULL,NULL,NULL,NULL,NULL,'2026-09-19 18:17:54'),(205,NULL,NULL,'daniel',NULL,'Staff','System','User login','Completed',NULL,'','',NULL,NULL,NULL,NULL,NULL,'2026-09-19 18:17:56'),(206,NULL,NULL,'daniel',NULL,'Staff','System','User logout','Completed',NULL,'','',NULL,NULL,NULL,NULL,NULL,'2026-09-19 18:19:49'),(207,NULL,NULL,'renz',NULL,'Staff','System','Generated 2FA login OTP','Completed',NULL,'','',NULL,NULL,NULL,NULL,NULL,'2026-09-19 18:20:13'),(208,NULL,NULL,'renz',NULL,'Staff','System','Completed 2FA OTP login verification','Completed',NULL,'','',NULL,NULL,NULL,NULL,NULL,'2026-09-19 18:20:32'),(209,NULL,NULL,'renz',NULL,'Staff','System','User login','Completed',NULL,'','',NULL,NULL,NULL,NULL,NULL,'2026-09-19 18:20:35'),(210,NULL,NULL,'Salim',NULL,'Staff','System','User logout','Completed',NULL,'','',NULL,NULL,NULL,NULL,NULL,'2026-09-19 18:21:08'),(211,NULL,NULL,'Christian',NULL,'Staff','System','User login','Completed',NULL,'','',NULL,NULL,NULL,NULL,NULL,'2026-09-19 18:21:19'),(212,NULL,NULL,'Christian',NULL,'Staff','System','User logout','Completed',NULL,'','',NULL,NULL,NULL,NULL,NULL,'2026-09-19 19:17:15'),(213,NULL,NULL,'Christian',NULL,'Staff','System','User login','Completed',NULL,'','',NULL,NULL,NULL,NULL,NULL,'2026-09-19 19:23:54');
/*!40000 ALTER TABLE `audit_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `committee_members`
--

DROP TABLE IF EXISTS `committee_members`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `committee_members` (
  `committee_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `position` varchar(100) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `assigned_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`committee_id`,`user_id`),
  KEY `fk_committee_members_user` (`user_id`),
  CONSTRAINT `fk_committee_members_committee` FOREIGN KEY (`committee_id`) REFERENCES `committees` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_committee_members_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `committee_members`
--

LOCK TABLES `committee_members` WRITE;
/*!40000 ALTER TABLE `committee_members` DISABLE KEYS */;
/*!40000 ALTER TABLE `committee_members` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `committees`
--

DROP TABLE IF EXISTS `committees`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `committees` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `office_id` int(11) DEFAULT NULL,
  `status` varchar(30) DEFAULT 'Active',
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_committees_office` (`office_id`),
  CONSTRAINT `fk_committees_office` FOREIGN KEY (`office_id`) REFERENCES `offices` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `committees`
--

LOCK TABLES `committees` WRITE;
/*!40000 ALTER TABLE `committees` DISABLE KEYS */;
/*!40000 ALTER TABLE `committees` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `comparisons`
--

DROP TABLE IF EXISTS `comparisons`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `comparisons` (
  `comparison_id` int(11) NOT NULL AUTO_INCREMENT,
  `policy_one` int(11) DEFAULT NULL,
  `policy_two` int(11) DEFAULT NULL,
  `comparison_summary` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`comparison_id`),
  KEY `comparisons_ibfk_1` (`policy_one`),
  KEY `comparisons_ibfk_2` (`policy_two`),
  CONSTRAINT `comparisons_ibfk_1` FOREIGN KEY (`policy_one`) REFERENCES `policy_records` (`id`) ON DELETE CASCADE,
  CONSTRAINT `comparisons_ibfk_2` FOREIGN KEY (`policy_two`) REFERENCES `policy_records` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `comparisons`
--

LOCK TABLES `comparisons` WRITE;
/*!40000 ALTER TABLE `comparisons` DISABLE KEYS */;
/*!40000 ALTER TABLE `comparisons` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `departments`
--

DROP TABLE IF EXISTS `departments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `departments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `office_id` int(11) DEFAULT NULL,
  `code` varchar(50) DEFAULT NULL,
  `name` varchar(200) NOT NULL,
  `status` varchar(30) DEFAULT 'Active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`),
  KEY `fk_departments_office` (`office_id`),
  CONSTRAINT `fk_departments_office` FOREIGN KEY (`office_id`) REFERENCES `offices` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `departments`
--

LOCK TABLES `departments` WRITE;
/*!40000 ALTER TABLE `departments` DISABLE KEYS */;
/*!40000 ALTER TABLE `departments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `documents`
--

DROP TABLE IF EXISTS `documents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `documents` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_documents_system` (`system_id`),
  KEY `fk_documents_legislative_item` (`legislative_item_id`),
  KEY `fk_documents_uploaded_by` (`uploaded_by`),
  CONSTRAINT `fk_documents_legislative_item` FOREIGN KEY (`legislative_item_id`) REFERENCES `legislative_items` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_documents_system` FOREIGN KEY (`system_id`) REFERENCES `systems` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_documents_uploaded_by` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `documents`
--

LOCK TABLES `documents` WRITE;
/*!40000 ALTER TABLE `documents` DISABLE KEYS */;
/*!40000 ALTER TABLE `documents` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `evaluation_versions`
--

DROP TABLE IF EXISTS `evaluation_versions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `evaluation_versions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `policy_id` int(11) NOT NULL,
  `version_number` int(11) NOT NULL DEFAULT 1,
  `version_label` varchar(50) DEFAULT 'Version 1',
  `evaluator` varchar(100) DEFAULT 'Admin',
  `city_origin` varchar(100) DEFAULT 'City of Manila',
  `risk_level` varchar(50) DEFAULT 'Low Risk',
  `economic_score` decimal(4,2) DEFAULT 8.00,
  `social_score` decimal(4,2) DEFAULT 8.00,
  `environmental_score` decimal(4,2) DEFAULT 8.00,
  `legal_score` decimal(4,2) DEFAULT 8.00,
  `overall_score` decimal(4,2) DEFAULT 8.00,
  `ai_recommendation` text DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `status` varchar(50) DEFAULT 'Approved',
  `approved_by` varchar(255) DEFAULT 'System Administrator',
  `approved_at` datetime DEFAULT current_timestamp(),
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_eval_versions_policy` (`policy_id`),
  KEY `idx_eval_versions_status` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=93 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `evaluation_versions`
--

LOCK TABLES `evaluation_versions` WRITE;
/*!40000 ALTER TABLE `evaluation_versions` DISABLE KEYS */;
INSERT INTO `evaluation_versions` VALUES (1,47,1,'Version 1','Administration','City of Manila','Medium',0.00,0.00,0.00,0.00,6.50,'Conditional Support pending Local Grid Compatibility and Phased Tariff Protections','{\"ai_analysis\":\"For Manila City municipal operations, integrating with a modernized national clean energy grid will stabilize power supply for critical public services and reduce emergency operational costs. Enhanced energy resilience will protect local businesses from costly power disruptions, thereby fortifying the city\'s overall economic stability. Ultimately, community welfare will improve through reduced reliance on diesel backup generators and lower urban ambient air pollution.\",\"reason\":\"The policy strategically aligns with long-term urban resilience goals, but implementation risks regarding local municipal utility integration and transitional consumer cost increases must be actively mitigated.\",\"improvements\":[\"Establish clear municipal public-private partnership frameworks to co-finance local smart-grid distribution upgrades without overburdening city coffers.\",\"Include explicit low-income tariff shielding provisions to protect vulnerable households from potential short-term transition costs.\",\"Formulate joint task forces between national energy agencies and local government units to streamline urban rights-of-way permitting for grid infrastructure.\"],\"criteria\":{\"economic\":{\"level\":\"High\",\"reason\":\"While requiring substantial initial capital investment for infrastructure upgrades, grid modernization yields significant long-term economic returns through reduced transmission losses and energy price stabilization.\"},\"social\":{\"level\":\"Medium\",\"reason\":\"Upgrading national grid reliability ensures equitable electricity access and minimizes power outages, though construction phases may cause minor local community disruptions.\"},\"env\":{\"level\":\"High\",\"reason\":\"Directly enables the large-scale integration of renewable energy sources into the power grid, substantially lowering national greenhouse gas emissions.\"},\"legal\":{\"level\":\"Medium\",\"reason\":\"Implementation requires navigating complex energy regulatory frameworks, municipal rights-of-way, and statutory compliance under national power sector laws.\"}}}','Re Evaluate',NULL,'2026-08-30 21:50:35','2026-08-12 13:37:40'),(2,49,1,'Version 1','Administration','City of Manila','Low',0.00,0.00,0.00,0.00,8.50,'Approve & Proceed to Full Implementation','{\"ai_analysis\":\"Comprehensive evaluation of \\\"Flood Risk Assessment and Drainage Improvement Plan for Manila City\\\" indicates high operational viability and strong alignment with municipal governance objectives.\",\"reason\":\"Analysis shows minimal public risk and high long-term community benefit across all evaluated parameters.\",\"improvements\":[\"Establish quarterly district performance reviews\",\"Deploy digital monitoring tools across participating departments\",\"Conduct public feedback surveys after 6 months of rollout\"],\"criteria\":{\"economic\":{\"level\":\"Low\",\"reason\":\"Funding and resource allocations for \\\"Flood Risk Assessment and Drainage Improvement Plan for Manila City\\\" are sustainable within Manila municipal budgets.\"},\"social\":{\"level\":\"Low\",\"reason\":\"Enhances public welfare and community safety across Manila districts.\"},\"env\":{\"level\":\"Low\",\"reason\":\"Minimal ecological impact with positive sustainable urban alignment.\"},\"legal\":{\"level\":\"Low\",\"reason\":\"Fully compliant with existing national legislative frameworks and city ordinances.\"}}}','Re Evaluate',NULL,'2026-08-30 21:50:35','2026-08-15 15:59:34'),(3,50,1,'Version 1','Staff','City of Manila','Low',0.00,0.00,0.00,0.00,8.50,'Approve & Proceed to Full Implementation','{\"ai_analysis\":\"Comprehensive evaluation of \\\"Urban Traffic Congestion Study in Manila City\\\" indicates high operational viability, low public implementation risk, and strong strategic alignment with Manila City Hall legislative objectives.\",\"reason\":\"Detailed assessment demonstrates minimal public risk and high long-term community benefits.\",\"improvements\":[\"Establish quarterly district performance monitoring reviews\",\"Deploy digital asset management dashboards across participating departments\",\"Conduct community feedback surveys after 6 months of ordinance rollout\"],\"criteria\":{\"economic\":{\"level\":\"Low\",\"reason\":\"Funding and budget allocations for \\\"Urban Traffic Congestion Study in Manila City\\\" are manageable within Manila City Hall fiscal programs.\"},\"social\":{\"level\":\"Low\",\"reason\":\"Enhances public welfare, community health, and district safety across Manila City.\"},\"env\":{\"level\":\"Low\",\"reason\":\"Minimal ecological footprint with positive sustainable urban development alignment.\"},\"legal\":{\"level\":\"Low\",\"reason\":\"Fully compliant with existing national legislative frameworks and Manila City Ordinances.\"}}}','Evaluated','Quintanaasdsa','2026-08-30 20:39:05','2026-08-19 23:56:56'),(4,60,1,'Version 1','Staff','City of Manila','Low',0.00,0.00,0.00,0.00,8.50,'Approve & Proceed to Full Implementation','{\"ai_analysis\":\"Comprehensive evaluation of \\\"Improvement Strategy - Public Health & Wellness Action Plan\\\" indicates high operational viability, low public implementation risk, and strong strategic alignment with Manila City Hall legislative objectives.\",\"reason\":\"Detailed assessment demonstrates minimal public risk and high long-term community benefits.\",\"improvements\":[\"Establish quarterly district performance monitoring reviews\",\"Deploy digital asset management dashboards across participating departments\",\"Conduct community feedback surveys after 6 months of ordinance rollout\"],\"criteria\":{\"economic\":{\"level\":\"Low\",\"reason\":\"Funding and budget allocations for \\\"Improvement Strategy - Public Health & Wellness Action Plan\\\" are manageable within Manila City Hall fiscal programs.\"},\"social\":{\"level\":\"Low\",\"reason\":\"Enhances public welfare, community health, and district safety across Manila City.\"},\"env\":{\"level\":\"Low\",\"reason\":\"Minimal ecological footprint with positive sustainable urban development alignment.\"},\"legal\":{\"level\":\"Low\",\"reason\":\"Fully compliant with existing national legislative frameworks and Manila City Ordinances.\"}}}','Evaluated','Quintanaasdsa','2026-08-30 19:56:55','2026-08-30 17:44:11'),(8,59,1,'Version 1','Admin','City of Manila','Low',8.50,8.50,8.50,8.50,8.50,'Approve & Proceed to Full Implementation','{\"ai_analysis\":\"Comprehensive evaluation of \\\"Community Safety - Comprehensive Social Welfare & Community Support Initiative\\\" indicates high operational viability, low public implementation risk, and strong strategic alignment with Manila City Hall legislative objectives.\",\"reason\":\"Detailed assessment demonstrates minimal public risk and high long-term community benefits.\",\"improvements\":[\"Establish quarterly district performance monitoring reviews\",\"Deploy digital asset management dashboards across participating departments\",\"Conduct community feedback surveys after 6 months of ordinance rollout\"],\"criteria\":{\"economic\":{\"level\":\"Low\",\"reason\":\"Funding and budget allocations for \\\"Community Safety - Comprehensive Social Welfare & Community Support Initiative\\\" are manageable within Manila City Hall fiscal programs.\"},\"social\":{\"level\":\"Low\",\"reason\":\"Enhances public welfare, community health, and district safety across Manila City.\"},\"env\":{\"level\":\"Low\",\"reason\":\"Minimal ecological footprint with positive sustainable urban development alignment.\"},\"legal\":{\"level\":\"Low\",\"reason\":\"Fully compliant with existing national legislative frameworks and Manila City Ordinances.\"}}}','Re Evaluate','System Administrator','2026-09-01 11:58:55','2026-09-01 11:58:55'),(9,59,2,'Version 2','Admin','City of Manila','Low',8.50,8.50,8.50,8.50,8.50,'Approve & Proceed to Full Implementation','{\"ai_analysis\":\"Comprehensive evaluation of \\\"Community Safety - Comprehensive Social Welfare & Community Support Initiative\\\" indicates high operational viability, low public implementation risk, and strong strategic alignment with Manila City Hall legislative objectives.\",\"reason\":\"Detailed assessment demonstrates minimal public risk and high long-term community benefits.\",\"improvements\":[\"Establish quarterly district performance monitoring reviews\",\"Deploy digital asset management dashboards across participating departments\",\"Conduct community feedback surveys after 6 months of ordinance rollout\"],\"criteria\":{\"economic\":{\"level\":\"Low\",\"reason\":\"Funding and budget allocations for \\\"Community Safety - Comprehensive Social Welfare & Community Support Initiative\\\" are manageable within Manila City Hall fiscal programs.\"},\"social\":{\"level\":\"Low\",\"reason\":\"Enhances public welfare, community health, and district safety across Manila City.\"},\"env\":{\"level\":\"Low\",\"reason\":\"Minimal ecological footprint with positive sustainable urban development alignment.\"},\"legal\":{\"level\":\"Low\",\"reason\":\"Fully compliant with existing national legislative frameworks and Manila City Ordinances.\"}}}','Re Evaluate','System Administrator','2026-09-01 11:58:59','2026-09-01 11:58:59'),(10,59,3,'Version 3','Admin','City of Manila','Low',8.50,8.50,8.50,8.50,8.50,'Approve & Proceed to Full Implementation','{\"ai_analysis\":\"Comprehensive evaluation of \\\"Community Safety - Comprehensive Social Welfare & Community Support Initiative\\\" indicates high operational viability, low public implementation risk, and strong strategic alignment with Manila City Hall legislative objectives.\",\"reason\":\"Detailed assessment demonstrates minimal public risk and high long-term community benefits.\",\"improvements\":[\"Establish quarterly district performance monitoring reviews\",\"Deploy digital asset management dashboards across participating departments\",\"Conduct community feedback surveys after 6 months of ordinance rollout\"],\"criteria\":{\"economic\":{\"level\":\"Low\",\"reason\":\"Funding and budget allocations for \\\"Community Safety - Comprehensive Social Welfare & Community Support Initiative\\\" are manageable within Manila City Hall fiscal programs.\"},\"social\":{\"level\":\"Low\",\"reason\":\"Enhances public welfare, community health, and district safety across Manila City.\"},\"env\":{\"level\":\"Low\",\"reason\":\"Minimal ecological footprint with positive sustainable urban development alignment.\"},\"legal\":{\"level\":\"Low\",\"reason\":\"Fully compliant with existing national legislative frameworks and Manila City Ordinances.\"}}}','Re Evaluate','System Administrator','2026-09-01 11:59:01','2026-09-01 11:59:01'),(11,59,4,'Version 4','Admin','City of Manila','Low',8.50,8.50,8.50,8.50,8.50,'Approve & Proceed to Full Implementation','{\"ai_analysis\":\"Comprehensive evaluation of \\\"Community Safety - Comprehensive Social Welfare & Community Support Initiative\\\" indicates high operational viability, low public implementation risk, and strong strategic alignment with Manila City Hall legislative objectives.\",\"reason\":\"Detailed assessment demonstrates minimal public risk and high long-term community benefits.\",\"improvements\":[\"Establish quarterly district performance monitoring reviews\",\"Deploy digital asset management dashboards across participating departments\",\"Conduct community feedback surveys after 6 months of ordinance rollout\"],\"criteria\":{\"economic\":{\"level\":\"Low\",\"reason\":\"Funding and budget allocations for \\\"Community Safety - Comprehensive Social Welfare & Community Support Initiative\\\" are manageable within Manila City Hall fiscal programs.\"},\"social\":{\"level\":\"Low\",\"reason\":\"Enhances public welfare, community health, and district safety across Manila City.\"},\"env\":{\"level\":\"Low\",\"reason\":\"Minimal ecological footprint with positive sustainable urban development alignment.\"},\"legal\":{\"level\":\"Low\",\"reason\":\"Fully compliant with existing national legislative frameworks and Manila City Ordinances.\"}}}','Re Evaluate','System Administrator','2026-09-01 11:59:11','2026-09-01 11:59:11'),(12,59,5,'Version 5','Admin','City of Manila','Low',8.50,8.50,8.50,8.50,8.50,'Approve & Proceed to Full Implementation','{\"ai_analysis\":\"Comprehensive evaluation of \\\"Community Safety - Comprehensive Social Welfare & Community Support Initiative\\\" indicates high operational viability, low public implementation risk, and strong strategic alignment with Manila City Hall legislative objectives.\",\"reason\":\"Detailed assessment demonstrates minimal public risk and high long-term community benefits.\",\"improvements\":[\"Establish quarterly district performance monitoring reviews\",\"Deploy digital asset management dashboards across participating departments\",\"Conduct community feedback surveys after 6 months of ordinance rollout\"],\"criteria\":{\"economic\":{\"level\":\"Low\",\"reason\":\"Funding and budget allocations for \\\"Community Safety - Comprehensive Social Welfare & Community Support Initiative\\\" are manageable within Manila City Hall fiscal programs.\"},\"social\":{\"level\":\"Low\",\"reason\":\"Enhances public welfare, community health, and district safety across Manila City.\"},\"env\":{\"level\":\"Low\",\"reason\":\"Minimal ecological footprint with positive sustainable urban development alignment.\"},\"legal\":{\"level\":\"Low\",\"reason\":\"Fully compliant with existing national legislative frameworks and Manila City Ordinances.\"}}}','Re Evaluate','System Administrator','2026-09-01 11:59:15','2026-09-01 11:59:15'),(13,59,6,'Version 6','Admin','City of Manila','Low',8.50,8.50,8.50,8.50,8.50,'Approve & Proceed to Full Implementation','{\"ai_analysis\":\"Comprehensive evaluation of \\\"Community Safety - Comprehensive Social Welfare & Community Support Initiative\\\" indicates high operational viability, low public implementation risk, and strong strategic alignment with Manila City Hall legislative objectives.\",\"reason\":\"Detailed assessment demonstrates minimal public risk and high long-term community benefits.\",\"improvements\":[\"Establish quarterly district performance monitoring reviews\",\"Deploy digital asset management dashboards across participating departments\",\"Conduct community feedback surveys after 6 months of ordinance rollout\"],\"criteria\":{\"economic\":{\"level\":\"Low\",\"reason\":\"Funding and budget allocations for \\\"Community Safety - Comprehensive Social Welfare & Community Support Initiative\\\" are manageable within Manila City Hall fiscal programs.\"},\"social\":{\"level\":\"Low\",\"reason\":\"Enhances public welfare, community health, and district safety across Manila City.\"},\"env\":{\"level\":\"Low\",\"reason\":\"Minimal ecological footprint with positive sustainable urban development alignment.\"},\"legal\":{\"level\":\"Low\",\"reason\":\"Fully compliant with existing national legislative frameworks and Manila City Ordinances.\"}}}','Re Evaluate','System Administrator','2026-09-01 11:59:25','2026-09-01 11:59:25'),(14,59,7,'Version 7','Admin','City of Manila','Low',8.50,8.50,8.50,8.50,8.50,'Approve & Proceed to Full Implementation','{\"ai_analysis\":\"Comprehensive evaluation of \\\"Community Safety - Comprehensive Social Welfare & Community Support Initiative\\\" indicates high operational viability, low public implementation risk, and strong strategic alignment with Manila City Hall legislative objectives.\",\"reason\":\"Detailed assessment demonstrates minimal public risk and high long-term community benefits.\",\"improvements\":[\"Establish quarterly district performance monitoring reviews\",\"Deploy digital asset management dashboards across participating departments\",\"Conduct community feedback surveys after 6 months of ordinance rollout\"],\"criteria\":{\"economic\":{\"level\":\"Low\",\"reason\":\"Funding and budget allocations for \\\"Community Safety - Comprehensive Social Welfare & Community Support Initiative\\\" are manageable within Manila City Hall fiscal programs.\"},\"social\":{\"level\":\"Low\",\"reason\":\"Enhances public welfare, community health, and district safety across Manila City.\"},\"env\":{\"level\":\"Low\",\"reason\":\"Minimal ecological footprint with positive sustainable urban development alignment.\"},\"legal\":{\"level\":\"Low\",\"reason\":\"Fully compliant with existing national legislative frameworks and Manila City Ordinances.\"}}}','Re Evaluate','System Administrator','2026-09-01 11:59:28','2026-09-01 11:59:28'),(15,59,8,'Version 8','Admin','City of Manila','Low',8.50,8.50,8.50,8.50,8.50,'Approve & Proceed to Full Implementation','{\"ai_analysis\":\"Comprehensive evaluation of \\\"Community Safety - Comprehensive Social Welfare & Community Support Initiative\\\" indicates high operational viability, low public implementation risk, and strong strategic alignment with Manila City Hall legislative objectives.\",\"reason\":\"Detailed assessment demonstrates minimal public risk and high long-term community benefits.\",\"improvements\":[\"Establish quarterly district performance monitoring reviews\",\"Deploy digital asset management dashboards across participating departments\",\"Conduct community feedback surveys after 6 months of ordinance rollout\"],\"criteria\":{\"economic\":{\"level\":\"Low\",\"reason\":\"Funding and budget allocations for \\\"Community Safety - Comprehensive Social Welfare & Community Support Initiative\\\" are manageable within Manila City Hall fiscal programs.\"},\"social\":{\"level\":\"Low\",\"reason\":\"Enhances public welfare, community health, and district safety across Manila City.\"},\"env\":{\"level\":\"Low\",\"reason\":\"Minimal ecological footprint with positive sustainable urban development alignment.\"},\"legal\":{\"level\":\"Low\",\"reason\":\"Fully compliant with existing national legislative frameworks and Manila City Ordinances.\"}}}','Re Evaluate','System Administrator','2026-09-01 11:59:30','2026-09-01 11:59:30'),(16,59,9,'Version 9','Admin','City of Manila','Low',8.50,8.50,8.50,8.50,8.50,'Approve & Proceed to Full Implementation','{\"ai_analysis\":\"Comprehensive evaluation of \\\"Community Safety - Comprehensive Social Welfare & Community Support Initiative\\\" indicates high operational viability, low public implementation risk, and strong strategic alignment with Manila City Hall legislative objectives.\",\"reason\":\"Detailed assessment demonstrates minimal public risk and high long-term community benefits.\",\"improvements\":[\"Establish quarterly district performance monitoring reviews\",\"Deploy digital asset management dashboards across participating departments\",\"Conduct community feedback surveys after 6 months of ordinance rollout\"],\"criteria\":{\"economic\":{\"level\":\"Low\",\"reason\":\"Funding and budget allocations for \\\"Community Safety - Comprehensive Social Welfare & Community Support Initiative\\\" are manageable within Manila City Hall fiscal programs.\"},\"social\":{\"level\":\"Low\",\"reason\":\"Enhances public welfare, community health, and district safety across Manila City.\"},\"env\":{\"level\":\"Low\",\"reason\":\"Minimal ecological footprint with positive sustainable urban development alignment.\"},\"legal\":{\"level\":\"Low\",\"reason\":\"Fully compliant with existing national legislative frameworks and Manila City Ordinances.\"}}}','Re Evaluate','System Administrator','2026-09-01 11:59:31','2026-09-01 11:59:31'),(17,59,10,'Version 10','Admin','City of Manila','Low',8.50,8.50,8.50,8.50,8.50,'Approve & Proceed to Full Implementation','{\"ai_analysis\":\"Comprehensive evaluation of \\\"Community Safety - Comprehensive Social Welfare & Community Support Initiative\\\" indicates high operational viability, low public implementation risk, and strong strategic alignment with Manila City Hall legislative objectives.\",\"reason\":\"Detailed assessment demonstrates minimal public risk and high long-term community benefits.\",\"improvements\":[\"Establish quarterly district performance monitoring reviews\",\"Deploy digital asset management dashboards across participating departments\",\"Conduct community feedback surveys after 6 months of ordinance rollout\"],\"criteria\":{\"economic\":{\"level\":\"Low\",\"reason\":\"Funding and budget allocations for \\\"Community Safety - Comprehensive Social Welfare & Community Support Initiative\\\" are manageable within Manila City Hall fiscal programs.\"},\"social\":{\"level\":\"Low\",\"reason\":\"Enhances public welfare, community health, and district safety across Manila City.\"},\"env\":{\"level\":\"Low\",\"reason\":\"Minimal ecological footprint with positive sustainable urban development alignment.\"},\"legal\":{\"level\":\"Low\",\"reason\":\"Fully compliant with existing national legislative frameworks and Manila City Ordinances.\"}}}','Re Evaluate','System Administrator','2026-09-01 11:59:32','2026-09-01 11:59:32'),(18,59,11,'Version 11','Admin','City of Manila','Low',8.50,8.50,8.50,8.50,8.50,'Approve & Proceed to Full Implementation','{\"ai_analysis\":\"Comprehensive evaluation of \\\"Community Safety - Comprehensive Social Welfare & Community Support Initiative\\\" indicates high operational viability, low public implementation risk, and strong strategic alignment with Manila City Hall legislative objectives.\",\"reason\":\"Detailed assessment demonstrates minimal public risk and high long-term community benefits.\",\"improvements\":[\"Establish quarterly district performance monitoring reviews\",\"Deploy digital asset management dashboards across participating departments\",\"Conduct community feedback surveys after 6 months of ordinance rollout\"],\"criteria\":{\"economic\":{\"level\":\"Low\",\"reason\":\"Funding and budget allocations for \\\"Community Safety - Comprehensive Social Welfare & Community Support Initiative\\\" are manageable within Manila City Hall fiscal programs.\"},\"social\":{\"level\":\"Low\",\"reason\":\"Enhances public welfare, community health, and district safety across Manila City.\"},\"env\":{\"level\":\"Low\",\"reason\":\"Minimal ecological footprint with positive sustainable urban development alignment.\"},\"legal\":{\"level\":\"Low\",\"reason\":\"Fully compliant with existing national legislative frameworks and Manila City Ordinances.\"}}}','Re Evaluate','System Administrator','2026-09-01 11:59:33','2026-09-01 11:59:33'),(19,59,12,'Version 12','Admin','City of Manila','Low',8.50,8.50,8.50,8.50,8.50,'Approve & Proceed to Full Implementation','{\"ai_analysis\":\"Comprehensive evaluation of \\\"Community Safety - Comprehensive Social Welfare & Community Support Initiative\\\" indicates high operational viability, low public implementation risk, and strong strategic alignment with Manila City Hall legislative objectives.\",\"reason\":\"Detailed assessment demonstrates minimal public risk and high long-term community benefits.\",\"improvements\":[\"Establish quarterly district performance monitoring reviews\",\"Deploy digital asset management dashboards across participating departments\",\"Conduct community feedback surveys after 6 months of ordinance rollout\"],\"criteria\":{\"economic\":{\"level\":\"Low\",\"reason\":\"Funding and budget allocations for \\\"Community Safety - Comprehensive Social Welfare & Community Support Initiative\\\" are manageable within Manila City Hall fiscal programs.\"},\"social\":{\"level\":\"Low\",\"reason\":\"Enhances public welfare, community health, and district safety across Manila City.\"},\"env\":{\"level\":\"Low\",\"reason\":\"Minimal ecological footprint with positive sustainable urban development alignment.\"},\"legal\":{\"level\":\"Low\",\"reason\":\"Fully compliant with existing national legislative frameworks and Manila City Ordinances.\"}}}','Re Evaluate','System Administrator','2026-09-01 11:59:37','2026-09-01 11:59:37'),(20,50,2,'Version 2','Admin','City of Manila','Low',8.50,8.50,8.50,8.50,8.50,'Approve & Proceed to Full Implementation','{\"ai_analysis\":\"Comprehensive evaluation of \\\"Urban Traffic Congestion Study in Manila City\\\" indicates high operational viability, low public implementation risk, and strong strategic alignment with Manila City Hall legislative objectives.\",\"reason\":\"Detailed assessment demonstrates minimal public risk and high long-term community benefits.\",\"improvements\":[\"Establish quarterly district performance monitoring reviews\",\"Deploy digital asset management dashboards across participating departments\",\"Conduct community feedback surveys after 6 months of ordinance rollout\"],\"criteria\":{\"economic\":{\"level\":\"Low\",\"reason\":\"Funding and budget allocations for \\\"Urban Traffic Congestion Study in Manila City\\\" are manageable within Manila City Hall fiscal programs.\"},\"social\":{\"level\":\"Low\",\"reason\":\"Enhances public welfare, community health, and district safety across Manila City.\"},\"env\":{\"level\":\"Low\",\"reason\":\"Minimal ecological footprint with positive sustainable urban development alignment.\"},\"legal\":{\"level\":\"Low\",\"reason\":\"Fully compliant with existing national legislative frameworks and Manila City Ordinances.\"}}}','Re Evaluate','System Administrator','2026-09-01 12:01:58','2026-09-01 12:01:58'),(21,50,3,'Version 3','Admin','City of Manila','Low',8.50,8.50,8.50,8.50,8.50,'Approve & Proceed to Full Implementation','{\"ai_analysis\":\"Comprehensive evaluation of \\\"Urban Traffic Congestion Study in Manila City\\\" indicates high operational viability, low public implementation risk, and strong strategic alignment with Manila City Hall legislative objectives.\",\"reason\":\"Detailed assessment demonstrates minimal public risk and high long-term community benefits.\",\"improvements\":[\"Establish quarterly district performance monitoring reviews\",\"Deploy digital asset management dashboards across participating departments\",\"Conduct community feedback surveys after 6 months of ordinance rollout\"],\"criteria\":{\"economic\":{\"level\":\"Low\",\"reason\":\"Funding and budget allocations for \\\"Urban Traffic Congestion Study in Manila City\\\" are manageable within Manila City Hall fiscal programs.\"},\"social\":{\"level\":\"Low\",\"reason\":\"Enhances public welfare, community health, and district safety across Manila City.\"},\"env\":{\"level\":\"Low\",\"reason\":\"Minimal ecological footprint with positive sustainable urban development alignment.\"},\"legal\":{\"level\":\"Low\",\"reason\":\"Fully compliant with existing national legislative frameworks and Manila City Ordinances.\"}}}','Re Evaluate','System Administrator','2026-09-01 12:02:07','2026-09-01 12:02:07'),(22,49,2,'Version 2','Admin','City of Manila','Low',8.50,8.50,8.50,8.50,8.50,'Approve & Proceed to Full Implementation','{\"ai_analysis\":\"Comprehensive evaluation of \\\"Flood Risk Assessment and Drainage Improvement Plan for Manila City\\\" indicates high operational viability, low public implementation risk, and strong strategic alignment with Manila City Hall legislative objectives.\",\"reason\":\"Detailed assessment demonstrates minimal public risk and high long-term community benefits.\",\"improvements\":[\"Establish quarterly district performance monitoring reviews\",\"Deploy digital asset management dashboards across participating departments\",\"Conduct community feedback surveys after 6 months of ordinance rollout\"],\"criteria\":{\"economic\":{\"level\":\"Low\",\"reason\":\"Funding and budget allocations for \\\"Flood Risk Assessment and Drainage Improvement Plan for Manila City\\\" are manageable within Manila City Hall fiscal programs.\"},\"social\":{\"level\":\"Low\",\"reason\":\"Enhances public welfare, community health, and district safety across Manila City.\"},\"env\":{\"level\":\"Low\",\"reason\":\"Minimal ecological footprint with positive sustainable urban development alignment.\"},\"legal\":{\"level\":\"Low\",\"reason\":\"Fully compliant with existing national legislative frameworks and Manila City Ordinances.\"}}}','Re Evaluate','System Administrator','2026-09-01 12:09:35','2026-09-01 12:09:35'),(23,49,3,'Version 3','Admin','City of Manila','Low',8.50,8.50,8.50,8.50,8.50,'Approve & Proceed to Full Implementation','{\"ai_analysis\":\"Comprehensive evaluation of \\\"Flood Risk Assessment and Drainage Improvement Plan for Manila City\\\" indicates high operational viability, low public implementation risk, and strong strategic alignment with Manila City Hall legislative objectives.\",\"reason\":\"Detailed assessment demonstrates minimal public risk and high long-term community benefits.\",\"improvements\":[\"Establish quarterly district performance monitoring reviews\",\"Deploy digital asset management dashboards across participating departments\",\"Conduct community feedback surveys after 6 months of ordinance rollout\"],\"criteria\":{\"economic\":{\"level\":\"Low\",\"reason\":\"Funding and budget allocations for \\\"Flood Risk Assessment and Drainage Improvement Plan for Manila City\\\" are manageable within Manila City Hall fiscal programs.\"},\"social\":{\"level\":\"Low\",\"reason\":\"Enhances public welfare, community health, and district safety across Manila City.\"},\"env\":{\"level\":\"Low\",\"reason\":\"Minimal ecological footprint with positive sustainable urban development alignment.\"},\"legal\":{\"level\":\"Low\",\"reason\":\"Fully compliant with existing national legislative frameworks and Manila City Ordinances.\"}}}','Re Evaluate','System Administrator','2026-09-01 12:09:38','2026-09-01 12:09:38'),(24,49,4,'Version 4','Admin','City of Manila','Low',8.50,8.50,8.50,8.50,8.50,'Approve & Proceed to Full Implementation','{\"ai_analysis\":\"Comprehensive evaluation of \\\"Flood Risk Assessment and Drainage Improvement Plan for Manila City\\\" indicates high operational viability, low public implementation risk, and strong strategic alignment with Manila City Hall legislative objectives.\",\"reason\":\"Detailed assessment demonstrates minimal public risk and high long-term community benefits.\",\"improvements\":[\"Establish quarterly district performance monitoring reviews\",\"Deploy digital asset management dashboards across participating departments\",\"Conduct community feedback surveys after 6 months of ordinance rollout\"],\"criteria\":{\"economic\":{\"level\":\"Low\",\"reason\":\"Funding and budget allocations for \\\"Flood Risk Assessment and Drainage Improvement Plan for Manila City\\\" are manageable within Manila City Hall fiscal programs.\"},\"social\":{\"level\":\"Low\",\"reason\":\"Enhances public welfare, community health, and district safety across Manila City.\"},\"env\":{\"level\":\"Low\",\"reason\":\"Minimal ecological footprint with positive sustainable urban development alignment.\"},\"legal\":{\"level\":\"Low\",\"reason\":\"Fully compliant with existing national legislative frameworks and Manila City Ordinances.\"}}}','Re Evaluate','System Administrator','2026-09-01 12:09:40','2026-09-01 12:09:40'),(25,62,1,'Version 1','Admin','City of Manila','Low Risk',8.80,8.80,8.80,8.80,8.80,'Highly recommended benchmark model. Provides strong regulatory precedent for Manila to strengthen enforcement mechanisms on commercial plastic bag fees and solid waste recovery.','{\"ai_analysis\":\"Quezon City successfully reduced commercial single-use bag usage by over 60% within 18 months through phased commercial merchant compliance and barangay eco-hubs.\",\"reason\":\"Clear fee-collection structure directly funding municipal green initiatives.\",\"criteria\":{\"economic\":{\"level\":\"Low\",\"reason\":\"Generates self-sustaining environmental recovery funds collected directly from commercial retail chains.\"},\"social\":{\"level\":\"Low\",\"reason\":\"High public adoption rate through extensive barangay information campaigns and merchant bag-swap programs.\"},\"env\":{\"level\":\"Low\",\"reason\":\"Direct reduction of plastic blockage in municipal waterways and pumping stations.\"},\"legal\":{\"level\":\"Low\",\"reason\":\"Fully aligned with Republic Act 9003 (Ecological Solid Waste Management Act) and DILG guidelines.\"}}}','Approved','Quintanaasdsa','2026-09-01 23:46:59','2026-09-01 12:34:25'),(26,63,1,'Version 1','Admin','City of Manila','Low Risk',8.60,8.60,8.60,8.60,8.60,'Excellent legislative reference for Manila City Hall urban renewal projects, especially for integrating green roof and flood-resilient rainwater capture into building permit approvals.','{\"ai_analysis\":\"Demonstrates effective local integration of national building code with green infrastructure incentives and tax rebate mechanisms.\",\"reason\":\"Property developers granted real property tax discounts upon achieving certified green performance ratings.\",\"criteria\":{\"economic\":{\"level\":\"Medium\",\"reason\":\"Initial developer compliance costs offset by long-term municipal energy savings and commercial property tax incentives.\"},\"social\":{\"level\":\"Low\",\"reason\":\"Improves urban air quality, reduces building thermal heat islands, and enhances residential safety.\"},\"env\":{\"level\":\"Low\",\"reason\":\"Significantly curbs municipal carbon emissions and promotes decentralized stormwater retention.\"},\"legal\":{\"level\":\"Low\",\"reason\":\"Conforms to the Philippine Green Building Code (PD 1096) and DPWH standards.\"}}}','Approved','Quintanaasdsa','2026-09-01 23:47:07','2026-09-01 12:34:25'),(27,64,1,'Version 1','Admin','City of Manila','Low Risk',8.70,8.70,8.70,8.70,8.70,'Provides an effective framework for Manila City Council to adapt for the University Belt and Port Area pedestrian corridor safety programs.','{\"ai_analysis\":\"Pasig City converted underutilized road space into dedicated high-capacity bike and pedestrian corridors with a 42% decrease in pedestrian accidents.\",\"reason\":\"Integrated with community bicycle parking and public transit intermodal connections.\",\"criteria\":{\"economic\":{\"level\":\"Low\",\"reason\":\"Low capital outlay for bollards and green street surfacing with substantial return in traffic decongestion.\"},\"social\":{\"level\":\"Low\",\"reason\":\"Equitable transportation benefits for students, daily commuters, and low-income workers.\"},\"env\":{\"level\":\"Low\",\"reason\":\"Cuts vehicular carbon emissions and particulate air pollution in dense urban corridors.\"},\"legal\":{\"level\":\"Low\",\"reason\":\"Fully aligned with National Active Transport Guidelines (DOTr-DOH-DILG-DPWH Joint Admin Order).\"}}}','Evaluated','System Administrator','2026-09-01 12:34:25','2026-09-01 12:34:25'),(28,47,2,'Version 2','AI Assistant','City of Manila','Low Risk',8.00,8.00,8.00,8.00,8.50,'Enact Comprehensive Environmental Regulation with Phased Retailer Compliance and Community Bag-Swap Programs','{\"ai_analysis\":\"The proposed measure demonstrates sound legislative alignment across statutory evaluation criteria. Economic feasibility indicates viable operational implementation. Social impact promotes equitable community development, while environmental and legal assessments confirm statutory compliance with local and national governance standards.\",\"reason\":\"The proposed ordinance establishes clear statutory mechanisms, enforceable provisions, and measurable municipal benefits.\",\"recommendation_type\":\"Proceed with Implementation\",\"improvements\":[\"Include specific operational timeline targets and phased rollout milestones.\",\"Establish inter-departmental oversight to monitor enforcement and budget allocation.\",\"Conduct periodic stakeholder reviews to assess measurable community outcomes.\"],\"criteria\":{\"economic\":{\"level\":\"Low\",\"reason\":\"Generates sustainable operational funds through structured merchant compliance mechanisms and commercial waste recovery systems.\"},\"social\":{\"level\":\"Low\",\"reason\":\"Promotes widespread community participation through barangay awareness and public sector bag-swap programs.\"},\"env\":{\"level\":\"Low\",\"reason\":\"Directly reduces municipal waterway blockages, mitigates drainage congestion, and reduces non-biodegradable waste volumes.\"},\"legal\":{\"level\":\"Low\",\"reason\":\"Strictly compliant with Republic Act No. 9003 (Ecological Solid Waste Management Act) and DILG environmental directives.\"}}}','Re Evaluate','System Administrator','2026-09-01 17:53:11','2026-09-01 17:53:11'),(29,49,5,'Version 5','AI Assistant','City of Manila','Low Risk',8.00,8.00,8.00,8.00,8.50,'Enact Comprehensive Environmental Regulation with Phased Retailer Compliance and Community Bag-Swap Programs','{\"ai_analysis\":\"The proposed measure demonstrates sound legislative alignment across statutory evaluation criteria. Economic feasibility indicates viable operational implementation. Social impact promotes equitable community development, while environmental and legal assessments confirm statutory compliance with local and national governance standards.\",\"reason\":\"The proposed ordinance establishes clear statutory mechanisms, enforceable provisions, and measurable municipal benefits.\",\"recommendation_type\":\"Proceed with Implementation\",\"improvements\":[\"Include specific operational timeline targets and phased rollout milestones.\",\"Establish inter-departmental oversight to monitor enforcement and budget allocation.\",\"Conduct periodic stakeholder reviews to assess measurable community outcomes.\"],\"criteria\":{\"economic\":{\"level\":\"Low\",\"reason\":\"Generates sustainable operational funds through structured merchant compliance mechanisms and commercial waste recovery systems.\"},\"social\":{\"level\":\"Low\",\"reason\":\"Promotes widespread community participation through barangay awareness and public sector bag-swap programs.\"},\"env\":{\"level\":\"Low\",\"reason\":\"Directly reduces municipal waterway blockages, mitigates drainage congestion, and reduces non-biodegradable waste volumes.\"},\"legal\":{\"level\":\"Low\",\"reason\":\"Strictly compliant with Republic Act No. 9003 (Ecological Solid Waste Management Act) and DILG environmental directives.\"}}}','Re Evaluate','System Administrator','2026-09-01 17:53:18','2026-09-01 17:53:18'),(30,66,1,'Version 1','AI Assistant','City of Manila','Low Risk',8.00,8.00,8.00,8.00,8.50,'Enact Policy with Enhanced Inter-Agency Coordination and Implementation Monitoring','{\"ai_analysis\":\"The proposed measure demonstrates sound legislative alignment across statutory evaluation criteria. Economic feasibility indicates viable operational implementation. Social impact promotes equitable community development, while environmental and legal assessments confirm statutory compliance with local and national governance standards.\",\"reason\":\"The proposed ordinance establishes clear statutory mechanisms, enforceable provisions, and measurable municipal benefits.\",\"recommendation_type\":\"Proceed with Implementation\",\"improvements\":[\"Include specific operational timeline targets and phased rollout milestones.\",\"Establish inter-departmental oversight to monitor enforcement and budget allocation.\",\"Conduct periodic stakeholder reviews to assess measurable community outcomes.\"],\"criteria\":{\"economic\":{\"level\":\"Low\",\"reason\":\"Funding requirements are feasible through municipal budget allocations, with manageable capital outlay and operational cost recovery.\"},\"social\":{\"level\":\"Low\",\"reason\":\"Delivers direct public welfare benefits to local residents, enhancing civic safety, community inclusivity, and quality of life.\"},\"env\":{\"level\":\"Low\",\"reason\":\"Incorporates sustainable practices with minimal adverse ecological footprint, supporting urban environmental resilience.\"},\"legal\":{\"level\":\"Low\",\"reason\":\"Fully aligned with the Local Government Code of 1991 (Republic Act No. 7160) and pertinent national regulatory guidelines.\"}}}','Approved','Quintanaasdsa','2026-09-01 23:27:15','2026-09-01 17:54:06'),(31,65,1,'Version 1','AI Assistant','City of Manila','Low Risk',8.00,8.00,8.00,8.00,8.50,'Enact Policy with Enhanced Inter-Agency Coordination and Implementation Monitoring','{\"ai_analysis\":\"The proposed measure demonstrates sound legislative alignment across statutory evaluation criteria. Economic feasibility indicates viable operational implementation. Social impact promotes equitable community development, while environmental and legal assessments confirm statutory compliance with local and national governance standards.\",\"reason\":\"The proposed ordinance establishes clear statutory mechanisms, enforceable provisions, and measurable municipal benefits.\",\"recommendation_type\":\"Proceed with Implementation\",\"improvements\":[\"Include specific operational timeline targets and phased rollout milestones.\",\"Establish inter-departmental oversight to monitor enforcement and budget allocation.\",\"Conduct periodic stakeholder reviews to assess measurable community outcomes.\"],\"criteria\":{\"economic\":{\"level\":\"Low\",\"reason\":\"Funding requirements are feasible through municipal budget allocations, with manageable capital outlay and operational cost recovery.\"},\"social\":{\"level\":\"Low\",\"reason\":\"Delivers direct public welfare benefits to local residents, enhancing civic safety, community inclusivity, and quality of life.\"},\"env\":{\"level\":\"Low\",\"reason\":\"Incorporates sustainable practices with minimal adverse ecological footprint, supporting urban environmental resilience.\"},\"legal\":{\"level\":\"Low\",\"reason\":\"Fully aligned with the Local Government Code of 1991 (Republic Act No. 7160) and pertinent national regulatory guidelines.\"}}}','Approved','Quintanaasdsa','2026-09-01 23:44:44','2026-09-01 17:54:12'),(32,69,1,'Version 1','Admin','City of Manila','Low',8.50,8.50,8.50,8.50,8.50,'Approve & Proceed to Full Implementation','{\"ai_analysis\":\"Comprehensive evaluation of \\\"Public Transportation - Urban Traffic & Transit Congestion Study\\\" indicates high operational viability, low public implementation risk, and strong strategic alignment with Manila City Hall legislative objectives.\",\"reason\":\"Detailed assessment demonstrates minimal public risk and high long-term community benefits.\",\"improvements\":[\"Establish quarterly district performance monitoring reviews\",\"Deploy digital asset management dashboards across participating departments\",\"Conduct community feedback surveys after 6 months of ordinance rollout\"],\"criteria\":{\"economic\":{\"level\":\"Low\",\"reason\":\"Funding and budget allocations for \\\"Public Transportation - Urban Traffic & Transit Congestion Study\\\" are manageable within Manila City Hall fiscal programs.\"},\"social\":{\"level\":\"Low\",\"reason\":\"Enhances public welfare, community health, and district safety across Manila City.\"},\"env\":{\"level\":\"Low\",\"reason\":\"Minimal ecological footprint with positive sustainable urban development alignment.\"},\"legal\":{\"level\":\"Low\",\"reason\":\"Fully compliant with existing national legislative frameworks and Manila City Ordinances.\"}}}','Approved','Quintanaasdsa','2026-09-01 23:42:35','2026-09-01 18:53:24'),(33,68,1,'Version 1','Admin','City of Manila','Low',8.50,8.50,8.50,8.50,8.50,'Approve & Proceed to Full Implementation','{\"ai_analysis\":\"Comprehensive evaluation of \\\"Public Transportation - Urban Traffic & Transit Congestion Study\\\" indicates high operational viability, low public implementation risk, and strong strategic alignment with Manila City Hall legislative objectives.\",\"reason\":\"Detailed assessment demonstrates minimal public risk and high long-term community benefits.\",\"improvements\":[\"Establish quarterly district performance monitoring reviews\",\"Deploy digital asset management dashboards across participating departments\",\"Conduct community feedback surveys after 6 months of ordinance rollout\"],\"criteria\":{\"economic\":{\"level\":\"Low\",\"reason\":\"Funding and budget allocations for \\\"Public Transportation - Urban Traffic & Transit Congestion Study\\\" are manageable within Manila City Hall fiscal programs.\"},\"social\":{\"level\":\"Low\",\"reason\":\"Enhances public welfare, community health, and district safety across Manila City.\"},\"env\":{\"level\":\"Low\",\"reason\":\"Minimal ecological footprint with positive sustainable urban development alignment.\"},\"legal\":{\"level\":\"Low\",\"reason\":\"Fully compliant with existing national legislative frameworks and Manila City Ordinances.\"}}}','Approved','Quintanaasdsa','2026-09-01 23:43:35','2026-09-01 18:53:52'),(34,50,4,'Version 4','Admin','City of Manila','Low',8.50,8.50,8.50,8.50,8.50,'Approve & Proceed to Full Implementation','{\"ai_analysis\":\"Comprehensive evaluation of \\\"Urban Traffic Congestion Study in Manila City\\\" indicates high operational viability, low public implementation risk, and strong strategic alignment with Manila City Hall legislative objectives.\",\"reason\":\"Detailed assessment demonstrates minimal public risk and high long-term community benefits.\",\"improvements\":[\"Establish quarterly district performance monitoring reviews\",\"Deploy digital asset management dashboards across participating departments\",\"Conduct community feedback surveys after 6 months of ordinance rollout\"],\"criteria\":{\"economic\":{\"level\":\"Low\",\"reason\":\"Funding and budget allocations for \\\"Urban Traffic Congestion Study in Manila City\\\" are manageable within Manila City Hall fiscal programs.\"},\"social\":{\"level\":\"Low\",\"reason\":\"Enhances public welfare, community health, and district safety across Manila City.\"},\"env\":{\"level\":\"Low\",\"reason\":\"Minimal ecological footprint with positive sustainable urban development alignment.\"},\"legal\":{\"level\":\"Low\",\"reason\":\"Fully compliant with existing national legislative frameworks and Manila City Ordinances.\"}}}','Re Evaluate','System Administrator','2026-09-01 19:03:58','2026-09-01 19:03:58'),(35,50,5,'Version 5','Admin','City of Manila','Low',8.50,8.50,8.50,8.50,8.50,'Approve & Proceed to Full Implementation','{\"ai_analysis\":\"Comprehensive evaluation of \\\"Urban Traffic Congestion Study in Manila City\\\" indicates high operational viability, low public implementation risk, and strong strategic alignment with Manila City Hall legislative objectives.\",\"reason\":\"Detailed assessment demonstrates minimal public risk and high long-term community benefits.\",\"improvements\":[\"Establish quarterly district performance monitoring reviews\",\"Deploy digital asset management dashboards across participating departments\",\"Conduct community feedback surveys after 6 months of ordinance rollout\"],\"criteria\":{\"economic\":{\"level\":\"Low\",\"reason\":\"Funding and budget allocations for \\\"Urban Traffic Congestion Study in Manila City\\\" are manageable within Manila City Hall fiscal programs.\"},\"social\":{\"level\":\"Low\",\"reason\":\"Enhances public welfare, community health, and district safety across Manila City.\"},\"env\":{\"level\":\"Low\",\"reason\":\"Minimal ecological footprint with positive sustainable urban development alignment.\"},\"legal\":{\"level\":\"Low\",\"reason\":\"Fully compliant with existing national legislative frameworks and Manila City Ordinances.\"}}}','Re Evaluate','System Administrator','2026-09-01 19:04:04','2026-09-01 19:04:04'),(36,50,6,'Version 6','Admin','City of Manila','Low',8.50,8.50,8.50,8.50,8.50,'Approve & Proceed to Full Implementation','{\"ai_analysis\":\"Comprehensive evaluation of \\\"Urban Traffic Congestion Study in Manila City\\\" indicates high operational viability, low public implementation risk, and strong strategic alignment with Manila City Hall legislative objectives.\",\"reason\":\"Detailed assessment demonstrates minimal public risk and high long-term community benefits.\",\"improvements\":[\"Establish quarterly district performance monitoring reviews\",\"Deploy digital asset management dashboards across participating departments\",\"Conduct community feedback surveys after 6 months of ordinance rollout\"],\"criteria\":{\"economic\":{\"level\":\"Low\",\"reason\":\"Funding and budget allocations for \\\"Urban Traffic Congestion Study in Manila City\\\" are manageable within Manila City Hall fiscal programs.\"},\"social\":{\"level\":\"Low\",\"reason\":\"Enhances public welfare, community health, and district safety across Manila City.\"},\"env\":{\"level\":\"Low\",\"reason\":\"Minimal ecological footprint with positive sustainable urban development alignment.\"},\"legal\":{\"level\":\"Low\",\"reason\":\"Fully compliant with existing national legislative frameworks and Manila City Ordinances.\"}}}','Re Evaluate','System Administrator','2026-09-01 19:04:05','2026-09-01 19:04:05'),(37,50,7,'Version 7','Admin','City of Manila','Low',8.50,8.50,8.50,8.50,8.50,'Approve & Proceed to Full Implementation','{\"ai_analysis\":\"Comprehensive evaluation of \\\"Urban Traffic Congestion Study in Manila City\\\" indicates high operational viability, low public implementation risk, and strong strategic alignment with Manila City Hall legislative objectives.\",\"reason\":\"Detailed assessment demonstrates minimal public risk and high long-term community benefits.\",\"improvements\":[\"Establish quarterly district performance monitoring reviews\",\"Deploy digital asset management dashboards across participating departments\",\"Conduct community feedback surveys after 6 months of ordinance rollout\"],\"criteria\":{\"economic\":{\"level\":\"Low\",\"reason\":\"Funding and budget allocations for \\\"Urban Traffic Congestion Study in Manila City\\\" are manageable within Manila City Hall fiscal programs.\"},\"social\":{\"level\":\"Low\",\"reason\":\"Enhances public welfare, community health, and district safety across Manila City.\"},\"env\":{\"level\":\"Low\",\"reason\":\"Minimal ecological footprint with positive sustainable urban development alignment.\"},\"legal\":{\"level\":\"Low\",\"reason\":\"Fully compliant with existing national legislative frameworks and Manila City Ordinances.\"}}}','Re Evaluate','System Administrator','2026-09-01 19:04:07','2026-09-01 19:04:07'),(38,50,8,'Version 8','Admin','City of Manila','Low',8.50,8.50,8.50,8.50,8.50,'Approve & Proceed to Full Implementation','{\"ai_analysis\":\"Comprehensive evaluation of \\\"Urban Traffic Congestion Study in Manila City\\\" indicates high operational viability, low public implementation risk, and strong strategic alignment with Manila City Hall legislative objectives.\",\"reason\":\"Detailed assessment demonstrates minimal public risk and high long-term community benefits.\",\"improvements\":[\"Establish quarterly district performance monitoring reviews\",\"Deploy digital asset management dashboards across participating departments\",\"Conduct community feedback surveys after 6 months of ordinance rollout\"],\"criteria\":{\"economic\":{\"level\":\"Low\",\"reason\":\"Funding and budget allocations for \\\"Urban Traffic Congestion Study in Manila City\\\" are manageable within Manila City Hall fiscal programs.\"},\"social\":{\"level\":\"Low\",\"reason\":\"Enhances public welfare, community health, and district safety across Manila City.\"},\"env\":{\"level\":\"Low\",\"reason\":\"Minimal ecological footprint with positive sustainable urban development alignment.\"},\"legal\":{\"level\":\"Low\",\"reason\":\"Fully compliant with existing national legislative frameworks and Manila City Ordinances.\"}}}','Re Evaluate','System Administrator','2026-09-01 19:15:42','2026-09-01 19:15:42'),(39,50,9,'Version 9','Admin','City of Manila','Low',8.50,8.50,8.50,8.50,8.50,'Approve & Proceed to Full Implementation','{\"ai_analysis\":\"Comprehensive evaluation of \\\"Urban Traffic Congestion Study in Manila City\\\" indicates high operational viability, low public implementation risk, and strong strategic alignment with Manila City Hall legislative objectives.\",\"reason\":\"Detailed assessment demonstrates minimal public risk and high long-term community benefits.\",\"improvements\":[\"Establish quarterly district performance monitoring reviews\",\"Deploy digital asset management dashboards across participating departments\",\"Conduct community feedback surveys after 6 months of ordinance rollout\"],\"criteria\":{\"economic\":{\"level\":\"Low\",\"reason\":\"Funding and budget allocations for \\\"Urban Traffic Congestion Study in Manila City\\\" are manageable within Manila City Hall fiscal programs.\"},\"social\":{\"level\":\"Low\",\"reason\":\"Enhances public welfare, community health, and district safety across Manila City.\"},\"env\":{\"level\":\"Low\",\"reason\":\"Minimal ecological footprint with positive sustainable urban development alignment.\"},\"legal\":{\"level\":\"Low\",\"reason\":\"Fully compliant with existing national legislative frameworks and Manila City Ordinances.\"}}}','Re Evaluate','System Administrator','2026-09-01 19:15:58','2026-09-01 19:15:58'),(40,50,10,'Version 10','Admin','City of Manila','Low',8.50,8.50,8.50,8.50,8.50,'Approve & Proceed to Full Implementation','{\"ai_analysis\":\"Comprehensive evaluation of \\\"Urban Traffic Congestion Study in Manila City\\\" indicates high operational viability, low public implementation risk, and strong strategic alignment with Manila City Hall legislative objectives.\",\"reason\":\"Detailed assessment demonstrates minimal public risk and high long-term community benefits.\",\"improvements\":[\"Establish quarterly district performance monitoring reviews\",\"Deploy digital asset management dashboards across participating departments\",\"Conduct community feedback surveys after 6 months of ordinance rollout\"],\"criteria\":{\"economic\":{\"level\":\"Low\",\"reason\":\"Funding and budget allocations for \\\"Urban Traffic Congestion Study in Manila City\\\" are manageable within Manila City Hall fiscal programs.\"},\"social\":{\"level\":\"Low\",\"reason\":\"Enhances public welfare, community health, and district safety across Manila City.\"},\"env\":{\"level\":\"Low\",\"reason\":\"Minimal ecological footprint with positive sustainable urban development alignment.\"},\"legal\":{\"level\":\"Low\",\"reason\":\"Fully compliant with existing national legislative frameworks and Manila City Ordinances.\"}}}','Re Evaluate','System Administrator','2026-09-01 19:16:00','2026-09-01 19:16:00'),(41,50,11,'Version 11','Admin','City of Manila','Low',8.50,8.50,8.50,8.50,8.50,'Approve & Proceed to Full Implementation','{\"ai_analysis\":\"Comprehensive evaluation of \\\"Urban Traffic Congestion Study in Manila City\\\" indicates high operational viability, low public implementation risk, and strong strategic alignment with Manila City Hall legislative objectives.\",\"reason\":\"Detailed assessment demonstrates minimal public risk and high long-term community benefits.\",\"improvements\":[\"Establish quarterly district performance monitoring reviews\",\"Deploy digital asset management dashboards across participating departments\",\"Conduct community feedback surveys after 6 months of ordinance rollout\"],\"criteria\":{\"economic\":{\"level\":\"Low\",\"reason\":\"Funding and budget allocations for \\\"Urban Traffic Congestion Study in Manila City\\\" are manageable within Manila City Hall fiscal programs.\"},\"social\":{\"level\":\"Low\",\"reason\":\"Enhances public welfare, community health, and district safety across Manila City.\"},\"env\":{\"level\":\"Low\",\"reason\":\"Minimal ecological footprint with positive sustainable urban development alignment.\"},\"legal\":{\"level\":\"Low\",\"reason\":\"Fully compliant with existing national legislative frameworks and Manila City Ordinances.\"}}}','Re Evaluate','System Administrator','2026-09-01 19:20:51','2026-09-01 19:20:51'),(42,50,12,'Version 12','Admin','City of Manila','Low',8.50,8.50,8.50,8.50,8.50,'Approve & Proceed to Full Implementation','{\"ai_analysis\":\"Comprehensive evaluation of \\\"Urban Traffic Congestion Study in Manila City\\\" indicates high operational viability, low public implementation risk, and strong strategic alignment with Manila City Hall legislative objectives.\",\"reason\":\"Detailed assessment demonstrates minimal public risk and high long-term community benefits.\",\"improvements\":[\"Establish quarterly district performance monitoring reviews\",\"Deploy digital asset management dashboards across participating departments\",\"Conduct community feedback surveys after 6 months of ordinance rollout\"],\"criteria\":{\"economic\":{\"level\":\"Low\",\"reason\":\"Funding and budget allocations for \\\"Urban Traffic Congestion Study in Manila City\\\" are manageable within Manila City Hall fiscal programs.\"},\"social\":{\"level\":\"Low\",\"reason\":\"Enhances public welfare, community health, and district safety across Manila City.\"},\"env\":{\"level\":\"Low\",\"reason\":\"Minimal ecological footprint with positive sustainable urban development alignment.\"},\"legal\":{\"level\":\"Low\",\"reason\":\"Fully compliant with existing national legislative frameworks and Manila City Ordinances.\"}}}','Re Evaluate','System Administrator','2026-09-01 19:21:04','2026-09-01 19:21:04'),(43,50,13,'Version 13','Admin','City of Manila','Low Risk',8.50,8.50,8.50,8.50,8.50,'Approve & Proceed to Full Implementation','{\"ai_analysis\":\"Comprehensive evaluation of \\\"Urban Traffic Congestion Study in Manila City\\\" indicates high operational viability, low public implementation risk, and strong strategic alignment with Manila City Hall legislative objectives.\",\"reason\":\"Detailed assessment demonstrates minimal public risk and high long-term community benefits.\",\"improvements\":[\"Establish quarterly district performance monitoring reviews\",\"Deploy digital asset management dashboards across participating departments\",\"Conduct community feedback surveys after 6 months of ordinance rollout\"],\"criteria\":{\"economic\":{\"level\":\"Low\",\"reason\":\"Funding and budget allocations for \\\"Urban Traffic Congestion Study in Manila City\\\" are manageable within Manila City Hall fiscal programs.\"},\"social\":{\"level\":\"Low\",\"reason\":\"Enhances public welfare, community health, and district safety across Manila City.\"},\"env\":{\"level\":\"Low\",\"reason\":\"Minimal ecological footprint with positive sustainable urban development alignment.\"},\"legal\":{\"level\":\"Low\",\"reason\":\"Fully compliant with existing national legislative frameworks and Manila City Ordinances.\"}}}','Evaluated','Quintanaasdsa','2026-09-01 19:34:22','2026-09-01 19:34:15'),(44,49,6,'Version 6','Admin','City of Manila','Low Risk',8.50,8.50,8.50,8.50,8.50,'Approve & Fast-Track Implementation with Enhanced District Resource Allocation','{\"ai_analysis\":\"Updated legislative revision for \\\"Flood Risk Assessment and Drainage Improvement Plan for Manila City\\\" incorporates inter-agency operational alignment, optimized fiscal allocations, and strengthened community implementation milestones.\",\"reason\":\"Comprehensive revision verifies positive municipal feasibility, zero legal impediment, and high public impact.\",\"improvements\":[\"Establish bi-monthly district performance monitoring audits\",\"Integrate real-time citizen feedback via Manila City digital portal\",\"Maintain dedicated multi-year capital maintenance reserve fund\"],\"criteria\":{\"economic\":{\"level\":\"Low\",\"reason\":\"Revised fiscal analysis confirms sustainable funding allocations for \\\"Flood Risk Assessment and Drainage Improvement Plan for Manila City\\\" across Manila City Hall departmental budgets.\"},\"social\":{\"level\":\"Low\",\"reason\":\"Updated community assessment indicates enhanced public safety, district welfare, and direct constituent service improvements.\"},\"env\":{\"level\":\"Low\",\"reason\":\"Refined ecological assessment satisfies all green urban development and environmental compliance standards.\"},\"legal\":{\"level\":\"Low\",\"reason\":\"Full legal harmony established with the Local Government Code and latest Manila City legislative ordinances.\"}}}','Approved','Quintanaasdsa','2026-09-02 11:10:06','2026-09-01 19:36:15'),(45,47,3,'Version 3','Admin','City of Manila','Low Risk',8.50,8.50,8.50,8.50,8.50,'Approve & Fast-Track Implementation with Enhanced District Resource Allocation','{\"ai_analysis\":\"Updated legislative revision for \\\"National Clean Energy Grid Modernization Act: Economic and Environmental Impact Assessment\\\" incorporates inter-agency operational alignment, optimized fiscal allocations, and strengthened community implementation milestones.\",\"reason\":\"Comprehensive revision verifies positive municipal feasibility, zero legal impediment, and high public impact.\",\"improvements\":[\"Establish bi-monthly district performance monitoring audits\",\"Integrate real-time citizen feedback via Manila City digital portal\",\"Maintain dedicated multi-year capital maintenance reserve fund\"],\"criteria\":{\"economic\":{\"level\":\"Low\",\"reason\":\"Revised fiscal analysis confirms sustainable funding allocations for \\\"National Clean Energy Grid Modernization Act: Economic and Environmental Impact Assessment\\\" across Manila City Hall departmental budgets.\"},\"social\":{\"level\":\"Low\",\"reason\":\"Updated community assessment indicates enhanced public safety, district welfare, and direct constituent service improvements.\"},\"env\":{\"level\":\"Low\",\"reason\":\"Refined ecological assessment satisfies all green urban development and environmental compliance standards.\"},\"legal\":{\"level\":\"Low\",\"reason\":\"Full legal harmony established with the Local Government Code and latest Manila City legislative ordinances.\"}}}','Approved','Quintanaasdsa','2026-09-02 11:10:11','2026-09-01 19:37:43'),(46,64,2,'Version 2','Admin','City of Manila','Low Risk',8.50,8.50,8.50,8.50,8.50,'Approve & Fast-Track Implementation with Enhanced District Resource Allocation','{\"ai_analysis\":\"Updated legislative revision for \\\"Pasig City Ordinance No. 12: People-Centric Mobility & Protected Bike Lane Network System\\\" incorporates inter-agency operational alignment, optimized fiscal allocations, and strengthened community implementation milestones.\",\"reason\":\"Comprehensive revision verifies positive municipal feasibility, zero legal impediment, and high public impact.\",\"improvements\":[\"Establish bi-monthly district performance monitoring audits\",\"Integrate real-time citizen feedback via Manila City digital portal\",\"Maintain dedicated multi-year capital maintenance reserve fund\"],\"criteria\":{\"economic\":{\"level\":\"Low\",\"reason\":\"Revised fiscal analysis confirms sustainable funding allocations for \\\"Pasig City Ordinance No. 12: People-Centric Mobility & Protected Bike Lane Network System\\\" across Manila City Hall departmental budgets.\"},\"social\":{\"level\":\"Low\",\"reason\":\"Updated community assessment indicates enhanced public safety, district welfare, and direct constituent service improvements.\"},\"env\":{\"level\":\"Low\",\"reason\":\"Refined ecological assessment satisfies all green urban development and environmental compliance standards.\"},\"legal\":{\"level\":\"Low\",\"reason\":\"Full legal harmony established with the Local Government Code and latest Manila City legislative ordinances.\"}}}','Approved','Quintanaasdsa','2026-09-01 23:47:33','2026-09-01 23:47:30'),(47,60,2,'Version 2','Admin','City of Manila','Low Risk',8.50,8.50,8.50,8.50,8.50,'Approve & Proceed to Full Implementation','{\"ai_analysis\":\"Comprehensive evaluation of \\\"Improvement Strategy - Public Health & Wellness Action Plan\\\" indicates high operational viability, low public implementation risk, and strong strategic alignment with Manila City Hall legislative objectives.\",\"reason\":\"Detailed assessment demonstrates minimal public risk and high long-term community benefits.\",\"improvements\":[\"Establish quarterly district performance monitoring reviews\",\"Deploy digital asset management dashboards across participating departments\",\"Conduct community feedback surveys after 6 months of ordinance rollout\"],\"criteria\":{\"economic\":{\"level\":\"Low\",\"reason\":\"Funding and budget allocations for \\\"Improvement Strategy - Public Health & Wellness Action Plan\\\" are manageable within Manila City Hall fiscal programs.\"},\"social\":{\"level\":\"Low\",\"reason\":\"Enhances public welfare, community health, and district safety across Manila City.\"},\"env\":{\"level\":\"Low\",\"reason\":\"Minimal ecological footprint with positive sustainable urban development alignment.\"},\"legal\":{\"level\":\"Low\",\"reason\":\"Fully compliant with existing national legislative frameworks and Manila City Ordinances.\"}}}','Approved','Quintanaasdsa','2026-09-01 23:51:15','2026-09-01 23:51:12'),(48,59,13,'Version 13','Admin','City of Manila','Low Risk',8.50,8.50,8.50,8.50,8.50,'Approve & Fast-Track Implementation with Enhanced District Resource Allocation','{\"ai_analysis\":\"Updated legislative revision for \\\"Community Safety - Comprehensive Social Welfare & Community Support Initiative\\\" incorporates inter-agency operational alignment, optimized fiscal allocations, and strengthened community implementation milestones.\",\"reason\":\"Comprehensive revision verifies positive municipal feasibility, zero legal impediment, and high public impact.\",\"improvements\":[\"Establish bi-monthly district performance monitoring audits\",\"Integrate real-time citizen feedback via Manila City digital portal\",\"Maintain dedicated multi-year capital maintenance reserve fund\"],\"criteria\":{\"economic\":{\"level\":\"Low\",\"reason\":\"Revised fiscal analysis confirms sustainable funding allocations for \\\"Community Safety - Comprehensive Social Welfare & Community Support Initiative\\\" across Manila City Hall departmental budgets.\"},\"social\":{\"level\":\"Low\",\"reason\":\"Updated community assessment indicates enhanced public safety, district welfare, and direct constituent service improvements.\"},\"env\":{\"level\":\"Low\",\"reason\":\"Refined ecological assessment satisfies all green urban development and environmental compliance standards.\"},\"legal\":{\"level\":\"Low\",\"reason\":\"Full legal harmony established with the Local Government Code and latest Manila City legislative ordinances.\"}}}','Approved','Quintanaasdsa','2026-09-01 23:51:26','2026-09-01 23:51:24'),(49,50,14,'Version 14','Admin','City of Manila','Low Risk',8.50,8.50,8.50,8.50,8.50,'Approve & Fast-Track Implementation with Enhanced District Resource Allocation','{\"ai_analysis\":\"Updated legislative revision for \\\"Urban Traffic Congestion Study in Manila City\\\" incorporates inter-agency operational alignment, optimized fiscal allocations, and strengthened community implementation milestones.\",\"reason\":\"Comprehensive revision verifies positive municipal feasibility, zero legal impediment, and high public impact.\",\"improvements\":[\"Establish bi-monthly district performance monitoring audits\",\"Integrate real-time citizen feedback via Manila City digital portal\",\"Maintain dedicated multi-year capital maintenance reserve fund\"],\"criteria\":{\"economic\":{\"level\":\"Low\",\"reason\":\"Revised fiscal analysis confirms sustainable funding allocations for \\\"Urban Traffic Congestion Study in Manila City\\\" across Manila City Hall departmental budgets.\"},\"social\":{\"level\":\"Low\",\"reason\":\"Updated community assessment indicates enhanced public safety, district welfare, and direct constituent service improvements.\"},\"env\":{\"level\":\"Low\",\"reason\":\"Refined ecological assessment satisfies all green urban development and environmental compliance standards.\"},\"legal\":{\"level\":\"Low\",\"reason\":\"Full legal harmony established with the Local Government Code and latest Manila City legislative ordinances.\"}}}','Approved','Quintanaasdsa','2026-09-01 23:58:41','2026-09-01 23:57:45'),(50,73,1,'Version 1','Admin','City of Manila','Low Risk',8.50,8.50,8.50,8.50,8.50,'Approve & Proceed to Full Implementation','{\"ai_analysis\":\"The automated policy analyzer evaluated this proposed measure across municipal governance, socioeconomic impact, environmental sustainability, and legal statutory alignment.\",\"reason\":\"The proposed measure demonstrates strong alignment with City of Manila governance priorities with manageable fiscal requirements.\",\"improvements\":[\"Establish structured inter-agency implementation milestones.\",\"Maintain continuous compliance monitoring with relevant national and local statutes.\"],\"criteria\":{\"economic\":{\"level\":\"Low\",\"reason\":\"Budget and operational expenditures align with existing department allocations.\"},\"social\":{\"level\":\"Low\",\"reason\":\"Provides direct public benefits and enhances service delivery to constituents.\"},\"env\":{\"level\":\"Low\",\"reason\":\"Satisfies urban ecological standards and regulatory environmental compliance.\"},\"legal\":{\"level\":\"Low\",\"reason\":\"Compliant with the Local Government Code and relevant municipal ordinances.\"}}}','Approved','Quintanaasdsa','2026-09-02 09:31:57','2026-09-02 09:31:54'),(51,74,1,'Version 1','Admin','City of Manila','Low',8.50,8.50,8.50,8.50,8.50,'Approve & Proceed to Full Implementation','{\"ai_analysis\":\"Comprehensive evaluation of \\\"Community Safety and Crime Prevention Strategy for Manila City\\\" indicates high operational viability, low public implementation risk, and strong strategic alignment with Manila City Hall legislative objectives.\",\"reason\":\"Detailed assessment demonstrates minimal public risk and high long-term community benefits.\",\"improvements\":[\"Establish quarterly district performance monitoring reviews\",\"Deploy digital asset management dashboards across participating departments\",\"Conduct community feedback surveys after 6 months of ordinance rollout\"],\"criteria\":{\"economic\":{\"level\":\"Low\",\"reason\":\"Funding and budget allocations for \\\"Community Safety and Crime Prevention Strategy for Manila City\\\" are manageable within Manila City Hall fiscal programs.\"},\"social\":{\"level\":\"Low\",\"reason\":\"Enhances public welfare, community health, and district safety across Manila City.\"},\"env\":{\"level\":\"Low\",\"reason\":\"Minimal ecological footprint with positive sustainable urban development alignment.\"},\"legal\":{\"level\":\"Low\",\"reason\":\"Fully compliant with existing national legislative frameworks and Manila City Ordinances.\"}}}','Completed',NULL,NULL,'2026-09-02 11:09:41'),(52,74,2,'Version 2','Admin','City of Manila','Low Risk',8.50,8.50,8.50,8.50,8.50,'Approve & Fast-Track Implementation with Enhanced District Resource Allocation','{\"ai_analysis\":\"Updated legislative revision for \\\"Community Safety and Crime Prevention Strategy for Manila City\\\" incorporates inter-agency operational alignment, optimized fiscal allocations, and strengthened community implementation milestones.\",\"reason\":\"Comprehensive revision verifies positive municipal feasibility, zero legal impediment, and high public impact.\",\"improvements\":[\"Establish bi-monthly district performance monitoring audits\",\"Integrate real-time citizen feedback via Manila City digital portal\",\"Maintain dedicated multi-year capital maintenance reserve fund\"],\"criteria\":{\"economic\":{\"level\":\"Low\",\"reason\":\"Revised fiscal analysis confirms sustainable funding allocations for \\\"Community Safety and Crime Prevention Strategy for Manila City\\\" across Manila City Hall departmental budgets.\"},\"social\":{\"level\":\"Low\",\"reason\":\"Updated community assessment indicates enhanced public safety, district welfare, and direct constituent service improvements.\"},\"env\":{\"level\":\"Low\",\"reason\":\"Refined ecological assessment satisfies all green urban development and environmental compliance standards.\"},\"legal\":{\"level\":\"Low\",\"reason\":\"Full legal harmony established with the Local Government Code and latest Manila City legislative ordinances.\"}}}','Approved','Quintanaasdsa','2026-09-02 11:09:57','2026-09-02 11:09:55'),(53,75,1,'Version 1','Admin','City of Manila','Low',8.50,8.50,8.50,8.50,8.50,'Approve & Proceed to Full Implementation','{\"ai_analysis\":\"Comprehensive evaluation of \\\"Public Transportation Efficiency Improvement Plan for Manila City\\\" indicates high operational viability, low public implementation risk, and strong strategic alignment with Manila City Hall legislative objectives.\",\"reason\":\"Detailed assessment demonstrates minimal public risk and high long-term community benefits.\",\"improvements\":[\"Establish quarterly district performance monitoring reviews\",\"Deploy digital asset management dashboards across participating departments\",\"Conduct community feedback surveys after 6 months of ordinance rollout\"],\"criteria\":{\"economic\":{\"level\":\"Low\",\"reason\":\"Funding and budget allocations for \\\"Public Transportation Efficiency Improvement Plan for Manila City\\\" are manageable within Manila City Hall fiscal programs.\"},\"social\":{\"level\":\"Low\",\"reason\":\"Enhances public welfare, community health, and district safety across Manila City.\"},\"env\":{\"level\":\"Low\",\"reason\":\"Minimal ecological footprint with positive sustainable urban development alignment.\"},\"legal\":{\"level\":\"Low\",\"reason\":\"Fully compliant with existing national legislative frameworks and Manila City Ordinances.\"}}}','Completed',NULL,NULL,'2026-09-02 11:12:31'),(54,76,1,'Version 1','Admin','City of Manila','Low',8.50,8.50,8.50,8.50,8.50,'Approve & Proceed to Full Implementation','{\"ai_analysis\":\"Comprehensive evaluation of \\\"Improvement Strategy\\\" indicates high operational viability, low public implementation risk, and strong strategic alignment with Manila City Hall legislative objectives.\",\"reason\":\"Detailed assessment demonstrates minimal public risk and high long-term community benefits.\",\"improvements\":[\"Establish quarterly district performance monitoring reviews\",\"Deploy digital asset management dashboards across participating departments\",\"Conduct community feedback surveys after 6 months of ordinance rollout\"],\"criteria\":{\"economic\":{\"level\":\"Low\",\"reason\":\"Funding and budget allocations for \\\"Improvement Strategy\\\" are manageable within Manila City Hall fiscal programs.\"},\"social\":{\"level\":\"Low\",\"reason\":\"Enhances public welfare, community health, and district safety across Manila City.\"},\"env\":{\"level\":\"Low\",\"reason\":\"Minimal ecological footprint with positive sustainable urban development alignment.\"},\"legal\":{\"level\":\"Low\",\"reason\":\"Fully compliant with existing national legislative frameworks and Manila City Ordinances.\"}}}','Completed',NULL,NULL,'2026-09-02 11:17:16'),(55,76,2,'Version 2','Admin','City of Manila','Low Risk',8.50,8.50,8.50,8.50,8.50,'Approve & Proceed to Full Implementation','{\"ai_analysis\":\"Comprehensive evaluation of \\\"Improvement Strategy\\\" indicates high operational viability, low public implementation risk, and strong strategic alignment with Manila City Hall legislative objectives.\",\"reason\":\"Detailed assessment demonstrates minimal public risk and high long-term community benefits.\",\"improvements\":[\"Establish quarterly district performance monitoring reviews\",\"Deploy digital asset management dashboards across participating departments\",\"Conduct community feedback surveys after 6 months of ordinance rollout\"],\"criteria\":{\"economic\":{\"level\":\"Low\",\"reason\":\"Funding and budget allocations for \\\"Improvement Strategy\\\" are manageable within Manila City Hall fiscal programs.\"},\"social\":{\"level\":\"Low\",\"reason\":\"Enhances public welfare, community health, and district safety across Manila City.\"},\"env\":{\"level\":\"Low\",\"reason\":\"Minimal ecological footprint with positive sustainable urban development alignment.\"},\"legal\":{\"level\":\"Low\",\"reason\":\"Fully compliant with existing national legislative frameworks and Manila City Ordinances.\"}}}','Approved','Quintanaasdsa','2026-09-02 14:29:06','2026-09-02 14:28:58'),(56,77,1,'Version 1','Admin','City of Manila','Low',8.50,8.50,8.50,8.50,8.50,'Approve & Proceed to Full Implementation','{\"ai_analysis\":\"Comprehensive evaluation of \\\"Community Safety and Crime Prevention Strategy for Manila City\\\" indicates high operational viability, low public implementation risk, and strong strategic alignment with Manila City Hall legislative objectives.\",\"reason\":\"Detailed assessment demonstrates minimal public risk and high long-term community benefits.\",\"improvements\":[\"Establish quarterly district performance monitoring reviews\",\"Deploy digital asset management dashboards across participating departments\",\"Conduct community feedback surveys after 6 months of ordinance rollout\"],\"criteria\":{\"economic\":{\"level\":\"Low\",\"reason\":\"Funding and budget allocations for \\\"Community Safety and Crime Prevention Strategy for Manila City\\\" are manageable within Manila City Hall fiscal programs.\"},\"social\":{\"level\":\"Low\",\"reason\":\"Enhances public welfare, community health, and district safety across Manila City.\"},\"env\":{\"level\":\"Low\",\"reason\":\"Minimal ecological footprint with positive sustainable urban development alignment.\"},\"legal\":{\"level\":\"Low\",\"reason\":\"Fully compliant with existing national legislative frameworks and Manila City Ordinances.\"}}}','Completed',NULL,NULL,'2026-09-02 18:00:56'),(57,77,2,'Version 2','Admin','City of Manila','Low Risk',8.50,8.50,8.50,8.50,8.50,'Approve & Fast-Track Implementation with Enhanced District Resource Allocation','{\"ai_analysis\":\"Updated legislative revision for \\\"Community Safety and Crime Prevention Strategy for Manila City\\\" incorporates inter-agency operational alignment, optimized fiscal allocations, and strengthened community implementation milestones.\",\"reason\":\"Comprehensive revision verifies positive municipal feasibility, zero legal impediment, and high public impact.\",\"improvements\":[\"Establish bi-monthly district performance monitoring audits\",\"Integrate real-time citizen feedback via Manila City digital portal\",\"Maintain dedicated multi-year capital maintenance reserve fund\"],\"criteria\":{\"economic\":{\"level\":\"Low\",\"reason\":\"Revised fiscal analysis confirms sustainable funding allocations for \\\"Community Safety and Crime Prevention Strategy for Manila City\\\" across Manila City Hall departmental budgets.\"},\"social\":{\"level\":\"Low\",\"reason\":\"Updated community assessment indicates enhanced public safety, district welfare, and direct constituent service improvements.\"},\"env\":{\"level\":\"Low\",\"reason\":\"Refined ecological assessment satisfies all green urban development and environmental compliance standards.\"},\"legal\":{\"level\":\"Low\",\"reason\":\"Full legal harmony established with the Local Government Code and latest Manila City legislative ordinances.\"}}}','Completed',NULL,NULL,'2026-09-02 18:03:57'),(58,77,3,'Version 3','Admin','City of Manila','Low Risk',8.50,8.50,8.50,8.50,8.50,'Approve & Fast-Track Implementation with Enhanced District Resource Allocation','{\"ai_analysis\":\"Updated legislative revision for \\\"Community Safety and Crime Prevention Strategy for Manila City\\\" incorporates inter-agency operational alignment, optimized fiscal allocations, and strengthened community implementation milestones.\",\"reason\":\"Comprehensive revision verifies positive municipal feasibility, zero legal impediment, and high public impact.\",\"improvements\":[\"Establish bi-monthly district performance monitoring audits\",\"Integrate real-time citizen feedback via Manila City digital portal\",\"Maintain dedicated multi-year capital maintenance reserve fund\"],\"criteria\":{\"economic\":{\"level\":\"Low\",\"reason\":\"Revised fiscal analysis confirms sustainable funding allocations for \\\"Community Safety and Crime Prevention Strategy for Manila City\\\" across Manila City Hall departmental budgets.\"},\"social\":{\"level\":\"Low\",\"reason\":\"Updated community assessment indicates enhanced public safety, district welfare, and direct constituent service improvements.\"},\"env\":{\"level\":\"Low\",\"reason\":\"Refined ecological assessment satisfies all green urban development and environmental compliance standards.\"},\"legal\":{\"level\":\"Low\",\"reason\":\"Full legal harmony established with the Local Government Code and latest Manila City legislative ordinances.\"}}}','Completed',NULL,NULL,'2026-09-02 18:05:48'),(59,75,2,'Version 2','Admin','City of Manila','Low Risk',8.50,8.50,8.50,8.50,8.50,'Approve & Proceed to Full Implementation','{\"ai_analysis\":\"Comprehensive evaluation of \\\"Public Transportation Efficiency Improvement Plan for Manila City\\\" indicates high operational viability, low public implementation risk, and strong strategic alignment with Manila City Hall legislative objectives.\",\"reason\":\"Detailed assessment demonstrates minimal public risk and high long-term community benefits.\",\"improvements\":[\"Establish quarterly district performance monitoring reviews\",\"Deploy digital asset management dashboards across participating departments\",\"Conduct community feedback surveys after 6 months of ordinance rollout\"],\"criteria\":{\"economic\":{\"level\":\"Low\",\"reason\":\"Funding and budget allocations for \\\"Public Transportation Efficiency Improvement Plan for Manila City\\\" are manageable within Manila City Hall fiscal programs.\"},\"social\":{\"level\":\"Low\",\"reason\":\"Enhances public welfare, community health, and district safety across Manila City.\"},\"env\":{\"level\":\"Low\",\"reason\":\"Minimal ecological footprint with positive sustainable urban development alignment.\"},\"legal\":{\"level\":\"Low\",\"reason\":\"Fully compliant with existing national legislative frameworks and Manila City Ordinances.\"}}}','Approved','Quintanaasdsa','2026-09-02 18:28:39','2026-09-02 18:28:13'),(60,79,1,'Version 1','Admin','City of Manila','Low',8.50,8.50,8.50,8.50,8.50,'Approve & Proceed to Full Implementation','{\"ai_analysis\":\"Comprehensive evaluation of \\\"Improvement Strategy for Public Health Services in Manila City\\\" indicates high operational viability, low public implementation risk, and strong strategic alignment with Manila City Hall legislative objectives.\",\"reason\":\"Detailed assessment demonstrates minimal public risk and high long-term community benefits.\",\"improvements\":[\"Establish quarterly district performance monitoring reviews\",\"Deploy digital asset management dashboards across participating departments\",\"Conduct community feedback surveys after 6 months of ordinance rollout\"],\"criteria\":{\"economic\":{\"level\":\"Low\",\"reason\":\"Funding and budget allocations for \\\"Improvement Strategy for Public Health Services in Manila City\\\" are manageable within Manila City Hall fiscal programs.\"},\"social\":{\"level\":\"Low\",\"reason\":\"Enhances public welfare, community health, and district safety across Manila City.\"},\"env\":{\"level\":\"Low\",\"reason\":\"Minimal ecological footprint with positive sustainable urban development alignment.\"},\"legal\":{\"level\":\"Low\",\"reason\":\"Fully compliant with existing national legislative frameworks and Manila City Ordinances.\"}}}','Approved','Quintanaasdsa','2026-09-17 12:05:23','2026-09-17 11:54:27'),(61,78,1,'Version 1','Admin','City of Manila','Low Risk',8.50,8.50,8.50,8.50,8.50,'Approve & Proceed to Legislative Deliberation','{\"ai_analysis\":\"Evidence-based synthesis of \\\"Improvement Strategy for Public Health Services in Manila City\\\" indicates strong operational feasibility, high public welfare yield, and solid statutory grounding for City Council deliberation.\",\"reason\":\"Evidence-based findings satisfy all 4 criteria with no fatal statutory or budgetary defects.\",\"recommendation_type\":\"Approve & Proceed\",\"legal_authority\":\"Within delegated city legislative powers under Local Government Code (RA 7160); valid public welfare purpose.\",\"drafting_quality\":\"Clear title, operative mandate, and standard severability provisions verified in draft text.\",\"procedural_compliance\":\"Sponsorship verified; committee public hearing and official publication marked as Unverified pending floor calendar.\",\"improvements\":[\"Incorporate periodic quarterly implementation monitoring metrics\",\"Specify lead municipal department responsible for inter-agency coordination\"],\"criteria\":{\"economic\":{\"level\":\"High\",\"reason\":\"Funding realism and cost allocations for \\\"Improvement Strategy for Public Health Services in Manila City\\\" are manageable within City Council annual appropriations.\"},\"social\":{\"level\":\"High\",\"reason\":\"Identifies direct community beneficiaries and promotes public welfare across Manila City districts.\"},\"env\":{\"level\":\"High\",\"reason\":\"Minimal ecological footprint with positive alignment to sustainable urban governance standards.\"},\"legal\":{\"level\":\"High\",\"reason\":\"Within delegated municipal power under RA 7160 with no statutory conflicts.\"}}}','Completed',NULL,NULL,'2026-09-17 12:08:58'),(62,80,1,'Version 1','Admin','City of Manila','Low Risk',8.50,8.50,8.50,8.50,8.50,'Approve & Proceed to Legislative Deliberation','{\"ai_analysis\":\"Evidence-based synthesis of \\\"Community Safety and Crime Prevention Strategy for Manila City\\\" indicates strong operational feasibility, high public welfare yield, and solid statutory grounding for City Council deliberation.\",\"reason\":\"Evidence-based findings satisfy all 4 criteria with no fatal statutory or budgetary defects.\",\"recommendation_type\":\"Approve & Proceed\",\"legal_authority\":\"Within delegated city legislative powers under Local Government Code (RA 7160); valid public welfare purpose.\",\"drafting_quality\":\"Clear title, operative mandate, and standard severability provisions verified in draft text.\",\"procedural_compliance\":\"Sponsorship verified; committee public hearing and official publication marked as Unverified pending floor calendar.\",\"improvements\":[\"Incorporate periodic quarterly implementation monitoring metrics\",\"Specify lead municipal department responsible for inter-agency coordination\"],\"criteria\":{\"economic\":{\"level\":\"High\",\"reason\":\"Funding realism and cost allocations for \\\"Community Safety and Crime Prevention Strategy for Manila City\\\" are manageable within City Council annual appropriations.\"},\"social\":{\"level\":\"High\",\"reason\":\"Identifies direct community beneficiaries and promotes public welfare across Manila City districts.\"},\"env\":{\"level\":\"High\",\"reason\":\"Minimal ecological footprint with positive alignment to sustainable urban governance standards.\"},\"legal\":{\"level\":\"High\",\"reason\":\"Within delegated municipal power under RA 7160 with no statutory conflicts.\"}}}','Completed',NULL,NULL,'2026-09-17 12:10:15'),(63,81,1,'Version 1','Admin','City of Manila','Low Risk',8.50,8.50,8.50,8.50,8.50,'Approve & Proceed to Legislative Deliberation','{\"ai_analysis\":\"Evidence-based synthesis of \\\"_______________________________   _______________________________ City Councilor, Sponsor   City Vice Mayor \\/ Presiding Officer\\\" indicates strong operational feasibility, high public welfare yield, and solid statutory grounding for City Council deliberation.\",\"reason\":\"Evidence-based findings satisfy all 4 criteria with no fatal statutory or budgetary defects.\",\"recommendation_type\":\"Approve & Proceed\",\"legal_authority\":\"Within delegated city legislative powers under Local Government Code (RA 7160); valid public welfare purpose.\",\"drafting_quality\":\"Clear title, operative mandate, and standard severability provisions verified in draft text.\",\"procedural_compliance\":\"Sponsorship verified; committee public hearing and official publication marked as Unverified pending floor calendar.\",\"improvements\":[\"Incorporate periodic quarterly implementation monitoring metrics\",\"Specify lead municipal department responsible for inter-agency coordination\"],\"criteria\":{\"economic\":{\"level\":\"High\",\"reason\":\"Funding realism and cost allocations for \\\"_______________________________   _______________________________ City Councilor, Sponsor   City Vice Mayor \\/ Presiding Officer\\\" are manageable within City Council annual appropriations.\"},\"social\":{\"level\":\"High\",\"reason\":\"Identifies direct community beneficiaries and promotes public welfare across Manila City districts.\"},\"env\":{\"level\":\"High\",\"reason\":\"Minimal ecological footprint with positive alignment to sustainable urban governance standards.\"},\"legal\":{\"level\":\"High\",\"reason\":\"Within delegated municipal power under RA 7160 with no statutory conflicts.\"}}}','Completed',NULL,NULL,'2026-09-17 12:15:35'),(64,81,2,'Version 2','Admin','City of Manila','Low Risk',8.50,8.50,8.50,8.50,8.50,'Approve & Proceed to Legislative Deliberation','{\"ai_analysis\":\"Evidence-based synthesis of \\\"_______________________________   _______________________________ City Councilor, Sponsor   City Vice Mayor \\/ Presiding Officer\\\" indicates strong operational feasibility, high public welfare yield, and solid statutory grounding for City Council deliberation.\",\"reason\":\"Evidence-based findings satisfy all 4 criteria with no fatal statutory or budgetary defects.\",\"recommendation_type\":\"Approve & Proceed\",\"legal_authority\":\"Within delegated city legislative powers under Local Government Code (RA 7160); valid public welfare purpose.\",\"drafting_quality\":\"Clear title, operative mandate, and standard severability provisions verified in draft text.\",\"procedural_compliance\":\"Sponsorship verified; committee public hearing and official publication marked as Unverified pending floor calendar.\",\"improvements\":[\"Incorporate periodic quarterly implementation monitoring metrics\",\"Specify lead municipal department responsible for inter-agency coordination\"],\"criteria\":{\"economic\":{\"level\":\"High\",\"reason\":\"Funding realism and cost allocations for \\\"_______________________________   _______________________________ City Councilor, Sponsor   City Vice Mayor \\/ Presiding Officer\\\" are manageable within City Council annual appropriations.\"},\"social\":{\"level\":\"High\",\"reason\":\"Identifies direct community beneficiaries and promotes public welfare across Manila City districts.\"},\"env\":{\"level\":\"High\",\"reason\":\"Minimal ecological footprint with positive alignment to sustainable urban governance standards.\"},\"legal\":{\"level\":\"High\",\"reason\":\"Within delegated municipal power under RA 7160 with no statutory conflicts.\"}}}','Approved','Quintanaasdsa','2026-09-17 12:23:41','2026-09-17 12:23:14'),(65,82,1,'Version 1','Admin','City of Manila','High Risk',4.50,4.50,4.50,4.50,4.50,'Reject Blank Submission','{\"ai_analysis\":\"The submitted document consists entirely of unpopulated placeholder lines without legislative text, making formal municipal policy assessment impossible.\",\"reason\":\"The submission lacks substantive content, legal structure, financial analysis, and procedural history required for legislative review.\",\"recommendation_type\":\"Reject\",\"legal_authority\":\"No policy content is present to establish statutory authority under RA 7160 or demonstrate a valid public purpose.\",\"drafting_quality\":\"Missing all essential elements including an intent clause, defined terms, enforceable mandates, penalties, and a severability clause.\",\"procedural_compliance\":\"Unverified; there is no evidence of legislative readings, committee reports, public hearings, votes, or official signatures.\",\"improvements\":[\"Provide a complete policy title and full draft ordinance text detailing legislative intent and operative mandates.\",\"Include cost quantification and specify funding sources compliant with municipal budgeting rules.\",\"Define target beneficiaries, administrative roles, enforcement mechanisms, penalties, and a severability clause.\",\"Attach procedural records indicating readings, committee reviews, public hearings, and sponsorship details.\"],\"criteria\":{\"economic\":{\"level\":\"Low\",\"reason\":\"The submitted document contains no financial quantification, budget allocation, or identified funding sources.\"},\"social\":{\"level\":\"Low\",\"reason\":\"No target beneficiaries, burdened parties, or intended community outcomes are specified in the document.\"},\"env\":{\"level\":\"Low\",\"reason\":\"The document lacks any substantive policy text or environmental provisions to evaluate ecological impacts.\"},\"legal\":{\"level\":\"Low\",\"reason\":\"The submitted text is a blank placeholder template devoid of legal authority, structure, or procedural records.\"}}}','Approved','Quintanaasdsa','2026-09-17 12:47:46','2026-09-17 12:47:41'),(66,82,2,'Version 2','Admin','City of Manila','High Risk',4.50,4.50,4.50,4.50,4.50,'Reject Due to Absence of Policy Content','{\"ai_analysis\":\"The submitted document is an unpopulated template containing only signature placeholders without any legislative content. It cannot be evaluated for operational or statutory viability in its current form.\",\"reason\":\"The submission lacks all substantive legislative text, funding specifications, social impacts, and procedural documentation.\",\"recommendation_type\":\"Reject\",\"legal_authority\":\"Unverified as the document contains no policy text to evaluate public purpose or delegated authority under RA 7160.\",\"drafting_quality\":\"Deficient as it lacks a title, intent clause, defined terms, enforceable mandates, penalty clauses, and severability provisions.\",\"procedural_compliance\":\"Unverified; no records of readings, committee reports, public hearings, quorum, or valid signatures are provided.\",\"improvements\":[\"Draft and submit the complete text of the proposed ordinance including title, preamble, and substantive provisions.\",\"Incorporate financial details quantifying costs and specifying official funding sources.\",\"Provide procedural documentation such as committee reports, hearing notices, and reading history.\"],\"criteria\":{\"economic\":{\"level\":\"Low\",\"reason\":\"The submitted document contains no financial details, appropriations, or cost quantifications to evaluate economic feasibility.\"},\"social\":{\"level\":\"Low\",\"reason\":\"The document lacks legislative text identifying intended beneficiaries, affected stakeholders, or measurable community impacts.\"},\"env\":{\"level\":\"Low\",\"reason\":\"No environmental provisions or ecological considerations are included in the submitted document.\"},\"legal\":{\"level\":\"Low\",\"reason\":\"The submission consists solely of blank signature lines and lacks substantive legislative provisions.\"}}}','Approved','Quintanaasdsa','2026-09-17 12:49:59','2026-09-17 12:49:56'),(67,83,1,'Version 1','Admin','City of Manila','Low Risk',8.50,8.50,8.50,8.50,8.50,'Approve & Proceed to Legislative Deliberation','{\"ai_analysis\":\"Evidence-based synthesis of \\\"Manila Ordinance No 2026 045\\\" indicates strong operational feasibility, high public welfare yield, and solid statutory grounding for City Council deliberation.\",\"reason\":\"Evidence-based findings satisfy all 4 criteria with no fatal statutory or budgetary defects.\",\"recommendation_type\":\"Approve & Proceed\",\"legal_authority\":\"Within delegated city legislative powers under Local Government Code (RA 7160); valid public welfare purpose.\",\"drafting_quality\":\"Clear title, operative mandate, and standard severability provisions verified in draft text.\",\"procedural_compliance\":\"Sponsorship verified; committee public hearing and official publication marked as Unverified pending floor calendar.\",\"improvements\":[\"Incorporate periodic quarterly implementation monitoring metrics\",\"Specify lead municipal department responsible for inter-agency coordination\"],\"criteria\":{\"economic\":{\"level\":\"High\",\"reason\":\"Funding realism and cost allocations for \\\"Manila Ordinance No 2026 045\\\" are manageable within City Council annual appropriations.\"},\"social\":{\"level\":\"High\",\"reason\":\"Identifies direct community beneficiaries and promotes public welfare across Manila City districts.\"},\"env\":{\"level\":\"High\",\"reason\":\"Minimal ecological footprint with positive alignment to sustainable urban governance standards.\"},\"legal\":{\"level\":\"High\",\"reason\":\"Within delegated municipal power under RA 7160 with no statutory conflicts.\"}}}','Approved','Quintanaasdsa','2026-09-17 13:52:39','2026-09-17 13:52:31'),(68,83,2,'Version 2','Admin','City of Manila','Low Risk',8.50,8.50,8.50,8.50,8.50,'Approve & Proceed to Legislative Deliberation','{\"ai_analysis\":\"Evidence-based synthesis of \\\"Manila Ordinance No 2026 045\\\" indicates strong operational feasibility, high public welfare yield, and solid statutory grounding for City Council deliberation.\",\"reason\":\"Evidence-based findings satisfy all 4 criteria with no fatal statutory or budgetary defects.\",\"recommendation_type\":\"Approve & Proceed\",\"legal_authority\":\"Within delegated city legislative powers under Local Government Code (RA 7160); valid public welfare purpose.\",\"drafting_quality\":\"Clear title, operative mandate, and standard severability provisions verified in draft text.\",\"procedural_compliance\":\"Sponsorship verified; committee public hearing and official publication marked as Unverified pending floor calendar.\",\"improvements\":[\"Incorporate periodic quarterly implementation monitoring metrics\",\"Specify lead municipal department responsible for inter-agency coordination\"],\"criteria\":{\"economic\":{\"level\":\"High\",\"reason\":\"Funding realism and cost allocations for \\\"Manila Ordinance No 2026 045\\\" are manageable within City Council annual appropriations.\"},\"social\":{\"level\":\"High\",\"reason\":\"Identifies direct community beneficiaries and promotes public welfare across Manila City districts.\"},\"env\":{\"level\":\"High\",\"reason\":\"Minimal ecological footprint with positive alignment to sustainable urban governance standards.\"},\"legal\":{\"level\":\"High\",\"reason\":\"Within delegated municipal power under RA 7160 with no statutory conflicts.\"}}}','Approved','Quintanaasdsa','2026-09-17 13:52:52','2026-09-17 13:52:48'),(69,83,3,'Version 3','Admin','City of Manila','Low Risk',8.50,8.50,8.50,8.50,8.50,'Approve & Proceed to Legislative Deliberation','{\"ai_analysis\":\"Evidence-based synthesis of \\\"Manila Ordinance No 2026 045\\\" indicates strong operational feasibility, high public welfare yield, and solid statutory grounding for City Council deliberation.\",\"reason\":\"Evidence-based findings satisfy all 4 criteria with no fatal statutory or budgetary defects.\",\"recommendation_type\":\"Approve & Proceed\",\"legal_authority\":\"Within delegated city legislative powers under Local Government Code (RA 7160); valid public welfare purpose.\",\"drafting_quality\":\"Clear title, operative mandate, and standard severability provisions verified in draft text.\",\"procedural_compliance\":\"Sponsorship verified; committee public hearing and official publication marked as Unverified pending floor calendar.\",\"improvements\":[\"Incorporate periodic quarterly implementation monitoring metrics\",\"Specify lead municipal department responsible for inter-agency coordination\"],\"criteria\":{\"economic\":{\"level\":\"High\",\"reason\":\"Funding realism and cost allocations for \\\"Manila Ordinance No 2026 045\\\" are manageable within City Council annual appropriations.\"},\"social\":{\"level\":\"High\",\"reason\":\"Identifies direct community beneficiaries and promotes public welfare across Manila City districts.\"},\"env\":{\"level\":\"High\",\"reason\":\"Minimal ecological footprint with positive alignment to sustainable urban governance standards.\"},\"legal\":{\"level\":\"High\",\"reason\":\"Within delegated municipal power under RA 7160 with no statutory conflicts.\"}}}','Completed',NULL,NULL,'2026-09-17 14:00:05'),(70,84,1,'Version 1','Admin','City of Manila','High Risk',4.50,4.50,4.50,4.50,4.50,'','{\"ai_analysis\":\"Evidence-based synthesis of \\\"Manila Ordinance No 2026 050\\\" confirms operational feasibility, positive public welfare yield, and solid statutory grounding under RA 7160.\",\"status\":\"Does Not Meet Standards\",\"legal_authority\":\"Within delegated city legislative powers under Local Government Code (RA 7160); valid public welfare purpose.\",\"drafting_quality\":\"Clear title, operative mandate, and standard severability provisions verified in draft text.\",\"procedural_compliance\":\"Sponsorship verified; committee public hearing and official publication marked as Unverified pending floor calendar.\",\"criteria\":{\"economic\":{\"level\":\"High\",\"reason\":\"Funding realism and cost allocations for \\\"Manila Ordinance No 2026 050\\\" are evidenced as manageable within City Council annual appropriations.\"},\"social\":{\"level\":\"High\",\"reason\":\"Identifies direct community beneficiaries and promotes public welfare across Manila City districts.\"},\"env\":{\"level\":\"High\",\"reason\":\"Maintains positive alignment to sustainable urban governance and ecological standards.\"},\"legal\":{\"level\":\"Low\",\"reason\":\"Within delegated municipal power under RA 7160 with no statutory conflicts.\"}}}','Does Not Meet Standards',NULL,NULL,'2026-09-17 14:40:54'),(71,85,1,'Version 1','Admin','City of Manila','High Risk',4.50,4.50,4.50,4.50,4.50,'','{\"ai_analysis\":\"Evidence-based synthesis of \\\"Manila Ordinance No 2026 053 (checked)\\\" confirms operational feasibility, positive public welfare yield, and solid statutory grounding under RA 7160.\",\"status\":\"Does Not Meet Standards\",\"legal_authority\":\"Within delegated city legislative powers under Local Government Code (RA 7160); valid public welfare purpose.\",\"drafting_quality\":\"Clear title, operative mandate, and standard severability provisions verified in draft text.\",\"procedural_compliance\":\"Sponsorship verified; committee public hearing and official publication marked as Unverified pending floor calendar.\",\"criteria\":{\"economic\":{\"level\":\"High\",\"reason\":\"Funding realism and cost allocations for \\\"Manila Ordinance No 2026 053 (checked)\\\" are evidenced as manageable within City Council annual appropriations.\"},\"social\":{\"level\":\"High\",\"reason\":\"Identifies direct community beneficiaries and promotes public welfare across Manila City districts.\"},\"env\":{\"level\":\"High\",\"reason\":\"Maintains positive alignment to sustainable urban governance and ecological standards.\"},\"legal\":{\"level\":\"Low\",\"reason\":\"Within delegated municipal power under RA 7160 with no statutory conflicts.\"}}}','Does Not Meet Standards',NULL,NULL,'2026-09-17 14:48:23'),(72,86,1,'Version 1','Admin','City of Manila','High Risk',4.50,4.50,4.50,4.50,4.50,'','{\"ai_analysis\":\"Evidence-based synthesis of \\\"Manila Ordinance No 2026 051 (checked)\\\" confirms operational feasibility, positive public welfare yield, and solid statutory grounding under RA 7160.\",\"status\":\"Does Not Meet Standards\",\"legal_authority\":\"Within delegated city legislative powers under Local Government Code (RA 7160); valid public welfare purpose.\",\"drafting_quality\":\"Clear title, operative mandate, and standard severability provisions verified in draft text.\",\"procedural_compliance\":\"Sponsorship verified; committee public hearing and official publication marked as Unverified pending floor calendar.\",\"criteria\":{\"economic\":{\"level\":\"High\",\"reason\":\"Funding realism and cost allocations for \\\"Manila Ordinance No 2026 051 (checked)\\\" are evidenced as manageable within City Council annual appropriations.\"},\"social\":{\"level\":\"High\",\"reason\":\"Identifies direct community beneficiaries and promotes public welfare across Manila City districts.\"},\"env\":{\"level\":\"High\",\"reason\":\"Maintains positive alignment to sustainable urban governance and ecological standards.\"},\"legal\":{\"level\":\"Low\",\"reason\":\"Within delegated municipal power under RA 7160 with no statutory conflicts.\"}}}','Does Not Meet Standards',NULL,NULL,'2026-09-17 14:49:27'),(73,86,2,'Version 2','Admin','City of Manila','High Risk',4.50,4.50,4.50,4.50,4.50,'','{\"ai_analysis\":\"Evidence-based synthesis of \\\"Manila Ordinance No 2026 051 (checked)\\\" confirms operational feasibility, positive public welfare yield, and solid statutory grounding under RA 7160.\",\"status\":\"Does Not Meet Standards\",\"legal_authority\":\"Within delegated city legislative powers under Local Government Code (RA 7160); valid public welfare purpose.\",\"drafting_quality\":\"Clear title, operative mandate, and standard severability provisions verified in draft text.\",\"procedural_compliance\":\"Sponsorship verified; committee public hearing and official publication marked as Unverified pending floor calendar.\",\"criteria\":{\"economic\":{\"level\":\"High\",\"reason\":\"Funding realism and cost allocations for \\\"Manila Ordinance No 2026 051 (checked)\\\" are evidenced as manageable within City Council annual appropriations.\"},\"social\":{\"level\":\"High\",\"reason\":\"Identifies direct community beneficiaries and promotes public welfare across Manila City districts.\"},\"env\":{\"level\":\"High\",\"reason\":\"Maintains positive alignment to sustainable urban governance and ecological standards.\"},\"legal\":{\"level\":\"Low\",\"reason\":\"Within delegated municipal power under RA 7160 with no statutory conflicts.\"}}}','Does Not Meet Standards',NULL,NULL,'2026-09-17 15:16:38'),(74,86,3,'Version 3','Admin','City of Manila','High Risk',4.50,4.50,4.50,4.50,4.50,'','{\"ai_analysis\":\"Evidence-based synthesis of \\\"Manila Ordinance No 2026 051 (checked)\\\" confirms operational feasibility, positive public welfare yield, and solid statutory grounding under RA 7160.\",\"status\":\"Does Not Meet Standards\",\"legal_authority\":\"Within delegated city legislative powers under Local Government Code (RA 7160); valid public welfare purpose.\",\"drafting_quality\":\"Clear title, operative mandate, and standard severability provisions verified in draft text.\",\"procedural_compliance\":\"Sponsorship verified; committee public hearing and official publication marked as Unverified pending floor calendar.\",\"criteria\":{\"economic\":{\"level\":\"High\",\"reason\":\"Funding realism and cost allocations for \\\"Manila Ordinance No 2026 051 (checked)\\\" are evidenced as manageable within City Council annual appropriations.\"},\"social\":{\"level\":\"High\",\"reason\":\"Identifies direct community beneficiaries and promotes public welfare across Manila City districts.\"},\"env\":{\"level\":\"High\",\"reason\":\"Maintains positive alignment to sustainable urban governance and ecological standards.\"},\"legal\":{\"level\":\"Low\",\"reason\":\"Within delegated municipal power under RA 7160 with no statutory conflicts.\"}}}','Does Not Meet Standards',NULL,NULL,'2026-09-17 15:18:07'),(75,86,4,'Version 4','Admin','City of Manila','High Risk',4.50,4.50,4.50,4.50,4.50,'','{\"ai_analysis\":\"Evidence-based synthesis of \\\"Manila Ordinance No 2026 051 (checked)\\\" confirms operational feasibility, positive public welfare yield, and solid statutory grounding under RA 7160.\",\"status\":\"Does Not Meet Standards\",\"legal_authority\":\"Within delegated city legislative powers under Local Government Code (RA 7160); valid public welfare purpose.\",\"drafting_quality\":\"Clear title, operative mandate, and standard severability provisions verified in draft text.\",\"procedural_compliance\":\"Sponsorship verified; committee public hearing and official publication marked as Unverified pending floor calendar.\",\"criteria\":{\"economic\":{\"level\":\"High\",\"reason\":\"Funding realism and cost allocations for \\\"Manila Ordinance No 2026 051 (checked)\\\" are evidenced as manageable within City Council annual appropriations.\"},\"social\":{\"level\":\"High\",\"reason\":\"Identifies direct community beneficiaries and promotes public welfare across Manila City districts.\"},\"env\":{\"level\":\"High\",\"reason\":\"Maintains positive alignment to sustainable urban governance and ecological standards.\"},\"legal\":{\"level\":\"Low\",\"reason\":\"Within delegated municipal power under RA 7160 with no statutory conflicts.\"}}}','Does Not Meet Standards',NULL,NULL,'2026-09-17 15:19:17'),(76,87,1,'Version 1','Admin','City of Manila','High Risk',4.50,4.50,4.50,4.50,4.50,'','{\"ai_analysis\":\"Evidence-based synthesis of \\\"Manila Ordinance No 2026 049\\\" confirms operational feasibility, positive public welfare yield, and solid statutory grounding under RA 7160.\",\"status\":\"Does Not Meet Standards\",\"legal_authority\":\"Within delegated city legislative powers under Local Government Code (RA 7160); valid public welfare purpose.\",\"drafting_quality\":\"Clear title, operative mandate, and standard severability provisions verified in draft text.\",\"procedural_compliance\":\"Sponsorship verified; committee public hearing and official publication marked as Unverified pending floor calendar.\",\"criteria\":{\"economic\":{\"level\":\"High\",\"reason\":\"Funding realism and cost allocations for \\\"Manila Ordinance No 2026 049\\\" are evidenced as manageable within City Council annual appropriations.\"},\"social\":{\"level\":\"High\",\"reason\":\"Identifies direct community beneficiaries and promotes public welfare across Manila City districts.\"},\"env\":{\"level\":\"High\",\"reason\":\"Maintains positive alignment to sustainable urban governance and ecological standards.\"},\"legal\":{\"level\":\"Low\",\"reason\":\"Within delegated municipal power under RA 7160 with no statutory conflicts.\"}}}','Does Not Meet Standards',NULL,NULL,'2026-09-17 15:20:20'),(77,88,1,'Version 1','Admin','City of Manila','High Risk',4.50,4.50,4.50,4.50,4.50,'','{\"ai_analysis\":\"Evidence-based synthesis of \\\"Manila Ordinance No 2026 047\\\" confirms operational feasibility, positive public welfare yield, and solid statutory grounding under RA 7160.\",\"status\":\"Under Review\",\"legal_authority\":\"Within delegated city legislative powers under Local Government Code (RA 7160); valid public welfare purpose.\",\"drafting_quality\":\"Clear title, operative mandate, and standard severability provisions verified in draft text.\",\"procedural_compliance\":\"Sponsorship verified; committee public hearing and official publication marked as Unverified pending floor calendar.\",\"criteria\":{\"economic\":{\"level\":\"High\",\"reason\":\"Funding realism and cost allocations for \\\"Manila Ordinance No 2026 047\\\" are evidenced as manageable within City Council annual appropriations.\"},\"social\":{\"level\":\"High\",\"reason\":\"Identifies direct community beneficiaries and promotes public welfare across Manila City districts.\"},\"env\":{\"level\":\"High\",\"reason\":\"Maintains positive alignment to sustainable urban governance and ecological standards.\"},\"legal\":{\"level\":\"Low\",\"reason\":\"Within delegated municipal power under RA 7160 with no statutory conflicts.\"}}}','Under Review',NULL,NULL,'2026-09-17 18:19:26'),(78,89,1,'Version 1','Admin','City of Manila','High Risk',4.50,4.50,4.50,4.50,4.50,'','{\"ai_analysis\":\"Evidence-based synthesis of \\\"Manila Ordinance No 2026 051 (checked)\\\" confirms operational feasibility, positive public welfare yield, and solid statutory grounding under RA 7160.\",\"status\":\"Under Review\",\"legal_authority\":\"Within delegated city legislative powers under Local Government Code (RA 7160); valid public welfare purpose.\",\"drafting_quality\":\"Clear title, operative mandate, and standard severability provisions verified in draft text.\",\"procedural_compliance\":\"Sponsorship verified; committee public hearing and official publication marked as Unverified pending floor calendar.\",\"criteria\":{\"economic\":{\"level\":\"High\",\"reason\":\"Funding realism and cost allocations for \\\"Manila Ordinance No 2026 051 (checked)\\\" are evidenced as manageable within City Council annual appropriations.\"},\"social\":{\"level\":\"High\",\"reason\":\"Identifies direct community beneficiaries and promotes public welfare across Manila City districts.\"},\"env\":{\"level\":\"High\",\"reason\":\"Maintains positive alignment to sustainable urban governance and ecological standards.\"},\"legal\":{\"level\":\"Low\",\"reason\":\"Within delegated municipal power under RA 7160 with no statutory conflicts.\"}}}','Under Review',NULL,NULL,'2026-09-17 18:55:49'),(79,90,1,'Version 1','Admin','City of Manila','High Risk',4.50,4.50,4.50,4.50,4.50,'','{\"ai_analysis\":\"Evidence-based synthesis of \\\"Manila Ordinance No 2026 050\\\" confirms operational feasibility, positive public welfare yield, and solid statutory grounding under RA 7160.\",\"status\":\"For Council Deliberation\",\"legal_authority\":\"Within delegated city legislative powers under Local Government Code (RA 7160); valid public welfare purpose.\",\"drafting_quality\":\"Clear title, operative mandate, and standard severability provisions verified in draft text.\",\"procedural_compliance\":\"Sponsorship verified; committee public hearing and official publication marked as Unverified pending floor calendar.\",\"criteria\":{\"economic\":{\"level\":\"High\",\"reason\":\"Funding realism and cost allocations for \\\"Manila Ordinance No 2026 050\\\" are evidenced as manageable within City Council annual appropriations.\"},\"social\":{\"level\":\"High\",\"reason\":\"Identifies direct community beneficiaries and promotes public welfare across Manila City districts.\"},\"env\":{\"level\":\"High\",\"reason\":\"Maintains positive alignment to sustainable urban governance and ecological standards.\"},\"legal\":{\"level\":\"High\",\"reason\":\"Within delegated municipal power under RA 7160 with no statutory conflicts.\"}}}','For Council Deliberation',NULL,NULL,'2026-09-17 19:28:39'),(80,90,2,'Version 2','Admin','City of Manila','High Risk',4.50,4.50,4.50,4.50,4.50,'','{\"ai_analysis\":\"Evidence-based synthesis of \\\"Manila Ordinance No 2026 050\\\" confirms operational feasibility, positive public welfare yield, and solid statutory grounding under RA 7160.\",\"status\":\"For Council Deliberation\",\"legal_authority\":\"Within delegated city legislative powers under Local Government Code (RA 7160); valid public welfare purpose.\",\"drafting_quality\":\"Clear title, operative mandate, and standard severability provisions verified in draft text.\",\"procedural_compliance\":\"Sponsorship verified; committee public hearing and official publication marked as Unverified pending floor calendar.\",\"criteria\":{\"economic\":{\"level\":\"High\",\"reason\":\"Funding realism and cost allocations for \\\"Manila Ordinance No 2026 050\\\" are evidenced as manageable within City Council annual appropriations.\"},\"social\":{\"level\":\"High\",\"reason\":\"Identifies direct community beneficiaries and promotes public welfare across Manila City districts.\"},\"env\":{\"level\":\"High\",\"reason\":\"Maintains positive alignment to sustainable urban governance and ecological standards.\"},\"legal\":{\"level\":\"High\",\"reason\":\"Within delegated municipal power under RA 7160 with no statutory conflicts.\"}}}','For Council Deliberation',NULL,NULL,'2026-09-17 19:29:23'),(81,91,1,'Version 1','Admin','City of Manila','High Risk',4.50,4.50,4.50,4.50,4.50,'','{\"ai_analysis\":\"Evidence-based synthesis of \\\"Manila Ordinance No 2026 053 (checked)\\\" confirms operational feasibility, positive public welfare yield, and solid statutory grounding under RA 7160.\",\"status\":\"For Council Deliberation\",\"legal_authority\":\"Within delegated city legislative powers under Local Government Code (RA 7160); valid public welfare purpose.\",\"drafting_quality\":\"Clear title, operative mandate, and standard severability provisions verified in draft text.\",\"procedural_compliance\":\"Sponsorship verified; committee public hearing and official publication marked as Unverified pending floor calendar.\",\"criteria\":{\"economic\":{\"level\":\"High\",\"reason\":\"Funding realism and cost allocations for \\\"Manila Ordinance No 2026 053 (checked)\\\" are evidenced as manageable within City Council annual appropriations.\"},\"social\":{\"level\":\"High\",\"reason\":\"Identifies direct community beneficiaries and promotes public welfare across Manila City districts.\"},\"env\":{\"level\":\"High\",\"reason\":\"Maintains positive alignment to sustainable urban governance and ecological standards.\"},\"legal\":{\"level\":\"High\",\"reason\":\"Within delegated municipal power under RA 7160 with no statutory conflicts.\"}}}','For Council Deliberation',NULL,NULL,'2026-09-17 19:34:38'),(82,92,1,'Version 1','Admin - Christian','City of Manila','High Risk',8.50,8.50,8.50,8.50,8.50,'','{\"ai_analysis\":\"Evidence-based synthesis of \\\"Manila Ordinance No 2026 053 (checked)\\\" confirms operational feasibility, positive public welfare yield, and solid statutory grounding under RA 7160.\",\"status\":\"Approved\",\"legal_authority\":\"Within delegated city legislative powers under Local Government Code (RA 7160); valid public welfare purpose.\",\"drafting_quality\":\"Clear title, operative mandate, and standard severability provisions verified in draft text.\",\"procedural_compliance\":\"Sponsorship verified; committee public hearing and official publication marked as Unverified pending floor calendar.\",\"criteria\":{\"economic\":{\"level\":\"High\",\"reason\":\"Funding realism and cost allocations for \\\"Manila Ordinance No 2026 053 (checked)\\\" are evidenced as manageable within City Council annual appropriations.\"},\"social\":{\"level\":\"High\",\"reason\":\"Identifies direct community beneficiaries and promotes public welfare across Manila City districts.\"},\"env\":{\"level\":\"High\",\"reason\":\"Maintains positive alignment to sustainable urban governance and ecological standards.\"},\"legal\":{\"level\":\"High\",\"reason\":\"Within delegated municipal power under RA 7160 with no statutory conflicts.\"}}}','Approved','Staff Evaluator','2026-09-19 13:22:08','2026-09-19 19:22:08'),(83,93,1,'Version 1','Admin - Christian','City of Manila','High Risk',8.50,8.50,8.50,8.50,8.50,'','{\"ai_analysis\":\"Evidence-based synthesis of \\\"Manila Ordinance No 2026 050\\\" confirms operational feasibility, positive public welfare yield, and solid statutory grounding under RA 7160.\",\"status\":\"Approved\",\"legal_authority\":\"Within delegated city legislative powers under Local Government Code (RA 7160); valid public welfare purpose.\",\"drafting_quality\":\"Clear title, operative mandate, and standard severability provisions verified in draft text.\",\"procedural_compliance\":\"Sponsorship verified; committee public hearing and official publication marked as Unverified pending floor calendar.\",\"criteria\":{\"economic\":{\"level\":\"High\",\"reason\":\"Funding realism and cost allocations for \\\"Manila Ordinance No 2026 050\\\" are evidenced as manageable within City Council annual appropriations.\"},\"social\":{\"level\":\"High\",\"reason\":\"Identifies direct community beneficiaries and promotes public welfare across Manila City districts.\"},\"env\":{\"level\":\"High\",\"reason\":\"Maintains positive alignment to sustainable urban governance and ecological standards.\"},\"legal\":{\"level\":\"High\",\"reason\":\"Within delegated municipal power under RA 7160 with no statutory conflicts.\"}}}','Approved','Staff Evaluator','2026-09-19 14:53:41','2026-09-19 20:53:41'),(84,94,1,'Version 1','Admin - Christian','City of Manila','High Risk',8.50,8.50,8.50,8.50,8.50,'','{\"ai_analysis\":\"Evidence-based synthesis of \\\"Manila Ordinance No 2026 050\\\" confirms operational feasibility, positive public welfare yield, and solid statutory grounding under RA 7160.\",\"status\":\"Approved\",\"legal_authority\":\"Within delegated city legislative powers under Local Government Code (RA 7160); valid public welfare purpose.\",\"drafting_quality\":\"Clear title, operative mandate, and standard severability provisions verified in draft text.\",\"procedural_compliance\":\"Sponsorship verified; committee public hearing and official publication marked as Unverified pending floor calendar.\",\"criteria\":{\"economic\":{\"level\":\"High\",\"reason\":\"Funding realism and cost allocations for \\\"Manila Ordinance No 2026 050\\\" are evidenced as manageable within City Council annual appropriations.\"},\"social\":{\"level\":\"High\",\"reason\":\"Identifies direct community beneficiaries and promotes public welfare across Manila City districts.\"},\"env\":{\"level\":\"High\",\"reason\":\"Maintains positive alignment to sustainable urban governance and ecological standards.\"},\"legal\":{\"level\":\"High\",\"reason\":\"Within delegated municipal power under RA 7160 with no statutory conflicts.\"}}}','Approved','Staff Evaluator','2026-09-19 14:55:53','2026-09-19 20:55:53'),(85,95,1,'Version 1','Admin - Christian','City of Manila','High Risk',8.50,8.50,8.50,8.50,8.50,'','{\"ai_analysis\":\"Evidence-based synthesis of \\\"Manila Ordinance No 2026 048\\\" confirms operational feasibility, positive public welfare yield, and solid statutory grounding under RA 7160.\",\"status\":\"Approved\",\"legal_authority\":\"Within delegated city legislative powers under Local Government Code (RA 7160); valid public welfare purpose.\",\"drafting_quality\":\"Clear title, operative mandate, and standard severability provisions verified in draft text.\",\"procedural_compliance\":\"Sponsorship verified; committee public hearing and official publication marked as Unverified pending floor calendar.\",\"criteria\":{\"economic\":{\"level\":\"High\",\"reason\":\"Funding realism and cost allocations for \\\"Manila Ordinance No 2026 048\\\" are evidenced as manageable within City Council annual appropriations.\"},\"social\":{\"level\":\"High\",\"reason\":\"Identifies direct community beneficiaries and promotes public welfare across Manila City districts.\"},\"env\":{\"level\":\"High\",\"reason\":\"Maintains positive alignment to sustainable urban governance and ecological standards.\"},\"legal\":{\"level\":\"High\",\"reason\":\"Within delegated municipal power under RA 7160 with no statutory conflicts.\"}}}','Approved','Staff Evaluator','2026-09-19 15:36:45','2026-09-19 21:36:45'),(86,97,1,'Version 1','Admin - Christian','City of Manila','High Risk',8.50,8.50,8.50,8.50,8.50,'','{\"ai_analysis\":\"Evidence-based synthesis of \\\"Manila Ordinance No 2026 051 (checked)\\\" confirms operational feasibility, positive public welfare yield, and solid statutory grounding under RA 7160.\",\"status\":\"Approved\",\"legal_authority\":\"Within delegated city legislative powers under Local Government Code (RA 7160); valid public welfare purpose.\",\"drafting_quality\":\"Clear title, operative mandate, and standard severability provisions verified in draft text.\",\"procedural_compliance\":\"Sponsorship verified; committee public hearing and official publication marked as Unverified pending floor calendar.\",\"criteria\":{\"economic\":{\"level\":\"High\",\"reason\":\"Funding realism and cost allocations for \\\"Manila Ordinance No 2026 051 (checked)\\\" are evidenced as manageable within City Council annual appropriations.\"},\"social\":{\"level\":\"High\",\"reason\":\"Identifies direct community beneficiaries and promotes public welfare across Manila City districts.\"},\"env\":{\"level\":\"High\",\"reason\":\"Maintains positive alignment to sustainable urban governance and ecological standards.\"},\"legal\":{\"level\":\"High\",\"reason\":\"Within delegated municipal power under RA 7160 with no statutory conflicts.\"}}}','Approved','Staff Evaluator','2026-09-19 16:00:45','2026-09-19 22:00:45'),(87,96,1,'Version 1','Admin - Christian','City of Manila','Low Risk',8.50,8.50,8.50,8.50,8.50,'','{\"ai_analysis\":\"Evidence-based synthesis of \\\"Manila Ordinance No 2026 053 (checked)\\\" confirms operational feasibility, positive public welfare yield, and solid statutory grounding under RA 7160.\",\"status\":\"Approved\",\"legal_authority\":\"Within delegated city legislative powers under Local Government Code (RA 7160); valid public welfare purpose.\",\"drafting_quality\":\"Clear title, operative mandate, and standard severability provisions verified in draft text.\",\"procedural_compliance\":\"Sponsorship verified; committee public hearing and official publication marked as Unverified pending floor calendar.\",\"criteria\":{\"economic\":{\"level\":\"High\",\"reason\":\"Funding realism and cost allocations for \\\"Manila Ordinance No 2026 053 (checked)\\\" are evidenced as manageable within City Council annual appropriations.\"},\"social\":{\"level\":\"High\",\"reason\":\"Identifies direct community beneficiaries and promotes public welfare across Manila City districts.\"},\"env\":{\"level\":\"High\",\"reason\":\"Maintains positive alignment to sustainable urban governance and ecological standards.\"},\"legal\":{\"level\":\"High\",\"reason\":\"Within delegated municipal power under RA 7160 with no statutory conflicts.\"}}}','Approved','Staff Evaluator','2026-09-19 16:38:05','2026-09-19 22:38:05'),(88,98,1,'Version 1','Admin - Christian','City of Manila','Low Risk',8.50,8.50,8.50,8.50,8.50,'','{\"ai_analysis\":\"Evidence-based synthesis of \\\"Manila Ordinance No 2026 049\\\" confirms operational feasibility, positive public welfare yield, and solid statutory grounding under RA 7160.\",\"status\":\"Approved\",\"legal_authority\":\"Within delegated city legislative powers under Local Government Code (RA 7160); valid public welfare purpose.\",\"drafting_quality\":\"Clear title, operative mandate, and standard severability provisions verified in draft text.\",\"procedural_compliance\":\"Sponsorship verified; committee public hearing and official publication marked as Unverified pending floor calendar.\",\"criteria\":{\"economic\":{\"level\":\"High\",\"reason\":\"Funding realism and cost allocations for \\\"Manila Ordinance No 2026 049\\\" are evidenced as manageable within City Council annual appropriations.\"},\"social\":{\"level\":\"High\",\"reason\":\"Identifies direct community beneficiaries and promotes public welfare across Manila City districts.\"},\"env\":{\"level\":\"High\",\"reason\":\"Maintains positive alignment to sustainable urban governance and ecological standards.\"},\"legal\":{\"level\":\"High\",\"reason\":\"Within delegated municipal power under RA 7160 with no statutory conflicts.\"}}}','Approved','Staff Evaluator','2026-09-19 16:38:48','2026-09-19 22:38:48'),(89,99,1,'Version 1','Admin - Christian','City of Manila','Low Risk',8.50,8.50,8.50,8.50,8.50,'','{\"ai_analysis\":\"Evidence-based synthesis of \\\"Manila Ordinance No 2026 046\\\" confirms operational feasibility, positive public welfare yield, and solid statutory grounding under RA 7160.\",\"status\":\"Approved\",\"legal_authority\":\"Within delegated city legislative powers under Local Government Code (RA 7160); valid public welfare purpose.\",\"drafting_quality\":\"Clear title, operative mandate, and standard severability provisions verified in draft text.\",\"procedural_compliance\":\"Sponsorship verified; committee public hearing and official publication marked as Unverified pending floor calendar.\",\"criteria\":{\"economic\":{\"level\":\"High\",\"reason\":\"Funding realism and cost allocations for \\\"Manila Ordinance No 2026 046\\\" are evidenced as manageable within City Council annual appropriations.\"},\"social\":{\"level\":\"High\",\"reason\":\"Identifies direct community beneficiaries and promotes public welfare across Manila City districts.\"},\"env\":{\"level\":\"High\",\"reason\":\"Maintains positive alignment to sustainable urban governance and ecological standards.\"},\"legal\":{\"level\":\"High\",\"reason\":\"Within delegated municipal power under RA 7160 with no statutory conflicts.\"}}}','Approved','Staff Evaluator','2026-09-19 16:39:50','2026-09-19 22:39:50'),(90,100,1,'Version 1','Admin - Christian','City of Manila','Low Risk',8.50,8.50,8.50,8.50,8.50,'','{\"ai_analysis\":\"Evidence-based synthesis of \\\"Flood Risk Assessment and Drainage Improvement Plan for Manila City\\\" confirms operational feasibility, positive public welfare yield, and solid statutory grounding under RA 7160.\",\"status\":\"Approved\",\"legal_authority\":\"Within delegated city legislative powers under Local Government Code (RA 7160); valid public welfare purpose.\",\"drafting_quality\":\"Clear title, operative mandate, and standard severability provisions verified in draft text.\",\"procedural_compliance\":\"Sponsorship verified; committee public hearing and official publication marked as Unverified pending floor calendar.\",\"criteria\":{\"economic\":{\"level\":\"High\",\"reason\":\"Funding realism and cost allocations for \\\"Flood Risk Assessment and Drainage Improvement Plan for Manila City\\\" are evidenced as manageable within City Council annual appropriations.\"},\"social\":{\"level\":\"High\",\"reason\":\"Identifies direct community beneficiaries and promotes public welfare across Manila City districts.\"},\"env\":{\"level\":\"High\",\"reason\":\"Maintains positive alignment to sustainable urban governance and ecological standards.\"},\"legal\":{\"level\":\"High\",\"reason\":\"Within delegated municipal power under RA 7160 with no statutory conflicts.\"}}}','Approved','Staff Evaluator','2026-09-19 18:04:22','2026-09-20 00:04:22'),(91,101,1,'Version 1','Admin - Christian','City of Manila','Low Risk',8.50,8.50,8.50,8.50,8.50,'','{\"ai_analysis\":\"Evidence-based synthesis of \\\"Improvement Strategy for Public Health Services in Manila City\\\" confirms operational feasibility, positive public welfare yield, and solid statutory grounding under RA 7160.\",\"status\":\"Approved\",\"legal_authority\":\"Within delegated city legislative powers under Local Government Code (RA 7160); valid public welfare purpose.\",\"drafting_quality\":\"Clear title, operative mandate, and standard severability provisions verified in draft text.\",\"procedural_compliance\":\"Sponsorship verified; committee public hearing and official publication marked as Unverified pending floor calendar.\",\"criteria\":{\"economic\":{\"level\":\"High\",\"reason\":\"Funding realism and cost allocations for \\\"Improvement Strategy for Public Health Services in Manila City\\\" are evidenced as manageable within City Council annual appropriations.\"},\"social\":{\"level\":\"High\",\"reason\":\"Identifies direct community beneficiaries and promotes public welfare across Manila City districts.\"},\"env\":{\"level\":\"High\",\"reason\":\"Maintains positive alignment to sustainable urban governance and ecological standards.\"},\"legal\":{\"level\":\"High\",\"reason\":\"Within delegated municipal power under RA 7160 with no statutory conflicts.\"}}}','Approved','Staff Evaluator','2026-09-19 18:33:45','2026-09-20 00:33:45'),(92,102,1,'Version 1','Admin - Christian','City of Manila','High Risk',4.50,4.50,4.50,4.50,4.50,'','{\"ai_analysis\":\"Evidence-based impact evaluation reveals critical statutory and feasibility failures in \\\"Executive Helicopter Transit and VIP Luxury Fleet Acquisition Act of 2026\\\". The measure does not meet municipal standards across standard evaluation criteria and requires substantial revision or rejection.\",\"status\":\"Needs Revision\",\"legal_authority\":\"Within delegated city legislative powers under Local Government Code (RA 7160); valid public welfare purpose.\",\"drafting_quality\":\"Clear title, operative mandate, and standard severability provisions verified in draft text.\",\"procedural_compliance\":\"Sponsorship verified; committee public hearing and official publication marked as Unverified pending floor calendar.\",\"criteria\":{\"economic\":{\"level\":\"Low\",\"reason\":\"Funding realism failure for \\\"Executive Helicopter Transit and VIP Luxury Fleet Acquisition Act of 2026\\\". Appropriates excessive unquantified allocations without verified revenue mechanisms, creating an unfeasible fiscal burden that exceeds municipal budgetary caps under RA 7160.\"},\"social\":{\"level\":\"High\",\"reason\":\"Identifies direct community beneficiaries and promotes public welfare across Manila City districts.\"},\"env\":{\"level\":\"High\",\"reason\":\"Maintains positive alignment to sustainable urban governance and ecological standards.\"},\"legal\":{\"level\":\"High\",\"reason\":\"Within delegated municipal power under RA 7160 with no statutory conflicts.\"}}}','Needs Revision',NULL,NULL,'2026-09-20 02:14:00');
/*!40000 ALTER TABLE `evaluation_versions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `evaluations`
--

DROP TABLE IF EXISTS `evaluations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `evaluations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `policy_id` int(11) NOT NULL,
  `policy_title` varchar(255) NOT NULL,
  `evaluator` varchar(150) NOT NULL,
  `economic_score` tinyint(4) DEFAULT 0,
  `social_score` tinyint(4) DEFAULT 0,
  `environmental_score` tinyint(4) DEFAULT 0,
  `legal_score` tinyint(4) DEFAULT 0,
  `overall_score` decimal(5,2) DEFAULT 0.00,
  `risk_level` varchar(50) DEFAULT NULL,
  `ai_recommendation` text DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `status` varchar(50) DEFAULT 'Draft',
  `approved_by` varchar(255) DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `policy_id` (`policy_id`),
  CONSTRAINT `fk_evaluations_policy` FOREIGN KEY (`policy_id`) REFERENCES `policy_records` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=154 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `evaluations`
--

LOCK TABLES `evaluations` WRITE;
/*!40000 ALTER TABLE `evaluations` DISABLE KEYS */;
INSERT INTO `evaluations` VALUES (25,47,'National Clean Energy Grid Modernization Act: Economic and Environmental Impact Assessment','Admin',0,0,0,0,8.50,'Low Risk','Approve & Fast-Track Implementation with Enhanced District Resource Allocation','{\"ai_analysis\":\"Updated legislative revision for \\\"National Clean Energy Grid Modernization Act: Economic and Environmental Impact Assessment\\\" incorporates inter-agency operational alignment, optimized fiscal allocations, and strengthened community implementation milestones.\",\"reason\":\"Comprehensive revision verifies positive municipal feasibility, zero legal impediment, and high public impact.\",\"improvements\":[\"Establish bi-monthly district performance monitoring audits\",\"Integrate real-time citizen feedback via Manila City digital portal\",\"Maintain dedicated multi-year capital maintenance reserve fund\"],\"criteria\":{\"economic\":{\"level\":\"Low\",\"reason\":\"Revised fiscal analysis confirms sustainable funding allocations for \\\"National Clean Energy Grid Modernization Act: Economic and Environmental Impact Assessment\\\" across Manila City Hall departmental budgets.\"},\"social\":{\"level\":\"Low\",\"reason\":\"Updated community assessment indicates enhanced public safety, district welfare, and direct constituent service improvements.\"},\"env\":{\"level\":\"Low\",\"reason\":\"Refined ecological assessment satisfies all green urban development and environmental compliance standards.\"},\"legal\":{\"level\":\"Low\",\"reason\":\"Full legal harmony established with the Local Government Code and latest Manila City legislative ordinances.\"}}}','Approved','Quintanaasdsa','2026-09-02 11:10:11','2026-08-11 21:37:40','2026-09-01 19:10:11'),(43,49,'Flood Risk Assessment and Drainage Improvement Plan for Manila City','Admin',0,0,0,0,8.50,'Low Risk','Approve & Fast-Track Implementation with Enhanced District Resource Allocation','{\"ai_analysis\":\"Updated legislative revision for \\\"Flood Risk Assessment and Drainage Improvement Plan for Manila City\\\" incorporates inter-agency operational alignment, optimized fiscal allocations, and strengthened community implementation milestones.\",\"reason\":\"Comprehensive revision verifies positive municipal feasibility, zero legal impediment, and high public impact.\",\"improvements\":[\"Establish bi-monthly district performance monitoring audits\",\"Integrate real-time citizen feedback via Manila City digital portal\",\"Maintain dedicated multi-year capital maintenance reserve fund\"],\"criteria\":{\"economic\":{\"level\":\"Low\",\"reason\":\"Revised fiscal analysis confirms sustainable funding allocations for \\\"Flood Risk Assessment and Drainage Improvement Plan for Manila City\\\" across Manila City Hall departmental budgets.\"},\"social\":{\"level\":\"Low\",\"reason\":\"Updated community assessment indicates enhanced public safety, district welfare, and direct constituent service improvements.\"},\"env\":{\"level\":\"Low\",\"reason\":\"Refined ecological assessment satisfies all green urban development and environmental compliance standards.\"},\"legal\":{\"level\":\"Low\",\"reason\":\"Full legal harmony established with the Local Government Code and latest Manila City legislative ordinances.\"}}}','Approved','Quintanaasdsa','2026-09-02 11:10:06','2026-08-14 23:59:34','2026-09-01 19:10:06'),(45,50,'Urban Traffic Congestion Study in Manila City','Admin',0,0,0,0,8.50,'Low Risk','Approve & Fast-Track Implementation with Enhanced District Resource Allocation','{\"ai_analysis\":\"Updated legislative revision for \\\"Urban Traffic Congestion Study in Manila City\\\" incorporates inter-agency operational alignment, optimized fiscal allocations, and strengthened community implementation milestones.\",\"reason\":\"Comprehensive revision verifies positive municipal feasibility, zero legal impediment, and high public impact.\",\"improvements\":[\"Establish bi-monthly district performance monitoring audits\",\"Integrate real-time citizen feedback via Manila City digital portal\",\"Maintain dedicated multi-year capital maintenance reserve fund\"],\"criteria\":{\"economic\":{\"level\":\"Low\",\"reason\":\"Revised fiscal analysis confirms sustainable funding allocations for \\\"Urban Traffic Congestion Study in Manila City\\\" across Manila City Hall departmental budgets.\"},\"social\":{\"level\":\"Low\",\"reason\":\"Updated community assessment indicates enhanced public safety, district welfare, and direct constituent service improvements.\"},\"env\":{\"level\":\"Low\",\"reason\":\"Refined ecological assessment satisfies all green urban development and environmental compliance standards.\"},\"legal\":{\"level\":\"Low\",\"reason\":\"Full legal harmony established with the Local Government Code and latest Manila City legislative ordinances.\"}}}','Approved','Quintanaasdsa','2026-09-01 23:58:41','2026-08-19 07:56:56','2026-09-01 07:58:41'),(58,60,'Improvement Strategy for Public Health Services in Manila City','Admin',0,0,0,0,8.50,'Low Risk','Approve & Proceed to Full Implementation','{\"ai_analysis\":\"Comprehensive evaluation of \\\"Improvement Strategy - Public Health & Wellness Action Plan\\\" indicates high operational viability, low public implementation risk, and strong strategic alignment with Manila City Hall legislative objectives.\",\"reason\":\"Detailed assessment demonstrates minimal public risk and high long-term community benefits.\",\"improvements\":[\"Establish quarterly district performance monitoring reviews\",\"Deploy digital asset management dashboards across participating departments\",\"Conduct community feedback surveys after 6 months of ordinance rollout\"],\"criteria\":{\"economic\":{\"level\":\"Low\",\"reason\":\"Funding and budget allocations for \\\"Improvement Strategy - Public Health & Wellness Action Plan\\\" are manageable within Manila City Hall fiscal programs.\"},\"social\":{\"level\":\"Low\",\"reason\":\"Enhances public welfare, community health, and district safety across Manila City.\"},\"env\":{\"level\":\"Low\",\"reason\":\"Minimal ecological footprint with positive sustainable urban development alignment.\"},\"legal\":{\"level\":\"Low\",\"reason\":\"Fully compliant with existing national legislative frameworks and Manila City Ordinances.\"}}}','Approved','Quintanaasdsa','2026-09-01 23:51:15','2026-08-30 01:44:11','2026-09-01 19:52:14'),(65,61,'Public Transportation Efficiency Improvement Plan for Manila City','A.I. Evaluator',0,0,0,0,8.50,'Low','Suitable for implementation.',NULL,'Approved','Quintanaasdsa','2026-09-01 23:45:13','2026-08-31 19:40:14','2026-09-01 16:51:30'),(66,59,'Community Safety - Comprehensive Social Welfare & Community Support Initiative','Admin',0,0,0,0,8.50,'Low Risk','Approve & Fast-Track Implementation with Enhanced District Resource Allocation','{\"ai_analysis\":\"Updated legislative revision for \\\"Community Safety - Comprehensive Social Welfare & Community Support Initiative\\\" incorporates inter-agency operational alignment, optimized fiscal allocations, and strengthened community implementation milestones.\",\"reason\":\"Comprehensive revision verifies positive municipal feasibility, zero legal impediment, and high public impact.\",\"improvements\":[\"Establish bi-monthly district performance monitoring audits\",\"Integrate real-time citizen feedback via Manila City digital portal\",\"Maintain dedicated multi-year capital maintenance reserve fund\"],\"criteria\":{\"economic\":{\"level\":\"Low\",\"reason\":\"Revised fiscal analysis confirms sustainable funding allocations for \\\"Community Safety - Comprehensive Social Welfare & Community Support Initiative\\\" across Manila City Hall departmental budgets.\"},\"social\":{\"level\":\"Low\",\"reason\":\"Updated community assessment indicates enhanced public safety, district welfare, and direct constituent service improvements.\"},\"env\":{\"level\":\"Low\",\"reason\":\"Refined ecological assessment satisfies all green urban development and environmental compliance standards.\"},\"legal\":{\"level\":\"Low\",\"reason\":\"Full legal harmony established with the Local Government Code and latest Manila City legislative ordinances.\"}}}','Approved','Quintanaasdsa','2026-09-01 23:51:26','2026-08-31 19:58:55','2026-09-01 07:51:26'),(83,62,'QC Ordinance No. SP-2876: Comprehensive Single-Use Plastic Regulation & Recovery Framework','Admin',0,0,0,0,8.80,'Low Risk','Highly recommended benchmark model. Provides strong regulatory precedent for Manila to strengthen enforcement mechanisms on commercial plastic bag fees and solid waste recovery.','{\"ai_analysis\":\"Quezon City successfully reduced commercial single-use bag usage by over 60% within 18 months through phased commercial merchant compliance and barangay eco-hubs.\",\"reason\":\"Clear fee-collection structure directly funding municipal green initiatives.\",\"criteria\":{\"economic\":{\"level\":\"Low\",\"reason\":\"Generates self-sustaining environmental recovery funds collected directly from commercial retail chains.\"},\"social\":{\"level\":\"Low\",\"reason\":\"High public adoption rate through extensive barangay information campaigns and merchant bag-swap programs.\"},\"env\":{\"level\":\"Low\",\"reason\":\"Direct reduction of plastic blockage in municipal waterways and pumping stations.\"},\"legal\":{\"level\":\"Low\",\"reason\":\"Fully aligned with Republic Act 9003 (Ecological Solid Waste Management Act) and DILG guidelines.\"}}}','Approved','Quintanaasdsa','2026-09-01 23:46:59','2026-08-31 20:34:25','2026-09-01 07:46:59'),(84,63,'QC Ordinance No. SP-2350: Quezon City Green Building & Energy Efficiency Code','Admin',0,0,0,0,8.60,'Low Risk','Excellent legislative reference for Manila City Hall urban renewal projects, especially for integrating green roof and flood-resilient rainwater capture into building permit approvals.','{\"ai_analysis\":\"Demonstrates effective local integration of national building code with green infrastructure incentives and tax rebate mechanisms.\",\"reason\":\"Property developers granted real property tax discounts upon achieving certified green performance ratings.\",\"criteria\":{\"economic\":{\"level\":\"Medium\",\"reason\":\"Initial developer compliance costs offset by long-term municipal energy savings and commercial property tax incentives.\"},\"social\":{\"level\":\"Low\",\"reason\":\"Improves urban air quality, reduces building thermal heat islands, and enhances residential safety.\"},\"env\":{\"level\":\"Low\",\"reason\":\"Significantly curbs municipal carbon emissions and promotes decentralized stormwater retention.\"},\"legal\":{\"level\":\"Low\",\"reason\":\"Conforms to the Philippine Green Building Code (PD 1096) and DPWH standards.\"}}}','Approved','Quintanaasdsa','2026-09-01 23:47:07','2026-08-31 20:34:25','2026-09-01 07:47:07'),(85,64,'Pasig City Ordinance No. 12: People-Centric Mobility & Protected Bike Lane Network System','Admin',0,0,0,0,8.50,'Low Risk','Approve & Fast-Track Implementation with Enhanced District Resource Allocation','{\"ai_analysis\":\"Updated legislative revision for \\\"Pasig City Ordinance No. 12: People-Centric Mobility & Protected Bike Lane Network System\\\" incorporates inter-agency operational alignment, optimized fiscal allocations, and strengthened community implementation milestones.\",\"reason\":\"Comprehensive revision verifies positive municipal feasibility, zero legal impediment, and high public impact.\",\"improvements\":[\"Establish bi-monthly district performance monitoring audits\",\"Integrate real-time citizen feedback via Manila City digital portal\",\"Maintain dedicated multi-year capital maintenance reserve fund\"],\"criteria\":{\"economic\":{\"level\":\"Low\",\"reason\":\"Revised fiscal analysis confirms sustainable funding allocations for \\\"Pasig City Ordinance No. 12: People-Centric Mobility & Protected Bike Lane Network System\\\" across Manila City Hall departmental budgets.\"},\"social\":{\"level\":\"Low\",\"reason\":\"Updated community assessment indicates enhanced public safety, district welfare, and direct constituent service improvements.\"},\"env\":{\"level\":\"Low\",\"reason\":\"Refined ecological assessment satisfies all green urban development and environmental compliance standards.\"},\"legal\":{\"level\":\"Low\",\"reason\":\"Full legal harmony established with the Local Government Code and latest Manila City legislative ordinances.\"}}}','Approved','Quintanaasdsa','2026-09-01 23:47:33','2026-08-31 20:34:25','2026-09-01 07:47:33'),(117,75,'Public Transportation Efficiency Improvement Plan for Manila City','Admin',0,0,0,0,8.50,'Low Risk','Approve & Proceed to Full Implementation','{\"ai_analysis\":\"Comprehensive evaluation of \\\"Public Transportation Efficiency Improvement Plan for Manila City\\\" indicates high operational viability, low public implementation risk, and strong strategic alignment with Manila City Hall legislative objectives.\",\"reason\":\"Detailed assessment demonstrates minimal public risk and high long-term community benefits.\",\"improvements\":[\"Establish quarterly district performance monitoring reviews\",\"Deploy digital asset management dashboards across participating departments\",\"Conduct community feedback surveys after 6 months of ordinance rollout\"],\"criteria\":{\"economic\":{\"level\":\"Low\",\"reason\":\"Funding and budget allocations for \\\"Public Transportation Efficiency Improvement Plan for Manila City\\\" are manageable within Manila City Hall fiscal programs.\"},\"social\":{\"level\":\"Low\",\"reason\":\"Enhances public welfare, community health, and district safety across Manila City.\"},\"env\":{\"level\":\"Low\",\"reason\":\"Minimal ecological footprint with positive sustainable urban development alignment.\"},\"legal\":{\"level\":\"Low\",\"reason\":\"Fully compliant with existing national legislative frameworks and Manila City Ordinances.\"}}}','Approved','Quintanaasdsa','2026-09-02 18:28:39','2026-09-01 19:12:31','2026-09-02 02:28:39'),(118,76,'Improvement Strategy for Public Health Services in Manila City','Admin',0,0,0,0,8.50,'Low Risk','Approve & Proceed to Full Implementation','{\"ai_analysis\":\"Comprehensive evaluation of \\\"Improvement Strategy\\\" indicates high operational viability, low public implementation risk, and strong strategic alignment with Manila City Hall legislative objectives.\",\"reason\":\"Detailed assessment demonstrates minimal public risk and high long-term community benefits.\",\"improvements\":[\"Establish quarterly district performance monitoring reviews\",\"Deploy digital asset management dashboards across participating departments\",\"Conduct community feedback surveys after 6 months of ordinance rollout\"],\"criteria\":{\"economic\":{\"level\":\"Low\",\"reason\":\"Funding and budget allocations for \\\"Improvement Strategy\\\" are manageable within Manila City Hall fiscal programs.\"},\"social\":{\"level\":\"Low\",\"reason\":\"Enhances public welfare, community health, and district safety across Manila City.\"},\"env\":{\"level\":\"Low\",\"reason\":\"Minimal ecological footprint with positive sustainable urban development alignment.\"},\"legal\":{\"level\":\"Low\",\"reason\":\"Fully compliant with existing national legislative frameworks and Manila City Ordinances.\"}}}','Approved','Quintanaasdsa','2026-09-02 14:29:06','2026-09-01 19:17:16','2026-09-01 22:29:06'),(120,77,'Community Safety and Crime Prevention Strategy for Manila City','Admin',0,0,0,0,8.50,'Low Risk','Approve & Fast-Track Implementation with Enhanced District Resource Allocation','{\"ai_analysis\":\"Updated legislative revision for \\\"Community Safety and Crime Prevention Strategy for Manila City\\\" incorporates inter-agency operational alignment, optimized fiscal allocations, and strengthened community implementation milestones.\",\"reason\":\"Comprehensive revision verifies positive municipal feasibility, zero legal impediment, and high public impact.\",\"improvements\":[\"Establish bi-monthly district performance monitoring audits\",\"Integrate real-time citizen feedback via Manila City digital portal\",\"Maintain dedicated multi-year capital maintenance reserve fund\"],\"criteria\":{\"economic\":{\"level\":\"Low\",\"reason\":\"Revised fiscal analysis confirms sustainable funding allocations for \\\"Community Safety and Crime Prevention Strategy for Manila City\\\" across Manila City Hall departmental budgets.\"},\"social\":{\"level\":\"Low\",\"reason\":\"Updated community assessment indicates enhanced public safety, district welfare, and direct constituent service improvements.\"},\"env\":{\"level\":\"Low\",\"reason\":\"Refined ecological assessment satisfies all green urban development and environmental compliance standards.\"},\"legal\":{\"level\":\"Low\",\"reason\":\"Full legal harmony established with the Local Government Code and latest Manila City legislative ordinances.\"}}}','Completed',NULL,NULL,'2026-09-02 02:00:55','2026-09-02 02:05:47'),(147,97,'Manila Ordinance No 2026 051 (checked)','Admin - Christian',0,0,0,0,8.50,'Low Risk','Evidence-based synthesis of \"Manila Ordinance No 2026 051 (checked)\" confirms operational feasibility, positive public welfare yield, and solid statutory grounding under RA 7160.','{\"ai_analysis\":\"Evidence-based synthesis of \\\"Manila Ordinance No 2026 051 (checked)\\\" confirms operational feasibility, positive public welfare yield, and solid statutory grounding under RA 7160.\",\"status\":\"Approved\",\"legal_authority\":\"Within delegated city legislative powers under Local Government Code (RA 7160); valid public welfare purpose.\",\"drafting_quality\":\"Clear title, operative mandate, and standard severability provisions verified in draft text.\",\"procedural_compliance\":\"Sponsorship verified; committee public hearing and official publication marked as Unverified pending floor calendar.\",\"criteria\":{\"economic\":{\"level\":\"High\",\"reason\":\"Funding realism and cost allocations for \\\"Manila Ordinance No 2026 051 (checked)\\\" are evidenced as manageable within City Council annual appropriations.\"},\"social\":{\"level\":\"High\",\"reason\":\"Identifies direct community beneficiaries and promotes public welfare across Manila City districts.\"},\"env\":{\"level\":\"High\",\"reason\":\"Maintains positive alignment to sustainable urban governance and ecological standards.\"},\"legal\":{\"level\":\"High\",\"reason\":\"Within delegated municipal power under RA 7160 with no statutory conflicts.\"}}}','Approved',NULL,NULL,'2026-09-19 14:00:45','2026-09-19 14:11:43'),(148,96,'Manila Ordinance No 2026 053 (checked)','Admin - Christian',0,0,0,0,8.50,'Low Risk','Evidence-based synthesis of \"Manila Ordinance No 2026 053 (checked)\" confirms operational feasibility, positive public welfare yield, and solid statutory grounding under RA 7160.','{\"ai_analysis\":\"Evidence-based synthesis of \\\"Manila Ordinance No 2026 053 (checked)\\\" confirms operational feasibility, positive public welfare yield, and solid statutory grounding under RA 7160.\",\"status\":\"Approved\",\"legal_authority\":\"Within delegated city legislative powers under Local Government Code (RA 7160); valid public welfare purpose.\",\"drafting_quality\":\"Clear title, operative mandate, and standard severability provisions verified in draft text.\",\"procedural_compliance\":\"Sponsorship verified; committee public hearing and official publication marked as Unverified pending floor calendar.\",\"criteria\":{\"economic\":{\"level\":\"High\",\"reason\":\"Funding realism and cost allocations for \\\"Manila Ordinance No 2026 053 (checked)\\\" are evidenced as manageable within City Council annual appropriations.\"},\"social\":{\"level\":\"High\",\"reason\":\"Identifies direct community beneficiaries and promotes public welfare across Manila City districts.\"},\"env\":{\"level\":\"High\",\"reason\":\"Maintains positive alignment to sustainable urban governance and ecological standards.\"},\"legal\":{\"level\":\"High\",\"reason\":\"Within delegated municipal power under RA 7160 with no statutory conflicts.\"}}}','Approved',NULL,NULL,'2026-09-19 14:38:05','2026-09-19 14:38:05'),(149,98,'Manila Ordinance No 2026 049','Admin - Christian',0,0,0,0,8.50,'Low Risk','Evidence-based synthesis of \"Manila Ordinance No 2026 049\" confirms operational feasibility, positive public welfare yield, and solid statutory grounding under RA 7160.','{\"ai_analysis\":\"Evidence-based synthesis of \\\"Manila Ordinance No 2026 049\\\" confirms operational feasibility, positive public welfare yield, and solid statutory grounding under RA 7160.\",\"status\":\"Approved\",\"legal_authority\":\"Within delegated city legislative powers under Local Government Code (RA 7160); valid public welfare purpose.\",\"drafting_quality\":\"Clear title, operative mandate, and standard severability provisions verified in draft text.\",\"procedural_compliance\":\"Sponsorship verified; committee public hearing and official publication marked as Unverified pending floor calendar.\",\"criteria\":{\"economic\":{\"level\":\"High\",\"reason\":\"Funding realism and cost allocations for \\\"Manila Ordinance No 2026 049\\\" are evidenced as manageable within City Council annual appropriations.\"},\"social\":{\"level\":\"High\",\"reason\":\"Identifies direct community beneficiaries and promotes public welfare across Manila City districts.\"},\"env\":{\"level\":\"High\",\"reason\":\"Maintains positive alignment to sustainable urban governance and ecological standards.\"},\"legal\":{\"level\":\"High\",\"reason\":\"Within delegated municipal power under RA 7160 with no statutory conflicts.\"}}}','Approved',NULL,NULL,'2026-09-19 14:38:47','2026-09-19 14:38:47'),(150,99,'Manila Ordinance No 2026 046','Admin - Christian',0,0,0,0,8.50,'Low Risk','Evidence-based synthesis of \"Manila Ordinance No 2026 046\" confirms operational feasibility, positive public welfare yield, and solid statutory grounding under RA 7160.','{\"ai_analysis\":\"Evidence-based synthesis of \\\"Manila Ordinance No 2026 046\\\" confirms operational feasibility, positive public welfare yield, and solid statutory grounding under RA 7160.\",\"status\":\"Approved\",\"legal_authority\":\"Within delegated city legislative powers under Local Government Code (RA 7160); valid public welfare purpose.\",\"drafting_quality\":\"Clear title, operative mandate, and standard severability provisions verified in draft text.\",\"procedural_compliance\":\"Sponsorship verified; committee public hearing and official publication marked as Unverified pending floor calendar.\",\"criteria\":{\"economic\":{\"level\":\"High\",\"reason\":\"Funding realism and cost allocations for \\\"Manila Ordinance No 2026 046\\\" are evidenced as manageable within City Council annual appropriations.\"},\"social\":{\"level\":\"High\",\"reason\":\"Identifies direct community beneficiaries and promotes public welfare across Manila City districts.\"},\"env\":{\"level\":\"High\",\"reason\":\"Maintains positive alignment to sustainable urban governance and ecological standards.\"},\"legal\":{\"level\":\"High\",\"reason\":\"Within delegated municipal power under RA 7160 with no statutory conflicts.\"}}}','Approved',NULL,NULL,'2026-09-19 14:39:49','2026-09-19 14:39:49'),(151,100,'Flood Risk Assessment and Drainage Improvement Plan for Manila City','Admin - Christian',0,0,0,0,8.50,'Low Risk','Evidence-based synthesis of \"Flood Risk Assessment and Drainage Improvement Plan for Manila City\" confirms operational feasibility, positive public welfare yield, and solid statutory grounding under RA 7160.','{\"ai_analysis\":\"Evidence-based synthesis of \\\"Flood Risk Assessment and Drainage Improvement Plan for Manila City\\\" confirms operational feasibility, positive public welfare yield, and solid statutory grounding under RA 7160.\",\"status\":\"Approved\",\"legal_authority\":\"Within delegated city legislative powers under Local Government Code (RA 7160); valid public welfare purpose.\",\"drafting_quality\":\"Clear title, operative mandate, and standard severability provisions verified in draft text.\",\"procedural_compliance\":\"Sponsorship verified; committee public hearing and official publication marked as Unverified pending floor calendar.\",\"criteria\":{\"economic\":{\"level\":\"High\",\"reason\":\"Funding realism and cost allocations for \\\"Flood Risk Assessment and Drainage Improvement Plan for Manila City\\\" are evidenced as manageable within City Council annual appropriations.\"},\"social\":{\"level\":\"High\",\"reason\":\"Identifies direct community beneficiaries and promotes public welfare across Manila City districts.\"},\"env\":{\"level\":\"High\",\"reason\":\"Maintains positive alignment to sustainable urban governance and ecological standards.\"},\"legal\":{\"level\":\"High\",\"reason\":\"Within delegated municipal power under RA 7160 with no statutory conflicts.\"}}}','Approved',NULL,NULL,'2026-09-19 16:04:22','2026-09-19 16:04:22'),(152,101,'Improvement Strategy for Public Health Services in Manila City','Admin - Christian',0,0,0,0,8.50,'Low Risk','Evidence-based synthesis of \"Improvement Strategy for Public Health Services in Manila City\" confirms operational feasibility, positive public welfare yield, and solid statutory grounding under RA 7160.','{\"ai_analysis\":\"Evidence-based synthesis of \\\"Improvement Strategy for Public Health Services in Manila City\\\" confirms operational feasibility, positive public welfare yield, and solid statutory grounding under RA 7160.\",\"status\":\"Approved\",\"legal_authority\":\"Within delegated city legislative powers under Local Government Code (RA 7160); valid public welfare purpose.\",\"drafting_quality\":\"Clear title, operative mandate, and standard severability provisions verified in draft text.\",\"procedural_compliance\":\"Sponsorship verified; committee public hearing and official publication marked as Unverified pending floor calendar.\",\"criteria\":{\"economic\":{\"level\":\"High\",\"reason\":\"Funding realism and cost allocations for \\\"Improvement Strategy for Public Health Services in Manila City\\\" are evidenced as manageable within City Council annual appropriations.\"},\"social\":{\"level\":\"High\",\"reason\":\"Identifies direct community beneficiaries and promotes public welfare across Manila City districts.\"},\"env\":{\"level\":\"High\",\"reason\":\"Maintains positive alignment to sustainable urban governance and ecological standards.\"},\"legal\":{\"level\":\"High\",\"reason\":\"Within delegated municipal power under RA 7160 with no statutory conflicts.\"}}}','Approved',NULL,NULL,'2026-09-19 16:33:45','2026-09-19 16:33:45'),(153,102,'Executive Helicopter Transit and VIP Luxury Fleet Acquisition Act of 2026','Admin - Christian',0,0,0,0,4.50,'High Risk','Evidence-based impact evaluation reveals critical statutory and feasibility failures in \"Executive Helicopter Transit and VIP Luxury Fleet Acquisition Act of 2026\". The measure does not meet municipal standards across standard evaluation criteria and requires substantial revision or rejection.','{\"ai_analysis\":\"Evidence-based impact evaluation reveals critical statutory and feasibility failures in \\\"Executive Helicopter Transit and VIP Luxury Fleet Acquisition Act of 2026\\\". The measure does not meet municipal standards across standard evaluation criteria and requires substantial revision or rejection.\",\"status\":\"Needs Revision\",\"legal_authority\":\"Within delegated city legislative powers under Local Government Code (RA 7160); valid public welfare purpose.\",\"drafting_quality\":\"Clear title, operative mandate, and standard severability provisions verified in draft text.\",\"procedural_compliance\":\"Sponsorship verified; committee public hearing and official publication marked as Unverified pending floor calendar.\",\"criteria\":{\"economic\":{\"level\":\"Low\",\"reason\":\"Funding realism failure for \\\"Executive Helicopter Transit and VIP Luxury Fleet Acquisition Act of 2026\\\". Appropriates excessive unquantified allocations without verified revenue mechanisms, creating an unfeasible fiscal burden that exceeds municipal budgetary caps under RA 7160.\"},\"social\":{\"level\":\"High\",\"reason\":\"Identifies direct community beneficiaries and promotes public welfare across Manila City districts.\"},\"env\":{\"level\":\"High\",\"reason\":\"Maintains positive alignment to sustainable urban governance and ecological standards.\"},\"legal\":{\"level\":\"High\",\"reason\":\"Within delegated municipal power under RA 7160 with no statutory conflicts.\"}}}','Needs Revision',NULL,NULL,'2026-09-19 18:14:00','2026-09-19 18:14:00');
/*!40000 ALTER TABLE `evaluations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `external_ordinances`
--

DROP TABLE IF EXISTS `external_ordinances`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `external_ordinances` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `city_name` varchar(100) NOT NULL,
  `ordinance_number` varchar(100) NOT NULL,
  `ordinance_title` varchar(255) NOT NULL,
  `policy_area` varchar(100) NOT NULL,
  `key_provisions` text NOT NULL,
  `enactment_date` date DEFAULT NULL,
  `source_link` varchar(500) DEFAULT NULL,
  `economic_level` varchar(20) DEFAULT 'Low',
  `economic_reason` text DEFAULT NULL,
  `social_level` varchar(20) DEFAULT 'Low',
  `social_reason` text DEFAULT NULL,
  `env_level` varchar(20) DEFAULT 'Low',
  `env_reason` text DEFAULT NULL,
  `legal_level` varchar(20) DEFAULT 'Low',
  `legal_reason` text DEFAULT NULL,
  `risk_level` varchar(50) DEFAULT 'Low Risk',
  `overall_score` decimal(4,2) DEFAULT 8.80,
  `economic_score` decimal(4,2) DEFAULT 8.50,
  `social_score` decimal(4,2) DEFAULT 9.00,
  `env_score` decimal(4,2) DEFAULT 9.20,
  `legal_score` decimal(4,2) DEFAULT 9.00,
  `ai_recommendation` text DEFAULT NULL,
  `benchmark_insight` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_ext_city` (`city_name`),
  KEY `idx_ext_policy_area` (`policy_area`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `external_ordinances`
--

LOCK TABLES `external_ordinances` WRITE;
/*!40000 ALTER TABLE `external_ordinances` DISABLE KEYS */;
INSERT INTO `external_ordinances` VALUES (1,'Quezon City','Ordinance No. SP-2876, S-2019','Comprehensive Single-Use Plastics & Disposable Materials Regulation Framework','Environmental Protection & Waste Management','• Total prohibition on single-use plastic bags, plastic cutlery, straws, and styrofoam containers in all shopping malls, supermarkets, hotels, and restaurants.\n• Imposition of a mandatory \'Plastic Recovery System Fee\' deposited into a dedicated municipal Green Fund managed by EPWMD.\n• Mandatory establishment of Barangay Ecological Centers and collection swap hubs for recyclable plastics.\n• Tiered administrative fines: ₱1,000 (1st offense), ₱3,000 (2nd offense), and ₱5,000 with revocation of Business Permit (3rd offense).','2019-10-15','https://quezoncity.gov.ph/departments/epwmd/ordinance-sp-2876-s-2019/','Low','Plastic recovery fees directly create a self-financing trust fund for barangay waste infrastructure without draining general council appropriations.','Low','High community adoption achieved via phased business compliance grace periods and city-wide informational barangay roadshows.','Low','Achieved documented 60% reduction in single-use plastic waterway blockage across San Juan River drainage networks within 18 months.','Low','Strictly aligned with the Ecological Solid Waste Management Act (RA 9003) and DILG municipal waste reduction circulars.','0',9.10,8.80,9.00,9.50,9.10,'Endorse adoption of QC\'s dedicated Plastic Recovery Trust Fund mechanism into the Manila Environment Code to fund local barangay MRFs.','Manila\'s draft plastic regulation relies on general budget allocations; adopting Quezon City\'s dedicated recovery fee model creates a self-sustaining funding mechanism for district material recovery facilities.','2026-09-19 06:50:22','2026-09-19 06:50:22'),(2,'City of Makati','City Ordinance No. 2003-095','The Makati Solid Waste Management Code & Ecological Recovery System','Solid Waste Management & Ecological Sanitation','• Mandatory waste segregation at source (biodegradable, non-biodegradable, recyclable, and hazardous) for all residential and commercial premises.\n• Color-coded container standards required for all commercial buildings and high-density subdivisions.\n• Scheduled door-to-door barangay collection protocols enforcing strict \'no segregation, no collection\' policy.\n• On-the-spot administrative citation authority empowered for Department of Environmental Services (DES) inspectors.','2003-12-16','https://makati.gov.ph/residents/services/environmental-management','Low','Collection routing optimizations and commercial compliance tickets generate municipal cost recovery for disposal contracts.','Low','Established widespread civic discipline through color-coded bin standards across all 33 Makati barangays.','Low','Substantially diverts solid waste from transfer stations, extending regional sanitary landfill lifespans.','Low','Solidly anchored in RA 9003 and Section 16 of the Local Government Code of 1991 (General Welfare Clause).','0',8.90,8.70,8.80,9.30,8.80,'Incorporate Makati\'s on-the-spot administrative citation ticket system to enhance DPS enforcement across Manila\'s high-density market zones.','Makati demonstrates that combining mandatory color-coded commercial containment with immediate administrative citation powers yields significantly higher compliance than delayed court summons.','2026-09-19 06:50:22','2026-09-19 06:50:22'),(3,'Quezon City','Ordinance No. SP-2350, S-2014','Quezon City Green Building Ordinance & Energy Efficiency Code','Green Building & Clean Energy Infrastructure','• Mandatory green building design and operational standards for all new building constructions with Gross Floor Area (GFA) >= 1,000 sqm.\n• Minimum 10% solar PV-ready rooftop infrastructure and mandatory rainwater harvesting collection cisterns for landscape and toilet flushing.\n• Window-to-Wall Ratio (WWR) thresholds to reduce air-conditioning thermal loads and urban heat island effects.\n• Incentive scheme: Real Property Tax (RPT) discount of up to 25% on improvements for projects achieving certified high-tier green ratings.','2014-11-24','https://quezoncity.gov.ph/ordinances/sp-2350-s-2014-green-building-code/','Medium','Upfront private development engineering costs are balanced by long-term municipal energy savings and attractive commercial RPT rebates.','Low','Significantly improves indoor air quality, thermal comfort, and localized disaster preparedness during utility outages.','Low','Cuts commercial building greenhouse gas emissions and conserves treated municipal potable water through mandatory rainwater capture.','Low','Harmonized with the Philippine National Building Code (PD 1096) and the Philippine Green Building Code of 2015.','0',8.95,8.40,8.90,9.50,9.00,'Formulate an amendment to the Manila City Revenue Code offering graduated RPT discounts for green-certified commercial developers in Binondo and Malate.','Quezon City effectively combines regulatory construction mandates with real property tax discounts, creating private sector buy-in that Manila could replicate for urban renewal initiatives.','2026-09-19 06:50:22','2026-09-19 06:50:22'),(4,'City of Makati','City Ordinance No. 2019-094','Makati Smart Automated Traffic Monitoring & Congestion Mitigation Code','Urban Mobility & Traffic Management','• Deployment of high-definition AI camera networks linked to a 24/7 central traffic command center for real-time corridor monitoring.\n• Automated Non-Contact Apprehension System (NCAP) capturing arterial lane obstructions, illegal loading, and red-light violations.\n• Peak-hour lane segregation for public utility jeepneys (PUJs) and city buses along Ayala Ave, Buendia, and Makati CBD access corridors.\n• Integration of adaptive smart traffic light controllers adjusting green phase intervals dynamically based on vehicular queue lengths.','2019-10-23','https://makati.gov.ph/traffic-management-and-safety','Low','Automated fine collection and reduced commuter delay hours yield measurable economic productivity gains across the city.','Low','Reduces peak transit commute times by 22% along CBD corridors and minimizes roadside traffic officer physical disputes.','Low','Decreases vehicular idling emissions and localized particulate pollution along arterial transit bottlenecks.','Low','Enacted under municipal traffic regulation powers under RA 7160 and MMDA traffic management guidelines.','0',8.85,8.60,8.80,8.90,9.10,'Prioritize smart synchronized signal controllers along Taft Avenue and España Boulevard, mirroring Makati\'s automated corridor management.','Makati\'s automated traffic management demonstrates that synchronized smart signaling combined with automated lane enforcement mitigates arterial gridlock far more sustainably than manual deployment.','2026-09-19 06:50:22','2026-09-19 06:50:22'),(5,'Pasig City','Ordinance No. 12, Series of 2019','Pasig People-Centric Mobility & Protected Active Transport Network Ordinance','Active Mobility & Protected Transport Networks','• Mandates physical segregation (bollards, concrete curbing, or planter barriers) for bicycle and active mobility corridors citywide.\n• Enforces minimum 2.0-meter unobstructed sidewalk clearance for pedestrians along commercial and transport terminal access roads.\n• Guaranteed annual municipal budget earmark: at least 2% of the city\'s local infrastructure capital fund allocated exclusively to active mobility.\n• Mandatory secure bicycle parking facilities, locker rooms, and repair stations in all new commercial developments.','2019-06-20','https://pasigcity.gov.ph/ordinances/ordinance-no-12-s-2019-active-mobility','Low','Statutory 2% infrastructure budget earmark guarantees sustainable maintenance without ad-hoc emergency fund reallocations.','Low','Democratizes road space for daily commuters, students, and low-income workers; reduces cyclist roadside fatalities by over 40%.','Low','Promotes zero-emission modal shifting, directly lowering municipal carbon footprints and urban noise pollution.','Low','Fully aligned with DOTr Active Transport Department Orders and the National Sustainable Transport Strategy.','0',9.05,8.70,9.40,9.30,8.80,'Adopt Pasig\'s permanent physical bollard segregation standards along Roxas Boulevard and university belt pedestrian corridors in Manila.','Pasig City\'s statutory budget earmark (2% of infra funds) ensures active transport infrastructure remains protected and maintained, resolving a key vulnerability in Manila\'s current bike lane planning.','2026-09-19 06:50:22','2026-09-19 06:50:22'),(6,'Quezon City','Ordinance No. SP-3032, S-2021','Quezon City Drainage Master Plan & Rainwater Catchment Basin Mandate','Disaster Resilience & Drainage Infrastructure','• Mandates all new commercial, industrial, and institutional developments exceeding 1,500 sqm to construct on-site rainwater detention/retention basins (minimum 50 liters per sqm of GFA).\n• Multi-year phased municipal drainage desiltation and widening schedule prioritized across critical river tributaries (San Juan, Tullahan).\n• Real-time ultrasonic water level sensors deployed across 50+ flood-prone bridges and low-lying barangays, connected directly to the QC Disaster Risk Reduction Center.\n• Mandatory integration of permeable paving materials in open parking lots to enhance natural groundwater recharge.','2021-07-28','https://quezoncity.gov.ph/disaster-risk-reduction/drainage-masterplan-sp-3032/','Low','Private on-site rainwater detention basins reduce public capital expenditures needed for mega pumping station expansions.','Low','Protects vulnerable low-lying barangays from flash floods and connects early warning sirens directly to neighborhood leaders.','Low','Attenuates peak stormwater surge runoff, preventing riverbank scouring and recharging local aquifer tables.','Low','Fully conforms with the Philippine Disaster Risk Reduction and Management Act (RA 10121) and the Water Code (PD 1067).','0',9.15,8.80,9.20,9.60,9.00,'Draft a Manila City Ordinance mandating on-site subsurface stormwater detention cisterns for all new commercial developments over 1,500 sqm in Sampaloc and Santa Mesa.','Manila currently concentrates flood control expenditures almost exclusively on pumping stations and dredging; adopting QC\'s mandatory private rainwater retention basins would reduce peak canal stormwater overload by up to 35%.','2026-09-19 06:50:22','2026-09-19 06:50:22');
/*!40000 ALTER TABLE `external_ordinances` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `feedback`
--

DROP TABLE IF EXISTS `feedback`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `feedback` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `category_id` (`category_id`),
  KEY `fk_feedback_hearing` (`hearing_id`),
  KEY `fk_feedback_legislative_item` (`legislative_item_id`),
  KEY `fk_feedback_stakeholder` (`stakeholder_id`),
  KEY `fk_feedback_user` (`user_id`),
  KEY `fk_feedback_validated_by` (`validated_by`),
  CONSTRAINT `feedback_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `feedback_categories` (`id`),
  CONSTRAINT `fk_feedback_hearing` FOREIGN KEY (`hearing_id`) REFERENCES `hearings` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_feedback_legislative_item` FOREIGN KEY (`legislative_item_id`) REFERENCES `legislative_items` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_feedback_stakeholder` FOREIGN KEY (`stakeholder_id`) REFERENCES `stakeholders` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_feedback_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_feedback_validated_by` FOREIGN KEY (`validated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `feedback`
--

LOCK TABLES `feedback` WRITE;
/*!40000 ALTER TABLE `feedback` DISABLE KEYS */;
/*!40000 ALTER TABLE `feedback` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `feedback_categories`
--

DROP TABLE IF EXISTS `feedback_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `feedback_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `feedback_categories`
--

LOCK TABLES `feedback_categories` WRITE;
/*!40000 ALTER TABLE `feedback_categories` DISABLE KEYS */;
/*!40000 ALTER TABLE `feedback_categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `hearing_action_assignments`
--

DROP TABLE IF EXISTS `hearing_action_assignments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `hearing_action_assignments` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `action_id` int(11) NOT NULL,
  `assigned_office_id` int(11) DEFAULT NULL,
  `assigned_user_id` int(11) DEFAULT NULL,
  `assigned_by` int(11) DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `assigned_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_hearing_action_assignments_action` (`action_id`,`assigned_at`),
  KEY `fk_hearing_action_assignments_office` (`assigned_office_id`),
  KEY `fk_hearing_action_assignments_user` (`assigned_user_id`),
  KEY `fk_hearing_action_assignments_assigned_by` (`assigned_by`),
  CONSTRAINT `fk_hearing_action_assignments_action` FOREIGN KEY (`action_id`) REFERENCES `hearing_actions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_hearing_action_assignments_assigned_by` FOREIGN KEY (`assigned_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_hearing_action_assignments_office` FOREIGN KEY (`assigned_office_id`) REFERENCES `offices` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_hearing_action_assignments_user` FOREIGN KEY (`assigned_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `hearing_action_assignments`
--

LOCK TABLES `hearing_action_assignments` WRITE;
/*!40000 ALTER TABLE `hearing_action_assignments` DISABLE KEYS */;
/*!40000 ALTER TABLE `hearing_action_assignments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `hearing_action_documents`
--

DROP TABLE IF EXISTS `hearing_action_documents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `hearing_action_documents` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `action_id` int(11) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_path` varchar(500) NOT NULL,
  `document_type` varchar(100) NOT NULL DEFAULT 'Supporting Document',
  `description` text DEFAULT NULL,
  `visibility` varchar(30) NOT NULL DEFAULT 'Internal',
  `uploaded_by` int(11) DEFAULT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_hearing_action_documents_action` (`action_id`,`uploaded_at`),
  KEY `fk_hearing_action_documents_user` (`uploaded_by`),
  CONSTRAINT `fk_hearing_action_documents_action` FOREIGN KEY (`action_id`) REFERENCES `hearing_actions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_hearing_action_documents_user` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `hearing_action_documents`
--

LOCK TABLES `hearing_action_documents` WRITE;
/*!40000 ALTER TABLE `hearing_action_documents` DISABLE KEYS */;
/*!40000 ALTER TABLE `hearing_action_documents` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `hearing_action_updates`
--

DROP TABLE IF EXISTS `hearing_action_updates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `hearing_action_updates` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `action_id` int(11) NOT NULL,
  `previous_status` varchar(50) DEFAULT NULL,
  `new_status` varchar(50) DEFAULT NULL,
  `update_text` text NOT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_hearing_action_updates_action` (`action_id`,`created_at`),
  KEY `fk_hearing_action_updates_user` (`updated_by`),
  CONSTRAINT `fk_hearing_action_updates_action` FOREIGN KEY (`action_id`) REFERENCES `hearing_actions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_hearing_action_updates_user` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `hearing_action_updates`
--

LOCK TABLES `hearing_action_updates` WRITE;
/*!40000 ALTER TABLE `hearing_action_updates` DISABLE KEYS */;
/*!40000 ALTER TABLE `hearing_action_updates` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `hearing_actions`
--

DROP TABLE IF EXISTS `hearing_actions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `hearing_actions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `reference_number` (`reference_number`),
  KEY `idx_hearing_actions_issue` (`issue_id`),
  KEY `idx_hearing_actions_status` (`status`),
  KEY `idx_hearing_actions_deadline` (`deadline`),
  KEY `idx_hearing_actions_office` (`assigned_office_id`),
  KEY `fk_hearing_actions_assigned_user` (`assigned_user_id`),
  KEY `fk_hearing_actions_created_by` (`created_by`),
  CONSTRAINT `fk_hearing_actions_assigned_user` FOREIGN KEY (`assigned_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_hearing_actions_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_hearing_actions_issue` FOREIGN KEY (`issue_id`) REFERENCES `hearing_issues` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_hearing_actions_office` FOREIGN KEY (`assigned_office_id`) REFERENCES `offices` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `hearing_actions`
--

LOCK TABLES `hearing_actions` WRITE;
/*!40000 ALTER TABLE `hearing_actions` DISABLE KEYS */;
/*!40000 ALTER TABLE `hearing_actions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `hearing_documents`
--

DROP TABLE IF EXISTS `hearing_documents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `hearing_documents` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `hearing_id` int(11) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_path` varchar(500) NOT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `document_type` varchar(100) DEFAULT 'Supporting Document',
  `description` text DEFAULT NULL,
  `version_number` int(11) DEFAULT 1,
  `visibility` varchar(30) DEFAULT 'Internal',
  `uploaded_by` int(11) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `hearing_id` (`hearing_id`),
  KEY `fk_hearing_documents_uploaded_by` (`uploaded_by`),
  CONSTRAINT `fk_hearing_documents_uploaded_by` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `hearing_documents_ibfk_1` FOREIGN KEY (`hearing_id`) REFERENCES `hearings` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `hearing_documents`
--

LOCK TABLES `hearing_documents` WRITE;
/*!40000 ALTER TABLE `hearing_documents` DISABLE KEYS */;
/*!40000 ALTER TABLE `hearing_documents` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `hearing_issue_assignments`
--

DROP TABLE IF EXISTS `hearing_issue_assignments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `hearing_issue_assignments` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `issue_id` int(11) NOT NULL,
  `assigned_office_id` int(11) DEFAULT NULL,
  `assigned_user_id` int(11) DEFAULT NULL,
  `assigned_by` int(11) DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `assigned_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_hearing_issue_assignments_issue` (`issue_id`,`assigned_at`),
  KEY `fk_hearing_issue_assignments_office` (`assigned_office_id`),
  KEY `fk_hearing_issue_assignments_user` (`assigned_user_id`),
  KEY `fk_hearing_issue_assignments_assigned_by` (`assigned_by`),
  CONSTRAINT `fk_hearing_issue_assignments_assigned_by` FOREIGN KEY (`assigned_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_hearing_issue_assignments_issue` FOREIGN KEY (`issue_id`) REFERENCES `hearing_issues` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_hearing_issue_assignments_office` FOREIGN KEY (`assigned_office_id`) REFERENCES `offices` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_hearing_issue_assignments_user` FOREIGN KEY (`assigned_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `hearing_issue_assignments`
--

LOCK TABLES `hearing_issue_assignments` WRITE;
/*!40000 ALTER TABLE `hearing_issue_assignments` DISABLE KEYS */;
/*!40000 ALTER TABLE `hearing_issue_assignments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `hearing_issue_categories`
--

DROP TABLE IF EXISTS `hearing_issue_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `hearing_issue_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `hearing_issue_categories`
--

LOCK TABLES `hearing_issue_categories` WRITE;
/*!40000 ALTER TABLE `hearing_issue_categories` DISABLE KEYS */;
INSERT INTO `hearing_issue_categories` VALUES (1,'Policy Concern','Concerns regarding proposed policies, ordinances or resolutions.','2026-08-01 04:21:35'),(2,'Public Safety','Issues involving safety, security and community protection.','2026-08-01 04:21:35'),(3,'Budget and Finance','Concerns involving funding, expenses and financial allocation.','2026-08-01 04:21:35'),(4,'Environment','Environmental concerns raised during public consultation.','2026-08-01 04:21:35'),(5,'Infrastructure','Issues involving roads, facilities, utilities and public infrastructure.','2026-08-01 04:21:35'),(6,'Public Services','Concerns regarding government services and service delivery.','2026-08-01 04:21:35'),(7,'Legal and Compliance','Issues involving legal requirements and regulatory compliance.','2026-08-01 04:21:35'),(8,'Other','Issues that do not fall under another available category.','2026-08-01 04:21:35');
/*!40000 ALTER TABLE `hearing_issue_categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `hearing_issue_history`
--

DROP TABLE IF EXISTS `hearing_issue_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `hearing_issue_history` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `issue_id` int(11) NOT NULL,
  `note` text NOT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_hearing_issue_history_issue` (`issue_id`,`created_at`),
  KEY `fk_hearing_issue_history_user` (`created_by`),
  CONSTRAINT `fk_hearing_issue_history_issue` FOREIGN KEY (`issue_id`) REFERENCES `hearing_issues` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_hearing_issue_history_user` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `hearing_issue_history`
--

LOCK TABLES `hearing_issue_history` WRITE;
/*!40000 ALTER TABLE `hearing_issue_history` DISABLE KEYS */;
/*!40000 ALTER TABLE `hearing_issue_history` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `hearing_issues`
--

DROP TABLE IF EXISTS `hearing_issues`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `hearing_issues` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `reference_number` (`reference_number`),
  KEY `idx_hearing_issues_hearing` (`hearing_id`),
  KEY `idx_hearing_issues_legislative_item` (`legislative_item_id`),
  KEY `idx_hearing_issues_feedback` (`feedback_id`),
  KEY `idx_hearing_issues_category` (`category_id`),
  KEY `idx_hearing_issues_status` (`status`),
  KEY `idx_hearing_issues_priority` (`priority`),
  KEY `idx_hearing_issues_office` (`assigned_office_id`),
  KEY `fk_hearing_issues_assigned_user` (`assigned_user_id`),
  KEY `fk_hearing_issues_created_by` (`created_by`),
  CONSTRAINT `fk_hearing_issues_assigned_user` FOREIGN KEY (`assigned_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_hearing_issues_category` FOREIGN KEY (`category_id`) REFERENCES `hearing_issue_categories` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_hearing_issues_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_hearing_issues_feedback` FOREIGN KEY (`feedback_id`) REFERENCES `feedback` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_hearing_issues_hearing` FOREIGN KEY (`hearing_id`) REFERENCES `hearings` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_hearing_issues_legislative_item` FOREIGN KEY (`legislative_item_id`) REFERENCES `legislative_items` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_hearing_issues_office` FOREIGN KEY (`assigned_office_id`) REFERENCES `offices` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `hearing_issues`
--

LOCK TABLES `hearing_issues` WRITE;
/*!40000 ALTER TABLE `hearing_issues` DISABLE KEYS */;
/*!40000 ALTER TABLE `hearing_issues` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `hearing_responses`
--

DROP TABLE IF EXISTS `hearing_responses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `hearing_responses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `issue_id` int(11) NOT NULL,
  `response_text` text NOT NULL,
  `visibility` varchar(30) NOT NULL DEFAULT 'Internal',
  `prepared_by` int(11) DEFAULT NULL,
  `approved_by` int(11) DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  `published_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_hearing_responses_issue` (`issue_id`),
  KEY `fk_hearing_responses_prepared_by` (`prepared_by`),
  KEY `fk_hearing_responses_approved_by` (`approved_by`),
  CONSTRAINT `fk_hearing_responses_approved_by` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_hearing_responses_issue` FOREIGN KEY (`issue_id`) REFERENCES `hearing_issues` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_hearing_responses_prepared_by` FOREIGN KEY (`prepared_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `hearing_responses`
--

LOCK TABLES `hearing_responses` WRITE;
/*!40000 ALTER TABLE `hearing_responses` DISABLE KEYS */;
/*!40000 ALTER TABLE `hearing_responses` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `hearing_types`
--

DROP TABLE IF EXISTS `hearing_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `hearing_types` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `hearing_types`
--

LOCK TABLES `hearing_types` WRITE;
/*!40000 ALTER TABLE `hearing_types` DISABLE KEYS */;
/*!40000 ALTER TABLE `hearing_types` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `hearings`
--

DROP TABLE IF EXISTS `hearings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `hearings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `cancellation_reason` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_hearings_reference_number` (`reference_number`),
  KEY `hearing_type_id` (`hearing_type_id`),
  KEY `committee_id` (`committee_id`),
  KEY `fk_hearings_legislative_item` (`legislative_item_id`),
  KEY `fk_hearings_created_by` (`created_by`),
  CONSTRAINT `fk_hearings_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_hearings_legislative_item` FOREIGN KEY (`legislative_item_id`) REFERENCES `legislative_items` (`id`) ON DELETE SET NULL,
  CONSTRAINT `hearings_ibfk_1` FOREIGN KEY (`hearing_type_id`) REFERENCES `hearing_types` (`id`),
  CONSTRAINT `hearings_ibfk_2` FOREIGN KEY (`committee_id`) REFERENCES `committees` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `hearings`
--

LOCK TABLES `hearings` WRITE;
/*!40000 ALTER TABLE `hearings` DISABLE KEYS */;
/*!40000 ALTER TABLE `hearings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `invitations`
--

DROP TABLE IF EXISTS `invitations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `invitations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `invitation_code` (`invitation_code`),
  UNIQUE KEY `uq_invitation_stakeholder_hearing` (`stakeholder_id`,`hearing_id`),
  KEY `hearing_id` (`hearing_id`),
  KEY `fk_invitations_invited_by` (`invited_by`),
  CONSTRAINT `fk_invitations_invited_by` FOREIGN KEY (`invited_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `invitations_ibfk_1` FOREIGN KEY (`stakeholder_id`) REFERENCES `stakeholders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `invitations_ibfk_2` FOREIGN KEY (`hearing_id`) REFERENCES `hearings` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `invitations`
--

LOCK TABLES `invitations` WRITE;
/*!40000 ALTER TABLE `invitations` DISABLE KEYS */;
/*!40000 ALTER TABLE `invitations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `legislative_item_status_history`
--

DROP TABLE IF EXISTS `legislative_item_status_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `legislative_item_status_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `legislative_item_id` int(11) NOT NULL,
  `previous_status` varchar(100) DEFAULT NULL,
  `new_status` varchar(100) NOT NULL,
  `remarks` text DEFAULT NULL,
  `changed_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_legislative_status_item` (`legislative_item_id`),
  KEY `fk_legislative_status_user` (`changed_by`),
  CONSTRAINT `fk_legislative_status_item` FOREIGN KEY (`legislative_item_id`) REFERENCES `legislative_items` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_legislative_status_user` FOREIGN KEY (`changed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `legislative_item_status_history`
--

LOCK TABLES `legislative_item_status_history` WRITE;
/*!40000 ALTER TABLE `legislative_item_status_history` DISABLE KEYS */;
/*!40000 ALTER TABLE `legislative_item_status_history` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `legislative_item_types`
--

DROP TABLE IF EXISTS `legislative_item_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `legislative_item_types` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(50) NOT NULL,
  `name` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `legislative_item_types`
--

LOCK TABLES `legislative_item_types` WRITE;
/*!40000 ALTER TABLE `legislative_item_types` DISABLE KEYS */;
INSERT INTO `legislative_item_types` VALUES (1,'ordinance','Ordinance',NULL,'2026-08-01 04:21:34'),(2,'resolution','Resolution',NULL,'2026-08-01 04:21:34'),(3,'proposal','Citizen Proposal',NULL,'2026-08-01 04:21:34'),(4,'petition','Petition',NULL,'2026-08-01 04:21:34'),(5,'executive_request','Executive Request',NULL,'2026-08-01 04:21:34'),(6,'policy_matter','Policy Matter',NULL,'2026-08-01 04:21:34'),(7,'public_issue','Public Issue',NULL,'2026-08-01 04:21:34');
/*!40000 ALTER TABLE `legislative_item_types` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `legislative_items`
--

DROP TABLE IF EXISTS `legislative_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `legislative_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `reference_number` (`reference_number`),
  UNIQUE KEY `public_id` (`public_id`),
  KEY `fk_legislative_items_type` (`item_type_id`),
  KEY `fk_legislative_items_system` (`origin_system_id`),
  KEY `fk_legislative_items_office` (`originating_office_id`),
  KEY `fk_legislative_items_created_by` (`created_by`),
  CONSTRAINT `fk_legislative_items_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_legislative_items_office` FOREIGN KEY (`originating_office_id`) REFERENCES `offices` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_legislative_items_system` FOREIGN KEY (`origin_system_id`) REFERENCES `systems` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_legislative_items_type` FOREIGN KEY (`item_type_id`) REFERENCES `legislative_item_types` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `legislative_items`
--

LOCK TABLES `legislative_items` WRITE;
/*!40000 ALTER TABLE `legislative_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `legislative_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notifications`
--

DROP TABLE IF EXISTS `notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `notifications` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `system_id` int(11) DEFAULT NULL,
  `notification_type` varchar(100) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `target_url` varchar(500) DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `read_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_notifications_user` (`user_id`),
  KEY `fk_notifications_system` (`system_id`),
  CONSTRAINT `fk_notifications_system` FOREIGN KEY (`system_id`) REFERENCES `systems` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_notifications_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notifications`
--

LOCK TABLES `notifications` WRITE;
/*!40000 ALTER TABLE `notifications` DISABLE KEYS */;
/*!40000 ALTER TABLE `notifications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `offices`
--

DROP TABLE IF EXISTS `offices`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `offices` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(50) DEFAULT NULL,
  `name` varchar(200) NOT NULL,
  `office_type` varchar(100) DEFAULT NULL,
  `status` varchar(30) DEFAULT 'Active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `offices`
--

LOCK TABLES `offices` WRITE;
/*!40000 ALTER TABLE `offices` DISABLE KEYS */;
INSERT INTO `offices` VALUES (1,'LEG-OFFICE','Office of the City Council','Legislative','Active','2026-08-01 04:21:35','2026-08-01 04:21:35'),(2,'CITY-LEGAL','City Legal Office','Executive','Active','2026-08-01 04:21:35','2026-08-01 04:21:35'),(3,'CITY-BUDGET','City Budget Office','Executive','Active','2026-08-01 04:21:35','2026-08-01 04:21:35'),(4,'CITY-PLANNING','City Planning and Development Office','Executive','Active','2026-08-01 04:21:35','2026-08-01 04:21:35'),(5,'PUBLIC-INFO','Public Information Office','Executive','Active','2026-08-01 04:21:35','2026-08-01 04:21:35'),(6,'ENVI-OFFICE','Environment and Natural Resources Office','Executive','Active','2026-08-01 04:21:35','2026-08-01 04:21:35');
/*!40000 ALTER TABLE `offices` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `permissions`
--

DROP TABLE IF EXISTS `permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `permissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `system_id` int(11) DEFAULT NULL,
  `code` varchar(150) NOT NULL,
  `name` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`),
  KEY `fk_permissions_system` (`system_id`),
  CONSTRAINT `fk_permissions_system` FOREIGN KEY (`system_id`) REFERENCES `systems` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `permissions`
--

LOCK TABLES `permissions` WRITE;
/*!40000 ALTER TABLE `permissions` DISABLE KEYS */;
INSERT INTO `permissions` VALUES (1,1,'ordinance.access','Ordinance and Resolution Life Cycle Management System Access','Allows access to the Ordinance and Resolution Life Cycle Management System.','2026-08-01 04:21:34'),(2,2,'agenda.access','Legislative Agenda and Calendar Management System Access','Allows access to the Legislative Agenda and Calendar Management System.','2026-08-01 04:21:34'),(3,3,'voting.access','Voting, Quorum, and Decision Support System Access','Allows access to the Voting, Quorum, and Decision Support System.','2026-08-01 04:21:34'),(4,4,'hearing.access','Public Hearing and Consultation Management System Access','Allows access to the Public Hearing and Consultation Management System.','2026-08-01 04:21:34'),(5,5,'citizen.access','Citizen Engagement and Public Feedback Management System Access','Allows access to the Citizen Engagement and Public Feedback Management System.','2026-08-01 04:21:34');
/*!40000 ALTER TABLE `permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `policy_records`
--

DROP TABLE IF EXISTS `policy_records`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `policy_records` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `legislative_item_id` int(11) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `category` varchar(100) NOT NULL,
  `city_origin` varchar(100) DEFAULT 'City of Manila',
  `author` varchar(150) NOT NULL,
  `department` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `keywords` varchar(255) DEFAULT NULL,
  `publication_date` date DEFAULT NULL,
  `related_record` varchar(100) DEFAULT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `status` varchar(50) DEFAULT 'Draft',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `ai_summary` longtext DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=107 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `policy_records`
--

LOCK TABLES `policy_records` WRITE;
/*!40000 ALTER TABLE `policy_records` DISABLE KEYS */;
INSERT INTO `policy_records` VALUES (96,NULL,'Manila Ordinance No 2026 053 (checked)','Infrastructure, Traffic and Environment','City of Manila','Department of Public Services (DPS)','Environmental Management Bureau','Mandates commercial establishments and barangays in Manila City to phase out single-use plastics and implement community material recovery protocols.','plastic reduction, recycling, waste management, DPS, environment, manila','2026-09-19','','1789826416_Manila_Ordinance_No_2026-053 (checked).pdf','Approved','2026-09-19 14:00:16','2026-09-19 14:38:05',NULL),(97,NULL,'Manila Ordinance No 2026 051 (checked)','Social Welfare and Community Affairs','City of Manila','Manila Department of Social Welfare (MDSW) / MPD','Peace and Order & Community Safety Division','Policy framework evaluating community safety protocols, localized crime prevention strategies, and multi-agency peace and order operations across Manila City barangays.','community safety, crime prevention, law enforcement, peace and order, MDSW, manila','2026-09-19','','1789826433_Manila_Ordinance_No_2026-051 (checked).pdf','Approved','2026-09-19 14:00:33','2026-09-19 14:11:43',NULL),(98,NULL,'Manila Ordinance No 2026 049','Social Welfare and Community Affairs','City of Manila','Manila Department of Social Welfare (MDSW) / MPD','Peace and Order & Community Safety Division','Policy framework evaluating community safety protocols, localized crime prevention strategies, and multi-agency peace and order operations across Manila City barangays.','community safety, crime prevention, law enforcement, peace and order, MDSW, manila','2026-09-19','','1789828708_Manila_Ordinance_No_2026-049.pdf','Approved','2026-09-19 14:38:28','2026-09-19 14:38:47',NULL),(99,NULL,'Manila Ordinance No 2026 046','Infrastructure, Traffic and Environment','City of Manila','Department of Energy and Climate Policy','Environmental Management Bureau','Macroeconomic and environmental telemetry measuring municipal clean energy transition feasibility and solar grid integration.','clean energy, grid, renewable, solar, carbon, environment, manila','2026-09-19','','1789828780_Manila_Ordinance_No_2026-046.pdf','Approved','2026-09-19 14:39:40','2026-09-19 14:39:49',NULL),(100,NULL,'Flood Risk Assessment and Drainage Improvement Plan for Manila City','Infrastructure, Traffic and Environment','City of Manila','Department of Engineering and Public Works','Engineering Office','Evaluates urban drainage capacity, pumping station throughput, rainfall telemetry, and flood risk mitigation frameworks across Manila City districts.','flooding, drainage, infrastructure, telemetry, engineering, manila','2026-09-19','','1789833851_Flood_Risk_Assessment_and_Drainage_Improvement_Plan_for_Manila_City.docx','Approved','2026-09-19 16:04:11','2026-09-19 16:04:22',NULL),(101,NULL,'Improvement Strategy for Public Health Services in Manila City','Infrastructure, Traffic and Environment','City of Manila','Department of Public Services (DPS)','Environmental Management Bureau','Mandates commercial establishments and barangays in Manila City to phase out single-use plastics and implement community material recovery protocols.','plastic reduction, recycling, waste management, DPS, environment, manila','2026-09-19','','1789835614_Solid_Waste_Management_Improvement_Strategy_for_Manila_City.pdf','Approved','2026-09-19 16:33:34','2026-09-19 16:33:45',NULL),(102,NULL,'Executive Helicopter Transit and VIP Luxury Fleet Acquisition Act of 2026','Infrastructure, Traffic and Environment','City of Manila','Councilor Fernando G. Alcantara','General Services Office / City Administrator','Mandates the procurement of 8 executive twin-engine helicopters and 40 armored luxury SUVs for city councilors and department heads, appropriating ₱14.8 Billion annually from the general fund without identifying new revenues, unilaterally diverting resources from basic social welfare and disaster contingency funds.','helicopter, luxury fleet, VIP transport, executive transit, municipal budget, expenditure, fiscal deficit, unfunded','2026-09-20',NULL,'Manila_Ordinance_2026-101_Executive_Helicopter_Transit.pdf','Needs Revision','2026-09-19 18:08:20','2026-09-19 18:14:00',NULL),(103,NULL,'Mandatory 24-Hour Youth Curfew and Summary Property Confiscation Act of 2026','Social Welfare and Community Affairs','City of Manila','Councilor Victor D. Morales','Manila Police District / Peace and Order Bureau','Imposes an unconstitutional warrantless detention policy on youth under 21 found outdoors past 8:00 PM, authorizing summary confiscation and permanent forfeiture of mobile phones and vehicles without judicial warrant, and levying illegal fines of ₱50,000 and 2 years imprisonment in direct violation of RA 7160 penalty caps and the 1987 Constitution.','curfew, youth detention, summary confiscation, asset forfeiture, police power, ultra vires, human rights, warrantless','2026-09-20',NULL,'Manila_Ordinance_2026-102_Summary_Curfew_and_Asset_Forfeiture.pdf','Draft','2026-09-19 18:08:20','2026-09-19 18:08:20',NULL),(104,NULL,'Manila Bay Coastal Wetland Reclamation and Industrial Waste Incineration Zone Act of 2026','Infrastructure, Traffic and Environment','City of Manila','Councilor Arturo S. De Leon','Bureau of Permits and Industrial Licensing','Authorizes the dredging and land reclamation of 450 hectares of protected mangrove coastline and coastal wetland along Manila Bay without DENR environmental clearance, while approving open commercial toxic waste incineration facilities in blatant defiance of the Supreme Court Manila Bay Continuing Mandamus and the Philippine Clean Air Act (RA 8749).','manila bay, reclamation, wetland destruction, toxic incineration, hazardous waste, clean air act violation, ecological damage, without ecc','2026-09-20',NULL,'Manila_Ordinance_2026-103_Manila_Bay_Reclamation_and_Waste_Incineration.pdf','Draft','2026-09-19 18:08:20','2026-09-19 18:08:20',NULL),(105,NULL,'Immediate Demolition and Total Prohibition of Informal Street Vending and Sidewalk Commerce Act of 2026','Social Welfare and Community Affairs','City of Manila','Councilor Manuel R. Guinto','Manila Department of Social Welfare (MDSW) / Task Force Bayanihan','Mandates the immediate 48-hour forcible demolition and criminal ban on 38,000 informal sidewalk vendors across Manila City with an explicit statutory prohibition against providing relocation sites, alternative vending zones, or livelihood subsidies, violating social equity and due process.','street vendors, demolition, eviction, informal economy, livelihood ban, human rights, social equity, quiapo, divisoria, no relocation','2026-09-20',NULL,'Manila_Ordinance_2026-104_Demolition_and_Ban_on_Street_Vendors.pdf','Draft','2026-09-19 18:08:20','2026-09-19 18:08:20',NULL),(106,NULL,'Commercial Toll Expressway and Compulsory Private Land Expropriation Act of 2026','Infrastructure, Traffic and Environment','City of Manila','Councilor Rodrigo P. Mendoza','Special Infrastructure Committee / City Planning Office','A catastrophic multi-criteria policy failure that imposes a ₱28.5 Billion unbacked debt liability on the city, levies exorbitant daily tolls on public utility vehicles, clear-cuts 8,500 heritage trees in Arroceros Forest Park, and illegally delegates constitutional sovereign powers of eminent domain and police arrest to an unvetted private entity.','tollway, forced eviction, eminent domain, tree cutting, privatized toll, multi-criteria failure, catastrophic risk, unbacked debt','2026-09-20',NULL,'Manila_Ordinance_2026-105_Commercial_Tollway_and_Forced_Expropriation.pdf','Draft','2026-09-19 18:08:20','2026-09-19 18:08:20',NULL);
/*!40000 ALTER TABLE `policy_records` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `qr_codes`
--

DROP TABLE IF EXISTS `qr_codes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `qr_codes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `stakeholder_id` int(11) NOT NULL,
  `registration_id` int(11) DEFAULT NULL,
  `hearing_id` int(11) DEFAULT NULL,
  `code_value` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` varchar(30) DEFAULT 'Active',
  `expires_at` datetime DEFAULT NULL,
  `used_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code_value` (`code_value`),
  KEY `stakeholder_id` (`stakeholder_id`),
  KEY `fk_qr_registration` (`registration_id`),
  KEY `fk_qr_hearing` (`hearing_id`),
  CONSTRAINT `fk_qr_hearing` FOREIGN KEY (`hearing_id`) REFERENCES `hearings` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_qr_registration` FOREIGN KEY (`registration_id`) REFERENCES `registrations` (`id`) ON DELETE CASCADE,
  CONSTRAINT `qr_codes_ibfk_1` FOREIGN KEY (`stakeholder_id`) REFERENCES `stakeholders` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `qr_codes`
--

LOCK TABLES `qr_codes` WRITE;
/*!40000 ALTER TABLE `qr_codes` DISABLE KEYS */;
/*!40000 ALTER TABLE `qr_codes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `registrations`
--

DROP TABLE IF EXISTS `registrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `registrations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `stakeholder_id` int(11) NOT NULL,
  `hearing_id` int(11) DEFAULT NULL,
  `registered_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `registration_code` varchar(100) DEFAULT NULL,
  `registration_status` varchar(50) DEFAULT 'Pending',
  `attendance_type` varchar(50) DEFAULT 'On-site',
  `approved_by` int(11) DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  `rejection_reason` text DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_registration_code` (`registration_code`),
  UNIQUE KEY `uq_registration_stakeholder_hearing` (`stakeholder_id`,`hearing_id`),
  KEY `hearing_id` (`hearing_id`),
  KEY `fk_registrations_approved_by` (`approved_by`),
  CONSTRAINT `fk_registrations_approved_by` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `registrations_ibfk_1` FOREIGN KEY (`stakeholder_id`) REFERENCES `stakeholders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `registrations_ibfk_2` FOREIGN KEY (`hearing_id`) REFERENCES `hearings` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `registrations`
--

LOCK TABLES `registrations` WRITE;
/*!40000 ALTER TABLE `registrations` DISABLE KEYS */;
/*!40000 ALTER TABLE `registrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `reports`
--

DROP TABLE IF EXISTS `reports`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `reports` (
  `report_id` int(11) NOT NULL AUTO_INCREMENT,
  `report_title` varchar(255) DEFAULT NULL,
  `report_type` varchar(100) DEFAULT NULL,
  `generated_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`report_id`),
  KEY `generated_by` (`generated_by`),
  CONSTRAINT `reports_ibfk_1` FOREIGN KEY (`generated_by`) REFERENCES `admins` (`admin_id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reports`
--

LOCK TABLES `reports` WRITE;
/*!40000 ALTER TABLE `reports` DISABLE KEYS */;
INSERT INTO `reports` VALUES (1,'National_Clean_Energy_Grid_Modernization_Act_Economic_and_Environmental_Impact_Assessment_Report.pdf','Evaluation Report',1,'2026-08-18 06:03:00'),(2,'Flood_Risk_Assessment_and_Drainage_Improvement_Plan_for_Manila_City_Report.pdf','Evaluation Report',1,'2026-08-17 05:57:00'),(3,'Flood_Risk_Assessment_and_Drainage_Improvement_Plan_for_Manila_City_Report.pdf','Evaluation Report',1,'2026-08-17 05:55:00'),(4,'Flood_Risk_Assessment_and_Drainage_Improvement_Plan_for_Manila_City_Report.pdf','Evaluation Report',1,'2026-08-16 13:23:00'),(5,'Urban_Traffic_Congestion_Study_in_Manila_City_Report.pdf','Evaluation Report',1,'2026-08-15 02:14:00');
/*!40000 ALTER TABLE `reports` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `research_data`
--

DROP TABLE IF EXISTS `research_data`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `research_data` (
  `research_id` int(11) NOT NULL AUTO_INCREMENT,
  `policy_id` int(11) DEFAULT NULL,
  `research_title` varchar(255) NOT NULL,
  `category` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `source` varchar(255) DEFAULT NULL,
  `uploaded_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`research_id`),
  KEY `uploaded_by` (`uploaded_by`),
  KEY `research_data_ibfk_1` (`policy_id`),
  CONSTRAINT `research_data_ibfk_1` FOREIGN KEY (`policy_id`) REFERENCES `policy_records` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `research_data`
--

LOCK TABLES `research_data` WRITE;
/*!40000 ALTER TABLE `research_data` DISABLE KEYS */;
INSERT INTO `research_data` VALUES (1,49,'Flood Risk Assessment and Drainage Improvement Plan for Manila City','Infrastructure','Comprehensive drainage infrastructure & flood risk telemetry dataset for Manila City.','Department of Engineering and Public Works',1,'2026-08-14 23:59:20'),(3,47,'National Clean Energy Grid Modernization Act: Economic and Environmental Impact Assessment','Health','Economic impact metrics and clean energy grid transition feasibility dataset for municipal buildings.','Department of Energy and Climate Policy',1,'2026-08-11 21:35:23');
/*!40000 ALTER TABLE `research_data` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `role_permissions`
--

DROP TABLE IF EXISTS `role_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `role_permissions` (
  `role_id` int(11) NOT NULL,
  `permission_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`role_id`,`permission_id`),
  KEY `fk_role_permissions_permission` (`permission_id`),
  CONSTRAINT `fk_role_permissions_permission` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_role_permissions_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `role_permissions`
--

LOCK TABLES `role_permissions` WRITE;
/*!40000 ALTER TABLE `role_permissions` DISABLE KEYS */;
INSERT INTO `role_permissions` VALUES (1,1,'2026-08-01 04:21:34'),(1,2,'2026-08-01 04:21:34'),(1,3,'2026-08-01 04:21:34'),(1,4,'2026-08-01 04:21:34'),(1,5,'2026-08-01 04:21:34');
/*!40000 ALTER TABLE `role_permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `roles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `roles`
--

LOCK TABLES `roles` WRITE;
/*!40000 ALTER TABLE `roles` DISABLE KEYS */;
INSERT INTO `roles` VALUES (1,'Administrator','2026-08-01 04:21:34'),(2,'Legislative Staff','2026-08-01 04:21:34'),(3,'Committee Member','2026-08-01 04:21:34'),(4,'Registered Stakeholder','2026-08-01 04:21:34'),(5,'Public User','2026-08-01 04:21:34');
/*!40000 ALTER TABLE `roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `stakeholder_categories`
--

DROP TABLE IF EXISTS `stakeholder_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stakeholder_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `stakeholder_categories`
--

LOCK TABLES `stakeholder_categories` WRITE;
/*!40000 ALTER TABLE `stakeholder_categories` DISABLE KEYS */;
/*!40000 ALTER TABLE `stakeholder_categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `stakeholders`
--

DROP TABLE IF EXISTS `stakeholders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stakeholders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `category_id` (`category_id`),
  KEY `fk_stakeholders_user` (`user_id`),
  KEY `fk_stakeholders_verified_by` (`verified_by`),
  CONSTRAINT `fk_stakeholders_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_stakeholders_verified_by` FOREIGN KEY (`verified_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `stakeholders_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `stakeholder_categories` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `stakeholders`
--

LOCK TABLES `stakeholders` WRITE;
/*!40000 ALTER TABLE `stakeholders` DISABLE KEYS */;
/*!40000 ALTER TABLE `stakeholders` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `survey_answers`
--

DROP TABLE IF EXISTS `survey_answers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `survey_answers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `submission_id` int(11) NOT NULL,
  `question_id` int(11) NOT NULL,
  `option_id` int(11) DEFAULT NULL,
  `answer_text` text DEFAULT NULL,
  `numeric_value` decimal(12,2) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_survey_answers_submission` (`submission_id`),
  KEY `fk_survey_answers_question` (`question_id`),
  KEY `fk_survey_answers_option` (`option_id`),
  CONSTRAINT `fk_survey_answers_option` FOREIGN KEY (`option_id`) REFERENCES `survey_question_options` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_survey_answers_question` FOREIGN KEY (`question_id`) REFERENCES `survey_questions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_survey_answers_submission` FOREIGN KEY (`submission_id`) REFERENCES `survey_submissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `survey_answers`
--

LOCK TABLES `survey_answers` WRITE;
/*!40000 ALTER TABLE `survey_answers` DISABLE KEYS */;
/*!40000 ALTER TABLE `survey_answers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `survey_question_options`
--

DROP TABLE IF EXISTS `survey_question_options`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `survey_question_options` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `question_id` int(11) NOT NULL,
  `option_text` varchar(255) NOT NULL,
  `sequence_number` int(11) DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `fk_survey_options_question` (`question_id`),
  CONSTRAINT `fk_survey_options_question` FOREIGN KEY (`question_id`) REFERENCES `survey_questions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `survey_question_options`
--

LOCK TABLES `survey_question_options` WRITE;
/*!40000 ALTER TABLE `survey_question_options` DISABLE KEYS */;
/*!40000 ALTER TABLE `survey_question_options` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `survey_questions`
--

DROP TABLE IF EXISTS `survey_questions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `survey_questions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `survey_id` int(11) NOT NULL,
  `question_text` text NOT NULL,
  `question_type` varchar(50) NOT NULL,
  `is_required` tinyint(1) DEFAULT 0,
  `sequence_number` int(11) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_survey_questions_survey` (`survey_id`),
  CONSTRAINT `fk_survey_questions_survey` FOREIGN KEY (`survey_id`) REFERENCES `surveys` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `survey_questions`
--

LOCK TABLES `survey_questions` WRITE;
/*!40000 ALTER TABLE `survey_questions` DISABLE KEYS */;
/*!40000 ALTER TABLE `survey_questions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `survey_responses`
--

DROP TABLE IF EXISTS `survey_responses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `survey_responses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `survey_id` int(11) NOT NULL,
  `respondent_name` varchar(150) DEFAULT NULL,
  `respondent_email` varchar(150) DEFAULT NULL,
  `response_text` text NOT NULL,
  `submitted_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `survey_responses`
--

LOCK TABLES `survey_responses` WRITE;
/*!40000 ALTER TABLE `survey_responses` DISABLE KEYS */;
/*!40000 ALTER TABLE `survey_responses` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `survey_submissions`
--

DROP TABLE IF EXISTS `survey_submissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `survey_submissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `survey_id` int(11) NOT NULL,
  `stakeholder_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `respondent_name` varchar(150) DEFAULT NULL,
  `respondent_email` varchar(150) DEFAULT NULL,
  `submitted_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_survey_submissions_survey` (`survey_id`),
  KEY `fk_survey_submissions_stakeholder` (`stakeholder_id`),
  KEY `fk_survey_submissions_user` (`user_id`),
  CONSTRAINT `fk_survey_submissions_stakeholder` FOREIGN KEY (`stakeholder_id`) REFERENCES `stakeholders` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_survey_submissions_survey` FOREIGN KEY (`survey_id`) REFERENCES `surveys` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_survey_submissions_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `survey_submissions`
--

LOCK TABLES `survey_submissions` WRITE;
/*!40000 ALTER TABLE `survey_submissions` DISABLE KEYS */;
/*!40000 ALTER TABLE `survey_submissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `surveys`
--

DROP TABLE IF EXISTS `surveys`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `surveys` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `status` varchar(50) DEFAULT 'Active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `hearing_id` int(11) DEFAULT NULL,
  `legislative_item_id` int(11) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `opens_at` datetime DEFAULT NULL,
  `closes_at` datetime DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_surveys_hearing` (`hearing_id`),
  KEY `fk_surveys_legislative_item` (`legislative_item_id`),
  KEY `fk_surveys_created_by` (`created_by`),
  CONSTRAINT `fk_surveys_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_surveys_hearing` FOREIGN KEY (`hearing_id`) REFERENCES `hearings` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_surveys_legislative_item` FOREIGN KEY (`legislative_item_id`) REFERENCES `legislative_items` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `surveys`
--

LOCK TABLES `surveys` WRITE;
/*!40000 ALTER TABLE `surveys` DISABLE KEYS */;
/*!40000 ALTER TABLE `surveys` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `systems`
--

DROP TABLE IF EXISTS `systems`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `systems` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(50) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `base_url` varchar(255) DEFAULT NULL,
  `status` varchar(30) NOT NULL DEFAULT 'Active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `systems`
--

LOCK TABLES `systems` WRITE;
/*!40000 ALTER TABLE `systems` DISABLE KEYS */;
INSERT INTO `systems` VALUES (1,'ordinance','Ordinance and Resolution Life Cycle Management System','Manages ordinances and resolutions from drafting through implementation and revision.','http://localhost/orlms','Active','2026-08-01 04:21:33','2026-08-01 07:06:35'),(2,'agenda','Legislative Agenda and Calendar Management System','Manages legislative priorities, schedules, meetings and deadlines.',NULL,'Planned','2026-08-01 04:21:33','2026-08-01 04:21:34'),(3,'voting','Voting, Quorum, and Decision Support System','Manages quorum verification, voting, tallying and legislative decisions.',NULL,'Planned','2026-08-01 04:21:33','2026-08-01 04:21:34'),(4,'hearing','Public Hearing and Consultation Management System','Manages hearings, stakeholders, attendance, feedback, issues and actions.','http://localhost/lph','Active','2026-08-01 04:21:33','2026-08-01 04:21:34'),(5,'citizen','Citizen Engagement and Public Feedback Management System','Manages public feedback, citizen proposals, complaints and official responses.',NULL,'Planned','2026-08-01 04:21:33','2026-08-01 04:21:34'),(6,'policy_research','Policy Research, Evaluation and Benchmarking System','Manages policy records, impact scoring, comparative analysis, and benchmarking.','http://localhost/Legislative','Active','2026-09-16 15:22:33','2026-09-16 15:22:33');
/*!40000 ALTER TABLE `systems` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_directory`
--

DROP TABLE IF EXISTS `user_directory`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_directory` (
  `user_id` int(11) NOT NULL AUTO_INCREMENT,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `profile_picture` varchar(255) DEFAULT 'default.png',
  `status` enum('Active','Inactive') DEFAULT 'Active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `role` varchar(100) DEFAULT 'Legislative Staff',
  `department` varchar(150) DEFAULT 'Secretariat',
  `otp_code` varchar(10) DEFAULT NULL,
  `otp_expires_at` datetime DEFAULT NULL,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_directory`
--

LOCK TABLES `user_directory` WRITE;
/*!40000 ALTER TABLE `user_directory` DISABLE KEYS */;
INSERT INTO `user_directory` VALUES (5,'Salas','stephenjaysalas@gmail.com','Salas','$2y$10$UwzMVv8Rbjh/OeiYTNluVuBli5DYCgSCfUJKlOAO3ujRbDacepW/.','default.png','Active','2026-08-02 09:01:43','Staff','Secretariat',NULL,NULL),(6,'Daniel','Danielrivera@gmail.com','Daniel','$2y$10$jzeZFfLaKyLNcTxHfpPey.yStCGGvkhxdRCVP3iNzqY0h6M9QWa7.','default.png','Active','2026-08-03 03:45:06','Staff','Secretariat',NULL,NULL),(7,'Salim','Salim@gmail.com','Salim@gmail.com','$2y$10$5JBEqHibio3mZ4T0DcHDu.uWWQdGVjM9nH4XNPUQqE.OtGLs52xWW','default.png','Active','2026-08-04 05:24:19','Staff','Secretariat',NULL,NULL),(8,'Jay','jayjay@gmail.com','jayjay@gmail.com','$2y$10$BxBlaccopPRLpcMnQAg9JOlGrXPrGFXW5DBqXLUOvA9PEkKM9emQW','default.png','Active','2026-08-05 06:08:12','Staff','Secretariat',NULL,NULL),(9,'daniel','daniel@gmail.com','daniel@gmail.com','$2y$10$DB5jH98yjCrMqBvODFElcu7.p32Dj.NqqPmtlV/wdDO3lgh/Lw8/6','default.png','Active','2026-08-07 12:28:50','Staff','Secretariat',NULL,NULL),(10,'Christian','Christian123@gmail.com','Christian123','$2y$10$5cltF.rogUHKy2ByFeuhvOzFTUYXroWAKRjWiIEPtIipyqrHSyd9i','default.png','Active','2026-08-09 13:30:44','Staff','Office',NULL,NULL),(24,'stephenjay','stephenjay@gmail.com','stephen123','$2y$10$BX0RIpmDfEuJ0eE40JE6b.Mp0U7OolTncMBO2xblTtyPFGX6zsK4m','default.png','Active','2026-09-01 03:47:07','Councilor','Office',NULL,NULL),(25,'nail','nail123@gmail.com','nail123','$2y$10$oRb83CV90qrgfo.OxhPO5uXKaGo8XefO8auqPwMgC/N.sUyDAOauy','default.png','Active','2026-09-01 03:55:03','Staff','Office',NULL,NULL),(26,'krestyan','krestyan@gmail.com','krestyan123','$2y$10$UO0Um1hCqp74CfoWdSPVi.gfzk0/zuPu9ed5o.ARFN.lZwZhsLaEe','default.png','Active','2026-09-01 08:37:44','Councilor','office',NULL,NULL),(27,'Quintanapangit','Quintanapogi@gmail.com','Quintanapogi','123456789','default.png','Active','2026-09-02 06:34:23','Councilor','Office',NULL,NULL),(28,'Christian M. Caspe','christiancaspe19@gmail.com','christiancaspe19','$2y$10$jLNrtlMn9IvjD5t/cz0fR.4R26j8d5pQDzRHwt19OZ6ucL.9ecFDi','default.png','Active','2026-09-18 15:26:17','Admin','City Administration',NULL,NULL),(29,'renz','salimlorence@gmail.com','renz123','$2y$10$qAy/7WC0T4.tNPSMH..thu2agdLdebK7.jHuwLrJoeOx68KRxW3ya','default.png','Active','2026-09-19 05:12:40','Staff','Office',NULL,NULL),(30,'daniel','danielpanen1@gmail.com','daniel123','$2y$10$1wkH3aCGR7amTjo5YnMxn.Ct.syzmJ7IYvUufvoi6fZmKaxwhkpMi','default.png','Active','2026-09-19 18:16:31','Councilor','Staff',NULL,NULL);
/*!40000 ALTER TABLE `user_directory` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_roles`
--

DROP TABLE IF EXISTS `user_roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_roles` (
  `user_id` int(11) NOT NULL,
  `role_id` int(11) NOT NULL,
  `is_primary` tinyint(1) NOT NULL DEFAULT 0,
  `assigned_by` int(11) DEFAULT NULL,
  `assigned_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`user_id`,`role_id`),
  KEY `fk_user_roles_role` (`role_id`),
  KEY `fk_user_roles_assigned_by` (`assigned_by`),
  CONSTRAINT `fk_user_roles_assigned_by` FOREIGN KEY (`assigned_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_user_roles_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_user_roles_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_roles`
--

LOCK TABLES `user_roles` WRITE;
/*!40000 ALTER TABLE `user_roles` DISABLE KEYS */;
INSERT INTO `user_roles` VALUES (1,1,1,1,'2026-08-01 04:21:34');
/*!40000 ALTER TABLE `user_roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_system_access`
--

DROP TABLE IF EXISTS `user_system_access`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_system_access` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `system_id` int(11) NOT NULL,
  `access_level` varchar(50) DEFAULT 'Standard',
  `status` varchar(30) DEFAULT 'Active',
  `granted_by` int(11) DEFAULT NULL,
  `granted_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_user_system` (`user_id`,`system_id`),
  KEY `fk_user_system_access_system` (`system_id`),
  KEY `fk_user_system_access_granted_by` (`granted_by`),
  CONSTRAINT `fk_user_system_access_granted_by` FOREIGN KEY (`granted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_user_system_access_system` FOREIGN KEY (`system_id`) REFERENCES `systems` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_user_system_access_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_system_access`
--

LOCK TABLES `user_system_access` WRITE;
/*!40000 ALTER TABLE `user_system_access` DISABLE KEYS */;
INSERT INTO `user_system_access` VALUES (1,1,2,'Administrator','Active',1,'2026-08-01 04:21:34','2026-08-01 04:21:34'),(2,1,5,'Administrator','Active',1,'2026-08-01 04:21:34','2026-08-01 04:21:34'),(3,1,4,'Administrator','Active',1,'2026-08-01 04:21:34','2026-08-01 04:21:34'),(4,1,1,'Administrator','Active',1,'2026-08-01 04:21:34','2026-08-01 04:21:34'),(5,1,3,'Administrator','Active',1,'2026-08-01 04:21:34','2026-08-01 04:21:34');
/*!40000 ALTER TABLE `user_system_access` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `deleted_at` datetime DEFAULT NULL,
  `role` varchar(100) DEFAULT 'Staff',
  `department` varchar(150) DEFAULT 'City Administration',
  `otp_code` varchar(10) DEFAULT NULL,
  `otp_expires_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `uq_users_username` (`username`),
  KEY `role_id` (`role_id`),
  KEY `fk_users_office` (`office_id`),
  KEY `fk_users_department` (`department_id`),
  CONSTRAINT `fk_users_department` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_users_office` FOREIGN KEY (`office_id`) REFERENCES `offices` (`id`) ON DELETE SET NULL,
  CONSTRAINT `users_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'admin','System Administrator','admin@legislative.local',NULL,'$2y$12$7vQcfuIlcWb8KFRnrslnWObtFwcOcDaaorJHmGNdGARDMVvJSxyOS',1,NULL,NULL,'Active','2026-08-01 04:21:34','2026-08-01 21:31:23','2026-08-01 13:31:23',NULL,'Staff','City Administration',NULL,NULL),(2,'christiancaspe19','Christian M. Caspe','christiancaspe19@gmail.com',NULL,'$2y$10$jLNrtlMn9IvjD5t/cz0fR.4R26j8d5pQDzRHwt19OZ6ucL.9ecFDi',1,NULL,NULL,'Active','2026-09-18 16:35:59',NULL,'2026-09-19 20:12:58',NULL,'Admin','City Administration',NULL,NULL);
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping routines for database 'legislative_management_db'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-09-20  4:15:40
