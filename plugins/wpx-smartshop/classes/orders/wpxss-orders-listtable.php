<?php
/**
 * @class              WPXSmartShopOrdersListTable
 * @description        Estensione della classe WP_List_Table per la visualizzazione tabellare dei record
 *
 * @package            wpx SmartShop
 * @subpackage         WPXSmartShopOrdersListTable
 * @author             =undo= <g.fazioli@wpxtre.me>
 * @copyright          Copyright (c)2012 wpXtreme, Inc.
 * @created            28/11/11
 * @version            1.0
 *
 */

if (!class_exists('WP_List_Table')) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

define('kWPSmartShopOrdersListTableDefaultOrder', 'desc');
define('kWPSmartShopOrdersListTableDefaultOrderBy', 'order_datetime');

class WPXSmartShopOrdersListTable extends WP_List_Table {

    /* @todo Verificare se necessario */
    private $_table_name;

    /**
     * @var int Numero record estratti
     */
    public $count = 0;

    /**
     * @var bool Se true siamo in stampa
     */
    public $_printing;

    /// Construct
    function __construct ( $printing = false ) {
        /* Set parent defaults */
        parent::__construct( array( 'singular' => 'id_order', 'plural' => 'orders', 'ajax' => false ) );
        $this->_table_name = WPXSmartShopOrders::tableName();

        /* Printing mode */
        $this->_printing = $printing;
    }

    /// No items
    function no_items() {
        _e( 'No Orders found.', WPXSMARTSHOP_TEXTDOMAIN );
        echo '<br/>';
        _e( 'Please, check your search filters parameters.', WPXSMARTSHOP_TEXTDOMAIN );
    }

    /* Azione inline */
    function get_views() {
        if ( $this->_printing ) {
            return array();
        }

        $statuses     = WPXSmartShopOrders::statuses();
        $views         = array();
        $filter_status = isset( $_GET['status'] ) ? $_GET['status'] : 'all';
        foreach ( $statuses as $key => $status ) {
            if ( intval( $status['count'] ) > 0 ) {
                $current = ( $filter_status == $key ) ? 'class="current"' : '';
                $href        = add_query_arg( array( 'status' => $key, 'action' => false ) );
                $views[$key] = sprintf( '<a %s href="%s">%s <span class="count">(%s)</span></a>', $current, $href, $status['label'], $status['count'] );
            }
        }
        return $views;
    }

    /* Extra filters */
    function extra_tablenav( $which ) {
        if( $which == 'top' ) {
            echo $this->filters_tablenav();
        }
    }

    /** **/
    function column_default($item, $column_name) {
        switch ( $column_name ) {
            case 'user':
                if ( is_null( $item['user_display_name'] ) ) {
                    return sprintf( '<span style="color:silver">%s</span>', __( 'No user found', WPXSMARTSHOP_TEXTDOMAIN ) );
                } else {
                    return sprintf( '%s %s', WPDKUser::gravatar( $item['id_user'], 16 ), $item['user_display_name'] );
                }
                break;

            case 'user_order':
                if ( is_null( $item['user_order_display_name'] ) ) {
                    return sprintf( '<span style="color:silver">%s</span>', __( 'No user found', WPXSMARTSHOP_TEXTDOMAIN ) );
                } else {
                    return sprintf( '%s %s', WPDKUser::gravatar( $item['id_user_order'], 16 ), $item['user_order_display_name'] );
                }
                break;

            case 'order_datetime':
            case 'status_datetime':
                return WPDKDateTime::timeNewLine( mysql2date( __( 'm/d/Y H:i', WPXSMARTSHOP_TEXTDOMAIN ), $item[$column_name] ) );
                break;

            case 'status':
                $statuses = WPXSmartShopOrders::arrayStatuses();
                $description_status = __( $statuses[$item[$column_name]]['label'], WPXSMARTSHOP_TEXTDOMAIN );
                return sprintf( '<span title="%s" class="wpdk-tooltip wpss-icon-%s">%s</span>', $description_status , $item[$column_name], ($this->_printing ? $description_status : '') );
                break;

            case 'total':
                return WPXSmartShopCurrency::formatCurrency( $item['total'] ) . ' ' . WPXSmartShopCurrency::currencySymbol();
                break;

            case 'transaction_id':
            case 'payment_type':
            case 'payment_gateway':
                return $item[$column_name];
                break;

            default:
                return print_r( $item, true ); // Show the whole array for troubleshooting purposes
        }
    }

