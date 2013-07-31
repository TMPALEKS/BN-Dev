-- --------------------------------------------------------
-- BNMExtendsEventsArtists Table SQL Dump
--
-- @author       =undo= <g.fazioli@saidmade.com>
-- @copyright    Copyright © 2008-2011 Saidmade Srl
-- @version      1.0
--
-- --------------------------------------------------------

CREATE TABLE `%s` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT 'Primary autoincrement ID',
  `id_event` bigint(20) NOT NULL COMMENT 'ID Events',
  `id_artist` bigint(20) NOT NULL COMMENT 'ID Artists',
  `show_time` time NOT NULL COMMENT 'Time',
  PRIMARY KEY (`id`),
  KEY `id` (`id`),
  KEY `id_event` (`id_event`),
  KEY `id_artist` (`id_artist`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;