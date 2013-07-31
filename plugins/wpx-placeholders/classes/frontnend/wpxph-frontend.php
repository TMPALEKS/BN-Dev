<?php
/**
 * @class              WPXPlaceholdersFrontend
 *
 * @description        Gestione Fontned
 *
 * @package            wpx Placeholders
 * @subpackage         frontend
 * @author             =undo= <g.fazioli@undolog.com>, <g.fazioli@wpxtre.me>
 * @copyright          Copyright (c) 2012 wpXtreme, Inc.
 * @link               http://wpxtre.me
 * @created            04/04/12
 * @version            1.0.0
 *
 * @filename           wpxph-frontnend
 *
 */

class WPXPlaceholdersFrontend extends WPDKWordPressTheme {

    function __construct( WPXPlaceholders $plugin ) {
        parent::__construct( $plugin );
    }

    // -----------------------------------------------------------------------------------------------------------------
    // WordPress Hook
    // -----------------------------------------------------------------------------------------------------------------

    function wp_enqueue_scripts() {
        //wp_enqueue_style( 'wp-smartshop-frontend-css', WPXSMARTSHOP_URL . 'css/wp-smartshop-frontend.css', array(), WPXSMARTSHOP_VERSION );

        wp_enqueue_script( 'wpxph-frontend', WPXPLACEHOLDERS_URL_JAVASCRIPT . 'wpxph-frontend.js', array( 'jquery' ), WPXPLACEHOLDERS_VERSION, true );
        wp_localize_script( 'wpxph-frontend', 'wpPlaceholdersJavascriptLocalization', WPXPlaceholders::scriptLocalization() );
    }

}
