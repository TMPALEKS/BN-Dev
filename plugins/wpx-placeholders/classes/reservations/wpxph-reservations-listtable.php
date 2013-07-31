<?php
/**
 * @class              WPPlaceholdersReservationsListTable
 *
 * Estensione della classe WP_List_Table per la visualizzazione tabellare dei record
 *
 * @package            wpx Placeholders
 * @author             =undo= <g.fazioli@wpxtre.me>
 * @copyright          Copyright (c) 2012 wpXtreme, Inc.
 * @created            03/04/12
 * @version            1.0.0
 *
 */

if (!class_exists('WP_List_Table')) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

define('kWPPlaceholdersReservationsListTableDefaultOrder', 'asc');
define('kWPPlaceholdersReservationsListTableDefaultOrderBy', 'date_start');

class WPPlaceholdersReservationsListTable extends WP_List_Table {

    /* @todo Verificare se necessario */
    private $_table_name;

    /**
     * @todo farla diventare impostazione da backend
     *
     * @var int
     */
    public static $itemPerPage = 20;

    /// Construct
    function __construct() {

        /* Set parent defaults */
        parent::__construct(array( 'singular' => 'id_reservation', 'plural'   => 'reservations', 'ajax'     => false ) );
        $this->_table_name = WPPlaceholdersReservations::tableName();
    }

    /// No items
    function no_items() {
        _e( 'No Reservations found.', WPXSMARTSHOP_TEXTDOMAIN );
        echo '<br/>';
        _e( 'Please, check your search filters parameters.', WPXSMARTSHOP_TEXTDOMAIN );
    }

