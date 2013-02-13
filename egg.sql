-- phpMyAdmin SQL Dump
-- version 3.5.2.2
-- http://www.phpmyadmin.net
--
-- Host: 127.0.0.1
-- Erstellungszeit: 13. Jan 2013 um 19:34
-- Server Version: 5.5.27
-- PHP-Version: 5.4.7

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Datenbank: `aeolus`
--

-- --------------------------------------------------------

--
-- Tabellenstruktur f&uuml;r Tabelle `egg`
--

CREATE TABLE IF NOT EXISTS `egg` (
  `feed_id` int(6) unsigned NOT NULL AUTO_INCREMENT,
  `password` varchar(40) NOT NULL,
  `lat` double(10,8) NOT NULL,
  `lon` double(10,8) NOT NULL,
  PRIMARY KEY (`feed_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=12346 ;

--
-- Daten f&uuml;r Tabelle `egg`
--

INSERT INTO `egg` (`feed_id`, `password`, `lat`, `lon`) VALUES
(12345, 'ffa6706ff2127a749973072756f83c532e43ed02', 51.96294400, 7.62499400),
(56789, 'ffa6706ff2127a749973072756f83c532e43ed02', 51.97294400, 7.62569400),
(23456, 'ffa6706ff2127a749973072756f83c532e43ed02', 51.96998500, 7.62639400),
(45678, 'ffa6706ff2127a749973072756f83c532e43ed02', 51.92440000, 7.62779400),
(98765, 'ffa6706ff2127a749973072756f83c532e43ed02', 51.96244000, 7.62800000);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
