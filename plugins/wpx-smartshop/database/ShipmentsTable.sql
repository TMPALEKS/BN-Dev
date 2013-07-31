-- --------------------------------------------------------
-- WP SmartShop Table SQL Dump
--
-- Tabella per la gestione delle spedizioni. Questa lega le
-- Zone, Corrieri, peso, dimensione e prezzo
--
-- @author       =undo= <g.fazioli@wpxtre.me>
-- @copyright    Copyright © 2008-2011 wpXtreme, Inc.
-- @version      1.0
--
-- --------------------------------------------------------

--
-- Struttura della tabella `{wpprefix}_wpss_shipments`
--

CREATE TABLE `%s` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT 'ID Spedizione',
  `id_carrier` bigint(20) NOT NULL DEFAULT '0' COMMENT 'ID del corriere',
  `id_size_shipment` bigint(20) NOT NULL DEFAULT '0' COMMENT 'ID della dimensione',
  `zone` varchar(255) CHARACTER SET utf8 NOT NULL DEFAULT '' COMMENT 'Zona',
  `price` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT 'Costo',
  `status` enum('publish','trash') CHARACTER SET utf8 NOT NULL DEFAULT 'publish' COMMENT 'Indica lo stato di questo record',

  PRIMARY KEY (`id`),
  KEY `id_carrier` (`id_carrier`),
  KEY `id_size_shipment` (`id_size_shipment`),
  KEY `zone` (`zone`),
  KEY `status` (`status`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Tabella delle spedizioni' AUTO_INCREMENT=1 ;