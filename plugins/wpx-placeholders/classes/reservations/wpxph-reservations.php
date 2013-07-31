<?php
/**
 * @class              WPPlaceholdersReservations
 *
 * Modello di gestione delle prenotazioni
 *
 * @package            wpx Placeholders
 * @author             =undo= <g.fazioli@wpxtre.me>
 * @copyright          Copyright (c) 2012 wpXtreme, Inc.
 * @created            03/04/12
 * @version            1.0.0
 *
 */

require_once( 'wpxph-reservations-viewcontroller.php' );

class WPPlaceholdersReservations extends WPDKDBTable  {

    // -----------------------------------------------------------------------------------------------------------------
    // Static values
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Restituisce il nome della tabella Environment
     *
     * @static
     * @return string Nome della tabella Environment comprensivo di prefisso WordPress
     */
    public static function tableName() {
        global $wpdb;
        return sprintf( '%s%s', $wpdb->prefix, kWPPlaceholdersReservationsTableName );
    }

    /// Campi form in formato SDF
    /**
     * Modulo nello standard SFD per l'inserimento e l'edit
     *
     * @static
     *
     * @param int $id ID del place
     *
     * @return array
     */
    public static function fields( $id = null ) {
        $reservation = null;

        if ( !is_null( $id ) ) {
            $reservation = parent::record( self::tableName(), absint( $id ) );
        }

        /**
         * @filters
         *
         * @param  array $sdf         Elemento in formato SDF
         * @param object $reservation Record reservation
         */
        $who = apply_filters( 'wpph_reservation_edit_id_who', array(), $reservation );
        
        $options = WPPlaceholdersPlaces::arrayPlacesForSDF();

        $options = apply_filters( 'wpph_reservation_options_custom_sort', $options ); //Trigger Custom sort on Combo values

        $fields = array(
            __( 'Reservation information', WPXPLACEHOLDERS_TEXTDOMAIN )   => array(
                array(
                    array(
                        'type'    => WPDK_FORM_FIELD_TYPE_SELECT,
                        'name'    => 'id_place',
                        'label'   => __( 'Place', WPXPLACEHOLDERS_TEXTDOMAIN ),
                        'options' => $options,
                        'value'   => is_null( $reservation ) ? '' : $reservation->id_place
                    ),
                ),
                array(
                    array(
                        'type'  => WPDK_FORM_FIELD_TYPE_DATETIME,
                        'name'  => 'date_start',
                        'label' => __( 'Start date', WPXPLACEHOLDERS_TEXTDOMAIN ),
                        'value' => is_null( $reservation ) ? '' : WPDKDateTime::formatFromFormat( $reservation->date_start, 'Y-m-d H:i:s', __( 'm/d/Y H:i', WPXPLACEHOLDERS_TEXTDOMAIN ) )
                    ),
                    array(
                        'type'  => WPDK_FORM_FIELD_TYPE_DATETIME,
                        'name'  => 'date_expiry',
                        'label' => __( 'Expiry date', WPXPLACEHOLDERS_TEXTDOMAIN ),
                        'value' => is_null( $reservation ) ? '' : WPDKDateTime::formatFromFormat( $reservation->date_expiry, 'Y-m-d H:i:s', __( 'm/d/Y H:i', WPXPLACEHOLDERS_TEXTDOMAIN ) )
                    ),
                ),
                $who
            )
        );
        return $fields;
    }

    /// Elenco stati
    /**
     * Restituisce l'elenco degli stati
     *
     * @static
     * @return array
     */
    public static function arrayStatuses() {

        $statuses = array(
            'all'                            => array(
                'label' => __( 'All', WPXPLACEHOLDERS_TEXTDOMAIN ),
                'count' => 0
            ),
            'publish'                        => array(
                'label' => __( 'Published', WPXPLACEHOLDERS_TEXTDOMAIN ),
                'count' => 0
            ),
            'trash'                          => array(
                'label' => __( 'Trashed', WPXPLACEHOLDERS_TEXTDOMAIN ),
                'count' => 0
            )
        );
        return $statuses;
    }

    /// Tipo di status
    /**
     * Restituisce un array con il tipo di status, la sua label e la count sul database
     *
     * @static
     * @retval array
     */
    public static function statuses() {
    	$statuses = parent::statusesWithCount( self::tableName(), self::arrayStatuses() );
    	//LOG
    	BNMExtends::logErrors($statuses, __METHOD__, __LINE__);
        return $statuses ;
    }

