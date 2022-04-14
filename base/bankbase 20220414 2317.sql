-- MySQL Administrator dump 1.4
--
-- ------------------------------------------------------
-- Server version	8.0.27


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;


--
-- Create schema bankbase
--

CREATE DATABASE IF NOT EXISTS bankbase;
USE bankbase;

--
-- Definition of table `account`
--

DROP TABLE IF EXISTS `account`;
CREATE TABLE `account` (
  `idclient` int unsigned NOT NULL,
  `accountnum` varchar(20) NOT NULL,
  `currency` varchar(45) NOT NULL,
  `descript` varchar(100) DEFAULT NULL,
  `closed` date DEFAULT '0000-00-00',
  `type` varchar(7) NOT NULL DEFAULT 'active' COMMENT 'Тип счета. Пассивный счет - с отрицательным балансом, чтобы найти баланс умнож. на -1',
  `default` tinyint unsigned NOT NULL DEFAULT '0' COMMENT 'Счет для получения переводов',
  PRIMARY KEY (`accountnum`) USING BTREE,
  KEY `FK_account_2` (`idclient`),
  KEY `FK_account_3` (`currency`),
  CONSTRAINT `FK_account_2` FOREIGN KEY (`idclient`) REFERENCES `clients` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT `FK_account_3` FOREIGN KEY (`currency`) REFERENCES `currency` (`code`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Таблица с информацией о счетах';

--
-- Dumping data for table `account`
--

/*!40000 ALTER TABLE `account` DISABLE KEYS */;
INSERT INTO `account` (`idclient`,`accountnum`,`currency`,`descript`,`closed`,`type`,`default`) VALUES 
 (1,'20202810100000000001','810','Счет кассы','0000-00-00','passive',0),
 (1,'20202840100000000001','840','Счет кассы','0000-00-00','passive',0),
 (1,'20202978100000000001','978','Счет кассы','0000-00-00','passive',0),
 (9,'40800810100000000001','810','Счет физ. лица','0000-00-00','active',1),
 (2,'40800810100000000002','810','Счет физ. лица','0000-00-00','active',0),
 (2,'40800810100000000003','810','Счет физ. лица','0000-00-00','active',1),
 (3,'40800810100000000004','810','Счет физ. лица','0000-00-00','active',1),
 (15,'40800810100010000013','810','Счет физ. лица','0000-00-00','active',0),
 (15,'40800810100010000014','810','Счет физ. лица','0000-00-00','active',1),
 (15,'40800810100010000015','810','Счет физ. лица','0000-00-00','active',0),
 (9,'40800810100010000016','810','Счет физ. лица','2022-03-22','active',0),
 (9,'40800840100010000002','840','Счет физ. лица','2022-03-22','active',0),
 (9,'40800840100010000003','840','Счет физ. лица','2022-03-22','active',0),
 (9,'40800840100010000004','840','Счет физ. лица','2022-03-22','active',0),
 (9,'40800978100010000001','978','Счет физ. лица','2022-03-22','active',0),
 (9,'40800978100010000002','978','Счет физ. лица','2022-03-22','active',0),
 (9,'40800978100010000003','978','Счет физ. лица','2022-03-22','active',0),
 (1,'70601810500000000001','810','Счет доходов','0000-00-00','active',0),
 (1,'70601810600000000002','810','Счет расходов','0000-00-00','active',0),
 (1,'70601840600000000001','840','Счет доходов','0000-00-00','active',0),
 (1,'70601840600000000002','840','Счет расходов','0000-00-00','active',0),
 (1,'70601978600000000001','978','Счет доходов','0000-00-00','active',0),
 (1,'70601978600000000002','978','Счет расходов','0000-00-00','active',0);
/*!40000 ALTER TABLE `account` ENABLE KEYS */;


--
-- Definition of table `accountcnt`
--

DROP TABLE IF EXISTS `accountcnt`;
CREATE TABLE `accountcnt` (
  `acc2p` varchar(5) NOT NULL COMMENT 'Первые 5 цифр счета',
  `currency` varchar(3) NOT NULL,
  `cnt` int unsigned NOT NULL COMMENT 'Последние 7 цифр счета',
  PRIMARY KEY (`acc2p`,`currency`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Счетчик и количества счетов';

--
-- Dumping data for table `accountcnt`
--

/*!40000 ALTER TABLE `accountcnt` DISABLE KEYS */;
INSERT INTO `accountcnt` (`acc2p`,`currency`,`cnt`) VALUES 
 ('40800','810',16),
 ('40800','840',4),
 ('40800','978',3);
/*!40000 ALTER TABLE `accountcnt` ENABLE KEYS */;


--
-- Definition of table `balance`
--

DROP TABLE IF EXISTS `balance`;
CREATE TABLE `balance` (
  `account` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `dt` varchar(45) NOT NULL COMMENT 'date',
  `sum` decimal(15,2) NOT NULL,
  PRIMARY KEY (`account`,`dt`) USING BTREE,
  CONSTRAINT `FK_balance_1` FOREIGN KEY (`account`) REFERENCES `account` (`accountnum`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Таблица остатков на счетах на конец дня';

--
-- Dumping data for table `balance`
--

/*!40000 ALTER TABLE `balance` DISABLE KEYS */;
INSERT INTO `balance` (`account`,`dt`,`sum`) VALUES 
 ('20202810100000000001','2022-03-19','-3580.20'),
 ('20202810100000000001','2022-03-21','-4580.20'),
 ('40800810100000000001','2022-03-19','20.00'),
 ('40800810100000000002','2022-03-19','1970.20'),
 ('40800810100000000003','2022-03-19','1511.00'),
 ('40800810100010000013','2022-03-19','2.00'),
 ('40800810100010000013','2022-03-21','1002.00'),
 ('40800810100010000014','2022-03-19','77.00'),
 ('70601810500000000001','2022-03-18','1000000000.00');
/*!40000 ALTER TABLE `balance` ENABLE KEYS */;


--
-- Definition of table `capterms`
--

DROP TABLE IF EXISTS `capterms`;
CREATE TABLE `capterms` (
  `cap` varchar(45) NOT NULL,
  `descript` varchar(100) NOT NULL,
  PRIMARY KEY (`cap`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Описание условий капитализации для вкладов';

--
-- Dumping data for table `capterms`
--

/*!40000 ALTER TABLE `capterms` DISABLE KEYS */;
INSERT INTO `capterms` (`cap`,`descript`) VALUES 
 ('dayend','В конце дня'),
 ('depositend','В конце срока'),
 ('month3end','В конце каждого третьего месяца'),
 ('monthend','В конце каждого месяца');
/*!40000 ALTER TABLE `capterms` ENABLE KEYS */;


--
-- Definition of table `clients`
--

DROP TABLE IF EXISTS `clients`;
CREATE TABLE `clients` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `birthdate` date DEFAULT NULL,
  `passport` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT 'Серия и номер паспорта',
  `address` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `phone` varchar(12) NOT NULL,
  `passgiven` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL COMMENT 'Кем выдан',
  `passcode` varchar(7) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL COMMENT 'Код подразделения',
  `passdate` date DEFAULT NULL COMMENT 'Дата выдачи',
  `sex` varchar(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL COMMENT 'Пол',
  `birthplace` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL COMMENT 'Место рождения',
  `reg` varchar(45) NOT NULL COMMENT 'Адрес регистрации',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `IDXPASS` (`passport`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Таблица с информацией о клиентах';

--
-- Dumping data for table `clients`
--

/*!40000 ALTER TABLE `clients` DISABLE KEYS */;
INSERT INTO `clients` (`id`,`name`,`email`,`birthdate`,`passport`,`address`,`phone`,`passgiven`,`passcode`,`passdate`,`sex`,`birthplace`,`reg`) VALUES 
 (1,'bank','bank@bank.ru','2022-03-19','-','','','','','0000-00-00','','',''),
 (2,'Воронин Роман Максимович','voronin@mail.ru','1998-09-12','8316 408679','Москва','223344','','','0000-00-00','','',''),
 (3,'Ефимова Каролина Алексеевна','efimova@mail.ru','1974-04-01','8703 595738','Москва','113355','','','0000-00-00','','',''),
 (9,'Фомина Варвара Львовна','fomina@mail.ru','1990-08-23','1234 123456','Москва','113377','','','0000-00-00','','',''),
 (15,'Иванов Иван Иванович','','0000-00-00','1111 789456','','789456','','','0000-00-00','','','');
/*!40000 ALTER TABLE `clients` ENABLE KEYS */;


--
-- Definition of table `converter`
--

DROP TABLE IF EXISTS `converter`;
CREATE TABLE `converter` (
  `currency` varchar(3) NOT NULL COMMENT 'валюта',
  `cost` varchar(45) NOT NULL COMMENT 'стоимость в рублях',
  `date` datetime NOT NULL COMMENT 'дата обновления',
  `current` tinyint unsigned NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Конвертер валют';

--
-- Dumping data for table `converter`
--

/*!40000 ALTER TABLE `converter` DISABLE KEYS */;
INSERT INTO `converter` (`currency`,`cost`,`date`,`current`) VALUES 
 ('840','82.62','2022-03-22 23:14:08',0),
 ('978','89.49','2022-03-22 23:14:53',0),
 ('840','81.89','2022-03-22 23:16:47',1),
 ('978','91.23','2022-03-22 23:17:06',1);
/*!40000 ALTER TABLE `converter` ENABLE KEYS */;


--
-- Definition of table `currency`
--

DROP TABLE IF EXISTS `currency`;
CREATE TABLE `currency` (
  `code` varchar(3) NOT NULL,
  `name` varchar(20) NOT NULL,
  `isocode` varchar(3) NOT NULL,
  PRIMARY KEY (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Классификатор валют';

--
-- Dumping data for table `currency`
--

/*!40000 ALTER TABLE `currency` DISABLE KEYS */;
INSERT INTO `currency` (`code`,`name`,`isocode`) VALUES 
 ('810','Российский рубль','RUR'),
 ('840','Доллар США','USD'),
 ('978','Евро','EUR');
/*!40000 ALTER TABLE `currency` ENABLE KEYS */;


--
-- Definition of table `depositeterms`
--

DROP TABLE IF EXISTS `depositeterms`;
CREATE TABLE `depositeterms` (
  `type` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `monthcnt` int unsigned DEFAULT NULL COMMENT 'Срок вклада в месяцах',
  `cap` varchar(45) NOT NULL COMMENT 'Условия уапитализации',
  `rate` decimal(15,2) NOT NULL COMMENT 'Процентная ставка',
  `descript` varchar(100) NOT NULL,
  PRIMARY KEY (`type`) USING BTREE,
  KEY `FK_depositeterms_1` (`cap`),
  CONSTRAINT `FK_depositeterms_1` FOREIGN KEY (`cap`) REFERENCES `capterms` (`cap`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Условия вкладов';

--
-- Dumping data for table `depositeterms`
--

/*!40000 ALTER TABLE `depositeterms` DISABLE KEYS */;
INSERT INTO `depositeterms` (`type`,`monthcnt`,`cap`,`rate`,`descript`) VALUES 
 ('ben1y',12,'depositend','12.00','Выгодный 1 год'),
 ('dv',NULL,'monthend','0.01','До востребования'),
 ('save1y',12,'monthend','10.00','Накопительный 1 год');
/*!40000 ALTER TABLE `depositeterms` ENABLE KEYS */;


--
-- Definition of table `deposits`
--

DROP TABLE IF EXISTS `deposits`;
CREATE TABLE `deposits` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `clientid` int unsigned NOT NULL,
  `type` varchar(45) NOT NULL,
  `opendate` date NOT NULL,
  `sum` decimal(15,2) NOT NULL,
  `mainacc` varchar(20) NOT NULL COMMENT 'Счет для суммы',
  `percacc` varchar(20) NOT NULL COMMENT 'Счет для процентов',
  PRIMARY KEY (`id`),
  KEY `FK_deposits_1` (`clientid`),
  KEY `FK_deposits_2` (`type`),
  KEY `FK_deposits_3` (`mainacc`),
  KEY `FK_deposits_4` (`percacc`),
  CONSTRAINT `FK_deposits_1` FOREIGN KEY (`clientid`) REFERENCES `clients` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT `FK_deposits_2` FOREIGN KEY (`type`) REFERENCES `depositeterms` (`type`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT `FK_deposits_3` FOREIGN KEY (`mainacc`) REFERENCES `account` (`accountnum`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT `FK_deposits_4` FOREIGN KEY (`percacc`) REFERENCES `account` (`accountnum`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Вклады';

--
-- Dumping data for table `deposits`
--

/*!40000 ALTER TABLE `deposits` DISABLE KEYS */;
/*!40000 ALTER TABLE `deposits` ENABLE KEYS */;


--
-- Definition of table `employee`
--

DROP TABLE IF EXISTS `employee`;
CREATE TABLE `employee` (
  `login` varchar(45) NOT NULL,
  `name` varchar(100) NOT NULL,
  `password` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `role` varchar(10) NOT NULL,
  PRIMARY KEY (`login`),
  KEY `FK_employee_1` (`role`),
  CONSTRAINT `FK_employee_1` FOREIGN KEY (`role`) REFERENCES `emproles` (`role`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Таблица с информацией для авторизации сотрудников';

--
-- Dumping data for table `employee`
--

/*!40000 ALTER TABLE `employee` DISABLE KEYS */;
INSERT INTO `employee` (`login`,`name`,`password`,`role`) VALUES 
 ('acc','Медведева Милана Матвеевна','1673448ee7064c989d02579c534f6b66','accountant'),
 ('admin','Николаев Павел Максимович','21232f297a57a5a743894a0e4a801fc3','admin'),
 ('oper','Киселев Давид Михайлович','fd154ffe305c26b5004231ff709bd1b8','operator'),
 ('root','Зубов Николай Андреевич','63a9f0ea7bb98050796b649e85481845','admin');
/*!40000 ALTER TABLE `employee` ENABLE KEYS */;


--
-- Definition of table `emproles`
--

DROP TABLE IF EXISTS `emproles`;
CREATE TABLE `emproles` (
  `role` varchar(10) NOT NULL,
  `descript` varchar(45) NOT NULL,
  PRIMARY KEY (`role`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Классификатор ролей';

--
-- Dumping data for table `emproles`
--

/*!40000 ALTER TABLE `emproles` DISABLE KEYS */;
INSERT INTO `emproles` (`role`,`descript`) VALUES 
 ('accountant','Бухгалтер'),
 ('admin','Администратор'),
 ('operator','Оператор');
/*!40000 ALTER TABLE `emproles` ENABLE KEYS */;


--
-- Definition of table `operations`
--

DROP TABLE IF EXISTS `operations`;
CREATE TABLE `operations` (
  `idoper` int unsigned NOT NULL AUTO_INCREMENT,
  `db` varchar(20) NOT NULL COMMENT 'debitaccountnum',
  `cr` varchar(20) NOT NULL COMMENT 'creditaccountnum',
  `operdate` datetime NOT NULL,
  `sum` decimal(15,2) NOT NULL,
  `employee` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  PRIMARY KEY (`idoper`),
  KEY `FK_operations_db` (`db`),
  KEY `FK_operations_cr` (`cr`),
  CONSTRAINT `FK_operations_cr` FOREIGN KEY (`cr`) REFERENCES `account` (`accountnum`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT `FK_operations_db` FOREIGN KEY (`db`) REFERENCES `account` (`accountnum`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Таблица операций';

--
-- Dumping data for table `operations`
--

/*!40000 ALTER TABLE `operations` DISABLE KEYS */;
INSERT INTO `operations` (`idoper`,`db`,`cr`,`operdate`,`sum`,`employee`) VALUES 
 (1,'20202810100000000001','40800810100000000002','2022-03-19 00:00:00','2000.20',''),
 (2,'20202810100000000001','40800810100000000003','2022-03-19 18:40:20','100.00',''),
 (3,'40800810100000000002','40800810100010000014','2022-03-19 18:33:41','10.00','admin'),
 (4,'40800810100000000002','40800810100000000001','2022-03-19 18:42:11','20.00','admin'),
 (5,'20202810100000000001','40800810100000000003','2022-03-19 19:16:59','400.00','admin'),
 (6,'20202810100000000001','40800810100000000003','2022-03-19 21:04:30','1000.00','admin'),
 (7,'40800810100010000014','40800810100000000003','2022-03-19 12:06:09','11.00','admin'),
 (8,'20202810100000000001','40800810100010000014','2022-03-19 15:26:02','100.00','admin'),
 (9,'40800810100010000014','40800810100010000013','2022-03-19 15:32:34','22.00','admin'),
 (10,'40800810100010000013','20202810100000000001','2022-03-19 16:22:08','10.00','admin'),
 (11,'40800810100010000013','20202810100000000001','2022-03-19 16:22:31','10.00','admin'),
 (12,'20202810100000000001','40800810100010000013','2022-03-21 20:54:53','1000.00','admin'),
 (13,'70601810500000000001','20202810100000000001','2022-03-22 21:03:31','5000.00','admin'),
 (14,'20202810100000000001','40800810100000000001','2022-03-22 16:29:10','100.00','admin'),
 (15,'20202810100000000001','40800810100000000001','2022-03-22 16:29:25','100.00','admin'),
 (16,'20202810100000000001','40800810100000000001','2022-03-22 16:30:45','100.00','admin'),
 (17,'20202810100000000001','40800810100000000001','2022-03-22 16:31:08','100.00','admin'),
 (18,'20202810100000000001','40800810100000000001','2022-03-22 16:34:01','19.00','admin'),
 (19,'20202810100000000001','40800810100000000001','2022-03-22 16:34:18','100.00','admin'),
 (20,'20202810100000000001','40800810100000000001','2022-03-22 16:34:36','100.00','admin');
/*!40000 ALTER TABLE `operations` ENABLE KEYS */;


--
-- Definition of table `operdays`
--

DROP TABLE IF EXISTS `operdays`;
CREATE TABLE `operdays` (
  `operdate` date NOT NULL,
  `current` tinyint(1) NOT NULL COMMENT 'currentday',
  PRIMARY KEY (`operdate`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Таблица операционных дней';

--
-- Dumping data for table `operdays`
--

/*!40000 ALTER TABLE `operdays` DISABLE KEYS */;
INSERT INTO `operdays` (`operdate`,`current`) VALUES 
 ('2022-03-18',0),
 ('2022-03-19',0),
 ('2022-03-21',0),
 ('2022-03-22',1);
/*!40000 ALTER TABLE `operdays` ENABLE KEYS */;




/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
