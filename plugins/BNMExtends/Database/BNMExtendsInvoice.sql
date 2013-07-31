-- --------------------------------------------------------
-- BNMExtendsInvoice Table SQL Dump
--
-- @author       =undo= <g.fazioli@saidmade.com>
-- @copyright    Copyright © 2008-2011 Saidmade Srl
-- @version      1.0
--
-- --------------------------------------------------------

--
-- Struttura della tabella `{wpprefix}_bnmextends_invoices`
--
CREATE TABLE IF NOT EXISTS `wpbn_bnmextends_invoices` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'PRIMARY KEY',
  `invoice_id_order` int(11) NOT NULL COMMENT 'Id Ordine relativo alla fattura',
  `invoice_vat_number` text NOT NULL COMMENT 'Partita Iva',
  `invoice_fiscal_code` text NOT NULL COMMENT 'Codice Fiscale (per uso futuro)',
  `invoice_company_name` text NOT NULL COMMENT 'Ragione Sociale',
  `invoice_note` text NOT NULL COMMENT 'Note',
  `invoice_check` tinyint(1) NOT NULL COMMENT 'Richiesta/Non richiesta (ad uso futuro)',

  PRIMARY KEY (`id`),
  KEY `invoice_id_order` (`invoice_id_order`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Tabella per la gestione delle fatture' AUTO_INCREMENT=1 ;
