-- MySQL dump 10.13  Distrib 8.0.27, for Linux (x86_64)
--
-- Host: localhost    Database: service
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
-- Temporary view structure for view `addr`
--

DROP TABLE IF EXISTS `addr`;
/*!50001 DROP VIEW IF EXISTS `addr`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `addr` AS SELECT 
 1 AS `id`,
 1 AS `city`,
 1 AS `street`,
 1 AS `house`,
 1 AS `full_addr`,
 1 AS `group_id`,
 1 AS `group_name`*/;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `addr_cities`
--

DROP TABLE IF EXISTS `addr_cities`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `addr_cities` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(150) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `addr_cities`
--

LOCK TABLES `addr_cities` WRITE;
/*!40000 ALTER TABLE `addr_cities` DISABLE KEYS */;
INSERT INTO `addr_cities` VALUES (4,'1.с. Крюковщина'),(5,'5.c. Софиевская Борщаговка'),(7,'3.м. Київ');
/*!40000 ALTER TABLE `addr_cities` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `addr_groups`
--

DROP TABLE IF EXISTS `addr_groups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `addr_groups` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `description` varchar(255) NOT NULL,
  `reaction_factor` decimal(8,2) NOT NULL DEFAULT '1.00',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb3 ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `addr_groups`
--

LOCK TABLES `addr_groups` WRITE;
/*!40000 ALTER TABLE `addr_groups` DISABLE KEYS */;
INSERT INTO `addr_groups` VALUES (-1,'Без группы','2019-04-24 17:22:57','Без группы',1.00);
/*!40000 ALTER TABLE `addr_groups` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `addr_houses`
--

DROP TABLE IF EXISTS `addr_houses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `addr_houses` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) DEFAULT NULL,
  `street` int unsigned DEFAULT NULL,
  `entrances` tinyint DEFAULT NULL,
  `floors` tinyint DEFAULT NULL,
  `apartments` int DEFAULT NULL,
  `descr` varchar(150) DEFAULT NULL,
  `group_id` int NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`,`street`),
  KEY `house_street` (`street`),
  KEY `group_id` (`group_id`),
  CONSTRAINT `addr_houses_ibfk_1` FOREIGN KEY (`group_id`) REFERENCES `addr_groups` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `house_street` FOREIGN KEY (`street`) REFERENCES `addr_streets` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=650 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `addr_houses`
--

LOCK TABLES `addr_houses` WRITE;
/*!40000 ALTER TABLE `addr_houses` DISABLE KEYS */;
INSERT INTO `addr_houses` VALUES (33,'2A',10,3,1,1,NULL,-1),(34,'2Б',10,3,1,1,NULL,-1),(35,'2В',10,3,1,1,NULL,-1),(36,'2Г',10,4,1,1,NULL,-1),(37,'1А',11,4,1,1,NULL,-1),(38,'1Б',11,4,1,1,NULL,-1),(39,'1В',11,4,1,1,NULL,-1),(40,'1Г',11,5,1,1,NULL,-1),(41,'1Д',11,5,1,1,NULL,-1),(42,'2А',11,3,1,1,NULL,-1),(43,'2Б',11,3,1,1,NULL,-1),(44,'2В',11,3,1,1,NULL,-1),(45,'101',12,1,8,1,NULL,-1),(46,'2Г',11,4,1,1,NULL,-1),(49,'56',14,1,1,1,NULL,-1),(50,'13б',15,1,1,1,NULL,-1),(51,'1Г',10,1,1,1,NULL,-1),(69,'74',19,1,1,1,NULL,-1),(70,'10а',20,1,1,1,NULL,-1),(71,'10г',20,1,1,1,NULL,-1),(72,'10б',21,1,1,1,NULL,-1),(73,'20',21,1,1,1,NULL,-1),(74,'20а',21,1,1,1,NULL,-1),(75,'20б',21,1,1,1,NULL,-1),(76,'20в',21,1,1,1,NULL,-1),(77,'20г',21,1,1,1,NULL,-1),(78,'86',22,1,1,1,NULL,-1),(79,'55б',23,1,1,1,NULL,-1),(80,'55в',23,1,1,1,NULL,-1),(81,'44',24,1,1,1,NULL,-1),(82,'50',24,1,1,1,NULL,-1),(83,'55',24,1,1,1,NULL,-1),(84,'61',24,1,1,1,NULL,-1),(85,'13',15,1,1,1,NULL,-1),(86,'13а',15,1,1,1,NULL,-1),(88,'13в',15,1,1,1,NULL,-1),(89,'13г',15,1,1,1,NULL,-1),(90,'5',15,1,1,1,NULL,-1),(91,'5а',15,1,1,1,NULL,-1),(92,'5б',15,1,1,1,NULL,-1),(93,'5в',15,1,1,1,NULL,-1),(94,'7',15,1,1,1,NULL,-1),(95,'7а',15,1,1,1,NULL,-1),(96,'7в',15,1,1,1,NULL,-1),(97,'9',15,1,1,1,NULL,-1),(98,'9а',15,1,1,1,NULL,-1),(99,'9б',15,1,1,1,NULL,-1),(100,'9в',15,1,1,1,NULL,-1),(101,'9г',15,1,1,1,NULL,-1),(102,'9д',15,1,1,1,NULL,-1),(103,'145',27,1,1,1,'',-1),(104,'165',27,1,1,1,NULL,-1),(105,'10д',26,1,1,1,NULL,-1),(106,'74',26,1,1,1,NULL,-1),(107,'34а',28,1,1,1,NULL,-1),(108,'1',29,1,1,1,NULL,-1),(109,'6а',30,1,1,1,NULL,-1),(110,'2',31,1,1,1,NULL,-1),(111,'11',15,1,1,1,NULL,-1),(112,'79',12,1,1,1,NULL,-1),(113,'81',12,1,1,1,NULL,-1),(114,'1',32,1,1,1,NULL,-1),(115,'18/2',33,1,1,1,NULL,-1),(116,'7',29,1,1,1,'1',-1),(118,'9',35,1,1,1,'1',-1),(637,'24/83',26,0,0,0,NULL,-1),(639,'5',76,0,0,0,NULL,-1),(640,'7',76,0,0,0,NULL,-1);
/*!40000 ALTER TABLE `addr_houses` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `addr_streets`
--

DROP TABLE IF EXISTS `addr_streets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `addr_streets` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(150) DEFAULT NULL,
  `city` int unsigned DEFAULT NULL,
  `show` bit(1) DEFAULT b'1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`,`city`),
  KEY `street_city` (`city`),
  CONSTRAINT `street_city` FOREIGN KEY (`city`) REFERENCES `addr_cities` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=78 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `addr_streets`
--

LOCK TABLES `addr_streets` WRITE;
/*!40000 ALTER TABLE `addr_streets` DISABLE KEYS */;
INSERT INTO `addr_streets` VALUES (10,'Европейская улица',4,_binary ''),(11,'Жулянская улица',4,_binary ''),(12,'Кошова',5,_binary ''),(14,'проспект Валерія Лобановського ',7,_binary ''),(15,'Яблунева ',5,_binary ''),(19,'Волошковая',5,_binary ''),(20,'Ленина',5,_binary ''),(21,'Оксамитова',5,_binary ''),(22,'Шалимова',5,_binary ''),(23,'Шевченка',5,_binary ''),(24,'Счастливая',5,_binary ''),(26,'просп. Героев Небесной Сотни',5,_binary ''),(27,'ул. Радужная',5,_binary ''),(28,'Зодчих',7,_binary ''),(29,'Щастя',4,_binary ''),(30,'Берковецька',7,_binary ''),(31,'Бударіна',7,_binary ''),(32,'ул. Петровская (ЖК Барселона)',5,_binary ''),(33,'ул. Небесной Сотни',5,_binary ''),(35,'Леонтовича',7,_binary ''),(76,'Леменівська',5,_binary '');
/*!40000 ALTER TABLE `addr_streets` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bill_prices`
--

DROP TABLE IF EXISTS `bill_prices`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `bill_prices` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) DEFAULT NULL,
  `price_day` decimal(10,2) NOT NULL DEFAULT '0.00',
  `price_month` decimal(10,2) NOT NULL DEFAULT '0.00',
  `recalc_time` enum('day','month') NOT NULL DEFAULT 'day',
  `show` bit(1) NOT NULL DEFAULT b'1',
  `speed` int DEFAULT '0',
  `provider` int unsigned DEFAULT NULL,
  `purpose_of_payment` varchar(255) DEFAULT NULL,
  `days_to_disable` int NOT NULL DEFAULT '0',
  `sms_name` varchar(255) DEFAULT NULL,
  `work_type` enum('inet','question','trinity','iptv','no_action') NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_price_provider` (`provider`),
  CONSTRAINT `fk_price_provider` FOREIGN KEY (`provider`) REFERENCES `providers` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=151 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bill_prices`
--

LOCK TABLES `bill_prices` WRITE;
/*!40000 ALTER TABLE `bill_prices` DISABLE KEYS */;
INSERT INTO `bill_prices` VALUES (42,'Швидкiсть 30',4.00,120.00,'day',_binary '',35,1026,'Оплата за телекомунікаційнi послуги',0,'iнтернет','inet'),(43,'Швидкiсть 50',4.66,140.00,'day',_binary '',55,1026,'Оплата за телекомунікаційнi послуги',0,'iнтернет','inet'),(44,'Швидкiсть 100',6.00,180.00,'day',_binary '',105,1026,'Оплата за телекомунікаційнi послуги',0,'iнтернет','inet'),(45,'Швидкість 500',8.33,250.00,'day',_binary '',505,1026,'Оплата за телекомунікаційнi послуги',0,'iнтернет','inet'),(46,'Біла Ip Адреса M',1.00,30.00,'month',_binary '',0,1026,'Оплата за телекомунікаційнi послуги',0,NULL,'no_action'),(50,'Инет Бесплатный',0.00,0.00,'day',_binary '',0,1026,'Оплата за телекомунікаційнi послуги',0,'iнтернет','inet'),(98,'Швидкість 30 М',4.00,120.00,'month',_binary '',30,1026,'Оплата за телекомунікаційні послуги',0,'інтернет','inet'),(99,'Швидкість 50 М ',4.66,140.00,'month',_binary '',50,1026,'Оплата за телекомунікаційні послуги',0,'інтернет','inet'),(100,'Швидкість 100 М ',6.00,180.00,'month',_binary '',100,1026,'Оплата за телекомунікаційні послуги',0,'інтернет','inet'),(101,'Швидкість 500 М ',8.33,250.00,'month',_binary '',500,1026,'Оплата за телекомунікаційні послуги',0,'інтернет','inet');
/*!40000 ALTER TABLE `bill_prices` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `billing_charge_off`
--

DROP TABLE IF EXISTS `billing_charge_off`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `billing_charge_off` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `agreement` int NOT NULL,
  `price` decimal(13,3) NOT NULL,
  `type` text,
  `balance` decimal(10,2) DEFAULT NULL,
  `price_ids` json DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `billing_charge_off`
--

LOCK TABLES `billing_charge_off` WRITE;
/*!40000 ALTER TABLE `billing_charge_off` DISABLE KEYS */;
/*!40000 ALTER TABLE `billing_charge_off` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `client_auth_by_phone`
--

DROP TABLE IF EXISTS `client_auth_by_phone`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `client_auth_by_phone` (
  `id` int NOT NULL AUTO_INCREMENT,
  `phone` varchar(60) NOT NULL,
  `code` varchar(4) NOT NULL,
  `created_at` datetime NOT NULL ON UPDATE CURRENT_TIMESTAMP,
  `expired_at` datetime NOT NULL,
  `code_confirmed` tinyint NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `client_auth_by_phone`
--

LOCK TABLES `client_auth_by_phone` WRITE;
/*!40000 ALTER TABLE `client_auth_by_phone` DISABLE KEYS */;
/*!40000 ALTER TABLE `client_auth_by_phone` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Temporary view structure for view `client_balances_v`
--

DROP TABLE IF EXISTS `client_balances_v`;
/*!50001 DROP VIEW IF EXISTS `client_balances_v`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `client_balances_v` AS SELECT 
 1 AS `id`,
 1 AS `agreement`,
 1 AS `balance`*/;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `client_contacts`