    /**
     * Restituisce un array con chiave uguale al nome del tavolo da usare in congiunzione con il metodo
     * self::planReservations()
     *
     * @static
     * @access     internal
     *
     * @param array $reservations Elenco delle prenotazioni ritornato da WPPlaceholdersReservations::reservations()
     *
     * @return array Elenco key pait con chiave uguale al nome del tavolo e seguito da un array informativo, usato per
     *             costruire la mappa
     */
    public static function arrayReseravtionsForMap( $reservations ) {
        $results = array();

        foreach ( $reservations as $reservation ) {
            $time_start = '';
            if( isset( $reservation->date_start) ) {
                $time_part  = explode( ' ', $reservation->date_start );
                $time_start = $time_part[1];
            }

            $results[$reservation->name] = array(
                'environment_description' => $reservation->description,
                'id_reserved'             => $reservation->id_reserved,
                'id_reserved_by'          => $reservation->id_reserved_by,
                'size'                    => $reservation->size,
                'time_start'              => $time_start
            );
        }

        return $results;
    }

    /// Numero prenotazioni con data e ora
    /**
     * Restituisce il numero delle prenotazioni per una specifica data e ora. Verifica quindi che ci siano posti
     * prenotati all'interno di una particolare data.
     *
     * @static
     *
     * @param string $datetime Data e ora in formato mySQL Y-m-d H:i
     *
     * @return int Numero delle prenotazioni
     */
    public static function countWithDatetime( $datetime ) {
        global $wpdb;
        $table = self::tableName();

        $sql = <<< SQL
SELECT COUNT(*) AS count
FROM `{$table}`
WHERE TIMESTAMP('{$datetime}') >= TIMESTAMP(date_start)
AND TIMESTAMP('{$datetime}') <= TIMESTAMP(date_expiry)
AND status = 'publish'
SQL;
        return absint( $wpdb->get_var( $sql ) );

    }


    // -----------------------------------------------------------------------------------------------------------------
    // Database
    // -----------------------------------------------------------------------------------------------------------------

    /// Creazione e aggiornamento tabello in delta
    /**
     * Crea o aggiorna (esegue un delta) della tabella.
     * Questo metodo viene chiamato (di solito) all'attivazione del plugin, quindi una sola volta.
     *
     * @static
     *
     */
    public static function updateTable() {
        if ( !function_exists( 'dbDelta' ) ) {
            require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        }
        $dbDeltaTableFile = sprintf( '%s%s', kWPPlaceholdersDirectoryPath, kWPPlaceholdersReservationsTableFilename );
        $file             = file_get_contents( $dbDeltaTableFile );
        $sql              = sprintf( $file, self::tableName() );
        @dbDelta( $sql );
    }


    /**
     * Recupera un'insieme di prenotazioni
     *
     * @static
     *
     * @param int|string $id_environment ID dell'ambiente o la sua descrizione
     * @param string     $date_start     MYSQL date time
     * @param string     $date_expiry    MYSQL date time
     * @param string     $status         Stato, default 'publish'
     *
     * @return array Insieme di prenotazioni
     */
    public static function reservations( $id_environment = null, $date_start = null, $date_expiry = null, $status = 'publish' ) {
        global $wpdb;

        $where = 'WHERE 1 AND places.status = "publish"';
        $join_where = '';

        $places       = WPPlaceholdersPlaces::tableName();
        $reservations = self::tableName();
        $environments = WPPlaceholdersEnvironments::tableName();

        /* Environment */
        if ( is_numeric( $id_environment ) ) {
            $where .= sprintf( ' AND places.id_environment = %s', $id_environment );
        } elseif ( !empty( $id_environment ) ) {
            $where .= sprintf( ' AND env.description = "%s"', $id_environment );
        }

        /* Dates */
        if ( !is_null( $date_start ) ) {
            $join_where .= sprintf( ' AND TIMESTAMP(reservations.date_start) >= TIMESTAMP("%s")', $date_start );
        }

        if ( !is_null( $date_expiry ) ) {
            $join_where .= sprintf( ' AND TIMESTAMP(reservations.date_expiry) <= TIMESTAMP("%s")', $date_expiry );
        }

        $sql     = <<< SQL
SELECT
    places.id,
    places.name,
    places.size,
    env.description,
    reservations.date_start AS date_start,
    reservations.date_expiry AS date_expiry,
    reservations.id_place AS id_reserved,
    reservations.id_who AS id_reserved_by
FROM {$places} AS places
LEFT JOIN {$reservations} AS reservations ON (places.id = reservations.id_place AND reservations.status = '{$status}' {$join_where})
LEFT JOIN {$environments} AS env ON env.id = places.id_environment
{$where}
SQL;

        $results = $wpdb->get_results( $sql );
        
        //LOG
        BNMExtends::logErrors($results, __METHOD__, __LINE__);
        
        return $results;
    }

