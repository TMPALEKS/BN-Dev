<?php
/**
 * Visualizzazione dei pease e altre informazioni
 *
 * @package            wpx SmartShop
 * @subpackage         WPSmartShopShippingCountriesListTable
 * @author             =undo= <g.fazioli@undolog.com>, <g.fazioli@wpxtre.me>
 * @copyright          Copyright (c) 2012 wpXtreme, Inc.
 * @link               http://wpxtre.me
 * @created            07/02/12
 * @version            1.0.0
 *
 */

if (!class_exists('WP_List_Table')) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

define('kWPSmartShopShippingCountriesListTableDefaultOrder', 'asc');
define('kWPSmartShopShippingCountriesListTableDefaultOrderBy', 'country');

class WPSmartShopShippingCountriesListTable extends WP_List_Table {

    /**
     * @package    wpx SmartShop
     * @subpackage WPSmartShopShippingCountriesListTable
     * @since      1.0.0
     *
     * @todo farla diventare impostazione da backend
     *
     * @var int
     */
    public static $itemPerPage = 20;

    function __construct() {

        /* Set parent defaults */
        parent::__construct(array(
                                 'singular' => 'shippingcountryid', //singular name of the listed records
                                 'plural'   => 'shippingcountries', //plural name of the listed records
                                 'ajax'     => false //does this table support ajax?
                            ));
    }

