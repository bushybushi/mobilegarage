/*M!999999\- enable the sandbox mode */ 
-- MariaDB dump 10.19  Distrib 10.6.21-MariaDB, for Linux (x86_64)
--
-- Host: localhost    Database: webvaria_MobileGarageLarnaca
-- ------------------------------------------------------
-- Server version	10.6.21-MariaDB

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
-- Table structure for table `addresses`
--

DROP TABLE IF EXISTS `addresses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `addresses` (
  `CustomerID` int(11) NOT NULL,
  `Address` varchar(60) NOT NULL,
  KEY `CustomerID` (`CustomerID`),
  CONSTRAINT `addresses_ibfk_1` FOREIGN KEY (`CustomerID`) REFERENCES `customers` (`CustomerID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `addresses`
--

LOCK TABLES `addresses` WRITE;
/*!40000 ALTER TABLE `addresses` DISABLE KEYS */;
INSERT INTO `addresses` VALUES (58,'arch. makariou3'),(59,'gsdiyrweg');
/*!40000 ALTER TABLE `addresses` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `carassoc`
--

DROP TABLE IF EXISTS `carassoc`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `carassoc` (
  `CustomerID` int(11) NOT NULL,
  `LicenseNr` varchar(10) NOT NULL,
  KEY `CustomerID` (`CustomerID`),
  KEY `LicenseNr` (`LicenseNr`),
  CONSTRAINT `carassoc_ibfk_1` FOREIGN KEY (`CustomerID`) REFERENCES `customers` (`CustomerID`),
  CONSTRAINT `carassoc_ibfk_2` FOREIGN KEY (`LicenseNr`) REFERENCES `cars` (`LicenseNr`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `carassoc`
--

LOCK TABLES `carassoc` WRITE;
/*!40000 ALTER TABLE `carassoc` DISABLE KEYS */;
INSERT INTO `carassoc` VALUES (58,'DAG272'),(59,'sf');
/*!40000 ALTER TABLE `carassoc` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cars`
--

DROP TABLE IF EXISTS `cars`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `cars` (
  `LicenseNr` varchar(10) NOT NULL,
  `Brand` varchar(50) NOT NULL,
  `Model` varchar(50) NOT NULL,
  `VIN` varchar(17) NOT NULL,
  `ManuDate` date DEFAULT NULL,
  `Fuel` varchar(20) DEFAULT NULL,
  `KWHorse` float DEFAULT NULL,
  `Engine` varchar(30) DEFAULT NULL,
  `KMMiles` float DEFAULT NULL,
  `Color` varchar(30) DEFAULT NULL,
  `Comments` varchar(500) DEFAULT NULL,
  PRIMARY KEY (`LicenseNr`),
  UNIQUE KEY `VIN` (`VIN`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cars`
--

LOCK TABLES `cars` WRITE;
/*!40000 ALTER TABLE `cars` DISABLE KEYS */;
INSERT INTO `cars` VALUES ('DAG272','Toyota','Yaris','-----','2025-04-14','petrol',NULL,'fgs3',245,'red',NULL),('sf','dfsd','sd','sdf','0000-00-00','ee',222,'eee',222,'res',NULL);
/*!40000 ALTER TABLE `cars` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `customers`
--

DROP TABLE IF EXISTS `customers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `customers` (
  `CustomerID` int(11) NOT NULL AUTO_INCREMENT,
  `FirstName` varchar(20) NOT NULL,
  `LastName` varchar(20) DEFAULT NULL,
  `Company` varchar(40) DEFAULT NULL,
  PRIMARY KEY (`CustomerID`),
  UNIQUE KEY `CustomerID` (`CustomerID`)
) ENGINE=InnoDB AUTO_INCREMENT=60 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `customers`
--

LOCK TABLES `customers` WRITE;
/*!40000 ALTER TABLE `customers` DISABLE KEYS */;
INSERT INTO `customers` VALUES (58,'Jorgos','Xidias',''),(59,'antreas','antreou','');
/*!40000 ALTER TABLE `customers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `emails`
--

DROP TABLE IF EXISTS `emails`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `emails` (
  `CustomerID` int(11) NOT NULL,
  `Emails` varchar(100) NOT NULL,
  KEY `CustomerID` (`CustomerID`),
  CONSTRAINT `emails_ibfk_1` FOREIGN KEY (`CustomerID`) REFERENCES `customers` (`CustomerID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `emails`
--

LOCK TABLES `emails` WRITE;
/*!40000 ALTER TABLE `emails` DISABLE KEYS */;
INSERT INTO `emails` VALUES (58,'pampos@gmail.com');
/*!40000 ALTER TABLE `emails` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `extraexpenses`
--

DROP TABLE IF EXISTS `extraexpenses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `extraexpenses` (
  `ExpenseID` int(11) NOT NULL AUTO_INCREMENT,
  `Description` varchar(50) NOT NULL,
  `DateCreated` date NOT NULL,
  `Expense` decimal(10,2) NOT NULL DEFAULT 0.00,
  PRIMARY KEY (`ExpenseID`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `extraexpenses`
--

LOCK TABLES `extraexpenses` WRITE;
/*!40000 ALTER TABLE `extraexpenses` DISABLE KEYS */;
INSERT INTO `extraexpenses` VALUES (5,'New equipment','2025-04-14',2000.00);
/*!40000 ALTER TABLE `extraexpenses` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `invoicejob`
--

DROP TABLE IF EXISTS `invoicejob`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `invoicejob` (
  `JobID` int(11) NOT NULL,
  `InvoiceID` int(11) NOT NULL,
  KEY `JobID` (`JobID`),
  KEY `InvoiceID` (`InvoiceID`),
  CONSTRAINT `invoicejob_ibfk_1` FOREIGN KEY (`JobID`) REFERENCES `jobcards` (`JobID`),
  CONSTRAINT `invoicejob_ibfk_2` FOREIGN KEY (`InvoiceID`) REFERENCES `invoices` (`InvoiceID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `invoicejob`
--

LOCK TABLES `invoicejob` WRITE;
/*!40000 ALTER TABLE `invoicejob` DISABLE KEYS */;
INSERT INTO `invoicejob` VALUES (31,43),(32,45),(32,46),(31,47),(32,48);
/*!40000 ALTER TABLE `invoicejob` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `invoices`
--

DROP TABLE IF EXISTS `invoices`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `invoices` (
  `InvoiceID` int(11) NOT NULL AUTO_INCREMENT,
  `InvoiceNr` int(11) DEFAULT NULL,
  `DateCreated` date DEFAULT NULL,
  `Vat` decimal(10,2) DEFAULT NULL,
  `Total` decimal(10,2) DEFAULT NULL,
  `PDF` blob DEFAULT NULL,
  PRIMARY KEY (`InvoiceID`),
  UNIQUE KEY `InvoiceID` (`InvoiceID`)
) ENGINE=InnoDB AUTO_INCREMENT=49 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `invoices`
--

LOCK TABLES `invoices` WRITE;
/*!40000 ALTER TABLE `invoices` DISABLE KEYS */;
INSERT INTO `invoices` VALUES (43,1,'2025-04-14',0.00,40.00,NULL),(44,0,'2025-04-15',40.00,5000.00,NULL),(45,1,'2025-04-15',0.00,0.00,NULL),(46,2,'2025-04-15',0.00,0.00,NULL),(47,3,'2025-04-15',0.00,40.00,NULL),(48,4,'2025-04-15',0.00,0.00,NULL);
/*!40000 ALTER TABLE `invoices` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jobcar`
--

DROP TABLE IF EXISTS `jobcar`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `jobcar` (
  `JobID` int(11) NOT NULL,
  `LicenseNr` varchar(10) NOT NULL,
  KEY `JobID` (`JobID`),
  KEY `LicenseNr` (`LicenseNr`),
  CONSTRAINT `jobcar_ibfk_1` FOREIGN KEY (`JobID`) REFERENCES `jobcards` (`JobID`),
  CONSTRAINT `jobcar_ibfk_2` FOREIGN KEY (`LicenseNr`) REFERENCES `cars` (`LicenseNr`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jobcar`
--

LOCK TABLES `jobcar` WRITE;
/*!40000 ALTER TABLE `jobcar` DISABLE KEYS */;
INSERT INTO `jobcar` VALUES (31,'DAG272'),(32,'DAG272');
/*!40000 ALTER TABLE `jobcar` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jobcardparts`
--

DROP TABLE IF EXISTS `jobcardparts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `jobcardparts` (
  `JobID` int(11) NOT NULL,
  `PartID` int(11) NOT NULL,
  `PiecesSold` int(11) NOT NULL,
  `PricePerPiece` decimal(10,2) NOT NULL,
  KEY `JobID` (`JobID`),
  KEY `PartID` (`PartID`),
  CONSTRAINT `jobcardparts_ibfk_1` FOREIGN KEY (`JobID`) REFERENCES `jobcards` (`JobID`),
  CONSTRAINT `jobcardparts_ibfk_2` FOREIGN KEY (`PartID`) REFERENCES `parts` (`PartID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jobcardparts`
--

LOCK TABLES `jobcardparts` WRITE;
/*!40000 ALTER TABLE `jobcardparts` DISABLE KEYS */;
INSERT INTO `jobcardparts` VALUES (31,42,1,0.00);
/*!40000 ALTER TABLE `jobcardparts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jobcards`
--

DROP TABLE IF EXISTS `jobcards`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `jobcards` (
  `JobID` int(11) NOT NULL AUTO_INCREMENT,
  `Location` varchar(60) NOT NULL,
  `DateCall` date DEFAULT NULL,
  `JobDesc` mediumtext DEFAULT NULL,
  `JobReport` mediumtext DEFAULT NULL,
  `DateStart` date DEFAULT NULL,
  `DateFinish` date DEFAULT NULL,
  `Rides` int(11) DEFAULT NULL,
  `DriveCosts` decimal(10,2) DEFAULT NULL,
  `Photo` blob DEFAULT NULL,
  `AdditionalCost` decimal(10,2) DEFAULT 0.00,
  PRIMARY KEY (`JobID`),
  UNIQUE KEY `JobID` (`JobID`)
) ENGINE=InnoDB AUTO_INCREMENT=33 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jobcards`
--

LOCK TABLES `jobcards` WRITE;
/*!40000 ALTER TABLE `jobcards` DISABLE KEYS */;
INSERT INTO `jobcards` VALUES (31,'limasool','2025-04-14','dsfgadul','lhbdfgublafd','2025-04-14','2025-04-15',4,40.00,NULL,0.00),(32,'werew','2025-04-14','rewrew','',NULL,NULL,0,0.00,NULL,0.00);
/*!40000 ALTER TABLE `jobcards` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `parts`
--

DROP TABLE IF EXISTS `parts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `parts` (
  `PartID` int(11) NOT NULL AUTO_INCREMENT,
  `SupplierID` int(11) NOT NULL,
  `PartDesc` varchar(30) NOT NULL,
  `PriceBulk` decimal(10,2) DEFAULT NULL,
  `SellPrice` decimal(10,2) DEFAULT NULL,
  `PiecesPurch` int(11) NOT NULL,
  `PricePerPiece` decimal(10,2) NOT NULL,
  `Vat` decimal(10,2) DEFAULT NULL,
  `DateCreated` date NOT NULL,
  `Sold` int(11) DEFAULT 0,
  `Stock` int(11) DEFAULT 1,
  PRIMARY KEY (`PartID`),
  UNIQUE KEY `PartID` (`PartID`),
  KEY `SupplierID` (`SupplierID`),
  CONSTRAINT `parts_ibfk_1` FOREIGN KEY (`SupplierID`) REFERENCES `suppliers` (`SupplierID`)
) ENGINE=InnoDB AUTO_INCREMENT=47 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `parts`
--

LOCK TABLES `parts` WRITE;
/*!40000 ALTER TABLE `parts` DISABLE KEYS */;
INSERT INTO `parts` VALUES (42,26,'filter',13076.91,44.00,333,33.00,19.00,'2025-04-21',1,332),(44,26,'tires',60.00,60.00,40,50.00,40.00,'2025-04-15',0,40),(45,26,'Restore Part',119.00,10.00,10,10.00,19.00,'2025-04-16',0,10);
/*!40000 ALTER TABLE `parts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `partssupply`
--

DROP TABLE IF EXISTS `partssupply`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `partssupply` (
  `InvoiceID` int(11) NOT NULL,
  `PartID` int(11) NOT NULL,
  KEY `InvoiceID` (`InvoiceID`),
  KEY `PartID` (`PartID`),
  CONSTRAINT `partssupply_ibfk_1` FOREIGN KEY (`InvoiceID`) REFERENCES `invoices` (`InvoiceID`),
  CONSTRAINT `partssupply_ibfk_2` FOREIGN KEY (`PartID`) REFERENCES `parts` (`PartID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `partssupply`
--

LOCK TABLES `partssupply` WRITE;
/*!40000 ALTER TABLE `partssupply` DISABLE KEYS */;
INSERT INTO `partssupply` VALUES (44,44);
/*!40000 ALTER TABLE `partssupply` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `partsupplier`
--

DROP TABLE IF EXISTS `partsupplier`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `partsupplier` (
  `PartID` int(11) NOT NULL,
  `SupplierID` int(11) NOT NULL,
  KEY `PartID` (`PartID`),
  KEY `SupplierID` (`SupplierID`),
  CONSTRAINT `partsupplier_ibfk_1` FOREIGN KEY (`PartID`) REFERENCES `parts` (`PartID`),
  CONSTRAINT `partsupplier_ibfk_2` FOREIGN KEY (`SupplierID`) REFERENCES `suppliers` (`SupplierID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `partsupplier`
--

LOCK TABLES `partsupplier` WRITE;
/*!40000 ALTER TABLE `partsupplier` DISABLE KEYS */;
INSERT INTO `partsupplier` VALUES (42,26),(45,26),(44,26);
/*!40000 ALTER TABLE `partsupplier` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_reset_tokens`
--

DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_reset_tokens` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` varchar(255) NOT NULL,
  `token` varchar(64) NOT NULL,
  `expiry` datetime NOT NULL,
  `used` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `password_reset_tokens_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_reset_tokens`
--

LOCK TABLES `password_reset_tokens` WRITE;
/*!40000 ALTER TABLE `password_reset_tokens` DISABLE KEYS */;
INSERT INTO `password_reset_tokens` VALUES (1,'stylianosk','902fbec24fbf9bb409174daa97178725c18e46c6e073a160572a753ce656f578','2025-04-10 02:13:56',0,'2025-04-09 20:13:56'),(2,'stylianosk','6d85b10cc50137e0222efa48fa60be05e47f2959c9c9df9b21b7b79bf371eb5b','2025-04-11 03:11:26',0,'2025-04-10 21:11:26'),(3,'stylianosk','e43c1ceee012c34ec791a3dfd49a273637f6659ad04f6e6fd2e347ea06b03d8a','2025-04-14 20:49:10',0,'2025-04-14 14:49:10'),(4,'stylianosk','948bc85fd30c3db43368e5607515cd76fd0e35f7a5998bf5184282eb8878dba2','2025-04-14 22:26:25',0,'2025-04-14 16:26:25'),(5,'Marinos','9d15f24870e9357bfcc75dff7870f61555f09c282f78ecb997d7f310793640c0','2025-04-14 22:28:20',0,'2025-04-14 16:28:20');
/*!40000 ALTER TABLE `password_reset_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `phonenumbers`
--

DROP TABLE IF EXISTS `phonenumbers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `phonenumbers` (
  `CustomerID` int(11) NOT NULL AUTO_INCREMENT,
  `Nr` varchar(15) NOT NULL,
  KEY `CustomerID` (`CustomerID`),
  CONSTRAINT `phonenumbers_ibfk_1` FOREIGN KEY (`CustomerID`) REFERENCES `customers` (`CustomerID`)
) ENGINE=InnoDB AUTO_INCREMENT=60 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `phonenumbers`
--

LOCK TABLES `phonenumbers` WRITE;
/*!40000 ALTER TABLE `phonenumbers` DISABLE KEYS */;
INSERT INTO `phonenumbers` VALUES (58,'99678467'),(59,'3456');
/*!40000 ALTER TABLE `phonenumbers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `security_questions`
--

DROP TABLE IF EXISTS `security_questions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `security_questions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `question` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `security_questions`
--

LOCK TABLES `security_questions` WRITE;
/*!40000 ALTER TABLE `security_questions` DISABLE KEYS */;
INSERT INTO `security_questions` VALUES (1,'What was your first pet\'s name?'),(2,'What is your mother\'s maiden name?'),(3,'What was the name of your first school?'),(4,'What city were you born in?'),(5,'What is your favorite book?');
/*!40000 ALTER TABLE `security_questions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `suppliers`
--

DROP TABLE IF EXISTS `suppliers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `suppliers` (
  `SupplierID` int(11) NOT NULL AUTO_INCREMENT,
  `Name` varchar(40) NOT NULL,
  `PhoneNr` varchar(15) DEFAULT NULL,
  `Email` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`SupplierID`),
  UNIQUE KEY `SupplierID` (`SupplierID`)
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `suppliers`
--

LOCK TABLES `suppliers` WRITE;
/*!40000 ALTER TABLE `suppliers` DISABLE KEYS */;
INSERT INTO `suppliers` VALUES (26,'Pampo','77484848','pampos@gmail.com');
/*!40000 ALTER TABLE `suppliers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `username` varchar(32) NOT NULL,
  `passwrd` varchar(128) NOT NULL,
  `email` varchar(100) NOT NULL,
  `admin` tinyint(1) DEFAULT 0,
  `security_question_id` int(11) DEFAULT NULL,
  `security_answer` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`username`),
  UNIQUE KEY `username` (`username`),
  KEY `security_question_id` (`security_question_id`),
  CONSTRAINT `users_ibfk_1` FOREIGN KEY (`security_question_id`) REFERENCES `security_questions` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES ('Marinos','$2y$10$UEsX2s8VoaAb3Do4BtyNx.e8cGwlZi3ptzWJQ.7CzBBPZAXcD4XAK','pampos@gmail.com',0,2,'pampos'),('stylianosk','$2y$10$Yq7Jq11IaA9YMO/NkqzOFO7WTILk.ZN0togykuoPAwUI1GVecrLyi','sty@gmail.com',1,1,'pampos');
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

-- Dump completed on 2025-04-15 21:23:26
