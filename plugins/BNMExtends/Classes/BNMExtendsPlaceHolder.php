<?php
/**
 * Gestisce Estensioni Placeholder 
 *
 * @package            BNMExtends
 * @subpackage         BNMExtendsPlaceHolder
 * @author             =undo= <g.fazioli@undolog.com>, <g.fazioli@saidmade.com>
 * @copyright          Copyright (C)2011 Saidmade Srl.
 * @created            18/07/12
 * @version            1.0
 *
 */
class BNMExtendsPlaceHolder {
	
	/*
	* Aggiunge script js custom in admin
	*/
	public static function adminEnqueueScripts() {
	    global $typenow;

        if ( $_GET['page'] == 'wpxph_menu_main') //script custom per backend
	        wp_enqueue_script( 'bnm-placeholder', kBNMExtendsURI . 'js/placeholder-admin.js', array(), kBNMExtendsVersion, true );
	}

    /**
     * Aggiunge script custom in frontend
     */
    public static function enqueueScripts(){

        if ( is_page('box-office-placeholder') ): //script custom per frontend
            wp_enqueue_script('jquery-ui-accordion','', array('jquery','jquery-ui-core'),'', true );
            wp_enqueue_script('jquery-ui-selectable','',array('jquery','jquery-ui-core'),'',true);
            wp_enqueue_script('jquery-ui-tabs','',array('jquery','jquery-ui-core'),'',true);
            wp_register_script('placeholder-boxoffice',kBNMExtendsURI .'js/placeholder-boxoffice.js',array('jquery','jquery-ui-core'),'',true);
            wp_enqueue_script('placeholder-boxoffice');

            wp_enqueue_script( 'bnm-placeholder', kBNMExtendsURI . 'js/placeholder-admin.js', array(), kBNMExtendsVersion, '', true );

            wp_localize_script( 'placeholder-boxoffice', 'wpphVars', array('ajaxUrl' => WPDKWordPressPlugin::url_ajax()) );

            wp_enqueue_script('datatable', kBNMExtendsURI .'js/jquery.dataTables.js', 'jquery', 1, true);
            wp_enqueue_script('datatable_editor', kBNMExtendsURI .'js/jquery.dataTablesEditors.js', 'jquery', 1, true);
            wp_enqueue_script('datatable_editable', kBNMExtendsURI .'js/jquery.jEditable.js',array('jquery','datatable'),'',true);


            //Stile Custom Interfaccia Box Office
            wp_enqueue_style('placeholder-boxoffice-style', kBNMExtendsURI .'css/placeholder-boxoffice-style.css');
            wp_enqueue_style('placeholder-boxoffice-ui', kBNMExtendsURI .'css/jquery-ui/jquery-ui.custom.boxoffice.placeholder.css');
            wp_enqueue_style('datatable-css', kBNMExtendsURI .'css/jquery-ui/jquery.dataTables.css');

        endif;
    }

    public static function deregisterScripts(){
        if ( is_page('box-office-placeholder') )
            wp_deregister_script('wpxph-frontend');
    }

    /**
     * @param $place
     * @param $id_who
     * @param $date_start
     * @param $date_expiry
     * @return mixed
     * Controlla che un posto sia effettivamente occupato da un utente
     */
    public static function isReservedBy($place,$id_who, $date_start, $date_expiry){
        global $wpdb;

        $tablename = WPPlaceholdersReservations::tableName();
        $tableplaces = WPPlaceholdersPlaces::tableName();


        /* Dates */
        if ( !is_null( $date_start ) ) {
            $date_start = sprintf( ' AND TIMESTAMP(date_start) >= TIMESTAMP("%s")', $date_start );
        }

        if ( !is_null( $date_expiry ) ) {
            $date_expiry = sprintf( ' AND TIMESTAMP(date_expiry) <= TIMESTAMP("%s")', $date_expiry );
        }

        $sql = <<<SQL
SELECT places.name AS place_name FROM {$tablename} AS reservations
LEFT JOIN {$tableplaces} AS places
ON reservations.id_place = places.id
WHERE 1
AND id_who = {$id_who}
AND reservations.is_place = {$place}
AND reservations.status = 'publish'
{$date_start}
{$date_expiry}
SQL;

        $result = $wpdb->get_results( $sql, ARRAY_A );
        return $result;
    }

