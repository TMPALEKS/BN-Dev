<?php
/**
 * @description        Utilizzata durante la visualizzazione del frontend
 *
 * @package            wpXtreme
 * @subpackage         WPXtremeFrontend
 * @author             =undo= <g.fazioli@undolog.com>, <g.fazioli@wpxtre.me>
 * @copyright          Copyright (c) 2012 wpXtreme, Inc.
 * @link               http://wpxtre.me
 * @created            02/06/12
 * @version            1.0.0
 *
 * @filename           wpxtreme-frontend
 *
 */

class WPXtremeFrontend extends WPDKWordPressTheme {

    /**
     * Init
     */
    function __construct( WPXtreme $plugin ) {
        parent::__construct( $plugin );
    }



    function wp_enqueue_scripts() {
        /* @todo Da sostituire quando wpdk.css saranno riscritti bene */
        wp_enqueue_style( 'wpxm-admin', $this->plugin->url_css . 'admin.css' );

        /* @todo Solo se richesta esegue una serie di migliorie a WordPress */
        wp_enqueue_style( 'wpxm-admin-enhanced', $this->plugin->url_css . 'admin-enhanced.css' );
    }

}