    /**  */
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

        $status = '';
        if ( isset( $_REQUEST['status'] ) ) {
            $status = $_REQUEST['status'];
        }

        $actions = WPDKListTable::actions( $args, $status );
        $row_actions = !$this->_printing ? '<br/>' . $this->row_actions( $actions ) : '';

        return sprintf( '# <span style="color:#789;">%s</span> - %s%s',
            $item['id'],
            $item['track_id'],
            $row_actions );
    }

    /**  */
    function column_cb( $item ) {
        return sprintf( '<input type="checkbox" name="%1$s[]" value="%2$s" />', $this->_args['singular'], $item['id'] );
    }

    /** */
    function get_columns() {
        $columns = array(
            'cb'              => '<input type="checkbox" />', //Render a checkbox instead of text
            'id'              => __( 'Order', WPXSMARTSHOP_TEXTDOMAIN ),
            'transaction_id'  => __( 'Transaction ID', WPXSMARTSHOP_TEXTDOMAIN ),
            'order_datetime'  => __( 'Date', WPXSMARTSHOP_TEXTDOMAIN ),
            'user'            => __( 'Ordered by', WPXSMARTSHOP_TEXTDOMAIN ),
            'user_order'      => __( 'Ordered for', WPXSMARTSHOP_TEXTDOMAIN ),
            'status_datetime' => __( 'Updated', WPXSMARTSHOP_TEXTDOMAIN ),
            'total'           => __( 'Total', WPXSMARTSHOP_TEXTDOMAIN ),
            'payment_type'    => __( 'Payment type', WPXSMARTSHOP_TEXTDOMAIN ),
            'payment_gateway' => __( 'Payment gateway', WPXSMARTSHOP_TEXTDOMAIN ),
            'status'          => __( 'Status', WPXSMARTSHOP_TEXTDOMAIN ),
        );
        if ( $this->_printing ) {
            unset( $columns['cb'] );
        }
        return $columns;
    }

    /**  */
    function get_sortable_columns() {
        $sortable_columns = array(
            'id'              => array( 'id', false ), //true means its already sorted
            'transaction_id'  => array( 'transaction_id', false),
            'order_datetime'  => array( 'order_datetime', true),
            'status'          => array( 'status', false),
            'status_datetime' => array( 'status_datetime', false ),
            'total'           => array( 'total', false),
            'payment_type'    => array( 'payment_type', false),
            'payment_gateway' => array( 'payment_gateway', false),
        );
        return $sortable_columns;
    }

    /**  */
    function get_bulk_actions() {
        if ( $this->_printing ) {
            return false;
        }
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

    /** * */
    function process_bulk_action() {

        switch ( $this->current_action() ) {
            case 'trash':
                $result = WPXSmartShopOrders::trash( $this->_table_name, $_REQUEST['id_order'], 'id', 'status' );
                /* @todo Check $result */
                break;

            case 'untrash':
                $result = WPXSmartShopOrders::untrash( $this->_table_name, $_REQUEST['id_order'], 'id', 'status' );
                /* @todo Check $result */
                break;

            case 'delete':
                if ( isset( $_REQUEST['id_order'] ) ) {
                    $result = WPXSmartShopOrders::delete( $_REQUEST['id_order'] );
                    if ( is_wp_error( $result ) ) {
                        WPDKUI::error( sprintf( '%s: %s', __( 'Error while deleting order', WPXSMARTSHOP_TEXTDOMAIN ), $result->get_error_message() ) );
                    } else {
                        WPDKUI::message( __( 'Orders(s) deleted successfully', WPXSMARTSHOP_TEXTDOMAIN ) );
                    }
                }

            case 'update':
                if ( WPDKForm::isNonceVerify( 'orders' ) ) {
                    $values = array(
                        'order_datetime'   => WPDKDateTime::dateTime2MySql( $_POST['order_datetime'], __( 'm/d/Y H:i', WPXSMARTSHOP_TEXTDOMAIN ) ),
                        'subtotal'         => $_POST['subtotal'],
                        'tax'              => $_POST['tax'],
                        'total'            => $_POST['total'],
                        'bill_first_name'  => $_POST['bill_first_name'],
                        'bill_last_name'   => $_POST['bill_last_name'],
                        'bill_address'     => $_POST['bill_address'],
                        'bill_country'     => $_POST['bill_country'],
                        'bill_town'        => $_POST['bill_town'],
                        'bill_zipcode'     => $_POST['bill_zipcode'],
                        'bill_email'       => sanitize_email( $_POST['bill_email'] ),
                        'bill_phone'       => $_POST['bill_phone'],
                        'status'           => $_POST['status']
                    );
                    $id_order = absint( $_POST['id'] );
                    $result = WPXSmartShopOrders::update( $id_order, $values );

                    if ( is_wp_error( $result ) ) {
                        WPDKUI::error( sprintf( '%s: %s', __( 'Error while updating order', WPXSMARTSHOP_TEXTDOMAIN ), $result->get_error_message() ) );
                    } else {
                        WPDKUI::message( __( 'Orders updated successfully', WPXSMARTSHOP_TEXTDOMAIN ) );
                        /* Aggiorno contatori */
                        WPXSmartShopOrders::updateProductsQuantityWithOrderID( $id_order );
                    }
                }
                break;

            case 'insert':
                /* L'add order vuole i parametri passati in linea */
                if ( WPDKForm::isNonceVerify( 'orders' ) ) {
                    $values = array(
                        'subtotal'            => $_POST['subtotal'],
                        'tax'                 => $_POST['tax'],
                        'total'               => $_POST['total'],
                        'bill_first_name'     => $_POST['bill_first_name'],
                        'bill_last_name'      => $_POST['bill_last_name'],
                        'bill_address'        => $_POST['bill_address'],
                        'bill_country'        => $_POST['bill_country'],
                        'bill_town'           => $_POST['bill_town'],
                        'bill_zipcode'        => $_POST['bill_zipcode'],
                        'bill_email'          => sanitize_email( $_POST['bill_email'] ),
                        'bill_phone'          => $_POST['bill_phone'],
                    );

                    $result = WPXSmartShopOrders::create( $values );

                    if ( is_wp_error( $result ) ) {
                        WPDKUI::error( sprintf( '%s: %s', __( 'Error while adding order', WPXSMARTSHOP_TEXTDOMAIN ), $result->get_error_message() ) );
                    } else {
                        WPDKUI::message( __( 'Order added successfully', WPXSMARTSHOP_TEXTDOMAIN ) );
                    }
                }
                break;
            case 'new':
                WPXSmartShopOrdersViewController::editView();
                /* Blocco esecuzione list table */
                return true;
                break;
            case 'edit':
                WPXSmartShopOrdersViewController::editView( $_REQUEST['id_order'] );
                /* Blocco esecuzione list table */
                return true;
                break;
        }

        /* Display list table */
        return false;
    }

    /** * */
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

        $this->_column_headers = $this->get_column_info();

        /**
         * Optional. You can handle your bulk actions however you see fit. In this
         * case, we'll handle them within our package just to keep things clean.
         */
        if ( $this->process_bulk_action() ) {
            return true;
        }

        /* Costruisco la select */
        $where        = 'WHERE 1';
        $orderby      = isset( $_GET['orderby'] ) ? $_GET['orderby'] : kWPSmartShopOrdersListTableDefaultOrderBy;
        $order        = isset( $_GET['order'] ) ? $_GET['order'] : kWPSmartShopOrdersListTableDefaultOrder;

        /* Nomi delle tabelle */
        $table_orders = WPXSmartShopOrders::tableName();
        $table_stats  = WPXSmartShopStats::tableName();

        /* Group by */
        $group_by = 'GROUP BY stats.id_order';
        $group_by = '';

        /* Where condiction filters */

        /* Status */
        if ( isset( $_GET['status'] ) ) {
            if ( $_GET['status'] != 'all' ) {
                $where .= sprintf( " AND orders.`status` = '%s'", esc_attr( $_GET['status'] ) );
            } else {
                $where .= " AND orders.`status` <> 'trash'";
            }
        } else {
            $where .= " AND orders.`status` <> 'trash'";
        }

        /* Users */
        if ( isset( $_GET['wpss-order-user-filter'] ) && !empty( $_GET['wpss-order-user-filter'] ) ) {
            $where .= sprintf( ' AND `id_user` = %s', esc_attr( $_GET['wpss-order-user-filter'] ) );
        }

        /* Users order */
        if ( isset( $_GET['wpss-order-userorder-filter'] ) && !empty( $_GET['wpss-order-userorder-filter'] ) ) {
            $where .= sprintf( ' AND `id_user_order` = %s', esc_attr( $_GET['wpss-order-userorder-filter'] ) );
        }

        /* Payment Type */
        if ( isset( $_GET['wpss-order-payment-type-filter'] ) && !empty( $_GET['wpss-order-payment-type-filter'] ) ) {
            $where .= sprintf( ' AND `payment_type` = "%s"', esc_attr( $_GET['wpss-order-payment-type-filter'] ) );
        }

        /* Payment Gateway */
        if ( isset( $_GET['wpss-order-payment-gateway-filter'] ) && !empty( $_GET['wpss-order-payment-gateway-filter'] ) ) {
            $where .= sprintf( ' AND `payment_gateway` = "%s"', esc_attr( $_GET['wpss-order-payment-gateway-filter'] ) );
        }

       /* Products */
//        if ( isset( $_GET['wpss-order-product-filter'] ) && !empty( $_GET['wpss-order-product-filter'] ) ) {
//            $where .= sprintf( ' AND stats.id_product = %s', esc_attr( $_GET['wpss-order-product-filter'] ) );
//        }

        /* Search for track ID */
        if ( isset( $_GET['wpxss_filter_track_id'] ) && !empty( $_GET['wpxss_filter_track_id'] ) ) {
            $where .= ' AND track_id LIKE "%' . esc_attr( $_GET['wpxss_filter_track_id'] ) . '%"';
        }

        /* Search for transaction ID */
        if ( isset( $_GET['wpxss_orders_filter_transaction_id'] ) && !empty( $_GET['wpxss_orders_filter_transaction_id'] ) ) {
            $where .= ' AND transaction_id LIKE "%' . esc_attr( $_GET['wpxss_orders_filter_transaction_id'] ) . '%"';
        }

        /* Search for order ID */
        if ( isset( $_GET['wpxss_filter_id'] ) && !empty( $_GET['wpxss_filter_id'] ) ) {
            $where .= ' AND orders.id = ' . absint( esc_attr( $_GET['wpxss_filter_id'] ) );
        }

        /* Date */
        if ( isset( $_GET['wpss-order-datestart-filter'] ) && !empty( $_GET['wpss-order-datestart-filter'] ) ) {
            $date_start_value = WPDKDateTime::dateTime2MySql( esc_attr( $_GET['wpss-order-datestart-filter'] ), __( 'm/d/Y H:i', WPXSMARTSHOP_TEXTDOMAIN ) );
        } elseif ( isset( $_GET['wpss-order-datestart-filter'] ) && empty( $_GET['wpss-order-datestart-filter'] ) ) {
            $date_start_value = date( MYSQL_DATE_TIME, time() - 60 * 60 * 24 * 365 * 30 );
        } else {
            $date_start_value = date( 'Y-m-d 00:01:00' );
        }

        if ( isset( $_GET['wpss-order-dateend-filter'] ) && !empty( $_GET['wpss-order-dateend-filter'] ) ) {
            $date_end_value = WPDKDateTime::dateTime2MySql( esc_attr( $_GET['wpss-order-dateend-filter'] ), __( 'm/d/Y H:i', WPXSMARTSHOP_TEXTDOMAIN ) );
        } else {
            $date_end_value = date( MYSQL_DATE_TIME );
        }

        $where .= sprintf( ' AND TIMESTAMP( order_datetime ) BETWEEN "%s" AND "%s" ', $date_start_value, $date_end_value );

        $sql = <<< SQL
SELECT orders.*,
       users.display_name AS user_display_name,
       users_orders.display_name AS user_order_display_name

FROM `{$table_orders}` AS orders

LEFT JOIN `{$wpdb->users}` AS users ON orders.id_user = users.ID
LEFT JOIN `{$wpdb->users}` AS users_orders ON orders.id_user_order = users_orders.ID

{$where}
{$group_by}
ORDER BY `{$orderby}` {$order}
SQL;

        $data = $wpdb->get_results( $sql, ARRAY_A );

        $this->count = count( $data );

        if ( ( $buffer = WPXSmartShopOrders::exportCSV( $data ) ) ) {
            set_transient( 'wpxss_orders_csv', $buffer );
        }

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
        if ( !$this->_printing ) {
            $data = array_slice( $data, ( ( $current_page - 1 ) * $per_page ), $per_page );
        }
            /**
             * REQUIRED. Now we can add our *sorted* data to the items property, where
             * it can be used by the rest of the class.
             */
            $this->items = $data;

            /**
             * REQUIRED. We also have to register our pagination options & calculations.
             */
        if ( !$this->_printing ) {
            $this->set_pagination_args( array( 'total_items'   => $total_items, 'per_page'      => $per_page, 'total_pages'   => ceil( $total_items / $per_page ) ) );
        }
    }

    // ---

    function filters_tablenav() {

        if ( $this->_printing ) {
            return;
        }

        /* Date start. */

        /* Devo impostare la data alla mezzanotte e uno di oggi mantenendo altresì il formato corretto, quindi opero per
        localizzazione e sostituzione manuale.
        */
        /* @todo Aggiungere filtri e/o meglio impostazioni da backend */
        $format           = __( 'm/d/Y H:i', WPXSMARTSHOP_TEXTDOMAIN );
        $format           = strtr( $format, array( 'H'   => '00', 'i'   => '01' ) );
        $date_start_value = date( $format );
        if ( isset( $_GET['wpss-order-datestart-filter'] ) ) {
            $date_start_value = $_GET['wpss-order-datestart-filter'];
        }

        $item = array(
            'type'   => WPDK_FORM_FIELD_TYPE_DATETIME,
            'name'   => 'wpss-order-datestart-filter',
            'label'  => __( 'Date start', WPXSMARTSHOP_TEXTDOMAIN ),
            'value'  => $date_start_value
        );

        /* @todo Da eliminare quando WPDKForm non emetterà più direttamente l'output */
        ob_start();
        WPDKForm::htmlItem( $item );
        $date_start = ob_get_contents();
        ob_end_clean();

        /* Date end. */
        /* @todo Aggiungere filtri e/o meglio impostazioni da backend */
        $date_end_value = date( __( 'm/d/Y H:i', WPXSMARTSHOP_TEXTDOMAIN ) );
        if ( isset( $_GET['wpss-order-dateend-filter'] ) ) {
            $date_end_value = $_GET['wpss-order-dateend-filter'];
        }
        $item = array(
            'type'   => WPDK_FORM_FIELD_TYPE_DATETIME,
            'name'   => 'wpss-order-dateend-filter',
            'label'  => __( 'Date to', WPXSMARTSHOP_TEXTDOMAIN ),
            'value'  => $date_end_value
        );

        /* @todo Da eliminare quando WPDKForm non emetterà più direttamente l'output */
        ob_start();
        WPDKForm::htmlItem( $item );
        $date_end = ob_get_contents();
        ob_end_clean();

        $button_label = __( 'Apply', WPXSMARTSHOP_TEXTDOMAIN );

        /* Get filters */
        $selected_user            = isset( $_GET['wpss-order-user-filter'] ) ? $_GET['wpss-order-user-filter'] : '';
        $selected_user_order      = isset( $_GET['wpss-order-userorder-filter'] ) ? $_GET['wpss-order-userorder-filter'] : '';
        $selected_payment_type    = isset( $_GET['wpss-order-payment-type-filter'] ) ? $_GET['wpss-order-payment-type-filter'] : '';
        $selected_payment_gateway = isset( $_GET['wpss-order-payment-gateway-filter'] ) ? $_GET['wpss-order-payment-gateway-filter'] : '';
        //$selected_product         = isset( $_GET['wpss-order-product-filter'] ) ? $_GET['wpss-order-product-filter'] : '';

        $filter_users           = WPXSmartShopOrders::selectFilterUsers( 'wpss-order-user-filter', $selected_user );
        $filter_users_order     = WPXSmartShopOrders::selectFilterUsersOrder( 'wpss-order-userorder-filter', $selected_user_order );
        $filter_payment_type    = WPXSmartShopOrders::selectFilterPaymentType( 'wpss-order-payment-type-filter', $selected_payment_type );
        $filter_payment_gateway = WPXSmartShopOrders::selectFilterPaymentGateway( 'wpss-order-payment-gateway-filter', $selected_payment_gateway );
        //$filter_product         = WPXSmartShopOrders::selectFilterProduct( 'wpss-order-product-filter', $selected_product );

        $export_csv_url   = add_query_arg( array( 'export_orders_csv' => '' ) );
        $export_csv_label = __( 'Export CSV', WPXSMARTSHOP_TEXTDOMAIN );

        $print_url   = add_query_arg( array( 'print_orders' => '', 'noheader' => '' ) );
        $print_label = __( 'Print', WPXSMARTSHOP_TEXTDOMAIN );

        $transaction_label = __( 'Transaction ID', WPXSMARTSHOP_TEXTDOMAIN );
        $wpxss_orders_filter_transaction_id = isset( $_GET['wpxss_orders_filter_transaction_id'] ) ? esc_attr($_GET['wpxss_orders_filter_transaction_id']) : '';
 		//BNMExtendsOrders::updateOrderStatus();
        $html = <<< HTML
        <div class="alignright actions">
            <a class="button" href="{$export_csv_url}">{$export_csv_label}</a>
        </div>

        <div class="alignright actions">
            <a class="button" href="{$print_url}">{$print_label}</a>
        </div>

        <div class="clearfix wpdk-list-table-filter">
            <div class="wpdk-list-table-filter-row">
                 <label for="wpxss_orders_filter_transaction_id" class="wpdk-form-label wpdk-form-input user_email">{$transaction_label}:</label>
                 <input type="text" title="{$transaction_label}" size="24" value="{$wpxss_orders_filter_transaction_id}" id="wpxss_orders_filter_transaction_id" name="wpxss_orders_filter_transaction_id" class="wpdk-form-input" />
                {$date_start}
                {$date_end}
            </div>
            <div class="wpdk-list-table-filter-row">
                {$filter_users}
                {$filter_users_order}
            </div>
            <div class="wpdk-list-table-filter-row">
                {$filter_payment_type}
                {$filter_payment_gateway}
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

	/// Display the search box for trackid and id order
	function search_text_box () {
        if ( empty( $_REQUEST['wpxss_filter_track_id'] ) && !$this->has_items() ) {
            return;
        }
        if ( empty( $_REQUEST['wpxss_filter_id'] ) && !$this->has_items() ) {
            return;
        }

        if ( !empty( $_REQUEST['orderby'] ) ) {
            echo '<input type="hidden" name="orderby" value="' . esc_attr( $_REQUEST['orderby'] ) . '" />';
        }
        if ( !empty( $_REQUEST['order'] ) ) {
            echo '<input type="hidden" name="order" value="' . esc_attr( $_REQUEST['order'] ) . '" />';
        }
?>
<p class="search-box">
	<label class="screen-reader-text" for="wpxss_filter_track_id"><?php _e('Track ID', WPXSMARTSHOP_TEXTDOMAIN ) ?>:</label>
	<input type="search" class="wpdk-form-input" id="wpxss_filter_track_id>" name="wpxss_filter_track_id" value="<?php echo isset($_REQUEST['wpxss_filter_track_id']) ? esc_attr( stripslashes( $_REQUEST['wpxss_filter_track_id'] ) ) : '' ?>" />
	<?php submit_button( __('Track ID', WPXSMARTSHOP_TEXTDOMAIN ), 'button', false, false, array('id' => 'search-submit') ); ?>

	<label class="screen-reader-text" for="wpxss_filter_id"><?php _e('Order ID', WPXSMARTSHOP_TEXTDOMAIN ) ?>:</label>
	<input type="search" class="wpdk-form-input wpdk-form-number" size="4" id="wpxss_filter_id>" name="wpxss_filter_id" value="<?php echo isset($_REQUEST['wpxss_filter_id']) ? esc_attr( stripslashes( $_REQUEST['wpxss_filter_id'] ) ) : '' ?>" />
	<?php submit_button( __('Order ID', WPXSMARTSHOP_TEXTDOMAIN ), 'button', false, false, array('id' => 'search-submit') ); ?>
</p>
<?php
	}

}