    /**
     * @param $place
     * @return mixed
     *
     * Recupera il numero di posti per una data plosizione
     */
    public static function occupationByPlace( $place ){
        global $wpdb;

       // $tablename = WPPlaceholdersReservations::tableName();
        $tableplaces = WPPlaceholdersPlaces::tableName();


        $sql = <<<SQL
SELECT places.size AS place_size FROM {$tableplaces} AS places
WHERE 1
AND places.name = '{$place}'
SQL;

        $result = $wpdb->get_results( $sql, ARRAY_A );
        return $result;
    }


    /**
     * @param $place
     * @return mixed
     *
     * Recupera le note da una data posizione
     */
    public static function notesByPlace($place, $date_start, $date_expiry){
            global $wpdb;

            $table = WPPlaceholdersReservations::tableName();

            /* Dates */
            if ( !is_null( $date_start ) ) {
                $date_start = sprintf( ' AND TIMESTAMP(date_start) >= TIMESTAMP("%s")', $date_start );
            }

            if ( !is_null( $date_expiry ) ) {
                $date_expiry = sprintf( ' AND TIMESTAMP(date_expiry) <= TIMESTAMP("%s")', $date_expiry );
            }


            $sql = <<<SQL
SELECT note FROM {$table}
WHERE 1
AND id_place = {$place}
{$date_start}
{$date_expiry}
SQL;

        $result = $wpdb->get_results( $sql, ARRAY_A );
        return $result;
    }


    /**
     * @param $id_who
     * @param $date_start
     * @param $date_expiry
     * @return mixed
     *
     * Ritorna la lista delle prenotazioni per un utente e per uno spettacolo
     */
    public static function reservationsByWho($id_who, $date_start, $date_expiry){
        global $wpdb;

        $tablename = WPPlaceholdersReservations::tableName();
        $tableplaces = WPPlaceholdersPlaces::tableName();


        /* Dates */
        if ( !is_null( $date_start ) ) {
            $date_start = sprintf( ' AND TIMESTAMP(date_start) >= TIMESTAMP("%s")', $date_start );
        }

        if ( !is_null( $date_expiry ) ) {
            $date_expiry = sprintf( ' AND TIMESTAMP(date_expiry) <= TIMESTAMP("%s")', $date_expiry );
        }

        $sql = <<<SQL
SELECT places.name AS place_name FROM {$tablename} AS reservations
LEFT JOIN {$tableplaces} AS places
ON reservations.id_place = places.id
WHERE 1
AND id_who = {$id_who}
AND reservations.status = 'publish'
{$date_start}
{$date_expiry}
SQL;

        $result = $wpdb->get_results( $sql, ARRAY_A );
        return $result;
    }

    /**
     * @param $place
     * @return mixed
     * Recupera ID del posto dal nome
     */
    public static function placeByName($place){
        global $wpdb;

        $table = WPPlaceholdersPlaces::tableName();

        $sql = <<<SQL
SELECT id FROM {$table}
WHERE 1
AND name = "{$place}"
SQL;
        $result = $wpdb->get_results( $sql, ARRAY_N );
        return $result[0];
    }

    /**
     * @param $place
     * @param $date_start
     * @param $date_expiry
     * @param $note
     * @return mixed
     *
     * Aggiorna le note di una prenotazione posti
     */
    public static function updateNotes($place, $date_start, $date_expiry, $note){
            global $wpdb;

            $table = WPPlaceholdersReservations::tableName();

            /* Dates */
            if ( !is_null( $date_start ) ) {
                $date_start = sprintf( ' AND TIMESTAMP(date_start) >= TIMESTAMP("%s")', $date_start );
            }

            if ( !is_null( $date_expiry ) ) {
                $date_expiry = sprintf( ' AND TIMESTAMP(date_expiry) <= TIMESTAMP("%s")', $date_expiry );
            }


            $sql = <<<SQL
UPDATE {$table}
SET note = '{$note}'
WHERE 1
AND id_place = {$place}
{$date_start}
{$date_expiry}
SQL;

            $result = $wpdb->get_results( $sql );
            return $result;
        }


