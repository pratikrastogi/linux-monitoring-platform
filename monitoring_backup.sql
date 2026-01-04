mysqldump: [Warning] Using a password on the command line interface can be insecure.
-- MySQL dump 10.13  Distrib 8.0.44, for Linux (x86_64)
--
-- Host: localhost    Database: monitoring
-- ------------------------------------------------------
-- Server version	8.0.44

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
mysqldump: Error: 'Access denied; you need (at least one of) the PROCESS privilege(s) for this operation' when trying to dump tablespaces

--
-- Table structure for table `alert_config`
--

DROP TABLE IF EXISTS `alert_config`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `alert_config` (
  `id` int NOT NULL,
  `teams_webhook` text,
  `enabled` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `alert_config`
--

LOCK TABLES `alert_config` WRITE;
/*!40000 ALTER TABLE `alert_config` DISABLE KEYS */;
INSERT INTO `alert_config` VALUES (1,'https://kyndryl.webhook.office.com/webhookb2/b6cd779f-6fe8-4248-a5b3-80a21dfbe5fd@f260df36-bc43-424c-8f44-c85226657b01/IncomingWebhook/04e869815e5b4297ab4876fb0c1284f8/e50f673e-d181-428d-8cb9-bdebb025dfcf/V2oF8pBCSFW2gK4Vl_slLweoQmEUa2r1dnX4Z15e3ogxQ1',1);
/*!40000 ALTER TABLE `alert_config` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `alerts`
--

DROP TABLE IF EXISTS `alerts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `alerts` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `server_id` int NOT NULL,
  `alert_type` varchar(50) DEFAULT NULL,
  `message` text,
  `active` tinyint(1) DEFAULT '1',
  `notified` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_alert_server` (`server_id`),
  CONSTRAINT `fk_alert_server` FOREIGN KEY (`server_id`) REFERENCES `servers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `alerts`
--

LOCK TABLES `alerts` WRITE;
/*!40000 ALTER TABLE `alerts` DISABLE KEYS */;
INSERT INTO `alerts` VALUES (1,1,'HOST_DOWN','Server unreachable',1,1,'2026-01-04 18:09:37'),(2,1,'HOST_DOWN','Server is tollay unreachable',1,1,'2026-01-04 18:10:41'),(3,2,'HOST_DOWN','Server unreachable',1,1,'2026-01-04 18:13:27'),(4,2,'HOST_DOWN','Server unreachable',1,1,'2026-01-04 18:13:41'),(5,2,'HOST_DOWN','Server unreachable',1,1,'2026-01-04 18:13:57'),(6,2,'HOST_DOWN','Server unreachable',1,1,'2026-01-04 18:14:12'),(7,2,'HOST_DOWN','Server unreachable',1,1,'2026-01-04 18:14:28'),(8,2,'HOST_DOWN','Server unreachable',1,1,'2026-01-04 18:14:42');
/*!40000 ALTER TABLE `alerts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `audit_log`
--

DROP TABLE IF EXISTS `audit_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `audit_log` (
  `id` int NOT NULL AUTO_INCREMENT,
  `actor` varchar(50) DEFAULT NULL,
  `action` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `audit_log`
--

LOCK TABLES `audit_log` WRITE;
/*!40000 ALTER TABLE `audit_log` DISABLE KEYS */;
/*!40000 ALTER TABLE `audit_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `server_metrics`
--

DROP TABLE IF EXISTS `server_metrics`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `server_metrics` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `server_id` int NOT NULL,
  `os_version` varchar(255) DEFAULT NULL,
  `virtualization` varchar(100) DEFAULT NULL,
  `uptime` varchar(255) DEFAULT NULL,
  `sshd_status` varchar(20) DEFAULT NULL,
  `reachable` tinyint(1) DEFAULT '0',
  `cpu_usage` float DEFAULT '0',
  `mem_usage` float DEFAULT '0',
  `disk_usage` float DEFAULT '0',
  `collected_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_metrics_server` (`server_id`),
  CONSTRAINT `fk_metrics_server` FOREIGN KEY (`server_id`) REFERENCES `servers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=182 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `server_metrics`
--

LOCK TABLES `server_metrics` WRITE;
/*!40000 ALTER TABLE `server_metrics` DISABLE KEYS */;
INSERT INTO `server_metrics` VALUES (1,1,'Red Hat Enterprise Linux 9.7 (Plow)','bhyve','up 5 hours, 42 minutes','active',1,3.6,38.33,48,'2026-01-04 17:58:41'),(2,1,'Red Hat Enterprise Linux 9.7 (Plow)','bhyve','up 5 hours, 42 minutes','active',1,1.8,38,48,'2026-01-04 17:58:53'),(3,1,'Red Hat Enterprise Linux 9.7 (Plow)','bhyve','up 5 hours, 42 minutes','active',1,0.7,38.03,48,'2026-01-04 17:59:05'),(4,1,'Red Hat Enterprise Linux 9.7 (Plow)','bhyve','up 5 hours, 42 minutes','active',1,1.1,38.05,48,'2026-01-04 17:59:17'),(5,1,'Red Hat Enterprise Linux 9.7 (Plow)','bhyve','up 5 hours, 42 minutes','active',1,1.4,38.22,48,'2026-01-04 17:59:29'),(6,1,'Red Hat Enterprise Linux 9.7 (Plow)','bhyve','up 5 hours, 43 minutes','active',1,3.9,38.08,48,'2026-01-04 17:59:41'),(7,1,'Red Hat Enterprise Linux 9.7 (Plow)','bhyve','up 5 hours, 43 minutes','active',1,3.3,37.91,48,'2026-01-04 17:59:53'),(8,1,'Red Hat Enterprise Linux 9.7 (Plow)','bhyve','up 5 hours, 43 minutes','active',1,2.3,38.07,48,'2026-01-04 18:00:06'),(9,1,'Red Hat Enterprise Linux 9.7 (Plow)','bhyve','up 5 hours, 43 minutes','active',1,3.3,38.16,48,'2026-01-04 18:00:18'),(10,1,'Red Hat Enterprise Linux 9.7 (Plow)','bhyve','up 5 hours, 43 minutes','active',1,1.8,38.14,48,'2026-01-04 18:00:29'),(11,1,'Red Hat Enterprise Linux 9.7 (Plow)','bhyve','up 5 hours, 44 minutes','active',1,2.1,38.16,48,'2026-01-04 18:00:41'),(12,1,'Red Hat Enterprise Linux 9.7 (Plow)','bhyve','up 5 hours, 44 minutes','active',1,0.4,38.04,48,'2026-01-04 18:00:53'),(13,1,'Red Hat Enterprise Linux 9.7 (Plow)','bhyve','up 5 hours, 44 minutes','active',1,1.8,38.14,48,'2026-01-04 18:01:05'),(14,1,'Red Hat Enterprise Linux 9.7 (Plow)','bhyve','up 5 hours, 44 minutes','active',1,1.1,38.16,48,'2026-01-04 18:01:17'),(15,1,'Red Hat Enterprise Linux 9.7 (Plow)','bhyve','up 5 hours, 44 minutes','active',1,1.5,38.1,48,'2026-01-04 18:01:28'),(16,1,'Red Hat Enterprise Linux 9.7 (Plow)','bhyve','up 5 hours, 45 minutes','active',1,2.5,38.06,48,'2026-01-04 18:01:40'),(17,1,'Red Hat Enterprise Linux 9.7 (Plow)','bhyve','up 5 hours, 45 minutes','active',1,1.4,37.97,48,'2026-01-04 18:01:53'),(18,1,'Red Hat Enterprise Linux 9.7 (Plow)','bhyve','up 5 hours, 45 minutes','active',1,1.4,38.15,48,'2026-01-04 18:02:05'),(19,1,'Red Hat Enterprise Linux 9.7 (Plow)','bhyve','up 5 hours, 45 minutes','active',1,1.4,38.16,48,'2026-01-04 18:02:17'),(20,1,'Red Hat Enterprise Linux 9.7 (Plow)','bhyve','up 5 hours, 45 minutes','active',1,1.5,38.1,48,'2026-01-04 18:02:29'),(21,1,'Red Hat Enterprise Linux 9.7 (Plow)','bhyve','up 5 hours, 46 minutes','active',1,3.3,38.11,48,'2026-01-04 18:02:42'),(22,1,'Red Hat Enterprise Linux 9.7 (Plow)','bhyve','up 5 hours, 46 minutes','active',1,1,38.08,48,'2026-01-04 18:02:55'),(23,1,'Red Hat Enterprise Linux 9.7 (Plow)','bhyve','up 5 hours, 46 minutes','active',1,1.1,37.89,48,'2026-01-04 18:03:06'),(24,1,'Red Hat Enterprise Linux 9.7 (Plow)','bhyve','up 5 hours, 46 minutes','active',1,2.7,38.11,48,'2026-01-04 18:03:18'),(25,1,'Red Hat Enterprise Linux 9.7 (Plow)','bhyve','up 5 hours, 46 minutes','active',1,2.2,38.29,48,'2026-01-04 18:03:31'),(26,1,'Red Hat Enterprise Linux 9.7 (Plow)','bhyve','up 5 hours, 47 minutes','active',1,1.4,38.06,48,'2026-01-04 18:03:43'),(27,1,'Red Hat Enterprise Linux 9.7 (Plow)','bhyve','up 5 hours, 47 minutes','active',1,0.6,38.13,48,'2026-01-04 18:03:56'),(28,1,'Red Hat Enterprise Linux 9.7 (Plow)','bhyve','up 5 hours, 47 minutes','active',1,2.6,37.88,48,'2026-01-04 18:04:09'),(29,1,'Red Hat Enterprise Linux 9.7 (Plow)','bhyve','up 5 hours, 47 minutes','active',1,2.3,38.26,48,'2026-01-04 18:04:22'),(30,1,'Red Hat Enterprise Linux 9.7 (Plow)','bhyve','up 5 hours, 47 minutes','active',1,2.5,38.38,48,'2026-01-04 18:04:33'),(31,1,'Red Hat Enterprise Linux 9.7 (Plow)','bhyve','up 5 hours, 48 minutes','active',1,2.2,38.26,48,'2026-01-04 18:04:46'),(32,1,'Red Hat Enterprise Linux 9.7 (Plow)','bhyve','up 5 hours, 48 minutes','active',1,1.1,37.95,48,'2026-01-04 18:04:58'),(33,1,'Red Hat Enterprise Linux 9.7 (Plow)','bhyve','up 5 hours, 48 minutes','active',1,1.9,38.42,48,'2026-01-04 18:05:11'),(34,1,'Red Hat Enterprise Linux 9.7 (Plow)','bhyve','up 5 hours, 48 minutes','active',1,2.3,38.26,48,'2026-01-04 18:05:23'),(35,1,'Red Hat Enterprise Linux 9.7 (Plow)','bhyve','up 5 hours, 48 minutes','active',1,2.2,38.36,48,'2026-01-04 18:05:34'),(36,1,'Red Hat Enterprise Linux 9.7 (Plow)','bhyve','up 5 hours, 49 minutes','active',1,1.5,38.04,48,'2026-01-04 18:05:47'),(37,1,'Red Hat Enterprise Linux 9.7 (Plow)','bhyve','up 5 hours, 49 minutes','active',1,1.7,38.2,48,'2026-01-04 18:05:58'),(38,1,'Red Hat Enterprise Linux 9.7 (Plow)','bhyve','up 5 hours, 49 minutes','active',1,3.8,38.29,48,'2026-01-04 18:06:11'),(39,1,'Red Hat Enterprise Linux 9.7 (Plow)','bhyve','up 5 hours, 49 minutes','active',1,0.9,38.29,48,'2026-01-04 18:06:23'),(40,1,'Red Hat Enterprise Linux 9.7 (Plow)','bhyve','up 5 hours, 49 minutes','active',1,1.9,38.13,48,'2026-01-04 18:06:35'),(41,1,'Red Hat Enterprise Linux 9.7 (Plow)','bhyve','up 5 hours, 50 minutes','active',1,1.1,38.18,48,'2026-01-04 18:06:46'),(42,1,'Red Hat Enterprise Linux 9.7 (Plow)','bhyve','up 5 hours, 50 minutes','active',1,1.9,38.06,48,'2026-01-04 18:06:59'),(43,1,'Red Hat Enterprise Linux 9.7 (Plow)','bhyve','up 5 hours, 50 minutes','active',1,2.1,38.87,48,'2026-01-04 18:07:11'),(44,1,'Red Hat Enterprise Linux 9.7 (Plow)','bhyve','up 5 hours, 50 minutes','active',1,0.8,38.16,48,'2026-01-04 18:07:23'),(45,1,'Red Hat Enterprise Linux 9.7 (Plow)','bhyve','up 5 hours, 50 minutes','active',1,1.9,37.91,48,'2026-01-04 18:07:36'),(46,1,'Red Hat Enterprise Linux 9.7 (Plow)','bhyve','up 5 hours, 51 minutes','active',1,3.6,38.12,48,'2026-01-04 18:07:48'),(47,1,'Red Hat Enterprise Linux 9.7 (Plow)','bhyve','up 5 hours, 51 minutes','active',1,1.4,38.42,48,'2026-01-04 18:08:00'),(48,1,'Red Hat Enterprise Linux 9.7 (Plow)','bhyve','up 5 hours, 51 minutes','active',1,1.5,38.26,48,'2026-01-04 18:08:11'),(49,1,'Red Hat Enterprise Linux 9.7 (Plow)','bhyve','up 5 hours, 51 minutes','active',1,0.8,37.98,48,'2026-01-04 18:08:23'),(50,1,'Red Hat Enterprise Linux 9.7 (Plow)','bhyve','up 5 hours, 51 minutes','active',1,1.4,38.15,48,'2026-01-04 18:08:35'),(51,1,'Red Hat Enterprise Linux 9.7 (Plow)','bhyve','up 5 hours, 52 minutes','active',1,0.7,38.13,48,'2026-01-04 18:08:47'),(52,1,'Red Hat Enterprise Linux 9.7 (Plow)','bhyve','up 5 hours, 52 minutes','active',1,1.2,38.28,48,'2026-01-04 18:08:59'),(53,1,'Red Hat Enterprise Linux 9.7 (Plow)','bhyve','up 5 hours, 52 minutes','active',1,1,37.95,48,'2026-01-04 18:09:12'),(54,1,'Red Hat Enterprise Linux 9.7 (Plow)','bhyve','up 5 hours, 52 minutes','active',1,0,37.86,48,'2026-01-04 18:09:24'),(55,1,'Red Hat Enterprise Linux 9.7 (Plow)','bhyve','up 5 hours, 52 minutes','active',1,1.9,38.06,48,'2026-01-04 18:09:35'),(56,1,'Red Hat Enterprise Linux 9.7 (Plow)','bhyve','up 5 hours, 53 minutes','active',1,1.1,37.98,48,'2026-01-04 18:09:47'),(57,1,'Red Hat Enterprise Linux 9.7 (Plow)','bhyve','up 5 hours, 53 minutes','active',1,1.7,37.98,48,'2026-01-04 18:09:59'),(58,1,'Red Hat Enterprise Linux 9.7 (Plow)','bhyve','up 5 hours, 53 minutes','active',1,3.1,38.72,48,'2026-01-04 18:10:11'),(59,1,'Red Hat Enterprise Linux 9.7 (Plow)','bhyve','up 5 hours, 53 minutes','active',1,2.1,37.77,48,'2026-01-04 18:10:22'),(60,1,'Red Hat Enterprise Linux 9.7 (Plow)','bhyve','up 5 hours, 53 minutes','active',1,1.8,38.19,48,'2026-01-04 18:10:33'),(61,1,'Red Hat Enterprise Linux 9.7 (Plow)','bhyve','up 5 hours, 54 minutes','active',1,2.5,37.91,48,'2026-01-04 18:10:45'),(62,1,'Red Hat Enterprise Linux 9.7 (Plow)','bhyve','up 5 hours, 54 minutes','active',1,2.4,38.08,48,'2026-01-04 18:10:57'),(63,1,'Red Hat Enterprise Linux 9.7 (Plow)','bhyve','up 5 hours, 54 minutes','active',1,3.6,37.74,48,'2026-01-04 18:11:08'),(64,1,'Red Hat Enterprise Linux 9.7 (Plow)','bhyve','up 5 hours, 54 minutes','active',1,1.4,38.12,48,'2026-01-04 18:11:19'),(65,1,'Red Hat Enterprise Linux 9.7 (Plow)','bhyve','up 5 hours, 54 minutes','active',1,1.4,38.05,48,'2026-01-04 18:11:31'),(66,1,'Red Hat Enterprise Linux 9.7 (Plow)','bhyve','up 5 hours, 55 minutes','active',1,1.1,38.08,48,'2026-01-04 18:11:44'),(67,1,'Red Hat Enterprise Linux 9.7 (Plow)','bhyve','up 5 hours, 55 minutes','active',1,1.1,37.94,48,'2026-01-04 18:11:56'),(68,1,'Red Hat Enterprise Linux 9.7 (Plow)','bhyve','up 5 hours, 55 minutes','active',1,3.2,38.06,48,'2026-01-04 18:12:08'),(69,1,'Red Hat Enterprise Linux 9.7 (Plow)','bhyve','up 5 hours, 55 minutes','active',1,4.4,39.06,48,'2026-01-04 18:12:21'),(70,1,'Red Hat Enterprise Linux 9.7 (Plow)','bhyve','up 5 hours, 55 minutes','active',1,1.3,38.12,48,'2026-01-04 18:12:33'),(71,1,'Red Hat Enterprise Linux 9.7 (Plow)','bhyve','up 5 hours, 56 minutes','active',1,2.3,38.01,48,'2026-01-04 18:12:46'),(72,1,'Red Hat Enterprise Linux 9.7 (Plow)','bhyve','up 5 hours, 56 minutes','active',1,1.4,37.86,48,'2026-01-04 18:12:58'),(73,1,'Red Hat Enterprise Linux 9.7 (Plow)','bhyve','up 5 hours, 56 minutes','active',1,4.3,38.14,48,'2026-01-04 18:13:10'),(74,1,'Red Hat Enterprise Linux 9.7 (Plow)','bhyve','up 5 hours, 56 minutes','active',1,2.2,37.93,48,'2026-01-04 18:13:24'),(75,2,'NA','NA','NA','down',0,0,0,0,'2026-01-04 18:13:27'),(76,1,'Red Hat Enterprise Linux 9.7 (Plow)','bhyve','up 5 hours, 57 minutes','active',1,2.6,38.04,48,'2026-01-04 18:13:38'),(77,2,'NA','NA','NA','down',0,0,0,0,'2026-01-04 18:13:41'),(78,1,'Red Hat Enterprise Linux 9.7 (Plow)','bhyve','up 5 hours, 57 minutes','active',1,1.5,38.25,48,'2026-01-04 18:13:54'),(79,2,'NA','NA','NA','down',0,0,0,0,'2026-01-04 18:13:57'),(80,1,'Red Hat Enterprise Linux 9.7 (Plow)','bhyve','up 5 hours, 57 minutes','active',1,1.8,38.28,48,'2026-01-04 18:14:09'),(81,2,'NA','NA','NA','down',0,0,0,0,'2026-01-04 18:14:12'),(82,1,'Red Hat Enterprise Linux 9.7 (Plow)','bhyve','up 5 hours, 57 minutes','active',1,3,38.25,48,'2026-01-04 18:14:25'),(83,2,'NA','NA','NA','down',0,0,0,0,'2026-01-04 18:14:28'),(84,1,'Red Hat Enterprise Linux 9.7 (Plow)','bhyve','up 5 hours, 58 minutes','active',1,0.4,38.16,48,'2026-01-04 18:14:39'),(85,2,'NA','NA','NA','down',0,0,0,0,'2026-01-04 18:14:42'),(86,1,'Red Hat Enterprise Linux 9.7 (Plow)','bhyve','up 5 hours, 58 minutes','active',1,1,38.19,48,'2026-01-04 18:14:56'),(87,2,'Red Hat Enterprise Linux 9.7 (Plow)','bhyve','up 0 minutes','active',1,22.6,24.7,51,'2026-01-04 18:14:57'),(88,1,'Red Hat Enterprise Linux 9.7 (Plow)','bhyve','up 5 hours, 58 minutes','active',1,3,38,48,'2026-01-04 18:15:10'),(89,2,'Red Hat Enterprise Linux 9.7 (Plow)','bhyve','up 0 minutes','active',1,8.3,38.89,51,'2026-01-04 18:15:12'),(90,1,'Red Hat Enterprise Linux 9.7 (Plow)','bhyve','up 5 hours, 58 minutes','active',1,1.1,38.06,48,'2026-01-04 18:15:24'),(91,2,'Red Hat Enterprise Linux 9.7 (Plow)','bhyve','up 0 minutes','active',1,9.1,37.2,51,'2026-01-04 18:15:25'),(92,3,'Red Hat Enterprise Linux 9.7 (Plow)','bhyve','up 5 hours, 58 minutes','active',1,0,89.07,51,'2026-01-04 18:15:27'),(93,1,'Red Hat Enterprise Linux 9.7 (Plow)','bhyve','up 5 hours, 59 minutes','active',1,2.2,38.15,48,'2026-01-04 18:15:39'),(94,2,'Red Hat Enterprise Linux 9.7 (Plow)','bhyve','up 1 minute','active',1,5.4,36.53,51,'2026-01-04 18:15:41'),(95,3,'Red Hat Enterprise Linux 9.7 (Plow)','bhyve','up 5 hours, 58 minutes','active',1,12.5,88.78,51,'2026-01-04 18:15:42'),(96,1,'Red Hat Enterprise Linux 9.7 (Plow)','bhyve','up 5 hours, 59 minutes','active',1,1.1,38.14,48,'2026-01-04 18:15:55'),(97,2,'Red Hat Enterprise Linux 9.7 (Plow)','bhyve','up 1 minute','active',1,0,37.02,51,'2026-01-04 18:15:57'),(98,3,'Red Hat Enterprise Linux 9.7 (Plow)','bhyve','up 5 hours, 59 minutes','active',1,5.6,88.78,51,'2026-01-04 18:15:58'),(99,1,'Red Hat Enterprise Linux 9.7 (Plow)','bhyve','up 5 hours, 59 minutes','active',1,1.2,38.6,48,'2026-01-04 18:16:10'),(100,2,'Red Hat Enterprise Linux 9.7 (Plow)','bhyve','up 1 minute','active',1,2.9,36.59,51,'2026-01-04 18:16:12'),(101,3,'Red Hat Enterprise Linux 9.7 (Plow)','bhyve','up 5 hours, 59 minutes','active',1,8.3,88.54,51,'2026-01-04 18:16:14'),(102,1,'Red Hat Enterprise Linux 9.7 (Plow)','bhyve','up 5 hours, 59 minutes','active',1,0.4,38.3,48,'2026-01-04 18:16:25'),(103,2,'Red Hat Enterprise Linux 9.7 (Plow)','bhyve','up 1 minute','active',1,2.9,37.35,51,'2026-01-04 18:16:27'),(104,3,'Red Hat Enterprise Linux 9.7 (Plow)','bhyve','up 5 hours, 59 minutes','active',1,0,88.68,51,'2026-01-04 18:16:28'),(105,1,'Red Hat Enterprise Linux 9.7 (Plow)','bhyve','up 6 hours','active',1,2.1,38.45,48,'2026-01-04 18:16:41'),(106,2,'Red Hat Enterprise Linux 9.7 (Plow)','bhyve','up 2 minutes','active',1,3.2,36.89,51,'2026-01-04 18:16:42'),(107,3,'Red Hat Enterprise Linux 9.7 (Plow)','bhyve','up 6 hours','active',1,5.7,88.54,51,'2026-01-04 18:16:44'),(108,1,'Red Hat Enterprise Linux 9.7 (Plow)','bhyve','up 6 hours','active',1,2.2,38.22,48,'2026-01-04 18:16:56'),(109,2,'Red Hat Enterprise Linux 9.7 (Plow)','bhyve','up 2 minutes','active',1,3,36.46,51,'2026-01-04 18:16:57'),(110,3,'Red Hat Enterprise Linux 9.7 (Plow)','bhyve','up 6 hours','active',1,5.9,88.43,51,'2026-01-04 18:16:58'),(111,1,'Red Hat Enterprise Linux 9.7 (Plow)','bhyve','up 6 hours','active',1,2.2,38.37,48,'2026-01-04 18:17:11'),(112,2,'Red Hat Enterprise Linux 9.7 (Plow)','bhyve','up 2 minutes','active',1,2.9,36.52,51,'2026-01-04 18:17:12'),(113,3,'Red Hat Enterprise Linux 9.7 (Plow)','bhyve','up 6 hours','active',1,5.6,88.58,51,'2026-01-04 18:17:14'),(114,1,'Red Hat Enterprise Linux 9.7 (Plow)','bhyve','up 6 hours','active',1,2.2,38.17,48,'2026-01-04 18:17:26'),(115,2,'Red Hat Enterprise Linux 9.7 (Plow)','bhyve','up 2 minutes','active',1,2.9,36.6,51,'2026-01-04 18:17:27'),(116,3,'Red Hat Enterprise Linux 9.7 (Plow)','bhyve','up 6 hours','active',1,2.9,88.51,51,'2026-01-04 18:17:29'),(117,1,'Red Hat Enterprise Linux 9.7 (Plow)','bhyve','up 6 hours, 1 minute','active',1,1.8,38.15,48,'2026-01-04 18:17:41'),(118,2,'Red Hat Enterprise Linux 9.7 (Plow)','bhyve','up 3 minutes','active',1,6.1,36.75,51,'2026-01-04 18:17:42'),(119,3,'Red Hat Enterprise Linux 9.7 (Plow)','bhyve','up 6 hours, 1 minute','active',1,6.1,88.56,51,'2026-01-04 18:17:43'),(120,1,'Red Hat Enterprise Linux 9.7 (Plow)','bhyve','up 6 hours, 1 minute','active',1,1.5,38.22,48,'2026-01-04 18:17:55'),(121,2,'Red Hat Enterprise Linux 9.7 (Plow)','bhyve','up 3 minutes','active',1,6.2,37.33,51,'2026-01-04 18:17:57'),(122,3,'Red Hat Enterprise Linux 9.7 (Plow)','bhyve','up 6 hours, 1 minute','active',1,9.1,88.52,51,'2026-01-04 18:17:58'),(123,1,'Red Hat Enterprise Linux 9.7 (Plow)','bhyve','up 6 hours, 1 minute','active',1,1.9,38.74,48,'2026-01-04 18:18:11'),(124,2,'Red Hat Enterprise Linux 9.7 (Plow)','bhyve','up 3 minutes','active',1,0,36.23,51,'2026-01-04 18:18:12'),(125,3,'Red Hat Enterprise Linux 9.7 (Plow)','bhyve','up 6 hours, 1 minute','active',1,5.7,88.57,51,'2026-01-04 18:18:14'),(126,1,'Red Hat Enterprise Linux 9.7 (Plow)','bhyve','up 6 hours, 1 minute','active',1,1.5,37.85,48,'2026-01-04 18:18:26'),(127,2,'Red Hat Enterprise Linux 9.7 (Plow)','bhyve','up 3 minutes','active',1,3,36.17,51,'2026-01-04 18:18:27'),(128,3,'Red Hat Enterprise Linux 9.7 (Plow)','bhyve','up 6 hours, 1 minute','active',1,11.4,88.45,51,'2026-01-04 18:18:29'),(129,1,'Red Hat Enterprise Linux 9.7 (Plow)','bhyve','up 6 hours, 2 minutes','active',1,1.8,38.39,48,'2026-01-04 18:18:41'),(130,2,'Red Hat Enterprise Linux 9.7 (Plow)','bhyve','up 4 minutes','active',1,3,35.87,51,'2026-01-04 18:18:42'),(131,3,'Red Hat Enterprise Linux 9.7 (Plow)','bhyve','up 6 hours, 2 minutes','active',1,5.7,88.66,51,'2026-01-04 18:18:44'),(132,1,'Red Hat Enterprise Linux 9.7 (Plow)','bhyve','up 6 hours, 2 minutes','active',1,0.9,38.14,48,'2026-01-04 18:18:56'),(133,2,'Red Hat Enterprise Linux 9.7 (Plow)','bhyve','up 4 minutes','active',1,3.1,36.57,51,'2026-01-04 18:18:57'),(134,3,'Red Hat Enterprise Linux 9.7 (Plow)','bhyve','up 6 hours, 2 minutes','active',1,16.7,89.75,51,'2026-01-04 18:18:59'),(135,1,'Red Hat Enterprise Linux 9.7 (Plow)','bhyve','up 6 hours, 2 minutes','active',1,1.1,38.63,48,'2026-01-04 18:19:11'),(136,2,'Red Hat Enterprise Linux 9.7 (Plow)','bhyve','up 4 minutes','active',1,2.9,35.78,51,'2026-01-04 18:19:12'),(137,3,'Red Hat Enterprise Linux 9.7 (Plow)','bhyve','up 6 hours, 2 minutes','active',1,5.9,88.79,51,'2026-01-04 18:19:13'),(138,1,'Red Hat Enterprise Linux 9.7 (Plow)','bhyve','up 6 hours, 2 minutes','active',1,1.8,37.42,48,'2026-01-04 18:19:26'),(139,2,'Red Hat Enterprise Linux 9.7 (Plow)','bhyve','up 4 minutes','active',1,10.7,36.42,51,'2026-01-04 18:19:27'),(140,3,'Red Hat Enterprise Linux 9.7 (Plow)','bhyve','up 6 hours, 2 minutes','active',1,18.8,89.25,51,'2026-01-04 18:19:29'),(141,1,'Red Hat Enterprise Linux 9.7 (Plow)','bhyve','up 6 hours, 3 minutes','active',1,1,38.45,48,'2026-01-04 18:19:40'),(142,2,'Red Hat Enterprise Linux 9.7 (Plow)','bhyve','up 5 minutes','active',1,2.9,35.8,51,'2026-01-04 18:19:42'),(143,3,'Red Hat Enterprise Linux 9.7 (Plow)','bhyve','up 6 hours, 3 minutes','active',1,10,88.47,51,'2026-01-04 18:19:43'),(144,1,'Red Hat Enterprise Linux 9.7 (Plow)','bhyve','up 6 hours, 3 minutes','active',1,1.6,37.36,48,'2026-01-04 18:19:55'),(145,2,'Red Hat Enterprise Linux 9.7 (Plow)','bhyve','up 5 minutes','active',1,0,36.41,51,'2026-01-04 18:19:57'),(146,3,'Red Hat Enterprise Linux 9.7 (Plow)','bhyve','up 6 hours, 3 minutes','active',1,2.9,88.31,51,'2026-01-04 18:19:58'),(147,1,'Red Hat Enterprise Linux 9.7 (Plow)','bhyve','up 6 hours, 3 minutes','active',1,1.8,37.51,48,'2026-01-04 18:20:10'),(148,2,'Red Hat Enterprise Linux 9.7 (Plow)','bhyve','up 5 minutes','active',1,6.5,36.24,51,'2026-01-04 18:20:12'),(149,3,'Red Hat Enterprise Linux 9.7 (Plow)','bhyve','up 6 hours, 3 minutes','active',1,9.4,88.85,51,'2026-01-04 18:20:13'),(150,1,'Red Hat Enterprise Linux 9.7 (Plow)','bhyve','up 6 hours, 3 minutes','active',1,1.3,37.47,48,'2026-01-04 18:20:25'),(151,2,'Red Hat Enterprise Linux 9.7 (Plow)','bhyve','up 5 minutes','active',1,2.9,36.6,51,'2026-01-04 18:20:26'),(152,3,'Red Hat Enterprise Linux 9.7 (Plow)','bhyve','up 6 hours, 3 minutes','active',1,5.3,88.48,51,'2026-01-04 18:20:28'),(153,1,'Red Hat Enterprise Linux 9.7 (Plow)','bhyve','up 6 hours, 4 minutes','active',1,0.7,37.61,48,'2026-01-04 18:20:39'),(154,2,'Red Hat Enterprise Linux 9.7 (Plow)','bhyve','up 6 minutes','active',1,0,35.94,51,'2026-01-04 18:20:41'),(155,3,'Red Hat Enterprise Linux 9.7 (Plow)','bhyve','up 6 hours, 4 minutes','active',1,8.3,88.41,51,'2026-01-04 18:20:43'),(156,1,'Red Hat Enterprise Linux 9.7 (Plow)','bhyve','up 6 hours, 4 minutes','active',1,1.4,37.5,48,'2026-01-04 18:20:55'),(157,2,'Red Hat Enterprise Linux 9.7 (Plow)','bhyve','up 6 minutes','active',1,0,36.96,51,'2026-01-04 18:20:57'),(158,3,'Red Hat Enterprise Linux 9.7 (Plow)','bhyve','up 6 hours, 4 minutes','active',1,22.6,88.45,51,'2026-01-04 18:20:58'),(159,1,'Red Hat Enterprise Linux 9.7 (Plow)','bhyve','up 6 hours, 4 minutes','active',1,4,37.74,48,'2026-01-04 18:21:10'),(160,2,'Red Hat Enterprise Linux 9.7 (Plow)','bhyve','up 6 minutes','active',1,2.9,35.87,51,'2026-01-04 18:21:12'),(161,3,'Red Hat Enterprise Linux 9.7 (Plow)','bhyve','up 6 hours, 4 minutes','active',1,2.9,88.66,51,'2026-01-04 18:21:13'),(162,1,'Red Hat Enterprise Linux 9.7 (Plow)','bhyve','up 6 hours, 4 minutes','active',1,1.1,37.72,48,'2026-01-04 18:21:25'),(163,2,'Red Hat Enterprise Linux 9.7 (Plow)','bhyve','up 6 minutes','active',1,3.1,36.41,51,'2026-01-04 18:21:27'),(164,3,'Red Hat Enterprise Linux 9.7 (Plow)','bhyve','up 6 hours, 4 minutes','active',1,7.9,88.58,51,'2026-01-04 18:21:28'),(165,1,'Red Hat Enterprise Linux 9.7 (Plow)','bhyve','up 6 hours, 5 minutes','active',1,1,38.48,48,'2026-01-04 18:21:41'),(166,2,'Red Hat Enterprise Linux 9.7 (Plow)','bhyve','up 7 minutes','active',1,2.8,36.02,51,'2026-01-04 18:21:42'),(167,3,'Red Hat Enterprise Linux 9.7 (Plow)','bhyve','up 6 hours, 5 minutes','active',1,5.7,88.51,51,'2026-01-04 18:21:44'),(168,1,'Red Hat Enterprise Linux 9.7 (Plow)','bhyve','up 6 hours, 5 minutes','active',1,1.1,37.52,48,'2026-01-04 18:21:55'),(169,2,'Red Hat Enterprise Linux 9.7 (Plow)','bhyve','up 7 minutes','active',1,12.1,36.51,51,'2026-01-04 18:21:56'),(170,3,'Red Hat Enterprise Linux 9.7 (Plow)','bhyve','up 6 hours, 5 minutes','active',1,5.7,88.6,51,'2026-01-04 18:21:57'),(171,1,'Red Hat Enterprise Linux 9.7 (Plow)','bhyve','up 6 hours, 5 minutes','active',1,1.4,37.81,48,'2026-01-04 18:22:09'),(172,2,'Red Hat Enterprise Linux 9.7 (Plow)','bhyve','up 7 minutes','active',1,5.9,35.97,51,'2026-01-04 18:22:10'),(173,3,'Red Hat Enterprise Linux 9.7 (Plow)','bhyve','up 6 hours, 5 minutes','active',1,2.9,88.54,51,'2026-01-04 18:22:12'),(174,1,'Red Hat Enterprise Linux 9.7 (Plow)','bhyve','up 6 hours, 5 minutes','active',1,1.4,37.74,48,'2026-01-04 18:22:23'),(175,2,'Red Hat Enterprise Linux 9.7 (Plow)','bhyve','up 7 minutes','active',1,2.9,35.84,51,'2026-01-04 18:22:25'),(176,3,'Red Hat Enterprise Linux 9.7 (Plow)','bhyve','up 6 hours, 5 minutes','active',1,2.9,88.76,51,'2026-01-04 18:22:26'),(177,1,'Red Hat Enterprise Linux 9.7 (Plow)','bhyve','up 6 hours, 6 minutes','active',1,0.8,37.68,48,'2026-01-04 18:22:37'),(178,2,'Red Hat Enterprise Linux 9.7 (Plow)','bhyve','up 7 minutes','active',1,3,35.87,51,'2026-01-04 18:22:38'),(179,3,'Red Hat Enterprise Linux 9.7 (Plow)','bhyve','up 6 hours, 5 minutes','active',1,5.9,88.6,51,'2026-01-04 18:22:41'),(180,1,'Red Hat Enterprise Linux 9.7 (Plow)','bhyve','up 6 hours, 6 minutes','active',1,2.6,37.56,48,'2026-01-04 18:22:52'),(181,2,'Red Hat Enterprise Linux 9.7 (Plow)','bhyve','up 8 minutes','active',1,3.3,36.32,51,'2026-01-04 18:22:54');
/*!40000 ALTER TABLE `server_metrics` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `servers`
--

DROP TABLE IF EXISTS `servers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `servers` (
  `id` int NOT NULL AUTO_INCREMENT,
  `hostname` varchar(255) DEFAULT NULL,
  `ip_address` varchar(50) DEFAULT NULL,
  `ssh_user` varchar(50) DEFAULT NULL,
  `ssh_port` int DEFAULT '22',
  `ssh_password` varchar(255) DEFAULT NULL,
  `enabled` tinyint(1) DEFAULT '1',
  `added_on` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `servers`
--

LOCK TABLES `servers` WRITE;
/*!40000 ALTER TABLE `servers` DISABLE KEYS */;
INSERT INTO `servers` VALUES (1,'master','192.168.1.46','root',22,'dell',1,'2026-01-04 17:45:06'),(2,'worker1','192.168.1.47','root',22,'dell',1,'2026-01-04 18:13:20'),(3,'worker2','192.168.1.48','root',22,'dell',1,'2026-01-04 18:15:16');
/*!40000 ALTER TABLE `servers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','user') NOT NULL DEFAULT 'user',
  `id` int NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`username`),
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES ('admin','095f26e6c7c467c5ecce3205a0dc90f6dc4868e8ddfe7a21829e91d47e386676','admin',1);
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-01-04 18:22:54
