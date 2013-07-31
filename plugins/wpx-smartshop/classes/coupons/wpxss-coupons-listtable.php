<?php
/**
 * @class              WPXSmartShopCouponsListTable
 * @description        Classe dedicata alla visualizzazione dei Coupon.
 *                     Questa classe eredita da WP_List_Table ed è tratta da un esempio fornito da WordPress.
 *
 * @package            wpx SmartShop
 * @subpackage         coupons
 * @author             =undo= <g.fazioli@wpxtre.me>
 * @copyright          Copyright (c) 2012 wpXtreme, Inc.
 * @created            30/12/11
 * @version            1.0
 *
 */

if ( !class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

define( 'WPXSMARTSHOP_COUPONS_LISTTABLE_DEFAULT_ORDER', 'date_insert');
define('WPXSMARTSHOP_COUPONS_LISTTABLE_DEFAULT_ORDER_BY', 'desc');

class WPXSmartShopCouponsListTable extends WP_List_Table {

    private $_table_name;

    function __construct() {
        parent::__construct(array( 'singular' => 'id_coupon', 'plural' => 'coupons', 'ajax' => false ) );
        $this->_table_name = WPXSmartShopCoupons::tableName();
    }

    function no_items() {
        _e( 'No Coupons found.', WPXSMARTSHOP_TEXTDOMAIN );
        echo '<br/>';
        _e( 'Please, check your search filters parameters.', WPXSMARTSHOP_TEXTDOMAIN );
    }

    function get_views() {
        $statuses      = WPXSmartShopCoupons::statuses();
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
        if( $which == 'top' ) {
            echo $this->filters_tablenav();
        }
    }

    /* Default content columns */
    function column_default( $item, $column_name ) {
        switch ( $column_name ) {
            case 'value':
                return sprintf( '%s', $item['value'] );
            case 'user_owner':
                if ( is_null( $item['users_owner_display_name'] ) ) {
                    return sprintf( '<span style="color:silver">%s</span>', __( 'Anyone', WPXSMARTSHOP_TEXTDOMAIN ) );
                } else {
                    return sprintf( '%s', $item['users_owner_display_name'] );
                }            
            case 'user_maker':
                if ( is_null( $item['users_maker_display_name'] ) ) {
                    return sprintf( '<span style="color:silver">%s</span>', __( 'None', WPXSMARTSHOP_TEXTDOMAIN ) );
                } else {
                    return sprintf( '%s', $item['users_maker_display_name'] );
                }
            case 'product_name':
                if ( is_null( $item['product_name'] ) ) {
                    return sprintf( '<span style="color:silver">%s</span>', __( 'Any', WPXSMARTSHOP_TEXTDOMAIN ) );
                } else {
                    return sprintf( '<a href="%s">%s</a>', get_edit_post_link( $item['id_product'] ), WPXSmartShopProduct::thumbnail( $item['id_product'] ) );
                }
            case 'product_maker':
                if ( is_null( $item['product_maker_name'] ) ) {
                    return sprintf( '<span style="color:silver">%s</span>', __( 'None', WPXSMARTSHOP_TEXTDOMAIN ) );
                } else {
                    return sprintf( '<a href="%s">%s</a>', get_edit_post_link( $item['id_product_maker'] ), WPXSmartShopProduct::thumbnail( $item['id_product_maker'] ) );
                }
            case 'product_type_name':
                if ( is_null( $item['product_type_name'] ) ) {
                    return sprintf( '<span style="color:silver;">%s</span>', __( 'Any', WPXSMARTSHOP_TEXTDOMAIN ) );
                } else {
                    return sprintf( '%s', $item['product_type_name'] );
                }
            case 'date_insert':
            case 'date_from':
            case 'date_to':
                return WPDKDateTime::timeNewLine( mysql2date( __( 'm/d/Y H:i', WPXSMARTSHOP_TEXTDOMAIN ), $item[$column_name] ) );
            case 'user':
                if ( is_null( $item['user_display_name'] ) ) {
                    return sprintf( '<span style="color:silver">%s</span>', __( 'Nobody', WPXSMARTSHOP_TEXTDOMAIN ) );
                } else {
                    return sprintf( '%s', $item['user_display_name'] );
                }
            case 'status':
                $statuses = WPXSmartShopCoupons::arrayStatuses();
                return sprintf( '<span title="%s" class="wpdk-tooltip wpss-icon-%s"></span>', __( $statuses[$item[$column_name]]['label'], WPXSMARTSHOP_TEXTDOMAIN ), $item[$column_name] );
            default:
                return print_r( $item, true );
        }
    }

    /* Inline actions */
    function column_id( $item ) {

        $args = array(
            'id_coupon'    => $item['id'],
            'actions'      => array(
                'edit'     => __( 'Edit', WPXSMARTSHOP_TEXTDOMAIN ),
                'untrash'  => __( 'Restore', WPXSMARTSHOP_TEXTDOMAIN ),
                'delete'   => __( 'Delete', WPXSMARTSHOP_TEXTDOMAIN ),
                'trash'    => __( 'Trash', WPXSMARTSHOP_TEXTDOMAIN ),
            )
        );

        $status = isset( $_REQUEST['status'] ) ? $_REQUEST['status'] : '';

        $actions = WPDKListTable::actions( $args, $status );

        // @todo Evidenziare gli item usati e/o disponibili
        if ( isset( $item['available'] ) && $item['available'] == 'no' ) {
            return sprintf( '<span style="text-decoration: line-through">%1$s</span> %2$s', $item['uniqcode'], $this->row_actions( $actions ) );
        }

        return sprintf( '<strong>%1$s</strong> %2$s', $item['uniqcode'], $this->row_actions( $actions ) );
    }

    /**  */
    function column_cb( $item ) {
        return sprintf( '<input type="checkbox" name="%1$s[]" value="%2$s" />', $this->_args['singular'], $item['id'] );
    }

    /**  */
    function get_columns() {
        $columns = array(
            'cb'                => '<input type="checkbox" />',
            'id'                => __( 'Coupon', WPXSMARTSHOP_TEXTDOMAIN ),
            'date_insert'       => __( 'Date', WPXSMARTSHOP_TEXTDOMAIN ),
            'user_maker'        => __( 'Create by', WPXSMARTSHOP_TEXTDOMAIN ),
            'product_maker'     => __( 'Product maker', WPXSMARTSHOP_TEXTDOMAIN ),
            'value'             => __( 'Value', WPXSMARTSHOP_TEXTDOMAIN ),
            'user_owner'        => __( 'Only for', WPXSMARTSHOP_TEXTDOMAIN ),
            'product_name'      => __( 'Product', WPXSMARTSHOP_TEXTDOMAIN ),
            'product_type_name' => __( 'Product Type', WPXSMARTSHOP_TEXTDOMAIN ),
            'date_from'         => __( 'Available from', WPXSMARTSHOP_TEXTDOMAIN ),
            'date_to'           => __( 'Available to', WPXSMARTSHOP_TEXTDOMAIN ),
            'user'              => __( 'Used by', WPXSMARTSHOP_TEXTDOMAIN ),
            'status'            => __( 'Status', WPXSMARTSHOP_TEXTDOMAIN ),
        );
        return $columns;
    }

    /**  */
    function get_sortable_columns() {
        $sortable_columns = array(
            'date_insert' => array( 'date_insert', true ),
            'id'          => array( 'id' ),
        );
        return $sortable_columns;
    }

    /** * */
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

    /**  **/
    function process_bulk_action() {

        switch ($this->current_action()) {
            case 'trash':
                $result = WPXSmartShopCoupons::trash( $this->_table_name, $_REQUEST['id_coupon'], 'id', 'status' );
                break;

            case 'untrash':
                $result = WPXSmartShopCoupons::untrash( $this->_table_name, $_REQUEST['id_coupon'], 'id', 'status' );
                break;

            case 'delete':
                if (isset($_REQUEST['id_coupon'])) {
                    $result = WPXSmartShopCoupons::delete( $this->_table_name, $_REQUEST['id_coupon']);
                    if ( !is_wp_error( $result ) ) : ?>

                    <div class="updated">
                        <p><?php echo $result ?> <?php _e( 'Coupon(s) deleted successfully', WPXSMARTSHOP_TEXTDOMAIN ) ?></p>
                    </div>

                    <?php else : ?>

                    <div class="error">
                        <p><?php echo sprintf( '%s: %s', _e( 'Error while deleting coupon', WPXSMARTSHOP_TEXTDOMAIN ), $result->get_error_message() ) ?></p>
                    </div>
                    <?php endif;
                }
                return false;
            case 'update':
                if ( WPDKForm::isNonceVerify( 'coupons' ) ) {
                    $result = WPXSmartShopCoupons::update(); ?>

                <?php if ( !is_wp_error( $result ) ) : ?>

                    <div class="updated">
                        <p><?php _e( 'Coupon update successfully', WPXSMARTSHOP_TEXTDOMAIN ) ?></p>
                    </div>

                    <?php else : ?>

                    <div class="updated">
                        <p><?php echo sprintf( '%s: %s', _e( 'Error while updating coupon', WPXSMARTSHOP_TEXTDOMAIN ), $result->get_error_message() ) ?></p>
                    </div>

                    <?php endif;
                }
                break;
            case 'insert':
                if ( WPDKForm::isNonceVerify( 'coupons' ) ) {
                    $result = WPXSmartShopCoupons::create(); ?>
                <?php if ( !is_wp_error( $result ) ) : ?>

                    <div class="updated">
                        <p><?php _e( 'Coupon(s) added successfully', WPXSMARTSHOP_TEXTDOMAIN ) ?></p>
                    </div>

                    <?php else : ?>

                    <div class="updated">
                        <p><?php echo sprintf( '%s: %s', _e( 'Error while adding coupon', WPXSMARTSHOP_TEXTDOMAIN ), $result->get_error_message() ) ?></p>
                    </div>

                    <?php endif;
                }
                break;
            case 'new':
                $result = WPXSmartShopCouponsViewController::editView();
                return true;
            case 'edit':
                $result = WPXSmartShopCouponsViewController::editView( $_REQUEST['id_coupon'] );
                return true;
        }
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
        if ($this->process_bulk_action()) {
            return true;
        }

        // Costruisco la select
        $where   = 'WHERE 1 ';
        $orderby = isset($_GET['orderby']) ? $_GET['orderby'] : WPXSMARTSHOP_COUPONS_LISTTABLE_DEFAULT_ORDER;
        $order   = isset($_GET['order']) ? $_GET['order'] : WPXSMARTSHOP_COUPONS_LISTTABLE_DEFAULT_ORDER_BY;
        $table   = WPXSmartShopCoupons::tableName();
        $status  = WPXSMARTSHOP_COUPON_STATUS_AVAILABLE;

        /* Status */
        if ( isset( $_GET['status'] ) && $_GET['status'] != 'all' ) {
            $sql_where = '';
            if ( $_REQUEST['status'] == WPXSMARTSHOP_COUPON_STATUS_AVAILABLE ) {
                $sql_where = <<< SQL
        AND (coupon.date_from IS NULL OR DATE(coupon.date_from) <= DATE(NOW()))
        AND (coupon.date_to IS NULL OR DATE(coupon.date_to) >= DATE(NOW()))
SQL;
            }
            $where .= sprintf( 'AND `status` = "%s" %s', $_GET['status'], $sql_where );
        }

        /* User maker */
        if ( isset( $_GET['wpxss_coupons_filter_user_maker_id'] ) && !empty( $_GET['wpxss_coupons_filter_user_maker_id'] ) ) {
            $where .= sprintf( ' AND id_user_maker = %s', absint( esc_attr( $_GET['wpxss_coupons_filter_user_maker_id'] ) ) );
        }
        
        /* User owner */
        if ( isset( $_GET['wpxss_coupons_filter_user_owner_id'] ) && !empty( $_GET['wpxss_coupons_filter_user_owner_id'] ) ) {
            $where .= sprintf( ' AND id_owner = %s', absint( esc_attr( $_GET['wpxss_coupons_filter_user_owner_id'] ) ) );
        }

        /* Search for coupon uniq code */
        if ( isset( $_GET['s'] ) && !empty( $_GET['s'] ) ) {
            $where .= ' AND uniqcode LIKE "%' . esc_attr( $_GET['s'] ) . '%"';
        }

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
SELECT coupon.*,
       users.display_name AS user_display_name,
       users_owner.display_name AS users_owner_display_name,
       users_maker.display_name AS users_maker_display_name,
       products.post_title AS product_name,

       products_maker.post_title AS product_maker_name,

       terms_product_type.name AS product_type_name,

       IF( (id_user = -1 OR id_user > 0 OR status = 'confirmed'), 'yes', 'no') AS used,
       IF(
         (id_user = 0 OR status = '{$status}') AND
         (date_from IS NULL OR TIMESTAMP(date_from) <= TIMESTAMP(NOW())) AND
         (date_to IS NULL OR TIMESTAMP(date_to) >= TIMESTAMP(NOW())),
       'yes',
       'no'
       ) AS available

FROM `{$table}` AS coupon
LEFT JOIN `{$wpdb->users}` AS users ON coupon.id_user = users.ID
LEFT JOIN `{$wpdb->users}` AS users_owner ON coupon.id_owner = users_owner.ID
LEFT JOIN `{$wpdb->users}` AS users_maker ON coupon.id_user_maker = users_maker.ID
LEFT JOIN `{$wpdb->posts}` AS products ON coupon.id_product = products.ID
LEFT JOIN `{$wpdb->posts}` AS products_maker ON coupon.id_product_maker = products_maker.ID
LEFT JOIN `{$wpdb->terms}` AS terms_product_type ON coupon.id_product_type = terms_product_type.term_id
{$where}
ORDER BY `{$orderby}` {$order}
SQL;

        $data = $wpdb->get_results($sql, ARRAY_A);

        /**
         * This checks for sorting input and sorts the data in our array accordingly.
         *
         * In a real-world situation involving a database, you would probably want
         * to handle sorting by passing the 'orderby' and 'order' values directly
         * to a custom query. The returned data will be pre-sorted, and this array
         * sorting technique would be unnecessary.
         */
        function usort_reorder($a, $b) {
            $orderby = (!empty($_REQUEST['orderby'])) ? $_REQUEST['orderby'] : 'date_insert'; //If no sort, default to title
            $order   = (!empty($_REQUEST['order'])) ? $_REQUEST['order'] : 'desc'; //If no order, default to asc
            $result  = strcmp($a[$orderby], $b[$orderby]); //Determine sort order
            return ($order === 'asc') ? $result : -$result; //Send final sort direction to usort
        }

        //usort($data, 'usort_reorder');

        /***********************************************************************
         * ---------------------------------------------------------------------
         * vvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvv
         *
         * In a real-world situation, this is where you would place your query.
         *
         * ^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
         * ---------------------------------------------------------------------
         **********************************************************************/

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

    // ---

    function filters_tablenav() {

        /* Get filters */
        $filter_users_maker_text  = isset( $_GET['wpxss_coupons_filter_user_maker'] ) ? $_GET['wpxss_coupons_filter_user_maker'] : '';
        $filter_users_maker_id    = isset( $_GET['wpxss_coupons_filter_user_maker_id'] ) ? $_GET['wpxss_coupons_filter_user_maker_id'] : '';
        $filter_users_maker_label = __( 'Filter for User Maker', WPXSMARTSHOP_TEXTDOMAIN );

        $filter_users_owner_text  = isset( $_GET['wpxss_coupons_filter_user_owner'] ) ? $_GET['wpxss_coupons_filter_user_owner'] : '';
        $filter_users_owner_id    = isset( $_GET['wpxss_coupons_filter_user_owner_id'] ) ? $_GET['wpxss_coupons_filter_user_owner_id'] : '';
        $filter_users_owner_label = __( 'Filter for User owner', WPXSMARTSHOP_TEXTDOMAIN );

        $button_label = __( 'Apply', WPXSMARTSHOP_TEXTDOMAIN );

        $html = <<< HTML
        <div class="clearfix wpdk-list-table-filter">
            <div class="wpdk-list-table-filter-row">
                <label for="wpxss_coupons_filter_user_maker" class="wpdk-form-label wpdk-form-input user_email">{$filter_users_maker_label}:</label>
                <input type="text" data-autocomplete_target="wpxss_coupons_filter_user_maker_id" data-autocomplete_action="wpdk_action_user_by" title="{$filter_users_maker_label}" size="64" value="{$filter_users_maker_text}" id="wpxss_coupons_filter_user_maker" name="wpxss_coupons_filter_user_maker" class="wpdk-form-input" />
                <input type="hidden" value="{$filter_users_maker_id}" name="wpxss_coupons_filter_user_maker_id" id="wpxss_coupons_filter_user_maker_id">
            </div>            
            <div class="wpdk-list-table-filter-row">
                <label for="wpxss_coupons_filter_user_owner" class="wpdk-form-label wpdk-form-input user_email">{$filter_users_owner_label}:</label>
                <input type="text" data-autocomplete_target="wpxss_coupons_filter_user_owner_id" data-autocomplete_action="wpdk_action_user_by" title="{$filter_users_owner_label}" size="64" value="{$filter_users_owner_text}" id="wpxss_coupons_filter_user_owner" name="wpxss_coupons_filter_user_owner" class="wpdk-form-input" />
                <input type="hidden" value="{$filter_users_owner_id}" name="wpxss_coupons_filter_user_owner_id" id="wpxss_coupons_filter_user_owner_id">
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