-- --------------------------------------------------------
-- wpx Placeholders Table SQL Dump
--
-- Tabella per la gestione dei singoli posti
--
-- @author       =undo= <g.fazioli@wpxtre.me>
-- @copyright    Copyright © 2012 Saidmade Srl
-- @version      1.0
--
-- --------------------------------------------------------

--
-- Struttura della tabella `{wpprefix}_wpph_places`
--


CREATE TABLE `%s` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT 'ID Place',
  `id_environment` bigint(20) NOT NULL COMMENT 'ID Environment',
  `name` varchar(255) CHARACTER SET utf8 NOT NULL COMMENT 'Identificativo del post: 160, 160T, ...',
  `description` varchar(255) CHARACTER SET utf8 NOT NULL COMMENT 'Ulteriore descrizione: panchina, posto VIP, ...',
  `size` int(11) NOT NULL DEFAULT '1' COMMENT 'Numero di posti da occupare',
  `image` varchar(255) CHARACTER SET utf8 NOT NULL COMMENT 'Immagine',
  `status` enum('publish','trash') CHARACTER SET utf8 NOT NULL DEFAULT 'publish' COMMENT 'Indica lo stato di questo record',

  PRIMARY KEY (`id`),
  KEY `id_environment` (`id_environment`),
  KEY `name` (`name`),
  KEY `status` (`status`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Tabella dei singoli posti' AUTO_INCREMENT=1;