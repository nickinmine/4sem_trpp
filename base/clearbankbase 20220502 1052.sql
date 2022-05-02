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
  `default` tinyint unsigned NOT NULL DEFAULT '0' COMMENT 'Счет для получения переводов',
  PRIMARY KEY (`accountnum`) USING BTREE,
  KEY `FK_account_2` (`idclient`),
  KEY `FK_account_3` (`currency`),
  CONSTRAINT `FK_account_2` FOREIGN KEY (`idclient`) REFERENCES `clients` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_account_3` FOREIGN KEY (`currency`) REFERENCES `currency` (`code`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Таблица с информацией о счетах';

--
-- Dumping data for table `account`
--

/*!40000 ALTER TABLE `account` DISABLE KEYS */;
INSERT INTO `account` (`idclient`,`accountnum`,`currency`,`descript`,`closed`,`default`) VALUES 
 (1,'20202810100000000001','810','Счет кассы','0000-00-00',0),
 (1,'20202840100010000001','840','Счет кассы','0000-00-00',0),
 (1,'20202978100010000001','978','Счет кассы','0000-00-00',0),
 (1,'70601810500000000001','810','Счет доходов','0000-00-00',0),
 (1,'70601840100000000001','840','Счет доходов','0000-00-00',0),
 (1,'70601978100000000001','978','Счет доходов','0000-00-00',0),
 (1,'70606810600010000001','810','Счет расходов','0000-00-00',0),
 (1,'70606840600000000001','840','Счет расходов','0000-00-00',0),
 (1,'70606978600000000001','978','Счет расходов','0000-00-00',0);
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
/*!40000 ALTER TABLE `accountcnt` ENABLE KEYS */;


--
-- Definition of table `accounttype`
--

DROP TABLE IF EXISTS `accounttype`;
CREATE TABLE `accounttype` (
  `acc2p` varchar(5) NOT NULL,
  `type` varchar(10) NOT NULL,
  `descr` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`acc2p`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `accounttype`
--

/*!40000 ALTER TABLE `accounttype` DISABLE KEYS */;
INSERT INTO `accounttype` (`acc2p`,`type`,`descr`) VALUES 
 ('20202','active','касса'),
 ('30303','passive','конвертация валют'),
 ('40817','passive','текущие счета физ. диц'),
 ('42301','passive','учет вкладов'),
 ('45505','active','учет осн. долга по КД'),
 ('45815','active','учет проср. долга по КД'),
 ('45915','active','учет проср. процентов по КД'),
 ('47411','passive','учет процентов по вкладам'),
 ('47427','active','учет осн. процентов по КД'),
 ('70601','passive','доходы банка'),
 ('70606','active','расходы банка');
/*!40000 ALTER TABLE `accounttype` ENABLE KEYS */;


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
 ('','В конце срока'),
 ('+1 month','В конце каждого месяца'),
 ('+3 month','В конце каждого третьего месяца');
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Таблица с информацией о клиентах';

--
-- Dumping data for table `clients`
--

/*!40000 ALTER TABLE `clients` DISABLE KEYS */;
/*!40000 ALTER TABLE `clients` ENABLE KEYS */;


--
-- Definition of table `converter`
--

DROP TABLE IF EXISTS `converter`;
CREATE TABLE `converter` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `currency` varchar(3) NOT NULL,
  `buy` decimal(15,2) NOT NULL,
  `cost` decimal(15,2) NOT NULL,
  `sell` decimal(15,2) NOT NULL,
  `dt` datetime NOT NULL,
  `current` tinyint unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `FK_converter_1` (`currency`),
  CONSTRAINT `FK_converter_1` FOREIGN KEY (`currency`) REFERENCES `currency` (`code`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Конвертер валют';

--
-- Dumping data for table `converter`
--

/*!40000 ALTER TABLE `converter` DISABLE KEYS */;
INSERT INTO `converter` (`id`,`currency`,`buy`,`cost`,`sell`,`dt`,`current`) VALUES 
 (2,'978','95.85','96.00','96.25','2022-03-22 11:13:42',1),
 (3,'840','80.90','81.00','81.15','2022-03-22 11:14:12',1),
 (4,'810','1.00','1.00','1.00','2022-03-22 11:19:58',1);
/*!40000 ALTER TABLE `converter` ENABLE KEYS */;


--
-- Definition of table `creditgraph`
--

DROP TABLE IF EXISTS `creditgraph`;
CREATE TABLE `creditgraph` (
  `id` int unsigned NOT NULL,
  `n` int unsigned NOT NULL,
  `dateplat` date NOT NULL,
  `sumod` decimal(15,2) NOT NULL,
  `sumpc` decimal(15,2) NOT NULL,
  `processed` tinyint unsigned NOT NULL DEFAULT '0',
  UNIQUE KEY `Index_2` (`id`,`n`,`dateplat`),
  CONSTRAINT `FK_creditgraph_1` FOREIGN KEY (`id`) REFERENCES `credits` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='графики погашения';

--
-- Dumping data for table `creditgraph`
--

/*!40000 ALTER TABLE `creditgraph` DISABLE KEYS */;
/*!40000 ALTER TABLE `creditgraph` ENABLE KEYS */;


--
-- Definition of table `credits`
--

DROP TABLE IF EXISTS `credits`;
CREATE TABLE `credits` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `idclient` int unsigned NOT NULL,
  `type` varchar(45) NOT NULL,
  `opendate` date NOT NULL,
  `closedate` date DEFAULT NULL,
  `curacc` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT 'текущий счет',
  `odacc` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT 'осн. долг',
  `pcacc` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT 'осн. %%',
  `prodacc` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL COMMENT 'проср. долг',
  `prpcacc` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL COMMENT 'проср. %%',
  `update` date DEFAULT NULL COMMENT 'дата посл. обновления',
  PRIMARY KEY (`id`),
  KEY `FK_credits_1` (`idclient`),
  KEY `FK_credits_2` (`type`),
  CONSTRAINT `FK_credits_1` FOREIGN KEY (`idclient`) REFERENCES `clients` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT `FK_credits_2` FOREIGN KEY (`type`) REFERENCES `creditterms` (`type`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='кредитные договоры';

--
-- Dumping data for table `credits`
--

/*!40000 ALTER TABLE `credits` DISABLE KEYS */;
/*!40000 ALTER TABLE `credits` ENABLE KEYS */;


--
-- Definition of table `creditterms`
--

DROP TABLE IF EXISTS `creditterms`;
CREATE TABLE `creditterms` (
  `type` varchar(45) NOT NULL,
  `monthcnt` int unsigned NOT NULL,
  `rate` decimal(15,2) NOT NULL COMMENT '% ставка',
  `ovdrate` decimal(15,2) NOT NULL COMMENT 'штрафная % ставка',
  `descript` varchar(100) NOT NULL,
  PRIMARY KEY (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `creditterms`
--

/*!40000 ALTER TABLE `creditterms` DISABLE KEYS */;
INSERT INTO `creditterms` (`type`,`monthcnt`,`rate`,`ovdrate`,`descript`) VALUES 
 ('ben1',12,'20.00','40.00','обычный'),
 ('ben2',18,'19.00','38.00','средний'),
 ('short1',6,'25.00','50.00','быстрый'),
 ('short2',3,'30.00','60.00','сверхбыстрый');
/*!40000 ALTER TABLE `creditterms` ENABLE KEYS */;


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
  `currency` varchar(3) NOT NULL,
  PRIMARY KEY (`type`) USING BTREE,
  KEY `FK_depositeterms_1` (`cap`),
  KEY `FK_depositeterms_2` (`currency`),
  CONSTRAINT `FK_depositeterms_2` FOREIGN KEY (`currency`) REFERENCES `currency` (`code`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Условия вкладов (только в рублях)';

--
-- Dumping data for table `depositeterms`
--

/*!40000 ALTER TABLE `depositeterms` DISABLE KEYS */;
INSERT INTO `depositeterms` (`type`,`monthcnt`,`cap`,`rate`,`descript`,`currency`) VALUES 
 ('ben1y',12,'','12.00','Выгодный 1 год','810'),
 ('dollar6m',6,'+3 month','5.00','Долларовый 6 месяцев','840'),
 ('dv',NULL,'+1 month','0.10','До востребования','810'),
 ('save1y',12,'+1 month','10.00','Накопительный 1 год','810');
/*!40000 ALTER TABLE `depositeterms` ENABLE KEYS */;


--
-- Definition of table `deposits`
--

DROP TABLE IF EXISTS `deposits`;
CREATE TABLE `deposits` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `idclient` int unsigned NOT NULL,
  `type` varchar(45) NOT NULL,
  `opendate` date NOT NULL,
  `closedate` date DEFAULT NULL,
  `mainacc` varchar(20) NOT NULL COMMENT 'Счет для суммы',
  `percacc` varchar(20) NOT NULL COMMENT 'Счет для процентов',
  `update` date NOT NULL COMMENT 'Дата последнего пересчета вклада',
  `capdate` date DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_deposits_2` (`type`),
  KEY `FK_deposits_3` (`mainacc`),
  KEY `FK_deposits_4` (`percacc`),
  KEY `FK_deposits_1` (`idclient`) USING BTREE,
  CONSTRAINT `FK_deposits_1` FOREIGN KEY (`idclient`) REFERENCES `clients` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Таблица операций';

--
-- Dumping data for table `operations`
--

/*!40000 ALTER TABLE `operations` DISABLE KEYS */;
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
 ('2022-01-01',1);
/*!40000 ALTER TABLE `operdays` ENABLE KEYS */;




/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
