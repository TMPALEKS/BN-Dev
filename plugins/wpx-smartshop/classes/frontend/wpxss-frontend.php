<?php
/**
 * @class              WPXSmartShopFrontend
 *
 * @description        Classe dedicata al Frontend.
 *
 * @package            wpx SmartShop
 * @subpackage         frontend
 * @author             =undo= <g.fazioli@undolog.com>, <g.fazioli@wpxtre.me>
 * @copyright          Copyright (c)2012 wpXtreme, Inc.
 * @created            16/11/11
 * @version            1.0
 *
 * @filename           wpxss-frontend
 *
 * @todo               Pensare ad implementare degli stili lato frontend per fornire una grafica/layout per alcuni componenti come: carrello, scheda prodotto, etc....
 *
 */

class WPXSmartShopFrontend extends WPDKWordPressTheme {

    // -----------------------------------------------------------------------------------------------------------------
    // Init constructor
    // -----------------------------------------------------------------------------------------------------------------

    function __construct( WPXSmartShop $plugin ) {
        parent::__construct( $plugin );
    }

    // -----------------------------------------------------------------------------------------------------------------
    // WordPress Hook
    // -----------------------------------------------------------------------------------------------------------------

    function wp_enqueue_scripts() {
        wp_enqueue_style( 'wp-smartshop-frontend-css', WPXSMARTSHOP_URL_CSS . 'wp-smartshop-frontend.css', array(), WPXSMARTSHOP_VERSION );

        wp_enqueue_script( 'wp-smartshop-frontend-js', WPXSMARTSHOP_URL_JAVASCRIPT . 'wp-smartshop-frontend.js', array( 'jquery' ), WPXSMARTSHOP_VERSION, true );
        wp_localize_script( 'wp-smartshop-frontend-js', 'wpSmartShopJavascriptLocalization', WPXSmartShop::scriptLocalization() );
    }


    /**
     * Intercetta il caricomento di una vista lato frontend ed esegue un bypass nel caso la pagina ricercata non
     * esista. Questa situazione potrebbe verificarsi per due motivi: il primo è che effettivamente la pagina non
     * eistse, il secondo è che è stato chiamato un custom permalink che dev'essere trattato onfly.
     *
     * @package            wpx SmartShop
     * @subpackage         WPXSmartShopFrontend
     * @since              1.0
     *
     * @static
     *
     */
    public function template_redirect() {

        /* WPML Integration. */
        if( WPXSmartShopWPML::isWPLM() ) {
            $code = ICL_LANGUAGE_CODE;
            setcookie('__WPXSS_LANGUAGE', $code, time()+86400, '/' );
        }

        /* @todo Patch per WP 3.4 e WPML che non restituiscono correttamente un 404 */
        global $wp;

        /* La regola è che se non ci sono pagine prefatte, uso by-pass interno */
        /* @todo $wp->matched_rule == 'en' Patch per WP 3.4 e WPML che non restituiscono correttamente un 404 */
        if ( is_404() || $wp->matched_rule == 'en' ) {
            if ( WPXSmartShopPermalink::dispatchRequest() ) {
                die();
            }
        }
    }

    // -----------------------------------------------------------------------------------------------------------------
    // Orders UI/View
    // -----------------------------------------------------------------------------------------------------------------