    /**
     * @param $place
     * @param $date_start
     * @param $date_expiry
     *
     * Elimina una prenotazione
     */
    public static function deleteReservation($place, $date_start, $date_expiry){
        global $wpdb;

        $table = WPPlaceholdersReservations::tableName();

        /* Dates */
        if ( !is_null( $date_start ) ) {
            $date_start = sprintf( ' AND TIMESTAMP(date_start) >= TIMESTAMP("%s")', $date_start );
        }

        if ( !is_null( $date_expiry ) ) {
            $date_expiry = sprintf( ' AND TIMESTAMP(date_expiry) <= TIMESTAMP("%s")', $date_expiry );
        }



        $sql = <<<SQL
DELETE FROM {$table}
WHERE 1
AND id_place = {$place}
{$date_start}
{$date_expiry}
SQL;

        $result = $wpdb->get_results( $sql );
        return $result;
    }

	/*
	* Aggiunge Autocomplete al backend Placeholder
	*/
	public static function addAutocompleteForm() {
	
	 	$sdf = array(
	        __( 'Seleziona uno spettacolo', 'bnm' )   => array(
	            array(
	                'type'    => WPDK_FORM_FIELD_TYPE_TEXT,
	                'name'    => 'product_title',
	                'data'    => array(
	                    'autocomplete_action'   => 'bnm_action_product_title',
	                    'autocomplete_target'   => 'id_product'
	                ),
	                'size'    => 64,
	                'label'   => __( 'Spettacolo', 'bnm' ),
	                'id'	  => 'wpph_product_title',
	                'value'   => $product_title == "" ? "" : $product_title
	            ),
	      
	        )
	    );
	

	    WPDKForm::htmlForm( $sdf );	
	}


    public static function prepareEnvironment($title = ""){
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
        $date_expiry = $mktime + 60 * 60 * 2;
        $date_start  = date( MYSQL_DATE_TIME, $date_start );
        $date_expiry = date( MYSQL_DATE_TIME, $date_expiry );

        $reservations         = WPPlaceholdersReservations::reservations( 1, $date_start, $date_expiry );
        $html_map_piano_terra = WPPlaceholdersReservations::planReservations( 1, $reservations, $map );

        $reservations         = WPPlaceholdersReservations::reservations( 2, $date_start, $date_expiry );
        $html_map_balconata   = WPPlaceholdersReservations::planReservations( 2, $reservations, $map );

        $bluenote_map = array(
            'pianoterra'    =>  $html_map_piano_terra,
            'balconata'     =>  $html_map_balconata
        );

        return $bluenote_map;

    }

    /**
     * @param string $title
     * @param string $id_product
     * @param int $count_variant
     *
     * Render della mappa per la prenotazione della cena frontend utente
     */
    public static function renderEnvironment( $title = "", $id_product = "", $count_variant = 0) {

        $bmap = self::prepareEnvironment($title);

        $html_map_piano_terra = $bmap['pianoterra'];
        $html_map_balconata = $bmap['balconata'];

        $html_select = <<< HTML
    <select name="" class="wpdk-form-select bnm-placeholder-select-environment">
        <option value="1">Piano Terra</option>
        <option value="2">Balconata</option>
    </select>
HTML;

        $open_dialog = __( 'Select a table for dinner', 'bnmextends' );
        $html        = <<< HTML
    <a data-id_product="{$id_product}" data-count_ticket="{$count_variant}" id="bnm-button-dinner-choice-{$id_product}" class="bnm-button-dinner-choice button orange" href="#">{$open_dialog}</a>
<input type="hidden" name="table_selected-{$id_product}" id="table_selected-{$id_product}" />
    <div data-id_product="{$id_product}" class="bnm-dialog-reservations" id="bnm-dialog-reservations-{$id_product}">
        {$html_select}
        <div class="bnm-placeholder-environment bnm-placeholder-environment-1">{$html_map_piano_terra}</div>
        <div style="display:none" class="bnm-placeholder-environment bnm-placeholder-environment-2">{$html_map_balconata}</div>
    </div>
HTML;
        return $title . $html;

    }


