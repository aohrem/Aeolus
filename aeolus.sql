-- phpMyAdmin SQL Dump
-- version 3.5.2.2
-- http://www.phpmyadmin.net
--
-- Host: 127.0.0.1
-- Erstellungszeit: 07. Apr 2013 um 16:27
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
-- Tabellenstruktur für Tabelle `egg`
--

CREATE TABLE IF NOT EXISTS `egg` (
  `feed_id` int(6) unsigned NOT NULL AUTO_INCREMENT,
  `password` varchar(40) NOT NULL,
  `lat` double(11,8) NOT NULL,
  `lon` double(11,8) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `location_name` varchar(255) NOT NULL,
  `ele` varchar(64) NOT NULL,
  `status` varchar(64) NOT NULL,
  `exposure` varchar(64) NOT NULL,
  `lastupdated` int(15) NOT NULL,
  PRIMARY KEY (`feed_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=116227 ;

--
-- Daten für Tabelle `egg`
--

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `eggdata_75842`
--

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `lanuv`
--

CREATE TABLE IF NOT EXISTS `lanuv` (
  `code` varchar(4) NOT NULL DEFAULT '',
  `city` varchar(40) NOT NULL,
  `street` varchar(40) NOT NULL,
  `lat` double(11,8) NOT NULL,
  `lon` double(11,8) NOT NULL,
  PRIMARY KEY (`code`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Daten für Tabelle `lanuv`
--

INSERT INTO `lanuv` (`code`, `city`, `street`, `lat`, `lon`) VALUES
('AABU', 'Aachen-Burtscheid', 'Hein-Görgen-Straße', 50.75468281, 6.09387621),
('BIEL', 'Bielefeld-Ost', 'Hermann-Delius-Straße / Bleichstraße', 52.02316571, 8.54832322),
('BONN', 'Bonn-Auerberg', 'An der Josefshöhe', 50.75362371, 7.08262817),
('BORG', 'Borken-Gemen', 'Lise-Meitner-Straße', 51.86198611, 6.87448442),
('BOTT', 'Bottrop-Welheim', 'Welheimer Straße', 51.52594876, 6.97685204),
('BUCH', 'Duisburg-Buchholz', 'Böhmerstraße', 51.38523936, 6.76294326),
('CHOR', 'Köln-Chorweiler', 'Fühlinger Weg', 51.01932873, 6.88458941),
('DATT', 'Datteln-Hagem', 'Mozartstraße', 51.64137119, 7.33134319),
('DDCS', 'Düsseldorf', 'Corneliusstraße', 51.21309437, 6.78250653),
('DLOH', 'Düsseldorf-Lohausen', 'Wacholderweg', 51.30759537, 6.55735621),
('DMD2', 'Dortmund-Eving', 'Burgweg', 51.53689956, 7.45740837),
('DUBR', 'Duisburg-Bruckhausen', 'Kaiser-Wilhelm-Straße', 51.48583171, 6.73579874),
('EIFE', 'Simmerath Eifel', 'Simmerath-Lammersdorf', 50.65321511, 6.28102014),
('ELAN', 'Essen-Schuir', 'Wallneyer Straße', 51.40695330, 6.96559037),
('ELSB', 'Elsdorf-Berrendorf', 'Zum Sportplatz / Holunderweg', 50.92709331, 6.58215391),
('EVOG', 'Essen-Vogelheim', 'Hafenstraße', 51.49651303, 6.98098912),
('GELS', 'Gelsenkirchen-Bismarck', 'Trinenkamp', 51.53363322, 7.10172375),
('HATT', 'Hattingen-Blankenstein', 'An der Becke', 51.40299208, 7.20851990),
('HUE2', 'Hürth', 'Dunantstraße', 50.87610404, 6.87373379),
('JACK', 'Jackerath', 'Jülicher Straße', 51.30759537, 6.55735621),
('KREF', 'Krefeld-Linn', 'Hammerstraße', 51.33765207, 6.64019078),
('KRHA', 'Krefeld-Hafen', 'Hentrichstraße', 51.34256318, 6.67012507),
('LEV2', 'Leverkusen-Manfort', 'Manforter Straße', 51.02886337, 7.00501008),
('LOER', 'Düsseldorf-Lörick', 'Zum Niederkasseler Deich', 51.24919431, 6.73233090),
('MGRH', 'Mönchengladbach-Rheydt', 'Urftstraße', 51.15459524, 6.42565711),
('MSGE', 'Münster-Geist', 'Gut Insel', 51.93653831, 7.61152182),
('NETT', 'Nettetal-Kaldenkirchen', 'Juiserfeldstraße', 51.32692362, 6.19581043),
('NIED', 'Lünen-Niederaden', 'Kreisstraße', 51.59219553, 7.56977829),
('NIZI', 'Niederzier', 'Treibbach', 50.88345567, 6.46930753),
('RAT2', 'Ratingen-Tiefenbroich', 'Daniel-Goldbach-Straße', 51.30390317, 6.81991350),
('REIS', 'Düsseldorf-Reisholz', 'Further Straße', 51.18880812, 6.85916503),
('RODE', 'Köln-Rodenkirchen', 'Friedrich-Ebert-Straße ', 50.88979790, 6.98511581),
('ROTH', 'Netphen Rothaargebirge', 'Nauholzer Weg', 50.93031885, 8.19188595),
('SHW2', 'Schwerte', 'Konrad-Zuse-Straße', 51.44875125, 7.58223505),
('SICK', 'Marl-Sickingmühle', 'Alte Straße', 51.69894685, 7.12265771),
('SOES', 'Soest-Ost', 'Enkeserweg', 51.57065452, 8.14800007),
('SOLI', 'Solingen-Wald', 'Dültgenstaler Straße', 51.18375809, 7.05258448),
('STYR', 'Mülheim-Styrum', 'Neustadtstraße', 51.45344509, 6.86504450),
('UNNA', 'Unna-Königsborn', 'Palaiseaustraße', 51.54748472, 7.69378130),
('VACW', 'Aachen', 'Wilhelmstraße', 50.77310939, 6.09572768),
('VBIS', 'Bielefeld', 'Stapenhorststraße', 52.02623245, 8.52151376),
('VBOT', 'Bottrop', 'Peterstraße', 51.51924752, 6.92420813),
('VDOM', 'Dortmund', 'Brackeler Straße', 51.52355622, 7.48353876),
('VDOR', 'Dortmund', 'Steinstraße', 51.51962728, 7.46429147),
('VDUI', 'Duisburg', 'Kardinal-Galen-Straße', 51.43769248, 6.77174108),
('VEAE', 'Essen', 'Gladbecker Straße', 51.47791739, 7.00521894),
('VESN', 'Essen-Ost', 'Steeler Str', 51.45116340, 7.03054044),
('VGES', 'Gelsenkirchen', 'Kurt-Schumacher-Straße', 51.52786995, 7.08453729),
('VHAM', 'Hagen', 'Graf-von-Galen-Ring', 51.36281691, 7.46347718),
('VHER', 'Herne', 'Recklinghauser Straße', 51.54312793, 7.15012835),
('VKCL', 'Köln', 'Clevischer Ring', 50.96284152, 7.00453856),
('VKTU', 'Köln', 'Turiner Straße', 50.94735141, 6.95764448),
('VMGF', 'Mönchengladbach', 'Friedrich-Ebert-Straße', 51.16941884, 6.43991974),
('VMGR', 'Mönchengladbach', 'Düsseldorfer Straße', 51.16994863, 6.45878376),
('VMS2', 'Münster', 'Weseler Straße', 51.95325812, 7.61932603),
('VOBM', 'Oberhausen', 'Mülheimer Straße', 51.47495615, 6.86359460),
('VSCH', 'Schwerte', 'Hörder Straße', 51.44657348, 7.56357316),
('VWEL', 'Wuppertal', 'Gathe / Wilhelmstraße', 51.26066673, 7.14734809),
('WALS', 'Duisburg-Walsum', 'Sonnenstraße', 51.52400667, 6.74834429),
('WAST', 'Warstein', 'Rangetriftweg', 51.44300583, 8.36066031),
('WESE', 'Wesel-Feldmark', 'Mercatorstraße', 51.67279313, 6.62950926),
('WIT2', 'Witten-Annen', 'Westfalenstraße', 51.44425497, 7.35834090),
('WULA', 'Wuppertal-Langerfeld', 'Am Buchenloh', 51.27763318, 7.23184172);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
