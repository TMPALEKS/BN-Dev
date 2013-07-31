<?php
/**
 * @class              WPXSmartShopAdmin
 * @description        Class for Manage Admin (back-end)
 *
 * @package            wpx SmartShop
 * @subpackage         admin
 * @author             =undo= <g.fazioli@wpxtre.me>
 * @copyright          Copyright (C) 2012 wpXtreme, Inc.
 * @version            1.0.0
 *
 */

class WPXSmartShopAdmin extends WPDKWordPressAdmin {

    /// Construct
    function __construct( WPXSmartShop $plugin ) {
        parent::__construct( $plugin );

        /* Plugin List */
        add_action('plugin_action_links_' . $this->plugin->plugin_basename, array( $this, 'plugin_action_links' ), 10, 4);
        add_filter('plugin_row_meta', array( $this, 'plugin_row_meta' ), 10, 2);

        /* Loading Script & style for backend */
        add_action( 'admin_head', array( $this, 'admin_head') );

        /* Dashboard */
        add_action('wp_dashboard_setup', array( $this, 'wp_dashboard_setup' ));

        /* Product Picker: Add product meta box in post type settings */
        add_action('add_meta_boxes', array( $this, 'add_meta_boxes' ));

        /* Trap special Get parameter */
        add_action( 'admin_init', array( $this, 'admin_init') );

        /* Screen options */
        add_filter( 'set-screen-option', array( $this, 'set_screen_option' ), 10, 3 );

        /* @todo Capire se lasciare qui. */
        require_once( $this->plugin->path_classes . 'helper/wpxss-help.php' );
        require_once( $this->plugin->path_classes . 'helper/wpxss-pointer.php' );

    }

    // -----------------------------------------------------------------------------------------------------------------
    // WordPress Hooks
    // -----------------------------------------------------------------------------------------------------------------

    /// Hook when admin is init
    function admin_init() {

        /* Sfrutto questa posizione privilegiata per eseguire tutta una serie di operazioni speciali quali download e
        stampe, in quanto ho bisogno di eludere tutto l'html del backend
        */

        /* Stats: Export CSV */
        if ( isset( $_GET['export_stats_csv'] ) ) {
            WPXSmartShopStats::downalodCSV();
            exit;
        }

        /* Orders: Export CSV */
        if ( isset( $_GET['export_orders_csv'] ) ) {
            WPXSmartShopOrders::downalodCSV();
            exit;
        }
    }

