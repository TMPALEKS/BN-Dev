<?php
/**
 * @description        Gestisce l'invio delle mail
 *
 * @package            BNMExtends
 * @subpackage         BNMExtendsMail
 * @author             =undo= <g.fazioli@undolog.com>, <g.fazioli@saidmade.com>
 * @copyright          Copyright (c) 2012 Saidmade Srl.
 * @link               http://www.saidmade.com
 * @created            29/05/12
 * @version            1.0.0
 *
 * @filename           bnmextends-mail
 *
 */

class BNMExtendsMail {

    /**
     * @var object Memorizza l'ordine attuale quando parte wpss_order_confirmed()
     */
    private static $order;

    /**
     * Init
     */
    function __construct() {
    }

    // -----------------------------------------------------------------------------------------------------------------
    // Static values
    // -----------------------------------------------------------------------------------------------------------------
    public static function headers() {
        $headers = array(
            BNMEXTENDS_EMAIL_FROM . WPDK_CRLF,
            'Bcc: ' . BNMEXTENDS_BCC_EMAIL_ADDRESSS . WPDK_CRLF,
            'Content-Type: text/html' . WPDK_CRLF
        );
        return $headers;
    }


    // -----------------------------------------------------------------------------------------------------------------
    // Core
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Regista (catch) tutti i filtri inviati sostanzialmente da Smart SHop e/o dal sistema
     *
     * @static
     *
     */
    public static function registerHooks() {
        /* Conferma ordine. */
        add_action( 'wpss_order_confirmed', array( __CLASS__, 'wpss_order_confirmed') );

        /* Creazione di coupon tramite prodotto */
        add_action( 'wpxss_product_coupons_with_order', array( __CLASS__, 'wpxss_product_coupons_with_order'), 10, 2 );

        /* Aggiunta di una membership/sottoscrizione; l'aggiunta potrebbe impllicare anche l'attivazione immediata. */
        add_action( 'wpxss_product_membership_added', array( __CLASS__, 'wpxss_product_membership_added' ), 10, 3 );

        /* Scadenza Membership */
        add_action( 'wpss_membership_expired', array( __CLASS__, 'wpss_membership_expired'), 10, 3 );
    }

    private static function mail( $to, $subject, $message, $attachments = array() ) {
        $headers = self::headers();

        if ( isset( self::$order ) ) {
            if ( self::$order->bill_email != self::$order->shipping_email ) {
                $shipping_to = sprintf( '%s %s <%s>', self::$order->shipping_first_name, self::$order->shipping_last_name, self::$order->shipping_email );
                wp_mail( $shipping_to, $subject, $message, $headers, $attachments );
            }
        }

        return( wp_mail( $to, $subject, $message, $headers, $attachments ) );
    }

    public static function template( $slug ) {
        $content = wpdk_content_page_with_slug( $slug, kBNMExtendsSystemPagePostTypeKey );
        if( $content ) {
            $result = apply_filters( "the_content", $content );
        } else {
            $result = 'Pagina non trovata: ' . $slug;
        }
        return $result;
    }

    public static function templateTitle( $slug ) {
        $page = get_page_by_path( $slug, OBJECT, kBNMExtendsSystemPagePostTypeKey );
        if( $page ) {
            $result = $page->post_title;
        } else {
            global $wpdb;

            /* WPML? */
            if ( function_exists( 'icl_object_id' ) ) {
                $post_type = kBNMExtendsSystemPagePostTypeKey;
                $sql = <<< SQL
SELECT ID FROM {$wpdb->posts}
WHERE post_name = '{$slug}'
AND post_type = '{$post_type}'
AND post_status = 'publish'
SQL;
                $id  = $wpdb->get_var( $sql );
                $id  = icl_object_id( $id, $post_type, true );
            } else {
                $result = 'Pagina non trovata: ' . $slug;
            }
            $page = get_post( $id );
            $result = $page->post_title;
        }

        /* Commentato in quanto non elaborava correttamente alcuni carreteri
           $result = apply_filters('the_title', $result );
        */

        return $result;
    }

