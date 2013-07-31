<?php
/**
 * @class              WPSmartShopShipmentsListTable
 * @description        Visualizzazione delle spedizioni
 *
 * @package            wpx SmartShop
 * @subpackage         shipments
 * @author             =undo= <g.fazioli@wpxtre.me>
 * @copyright          Copyright (c) 2012 wpXtreme, Inc.
 * @created            07/02/12
 * @version            1.0.0
 *
 */

if (!class_exists('WP_List_Table')) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

define('kWPSmartShopShipmentsListTableDefaultOrder', 'asc');
define('kWPSmartShopShipmentsListTableDefaultOrderBy', 'price');

class WPSmartShopShipmentsListTable extends WP_List_Table {

    private $_table_name;

    /**
     * ID del corriere preimpostato nei filtri
     *
     * @var mixed
     */
    private $_default_carrier_id_filter;

    /**
     * ID della zona preimpostata nei filtri
     *
     * @var mixed
     */
    private $_default_zone_id_filter;



    function __construct() {

        /* Set parent defaults */
        parent::__construct(array( 'singular' => 'shipmentid', 'plural'   => 'shipments', 'ajax'     => false ) );

        /* Corriere di default, il primo */
        $carriers                         = WPXSmartShopCarriers::arrayCarriersForSDF();
        $this->_default_carrier_id_filter = key( $carriers );

        /* Zona di default, la prima */
        $zones                         = WPSmartShopShippingCountries::zonesArray();
        $this->_default_zone_id_filter = key( $zones );
    }

    function no_items() {
        _e( 'No Shipments found.', WPXSMARTSHOP_TEXTDOMAIN );
        echo '<br/>';
        _e( 'Please, check your search filters parameters.', WPXSMARTSHOP_TEXTDOMAIN );
    }

    /* Azioni inline */
    function get_views() {
        $statuses     = WPSmartShopShipments::statusesWithCount();
        $views         = array();
        $filter_status = isset( $_GET['status'] ) ? $_GET['status'] : 'all';
        foreach ( $statuses as $key => $status ) {
            if ( intval( $status['count'] ) > 0 ) {

                $current = ( $filter_status == $key ) ? 'class="current"' : '';
                $href    = add_query_arg( array( 'status'   => $key, 'action'   => false ) );

                $views[$key] = sprintf( '<a %s href="%s">%s <span class="count">(%s)</span></a>', $current, $href, $status['label'], $status['count'] );
            }
        }
        return $views;
    }

    /* Extra filters */
    function extra_tablenav( $which ) {
        if ( $which == 'top' ) {
            echo $this->filters_tablenav();
        }
    }

    /* Default content Columns */
    function column_default( $item, $column_name ) {
        switch ( $column_name ) {
            case 'weight_from':
            case 'weight_to':
            case 'width_from':
            case 'width_to':
            case 'height_from':
            case 'height_to':
            case 'depth_from':
            case 'depth_to':
            case 'volume':
            case 'price':
                return sprintf( '%s', $item[$column_name] );
            default:
                return print_r( $item, true );
        }
    }

    /* Inline actions */
    function column_id( $item ) {

        $args = array(
            $this->_args['singular'] => $item['id_shipment'],
            'actions'                => array(
                'edit'     => __( 'Edit', WPXSMARTSHOP_TEXTDOMAIN ),
                'untrash'  => __( 'Restore', WPXSMARTSHOP_TEXTDOMAIN ),
                'delete'   => __( 'Delete', WPXSMARTSHOP_TEXTDOMAIN ),
                'trash'    => __( 'Trash', WPXSMARTSHOP_TEXTDOMAIN ),
            )
        );

        $status = isset( $_REQUEST['status'] ) ? $_REQUEST['status'] : '';

        $actions = WPDKListTable::actions( $args, $status );

        return sprintf( '%s %s', $item['weight_from'], $this->row_actions( $actions ) );
    }

    /* Checkbox for group actions */
    function column_cb($item) {
        return sprintf('<input type="checkbox" name="%1$s[]" value="%2$s" />', $this->_args['singular'], $item['id_shipment'] );
    }

