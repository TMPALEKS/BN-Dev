<?php
/**
 * Gestisce gli ambienti
 *
 * @package            wpx Placeholders
 * @subpackage         WPPlaceholdersEnvironments
 * @author             =undo= <g.fazioli@undolog.com>, <g.fazioli@wpxtre.me>
 * @copyright          Copyright (c) 2012 wpXtreme, Inc.
 * @link               http://wpxtre.me
 * @created            03/04/12
 * @version            1.0.0
 *
 */

require_once( 'wpxph-environments-viewcontroller.php' );

class WPPlaceholdersEnvironments  {

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
     * @subpackage WPPlaceholdersEnvironments
     * @since      1.0.0
     *
     * @static
     * @return string Nome della tabella Environment comprensivo di prefisso WordPress
     */
    public static function tableName() {
        global $wpdb;
        return sprintf( '%s%s', $wpdb->prefix, kWPPlaceholdersEnvironmentsTableName );
    }

    /**
     * Restituisce l'url dell'immagine da usare come placeholder quando non se ne trova una
     *
     * @todo Aggiungere diverse misure
     *
     * @package    wpx Placeholders
     * @subpackage WPPlaceholdersEnvironments
     * @since      1.0.0
     *
     * @static
     * @return string
     */
    public static function srcPlaceholder( $size = null ) {
        $src = WPXPLACEHOLDERS_URL_IMAGES . 'wp-placeholders-environments-bn-55x55.png';
        return $src;
    }

    /**
     * Modulo nello standard SFD per l'inserimento ed edit
     *
     * @package    wpx Placeholders
     * @subpackage WPPlaceholdersEnvironments
     * @since      1.0.0
     *
     * @static
     *
     * @param int $id ID del place
     *
     * @return array
     */
    public static function fields( $id = null ) {
        $evironment = null;

        if ( !is_null( $id ) ) {
            $evironment = self::read( absint( $id ) );
        }
        $fields = array(
            __( 'Environment information', WPXPLACEHOLDERS_TEXTDOMAIN )   => array(
                array(
                    array(
                        'type'  => WPDK_FORM_FIELD_TYPE_TEXT,
                        'name'  => 'description',
                        'label' => __( 'Description', WPXPLACEHOLDERS_TEXTDOMAIN ),
                        'value' => is_null( $evironment ) ? '' : $evironment->description
                    ),
                ),
            )
        );
        return $fields;
    }


    /**
     * Restituisce l'elenco degli stati
     *
     * @package    wpx Placeholders
     * @subpackage WPPlaceholdersEnvironments
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


    // -----------------------------------------------------------------------------------------------------------------
    // Database
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Crea o aggiorna (esegue un delta) della tabella.
     * Questo metodo viene chiamato (di solito) all'attivazione del plugin, quindi una sola volta.
     *
     * @package    wpx Placeholders
     * @subpackage WPPlaceholdersEnvironments
     * @since      1.0.0
     *
     * @static
     *
     */
    public static function updateTable() {
        if ( !function_exists( 'dbDelta' ) ) {
            require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        }
        $dbDeltaTableFile = sprintf( '%s%s', kWPPlaceholdersDirectoryPath, kWPPlaceholdersEnvironmentsTableFilename );
        $file             = file_get_contents( $dbDeltaTableFile );
        $sql              = sprintf( $file, self::tableName() );
        @dbDelta( $sql );
    }