    // -----------------------------------------------------------------------------------------------------------------
    // Database CRUD
    // -----------------------------------------------------------------------------------------------------------------

    /// Crea una prenotazione
    /**
     * Aggiunge una prenotazione
     *
     * @static
     *
     * @return mixed
     */
    public static function create() {
        global $wpdb;

        if ( empty( $_POST['date_start'] ) || empty( $_POST['date_expiry'] ) ) {
            $message = __( 'Dates not set', WPXPLACEHOLDERS_TEXTDOMAIN );
            $error   = new WP_Error( 'wpph_error-dates_not_set_in_reservation', $message );
            return $error;
        }

        $values = array(
            'id_place'    => absint( $_POST['id_place'] ),
            'date_start'  => WPDKDateTime::dateTime2MySql( $_POST['date_start'], __( 'm/d/Y H:i', WPXPLACEHOLDERS_TEXTDOMAIN ) ),
            'date_expiry' => WPDKDateTime::dateTime2MySql( $_POST['date_expiry'], __( 'm/d/Y H:i', WPXPLACEHOLDERS_TEXTDOMAIN ) ),
        );

        if ( isset( $_POST['id_who'] ) && !empty( $_POST['id_who'] ) ) {
            $values['id_who'] = absint( $_POST['id_who'] );
        }

        //var_dump($values); die();

        //LOG
        WPDKWatchDog::watchDog( __METHOD__," Inserimento nuovo Item");

        $result = $wpdb->insert( self::tableName(), $values );
        
        //LOG
        WPDKWatchDog::watchDog( __METHOD__," Inserito nuovo Item: " . $result);

        return $result;
    }

    /**
     * Prenota un place per una specifica data e uno specifico utente
     *
     * @static
     *
     * @param int|string     $place       ID o nome del place
     * @param int|string     $date_start  Data in formato MYSQL o timestamp
     * @param int|string     $date_expiry Data in formato MYSQL o timestamp
     * @param int            $id_who      Identificatore di chi a prenotato, di solito un id utente
     *
     * @return mixed
     */
    public static function  doReservation( $place, $date_start, $date_expiry, $id_who = 0, $note = "" ) {
        global $wpdb;

        $place    = WPPlaceholdersPlaces::place( $place );
        $id_place = $place->id;

        /* Controlla se le date sono passate in timestamp */
        if ( is_numeric( $date_start ) ) {
            $date_start = date( MYSQL_DATE_TIME, $date_start );
        }

        if ( is_numeric( $date_expiry ) ) {
            $date_expiry = date( MYSQL_DATE_TIME, $date_expiry );
        }

        $values = array(
            'id_place'    => $id_place,
            'date_start'  => $date_start,
            'date_expiry' => $date_expiry,
            'id_who'      => $id_who,
            'note'        => $note
        );



        //LOG
        WPDKWatchDog::watchDog(__CLASS__ . " | " . __METHOD__," Inserimento nuovo Item");

        $result = $wpdb->insert( self::tableName(), $values );
        
        //LOG
        WPDKWatchDog::watchDog(__CLASS__ . " | " . __METHOD__," Inserito nuovo Item: " . $result);

        return $result;
    }