--

DROP TABLE IF EXISTS `client_contacts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `client_contacts` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `agreement_id` int unsigned NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `type` enum('PHONE','EMAIL','TELEGRAM','VIBER') NOT NULL,
  `value` varchar(200) NOT NULL,
  `created_at` datetime NOT NULL ON UPDATE CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL ON UPDATE CURRENT_TIMESTAMP,
  `employee_id` int NOT NULL,
  `main` tinyint NOT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `uniq_agree_typ_val` (`agreement_id`,`type`,`value`) USING BTREE,
  CONSTRAINT `client_contacts_ibfk_1` FOREIGN KEY (`agreement_id`) REFERENCES `clients` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `client_contacts`
--

LOCK TABLES `client_contacts` WRITE;
/*!40000 ALTER TABLE `client_contacts` DISABLE KEYS */;
/*!40000 ALTER TABLE `client_contacts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `client_credit`
--

DROP TABLE IF EXISTS `client_credit`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `client_credit` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_employee` int unsigned NOT NULL,
  `client_id` int unsigned NOT NULL,
  `amount` decimal(13,2) NOT NULL,
  `days` int NOT NULL,
  `status` enum('OPEN','CANCEL','CLOSED','DIACTIVATED') NOT NULL,
  `closed_date` timestamp NULL DEFAULT NULL,
  `closed_employee` int unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `client_id` (`client_id`),
  KEY `closed_emplo_f2k22` (`closed_employee`) USING BTREE,
  KEY `status` (`status`),
  CONSTRAINT `client_credit_ibfk_1` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `client_credit`
--

LOCK TABLES `client_credit` WRITE;
/*!40000 ALTER TABLE `client_credit` DISABLE KEYS */;
/*!40000 ALTER TABLE `client_credit` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `client_disable_days`
--

DROP TABLE IF EXISTS `client_disable_days`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `client_disable_days` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_employee` int unsigned NOT NULL,
  `client` int unsigned NOT NULL,
  `days` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `client_id` (`client`),
  CONSTRAINT `client_disable_days_ibfk_1` FOREIGN KEY (`client`) REFERENCES `clients` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `client_disable_days`
--

LOCK TABLES `client_disable_days` WRITE;
/*!40000 ALTER TABLE `client_disable_days` DISABLE KEYS */;
/*!40000 ALTER TABLE `client_disable_days` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Temporary view structure for view `client_disable_days_last`
--

DROP TABLE IF EXISTS `client_disable_days_last`;
/*!50001 DROP VIEW IF EXISTS `client_disable_days_last`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `client_disable_days_last` AS SELECT 
 1 AS `id`,
 1 AS `created`,
 1 AS `created_employee`,
 1 AS `client`,
 1 AS `days`*/;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `client_password_reminder`
--

