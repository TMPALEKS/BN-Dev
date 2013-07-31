<?php
/**
 * Bootstrap
 *
 * This file provide to boostrap with WPDK framework. If the WPDK framework is not found on standard path or in the
 * plugin's path, a warning message will be display.
 *
 * @author          =undo= <g.fazioli@undolog.com>, <g.fazioli@wpxtre.me>
 * @copyright       Copyright (c) 2012, wpXtreme, Inc.
 * @date            17/01/12
 * @version         1.0
 *
 */

/* Avoid directly access */
if ( !defined( 'ABSPATH' ) ) {
    exit;
}

/* Check if WPDK is embed or installed */

if ( !class_exists( 'WPDK' ) ) {
    if ( file_exists( ABSPATH . PLUGINDIR . '/wpxtreme/wpdk/wpdk.php' ) ) { // Search as shared
        require_once( ABSPATH . PLUGINDIR . '/wpxtreme/wpdk/wpdk.php' );
    }
}

if ( !class_exists( 'WPDK' ) ) {
    add_action( 'admin_notices', 'wpxss_admin_notices' );

    function wpxss_admin_notices() {
        ?>
    <div id="message" class="error">
        <h3>wpx SmartShop: WARNING!</h3>

        <p>You have to install <a href="#">WPDK (WordPress Developer Kit)</a></p>
    </div>
    <?php
        //wp_die('You have to install WPDK (WordPress Developer Kit)', 'WARNING!');
    }
}