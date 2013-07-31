-- --------------------------------------------------------
-- WP SmartShop Table SQL Dump
--
-- Tabella per la gestione delle memberships
--
-- @author       =undo= <g.fazioli@wpxtre.me>
-- @copyright    Copyright © 2008-2011 wpXtreme, Inc.
-- @version      1.0
--
-- --------------------------------------------------------

--
-- Struttura della tabella `{wpprefix}_wpss_memberships`
--

CREATE TABLE `%s` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT 'ID Memberships',

  `id_user` bigint(20) NOT NULL DEFAULT '0' COMMENT 'ID dell''utente di questa sottoscrizione',
  `id_user_maker` bigint(20) NOT NULL DEFAULT '0' COMMENT 'Se > 0 ID dell''utente che ha generato questa membership. Potrebbe essere un utente operatore o l''utente che ha comprato un prodotto e che a sua volta a generato questa Membership',
  `id_product_maker` bigint(20) NOT NULL DEFAULT '0' COMMENT 'Se > 0 ID del prodotto che ha generato questa Membership',

  `date_insert` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Indica la data di creazione della membership o aggiornamento',
  `date_start` datetime DEFAULT NULL COMMENT 'Se valorizzato (not null) indica la data dalla quale è possibile utilizzare la membership',
  `date_expired` datetime DEFAULT NULL COMMENT 'Se valorizzato (not null) indica la data di scadenza della membership',

  `role` varchar(64) CHARACTER SET utf8 NOT NULL DEFAULT '' COMMENT 'Ruolo',
  `role_previous` varchar(64) CHARACTER SET utf8 NOT NULL DEFAULT '' COMMENT 'Ruolo precendente',
  `caps` varchar(255) CHARACTER SET utf8 NOT NULL DEFAULT '' COMMENT 'Permessi (capabilities) - stringhe id separate da virgola con virgola finale',
  `status` enum('current','expired','available','trash') CHARACTER SET utf8 NOT NULL DEFAULT 'available' COMMENT 'Indica lo stato di questa memberships',

  PRIMARY KEY (`id`),
  KEY `id_user` (`id_user`),
  KEY `id_user_maker` (`id_user_maker`),
  KEY `id_product_maker` (`id_product_maker`),
  KEY `status` (`status`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Tabella per le Memberships' AUTO_INCREMENT=1 ;