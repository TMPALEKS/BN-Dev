-- --------------------------------------------------------
-- BNMExtendsUsers Table SQL Dump
--
-- @author       =undo= <g.fazioli@saidmade.com>
-- @copyright    Copyright © 2008-2011 Saidmade Srl
-- @version      1.0
--
-- --------------------------------------------------------

--
-- Struttura della tabella `{wpprefix}_bnmextends_users`
--

CREATE TABLE `%s` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT 'Primary autoincrement ID',
  `id_user` bigint(20) NOT NULL COMMENT 'ID utenza WordPress quando l''utente passa da pending a confirmed',
  `request_datetime` datetime NOT NULL COMMENT 'Data richiesta registazione',

  `uniqid` varchar(64) CHARACTER SET utf8 NOT NULL COMMENT 'ID univoco per questo utente, usato per sbloccare l''utenza',
  `ip_address` varchar(16) CHARACTER SET utf8 NOT NULL COMMENT 'IP',
  `browser` varchar(255) CHARACTER SET utf8 NOT NULL COMMENT 'Browser information',

  `company_name` varchar(255) CHARACTER SET utf8 NOT NULL COMMENT 'Ragione sociale',
  `vat_number` varchar(255) CHARACTER SET utf8 NOT NULL COMMENT 'partita iva',

  `first_name` varchar(255) CHARACTER SET utf8 NOT NULL COMMENT 'Nome',
  `last_name` varchar(255) CHARACTER SET utf8 NOT NULL COMMENT 'Cognome',
  `birth_date` date DEFAULT NULL COMMENT 'Data di nascita',
  `sex` enum('-','f','m') CHARACTER SET utf8 NOT NULL DEFAULT '-' COMMENT 'Sesso',
  `email` varchar(255) CHARACTER SET utf8 NOT NULL COMMENT 'Indirizzo posta elettronica',
  `job_position` varchar(255) CHARACTER SET utf8 NOT NULL NOT NULL DEFAULT '1' COMMENT 'Posizione lavorativa',
  `newsletter` enum('y','n') CHARACTER SET utf8 NOT NULL DEFAULT 'y' COMMENT 'Registrazione alla Newsletter',

  `bill_address` varchar(255) CHARACTER SET utf8 NOT NULL COMMENT 'Indirizzo dell''acquirente',
  `bill_country` bigint(20) NOT NULL COMMENT 'Foreign Key verso la tabella wpss_shipping_countries che ha i paesi',
  `bill_town` varchar(255) CHARACTER SET utf8 NOT NULL COMMENT 'Città dell''acquirente',
  `bill_zipcode` varchar(255) CHARACTER SET utf8 NOT NULL COMMENT 'CAP dell''acquirente',
  `bill_phone` varchar(255) CHARACTER SET utf8 NOT NULL COMMENT 'Telefono dell''acquirente',
  `bill_mobile` varchar(255) CHARACTER SET utf8 NOT NULL COMMENT 'Cellulare dell''acquirente',

  `shipping_first_name` varchar(255) CHARACTER SET utf8 NOT NULL COMMENT 'Nome del destinatario dell''ordine',
  `shipping_last_name` varchar(255) CHARACTER SET utf8 NOT NULL COMMENT 'Cognome del destinatario dell''ordine',
  `shipping_email` varchar(255) CHARACTER SET utf8 NOT NULL COMMENT 'Email del destinatario dell''ordine',
  `shipping_address` varchar(255) CHARACTER SET utf8 NOT NULL COMMENT 'Indirizzo del destinatario dell''ordine',
  `shipping_country` bigint(20) NOT NULL COMMENT 'Foreign Key verso la tabella wpss_shipping_countries che ha i paesi',
  `shipping_town` varchar(255) CHARACTER SET utf8 NOT NULL COMMENT 'Città del destinatario dell''ordine',
  `shipping_zipcode` varchar(255) CHARACTER SET utf8 NOT NULL COMMENT 'CAP del destinatario dell''ordine',
  `shipping_phone` varchar(255) CHARACTER SET utf8 NOT NULL COMMENT 'Telefono del destinatario dell''ordine',
  `shipping_mobile` varchar(255) CHARACTER SET utf8 NOT NULL COMMENT 'Cellulare del destinatario dell''ordine',

  `privacy_agree_a` enum('y','n') CHARACTER SET utf8 NOT NULL DEFAULT 'y' COMMENT 'Privacy A',
  `privacy_agree_b` enum('y','n') CHARACTER SET utf8 NOT NULL DEFAULT 'y' COMMENT 'Privacy B',
  `privacy_agree_c` enum('y','n') CHARACTER SET utf8 NOT NULL DEFAULT 'y' COMMENT 'Privacy C',

  `status` enum('pending','confirmed','cancelled','defunct') CHARACTER SET utf8 NOT NULL DEFAULT 'pending' COMMENT 'Indica lo stato di questo utente',
  `status_datetime` timestamp NOT NULL  ON UPDATE CURRENT_TIMESTAMP,

  PRIMARY KEY (`id`),
  KEY `status` (`status`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Tabella per la gestione delle iscrizioni degli utenti' AUTO_INCREMENT=1 ;