<?php
/// @cond private
/**
 * Plugin Name: wpx SmartShop
 * Plugin URI: http://wpxtre.me
 * Description: Amazing e-commerce manager
 * Version: 1.0
 * Author: wpXtreme
 * Author URI: http://wpxtre.me
 * Text Domain: wpx-smartshop
 * Domain Path: localization
 */
/// @endcond

/**
 * Welcome to **wpx SmartShop**
 *
 * @mainpage Welcome
 * @author wpXtreme, Inc.
 * @version 1.0
 * @copyright Copyright (c) 2011, wpXtreme, Inc.
 *
 * @section introduction Introduction
 * wpx SmartShop is an amazing e-commerce plugin for WordPress neveer see before.
 *
 * @section getting_started Getting Started
 * wpx SmartShop has several static class for immediatly method access. In this release yu can find:
 *
 * * Pure static class
 * * Mixed static and instance classes
 *
 * The rule is that when a class is a object with a lot of instance, then this class is not static but you have to
 * create a new instance for use it.
 * However usually you can use `CLASS_NAME::method_name` for execute a function. In other words, the static class are
 * used for protect the context, as a namespace, and for organize the arguments of methods.
 *
 * @note This program is free software; you can redistribute it and/or modify it under the terms of the GNU
 * General Public License version 2, as published by the Free Software Foundation.  You may NOT assume
 * that you can use any other version of the GPL.
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without
 * even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * @todo Renoame this file in wpx-smartshop.php, because contains the main master plugin class WPXSmartShop
 *
 */

/* Avoid directly access */
if ( !defined( 'ABSPATH' ) ) {
    exit;
}

/* Check WPDK */
require_once('bootstrap.php');

/* Main core configuration. */
require_once( 'config.php' );

/// wpx SmartShop Main Plugin Class
/**
 * @class       WPXSmartShop
 * @author      wpXtreme, Inc.
 * @copyright   Copyright (c) 2011-2012, wpXtreme, Inc.
 * @date        15/02/12
 * @version     1.0.0
 *
 * This is the main class of plugin. This class extends WPDKWordPressPlugin in order to make easy several WordPress
 * funtions.
 *
 */
class WPXSmartShop extends WPDKWordPressPlugin {

    /// Alias static
    /**
     * @var WPSmartShopSettings
     */
    static $settings;

    /// Construct
    function __construct() {
        parent::__construct( __FILE__ );

        /* Init the session */
        if ( !session_id() ) {
            add_action( 'init', 'session_start' );
        }

        /* @todo Test */
        date_default_timezone_set('Europe/Rome');

        /* Include */
        $this->includes();

        /* Hook on Login */
        add_action( 'wp_login', array( $this, 'wp_login') );
        add_action( 'wp_logout', array( $this, 'wp_logout') );

        /* Register custom post type and taxonomies */
        add_action( 'init', array( $this, 'registerCustomPostType') );

        /* Register news image size */
        add_action( 'init', array( 'WPXSmartShopProduct', 'registerImageSizes' ) );

        /* Widget Init */
        add_action( 'widgets_init', array( $this, 'widgets_init') );

        /* @todo Da rivedere */
        $this->defines();
    }

    // -----------------------------------------------------------------------------------------------------------------
    // Defines constants
    // -----------------------------------------------------------------------------------------------------------------

    /// This is a comodity for global define shorthand
    function defines() {
        require_once( 'defines.php' );
    }

    // -----------------------------------------------------------------------------------------------------------------
    // Includes
    // -----------------------------------------------------------------------------------------------------------------

