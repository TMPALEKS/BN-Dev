-- --------------------------------------------------------
-- WP SmartShop Table SQL Dump
--
-- Tabella per la gestione delle zone di spedizione e i paesi
--
-- @author       =undo= <g.fazioli@wpxtre.me>
-- @copyright    Copyright © 2008-2011 wpXtreme, Inc.
-- @version      1.0
--
-- --------------------------------------------------------

--
-- Struttura della tabella `{wpprefix}_wpss_shipping_countries`
--

CREATE TABLE `%s` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID Zona-Paese',
  `zone` varchar(255) CHARACTER SET utf8 NOT NULL DEFAULT '' COMMENT 'Zona',
  `country` varchar(255) CHARACTER SET utf8 NOT NULL DEFAULT '' COMMENT 'Paese',
  `isocode` char(2) CHARACTER SET utf8 DEFAULT '' COMMENT 'ISO CODE',
  `currency` varchar(255) CHARACTER SET utf8 NOT NULL DEFAULT '' COMMENT 'Moneta',
  `symbol` varchar(10) CHARACTER SET utf8 NOT NULL DEFAULT '' COMMENT 'Simbolo',
  `symbol_html` varchar(10) CHARACTER SET utf8 NOT NULL DEFAULT '' COMMENT 'Simbolo in HTML',
  `code` char(3) CHARACTER SET utf8 NOT NULL DEFAULT '' COMMENT 'Codice',
  `tax` decimal(8,2) NOT NULL DEFAULT '0.00' COMMENT 'Tasse',
  `continent` varchar(20) CHARACTER SET utf8 NOT NULL DEFAULT '' COMMENT 'Continente',
  `status` enum('publish','trash') CHARACTER SET utf8 NOT NULL DEFAULT 'publish' COMMENT 'Indica lo stato di questo record',

  PRIMARY KEY (`id`),
  KEY `status` (`status`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Tabella per la lista dei country, Zone, Tasse e la gestione della moneta' AUTO_INCREMENT=242;