    /**
     * @param string $title
     * @param string $id_product
     * @param int $count_variant
     * @return string
     * Renderizza l'ambiente Box Office per Placeholder
     */
    public static function renderEnvironmentForBoxOffice( $title = "", $id_product = "", $count_variant = 0){

        $bmap = self::prepareEnvironment($title);
        $html_map_piano_terra = $bmap['pianoterra'];
        $html_map_balconata = $bmap['balconata'];


        $html_tabs = <<< HTML
    <ul class="wpdk-form-select bnm-placeholder-select-environment">
        <li><a href="#tabs-pt">Piano Terra</a></li>
        <li><a href="#tabs-b">Balconata</a></li>
    </ul>
HTML;

        $html  = <<< HTML
    <input type="hidden" name="table_selected-{$id_product}" id="table_selected-{$id_product}" />
    <div data-id_product="{$id_product}" class="bnm-dialog-reservations" id="bnm-dialog-reservations-{$id_product}">
        {$html_tabs}
        <div id="tabs-pt" class="bnm-placeholder-environment bnm-placeholder-environment-1">

            <div class="accordion-placeholder">
                <h3>Prenota tavoli <small class="close-box"><a href="" title="chiudi">[chiudi]</a></small></h3>
                <div class="accordion-content">
                    <div class="evidence"> <!-- Start Evidence -->
                            <p>Usa la mappa visuale per selezionare i singoli tavoli, oppure seleziona i gruppi di tavoli riepilogati di seguito</p>
                            <div class="group-placeholder">
                                <ol id="selectable">
                                    <li class="ui-widget-content">Terra Ant Sx</li>
                                    <li class="ui-widget-content">Terra Ant Dx</li>
                                    <li class="ui-widget-content">Terra Post Sx</li>
                                    <li class="ui-widget-content">Terra Post Dx</li>
                                    <li class="ui-widget-content">Fossa</li>
                                </ol>
                                <br class="clear"/>
                            </div>
                             <a title="Prenota" href="#" class="bnm-button-dinner-choice button orange" id="btn-reserve">Prenota</a>
                    </div> <!-- End Evidence -->

                    {$html_map_piano_terra}

                </div>
        </div><!--.accordion-placeholder #tabs-pt -->
     </div><!--#tabs-pt-->
    <div id="tabs-b" class="bnm-placeholder-environment bnm-placeholder-environment-2">
        <div class="accordion-placeholder-b">
                <h3>Prenota tavoli</h3>
                <div class="accordion-content">
                    <div class="evidence"> <!-- Start Evidence -->
                            <p>Usa la mappa visuale per selezionare i singoli tavoli, oppure seleziona i gruppi di tavoli riepilogati di seguito</p>
                            <div class="group-placeholder">
                                <ol id="selectable-balconata">
                                    <li class="ui-widget-content">Tavoli Centrali</li>
                                    <li class="ui-widget-content">Sgabelli Centrali</li>
                                    <li class="ui-widget-content">Sgabelli Sx</li>
                                    <li class="ui-widget-content">Sgabelli Dx</li>
                                </ol>
                                <br class="clear"/>
                            </div>
                             <a title="Prenota" href="#" class="bnm-button-dinner-choice button orange" id="btn-reserve-b">Prenota</a>
                    </div> <!-- End Evidence -->

                   {$html_map_balconata}

                </div>
    </div>
  </div>
 </div>
HTML;
        return   $html;


    }


