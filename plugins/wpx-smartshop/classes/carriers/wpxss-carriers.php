<?php
/**
 * @class              WPXSmartShopCarriers
 *
 * @description        Carriers Manage
 *
 * @package            wpx SmartShop
 * @subpackage         carriers
 * @author             =undo= <g.fazioli@undolog.com>, <g.fazioli@wpxtre.me>
 * @copyright          Copyright (c) 2012 wpXtreme, Inc.
 * @created            06/02/12
 * @version            1.0.0
 *
 */

require_once ( 'wpxss-carriers-viewcontroller.php' );

class WPXSmartShopCarriers extends WPDKDBTable {

    // -----------------------------------------------------------------------------------------------------------------
    // Static values
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Restituisce il nome della tabella dei corrieri
     *
     * @static
     * @retval string
     */
    static function tableName() {
        global $wpdb;
        return sprintf( '%s%s', $wpdb->prefix, WPXSMARTSHOP_DB_TABLENAME_CARRIERS );
    }

    /**
     * Modulo nello standard SFD per l'inserimento ed edit
     *
     * @static
     * @param null $id
     * @retval array
     */
    public static function fields( $id = null ) {
        $carrier = null;

        if ( !is_null( $id ) ) {
            $carrier = self::carrier( absint( $id ) );
        }
        $fields = array(
            __( 'Carrier information', WPXSMARTSHOP_TEXTDOMAIN )   => array(
                array(
                    array(
                        'type'    => WPDK_FORM_FIELD_TYPE_SELECT,
                        'name'    => 'status',
                        'label'   => __( 'Status', WPXSMARTSHOP_TEXTDOMAIN ),
                        'options' => self::arrayStatusesForSDF( self::arrayStatuses() ),
                        'value'   => !is_null( $carrier ) ? $carrier->status : 'current'
                    ),
                ),
                array(
                    array(
                        'type'  => WPDK_FORM_FIELD_TYPE_TEXT,
                        'name'  => 'wpss-carrier-name',
                        'label' => __( 'Name', WPXSMARTSHOP_TEXTDOMAIN ),
                        'value' => is_null( $carrier ) ? '' : $carrier->name
                    )
                ),
                array(
                    array(
                        'type'  => WPDK_FORM_FIELD_TYPE_TEXT,
                        'name'  => 'wpss-carrier-website',
                        'label' => __( 'Web site', WPXSMARTSHOP_TEXTDOMAIN ),
                        'value' => is_null( $carrier ) ? '' : $carrier->website
                    )
                ),
                array(
                    array(
                        'type'    => WPDK_FORM_FIELD_TYPE_SELECT,
                        'name'    => 'wpss-carrier-measure_shipping_unit',
                        'label'   => __( 'Measure shipping unit', WPXSMARTSHOP_TEXTDOMAIN ),
                        'options' => array(
                            'weight'    => __( 'Weight', WPXSMARTSHOP_TEXTDOMAIN ),
                            'size'      => __( 'Size', WPXSMARTSHOP_TEXTDOMAIN ),
                            'volume'    => __( 'Volume', WPXSMARTSHOP_TEXTDOMAIN ),
                        ),
                        'value'   => is_null( $carrier ) ? '' : $carrier->measure_shipping_unit
                    )
                ),
            )
        );
        return $fields;
    }

    /**
     * Restituisce l'elenco degli stati
     *
     * @static
     * @retval array
     */
    public static function arrayStatuses() {

        $statuses = array(
            'all'  => array(
                'label' => __('All', WPXSMARTSHOP_TEXTDOMAIN ),
                'count' => 0
            ),
            'publish' => array(
                'label' => __( 'Publish', WPXSMARTSHOP_TEXTDOMAIN ),
                'count' => 0
            ),
            'trash'   => array(
                'label' => __('Trash', WPXSMARTSHOP_TEXTDOMAIN ),
                'count' => 0
            )
        );
        return $statuses;
    }

    function statuses() {
        return parent::statusesWithCount( self::tableName(), self::arrayStatuses() );
    }

    // -----------------------------------------------------------------------------------------------------------------
    // Database CRUD
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Aggiunge un corriere
     *
     * @static
     *
     * @retval mixed
     */
    public static function insertFromPost() {
        $values = array(
            'name'                  => esc_attr( $_POST['wpss-carrier-name'] ),
            'website'               => esc_attr( $_POST['wpss-carrier-website'] ),
            'measure_shipping_unit' => esc_attr( $_POST['wpss-carrier-measure_shipping_unit'] ),
        );
        parent::create( self::tableName(), $values );
    }

    /* @deprecated */
    public static function create() {
        _deprecated_function( __FUNCTION__, '1.0', 'insertFromPost' );
        self::insertFromPost();
    }

