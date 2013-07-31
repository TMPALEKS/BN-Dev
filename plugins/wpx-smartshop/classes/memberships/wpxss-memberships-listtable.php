<?php
/**
 * @class              WPXSmartShopMembershipsListTable
 *
 * @description        Classe dedicata alla visualizzazione delle membership
 *
 * @package            wpx SmartShop
 * @subpackage         memberships
 * @author             =undo= <g.fazioli@undolog.com>, <g.fazioli@wpxtre.me>
 * @copyright          Copyright (c) 2012 wpXtreme, Inc.
 * @link               http://wpxtre.me
 * @created            22/02/12
 * @version            1.0.0
 *
 * @filename           wpxss-memberships-listtable
 *
 */

if (!class_exists('WP_List_Table')) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

define('kWPSmartShopMembershipsListTableDefaultOrder', 'desc');
define('kWPSmartShopMembershipsListTableDefaultOrderBy', 'date_insert');

class WPXSmartShopMembershipsListTable extends WP_List_Table {

    private $_table_name;

    private $_roles;

    /**
     * @package    wpx SmartShop
     * @subpackage WPXSmartShopMembershipsListTable
     * @since      1.0.0
     *
     * @todo farla diventare impostazione da backend
     *
     * @var int
     */
    public static $itemPerPage = 20;

    function __construct() {
        /* Set parent defaults */
        parent::__construct(array( 'singular' => 'id_membership', 'plural' => 'memberships', 'ajax' => false ) );
        $this->_table_name = WPXSmartShopMemberships::tableName();

        /* Store roles */
        $this->_roles = WPDKUser::allRoles();
    }

    function no_items() {
        _e( 'No Membership or subscription found.', WPXSMARTSHOP_TEXTDOMAIN );
        echo '<br/>';
        _e( 'Please, check your search filters parameters.', WPXSMARTSHOP_TEXTDOMAIN );
    }