    /**
     * @param $id_product
     * Renderizza il sommario
     */
    public static function summary( $id_product ){
        global $wpdb;

    /* Catch filtro spettacolo/prodotto */
    $product = null;

    $product    = get_post( $id_product );
    ?>
    <div class="summary" style="clear: both">
    <form action="" method="post">
    <?php


    /* Eseguo select se spettacolo selezionato */
    if ( !is_null( $product ) ) {

        $table_stats    = WPXSmartShopStats::tableName();
        $table_orders   = WPXSmartShopOrders::tableName();
        $table_coupons  = WPXSmartShopCoupons::tableName();
        $table_products = $wpdb->posts;
        $table_places   = WPPlaceholdersPlaces::tableName();

        $sql = <<<SQL
SELECT
res.id,
id_who AS user,
date_start,
date_expiry,
res.status,
places.name as places,
NULL as place_notes,
users_orders.display_name AS user_order_display_name,
users.user_email,
NULL as order_id,
NULL as order_note

FROM wpbn_wpph_reservations AS res

LEFT JOIN wpbn_users AS users ON id_who = users.ID
LEFT JOIN {$wpdb->users} AS users_orders ON orders.id_user_order = users_orders.ID
LEFT JOIN {$wpdb->usermeta} AS usermeta ON orders.id_user_order = usermeta.user_id AND usermeta.meta_key = '{$wpdb->prefix}_capabilities'
LEFT JOIN {$table_places} As places ON id_place = places.id

WHERE 1
AND id_who = 55
AND TIMESTAMP(date_start) >= TIMESTAMP("2012-10-01 21:00:00")
AND TIMESTAMP(date_expiry) <= TIMESTAMP("2012-10-01 23:00:00")
AND  res.status = 'publish'

UNION ALL

SELECT

NULL AS id,
orders.id_user_order AS user,
NULL AS date_start,
NULL AS date_expiry,
stats.status,
NULL as places,
stats.note as places_notes,

users_orders.display_name AS user_order_display_name,
users.user_email,

           orders.id,
           orders.note AS order_note

        FROM {$table_stats} AS stats

        LEFT JOIN {$table_orders} AS orders ON orders.id = stats.id_order

        LEFT JOIN {$table_products} AS products ON products.ID = stats.id_product
        LEFT JOIN {$wpdb->users} AS users ON orders.id_user = users.ID
        LEFT JOIN {$wpdb->users} AS users_orders ON orders.id_user_order = users_orders.ID
        LEFT JOIN {$wpdb->usermeta} AS usermeta ON orders.id_user_order = usermeta.user_id AND usermeta.meta_key = '{$wpdb->prefix}_capabilities'

        WHERE products.id = {$id_product}
        AND orders.status = 'confirmed'
        AND stats.status <> 'trash'
        AND stats.model = 'With Dinner Reservation'


        /* GROUP BY orders.bill_first_name, orders.bill_last_name, orders.id, coupons.uniqcode, stats.price_rule, stats.model */
        GROUP BY orders.id

        ORDER BY   user_order_display_name
SQL;


        $data = $wpdb->get_results( $sql, ARRAY_A );

        if ( !empty( $data ) ) : ?>

        <h3>Riepilogo <small>[ <?php echo $product->post_title ?> ]</small></h3>

        <div class="accordion-content">
            <table id="placeholder-summary" class="bnm-show-summary" width="100%" cellpadding="0" cellspacing="0">
                <thead>
                <tr>
                    <th>Spettatore</th>
                    <th>Ordine</th>
                    <th>Categoria</th>
                    <th>Coupon</th>
                    <th>Posti</th>
                    <th>Cena</th>
                    <th>Email</th>
                    <th>Tel</th>
                    <th>Note</th>
                </tr>
                </thead>

                 <tbody>
                <?php

                /* Export CSV */
                $export_columns = array(
                    'Spettatore',
                    'Ordine',
                    'Categoria',
                    'Coupon',
                    'Posti',
                    'Cena',
                    'Email',
                    'Tel',
                    'Note'
                );

                $export_buffer = '';
                foreach ( $data as $item ) :
                    $price_rule      = '';
                    $price_rule_code = $item['price_rule'];

                    if ( $price_rule_code == kWPSmartShopProductTypeRuleOnlinePrice ) {
                        $price_rule = 'Advance';
                    } elseif ( $price_rule == kWPSmartShopProductTypeRuleDatePrice ) {
                        $price_rule = 'Per data';
                    } elseif ( isset( $roles[$price_rule_code] ) ) {
                        $price_rule = $roles[$price_rule_code];
                    } elseif ( isset( $discountID[$price_rule_code] ) ) {
                        $price_rule = $discountID[$price_rule_code]['label'];
                    } else {
                        $price_rule = 'Sconosciuto';
                    }

                    $coupon = '';
                    if ( !is_null( $item['coupon_product_maker_name'] ) ) {
                        $coupon = sprintf( '%s<br/>(%s)', $item['coupon_uniqcode'], $item['coupon_product_maker_name'] );
                    }

                    $dinner = ( $item['model'] == BNMEXTENDS_WITH_DINNER_RESERVATION_KEY ) ? 'Cena' : '';

                    /* Comulative branch */
                    if ( $item['model'] == BNMEXTENDS_2_ADULTS_2_CHILDREN_KEY ) {
                        $item['posti'] *= 4;
                        $dinner = BNMEXTENDS_2_ADULTS_2_CHILDREN;
                    } elseif ( $item['model'] == BNMEXTENDS_2_ADULTS_1_CHILD_KEY ) {
                        $item['posti'] *= 3;
                        $dinner = BNMEXTENDS_2_ADULTS_1_CHILD;
                    } elseif ( $item['model'] == BNMEXTENDS_2_ADULTS_KEY ) {
                        $item['posti'] *= 2;
                        $dinner = BNMEXTENDS_2_ADULTS;
                    }


                    //$has_invoice = BNMExtendsInvoices::getInvoiceByOrder( $item['id_order'] );

                    $export_buffer .= sprintf( '"%s","%s","%s","%s","%s","%s","%s","%s","%s","%s"',
                        sprintf( '%s %s', $item['bill_last_name'], $item['bill_first_name'] ),
                        sprintf( "# %s - %s\r\n%s", $item['id_order'], $item['track_id'], ( mysql2date( __( 'm/d/Y H:i', WPXSMARTSHOP_TEXTDOMAIN ), $item['order_datetime'] ) ) ),
                        sprintf( '%s', $price_rule ),
                        sprintf( '%s', $coupon ),
                        sprintf( '%s', $item['posti'] ),
                        sprintf( "%s\r\n%s", $dinner, $item['note'] ),
                        sprintf( '%s', $item['bill_email'] ),
                        sprintf( '%s', $item['bill_phone'] ),
                        sprintf( '%s', $item['order_note'] ) );
                    $export_buffer .= WPDK_CRLF;


                    ?>
                <tr>
                    <td><?php printf( '%s %s', $item['bill_last_name'], $item['bill_first_name'] ); ?></td>
                    <td><?php printf( '# <span style="color:#789;">%s</span> - %s%s<br/>%s', $item['id_order'], $item['track_id'], $transaction_id, ( mysql2date( __( 'm/d/Y H:i', WPXSMARTSHOP_TEXTDOMAIN ), $item['order_datetime'] ) ) );?></td>
                    <td><?php printf( '%s', $price_rule ) ?></td>
                    <td><?php printf( '%s', $coupon ) ?></td>
                    <td><?php printf( '%s', $item['posti'] ) ?></td>
                    <td><?php printf( '%s<br/><strong>%s</strong>', $dinner, $item['note'] ) ?></td>
                    <td><?php printf( '%s', $item['bill_email'] ) ?></td>
                    <td><?php printf( '%s', $item['bill_phone'] ) ?></td>
                    <td><?php printf( '%s', $item['order_note'] ) ?></td>
                </tr>
                    <?php endforeach;

                $export_columns_row = sprintf( '"%s"', join( '","', $export_columns ) ) . WPDK_CRLF;
                $export             = $export_columns_row . $export_buffer;
                //set_transient( 'wpxss_frontend_summary_csv', $export );
                ?>
                </tbody>
            </table>
          </div>
         </form>
        </div>
     <?php endif;
    }
  }