    // -----------------------------------------------------------------------------------------------------------------
    // Smart Shop actions
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Questa action è scatenata dalla funzione di flush legate alle membership, quella che controlla quali
     * sottoscrizioni attivare e/o disattivare.
     *
     * @static
     *
     * @param int    $id_user ID dell'utente
     * @param object $row     Riga dal database
     * @param int    $days    Scaduta da $day giorni
     */
    public static function wpss_membership_expired( $id_user, $row, $days_from_expired ) {

        /* Ottengo informazioni sull'utente. */
        $user = new WP_User( $id_user );

        /* Ruoli utente: WP tiene misteriosamente (e inutilmente) un array */
        $user_roles = $user->roles;

        /* Ruolo attuale; recupero il primo: meglio agire per chiave che per indice, potrebbe dare errore */
        $role_key = $user_roles[key( $user_roles )];

        /* Ruolo precedente, mi serve per dire all'utente cosa sta per ridiventare */
        $previous_role_key = '';
        if ( !empty( $row->role_previous ) ) {
            $previous_role_key = $row->role_previous;
        }

        /* Trasformo i ruoli di sopra da codice a stringa */
        $wp_roles = new WP_Roles();

        /* Nome/Descrizione ruolo attuale */
        $role_name = __( $wp_roles->roles[$role_key]['name'], 'bnmextends' );

        /* Nome/Descrizione ruolo precedente */
        $previous_role_name = '';
        if ( !empty( $previous_role_key ) ) {
            $previous_role_name = __( $wp_roles->roles[$previous_role_key]['name'], 'bnmextends' );
        }

        /* Confezione mail */
        $to           = $user->data->user_email;
        $display_name = $user->data->display_name;

        /* In base al tipo di ruolo scaduto scelgo un template diverso di mail */
        $mail_slug = 'scadenza-abilitazione';
        if ( $role_key == 'bnm_role_5' || $role_key == 'bnm_role_6' ) {
            $mail_slug = 'scadenza-membership';
        }

        $subject = self::templateTitle( $mail_slug );
        $body    = self::template( $mail_slug );

        $filters = array(
            '[$NOME_E_COGNOME$]'        => $display_name,
            '[$MEMBERSHIP_SCADUTA$]'    => $role_name,
            '[$MEMBERSHIP_PRECEDENTE$]' => $previous_role_name,
            '[$GIORNI$]'                => $days_from_expired
        );

        $body = strtr( $body, $filters );

        return( self::mail( $to, $subject, $body ) );
    }

    /// Attivazione di una membership
    public static function wpxss_product_membership_added( $membership, $order, $product ) {
        switch( $membership['role'] ) {
            // Club Member
            case 'bnm_role_5':
                self::club_membership( $order );
                break;
            // Club Platinum
            case 'bnm_role_6':
                self::club_platinum( $order );
                break;
        }
    }

    /// Invia mail quando viene generato un coupon
    public static function wpxss_product_coupons_with_order( $order, $product_coupons ) {

        $terms_by_name = WPSmartShopProductTypeTaxonomy::arrayTermsWithKeyName();
        $to            = $order->bill_email;
        $name          = $order->bill_first_name;
        $lastname      = $order->bill_last_name;

        if ( !isset( $terms_by_name['abbonamento under 26'] ) ) {
            $translate = array(
                'abbonamento under 26' => 'young subscription',
                'abbonamento rosa'     => 'pink subscription',
                'abbonamento satchmo'  => 'satchmo subscription',
                'abbonamento verde'    => 'green subscription',
                'dinner voucher'       => 'dinner voucher',
                'show voucher'         => 'show voucher',
                'platinum'             => 'platinum',

                'club regalo'      => 'gift club',
                'platinum regalo'  => 'gift platinum',
                'satchmo regalo'   => 'gift satchmo',
                'verde regalo'     => 'gift green',
                'rosa regalo'      => 'gift pink',
                '2 dinner voucher' => '2 dinner voucher',
                '5 dinner voucher' => '5 dinner voucher',
                '2 show voucher'   => '2 show voucher',
                '5 show voucher'   => '5 show voucher',
            );
        } else {
            $translate = array_combine(array_keys( $terms_by_name ), array_keys( $terms_by_name ) );
        }

        foreach ( $product_coupons as $product_coupon ) {
            $page_slug  = '';
            $id_product = $product_coupon['id_product'];

            if ( has_term( $terms_by_name[$translate['abbonamento under 26']], kWPSmartShopProductTypeTaxonomyKey, $id_product ) ) {
                $page_slug = 'codice-coupon-abbonamento-under-26';
            } elseif ( has_term( $terms_by_name[$translate['abbonamento rosa']], kWPSmartShopProductTypeTaxonomyKey, $id_product ) ) {
                $page_slug = 'codice-coupon-abbonamento-rosa';
            } elseif ( has_term( $terms_by_name[$translate['abbonamento satchmo']], kWPSmartShopProductTypeTaxonomyKey, $id_product ) ) {
                $page_slug = 'codice-coupon-abbonamento-satchmo';
            } elseif ( has_term( $terms_by_name[$translate['abbonamento verde']], kWPSmartShopProductTypeTaxonomyKey, $id_product ) ) {
                $page_slug = 'codice-coupon-abbonamento-verde';
            } elseif ( has_term( $terms_by_name[$translate['dinner voucher']], kWPSmartShopProductTypeTaxonomyKey, $id_product ) ) {
                $page_slug = 'codice-coupon-dinner-voucher';
            } elseif ( has_term( $terms_by_name[$translate['show voucher']], kWPSmartShopProductTypeTaxonomyKey, $id_product ) ) {
                $page_slug = 'codice-coupon-show-voucher';
            } elseif ( has_term( $terms_by_name[$translate['platinum']], kWPSmartShopProductTypeTaxonomyKey, $id_product ) ) {
                $page_slug = 'codice-coupon-platinum-membership';
            } elseif ( has_term( $terms_by_name[$translate['club regalo']], kWPSmartShopProductTypeTaxonomyKey, $id_product ) ) {
                $page_slug = 'codice-coupon-club-membership-prepagato';
            } elseif ( has_term( $terms_by_name[$translate['platinum regalo']], kWPSmartShopProductTypeTaxonomyKey, $id_product ) ) {
                $page_slug = 'codice-coupon-platinum-membership-prepagato';
            } elseif ( has_term( $terms_by_name[$translate['satchmo regalo']], kWPSmartShopProductTypeTaxonomyKey, $id_product ) ) {
                $page_slug = 'codice-coupon-abbonamento-satchmo-prepagato';
            } elseif ( has_term( $terms_by_name[$translate['verde regalo']], kWPSmartShopProductTypeTaxonomyKey, $id_product ) ) {
                $page_slug = 'codice-coupon-abbonamento-verde-prepagato';
            } elseif ( has_term( $terms_by_name[$translate['rosa regalo']], kWPSmartShopProductTypeTaxonomyKey, $id_product ) ) {
                $page_slug = 'codice-coupon-abbonamento-rosa-prepagato';
            } elseif ( has_term( $terms_by_name[$translate['2 dinner voucher']], kWPSmartShopProductTypeTaxonomyKey, $id_product ) ) {
                $page_slug = 'codice-coupon-2-dinner-voucher';
            } elseif ( has_term( $terms_by_name[$translate['5 dinner voucher']], kWPSmartShopProductTypeTaxonomyKey, $id_product ) ) {
                $page_slug = 'codice-coupon-5-dinner-voucher';
            } elseif ( has_term( $terms_by_name[$translate['2 show voucher']], kWPSmartShopProductTypeTaxonomyKey, $id_product ) ) {
                $page_slug = 'codice-coupon-2-show-voucher';
            } elseif ( has_term( $terms_by_name[$translate['5 show voucher']], kWPSmartShopProductTypeTaxonomyKey, $id_product ) ) {
                $page_slug = 'codice-coupon-5-show-voucher';
            }

            $subject        = self::templateTitle( $page_slug );
            $coupons        = WPXSmartShopCoupons::coupons( $product_coupon['coupons'] );
            $coupon         = $coupons[0];
            $coupons_string = '<ul>';
            //foreach ( $coupons as $coupon ) {
            $coupons_string .= sprintf( '<li>%s</li>', $coupon->uniqcode );
            //}
            $coupons_string .= '</ul>';

            $str_replaces = array(
                '[$NOME$]'              => $name,
                '[$COGNOME$]'           => $lastname,
                '[$ABBONAMENTOCOUPON$]' => $coupons_string
            );

            $body = self::template( $page_slug );
            $body = strtr( $body, $str_replaces );

            self::mail( $to, $subject, $body );
        }
    }

