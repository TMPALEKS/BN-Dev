<?php
/**
 * @class              WPDKWordPressTheme
 *
 * @description        Classe base da estendere usata per gestire il tema di front end
 *
 * @package            WPDK
 * @subpackage         core
 * @author             =undo= <g.fazioli@undolog.com>, <g.fazioli@wpxtre.me>
 * @copyright          Copyright (c) 2012 wpXtreme, Inc.
 * @link               http://wpxtre.me
 * @created            02/06/12
 * @version            1.0.0
 *
 * @filename           wpdk-wordpress-theme
 *
 */

class WPDKWordPressTheme {

    var $plugin;

    /**
     * Init
     */
    function __construct( WPDKWordPressPlugin $plugin = null ) {
        $this->plugin = $plugin;

        /* Before init */
        add_action( 'after_setup_theme', array( $this, 'after_setup_theme' ) );

        add_action( 'wp', array( $this, 'wp' ) );
        add_action( 'wp_head', array( $this, 'wp_head' ) );
        add_action( 'wp_footer', array( $this, 'wp_footer' ) );

        /* Filtro sul body class. */
        add_filter( 'body_class', array( $this, 'body_class' ) );

        add_action( 'wp_enqueue_scripts', array( $this, 'wp_enqueue_scripts') );

        /* template-loader */
        add_action( 'template_redirect', array( $this, 'template_redirect' ) );
        add_filter( 'template_include', array( $this, 'template_include' ) );
    }

    // -----------------------------------------------------------------------------------------------------------------
    // WordPress Hook
    // -----------------------------------------------------------------------------------------------------------------

    function body_class( $classes ) {

        /* Se ho un puntatore ad un plugin inserisco il suo slug. */
        if( !is_null( $this->plugin ) ) {
            $classes[] = sprintf( ' %s-body', $this->plugin->slug );
        }
        return $classes;
    }

    function after_setup_theme() {
        /* To overwrite */
    }

    function template_redirect( ) {
        /* To overwrite */
    }

    function template_include( $template ) {
        /* To overwrite */
        return $template;
    }

    function wp() {
        /* To overwrite */
    }

    function wp_head() {
        /* To overwrite */
    }

    function wp_footer() {
        /* To overwrite */
    }

    function wp_enqueue_scripts() {
        /* To overwrite */
    }

}
