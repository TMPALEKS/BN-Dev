<?php
/**
 * @class              WPPlaceholdersPlaces
 * @description        Gestisce i songoli posti
 *
 * @package            wpx Placeholders
 * @subpackage         WPPlaceholdersPlaces
 * @author             =undo= <g.fazioli@undolog.com>, <g.fazioli@wpxtre.me>
 * @copyright          Copyright (c) 2012 wpXtreme, Inc.
 * @link               http://wpxtre.me
 * @created            03/04/12
 * @version            1.0.0
 *
 * @filename           wpxph-places
 *
 * @todo               Riallineare con l'eredità della WPDKDBTable
 *
 */

require_once( 'wpxph-places-viewcontroller.php' );

class WPPlaceholdersPlaces  {

    /**
     * Restituisce il record di un posto
     *
     * @static
     *
     * @param int|string $place ID o nome del place da restituire
     * @param string     $status Per default uguale a 'publish', se false restituisce qualsiasi status
     *
     * @return mixed
     */
    public static function place( $place, $status = 'publish' ) {
        global $wpdb;
        

		if ( is_object( $place ) && is_a( $place, 'stdClass' ) ) {
            return $place;
        } else {
        	$where    = sprintf( ' AND name = "%s"', $place );
        	$order_by = 'name';
        }
        
        $places = self::tableName();

        $where_status = '';
        if ( !empty( $status ) && $status !== false ) {
            $where_status = sprintf( ' AND status = "%s"', $status );
        }

        $sql    = <<< SQL
SELECT * FROM {$places}
WHERE 1
{$where}
{$where_status}
ORDER BY {$order_by}
SQL;

        $result = $wpdb->get_row( $sql );
        return $result;
    }

    public static function read( $id = null, $id_id = 'id', $orderby = '', $where = '', $output = OBJECT ) {
        global $wpdb;

        $table = self::tableName();

        $where_cond = 'WHERE 1';

        if ( !is_null( $id ) ) {
            if ( is_numeric( $id ) ) {
                $where_cond = sprintf( 'WHERE %s = %s', $id_id, $id );
            } elseif ( is_string( $id ) ) {
                $where_cond = sprintf( 'WHERE %s = \'%s\'', $id_id, $id );
            }
        }

        if ( !empty( $where ) ) {
            $where_cond = sprintf( '%s %s', $where_cond, $where );
        }

        $sql = <<< SQL
        SELECT * FROM `{$table}`
        {$where_cond}
        {$orderby}
SQL;
        if ( !is_null( $id ) ) {
            $result = $wpdb->get_row( $sql, $output );
        } else {
            $result = $wpdb->get_results( $sql, $output );
        }
        return $result;
    }

    public static function count() {
        global $wpdb;

        $table = self::tableName();

        $sql = <<< SQL
		SELECT COUNT(*) AS count
		FROM `{$table}`
SQL;
        return absint( $wpdb->get_var( $sql ) );
    }

    public static function statusesWithCount() {
        global $wpdb;

        $statuses = self::arrayStatuses();
        $table    = self::tableName();

        $sql    = <<< SQL
SELECT DISTINCT(`status`),
       COUNT(*) AS count
FROM `{$table}` GROUP BY `status`
SQL;
        $result = $wpdb->get_results( $sql, ARRAY_A );

        foreach ( $result as $status ) {
            if ( !empty( $status['status'] ) ) {
                $statuses[$status['status']]['count'] = $status['count'];
            }
        }

        $statuses['all']['count'] = self::count();

        return $statuses;
    }

    // -----------------------------------------------------------------------------------------------------------------
    // Static values
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Restituisce il nome della tabella Environment
     *
     * @package    wpx Placeholders
     * @subpackage WPPlaceholdersPlaces
     * @since      1.0.0
     *
     * @static
     * @return string Nome della tabella Environment comprensivo di prefisso WordPress
     */
    public static function tableName() {
        global $wpdb;
        return sprintf( '%s%s', $wpdb->prefix, kWPPlaceholdersPlacesTableName );
    }

