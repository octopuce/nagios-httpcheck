
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
-- Table structure for table `nagios_httpcheck_service`
--

DROP TABLE IF EXISTS `nagios_httpcheck_service`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nagios_httpcheck_service` (
  `command_line` varchar(2048) NOT NULL,
  `httpcheck_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`httpcheck_id`),
  CONSTRAINT `fk_nagios_httpcheck_service1` FOREIGN KEY (`httpcheck_id`) REFERENCES `nagios_httpcheck` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='HTTP Checks services for nagios';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `nagios_httpcheck`
--

DROP TABLE IF EXISTS `nagios_httpcheck`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nagios_httpcheck` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `fqdn` varchar(255) NOT NULL,
  `ip` varchar(255) NOT NULL,
  `host` varchar(255) NOT NULL,
  `uri` varchar(255) NOT NULL DEFAULT '/',
  `port` varchar(8) DEFAULT '',
  `ssl` tinyint(4) NOT NULL DEFAULT '0',
  `status` smallint(6) DEFAULT NULL,
  `regexp` varchar(32) DEFAULT '',
  `invert_regexp` tinyint(4) NOT NULL DEFAULT '0',
  `no_alert` tinyint(4) NOT NULL DEFAULT '0',
  `login` varchar(255) DEFAULT NULL,
  `pass` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `fqdn` (`fqdn`),
  INDEX `created_at` (`created_at`),
  INDEX `no_alert` (`no_alert`),
  INDEX `updated_at` (`updated_at`),
  INDEX `host` (`host`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='HTTP Checks for nagios';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