    /* Columns */
    function get_columns() {
        $weight  = ' (' . WPXSmartShopMeasures::weightSymbol() . ')';
        $size    = ' (' . WPXSmartShopMeasures::sizeSymbol() . ')';
        $columns = array(
            'cb'          => '<input type="checkbox" />',
            'id'          => __( 'Weight from', WPXSMARTSHOP_TEXTDOMAIN ) . $weight,
            'weight_to'   => __( 'Weight to', WPXSMARTSHOP_TEXTDOMAIN ) . $weight,
            'width_from'  => __( 'Width from', WPXSMARTSHOP_TEXTDOMAIN ) . $size,
            'width_to'    => __( 'Width to', WPXSMARTSHOP_TEXTDOMAIN ) . $size,
            'height_from' => __( 'Height from', WPXSMARTSHOP_TEXTDOMAIN ) . $size,
            'height_to'   => __( 'Height to', WPXSMARTSHOP_TEXTDOMAIN ) . $size,
            'depth_from'  => __( 'Depth from', WPXSMARTSHOP_TEXTDOMAIN ) . $size,
            'depth_to'    => __( 'Depth to', WPXSMARTSHOP_TEXTDOMAIN ) . $size,
            'volume'      => __( 'Volume', WPXSMARTSHOP_TEXTDOMAIN ) . ' (' . WPXSmartShopMeasures::volumeSymbol() . ')',
            'price'       => __( 'Price', WPXSMARTSHOP_TEXTDOMAIN ) . ' ' . WPXSmartShopCurrency::currencySymbol(),
        );
        return $columns;
    }

    /* Sortable columns */
    function get_sortable_columns() {
        $sortable_columns = array(
            'weight_from'      => array( 'weight_from', true ),
            'price'      => array( 'price', true ),
        );
        return $sortable_columns;
    }

    /* Bulk Actions */
    function get_bulk_actions() {
        $actions = array(
            'delete'   => __( 'Delete', WPXSMARTSHOP_TEXTDOMAIN ),
            'trash'    => __( 'Move to Trash', WPXSMARTSHOP_TEXTDOMAIN ),
            'untrash'  => __( 'Restore', WPXSMARTSHOP_TEXTDOMAIN ),
        );

        if ( empty( $_REQUEST['status'] ) || $_REQUEST['status'] != 'trash' ) {
            unset( $actions['untrash'] );
            unset( $actions['delete'] );
        } else if ( $_REQUEST['status'] == 'trash' ) {
            unset( $actions['trash'] );
        }
        return $actions;
    }

    /* Process Bulk actions */
    function process_bulk_action() {

        switch ( $this->current_action() ) {
            case 'trash':
                $result = WPSmartShopShipments::trash( $_REQUEST['shipmentid'], 'id', 'status' );
                break;

            case 'untrash':
                $result = WPSmartShopShipments::untrash( $_REQUEST['shipmentid'], 'id', 'status' );
                break;

            case 'delete':
                if ( isset( $_REQUEST['shipmentid'] ) ) {
                    $result = WPSmartShopShipments::delete( $_REQUEST['shipmentid'] );
                    if ( !is_wp_error( $result ) ) : ?>

                    <div class="updated">
                        <p><?php echo $result ?> <?php _e( 'Shipment deleted successfully', WPXSMARTSHOP_TEXTDOMAIN ) ?></p>
                    </div>

                    <?php else : ?>

                    <div class="error">
                        <p><?php echo sprintf( '%s: %s', _e( 'Error while deleting Shipment', WPXSMARTSHOP_TEXTDOMAIN ), $result->get_error_message() ) ?></p>
                    </div>
                    <?php endif;
                }
                return false;
            case 'update':
                if ( WPDKForm::isNonceVerify( 'shipments' ) ) {
                    $result = WPSmartShopShipments::update(); ?>

                <?php if ( !is_wp_error( $result ) ) : ?>

                    <div class="updated">
                        <p><?php _e( 'Shipment update successfully', WPXSMARTSHOP_TEXTDOMAIN ) ?></p>
                    </div>

                    <?php else : ?>

                    <div class="updated">
                        <p><?php echo sprintf( '%s: %s', _e( 'Error while updating Shipment', WPXSMARTSHOP_TEXTDOMAIN ), $result->get_error_message() ) ?></p>
                    </div>

                    <?php endif;
                }
                break;
            case 'insert':
                if ( WPDKForm::isNonceVerify( 'shipments' ) ) {
                    $result = WPSmartShopShipments::create(); ?>

                <?php if ( !is_wp_error( $result ) ) : ?>

                    <div class="updated">
                        <p><?php _e( 'Shipment added successfully', WPXSMARTSHOP_TEXTDOMAIN ) ?></p>
                    </div>

                    <?php else : ?>

                    <div class="updated">
                        <p><?php echo sprintf( '%s: %s', _e( 'Error while adding Shipment', WPXSMARTSHOP_TEXTDOMAIN ), $result->get_error_message() ) ?></p>
                    </div>

                    <?php endif;
                }
                break;

            case 'new':
                WPSmartShopShipmentsViewController::editView();
                return true;
            case 'edit':
                WPSmartShopShipmentsViewController::editView( $_REQUEST['shipmentid'] );
                return true;
        }
    }