    /// Ordine confermato
    public static function wpss_order_confirmed( $track_id ) {
        global $wpdb;

        $terms_by_name = WPSmartShopProductTypeTaxonomy::arrayTermsWithKeyName();

        $order       = WPXSmartShopOrders::order( $track_id );
        self::$order = $order;
        $id_order    = $order->id;

        /* Prima di tutto invio una mail per conferma ordine con relativa invoice. */
        self::invoice( $order );

        /* Poi salvo i dati della fattura*/
        /* Se ho impostato i valori */
        $invoiceValues =  $_SESSION['invoiceValues'];
        if ( self::checkInvoiceValues( $invoiceValues ) ){
            $results = self::storeInvoiceInfo( $id_order, $invoiceValues );
            unset ($_SESSION['invoiceValues']);
        }

        /* Invio mail sui prodotti acquistati, eseguendo una serie di raggruppamenti */

        /* @todo Determinare Dinner e dire a Plceholder di confermare tavolo */

        $table_stats = WPXSmartShopStats::tableName();

        /* Questa select restituisce una cosa del genere
         *
         * number_ticket  model 	                 note 	                            post_title
         * -------------------------------------------------------------------------------------------------------------
         *       3 	      Senza Prenotazione Cena 		                                03/07/2012 21:00 - Patty Pravo
         *       4 	      Con Prenotazione Cena 	 Piano Terra-26BIS,Piano Terra-27T 	03/07/2012 21:00 - Patty Pravo
         *
         */

        $sql = <<< SQL
SELECT COUNT(id_product) AS number_ticket, model, note, products.post_title, stats.id_variant
FROM `{$table_stats}` AS stats
LEFT JOIN `{$wpdb->posts}` AS products ON products.ID = stats.id_product
WHERE id_order = {$id_order}
AND id_variant = 'Dinner'
AND status = 'publish'
GROUP BY stats.id_product, stats.id_variant, stats.model, products.post_title, stats.note
SQL;

        $products = $wpdb->get_results( $sql );
        if ( !empty( $products ) ) {
            self::tableReservation( $order, $products );
        }

        /*
         * SELECT count(stats.id_product) AS qty, terms.name, stats.id_product, stats.id_variant, stats.model, stats.product_title
         * FROM wpbn_terms AS terms
         * LEFT JOIN wpbn_term_taxonomy AS tt ON tt.term_id = terms.term_id
         * LEFT JOIN wpbn_term_relationships AS tr ON tr.term_taxonomy_id = tt.term_taxonomy_id
         * RIGHT JOIN wpbn_wpss_stats AS stats ON stats.id_product =  tr.object_id
         * WHERE terms.term_id IN ( 63,64,53 )
         * GROUP BY stats.id_product
         */

        /* Select per biglietti tipo brunch */
        $terms    = WPSmartShopProductTypeTaxonomy::arrayTermsWithKeyName();

        if ( !isset( $terms['brunch cumulativo'] ) ) {
            $terms_id = sprintf( '%s, %s, %s',
                icl_object_id( $terms['brunch cumulative ticket - family'], kWPSmartShopProductTypeTaxonomyKey, false, 'it'),
                icl_object_id( $terms['brunch single ticket - adult'], kWPSmartShopProductTypeTaxonomyKey, false, 'it'),
                icl_object_id( $terms['brunch single ticket - child 12y'], kWPSmartShopProductTypeTaxonomyKey, false, 'it')
            );
        } else {
            $terms_id = sprintf( '%s, %s, %s',
                $terms['brunch cumulativo'],
                $terms['brunch adulto singolo'],
                $terms['brunch bambino singolo 12a'] );
        }

        $sql = <<< SQL
SELECT COUNT( stats.id_product ) AS qty,
       terms.name,
       stats.id_product,
       stats.id_variant,
       stats.model,
       stats.product_title
FROM {$wpdb->terms} AS terms
LEFT JOIN {$wpdb->term_taxonomy} AS tt ON tt.term_id = terms.term_id
LEFT JOIN {$wpdb->term_relationships} AS tr ON tr.term_taxonomy_id = tt.term_taxonomy_id
RIGHT JOIN {$table_stats} AS stats ON stats.id_product = tr.object_id
WHERE terms.term_id IN ( {$terms_id} )
AND stats.id_order = {$id_order}
GROUP BY stats.id_product, stats.model
SQL;

        $products = $wpdb->get_results( $sql );
        if ( !empty( $products ) ) {
            self::brunchReservation( $order, $products );
        }

        /* Specifico per Club Member e Altri prodotti ( Capodanno,... ) */
        $products = WPXSmartShopStats::productsWithOrderID( $id_order );

        foreach ( $products as $product ) {
            $product_ids[] = $product['id_product'];
        }

        if ( !empty( $product_ids ) ) {
            WPDKWatchDog::watchDog(__CLASS__, "Prodotti: " . WPDKWatchDog::get_var_dump($product_ids));

            /* Verifico che ci sia almeno un prodotto che rientra nella tipologia prodotto Biglietti Ingresso */
            $newyear            = $terms_by_name['biglietti'];
            WPDKWatchDog::watchDog(__CLASS__,"Tipo di ticket: " . $newyear);


            $id_term = $newyear;

            $term_taxonomy      = $wpdb->term_taxonomy;
            $term_relationships = $wpdb->term_relationships;
            $ids                = join( ', ', $product_ids );

            $sql    = <<< SQL
SELECT * FROM {$term_taxonomy} AS tt
LEFT JOIN  `{$term_relationships}` AS tr ON  tr.`term_taxonomy_id` = tt.term_taxonomy_id
WHERE tt.term_id = {$id_term}
AND tr.object_id IN ( {$ids})
SQL;


            $vals = $wpdb->get_row($sql);


            //$count = $wpdb->get_var( $sql );
            $count = count($vals);

            WPDKWatchDog::watchDog(__CLASS__,"Totali: " . $count);


            /**
             * Ticket di tipo Biglietto Ingresso
             */
            if ( ($count > 0) ) {
                #self::ticketNewYear($order,$products);
                self::ticketCustom($order,$products);
            }

            /*
             * Coupon Membership
             */

            /* Verifico che ci sia almeno un prodotto che rientra nella tipologia prodotto 'Club Member' */
            $club            = $terms_by_name['club'];
            $id_term = $club;

            $term_taxonomy      = $wpdb->term_taxonomy;
            $term_relationships = $wpdb->term_relationships;
            $ids                = join( ', ', $product_ids );

            $sql    = <<< SQL
SELECT * FROM {$term_taxonomy} AS tt
LEFT JOIN  `{$term_relationships}` AS tr ON  tr.`term_taxonomy_id` = tt.term_taxonomy_id
WHERE tt.term_id = {$id_term}
AND tr.object_id IN ( {$ids})
SQL;


            $vals = $wpdb->get_row($sql);

            //$count = $wpdb->get_var( $sql );
            $count = count($vals);


            if ( ($count > 0) ) {

                $cm = new WPXSmartShopCouponMaker();

                WPDKWatchDog::watchDog(__CLASS__, "Creazione dei coupon Members");



                /* 3 Coupon gratis */
                $cm->value( '100%' );
                $cm->id_user_maker( $order->id_user_order );
                $cm->restrict_user( 'y' );
                $cm->same_uniqcode( 1 );
                $cm->id_product_maker($vals->object_id);
                $cm->qty( 3 );
                $cm->id_product_type( $terms_by_name['ingresso club membership'] );
                $cm->limit_product_qty( 1 );
                $ids                      = $cm->create();
                $coupon                   = WPXSmartShopCoupons::coupon( $ids[0] );
                $ingresso_club_membership = $coupon->uniqcode;

                /* 2 Ingressi per */
                $cm->qty( 2 );
                $cm->id_product_type( $terms_by_name['eventi esclusivi'] );
                $cm->limit_product_qty( 1 );
                $ids              = $cm->create();
                $coupon           = WPXSmartShopCoupons::coupon( $ids[0] );
                $eventi_esclusivi = $coupon->uniqcode;

                /* 1 Ingressi per */
                $cm->qty( 1 );
                $cm->id_product_type( $terms_by_name['brunch adulto singolo'] );
                $cm->limit_product_qty( 1 );
                $ids                   = $cm->create();
                $coupon                = WPXSmartShopCoupons::coupon( $ids[0] );
                $brunch_adulto_singolo = $coupon->uniqcode;

                /* Invio mail per Club membership */
                /* Vedi action wpxss_product_membership_added */
                // self::club_membership( $order );

                /* Invio mail con lista coupon */
                self::club_membership_coupons( $order, $ingresso_club_membership, $eventi_esclusivi, $brunch_adulto_singolo );
            }
        }
    }

