-- --------------------------------------------------------
-- WP SmartShop Table SQL Dump
--
-- Tabella per la gestione delle dimensioni
--
-- @author       =undo= <g.fazioli@wpxtre.me>
-- @copyright    Copyright © 2008-2011 wpXtreme, Inc.
-- @version      1.0
--
-- --------------------------------------------------------

--
-- Struttura della tabella `{wpprefix}_wpss_size_shipments`
--


CREATE TABLE `%s` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT 'ID Dimensione',

  `weight_from` decimal(8,2) NOT NULL DEFAULT '0.00' COMMENT 'Dal peso',
  `weight_to` decimal(8,2) NOT NULL DEFAULT '0.00' COMMENT 'Al peso',

  `width_from` decimal(8,2) NOT NULL DEFAULT '0.00' COMMENT 'Dalla larghezza',
  `width_to` decimal(8,2) NOT NULL DEFAULT '0.00' COMMENT 'Alla larghezza',

  `height_from` decimal(8,2) NOT NULL DEFAULT '0.00' COMMENT 'Dall''altezza',
  `height_to` decimal(8,2) NOT NULL DEFAULT '0.00' COMMENT 'All''altezza',

  `depth_from` decimal(8,2) NOT NULL DEFAULT '0.00' COMMENT 'Dalla profondità',
  `depth_to` decimal(8,2) NOT NULL DEFAULT '0.00' COMMENT 'Alla profondità',

  `volume` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT 'Volume',

  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Tabella delle dimensioni' AUTO_INCREMENT=1 ;
