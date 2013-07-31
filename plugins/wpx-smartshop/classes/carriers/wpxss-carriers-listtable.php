<?php
/**
 * @class              WPXSmartShopCarriersListTable
 *
 * @description        Classe dedicata alla visualizzazione dei corrieri
 *
 * @package            wpx SmartShop
 * @subpackage         carriers
 * @author             =undo= <g.fazioli@undolog.com>, <g.fazioli@wpxtre.me>
 * @copyright          Copyright (c) 2012 wpXtreme, Inc.
 * @created            06/02/12
 * @version            1.0.0
 *
 */

if ( !class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

define( 'WPXSMARTSHOP_CARRIERS_LISTTABLE_DEFAULT_ORDER', 'asc' );
define( 'WPXSMARTSHOP_CARRIERS_LISTTABLE_DEFAULT_ORDER_BY', 'name' );

class WPXSmartShopCarriersListTable extends WP_List_Table {

    private $_table_name;

    /**
     * @todo farla diventare impostazione da backend
     *
     * @var int
     */
     static $itemPerPage = 20;


    // -----------------------------------------------------------------------------------------------------------------
    // Init
    // -----------------------------------------------------------------------------------------------------------------

    function __construct() {
        /* Set parent defaults */
        parent::__construct( array( 'singular' => 'carrierid', 'plural' => 'carriers', 'ajax' => false ) );
        $this->_table_name = WPXSmartShopCarriers::tableName();
    }

    function no_items() {
        _e( 'No Carriers found.', WPXSMARTSHOP_TEXTDOMAIN );
        echo '<br/>';
        _e( 'Please, check your search filters parameters.', WPXSMARTSHOP_TEXTDOMAIN );
    }

    // -----------------------------------------------------------------------------------------------------------------
    // CRUD Model
    // -----------------------------------------------------------------------------------------------------------------

    function get_views() {
        $statuses      = WPXSmartShopCarriers::statuses();
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

    /* Default content Columns */
    function column_default( $item, $column_name ) {
        switch ( $column_name ) {
            case 'name':
            case 'website':
            case 'measure_shipping_unit':
                return sprintf( '%s', $item[$column_name] );
            case 'status':
                return sprintf( '<span title="%s" class="wpss-icon-%s"></span>', __( $item[$column_name], WPXSMARTSHOP_TEXTDOMAIN ), $item[$column_name] );

            default:
                return print_r( $item, true );
        }
    }

    /* Inline actions */
    function column_id( $item ) {

        $args = array(
            'carrierid' => $item['id'],
            'actions'      => array(
                'edit'     => __( 'Edit', WPXSMARTSHOP_TEXTDOMAIN ),
                'untrash'  => __( 'Restore', WPXSMARTSHOP_TEXTDOMAIN ),
                'delete'   => __( 'Delete', WPXSMARTSHOP_TEXTDOMAIN ),
                'trash'    => __( 'Trash', WPXSMARTSHOP_TEXTDOMAIN ),
            )
        );

        $actions = WPDKListTable::actions( $args, $_REQUEST['status'] );

        return sprintf( '<strong>%s</strong> %s', $item['name'], $this->row_actions( $actions ) );
    }

    /* Checkbox for group actions */
    function column_cb($item) {
        return sprintf('<input type="checkbox" name="%1$s[]" value="%2$s" />', $this->_args['singular'], $item['id'] );
    }

    /* Columns */
    function get_columns() {
        $columns = array(
            'cb'                     => '<input type="checkbox" />',
            'id'                     => __( 'Name', WPXSMARTSHOP_TEXTDOMAIN ),
            'measure_shipping_unit'  => __( 'Measure shipping unit', WPXSMARTSHOP_TEXTDOMAIN ),
            'website'                => __( 'Web site', WPXSMARTSHOP_TEXTDOMAIN ),
        );
        return $columns;
    }

    /* Sortable columns */
    function get_sortable_columns() {
        $sortable_columns = array(
            'id'                     => array( 'name', true ),
            'measure_shipping_unit'  => array( 'measure_shipping_unit', false ),
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
                $result = WPXSmartShopCarriers::trash( $this->_table_name, $_REQUEST['carrierid'], 'id', 'status' );
                break;

            case 'untrash':
                $result = WPXSmartShopCarriers::untrash( $this->_table_name, $_REQUEST['carrierid'], 'id', 'status' );
                break;

            case 'delete':
                if ( isset( $_REQUEST['carrierid'] ) ) {
                    $result = WPXSmartShopCarriers::delete( $this->_table_name, $_REQUEST['carrierid'] );
                    if ( !is_wp_error( $result ) ) : ?>

                    <div class="updated">
                        <p><?php echo $result ?> <?php _e( 'Carrier(s) deleted successfully', WPXSMARTSHOP_TEXTDOMAIN ) ?></p>
                    </div>

                    <?php else : ?>

                    <div class="error">
                        <p><?php echo sprintf( '%s: %s', _e( 'Error while deleting carrier', WPXSMARTSHOP_TEXTDOMAIN ), $result->get_error_message() ) ?></p>
                    </div>
                    <?php endif;
                }
                return false;
            case 'update':
                $result = WPXSmartShopCarriers::update(); ?>

                <?php if ( !is_wp_error( $result ) ) : ?>

                <div class="updated">
                    <p><?php _e( 'Carrier update successfully', WPXSMARTSHOP_TEXTDOMAIN ) ?></p>
                </div>

                <?php else : ?>

                <div class="updated">
                    <p><?php echo sprintf( '%s: %s', _e( 'Error while updating carrier', WPXSMARTSHOP_TEXTDOMAIN ), $result->get_error_message() ) ?></p>
                </div>

                <?php endif;
                break;
            case 'insert':
                $result = WPXSmartShopCarriers::insertFromPost(); ?>

                <?php if ( !is_wp_error( $result ) ) : ?>

                <div class="updated">
                    <p><?php _e( 'Carrier added successfully', WPXSMARTSHOP_TEXTDOMAIN ) ?></p>
                </div>

                <?php else : ?>

                <div class="updated">
                    <p><?php echo sprintf( '%s: %s', _e( 'Error while adding carrier', WPXSMARTSHOP_TEXTDOMAIN ), $result->get_error_message() ) ?></p>
                </div>

                <?php endif;
                break;

            case 'new':
                WPXSmartShopCarriersViewController::editView();
                return true;
            case 'edit':
                WPXSmartShopCarriersViewController::editView( $_REQUEST['carrierid'] );
                return true;
        }
        return false;
    }

    /* Prepare */
    function prepare_items() {
        global $wpdb;

        /**
         * First, lets decide how many records per page to show
         */
        $per_page = self::$itemPerPage;

        /**
         * REQUIRED. Now we need to define our column headers. This includes a complete
         * array of columns to be displayed (slugs & titles), a list of columns
         * to keep hidden, and a list of columns that are sortable. Each of these
         * can be defined in another method (as we've done here) before being
         * used to build the value for our _column_headers property.
         */
        $columns  = $this->get_columns();
        $hidden   = array();
        $sortable = $this->get_sortable_columns();

        /**
         * REQUIRED. Finally, we build an array to be used by the class for column
         * headers. The $this->_column_headers property takes an array which contains
         * 3 other arrays. One for all columns, one for hidden columns, and one
         * for sortable columns.
         */
        $this->_column_headers = array($columns, $hidden, $sortable);

        /**
         * Optional. You can handle your bulk actions however you see fit. In this
         * case, we'll handle them within our package just to keep things clean.
         */
        if ($this->process_bulk_action()) {
            return true;
        }

        // Costruisco la select
        $where   = 'WHERE 1';
        $orderby = isset($_GET['orderby']) ? $_GET['orderby'] : WPXSMARTSHOP_CARRIERS_LISTTABLE_DEFAULT_ORDER_BY;
        $order   = isset($_GET['order']) ? $_GET['order'] : WPXSMARTSHOP_CARRIERS_LISTTABLE_DEFAULT_ORDER;

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

        $sql = <<< SQL
SELECT *
FROM `{$this->_table_name}`
{$where}
ORDER BY `{$orderby}` {$order}
SQL;


        $data = $wpdb->get_results( $sql, ARRAY_A );

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
        $total_items = count( $data );

        /**
         * The WP_List_Table class does not handle pagination for us, so we need
         * to ensure that the data is trimmed to only the current page. We can use
         * array_slice() to
         */
        $data = array_slice( $data, ( ( $current_page - 1 ) * $per_page ), $per_page );

        /**
         * REQUIRED. Now we can add our *sorted* data to the items property, where
         * it can be used by the rest of the class.
         */
        $this->items = $data;

        /**
         * REQUIRED. We also have to register our pagination options & calculations.
         */
        $this->set_pagination_args( array( 'total_items' => $total_items, 'per_page' => $per_page, 'total_pages' => ceil( $total_items / $per_page ) ) );
    }
}
