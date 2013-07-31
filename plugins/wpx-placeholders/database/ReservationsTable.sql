-- --------------------------------------------------------
-- wpx Placeholders Table SQL Dump
--
-- Tabella per la gestione dei posti occupati
--
-- @author       =undo= <g.fazioli@wpxtre.me>
-- @copyright    Copyright © 2012 Saidmade Srl
-- @version      1.0
--
-- --------------------------------------------------------

--
-- Struttura della tabella `{wpprefix}_wpph_reservations`
--


CREATE TABLE `%s` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT 'ID Prenotazione',
  `id_place` bigint(20) NOT NULL COMMENT 'ID Place',
  `id_who` bigint(20) DEFAULT NULL COMMENT 'User data per legare chi o cosa ha prenotato',
  `date_start` datetime NOT NULL COMMENT 'Occupato dal',
  `date_expiry` datetime NOT NULL COMMENT 'Occupato fino',
  `status` enum('publish','trash') CHARACTER SET utf8 NOT NULL DEFAULT 'publish' COMMENT 'Indica lo stato di questo record',

  PRIMARY KEY (`id`),
  KEY `id_place` (`id_place`),
  KEY `status` (`status`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Tabella delle prenotazioni' AUTO_INCREMENT=1;