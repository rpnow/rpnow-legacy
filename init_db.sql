--
-- Table structure for table `Character`
--

CREATE TABLE IF NOT EXISTS `Character` (
  `Number` int(11) NOT NULL AUTO_INCREMENT,
  `Name` varchar(30) NOT NULL,
  `Color` tinytext NOT NULL,
  `Room_Number` int(11) NOT NULL,
  `Time_Created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `Time_Updated` timestamp NULL DEFAULT NULL,
  `IP` varchar(45) NOT NULL,
  `Deleted` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`Number`),
  KEY `Room_Number` (`Room_Number`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `Message`
--

CREATE TABLE IF NOT EXISTS `Message` (
  `Number` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `Type` enum('Narrator','Character','OOC') NOT NULL,
  `Content` text NOT NULL,
  `Room_Number` int(11) NOT NULL,
  `Time_Created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `Time_Updated` timestamp NULL DEFAULT NULL,
  `Chara_Number` int(11) DEFAULT NULL,
  `IP` varchar(45) NOT NULL,
  `Deleted` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`Number`),
  KEY `Timestamp` (`Time_Created`),
  KEY `Room_Number` (`Room_Number`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `Room`
--

CREATE TABLE IF NOT EXISTS `Room` (
  `Number` int(11) NOT NULL AUTO_INCREMENT,
  `ID` varchar(20) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `Title` tinytext NOT NULL,
  `Description` tinytext NOT NULL,
  `Time_Created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `IP` varchar(45) NOT NULL,
  PRIMARY KEY (`Number`),
  UNIQUE KEY `ID` (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;