    function get_views() {
        $statuses      = WPXSmartShopMemberships::statuses();
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

    /* Default content Columns */
    function column_default( $item, $column_name ) {
        switch ( $column_name ) {
            case 'role':
                return sprintf( '%s<br/>(%s)', $this->_roles[ $item[$column_name] ], $this->_roles[ $item['role_previous'] ] );
                break;
            case 'capabilities':
                if( !empty( $item[$column_name] ) ) {
                    $caps = explode(',', $item[$column_name] );
                    return join( '<br/>', $caps );
                }
                break;
            case 'id_user_maker':
                return sprintf( '%s %s', WPDKUser::gravatar( $item[$column_name], 16 ), $item['user_maker_display_name'] );
            case 'id_product_maker':
                if ( empty( $item[$column_name] ) ) {
                    return sprintf( '<span style="color:silver">%s</span>', __( 'None', WPXSMARTSHOP_TEXTDOMAIN ) );
                } else {
                    return sprintf( '<a data-id_product="%s" href="%s">%s</a>', $item[$column_name], get_edit_post_link( $item[$column_name] ), WPXSmartShopProduct::thumbnail( $item[$column_name] ) );
                }
            case 'date_insert':
            case 'date_start':
            case 'date_expired':
                return WPDKDateTime::timeNewLine( mysql2date( __( 'm/d/Y H:i', WPXSMARTSHOP_TEXTDOMAIN ), $item[$column_name] ) );
            case 'status':
                $statuses = WPXSmartShopMemberships::arrayStatuses();
                return sprintf( '<span title="%s" class="wpdk-tooltip wpss-icon-%s"></span>', __( $statuses[$item[$column_name]]['label'], WPXSMARTSHOP_TEXTDOMAIN ), $item[$column_name] );
            default:
                return print_r( $item, true );
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

        return sprintf( '%s %s %s',  WPDKUser::gravatar( $item['id_user'], 16 ), $item['user_display_name'], $this->row_actions( $actions ) );
    }

    /* Checkbox for group actions */
    function column_cb($item) {
        return sprintf('<input type="checkbox" name="%1$s[]" value="%2$s" />', $this->_args['singular'], $item['id'] );
    }

    /* Columns */
    function get_columns() {
        $columns = array(
            'cb'               => '<input type="checkbox" />',
            'date_insert'      => __( 'Date', WPXSMARTSHOP_TEXTDOMAIN ),
            'id'               => __( 'User', WPXSMARTSHOP_TEXTDOMAIN ),
            'role'             => __( 'Role', WPXSMARTSHOP_TEXTDOMAIN ),
            'capabilities'     => __( 'Capabilities', WPXSMARTSHOP_TEXTDOMAIN ),
            'date_start'       => __( 'Start', WPXSMARTSHOP_TEXTDOMAIN ),
            'date_expired'     => __( 'Expiry', WPXSMARTSHOP_TEXTDOMAIN ),
            'id_user_maker'    => __( 'Create by', WPXSMARTSHOP_TEXTDOMAIN ),
            'id_product_maker' => __( 'Product', WPXSMARTSHOP_TEXTDOMAIN ),
            'status'           => __( 'Status', WPXSMARTSHOP_TEXTDOMAIN ),
        );
        return $columns;
    }

    /* Sortable columns */
    function get_sortable_columns() {
        $sortable_columns = array(
            'id'             => array( 'user_display_name', false ),
            'date_insert'    => array( 'date_insert', true ),
            'date_start'     => array( 'date_start', false ),
            'date_expired'   => array( 'date_expired', false ),
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
            case 'delete':
                if ( isset( $_REQUEST['id_membership'] ) ) {
                    $result = WPXSmartShopMemberships::delete( $this->_table_name, $_REQUEST['id_membership'] );
                    if ( !is_wp_error( $result ) ) : ?>

                    <div class="updated">
                        <p><?php echo $result ?> <?php _e( 'Membership(s) deleted successfully', WPXSMARTSHOP_TEXTDOMAIN ) ?></p>
                    </div>

                    <?php else : ?>

                    <div class="error">
                        <p><?php echo sprintf( '%s: %s', _e( 'Error while deleting Membership', WPXSMARTSHOP_TEXTDOMAIN ), $result->get_error_message() ) ?></p>
                    </div>
                    <?php endif;
                }
                break;
            case 'update':
                $result = WPXSmartShopMemberships::update(); ?>

                <?php if ( !is_wp_error( $result ) ) : ?>

                <div class="updated">
                    <p><?php _e( 'Membership update successfully', WPXSMARTSHOP_TEXTDOMAIN ) ?></p>
                </div>

                <?php else : ?>

                <div class="updated">
                    <p><?php echo sprintf( '%s: %s', _e( 'Error while updating Membership', WPXSMARTSHOP_TEXTDOMAIN ), $result->get_error_message() ) ?></p>
                </div>

                <?php endif;
                break;
            case 'insert':
                $result = WPXSmartShopMemberships::create( $_POST ); ?>

                <?php if ( !is_wp_error( $result ) ) : ?>

                <div class="updated">
                    <p><?php _e( 'Membership added successfully', WPXSMARTSHOP_TEXTDOMAIN ) ?></p>
                </div>

                <?php else : ?>

                <div class="updated">
                    <p><?php echo sprintf( '%s: %s', _e( 'Error while adding Membership', WPXSMARTSHOP_TEXTDOMAIN ), $result->get_error_message() ) ?></p>
                </div>

                <?php endif;
                break;

            case 'trash':
                $result = WPXSmartShopMemberships::trash( $this->_table_name, $_REQUEST['id_membership'], 'id', 'status' );
                break;

            case 'untrash':
                $result = WPXSmartShopMemberships::untrash( $this->_table_name, $_REQUEST['id_membership'], 'id', 'status' );
                break;

            case 'new':
                WPXSmartShopMembershipsViewController::editView();
                return true;
            case 'edit':
                WPXSmartShopMembershipsViewController::editView( $_REQUEST['id_membership'] );
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

        // Costruisco la select
        $where   = "WHERE 1";
        $orderby = isset($_GET['orderby']) ? $_GET['orderby'] : kWPSmartShopMembershipsListTableDefaultOrderBy;
        $order   = isset($_GET['order']) ? $_GET['order'] : kWPSmartShopMembershipsListTableDefaultOrder;

        $membership = WPXSmartShopMemberships::tableName();
        $products   = $wpdb->posts;
        $users      = $wpdb->users;

        /* Search for coupon uniq code */
        if ( isset( $_GET['s'] ) && !empty( $_GET['s'] ) ) {
            //$where .= ' AND users.user_email LIKE "%' . esc_attr( $_GET['s'] ) . '%"';
            $where .= sprintf( ' AND (users.user_email LIKE "%%%s%%" OR users.display_name LIKE "%%%s%%")', esc_attr( $_GET['s'] ), esc_attr( $_GET['s'] ) );
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

        /* User */
        if ( isset( $_GET['wpss_memberships_user_filter'] ) && !empty( $_GET['wpss_memberships_user_filter'] ) ) {
            $where .= sprintf( ' AND id_user = %s', absint( esc_attr( $_GET['wpss_memberships_user_filter'] ) ) );
        }

        $sql = <<< SQL
		SELECT membership.*,
		       users.display_name AS user_display_name,
		       users_maker.display_name AS user_maker_display_name

		FROM `{$membership}` AS membership
		LEFT JOIN {$users} AS users ON users.ID = membership.id_user
		LEFT JOIN {$users} AS users_maker ON users_maker.ID = membership.id_user_maker
		LEFT JOIN {$products} AS products ON products.ID = membership.id_product_maker
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

    // ---

    function filters_tablenav() {

        /* Get filters */
        $selected_user  = isset( $_GET['wpss_memberships_user_filter'] ) ? $_GET['wpss_memberships_user_filter'] : '';

        $button_label = __( 'Apply', WPXSMARTSHOP_TEXTDOMAIN );

        $filter_users = WPXSmartShopMemberships::selectFilterUsers( 'wpss_memberships_user_filter', $selected_user );

        $html = <<< HTML
        <div class="clearfix wpdk-list-table-filter">
            <div class="wpdk-list-table-filter-row">
                {$filter_users}
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