    /* Azioni inline */
    function get_views() {
        $statuses     = WPSmartShopShippingCountries::statusesWithCount();
        $views         = array();
        $filter_status = isset( $_GET['status'] ) ? $_GET['status'] : 'all';
        foreach ( $statuses as $key => $status ) {
            if ( intval( $status['count'] ) > 0 ) {

                $current = ( $filter_status == $key ) ? 'class="current"' : '';
                $href    = add_query_arg( array(
                                               'status'   => $key,
                                               'action'   => false
                                          ) );

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
            case 'country':
            case 'continent':
                return sprintf( '%s', ucfirst( $item[$column_name] ) );
            case 'zone':
            case 'currency':
            case 'symbol':
            case 'isocode':
            case 'code':
            case 'tax':
                return sprintf( '%s', $item[$column_name] );
            default:
                return print_r( $item, true ); //Show the whole array for troubleshooting purposes
        }
    }

    /* Inline actions */
    function column_id( $item ) {

        $args = array(
            $this->_args['singular'] => $item['id'],
            'actions'                => array(
                'edit'     => __( 'Edit', WPXSMARTSHOP_TEXTDOMAIN ),
                'untrash'  => __( 'Restore', WPXSMARTSHOP_TEXTDOMAIN ),
                'delete'   => __( 'Delete', WPXSMARTSHOP_TEXTDOMAIN ),
                'trash'    => __( 'Trash', WPXSMARTSHOP_TEXTDOMAIN ),
            )
        );

        $actions = WPDKListTable::actions( $args, $_REQUEST['status'] );

        return sprintf( '<strong>%s</strong> %s', $item['country'], $this->row_actions( $actions ) );
    }

    /* Checkbox for group actions */
    function column_cb($item) {
        return sprintf('<input type="checkbox" name="%1$s[]" value="%2$s" />', $this->_args['singular'], $item['id'] );
    }

    /* Columns */
    function get_columns() {
        $columns = array(
            'cb'         => '<input type="checkbox" />',
            'id'         => __( 'Country', WPXSMARTSHOP_TEXTDOMAIN ),
            'continent'  => __( 'Continent', WPXSMARTSHOP_TEXTDOMAIN ),
            'zone'       => __( 'Zone', WPXSMARTSHOP_TEXTDOMAIN ),
            'currency'   => __( 'Currency', WPXSMARTSHOP_TEXTDOMAIN ),
            'symbol'     => __( 'Symbol', WPXSMARTSHOP_TEXTDOMAIN ),
            'isocode'    => __( 'ISO Code', WPXSMARTSHOP_TEXTDOMAIN ),
            'code'       => __( 'Code', WPXSMARTSHOP_TEXTDOMAIN ),
            'tax'        => __( 'Tax', WPXSMARTSHOP_TEXTDOMAIN ) . ' %',
        );
        return $columns;
    }

    /* Sortable columns */
    function get_sortable_columns() {
        $sortable_columns = array(
            'id'         => array( 'country', true ),
            'continent'  => array( 'continent', false ),
            'zone'       => array( 'zone', false ),
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
                $result = WPSmartShopShippingCountries::trash( $_REQUEST['shippingcountryid'], 'id', 'status' );
                break;

            case 'untrash':
                $result = WPSmartShopShippingCountries::untrash( $_REQUEST['shippingcountryid'], 'id', 'status' );
                break;

            case 'delete':
                if ( isset( $_REQUEST['shippingcountryid'] ) ) {
                    $result = WPSmartShopShippingCountries::delete( $_REQUEST['shippingcountryid'] );
                    if ( !is_wp_error( $result ) ) : ?>

                    <div class="updated">
                        <p><?php echo $result ?> <?php _e( 'The Shipping Country has been deleted successfully', WPXSMARTSHOP_TEXTDOMAIN ) ?></p>
                    </div>

                    <?php else : ?>

                    <div class="error">
                        <p><?php echo sprintf( '%s: %s', _e( 'Error while deleting Shipping Country', WPXSMARTSHOP_TEXTDOMAIN ), $result->get_error_message() ) ?></p>
                    </div>
                    <?php endif;
                }
                return false;
            case 'update':
                $result = WPSmartShopShippingCountries::update(); ?>

                <?php if ( !is_wp_error( $result ) ) : ?>

                <div class="updated">
                    <p><?php _e( 'The Shipping Country has been updated successfully', WPXSMARTSHOP_TEXTDOMAIN ) ?></p>
                </div>

                <?php else : ?>

                <div class="updated">
                    <p><?php echo sprintf( '%s: %s', _e( 'Error while updating of Shipping Country', WPXSMARTSHOP_TEXTDOMAIN ), $result->get_error_message() ) ?></p>
                </div>

                <?php endif;
                break;
            case 'insert':
                $result = WPSmartShopShippingCountries::create(); ?>

                <?php if ( !is_wp_error( $result ) ) : ?>

                <div class="updated">
                    <p><?php _e( 'The Shipping Country has been added successfully', WPXSMARTSHOP_TEXTDOMAIN ) ?></p>
                </div>

                <?php else : ?>

                <div class="updated">
                    <p><?php echo sprintf( '%s: %s', _e( 'Error while adding Shippping Country', WPXSMARTSHOP_TEXTDOMAIN ), $result->get_error_message() ) ?></p>
                </div>

                <?php endif;
                break;

            case 'new':
                WPSmartShopShippingCountriesViewController::editView();
                return true;
            case 'edit':
                WPSmartShopShippingCountriesViewController::editView( $_REQUEST['shippingcountryid'] );
                return true;
        }
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

        /* Costruisco la select */
        $where   = 'WHERE 1';
        $orderby = isset( $_GET['orderby'] ) ? esc_attr( $_GET['orderby'] ) : kWPSmartShopShippingCountriesListTableDefaultOrderBy;
        $order   = isset( $_GET['order'] ) ? esc_attr( $_GET['order'] ) : kWPSmartShopShippingCountriesListTableDefaultOrder;
        $table   = WPSmartShopShippingCountries::tableName();

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

        if ( isset( $_GET['wpss-shipping-countries-continents-filter'] ) &&
            !empty( $_GET['wpss-shipping-countries-continents-filter'] )
        ) {
            $where .= ' AND continent = "' . esc_attr( $_GET['wpss-shipping-countries-continents-filter'] ) . '"';
        }

        if ( isset( $_GET['wpss-shipping-countries-zones-filter'] ) &&
            !empty( $_GET['wpss-shipping-countries-zones-filter'] )
        ) {
            $where .= ' AND zone = "' . esc_attr( $_GET['wpss-shipping-countries-zones-filter'] ) . '"';
        }

        if ( isset( $_GET['s'] ) && !empty( $_GET['s'] ) ) {
            $where .= ' AND country LIKE "%' . esc_attr( $_GET['s'] ) . '%"';
        }

        $sql = <<< SQL
		SELECT *
		FROM `{$table}`
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
        $this->set_pagination_args(array(
                                        'total_items' => $total_items, //WE have to calculate the total number of items
                                        'per_page'    => $per_page, //WE have to determine how many items to show on a page
                                        'total_pages' => ceil($total_items / $per_page) //WE have to calculate the total number of pages
                                   ));
    }

    function filters_tablenav() {

        /* Get filters */
        $selected_continent = isset( $_GET['wpss-shipping-countries-continents-filter'] ) ? $_GET['wpss-shipping-countries-continents-filter'] : '';
        $selected_zone      = isset( $_GET['wpss-shipping-countries-zones-filter'] ) ? $_GET['wpss-shipping-countries-zone-filter'] : -1;

        $button_label = __( 'Apply', WPXSMARTSHOP_TEXTDOMAIN );

        $filter_continent = WPSmartShopShippingCountries::selectFilterContinents( 'wpss-shipping-countries-continents-filter', $selected_continent );
        $filter_zone      = WPSmartShopShippingCountries::selectFilterZones( 'wpss-shipping-countries-zones-filter', $selected_zone );

        $html = <<< HTML
        <div class="alignleft actions wpdk-list-table-filter">
            {$filter_continent}
            {$filter_zone}
            <input type="submit"
                   value="{$button_label}"
                   class="button-secondary action alignright"
                   id="doaction"
                   name=""/>
        </div>
HTML;
        return $html;
    }
}