    // -----------------------------------------------------------------------------------------------------------------
    // Mail
    // -----------------------------------------------------------------------------------------------------------------

    public static function club_platinum( $order ) {
        $to      = $order->bill_email;
        $subject = self::templateTitle( 'conferma-platinum-membership' );

        $name     = $order->bill_first_name;
        $lastname = $order->bill_last_name;

        $body = str_replace( '[$NOME$]', $name, self::template( 'conferma-platinum-membership') );
        $body = str_replace( '[$COGNOME$]', $lastname, $body );

        return( self::mail( $to, $subject, $body ) );
    }

    public static function club_membership( $order ) {
        $to      = $order->bill_email;
        $subject = self::templateTitle( 'conferma-club-membership' );

        $name     = $order->bill_first_name;
        $lastname = $order->bill_last_name;

        $body = str_replace( '[$NOME$]', $name, self::template( 'conferma-club-membership') );
        $body = str_replace( '[$COGNOME$]', $lastname, $body );

        return( self::mail( $to, $subject, $body ) );
    }

    public static function club_membership_coupons( $order, $ingresso_club_membership, $eventi_esclusivi, $brunch_adulto_singolo ) {
        $to      = $order->bill_email;
        $subject = self::templateTitle( 'codici-coupon-club-membership' );

        $name     = $order->bill_first_name;
        $lastname = $order->bill_last_name;

        $body = str_replace( '[$NOME$]', $name, self::template( 'codici-coupon-club-membership' ) );
        $body = str_replace( '[$COGNOME$]', $lastname, $body );
        $body = str_replace( '[$INGRESSOCONCERTOCLUB$]', $ingresso_club_membership, $body );
        $body = str_replace( '[$INGRESSOESCLUSIVOCLUB$]', $eventi_esclusivi, $body );
        $body = str_replace( '[$INGRESSOBRUNCHCLUB$]', $brunch_adulto_singolo, $body );

        return ( self::mail( $to, $subject, $body ) );
    }

