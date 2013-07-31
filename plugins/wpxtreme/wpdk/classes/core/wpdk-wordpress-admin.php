<?php
/**
 * @description        Classe base da estendere per la gestione dell'amministrazione backend di WordPress
 *
 * @package            WPDK
 * @subpackage         WPDKWordPressAdmin
 * @author             =undo= <g.fazioli@undolog.com>, <g.fazioli@wpxtre.me>
 * @copyright          Copyright (c) 2012 wpXtreme, Inc.
 * @link               http://wpxtre.me
 * @created            28/05/12
 * @version            1.0.0
 *
 * @filename           wpdk-wordpress-admin
 *
 */

class WPDKWordPressAdmin {

    var $plugin;

    /**
     * @var WPDKPointer
     */
    var $pointer;

    /**
     * Permette di aggiungere classi al body dell'amministrazione
     *
     * @var array
     */
    var $body_classes;

    /**
     * Init
     */
    function __construct( WPDKWordPressPlugin $plugin ) {
        $this->plugin = $plugin;

        /* Admin page is loaded */
        add_action('admin_menu', array( $this, 'admin_menu' ));

        /* Register this plugin in body. */
        add_filter( 'admin_body_class', array( $this, '_admin_body_class') );
    }

    // -----------------------------------------------------------------------------------------------------------------
    // WordPress Hook
    // -----------------------------------------------------------------------------------------------------------------

    function _admin_body_class( $classes ) {
        if ( !empty( $this->body_classes ) ) {
            $keys = array_keys( $this->body_classes );
            $classes .= ' ' . join( ' ', $keys );
        }
        return $classes;
    }

    // -----------------------------------------------------------------------------------------------------------------
    // Methods to overwrite
    // -----------------------------------------------------------------------------------------------------------------

    function admin_menu() {
        /* To overwrite. */
    }


}