    /// Hook when menu is ready to add
    /**
     * Typically, you will use wp_enqueue_script() hooked to an early action that occurs before any content is sent to
     * the browser, when administrator is loaded
     *
     * @static
     */
    public function admin_menu() {

        /* We use Pointer. */
        $this->pointer = new WPXSmartShopPointer();

        /* Icona */
        $icon_menu = $this->plugin->url_images . 'logo-16x16.png';

        /* @todo Mostra eventuali varie notifiche - ordini pending, etc... */
        //$count = WPXCleanFixModules::countWarning();
        $count = 0;
        /* Creo un badged da mettere nel menu store in caso ci siano dei plugin da aggiornare. */
        $badged = WPDKUI::badged( $count, 'wpxss-badged' );

        /* Main menu */
        $main_menu_key = 'wpxss-main-menu';
        $menu_main = add_menu_page( 'SmartShop', 'SmartShop' . $badged, kWPSmartShopUserCapability, $main_menu_key, array( $this, 'menu_settings'), $icon_menu, 100 );
        $this->menus['menu_main'] = $menu_main = add_submenu_page( $main_menu_key, __( 'wpx SmartShop Settings', WPXSMARTSHOP_TEXTDOMAIN ), __( 'Settings', WPXSMARTSHOP_TEXTDOMAIN ), kWPSmartShopUserCapability, $main_menu_key, array( $this, 'menu_settings') );
        //add_action( 'admin_head-' . $menu_main, array( $this, 'admin_head_menu_main' ) );
        add_action( 'load-' . $menu_main, array( $this, 'load_menu_settings' ) );

        /* Orders */
        $orders = add_submenu_page( $main_menu_key, __( 'wpx SmartShop Orders', WPXSMARTSHOP_TEXTDOMAIN ), __( 'Orders', WPXSMARTSHOP_TEXTDOMAIN ), kWPSmartShopUserCapability, 'wpxss_menu_orders', array( $this, 'menu_orders') );
        //add_action( 'admin_head-' . $orderss, array( $this, 'admin_head_menu_main' ) ); // Per adesso le stesse
        add_action( 'load-' . $orders, array( $this, 'load_menu_orders' ) );

        /* Statistiche */
        $stats = add_submenu_page( $main_menu_key, __( 'wpx SmartShop Stats', WPXSMARTSHOP_TEXTDOMAIN ), __( 'Stats', WPXSMARTSHOP_TEXTDOMAIN ), kWPSmartShopUserCapability, 'wpxss_menu_stats', array( $this, 'menu_stats') );
        //add_action( 'admin_head-' . $orderss, array( $this, 'admin_head_menu_main' ) ); // Per adesso le stesse
        add_action( 'load-' . $stats, array( $this, 'load_menu_stats' ) );

        /* Coupons */
        $coupons = add_submenu_page( $main_menu_key, __( 'wpx SmartShop Coupons', WPXSMARTSHOP_TEXTDOMAIN ), __( 'Coupons', WPXSMARTSHOP_TEXTDOMAIN ), kWPSmartShopUserCapability, 'wpxss_menu_coupons', array( $this, 'menu_coupons') );
        //add_action( 'admin_head-' . $orderss, array( $this, 'admin_head_menu_main' ) ); // Per adesso le stesse
        add_action( 'load-' . $coupons, array( $this, 'load_menu_coupons' ) );

        /* Memberships */
        $memberships = add_submenu_page( $main_menu_key, __( 'wpx SmartShop Memberships', WPXSMARTSHOP_TEXTDOMAIN ), __( 'Memberships', WPXSMARTSHOP_TEXTDOMAIN ), kWPSmartShopUserCapability, 'wpxss_menu_memberships', array( $this, 'menu_memberships') );
        //add_action( 'admin_head-' . $orderss, array( $this, 'admin_head_menu_main' ) ); // Per adesso le stesse

        /* Payment Gateways */
        $payment_gateways = add_submenu_page( $main_menu_key, __( 'wpx SmartShop Payment Gateways', WPXSMARTSHOP_TEXTDOMAIN ), __( 'Payment Gateways', WPXSMARTSHOP_TEXTDOMAIN ), kWPSmartShopUserCapability, 'wpxss_menu_payment_gateways', array( $this, 'menu_payment_gateways') );
        //add_action( 'admin_head-' . $orderss, array( $this, 'admin_head_menu_main' ) ); // Per adesso le stesse

        /* Carriers */
        $carriers = add_submenu_page( $main_menu_key, __( 'wpx SmartShop Carriers', WPXSMARTSHOP_TEXTDOMAIN ), __( 'Carriers', WPXSMARTSHOP_TEXTDOMAIN ), kWPSmartShopUserCapability, 'wpxss_menu_carriers', array( $this, 'menu_carriers') );
        //add_action( 'admin_head-' . $orderss, array( $this, 'admin_head_menu_main' ) ); // Per adesso le stesse

        /* Shipping Countries */
        $shipping_countries = add_submenu_page( $main_menu_key, __( 'wpx SmartShop Shipping Countries', WPXSMARTSHOP_TEXTDOMAIN ), __( 'Shipping Countries', WPXSMARTSHOP_TEXTDOMAIN ), kWPSmartShopUserCapability, 'wpxss_menu_shipping_countries', array( $this, 'menu_shipping_countries') );
        //add_action( 'admin_head-' . $orderss, array( $this, 'admin_head_menu_main' ) ); // Per adesso le stesse

        /* Shipments */
        $shipments = add_submenu_page( $main_menu_key, __( 'wpx SmartShop Shipments', WPXSMARTSHOP_TEXTDOMAIN ), __( 'Shipments', WPXSMARTSHOP_TEXTDOMAIN ), kWPSmartShopUserCapability, 'wpxss_menu_shipments', array( $this, 'menu_shipments') );
        //add_action( 'admin_head-' . $orderss, array( $this, 'admin_head_menu_main' ) ); // Per adesso le stesse
        add_action( 'load-' . $shipments, array( $this, 'load_menu_shipments' ) );

        /* Credits */
        $credits = add_submenu_page( $main_menu_key, __( 'wpx SmartShop Credits', WPXSMARTSHOP_TEXTDOMAIN ), __( 'Credits', WPXSMARTSHOP_TEXTDOMAIN ), kWPSmartShopUserCapability, 'wpxss_menu_credits', array( $this, 'menu_credits') );
        //add_action( 'admin_head-' . $orderss, array( $this, 'admin_head_menu_main' ) ); // Per adesso le stesse

        if ( defined( 'WPXSMARTSHOP_UNIT_TEST' ) && WPXSMARTSHOP_UNIT_TEST ) {
            /* Unit test */
            $unit_test = add_submenu_page( $main_menu_key, __( 'wpx SmartShop Unit Test', WPXSMARTSHOP_TEXTDOMAIN ), __( 'Unit test', WPXSMARTSHOP_TEXTDOMAIN ), 'read', 'wpxss_menu_unit_test', array( $this, 'menu_unit_test' ) );
        }

        /* Showcase menu extra */
        $show_case = add_submenu_page( kWPSmartShopShowcaseTypeMenuKey, __( 'wpx SmartShop Showcase Settings', WPXSMARTSHOP_TEXTDOMAIN ), __( 'Settings', WPXSMARTSHOP_TEXTDOMAIN ), kWPSmartShopUserCapability, 'wpxss_menu_showcase_settings', array( $this, 'menu_showcase_settings') );
//
//
//   /* Showcase */
//            'showcaseMenuItemSettings' => array(
//                'parent_slug'        => kWPSmartShopShowcaseTypeMenuKey,
//                'page_title'         => __( 'Settings', WPXSMARTSHOP_TEXTDOMAIN ),
//                'menu_title'         => __( 'Settings', WPXSMARTSHOP_TEXTDOMAIN ),
//                'capability'         => kWPSmartShopUserCapability,
//                'admin_head'         => array( $this, 'admin_head' ),
//                'callback'           => array( __CLASS__, 'showcaseMenuSettings' ),
//            )

    }

