<?php
/**
 * Bootstrap
 *
 * This file provide to boostrap with WPDK framework. If the WPDK framework is not found on standard path or in the
 * plugin's path, a warning message is display.
 *
 * @package         WPDK (WordPress Development Kit)
 * @subpackage      bootstrap
 * @author          =undo= <g.fazioli@wpxtre.me>
 * @copyright       Copyright (c) 2012 wpXtreme, Inc.
 * @created         17/01/12
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
    add_action( 'admin_notices', 'bnm_extends_admin_notices' );

    function bnm_extends_admin_notices() {
        ?>
    <div id="message" class="error">
        <h3>BNMExtends: WARNING!</h3>

        <p>You have to install <a href="#">WPDK (WordPress Developer Kit)</a></p>
    </div>
    <?php
        //wp_die('You have to install WPDK (WordPress Developer Kit)', 'WARNING!');
    }
}