    /**
     * Restituisce il record di un ambiente
     *
     * @package    wpx Placeholders
     * @subpackage WPPlaceholdersEnvironments
     * @since      1.0.0
     *
     * @static
     *
     * @param int|string $id_environment ID dell'ambiente o sua descrizione
     * @param string     $status
     *
     * @return mixed
     */
    public static function environment( $id_environment, $status = 'publish' ) {

        global $wpdb;

        $where = 'WHERE 1';

        if ( is_numeric( $id_environment ) ) {
            $where .= sprintf( ' AND id = %s', $id_environment );
        } else {
            $where .= sprintf( ' AND description = \'%s\'', $id_environment );
        }

        if ( !empty( $status ) ) {
            $where .= sprintf( ' AND status = \'%s\'', $status );
        }

        $table = self::tableName();

        $sql = <<< SQL
    SELECT * FROM {$table}
    {$where}
SQL;
        $row = $wpdb->get_row( $sql );
        return $row;
    }


    // -----------------------------------------------------------------------------------------------------------------
    // Database CRUD
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Aggiunge un corriere
     *
     * @package    wpx Placeholders
     * @subpackage WPPlaceholdersEnvironments
     * @since      1.0.0
     *
     * @static
     *
     * @return mixed
     */
    public static function create() {

        $values = array(
            'description'    => esc_attr( $_POST['description'] ),
        );

        $result = parent::create( $values );

        return $result;
    }

    /**
     * Aggiorna un Ambiente
     *
     * @package    wpx Placeholders
     * @subpackage WPPlaceholdersEnvironments
     * @since      1.0.0
     *
     * @static
     * @return mixed
     */
    public static function update() {

        $values = array(
            'description'    => esc_attr( $_POST['description'] ),
        );

        $result = parent::update( absint( $_POST['id'] ), $values );
        return $result;
    }

    // -----------------------------------------------------------------------------------------------------------------
    // SDF Helper
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Restituisce l'elenco degli ambienti ordinati per nome
     *
     * @package    wpx Placeholders
     * @subpackage WPPlaceholdersEnvironments
     * @since      1.0.0
     *
     * @static
     * @return array
     * Array key pair con id/nome dell'ambiente per combo select in formato SDF
     */
    public static function arrayEnvironmentForSDF() {
        $environments = self::read( null, 'id', 'ORDER BY description', 'AND status = \'publish\'' );
        $results      = array();
        foreach ( $environments as $environment ) {
            $results[$environment->id] = $environment->description;
        }
        return $results;
    }

    // -----------------------------------------------------------------------------------------------------------------
    // UI Aux
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Restituisce il tag img con l'immagine da usare come placeholder
     *
     * @package    wpx Placeholders
     * @subpackage WPPlaceholdersEnvironments
     * @since      1.0.0
     *
     * @static
     * @return string
     */
    public static function imagePlaceholder() {
        $src  = self::srcPlaceholder();
        $html = <<< HTML
    <img src="{$src}" alt="Placeholder" title="" />
HTML;
        return $html;
    }


    /**
     * Costruisce il combo menu select per i filtri nei list table
     *
     * @package    WP Smart Shop
     * @subpackage WPXSmartShopCoupons
     * @since      1.0.0
     *
     * @static
     *
     * @param string $id_select Imposta attributo name e id del tag select
     * @param string $selected  ID della option selezionata
     *
     * @return string
     */
    public static function selectFilterEnvironment( $id_select, $selected = '' ) {
        global $wpdb;

        $table_environment  = self::tableName();

        /* Seleziono gli utenti in group by */
        $sql   = <<< SQL
        SELECT *
        FROM `{$table_environment}` AS environment
        WHERE environment.status = 'publish'
        GROUP BY environment.description
        ORDER BY environment.description
SQL;
        $environments = $wpdb->get_results( $sql );

        $options = '';
        foreach ( $environments as $environment ) {
            if ( !empty( $environment->description ) ) {
                $options .= sprintf( '<option %s value="%s">%s</option>', selected( $environment->id, $selected,
                    false ), $environment->id, $environment->description );
            }
        }

        $label = __( 'Filter for Environment', 'wp-smartshop' );

        $html = <<< HTML
        <select name="{$id_select}" id="{$id_select}">
            <option value ="">{$label}</option>
            {$options}
        </select>
HTML;
        return $html;
    }

}