    public static function orders( $id_user, $date_from, $date_to ) {
        global $wpdb;

        /* Costruisco la select */
        $where        = 'WHERE 1';
        $orderby      = isset( $_GET['orderby'] ) ? $_GET['orderby'] : 'order_datetime';
        $order        = isset( $_GET['order'] ) ? $_GET['order'] : 'desc';

        /* Nomi delle tabelle */
        $table_orders = WPXSmartShopOrders::tableName();
        $table_stats  = WPXSmartShopStats::tableName();

        /* Group by */
        $group_by = 'GROUP BY orders.id';

        $where .= sprintf( ' AND `id_user_order` = %s', $id_user );

        $sql = <<< SQL
SELECT orders.*,
       users.display_name AS user_display_name,
       users_orders.display_name AS user_order_display_name
FROM `{$table_orders}` AS orders
LEFT JOIN `{$wpdb->users}` AS users ON orders.id_user = users.ID
LEFT JOIN `{$wpdb->users}` AS users_orders ON orders.id_user_order = users_orders.ID
LEFT JOIN `{$table_stats}` AS stats ON orders.id = stats.id_order
{$where}
{$group_by}
ORDER BY `{$orderby}` {$order}

SQL;
        $rows = $wpdb->get_results($sql);

        if ( $rows ) {

            /* thead columns */
            $columns = array(
                'order_datetime'  => __( 'Date', WPXSMARTSHOP_TEXTDOMAIN ),
                'track_id'        => __( 'Order', WPXSMARTSHOP_TEXTDOMAIN ),
                'total'           => __( 'Amount', WPXSMARTSHOP_TEXTDOMAIN ),
                'status'          => __( 'Status', WPXSMARTSHOP_TEXTDOMAIN ),
            );

            $html_columns = '';
            foreach ( $columns as $column_key => $column ) {
                $html_columns .= sprintf( '<th class="wpss_frontend_orders-%s">%s</td>', $column_key, $column );
            }

            /* tbody data rows */
            $html_rows = '';

            function cel( $columns, $row ) {
                $html_cels = '';
                foreach ( $columns as $column_key => $column ) {
                    if ( $column_key == 'order_datetime' ) {
                        $value = WPDKDateTime::timeNewLine( mysql2date( __( 'm/d/Y H:i', WPXSMARTSHOP_TEXTDOMAIN ), $row->$column_key ) );
                    }
                    elseif ( $column_key == 'track_id' ) {
                        /**
                         * @todo Da documentare
                         *
                         * Permette di decidere un URL
                         *
                         * @filters
                         */
                        $url = apply_filters( 'wpxss_invoice_page_link', '', $row );
                        if ( !empty( $url ) ) {
                            $value = sprintf( '<a href="%s">%s</a>', $url, $row->$column_key );
                        } else {
                            $value = $row->$column_key;
                        }
                    }
                elseif( $column_key == 'total') {
                        $value = WPXSmartShopCurrency::formatCurrency( $row->$column_key );
                    }
                else {
                        $value = $row->$column_key;
                    }
                    $html_cels .= sprintf( '<td class="wpss_frontend_orders-%s">%s</td>', $column_key, $value );
                }
                return $html_cels;
            }

            foreach ( $rows as $row ) {
                $html_rows .= sprintf( '<tr class="wpss_frontend_orders-%s">%s</tr>', $row->status, cel( $columns, $row ) );
            }


            $html = <<< HTML
    <table class="wpss_frontend_orders" cellpadding="0" cellspacing="0" border="0">
        <thead>
            <tr>
                {$html_columns}
            </tr>
        </thead>
        <tbody>
            {$html_rows}
        </tbody>
    </table>
HTML;

        } else {
            $message = __( 'No order for selected filters', WPXSMARTSHOP_TEXTDOMAIN );
            $html = <<< HTML
    <p>{$message}</p>
HTML;

        }

        return $html;
    }

    // -----------------------------------------------------------------------------------------------------------------
    // Coupons UI/View
    // -----------------------------------------------------------------------------------------------------------------

