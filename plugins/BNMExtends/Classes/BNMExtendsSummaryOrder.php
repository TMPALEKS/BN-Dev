<?php
/**
 * Applica le action ed i filtri per il summary order di Smart Shop.
 * A questa classe va aggiunta una gestione Javascript inserita in checkout.js
 *
 * @package            BNMExtends
 * @subpackage         BNMExtendsSummaryOrder.php
 * @author             =undo= <g.fazioli@saidmade.com>
 * @copyright          Copyright (c) 2011-2012 Saidmade Srl.
 * @created            27/12/11
 * @version            1.0
 *
 */

class BNMExtendsSummaryOrder {

    // -----------------------------------------------------------------------------------------------------------------
    // Static values
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Quest'array definisce una serie di sconti personalizzati di Blue Note, aggiunti al Summary Order tramite i
     * filtri. Questi vengono applicati manualmente dall'operatore di Box office.
     *
     * @note Il campo value può essere: una percentuale sul prezzo base (20% come stringa), una diminuzione assoluta o
     *       un aumento assoluto ( -5 o '+10' quest'ultimo stringa) o un valore sempre uguale ( '18' ).
     *
     * @static
     * @return array
     */
    public static function discountIDs() {
        $discountIDs = array(
            'door'              => array(
                'label'      => __( 'Door', 'bnmextends' ),
                'value'      => '+5'
            ),
            'adv_prev'                       => array(
                'label'      => __( 'Adv+Prev', 'bnmextends' ),
                'value'      => '+2.50'
            ),
            'kids'                           => array(
                'label'      => __( 'Bambino', 'bnmextends' ),
                'value'      => 12
            ),
            'music-school-18'                => array(
                'label'      => __( 'Scuola di Musica', 'bnmextends' ),
                'value'      => '18'
            ),
            'cral'                           => array(
                'label'      => __( 'CRAL', 'bnmextends' ),
                'value'      => '20%'
            ),
            'carte'                          => array(
                'label'      => __( 'Carte', 'bnmextends' ),
                'value'      => '20%'
            ),
            'misic-school'                   => array(
                'label'      => __( 'Scuola di Musica', 'bnmextends' ),
                'value'      => '20%'
            ),
            'platinum'                       => array(
                'label'      => __( 'Platinum', 'bnmextends' ),
                'value'      => '100%'
            ),
            'club'                           => array(
                'label'      => __( 'Club', 'bnmextends' ),
                'value'      => '40%'
            ),
            'over65'                         => array(
                'label'      => __( 'Over 65', 'bnmextends' ),
                'value'      => '40%'
            ),
            'under26'                        => array(
                'label'      => __( 'Under 26', 'bnmextends' ),
                'value'      => '40%'
            ),
            'groups'                         => array(
                'label'      => __( 'Groups', 'bnmextends' ),
                'value'      => '100%'
            ),
            'rateo_members'                  => array(
                'label'      => __( 'Rateo Coupon Members', 'bnmextends' ),
                'value'      => '100%'
            ),
            'rateo_satchmo'                  => array(
                'label'      => __( 'Rateo Abb. Satchmo', 'bnmextends' ),
                'value'      => '100%'
            ),
            'rateo_rosa'                     => array(
                'label'      => __( 'Rateo Abb. Rosa', 'bnmextends' ),
                'value'      => '100%'
            ),
            'rateo_verde'                    => array(
                'label'      => __( 'Rateo Abb. Verde', 'bnmextends' ),
                'value'      => '100%'
            ),
            'rateo_u26'                      => array(
                'label'      => __( 'Rateo Abb. U26', 'bnmextends' ),
                'value'      => '100%'
            ),
            'show_voucher'                   => array(
                'label'      => __( 'Show Voucher', 'bnmextends' ),
                'value'      => '100%'
            ),
            'dinner_voucher'                 => array(
                'label'      => __( 'Dinner Voucher', 'bnmextends' ),
                'value'      => '100%'
            ),
            'sconto20percento_advance'       => array(
                'label'      => __( 'sconto 20% su ticket 10€', 'bnmextends' ),
                'value'      => '8'
            ),
            'sconto40percento_advance'       => array(
                'label'      => __( 'sconto 40% su ticket 10€', 'bnmextends' ),
                'value'      => '6'
            ),
            'sconto20percento_cenone'        => array(
                'label'      => __( 'sconto 20% su cenone capodanno', 'bnmextends' ),
                'value'      => '160'
            ),
            'sconto40percento_brindisi'        => array(
                'label'      => __( 'sconto 40% su brindisi capodanno', 'bnmextends' ),
                'value'      => '45'
            ),
            'cambio_spettacolo'              => array(
                'label'      => __( 'Cambio Spettacolo', 'bnmextends' ),
                'value'      => '100%'
            ),

        );
        return $discountIDs;
    }

    /**
     * Associa i Coupon con i ratei - serve per il conteggio richiesto lato riepilogo
     * La lettura da dare a questo array è
     *
     * 'Chiave Tendina Lato Operatore' = array ('Chiave/Label/Nome lato Ruolo o Abbonamento' => ' Descrizione da far comparire a video')
     *
     * Purtroppo i Ruoli e gli Abbonamenti sono mischiati
     * Questa funzione è stata necessaria perchè allo stato attuale non c'è nulla che leghi univocamente i valori per permettere gli aggregati richiesti
     */
    public static function coupons2Discounts(){
        $coupons2Discounts = array();
        //$discountsId = self::discountIDs();

        $coupons2Discounts["rateo_satchmo"] = array( 'Abbonamento Satchmo' => 'Rateo Abbonamento Satchmo');
        $coupons2Discounts["show_voucher"] = array(
                                                '5 Show Voucher' => 'Show Voucher',
                                                "2 Show Voucher"  => 'Show Voucher',
                                                "Show Voucher" => 'Show Voucher',
                                            );

        $coupons2Discounts["dinner_voucher"] = array(
                                                'Dinner Voucher' => 'Dinner Voucher',
                                                '5 Dinner Voucher' => 'Dinner Voucher',
                                                "2 Dinner Voucher"  => 'Dinner Voucher',
                                            );
        $coupons2Discounts["rateo_rosa"] = array('Abbonamento Rosa' => 'Rateo Abbonamento Rosa');
        $coupons2Discounts["rateo_verde"] = array('Abbonamento Verde' => 'Rateo Abbonamento Verde');
        //$coupons2Discounts["under26"] = array('Abbonamento Under 26' => 'Rateo Abbonamento Giovani');

        return $coupons2Discounts;
    }

    // -----------------------------------------------------------------------------------------------------------------
    // Register Summary Order filters and actions
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Registra una serie di filtri Smart Shop.
     * Questo metodo viene chiamato solo per un determinato tipo di utente.
     *
     * @static
     *
     * @see registerBoxOffice() in main.php
     *
     */
    public static function registerBoxOffice() {
        add_filter( 'wpss_summary_order_columns', array( __CLASS__, 'wpss_summary_order_columns') );
        add_filter( 'wpss_summary_order_cell', array( __CLASS__, 'wpss_summary_order_cell'), 10, 3);
        add_filter( 'wpss_summary_order_apply_custom_discount', array( __CLASS__, 'wpss_summary_order_apply_custom_discount'), 10, 4 );
        add_filter( 'wpss_summary_order_id_custom_discount', array( __CLASS__, 'wpss_summary_order_id_custom_discount'), 10, 5 );

        /* A titolo di esempio ma in pratica non usati */
        add_filter( 'wpss_summary_order_vat', array( __CLASS__, 'wpss_summary_order_vat') );
        add_filter( 'wpss_summary_order_shipping', array( __CLASS__, 'wpss_summary_order_shipping') );

        /* Aggiunge una classe nel tag body nell'amministrazione per gli stili */
        //add_filter( 'admin_body_class', array( __CLASS__, 'admin_body_class') );
    }

    /**
     * Registra una serie di filtri Smart Shop e Placeholder.
     * Questo metodo viene chiamato solo per un determinato tipo di utente.
     *
     * @static
     *
     * @see registerClubPlatinum() in main.php
     *
     */
    public static function registerClubPlatinum() {
        add_filter( 'wpss_summary_order_cell', array( __CLASS__, 'wpss_summary_order_cell_club_platinum' ), 10, 3);
        add_filter( 'wpss_stats_create_variant_note', array( __CLASS__, 'wpss_stats_create_variant_note' ), 10, 3);
    }

    // -----------------------------------------------------------------------------------------------------------------
    // Smart Shop Filters
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Questo filtro è utilizzato per scrivere nelle note della tabella stats di Smart Shop le informazioni sui tavoli
     * scelti dall'utente Club Platinum, se presenti
     *
     * @static
     *
     * @param string  $note    Nota con le informazioni della variante come memorizzate nei post meta da backend, potrebbe
     *                         essere vuota
     * @param array   $product Array che descrive il prodotto, come da sessione
     * @param array   $variant Array che descrive la variante, recuperata dall'array delle varianti memorizzato nei post
     *                         meta
     *
     * @return string
     * Stringa con la nuova 'note'
     */
    public static function wpss_stats_create_variant_note( $note, $product, $variant ) {
        $id_product = $product['id_product'];

        $table_selected = 'table_selected-' . $id_product;

        /* Solo se esiste il campo hidden con l'elenco dei tavoli scelti e la giusta variante */
        if ( isset( $_POST[$table_selected] ) && $product['id_variant'] == 'Dinner' && !empty( $product['model'] ) &&
            $product['model'] == BNMEXTENDS_WITH_DINNER_RESERVATION_KEY
        ) {
            $note .= esc_attr( $_POST[$table_selected] );
        }

        return $note;
    }

    /**
     * Questo è un filtro di Smart Shop usato per inserire del contenuto prima del summary order
     *
     * @static
     *
     * @param string $title      Titolo
     * @param string $column     ID della colonna
     * @param int    $id_product ID Del prodotto
     *
     * @return string
     */
    public static function wpss_summary_order_cell_club_platinum( $title, $column, $id_product_key ) {

        if( $column != 'product') {
            return $title;
        }

        /* Corrisponde al numero di biglietti presi, quindi al numero dei posti da prendere */
        $count_variant = 0;
        $products      = WPXSmartShopSession::products();
        $product       = $products[$id_product_key];
        if ( $product['id_variant'] == 'Dinner' && !empty( $product['model'] ) &&
            $product['model'] == BNMEXTENDS_WITH_DINNER_RESERVATION_KEY
        ) {
            $count_variant = $product['qty'];
        }

        if ( $count_variant == 0 ) {
            return $title;
        }

        $id_product = $product['id_product'];

        /* @todo Da eliminare || 1 solo per debug */
        if ( WPDKUser::hasCaps( array( 'bnm_cap_facility_dinner' ) ) ) {

            $balconata = array(
                array( '160',  0000,  0000,  0000,  0000,  0000,  0000,  0000,  0000,  0000, 0000,      0000,     0000,    0000,   0000,  0000,  0000,  0000,  0000,  0000,  0000,  0000,  '101' ),
                array( '159',  0000,  0000,  0000,  0000,  0000,  0000,  0000,  0000,  0000, 0000,      0000,     0000,    0000,   0000,  0000,  0000,  0000,  0000,  0000,  0000,  0000,  '102' ),
                array( '158',  0000,  0000,  0000,  0000,  0000,  0000,  0000,  0000,  0000, 0000,      0000,     0000,    0000,   0000,  0000,  0000,  0000,  0000,  0000,  0000,  0000,  '103' ),
                array( '157',  0000,  0000,  0000,  0000,  0000,  0000,  0000,  0000,  0000, 0000,      0000,     0000,    0000,   0000,  0000,  0000,  0000,  0000,  0000,  0000,  0000,  '104' ),
                array( '156',  0000,  0000,  0000,  0000,  0000,  0000,  0000,  0000,  0000, 0000,      0000,     0000,    0000,   0000,  0000,  0000,  0000,  0000,  0000,  0000,  0000,  '105' ),
                array( '155',  0000,  0000,  0000,  0000,  0000,  0000,  0000,  0000,  0000, 0000,      0000,     0000,    0000,   0000,  0000,  0000,  0000,  0000,  0000,  0000,  0000,  '106' ),
                array( '154',  0000,  0000,  0000,  0000,  0000,  0000,  0000,  0000,  0000, 0000,      0000,     0000,    0000,   0000,  0000,  0000,  0000,  0000,  0000,  0000,  0000,  '107' ),
                array( '153',  0000,  0000,  0000,  0000,  0000,  0000,  0000,  0000,  0000, 0000,      0000,     0000,    0000,   0000,  0000,  0000,  0000,  0000,  0000,  0000,  0000,  '108' ),
                array( '152',  0000,  0000,  0000,  0000,  0000,  0000,  0000,  0000,  0000, 0000,      0000,     0000,    0000,   0000,  0000,  0000,  0000,  0000,  0000,  0000,  0000,  '109' ),
                array( '151',  0000,  0000,  0000,  0000,  0000,  0000,  0000,  0000,  0000, 0000,      0000,     0000,    0000,   0000,  0000,  0000,  0000,  0000,  0000,  0000,  0000,  '110' ),
                array( '150',  0000,  0000,  0000,  0000,  0000,  0000,  0000,  0000,  0000, 0000,      0000,     0000,    0000,   0000,  0000,  0000,  0000,  0000,  0000,  0000,  0000,  '111' ),
                array( '149',  0000,  0000,  0000,  0000,  0000,  0000,  0000,  0000,  0000, 0000,      0000,     0000,    0000,   0000,  0000,  0000,  0000,  0000,  0000,  0000,  0000,  '112' ),
                array( '148',  0000,  0000,  0000,  0000,  0000,  0000,  0000,  0000,  0000, 0000,      0000,     0000,    0000,   0000,  0000,  0000,  0000,  0000,  0000,  0000,  0000,  '113' ),
                array( '147',  0000,  0000,  0000,  0000,  0000,  0000,  0000,  0000,  0000, 0000,      0000,     0000,    0000,   0000,  0000,  0000,  0000,  0000,  0000,  0000,  0000,  '114' ),
                array( '146',  0000,  0000,  0000,  0000,  0000,  0000,  0000,  0000,  0000, 0000,      0000,     0000,    0000,   0000,  0000,  0000,  0000,  0000,  0000,  0000,  0000,  '115' ),
                array( '145',  0000,  0000,  0000,  0000,  0000,  0000,  0000,  0000,  0000, 0000,      0000,     0000,    0000,   0000,  0000,  0000,  0000,  0000,  0000,  0000,  0000,  '116' ),
                array( '144',  0000,  0000,  0000,  0000,  0000,  0000,  0000,  0000,  0000, 0000,      0000,     0000,    0000,   0000,  0000,  0000,  0000,  0000,  0000,  0000,  0000,  '117' ),
                array( '143',  0000,  0000,  0000,  0000,  0000,  0000,  0000,  0000,  0000, 0000,      0000,     0000,    0000,   0000,  0000,  0000,  0000,  0000,  0000,  0000,  0000,  '118' ),
                array( '142',  0000,  0000,  0000,  0000,  0000,  0000,  0000,  0000,  0000, 0000,      0000,     0000,    0000,   0000,  0000,  0000,  0000,  0000,  0000,  0000,  0000,  '119' ),
                array( '141',  0000,  0000,  0000,  0000,  0000,  0000,  0000,  0000,  0000, '163',     '162',    '161',    0000,   0000,  0000,  0000,  0000,  0000,  0000,  0000,  0000,  '120' ),

                array( '140', '139', '138', '137', '136', '135', '134', '133', '132', '131', '163BIS',   '162BIS',  '161BIS',  '130', '129', '128', '127', '126', '125', '124', '123', '122', '121' ),
                array( 0000,  0000,  0000,  0000,  0000,  0000,  0000,  0000,  0000,  0000,  0000,       0000,      0000,     0000,   0000,  0000,  0000,  0000,  0000,  0000,  0000,  0000,  0000 ),
                array( 0000,  0000,  0000,  0000,  0000,  0000,  0000,  0000,  0000,  0000,  '164',      '165',     '166',    0000,   0000,  0000,  0000,  0000,  0000,  0000,  0000,  0000,  0000 ),
                array( 0000,  0000,  0000,  0000,  0000,  0000,  0000,  0000,  0000,  0000,  '164BIS',  '165BIS',  '166BIS',  0000,   0000,  0000,  0000,  0000,  0000,  0000,  0000,  0000,  0000 ),
                array( 0000,  0000,  0000,  0000,  0000,  0000,  0000,  0000,  0000,  0000,   0000,      0000,      0000,     0000,   0000,   0000,  0000,  0000,  0000,  0000,  0000,  0000,  0000 ),

            );

            $piano_terra = array(
                array( 0000,      0000,    0000,     0000,    0000,      0000,    0000,    0000,      0000,    0000,    0000,      0000 ),
                array( '23BIS',  '23T',    '2BIS',  '2F',   0000,      0000,    0000,    0000,      '14F',  '14BIS',  '37T',     '37BIS' ),
                array( '24BIS',  '24T',    '3BIS',  '3F',  'RSRV-2', 'RSRV-2', 'RSRV-2', 'RSRV-2',  '12F',  '12BIS',  '36T',     '36BIS' ),
                array( 'RSRV-2', 'RSRV-2',  0000,    0000,  'RSRV-2', 'RSRV-2', 'RSRV-2', 'RSRV-2',   0000,   0000,    'RSRV-2',  'RSRV-2' ),
                array( 'RSRV-2', 'RSRV-2',  '4BIS',  '4F',  'RSRV-2', 'RSRV-2', 'RSRV-2', 'RSRV-2',  '11F',  '11BIS',  'RSRV-2', 'RSRV-2' ),

                array( 0000,   0000,    0000,    0000,    0000,    0000,     0000,    0000,    0000,    0000,    0000,     0000 ),

                array( 0000,   0000,     0000,     0000,   '6F',     '7F',    '8F',    '9F',    0000,    0000,     0000,    0000 ),
                array( 0000,   '26T',    0000,     '5',    '6BIS',   '7BIS',  '8BIS',  '9BIS',     '10',    0000,    '40T',    0000 ),
                array( 0000,   '26BIS',  '27T' ,   0000,   '6TR',    '7TR',   '8TR',   '9TR',  0000,    '34T',    '40BIS',  0000 ),
                array( 0000,   00000,    '27BIS',  0000,    0000,   '7QR',   '8QR',    0000,   0000,    '34BIS',   0000,    0000 ),
                array( 0000,   0000,     '27TR',  '28T',    0000,    0000,    0000,    0000,   '33T',   '34TR',    0000,    0000 ),
                array( 0000,   0000,     0000,    '28BIS', '29T',   '30T',   '31T',   '32T',   '33BIS',  0000,     0000,    0000 ),
                array( 0000,   0000,     0000,    '28TR',  '29BIS', '30BIS', '31BIS', '32BIS', '33TR',   0000,     0000,    0000 ),
                array( 0000,   '49T',    0000,    0000,    '29TR',  '30TR',  '31TR',  '32TR',   0000,    0000,    '41T',    0000 ),
                array( 0000,   '49BIS', '48T',    0000,    0000,    0000,    0000,    0000,     0000,    '42T',   '41BIS',  0000 ),
                array( 0000,   0000,    '48BIS', '47T',   '46T',   '45T',   '44T',    0000,    '43T',    '42BIS',  0000,    0000 ),
                array( 0000,   0000,    '48TR',  '47BIS', '46BIS', '45BIS', '44BIS',  0000,    '43BIS',  0000,     0000,    0000 ),
                array( 0000,   0000,    0000,    '47TR',  '46TR',   0000,   '44TR',   0000,     0000,    0000,     0000,    0000 ),
                array( 0000,   0000,    0000,    0000,     0000,    0000,    0000,    0000,     0000,    0000,     0000,    0000 ),
            );

            $map = array(
                '1' => $piano_terra,
                '2' => $balconata
            );

            /* Disegna la mappa */

            /* @warning Parto dal presupposto che la data del biglietto è '03/07/2012 21:00 - Patty Pravo' */
            $time        = substr( $title, 0, 16 );
            $mktime      = WPDKDateTime::makeTimeFrom( 'd/m/Y H:i', $time );
            $date_start  = $mktime;
            $date_expiry = $mktime + 2 * 60 * 60;
            $date_start  = date( MYSQL_DATE_TIME, $date_start );
            $date_expiry = date( MYSQL_DATE_TIME, $date_expiry );

            $reservations         = WPPlaceholdersReservations::reservations( 1, $date_start, $date_expiry );
            $html_map_piano_terra = WPPlaceholdersReservations::planReservations( 1, $reservations, $map );

            $reservations         = WPPlaceholdersReservations::reservations( 2, $date_start, $date_expiry );
            $html_map_balconata   = WPPlaceholdersReservations::planReservations( 2, $reservations, $map );

            $html_select = <<< HTML
    <select name="" class="wpdk-form-select bnm-placeholder-select-environment">
        <option value="1">Piano Terra</option>
        <option value="2">Balconata</option>
    </select>
HTML;

            $open_dialog = __( 'Select a table for dinner', 'bnmextends' );
            $html        = <<< HTML
    <br /><a data-id_product="{$id_product}" data-count_ticket="{$count_variant}" id="bnm-button-dinner-choice-{$id_product}" class="bnm-button-dinner-choice button orange" href="#">{$open_dialog} <br />
    <small class="product-warning">Inserisci eventuali coupon prima di prenotare i tavoli</small></a>
    <input type="hidden" name="table_selected-{$id_product}" id="table_selected-{$id_product}" />
    <div style="display:none" data-id_product="{$id_product}" class="bnm-dialog-reservations" id="bnm-dialog-reservations-{$id_product}">
        {$html_select}
        <div class="bnm-placeholder-environment bnm-placeholder-environment-1">{$html_map_piano_terra}</div>
        <div style="display:none" class="bnm-placeholder-environment bnm-placeholder-environment-2">{$html_map_balconata}</div>
    </div>
HTML;
            return $title . $html;

        }

        return $title;
    }

    /**
     * Aggiunge una nuova colonna al Summary Order standard di Smart Shop
     *
     * @static
     *
     * @param $columns
     *
     * @return array
     */
    public static function wpss_summary_order_columns( $columns ) {

        /* Aggiungo una nuova colonna in penultima posizione */
        $columns = WPDKArray::insertKeyValuePair( $columns, 'discount', __( 'Discount', 'bnmextends' ),
            count( $columns ) - 1 );

        return $columns;
    }

    /**
     * Costruisce il contenuto della cella di una colonna aggiunta al Summary order di Smart Shop
     *
     * @static
     *
     * @param string $content Contenuto originale
     * @param string $column_key ID della colonna
     * @param string $id_product_key Codice del prodotto + variante codificato base64
     *
     * @return string
     */
    public static function wpss_summary_order_cell( $content, $column_key, $id_product_key ) {

        if ( $column_key == 'discount' ) { //vedo se la cella è di tipo DISCOUNT (definito sopra)
            $selected    = WPXSmartShopSession::productCustomDiscount( $id_product_key );
            $ids_custom_discount = self::discountIDs();

            $first_item = __( 'Apply a discount', 'bnmextends' );

            $options = '';

            foreach ( $ids_custom_discount as $id_custom_discount => $discount ) {
                $value = $discount['value'];
                if ( !WPXSmartShopCurrency::isPercentage( $value ) ) {
                    $value .= WPXSmartShopCurrency::currencySymbol();
                }
                $options .= sprintf('<option %s value="%s">%s (%s)</option>',
                    selected( $selected, $id_custom_discount, false ),
                    $id_custom_discount, $discount['label'],
                    $value );
            }

            $content = <<< HTML
            <select name="discountID[]" data-id_product_key="{$id_product_key}" class="bnm-summary-order-discount wpdk-form-select">
            <option value="">{$first_item}</option>
            {$options}
            </select>
HTML;
        }
        return $content;
    }

    /**
     * Applica un determinato sconto ad un prodotto
     *
     * @static
     *
     * @param float  $price
     * @param string $id_product_key ID prodotto + variante codificata base 64
     * @param int    $qty            Quantità
     * @param int    $nth            A partire da
     *
     * @return float
     */
 
    public static function wpss_summary_order_apply_custom_discount( $price, $id_product_key, $qty, $nth ) {
            $selected = WPXSmartShopSession::productCustomDiscount( $id_product_key );
    
            if( $selected ) {
                $discountIDs = self::discountIDs();
                $discount = $discountIDs[$selected]['value'];
    			

                if ( WPXSmartShopCurrency::isPercentage( $discount ) ) {
    			/* 
                    $price = $price + kBNMExtendsSummaryOrderDiscountPrice * $qty;                               
                    $percentage = round( $price * intval( $discount ) / 100 );

                    return $price - $percentage;
       			*/
                    $price = ( $price + kBNMExtendsSummaryOrderDiscountPrice * $qty ) / $qty;                               
                    $percentage = round( $price * intval( $discount ) / 100 );

                    return ( $price - $percentage ) * $qty;
                    
                } elseif ( substr( $discount, 0, 1 ) == '-' || substr( $discount, 0, 1 ) == '+' ) {
                    return ( $price + floatval( $discount ) * $qty );
                } elseif ( is_numeric( $discount ) ) {
                    return $discount * $qty;
                }
                
            }
            return $price;
        }
    

    /**
     * Comunica a SmartShop il codice sconto personalizzato.
     * Questo filtro controlla wpss_summary_order_apply_custom_discount, se viene restituito un valore vuoto o questo
     * filtro non viene impostato, anche wpss_summary_order_apply_custom_discount non viene eseguito.
     *
     * @param mixed  $rule           Identificatico della regola. Di solito una stringa
     * @param float  $price
     * @param string $id_product_key ID prodotto + variante codificata base 64
     * @param int    $qty            Quantità
     * @param int    $nth            A partire da
     *
     * @return mixed
     *
     */
    public static function wpss_summary_order_id_custom_discount( $rule, $price, $id_product_key, $qty, $nth ) {
        $selected = WPXSmartShopSession::productCustomDiscount( $id_product_key );
        if ( $selected ) {
            return $selected;
        }
        return $rule;
    }

    /**
     * Action chiamata dallo shortcode del summary order di smart shop per visualizzare le informazioni sulla
     * fatturazione
     *
     * @static
     *
     */
    public static function wpss_checkout_bill_information() {

   		$user = BNMExtendsUser::userWithID( get_current_user_id() );

        if ( WPDKUser::hasCaps( array ( 'bnm_cap_offline' ) ) ) {
            $user = false;
        }

        $fields = array(
            __( 'Billing information', 'bnmextends' ) => array(
                __( 'Please fill in or update all fields.', 'bnmextends' ),
                array(
                    'type'  => WPDK_FORM_FIELD_TYPE_HIDDEN,
                    'name'  => 'id_user_order'
                ),
                array(
                    array(
                        'type'   => WPDK_FORM_FIELD_TYPE_TEXT,
                        'name'   => 'bill_email',
                        'size'   => 32,
                        'label'  => __( 'Email', 'bnmextends' ),
                        'value'  => $user ? $user->extra->email : '',
                        'append' => '<span class="bnm-user-role"></span>'
                    ),
                ),
                array(
                    array(
                        'type'  => WPDK_FORM_FIELD_TYPE_TEXT,
                        'name'  => 'bill_first_name',
                        'size'  => 32,
                        'label' => __( 'First Name', 'bnmextends' ),
                        'value' => $user ? $user->first_name : ''
                    ),
                    array(
                        'type'  => WPDK_FORM_FIELD_TYPE_TEXT,
                        'name'  => 'bill_last_name',
                        'size'  => 32,
                        'label' => __( 'Last Name', 'bnmextends' ),
                        'value' => $user ? $user->last_name : ''
                    ),
                ),
                array(
                    array(
                        'type'      => WPDK_FORM_FIELD_TYPE_TEXT,
                        'label'     => __( 'Address', 'bnmextends' ),
                        'size'      => 32,
                        'name'      => 'bill_address',
                        'value'     => $user ? $user->extra->bill_address : ''
                    ),
                    array(
                        'type'      => WPDK_FORM_FIELD_TYPE_NUMBER,
                        'label'     => __( 'ZIP code', 'bnmextends' ),
                        'size'      => 6,
                        'name'      => 'bill_zipcode',
                        'value'     => $user ? $user->extra->bill_zipcode : ''
                    ),
                    array(
                        'type'      => WPDK_FORM_FIELD_TYPE_TEXT,
                        'label'     => __( 'Town', 'bnmextends' ),
                        'size'      => 11,
                        'name'      => 'bill_town',
                        'value'     => $user ? $user->extra->bill_town : ''
                    ),
                ),
                array(
                    array(
                        'type'      => WPDK_FORM_FIELD_TYPE_SELECT,
                        'name'      => 'bill_country',
                        'label'     => __( 'Country', 'bnmextends' ),
                        'options'   => WPSmartShopShippingCountries::countriesForSelectMenu(),
                        'value'     => $user ? ( ( empty( $user->extra->bill_country ) ) ? '33' : $user->extra->bill_country ) : '33'
                    ),
                    array(
                        'type'      => WPDK_FORM_FIELD_TYPE_PHONE,
                        'label'     => __( 'Phone', 'bnmextends' ),
                        'size'      => 10,
                        'name'      => 'bill_phone',
                        'value'     => $user ? $user->extra->bill_phone : ''
                    ),
                    array(
                        'type'      => WPDK_FORM_FIELD_TYPE_PHONE,
                        'label'     => __( 'Mobile', 'bnmextends' ),
                        'size'      => 10,
                        'name'      => 'bill_mobile',
                        'value'     => $user ? $user->extra->bill_mobile : ''
                    ),
                ),
                array(
                    array(
                        'type'  => WPDK_FORM_FIELD_TYPE_TEXTAREA,
                        'name'  => 'note',
                        'label' => __( 'Note', 'bnmextends' ),
                        'cols'  => 50
                    )
                ),
                array(
                    array(
                        'type'  => WPDK_FORM_FIELD_TYPE_CHECKBOX,
                        'name'  => 'invoice_request',
                        'label' => __( 'Are you buying for a company and you need invoice?', 'bnmextends' ),
                        'value' => 'y',
                    )
                ),
                array(
                    array(
                        'type'       => WPDK_FORM_FIELD_TYPE_CHECKBOX,
                        'name'       => 'bnm-toggle-update-billing-information',
                        'value'      => 'y',
                        'label'      => __( 'Update this information on your profile', 'bnmextends' )
                    ),
                ),
                array(
                    array(
                        'type'       => WPDK_FORM_FIELD_TYPE_CHECKBOX,
                        'name'       => 'bnm-toggle-shipping-information',
                        'value'      => 'y',
                        'label'      => __( 'Different shipping address', 'bnmextends' )
                    ),
                ),
            ),
            __( 'Invoice information', 'bnmextends') => array(
                array(
                    array(
                        'type'    => WPDK_FORM_FIELD_TYPE_TEXT,
                        'name'    => 'company_name',
                        'label'   => __( 'Company Name', 'bnmextends' ),
                        'value'   => $user ? $user->extra->company_name : ''
                    ),
                    array(
                        'type'    => WPDK_FORM_FIELD_TYPE_TEXT,
                        'name'    => 'vat_number',
                        'label'   => __( 'Vat or Fiscal Number', 'bnmextends' ),
                        'value'   => $user ? $user->extra->vat_number : '',
                        'size'   => 50
                    )
                ),

                array(
                    array(
                        'type'        => WPDK_FORM_FIELD_TYPE_TEXTAREA,
                        'name'        => 'invoice_note',
                        'label'       =>__('Full Company Address: please insert Street, City, Zip, Country', 'bnmextends'),
                        'cols'        => 50,
                        'value'       => $user ? get_user_meta( $user->ID, 'invoice_note', true ) : ''
                    )
                ),
            )
        );

        /* Se non box office elimino blocco note. */
        if ( !WPDKUser::hasCaps( array ( 'bnm_cap_offline' ) ) && !WPDKUser::hasCaps( array ( 'bnm_cap_intermediaries' ) ) ) {
            $subfields = &$fields[key($fields)];
            array_splice( $subfields, 6, 1 );
        }

        WPDKForm::htmlForm( $fields );
   	}

    /**
     * Action chiamata dallo shortcode del summary order di smart shop per visualizzare le informazioni sulla spedizione
     *
     * @static
     *
     */
    public static function wpss_checkout_shipping_information() {
        $user = BNMExtendsUser::userWithID( get_current_user_id() );

        $fields = array(
            __( 'Shipping information', 'bnmextends' ) => array(
                array(
                    array(
                        'type'  => WPDK_FORM_FIELD_TYPE_TEXT,
                        'name'  => 'shipping_email',
                        'size'  => 32,
                        'label' => __( 'Email', 'bnmextends' ),
                        'value' => $user->extra->shipping_email
                    ),
                ),
                array(
                    array(
                        'type'  => WPDK_FORM_FIELD_TYPE_TEXT,
                        'name'  => 'shipping_first_name',
                        'size'  => 32,
                        'label' => __( 'First Name', 'bnmextends' ),
                        'value' => $user->shipping_first_name
                    ),
                    array(
                        'type'  => WPDK_FORM_FIELD_TYPE_TEXT,
                        'name'  => 'shipping_last_name',
                        'size'  => 32,
                        'label' => __( 'Last Name', 'bnmextends' ),
                        'value' => $user->shipping_last_name
                    ),
                ),
                array(
                    array(
                        'type'      => WPDK_FORM_FIELD_TYPE_TEXT,
                        'label'     => __( 'Address', 'bnmextends' ),
                        'size'      => 32,
                        'name'      => 'shipping_address',
                        'value'     => $user ? $user->extra->shipping_address : ''
                    ),
                    array(
                        'type'      => WPDK_FORM_FIELD_TYPE_NUMBER,
                        'label'     => __( 'ZIP code', 'bnmextends' ),
                        'size'      => 6,
                        'name'      => 'shipping_zipcode',
                        'value'     => $user ? $user->extra->shipping_zipcode : ''
                    ),
                    array(
                        'type'      => WPDK_FORM_FIELD_TYPE_TEXT,
                        'label'     => __( 'Town', 'bnmextends' ),
                        'size'      => 11,
                        'name'      => 'shipping_town',
                        'value'     => $user ? $user->extra->shipping_town : ''
                    ),
                ),
                array(
                    array(
                        'type'      => WPDK_FORM_FIELD_TYPE_SELECT,
                        'name'      => 'shipping_country',
                        'label'     => __( 'Country', 'bnmextends' ),
                        'options'   => WPSmartShopShippingCountries::countriesForSelectMenu(),
                        'value'     => $user ? ( (
                            empty($user->extra->shipping_country ) ) ? '33' : $user->extra->shipping_country ) : '33'
                    ),
                    array(
                        'type'      => WPDK_FORM_FIELD_TYPE_PHONE,
                        'label'     => __( 'Phone', 'bnmextends' ),
                        'size'      => 10,
                        'name'      => 'shipping_phone',
                        'value'     => $user ? $user->extra->shipping_phone : ''
                    ),
                    array(
                        'type'      => WPDK_FORM_FIELD_TYPE_PHONE,
                        'label'     => __( 'Mobile', 'bnmextends' ),
                        'size'      => 10,
                        'name'      => 'shipping_mobile',
                        'value'     => $user ? $user->extra->shipping_mobile : ''
                    ),
                ),
                array(
                    array(
                        'type'       => WPDK_FORM_FIELD_TYPE_CHECKBOX,
                        'name'       => 'bnm-toggle-update-shipping-information',
                        'value'      => 'y',
                        'label'      => __( 'Update this information on your profile', 'bnmextends' )
                    ),
                ),
            )
        );
        ?>
    <div id="wpssPersonalInformationShippingAddress" style="display:none"><?php
        WPDKForm::htmlForm( $fields );
        ?></div><?php

        printf( __( '<p style="text-align:right;font-weight: bold">Proceed to Purchase even if the amount of your purchase is zero</p>', 'bnmextends'));

    }

    /**
     * Filtro per calcolare le spese di spedizione
     *
     * @static
     *
     * @param float $value
     * @param int   $id_product
     * @param int   $qty
     *
     * @return float|bool
     * Spese di spedizione o false se non ci sono
     */
    public static function wpss_summary_order_product_shipping( $value, $id_product, $qty ) {

        /* Paese */
        $id_country = WPXSmartShopSession::orderShippingCountry();
        if ( empty( $id_country ) ) {
            $user       = BNMExtendsUser::userWithID( get_current_user_id() );
            $id_country = $user->extra->bill_country;
        }

        /*  Corriere */
        $id_carrier = WPXSmartShopSession::orderShippingCarrier();
        if ( empty( $id_carrier ) ) {
            $id_carrier = WPXSmartShop::settings()->default_carrier();
        }

        /* Calcolo */
        $value = WPSmartShopShipments::shipmentValueForProduct( $id_product, $id_country, $id_carrier, 0, $qty );
        return $value;
    }

    /**
     * Action per il bottone di Cash - acquisto in contanti
     *
     * @static
     * @deprecated
     *
     */
    public static function buyWithCash() {
        ?>
    <input name="WPSS_CUSTOM_SHOP"
           class="button orange left"
           type="submit"
           value="<?php _e( 'Cassa', 'bnmextends' ) ?>"/>
    <?php
    }

    public static function wpxss_button_cash_values( $values ) {
        return $values;
    }

    public static function wpxss_button_cash_select_class( $class ) {
        $class[] = 'left';
        return $class;
    }

    public static function wpxss_button_cash_submit_class( $class ) {
        $class = array(
            'button',
            'orange',
            'left'
        );
        return $class;
    }


    /**
     * IVA
     *
     * @static
     *
     * @param float $vat
     *
     * @return float
     */
    public static function wpss_summary_order_vat( $vat ) {
        return $vat;
    }

    /**
     * Spese di spedizione
     *
     * @static
     *
     * @param float $price
     *
     * @return float
     */
    public static function wpss_summary_order_shipping( $price ) {
        return $price;
    }


    // -----------------------------------------------------------------------------------------------------------------
    // Payment Gateway
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Chiamata prima di inserire un ordine (da frontend).
     * Questa adesso si preoccupa e viene usata per memorizzare (se richiesto dall'utente) i dati anagrafici e di
     * billing presenti nel Summary Order.
     * Inoltre è da considerarsi come momento per elaborare eventuali altri dati nel form.
     *
     * @filter
     * @static
     * @param $values
     */
    public static function wpss_payment_gateway_order_will_insert( $values ) {

        /* Verifico se devo memorizzare i dati di billing nella tabella utente */
        if ( isset( $_POST['bnm-toggle-update-billing-information'] ) &&
            $_POST['bnm-toggle-update-billing-information'] == 'y'
        ) {
            $id_user = $values['id_user_order'];
            BNMExtendsUser::updateUserBilling( $id_user ); //aggiorna i dati di spedizione
            BNMExtendsUser::updateUserInvoice( $id_user ); //aggiorna i dati di fatturazione
        }

        /* Verifico se devo memorizzare i dati di shipping nella tabella utente */
        if ( isset( $_POST['bnm-toggle-update-shipping-information'] ) &&
            $_POST['bnm-toggle-update-shipping-information'] == 'y'
        ) {
            $id_user = $values['id_user_order'];
            BNMExtendsUser::updateUserShipping( $id_user );
        }

        /* Note aggiuntive per ordine */
        if ( !empty( $_POST['note'] ) ) {
            $values['note'] = esc_textarea( $_POST['note'] );
        }

        return $values;
    }

    public static function wpss_payment_gateway_order_did_insert( $values ) {
        /* Verifico richiesta fattura */
        if ( isset( $_POST['invoice_request'] ) && $_POST['invoice_request'] == 'y' ) {
            BNMExtendsMail::invoiceRequest( $values );
        }
        return $values;
    }

    public static function wpss_payment_result_invoice( $order ) {
        $thanks_message = __( 'Thanks for your shopping at Blue Note Milano', 'bnmextends' );
        echo '<p>' . $thanks_message . '</p>';
    }

    public static function wpss_summary_order_before( $content ) {
        $content = '<div style="margin:8px 0"><a href="' . get_bloginfo('url') . '/wp-admin/admin.php?page=wpxss_menu_coupons" class="button orange" target="_blank">' . __('Coupon Admin', 'bnm') . '</a></div>';
        return $content;
    }

}