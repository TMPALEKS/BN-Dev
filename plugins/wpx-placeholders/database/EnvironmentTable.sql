-- --------------------------------------------------------
-- wpx Placeholders Table SQL Dump
--
-- Tabella per la gestione degli ambienti
--
-- @author       =undo= <g.fazioli@wpxtre.me>
-- @copyright    Copyright © 2012 Saidmade Srl
-- @version      1.0
--
-- --------------------------------------------------------

--
-- Struttura della tabella `{wpprefix}_wpph_environment`
--


CREATE TABLE `%s` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT 'ID Environment',
  `description` varchar(255) CHARACTER SET utf8 NOT NULL COMMENT 'Descrizione ambiente, Es. Piano Terra, Balconata, etc....',
  `image` varchar(255) CHARACTER SET utf8 NOT NULL COMMENT 'Immagine',
  `status` enum('publish','trash') CHARACTER SET utf8 NOT NULL DEFAULT 'publish' COMMENT 'Indica lo stato di questo record',

  PRIMARY KEY (`id`),
  KEY `status` (`status`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Tabella degli ambeinti' AUTO_INCREMENT=1;