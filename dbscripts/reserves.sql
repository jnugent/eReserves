-- MySQL dump 10.13  Distrib 5.1.55, for redhat-linux-gnu (i686)
--
-- Host: localhost    Database: reserves
-- ------------------------------------------------------
-- Server version	5.1.55

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
-- Table structure for table `course`
--

DROP TABLE IF EXISTS `course`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `course` (
  `courseID` int(11) NOT NULL AUTO_INCREMENT,
  `courseName` text NOT NULL,
  `courseNumber` varchar(255) NOT NULL,
  `prefix` varchar(255) NOT NULL,
  `visible` enum('0','1') NOT NULL DEFAULT '1',
  PRIMARY KEY (`courseID`),
  KEY `courseNumberIndex` (`courseNumber`),
  KEY `prefixIndex` (`prefix`),
  FULLTEXT KEY `textIndex` (`courseName`,`courseNumber`),
  FULLTEXT KEY `textIndexCourse` (`courseName`)
) ENGINE=MyISAM AUTO_INCREMENT=8373 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `courseName`
--

DROP TABLE IF EXISTS `courseName`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `courseName` (
  `courseNameID` int(11) NOT NULL AUTO_INCREMENT,
  `prefix` varchar(255) NOT NULL,
  `courseNumber` varchar(255) NOT NULL,
  `courseName` text NOT NULL,
  PRIMARY KEY (`courseNameID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `crossList`
--

DROP TABLE IF EXISTS `crossList`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `crossList` (
  `crossListCourseID` int(11) NOT NULL AUTO_INCREMENT,
  `courseID` int(11) NOT NULL,
  `courseName` text NOT NULL,
  PRIMARY KEY (`crossListCourseID`),
  KEY `crossListCourseIDIndex` (`courseID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `electronicItem`
--

DROP TABLE IF EXISTS `electronicItem`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `electronicItem` (
  `electronicItemID` int(11) NOT NULL AUTO_INCREMENT,
  `mimeType` varchar(255) NOT NULL,
  `doi` varchar(255) NOT NULL,
  `reservesRecordID` int(11) NOT NULL,
  `url` text,
  `itemTitle` text NOT NULL,
  `usageRights` varchar(255) NOT NULL DEFAULT '',
  `originalFileName` text,
  `restrictToLogin` enum('0','1') NOT NULL DEFAULT '0',
  `restrictToEnroll` enum('0','1') NOT NULL DEFAULT '0',
  PRIMARY KEY (`electronicItemID`),
  KEY `electronicItemIndex` (`reservesRecordID`)
) ENGINE=MyISAM AUTO_INCREMENT=24 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `itemHeading`
--

DROP TABLE IF EXISTS `itemHeading`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `itemHeading` (
  `itemHeadingID` int(11) NOT NULL AUTO_INCREMENT,
  `headingTitle` text NOT NULL,
  `sectionID` int(11) unsigned NOT NULL,
  `sequence` tinyint(4) NOT NULL DEFAULT '1',
  PRIMARY KEY (`itemHeadingID`),
  KEY `itemHeadingIndex` (`sectionID`)
) ENGINE=MyISAM AUTO_INCREMENT=52 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `physicalItem`
--

DROP TABLE IF EXISTS `physicalItem`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `physicalItem` (
  `physicalItemID` int(11) NOT NULL AUTO_INCREMENT,
  `callNumber` varchar(255) NOT NULL,
  `barCode` varchar(255) NOT NULL,
  `reservesRecordID` int(11) NOT NULL,
  `citation` text NOT NULL,
  `location` varchar(255) NOT NULL,
  `loanPeriod` varchar(255) NOT NULL,
  `shadow` enum('0','1') NOT NULL DEFAULT '0',
  `usageRights` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`physicalItemID`),
  KEY `physicalItemIndex` (`reservesRecordID`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `physicalItemNote`
--

DROP TABLE IF EXISTS `physicalItemNote`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `physicalItemNote` (
  `physicalItemNoteID` int(11) NOT NULL AUTO_INCREMENT,
  `physicalItemID` int(11) NOT NULL,
  `note` text,
  PRIMARY KEY (`physicalItemNoteID`),
  KEY `physicalItemNoteIndex` (`physicalItemID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `programPrefix`
--

DROP TABLE IF EXISTS `programPrefix`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `programPrefix` (
  `programPrefixID` int(11) NOT NULL AUTO_INCREMENT,
  `programPrefix` varchar(255) NOT NULL DEFAULT '',
  `programName` varchar(255) NOT NULL,
  PRIMARY KEY (`programPrefixID`),
  UNIQUE KEY `programPrefixIndex` (`programPrefix`)
) ENGINE=MyISAM AUTO_INCREMENT=66 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `reservesRecord`
--

DROP TABLE IF EXISTS `reservesRecord`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `reservesRecord` (
  `reservesRecordID` int(11) NOT NULL AUTO_INCREMENT,
  `reservesRecordTitle` varchar(255) NOT NULL,
  `details` text,
  `itemHeadingID` int(10) unsigned NOT NULL DEFAULT '0',
  `linkID` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`reservesRecordID`),
  KEY `reservesItemHeadingIDX` (`itemHeadingID`),
  KEY `linkedIDIndex` (`linkID`)
) ENGINE=MyISAM AUTO_INCREMENT=41 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `reservesRecordHeading`
--

DROP TABLE IF EXISTS `reservesRecordHeading`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `reservesRecordHeading` (
  `reservesRecordHeadingID` int(11) NOT NULL AUTO_INCREMENT,
  `reservesRecordID` int(11) NOT NULL,
  `itemHeadingID` int(11) NOT NULL,
  PRIMARY KEY (`reservesRecordHeadingID`),
  KEY `reservesRecordHeadingIndex` (`reservesRecordID`,`itemHeadingID`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `reservesRecordNote`
--

DROP TABLE IF EXISTS `reservesRecordNote`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `reservesRecordNote` (
  `reservesRecordNoteID` int(11) NOT NULL AUTO_INCREMENT,
  `reservesRecordID` int(11) NOT NULL,
  `note` text,
  PRIMARY KEY (`reservesRecordNoteID`),
  KEY `reservesRecordNoteIndex` (`reservesRecordID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `reservesUser`
--

DROP TABLE IF EXISTS `reservesUser`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `reservesUser` (
  `reservesUserID` int(11) NOT NULL AUTO_INCREMENT,
  `userName` varchar(255) NOT NULL DEFAULT '',
  `email` varchar(255) NOT NULL,
  `isAdmin` int(11) NOT NULL DEFAULT '0',
  `lastLogin` datetime NOT NULL,
  PRIMARY KEY (`reservesUserID`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `role`
--

DROP TABLE IF EXISTS `role`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `role` (
  `roleID` int(11) NOT NULL AUTO_INCREMENT,
  `roleDescription` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`roleID`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `section`
--

DROP TABLE IF EXISTS `section`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `section` (
  `sectionID` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `year` varchar(255) NOT NULL,
  `term` varchar(10) NOT NULL,
  `sectionNumber` varchar(255) NOT NULL,
  `courseID` bigint(20) unsigned NOT NULL,
  `visible` enum('0','1') NOT NULL DEFAULT '1',
  PRIMARY KEY (`sectionID`)
) ENGINE=MyISAM AUTO_INCREMENT=5695 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `sectionRole`
--

DROP TABLE IF EXISTS `sectionRole`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sectionRole` (
  `sectionRoleID` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `roleID` int(11) NOT NULL,
  `sectionID` bigint(20) unsigned NOT NULL,
  `userName` varchar(255) NOT NULL,
  PRIMARY KEY (`sectionRoleID`),
  KEY `sectionRoleUserIdx` (`userName`),
  KEY `sectionIDIndex` (`sectionID`)
) ENGINE=MyISAM AUTO_INCREMENT=2324 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2011-02-23 14:51:07