    /**
     * Restituisce le informazioni su un corriere
     *
     * @alias
     * @static
     * @param int|object|array $id
     * @retval mixed
     */
    public static function carrier( $id ) {
        return parent::record( self::tableName(), $id );
    }

    /**
     * Restituisce la lista dei corrieri ordinata per nome
     *
     * @static
     * @alias
     * @param string $output
     * @retval mixed
     */
    public static function carriers( $output = OBJECT ) {
        $where = array(
            'status'    => 'publish'
        );
        return parent::records( self::tableName(), $where, 'name' );
    }

    /**
     * Aggiorna un corriere
     *
     * @static
     * @retval mixed
     */
    public static function update() {

        $values = array(
            'name'                  => $_POST['wpss-carrier-name'],
            'website'               => esc_url_raw( $_POST['wpss-carrier-website'] ),
            'measure_shipping_unit' => $_POST['wpss-carrier-measure_shipping_unit'],
            'status'                => $_POST['status'],
        );

        return parent::update( self::tableName(), absint( $_POST['id'] ), $values );
    }

    // -----------------------------------------------------------------------------------------------------------------
    // Database
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Crea o aggiorna (esegue un delta) la tabella dei corrieri
     *
     * @static
     *
     */
    public static function updateTable() {
        if ( !function_exists( 'dbDelta' ) ) {
            require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        }
        $dbDeltaTableFile = sprintf( '%s%s', WPXSMARTSHOP_PATH_DATABASE, WPXSMARTSHOP_DB_TABLENAME_FILENAME_CARRIERS );
        $file             = file_get_contents( $dbDeltaTableFile );
        $sql              = sprintf( $file, self::tableName() );
        @dbDelta( $sql );
    }

    // -----------------------------------------------------------------------------------------------------------------
    // Shorthand
    // -----------------------------------------------------------------------------------------------------------------

    /* Short hand */
    public static function measureShippingUnit( $id_carrier ) {
        $record = self::record( self::tableName(), $id_carrier );
        return $record->measure_shipping_unit;
    }

    // -----------------------------------------------------------------------------------------------------------------
    // WPDK SDF Form
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Restituisce un array in formato SDF con la lista dei corrieri. Usato per popolare un campo select SDF
     *
     * @static
     * @retval array
     */
    public static function arrayCarriersForSDF() {
        $carriers = self::carriers();
        $result   = array();
        foreach ( $carriers as $carrier ) {
            $result[$carrier->id] = $carrier->name;
        }
        return $result;
    }

    /* @deprecated */
    public static function carriersArray() {
        _deprecated_function( __CLASS__ . '::' . __FUNCTION__, '1.0', 'arrayCarriersForSDF()');

        $carriers = array();
        $result     = self::carriers();
        foreach ( $result as $carrier ) {
            $carriers[$carrier->id] = $carrier->name;
        }
        return $carriers;
    }

    // -----------------------------------------------------------------------------------------------------------------
    // UI
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Costruisce il combo menu SELECT per i filtri nei list table degli shipments.
     *
     * @nota       Usato dal list table shipments
     *
     * @static
     *
     * @param string $id_select
     * @param string $selected
     *
     * @retval string
     */
    public static function selectFilterCarriers( $id_select, $selected = '' ) {
        $carriers = self::carriers();

        $options = '';
        foreach ( $carriers as $carrier ) {
            $options .= sprintf( '<option %s value="%s">%s</option>', selected( $carrier->id , $selected, false ), $carrier->id , $carrier->name  );
        }

        $label = __( 'Filter by Carrier', WPXSMARTSHOP_TEXTDOMAIN );

        $html = <<< HTML
        <select name="{$id_select}" id="{$id_select}">
            <option value ="">{$label}</option>
            {$options}
        </select>
HTML;
        return $html;
    }

    /**
     * Costruisce un combo menu SELECT usato nel summary order.
     *
     * @static
     *
     * @note       Usato dal summary order
     *
     * @param string $id_select
     * @param string $selected
     * @param bool   $first_item
     *
     * @retval string
     */
    public static function carriersSelect( $id_select, $selected = '', $first_item = false ) {
        $result = self::carriers();
        $first = '';

        if ( $first_item ) {
            $first = sprintf( '<option value="">%s</option>', $first_item );
        }

        $options = '';

        foreach ( $result as $carrier ) {
            if ( !empty( $carrier->name ) ) {
                $options .= sprintf( '<option %s value="%s">%s</option>', selected( $carrier->id, $selected, false ), $carrier->id, $carrier->name );
            }
        }

        $html = <<< HTML
        <select class="wpdk-form-select" name="{$id_select}" id="{$id_select}">
            {$first}
            {$options}
        </select>
HTML;
        return $html;
    }

}
