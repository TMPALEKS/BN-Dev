<?php
/**
 * @class              BNMExtendsUserListTable
 *
 * @description
 *
 * @package            BNMExtends
 * @subpackage         User
 * @author             Enrico Corinti
 * @copyright          Copyright (c) 2013 Webeing.net
 * @link               http://webeing.net
 * @created            13/06/12
 * @version            1.0.0
 *
 */

if (!class_exists('WP_List_Table')) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}


class BNMExtendsUserListTable extends WP_List_Table {

    private $_table_name;

    function __construct(  ) {
        /* Set parent defaults */
        parent::__construct( array( 'singular'   => 'id_user', 'plural'     => 'users', 'ajax'       => false ) );
        $this->_table_name  = BNMExtendsUser::tableName();

    }


    function no_items() {
        _e( 'No Products found.', WPXSMARTSHOP_TEXTDOMAIN );
        echo '<br/>';
        _e( 'Please, check your search filters parameters.', WPXSMARTSHOP_TEXTDOMAIN );
    }

    function get_columns(){
        $columns = array(
            'id'            =>  __('UniqueId','bnmextends'),
            'user'          =>  __('Utente','bnmextends'),
            //'name'        =>  'Nome',
            'email'         =>  __('Email','bnmextends'),
            'subscription'  =>  __('Data di iscrizione','bnmextends'),
            'last_login'    =>  __("Ultimo Login", 'bnmextends'),
            'nlogin'       =>  __("#Login", 'bnmextends')
        );
        return $columns;
    }

    function column_default( $item, $column_name ) {
        switch( $column_name ) {
            case 'id':
                return $item[ 'uniqid' ];
            case 'user':
                return $item[ 'last_name' ] . ' ' . $item[ 'first_name' ];
            case 'email':
                return $item[ 'email' ];
            case 'subscription':
                return $item[ 'status_datetime' ];


            default:
                return print_r( $item, true ) ; //Show the whole array for troubleshooting purposes
        }
    }

    function column_last_login( $item ){
        return WPDKDateTime::timeNewLine( date( __('m/d/Y H:i:s', WPDK_TEXTDOMAIN), get_user_meta( $item['id_user'], 'wpdk_user_internal-time_last_login', true ) ) );
    }

    function column_nlogin( $item ){
        return get_user_meta( $item['id_user'], 'wpdk_user_internal-count_success_login', true);
    }

    function prepare_items(){
        global $wpdb;

        $screen   = get_current_screen();
        //$option   = $screen->get_option( 'per_page', 'option' );
        $per_page = 20;

        $columns = $this->get_columns();
        $hidden = array();
        $sortable = array();

        $this->_column_headers = array($columns, $hidden, $sortable);

        $tablename = BNMExtendsUser::tableName();
        $where = "WHERE 1=1";

        if ( empty ( $per_page ) || $per_page < 1 ) {
            $per_page = $screen->get_option( 'per_page', 'default' );
        }


        if( isset( $_GET['wpss-user-datestart-filter'] ) && !empty( $_GET['wpss-user-datestart-filter'] ) ) {
            $date_start_value = WPDKDateTime::dateTime2MySql( esc_attr( $_GET['wpss-user-datestart-filter'] ), __( 'm/d/Y H:i', WPXSMARTSHOP_TEXTDOMAIN ) );
        } else {
            //$date_start_value = date( MYSQL_DATE_TIME, time() - 60*60*24*365*30 );
            $date_start_value = ""; //date( MYSQL_DATE_TIME, time() - 60*60*24*7 );
        }

        if( isset( $_GET['wpss-user-dateend-filter'] ) && !empty( $_GET['wpss-user-dateend-filter'] ) ) {
            $date_end_value = WPDKDateTime::dateTime2MySql( esc_attr( $_GET['wpss-user-dateend-filter'] ), __( 'm/d/Y H:i', WPXSMARTSHOP_TEXTDOMAIN ) );
        } else {
            $date_end_value = ""; // date( MYSQL_DATE_TIME );
        }

        if ($date_end_value && $date_start_value)
            $where .= sprintf( ' AND TIMESTAMP( users.status_datetime ) BETWEEN "%s" AND "%s" ', $date_start_value, $date_end_value );


        $query = <<< SQL
SELECT * FROM {$tablename} AS users
{$where}
ORDER BY users.status_datetime DESC
SQL;


        #var_dump($query);

        $data = $wpdb->get_results($query, ARRAY_A);

        #var_dump($data);

        if ( $buffer = BNMExtendsUser::exportCSV( $data )  ) {
            set_transient( 'wpxss_users_csv', $buffer );
        }

        $this->count = count( $data );
        $current_page = $this->get_pagenum();
        $total_items = count( $data );

        $data = array_slice( $data, ( ( $current_page - 1 ) * $per_page ), $per_page );
        $this->items = $data;
        $this->set_pagination_args( array( 'total_items' => $total_items, 'per_page' => $per_page, 'total_pages' => ceil( $total_items / $per_page ) ) );
    }

