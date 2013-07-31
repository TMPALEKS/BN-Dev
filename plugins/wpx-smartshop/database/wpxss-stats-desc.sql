-- --------------------------------------------------------
-- wpx SmartShop Statistics Table SQL Dump
--
-- Tabella per la gestione delle statistiche). Ogni riga, alla fine, tiene traccia di ogni prodotto venduto o, meglio,
-- di ogni prodotto legato ad un ordine, anche se quest'ultimo, per ragioni qualsiasi, non è andato a buon fine.
--
-- @author       =undo= <g.fazioli@wpxtre.me>
-- @copyright    Copyright © 2012 wpXtreme, Inc.
-- @version      1.0
--
-- @filename     wpxss-stats-desc.sql
--
-- --------------------------------------------------------

--
-- Struttura della tabella `{wpprefix}_wpss_stats`
--


CREATE TABLE `%s` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT 'ID Stats',
  `id_order` bigint(20) NOT NULL COMMENT 'ID dell''ordine',
  `id_product` bigint(20) NOT NULL COMMENT 'ID del prodotto',
  `id_variant` varchar(255) CHARACTER SET utf8 NOT NULL COMMENT 'ID della variante',
  `id_coupon` bigint(20) NOT NULL COMMENT 'ID del coupon usato come ulteriore sconto - o per l''acquisto - di questo prodotto',

  `weight` varchar(255) CHARACTER SET utf8 NOT NULL COMMENT 'Peso',
  `width` varchar(255) CHARACTER SET utf8 NOT NULL COMMENT 'Larghezza',
  `height` varchar(255) CHARACTER SET utf8 NOT NULL COMMENT 'Altezza',
  `depth` varchar(255) CHARACTER SET utf8 NOT NULL COMMENT 'Profondità',
  `volume` varchar(255) CHARACTER SET utf8 NOT NULL COMMENT 'Volume',
  `color` varchar(255) CHARACTER SET utf8 NOT NULL COMMENT 'Colore',
  `material` varchar(255) CHARACTER SET utf8 NOT NULL COMMENT 'Materiale',
  `model` varchar(255) CHARACTER SET utf8 NOT NULL COMMENT 'Modello',
  `value` varchar(255) CHARACTER SET utf8 NOT NULL COMMENT 'Prezzo aggiuntivo o percentuale se finisce con il sembolo percento',
  `note` text CHARACTER SET utf8 NOT NULL COMMENT 'Note',

  `product_title` varchar(255) CHARACTER SET utf8 NOT NULL COMMENT 'Nome (titolo) del prodotto',

  `product_amount` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT 'Prezzo base (escluse tasse o spedizione) di questo prodotto al momento dell''acquisto',
  `price_rule` varchar(255) CHARACTER SET utf8 NOT NULL COMMENT 'ID della regola del prezzo applicata a questo prodotto',
  `amount` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT 'Prezzo pagato potrebbe non corrispondere in caso di sconti e coupon',

  `status` enum('publish','trash') CHARACTER SET utf8 NOT NULL DEFAULT 'publish' COMMENT 'Indica lo stato di questo record',

  PRIMARY KEY (`id`),
  KEY `id_order` (`id_order`),
  KEY `id_product` (`id_product`),
  KEY `id_variant` (`id_variant`),
  KEY `id_coupon` (`id_coupon`),
  KEY `status` (`status`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Tabella per le statistiche' AUTO_INCREMENT=1 ;