    public static function invoice( $order ) {
        $to      = sprintf( '%s %s <%s>', $order->bill_first_name, $order->bill_last_name, $order->bill_email);
        $subject = self::templateTitle( 'conferma-d-ordine' );
        WPXSmartShopInvoice::$output_email = true;
        $invoice = WPXSmartShopInvoice::wrapForPrinting( WPXSmartShopInvoice::invoice( $order->id ), true );

        /* Recupero template. */
        $body = str_replace( '[$ORDINE$]', $invoice, self::template( 'conferma-d-ordine' ) );

        return( self::mail( $to, $subject, $body ) );
    }

    /**
     * Wrapper per la preparazione e la memorizzazione sul DB del Invoice
     * @param $order_id
     * @param $values
     *
     */
    public static function storeInvoiceInfo( $order_id, $values ){

        $values['invoice_id_order'] = $order_id;

        WPDKWatchDog::watchDog(__METHOD__,"Aggiungo Invoice per ordine " . $order_id );

        //salvo nel DB
        return BNMExtendsInvoices::createInvoice( $values );
    }


    public static function ticketCustom( $order, $products ) {

        WPDKWatchDog::watchDog(__METHOD__,"Prodotti: " . WPDKWatchDog::get_var_dump($products) );




        $subject       = self::templateTitle( 'conferma-ticket-musicastelle-in-blue' );
        $to            = $order->bill_email;
        $original_body = self::template( 'conferma-ticket-musicastelle-in-blue' );

        WPDKWatchDog::watchDog(__METHOD__,"Subject: " . $subject );
        WPDKWatchDog::watchDog(__METHOD__,"Body: " . $original_body );


        /* Invio mail raggruppate per titolo */
        $mails = array();
        foreach( $products as $product ) {

            $title_md5 = md5( $product->post_title );
            $mails[$title_md5][] = $product;
        }

        WPDKWatchDog::watchDog(__METHOD__,"mails: " . WPDKWatchDog::get_var_dump($mails) );

        foreach ( $mails as $mail ) {
            $buffer = '';
            foreach ( $mail as $ticket ) {
                $buffer .= sprintf(
                    '<tr><td style="border-bottom: 1px solid #aaa;">%s</td>' . WPDK_CRLF .
                        '<td style="border-bottom: 1px solid #aaa">%s</td>' . WPDK_CRLF .
                        '<td style="border-bottom: 1px solid #aaa">%s</td>' . WPDK_CRLF .
                        '<td style="border-bottom: 1px solid #aaa;font-weigth:bold;">%s</td></tr>' .
                        WPDK_CRLF, $ticket['qty'], $ticket['product_title'], '', '' );
            }
        }

        WPDKWatchDog::watchDog(__METHOD__,"Buffer: " . $buffer );


        $quantity = __('Posti','bnmextends');
        $name = __('Show','bnmextends');

        $html = <<< HTML
<table width="100%" cellpadding="4" cellspacing="0">
<thead>
<tr>
<td style="background-color: #555;color:#fff">{$quantity}</td>
<td style="background-color: #555;color:#fff">{$name}</td>
<td style="background-color: #555;color:#fff">&nbsp;</td>
<td style="background-color: #555;color:#fff">&nbsp;</td>
</tr>
</thead>
<tbody>
{$buffer}
</tbody>
</table>
HTML;

        /* Filters placeholder */
        $filters = array(
            '[$ORDERID$]' => $order->id,
            '[$TICKETS$]' => $html,
        );

        $body = strtr( $original_body, $filters );

        return(self::mail( $to, $subject, $body ));

    }

