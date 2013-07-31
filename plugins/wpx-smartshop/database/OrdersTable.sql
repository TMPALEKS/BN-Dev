-- --------------------------------------------------------
-- WP SmartShop Table SQL Dump
--
-- Tabella per la gestione degli ordini (acquisti). Le informazioni utente in questa tabella sono in pratica quattro:
--
-- id_user
--  Questo è l'id dell'utenza WordPress che effettua l'ordine, in pratica l'utente loggato
--
-- id_user_oder
--  Questo è l'id per il quale si effettua l'ordine. Normalmente id_user == id_user_order, cioè chi è loggato è anche
--  colui che compra. Tuttavia questo potrebbe non essere vero sempre, cioè quando l'utente x effettua un ordine a nome
--  (o per conto di) un utente y, come se fosse y ad acquistare.
--
-- track_id
--  Questo è un codice generalmente generato in proprio, tipo: 'cty-prova-001' che identifica un ordine.
--
-- transaction_id
--  Questo codice, simile al track_id, viene invece generato e restitioto dal sistema di gateway.
--
-- bill_{xyz}
--  queste sono copie, e sono le informazioni di id_user_order. Sono copie in quanto nel tempo i dati di un'utenza
--  possono cambiare (l'utente cambia email) ma gli ordini fanno parte degli storici quindi se voglio sapere all'epoca
--  che mail aveva l'utente è meglio duplicarla.
--
-- shipping_{xyz}
--  Queste sono le informazioni del destinatario. Anch'essere possono essere identica a bill_ (cioè io compro per me
--  stesso, quindi il destinatario sono io), oppure io compro per una destinazione diversa da quella indicata nel mio
--  account (ad esempio mi spedisco un ordine ad un indirizzo diverso: lavoro, baita, oppure sto effettuando un regalo)
--
-- status
--  Lo status indicato lo stato dell'ordine. Abbiamo 4 possibili valori per questo campo, 'pending' è il default
--
-- 'pending'
--  Stato di default. L'ordine è inserito. Il campo order_datetime sarà vitale per il passaggio di quest'orine allo
--  stato di 'defunct' se per un certo lasso di tempo l'ordine dovesse rimanere in 'pending'
--
-- 'confirmed'
--  Il pagamento è avvenuto con cuccesso
--
-- 'cancelled'
--  L'ordine è stato cancellato, annullato dall'amministrazione o dall'utente. Vedere campo 'note'
--
-- 'defunct'
--  Quando un ordine rimane troppo tempo nello stato di 'pending', viene aggiornato allo stato di morto, defunto, cioè
--  nessuno ha più recriminato quest'ordine.
--
-- @author       =undo= <g.fazioli@wpxtre.me>
-- @copyright    Copyright © 2008-2011 wpXtreme, Inc.
-- @version      1.0
--
-- --------------------------------------------------------

--
-- Struttura della tabella `{wpprefix}_wpss_orders`
--

CREATE TABLE `%s` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT 'ID Ordine',
  `order_datetime` datetime DEFAULT NULL COMMENT 'Data di creazione del record ordine',
  `id_user` bigint(20) NOT NULL COMMENT 'ID dell''utenza che ha generato l''ordine',
  `id_user_order` bigint(11) NOT NULL COMMENT 'ID dell''utenza collegata a quest''ordine',
  `id_carrier` bigint(11) NOT NULL COMMENT 'ID del corriere usato per spedire quest''ordine',

  `id_coupon` bigint(20) NOT NULL COMMENT 'ID del coupon usato come ulteriore sconto sull''intero ordine',

  `track_id` varchar(255) CHARACTER SET utf8 NOT NULL COMMENT 'ID della transazione',
  `transaction_id` varchar(255) CHARACTER SET utf8 NOT NULL COMMENT 'ID della transazione',
  `payment_type` varchar(255) CHARACTER SET utf8 NOT NULL COMMENT 'Indica il tipo di pagamento (PayPal, bancomat, Masterdard, etc...)',
  `payment_gateway` varchar(255) CHARACTER SET utf8 NOT NULL COMMENT 'Indica il tipo di Gateway utilizzato MPS, IWBank, etc...',
  `payment_result` text NOT NULL COMMENT 'Informazioni aggiuntive sul risultato della transazione',

  `subtotal` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT 'Subtotale di quest''ordine',
  `tax` decimal(8,2) NOT NULL DEFAULT '0.00' COMMENT 'Tasse',
  `shipping` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT 'Costo spedizione',
  `total` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT 'Totale da pagare (total due)',

  `bill_first_name` varchar(255) CHARACTER SET utf8 NOT NULL COMMENT 'Nome dell''acquirente',
  `bill_last_name` varchar(255) CHARACTER SET utf8 NOT NULL COMMENT 'Cognome dell''acquirente',
  `bill_address` varchar(255) CHARACTER SET utf8 NOT NULL COMMENT 'Indirizzo dell''acquirente',
  `bill_country` bigint(20) NOT NULL COMMENT 'Foreign Key verso la tabella wpss_shipping_countries che ha i paesi',
  `bill_town` varchar(255) CHARACTER SET utf8 NOT NULL COMMENT 'Città dell''acquirente',
  `bill_zipcode` varchar(255) CHARACTER SET utf8 NOT NULL COMMENT 'CAP dell''acquirente',
  `bill_email` varchar(255) CHARACTER SET utf8 NOT NULL COMMENT 'Email dell''acquirente',
  `bill_phone` varchar(255) CHARACTER SET utf8 NOT NULL COMMENT 'Telefono dell''acquirente',

  `shipping_first_name` varchar(255) CHARACTER SET utf8 NOT NULL COMMENT 'Nome del destinatario dell''ordine',
  `shipping_last_name` varchar(255) CHARACTER SET utf8 NOT NULL COMMENT 'Cognome del destinatario dell''ordine',
  `shipping_address` varchar(255) CHARACTER SET utf8 NOT NULL COMMENT 'Indirizzo del destinatario dell''ordine',
  `shipping_country` bigint(20) NOT NULL COMMENT 'Foreign Key verso la tabella wpss_shipping_countries che ha i paesi',
  `shipping_town` varchar(255) CHARACTER SET utf8 NOT NULL COMMENT 'Città del destinatario dell''ordine',
  `shipping_zipcode` varchar(255) CHARACTER SET utf8 NOT NULL COMMENT 'CAP del destinatario dell''ordine',
  `shipping_email` varchar(255) CHARACTER SET utf8 NOT NULL COMMENT 'Email del destinatario dell''ordine',
  `shipping_phone` varchar(255) CHARACTER SET utf8 NOT NULL COMMENT 'Telefono del destinatario dell''ordine',

  `note` text NOT NULL COMMENT 'Informazioni aggiuntive sull''ordine: di solito usate quando un ordine viene annullato',

  `status` enum('pending','confirmed','cancelled','defunct','trash') CHARACTER SET utf8 NOT NULL DEFAULT 'pending' COMMENT 'Indica lo stato di questo ordine',
  `status_datetime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Data di cambio di stato',

  PRIMARY KEY (`id`),
  KEY `id_user` (`id_user`),
  KEY `id_user_order` (`id_user_order`),
  KEY `id_coupon` (`id_coupon`),
  KEY `status` (`status`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Tabella per la gestione degli ordini (acquisti)' AUTO_INCREMENT=1;