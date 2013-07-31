<?php
/// @cond private
/**
 * Plugin Name: wpx Placeholders
 * Plugin URI: http://wpxtre.me
 * Description: Places reservation Plugin
 * Version: 1.0
 * Author: wpXtreme
 * Author URI: http://wpxtre.me
 * Text Domain: wpx-placeholders
 * Domain Path: localization
 */
/// @endcond

/**
 *
 * Welcome to **wpx Placeholders**
 *
 * @mainpage  Welcome
 * @author    wpXtreme, Inc.
 * @version   1.0
 * @copyright Copyright (c) 2012, wpXtreme, Inc.
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

if ( !class_exists( 'WPXPlaceholders' ) ) {

    /// wpx Placeholders Main Plugin Class
    /**
     * @class       WPXPlaceholders
     * @author      wpXtreme, Inc.
     * @copyright   Copyright (c) 2011-2012, wpXtreme, Inc.
     * @date        12/012
     * @version     1.0
     *
     * This is the main class of plugin. This class extends WPDKWordPressPlugin in order to make easy several WordPress
     * funtions.
     *
     */
    class WPXPlaceholders extends WPDKWordPressPlugin {

        static $settings;

        /// Construct
        function __construct() {
            parent::__construct( __FILE__ );

            /* Init the session */
            if ( !session_id() ) {
                add_action( 'init', 'session_start' );
            }

            /* Include */
            $this->includes();

            /* @todo Da eliminare */
            $this->defines();
        }


        // -----------------------------------------------------------------------------------------------------------------
        // Defines Constants
        // -----------------------------------------------------------------------------------------------------------------

        /// This is a comodity for global define shorthand
        function defines() {
            include_once( 'defines.php' );
        }

        // -----------------------------------------------------------------------------------------------------------------
        // Includes
        // -----------------------------------------------------------------------------------------------------------------

        /// Include all modules
        private function includes() {
            /* Core */
            require_once( 'classes/environment/wpxph-environments.php' );
            require_once( 'classes/places/wpxph-places.php' );
            require_once( 'classes/reservations/wpxph-reservations.php' );
        }

        // -----------------------------------------------------------------------------------------------------------------
        // WordPress activation hook
        // -----------------------------------------------------------------------------------------------------------------

        /// Hook when admin is loading
        function admin() {
            require_once( 'classes/admin/wpxph-admin.php' );
            $admin = new WPPlaceholdersAdmin( $this );
        }

        function theme() {
            require_once( 'classes/frontnend/wpxph-frontend.php' );
            $fontend = new WPXPlaceholdersFrontend( $this );
        }

        public function activation() {
            /* Register possible unistaller */

            /* Esegue un delta sulla struttura delle tabelle */
            ob_start(); // Necessario a causa dei warning emessi da dbDelta()

            /* Environment */
            if ( !class_exists( 'WPPlaceholdersEnvironments' ) ) {
                require_once( 'classes/environment/wpxph-environments.php' );
            }
            WPPlaceholdersEnvironments::updateTable();

            /* Places */
            if ( !class_exists( 'WPPlaceholdersPlaces' ) ) {
                require_once( 'classes/places/wpxph-places.php' );
            }
            WPPlaceholdersPlaces::updateTable();

            /* Placeholders */
            if ( !class_exists( 'WPPlaceholdersReservations' ) ) {
                require_once( 'classes/reservations/wpxph-reservations.php' );
            }
            WPPlaceholdersReservations::updateTable();

            ob_end_clean();
        }

        public function deactivation() {
            // none...
        }

        // -----------------------------------------------------------------------------------------------------------------
        // Static values
        // -----------------------------------------------------------------------------------------------------------------

        public function scriptLocalization() {
            $result = array(
                'ajaxURL'                           => self::url_ajax(),
            );
            return $result;
        }

    }

    /* Let's dance */
    $GLOBALS['wpx_placeholders'] = new WPXPlaceholders();

}