    /**
     * Aggiorna una prenotazione
     *
     * @static
     * @return mixed
     */
    public static function update() {

        global $wpdb;

        if ( empty( $_POST['date_start'] ) || empty( $_POST['date_expiry'] ) ) {
            $message = __( 'Dates not set', WPXPLACEHOLDERS_TEXTDOMAIN );
            $error   = new WP_Error( 'wpph_error-dates_not_set_in_reservation', $message );
            return $error;
        }

        $values = array(
            'id_place'     => absint( $_POST['id_place'] ),
            'date_start'   => WPDKDateTime::dateTime2MySql( $_POST['date_start'], __( 'm/d/Y H:i', WPXPLACEHOLDERS_TEXTDOMAIN ) ),
            'date_expiry'  => WPDKDateTime::dateTime2MySql( $_POST['date_expiry'], __( 'm/d/Y H:i', WPXPLACEHOLDERS_TEXTDOMAIN ) ),
        );

        if ( isset( $_POST['id_who'] ) && !empty( $_POST['id_who'] ) ) {
            $values['id_who'] = absint( $_POST['id_who'] );
        }

        $where = array(
            'id' => absint( $_POST['id'] )
        );


        if ( empty( $formats ) ) {
            $result = $wpdb->update( self::tableName(), $values, $where );
        } else {
            $where_formats = array( '%d' );
            $result        = $wpdb->update( self::tableName(), $values, $where, $formats, $where_formats );
        }
		
		//LOG
		BNMExtends::logErrors($result, __METHOD__, __LINE__);

        return $result;
    }

    // -----------------------------------------------------------------------------------------------------------------
    // UI Helper
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Restituisce l'HTML con la mappa 'tabellare' dell'ambiente
     *
     * @static
     *
     * @param int   $id_envirorment ID dell'ambiente
     * @param array $reservations   Elenco delle prenotazione, il ritorno di self::reservations();
     * @param array $map            Array a griglia che ddescrive la mappa 'grafica'
     *
     * @return string HTML con la mappa tabellare
     */
    public static function planReservations( $id_envirorment, $reservations, $map ) {

        $envirorment = WPPlaceholdersEnvironments::read( $id_envirorment );

        /* Converte in array key pair per hash sui nomi tavoli */
        $reservations = self::arrayReseravtionsForMap( $reservations );


        /* Punta all'ambiente corretto */
        $map = $map[$id_envirorment];

        $html_rows = '';
        foreach ( $map as $row ) {
            $html_cel = '';
            foreach ( $row as $cel ) {
                if ( !empty( $cel ) ) {
                    /* Tavolo occupato */
                    if ( isset( $reservations[$cel] ) && !is_null( $reservations[$cel]['id_reserved'] ) ) {
                        $title = $cel . ' - ' .  __( 'Reserved', WPXPLACEHOLDERS_TEXTDOMAIN );
                        $inner = sprintf( '<div title="%s" data-environment="%s" data-environment_description="%s" data-place_name="%s" class="wpph-plan-reservations-table wpph-plan-reservations-table-size-%s wpph-plan-reservations-table-busy wpdk-tooltip"></div>', $title, $id_envirorment, $reservations[$cel]['environment_description'], $cel, $reservations[$cel]['size'] );
                    } else {
                        if ( $cel == 'RSRV-2' ) {
                            $title = __( 'Not Available', WPXPLACEHOLDERS_TEXTDOMAIN );
                            $description = $envirorment->description;
                            $size        = 2;
                            $inner       = <<< HTML
<div data-environment="{$id_envirorment}"
     data-environment_description="{$description}"
     data-place_name="{$cel}"
     data-place_size="{$size}"
     title="{$title}"
     class="wpph-plan-reservations-table wpph-plan-reservations-table-size-{$size} wpph-plan-place-reserved wpdk-tooltip"></div>
HTML;
                        } elseif( isset( $reservations[$cel] )) {
                            /* Libero */
                            $title = $cel . ' - ' . __( 'Available', WPXPLACEHOLDERS_TEXTDOMAIN);
                            $description = $reservations[$cel]['environment_description'];
                            $size        = $reservations[$cel]['size'];
                            $inner       = <<< HTML
    <div data-environment="{$id_envirorment}"
         data-environment_description="{$description}"
         data-place_name="{$cel}"
         data-place_size="{$size}"
         title="{$title}"
         class="wpph-plan-reservations-table wpph-plan-reservations-table-size-{$size} wpph-plan-reservations-table-free wpdk-tooltip"></div>
HTML;
                        }
                    }
                    $html_cel .= sprintf( '<td class="wpph-plan-reservations-cell_%s">%s</td>', $cel, $inner );
                } else {
                    /* Spazio vuoto x griglia */
                    $html_cel .= '<td class="wpph-plan-reservations-cell-empty"></td>';
                }
            }
            $html_rows .= sprintf( '<tr>%s</tr>', $html_cel );
        }

        $html = <<< HTML
    <table class="wpph-plan-reservations" width="100%">
        {$html_rows}
    </table>
HTML;
        return $html;
    }

}
