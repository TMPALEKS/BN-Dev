<?php
/**
 * @class              WPXSmartShopAjax
 *
 * @description        Ajax Engine
 *
 * @package            wpx SmartShop
 * @subpackage         core
 * @author             =undo= <g.fazioli@wpxtre.me>
 * @copyright          Copyright (c)2012 wpXtreme, Inc.
 * @created            16/11/11
 * @version            1.0
 *
 */

if ( wpdk_is_ajax() ) {

    class WPXSmartShopAjax {

        // -------------------------------------------------------------------------------------------------------------
        // Statics: method array to register
        // -------------------------------------------------------------------------------------------------------------
        private static function actionsMethods() {
            $actionsMethods = array(
                'action_cart_add_product'                    => true,
                'action_cart_empty'                          => true,
                'action_cart_update_qty'                     => true,
                'action_cart_delete_product'                 => true,
                'action_cart_display'                        => true,

                'action_summary_order_update_qty'            => true,
                'action_summary_order_delete_product'        => true,
                'action_summary_order_display'               => true,
                'action_summary_order_update_product_coupon' => true,
                'action_summary_order_update_order_coupon'   => true,

                'action_product_update_status'               => true,
                'action_product_check_availability'          => true,

                'action_showcase_update_status'              => true,

                'action_user_more'                           => true,

                'action_product_picker_display'              => true,
                'action_product_picker_html_products'        => true,
                'action_product_picker_load_next_items'      => true,

                'action_product_card_reload'                 => true,

                'action_wpdk_dismiss_wp_pointer'             => true,
            );
            return $actionsMethods;
        }

        // -------------------------------------------------------------------------------------------------------------
        // Register Ajax methods
        // -------------------------------------------------------------------------------------------------------------
        public static function registerAjaxMethods() {
            $actionsMethods = self::actionsMethods();
            foreach ( $actionsMethods as $method => $nopriv ) {
                add_action( 'wp_ajax_' . $method, array( __CLASS__, $method ) );
                if ( $nopriv ) {
                    add_action( 'wp_ajax_nopriv_' . $method, array( __CLASS__, $method ) );
                }
            }

            /* Questa è un hack per WPML quando funziona da Ajax. Viene gestito un proprio cookie registrato dal
            frontend in modo da capire se siamo in italiano, inglese o altro. Vedi quindi theme() per la creazione del
            cookie.
            */
            if ( WPXSmartShopWPML::isWPLM() ) {
                global $sitepress;
                $code = $_COOKIE['__WPXSS_LANGUAGE'];
                $sitepress->switch_lang( $code, false );
            }
        }

        // -------------------------------------------------------------------------------------------------------------
        // Actions methods
        // -------------------------------------------------------------------------------------------------------------

        // -------------------------------------------------------------------------------------------------------------
        // Cart
        // -------------------------------------------------------------------------------------------------------------

        /**
         * Aggiunge un prodotto nel carrello
         *
         * @static
         *
         * @internal   param int $_POST ['id_product'] ID del prodotto
         * @internal   param string $_POST ['product'] Stringa jSON codificata base 64
         * @internal   param string $_POST ['id_variant'] ID della variante
         * @internal   param array $_POST ['variant'] Serializzata da jQuery
         */
        public static function action_cart_add_product() {
            $product               = WPXSmartShopSession::decodeProductKey( $_POST['product'] );
            $product['id_product'] = absint( $_POST['id_product'] );
            $id_variant            = esc_attr( $_POST['id_variant'] );
            if ( !empty( $id_variant ) ) {
                $product['id_variant'] = $id_variant;
                $variant               = $_POST['variant'];
                foreach ( $variant as $key_pair ) {
                    $product[$key_pair['name']] = $key_pair['value'];
                }
            }

            /* @todo Patch session su select variante */
            $_SESSION['wpss_last_product_variant'] = serialize( $product );

            $result = WPXSmartShopSession::addProduct( $product );

            $json = array(
                'content' => WPXSmartShopShoppingCart::cart()
            );

            if ( is_wp_error( $result ) ) {
                $json['message'] = $result->get_error_message();
            }

            echo json_encode( $json );

            die();
        }

        /**
         * Destroy Session
         *
         * @retval void
         */
        public static function action_cart_empty() {
            WPXSmartShopShoppingCart::emptyCart();
            $json = array(
                'content' => WPXSmartShopShoppingCart::cart()
            );
            echo json_encode( $json );
            die();
        }

        /**
         * Update Quantity Widget Cart Session
         *
         * @retval void
         */
        public static function action_cart_update_qty() {
            $id_product_key = $_POST['id_product_key'];
            $qty            = max( absint( $_POST['qty'] ), 1 );
            $new_qty        = WPXSmartShopShoppingCart::updateProductQuantity( $id_product_key, $qty );
            $content        = WPXSmartShopShoppingCart::cart();

            $json = array(
                'content'   => $content
            );

            /* @todo Da completare */
            if ( is_wp_error( $new_qty ) ) {
                /* @todo Aggiungere filtro */
                $json['message'] = $new_qty->get_error_message();
            }
            echo json_encode( $json );

            die();
        }

        /**
         * Delete an item product.
         * Questo restituisce un jSON articolato così che il Javascript che lo legge possa elaborare più informazioni
         * oltre che aggiornare l'output del carrello. Si veda il Javascript che emette un evento personalizzato per
         * segnalare alla pagina l'avvenuta modifica del carrello.
         *
         * @retval void
         */
        public static function action_cart_delete_product() {

            $id_product_key = $_POST['id_product_key'];
            WPXSmartShopShoppingCart::deleteProduct( $id_product_key );

            $content = WPXSmartShopShoppingCart::cart();
            /* @todo Da spostare in session */
            $total   = WPXSmartShopShoppingCart::productNumbers();

            $json = array(
                'content'   => $content,
                'count'     => $total['count'],
                'total'     => $total['total'],
            );

            echo json_encode( $json );

            die();
        }

        /**
         * Simple Reload Widget Cart Session
         *
         * @retval void
         */
        public static function action_cart_display() {
            $content = WPXSmartShopShoppingCart::cart();

            $json = array(
                'content'   => $content
            );

            echo json_encode( $json );

            die();
        }


        // -------------------------------------------------------------------------------------------------------------
        // Summary Order
        // Simile al Cart ma separato per questioni di debug, personalizzazioni future e logica
        // -------------------------------------------------------------------------------------------------------------

        /**
         * Update Quantity in Summary Order
         *
         * @retval void
         */
        public static function action_summary_order_update_qty() {
            $id_product_key = $_POST['id_product_key'];
            $qty            = max( absint( $_POST['qty'] ), 1 );
            $new_qty        = WPXSmartShopShoppingCart::updateProductQuantity( $id_product_key, $qty );
            $content        = WPXSmartShopSummaryOrder::summaryOrder();

            $json = array(
                'content'   => $content
            );

            /* @todo Da completare */
            if ( is_wp_error( $new_qty ) ) {
                $json['message'] = $new_qty->get_error_message();
            }
            echo json_encode( $json );

            die();
        }

        /**
         * Delete an item product
         */
        public static function action_summary_order_delete_product() {

            $id_product_key = $_POST['id_product_key'];
            WPXSmartShopShoppingCart::deleteProduct( $id_product_key );

            $content = WPXSmartShopSummaryOrder::summaryOrder();
            /* @todo Da spostare in session */
            $total   = WPXSmartShopShoppingCart::productNumbers();

            $json = array(
                'content'   => $content,
                'count'     => $total['count'],
                'total'     => $total['total'],
            );

            echo json_encode( $json );

            die();
        }

        /**
         * Display Summary Order
         */
        public static function action_summary_order_display() {
            $content = WPXSmartShopSummaryOrder::summaryOrder();
            $json    = array(
                'content'   => $content
            );

            echo json_encode( $json );
            die();
        }

        /**
         * Associa ad un prodotto un coupon eseguendo una serie di controlli sul singolo coupon
         *
         * @internal param string id_product_key ID del prodotto + variante in jSON codificato base 64
         * @internal param string coupon_code Serial number del coupon (uniqcode)
         *
         */
        public static function action_summary_order_update_product_coupon() {

            $id_product_key = $_POST['id_product_key'];
            $coupon_code    = $_POST['coupon_code'];
            $result         = WPXSmartShopSession::updateProductCoupons( $id_product_key, $coupon_code );
            $content        = WPXSmartShopSummaryOrder::summaryOrder();

            $json = array(
                'content' => $content
            );

            if ( is_wp_error( $result ) ) {
                $json['message'] = $result->get_error_message();
            }

            echo json_encode( $json );

            die();
        }

        /**
         * Associa ad un ordine un coupon eseguendo una serie di controlli sul singolo coupon
         *
         */
        public static function action_summary_order_update_order_coupon() {

            $coupon_code = $_POST['coupon_code'];
            $result      = WPXSmartShopSession::updateOrderCoupon( $coupon_code );
            $content     = WPXSmartShopSummaryOrder::summaryOrder();

            $json = array(
                'content' => $content
            );

            if ( is_wp_error( $result ) ) {
                $json['message'] = $result->get_error_message();
            }

            echo json_encode( $json );
            die();
        }


        // -------------------------------------------------------------------------------------------------------------
        // Product Card
        // -------------------------------------------------------------------------------------------------------------

        /**
         * Ricarica la scheda di un prodotto
         *
         */
        public static function action_product_card_reload() {

            $id_product = absint( $_POST['id_product'] );
            $product    = WPXSmartShopProduct::product( $id_product );
            $args       = unserialize( base64_decode( $_POST['args'] ) );

            /**
             * @actions
             *
             * @param int   $id_product ID del prodotto
             * @param array $args       Array di keypair parametri per il product card
             */
            do_action( 'wpss_ajax_action_product_card_reload', $id_product, $args );

            $content = WPXSmartShopProduct::card( $product, $args );

            $json = array(
                'content' => $content
            );
            echo json_encode( $json );
            die();
        }

        // -------------------------------------------------------------------------------------------------------------
        // Product
        // -------------------------------------------------------------------------------------------------------------

        /**
         * Modifica il post_status di un prodotto
         *
         * @retval void
         */
        public static function action_product_update_status() {
            $id_post     = intval( $_POST['id'] );
            $enabled     = esc_attr( $_POST['enabled'] );
            $post_status = ( $enabled == 'on' ) ? 'publish' : 'draft';

            $post = array(
                'ID'            => $id_post,
                'post_status'   => $post_status
            );

            wp_update_post( $post );

            $availability = WPXSmartShopProduct::isProductAvailable( $id_post );
            $class        = $availability ? 'yes' : 'no';
            $text         = $availability ? __( 'Yes', WPXSMARTSHOP_TEXTDOMAIN ) : __( 'No', WPXSMARTSHOP_TEXTDOMAIN );
            $content      = sprintf( '<span class="wpssProductAvailable %s">%s</span>', $class, $text );
            $json         = array(
                'content' => $content
            );
            echo json_encode( $json );

            die();
        }

        /**
         * Controlla la disponibilità di un prodotto
         *
         * @todo       Questa attualmente non viene utilizzata in quanto verifica la disponibilità solo sui dati
         *             memorizzati sul database e non su quello degli input text onfly
         *
         * @retval void
         */
        public static function action_product_check_availability() {
            $id_post = intval( $_POST['id'] );

            $availability = WPXSmartShopProduct::isProductAvailable( $id_post );
            $class        = $availability ? 'yes' : 'no';
            $text         = $availability ? __( 'Yes', WPXSMARTSHOP_TEXTDOMAIN ) : __( 'No', WPXSMARTSHOP_TEXTDOMAIN );
            $content      = sprintf( '<span class="wpssProductAvailable %s">%s</span>', $class, $text );
            $json         = array(
                'content' => $content
            );
            echo json_encode( $json );

            die();
        }


        // -------------------------------------------------------------------------------------------------------------
        // Used by Product Picker
        // -------------------------------------------------------------------------------------------------------------

        /**
         * Display a Product Picker
         * @static
         *
         */
        public static function action_product_picker_display() {

            $id = $_POST['id'];

            $args = WPSmartShopProductPicker::merge( $_POST );

            $product_picker = new WPSmartShopProductPicker( $args );
            $content        = $product_picker->display( $id, false );
            $json           = array(
                'content' => $content
            );
            echo json_encode( $json );

            die();
        }

        /**
         * Display all product by term id
         *
         * @static
         *
         */
        public static function action_product_picker_html_products() {
            $term_id   = intval( $_POST['term_id'] );
            $json_args = json_decode( base64_decode( $_POST['json_args'] ) );

            $json_args->search_filter = '';
            if ( isset( $_POST['search_filter'] ) ) {
                $json_args->search_filter = $_POST['search_filter'];
            }

            $product_picker = new WPSmartShopProductPicker( $json_args );
            $content        = $product_picker->htmlProducts( $term_id );

            $json = array(
                'content' => $content
            );
            echo json_encode( $json );

            die();
        }

        /**
         * Display next more items
         *
         * @static
         *
         */
        public static function action_product_picker_load_next_items() {
            $paged     = isset( $_POST['paged'] ) ? absint( $_POST['paged'] ) : 1;
            $term_id   = absint( $_POST['term_id'] );
            $json_args = json_decode( base64_decode( $_POST['json_args'] ) );

            $product_picker = new WPSmartShopProductPicker( $json_args );
            $content        = $product_picker->htmlProductsList( $term_id, $paged );
            $json           = array(
                'content' => $content
            );
            echo json_encode( $json );
            die();
        }


        // -------------------------------------------------------------------------------------------------------------
        // Showcase
        // -------------------------------------------------------------------------------------------------------------


        /**
         * Modifica il post_status di un post custom showcase
         *
         * @retval void
         */
        public static function action_showcase_update_status() {
            $id_post     = intval( $_POST['id'] );
            $enabled     = esc_attr( $_POST['enabled'] );
            $post_status = ( $enabled == 'on' ) ? 'publish' : 'draft';

            $post = array(
                'ID'            => $id_post,
                'post_status'   => $post_status
            );

            wp_update_post( $post );

            /* @todo Ancora non usato. */
            //$availability = WPXSmartShopProduct::isProductAvailable( $id_post );
            $availability = true;
            $class        = $availability ? 'yes' : 'no';
            $text         = $availability ? __( 'Yes', WPXSMARTSHOP_TEXTDOMAIN ) : __( 'No', WPXSMARTSHOP_TEXTDOMAIN );
            $content      = sprintf( '<span class="wpss_showcase_available %s">%s</span>', $class, $text );

            $json         = array(
                'content' => $content
            );
            echo json_encode( $json );

            die();
        }


        // -------------------------------------------------------------------------------------------------------------
        // User
        // -------------------------------------------------------------------------------------------------------------

        /**
         * Pagina lo UserPicker, carica cioè la pagina successiva
         *
         * @todo       Questo sembrerebbe verticalizzato per i Coupon, anche se uno user picker può essere usato da chiunque
         *             sarebbe da postare tale paginazione in WPDK
         *
         */
        public static function action_user_more() {
            $paged = isset( $_POST['paged'] ) ? intval( $_POST['paged'] ) : 1;
            WPXSmartShopCoupons::dialogUserPicker( $paged );
            die();
        }


        // -------------------------------------------------------------------------------------------------------------
        // WordPress Pointer
        // -------------------------------------------------------------------------------------------------------------

        /**
         * Aggiunge un pointer alla lista degli esclusi. Il tutto per utenza.
         *
         * @static
         *
         */
        public static function action_wpdk_dismiss_wp_pointer() {
            $id_user             = get_current_user_id();
            $pointer             = esc_attr( $_POST['pointer'] );
            $dismissed           = unserialize( get_user_meta( $id_user, 'wpdk_dismissed_wp_pointer', true ) );
            $dismissed[$pointer] = true;
            update_user_meta( $id_user, 'wpdk_dismissed_wp_pointer', serialize( $dismissed ) );
        }

    }

    WPXSmartShopAjax::registerAjaxMethods();
}