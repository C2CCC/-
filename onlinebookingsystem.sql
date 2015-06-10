-- phpMyAdmin SQL Dump
-- version 3.2.0.1
-- http://www.phpmyadmin.net
--
-- 主机: localhost
-- 生成日期: 2015 年 06 月 10 日 10:07
-- 服务器版本: 5.5.8
-- PHP 版本: 5.3.3

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- 数据库: `onlinebookingsystem`
--

-- --------------------------------------------------------

--
-- 表的结构 `bookedtickets`
--

CREATE TABLE IF NOT EXISTS `bookedtickets` (
  `username` varchar(20) NOT NULL,
  `tno` varchar(20) NOT NULL,
  `amount` int(11) NOT NULL,
  PRIMARY KEY (`username`,`tno`),
  KEY `tno` (`tno`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- 转存表中的数据 `bookedtickets`
--

INSERT INTO `bookedtickets` (`username`, `tno`, `amount`) VALUES
('031302305', '20150604234200', 2),
('031302305', '20150604234300', 4),
('031302305', '20150610125403', 2),
('031302305', '20150610125530', 3),
('boss', '20150610125403', 3),
('jg', '20150604234200', 2),
('jg', '20150604234300', 7),
('jg', '20150610125403', 5),
('jg', '20150610125530', 4),
('jg1', '20150604234200', 4),
('jg1', '20150604234300', 3),
('jg1', '20150610125403', 1),
('jgxy', '20150604234200', 2),
('sj', '20150610125403', 7),
('sj1', '20150604234200', 3),
('sj1', '20150610125530', 5),
('sjxy', '20150604234300', 5),
('wx', '20150604234200', 2),
('wx', '20150604234300', 6),
('wx', '20150610125530', 1),
('wx1', '20150604234200', 20),
('wx1', '20150604234300', 15),
('wxxy', '20150604234200', 6),
('wxxy', '20150604234300', 1);

-- --------------------------------------------------------

--
-- 表的结构 `tickets`
--

CREATE TABLE IF NOT EXISTS `tickets` (
  `tno` varchar(20) NOT NULL,
  `fromto` varchar(20) NOT NULL,
  `time` datetime NOT NULL,
  `price` int(11) NOT NULL,
  `rest` int(11) NOT NULL,
  `deadline` datetime NOT NULL,
  `overdue` int(11) NOT NULL,
  `changed` int(11) NOT NULL,
  PRIMARY KEY (`tno`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- 转存表中的数据 `tickets`
--

INSERT INTO `tickets` (`tno`, `fromto`, `time`, `price`, `rest`, `deadline`, `overdue`, `changed`) VALUES
('20150604234200', '福州-晋江', '2015-06-16 15:00:00', 66, 115, '2015-06-27 14:00:00', 0, 0),
('20150604234300', '福州-石狮', '2015-06-25 08:00:00', 66, 115, '2015-06-14 00:00:00', 0, 0),
('20150609184327', 'abcddd', '2015-06-06 06:06:00', 2333, 666666, '2015-06-07 07:21:00', 0, 1),
('20150610125403', '福州-厦门', '2015-06-27 14:00:00', 66, 102, '2015-06-13 00:00:00', 0, 0),
('20150610125530', '福州-漳州', '2015-06-28 13:00:00', 66, 97, '2015-06-15 02:00:00', 0, 1);

-- --------------------------------------------------------

--
-- 表的结构 `user`
--

CREATE TABLE IF NOT EXISTS `user` (
  `username` varchar(20) NOT NULL,
  `password` varchar(32) NOT NULL,
  `department` varchar(20) NOT NULL,
  `manager` int(1) NOT NULL,
  `chief` int(1) NOT NULL,
  PRIMARY KEY (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- 转存表中的数据 `user`
--

INSERT INTO `user` (`username`, `password`, `department`, `manager`, `chief`) VALUES
('031302305', '548c482bf7fb6993c66b342720e0e3ba', '数计学院', 0, 0),
('admin', '21232f297a57a5a743894a0e4a801fc3', '数计学院', 1, 0),
('boss', 'ceb8447cc4ab78d2ec34cd9f11e4bed2', '数计学院', 1, 1),
('boss1', 'c333ba0d6e308bdb32ce3f2785301ae8', '经管学院', 1, 1),
('jg', '1272c19590c3d44ce33ba054edfb9c78', '经管学院', 0, 0),
('jg1', '8fe9a7a0af0ce89b8fd41378f45197e9', '经管学院', 0, 0),
('jgxy', '74ab4fd253713b098b9968f82242d2b1', '经管学院', 1, 0),
('sj', 'b5bf27b2555de44e3df2230080db5a1d', '数计学院', 0, 0),
('sj1', '2f367468a269b6e362b45b8b408fc321', '数计学院', 0, 0),
('sjxy', '9335687fa85200b11f73868695f4816c', '数计学院', 1, 0),
('test', '098f6bcd4621d373cade4e832627b4f6', '数计学院', 1, 0),
('test1', '5a105e8b9d40e1329780d62ea2265d8a', '数计学院', 1, 1),
('testttt', '4728960471f6f8cf6130b05e3a27bf5a', '数计学院', 0, 0),
('wx', '79b4de7cf79777bf4af9e213ede350af', '物信学院', 0, 0),
('wx1', '6dc9fecf1753319aee99dcc3a8cbb8ed', '物信学院', 0, 0),
('wxxy', '4c36272f3e1f72a2c40429d6d58a5355', '物信学院', 1, 0);

--
-- 限制导出的表
--

--
-- 限制表 `bookedtickets`
--
ALTER TABLE `bookedtickets`
  ADD CONSTRAINT `bookedtickets_ibfk_1` FOREIGN KEY (`username`) REFERENCES `user` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `bookedtickets_ibfk_2` FOREIGN KEY (`tno`) REFERENCES `tickets` (`tno`) ON DELETE CASCADE ON UPDATE CASCADE;