    public static function ticketNewYear( $order, $products ) {

        $subject       = self::templateTitle( 'conferma-blue-note-ticket-capodanno' );
        $to            = $order->bill_email;
        $original_body = self::template( 'conferma-blue-note-ticket-capodanno' );

        /* Invio mail raggruppate per titolo */
        $mails = array();
        foreach( $products as $product ) {
            $title_md5 = md5( $product->post_title );
            $mails[$title_md5][] = $product;
        }

        foreach ( $mails as $mail ) {
            $buffer = '';
            foreach ( $mail as $ticket ) {
                $buffer .= sprintf(
                    '<tr><td style="border-bottom: 1px solid #aaa;">%s</td>' . WPDK_CRLF .
                        '<td style="border-bottom: 1px solid #aaa">%s</td>' . WPDK_CRLF .
                        '<td style="border-bottom: 1px solid #aaa">%s</td>' . WPDK_CRLF .
                        '<td style="border-bottom: 1px solid #aaa;font-weigth:bold;">%s</td></tr>' .
                        WPDK_CRLF, $ticket['qty'], $ticket['product_title'], '', '' );
            }
        }

        $quantity = __('Posti','bnmextends');
        $name = __('Show','bnmextends');

        $html = <<< HTML
<table width="100%" cellpadding="4" cellspacing="0">
<thead>
<tr>
<td style="background-color: #555;color:#fff">{$quantity}</td>
<td style="background-color: #555;color:#fff">{$name}</td>
<td style="background-color: #555;color:#fff">&nbsp;</td>
<td style="background-color: #555;color:#fff">&nbsp;</td>
</tr>
</thead>
<tbody>
{$buffer}
</tbody>
</table>
HTML;

        /* Filters placeholder */
        $filters = array(
            '[$ORDERID$]' => $order->id,
            '[$TICKETS$]' => $html,
        );

        $body = strtr( $original_body, $filters );

        return(self::mail( $to, $subject, $body ));

    }

    public static function requestUnder26( $attachments ) {
        $to = BNMEXTENDS_SERVICES_EMAIL;
        $subject = __( 'Under 26 request', 'bnmextends' );

        $user = WPDKUser::user();

        $name     = $user->user_meta['first_name'][0];
        $lastname = $user->user_meta['last_name'][0];
        $email    = $user->user_email;

        $body = str_replace( '[$NOME$]', $name, self::template( 'richiesta-di-abilitazione') );
        $body = str_replace( '[$COGNOME$]', $lastname, $body );
        $body = str_replace( '[$EMAIL$]', $email, $body );

        if( self::mail( $to, $subject, $body, $attachments ) ) {
            self::responseRequest( $user, $subject );
        }
    }

