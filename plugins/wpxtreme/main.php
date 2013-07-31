<?php
/// @cond private
/**
 * Plugin Name: wpXtreme
 * Plugin URI: http://wpxtre.me/
 * Description: Amazing WordPress Xtreme pack
 * Version: 1.0
 * Author: wpXtreme
 * Author URI: http://wpxtre.me
 * Text Domain: wp-smartshop
 * Domain Path: localization
 */
/// @endcond

/**
 * Welcome to **wpXtreme Plugin**
 *
 * @mainpage  Welcome
 * @author    wpXtreme, Inc.
 * @version   1.0
 * @copyright Copyright (c) 2012, wpXtreme, Inc.
 *
 * @section   introduction Introduction
 *            wpXtreme Plugin allow to enhanched your WordPress installation and give you a special access to wpx Plugin Store
 *
 * @todo      Verificare l'admin_body_class auto costruito da wpdk con quello sovrascritto
 * @todo      Renoame this file in wpxtreme.php, because contains the main master plugin class WPXSmartShop
 *
 */

/* Avoid directly access */
if ( !defined( 'ABSPATH' ) ) {
    exit;
}

/* Include WPDK */
require_once( 'wpdk/wpdk.php' );

/* Main core configuration. */
require_once( 'config.php' );

/// wpXtreme main Plugin Class
/**
 * @class       WPXtreme
 * @author      wpXtreme, Inc.
 * @copyright   Copyright (c) 2011-2012, wpXtreme, Inc.
 * @date        15/02/12
 * @version     1.0.0
 *
 * This is the main class of plugin. This class extends WPDKWordPressPlugin in order to make easy several WordPress
 * funtions.
 *
 */
class WPXtreme extends WPDKWordPressPlugin {

    static $settings;

    // -----------------------------------------------------------------------------------------------------------------
    // Constants values
    // -----------------------------------------------------------------------------------------------------------------

    function __construct() {
        parent::__construct( __FILE__ );

        /* Init the session */
        if ( !session_id() ) {
            add_action( 'init', 'session_start' );
        }

        /* Definisco una serie di costanti di comodotià. */
        $this->defines();

        /* Include */
        $this->includes();

        /* Internal plugins. */
        add_action( 'init', array( 'WPXtremeMaintenance', 'init' ) );

        /* Dope WordPress */
        add_action( 'init', array( 'WPDKUser', 'init' ), 1 );
        add_action( 'init', array( 'WPDKPost', 'init' ), 1 );

        /* Extending Custom post Type. */
        add_action( 'init', array( $this, 'registerCustomPostType'), 0);

        /* Put WPXtreme as first plugin in WordPress queue */
        add_action( 'activated_plugin', array( $this, 'activated_plugin' ), 10, 2 );
    }

    // -----------------------------------------------------------------------------------------------------------------
    // Includes
    // -----------------------------------------------------------------------------------------------------------------

    /// Includes
    private function includes() {
        /* Core */
        require_once( 'classes/core/wpxtreme-ajax.php' );
        require_once( 'classes/core/wpxtreme-api.php' );
        require_once( 'classes/core/wpxtreme-settings.php' );
        require_once( 'classes/core/wpxtreme-maintenance.php' );

        /* Custom Post Type. */
        require_once( 'classes/cpt/wpxtreme-mail-cpt.php' );

        /* Store */
        require_once( 'classes/store/wpxtreme-store.php' );
    }

    // -----------------------------------------------------------------------------------------------------------------
    // Methods to overwrite
    // -----------------------------------------------------------------------------------------------------------------

    /// Catch for ajax
    function ajax() {
        /* To overwrite. */
    }

    /// Catch for admin
    function admin() {
        require_once( 'classes/admin/wpxtreme-admin.php' );
        $admin = new WPXtremeAdmin( $this );
    }

    function theme() {
        /* To overwrite. */
        require_once( 'classes/frontend/wpxtreme-frontend.php' );
        $frontend = new WPXtremeFrontend( $this );
    }

    function activation() {
        /* To overwrite. */
    }

    function deactivation() {
        /* To overwrite. */
    }

    function init_options() {
        self::settings();
    }

    // -----------------------------------------------------------------------------------------------------------------
    // Defines Constants
    // -----------------------------------------------------------------------------------------------------------------

    public function defines() {
        require_once( 'defines.php' );
    }

    // -----------------------------------------------------------------------------------------------------------------
    // WordPress hook
    // -----------------------------------------------------------------------------------------------------------------

    public function activated_plugin( $plugin, $network_wide ) {

        if ( $plugin == $this->plugin_basename ) {

            /* Put me at the top */
            $active_plugins = get_option( 'active_plugins' );
            $position       = array_search( $plugin, $active_plugins );
            if ( $position > 0 ) {
                array_splice( $active_plugins, $position, 1 );
                array_unshift( $active_plugins, $plugin );
                update_option( 'active_plugins', $active_plugins );
            }

            remove_action( 'activated_plugin', array( $this, 'activated_plugin' ), 10, 2 );
        }
    }

    // -----------------------------------------------------------------------------------------------------------------
    // Register Custom Post Type
    // -----------------------------------------------------------------------------------------------------------------

    public function registerCustomPostType() {
        WPXtremeMailCustomPostType::registerPostType();
        flush_rewrite_rules();
    }

    // -----------------------------------------------------------------------------------------------------------------
    // Static
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * @static
     * @return WPXtremeSettings
     */
    public static function settings() {
        if ( !isset( self::$settings ) ) {
            self::$settings = new WPXtremeSettings();
        }
        return self::$settings;
    }

}

/* Let's dance */
$GLOBALS['wpxtreme'] = new WPXtreme();