    /* Serie di tag A affiancati per lo stato */
    function get_views() {
        $statuses      = WPPlaceholdersReservations::statuses();
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

    /* Default content Columns */
    function column_default( $item, $column_name ) {

        /* Controllo sugli scaduti */
        $expired = '';
        if ( isset( $item['date_expiry'] ) ) {
            $date_expiry_stamp = WPDKDateTime::makeTimeFrom( MYSQL_DATE_TIME, $item['date_expiry'] );
            if ( time() > $date_expiry_stamp ) {
                $expired = 'wpxph-reservation-exipred';
            }
        }

        switch ( $column_name ) {
            case 'icon':
                return WPPlaceholdersPlaces::imagePlaceholder();
            case 'place':
                return sprintf( '<span class="%s">%s</span>', $expired, $item[$column_name] );
            case 'status':
                return sprintf( '<span title="%s" class="wpph-icon-%s"></span>', __( $item[$column_name], WPXPLACEHOLDERS_TEXTDOMAIN ), $item[$column_name] );
            case 'date_start':
            case 'date_expiry':
                $date = WPDKDateTime::formatFromFormat( $item[$column_name] , MYSQL_DATE_TIME, __( 'm/d/Y H:i', WPXPLACEHOLDERS_TEXTDOMAIN ) );
                return sprintf( '<span class="%s">%s</span>', $expired, WPDKDateTime::timeNewLine( $date ) );
            default:

                /**
                 * Utilizzato in congiunzione con il filtro 'wpph_reservation_list_table_columns' per visualizzare e
                 * gestire colonne aggiuntive
                 *
                 * @param string $output      Output per la colonna/riga
                 * @param string $column_name Nome della colonna
                 * @param array  $item        Record in forma di array con i dati della colonna/riga
                 */
                $result = apply_filters( 'wpph_reservation_list_table_column_output', null, $column_name, $item );
                if ( is_null( $result ) ) {
                    return print_r( $item, true ); //Show the whole array for troubleshooting purposes
                } else {
                    return $result;
                }
        }
    }

    /* Inline actions */
    function column_id( $item ) {

        /* Controllo sugli scaduti */
        $expired = '';
        if ( isset( $item['date_expiry'] ) ) {
            $date_expiry_stamp = WPDKDateTime::makeTimeFrom( MYSQL_DATE_TIME, $item['date_expiry'] );
            if ( time() > $date_expiry_stamp ) {
                $expired = 'wpxph-reservation-exipred';
            }
        }

        $args = array(
            'id_reservation' => $item['id'],
            'actions'      => array(
                'edit'     => __( 'Edit', WPXPLACEHOLDERS_TEXTDOMAIN ),
                'untrash'  => __( 'Restore', WPXPLACEHOLDERS_TEXTDOMAIN ),
                'delete'   => __( 'Delete', WPXPLACEHOLDERS_TEXTDOMAIN ),
                'trash'    => __( 'Trash', WPXPLACEHOLDERS_TEXTDOMAIN ),
            )
        );

        $status = '';
        if ( isset( $_REQUEST['status'] ) ) {
            $status = $_REQUEST['status'];
        }
        $actions = WPDKListTable::actions( $args, $status );

        return sprintf( '<strong class="%s">%s</strong> %s', $expired, $item['place'], $this->row_actions( $actions ) );
    }

    /* Checkbox for group actions */
    function column_cb($item) {
        return sprintf('<input type="checkbox" name="%1$s[]" value="%2$s" />', $this->_args['singular'], $item['id'] );
    }

    /* Columns */
    function get_columns() {
        $columns = array(
            'cb'            => '<input type="checkbox" />',
            'icon'          => __( 'Icon', WPXPLACEHOLDERS_TEXTDOMAIN ),
            'id'            => __( 'Place', WPXPLACEHOLDERS_TEXTDOMAIN ),
            'date_start'    => __( 'Start date', WPXPLACEHOLDERS_TEXTDOMAIN ),
            'date_expiry'   => __( 'Expiry date', WPXPLACEHOLDERS_TEXTDOMAIN ),
        );

        /**
         * @param array $columns Colonne
         */
        $columns = apply_filters( 'wpph_reservation_list_table_columns', $columns );

        return $columns;
    }

    /* Sortable columns */
    function get_sortable_columns() {
        $sortable_columns = array(
            'id'          => array( 'place', false ),
            'date_start'  => array( 'date_start', true ),
            'date_expiry' => array( 'date_expiry', false ),
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
                $result = WPPlaceholdersReservations::trash( WPPlaceholdersReservations::tableName(), $_REQUEST['id_reservation'], 'id', 'status' );
                /* @todo Check $result */
                break;

            case 'untrash':
                $result = WPPlaceholdersReservations::untrash( WPPlaceholdersReservations::tableName(), $_REQUEST['id_reservation'], 'id', 'status' );
                /* @todo Check $result */
                break;

            case 'delete':
                if ( isset( $_REQUEST['id_reservation'] ) ) {
                    $result = WPPlaceholdersReservations::delete( WPPlaceholdersReservations::tableName(), $_REQUEST['id_reservation'] );

                    if ( is_wp_error( $result ) ) {
                        WPDKUI::error( sprintf( '%s: %s', __( 'Error while deleting reservation', WPXPLACEHOLDERS_TEXTDOMAIN ), $result->get_error_message() ) );
                    } else {
                        WPDKUI::message( __( 'Reservation(s) deleted successfully', WPXPLACEHOLDERS_TEXTDOMAIN ) );
                    }
                }
                break;

            case 'update':
                $result = WPPlaceholdersReservations::update();

                if ( is_wp_error( $result ) ) {
                    WPDKUI::error( sprintf( '%s: %s', __( 'Error while updating reservation', WPXPLACEHOLDERS_TEXTDOMAIN ), $result->get_error_message() ) );
                } else {
                    WPDKUI::message( __( 'Reservation updated successfully', WPXPLACEHOLDERS_TEXTDOMAIN ) );
                }
                break;

            case 'insert':
                $result = WPPlaceholdersReservations::create();
                if ( is_wp_error( $result ) ) {
                    WPDKUI::error( sprintf( '%s: %s', __( 'Error while adding reservation', WPXPLACEHOLDERS_TEXTDOMAIN ), $result->get_error_message() ) );
                } else {
                    WPDKUI::message( __( 'Reservation added successfully', WPXPLACEHOLDERS_TEXTDOMAIN ) );
                }
                break;

            case 'new':
                WPPlaceholdersReservationsViewController::editView();
                return true;
                break;
            case 'edit':
                WPPlaceholdersReservationsViewController::editView( $_REQUEST['id_reservation'] );
                return true;
                break;
        }

        /* Display list table */
        return false;
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

        $this->_column_headers = $this->get_column_info();

        /**
         * Optional. You can handle your bulk actions however you see fit. In this
         * case, we'll handle them within our package just to keep things clean.
         */
        if ($this->process_bulk_action()) {
            return true;
        }

        // Costruisco la select
        $where        = 'WHERE 1';
        $orderby      = isset( $_GET['orderby'] ) ? $_GET['orderby'] : kWPPlaceholdersReservationsListTableDefaultOrderBy;
        $order        = isset( $_GET['order'] ) ? $_GET['order'] : kWPPlaceholdersReservationsListTableDefaultOrder;
        $table        = WPPlaceholdersReservations::tableName();
        $table_places = WPPlaceholdersPlaces::tableName();

        /* Status */
        if ( isset( $_GET['status'] ) ) {
            if ( $_GET['status'] != 'all' ) {
                $where .= sprintf( " AND reservations.`status` = '%s'", esc_attr( $_GET['status'] ) );
            } else {
                $where .= " AND reservations.`status` <> 'trash'";
            }
        } else {
            $where .= " AND reservations.`status` <> 'trash'";
        }

        /* User for */
        if ( isset( $_GET['wpxph_stats_filter_id_user'] ) && !empty( $_GET['wpxph_stats_filter_id_user'] ) ) {
            $where .= sprintf( ' AND reservations.`id_who` = %s', esc_attr( $_GET['wpxph_stats_filter_id_user'] ) );
        }

        /* Date */
        $where_date_start = '1';
        if ( !isset( $_GET['wpph_date_start_filter'] ) ) {
            $default_date_start = apply_filters( 'wpph_reservations_list_table_filter_default_date_start', date( 'd/m/Y 08:00' ) );
        } else {
            $default_date_start = esc_attr( $_GET['wpph_date_start_filter'] );
        }
        if ( !empty( $default_date_start ) ) {
            $where_date_start = sprintf( " TIMESTAMP(reservations.`date_start`) >= TIMESTAMP('%s') ", WPDKDateTime::dateTime2MySql( $default_date_start, __( 'm/d/Y H:i', WPXPLACEHOLDERS_TEXTDOMAIN ) ) );
        }

        $where_date_expiry = '1';
        if ( !isset( $_GET['wpph_date_expiry_filter'] ) ) {
            $default_date_expiry = apply_filters( 'wpph_reservations_list_table_filter_default_date_expiry', date( 'd/m/Y 23:59' ) );
        } else {
            $default_date_expiry = esc_attr( $_GET['wpph_date_expiry_filter'] );
        }
        if ( !empty( $default_date_expiry ) ) {
            $where_date_expiry = sprintf( " TIMESTAMP(reservations.`date_expiry`) <= TIMESTAMP('%s') ", WPDKDateTime::dateTime2MySql( $default_date_expiry, __( 'm/d/Y H:i', WPXPLACEHOLDERS_TEXTDOMAIN ) ) );
        }

        $where .= sprintf( " AND ( %s AND %s )", $where_date_start, $where_date_expiry );

        /* Campi che passo anche al filtro */
        $fields = array(
            'reservations.*',
            'places.name AS place'
        );

        /**
         * @filters
         *
         * @param array $fields Elenco dei campi dell select
         */
        $fields = apply_filters( 'wpph_reservation_list_table_sql_extra_field', $fields );
        $select = join(',', $fields);

        /**
         * @filters
         *
         * @param string $joins Eventuali altre JOIN
         */
        $extra_joins  = apply_filters( 'wpph_reservation_list_table_sql_extra_join', '' );

        $sql = <<< SQL
SELECT {$select}
FROM `{$table}` AS reservations
LEFT JOIN {$table_places} AS places ON places.id = reservations.id_place
{$extra_joins}
{$where}
ORDER BY `{$orderby}` {$order}
SQL;

        $sql = apply_filters( 'wpph_reservation_list_table_sql', $sql );

        if( isset( $_GET['wpxph_debug_show_sql'] ) ) {
            ?><pre><?php var_dump( $sql ) ?></pre><?php
        }

        $data = $wpdb->get_results($sql, ARRAY_A);
        
        $data = apply_filters('wpph_reservation_list_table_custom_sort', $data);
        
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
        $data = array_slice( $data, ( ( $current_page - 1 ) * $per_page ), $per_page );

        /**
         * REQUIRED. Now we can add our *sorted* data to the items property, where
         * it can be used by the rest of the class.
         */ 

        $this->items = $data;

        /**
         * REQUIRED. We also have to register our pagination options & calculations.
         */
        $this->set_pagination_args(array( 'total_items' => $total_items, 'per_page'    => $per_page, 'total_pages' => ceil( $total_items / $per_page) ) );
    }

    /**
     * Restituisce la data inizio del filro, impostata per default alle 08:00 di oggi
     *
     * @access internal
     *
     * @return string Data formattata in base alla localizzazione
     */
    private function dateStartDefault() {
        /* L'orario di default modificabile o da impostazioni o da filtri */

        /* @todo Da impostazioni da fare */
        $default = mktime( 8, 0, 0, date( 'm' ), date( 'd' ), date( 'Y' ) );

        /**
         * @param string $default_date_start Data inizio in time stamp, default alle 08:00 di oggi
         */
        $default = apply_filters( 'wpph_reservations_list_table_filter_default_date_start', $default );

        /* Formatto la data */
        $default = date( __( 'm/d/Y H:i', WPXPLACEHOLDERS_TEXTDOMAIN ), $default );

        return $default;
    }

    /**
     * Restituisce la data inizio del filro, impostata per default alle 08:00 di oggi
     *
     * @access internal
     *
     * @return string Data formattata in base alla localizzazione
     */
    private function dateExpiryDefault() {
        /* L'orario di defaul modificabile o da impostazioni o da filtri */
        /* @todo Da impostazioni da fare */
        $default = mktime( 23, 59, 0, date( 'm' ), date( 'd' ), date( 'Y' ) );

        /**
         * @param string $default_date_expiry Data inizio in time stamp, default alle 23:59 di oggi
         */
        $default = apply_filters( 'wpph_reservations_list_table_filter_default_date_expiry', $default );

        /* Formatto la data */
        $default = date( __( 'm/d/Y H:i', WPXPLACEHOLDERS_TEXTDOMAIN ), $default );

        return $default;
    }

    function filters_tablenav() {

        /* Formatto la data */
        $default_date_start  = self::dateStartDefault();
        $default_date_expiry = self::dateExpiryDefault();

        /* Get filters */
        $date_start  = isset( $_GET['wpph_date_start_filter'] ) ? $_GET['wpph_date_start_filter'] : $default_date_start;
        $date_expiry = isset( $_GET['wpph_date_expiry_filter'] ) ? $_GET['wpph_date_expiry_filter'] : $default_date_expiry;

        $button_label = __( 'Apply', WPXPLACEHOLDERS_TEXTDOMAIN );

        $label_start_date  = __( 'Start date', WPXPLACEHOLDERS_TEXTDOMAIN );
        $label_expiry_date = __( 'Expiry date', WPXPLACEHOLDERS_TEXTDOMAIN );

        if ( isset( $_GET['status'] ) ) {
            printf( '<input type="hidden" name="status" value="%s" />', $_GET['status'] );
        }

        $user_text  = isset( $_GET['wpxph_stats_filter_user'] ) ? $_GET['wpxph_stats_filter_user'] : '';
        $user_id    = isset( $_GET['wpxph_stats_filter_id_user'] ) ? $_GET['wpxph_stats_filter_id_user'] : '';
        $user_label = __( 'Reservation by', WPXPLACEHOLDERS_TEXTDOMAIN );
        

		echo '<div class="clearfix wpdk-list-table-filter">';

		do_action('wpdk_list_before_table_filter');
	
$html = <<< HTML
    <div class="wpdk-list-table-filter-row">
        <label for="date_start"
               class="wpdk-form-label wpdk-form-input wpdk-form-datetime wpdk-form-has-button-clear-left date_start">{$label_start_date}:</label>
        <input type="text"
               title="{$label_start_date}"
               size="16"
               value="{$date_start}"
               id="wpph_date_start_filter"
               name="wpph_date_start_filter"
               class="wpdk-tooltip wpdk-form-input wpdk-form-datetime wpdk-form-has-button-clear-left"><span class="wpdk-form-clear-left"></span>

        <label for="date_expiry"
               class="wpdk-form-label wpdk-form-input wpdk-form-datetime wpdk-form-has-button-clear-left date_expiry">{$label_expiry_date}:</label>
        <input type="text"
               title="{$label_expiry_date}"
               size="16"
               value="{$date_expiry}"
               id="wpph_date_expiry_filter"
               name="wpph_date_expiry_filter"
               class="wpdk-tooltip wpdk-form-input wpdk-form-datetime wpdk-form-has-button-clear-left"><span class="wpdk-form-clear-left"></span>
    </div>

    <div class="wpdk-list-table-filter-row">

        <label for="wpxph_stats_filter_user" class="wpdk-form-label wpdk-form-input user_email">{$user_label}:</label>
        <input type="text" data-autocomplete_target="wpxph_stats_filter_id_user" data-autocomplete_action="wpdk_action_user_by" title="{$user_label}" size="64" value="{$user_text}" id="wpxph_stats_filter_user" name="wpxph_stats_filter_user" class="wpdk-form-input" />
        <input type="hidden" value="{$user_id}" name="wpxph_stats_filter_id_user" id="wpxss_stats_filter_id_user">

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