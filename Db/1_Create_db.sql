/* 
 * (C) Copyright 2017 CEFRIEL (http://www.cefriel.com/).
 * 
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * 
 *     http://www.apache.org/licenses/LICENSE-2.0
 * 
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * 
 * Contributors:
 *     Andrea Fiano, Gloria Re Calegari, Irene Celino.
 */
 
CREATE DATABASE  IF NOT EXISTS `gwap-enabler-db` /*!40100 DEFAULT CHARACTER SET utf8 */;
USE `gwap-enabler-db`;
-- MySQL dump 10.13  Distrib 5.6.24, for Win64 (x86_64)
--
-- Host: 127.0.0.1    Database: gwap-enabler-db
-- ------------------------------------------------------
-- Server version	5.6.23-log

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
-- Table structure for table `badge`
--

DROP TABLE IF EXISTS `badge`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `badge` (
  `idBadge` int(16) NOT NULL AUTO_INCREMENT,
  `name` varchar(256) NOT NULL,
  `value` varchar(256) NOT NULL,
  `image` varchar(256) NOT NULL,
  `goal` int(11) NOT NULL,
  PRIMARY KEY (`idBadge`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `configuration`
--

DROP TABLE IF EXISTS `configuration`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `configuration` (
  `idParameters` int(11) NOT NULL AUTO_INCREMENT,
  `upperThreshold` float NOT NULL,
  `lowerThreshold` float DEFAULT NULL,
  `positiveK` float NOT NULL,
  `negativeK` float DEFAULT NULL,
  `nOfLevels` int(11) NOT NULL,
  `nOfGT` int(11) NOT NULL,
  `levelPoints` int(11) NOT NULL,
  `consecutiveLevelPoints` int(11) NOT NULL,
  `reputationParam` int(11) NOT NULL,
  PRIMARY KEY (`idParameters`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `leaderboard`
--

DROP TABLE IF EXISTS `leaderboard`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `leaderboard` (
  `idLeaderboard` int(16) NOT NULL AUTO_INCREMENT,
  `idUser` int(16) NOT NULL,
  `score` int(11) NOT NULL,
  PRIMARY KEY (`idLeaderboard`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `level`
--

DROP TABLE IF EXISTS `level`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `level` (
  `idlevel` int(11) NOT NULL AUTO_INCREMENT,
  `level` varchar(45) DEFAULT NULL,
  `idRound` int(11) NOT NULL,
  `startLevel` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `endLevel` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`idlevel`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `logging`
--

DROP TABLE IF EXISTS `logging`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `logging` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `idUser` int(11) NOT NULL,
  `idTopic` int(11) NOT NULL,
  `idResource` int(11) NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `idRound` int(11) NOT NULL,
  `idLevel` int(11) DEFAULT NULL,
  `partnerChosen` bit(1) DEFAULT NULL,
  `chosen` bit(1) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IX_idResource` (`idResource`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `resource`
--

DROP TABLE IF EXISTS `resource`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `resource` (
  `idResource` int(16) NOT NULL AUTO_INCREMENT,
  `refId` varchar(128) NOT NULL,
  `label` varchar(128) DEFAULT NULL,
  `lat` double DEFAULT NULL,
  `long` double DEFAULT NULL,
  `url` varchar(256) DEFAULT NULL,
  `orderBy` double DEFAULT NULL,
  PRIMARY KEY (`idResource`),
  KEY `idx_resource_refId` (`refId`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `resource_has_topic`
--

DROP TABLE IF EXISTS `resource_has_topic`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `resource_has_topic` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `idResource` int(11) NOT NULL,
  `idTopic` int(11) NOT NULL,
  `score` float NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `round`
--

DROP TABLE IF EXISTS `round`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `round` (
  `idRound` int(11) NOT NULL AUTO_INCREMENT,
  `startRound` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `endRound` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `idUser` int(11) NOT NULL,
  `score` int(11) NOT NULL,
  PRIMARY KEY (`idRound`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `topic`
--

DROP TABLE IF EXISTS `topic`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `topic` (
  `idTopic` int(16) NOT NULL AUTO_INCREMENT,
  `refId` varchar(256) DEFAULT NULL,
  `value` varchar(256) DEFAULT NULL,
  `label` varchar(45) NOT NULL,
  `url` varchar(256) DEFAULT NULL,
  `weight` float DEFAULT NULL,
  PRIMARY KEY (`idTopic`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `true_response`
--

DROP TABLE IF EXISTS `true_response`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `true_response` (
  `idRound` int(11) NOT NULL,
  `level` int(11) NOT NULL,
  `idTopicTrue` int(11) NOT NULL,
  `isGT` bit(1) NOT NULL,
  `score` int(11) NOT NULL DEFAULT '0',
  `consecutiveAnswer` int(11) NOT NULL DEFAULT '-1',
  `nErrors` int(11) NOT NULL DEFAULT '0',
  `nGTErrors` int(11) NOT NULL DEFAULT '0',
  `played` bit(1) NOT NULL DEFAULT b'0',
  PRIMARY KEY (`idRound`,`level`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `user`
--

DROP TABLE IF EXISTS `user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user` (
  `idUser` int(16) NOT NULL AUTO_INCREMENT,
  `firstName` varchar(256) NOT NULL,
  `lastName` varchar(256) NOT NULL,
  `life_play` int(11) NOT NULL DEFAULT '0',
  `idSocial` varchar(256) NOT NULL,
  `social` varchar(256) NOT NULL,
  `name` varchar(256) DEFAULT NULL,
  `cover` varchar(256) DEFAULT NULL,
  `thumbnail` varchar(256) DEFAULT NULL,
  PRIMARY KEY (`idUser`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `user_has_badge`
--

DROP TABLE IF EXISTS `user_has_badge`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_has_badge` (
  `idUser` int(16) NOT NULL,
  `idBadge` int(16) NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `idUser` (`idUser`,`idBadge`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `user_reputation`
--

DROP TABLE IF EXISTS `user_reputation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_reputation` (
  `idUser` int(16) NOT NULL,
  `idRound` int(11) NOT NULL,
  `reputation` float NOT NULL,
  PRIMARY KEY (`idUser`,`idRound`)
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

-- Dump completed on 2018-03-22 17:13:05