    /* Extra filters */
    function extra_tablenav( $which ) {
        if( $which == 'top' ) {
            echo $this->filters_tablenav();
        }
    }

    function filters_tablenav() {
        /* Date start. */
        /* Se la data di start non è impostata, prendo oggi e torno indietro di una settimana. */
        /* @todo Aggiungere filtri o meglio impostazioni da backend */

        $date_start_value = date( __( 'm/d/Y H:i', WPXSMARTSHOP_TEXTDOMAIN ), time() - 60 * 60 * 24 * 7 );
        if ( isset( $_GET['wpss-user-datestart-filter'] ) ) {
            $date_start_value = $_GET['wpss-user-datestart-filter'];
        }

        $item = array(
            'type'   => WPDK_FORM_FIELD_TYPE_DATETIME,
            'name'   => 'wpss-user-datestart-filter',
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
        if ( isset( $_GET['wpss-user-dateend-filter'] ) ) {
            $date_end_value = $_GET['wpss-user-dateend-filter'];
        }
        $item = array(
            'type'   => WPDK_FORM_FIELD_TYPE_DATETIME,
            'name'   => 'wpss-user-dateend-filter',
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

        $selected_variant = isset( $_GET['wpss-user-variant-filter'] ) ? $_GET['wpss-user-variant-filter'] : '';
        //$filter_variant   = WPXSmartShopStats::selectFilterVariant( 'wpss-stats-variant-filter', $selected_variant );

        $selected_model = isset( $_GET['wpss-stats-model-filter'] ) ? $_GET['wpss-stats-model-filter'] : '';
        //$filter_model   = WPXSmartShopStats::selectFilterModel( 'wpss-stats-model-filter', $selected_model );

        $selected_product = isset( $_GET['wpss-stats-product-filter'] ) ? $_GET['wpss-stats-product-filter'] : '';
       // $filter_product   = WPXSmartShopStats::selectFilterProduct( 'wpss-stats-product-filter', $selected_product );

        $selected_user_role = isset( $_GET['wpss-stats-user-role'] ) ? $_GET['wpss-stats-user-role'] : '';
        //$filter_user_role   = WPXSmartShopStats::selectFilterUserRole( 'wpss-stats-user-role', $selected_user_role );

        $export_csv_url   = add_query_arg( array( 'export_users_csv' => '' ) );
        $export_csv_label = __( 'Export CSV', WPXSMARTSHOP_TEXTDOMAIN );


        $user_order_text  = isset( $_GET['wpxss_stats_filter_user'] ) ? $_GET['wpxss_stats_filter_user'] : '';
        $user_order_id    = isset( $_GET['wpxss_stats_filter_id_user_for'] ) ? $_GET['wpxss_stats_filter_id_user_for'] : '';
        $user_order_label = __( 'Ordered for', WPXSMARTSHOP_TEXTDOMAIN );

        $html = <<< HTML




        <div class="clearfix wpdk-list-table-filter">
            <div class="wpdk-list-table-filter-row">
                {$date_start}
                {$date_end}
            </div>
            <div class="wpdk-list-table-filter-row">
                <input type="hidden" value="{$user_order_id}" name="wpxss_stats_filter_id_user_for" id="wpxss_stats_filter_id_user_for">
            </div>

            <div class="wpdk-list-table-filter-row">
                <input type="submit"
                       value="{$button_label}"
                       class="button-secondary action alignright wpdk-form-button"
                       id="doaction"
                       name=""/>
            </div>
            <div class="alignright actions">
                <a class="button" href="{$export_csv_url}">{$export_csv_label}</a>
            </div>

        </div>
HTML;
        return $html;
    }

}