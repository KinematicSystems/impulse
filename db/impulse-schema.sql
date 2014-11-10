-- MySQL dump 10.13  Distrib 5.6.16, for osx10.7 (x86_64)
--
-- Host: localhost    Database: impulse
-- ------------------------------------------------------
-- Server version	5.6.16

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `forum`
--

DROP TABLE IF EXISTS `forum`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `forum` (
  `id` char(36) NOT NULL,
  `name` varchar(255) NOT NULL,
  `owner` varchar(32) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `forum_file_metadata`
--

DROP TABLE IF EXISTS `forum_file_metadata`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `forum_file_metadata` (
  `nodeId` char(36) NOT NULL,
  `contentType` varchar(255) NOT NULL,
  `label` varchar(255) DEFAULT NULL,
  `description` text,
  `authorId` bigint(20) NOT NULL,
  `authorName` varchar(255) NOT NULL,
  `creationDate` datetime NOT NULL,
  `editorName` varchar(255) DEFAULT NULL,
  `editedDate` datetime DEFAULT NULL,
  PRIMARY KEY (`nodeId`),
  KEY `sourceId` (`nodeId`),
  CONSTRAINT `forum_file_metadata_ibfk_1` FOREIGN KEY (`nodeId`) REFERENCES `forum_file_node` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `forum_file_node`
--

DROP TABLE IF EXISTS `forum_file_node`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `forum_file_node` (
  `id` char(36) NOT NULL,
  `forumId` char(36) NOT NULL,
  `parentId` char(36) NOT NULL,
  `name` varchar(255) NOT NULL,
  `contentType` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `ContentTypeIdx` (`contentType`),
  KEY `forumId` (`forumId`),
  KEY `folderId` (`parentId`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `forum_log`
--

DROP TABLE IF EXISTS `forum_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `forum_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `forumId` varchar(36) NOT NULL,
  `userId` varchar(45) NOT NULL,
  `entryDate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `content` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=215 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `forum_user`
--

DROP TABLE IF EXISTS `forum_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `forum_user` (
  `forumId` char(36) NOT NULL,
  `userId` varchar(32) NOT NULL,
  `enrollmentStatus` char(1) NOT NULL,
  `lastUpdated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`forumId`,`userId`),
  KEY `user_account_id_idx` (`userId`),
  CONSTRAINT `forum_id` FOREIGN KEY (`forumId`) REFERENCES `forum` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `user_account_id` FOREIGN KEY (`userId`) REFERENCES `user_account` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `properties`
--

DROP TABLE IF EXISTS `properties`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `properties` (
  `id` varchar(32) NOT NULL,
  `section` varchar(32) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `section_idx` (`section`,`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `system_setting`
--

DROP TABLE IF EXISTS `system_setting`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `system_setting` (
  `domain` varchar(255) NOT NULL,
  `settingKey` varchar(255) NOT NULL,
  `value` text,
  `type` varchar(255) NOT NULL,
  `parent` varchar(255) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`domain`,`settingKey`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `user_account`
--

DROP TABLE IF EXISTS `user_account`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_account` (
  `id` varchar(32) NOT NULL,
  `firstName` varchar(255) NOT NULL,
  `lastName` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `enabled` tinyint(1) NOT NULL,
  `sysadmin` tinyint(1) NOT NULL DEFAULT '0',
  `sysuser` tinyint(4) NOT NULL DEFAULT '0',
  `initials` varchar(255) DEFAULT NULL,
  `middleName` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `organization` varchar(255) DEFAULT NULL,
  `phone` varchar(45) DEFAULT NULL,
  `poc` varchar(255) DEFAULT NULL,
  `pocPhone` varchar(45) DEFAULT NULL,
  `notes` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `user_properties`
--

DROP TABLE IF EXISTS `user_properties`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_properties` (
  `userId` varchar(32) NOT NULL,
  `propertyId` varchar(32) NOT NULL,
  PRIMARY KEY (`userId`,`propertyId`),
  KEY `property_id` (`propertyId`),
  CONSTRAINT `property_id` FOREIGN KEY (`propertyId`) REFERENCES `properties` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2014-11-06  9:08:15
