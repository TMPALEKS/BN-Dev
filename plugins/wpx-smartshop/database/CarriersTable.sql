-- --------------------------------------------------------
-- WP SmartShop Table SQL Dump
--
-- Tabella per la gestione dei corrieri
--
-- @author       =undo= <g.fazioli@wpxtre.me>
-- @copyright    Copyright © 2008-2011 wpXtreme, Inc.
-- @version      1.0
--
-- --------------------------------------------------------

--
-- Struttura della tabella `{wpprefix}_wpss_carriers`
--


CREATE TABLE `%s` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT 'ID Carrier',
  `name` varchar(255) CHARACTER SET utf8 NOT NULL COMMENT 'Nome del corriere: Es Poste Italiane',
  `measure_shipping_unit` enum('weight','size','volume') CHARACTER SET utf8 NOT NULL DEFAULT 'weight' COMMENT 'Unità da considerare per il calcolo della spedizione',
  `website` varchar(255) CHARACTER SET utf8 NOT NULL COMMENT 'Sito Web',
  `status` enum('publish','trash') CHARACTER SET utf8 NOT NULL DEFAULT 'publish' COMMENT 'Indica lo stato di questo record',

  PRIMARY KEY (`id`),
  KEY `status` (`status`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Tabella dei corrieri' AUTO_INCREMENT=1 ;