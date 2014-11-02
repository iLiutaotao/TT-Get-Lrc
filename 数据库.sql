-- phpMyAdmin SQL Dump
-- version 4.0.10.2
-- http://www.phpmyadmin.net
--
-- 主机: localhost:/tmp/mysql-5.5.37.sock
-- 生成日期: 2014-10-01 14:53:47
-- 服务器版本: 5.5.37-log
-- PHP 版本: 5.3.28

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- 数据库: `liu_lab`
--

-- --------------------------------------------------------

--
-- 表的结构 `music_list`
--

CREATE TABLE IF NOT EXISTS `music_list` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` text,
  `url` text,
  `singer` text,
  `lrc` int(11) NOT NULL,
  `lrc_data` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

--
-- 转存表中的数据 `music_list`
--

INSERT INTO `music_list` (`id`, `name`, `url`, `singer`, `lrc`, `lrc_data`) VALUES
(1, 'A Little Love', 'http://tool.liujiantao.me/upload/up/1412073125.mp3', '冯曦妤', 1, '');

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