    /* Prepare */
    function prepare_items() {
        global $wpdb;

        /**
         * First, lets decide how many records per page to show
         */
        $id_user  = get_current_user_id();
        $screen   = get_current_screen();
        $option   = $screen->get_option( 'per_page', 'option' );
        $per_page = get_user_meta( $id_user, $option, true );
        if ( empty ( $per_page ) || $per_page < 1 ) {
            $per_page = $screen->get_option( 'per_page', 'default' );
        }

        /* Columns Header */
        $this->_column_headers = $this->get_column_info();

        /**
         * Optional. You can handle your bulk actions however you see fit. In this
         * case, we'll handle them within our package just to keep things clean.
         */
        if ($this->process_bulk_action()) {
            return true;
        }

        /* Costruisco la select */
        $where   = 'WHERE 1';
        $orderby = isset( $_GET['orderby'] ) ? esc_attr( $_GET['orderby'] ) : kWPSmartShopShipmentsListTableDefaultOrderBy;
        $order   = isset( $_GET['order'] ) ? esc_attr( $_GET['order'] ) : kWPSmartShopShipmentsListTableDefaultOrder;

        $table         = WPSmartShopShipments::tableName();
        $tableSize     = WPSmartShopShipments::tableName(true);

        /* Where condiction filters */

        /* Status */
        if ( isset( $_GET['status'] ) ) {
            if ( $_GET['status'] != 'all' ) {
                $where .= sprintf( " AND `status` = '%s'", esc_attr( $_GET['status'] ) );
            } else {
                $where .= " AND `status` <> 'trash'";
            }
        } else {
            $where .= " AND `status` <> 'trash'";
        }

        $id_carrier_filter = $this->_default_carrier_id_filter;
        if ( isset( $_GET['wpss-shipment-carriers-filter'] ) &&
            !empty( $_GET['wpss-shipment-carriers-filter'] )
        ) {
            $id_carrier_filter = esc_attr( $_GET['wpss-shipment-carriers-filter'] ) ;
        }

        $where .= ' AND shipments.id_carrier = "' . $id_carrier_filter . '"';

        $id_zone_filter = $this->_default_zone_id_filter;
        if ( isset( $_GET['wpss-shipment-zones-filter'] ) &&
            !empty( $_GET['wpss-shipment-zones-filter'] )
        ) {
            $id_zone_filter = esc_attr( $_GET['wpss-shipment-zones-filter'] ) ;
        }
        $where .= ' AND shipments.zone = "' . $id_zone_filter . '"';

        $sql = <<< SQL
SELECT size_shipments.*, shipments.price, shipments.id AS id_shipment
FROM `{$table}` AS shipments
LEFT JOIN `{$tableSize}` AS size_shipments ON size_shipments.id = shipments.id_size_shipment
{$where}
ORDER BY `{$orderby}` {$order}
SQL;

        $data = $wpdb->get_results($sql, ARRAY_A);

        /**
         * REQUIRED for pagination. Let's figure out what page the user is currently
         * looking at. We'll need this later, so you should always include it in
         * your own package classes.
         */
        $current_page = $this->get_pagenum();

        /**
         * REQUIRED for pagination. Let's check how many items are in our data array.
         * In real-world use, this would be the total number of items in your database,
         * without filtering. We'll need this later, so you should always include it
         * in your own package classes.
         */
        $total_items = count($data);

        /**
         * The WP_List_Table class does not handle pagination for us, so we need
         * to ensure that the data is trimmed to only the current page. We can use
         * array_slice() to
         */
        $data = array_slice($data, (($current_page - 1) * $per_page), $per_page);

        /**
         * REQUIRED. Now we can add our *sorted* data to the items property, where
         * it can be used by the rest of the class.
         */
        $this->items = $data;

        /**
         * REQUIRED. We also have to register our pagination options & calculations.
         */
        $this->set_pagination_args(array( 'total_items' => $total_items, 'per_page' => $per_page, 'total_pages' => ceil( $total_items / $per_page) ) );

    }

    function filters_tablenav() {

        /* Get filters */
        $selected_carrier = isset( $_GET['wpss-shipment-carriers-filter'] ) ? $_GET['wpss-shipment-carriers-filter'] : $this->_default_carrier_id_filter;
        $selected_zone = isset( $_GET['wpss-shipment-zones-filter'] ) ? $_GET['wpss-shipment-zones-filter'] : $this->_default_zone_id_filter;

        $button_label = __( 'Apply', WPXSMARTSHOP_TEXTDOMAIN );

        $filter_carrier = WPXSmartShopCarriers::selectFilterCarriers( 'wpss-shipment-carriers-filter', $selected_carrier );
        $filter_zone = WPSmartShopShippingCountries::selectFilterZones( 'wpss-shipment-zones-filter', $selected_zone );

        $html = <<< HTML
        <div class="clearfix wpdk-list-table-filter">
            <div class="wpdk-list-table-filter-row">
                {$filter_carrier}
                {$filter_zone}
            </div>
            <div class="wpdk-list-table-filter-row">
            <input type="submit"
                   value="{$button_label}"
                   class="button-secondary action alignright wpdk-form-button"
                   id="doaction"
                   name=""/>
            </div>
        </div>
HTML;
        return $html;
    }

}