    /**
     * Modulo nello standard SFD per l'inserimento ed edit
     *
     * @package    wpx Placeholders
     * @subpackage WPPlaceholdersPlaces
     * @since      1.0.0
     *
     * @static
     *
     * @param int $id ID del place
     *
     * @return array
     */
    public static function fields( $id = null ) {
        $place = null;

        if ( !is_null( $id ) ) {
            $place = self::read( absint( $id ) );
        }
        $fields = array(
            __( 'Place information', WPXPLACEHOLDERS_TEXTDOMAIN )   => array(
                array(
                    array(
                        'type'  => WPDK_FORM_FIELD_TYPE_TEXT,
                        'name'  => 'name',
                        'label' => __( 'Name', WPXPLACEHOLDERS_TEXTDOMAIN ),
                        'value' => is_null( $place ) ? '' : $place->name
                    ),
                    array(
                        'type'  => WPDK_FORM_FIELD_TYPE_NUMBER,
                        'name'  => 'size',
                        'label' => __( 'Seating capacity', WPXPLACEHOLDERS_TEXTDOMAIN ),
                        'value' => is_null( $place ) ? '' : $place->size
                    ),
                ),
                array(
                    array(
                        'type'    => WPDK_FORM_FIELD_TYPE_SELECT,
                        'name'    => 'id_environment',
                        'label'   => __( 'Environment', WPXPLACEHOLDERS_TEXTDOMAIN ),
                        'options' => WPPlaceholdersEnvironments::arrayEnvironmentForSDF(),
                        'value'   => !is_null( $place ) ? $place->id_environment : ''
                    ),
                ),

                array(
                    array(
                        'type'  => WPDK_FORM_FIELD_TYPE_TEXT,
                        'name'  => 'description',
                        'label' => __( 'Description', WPXPLACEHOLDERS_TEXTDOMAIN ),
                        'value' => is_null( $place ) ? '' : $place->description
                    )
                ),
            )
        );
        return $fields;
    }

    /**
     * Restituisce l'elenco degli stati
     *
     * @package    wpx Placeholders
     * @subpackage WPPlaceholdersPlaces
     * @since      1.0.0
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

    /**
     * @static
     * @return array
     */
    public static function arrayStatusesForSDF() {
        $statuses = self::arrayStatuses();
        $result   = array();
        foreach ( $statuses as $key => $status ) {
            $result[$key] = $status['label'];
        }
        unset( $result['all'] );
        unset( $result['trash'] );
        return $result;
    }


    /**
     * Elenco dei place per form SDF
     *
     * @static
     * @return array
     */
    public static function arrayPlacesForSDF() {
        $places  = self::read( null, 'id', 'ORDER BY name', 'AND status = \'publish\'' );
        $results = array();
        foreach ( $places as $place ) {
            $results[$place->id] = $place->name;
        }
        return $results;
    }

    // -----------------------------------------------------------------------------------------------------------------
    // Database
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Crea o aggiorna (esegue un delta) della tabella.
     * Questo metodo viene chiamato (di solito) all'attivazione del plugin, quindi una sola volta.
     *
     * @package    wpx Placeholders
     * @subpackage WPPlaceholdersPlaces
     * @since      1.0.0
     *
     * @static
     *
     */
    public static function updateTable() {
        if ( !function_exists( 'dbDelta' ) ) {
            require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        }
        $dbDeltaTableFile = sprintf( '%s%s', kWPPlaceholdersDirectoryPath, kWPPlaceholdersPlacesTableFilename );
        $file             = file_get_contents( $dbDeltaTableFile );
        $sql              = sprintf( $file, self::tableName() );
        @dbDelta( $sql );
    }

    // -----------------------------------------------------------------------------------------------------------------
    // Database CRUD
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Aggiunge un record
     *
     * @package    wpx Placeholders
     * @subpackage WPPlaceholdersPlaces
     * @since      1.0.0
     *
     * @static
     *
     * @return mixed
     */
    public static function create() {

        $values = array(
            'id_environment' => absint( $_POST['id_environment'] ),
            'size'           => absint( $_POST['size'] ),
            'name'           => esc_attr( $_POST['name'] ),
            'description'    => esc_attr( $_POST['description'] ),
        );

        $result = parent::create( $values );

        return $result;
    }

    /**
     * Aggiorna un Place
     *
     * @package    wpx Placeholders
     * @subpackage WPPlaceholdersPlaces
     * @since      1.0.0
     *
     * @static
     * @return mixed
     */
    public static function update() {

        $values = array(
            'id_environment' => absint( $_POST['id_environment'] ),
            'size'           => absint( $_POST['size'] ),
            'name'           => esc_attr( $_POST['name'] ),
            'description'    => esc_attr( $_POST['description'] ),
        );

        $result = parent::update( absint( $_POST['id'] ), $values );
        return $result;
    }

    // -----------------------------------------------------------------------------------------------------------------
    // UI Aux
    // -----------------------------------------------------------------------------------------------------------------

    public static function imagePlaceholder() {
        $src  = WPXPLACEHOLDERS_URL_IMAGES . 'wp-placeholders-bn-55x55.png';
        $html = <<< HTML
    <img src="{$src}" alt="Placeholder" title="" />
HTML;
        return $html;
    }


}
