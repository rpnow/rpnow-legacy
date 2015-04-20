-- phpMyAdmin SQL Dump
-- version 4.1.14
-- http://www.phpmyadmin.net
--
-- Host: 127.0.0.1
-- Generation Time: Apr 20, 2015 at 11:00 AM
-- Server version: 5.6.17
-- PHP Version: 5.5.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `rp_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `Character`
--

CREATE TABLE `Character` (
  `Number` int(11) NOT NULL AUTO_INCREMENT,
  `Name` varchar(30) NOT NULL,
  `Color` tinytext NOT NULL,
  `Room` char(4) NOT NULL,
  `Timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`Number`),
  UNIQUE KEY `NameRoom` (`Name`,`Room`),
  KEY `Room` (`Room`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `Message`
--

CREATE TABLE `Message` (
  `Number` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `Type` enum('Narrator','Character','OOC') NOT NULL,
  `Content` text NOT NULL,
  `Room` char(4) NOT NULL,
  `Timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `Character_Name` varchar(30) DEFAULT NULL,
  PRIMARY KEY (`Number`),
  KEY `Timestamp` (`Timestamp`),
  KEY `Character` (`Character_Name`,`Room`),
  KEY `Character_Room` (`Room`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `Room`
--

CREATE TABLE `Room` (
  `ID` char(4) NOT NULL,
  `Title` tinytext NOT NULL,
  `Description` tinytext NOT NULL,
  `Timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `Character`
--
ALTER TABLE `Character`
  ADD CONSTRAINT `Character_ibfk_1` FOREIGN KEY (`Room`) REFERENCES `room` (`ID`);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
