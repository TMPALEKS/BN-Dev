<?php
/**
 * Visualizzazione tabellare dei record
 *
 * @package            wpx Placeholders
 * @subpackage         WPPlaceholdersEnvironmentsListTable
 * @author             =undo= <g.fazioli@undolog.com>, <g.fazioli@wpxtre.me>
 * @copyright          Copyright (c) 2012 wpXtreme, Inc.
 * @link               http://wpxtre.me
 * @created            03/04/12
 * @version            1.0.0
 *
 */

if (!class_exists('WP_List_Table')) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

define('kWPPlaceholdersEnvironmentsListTableDefaultOrder', 'asc');
define('kWPPlaceholdersEnvironmentsListTableDefaultOrderBy', 'description');

class WPPlaceholdersEnvironmentsListTable extends WP_List_Table {

    /**
     * @package            wpx Placeholders
     * @subpackage         WPPlaceholdersEnvironmentsListTable
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
                                 'singular' => 'id_environment', //singular name of the listed records
                                 'plural'   => 'environments', //plural name of the listed records
                                 'ajax'     => false //does this table support ajax?
                            ));
    }

    /* Serie di tag A affiancati per lo stato */
    function get_views() {
        $statuses      = WPPlaceholdersEnvironments::statusesWithCount();
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

    /* Default content Columns */
    function column_default( $item, $column_name ) {
        switch ( $column_name ) {
            case 'icon':
                return WPPlaceholdersEnvironments::imagePlaceholder();
            case 'description':
                return sprintf( '%s', $item[$column_name] );
            case 'status':
                return sprintf( '<span title="%s" class="wpph-icon-%s"></span>', __( $item[$column_name], WPXPLACEHOLDERS_TEXTDOMAIN ), $item[$column_name] );

            default:
                return print_r( $item, true ); //Show the whole array for troubleshooting purposes
        }
    }

    /* Inline actions */
    function column_id( $item ) {

        $args = array(
            'id_environment' => $item['id'],
            'actions'      => array(
                'edit'     => __( 'Edit', WPXPLACEHOLDERS_TEXTDOMAIN ),
                'untrash'  => __( 'Restore', WPXPLACEHOLDERS_TEXTDOMAIN ),
                'delete'   => __( 'Delete', WPXPLACEHOLDERS_TEXTDOMAIN ),
                'trash'    => __( 'Trash', WPXPLACEHOLDERS_TEXTDOMAIN ),
            )
        );

        $actions = WPDKListTable::actions( $args, $_REQUEST['status'] );

        return sprintf( '<strong>%s</strong> %s', $item['description'], $this->row_actions( $actions ) );
    }

    /* Checkbox for group actions */
    function column_cb($item) {
        return sprintf('<input type="checkbox" name="%1$s[]" value="%2$s" />', $this->_args['singular'], $item['id'] );
    }

    /* Columns */
    function get_columns() {
        $columns = array(
            'cb'   => '<input type="checkbox" />',
            'icon' => __( 'Icon', WPXPLACEHOLDERS_TEXTDOMAIN ),
            'id'   => __( 'Description', WPXPLACEHOLDERS_TEXTDOMAIN ),
        );
        return $columns;
    }

    /* Sortable columns */
    function get_sortable_columns() {
        $sortable_columns = array(
            'id'          => array(
                'description',
                true
            ),
        );
        return $sortable_columns;
    }

    /* Bulk Actions */
    function get_bulk_actions() {
        $actions = array(
            'delete'   => __( 'Delete', WPXPLACEHOLDERS_TEXTDOMAIN ),
            'trash'    => __( 'Move to Trash', WPXPLACEHOLDERS_TEXTDOMAIN ),
            'untrash'  => __( 'Restore', WPXPLACEHOLDERS_TEXTDOMAIN ),
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
                $result = WPPlaceholdersEnvironments::trash( $_REQUEST['id_environment'], 'id', 'status' );
                break;

            case 'untrash':
                $result = WPPlaceholdersEnvironments::untrash( $_REQUEST['id_environment'], 'id', 'status' );
                break;

            case 'delete':
                if ( isset( $_REQUEST['id_environment'] ) ) {
                    $result = WPPlaceholdersEnvironments::delete( $_REQUEST['id_environment'] );
                    if ( !is_wp_error( $result ) ) : ?>

                    <div class="updated">
                        <p><?php echo $result ?> <?php _e( 'Environment(s) deleted successfully',
                            WPXPLACEHOLDERS_TEXTDOMAIN ) ?></p>
                    </div>

                    <?php else : ?>

                    <div class="error">
                        <p><?php echo sprintf( '%s: %s', _e( 'Error while deleting environment',
                            WPXPLACEHOLDERS_TEXTDOMAIN ), $result->get_error_message() ) ?></p>
                    </div>
                    <?php endif;
                }
                return false;
            case 'update':
                $result = WPPlaceholdersEnvironments::update(); ?>

                <?php if ( !is_wp_error( $result ) ) : ?>

                <div class="updated">
                    <p><?php _e( 'Place update successfully', WPXPLACEHOLDERS_TEXTDOMAIN ) ?></p>
                </div>

                <?php else : ?>

                <div class="updated">
                    <p><?php echo sprintf( '%s: %s', _e( 'Error while updating environment',
                            WPXPLACEHOLDERS_TEXTDOMAIN ),
                        $result->get_error_message() ) ?></p>
                </div>

                <?php endif;
                break;
            case 'insert':
                $result = WPPlaceholdersEnvironments::create(); ?>

                <?php if ( !is_wp_error( $result ) ) : ?>

                <div class="updated">
                    <p><?php _e( 'Environment added successfully', WPXPLACEHOLDERS_TEXTDOMAIN ) ?></p>
                </div>

                <?php else : ?>

                <div class="updated">
                    <p><?php echo sprintf( '%s: %s', _e( 'Error while adding environment', WPXPLACEHOLDERS_TEXTDOMAIN ),
                        $result->get_error_message() ) ?></p>
                </div>

                <?php endif;
                break;

            case 'new':
                WPPlaceholdersEnvironmentsViewController::editView();
                return true;
            case 'edit':
                WPPlaceholdersEnvironmentsViewController::editView( $_REQUEST['id_environment'] );
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
        $where     = 'WHERE 1';
        $orderby   = isset( $_GET['orderby'] ) ? $_GET['orderby'] : kWPPlaceholdersEnvironmentsListTableDefaultOrderBy;
        $order     = isset( $_GET['order'] ) ? $_GET['order'] : kWPPlaceholdersEnvironmentsListTableDefaultOrder;
        $table     = WPPlaceholdersEnvironments::tableName();

        /* Status */
        if ( isset( $_GET['status'] ) ) {
            if ( $_GET['status'] != 'all' ) {
                $where .= sprintf( " AND environments.`status` = '%s'", esc_attr( $_GET['status'] ) );
            } else {
                $where .= " AND environments.`status` <> 'trash'";
            }
        } else {
            $where .= " AND environments.`status` <> 'trash'";
        }

        $sql = <<< SQL
		SELECT environments.*
		FROM `{$table}` AS environments
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


}
