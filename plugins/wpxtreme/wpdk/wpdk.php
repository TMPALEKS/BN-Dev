<?php
/**
 * WordPress Development Kit™
 *
 * This program is free software; you can redistribute it and/or modify it under the terms of the GNU
 * General Public License version 2, as published by the Free Software Foundation.  You may NOT assume
 * that you can use any other version of the GPL.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without
 * even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * @package   WPDK (WordPress Development Kit)
 * @version   1.0.0
 * @author    Giovambattista Fazioli <g.fazioli@wpxtre.me>
 * @copyright Copyright (c) 2012, Saidmade, srl
 * @link      http://www.wpxtre.me/
 * @link      http://wpxtre.me
 * @license   http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 *
 */

/* Avoid directly access */
if ( !defined( 'ABSPATH' ) ) {
    exit;
}

if ( !class_exists( 'WPDK' ) ) {

    require_once( 'config.php' );

    class WPDK {

        public static function init() {

            self::defines();
            self::includes();

            /* Load the translation of WPDK */
            add_action( 'init', array( __CLASS__, 'load_plugin_textdomain') );

            /* Loading Script & style for backend */
            add_action( 'admin_head', array( __CLASS__, 'admin_head') );

            /* Loading script & style for frontend */
            add_action( 'wp_head', array( __CLASS__, 'wp_head' ) );

        }

        // -------------------------------------------------------------------------------------------------------------
        // Defines Constants
        // -------------------------------------------------------------------------------------------------------------

        public static function defines() {
            require_once( 'defines.php' );
        }

        // -------------------------------------------------------------------------------------------------------------
        // Includes
        // -------------------------------------------------------------------------------------------------------------

        public static function includes() {

            /* Core */
            //require_once( 'classes/core/wpdk-object.php' );
            require_once( 'classes/core/wpdk-functions.php' );
            require_once( 'classes/core/wpdk-settings.php' );
            require_once( 'classes/core/wpdk-update.php' );
            require_once( 'classes/core/wpdk-watchdog.php' );
            require_once( 'classes/core/wpdk-wordpress.php' );
            require_once( 'classes/core/wpdk-wordpress-plugin.php' );
            require_once( 'classes/core/wpdk-wordpress-admin.php' );
            require_once( 'classes/core/wpdk-wordpress-theme.php' );
            require_once( 'classes/core/wpdk-shortcode.php' );
            require_once( 'classes/core/wpdk-ajax.php' );

            /* Database */
            require_once( 'classes/database/wpdk-db-table.php' );

            /* WordPress & common Helper */
            require_once( 'classes/helper/wpdk-array-helper.php' );
            require_once( 'classes/helper/wpdk-crypt-helper.php' );
            require_once( 'classes/helper/wpdk-datetime-helper.php' );
            require_once( 'classes/helper/wpdk-listtable-helper.php' );
            require_once( 'classes/helper/wpdk-math-helper.php' );
            require_once( 'classes/helper/wpdk-menu-helper.php' );
            require_once( 'classes/helper/wpdk-post-helper.php' );
            require_once( 'classes/helper/wpdk-postmeta-helper.php' );
            require_once( 'classes/helper/wpdk-settings-view-helper.php' );
            require_once( 'classes/helper/wpdk-ui-helper.php' );
            require_once( 'classes/helper/wpdk-user-helper.php' );
            require_once( 'classes/helper/wpdk-filesystem-helper.php' );

            /* UI */
            require_once( 'classes/ui/wpdk-dynamic-table.php' );
            require_once( 'classes/ui/wpdk-jquery.php' );
            require_once( 'classes/ui/wpdk-tableview.php' );
            require_once( 'classes/ui/wpdk-pointer.php' );

            /* Extra libs */
            require_once( 'libs/tcpdf/tcpdf.php' );

            // --------------------------------------------------------------

            /* @deprecated */
            require_once( 'classes/WPDKCRUD.php' );
            require_once( 'classes/WPDKForm.php' );

            /* Test */
            //require_once( 'classes/ui/_WPDKForm.php' );

        }

        // -------------------------------------------------------------------------------------------------------------
        // WordPress Hooks
        // -------------------------------------------------------------------------------------------------------------

        function load_plugin_textdomain() {
            load_plugin_textdomain( WPDK_TEXTDOMAIN, false, WPDK_TEXTDOMAIN_PATH );
        }

        /* @todo Per ora gli stili e gli scriot coincidono */
        function wp_head() {
            /* Gli shortcode sono per il frontend. */
            WPDKShortcode::registerShortcodes();

            /* Stili per il frontend relativi a wpdk */
            self::_admin_styles();

            /* Scripts per il funzionamento di wpdk lato frontend */
            self::admin_scripts();

        }

        function admin_head() {
            /* Stili per il backend relativi a wpdk */
            self::_admin_styles();

            /* Scripts per il funzionamento di wpdk lato backend */
            self::admin_scripts();

        }

        // -------------------------------------------------------------------------------------------------------------
        // Private
        // -------------------------------------------------------------------------------------------------------------

        private function _admin_styles() {
            $deps = array(
                'thickbox'
            );

            wp_enqueue_style( 'wpdk-jquery-ui', WPDK_URI_CSS . 'jquery-ui/jquery-ui.custom.css', $deps, WPDK_VERSION );
            wp_enqueue_style( 'wpdk-style', WPDK_URI_CSS . 'wpdk.css', $deps, WPDK_VERSION );

            //wp_enqueue_style( 'wpdk-tooltip', WPDK_URI_JAVASCRIPT . 'tiptip/tipTip.css', null, WPDK_VERSION );
        }

        private function admin_scripts() {
            /* Registro tutte le chiavi/percorso degli script che andrò ad utilizzare */
            $deps = array(
                'jquery',
                'jquery-ui-core',
                'jquery-ui-tabs',
                'jquery-ui-dialog',
                'jquery-ui-datepicker',
                'jquery-ui-autocomplete',
                'jquery-ui-slider',
                'jquery-ui-sortable',
                'jquery-ui-draggable',
                'jquery-ui-droppable',
                'jquery-ui-resizable',
                'thickbox'
            );

            wp_enqueue_script( 'wpdk-jquery-cookie', WPDK_URI_JAVASCRIPT . 'jquery.cookie.js', 'jquery', WPDK_VERSION, true );

            // Own
            wp_enqueue_script( 'wpdk-jquery-ui-timepicker', WPDK_URI_JAVASCRIPT . 'timepicker/jquery.timepicker.js', $deps, WPDK_VERSION, true );
            wp_enqueue_script( 'wpdk-jquery-validation', WPDK_URI_JAVASCRIPT . 'validate/jquery.validate.js', array( 'jquery' ), WPDK_VERSION, true );
            wp_enqueue_script( 'wpdk-jquery-validation-additional-method', WPDK_URI_JAVASCRIPT . 'validate/additional-methods.js', array( 'jquery-validation' ), WPDK_VERSION, true );
            wp_enqueue_script( 'wpdk-screenfull', WPDK_URI_JAVASCRIPT . 'screenfull.js', array(), WPDK_VERSION, true );
            wp_enqueue_script( 'wpdk-scroller', WPDK_URI_JAVASCRIPT . 'jquery.scroller.js', array(), WPDK_VERSION, true );

            /* Per adesso includo tutto bootstrap.js, anche se sto usando solo i tooltip. */
            wp_enqueue_script( 'wpdk-bootstrap', WPDK_URI_JAVASCRIPT . 'bootstrap.min.js', array(), WPDK_VERSION, true );

            /* Main wpdk. */
            wp_enqueue_script( 'wpdk-script', WPDK_URI_JAVASCRIPT . 'wpdk.js', $deps, WPDK_VERSION, true );

            /* Localize wpdk_i18n*/
            wp_localize_script( 'wpdk-script', 'wpdk_i18n', self::scriptLocalization() );
        }

        // -------------------------------------------------------------------------------------------------------------
        // Static values
        // -------------------------------------------------------------------------------------------------------------

        /**
         * Localizza le stringhe per Javascript
         *
         * @package    WordPress Development Kit
         * @subpackage WPDK
         * @since      1.0.0
         *
         * @static
         * @return array
         */
        public static function scriptLocalization() {
            // @todo Sostituire il dominio di localizzazione wp-smartshop conwpdk (da fare)
            $result = array(
                'ajaxURL'                     => WPDKWordPressPlugin::url_ajax(),

                'messageUnLockField'          => __( "Please confirm before unlock this form field.\nDo you want unlock this form field?", WPDK_TEXTDOMAIN ),

                'timeOnlyTitle'               => __( 'Choose Time', WPDK_TEXTDOMAIN ),
                'timeText'                    => __( 'Time', WPDK_TEXTDOMAIN ),
                'hourText'                    => __( 'Hour', WPDK_TEXTDOMAIN ),
                'minuteText'                  => __( 'Minute', WPDK_TEXTDOMAIN ),
                'secondText'                  => __( 'Seconds', WPDK_TEXTDOMAIN ),
                'currentText'                 => __( 'Now', WPDK_TEXTDOMAIN ),
                'dayNamesMin'                 => __( 'Su,Mo,Tu,We,Th,Fr,Sa', WPDK_TEXTDOMAIN ),
                'monthNames'                  => __( 'January,February,March,April,May,June,July,August,September,October,November,December', WPDK_TEXTDOMAIN ),
                'monthNamesShort'             => __( 'Jan,Feb,Mar,Apr,May,Jun,Jul,Aug,Sep,Oct,Nov,Dec', WPDK_TEXTDOMAIN ),
                'closeText'                   => __( 'Close', WPDK_TEXTDOMAIN ),
                'dateFormat'                  => __( 'mm/dd/yy', WPDK_TEXTDOMAIN ),
                'timeFormat'                  => __( 'hh:mm', WPDK_TEXTDOMAIN ),

            );
            return $result;
        }

        /**
         * Da implementare in tutte le classe, sono loro stesse ad aggiungere nelle code scripts e styles ciò che gli serve
         *
         * @package    WordPress Development Kit
         * @subpackage WPDK
         * @since      1.0.0
         *
         * @deprecated
         *
         * @static
         *
         */
        public static function enqueueStyles() {

            _deprecated_function( __CLASS__ . '::' . __FUNCTION__, '1.0', 'admin_head()' );
        }

        /**
         * Da implementare in tutte le classe, sono loro stesse ad aggiungere nelle code scripts e styles ciò che gli serve
         *
         * @package    WordPress Development Kit
         * @subpackage WPDK
         * @since      1.0.0
         *
         * @deprecated
         *
         * @static
         *
         */
        public static function enqueueScripts() {
            _deprecated_function( __CLASS__ . '::' . __FUNCTION__, '1.0', 'admin_head()' );
        }

    }

    /* Let's dance */
    WPDK::init();
}