    public static function requestOver65( $attachments ) {
        $to = BNMEXTENDS_SERVICES_EMAIL;
        $subject = __( 'Over 65 request', 'bnmextends' );

        $user = WPDKUser::user();

        $name     = $user->user_meta['first_name'][0];
        $lastname = $user->user_meta['last_name'][0];
        $email    = $user->user_email;

        $body = str_replace( '[$NOME$]', $name, self::template( 'richiesta-di-abilitazione') );
        $body = str_replace( '[$COGNOME$]', $lastname, $body );
        $body = str_replace( '[$EMAIL$]', $email, $body );

        if( self::mail( $to, $subject, $body, $attachments ) ) {
            self::responseRequest( $user, $subject );
        }
    }

    public static function requestAssociations( $association, $attachments ) {
        $to = BNMEXTENDS_SERVICES_EMAIL;
        $subject = sprintf( '%s %s', $association, __( 'request', 'bnmextends' ));

        $user = WPDKUser::user();

        $name     = $user->user_meta['first_name'][0];
        $lastname = $user->user_meta['last_name'][0];
        $email    = $user->user_email;

        $body = str_replace( '[$NOME$]', $name, self::template( 'richiesta-di-abilitazione') );
        $body = str_replace( '[$COGNOME$]', $lastname, $body );
        $body = str_replace( '[$EMAIL$]', $email, $body );

        if( self::mail( $to, $subject, $body, $attachments ) ) {
            self::responseRequest( $user, $association );
        }
    }

    public static function responseRequest( $user, $request ) {
        $subject = self::templateTitle( 'richiesta-di-aggiornamento' );
        $to      = $user->user_email;

        $body = self::template( 'richiesta-di-aggiornamento' );

        return( self::mail( $to, $subject, $body ) );
    }

    public static function thanksForContacts( $email_to ) {
        $subject = self::templateTitle( 'grazie-per-averci-contattato' );
        $to      = $email_to;

        $body = self::template( 'grazie-per-averci-contattato' );

        return( self::mail( $to, $subject, $body ) );
    }

    public static function tableReservation( $order, $products ) {

        foreach( $products as $product ) {
            /* @todo In caso aggiungere in OR BNMEXTENDS_WITH_DINNER_RESERVATION_KEY non tradotta, ma non dovrebbe servire */
            if( $product->id_variant == 'Dinner' && $product->model == BNMEXTENDS_WITH_DINNER_RESERVATION_KEY ) {
                if( !empty( $product->note ) ) {
                    /* Piano Terra-26BIS,Piano Terra-27T */
                    $tables = explode(',', $product->note );
                    $places = array();
                    foreach( $tables as $table ) {
                        /* Piano Terra-26BIS */
                        $split = explode('-', $table );
                        /* La chiave è il posto, il valore l'orario 03/07/2012 21:00 - Patty Pravo */
                        $places[$split[1]] = substr( $product->post_title, 0, 16 ) ;
                    }
                }
            }
        }

        /* In places ho per chiave la lista dei place da prenotare */
        if( !empty( $places ) ) {
            foreach( $places as $place => $time ) {
                $mktime = WPDKDateTime::makeTimeFrom( 'd/m/Y H:i', $time );
                $date_start = $mktime;
                /* Incremento di un'ora */
                $date_expiry = $mktime + 60*60;
                WPPlaceholdersReservations::doReservation( $place, $date_start, $date_expiry, $order->id_user_order );
            }
        }

        /* Invio mail raggruppate per titolo */
        $mails = array();
        foreach( $products as $product ) {
            $title_md5 = md5( $product->post_title );
            $mails[$title_md5][] = $product;
        }

        $subject       = self::templateTitle( 'conferma-blue-note-ticket' );
        $to            = $order->bill_email;
        $original_body = self::template( 'conferma-blue-note-ticket' );

        /* Label for html inject */
        $labels              = new stdClass();
        $labels->qty         = __( 'Seats', 'bnmextends' );
        $labels->name        = __( 'Show', 'bnmextends' );
        $labels->dinner      = __( 'Dinner', 'bnmextends' );
        $labels->reservation = __( 'Reservation', 'bnmextends' );

        foreach ( $mails as $mail ) {
            $buffer = '';
            foreach ( $mail as $ticket ) {
                $buffer .= sprintf(
                    '<tr><td style="border-bottom: 1px solid #aaa;text-align: right">%s</td>' . WPDK_CRLF .
                        '<td style="border-bottom: 1px solid #aaa">%s</td>' . WPDK_CRLF .
                        '<td style="border-bottom: 1px solid #aaa">%s</td>' . WPDK_CRLF .
                        '<td style="border-bottom: 1px solid #aaa;font-weigth:bold;">%s</td></tr>' .
                        WPDK_CRLF, $ticket->number_ticket, $ticket->post_title, __( $ticket->model, 'bnmextends' ), $ticket->note );
                if ( !empty( $ticket->note ) ) {
                    self::reservationSeat( $order, $ticket->note, $ticket->post_title );
                }
            }

            $html = <<< HTML
<table width="100%" cellpadding="8" cellspacing="0">
<thead>
<tr>
<td style="background-color: #555;color:#fff;text-align: right">{$labels->qty}</td>
<td style="background-color: #555;color:#fff">{$labels->name}</td>
<td style="background-color: #555;color:#fff">{$labels->dinner}</td>
<td style="background-color: #555;color:#fff">{$labels->reservation}</td>
</tr>
</thead>
<tbody>
{$buffer}
</tbody>
</table>
HTML;
            /* Filters placeholder */
            $filters = array(
                '[$ORDERID$]' => $order->id,
                '[$TICKETS$]' => $html,
            );

            $body = strtr( $original_body, $filters );

            self::mail( $to, $subject, $body );
        }
    }

