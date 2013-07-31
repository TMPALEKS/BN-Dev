<?php
/**
 * Plugin Name: BNM Extends
 * Plugin URI: http://www.saidmade.com/
 * Description: Custom WordPress Plugin for Blue Note Milano
 * Version: 0.5.45
 * Author: Giovambattista Fazioli
 * Author URI: http://www.saidmade.com
 *
 * This plugin was create for Blue Note Milano by Saidmade, srl. All rights reserver
 *
 * @package   BNMExtends
 * @author    Giovambattista Fazioli <g.fazioli@saidmade.com>
 * @copyright Copyright (c) 2011-2012, Saidmade, srl
 * @link      http://www.saidmade.com
 *
 */

// Avoid direct access
if( !defined( 'ABSPATH' ) ) {
  exit;
}

/* Check WPDK */
require_once('bootstrap.php');

/* @todo Questa struttura è obsolete e andrebbe riallineata con lo scheletro più recente */
if ( class_exists( 'WPDK' ) ) {

    class BNMExtends extends WPDKWordPress {

        private static $_discoundIDs;

        public static function init() {

            /* Hook on Login */
            add_action( 'wp_login', array( __CLASS__, 'wp_login') );

            /* Widget Init */
            add_action( 'widgets_init', array( __CLASS__, 'widgets_init') );

            /* Set the constants needed by the plugin. */
            add_action( 'plugins_loaded', array( __CLASS__, 'defineConstants' ), 1 );

            /* Load the functions files. */
            add_action( 'plugins_loaded', array( __CLASS__, 'includes' ), 3 );

            /* Load the admin files. */
            add_action( 'plugins_loaded', array( __CLASS__, 'plugins_loaded' ), 4 );

            /* Varie registrazione alla init */
            add_action( 'init', array( __CLASS__, '_init' ) );

            /* Internationalize the text strings used. */
            add_action( 'init', array( __CLASS__, 'i18n' ) );

            /* Activation & Deactivation Hook */
            register_activation_hook( __FILE__ , array( __CLASS__, 'activation' ));
            register_deactivation_hook( __FILE__, array( __CLASS__, 'deactivation' ));

            /* -- Extra -- */

            /* Login: BNMExtendsUser eredita WPDKUser */
            add_action( 'init', array( 'BNMExtendsUser', 'doLogin' ), 10, 0 );
            add_action( 'delete_user', array( 'BNMExtendsUser', 'delete_user' ) );
            add_action( 'wpdk_login_wrong', array( __CLASS__, 'wpdk_login_wrong' ) );

            /* WordPress search filter */
            add_filter( 'pre_get_posts', array( __CLASS__, 'pre_get_posts' ) );
            add_filter( 'widget_categories_dropdown_args', array( __CLASS__, 'widget_categories_dropdown_args') );
            //add_filter( 'the_content_more_link', array( __CLASS__, 'the_content_more_link') );

            /* Smart Shop */

            add_filter( 'wpss_message_you_have_to_login', array( __CLASS__, 'wpss_message_you_have_to_login') );
            add_action( 'wpss_ajax_action_product_card_reload', array( __CLASS__, 'wpss_ajax_action_product_card_reload'), 10, 2);

            add_action( 'wpss_checkout_bill_information', array( 'BNMExtendsSummaryOrder', 'wpss_checkout_bill_information'));
            add_action( 'wpss_checkout_shipping_information', array( 'BNMExtendsSummaryOrder', 'wpss_checkout_shipping_information'));
            //add_filter( 'wpss_product_variant_label', array( __CLASS__, 'wpss_product_variant_label'), 10, 2);
            add_filter( 'wpss_product_variant_localizable_value', array( __CLASS__, 'wpss_product_variant_localizable_value' ), 10, 5 );
            //add_filter( 'wpss_product_variant_value', array( __CLASS__, 'wpss_product_variant_localizable_value' ), 10, 5 );
            //add_filter( 'wpss_product_card_rows', array( __CLASS__, 'wpss_product_card_rows' ), 10 );
            add_filter( 'wpss_product_card_div_base_price', array( __CLASS__, 'wpss_product_card_div_base_price'), 10, 2 );
            add_filter( 'wpxss_product_card_title', array( __CLASS__, 'wpxss_product_card_title') );
            add_filter( 'wpxss_product_store_quantity_for_order', array( __CLASS__, 'wpxss_product_store_quantity_for_order'), 10, 5 );
            add_filter( 'wpxss_cart_add_enabled', array( __CLASS__, 'wpxss_cart_add_enabled'), 10, 2 );

            add_filter( 'wpxss_stats_column_price_rule_online', array( __CLASS__, 'wpxss_stats_column_price_rule_online'), 10, 2 );
            add_filter( 'wpxss_stats_column_price_rule', array( __CLASS__, 'wpxss_stats_column_price_rule'), 10, 2 );
            add_filter( 'wpxss_product_date_expired', array( __CLASS__, 'wpxss_product_date_expired'), 10, 2 );
            add_filter( 'wpxss_coupon_user_owner_different', array( __CLASS__, 'wpxss_coupon_user_owner_different') );

            /* Payment Gateway */
            add_filter( 'wpss_payment_gateway_order_will_insert', array( 'BNMExtendsSummaryOrder', 'wpss_payment_gateway_order_will_insert'));
            add_filter( 'wpss_payment_gateway_order_did_insert', array( 'BNMExtendsSummaryOrder', 'wpss_payment_gateway_order_did_insert'));
            add_action( 'wpss_payment_result_invoice', array( 'BNMExtendsSummaryOrder', 'wpss_payment_result_invoice') );

            /* Custom capabilities in product membership meta box */
            add_filter( 'wpss_product_membership_capabilities_list', array( __CLASS__, 'wpss_product_membership_capabilities_list'));

            add_filter( 'wpss_cart_update_quantity', array( __CLASS__, 'wpss_summary_order_update_quantity'), 10, 2);

            add_filter( 'wpss_invoice_rows_summary_products', array( __CLASS__, 'wpss_invoice_rows_summary_products') );
            add_filter( 'wpss_invoice_columns_summary_products', array( __CLASS__, 'wpss_invoice_columns_summary_products') );

            /* Altera la tabella standard del checkout Summary Order */
            add_filter( 'wpss_summary_order_rows', array( __CLASS__, 'wpss_summary_order_rows'));
            add_filter( 'wpss_summary_order_update_quantity', array( __CLASS__, 'wpss_summary_order_update_quantity'), 10, 2);
            add_filter( 'wpss_summary_order_product_shipping', array( 'BNMExtendsSummaryOrder', 'wpss_summary_order_product_shipping'), 10, 3);

            /* Placeholders lato backend */
            add_filter( 'wpph_reservation_edit_id_who', array( __CLASS__, 'wpph_reservation_edit_id_who'), 10, 2);
            add_filter( 'wpph_reservation_list_table_columns', array( __CLASS__, 'wpph_reservation_list_table_columns'));
            add_filter( 'wpph_reservation_list_table_column_output', array( __CLASS__, 'wpph_reservation_list_table_column_output'), 10, 3);
            add_filter( 'wpph_reservation_list_table_sql_extra_field', array( __CLASS__, 'wpph_reservation_list_table_sql_extra_field'));
            add_filter( 'wpph_reservation_list_table_sql_extra_join', array( __CLASS__, 'wpph_reservation_list_table_sql_extra_join'));

            add_action( 'admin_head', array( 'BNMExtendsPlaceHolder', 'adminEnqueueScripts' ) ); //Add custom admin scripts
            add_action( 'wp_enqueue_scripts', array( 'BNMExtendsPlaceHolder', 'enqueueScripts' ) ); //Add custom scripts
            add_action( 'wp_enqueue_scripts', array( 'BNMExtendsPlaceHolder', 'deregisterScripts' ), 999 );

            add_action( 'wpdk_list_before_table_filter', array( 'BNMExtendsPlaceHolder', 'addAutocompleteForm' ) ); //Add autocomplete form
            add_action( 'wpdk_edit_before_table_filter', array( 'BNMExtendsPlaceHolder', 'addAutocompleteForm' ) ); //Add autocomplete form

            add_filter( 'wpph_reservation_list_table_custom_sort', array( 'BNMExtendsPlaceHolder','doSortPlaces' ) ); //Trigger custom sort properties in list all
            add_filter( 'wpph_reservation_options_custom_sort', array( 'BNMExtendsPlaceHolder','doSortComboPlaces' ) ); //Trigger custom sort properties in combos

            //add_action( 'wp_ajax_wpph_reservations', array( 'BNMExtendsPlaceHolder','doReservationFromBoxOffice') );

            /* Invoices */
            add_filter( 'wpxss_orders_custom_field', array( 'BNMExtendsInvoices', 'addInvoiceInformationsBox'),10,2);
            add_action( 'wpss_orders_after_insert', array( 'BNMExtendsInvoices','addOrUpdateInvoice' ), 10, 1 );
            add_action( 'wpss_orders_after_update', array( 'BNMExtendsInvoices','addOrUpdateInvoice' ), 10, 1 );

            /* Orders */
            add_action( 'wpss_orders_after_insert', array( 'BNMExtendsOrders','closePendingOrders' ), 20, 1 ); //aggancia l'ordine effettuato
            add_action( 'bnmextends_orders_status', array( 'BNMExtendsOrders','updateOrderWithStatus'), 1, 1 ); //Imposta il cron sull'ordine pending
            add_action( 'wpss_checkout_bill_information', array( 'BNMExtendsOrders','fixBoxOfficePlacehoder') );
            add_action( 'cron_schedules', array( 'BNMExtendsOrders','cornSchedules') );

            /* Stats */
            add_filter( 'wpxss_orderby_stats_summary', array( 'BNMExtendsStats','orderSummary' ) );
           // add_filter( 'wpxss_listtable_query_where', array( 'BNMExtendsStats','queryWhere') );
            add_filter( 'wpxss_add_columns_listtable', array( 'BNMExtendsStats','addColumns') );
            add_filter( 'wpxss_column_custom', array( 'BNMExtendsStats','customColumnDefault') );

            add_filter( 'wpxss_stats_export_columns_csv', array( 'BNMExtendsStats','addColumnsCSV') );
            add_filter( 'wpxss_stats_export_buffer_csv', array( 'BNMExtendsStats','alterBufferCSV') );
            add_action( 'admin_init', array( 'BNMExtendsStats', 'adminInit') );
            add_action( 'wpxss_stats_csv_should_export', array( 'BNMExtendsStats','addExportForSapCSV' ) );

            add_action( 'wpss_stats_extra_values', array( 'BNMExtendsStats','addStatsProductCategories' ), 10, 2 );

        }

        /// Registrati e inizializzazioni in fase di init WordPress
        public static function _init() {

            /* Register Custom Post Type and Taxonomies. */
            BNMExtendsEventPostType::registerPostType();
            BNMExtendsArtistPostType::registerPostType();
            BNMExtendsSystemPagePostType::registerPostType();

            flush_rewrite_rules();

            /* Lasciare sotto forma di metodo perché chiamato anche dal gateway Ajax. */
            self::registerBoxOffice();

            if ( WPDKUser::hasCaps( array( 'bnm_cap_facility_dinner' ) ) ) {
                BNMExtendsSummaryOrder::registerClubPlatinum();
            }

            /* Add custom own image size: if you change this value you must rigenerate all thumbnail icon */
            add_image_size( kBNMExtendsThumbnailSizeSmallKey, kBNMExtendsThumbnailSizeSmallWidth, kBNMExtendsThumbnailSizeSmallHeight, true );
            add_image_size( kBNMExtendsThumbnailSizeMediumKey, kBNMExtendsThumbnailSizeMediumWidth, kBNMExtendsThumbnailSizeMediumHeight, true );
            add_image_size( kBNMExtendsThumbnailSizeLargeKey, kBNMExtendsThumbnailSizeLargeWidth, kBNMExtendsThumbnailSizeLargeHeight, true );

            /* Register class mail. */
            BNMExtendsMail::registerHooks();
        }

        // -------------------------------------------------------------------------------------------------------------
        // Defines Constants
        // -------------------------------------------------------------------------------------------------------------

        public static function defineConstants() {

            /* Define some useful backward compatible constants */
            //parent::definesBackwordConstats();

            /* Set __FILE__ for global access */
            define( 'kBNMExtends__FILE__', __FILE__ );

            /* Set constant path: plugin directory. */
            define( 'kBNMExtendsDirectoryPath', trailingslashit( plugin_dir_path( __FILE__ ) ) );

            /* Set constant path: plugin URL. */
            define( 'kBNMExtendsURI', trailingslashit( plugin_dir_url( __FILE__ ) ) );

            /* Includes other defines */
            require_once( 'main.h.php' );
        }

        // -------------------------------------------------------------------------------------------------------------
        // Localization
        // -------------------------------------------------------------------------------------------------------------

        public static function i18n() {

            /* Load the translation of the plugin. */

            $result = load_plugin_textdomain( 'bnmextends', false, 'BNMExtends/localization' );

            /* Da cambiare due volte per via di poedit */
            define( 'BNMEXTENDS_WITHOUT_DINNER_RESERVATION', __( 'Without Dinner Reservation', 'bnmextends' ) );
            define( 'BNMEXTENDS_WITH_DINNER_RESERVATION', __( 'With Dinner Reservation', 'bnmextends' ) );

            define( 'BNMEXTENDS_2_ADULTS_2_CHILDREN', __( '2 adults and 2 children', 'bnmextends' )  );
            define( 'BNMEXTENDS_2_ADULTS_1_CHILD', __( '2 adults and 1 child', 'bnmextends' )  );
            define( 'BNMEXTENDS_2_ADULTS', __( '2 adults', 'bnmextends' )  );

        }
        // -------------------------------------------------------------------------------------------------------------
        // Includes
        // -------------------------------------------------------------------------------------------------------------

        public static function includes() {

            /* WordPress options */
            require_once('Classes/BNMExtendsOptions.php');

            /* CPT & taxonomies */
            require_once( 'Classes/BNMExtendsArtistPostType.php' );
            require_once( 'Classes/BNMExtendsEventPostType.php' );
            require_once( 'Classes/BNMExtendsSystemPagePostType.php' );

            /* Widgets
            require_once( 'Classes/BNMExtendsWidgetComments.php' );
            require_once( 'Classes/BNMExtendsWidgetNewsletter.php' );
            require_once( 'Classes/BNMExtendsWidgetChildrenPages.php' );
            */

            /* Extends */
            require_once( 'Classes/BNMExtendsUser.php' );
            require_once( 'Classes/BNMExtendsSummaryOrder.php' );
            require_once( 'Classes/BNMExtendsCalendar.php' );
            require_once('Classes/BNMExtendsOrders.php');
            require_once( 'Classes/BNMExtendsInvoices.php' );
            require_once( 'Classes/BNMExtendsPlaceHolder.php' );
            require_once( 'Classes/BNMExtendsStats.php' );


            /* Clone */
            require_once( 'Classes/BNMExtendsClone.php' );

            /* Mail */
            require_once( 'Classes/bnmextends-mail.php' );

            /* User Advanced */
            #require_once( 'Classes/user/BNMExtendsUser-listtable.php' );

            self::$_discoundIDs = BNMExtendsSummaryOrder::discountIDs();

        }

        // -------------------------------------------------------------------------------------------------------------
        // Plugin Loaded
        // -------------------------------------------------------------------------------------------------------------

        public static function plugins_loaded() {

            /* Register Options */
            BNMExtendsOptions::init();

            /* Check Ajax */
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
                require_once('Classes/BNMExtendsAjax.php');
                return;
            }

            /* Only load files if in the WordPress admin. */
            if ( is_admin() ) {

                /* Load the main admin file. */
                require_once('Classes/BNMExtendsAdmin.php');
                BNMExtendsAdmin::init();

                /* Load the secondary admin file. */
                require_once('admin/BNMExtends-admin.php');
                $adminAdvance = new BNMExtendsAdminAdvance();
            } else {

                add_filter( 'body_class', array( __CLASS__, 'body_class' ) );

                /* Front end */
                require_once('Classes/BNMExtendsCalendar.php');
                require_once('Classes/BNMExtendsContacts.php');
            }
        }

        // -------------------------------------------------------------------------------------------------------------
        // Widgets Init
        // -------------------------------------------------------------------------------------------------------------

        public static function widgets_init() {

            /* Init the Comments Widget */
            if(!class_exists('BNMExtendsWidgetComments')) {
                require_once('Classes/BNMExtendsWidgetComments.php');
            }
            register_widget('BNMExtendsWidgetComments');

            /* Init the MailChimp Newsletter Widget */
            if(!class_exists('BNMExtendsWidgetNewsletter')) {
                require_once('Classes/BNMExtendsWidgetNewsletter.php');
            }
            register_widget('BNMExtendsWidgetNewsletter');

		    /* Init the Boxoffice Widget */
            if(!class_exists('BNMExtendsWidgetBoxoffice')) {
                require_once('Classes/BNMExtendsWidgetBoxoffice.php');
            }
            register_widget('BNMExtendsWidgetBoxoffice');

		    /* Init the Children Pages Widget */
            if(!class_exists('BNMExtendsWidgetChildrenPages')) {
                require_once( 'Classes/BNMExtendsWidgetChildrenPages.php' );
            }
            register_widget('BNMExtendsWidgetChildrenPages');
	    }

        // -------------------------------------------------------------------------------------------------------------
        // WordPress and Smart Shop integration
        // -------------------------------------------------------------------------------------------------------------

        public static function activation() {

            self::defineConstants();

            /* Esegue un delta sulla struttura delle tabelle */
            if ( !class_exists( 'BNMExtendsUser' ) ) {
                require_once( 'Classes/BNMExtendsUser.php' );
            }
            ob_start(); // Necessario a causa dei warning emessi da dbDelta()
            BNMExtendsUser::updateTable();
            ob_end_clean();

            /* Register user capabilities and roles */
            BNMExtendsUser::registerRolesAndCapabilities();
        }

        public static function deactivation() {
            BNMExtendsUser::deregisterRolesAndCapabilities();
        }

        /**
         * Registra gli hook per l'utente Box officer che può operare in modo più completo sull'ordine
         *
         * @static
         *
         */
        public static function registerBoxOffice() {

            add_action( 'wp_head', array ( __CLASS__, 'wp_head_checkoutScript' ) );

       		/* Se l'utente corrente è un operatore box office */
       		if ( WPDKUser::hasCaps( array ( 'bnm_cap_offline' ) ) ) {

                /* Load script for box officer specified functions */
       			add_action( 'wp_head', array ( __CLASS__, 'wp_head_checkoutScriptBoxOffice' ) );
                add_filter( 'wpss_summary_order_before', array( 'BNMExtendsSummaryOrder', 'wpss_summary_order_before' ) );

                /* Smart Shop Hooks */
       			add_action( 'wpss_payment_gateway_did_order_insert', array ( __CLASS__, 'wpss_payment_gateway_did_order_insert' ), 10, 1 );
       			add_filter( 'wpss_payment_gateway_name', array ( __CLASS__, 'wpss_payment_gateway_name' ) );
       			add_filter( 'wpss_payment_gateway_payment_type', array ( __CLASS__, 'wpss_payment_gateway_payment_type' ) );
                add_filter( 'wpss_payment_gateway_id_user_order', array( __CLASS__, 'wpss_payment_gateway_id_user_order') );

       			/* Custom Smart Shop Summary Order for Box Officer operator */
                BNMExtendsSummaryOrder::registerBoxOffice();
       		}
       	}

        private static function searchExcludeWithIDs() {

            /* Slug in italiano delle pagine da eliminare dalla ricerca. */
            $pages = array(
                'benvenuto',
                'registrazione',
                'conferma-registrazione',
                'profilo',
                'profilo/coupons',
                'profilo/ordini',
            );
            $ids   = array();

            foreach ( $pages as $page ) {
                $post = get_page_by_path( $page );
                if ( $post ) {
                    $ids[absint( $post->ID )] = true;
                    if ( function_exists( 'icl_object_id' ) ) {
                        $id_eng = icl_object_id( $post->ID, $post->post_type, false, 'en' );
                        if ( $id_eng ) {
                            $ids[absint( $id_eng )] = true;
                        }
                    }
                }
            }

            return array_keys( $ids );
        }

        // -------------------------------------------------------------------------------------------------------------
        // DEBUG HELPER
        // -------------------------------------------------------------------------------------------------------------
        public static function logErrors( $message, $method = "", $line = "" ) {

            if ( BNMEXTENDS_ENABLE_ERRORS_LOG_FILE ){

                $timestamp = date( 'd/m/Y H:i:s' );

                if ( is_array($message) || is_object($message) )
                    $message = print_r($message, true);

                if ( $method )
                    $log = "\n[" . $timestamp . "] " . "Excecuting Method " . $method;
                if ( $line )
                    $log .= " (Line " . $line . ")";

                error_log( "\n---------------------------------------------", 3, BNMEXTENDS_ERRORS_LOG_FILE );
                error_log( "\n". $log, 3, BNMEXTENDS_ERRORS_LOG_FILE );
                error_log( "\n". $message , 3, BNMEXTENDS_ERRORS_LOG_FILE );
                error_log( "\n---------------------------------------------", 3, BNMEXTENDS_ERRORS_LOG_FILE );
            }
        }


        private static function searchForPostType() {
            $postTypes = array('page', 'post', kBNMExtendsEventPostTypeKey, kBNMExtendsArtistPostTypeKey, WPXSMARTSHOP_PRODUCT_POST_KEY );
            return $postTypes;
        }

        /**
         * @todo Da controllare
         *
         * @param $query
         *
         * @return
         */
        public static function pre_get_posts( $query ) {
            if ( !is_admin() && $query->is_search ) {
                $query->set( 'post_type', self::searchForPostType() );
                $query->set( 'post__not_in', self::searchExcludeWithIDs() );
            }
            return $query;
        }

        public static function wp_head_checkoutScriptBoxOffice() {
            if ( is_singular( kWPSmartShopStorePagePostTypeKey ) ) {

                global $post;

                /* @todo E qui... */
                if ( $post->post_name == 'checkout' || $post->post_name == 'cassa' ) {
                    wp_enqueue_script( 'bnm-checkout-boxoffice', kBNMExtendsURI . 'js/checkout_boxoffice.js' );

                    /* Attivo la visualizzazione della cassa manuale */
                    add_filter( 'wpxss_button_cash', '__return_true' );
                    add_filter( 'wpxss_button_cash_values', array( 'BNMExtendsSummaryOrder', 'wpxss_button_cash_values' ) );
                    add_filter( 'wpxss_button_cash_select_class', array( 'BNMExtendsSummaryOrder', 'wpxss_button_cash_select_class' ) );
                    add_filter( 'wpxss_button_cash_submit_class', array( 'BNMExtendsSummaryOrder', 'wpxss_button_cash_submit_class' ) );

                    /* Bottone di 'compra via cash' */
                    /* @todo Rimuovere per nuova gestione di sopra */
                    /* @deprecated */
                    add_action( 'wpss_checkout_bottom_button', array( 'BNMExtendsSummaryOrder', 'buyWithCash' ) );
                }
            }
        }

    	public static function wp_head_checkoutScript() {
    		if ( is_singular( kWPSmartShopStorePagePostTypeKey ) ) {
    			global $post;
    			if ( $post->post_name == 'checkout' || $post->post_name == 'cassa' ) {
    				wp_enqueue_script( 'bnm-checkout', kBNMExtendsURI . 'js/checkout.js' );
                    /* @todo Questa riga può essere eliminata se la localizzazione viene già caricata */
    				wp_localize_script( 'bnm-checkout', 'bnmExtendsJavascriptLocalization', BNMExtendsEventPostType::scriptLocalization() );
    			}
    		}
    	}

        public static function body_class( $classes ) {

            global $post;

            if( $post && is_object( $post ) && isset( $post->post_name )) {
                $classes[] = ' bnm-' . $post->post_name;
            }

            return $classes;
        }

        public static function widget_categories_dropdown_args( $args ) {
            unset( $args['show_option_none'] );
            return $args;
        }

        public static function the_content_more_link( $more_text ) {
            $more_text = "[...]";
            return $more_text;
        }

        // -------------------------------------------------------------------------------------------------------------
        // WordPress Login
        // -------------------------------------------------------------------------------------------------------------

        /**
         * Questa viene usata per determinare se l'utente è la prima volta che si connette, così da poter fare un
         * redirect su una pagina di benvenuto.
         * Il conto dei login count viene invece fatto da wpXtreme
         *
         * @static
         * @param $user_login
         */
        public static function wp_login( $user_login ) {
            $user = get_user_by( 'login', $user_login );

            /* Controlla se è la prima volta che l'utente si logga */
            $count = intval( get_user_meta( $user->ID, 'bnm_user_login_count', true ) );
            if( empty( $count )) {
                $count == 0;
                $count++;
                update_user_meta( $user->ID, 'bnm_user_login_count', $count );
                if( $count == 1) {
                    wp_redirect( BNMExtends::pagePermalinkWithSlug( 'benvenuto' ) );
                }
            }

        }
        
        // -------------------------------------------------------------------------------------------------------------
        // WPDK hook
        // -------------------------------------------------------------------------------------------------------------

        public static function wpdk_login_wrong() {
            add_action( 'wp_footer', array( __CLASS__, 'wpdk_login_wrong_wp_footer' ) );
        }

        public static function wpdk_login_wrong_wp_footer() {
            ?>
        <script type="text/javascript">
            alert( '<?php _e( 'Wrong username or password. Please try again. WARNING! In August 2012, Blue Note has updated its website, and previous username and password are no longer valid. If by then you have not made a new registration, click ok, then click on Register and follow the procedures.', 'bnmextends' ) ?>' );
        </script>
        <?php
        }

        // -------------------------------------------------------------------------------------------------------------
        // Smart Shop integration
        // -------------------------------------------------------------------------------------------------------------

        public static function wpss_payment_gateway_id_user_order( $id_user_order ) {
            if ( isset( $_POST['id_user_order'] ) && !empty( $_POST['id_user_order'] ) ) {
                $id_user_order = absint( $_POST['id_user_order'] );
            }
            return $id_user_order;
        }

        public static function wpss_message_you_have_to_login( $message ) {
            return __( 'Log in upper right to purchase', 'bnmextends' );
        }

        public static function wpss_payment_gateway_did_order_insert( $values ) {
            /* Aggiorno lo stato dell'ordine come 'confirmed' */
            //WPXSmartShopOrders::orderConfirmed( $values['track_id'] );
        }

        public static function wpss_payment_gateway_payment_type( $paymentType ) {
            //if ( isset( $_POST['WPSS_CUSTOM_SHOP'] ) ) {
            if ( isset( $_POST['wpxss_cash'] ) ) {
                //$paymentType = WPSmartShopPaymentGateway::kCash;
                $paymentType = $_POST['wpxss_cash_values'];
            }
            return $paymentType;
        }

        public static function wpss_payment_gateway_name( $paymentGateway ) {
            //if ( isset( $_POST['WPSS_CUSTOM_SHOP'] ) ) {
            if ( isset( $_POST['wpxss_cash'] ) ) {
                $paymentGateway = ''; // Nessuno, pagato con cash
            }
            return $paymentGateway;
        }

        public static function wpss_product_membership_capabilities_list() {
            $result       = array();
            $capabilities = BNMExtendsUser::capabilities();
            foreach ( $capabilities as $key => $cap ) {
                $result[$key] = $cap['description'];
            }
            return $result;
        }

        public static function wpss_summary_order_rows( $rows ) {
            /* Elimino le righe nel Summary Order standard di Smart Shop che non vengono usate */
            unset($rows['coupon_order']);
            unset($rows['discount']);
            return $rows;
        }

        public static function wpss_ajax_action_product_card_reload( $id_product, $args ) {
            add_filter( 'wpss_cart_add_to_cart_button_label', array( __CLASS__, 'wpss_cart_add_to_cart_button_label'), 10, 3 );
        }

        public static function wpss_cart_add_to_cart_button_label( $label, $product, $id_variant ) {
            $id_product = $product->ID;
            $qty_cart   = WPXSmartShopSession::countProductWithID( $id_product );
            $price      =  WPXSmartShopCurrency::formatCurrency( WPXSmartShopProduct::price( $id_product, 1, '', $qty_cart ) );

            return sprintf( __( 'Purchase %s %s', 'bnmextends' ), $price, WPXSmartShopCurrency::currencySymbol());
        }

        public static function wpss_product_variant_label( $field, $key ) {
            $type = get_post_type();

            switch( $type ) {
                case kBNMExtendsEventPostTypeKey:
                    if ( $key == 'model' ) {
                        $field = ''; //__( 'Dinner', 'bnmextends' );
                    }
                    break;
                default:
                    if ( $key == 'model' ) {
                        $field = __( 'Size', 'bnmextends' );
                    }
                    break;
            }

            return $field;
        }

        public static function wpss_product_variant_localizable_value( $localizable_value, $id_product, $id_variant, $variant, $key ) {
            /* Vedi nelle defines per la localizzazione */
            return __( $localizable_value, 'bnmextends' );
        }

        public static function wpss_product_card_rows( $rows ) {

            $type = get_post_type();

            if ( $type == kBNMExtendsEventPostTypeKey || defined('DOING_AJAX') ) {

                if ( !in_array( 'base_price', $rows ) ) {
                    array_splice( $rows, 2, 0, 'base_price' );
                }
            }
            return $rows;
        }

        public static function wpss_product_card_div_base_price( $html, $id_product ) {

            $type = get_post_type();

            if ( $type == kBNMExtendsEventPostTypeKey || defined('DOING_AJAX') ) {

                $terms = get_the_terms( $id_product, kWPSmartShopProductTypeTaxonomyKey );

                if( $terms[0]->slug != 'biglietti-di-ingresso' ) {

                    if( defined( 'ICL_LANGUAGE_CODE') ) {
                        $id_term = icl_object_id( $terms[0]->term_id, kWPSmartShopProductTypeTaxonomyKey, true );
                        $tterm = get_term( $id_term, kWPSmartShopProductTypeTaxonomyKey );
                    } else {
                        $tterm = $terms[0];
                    }

                    $label = $tterm->name;
                    $html = <<< HTML
    <div data-id_product="{$id_product}" class="bnm-event-price-door-message">
        <span class="bnm-event-price-door-message">{$label}</span>
    </div>
HTML;
                } else {

                $label = __('Door price','bnmextends');
                $price = WPXSmartShopCurrency::currencyHTML( WPXSmartShopProduct::priceBase( $id_product ), 'bnm-price-door' );
                $html = <<< HTML
<div class="bnm-event-price-door-message">
    <span class="bnm-event-price-door-message">{$label}</span>
    {$price}
</div>
HTML;
                }
            }
            return $html;
        }

        public static function wpxss_product_card_title( $title ) {
            if( strlen( $title ) > 30 ) {
                $title = substr( $title, 0, 30 ) . '...';
            }
            return $title;
        }

        /**
         * Utilizzando per alterare la quantità dei biglietti di tipo brunch quando si scelgono le varianti. Queste
         * infatti decurtano quantità diverse in base alla selezione: 2 adulti, 2 adulti e un bambino e 2 adulti con 2
         * bambini.
         *
         * @todo Da finire
         *
         * @static
         *
         * @param $qty
         * @param $id_order
         * @param $id_product
         * @param $status
         * @param $count
         *
         * @return mixed
         */
        public static function wpxss_product_store_quantity_for_order( $qty, $id_order, $id_product, $status, $count ) {

            $qty = absint( $qty );

            if ( $count['id_variant'] == 'Cumulative' ) {
                if ( $count['model'] == BNMEXTENDS_2_ADULTS_KEY ) {
                    $qty = $qty * 2;
                } elseif ( $count['model'] == BNMEXTENDS_2_ADULTS_1_CHILD_KEY ) {
                    $qty = $qty * 3;
                } elseif ( $count['model'] == BNMEXTENDS_2_ADULTS_2_CHILDREN_KEY ) {
                    $qty = $qty * 4;
                }
            }

            return $qty;
        }


        public static function wpxss_cart_add_enabled( $enabled, $id_product ) {
            if ( is_user_logged_in() ) {

                $terms_by_name = WPSmartShopProductTypeTaxonomy::arrayTermsWithKeyName();

                /* Test italiano(inglese */
                if ( !isset( $terms_by_name['abbonamento under 26'] ) ) {
                    $translate = array(
                        'platinum' => 'platinum',
                        'club'     => 'club',
                    );
                } else {
                    $translate = array_combine( array_keys( $terms_by_name ), array_keys( $terms_by_name ) );
                }

                if (
                    has_term( $terms_by_name[$translate['platinum']], kWPSmartShopProductTypeTaxonomyKey, $id_product ) ||
                        has_term( $terms_by_name[$translate['club']], kWPSmartShopProductTypeTaxonomyKey, $id_product )
                ) {

                    $id_user  = get_current_user_id();
                    $user     = new WP_User( $id_user );
                    $key_role = key( $user->roles );

                    $blocks = array(
                        'bnm_role_5',
                        'bnm_role_6'
                    );

                    if ( in_array( $user->roles[$key_role], $blocks ) ) {
                        return false;
                    }
                }
            }
            return $enabled;
        }

        /**
         * Questo filtro permette di limitare gli acquisti per quantità legati alla sessione ovvero all'ordine che si
         * sta effettuando.
         *
         * @filter
         * @static
         *
         * @param int $qty Quantità
         * @param int $id_product ID Prodotto
         *
         * @return int|WP_Error
         * Restituisce un WP_Error per informare del limite, altrimenti restituisce l'intero $qty as it is
         */
        public static function wpss_summary_order_update_quantity( $qty, $id_product ) {

            /* Per adesso agisco solo su un determinato tipo di categoria prodotto */
            $terms = get_the_terms( $id_product, kWPSmartShopProductTypeTaxonomyKey );
            foreach ( $terms as $term ) {

                /* Biglietti di ingresso? */
                if ( $term->slug == 'biglietti-di-ingresso' && WPDKUser::hasCaps( array( 'bnm_cap_store_max_item' ) ) ) {
                    if ( $qty > 8 ) {
                        $qty     = 8;
                        $message = __( 'You can not get more 8 items', 'bnmextends' );
                        $warning = new WP_Error( 'wpss_status-alter_quantity', $message, $qty );
                        return $warning;
                    }
                }

                /* Blocco sulle membership */
                if ( $term->slug == 'club-membership-platinum-membership' || $term->slug == 'membership' ) {
                    if ( $qty > 1 ) {
                        $qty     = 1;
                        $message = __( 'You can not get more 1 item', 'bnmextends' );
                        $warning = new WP_Error( 'wpss_status-alter_quantity', $message, $qty );
                        return $warning;
                    }
                }

                /* Blocco under 26 */
                if ( $term->slug == 'abbonamentounder26' || $term->slug == 'under26-subscription-ticket' ) {
                    if ( $qty > 1 ) {
                        $qty     = 1;
                        $message = __( 'You can not get more 1 item', 'bnmextends' );
                        $warning = new WP_Error( 'wpss_status-alter_quantity', $message, $qty );
                        return $warning;
                    }
                }
            }
            return $qty;
        }

        public static function wpss_invoice_rows_summary_products( $rows ) {
            unset( $rows['coupon_order']);
            unset( $rows['discount']);
            unset( $rows['vat']);
            return $rows;
        }

        public static function wpss_invoice_columns_summary_products( $columns ) {
            unset( $columns['vat'] );
            return $columns;
        }

        public static function wpxss_stats_column_price_rule_online( $description, $price_rule ) {
            return 'Advance';
        }

        public static function wpxss_stats_column_price_rule( $description, $price_rule ) {

            if ( isset( self::$_discoundIDs[$price_rule] ) ) {
                return self::$_discoundIDs[$price_rule]['label'];
            }
            return $description;
        }

        /**
         * Questo hook serve per permettere all'utente operatore di aggiungere un prodotto nel carrello anche quando
         * la sua data di disponibilità dettata dal magazzino è scaduta.
         * L'utente operatore, infatti, può acquistare anche quando lo spettacolo è iniziato o comunque non è vincolato
         * alle due ore prima come per gli utenti normali.
         *
         * @static
         *
         * @param bool $false
         * @param int  $id_product
         */
        public static function wpxss_product_date_expired( $false, $id_product ) {
            /* Prima di tutto verifico che sono un utente operatore */
            if ( WPDKUser::hasCaps( array ( 'bnm_cap_offline' ) ) ) {

                /* Verifico che l'id prodotto sia un biglietto */
                $terms_by_name = WPSmartShopProductTypeTaxonomy::arrayTermsWithKeyName();
                if ( isset( $terms_by_name['biglietti di ingresso'] ) ) {
                    if ( !has_term( $terms_by_name['biglietti di ingresso'], kWPSmartShopProductTypeTaxonomyKey, $id_product ) ) {
                        return false;
                    }
                } else {
                    if ( !has_term( $terms_by_name['admission tickets'], kWPSmartShopProductTypeTaxonomyKey, $id_product ) ) {
                        return false;
                    }
                }

                /* Verifico che il prodotto non sia davvero esaurito, considerando anche il carrello */
                $qty_cart = WPXSmartShopSession::countProductWithID( $id_product );

                /* Magazzino */
                $warehouse = WPXSmartShopProduct::warehouse( $id_product );
                $qty_store = $warehouse['qty'];

                if ( wpdk_is_infinity( $qty_store ) ) {
                    $qty_store = $qty_cart + 1;
                }

                if ( ( $qty_store - $qty_cart ) > 0 ) {
                    return true;
                }
            }


            return $false;
        }

        /// Bypass coupon user owner
        public static function wpxss_coupon_user_owner_different( $true ) {
            /* Se l'utente corrente è un operatore box office */
            if ( WPDKUser::hasCaps( array( 'bnm_cap_offline' ) ) ) {
                return false;
            }
            return $true;
        }

        // -------------------------------------------------------------------------------------------------------------
        // Placeholders integration
        // -------------------------------------------------------------------------------------------------------------

        public static function wpph_reservation_edit_id_who( $sdf, $reservation ) {

            $user_email = '';
            if ( !empty( $reservation->id_who ) ) {
                $users      = get_users( array( 'include' => $reservation->id_who, ) );
                $user       = $users[0];
                $user_email = sprintf( '%s (%s)', $user->display_name, $user->user_email );
            }

//            $users_list = get_users();
//
//            if ( $users_list ) {
//                $users = array();
//                foreach ( $users_list as $user ) {
//                    $users[$user->ID] = sprintf( '%s (%s)', $user->display_name, $user->user_email );
//                }
//            }

            $result = array(
//                array(
//                    'type'    => WPDK_FORM_FIELD_TYPE_SELECT,
//                    'label'   => __( 'Reservation by', 'bnmextends' ),
//                    'name'    => 'id_who',
//                    'value'   => $reservation->id_who,
//                    'options' => $users
//                )
                array(
                    'type'    => WPDK_FORM_FIELD_TYPE_TEXT,
                    'name'    => 'user_email',
                    'data'    => array(
                        'autocomplete_action'   => 'wpdk_action_user_by',
                        'autocomplete_target'   => 'id_who'
                    ),
                    'size'    => 64,
                    'label'   => __( 'Reservation by', 'bnmextends' ),
                    'value'   => $user_email
                ),
                array(
                    'type'    => WPDK_FORM_FIELD_TYPE_HIDDEN,
                    'name'    => 'id_who',
                    'value'   => !is_null( $reservation ) ? $reservation->id_user : ''
                ),
            );
            return $result;
        }

        public static function wpph_reservation_list_table_columns( $columns ) {
            $columns['id_who'] = __( 'Booked by', 'bnmextends' );
            return $columns;
        }

        public static function wpph_reservation_list_table_column_output( $output, $column_name, $item ) {
            if ( $column_name == 'id_who' ) {
                $output = __( 'None', 'bnmextends' );
                if( !empty( $item['user_display_name'] )) {
                    //$output = sprintf( '<strong>%s</strong> (%s)', $item['user_display_name'], $item['user_email'] );
                    $output = sprintf( '%s %s', WPDKUser::gravatar( $item['user_id'], 16 ), $item['user_display_name'] );
                }
            }
            return $output;
        }

        public static function wpph_reservation_list_table_sql_extra_field( $fields ) {
            $fields[] = 'users.ID AS user_id';
            $fields[] = 'users.display_name AS user_display_name';
            $fields[] = 'users.user_email AS user_email';
            return $fields;
        }

        public static function wpph_reservation_list_table_sql_extra_join( $joins ) {
            global $wpdb;

            $table_user = $wpdb->users;
            $joins      = <<< SQL
    LEFT JOIN {$table_user} AS users ON users.ID = reservations.id_who
SQL;
            return $joins;
        }

        // -------------------------------------------------------------------------------------------------------------
        // WPML Integration
        // -------------------------------------------------------------------------------------------------------------

        /**
         * Restituisce il permalink di una pagina o post nella lingua corrente di WPML. Se WPML non è attivo ritorna il
         * permalink normale. Se la pagina o post nella lingua corrente non è presente, viene restituito il permalink della
         * pagina o post base.
         *
         * @static
         *
         * @param string $slug      Il post_name
         * @param string $post_type Questa per default è impostato a 'page', ma può essere 'post' o un custom post type
         *
         * @return string Permalink | NULL
         */
        public static function pagePermalinkWithSlug( $slug, $post_type = 'page' ) {
            global $wpdb;

            $page = get_page_by_path( $slug, OBJECT, $post_type );
            if ( is_null( $page ) ) {
                if ( function_exists( 'icl_object_id' ) ) {
                    $sql = <<< SQL
SELECT ID FROM {$wpdb->posts}
WHERE post_name = '{$slug}'
AND post_type = '{$post_type}'
AND post_status = 'publish'
SQL;
                    $id  = $wpdb->get_var( $sql );
                    $id  = icl_object_id( $id, $post_type, true );
                } else {

                    /* NULL */
                    return $page;
                }
            } else {
                $id = $page->ID;
            }

            $permalink = get_permalink( $id );

            return trailingslashit( $permalink );
        }
    }

    /* Let's dance */
    BNMExtends::init();
}

