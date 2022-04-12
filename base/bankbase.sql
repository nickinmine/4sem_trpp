-- phpMyAdmin SQL Dump
-- version 5.1.3
-- https://www.phpmyadmin.net/
--
-- Хост: localhost
-- Время создания: Апр 12 2022 г., 15:49
-- Версия сервера: 8.0.11
-- Версия PHP: 7.4.28

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- База данных: `bankbase`
--

-- --------------------------------------------------------

--
-- Структура таблицы `account`
--

CREATE TABLE `account` (
  `idclient` int(10) UNSIGNED NOT NULL,
  `accountnum` varchar(20) NOT NULL,
  `currency` varchar(45) NOT NULL,
  `descript` varchar(100) DEFAULT NULL,
  `closed` date DEFAULT '0000-00-00',
  `type` varchar(7) NOT NULL DEFAULT 'active' COMMENT 'Тип счета. Пассивный счет - с отрицательным балансом, чтобы найти баланс умнож. на -1',
  `default` tinyint(3) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Счет для получения переводов'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Таблица с информацией о счетах';

--
-- Дамп данных таблицы `account`
--

INSERT INTO `account` (`idclient`, `accountnum`, `currency`, `descript`, `closed`, `type`, `default`) VALUES
(1, '20202810100000000001', '810', 'Счет кассы в рублях', '0000-00-00', 'passive', 0),
(1, '20202840100000000001', '840', 'Счет кассы в долларах', '0000-00-00', 'passive', 0),
(1, '20202978100000000001', '978', 'Счет кассы в евро', '0000-00-00', 'passive', 0),
(9, '40800810100000000001', '810', 'Счет физ. лица', '0000-00-00', 'active', 1),
(2, '40800810100000000002', '810', 'Счет физ. лица', '0000-00-00', 'active', 0),
(2, '40800810100000000003', '810', 'Счет физ. лица', '0000-00-00', 'active', 1),
(3, '40800810100000000004', '810', 'Счет физ. лица', '0000-00-00', 'active', 1),
(15, '40800810100010000013', '810', 'Счет физ. лица', '0000-00-00', 'active', 0),
(15, '40800810100010000014', '810', 'Счет физ. лица', '0000-00-00', 'active', 1),
(16, '40800810100010000018', '810', 'Счет физ. лица', '0000-00-00', 'active', 1),
(16, '40800810100010000019', '810', 'Счет физ. лица', '0000-00-00', 'active', 0),
(16, '40800810100010000020', '810', 'Счет физ. лица', '0000-00-00', 'active', 0),
(16, '40800810100010000021', '810', 'Счет физ. лица', '0000-00-00', 'active', 0),
(16, '40800810100010000022', '810', 'Счет физ. лица', '0000-00-00', 'active', 0),
(16, '40800840100010000004', '840', 'Счет физ. лица', '0000-00-00', 'active', 1),
(16, '40800840100010000005', '840', 'Счет физ. лица', '0000-00-00', 'active', 0),
(16, '40800840100010000006', '840', 'Счет физ. лица', '0000-00-00', 'active', 0),
(16, '40800978100010000002', '978', 'Счет физ. лица', '0000-00-00', 'active', 1),
(16, '40800978100010000003', '978', 'Счет физ. лица', '0000-00-00', 'active', 0),
(16, '40800978100010000004', '978', 'Счет физ. лица', '0000-00-00', 'active', 0),
(1, '70601810500000000001', '810', 'Счет доходов банка', '0000-00-00', 'active', 0),
(1, '70601810600000000002', '810', 'Счет расходов банка', '0000-00-00', 'active', 0);

-- --------------------------------------------------------

--
-- Структура таблицы `accountcnt`
--

CREATE TABLE `accountcnt` (
  `acc2p` varchar(5) NOT NULL COMMENT 'Первые 5 цифр счета',
  `currency` varchar(3) NOT NULL,
  `cnt` int(10) UNSIGNED NOT NULL COMMENT 'Последние 7 цифр счета'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Счетчик и количества счетов';

--
-- Дамп данных таблицы `accountcnt`
--

INSERT INTO `accountcnt` (`acc2p`, `currency`, `cnt`) VALUES
('40800', '810', 22),
('40800', '840', 6),
('40800', '978', 4);

-- --------------------------------------------------------

--
-- Структура таблицы `balance`
--

CREATE TABLE `balance` (
  `account` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `dt` varchar(45) NOT NULL COMMENT 'date',
  `sum` decimal(15,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Таблица остатков на счетах на конец дня';

--
-- Дамп данных таблицы `balance`
--

INSERT INTO `balance` (`account`, `dt`, `sum`) VALUES
('70601810500000000001', '2022-03-18', '1000000000.00');

-- --------------------------------------------------------

--
-- Структура таблицы `capterms`
--

CREATE TABLE `capterms` (
  `cap` varchar(45) NOT NULL,
  `descript` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Описание условий капитализации для вкладов';

--
-- Дамп данных таблицы `capterms`
--

INSERT INTO `capterms` (`cap`, `descript`) VALUES
('dayend', 'В конце дня'),
('depositend', 'В конце срока'),
('month3end', 'В конце каждого третьего месяца'),
('monthend', 'В конце каждого месяца');

-- --------------------------------------------------------

--
-- Структура таблицы `clients`
--

CREATE TABLE `clients` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `birthdate` date DEFAULT NULL,
  `passport` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT 'Серия и номер паспорта',
  `address` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL COMMENT 'Адрес проживания',
  `phone` varchar(12) NOT NULL,
  `passgiven` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL COMMENT 'Кем выдан',
  `passcode` varchar(7) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL COMMENT 'Код подразделения',
  `passdate` date DEFAULT NULL COMMENT 'Дата выдачи',
  `sex` varchar(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL COMMENT 'Пол',
  `birthplace` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL COMMENT 'Место рождения',
  `reg` text COMMENT 'Адрес регистрации'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Таблица с информацией о клиентах';

--
-- Дамп данных таблицы `clients`
--

INSERT INTO `clients` (`id`, `name`, `email`, `birthdate`, `passport`, `address`, `phone`, `passgiven`, `passcode`, `passdate`, `sex`, `birthplace`, `reg`) VALUES
(1, 'bank', 'bank@bank.ru', '2022-03-19', '-', '', '', '', '', '0000-00-00', '', '', NULL),
(2, 'Воронин Роман Максимович', 'voronin@mail.ru', '1998-09-12', '8316 408679', 'Москва', '223344', 'кем-то', '345-008', '0000-00-00', '', 'Хуево-Кукуево', NULL),
(3, 'Ефимова Каролина Алексеевна', 'efimova@mail.ru', '1974-04-01', '8703 595738', 'Москва', '113355', '', '', '0000-00-00', '', '', NULL),
(9, 'Фомина Варвара Львовна', 'fomina@mail.ru', '1990-08-23', '1234 123456', 'Москва', '113377', 'мной', '784-562', '2022-04-15', 'М', 'Мухосранск', 'Нур-Султан))'),
(15, 'Иванов Иван Иванович', '', '0000-00-00', '1111 789456', '', '789456', '', '', '0000-00-00', '', '', NULL),
(16, 'Тестов Тест Тестович', 'testmail@email.net', '2022-04-09', '4515 428346', 'Улица Пушкина, дом Колотушкина, квартира Петрова', '88005553535', 'Подразделение Теста', '000-001', '2022-04-10', 'М', 'Москва', 'Тест редактирования адреса регистрации'),
(17, 'Фамилия Имя Отчество', 'net-emaila@email.net', '2022-04-02', '0000 000000', 'Где-то под теплотрассой', '81234567890', 'Организация 1', '001-228', '2022-04-07', 'М', 'Регион, город', 'Отсутствует))');

-- --------------------------------------------------------

--
-- Структура таблицы `currency`
--

CREATE TABLE `currency` (
  `code` varchar(3) NOT NULL,
  `name` varchar(20) NOT NULL,
  `isocode` varchar(3) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Классификатор валют';

--
-- Дамп данных таблицы `currency`
--

INSERT INTO `currency` (`code`, `name`, `isocode`) VALUES
('810', 'Российский рубль', 'RUR'),
('840', 'Доллар США', 'USD'),
('978', 'Евро', 'EUR');

-- --------------------------------------------------------

--
-- Структура таблицы `depositeterms`
--

CREATE TABLE `depositeterms` (
  `type` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `monthcnt` int(10) UNSIGNED DEFAULT NULL COMMENT 'Срок вклада в месяцах',
  `cap` varchar(45) NOT NULL COMMENT 'Условия уапитализации',
  `rate` decimal(15,2) NOT NULL COMMENT 'Процентная ставка',
  `descript` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Условия вкладов';

--
-- Дамп данных таблицы `depositeterms`
--

INSERT INTO `depositeterms` (`type`, `monthcnt`, `cap`, `rate`, `descript`) VALUES
('ben1y', 12, 'depositend', '12.00', 'Выгодный 1 год'),
('dv', NULL, 'monthend', '0.01', 'До востребования'),
('save1y', 12, 'monthend', '10.00', 'Накопительный 1 год');

-- --------------------------------------------------------

--
-- Структура таблицы `deposits`
--

CREATE TABLE `deposits` (
  `id` int(10) UNSIGNED NOT NULL,
  `clientid` int(10) UNSIGNED NOT NULL,
  `type` varchar(45) NOT NULL,
  `opendate` date NOT NULL,
  `sum` decimal(15,2) NOT NULL,
  `mainacc` varchar(20) NOT NULL COMMENT 'Счет для суммы',
  `percacc` varchar(20) NOT NULL COMMENT 'Счет для процентов'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Вклады';

-- --------------------------------------------------------

--
-- Структура таблицы `employee`
--

CREATE TABLE `employee` (
  `login` varchar(45) NOT NULL,
  `name` varchar(100) NOT NULL,
  `password` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `role` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Таблица с информацией для авторизации сотрудников';

--
-- Дамп данных таблицы `employee`
--

INSERT INTO `employee` (`login`, `name`, `password`, `role`) VALUES
('acc', 'Медведева Милана Матвеевна', '1673448ee7064c989d02579c534f6b66', 'accountant'),
('admin', 'Николаев Павел Максимович', '21232f297a57a5a743894a0e4a801fc3', 'admin'),
('oper', 'Киселев Давид Михайлович', 'fd154ffe305c26b5004231ff709bd1b8', 'operator'),
('org1', 'Организация 1', 'd448b95936703db7d0923122172fb13c', 'operator'),
('org2', 'Организация 2', '042aec8b8d22ba46cd428a16b50f640b', 'accountant'),
('root', 'Зубов Николай Андреевич', '63a9f0ea7bb98050796b649e85481845', 'admin');

-- --------------------------------------------------------

--
-- Структура таблицы `emproles`
--

CREATE TABLE `emproles` (
  `role` varchar(10) NOT NULL,
  `descript` varchar(45) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Классификатор ролей';

--
-- Дамп данных таблицы `emproles`
--

INSERT INTO `emproles` (`role`, `descript`) VALUES
('accountant', 'Бухгалтер'),
('admin', 'Администратор'),
('operator', 'Оператор');

-- --------------------------------------------------------

--
-- Структура таблицы `operations`
--

CREATE TABLE `operations` (
  `db` varchar(20) NOT NULL COMMENT 'debitaccountnum',
  `cr` varchar(20) NOT NULL COMMENT 'creditaccountnum',
  `operdate` datetime NOT NULL,
  `sum` decimal(15,2) NOT NULL,
  `employee` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Таблица операций';

--
-- Дамп данных таблицы `operations`
--

INSERT INTO `operations` (`db`, `cr`, `operdate`, `sum`, `employee`) VALUES
('20202810100000000001', '40800810100000000002', '2022-03-19 00:00:00', '2000.20', ''),
('20202810100000000001', '40800810100000000003', '2022-03-19 18:40:20', '100.00', ''),
('40800810100000000002', '40800810100010000014', '2022-03-19 18:33:41', '10.00', 'admin'),
('40800810100000000002', '40800810100000000001', '2022-03-19 18:42:11', '20.00', 'admin'),
('20202810100000000001', '40800810100000000003', '2022-03-19 19:16:59', '400.00', 'admin'),
('20202810100000000001', '40800810100000000003', '2022-03-19 21:04:30', '1000.00', 'admin'),
('40800810100010000013', '40800810100000000001', '2022-03-19 00:21:28', '5.00', 'root'),
('40800810100010000013', '40800810100000000001', '2022-03-19 00:22:16', '5.00', 'root'),
('20202810100000000001', '40800810100000000001', '2022-03-19 00:22:46', '6845656.00', 'root'),
('20202810100000000001', '40800810100000000001', '2022-03-19 00:22:57', '-454564341.00', 'root'),
('20202810100000000001', '40800810100000000001', '2022-03-19 00:23:02', '854687486.00', 'root');

-- --------------------------------------------------------

--
-- Структура таблицы `operdays`
--

CREATE TABLE `operdays` (
  `operdate` date NOT NULL,
  `current` tinyint(1) NOT NULL COMMENT 'currentday'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Таблица операционных дней';

--
-- Дамп данных таблицы `operdays`
--

INSERT INTO `operdays` (`operdate`, `current`) VALUES
('2022-03-18', 0),
('2022-03-19', 1);

--
-- Индексы сохранённых таблиц
--

--
-- Индексы таблицы `account`
--
ALTER TABLE `account`
  ADD PRIMARY KEY (`accountnum`) USING BTREE,
  ADD KEY `FK_account_2` (`idclient`),
  ADD KEY `FK_account_3` (`currency`);

--
-- Индексы таблицы `accountcnt`
--
ALTER TABLE `accountcnt`
  ADD PRIMARY KEY (`acc2p`,`currency`) USING BTREE;

--
-- Индексы таблицы `balance`
--
ALTER TABLE `balance`
  ADD PRIMARY KEY (`account`,`dt`) USING BTREE;

--
-- Индексы таблицы `capterms`
--
ALTER TABLE `capterms`
  ADD PRIMARY KEY (`cap`);

--
-- Индексы таблицы `clients`
--
ALTER TABLE `clients`
  ADD PRIMARY KEY (`id`) USING BTREE,
  ADD UNIQUE KEY `IDXPASS` (`passport`);

--
-- Индексы таблицы `currency`
--
ALTER TABLE `currency`
  ADD PRIMARY KEY (`code`);

--
-- Индексы таблицы `depositeterms`
--
ALTER TABLE `depositeterms`
  ADD PRIMARY KEY (`type`) USING BTREE,
  ADD KEY `FK_depositeterms_1` (`cap`);

--
-- Индексы таблицы `deposits`
--
ALTER TABLE `deposits`
  ADD PRIMARY KEY (`id`),
  ADD KEY `FK_deposits_1` (`clientid`),
  ADD KEY `FK_deposits_2` (`type`),
  ADD KEY `FK_deposits_3` (`mainacc`),
  ADD KEY `FK_deposits_4` (`percacc`);

--
-- Индексы таблицы `employee`
--
ALTER TABLE `employee`
  ADD PRIMARY KEY (`login`),
  ADD KEY `FK_employee_1` (`role`);

--
-- Индексы таблицы `emproles`
--
ALTER TABLE `emproles`
  ADD PRIMARY KEY (`role`);

--
-- Индексы таблицы `operations`
--
ALTER TABLE `operations`
  ADD KEY `FK_operations_db` (`db`),
  ADD KEY `FK_operations_cr` (`cr`);

--
-- Индексы таблицы `operdays`
--
ALTER TABLE `operdays`
  ADD PRIMARY KEY (`operdate`) USING BTREE;

--
-- AUTO_INCREMENT для сохранённых таблиц
--

--
-- AUTO_INCREMENT для таблицы `clients`
--
ALTER TABLE `clients`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT для таблицы `deposits`
--
ALTER TABLE `deposits`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Ограничения внешнего ключа сохраненных таблиц
--

--
-- Ограничения внешнего ключа таблицы `account`
--
ALTER TABLE `account`
  ADD CONSTRAINT `FK_account_2` FOREIGN KEY (`idclient`) REFERENCES `clients` (`id`),
  ADD CONSTRAINT `FK_account_3` FOREIGN KEY (`currency`) REFERENCES `currency` (`code`);

--
-- Ограничения внешнего ключа таблицы `balance`
--
ALTER TABLE `balance`
  ADD CONSTRAINT `FK_balance_1` FOREIGN KEY (`account`) REFERENCES `account` (`accountnum`) ON UPDATE CASCADE;

--
-- Ограничения внешнего ключа таблицы `depositeterms`
--
ALTER TABLE `depositeterms`
  ADD CONSTRAINT `FK_depositeterms_1` FOREIGN KEY (`cap`) REFERENCES `capterms` (`cap`);

--
-- Ограничения внешнего ключа таблицы `deposits`
--
ALTER TABLE `deposits`
  ADD CONSTRAINT `FK_deposits_1` FOREIGN KEY (`clientid`) REFERENCES `clients` (`id`),
  ADD CONSTRAINT `FK_deposits_2` FOREIGN KEY (`type`) REFERENCES `depositeterms` (`type`),
  ADD CONSTRAINT `FK_deposits_3` FOREIGN KEY (`mainacc`) REFERENCES `account` (`accountnum`),
  ADD CONSTRAINT `FK_deposits_4` FOREIGN KEY (`percacc`) REFERENCES `account` (`accountnum`);

--
-- Ограничения внешнего ключа таблицы `employee`
--
ALTER TABLE `employee`
  ADD CONSTRAINT `FK_employee_1` FOREIGN KEY (`role`) REFERENCES `emproles` (`role`);

--
-- Ограничения внешнего ключа таблицы `operations`
--
ALTER TABLE `operations`
  ADD CONSTRAINT `FK_operations_cr` FOREIGN KEY (`cr`) REFERENCES `account` (`accountnum`),
  ADD CONSTRAINT `FK_operations_db` FOREIGN KEY (`db`) REFERENCES `account` (`accountnum`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
