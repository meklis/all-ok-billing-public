-- MySQL dump 10.13  Distrib 8.0.27, for Linux (x86_64)
--
-- Host: localhost    Database: gwPayments
-- ------------------------------------------------------
-- Server version	8.0.27-0ubuntu0.20.04.1

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

--
-- Table structure for table `b24_cancel`
--

DROP TABLE IF EXISTS `b24_cancel`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `b24_cancel` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `time` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `payID` varchar(64) DEFAULT NULL,
  `transactionID` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `b24_cancel`
--

LOCK TABLES `b24_cancel` WRITE;
/*!40000 ALTER TABLE `b24_cancel` DISABLE KEYS */;
/*!40000 ALTER TABLE `b24_cancel` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `b24_check`
--

DROP TABLE IF EXISTS `b24_check`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `b24_check` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `time` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `transactionId` bigint unsigned DEFAULT NULL,
  `payElementId` int unsigned DEFAULT NULL,
  `account` int unsigned DEFAULT NULL,
  `status` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `b24_check`
--

LOCK TABLES `b24_check` WRITE;
/*!40000 ALTER TABLE `b24_check` DISABLE KEYS */;
/*!40000 ALTER TABLE `b24_check` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `b24_payment`
--

DROP TABLE IF EXISTS `b24_payment`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `b24_payment` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `time` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `account` int unsigned DEFAULT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `payID` varchar(64) DEFAULT NULL,
  `payTimestamp` timestamp NULL DEFAULT NULL,
  `transactionID` bigint unsigned DEFAULT NULL,
  `terminalId` int unsigned DEFAULT NULL,
  `payElementID` int unsigned DEFAULT NULL,
  `status` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `payID` (`payID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `b24_payment`
--

LOCK TABLES `b24_payment` WRITE;
/*!40000 ALTER TABLE `b24_payment` DISABLE KEYS */;
/*!40000 ALTER TABLE `b24_payment` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `city24_cancel`
--

DROP TABLE IF EXISTS `city24_cancel`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `city24_cancel` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `serviceId` int unsigned DEFAULT NULL,
  `payment` int unsigned DEFAULT NULL,
  `time` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `payment` (`payment`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `city24_cancel`
--

LOCK TABLES `city24_cancel` WRITE;
/*!40000 ALTER TABLE `city24_cancel` DISABLE KEYS */;
/*!40000 ALTER TABLE `city24_cancel` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `city24_check`
--

DROP TABLE IF EXISTS `city24_check`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `city24_check` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `serviceId` int DEFAULT NULL,
  `account` int unsigned DEFAULT NULL,
  `response` varchar(255) DEFAULT NULL,
  `time` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=74 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `city24_check`
--

LOCK TABLES `city24_check` WRITE;
/*!40000 ALTER TABLE `city24_check` DISABLE KEYS */;
/*!40000 ALTER TABLE `city24_check` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `city24_confirm`
--

DROP TABLE IF EXISTS `city24_confirm`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `city24_confirm` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `serviceId` int DEFAULT NULL,
  `payment` int unsigned DEFAULT NULL,
  `time` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `status` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `eq_confirm_ibfk_1` (`payment`) USING BTREE,
  KEY `status` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `city24_confirm`
--

LOCK TABLES `city24_confirm` WRITE;
/*!40000 ALTER TABLE `city24_confirm` DISABLE KEYS */;
/*!40000 ALTER TABLE `city24_confirm` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `city24_payments`
--

DROP TABLE IF EXISTS `city24_payments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `city24_payments` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `account` int unsigned DEFAULT NULL,
  `serviceId` int unsigned DEFAULT NULL,
  `orderId` bigint unsigned DEFAULT NULL,
  `amount` decimal(10,2) unsigned DEFAULT NULL,
  `time` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `account` (`account`)
) ENGINE=InnoDB AUTO_INCREMENT=606 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `city24_payments`
--

LOCK TABLES `city24_payments` WRITE;
/*!40000 ALTER TABLE `city24_payments` DISABLE KEYS */;
/*!40000 ALTER TABLE `city24_payments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ep_cancel`
--

DROP TABLE IF EXISTS `ep_cancel`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ep_cancel` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `serviceId` int unsigned DEFAULT NULL,
  `payment` int unsigned DEFAULT NULL,
  `time` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `payment` (`payment`),
  CONSTRAINT `ep_cancel_ibfk_1` FOREIGN KEY (`payment`) REFERENCES `ep_payments` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ep_cancel`
--

LOCK TABLES `ep_cancel` WRITE;
/*!40000 ALTER TABLE `ep_cancel` DISABLE KEYS */;
/*!40000 ALTER TABLE `ep_cancel` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ep_check`
--

DROP TABLE IF EXISTS `ep_check`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ep_check` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `serviceId` int DEFAULT NULL,
  `account` int unsigned DEFAULT NULL,
  `response` varchar(255) DEFAULT NULL,
  `time` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9033 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ep_check`
--

LOCK TABLES `ep_check` WRITE;
/*!40000 ALTER TABLE `ep_check` DISABLE KEYS */;
/*!40000 ALTER TABLE `ep_check` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ep_confirm`
--

DROP TABLE IF EXISTS `ep_confirm`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ep_confirm` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `serviceId` int DEFAULT NULL,
  `payment` int unsigned DEFAULT NULL,
  `time` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `status` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `eq_confirm_ibfk_1` (`payment`) USING BTREE,
  KEY `status` (`status`),
  CONSTRAINT `ep_confirm_ibfk_1` FOREIGN KEY (`payment`) REFERENCES `ep_payments` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5751 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ep_confirm`
--

LOCK TABLES `ep_confirm` WRITE;
/*!40000 ALTER TABLE `ep_confirm` DISABLE KEYS */;
/*!40000 ALTER TABLE `ep_confirm` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ep_payments`
--

DROP TABLE IF EXISTS `ep_payments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ep_payments` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `account` int unsigned DEFAULT NULL,
  `serviceId` int unsigned DEFAULT NULL,
  `orderId` bigint unsigned DEFAULT NULL,
  `amount` decimal(10,2) unsigned DEFAULT NULL,
  `time` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `account` (`account`)
) ENGINE=InnoDB AUTO_INCREMENT=5716 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ep_payments`
--

LOCK TABLES `ep_payments` WRITE;
/*!40000 ALTER TABLE `ep_payments` DISABLE KEYS */;
/*!40000 ALTER TABLE `ep_payments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ibox_cancel`
--

DROP TABLE IF EXISTS `ibox_cancel`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ibox_cancel` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `payment` int unsigned DEFAULT NULL,
  `time` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `payment` (`payment`),
  CONSTRAINT `ibox_cancel_ibfk_1` FOREIGN KEY (`payment`) REFERENCES `ibox_payment` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ibox_cancel`
--

LOCK TABLES `ibox_cancel` WRITE;
/*!40000 ALTER TABLE `ibox_cancel` DISABLE KEYS */;
/*!40000 ALTER TABLE `ibox_cancel` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ibox_check`
--

DROP TABLE IF EXISTS `ibox_check`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ibox_check` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `time` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `transactionId` bigint unsigned DEFAULT NULL,
  `account` int unsigned DEFAULT NULL,
  `status` int DEFAULT NULL,
  `amount` decimal(13,2) DEFAULT NULL,
  `payType` int DEFAULT NULL,
  `provider` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1275 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ibox_check`
--

LOCK TABLES `ibox_check` WRITE;
/*!40000 ALTER TABLE `ibox_check` DISABLE KEYS */;
/*!40000 ALTER TABLE `ibox_check` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ibox_payment`
--

DROP TABLE IF EXISTS `ibox_payment`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ibox_payment` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `time` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `account` int unsigned DEFAULT NULL,
  `amount` decimal(13,2) DEFAULT NULL,
  `transactionID` bigint unsigned DEFAULT NULL,
  `status` int DEFAULT NULL,
  `provider` int unsigned DEFAULT NULL,
  `payType` int DEFAULT NULL,
  `termId` int DEFAULT NULL,
  `agentId` int DEFAULT NULL,
  `transactionDate` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=940 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ibox_payment`
--

LOCK TABLES `ibox_payment` WRITE;
/*!40000 ALTER TABLE `ibox_payment` DISABLE KEYS */;
/*!40000 ALTER TABLE `ibox_payment` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `time_cancel`
--

DROP TABLE IF EXISTS `time_cancel`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `time_cancel` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `time` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `payID` varchar(64) DEFAULT NULL,
  `transactionID` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `time_cancel`
--

LOCK TABLES `time_cancel` WRITE;
/*!40000 ALTER TABLE `time_cancel` DISABLE KEYS */;
/*!40000 ALTER TABLE `time_cancel` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `time_check`
--

DROP TABLE IF EXISTS `time_check`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `time_check` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `time` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `transactionId` bigint unsigned DEFAULT NULL,
  `account` int unsigned DEFAULT NULL,
  `status` int DEFAULT NULL,
  `provider` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `time_check`
--

LOCK TABLES `time_check` WRITE;
/*!40000 ALTER TABLE `time_check` DISABLE KEYS */;
/*!40000 ALTER TABLE `time_check` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `time_payment`
--

DROP TABLE IF EXISTS `time_payment`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `time_payment` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `time` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `account` int unsigned DEFAULT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `payTimestamp` timestamp NULL DEFAULT NULL,
  `transactionID` bigint unsigned DEFAULT NULL,
  `status` int DEFAULT NULL,
  `provider` int unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `time_payment`
--

LOCK TABLES `time_payment` WRITE;
/*!40000 ALTER TABLE `time_payment` DISABLE KEYS */;
/*!40000 ALTER TABLE `time_payment` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `twoClick_check`
--

DROP TABLE IF EXISTS `twoClick_check`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `twoClick_check` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `service_id` int unsigned NOT NULL,
  `account` int unsigned NOT NULL,
  `payment_id` varchar(40) NOT NULL,
  `terminal` varchar(40) NOT NULL,
  `status` varchar(160) NOT NULL,
  `status_code` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `payment_id` (`payment_id`)
) ENGINE=InnoDB AUTO_INCREMENT=488 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `twoClick_check`
--

LOCK TABLES `twoClick_check` WRITE;
/*!40000 ALTER TABLE `twoClick_check` DISABLE KEYS */;
/*!40000 ALTER TABLE `twoClick_check` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `twoClick_payment`
--

DROP TABLE IF EXISTS `twoClick_payment`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `twoClick_payment` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `service_id` int unsigned NOT NULL,
  `account` int unsigned NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `receipt_num` int unsigned NOT NULL,
  `payment_id` varchar(40) NOT NULL,
  `terminal` varchar(40) NOT NULL,
  `canceled` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `payment_id` (`payment_id`)
) ENGINE=InnoDB AUTO_INCREMENT=361 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `twoClick_payment`
--

LOCK TABLES `twoClick_payment` WRITE;
/*!40000 ALTER TABLE `twoClick_payment` DISABLE KEYS */;
/*!40000 ALTER TABLE `twoClick_payment` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `twoClick_status`
--

DROP TABLE IF EXISTS `twoClick_status`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `twoClick_status` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `service_id` int unsigned NOT NULL,
  `payment_id` varchar(40) NOT NULL,
  `pay_local_id` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `twoClick_status`
--

LOCK TABLES `twoClick_status` WRITE;
/*!40000 ALTER TABLE `twoClick_status` DISABLE KEYS */;
/*!40000 ALTER TABLE `twoClick_status` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping routines for database 'gwPayments'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2022-01-23 14:24:16
