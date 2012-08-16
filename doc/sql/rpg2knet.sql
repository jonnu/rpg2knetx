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
  `forum_id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `forum_title` varchar(255) DEFAULT NULL,
  `forum_slug` varchar(255) DEFAULT NULL,
  `forum_description` text,
  `forum_left` smallint(5) unsigned DEFAULT NULL,
  `forum_right` smallint(5) unsigned DEFAULT NULL,
  `forum_latest_post_id` int(10) unsigned DEFAULT NULL,
  `forum_date_created` datetime DEFAULT NULL,
  `forum_date_updated` datetime DEFAULT NULL,
  PRIMARY KEY (`forum_id`),
  UNIQUE KEY `forum_unique` (`forum_slug`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `post`
--

DROP TABLE IF EXISTS `post`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `post` (
  `post_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `post_thread_id` int(10) unsigned NOT NULL,
  `post_author_id` int(10) unsigned NOT NULL,
  `post_content` text NOT NULL,
  `post_date_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `post_date_updated` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`post_id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `security`
--

DROP TABLE IF EXISTS `security`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `security` (
  `security_user_id` int(10) unsigned NOT NULL,
  `security_type` enum('COOKIE','AFFIRM','LOSTPW') NOT NULL,
  `security_user_hash` char(64) NOT NULL,
  `security_user_address` int(10) unsigned NOT NULL,
  `security_date_expires` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `thread`
--

DROP TABLE IF EXISTS `thread`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `thread` (
  `thread_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `thread_forum_id` smallint(5) unsigned DEFAULT NULL,
  `thread_author_id` mediumint(8) unsigned DEFAULT NULL,
  `thread_title` varchar(255) DEFAULT NULL,
  `thread_slug` varchar(255) DEFAULT NULL,
  `thread_date_created` timestamp NULL DEFAULT NULL,
  `thread_date_updated` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`thread_id`),
  UNIQUE KEY `thread_unique` (`thread_forum_id`,`thread_slug`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `user`
--

DROP TABLE IF EXISTS `user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user` (
  `user_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_email` varchar(64) NOT NULL,
  `user_name` varchar(32) DEFAULT NULL,
  `user_hash_password` char(60) DEFAULT NULL,
  `user_ip_created` int(10) unsigned NOT NULL,
  `user_date_birthday` date DEFAULT NULL,
  `user_date_created` datetime NOT NULL,
  `user_date_pulsed` datetime DEFAULT NULL,
  `user_date_confirmed` datetime DEFAULT NULL,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `user_email` (`user_email`),
  UNIQUE KEY `user_confirmed` (`user_date_confirmed`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2012-08-16 15:25:58