    public static function coupons( $id_user, $date_from, $date_to ) {
        global $wpdb;

        /* Costruisco la select */
        $where        = 'WHERE 1';
        $orderby      = isset( $_GET['orderby'] ) ? $_GET['orderby'] : 'status_datetime';
        $order        = isset( $_GET['order'] ) ? $_GET['order'] : 'asc';

        /* Nomi delle tabelle */
        $table   = WPXSmartShopCoupons::tableName();
        $status  = WPXSMARTSHOP_COUPON_STATUS_AVAILABLE;

        $where .= sprintf( ' AND `id_user_maker` = %s', $id_user );

        /* Status */
        if ( !empty( $_GET['status'] ) ) {
            if ( $_GET['status'] != 'all' ) {
                $where .= sprintf( " AND `status` = '%s'", esc_attr( $_GET['status'] ) );
            }
        } else {
            $where .= " AND `status` = 'available'";
        }

        $sql = <<< SQL
 		SELECT coupon.*,
 		       users.display_name AS user_display_name,
 		       users_owner.display_name AS users_owner_display_name,
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
 		LEFT JOIN `{$wpdb->posts}` AS products ON coupon.id_product = products.ID
 		LEFT JOIN `{$wpdb->posts}` AS products_maker ON coupon.id_product_maker = products_maker.ID
 		LEFT JOIN `{$wpdb->terms}` AS terms_product_type ON coupon.id_product_type = terms_product_type.term_id
 		{$where}
 		ORDER BY `{$orderby}` {$order}

SQL;
        $rows = $wpdb->get_results($sql);

        if ( $rows ) {

            /* thead columns */
            $columns = array(
                'uniqcode'           => __( 'Code', WPXSMARTSHOP_TEXTDOMAIN ),
                'product_name'       => __( 'Product', WPXSMARTSHOP_TEXTDOMAIN ),
                'product_type_name'  => __( 'Product type', WPXSMARTSHOP_TEXTDOMAIN ),
            );

            $html_columns = '';
            foreach ( $columns as $column_key => $column ) {
                $html_columns .= sprintf( '<th class="wpss_frontend_coupons-%s">%s</td>', $column_key, $column );
            }

            /* tbody data rows */
            $html_rows = '';

            function cel( $columns, $row ) {
                $html_cels = '';
                foreach ( $columns as $column_key => $column ) {
                    if ( $column_key == 'date_insert' ) {
                        $value = WPDKDateTime::timeNewLine( mysql2date( __( 'm/d/Y H:i', WPXSMARTSHOP_TEXTDOMAIN ), $row->$column_key ) );
                    } elseif ( $column_key == 'product_name' || $column_key == 'product_type_name' ) {
                        $value = empty($row->$column_key) ? '-' :$row->$column_key;
                    } else {
                        $value = $row->$column_key;
                    }
                    $html_cels .= sprintf( '<td class="wpss_frontend_coupons-%s">%s</td>', $column_key, $value );
                }
                return $html_cels;
            }

            foreach ( $rows as $row ) {
                $html_rows .= sprintf( '<tr class="wpss_frontend_coupons-%s">%s</tr>', $row->status, cel( $columns, $row ) );
            }

            /* Status filters */

            $filters = array(
                'available' => __( 'Available', 'bnm' ),
                'confirmed' => __( 'Used', 'bnm' ),
            );

            $html_options = '';
            foreach ( $filters as $filter_key => $filter ) {
                $selected = '';
                if ( !empty( $_GET['status'] ) ) {
                    if ( esc_attr( $_GET['status'] ) == $filter_key ) {
                        $selected = 'selected="selected"';
                    }
                }
                $html_options .= sprintf( '<option %s value="%s">%s</option>', $selected, $filter_key, $filter );
            }

            $html_select_status = <<< HTML
    <select class="wpdk-form-select" onchange="jQuery(this).parent().submit()" name="status">
        {$html_options}
    </select>
HTML;



            $html = <<< HTML
    <div class="wpss_frontend_filters">
        <form class="wpss_frontend_filters-form" method="get" action="">
            {$html_select_status}
        </form>
    </div>
    <table class="wpss_frontend_coupons" cellpadding="0" cellspacing="0" border="0">
        <thead>
            <tr>
                {$html_columns}
            </tr>
        </thead>
        <tbody>
            {$html_rows}
        </tbody>
    </table>
HTML;

        } else {
            $message = __( 'No coupons for selected filters', WPXSMARTSHOP_TEXTDOMAIN );
            $html = <<< HTML
    <p>{$message}</p>
HTML;

        }

        return $html;
    }


}