    /// Hook when the header of admin page is ready
    public function admin_head() {

        $this->body_classes['wpdk-body']        = true;
        $this->body_classes['wpss-cpt-product'] = true;

        /* Registro tutte le chiavi/percorso degli script che andrò ad utilizzare */
        $deps = array(
            'jquery',
            'jquery-ui-core',
            'jquery-ui-tabs',
            'jquery-ui-datepicker',
            'jquery-ui-slider',
            'jquery-ui-timepicker'
        );
        wp_deregister_style('jquery-ui');

        wp_enqueue_style( 'wpss-style', $this->plugin->url_css . 'admin.css' );
        wp_enqueue_script( 'wpss-script', $this->plugin->url_javascript . 'wp-smartshop.js', array( 'jquery' ), $this->plugin->version, true );
        wp_localize_script( 'wpss-script', 'wpSmartShopJavascriptLocalization', WPXSmartShop::scriptLocalization() );
    }

    /// Hook when the dashboard is ready
    /**
     * @todo Da fare con le ultime info utili, tipo: ultimi ordini, ultimi prodotto aggiunti, etc... da sviluppare
     *
     * @static
     */
    public function wp_dashboard_setup() {
        if ( current_user_can( kWPSmartShopUserCapability ) ) {
            if( !class_exists('WPXSmartShopDashboards')) {
                require_once( WPXSMARTSHOP_PATH_CLASSES . 'dashboards/wpxss-dashboards.php' );
                WPXSmartShopDashboards::init();
            }
        }
    }