    /// Include all modules
    private function includes() {

        /* Core */
        require_once( 'classes/core/wpxss-permalink.php' );
        require_once( 'classes/core/wpxss-session.php' );
        require_once( 'classes/core/wpxss-transient.php' );
        require_once( 'classes/core/wpxss-shortcode.php' );
        require_once( 'classes/core/wpxss-users-picker.php' );
        require_once( 'classes/core/wpxss-wpml.php' );

        /* Store page */
        /* @deprecated */
        require_once( 'classes/WPSmartShopStorePagePostType.php' );

        /* Products */
        require_once( 'classes/products/wpxss-product.php' );
        require_once( 'classes/products/WPSmartShopProductCoupon.php' );
        require_once( 'classes/products/WPSmartShopProductMaker.php' );
        require_once( 'classes/products/WPSmartShopProductMembership.php' );
        require_once( 'classes/products/WPSmartShopProductPicker.php' );
        require_once( 'classes/products/WPSmartShopProductPostType.php' );
        require_once( 'classes/products/WPSmartShopProductTagTaxonomy.php' );
        require_once( 'classes/products/WPSmartShopProductTypeTaxonomy.php' );

        /* Orders */
        require_once( 'classes/orders/wpxss-orders.php' );
        require_once( 'classes/orders/wpxss-shopping-cart.php' );
        require_once( 'classes/orders/wpxss-summary-order.php' );

        /* Stats */
        require_once( 'classes/stats/wpxss-stats.php' );

        /* Coupons */
        require_once( 'classes/coupons/wpxss-coupons.php' );
        require_once( 'classes/coupons/wpxss-coupons-maker.php' );

        /* Memberships */
        require_once( 'classes/memberships/wpxss-memberships.php' );

        /* Carriers */
        require_once( 'classes/carriers/wpxss-carriers.php' );

        /* Shipments */
        require_once( 'classes/shipments/WPSmartShopShipments.php' );
        require_once( 'classes/shipments/WPSmartShopShippingCountries.php' );

        /* Showcase */
        require_once( 'classes/showcase/WPSmartShopShowcasePostType.php' );
        require_once( 'classes/showcase/WPSmartShopShowcase.php' );

        /* Invoice */
        require_once( 'classes/invoices/wpxss-invoice.php' );

        /* Settings */
        require_once( 'classes/settings/WPSmartShopSettings.php' );

        /* Utility */
        require_once( 'classes/utility/wpxss-currency.php' );
        require_once( 'classes/utility/wpxss-measures.php' );

        /* Payment Gateways */
        require_once( 'classes/payment_gateways/WPSmartShopPaymentGateway.php' );
    }

    // -----------------------------------------------------------------------------------------------------------------
    // Plugin Loaded
    // -----------------------------------------------------------------------------------------------------------------

    /// Hook when ajax is loading
    function ajax() {
        require_once( 'classes/core/wpxss-ajax.php' );

        /* Re-register short code. */
        WPXSmartShopShortCode::registerShortcodes();
    }

    /// Hook when admin is loading
    function admin() {
        require_once( 'classes/admin/wpxss-admin.php' );
        $admin = new WPXSmartShopAdmin( $this );
    }

    /// Hook when the frontend theme is loading
    function theme() {

        require_once( 'classes/frontend/wpxss-frontend.php' );
        $frontend = new WPXSmartShopFrontend( $this );

        /* Re-register short code. */
        WPXSmartShopShortCode::registerShortcodes();
    }

    /// Hook when the plugin is activate - only first time
    function activation() {
        /* Register possible unistaller */

        /* Esegue un delta sulla struttura delle tabelle */
        ob_start(); // Necessario a causa dei warning emessi da dbDelta()

        /* Orders */
        if ( !class_exists( 'WPXSmartShopOrders' ) ) {
            require_once( 'classes/orders/wpxss-orders.php' );
        }
        WPXSmartShopOrders::updateTable();

        /* Stats */
        if ( !class_exists( 'WPXSmartShopStats' ) ) {
            require_once( 'classes/stats/wpxss-stats.php' );
        }
        WPXSmartShopStats::updateTable();

        /* Coupons */
        if ( !class_exists( 'WPXSmartShopCoupons' ) ) {
            require_once( 'classes/coupons/wpxss-coupons.php' );
        }
        WPXSmartShopCoupons::updateTable();

        /* Memberships */
        if ( !class_exists( 'WPXSmartShopMemberships' ) ) {
            require_once( 'classes/memberships/wpxss-memberships.php' );
        }
        WPXSmartShopMemberships::updateTable();

        /* Carrires */
        if ( !class_exists( 'WPXSmartShopCarriers' ) ) {
            require_once( 'classes/carriers/wpxss-carriers.php' );
        }
        WPXSmartShopCarriers::updateTable();

        /* Shipping Countries */
        if ( !class_exists( 'WPSmartShopShippingCountries' ) ) {
            require_once( 'classes/shipments/WPSmartShopShippingCountries.php' );
        }
        WPSmartShopShippingCountries::updateTable();
        WPSmartShopShippingCountries::loadDataTable();

        /* Shipments */
        if ( !class_exists( 'WPSmartShopShipments' ) ) {
            require_once( 'classes/shipments/WPSmartShopShipments.php' );
        }
        WPSmartShopShipments::updateTable();

        ob_end_clean();
    }

    /// Hook when the plugin is deactivated
    function deactivation() {
        flush_rewrite_rules();
    }

    /// Init the option
    /**
     * Return the settings instance
     * @retval WPSmartShopSettings
     */
    function init_options() {
        return self::settings();
    }

    // -----------------------------------------------------------------------------------------------------------------
    // Register Custom Post Type and Taxonomies
    // -----------------------------------------------------------------------------------------------------------------

    /// Register wpx SmartShop custom post type
    public function registerCustomPostType() {
        WPSmartShopProductPostType::registerPostType();
        WPSmartShopProductTypeTaxonomy::registerTaxonomy();
        WPSmartShopStorePagePostType::registerPostType();
        WPSmartShopProductTagTaxonomy::registerTaxonomy();
        WPSmartShopShowcasePostType::registerPostType();
        flush_rewrite_rules();
    }

