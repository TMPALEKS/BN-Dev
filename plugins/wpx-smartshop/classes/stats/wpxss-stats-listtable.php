<?php
/**
 * @class              WPXSmartShopStatsListTable
 *
 * @description
 *
 * @package            wpx SmartShop
 * @subpackage         stats
 * @author             =undo= <g.fazioli@wpxtre.me>
 * @copyright          Copyright (c) 2012 wpXtreme, Inc.
 * @link               http://wpxtre.me
 * @created            13/06/12
 * @version            1.0.0
 *
 */

if (!class_exists('WP_List_Table')) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

define('WPXSMARTSHOP_STATS_LISTTABLE_DEFAULT_ORDER', 'desc');
define('WPXSMARTSHOP_STATS_LISTTABLE_DEFAULT_ORDER_BY', 'stats.id_order');

class WPXSmartShopStatsListTable extends WP_List_Table {

    private $_table_name;
    private $_roles;
    private $_roles_names;

    /**
     * @var int Numero record estratti
     */
    public $count = 0;

    /**
     * @var bool Se true siamo in stampa
     */
    public $_printing;


    /**
     * Init
     */
    function __construct(  $printing = false ) {
        /* Set parent defaults */
        parent::__construct( array( 'singular'   => 'id_stats', 'plural'     => 'stats', 'ajax'       => false ) );
        $this->_table_name  = WPXSmartShopStats::tableName();
        $this->_roles       = new WP_Roles();

        /* Used below for price rule column. */
        $this->_roles_names = $this->_roles->get_names();

        /* Printing mode */
        $this->_printing = $printing;
    }

    function no_items() {
        _e( 'No Products found.', WPXSMARTSHOP_TEXTDOMAIN );
        echo '<br/>';
        _e( 'Please, check your search filters parameters.', WPXSMARTSHOP_TEXTDOMAIN );
    }