    /*
     * Ordina i posti
     */
	public function doSortPlaces($data) {

		if ( $_GET['page'] == 'wpxph_menu_main') 
			BNMExtendsPlaceHolder::aasort($data, 'place');

		return $data;
	}
	
	/*
	* Permette un ordinamento custom degli item in combo box (vedi riferimento all classe Reservations )
	*/
	public function doSortComboPlaces($data) {
			
			$data2 = array();
			
			foreach ($data as $key => $value) {
				$data2[$key] = ereg_replace("[^0-9]", "", $value );
			}
						
			asort($data2);
			
			foreach ($data as $key => $value) {
				$data2[$key] = $data[$key];
			}
			//LOG
			//BNMExtends::logErrors($data2, __METHOD__, __LINE__);
	
			return $data2;
		}
	
	
	private function sortPlaces( $place_a, $place_b ) {
		
		$numbers_a = ereg_replace("[^a-zA-Z\s]", "", $place_a); 
		$numbers_b = ereg_replace("[^a-zA-Z\s]", "", $place_b); 
		
		if ($numbers_a == $numbers_b) {
		       return 0;
		   }
		
	  	// Send final sort direction to usort
	  	return  ($numbers_a < $numbers_b) ? 1 : -1;
	}

    /*
	* Funzione di ordinamento per gli arrai pluridimensionali come il $data proveniente dal DB
	*/
	private function aasort (&$array, $key) {
	
	    $sorter=array();
	    $ret=array();
	    reset($array);
	    foreach ($array as $ii => $va) {
	        //$sorter[$ii]=$va[$key];
	        $sorter[$ii] = ereg_replace("[^0-9]", "", $va[$key] );
	    }

	    asort($sorter);
	    foreach ($sorter as $ii => $va) {
	        $ret[$ii]=$array[$ii];
	    }
	    $array=$ret;
	}

}


?>