    /// Hook for uninstall plugin
    /**
     * This method should be delete all options and table on database.
     *
     * @todo Da fare completamente e decidere come e quando implementarla
     *
     * @static
     */
    public static function plugin_uninstall() {}

    // -----------------------------------------------------------------------------------------------------------------
    // Plugin page Table List integration
    // -----------------------------------------------------------------------------------------------------------------

    /// Hook for adding row in WordPress plugin page
    /**
     * Aggiunge un link nella riga che identifica questo Plugin nella schermata con l'elenco dei Plugin nel backend di
     * WordPrsss.
     *
     * @static
     *
     * @param array $links
     *
     * @retval array
     */
    public function plugin_action_links( $links ) {
        $result = '<a href="index.php?page=wp-smartshop">' . __( 'Settings' ) . '</a>';
        array_unshift( $links, $result );
        return $links;
    }

    /// Hook for adding row in WordPress plugin page
    /**
     * Aggiunge un link nella riga che identifica questo Plugin nella schermata con l'elenco dei Plugin nel backend di
     * WordPrsss.
     *
     * @static
     *
     * @param array $links
     * @param string $file
     *
     * @retval array
     */
    public function plugin_row_meta( $links, $file ) {
        if ( $file == $this->plugin->plugin_basename ) {
            $links[] = '<strong style="color:#fa0">' . __( 'For more info and plugins visit', WPXSMARTSHOP_TEXTDOMAIN ) .
                ' <a href="http://wpxtre.me">wpXtreme</a></strong>';
        }
        return $links;
    }

    // -----------------------------------------------------------------------------------------------------------------
    // Product Picker
    // -----------------------------------------------------------------------------------------------------------------

    /// Hook for custom meta box
    /**
     * Add Product (picker) Meta Boxes to Post Type choose by user - see settings
     *
     * @retval void
     */
    public function add_meta_boxes() {
        global $typenow;

        /* Solo se WPML è off o siamo nella lingua di default */
        if ( WPXSmartShopWPML::isDefaultLanguage() ) {
            $settings = WPXSmartShop::settings()->wp_integration();
            if ( !empty( $settings['post_types'] ) ) {
                foreach ( $settings['post_types'] as $post_type ) {
                    if ( $typenow == $post_type ) {
                        WPSmartShopProductPicker::registerMCEButtons();
                    }
                }
            }
        }
    }

    // -----------------------------------------------------------------------------------------------------------------
    // Screen options
    // -----------------------------------------------------------------------------------------------------------------

    /// Settings
    function set_screen_option( $status, $option, $value ) {
        $options = array(
            'orders_per_page',
            'stats_per_page',
            'coupons_per_page'
        );
        if ( in_array( $option, $options ) ) {
            return $value;
        }
    }

    // -----------------------------------------------------------------------------------------------------------------
    // Menu items View Controller
    // -----------------------------------------------------------------------------------------------------------------

    /// Display Settiings
    function menu_settings() {
        if ( !class_exists( 'WPSmartShopSettingsViewController' ) ) {
            require_once( WPXSMARTSHOP_PATH_CLASSES . 'settings/WPSmartShopSettingsViewController.php' );
        }
        WPSmartShopSettingsViewController::display();
    }

    /// Settings loading
    function load_menu_settings() {
        $screen = get_current_screen();
        $screen->add_help_tab( array(
                                    'title'    => 'Help',
                                    'id'       => 'help_tab',
                                    'content'  => '<p>This is my content.</p>',
                                    'callback' => false
                               ) );
        $screen->set_help_sidebar( '<p>This is my help sidebar content.</p>' );
    }

