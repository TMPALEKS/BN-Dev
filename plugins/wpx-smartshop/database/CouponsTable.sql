-- --------------------------------------------------------
-- WP SmartShop Table SQL Dump
--
-- Tabella per la gestione dei Coupons
--
-- @author       =undo= <g.fazioli@wpxtre.me>
-- @copyright    Copyright © 2008-2011 wpXtreme, Inc.
-- @version      1.0
--
-- --------------------------------------------------------

--
-- Struttura della tabella `{wpprefix}_wpss_coupons`
--

CREATE TABLE `%s` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT 'ID Coupon',

  `id_user_maker` bigint(20) NOT NULL DEFAULT '0' COMMENT 'Se > 0 ID dell''utente che ha generato questo Coupon. Potrebbe essere un utente operatore o l''utente che ha comprato un prodotto e che a sua volta a generato questo Coupon',
  `id_product_maker` bigint(20) NOT NULL DEFAULT '0' COMMENT 'Se > 0 ID del prodotto che ha generato questo Coupon',
  `date_insert` datetime DEFAULT NULL COMMENT 'Indica la data di creazione del coupon',

  `id_owner` bigint(20) NOT NULL DEFAULT '0' COMMENT 'ID dell''utente. Se > 0 indica l''id dell''utente che lo può utilizzare. Se -1 coupon illimitati',
  `id_product` bigint(20) NOT NULL DEFAULT '0' COMMENT 'ID del prodotto. Se > 0 indica che il coupon è valido solo per un determinato prodotto',
  `id_product_type` bigint(20) NOT NULL DEFAULT '0' COMMENT 'ID del tipo prodotto. Se > 0 indica che il coupon è valido solo per una determinata categoria (tipo) prodotto',

  `limit_product_qty` int(11) NOT NULL DEFAULT '0' COMMENT 'Limita il coupon ad un determinato numero di prodotti.',

  `uniqcode` varchar(64) CHARACTER SET utf8 NOT NULL COMMENT 'Codice alfa numerico che identifica il coupon',
  `value` varchar(16) CHARACTER SET utf8 NOT NULL COMMENT 'Valore di sconto. Questo può essere un valore in moneta, tipo 10 o percentuale 10%%',
  `cumulative` char(1) NOT NULL DEFAULT '0' COMMENT 'Indica se, all''interno di un ordine, un coupon può essere usato insieme ad altri',

  `date_from` datetime DEFAULT NULL COMMENT 'Se valorizzato (not null) indica la data dalla quale è possibile utilizzare il coupon',
  `date_to` datetime DEFAULT NULL COMMENT 'Se valorizzato (not null) indica la data fino alla quale è possibile utilizzare il coupon',

  `id_user` bigint(20) NOT NULL DEFAULT '0' COMMENT 'ID dell''utente. Se diverso da zero indica chi ha usato il coupon. In pratica si comporta da flag di disponibilità',
  `status` enum('pending','confirmed','cancelled','available','trash') CHARACTER SET utf8 NOT NULL DEFAULT 'available' COMMENT 'Indica lo stato di questo coupon',
  `status_datetime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Data di cambio di stato',

  PRIMARY KEY (`id`),
  KEY `id_owner` (`id_owner`),
  KEY `id_user_maker` (`id_user_maker`),
  KEY `id_product_maker` (`id_product_maker`),
  KEY `id_product` (`id_product`),
  KEY `id_product_type` (`id_product_type`),
  KEY `id_user` (`id_user`),
  KEY `status` (`status`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Tabella per i Coupon' AUTO_INCREMENT=1 ;