    /* Azione inline */
    function get_views() {
        if ( $this->_printing ) {
            return array();
        }

        $statuses     = WPXSmartShopStats::statuses();
        $views         = array();
        $filter_status = isset( $_GET['status'] ) ? $_GET['status'] : 'all';
        foreach ( $statuses as $key => $status ) {
            if ( intval( $status['count'] ) > 0 ) {

                $current = ( $filter_status == $key ) ? 'class="current"' : '';
                $href    = add_query_arg( array( 'status' => $key, 'action' => false ) );

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
    function column_default( $item, $column_name ) {
        $column_custom = array();
        $column_custom = apply_filters('wpxss_column_custom', $item);
        switch ( $column_name ) {
            case $column_custom['column_name']:
                return $column_custom['column_default'];
            break;
            case 'user':
                if ( is_null( $item['user_display_name'] ) ) {
                    return sprintf( '<span style="color:#789">%s</span>', __( 'No user found', WPXSMARTSHOP_TEXTDOMAIN ) );
                } else {
                    /* @todo Metere la visualizzazione dell'avatar come impostazione */
                    //return sprintf( '%s %s<br/><strong>(%s)</strong>', WPDKUser::gravatar( $item['id_user'], 16 ), $item['user_display_name'], WPDKUser::roleNameForUserID( $item['id_user'] ) );
                    return sprintf( '%s<br/><strong>(%s)</strong>', $item['user_display_name'], WPDKUser::roleNameForUserID( $item['id_user'] ) );
                }
            case 'user_order':
                if ( is_null( $item['user_order_display_name'] ) ) {
                    return sprintf( '<span style="color:#789">%s</span>', __( 'No user found', WPXSMARTSHOP_TEXTDOMAIN ) );
                } else {
                    /* @todo Metere la visualizzazione dell'avatar come impostazione */
                    //return sprintf( '%s %s<br/><strong>(%s)</strong>', WPDKUser::gravatar( $item['id_user_order'], 16 ), $item['user_order_display_name'], WPDKUser::roleNameForUserID( $item['id_user_order'] ) );
                    return sprintf( '%s<br/><strong>(%s)</strong>', $item['user_order_display_name'], WPDKUser::roleNameForUserID( $item['id_user_order'] ) );
                }
            case 'model':
                /**
                 * @filters
                 *
                 * @param string $localizable_value
                 * @param int    $id_product
                 * @param string $id_variant
                 * @param array  $variant
                 * @param string $key
                 */
                return apply_filters( 'wpss_product_variant_localizable_value', $item[$column_name] );

            case 'product_title':
            case 'id_variant':
            case 'coupon_uniqcode':
                if ( is_null( $item[$column_name] ) ) {
                    return '-';
                }
                if ( !is_null( $item['coupon_product_maker_name'] ) ) {
                    return sprintf( '%s<br/>(%s)', $item[$column_name], $item['coupon_product_maker_name'] );
                }
                return $item[$column_name];
                break;

            case 'price_rule':
                $price_rule_code = $item[$column_name];
                if ( $price_rule_code == kWPSmartShopProductTypeRuleOnlinePrice ) {
                    /**
                     * @filters
                     * @todo Da documentare
                     */
                    $price_rule = apply_filters( 'wpxss_stats_column_price_rule_online', __( 'Online', WPXSMARTSHOP_TEXTDOMAIN ), $price_rule_code );
                } elseif ( $price_rule_code == 'base_price' ) {
                    /**
                     * @filters
                     * @todo Da documentare
                     */
                    $price_rule = apply_filters( 'wpxss_stats_column_price_rule_base_price', __( 'Base price', WPXSMARTSHOP_TEXTDOMAIN ), $price_rule_code );
                } elseif ( $price_rule_code == kWPSmartShopProductTypeRuleDatePrice ) {
                    /**
                     * @filters
                     * @todo Da documentare
                     */
                    $price_rule = apply_filters( 'wpxss_stats_column_price_rule_date_range', __( 'Date range', WPXSMARTSHOP_TEXTDOMAIN ), $price_rule_code );
                } elseif ( isset( $this->_roles_names[$price_rule_code] ) ) {
                    $price_rule = $this->_roles_names[$price_rule_code];
                } else {
                    /**
                     * Invia il codice della regola per attività personalizzate esterne
                     *
                     * @filters
                     *
                     * @param string Decsription
                     * @param string ID key ruole
                     *
                     * @todo Da documentare
                     */
                    $price_rule = apply_filters( 'wpxss_stats_column_price_rule', __( 'Unknown', WPXSMARTSHOP_TEXTDOMAIN ), $price_rule_code );
                }
                return $price_rule;
                break;

            case 'price_on_purchase':
                return sprintf( '%s / <strong>%s</strong>', $item['product_amount'], $item['amount'] );
                break;

            default:
                return print_r( $item, true ); // Show the whole array for troubleshooting purposes
                break;
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

        return sprintf( '# <span style="color:#789;">%s</span> - %s<br/>%s<br/>%s',
            $item['id_order'],
            $item['track_id'],
            WPDKDateTime::timeNewLine( mysql2date( __( 'm/d/Y H:i', WPXSMARTSHOP_TEXTDOMAIN ), $item['order_datetime'] ) ),
            $row_actions );
    }

    /**  */
    function column_cb($item) {
        return sprintf('<input type="checkbox" name="%1$s[]" value="%2$s" />', $this->_args['singular'], $item['id'] );
    }

    /** */
    function get_columns() {
        $columns = array(
            'cb'                => '<input type="checkbox" />',
            'id'                => __( 'Order', WPXSMARTSHOP_TEXTDOMAIN ),
            'product_title'     => __( 'Product title', WPXSMARTSHOP_TEXTDOMAIN ),
            'user_order'        => __( 'Ordered for', WPXSMARTSHOP_TEXTDOMAIN ),
            'user'              => __( 'Ordered by', WPXSMARTSHOP_TEXTDOMAIN ),
            'id_variant'        => __( 'Variant', WPXSMARTSHOP_TEXTDOMAIN ),
            'model'             => __( 'Model', WPXSMARTSHOP_TEXTDOMAIN ),
            'coupon_uniqcode'   => __( 'Coupon', WPXSMARTSHOP_TEXTDOMAIN ),
            'price_rule'        => __( 'Price rule', WPXSMARTSHOP_TEXTDOMAIN ),
            'price_on_purchase' => __( 'Price / Purchased', WPXSMARTSHOP_TEXTDOMAIN ) . ' ' . WPXSmartShopCurrency::currencySymbol(),
        );
        if ( $this->_printing ) {
            unset( $columns['cb'] );
        }
        $columns = apply_filters('wpxss_add_columns_listtable',$columns);
        return $columns;
    }

    /**  */
    function get_sortable_columns() {
        $sortable_columns = array(
            'id'              => array( 'id', false ),
            'user_order'      => array( 'user_order_display_name', false ),
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
                $result = WPXSmartShopStats::trash( $this->_table_name, $_REQUEST['id_stats'], 'status' );
                /* @todo Check $result */
                break;

            case 'untrash':
                $result = WPXSmartShopStats::untrash( $this->_table_name, $_REQUEST['id_stats'], 'status' );
                /* @todo Check $result */
                break;

            case 'delete':
                if ( isset( $_REQUEST['id_stats'] ) ) {
                    $result = WPXSmartShopStats::delete( $this->_table_name, $_REQUEST['id_stats'] );
                    if ( is_wp_error( $result ) ) {
                        WPDKUI::error( sprintf( '%s: %s', __( 'Error while deleting stats', WPXSMARTSHOP_TEXTDOMAIN ), $result->get_error_message() ) );
                    } else {
                        WPDKUI::message( __( 'Stats deleted successfully', WPXSMARTSHOP_TEXTDOMAIN ) );
                    }
                }
                break;

            case 'update':
                if ( WPDKForm::isNonceVerify( 'stats' ) ) {
                    /* @todo Da fare overwrite */
                    //$result = WPXSmartShopStats::update( $this->_table_name, $_REQUEST['id_stats'] );
                }
                break;

            case 'insert':
                /* L'add order vuole i parametri passati in linea */
                if ( WPDKForm::isNonceVerify( 'stats' ) ) {
                    $args = array(
                        'subtotal'   => $_POST['subtotal'],
                        'tax'        => $_POST['tax'],
                        'total'      => $_POST['total'],

                        'bill_first_name'     => $_POST['bill_first_name'],
                        'bill_last_name'      => $_POST['bill_last_name'],
                        'bill_address'        => $_POST['bill_address'],
                        'bill_country'        => $_POST['bill_country'],
                        'bill_town'           => $_POST['bill_town'],
                        'bill_zipcode'        => $_POST['bill_zipcode'],
                        'bill_email'          => sanitize_email( $_POST['bill_email'] ),
                        'bill_phone'          => $_POST['bill_phone'],
                    );

                    $result = WPXSmartShopStats::create( $args );

                    if ( is_wp_error( $result ) ) {
                        WPDKUI::error( sprintf( '%s: %s', __( 'Error while adding stats', WPXSMARTSHOP_TEXTDOMAIN ), $result->get_error_message() ) );
                    } else {
                        WPDKUI::message( __( 'Stats added successfully', WPXSMARTSHOP_TEXTDOMAIN ) );
                    }
                }
                break;
            case 'new':
                //WPXSmartShopStatsViewController::editView();
                WPDKUI::message( 'Not yet implement!' );
//                return true;
                break;
            case 'edit':
                //WPXSmartShopStatsViewController::editView( $_REQUEST['id_stats'] );
                WPDKUI::message( 'Not yet implement!' );
//                return true;
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

        /* Columns Header */
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
        $orderby      = isset( $_GET['orderby'] ) ? $_GET['orderby'] : WPXSMARTSHOP_STATS_LISTTABLE_DEFAULT_ORDER_BY;
        $order        = isset( $_GET['order'] ) ? $_GET['order'] : WPXSMARTSHOP_STATS_LISTTABLE_DEFAULT_ORDER;

        /* Nomi delle tabelle */
        $table_stats    = WPXSmartShopStats::tableName();
        $table_orders   = WPXSmartShopOrders::tableName();
        $table_coupons  = WPXSmartShopCoupons::tableName();
        $table_products = $wpdb->posts;

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

        /* Variant */
        if ( isset( $_GET['wpss-stats-variant-filter'] ) && !empty( $_GET['wpss-stats-variant-filter'] ) ) {
            $where .= sprintf( ' AND stats.`id_variant` = "%s"', esc_attr( $_GET['wpss-stats-variant-filter'] ) );
        }

        /* Model */
        if ( isset( $_GET['wpss-stats-model-filter'] ) && !empty( $_GET['wpss-stats-model-filter'] ) ) {
            $where .= sprintf( ' AND stats.`model` = "%s"', esc_attr( $_GET['wpss-stats-model-filter'] ) );
        }

        /* User for */
        if ( isset( $_GET['wpxss_stats_filter_id_user_for'] ) && !empty( $_GET['wpxss_stats_filter_id_user_for'] ) ) {
            $where .= sprintf( ' AND orders.`id_user_order` = %s', esc_attr( $_GET['wpxss_stats_filter_id_user_for'] ) );
        }

        /* Product */
        if ( isset( $_GET['wpss-stats-product-filter'] ) && !empty( $_GET['wpss-stats-product-filter'] ) ) {
            $where .= sprintf( ' AND stats.`id_product` = %s', esc_attr( $_GET['wpss-stats-product-filter'] ) );
        }

        /* Date */

        if( isset( $_GET['wpss-stats-datestart-filter'] ) && !empty( $_GET['wpss-stats-datestart-filter'] ) ) {
            $date_start_value = WPDKDateTime::dateTime2MySql( esc_attr( $_GET['wpss-stats-datestart-filter'] ), __( 'm/d/Y H:i', WPXSMARTSHOP_TEXTDOMAIN ) );
        } else {
          	//$date_start_value = date( MYSQL_DATE_TIME, time() - 60*60*24*365*30 );
          	$date_start_value = date( MYSQL_DATE_TIME, time() - 60*60*24*7 );
        }

        if( isset( $_GET['wpss-stats-dateend-filter'] ) && !empty( $_GET['wpss-stats-dateend-filter'] ) ) {
            $date_end_value = WPDKDateTime::dateTime2MySql( esc_attr( $_GET['wpss-stats-dateend-filter'] ), __( 'm/d/Y H:i', WPXSMARTSHOP_TEXTDOMAIN ) );
        } else {
            $date_end_value = date( MYSQL_DATE_TIME );
        }

        if( isset( $_GET['wpss-stats-user-role'] ) && !empty( $_GET['wpss-stats-user-role'] ) ) {
            $where .= sprintf( ' AND usermeta.meta_value LIKE \'%%"%s"%%\' ', $_GET['wpss-stats-user-role'] );
        }

        $where .= sprintf( ' AND TIMESTAMP( orders.order_datetime ) BETWEEN "%s" AND "%s" ', $date_start_value, $date_end_value );

        //$where .= apply_filters('wpxss_listtable_query_where',$where);

        $sql = <<< SQL
SELECT stats.*,
       orders.note AS order_note,
       orders.order_datetime AS order_datetime,
       orders.track_id AS track_id,
       orders.id_user,
       orders.id_user_order,
       orders.bill_first_name,
       orders.bill_last_name,
       orders.bill_email,
       orders.bill_phone,
       orders.status,

       products.post_title AS coupon_product_maker_name,

       coupons.uniqcode AS coupon_uniqcode,
       users.display_name AS user_display_name,
       users_orders.display_name AS user_order_display_name,
       usermeta.meta_value
FROM `{$table_stats}` AS stats
LEFT JOIN `{$table_orders}` AS orders ON orders.id = stats.id_order
LEFT JOIN `{$table_coupons}` AS coupons ON (stats.id_coupon <> 0 AND coupons.id = stats.id_coupon)
LEFT JOIN `{$table_products}` AS products ON products.ID = coupons.id_product_maker
LEFT JOIN `{$wpdb->users}` AS users ON orders.id_user = users.ID
LEFT JOIN `{$wpdb->users}` AS users_orders ON orders.id_user_order = users_orders.ID
LEFT JOIN `{$wpdb->usermeta}` AS usermeta ON orders.id_user_order = usermeta.user_id AND usermeta.meta_key = '{$wpdb->prefix}capabilities'
{$where}
ORDER BY {$orderby} {$order}
SQL;



        $data = $wpdb->get_results($sql, ARRAY_A);

        $this->count = count( $data );

        if ( ( $buffer = WPXSmartShopStats::exportCSV( $data ) ) ) {
            set_transient( 'wpxss_stats_csv', $buffer );
        }

        //Gestisce tutti i tipi di export che vorranno essere aggiunti in seguito
        do_action('wpxss_stats_csv_should_export', $data);

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
            $this->set_pagination_args( array( 'total_items' => $total_items, 'per_page' => $per_page, 'total_pages' => ceil( $total_items / $per_page ) ) );
        }
    }

    // ---

    function filters_tablenav() {

        if ( $this->_printing ) {
            return;
        }

        /* Date start. */
        /* Se la data di start non è impostata, prendo oggi e torno indietro di una settimana. */
        /* @todo Aggiungere filtri o meglio impostazioni da backend */

        $date_start_value = date( __( 'm/d/Y H:i', WPXSMARTSHOP_TEXTDOMAIN ), time() - 60 * 60 * 24 * 7 );
        if ( isset( $_GET['wpss-stats-datestart-filter'] ) ) {
            $date_start_value = $_GET['wpss-stats-datestart-filter'];
        }

        $item = array(
            'type'   => WPDK_FORM_FIELD_TYPE_DATETIME,
            'name'   => 'wpss-stats-datestart-filter',
            'label'  => __( 'Date start', WPXSMARTSHOP_TEXTDOMAIN ),
            'value'  => $date_start_value
        );

        /* @todo Da eliminare quando WPDKForm non emetterà più direttamente l'output */
        ob_start();
        WPDKForm::htmlItem( $item );
        $date_start = ob_get_contents();
        ob_end_clean();

        /* Date end. */

        $date_end_value = date( __( 'm/d/Y H:i', WPXSMARTSHOP_TEXTDOMAIN ) );
        if ( isset( $_GET['wpss-stats-dateend-filter'] ) ) {
            $date_end_value = $_GET['wpss-stats-dateend-filter'];
        }
        $item = array(
            'type'   => WPDK_FORM_FIELD_TYPE_DATETIME,
            'name'   => 'wpss-stats-dateend-filter',
            'label'  => __( 'Date to', WPXSMARTSHOP_TEXTDOMAIN ),
            'value'  => $date_end_value
        );

        /* @todo Da eliminare quando WPDKForm non emetterà più direttamente l'output */
        ob_start();
        WPDKForm::htmlItem( $item );
        $date_end = ob_get_contents();
        ob_end_clean();

        $button_label = __( 'Apply', WPXSMARTSHOP_TEXTDOMAIN );

        /* Get more filters. */
        $selected_variant = isset( $_GET['wpss-stats-variant-filter'] ) ? $_GET['wpss-stats-variant-filter'] : '';
        $filter_variant   = WPXSmartShopStats::selectFilterVariant( 'wpss-stats-variant-filter', $selected_variant );

        $selected_model = isset( $_GET['wpss-stats-model-filter'] ) ? $_GET['wpss-stats-model-filter'] : '';
        $filter_model   = WPXSmartShopStats::selectFilterModel( 'wpss-stats-model-filter', $selected_model );

        $selected_product = isset( $_GET['wpss-stats-product-filter'] ) ? $_GET['wpss-stats-product-filter'] : '';
        $filter_product   = WPXSmartShopStats::selectFilterProduct( 'wpss-stats-product-filter', $selected_product );

        $selected_user_role = isset( $_GET['wpss-stats-user-role'] ) ? $_GET['wpss-stats-user-role'] : '';
        $filter_user_role   = WPXSmartShopStats::selectFilterUserRole( 'wpss-stats-user-role', $selected_user_role );

        $export_csv_url   = add_query_arg( array( 'export_stats_csv' => '' ) );
        $export_csv_label = __( 'Export CSV', WPXSMARTSHOP_TEXTDOMAIN );

        $export_csv_sap_url   = add_query_arg( array( 'export_stats_sap_csv' => '' ) );
        $export_csv_sap_label = __( 'Export CSV for SAP', WPXSMARTSHOP_TEXTDOMAIN );

        $print_url   = add_query_arg( array( 'print_stats' => '', 'noheader' => '' ) );
        $print_label = __( 'Print', WPXSMARTSHOP_TEXTDOMAIN );

        $user_order_text  = isset( $_GET['wpxss_stats_filter_user'] ) ? $_GET['wpxss_stats_filter_user'] : '';
        $user_order_id    = isset( $_GET['wpxss_stats_filter_id_user_for'] ) ? $_GET['wpxss_stats_filter_id_user_for'] : '';
        $user_order_label = __( 'Ordered for', WPXSMARTSHOP_TEXTDOMAIN );

        $html = <<< HTML
        <div class="alignright actions">
            <a class="button" href="{$export_csv_url}">{$export_csv_label}</a>
        </div>

        <div class="alignright actions">
            <a class="button" href="{$export_csv_sap_url}">{$export_csv_sap_label}</a>
        </div>

        <div class="alignright actions">
            <a class="button" href="{$print_url}">{$print_label}</a>
        </div>

        <div class="clearfix wpdk-list-table-filter">
            <div class="wpdk-list-table-filter-row">
                {$date_start}
                {$date_end}
            </div>
            <div class="wpdk-list-table-filter-row">
                <label for="wpxss_stats_filter_user" class="wpdk-form-label wpdk-form-input user_email">{$user_order_label}:</label>
                <input type="text" data-autocomplete_target="wpxss_stats_filter_id_user_for" data-autocomplete_action="wpdk_action_user_by" title="{$user_order_label}" size="64" value="{$user_order_text}" id="wpxss_stats_filter_user" name="wpxss_stats_filter_user" class="wpdk-form-input" />
                <input type="hidden" value="{$user_order_id}" name="wpxss_stats_filter_id_user_for" id="wpxss_stats_filter_id_user_for">
            </div>
            <div class="wpdk-list-table-filter-row">
                {$filter_product}
                {$filter_user_role}
                {$filter_variant}
                {$filter_model}
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