    /// Display Orders
    function menu_orders() {
        if(!class_exists('WPXSmartShopOrdersViewController')) {
            require_once( WPXSMARTSHOP_PATH_CLASSES . 'orders/wpxss-orders-viewcontroller.php' );
        }
        WPXSmartShopOrdersViewController::listTableView();
    }

    /// Orders page is loading
    function load_menu_orders() {

        /* Screen options */
        global $wpxss_stats_list_table;
        $args = array(
            'label'   => __( 'Items per page', WPXSMARTSHOP_TEXTDOMAIN ),
            'default' => 10,
            'option'  => 'orders_per_page'
        );
        add_screen_option( 'per_page', $args );

        if ( !class_exists( 'WPXSmartShopOrdersListTable' ) ) {
            require_once( WPXSMARTSHOP_PATH_CLASSES . 'orders/wpxss-orders-listtable.php' );
        }
        $wpxss_stats_list_table = new WPXSmartShopOrdersListTable();

        /* Orders: print */
        if ( isset( $_GET['print_orders'] ) ) {
            if ( !class_exists( 'WPXSmartShopOrdersViewController' ) ) {
                require_once( WPXSMARTSHOP_PATH_CLASSES . 'orders/wpxss-orders-viewcontroller.php' );
            }
            WPXSmartShopOrdersViewController::printing();
            exit;
        }

        /* Help & Pointer */
        $screen = get_current_screen();
        $screen->add_help_tab( WPXSmartShopHelp::orders_what_is_a_order() );
        $screen->add_help_tab( WPXSmartShopHelp::orders_manage() );

        $screen->set_help_sidebar( WPXSmartShopHelp::sidebar() );

        $this->pointer->registerPointer( array( $this->pointer, 'orders_welcome' ) );
    }

    /// Display Stats
    function menu_stats() {
        if ( !class_exists( 'WPXSmartShopStatsViewController' ) ) {
            require_once( WPXSMARTSHOP_PATH_CLASSES . 'stats/wpxss-stats-viewcontroller.php' );
        }
        WPXSmartShopStatsViewController::listTableView();
    }

    /// Stats page is loading
    function load_menu_stats() {

        /* Screen options */
        global $wpxss_stats_list_table;
        $args = array(
            'label'   => __( 'Items per page', WPXSMARTSHOP_TEXTDOMAIN ),
            'default' => 10,
            'option'  => 'stats_per_page'
        );
        add_screen_option( 'per_page', $args );

        if ( !class_exists( 'WPXSmartShopStatsListTable' ) ) {
            require_once( WPXSMARTSHOP_PATH_CLASSES . 'stats/wpxss-stats-listtable.php' );
        }
        $wpxss_stats_list_table = new WPXSmartShopStatsListTable();

        /* Stats: print */
        if ( isset( $_GET['print_stats'] ) ) {
            if ( !class_exists( 'WPXSmartShopStatsViewController' ) ) {
                require_once( WPXSMARTSHOP_PATH_CLASSES . 'stats/wpxss-stats-viewcontroller.php' );
            }
            WPXSmartShopStatsViewController::printing();
            exit;
        }

        /* @todo For stats
        $screen = get_current_screen();
        $screen->add_help_tab( WPXSmartShopHelp::orders_what_is_a_order() );
        $screen->add_help_tab( WPXSmartShopHelp::orders_manage() );

        $screen->set_help_sidebar( WPXSmartShopHelp::sidebar() );

        $this->pointer->registerPointer( array( $this->pointer, 'orders_welcome' ) );
        */

    }

    /// Display Coupons
    function menu_coupons() {
        if ( !class_exists( 'WPXSmartShopCouponsViewController' ) ) {
            require_once( WPXSMARTSHOP_PATH_CLASSES . 'coupons/wpxss-coupons-viewcontroller.php' );
        }
        WPXSmartShopCouponsViewController::listTableView();
    }