    // -----------------------------------------------------------------------------------------------------------------
    // Widgets Init
    // -----------------------------------------------------------------------------------------------------------------

    /// Init the widgets
    public function widgets_init() {

        /* Init the Cart Widget */
        if ( !class_exists( 'WPXSmartShopShoppingCartWidget' ) ) {
            require_once( 'classes/widgets/wpxss-widget-shopping-cart.php' );
        }
        register_widget( 'WPXSmartShopShoppingCartWidget' );
    }

    // -----------------------------------------------------------------------------------------------------------------
    // WordPress Login
    // -----------------------------------------------------------------------------------------------------------------

    /// Hook when an user login
    public function wp_login( $user_login ) {
        $user = get_user_by( 'login', $user_login );

        /* Ricostruisce il carello se ordini in pending */
        //WPXSmartShopSession::sessionFromPendingOrderWithUser( $user->ID );
        /*
         * Deprecated: la ricostruzione del carrello è stata deprecata in data 01/10/12
         */
        /* Controlla e repristina lo stato di membership di questa utenza */
        WPXSmartShopMemberships::flush( $user->ID );
    }

    /// Hook when an user logout
    public function wp_logout() {
        WPXSmartShopSession::init();
    }

    // -----------------------------------------------------------------------------------------------------------------
    // Static values
    // -----------------------------------------------------------------------------------------------------------------

    /// Return the array for javascript localization
    public function scriptLocalization() {
        $result = array(
            'ajaxURL'                           => self::url_ajax(),

            // Widget Cart - Front
            'confirmDeleteItemWidgetCart'       => __('Are you sure to remove this product?', WPXSMARTSHOP_TEXTDOMAIN ),
            'confirmClearWidgetCart'            => __('Are you sure to remove all your products?', WPXSMARTSHOP_TEXTDOMAIN ),

            'timeOnlyTitle'                     => __('Choose Time', WPXSMARTSHOP_TEXTDOMAIN ),
            'timeText'                          => __('Time', WPXSMARTSHOP_TEXTDOMAIN ),
            'hourText'                          => __('Hour', WPXSMARTSHOP_TEXTDOMAIN ),
            'minuteText'                        => __('Minute', WPXSMARTSHOP_TEXTDOMAIN ),
            'secondText'                        => __('Seconds', WPXSMARTSHOP_TEXTDOMAIN ),
            'currentText'                       => __('Now', WPXSMARTSHOP_TEXTDOMAIN ),
            'dayNamesMin'                       => __('Su,Mo,Tu,We,Th,Fr,Sa', WPXSMARTSHOP_TEXTDOMAIN ),
            'monthNames'                        => __('January,February,March,April,May,June,July,August,September,October,November,December', WPXSMARTSHOP_TEXTDOMAIN ),
            'dateFormat'                        => __('mm/dd/yy', WPXSMARTSHOP_TEXTDOMAIN ),

            // Common
            'closeText'                         => __('Close', WPXSMARTSHOP_TEXTDOMAIN ),
            'Ok'                                => __('Ok', WPXSMARTSHOP_TEXTDOMAIN ),
            'Yes'                               => __('Yes', WPXSMARTSHOP_TEXTDOMAIN ),
            'No'                                => __('No', WPXSMARTSHOP_TEXTDOMAIN ),
            'Cancel'                            => __('Cancel', WPXSMARTSHOP_TEXTDOMAIN ),

            'logo16x16'                         => WPXSMARTSHOP_URL_CSS . 'images/logo-16x16.png',
            'logo34x34'                         => WPXSMARTSHOP_URL_CSS . 'images/logo-48x48.png',

            // Product Picker jQuery Dialog
            'productPickerTitle'                => __('Select a product', WPXSMARTSHOP_TEXTDOMAIN ),
            'productTypesPickerTitle'           => __('Select a product type', WPXSMARTSHOP_TEXTDOMAIN ),
            'userPickerTitle'                   => __('Select an user', WPXSMARTSHOP_TEXTDOMAIN ),

            'currency_decimal_point'        => WPXSMARTSHOP_CURRENCY_DECIMAL_POINT,
            'currency_thousands_separator'  => WPXSMARTSHOP_CURRENCY_THOUSANDS_SEPARATOR

        );
        return $result;
    }

    // -----------------------------------------------------------------------------------------------------------------
    // Static instance
    // -----------------------------------------------------------------------------------------------------------------

    /// Init the options
    /**
     * @static
     * @retval WPSmartShopSettings
     */
    static function settings() {
        if ( !isset( self::$settings ) ) {
            self::$settings = new WPSmartShopSettings();
        }
        return self::$settings;
    }

}

/* Let's dance */
$GLOBALS['wpx_smartshop'] = new WPXSmartShop();