DROP TABLE IF EXISTS `client_password_reminder`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `client_password_reminder` (
  `id` int NOT NULL AUTO_INCREMENT,
  `phone` varchar(60) NOT NULL,
  `code` varchar(4) NOT NULL,
  `created_at` datetime NOT NULL ON UPDATE CURRENT_TIMESTAMP,
  `expired_at` datetime NOT NULL,
  `code_confirmed` tinyint NOT NULL DEFAULT '0',
  `uuid` varchar(60) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `client_password_reminder`
--

LOCK TABLES `client_password_reminder` WRITE;
/*!40000 ALTER TABLE `client_password_reminder` DISABLE KEYS */;
/*!40000 ALTER TABLE `client_password_reminder` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `client_prices`
--

DROP TABLE IF EXISTS `client_prices`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `client_prices` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `agreement` int unsigned NOT NULL,
  `price` int unsigned NOT NULL,
  `time_start` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `time_stop` timestamp NULL DEFAULT NULL,
  `act_employee_id` int DEFAULT NULL,
  `deact_employee_id` int DEFAULT NULL,
  `parent` int unsigned DEFAULT NULL,
  `disable_day` datetime DEFAULT NULL,
  `disable_day_static` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `agreement_price` (`agreement`,`price`) USING BTREE,
  KEY `price` (`price`),
  KEY `parent` (`parent`),
  CONSTRAINT `client` FOREIGN KEY (`agreement`) REFERENCES `clients` (`id`),
  CONSTRAINT `client_prices_ibfk_1` FOREIGN KEY (`parent`) REFERENCES `client_prices` (`id`) ON DELETE SET NULL ON UPDATE SET NULL,
  CONSTRAINT `price` FOREIGN KEY (`price`) REFERENCES `bill_prices` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `client_prices`
--

LOCK TABLES `client_prices` WRITE;
/*!40000 ALTER TABLE `client_prices` DISABLE KEYS */;
/*!40000 ALTER TABLE `client_prices` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `client_tokens`
--

DROP TABLE IF EXISTS `client_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `client_tokens` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `token` varchar(255) NOT NULL,
  `client_id` int unsigned NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `expired_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `employee` (`client_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb3 ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `client_tokens`
--

LOCK TABLES `client_tokens` WRITE;
/*!40000 ALTER TABLE `client_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `client_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `client_trinity_agreements`
--

DROP TABLE IF EXISTS `client_trinity_agreements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `client_trinity_agreements` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `trinity_agreement` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `agreement` int NOT NULL,
  `price_name` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `addresses` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `created_time` datetime DEFAULT NULL,
  `updated_time` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `agreement` (`agreement`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `client_trinity_agreements`
--

LOCK TABLES `client_trinity_agreements` WRITE;
/*!40000 ALTER TABLE `client_trinity_agreements` DISABLE KEYS */;
/*!40000 ALTER TABLE `client_trinity_agreements` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `clients`
--

DROP TABLE IF EXISTS `clients`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `clients` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `agreement` bigint NOT NULL,
  `name` varchar(150) NOT NULL DEFAULT '',
  `entrance` tinyint unsigned NOT NULL DEFAULT '1',
  `floor` tinyint unsigned NOT NULL DEFAULT '1',
  `apartment` varchar(150) NOT NULL DEFAULT '1',
  `house` int unsigned NOT NULL,
  `balance` decimal(9,2) NOT NULL DEFAULT '0.00',
  `add_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `descr` varchar(4096) NOT NULL DEFAULT '',
  `notice_mail` tinyint unsigned NOT NULL DEFAULT '1',
  `notice_sms` tinyint unsigned NOT NULL DEFAULT '1',
  `enable_credit` tinyint unsigned NOT NULL DEFAULT '1',
  `password` varchar(100) NOT NULL DEFAULT '',
  `provider` int unsigned NOT NULL,
  `enable_credit_period` tinyint unsigned NOT NULL DEFAULT '1',
  `telegram_chat_id` varchar(20) DEFAULT NULL,
  `status` enum('ENABLED','DISABLED','DELETED') NOT NULL DEFAULT 'ENABLED',
  PRIMARY KEY (`id`,`agreement`),
  UNIQUE KEY `agreement` (`agreement`) USING BTREE,
  KEY `house` (`house`),
  KEY `id` (`id`),
  KEY `provider` (`provider`),
  CONSTRAINT `clients_ibfk_1` FOREIGN KEY (`provider`) REFERENCES `providers` (`id`),
  CONSTRAINT `house` FOREIGN KEY (`house`) REFERENCES `addr_houses` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `clients`
--

LOCK TABLES `clients` WRITE;
/*!40000 ALTER TABLE `clients` DISABLE KEYS */;
/*!40000 ALTER TABLE `clients` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `device_models`
--

DROP TABLE IF EXISTS `device_models`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `device_models` (
  `id` int NOT NULL AUTO_INCREMENT,
  `key` varchar(150) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `params` json DEFAULT NULL,
  `vendor` varchar(50) DEFAULT NULL,
  `model` varchar(50) DEFAULT NULL,
  `type` enum('SWITCH','OLT','ONU','ROUTER') NOT NULL DEFAULT 'SWITCH',
  `icon` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `device_models`
--

LOCK TABLES `device_models` WRITE;
/*!40000 ALTER TABLE `device_models` DISABLE KEYS */;
/*!40000 ALTER TABLE `device_models` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `emplo_positions`
--

DROP TABLE IF EXISTS `emplo_positions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `emplo_positions` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `position` varchar(50) NOT NULL DEFAULT '',
  `rank` tinyint NOT NULL,
  `show` bit(1) DEFAULT b'1',
  `permissions` json DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`position`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `emplo_positions`
--

LOCK TABLES `emplo_positions` WRITE;
/*!40000 ALTER TABLE `emplo_positions` DISABLE KEYS */;
INSERT INTO `emplo_positions` VALUES (4,'Администратор',30,_binary '','[\"customer_create\", \"customer_mass_messages\", \"customer_report_certs\", \"customer_deptors\", \"customer_search\", \"customer_show_card\", \"customer_change_name\", \"customer_change_provider\", \"customer_change_addr\", \"customer_stop_service\", \"customer_start_service\", \"customer_pause_service\", \"customer_resume_service\", \"customer_change_description\", \"customer_disable_agreement\", \"customer_change_notification\", \"customer_change_ack\", \"customer_change_contacts\", \"customer_change_password\", \"customer_related\", \"customer_purpose_of_payment\", \"question_create\", \"question_search\", \"question_change\", \"question_show\", \"question_report_change\", \"question_report_show\", \"payment_search\", \"payment_delete\", \"payment_show\", \"payment_create\", \"payment_source\", \"payment_summary_source\", \"payment_summary_price\", \"payment_liqpay\", \"eq_binding_search\", \"eq_binding_delete\", \"eq_binding_change_mac\", \"eq_binding_change_ip\", \"eq_binding_change_static\", \"eq_binding_change_port\", \"eq_binding_create\", \"eq_models\", \"eq_access\", \"eq_group\", \"eq_pinger\", \"eq_show\", \"eq_list\", \"eq_edit\", \"eq_create\", \"eq_delete\", \"eq_change_vlan\", \"vlan_show\", \"vlan_change\", \"network_show\", \"network_edit\", \"employees_show\", \"employees_group\", \"employees_add\", \"employees_notification\", \"employees_reaction_stat\", \"employees_schedule_show\", \"employees_schedule_edit\", \"question_loading\", \"sys_question_reason\", \"trinity_binding_add\", \"trinity_contracts\", \"trinity_search\", \"trinity_delete\", \"omo_display\", \"omo_control\"]'),(9,'Оператор',30,_binary '','[\"customer_create\", \"customer_report_certs\", \"customer_search\", \"customer_show_card\", \"customer_change_name\", \"customer_change_provider\", \"customer_change_addr\", \"customer_stop_service\", \"customer_start_service\", \"customer_pause_service\", \"customer_resume_service\", \"customer_change_description\", \"customer_change_notification\", \"customer_change_ack\", \"customer_change_contacts\", \"customer_change_password\", \"customer_related\", \"question_create\", \"question_search\", \"question_change\", \"question_show\", \"question_report_change\", \"question_report_show\", \"payment_search\", \"payment_show\", \"payment_create\", \"eq_binding_search\", \"eq_binding_delete\", \"eq_binding_change_mac\", \"eq_binding_create\", \"eq_pinger\", \"eq_show\", \"eq_list\", \"employees_show\", \"trinity_binding_add\", \"trinity_search\", \"trinity_delete\"]'),(19,'Монтажник',30,_binary '','[\"customer_create\", \"customer_report_certs\", \"customer_deptors\", \"customer_search\", \"customer_show_card\", \"customer_change_name\", \"customer_change_provider\", \"customer_change_addr\", \"customer_stop_service\", \"customer_start_service\", \"customer_pause_service\", \"customer_resume_service\", \"customer_change_description\", \"customer_change_notification\", \"customer_change_ack\", \"customer_change_contacts\", \"customer_change_password\", \"customer_related\", \"customer_purpose_of_payment\", \"question_create\", \"question_search\", \"question_change\", \"question_show\", \"question_report_change\", \"question_report_show\", \"payment_search\", \"payment_delete\", \"payment_show\", \"payment_create\", \"eq_binding_search\", \"eq_binding_delete\", \"eq_binding_change_mac\", \"eq_binding_change_ip\", \"eq_binding_change_port\", \"eq_binding_create\", \"eq_pinger\", \"eq_show\", \"eq_create\", \"employees_show\", \"trinity_binding_add\", \"trinity_search\", \"trinity_delete\"]'),(21,'Инженер',30,_binary '','[\"customer_create\", \"customer_search\", \"customer_show_card\", \"customer_change_provider\", \"customer_stop_service\", \"customer_start_service\", \"customer_pause_service\", \"customer_resume_service\", \"customer_change_ack\", \"customer_change_contacts\", \"question_search\", \"question_change\", \"question_show\", \"question_report_change\", \"question_report_show\", \"eq_binding_search\", \"eq_binding_delete\", \"eq_binding_change_mac\", \"eq_binding_change_ip\", \"eq_binding_change_static\", \"eq_binding_change_port\", \"eq_binding_create\", \"eq_models\", \"eq_access\", \"eq_group\", \"eq_pinger\", \"eq_show\", \"eq_list\", \"eq_edit\", \"eq_create\", \"eq_delete\", \"eq_change_vlan\", \"vlan_show\", \"vlan_change\", \"network_show\", \"network_edit\", \"trinity_binding_add\", \"trinity_contracts\", \"trinity_search\", \"trinity_delete\"]'),(22,'Система',30,_binary '','[]'),(23,'Бугалтер',30,_binary '','[\"customer_create\", \"customer_deptors\", \"customer_search\", \"customer_show_card\", \"customer_change_name\", \"customer_change_provider\", \"customer_change_addr\", \"customer_change_description\", \"payment_search\", \"payment_delete\", \"payment_show\", \"payment_create\"]'),(24,'Офіс Менеджер',30,_binary '','[\"customer_create\", \"customer_report_certs\", \"customer_deptors\", \"customer_search\", \"customer_show_card\", \"customer_change_name\", \"customer_change_provider\", \"customer_change_addr\", \"customer_stop_service\", \"customer_start_service\", \"customer_pause_service\", \"customer_resume_service\", \"customer_change_description\", \"customer_disable_agreement\", \"customer_change_contacts\", \"customer_change_password\", \"customer_purpose_of_payment\", \"question_create\", \"question_search\", \"question_change\", \"question_show\", \"question_report_change\", \"question_report_show\", \"payment_search\", \"payment_show\", \"payment_create\", \"employees_reaction_stat\", \"employees_schedule_show\", \"employees_schedule_edit\", \"question_loading\"]'),(25,'Монтажник ЮГ',30,_binary '','[\"customer_create\", \"customer_report_certs\", \"customer_deptors\", \"customer_search\", \"customer_show_card\", \"customer_change_name\", \"customer_change_addr\", \"customer_stop_service\", \"customer_start_service\", \"customer_pause_service\", \"customer_resume_service\", \"customer_change_description\", \"customer_change_notification\", \"customer_change_ack\", \"customer_change_contacts\", \"customer_change_password\", \"customer_related\", \"customer_purpose_of_payment\", \"question_create\", \"question_search\", \"question_change\", \"question_show\", \"question_report_change\", \"question_report_show\", \"payment_search\", \"payment_show\", \"payment_create\", \"eq_binding_search\", \"eq_binding_delete\", \"eq_binding_change_mac\", \"eq_binding_change_ip\", \"eq_binding_change_static\", \"eq_binding_change_port\", \"eq_binding_create\", \"eq_pinger\", \"eq_show\", \"eq_list\", \"employees_show\", \"employees_schedule_show\", \"question_loading\", \"trinity_binding_add\", \"trinity_contracts\", \"trinity_search\"]');
/*!40000 ALTER TABLE `emplo_positions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `emplo_tokens`
--

DROP TABLE IF EXISTS `emplo_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `emplo_tokens` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `token` varchar(255) NOT NULL,
  `employee` int unsigned NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `expired_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `employee` (`employee`) USING BTREE,
  CONSTRAINT `emplo_tokens_ibfk_1` FOREIGN KEY (`employee`) REFERENCES `employees` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb3 ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `emplo_tokens`
--

LOCK TABLES `emplo_tokens` WRITE;
/*!40000 ALTER TABLE `emplo_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `emplo_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `employee_positions_to_house_groups`
--

DROP TABLE IF EXISTS `employee_positions_to_house_groups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `employee_positions_to_house_groups` (
  `id` int NOT NULL AUTO_INCREMENT,
  `position_id` int unsigned NOT NULL,
  `house_group_id` int NOT NULL,
  `updated_at` datetime NOT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `position_id` (`position_id`),
  KEY `house_group_id` (`house_group_id`),
  CONSTRAINT `employee_positions_to_house_groups_ibfk_1` FOREIGN KEY (`position_id`) REFERENCES `emplo_positions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `employee_positions_to_house_groups_ibfk_2` FOREIGN KEY (`house_group_id`) REFERENCES `addr_groups` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `employee_positions_to_house_groups`
--

LOCK TABLES `employee_positions_to_house_groups` WRITE;
/*!40000 ALTER TABLE `employee_positions_to_house_groups` DISABLE KEYS */;
/*!40000 ALTER TABLE `employee_positions_to_house_groups` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `employee_work_statuses`
--

DROP TABLE IF EXISTS `employee_work_statuses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `employee_work_statuses` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` int unsigned NOT NULL,
  `start` datetime NOT NULL,
  `stop` datetime DEFAULT NULL,
  `status` enum('DUTY','IN_WORK') NOT NULL,
  PRIMARY KEY (`id`),
  KEY `employee_id` (`employee_id`),
  CONSTRAINT `employee_work_statuses_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `employee_work_statuses`
--

LOCK TABLES `employee_work_statuses` WRITE;
/*!40000 ALTER TABLE `employee_work_statuses` DISABLE KEYS */;
/*!40000 ALTER TABLE `employee_work_statuses` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `employees`
--

DROP TABLE IF EXISTS `employees`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `employees` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(150) DEFAULT NULL,
  `phone` varchar(32) DEFAULT NULL,
  `skype` varchar(32) DEFAULT NULL,
  `mail` varchar(32) DEFAULT NULL,
  `position` int unsigned DEFAULT NULL,
  `password` varchar(50) NOT NULL,
  `login` varchar(50) NOT NULL,
  `display` int DEFAULT '1',
  `telegram_id` text,
  PRIMARY KEY (`id`),
  KEY `positions` (`position`),
  CONSTRAINT `positions` FOREIGN KEY (`position`) REFERENCES `emplo_positions` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=46 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `employees`
--

LOCK TABLES `employees` WRITE;
/*!40000 ALTER TABLE `employees` DISABLE KEYS */;
INSERT INTO `employees` VALUES (1,'Заявка ЛК',' ','','',22,'','personal-area',0,'0'),(2,'System',' ',' ',' ',22,'','system',0,'0'),(3,'Автокредитование','','','',22,'','ack',0,'0'),(4,'Admin',NULL,NULL,NULL,4,'admin','admin',1,'0');
/*!40000 ALTER TABLE `employees` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `eq_bindings`
--

DROP TABLE IF EXISTS `eq_bindings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `eq_bindings` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `created` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `switch` int unsigned DEFAULT NULL,
  `activation` int unsigned DEFAULT NULL,
  `port` varchar(15) DEFAULT NULL,
  `mac` varchar(22) DEFAULT NULL,
  `ip` varchar(15) DEFAULT NULL,
  `employee` int unsigned DEFAULT NULL,
  `allow_static` tinyint NOT NULL DEFAULT '0',
  `port_ident` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ip` (`ip`),
  KEY `switch` (`switch`),
  KEY `activation` (`activation`),
  CONSTRAINT `eq_bindings_ibfk_1` FOREIGN KEY (`switch`) REFERENCES `equipment` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `eq_bindings_ibfk_2` FOREIGN KEY (`activation`) REFERENCES `client_prices` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `eq_bindings`
--

LOCK TABLES `eq_bindings` WRITE;
/*!40000 ALTER TABLE `eq_bindings` DISABLE KEYS */;
/*!40000 ALTER TABLE `eq_bindings` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'STRICT_TRANS_TABLES,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`service`@`localhost`*/ /*!50003 TRIGGER `to_history_updated` BEFORE UPDATE ON `eq_bindings` FOR EACH ROW BEGIN
INSERT INTO eq_bindings_history
SELECT *, 'UPDATED', NOW() FROM eq_bindings WHERE id = OLD.id;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'STRICT_TRANS_TABLES,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`service`@`localhost`*/ /*!50003 TRIGGER `to_history_deleted` BEFORE DELETE ON `eq_bindings` FOR EACH ROW BEGIN
INSERT INTO eq_bindings_history
SELECT *, 'DELETED', NOW() FROM eq_bindings WHERE id = OLD.id;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `eq_bindings_activity`
--

DROP TABLE IF EXISTS `eq_bindings_activity`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `eq_bindings_activity` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL ON UPDATE CURRENT_TIMESTAMP,
  `binding_id` int NOT NULL,
  `request` json NOT NULL,
  `response` json NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `binding_id` (`binding_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `eq_bindings_activity`
--

LOCK TABLES `eq_bindings_activity` WRITE;
/*!40000 ALTER TABLE `eq_bindings_activity` DISABLE KEYS */;
/*!40000 ALTER TABLE `eq_bindings_activity` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `eq_bindings_history`
--

DROP TABLE IF EXISTS `eq_bindings_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `eq_bindings_history` (
  `id` int unsigned DEFAULT NULL,
  `created` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `switch` int unsigned DEFAULT NULL,
  `activation` int unsigned DEFAULT NULL,
  `port` varchar(50) DEFAULT NULL,
  `mac` varchar(22) DEFAULT NULL,
  `ip` varchar(15) DEFAULT NULL,
  `employee` int unsigned DEFAULT NULL,
  `allow_static` tinyint NOT NULL DEFAULT '0',
  `port_ident` int DEFAULT NULL,
  `action` enum('DELETED','UPDATED') DEFAULT NULL,
  `action_time` datetime DEFAULT NULL,
  KEY `switch` (`switch`),
  KEY `activation` (`activation`),
  KEY `ip` (`ip`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `eq_bindings_history`
--

LOCK TABLES `eq_bindings_history` WRITE;
/*!40000 ALTER TABLE `eq_bindings_history` DISABLE KEYS */;
/*!40000 ALTER TABLE `eq_bindings_history` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `eq_binds_extra`
--

DROP TABLE IF EXISTS `eq_binds_extra`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `eq_binds_extra` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `binding_id` int unsigned NOT NULL,
  `type` enum('FLAG','VALUE') NOT NULL,
  `name` varchar(255) NOT NULL,
  `value` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `binding_id` (`binding_id`),
  CONSTRAINT `eq_binds_extra_ibfk_1` FOREIGN KEY (`binding_id`) REFERENCES `eq_bindings` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `eq_binds_extra`
--

LOCK TABLES `eq_binds_extra` WRITE;
/*!40000 ALTER TABLE `eq_binds_extra` DISABLE KEYS */;
/*!40000 ALTER TABLE `eq_binds_extra` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `eq_kinds`
--

DROP TABLE IF EXISTS `eq_kinds`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `eq_kinds` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `rank` int DEFAULT NULL,
  `created` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `parent` int unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `parent` (`parent`),
  CONSTRAINT `eq_kinds_ibfk_1` FOREIGN KEY (`parent`) REFERENCES `eq_kinds` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `eq_kinds`
--

LOCK TABLES `eq_kinds` WRITE;
/*!40000 ALTER TABLE `eq_kinds` DISABLE KEYS */;
INSERT INTO `eq_kinds` VALUES (1,'Типы вланов','',NULL,'2017-07-08 09:16:10',NULL),(2,'Типы подсетей',NULL,NULL,'2017-07-08 09:16:18',NULL),(3,'INTERNET','для фейковых IP',NULL,'2017-07-08 09:53:17',1),(4,'INTERNET-REAL','для белых IP',NULL,'2017-07-08 09:53:19',1),(5,'FAKE','для отключенных(замороженных)',NULL,'2017-07-08 09:53:23',1),(6,'Серая','Абонентские, серые подсети',NULL,'2017-07-08 09:37:58',2),(7,'Свитчевая','Оборудование, Свитчевая',NULL,'2017-07-08 09:53:12',2),(8,'Белая','Абонентские, белые подсети',NULL,'2017-07-08 09:38:20',2),(9,'LOCAL','Служебный',NULL,'2017-07-08 09:52:40',1);
/*!40000 ALTER TABLE `eq_kinds` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `eq_neth`
--

DROP TABLE IF EXISTS `eq_neth`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `eq_neth` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `type` int unsigned DEFAULT NULL,
  `created` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `startIp` varchar(15) DEFAULT NULL,
  `stopIp` varchar(15) DEFAULT NULL,
  `mask` varchar(15) DEFAULT NULL,
  `gateway` varchar(15) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `type` (`type`),
  CONSTRAINT `eq_neth_ibfk_1` FOREIGN KEY (`type`) REFERENCES `eq_kinds` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `eq_neth`
--

LOCK TABLES `eq_neth` WRITE;
/*!40000 ALTER TABLE `eq_neth` DISABLE KEYS */;
/*!40000 ALTER TABLE `eq_neth` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `eq_pinger_log`
--

DROP TABLE IF EXISTS `eq_pinger_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `eq_pinger_log` (
  `equipment` int unsigned NOT NULL,
  `down` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `up` timestamp NULL DEFAULT NULL,
  KEY `equipment` (`equipment`),
  CONSTRAINT `eq_pinger_log_ibfk_1` FOREIGN KEY (`equipment`) REFERENCES `equipment` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `eq_pinger_log`
--

LOCK TABLES `eq_pinger_log` WRITE;
/*!40000 ALTER TABLE `eq_pinger_log` DISABLE KEYS */;
/*!40000 ALTER TABLE `eq_pinger_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `eq_vlan_equipment`
--

DROP TABLE IF EXISTS `eq_vlan_equipment`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `eq_vlan_equipment` (
  `vlan` int unsigned DEFAULT NULL,
  `equipment` int unsigned DEFAULT NULL,
  `created` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY `eq_vlan` (`vlan`,`equipment`) USING BTREE,
  KEY `equipment` (`equipment`),
  KEY `vlan` (`vlan`),
  CONSTRAINT `eq_vlan_equipment_ibfk_1` FOREIGN KEY (`vlan`) REFERENCES `eq_vlans` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `eq_vlan_equipment_ibfk_2` FOREIGN KEY (`equipment`) REFERENCES `equipment` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `eq_vlan_equipment`
--

LOCK TABLES `eq_vlan_equipment` WRITE;
/*!40000 ALTER TABLE `eq_vlan_equipment` DISABLE KEYS */;
/*!40000 ALTER TABLE `eq_vlan_equipment` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `eq_vlan_neth`
--

DROP TABLE IF EXISTS `eq_vlan_neth`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `eq_vlan_neth` (
  `neth` int unsigned DEFAULT NULL,
  `vlan` int unsigned DEFAULT NULL,
  `created` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY `ntt` (`neth`,`vlan`),
  KEY `vlan` (`vlan`),
  KEY `neth` (`neth`),
  CONSTRAINT `eq_vlan_neth_ibfk_1` FOREIGN KEY (`neth`) REFERENCES `eq_neth` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `eq_vlan_neth_ibfk_2` FOREIGN KEY (`vlan`) REFERENCES `eq_vlans` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `eq_vlan_neth`
--

LOCK TABLES `eq_vlan_neth` WRITE;
/*!40000 ALTER TABLE `eq_vlan_neth` DISABLE KEYS */;
/*!40000 ALTER TABLE `eq_vlan_neth` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `eq_vlans`
--

DROP TABLE IF EXISTS `eq_vlans`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `eq_vlans` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `vlan` int DEFAULT NULL,
  `name` varchar(60) DEFAULT NULL,
  `type` int unsigned DEFAULT NULL,
  `work_with_device` enum('YES','NO') NOT NULL DEFAULT 'YES',
  PRIMARY KEY (`id`),
  KEY `type` (`type`),
  CONSTRAINT `eq_vlans_ibfk_1` FOREIGN KEY (`type`) REFERENCES `eq_kinds` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `eq_vlans`
--

LOCK TABLES `eq_vlans` WRITE;
/*!40000 ALTER TABLE `eq_vlans` DISABLE KEYS */;
/*!40000 ALTER TABLE `eq_vlans` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `equipment`
--

DROP TABLE IF EXISTS `equipment`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `equipment` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `ip` varchar(15) NOT NULL DEFAULT '',
  `model` int unsigned DEFAULT NULL,
  `mac` varchar(22) NOT NULL,
  `sn` varchar(150) DEFAULT '',
  `hardware` varchar(20) DEFAULT '',
  `firmware` varchar(50) DEFAULT '',
  `change` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `add_person` int unsigned DEFAULT '0',
  `house` int unsigned DEFAULT NULL,
  `access` int unsigned NOT NULL,
  `entrance` tinyint DEFAULT '0',
  `floor` tinyint DEFAULT NULL,
  `description` varchar(160) DEFAULT NULL,
  `repeats` tinyint NOT NULL DEFAULT '0',
  `ping` int NOT NULL DEFAULT '0',
  `last_ping` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `uplink_port` int DEFAULT NULL,
  `group` int unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_2` (`id`) USING BTREE,
  UNIQUE KEY `ip` (`ip`) USING BTREE,
  KEY `id` (`access`),
  KEY `group` (`group`),
  KEY `models` (`model`),
  KEY `house_equipment` (`house`),
  CONSTRAINT `equipment_addr_houses_id_fk` FOREIGN KEY (`house`) REFERENCES `addr_houses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `group` FOREIGN KEY (`group`) REFERENCES `equipment_group` (`id`),
  CONSTRAINT `id` FOREIGN KEY (`access`) REFERENCES `equipment_access` (`id`),
  CONSTRAINT `models` FOREIGN KEY (`model`) REFERENCES `equipment_models` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `equipment`
--

LOCK TABLES `equipment` WRITE;
/*!40000 ALTER TABLE `equipment` DISABLE KEYS */;
/*!40000 ALTER TABLE `equipment` ENABLE KEYS */;
UNLOCK TABLES;
ALTER DATABASE `service` CHARACTER SET latin1 COLLATE latin1_swedish_ci ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb3 */ ;
/*!50003 SET character_set_results = utf8mb3 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`service`@`localhost`*/ /*!50003 TRIGGER `update_log` AFTER UPDATE ON `equipment` FOR EACH ROW BEGIN
       IF NEW.ping != OLD.ping THEN
           INSERT INTO equipment_pinger_log (equipment, status) VALUES (NEW.id, NEW.ping);
        END IF;
   END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
ALTER DATABASE `service` CHARACTER SET utf8mb3 COLLATE utf8_general_ci ;

--
-- Table structure for table `equipment_access`
--

DROP TABLE IF EXISTS `equipment_access`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `equipment_access` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `login` varchar(50) NOT NULL,
  `password` varchar(50) NOT NULL,
  `community` varchar(50) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `equipment_access`
--

LOCK TABLES `equipment_access` WRITE;
/*!40000 ALTER TABLE `equipment_access` DISABLE KEYS */;
INSERT INTO `equipment_access` VALUES (4,'service','billing','billing'),(5,'service-core','billing-core','billing'),(6,'public','public','public');
/*!40000 ALTER TABLE `equipment_access` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `equipment_group`
--

DROP TABLE IF EXISTS `equipment_group`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `equipment_group` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) DEFAULT NULL,
  `description` varchar(150) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `equipment_group`
--

LOCK TABLES `equipment_group` WRITE;
/*!40000 ALTER TABLE `equipment_group` DISABLE KEYS */;
INSERT INTO `equipment_group` VALUES (5,'Уровень доступа',''),(6,'Ядро',''),(7,'Сервера',''),(8,'Транспорт',''),(19,'Контроль питания ','Контроль Питания');
/*!40000 ALTER TABLE `equipment_group` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `equipment_models`
--

DROP TABLE IF EXISTS `equipment_models`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `equipment_models` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(150) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `configurable` enum('YES','NO') NOT NULL DEFAULT 'YES',
  `device_type` enum('SWITCH','OLT','ROUTER','OTHER','ONU') NOT NULL DEFAULT 'SWITCH',
  `port_regex` varchar(255) NOT NULL DEFAULT '[0-9]{1,}',
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=41 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `equipment_models`
--

LOCK TABLES `equipment_models` WRITE;
/*!40000 ALTER TABLE `equipment_models` DISABLE KEYS */;
INSERT INTO `equipment_models` VALUES (1,'серверная','','NO','OTHER','[0-9]{1,}'),(4,'D-Link DES-3200-18/A1','','YES','SWITCH','[0-9]{1,}'),(5,'D-link DES-3200-28/C1 ','','YES','SWITCH','[0-9]{1,}'),(6,'D-Link DES-1228/ME','','YES','SWITCH','[0-9]{1,}'),(8,'D-Link DGS-1100-06/ME/A1','','YES','SWITCH','[0-9]{1,}'),(11,'D-Link DES-3200-28/A1','','YES','SWITCH','[0-9]{1,}'),(12,'D-link DES-3200-26/A1','','YES','SWITCH','[0-9]{1,}'),(16,'Камера','','NO','OTHER','[0-9]{1,}'),(17,'Mikrotik','','YES','ROUTER','[0-9]{1,}'),(18,'D-link DES-3200-26/C1','','YES','SWITCH','[0-9]{1,}'),(19,'D-Link DGS-3000-26TC/A1','','YES','SWITCH','[0-9]{1,}'),(20,'Edge-Core ECS4120-28F','','YES','SWITCH','[0-9]{1,}'),(21,'C-Data FD1204SN','','NO','OLT','^[0-9]{1,}\\/[0-9]{1,}\\/[0-9]{1,}:[0-9]{1,}$'),(22,'ONU--HZ660.2A','','NO','ONU','[0-9]{1,}'),(23,'Hub','','NO','OTHER','[0-9]{1,}'),(24,'ControlPower','','NO','OTHER','[0-9]{1,}'),(25,'Абонент','','NO','OTHER','[0-9]{1,}'),(26,'Sip','','NO','OTHER','[0-9]{1,}'),(27,'D-Link DGS-1210-28/ME/B1','','YES','SWITCH','[0-9]{1,}'),(28,'BDCOM 3310B','','NO','OLT','^[0-9]{1,}\\/[0-9]{1,}\\/[0-9]{1,}:[0-9]{1,}$'),(29,'BDCOM 3310C','','NO','OLT','^[0-9]{1,}\\/[0-9]{1,}\\/[0-9]{1,}:[0-9]{1,}$'),(30,'MikroTik RB911G-5HPnD','','NO','SWITCH','[0-9]{1,}'),(31,'C-Data FD1208S','','NO','SWITCH','^[0-9]{1,}\\/[0-9]{1,}\\/[0-9]{1,}:[0-9]{1,}$'),(32,'D-Link DGS-3120-24SC','','NO','SWITCH','^[0-9]{1,}\\/[0-9]{1,}\\/[0-9]{1,}:[0-9]{1,}$'),(33,'D-Link DGS-1210-28/ME/A1',NULL,'YES','SWITCH','^[0-9]{1,3}$'),(34,'D-Link DGS-1210-28/ME/A2',NULL,'YES','SWITCH','^[0-9]{1,3}$'),(35,'D-Link DGS-1210-28/ME/B1',NULL,'YES','SWITCH','^[0-9]{1,3}$'),(36,'Mikrotik CCR1036',NULL,'YES','ROUTER','[0-9]{1,}'),(37,'Mikrotik CCR1009',NULL,'YES','ROUTER','[0-9]{1,}'),(38,'Mikrotik RB4011',NULL,'YES','ROUTER','[0-9]{1,}'),(39,'Mikrotik CCR1016',NULL,'YES','ROUTER','[0-9]{1,}'),(40,'Mikrotik CCR1009',NULL,'YES','ROUTER','[0-9]{1,}');
/*!40000 ALTER TABLE `equipment_models` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `equipment_pinger_log`
--

DROP TABLE IF EXISTS `equipment_pinger_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `equipment_pinger_log` (
  `equipment` int unsigned DEFAULT NULL,
  `status` int DEFAULT NULL,
  `time` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `equipment_pinger_log`
--

LOCK TABLES `equipment_pinger_log` WRITE;
/*!40000 ALTER TABLE `equipment_pinger_log` DISABLE KEYS */;
/*!40000 ALTER TABLE `equipment_pinger_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `liqpay`
--

DROP TABLE IF EXISTS `liqpay`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `liqpay` (
  `action` varchar(40) DEFAULT NULL,
  `payment_id` bigint unsigned DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL,
  `paytype` varchar(50) DEFAULT NULL,
  `acq_id` varchar(50) DEFAULT NULL,
  `order_id` varchar(50) DEFAULT NULL,
  `liqpay_order_id` varchar(150) DEFAULT NULL,
  `description` varchar(150) DEFAULT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `currency` varchar(30) DEFAULT NULL,
  `sender_commission` decimal(10,2) DEFAULT NULL,
  `receiver_commission` decimal(10,2) DEFAULT NULL,
  `agent_commission` decimal(10,2) DEFAULT NULL,
  `amount_debit` decimal(10,2) DEFAULT NULL,
  `amount_credit` decimal(10,2) DEFAULT NULL,
  `commission_debit` decimal(10,2) DEFAULT NULL,
  `commission_credit` decimal(10,2) DEFAULT NULL,
  `is_3ds` varchar(50) DEFAULT NULL,
  `customer` varchar(30) DEFAULT NULL,
  `transaction_id` bigint unsigned DEFAULT NULL,
  `time` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `liqpay`
--

LOCK TABLES `liqpay` WRITE;
/*!40000 ALTER TABLE `liqpay` DISABLE KEYS */;
/*!40000 ALTER TABLE `liqpay` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `omo_agreement_bindings`
--

DROP TABLE IF EXISTS `omo_agreement_bindings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `omo_agreement_bindings` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `agreement_id` int unsigned NOT NULL,
  `user_id` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `agreement_id_2` (`agreement_id`,`user_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `omo_agreement_bindings_ibfk_1` FOREIGN KEY (`agreement_id`) REFERENCES `clients` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `omo_agreement_bindings_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `omo_users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `omo_agreement_bindings`
--

LOCK TABLES `omo_agreement_bindings` WRITE;
/*!40000 ALTER TABLE `omo_agreement_bindings` DISABLE KEYS */;
/*!40000 ALTER TABLE `omo_agreement_bindings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `omo_device_bindings`
--

DROP TABLE IF EXISTS `omo_device_bindings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `omo_device_bindings` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `created_at` datetime NOT NULL,
  `active` enum('YES','NO') NOT NULL DEFAULT 'YES',
  `device_id` int unsigned NOT NULL,
  `user_id` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `device_id` (`device_id`,`user_id`),
  KEY `omo_device_bindings_ibfk_1` (`user_id`),
  CONSTRAINT `omo_device_bindings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `omo_users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `omo_device_bindings_ibfk_2` FOREIGN KEY (`device_id`) REFERENCES `omo_devices` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `omo_device_bindings`
--

LOCK TABLES `omo_device_bindings` WRITE;
/*!40000 ALTER TABLE `omo_device_bindings` DISABLE KEYS */;
/*!40000 ALTER TABLE `omo_device_bindings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `omo_devices`
--

DROP TABLE IF EXISTS `omo_devices`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `omo_devices` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `hub_uid` varchar(150) NOT NULL,
  `device_uid` varchar(150) NOT NULL,
  `user_uid` varchar(150) DEFAULT NULL,
  `type` varchar(150) NOT NULL,
  `status` enum('BINDED','ENABLED','DISABLED','NOT_BINDED','DELETED') NOT NULL DEFAULT 'NOT_BINDED',
  `house` int unsigned DEFAULT NULL,
  `entrance` int DEFAULT NULL,
  `floor` int DEFAULT NULL,
  `apartment` varchar(15) DEFAULT NULL,
  `comment` text,
  `delete_reason` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `device_uid` (`device_uid`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `omo_devices`
--

LOCK TABLES `omo_devices` WRITE;
/*!40000 ALTER TABLE `omo_devices` DISABLE KEYS */;
INSERT INTO `omo_devices` VALUES (1,'2020-06-11 09:29:19','String','String','String','Intercom','DELETED',NULL,NULL,NULL,NULL,'',NULL);
/*!40000 ALTER TABLE `omo_devices` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `omo_events`
--

DROP TABLE IF EXISTS `omo_events`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `omo_events` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `type` varchar(150) NOT NULL,
  `correlation_id` varchar(150) DEFAULT NULL,
  `user_uid` varchar(150) DEFAULT NULL,
  `hub_uid` varchar(150) DEFAULT NULL,
  `device_uid` varchar(150) DEFAULT NULL,
  `device_type` varchar(150) DEFAULT NULL,
  `receiver_phone` varchar(150) DEFAULT NULL,
  `shared_from_uid` varchar(150) DEFAULT NULL,
  `shared_from_phone` varchar(150) DEFAULT NULL,
  `reason` varchar(150) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `omo_events`
--

LOCK TABLES `omo_events` WRITE;
/*!40000 ALTER TABLE `omo_events` DISABLE KEYS */;
/*!40000 ALTER TABLE `omo_events` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `omo_users`
--

DROP TABLE IF EXISTS `omo_users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `omo_users` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `uid` varchar(150) DEFAULT NULL,
  `phone` varchar(30) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `agreement_id_2` (`uid`,`phone`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `omo_users`
--

LOCK TABLES `omo_users` WRITE;
/*!40000 ALTER TABLE `omo_users` DISABLE KEYS */;
/*!40000 ALTER TABLE `omo_users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `paymants`
--

DROP TABLE IF EXISTS `paymants`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `paymants` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `money` decimal(7,2) NOT NULL DEFAULT '0.00',
  `agreement` int unsigned DEFAULT NULL,
  `time` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `comment` varchar(2048) DEFAULT NULL,
  `debug_info` varchar(4096) DEFAULT NULL,
  `payment_type` varchar(50) DEFAULT NULL,
  `payment_id` varchar(50) DEFAULT NULL,
  `balance` decimal(10,2) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `agreement` (`agreement`),
  CONSTRAINT `agreement` FOREIGN KEY (`agreement`) REFERENCES `clients` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `paymants`
--

LOCK TABLES `paymants` WRITE;
/*!40000 ALTER TABLE `paymants` DISABLE KEYS */;
/*!40000 ALTER TABLE `paymants` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'STRICT_TRANS_TABLES,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`service`@`localhost`*/ /*!50003 TRIGGER `balance` BEFORE INSERT ON `paymants` FOR EACH ROW BEGIN 
SET @agree = NEW.agreement;
SET @money = NEW.money;
UPDATE clients SET balance = balance+@money WHERE id = @agree;
UPDATE client_credit SET status = 'CLOSED', closed_date = NOW(), closed_employee = 20 WHERE client_id = @agree and (SELECT balance FROM clients WHERE id = @agree LIMIT 1) + @money > 0;
UPDATE client_credit SET amount = amount - @money WHERE client_id = @agree and (SELECT balance FROM clients WHERE id = @agree LIMIT 1) + @money < 0;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
ALTER DATABASE `service` CHARACTER SET latin1 COLLATE latin1_swedish_ci ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`service`@`localhost`*/ /*!50003 TRIGGER `minus` BEFORE DELETE ON `paymants` FOR EACH ROW BEGIN
UPDATE clients SET balance = balance - OLD.money WHERE id = OLD.agreement;
INSERT INTO paymants_deleted
SELECT * FROM paymants WHERE id =OLD.id;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
ALTER DATABASE `service` CHARACTER SET utf8mb3 COLLATE utf8_general_ci ;

--
-- Table structure for table `paymants_deleted`
--

DROP TABLE IF EXISTS `paymants_deleted`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `paymants_deleted` (
  `id` int unsigned NOT NULL,
  `money` decimal(7,2) NOT NULL DEFAULT '0.00',
  `agreement` int unsigned DEFAULT NULL,
  `time` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `comment` varchar(120) DEFAULT NULL,
  `debug_info` varchar(300) DEFAULT NULL,
  `payment_type` varchar(50) DEFAULT NULL,
  `payment_id` varchar(50) DEFAULT NULL,
  `balance` decimal(10,2) DEFAULT NULL,
  KEY `agreement` (`agreement`),
  CONSTRAINT `paymants_deleted_ibfk_1` FOREIGN KEY (`agreement`) REFERENCES `clients` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `paymants_deleted`
--

LOCK TABLES `paymants_deleted` WRITE;
/*!40000 ALTER TABLE `paymants_deleted` DISABLE KEYS */;
/*!40000 ALTER TABLE `paymants_deleted` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `paymants_orders`
--

DROP TABLE IF EXISTS `paymants_orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `paymants_orders` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `money` decimal(7,2) NOT NULL DEFAULT '0.00',
  `agreement` int unsigned DEFAULT NULL,
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `comment` varchar(120) DEFAULT NULL,
  `order_id` varchar(120) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `order_id` (`order_id`) USING BTREE,
  KEY `agreement` (`agreement`),
  CONSTRAINT `paymants_orders_ibfk_1` FOREIGN KEY (`agreement`) REFERENCES `clients` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `paymants_orders`
--

LOCK TABLES `paymants_orders` WRITE;
/*!40000 ALTER TABLE `paymants_orders` DISABLE KEYS */;
/*!40000 ALTER TABLE `paymants_orders` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `providers`
--

DROP TABLE IF EXISTS `providers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `providers` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `descr` varchar(255) DEFAULT NULL,
  `changed` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1028 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `providers`
--

LOCK TABLES `providers` WRITE;
/*!40000 ALTER TABLE `providers` DISABLE KEYS */;
INSERT INTO `providers` VALUES (1025,'Без провайдера',NULL,'2022-01-23 12:01:32','2018-02-09 14:33:57'),(1026,'AllOkBilling',NULL,'2022-01-23 12:01:32',NULL);
/*!40000 ALTER TABLE `providers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `question_comments`
--

DROP TABLE IF EXISTS `question_comments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `question_comments` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `question` int unsigned NOT NULL,
  `dest_time` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `comment` text NOT NULL,
  `employee` int unsigned NOT NULL,
  `responsible_employee` int NOT NULL DEFAULT '0',
  `entrance` varchar(20) DEFAULT NULL,
  `floor` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `question` (`question`),
  CONSTRAINT `question_comments_ibfk_1` FOREIGN KEY (`question`) REFERENCES `questions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `question_comments`
--

LOCK TABLES `question_comments` WRITE;
/*!40000 ALTER TABLE `question_comments` DISABLE KEYS */;
/*!40000 ALTER TABLE `question_comments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `question_reason`
--

DROP TABLE IF EXISTS `question_reason`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `question_reason` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `display` enum('NO','YES') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'YES',
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `send_telegram` int DEFAULT NULL,
  `pay_required` tinyint NOT NULL DEFAULT '0',
  `reaction_time` int unsigned DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `question_reason`
--

LOCK TABLES `question_reason` WRITE;
/*!40000 ALTER TABLE `question_reason` DISABLE KEYS */;
INSERT INTO `question_reason` VALUES (2,'Подключение Интернета','YES','2020-11-29 12:51:57',NULL,0,90),(3,'Ремонт','YES','2020-10-21 08:05:30',NULL,0,60),(4,'Временное отключение','YES','2021-11-28 18:30:57',NULL,0,2),(5,'Расторжение договора','YES','2021-11-28 18:30:57',NULL,0,2),(6,'Повторная активация','YES','2021-11-28 18:30:57',NULL,0,2),(7,'Не известно','YES','2020-10-21 09:35:18',NULL,0,10),(8,'Приостановление услуги в связи с долгом','YES','2021-11-28 18:30:57',NULL,0,2),(9,'Заявка ЛК','YES','2021-11-28 18:30:57',NULL,0,5),(10,'Проверка оплаты','YES','2020-10-21 09:34:50',NULL,0,5),(11,'Выдача ключа','YES','2020-10-21 09:34:34',NULL,0,10),(12,'Консультация','YES','2020-10-21 09:34:44',NULL,0,10),(14,'Подключения Аудиодомофона','YES','2020-11-29 12:51:29',NULL,0,60),(15,'Подключения Видеодомофона','YES','2020-11-29 12:51:18',NULL,0,120),(16,'Настройка IPTV','YES','2021-11-28 18:30:57',NULL,0,30),(17,'Будівництво','YES','2021-04-08 20:58:15',NULL,0,60);
/*!40000 ALTER TABLE `question_reason` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `question_responses`
--

DROP TABLE IF EXISTS `question_responses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `question_responses` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `question` int unsigned NOT NULL,
  `comment` text NOT NULL,
  `status` enum('CLOSED','OPEN','CANCEL','DONE','IN_PROCESS') DEFAULT NULL,
  `employee` int unsigned NOT NULL,
  `amount` decimal(13,2) unsigned DEFAULT NULL,
  `cert_of_completion` text,
  `cert_subscribed` tinyint DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `question` (`question`),
  CONSTRAINT `question_responses_ibfk_1` FOREIGN KEY (`question`) REFERENCES `questions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `question_responses`
--

LOCK TABLES `question_responses` WRITE;
/*!40000 ALTER TABLE `question_responses` DISABLE KEYS */;
/*!40000 ALTER TABLE `question_responses` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `question_responses_pictures`
--

DROP TABLE IF EXISTS `question_responses_pictures`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `question_responses_pictures` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `response_id` int unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `response_id` (`response_id`),
  CONSTRAINT `question_responses_pictures_ibfk_1` FOREIGN KEY (`response_id`) REFERENCES `question_responses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `question_responses_pictures`
--

LOCK TABLES `question_responses_pictures` WRITE;
/*!40000 ALTER TABLE `question_responses_pictures` DISABLE KEYS */;
/*!40000 ALTER TABLE `question_responses_pictures` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `questions`
--

DROP TABLE IF EXISTS `questions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `questions` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `agreement` int unsigned NOT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `phone` varchar(22) NOT NULL,
  `reason` int unsigned NOT NULL,
  `entrance` varchar(20) DEFAULT NULL,
  `floor` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `agree` (`agreement`),
  KEY `reason` (`reason`),
  CONSTRAINT `agree` FOREIGN KEY (`agreement`) REFERENCES `clients` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `reason` FOREIGN KEY (`reason`) REFERENCES `question_reason` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `questions`
--

LOCK TABLES `questions` WRITE;
/*!40000 ALTER TABLE `questions` DISABLE KEYS */;
/*!40000 ALTER TABLE `questions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Temporary view structure for view `questions_full`
--

DROP TABLE IF EXISTS `questions_full`;
/*!50001 DROP VIEW IF EXISTS `questions_full`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `questions_full` AS SELECT 
 1 AS `id`,
 1 AS `agreement`,
 1 AS `created`,
 1 AS `phone`,
 1 AS `reason_id`,
 1 AS `reason`,
 1 AS `comment`,
 1 AS `dest_time`,
 1 AS `created_employee`,
 1 AS `reported_employee`,
 1 AS `report_status`,
 1 AS `report_time`,
 1 AS `report_id`,
 1 AS `report_comment`,
 1 AS `responsible_employee`,
 1 AS `amount`,
 1 AS `cert_of_completion`,
 1 AS `cert_subscribed`*/;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `radius_acct`
--

DROP TABLE IF EXISTS `radius_acct`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `radius_acct` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `mac` varchar(40) NOT NULL,
  `ip` varchar(15) NOT NULL,
  `dhcp_server` varchar(100) NOT NULL,
  `vlan_id` int DEFAULT NULL,
  `start` datetime NOT NULL,
  `stop` datetime DEFAULT NULL,
  `switch` varchar(15) DEFAULT NULL,
  `port` int DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `hostname` varchar(150) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `mac` (`mac`,`ip`,`dhcp_server`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `radius_acct`
--

LOCK TABLES `radius_acct` WRITE;
/*!40000 ALTER TABLE `radius_acct` DISABLE KEYS */;
/*!40000 ALTER TABLE `radius_acct` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Temporary view structure for view `radius_binding_status`
--

DROP TABLE IF EXISTS `radius_binding_status`;
/*!50001 DROP VIEW IF EXISTS `radius_binding_status`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `radius_binding_status` AS SELECT 
 1 AS `binding_id`,
 1 AS `binding_mac`,
 1 AS `real_mac`,
 1 AS `switch`,
 1 AS `port`,
 1 AS `hostname`,
 1 AS `status`,
 1 AS `binding_ip`,
 1 AS `attached_ip`,
 1 AS `active`*/;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `schedule_calendar_types`
--

DROP TABLE IF EXISTS `schedule_calendar_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `schedule_calendar_types` (
  `id` int NOT NULL AUTO_INCREMENT,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `name` varchar(100) NOT NULL,
  `work_type` enum('WORK','CALLCENTRE','FREE') NOT NULL,
  `colors` json NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `schedule_calendar_types`
--

LOCK TABLES `schedule_calendar_types` WRITE;
/*!40000 ALTER TABLE `schedule_calendar_types` DISABLE KEYS */;
INSERT INTO `schedule_calendar_types` VALUES (1,'2020-11-02 14:37:20','Заявки','WORK','{\"color\": \"#ffffff\", \"bgColor\": \"#31B404\", \"borderColor\": \"#006805\", \"dragBgColor\": \"#31B404\"}'),(2,'2020-11-02 14:37:20','Маркетинг','FREE','{\"color\": \"#ffffff\", \"bgColor\": \"#191970\", \"borderColor\": \"#006805\", \"dragBgColor\": \"#191970\"}'),(3,'2020-11-02 14:37:20','Колл-центр','CALLCENTRE','{\"color\": \"#ffffff\", \"bgColor\": \"#B40404\", \"borderColor\": \"#B40404\", \"dragBgColor\": \"#B40404\"}'),(4,'2020-11-21 23:21:35','Регламентные работы','FREE','{\"color\": \"#ffffff\", \"bgColor\": \"#8B008B\", \"borderColor\": \"#9400D3\", \"dragBgColor\": \"#8B008B\"}');
/*!40000 ALTER TABLE `schedule_calendar_types` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `schedule_list`
--

DROP TABLE IF EXISTS `schedule_list`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `schedule_list` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL,
  `calendar_id` int NOT NULL,
  `employee_id` int unsigned NOT NULL,
  `start` datetime NOT NULL,
  `end` datetime NOT NULL,
  `title` varchar(255) NOT NULL,
  `is_all_day` tinyint NOT NULL DEFAULT '0',
  `created_employee_id` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `schedule_calendar_id_fk` (`calendar_id`),
  KEY `schedule_list_ibfk_1` (`employee_id`),
  CONSTRAINT `schedule_calendar_id_fk` FOREIGN KEY (`calendar_id`) REFERENCES `schedule_calendar_types` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `schedule_list_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `schedule_list`
--

LOCK TABLES `schedule_list` WRITE;
/*!40000 ALTER TABLE `schedule_list` DISABLE KEYS */;
/*!40000 ALTER TABLE `schedule_list` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `schedule_list_groups`
--

DROP TABLE IF EXISTS `schedule_list_groups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `schedule_list_groups` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `schedule_id` int unsigned NOT NULL,
  `group_id` int NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `schedule_id` (`schedule_id`,`group_id`),
  KEY `group_id` (`group_id`),
  CONSTRAINT `schedule_list_groups_ibfk_1` FOREIGN KEY (`schedule_id`) REFERENCES `schedule_list` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `schedule_list_groups_ibfk_2` FOREIGN KEY (`group_id`) REFERENCES `addr_groups` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `schedule_list_groups`
--

LOCK TABLES `schedule_list_groups` WRITE;
/*!40000 ALTER TABLE `schedule_list_groups` DISABLE KEYS */;
/*!40000 ALTER TABLE `schedule_list_groups` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `shedule`
--

DROP TABLE IF EXISTS `shedule`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `shedule` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `generator` int unsigned NOT NULL,
  `method` varchar(50) NOT NULL,
  `created` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `start` timestamp NULL DEFAULT NULL,
  `request` varchar(600) DEFAULT NULL,
  `response` text,
  `begin` timestamp NULL DEFAULT NULL,
  `finished` timestamp NULL DEFAULT NULL,
  `code` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `generator` (`generator`),
  KEY `action` (`method`),
  CONSTRAINT `shedule_ibfk_1` FOREIGN KEY (`generator`) REFERENCES `shedule_kinds` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `shedule`
--

LOCK TABLES `shedule` WRITE;
/*!40000 ALTER TABLE `shedule` DISABLE KEYS */;
/*!40000 ALTER TABLE `shedule` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `shedule_kinds`
--

DROP TABLE IF EXISTS `shedule_kinds`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `shedule_kinds` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `parent` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `shedule_kinds`
--

LOCK TABLES `shedule_kinds` WRITE;
/*!40000 ALTER TABLE `shedule_kinds` DISABLE KEYS */;
INSERT INTO `shedule_kinds` VALUES (17,'Автокредитовалка',NULL,NULL),(18,'АвтоЗаморозка',NULL,17),(19,'Авторозморозка, платеж',NULL,NULL),(23,'Платежка, LiqPay - разморозка',NULL,NULL),(24,'Пингер, отправка СМС',NULL,NULL),(25,'ЛК, новая заявка',NULL,NULL),(26,'Заявка на отключение',NULL,NULL);
/*!40000 ALTER TABLE `shedule_kinds` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `smsOutgoing`
--

DROP TABLE IF EXISTS `smsOutgoing`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `smsOutgoing` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `created` timestamp NULL DEFAULT NULL,
  `sended` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `type` int DEFAULT NULL,
  `phone` varchar(17) DEFAULT NULL,
  `message` varchar(160) DEFAULT NULL,
  `uid` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `smsOutgoing`
--

LOCK TABLES `smsOutgoing` WRITE;
/*!40000 ALTER TABLE `smsOutgoing` DISABLE KEYS */;
/*!40000 ALTER TABLE `smsOutgoing` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `smsTypes`
--

DROP TABLE IF EXISTS `smsTypes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `smsTypes` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb3 ROW_FORMAT=COMPACT;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `smsTypes`
--

LOCK TABLES `smsTypes` WRITE;
/*!40000 ALTER TABLE `smsTypes` DISABLE KEYS */;
INSERT INTO `smsTypes` VALUES (1,'Не указан'),(2,'Быстрая заявка'),(3,'Заявка с ЛК'),(4,'Рассылка об окончании средств'),(5,'Ручная рассылка'),(6,'Пингер');
/*!40000 ALTER TABLE `smsTypes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sms_send_list`
--

DROP TABLE IF EXISTS `sms_send_list`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sms_send_list` (
  `eid` int unsigned NOT NULL,
  `time` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `type` int unsigned NOT NULL,
  KEY `eid` (`eid`),
  KEY `type` (`type`) USING BTREE,
  CONSTRAINT `eid` FOREIGN KEY (`eid`) REFERENCES `employees` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `types` FOREIGN KEY (`type`) REFERENCES `sms_types` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sms_send_list`
--

LOCK TABLES `sms_send_list` WRITE;
/*!40000 ALTER TABLE `sms_send_list` DISABLE KEYS */;
INSERT INTO `sms_send_list` VALUES (3,'2016-05-16 09:46:43',6),(1,'2019-04-05 19:02:35',5),(3,'2019-04-05 19:02:35',5),(1,'2020-10-07 19:09:49',3),(3,'2020-10-07 19:09:50',3),(2,'2020-10-07 19:09:50',3);
/*!40000 ALTER TABLE `sms_send_list` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sms_types`
--

DROP TABLE IF EXISTS `sms_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sms_types` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(150) DEFAULT NULL,
  `type` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sms_types`
--

LOCK TABLES `sms_types` WRITE;
/*!40000 ALTER TABLE `sms_types` DISABLE KEYS */;
INSERT INTO `sms_types` VALUES (1,'Новая заявка с сайта','SMS'),(3,'Падения оборудования','SMS'),(4,'Падения оборудования','Email'),(5,'Заявка с ЛК','SMS'),(6,'Заявка с ЛК','Email');
/*!40000 ALTER TABLE `sms_types` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `stub_page_results`
--

DROP TABLE IF EXISTS `stub_page_results`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `stub_page_results` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(60) NOT NULL,
  `created_at` datetime NOT NULL ON UPDATE CURRENT_TIMESTAMP,
  `remote_addr` varchar(50) NOT NULL,
  `agreement_id` int DEFAULT NULL,
  `search_result` json DEFAULT NULL,
  `error` varchar(255) DEFAULT NULL,
  `old_mac_addr` varchar(40) DEFAULT NULL,
  `new_mac_addr` varchar(40) DEFAULT NULL,
  `binding_id` int DEFAULT NULL,
  `binding_updated_at` datetime DEFAULT NULL,
  `binding_update_result` json DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uuid` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `stub_page_results`
--

LOCK TABLES `stub_page_results` WRITE;
/*!40000 ALTER TABLE `stub_page_results` DISABLE KEYS */;
/*!40000 ALTER TABLE `stub_page_results` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `system_events`
--

DROP TABLE IF EXISTS `system_events`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `system_events` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `event_type` varchar(255) NOT NULL DEFAULT '',
  `data` json DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `system_events`
--

LOCK TABLES `system_events` WRITE;
/*!40000 ALTER TABLE `system_events` DISABLE KEYS */;
/*!40000 ALTER TABLE `system_events` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `trinity_bindings`
--

DROP TABLE IF EXISTS `trinity_bindings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `trinity_bindings` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `created` datetime NOT NULL,
  `activation` int NOT NULL,
  `contract` int unsigned DEFAULT NULL,
  `device_id` int DEFAULT NULL,
  `mac` varchar(50) DEFAULT NULL,
  `uuid` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `employee` int NOT NULL,
  `local_playlist_id` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `mac` (`mac`,`uuid`,`local_playlist_id`),
  KEY `contract` (`contract`),
  CONSTRAINT `trinity_bindings_ibfk_1` FOREIGN KEY (`contract`) REFERENCES `trinity_contracts` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `trinity_bindings`
--

LOCK TABLES `trinity_bindings` WRITE;
/*!40000 ALTER TABLE `trinity_bindings` DISABLE KEYS */;
/*!40000 ALTER TABLE `trinity_bindings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `trinity_contracts`
--

DROP TABLE IF EXISTS `trinity_contracts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `trinity_contracts` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `subscr_id` int DEFAULT NULL,
  `subscr_price` decimal(8,2) DEFAULT NULL,
  `subscr_status_id` int DEFAULT NULL,
  `contract_trinity` int DEFAULT NULL,
  `devices_count` int DEFAULT NULL,
  `contract_date` date DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `trinity_contracts`
--

LOCK TABLES `trinity_contracts` WRITE;
/*!40000 ALTER TABLE `trinity_contracts` DISABLE KEYS */;
/*!40000 ALTER TABLE `trinity_contracts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `trinity_price_binding`
--

DROP TABLE IF EXISTS `trinity_price_binding`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `trinity_price_binding` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `trinity_price_id` int DEFAULT NULL,
  `local_price_id` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `trinity_price_binding`
--

LOCK TABLES `trinity_price_binding` WRITE;
/*!40000 ALTER TABLE `trinity_price_binding` DISABLE KEYS */;
/*!40000 ALTER TABLE `trinity_price_binding` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Temporary view structure for view `v_employee_statuses`
--

DROP TABLE IF EXISTS `v_employee_statuses`;
/*!50001 DROP VIEW IF EXISTS `v_employee_statuses`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `v_employee_statuses` AS SELECT 
 1 AS `id`,
 1 AS `status`,
 1 AS `from_time`,
 1 AS `last_status_id`*/;
SET character_set_client = @saved_cs_client;

--
-- Temporary view structure for view `v_eq_ping_status`
--

DROP TABLE IF EXISTS `v_eq_ping_status`;
/*!50001 DROP VIEW IF EXISTS `v_eq_ping_status`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `v_eq_ping_status` AS SELECT 
 1 AS `equipment`,
 1 AS `down`,
 1 AS `up`*/;
SET character_set_client = @saved_cs_client;

--
-- Temporary view structure for view `v_reaction_times`
--

DROP TABLE IF EXISTS `v_reaction_times`;
/*!50001 DROP VIEW IF EXISTS `v_reaction_times`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `v_reaction_times` AS SELECT 
 1 AS `reason_id`,
 1 AS `house_id`,
 1 AS `reaction_time`*/;
SET character_set_client = @saved_cs_client;

--
-- Temporary view structure for view `v_trinity_contract_stat`
--

DROP TABLE IF EXISTS `v_trinity_contract_stat`;
/*!50001 DROP VIEW IF EXISTS `v_trinity_contract_stat`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `v_trinity_contract_stat` AS SELECT 
 1 AS `id`,
 1 AS `trinity_price_id`,
 1 AS `contract_trinity`,
 1 AS `count_on_bindings`,
 1 AS `count`*/;
SET character_set_client = @saved_cs_client;

--
-- Temporary view structure for view `view_omo_agreement_users`
--

DROP TABLE IF EXISTS `view_omo_agreement_users`;
/*!50001 DROP VIEW IF EXISTS `view_omo_agreement_users`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `view_omo_agreement_users` AS SELECT 
 1 AS `id`,
 1 AS `created_at`,
 1 AS `uid`,
 1 AS `phone`,
 1 AS `agreement_id`*/;
SET character_set_client = @saved_cs_client;

--
-- Temporary view structure for view `walker_arp_fdb`
--

DROP TABLE IF EXISTS `walker_arp_fdb`;
/*!50001 DROP VIEW IF EXISTS `walker_arp_fdb`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `walker_arp_fdb` AS SELECT 
 1 AS `time`,
 1 AS `switch`,
 1 AS `port`,
 1 AS `mac`,
 1 AS `vlan`,
 1 AS `router`,
 1 AS `ip`*/;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `walker_fdb`
--

DROP TABLE IF EXISTS `walker_fdb`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `walker_fdb` (
  `switch` varchar(15) NOT NULL,
  `port` int NOT NULL,
  `mac` varchar(50) NOT NULL,
  `vlan_id` int NOT NULL,
  `start_at` datetime NOT NULL,
  `stop_at` datetime DEFAULT NULL,
  `actualized` tinyint DEFAULT NULL,
  UNIQUE KEY `switch` (`switch`,`port`,`mac`,`vlan_id`),
  KEY `actualized` (`actualized`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `walker_fdb`
--

LOCK TABLES `walker_fdb` WRITE;
/*!40000 ALTER TABLE `walker_fdb` DISABLE KEYS */;
/*!40000 ALTER TABLE `walker_fdb` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Temporary view structure for view `walker_incorrect_binding_mac`
--

DROP TABLE IF EXISTS `walker_incorrect_binding_mac`;
/*!50001 DROP VIEW IF EXISTS `walker_incorrect_binding_mac`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `walker_incorrect_binding_mac` AS SELECT 
 1 AS `agreement`,
 1 AS `binding_id`,
 1 AS `mac_in_binding`,
 1 AS `real_mac`,
 1 AS `vlan_id`*/;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `walker_topology`
--

DROP TABLE IF EXISTS `walker_topology`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `walker_topology` (
  `src_mac` varchar(50) DEFAULT NULL,
  `src_port` int DEFAULT NULL,
  `dest_mac` varchar(50) DEFAULT NULL,
  `dest_port` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `walker_topology`
--

LOCK TABLES `walker_topology` WRITE;
/*!40000 ALTER TABLE `walker_topology` DISABLE KEYS */;
/*!40000 ALTER TABLE `walker_topology` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `walkers_arps`
--

DROP TABLE IF EXISTS `walkers_arps`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `walkers_arps` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `created_at` datetime NOT NULL ON UPDATE CURRENT_TIMESTAMP,
  `router` varchar(15) NOT NULL,
  `ip` varchar(15) NOT NULL,
  `mac` varchar(50) NOT NULL,
  `vlan` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `walkers_arps`
--

LOCK TABLES `walkers_arps` WRITE;
/*!40000 ALTER TABLE `walkers_arps` DISABLE KEYS */;
/*!40000 ALTER TABLE `walkers_arps` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `walkers_untag_ports`
--

DROP TABLE IF EXISTS `walkers_untag_ports`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `walkers_untag_ports` (
  `switch` varchar(15) NOT NULL,
  `port` int NOT NULL,
  `vlan_id` int NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `walkers_untag_ports`
--

LOCK TABLES `walkers_untag_ports` WRITE;
/*!40000 ALTER TABLE `walkers_untag_ports` DISABLE KEYS */;
/*!40000 ALTER TABLE `walkers_untag_ports` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `walkers_untag_ports_fdb`
--

DROP TABLE IF EXISTS `walkers_untag_ports_fdb`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `walkers_untag_ports_fdb` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `created_at` datetime NOT NULL ON UPDATE CURRENT_TIMESTAMP,
  `switch` varchar(15) NOT NULL,
  `port` int NOT NULL,
  `mac` varchar(50) NOT NULL,
  `vlan` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `walkers_untag_ports_fdb`
--

LOCK TABLES `walkers_untag_ports_fdb` WRITE;
/*!40000 ALTER TABLE `walkers_untag_ports_fdb` DISABLE KEYS */;
/*!40000 ALTER TABLE `walkers_untag_ports_fdb` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping routines for database 'service'
--
/*!50003 DROP FUNCTION IF EXISTS `days_in_month` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'STRICT_TRANS_TABLES,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO' */ ;
DELIMITER ;;
CREATE DEFINER=`service`@`localhost` FUNCTION `days_in_month`() RETURNS int
BEGIN
    RETURN DAYOFMONTH(LAST_DAY(NOW()));
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP FUNCTION IF EXISTS `days_to_end_month` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'STRICT_TRANS_TABLES,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO' */ ;
DELIMITER ;;
CREATE DEFINER=`service`@`localhost` FUNCTION `days_to_end_month`() RETURNS int
BEGIN
    RETURN DAYOFMONTH(LAST_DAY(NOW())) - DAYOFMONTH(NOW()) + 1;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP FUNCTION IF EXISTS `first_date` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'STRICT_TRANS_TABLES,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO' */ ;
DELIMITER ;;
CREATE DEFINER=`service`@`localhost` FUNCTION `first_date`(`curr_date` datetime) RETURNS datetime
BEGIN
    return date_add(curr_date,interval -DAY(curr_date)+1 DAY)  ;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP FUNCTION IF EXISTS `get_free_agreement` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'STRICT_TRANS_TABLES,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO' */ ;
DELIMITER ;;
CREATE DEFINER=`service`@`localhost` FUNCTION `get_free_agreement`() RETURNS int
    READS SQL DATA
    DETERMINISTIC
BEGIN
	
SELECT agree into @freeAgree
FROM (
SELECT agreement + 1 agree FROM clients  WHERE agreement > 0 
) c 
LEFT JOIN clients exist on exist.agreement = c.agree 
WHERE exist.agreement is null and c.agree > 0 
ORDER BY 1 
LIMIT 1;
RETURN @freeAgree;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Final view structure for view `addr`
--

/*!50001 DROP VIEW IF EXISTS `addr`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `addr` AS select `h`.`id` AS `id`,`c`.`name` AS `city`,`st`.`name` AS `street`,`h`.`name` AS `house`,concat(`c`.`name`,', ',`st`.`name`,', ',`h`.`name`) AS `full_addr`,`g`.`id` AS `group_id`,`g`.`name` AS `group_name` from (((`addr_houses` `h` join `addr_streets` `st` on((`st`.`id` = `h`.`street`))) join `addr_cities` `c` on((`c`.`id` = `st`.`city`))) left join `addr_groups` `g` on((`g`.`id` = `h`.`group_id`))) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `client_balances_v`
--

/*!50001 DROP VIEW IF EXISTS `client_balances_v`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `client_balances_v` AS select `c`.`id` AS `id`,`c`.`agreement` AS `agreement`,if((`bp`.`price_month` is not null),(`c`.`balance` - ((sum(`bp`.`price_month`) / `days_in_month`()) * `days_to_end_month`())),`c`.`balance`) AS `balance` from ((`clients` `c` left join `client_prices` `p` on(((`p`.`agreement` = `c`.`id`) and (`p`.`time_stop` is null)))) left join `bill_prices` `bp` on(((`bp`.`id` = `p`.`price`) and (`bp`.`recalc_time` = 'month')))) group by `c`.`id`,`c`.`agreement`,`c`.`balance` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `client_disable_days_last`
--

/*!50001 DROP VIEW IF EXISTS `client_disable_days_last`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `client_disable_days_last` AS select `d`.`id` AS `id`,`d`.`created` AS `created`,`d`.`created_employee` AS `created_employee`,`d`.`client` AS `client`,`d`.`days` AS `days` from (`client_disable_days` `d` join (select max(`client_disable_days`.`id`) AS `id`,`client_disable_days`.`client` AS `client` from `client_disable_days` group by `client_disable_days`.`client`) `l` on((`l`.`id` = `d`.`id`))) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `questions_full`
--

/*!50001 DROP VIEW IF EXISTS `questions_full`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `questions_full` AS select `q`.`id` AS `id`,`q`.`agreement` AS `agreement`,`comments`.`created_at` AS `created`,`q`.`phone` AS `phone`,`r`.`id` AS `reason_id`,`r`.`name` AS `reason`,`comments`.`comment` AS `comment`,`comments`.`dest_time` AS `dest_time`,`comments`.`employee` AS `created_employee`,`reports`.`employee` AS `reported_employee`,`reports`.`status` AS `report_status`,`reports`.`created_at` AS `report_time`,`reports`.`id` AS `report_id`,`reports`.`comment` AS `report_comment`,`comments`.`responsible_employee` AS `responsible_employee`,`reports`.`amount` AS `amount`,`reports`.`cert_of_completion` AS `cert_of_completion`,`reports`.`cert_subscribed` AS `cert_subscribed` from (((((`questions` `q` left join `question_reason` `r` on((`r`.`id` = `q`.`reason`))) left join (select max(`question_responses`.`id`) AS `id`,`question_responses`.`question` AS `question` from `question_responses` group by `question_responses`.`question`) `l_resp` on((`l_resp`.`question` = `q`.`id`))) left join (select max(`question_comments`.`id`) AS `id`,`question_comments`.`question` AS `question` from `question_comments` group by `question_comments`.`question`) `l_comment` on((`l_comment`.`question` = `q`.`id`))) left join `question_comments` `comments` on((`comments`.`id` = `l_comment`.`id`))) left join `question_responses` `reports` on((`reports`.`id` = `l_resp`.`id`))) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `radius_binding_status`
--

/*!50001 DROP VIEW IF EXISTS `radius_binding_status`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `radius_binding_status` AS select `b`.`id` AS `binding_id`,`b`.`mac` AS `binding_mac`,`a`.`mac` AS `real_mac`,`a`.`switch` AS `switch`,`a`.`port` AS `port`,`a`.`hostname` AS `hostname`,'registered' AS `status`,`b`.`ip` AS `binding_ip`,`a`.`ip` AS `attached_ip`,if((`a`.`stop` is null),'active',`a`.`stop`) AS `active` from (((`radius_acct` `a` join (select max(`radius_acct`.`id`) AS `id` from `radius_acct` group by `radius_acct`.`mac`,`radius_acct`.`switch`,`radius_acct`.`port`) `f` on((`f`.`id` = `a`.`id`))) join `equipment` `e` on((`e`.`ip` = `a`.`switch`))) join `eq_bindings` `b` on(((`b`.`mac` = `a`.`mac`) and (`e`.`id` = `b`.`switch`) and (`a`.`port` = `b`.`port`)))) union all select `b`.`id` AS `binding_id`,`b`.`mac` AS `binding_mac`,`a`.`mac` AS `real_mac`,`a`.`switch` AS `switch`,`a`.`port` AS `port`,`a`.`hostname` AS `hostname`,'unregistered' AS `status`,`b`.`ip` AS `binding_ip`,`a`.`ip` AS `attached_ip`,if((`a`.`stop` is null),'active',`a`.`stop`) AS `active` from ((((`radius_acct` `a` join (select max(`radius_acct`.`id`) AS `id` from `radius_acct` group by `radius_acct`.`mac`,`radius_acct`.`switch`,`radius_acct`.`port`) `f` on((`f`.`id` = `a`.`id`))) join `equipment` `e` on((`e`.`ip` = `a`.`switch`))) join `eq_bindings` `b` on(((`e`.`id` = `b`.`switch`) and (`a`.`port` = `b`.`port`)))) left join (select `b`.`id` AS `id` from (((`radius_acct` `a` join (select max(`radius_acct`.`id`) AS `id` from `radius_acct` group by `radius_acct`.`mac`,`radius_acct`.`switch`,`radius_acct`.`port`) `f` on((`f`.`id` = `a`.`id`))) join `equipment` `e` on((`e`.`ip` = `a`.`switch`))) join `eq_bindings` `b` on(((`b`.`mac` = `a`.`mac`) and (`e`.`id` = `b`.`switch`) and (`a`.`port` = `b`.`port`))))) `uns` on((`uns`.`id` = `b`.`id`))) where (`uns`.`id` is null) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `v_employee_statuses`
--

/*!50001 DROP VIEW IF EXISTS `v_employee_statuses`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `v_employee_statuses` AS select `e`.`id` AS `id`,ifnull(if((`s`.`stop` is null),`s`.`status`,'OFFLINE'),'OFFLINE') AS `status`,ifnull(convert(if((`s`.`stop` is not null),`s`.`stop`,`s`.`start`) using utf8mb4),'0000-00-00 00:00:00') AS `from_time`,`l`.`last_id` AS `last_status_id` from ((`employees` `e` left join (select max(`employee_work_statuses`.`id`) AS `last_id`,`employee_work_statuses`.`employee_id` AS `employee_id` from `employee_work_statuses` group by `employee_work_statuses`.`employee_id`) `l` on((`l`.`employee_id` = `e`.`id`))) left join `employee_work_statuses` `s` on((`s`.`id` = `l`.`last_id`))) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `v_eq_ping_status`
--

/*!50001 DROP VIEW IF EXISTS `v_eq_ping_status`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `v_eq_ping_status` AS select `equipment`.`id` AS `equipment`,`equipment`.`last_ping` AS `down`,NULL AS `up` from `equipment` where (`equipment`.`ping` < 0) union select `l`.`equipment` AS `equipment`,`l`.`down` AS `down`,`l`.`up` AS `up` from (`eq_pinger_log` `l` join `equipment` `e` on((`e`.`id` = `l`.`equipment`))) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `v_reaction_times`
--

/*!50001 DROP VIEW IF EXISTS `v_reaction_times`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `v_reaction_times` AS select `r`.`id` AS `reason_id`,`h`.`id` AS `house_id`,round((`r`.`reaction_time` * `gr`.`reaction_factor`),0) AS `reaction_time` from (`question_reason` `r` join (`addr_houses` `h` join `addr_groups` `gr` on((`gr`.`id` = `h`.`group_id`)))) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `v_trinity_contract_stat`
--

/*!50001 DROP VIEW IF EXISTS `v_trinity_contract_stat`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `v_trinity_contract_stat` AS select `c`.`id` AS `id`,`c`.`subscr_id` AS `trinity_price_id`,`c`.`contract_trinity` AS `contract_trinity`,if((`lc`.`count` is null),0,`lc`.`count`) AS `count_on_bindings`,`c`.`devices_count` AS `count` from (`trinity_contracts` `c` left join (select `b`.`contract` AS `contract`,count(0) AS `count` from ((`trinity_bindings` `b` join `client_prices` `p` on((`p`.`id` = `b`.`activation`))) join `trinity_price_binding` `tpb` on((`tpb`.`local_price_id` = `p`.`price`))) where (`b`.`contract` is not null) group by `b`.`contract`) `lc` on((`lc`.`contract` = `c`.`id`))) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `view_omo_agreement_users`
--

/*!50001 DROP VIEW IF EXISTS `view_omo_agreement_users`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `view_omo_agreement_users` AS select ifnull(`b`.`id`,`u`.`id`) AS `id`,`u`.`created_at` AS `created_at`,`u`.`uid` AS `uid`,`u`.`phone` AS `phone`,`b`.`agreement_id` AS `agreement_id` from (`omo_users` `u` left join `omo_agreement_bindings` `b` on((`b`.`user_id` = `u`.`id`))) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `walker_arp_fdb`
--

/*!50001 DROP VIEW IF EXISTS `walker_arp_fdb`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `walker_arp_fdb` AS select `f`.`time` AS `time`,`f`.`switch` AS `switch`,`f`.`port` AS `port`,`f`.`mac` AS `mac`,`f`.`vlan` AS `vlan`,`a`.`router` AS `router`,`a`.`ip` AS `ip` from ((select max(`walkers_untag_ports_fdb`.`created_at`) AS `time`,`walkers_untag_ports_fdb`.`switch` AS `switch`,`walkers_untag_ports_fdb`.`port` AS `port`,`walkers_untag_ports_fdb`.`mac` AS `mac`,`walkers_untag_ports_fdb`.`vlan` AS `vlan` from `walkers_untag_ports_fdb` group by `walkers_untag_ports_fdb`.`switch`,`walkers_untag_ports_fdb`.`port`,`walkers_untag_ports_fdb`.`mac`,`walkers_untag_ports_fdb`.`vlan`) `f` left join (select max(`walkers_arps`.`created_at`) AS `time`,`walkers_arps`.`router` AS `router`,`walkers_arps`.`ip` AS `ip`,`walkers_arps`.`mac` AS `mac`,`walkers_arps`.`vlan` AS `vlan` from `walkers_arps` group by `walkers_arps`.`router`,`walkers_arps`.`ip`,`walkers_arps`.`mac`,`walkers_arps`.`vlan`) `a` on(((`a`.`mac` = `f`.`mac`) and (`a`.`vlan` = `f`.`vlan`)))) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `walker_incorrect_binding_mac`
--

/*!50001 DROP VIEW IF EXISTS `walker_incorrect_binding_mac`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `walker_incorrect_binding_mac` AS select `c`.`agreement` AS `agreement`,`b`.`id` AS `binding_id`,`b`.`mac` AS `mac_in_binding`,`w`.`mac` AS `real_mac`,`w`.`vlan` AS `vlan_id` from ((((((`clients` `c` join `client_prices` `p` on((`p`.`agreement` = `c`.`id`))) join `eq_bindings` `b` on((`b`.`activation` = `p`.`id`))) join `equipment` `e` on((`e`.`id` = `b`.`switch`))) left join (select `b`.`switch` AS `switch`,`b`.`port` AS `port` from (`client_prices` `p` join `eq_bindings` `b` on((`b`.`activation` = `p`.`id`))) where (`p`.`time_stop` is null) group by `b`.`switch`,`b`.`port` having (count(0) > 1)) `excl` on(((`excl`.`switch` = `e`.`ip`) and (`excl`.`port` = `b`.`port`)))) left join (select `walker_arp_fdb`.`switch` AS `switch`,`walker_arp_fdb`.`port` AS `port`,count(0) AS `count_mac` from `walker_arp_fdb` group by `walker_arp_fdb`.`switch`,`walker_arp_fdb`.`port` having (count(0) > 1)) `excl_real` on(((`excl_real`.`switch` = `e`.`ip`) and (`excl_real`.`port` = `b`.`port`)))) join `walker_arp_fdb` `w` on(((`w`.`switch` = `e`.`ip`) and (`b`.`port` = `w`.`port`)))) where ((`p`.`time_stop` is null) and (`excl`.`switch` is null) and (`excl_real`.`switch` is null) and (`w`.`mac` <> `b`.`mac`)) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2022-01-23 15:01:13
