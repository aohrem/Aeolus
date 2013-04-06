-- phpMyAdmin SQL Dump
-- version 3.5.2.2
-- http://www.phpmyadmin.net
--
-- Host: 127.0.0.1
-- Erstellungszeit: 06. Apr 2013 um 12:03
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
  `lat` double(10,8) NOT NULL,
  `lon` double(10,8) NOT NULL,
  PRIMARY KEY (`feed_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=116227 ;

--
-- Daten für Tabelle `egg`
--

INSERT INTO `egg` (`feed_id`, `password`, `lat`, `lon`) VALUES
(75842, '9485989ff514b5106b7738850fd73c23e8c1e3f7', 51.94696313, 7.59155273),
(81697, 'ffa6706ff2127a749973072756f83c532e43ed02', 45.46387998, -99.99999999),
(88892, 'ffa6706ff2127a749973072756f83c532e43ed02', 33.59785600, -99.99999999),
(96863, 'ffa6706ff2127a749973072756f83c532e43ed02', 40.28583101, -74.22033636),
(101426, 'ffa6706ff2127a749973072756f83c532e43ed02', 51.08948032, 4.38993931),
(101490, '9485989ff514b5106b7738850fd73c23e8c1e3f7', -37.51113420, 99.99999999),
(104992, 'ffa6706ff2127a749973072756f83c532e43ed02', 34.03420366, -99.99999999),
(107067, 'a9993e364706816aba3e25717850c26c9cd0d89d', 53.58210451, 9.95200790),
(115602, 'ffa6706ff2127a749973072756f83c532e43ed02', 43.06958270, -77.19993780),
(116226, 'ffa6706ff2127a749973072756f83c532e43ed02', 52.07968640, 5.11605200);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;


CREATE TABLE IF NOT EXISTS `lanuv` (
  `code` varchar(4) UNIQUE,
  `city` varchar(40) NOT NULL,
  `street` varchar(40) NOT NULL,
  `lat` double(10,8) NOT NULL,
  `lon` double(10,8) NOT NULL,
  PRIMARY KEY (`code`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=12346 ;

INSERT INTO `lanuv`
(`code`, `city`, `street`, `lat`, `lon`)
VALUES 
('VACW', 'Aachen', 'Wilhelmstraße', 50.77310939, 6.09572768),
('AABU', 'Aachen-Burtscheid', 'Hein-Görgen-Straße', 50.75468281, 6.09387621),
('BIEL', 'Bielefeld-Ost', 'Hermann-Delius-Straße / Bleichstraße', 52.02316571, 8.54832322),
('VBIS', 'Bielefeld', 'Stapenhorststraße', 52.02623245, 8.52151376),
('BONN', 'Bonn-Auerberg', 'An der Josefshöhe', 50.75362371, 7.08262817),
('BORG', 'Borken-Gemen', 'Lise-Meitner-Straße', 51.86198611, 6.87448442),
('VBOT', 'Bottrop', 'Peterstraße', 51.51924752, 6.92420813),
('BOTT', 'Bottrop-Welheim', 'Welheimer Straße', 51.52594876, 6.97685204),
('DATT', 'Datteln-Hagem', 'Mozartstraße', 51.64137119, 7.33134319),
('VDOM', 'Dortmund', 'Brackeler Straße', 51.52355622, 7.48353876),
('VDOR', 'Dortmund', 'Steinstraße', 51.51962728, 7.46429147),
('DMD2', 'Dortmund-Eving', 'Burgweg', 51.53689956, 7.45740837),
('VDUI', 'Duisburg', 'Kardinal-Galen-Straße', 51.43769248, 6.77174108),
('DUBR', 'Duisburg-Bruckhausen', 'Kaiser-Wilhelm-Straße', 51.48583171, 6.73579874),
('BUCH', 'Duisburg-Buchholz', 'Böhmerstraße', 51.38523936, 6.76294326),
('WALS', 'Duisburg-Walsum', 'Sonnenstraße', 51.52400667, 6.74834429),
('DLOH', 'Düsseldorf-Lohausen', 'Wacholderweg', 51.30759537, 6.55735621),
('DDCS', 'Düsseldorf', 'Corneliusstraße', 51.21309437, 6.78250653),
('LOER', 'Düsseldorf-Lörick', 'Zum Niederkasseler Deich', 51.24919431, 6.7323309),
('REIS', 'Düsseldorf-Reisholz', 'Further Straße', 51.18880812, 6.85916503),
('ELSB', 'Elsdorf-Berrendorf', 'Zum Sportplatz / Holunderweg', 50.92709331, 6.58215391),
('VEAE', 'Essen', 'Gladbecker Straße', 51.47791739, 7.00521894),
('VESN', 'Essen-Ost', 'Steeler Str', 51.4511634, 7.03054044),
('ELAN', 'Essen-Schuir', 'Wallneyer Straße', 51.4069533, 6.96559037),
('EVOG', 'Essen-Vogelheim', 'Hafenstraße', 51.49651303, 6.98098912),
('VGES', 'Gelsenkirchen', 'Kurt-Schumacher-Straße', 51.52786995, 7.08453729),
('GELS', 'Gelsenkirchen-Bismarck', 'Trinenkamp', 51.53363322, 7.10172375),
('VHAM', 'Hagen', 'Graf-von-Galen-Ring', 51.36281691, 7.46347718),
('HATT', 'Hattingen-Blankenstein', 'An der Becke', 51.40299208, 7.2085199),
('VHER', 'Herne', 'Recklinghauser Straße', 51.54312793, 7.15012835),
('HUE2', 'Hürth', 'Dunantstraße', 50.87610404, 6.87373379),
('JACK', 'Jackerath', 'Jülicher Straße', 51.30759537, 6.55735621),
('VKCL', 'Köln', 'Clevischer Ring', 50.96284152, 7.00453856),
('VKTU', 'Köln', 'Turiner Straße', 50.94735141, 6.95764448),
('CHOR', 'Köln-Chorweiler', 'Fühlinger Weg', 51.01932873, 6.88458941),
('RODE', 'Köln-Rodenkirchen', 'Friedrich-Ebert-Straße ', 50.8897979, 6.98511581),
('KRHA', 'Krefeld-Hafen', 'Hentrichstraße', 51.34256318, 6.67012507),
('KREF', 'Krefeld-Linn', 'Hammerstraße', 51.33765207, 6.64019078),
('LEV2', 'Leverkusen-Manfort', 'Manforter Straße', 51.02886337, 7.00501008),
('NIED', 'Lünen-Niederaden', 'Kreisstraße', 51.59219553, 7.56977829),
('SICK', 'Marl-Sickingmühle', 'Alte Straße', 51.69894685, 7.12265771),
('VMGR', 'Mönchengladbach', 'Düsseldorfer Straße', 51.16994863, 6.45878376),
('VMGF', 'Mönchengladbach', 'Friedrich-Ebert-Straße', 51.16941884, 6.43991974),
('MGRH', 'Mönchengladbach-Rheydt', 'Urftstraße', 51.15459524, 6.42565711),
('STYR', 'Mülheim-Styrum', 'Neustadtstraße', 51.45344509, 6.8650445),
('VMS2', 'Münster', 'Weseler Straße', 51.95325812, 7.61932603),
('MSGE', 'Münster-Geist', 'Gut Insel', 51.93653831, 7.61152182),
('ROTH', 'Netphen Rothaargebirge', 'Nauholzer Weg', 50.93031885, 8.19188595),
('NETT', 'Nettetal-Kaldenkirchen', 'Juiserfeldstraße', 51.32692362, 6.19581043),
('NIZI', 'Niederzier', 'Treibbach', 50.88345567, 6.46930753),
('VOBM', 'Oberhausen', 'Mülheimer Straße', 51.47495615, 6.8635946),
('RAT2', 'Ratingen-Tiefenbroich', 'Daniel-Goldbach-Straße', 51.30390317, 6.8199135),
('VSCH', 'Schwerte', 'Hörder Straße', 51.44657348, 7.56357316),
('SHW2', 'Schwerte', 'Konrad-Zuse-Straße', 51.44875125,	7.58223505),
('EIFE', 'Simmerath Eifel', 'Simmerath-Lammersdorf', 50.65321511, 6.28102014),
('SOES', 'Soest-Ost', 'Enkeserweg', 51.57065452, 8.14800007),
('SOLI', 'Solingen-Wald', 'Dültgenstaler Straße', 51.18375809, 7.05258448),
('UNNA', 'Unna-Königsborn', 'Palaiseaustraße', 51.54748472, 7.6937813),
('WAST', 'Warstein', 'Rangetriftweg', 51.44300583, 8.36066031),
('WESE', 'Wesel-Feldmark', 'Mercatorstraße', 51.67279313, 6.62950926),
('WIT2', 'Witten-Annen', 'Westfalenstraße', 51.44425497, 7.3583409),
('VWEL', 'Wuppertal', 'Gathe / Wilhelmstraße', 51.26066673, 7.14734809),
('WULA', 'Wuppertal-Langerfeld', 'Am Buchenloh', 51.27763318, 7.23184172);
