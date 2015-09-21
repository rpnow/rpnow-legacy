SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

-- --------------------------------------------------------

--
-- Table structure for table `Character`
--

CREATE TABLE `Character` (
  `Number` int(11) NOT NULL AUTO_INCREMENT,
  `Name` varchar(30) NOT NULL,
  `Color` tinytext NOT NULL,
  `Room` char(4) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `Time_Created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `Time_Updated` timestamp NULL DEFAULT NULL,
  `IP` varchar(45) NOT NULL,
  `Deleted` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`Number`),
  UNIQUE KEY `NameRoom` (`Name`,`Room`),
  KEY `Room` (`Room`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `Chara_Update`
--

CREATE TABLE `Chara_Update` (
  `Number` int(11) NOT NULL AUTO_INCREMENT,
  `Chara_Number` int(11) NOT NULL,
  `Action` enum('delete','undelete') NOT NULL,
  `Time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `IP` varchar(45) NOT NULL,
  PRIMARY KEY (`Number`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `Message`
--

CREATE TABLE `Message` (
  `Number` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `Type` enum('Narrator','Character','OOC') NOT NULL,
  `Content` text NOT NULL,
  `Room` char(4) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `Time_Created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `Time_Updated` timestamp NULL DEFAULT NULL,
  `Chara_Number` int(11) DEFAULT NULL,
  `IP` varchar(45) NOT NULL,
  `Deleted` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`Number`),
  KEY `Timestamp` (`Time_Created`),
  KEY `Character` (`Room`),
  KEY `Character_Room` (`Room`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `Message_Update`
--

CREATE TABLE `Message_Update` (
  `Number` int(11) NOT NULL AUTO_INCREMENT,
  `Message_Number` int(11) NOT NULL,
  `Action` enum('delete','undelete') NOT NULL,
  `Time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `IP` varchar(45) NOT NULL,
  PRIMARY KEY (`Number`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `Room`
--

CREATE TABLE `Room` (
  `ID` char(4) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `Title` tinytext NOT NULL,
  `Description` tinytext NOT NULL,
  `Time_Created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `IP` varchar(45) NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