    /// Coupons page is loading
    function load_menu_coupons() {
        /* Screen options */
        global $wpxss_coupons_list_table;
        $args = array(
            'label'   => __( 'Items per page', WPXSMARTSHOP_TEXTDOMAIN ),
            'default' => 10,
            'option'  => 'coupons_per_page'
        );
        add_screen_option( 'per_page', $args );

        if ( !class_exists( 'WPXSmartShopCouponsListTable' ) ) {
            require_once( WPXSMARTSHOP_PATH_CLASSES . 'coupons/wpxss-coupons-listtable.php' );
        }
        $wpxss_coupons_list_table = new WPXSmartShopCouponsListTable();

        /* @todo For stats
        $screen = get_current_screen();
        $screen->add_help_tab( WPXSmartShopHelp::orders_what_is_a_order() );
        $screen->add_help_tab( WPXSmartShopHelp::orders_manage() );

        $screen->set_help_sidebar( WPXSmartShopHelp::sidebar() );

        $this->pointer->registerPointer( array( $this->pointer, 'orders_welcome' ) );
        */

    }

    /// Display Payment Gateway
    function menu_payment_gateways() {
        if(!class_exists('WPSmartShopPaymentGatewayViewController')) {
            require_once( WPXSMARTSHOP_PATH_CLASSES . 'payment_gateways/WPSmartShopPaymentGatewayViewController.php' );
        }
        WPSmartShopPaymentGatewayViewController::view();
    }

    /// Display Carriers
    function menu_carriers() {
        WPXSmartShopCarriersViewController::listTableView();
    }

    /// Display Shipping Country
    function menu_shipping_countries() {
        WPSmartShopShippingCountriesViewController::listTableView();
    }

    /// Display Shipments
    function menu_shipments() {
        if ( !class_exists( 'WPSmartShopShipmentsViewController' ) ) {
            require_once ( WPXSMARTSHOP_PATH_CLASSES . 'shipments/WPSmartShopShipmentsViewController.php' );
        }
        WPSmartShopShipmentsViewController::listTableView();
    }

    /// Shipments page is loading
    function load_menu_shipments() {
        /* Screen options */
        global $wpxss_shipments_list_table;
        $args = array(
            'label'   => __( 'Items per page', WPXSMARTSHOP_TEXTDOMAIN ),
            'default' => 10,
            'option'  => 'shipments_per_page'
        );
        add_screen_option( 'per_page', $args );

        if ( !class_exists( 'WPSmartShopShipmentsListTable' ) ) {
            require_once( WPXSMARTSHOP_PATH_CLASSES . 'shipments/WPSmartShopShipmentsListTable.php' );
        }
        $wpxss_shipments_list_table = new WPSmartShopShipmentsListTable();
    }

    /// Display Memberships
    function menu_memberships() {
        if ( !class_exists( 'WPXSmartShopMembershipsViewController' ) ) {
            require_once( WPXSMARTSHOP_PATH_CLASSES . 'memberships/WPXSmartShopMembershipsViewController.php' );
        }
        WPXSmartShopMembershipsViewController::listTableView();
    }

    // -----------------------------------------------------------------------------------------------------------------
    // Showcase custom post type
    // -----------------------------------------------------------------------------------------------------------------

    /// Display Showcase
    public static function menu_showcase_settings() {
        if ( !class_exists( 'WPSmartShopShowcaseViewController' ) ) {
            require_once( WPXSMARTSHOP_PATH_CLASSES . 'showcase/WPSmartShopShowcaseViewController.php' );
        }
        WPSmartShopShowcaseViewController::display();
    }

    // -----------------------------------------------------------------------------------------------------------------
    // Menu item Unit Test
    // -----------------------------------------------------------------------------------------------------------------

    /// Test
    function menu_unit_test() {
        include( WPXSMARTSHOP_PATH_CLASSES . 'unit_test.php' );
    }


} // end of class