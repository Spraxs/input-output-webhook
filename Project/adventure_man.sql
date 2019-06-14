-- phpMyAdmin SQL Dump
-- version 4.6.6deb5
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Gegenereerd op: 03 jun 2019 om 18:13
-- Serverversie: 5.7.26-0ubuntu0.18.04.1
-- PHP-versie: 7.2.17-0ubuntu0.18.04.1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `adventure_man`
--

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `sessions`
--

CREATE TABLE `sessions` (
  `id` bigint(20) NOT NULL,
  `id_session` bigint(20) DEFAULT NULL,
  `id_user` varchar(90) NOT NULL,
  `die_time` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Triggers `sessions`
--
DELIMITER $$
CREATE TRIGGER `catch_before_insert` BEFORE INSERT ON `sessions` FOR EACH ROW BEGIN

INSERT INTO sessions_info (level, location, l_key)
VALUES ('default', 0, 0);

SET NEW.id_session = (SELECT LAST_INSERT_ID());

IF (NEW.die_time IS NULL) THEN
    SET NEW.die_time = (now() + INTERVAL 5 MINUTE);
END IF;

END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `catch_delete` AFTER DELETE ON `sessions` FOR EACH ROW BEGIN

DELETE FROM sessions_info where sessions_info.id = old.id_session;

END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `sessions_info`
--

CREATE TABLE `sessions_info` (
  `id` bigint(20) NOT NULL,
  `level` varchar(20) NOT NULL,
  `location` tinyint(4) NOT NULL,
  `l_key` bit(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Triggers `sessions_info`
--
DELIMITER $$
CREATE TRIGGER `catch_update` AFTER UPDATE ON `sessions_info` FOR EACH ROW BEGIN

UPDATE sessions INNER JOIN sessions_info ON sessions_info.id = sessions.id_session SET sessions.die_time = (now() + INTERVAL 5 MINUTE);

END
$$
DELIMITER ;

--
-- Indexen voor geëxporteerde tabellen
--

--
-- Indexen voor tabel `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id_session` (`id_session`);

--
-- Indexen voor tabel `sessions_info`
--
ALTER TABLE `sessions_info`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT voor geëxporteerde tabellen
--

--
-- AUTO_INCREMENT voor een tabel `sessions`
--
ALTER TABLE `sessions`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;
--
-- AUTO_INCREMENT voor een tabel `sessions_info`
--
ALTER TABLE `sessions_info`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