    public static function brunchReservation( $order, $products ) {
        $buffer = '';
        foreach ( $products as $product ) {
            $buffer .= sprintf(
                '<tr><td style="border-bottom: 1px solid #aaa;text-align: right">%s</td><td style="border-bottom: 1px solid #aaa">%s - %s</td></tr>' .
                    WPDK_CRLF, $product->qty, $product->product_title, __( $product->model, 'bnmextends' ) );
        }

        $labels       = new stdClass();
        $labels->qty  = __( 'Tickets', 'bnmextends' );
        $labels->name = __( 'Show', 'bnmextends' );

        $html          = <<< HTML
<table width="100%" cellpadding="8" cellspacing="0">
<thead>
<tr>
<td style="background-color: #555;color:#fff;text-align: right">{$labels->qty}</td>
<td style="background-color: #555;color:#fff">{$labels->name}</td>
</tr>
</thead>
<tbody>
{$buffer}
</tbody>
</table>
HTML;
        $subject       = self::templateTitle( 'conferma-blue-note-brunch' );
        $to            = $order->bill_email;
        $original_body = self::template( 'conferma-blue-note-brunch' );

        /* Filters placeholder */
        $filters = array(
            '[$ORDERID$]' => $order->id,
            '[$TICKETS$]' => $html,
        );

        $body = strtr( $original_body, $filters );

        return ( self::mail( $to, $subject, $body ) );
    }

    public static function reservationSeat( $order, $note, $show ) {
        $subject = self::templateTitle( 'prenotazione-tavolo-soci' );
        $to      = BNMEXTENDS_PRIMARY_EMAIL;

        $body = self::template( 'prenotazione-tavolo-soci' );

        $filters = array(
            '[$NOME$]'         => $order->bill_first_name,
            '[$COGNOME$]'      => $order->bill_last_name,
            '[$NUMEROTAVOLO$]' => $note,
            '[$SPETTACOLO$]'   => $show,
            '[$MAILUTENTE$]'   => $order->bill_email
        );

        $body = strtr( $body, $filters );

        return ( self::mail( $to, $subject, $body ) );
    }

    public static function invoiceRequest( $values ) {
        $subject = self::templateTitle( 'richiesta-fattura' );
        $to      = BNMEXTENDS_INVOICE_REQUEST_EMAIL;

        $body = self::template( 'richiesta-fattura' );

        $ragione_sociale = sprintf( '<p>%s</p><p>%s</p><p>%s</p>', esc_attr( $_POST['company_name'] ), esc_attr( $_POST['vat_number'] ), esc_attr( $_POST['invoice_note'] ) );

        //salvo i dati in sessione per poterli poi memorizzare nel DB
        $_SESSION['invoiceValues'] = self::invoiceValues();

        $filters = array(
            '[$NOME$]'           => esc_attr( $_POST['bill_first_name'] ),
            '[$COGNOME$]'        => esc_attr( $_POST['bill_last_name'] ),
            '[$ORDINE$]'         => $values['id'],
            '[$EMAIL$]'          => sanitize_email( $_POST['bill_email'] ),
            '[$RAGIONESOCIALE$]' => $ragione_sociale
        );

        $body = strtr( $body, $filters );

        return ( self::mail( $to, $subject, $body ) );
    }

    /**
     * Preparo i valori da memorizzare nel DB secondo lo schema del DB
     * @return array
     */
    private static function invoiceValues(){
        $invoice = array();

        $invoice['invoice_company_name'] = isset( $_POST['company_name'] ) ? $_POST['company_name'] : "" ;
        $invoice['invoice_vat_number'] = isset( $_POST['vat_number']) ? $_POST['vat_number'] : "";
        $invoice['invoice_note'] = isset( $_POST['invoice_note'] ) ? $_POST['invoice_note'] : "";
        $invoice['invoice_check'] = isset( $_POST['invoice_check'] ) ? $_POST['invoice_check'] : ""; //Not used yet
        $invoice['invoice_fiscal_code'] = isset( $_POST['invoice_fiscal_code'] ) ? $_POST['invoice_fiscal_code'] : ""; //Not used yet

        return $invoice;

    }

    private static function checkInvoiceValues( $invoice ){
        return !( empty($invoice['invoice_company_name']) || empty( $invoice['invoice_vat_number'] ) );
    }
}