
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Databáze: `kolemplzne`
--

-- --------------------------------------------------------

--
-- Struktura tabulky `rents`
--

CREATE TABLE `rents` (
  `rent_end_datetime` datetime NOT NULL,
  `bike_label` varchar(255) COLLATE utf8_czech_ci NOT NULL,
  `user_first_name` varchar(255) COLLATE utf8_czech_ci NOT NULL,
  `rent_end_lon` double NOT NULL,
  `user_username` varchar(255) COLLATE utf8_czech_ci NOT NULL,
  `rent_start_lat` double NOT NULL,
  `bike_code` varchar(255) COLLATE utf8_czech_ci NOT NULL,
  `user_id` int(11) NOT NULL,
  `rent_start_datetime` datetime NOT NULL,
  `rent_id` int(11) NOT NULL,
  `user_last_name` varchar(255) COLLATE utf8_czech_ci NOT NULL,
  `bike_id` varchar(255) COLLATE utf8_czech_ci NOT NULL,
  `rent_end_lat` double NOT NULL,
  `rent_start_lon` double NOT NULL,
  `area` varchar(255) COLLATE utf8_czech_ci NOT NULL,
  `part` varchar(255) COLLATE utf8_czech_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

--
-- Klíče pro exportované tabulky
--

--
-- Klíče pro tabulku `rents`
--
ALTER TABLE `rents`
  ADD UNIQUE KEY `rent_id` (`rent_id`);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
