-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               5.7.29 - MySQL Community Server (GPL)
-- Server OS:                    Win64
-- HeidiSQL Version:             11.0.0.5958
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;


-- Dumping database structure for atm_db
CREATE DATABASE IF NOT EXISTS `atm_db` /*!40100 DEFAULT CHARACTER SET utf8mb4 */;
USE `atm_db`;

-- Dumping structure for table atm_db.available_banknotes
CREATE TABLE IF NOT EXISTS `available_banknotes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `denomination` int(5) NOT NULL,
  `number_banknotes` int(5) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table atm_db.available_banknotes: ~8 rows (approximately)
/*!40000 ALTER TABLE `available_banknotes` DISABLE KEYS */;
INSERT INTO `available_banknotes` (`id`, `denomination`, `number_banknotes`) VALUES
	(1, 1000, 5),
	(2, 500, 5),
	(3, 200, 5),
	(4, 100, 3),
	(5, 50, 3),
	(6, 20, 1),
	(7, 10, 2),
	(8, 5, 2);
/*!40000 ALTER TABLE `available_banknotes` ENABLE KEYS */;

-- Dumping structure for table atm_db.logs
CREATE TABLE IF NOT EXISTS `logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date` datetime NOT NULL,
  `name` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `sum` int(6) NOT NULL,
  `balance_before` int(11) NOT NULL,
  `balance_after` int(11) NOT NULL,
  `remark` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table atm_db.logs: ~0 rows (approximately)
/*!40000 ALTER TABLE `logs` DISABLE KEYS */;
INSERT INTO `logs` (`id`, `date`, `name`, `sum`, `balance_before`, `balance_after`, `remark`) VALUES
	(2, '2020-11-04 01:15:45', 'anonymous', 25000, 9000, 9000, 'Указанная сумма не может быть выдана: в банкомате недостаточно денежных средств'),
	(3, '2020-11-04 01:16:23', 'anonymous', 0, 9000, 9000, 'Неверно указана сумма: ноль выдать невозможно либо поле для ввода суммы пустое'),
	(4, '2020-11-04 01:17:47', 'anonymous', 153, 9000, 9000, 'Неверно указана сумма: сумма должна быть кратной 5'),
	(5, '2020-11-04 01:18:04', 'anonymous', 7735, 9000, 1265, ''),
	(6, '2020-11-04 01:18:22', 'anonymous', 160, 1265, 1105, ''),
	(7, '2020-11-04 01:18:33', 'anonymous', 565, 1105, 1105, 'Не осталось необходимых купюр'),
	(8, '2020-11-04 01:18:49', 'anonymous', 2050, 1105, 1105, 'Указанная сумма не может быть выдана: в банкомате недостаточно денежных средств'),
	(9, '2020-11-04 01:19:04', 'anonymous', 1105, 1105, 0, '');
/*!40000 ALTER TABLE `logs` ENABLE KEYS */;

/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
