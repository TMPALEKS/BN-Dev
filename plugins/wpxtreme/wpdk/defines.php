<?php
/**
 * @description        Defines
 *
 * @package            WPDK (WordPress Development Kit)
 * @author             =undo= <g.fazioli@undolog.com>, <g.fazioli@wpxtre.me>
 * @copyright          Copyright (c) 2012 wpXtreme, Inc.
 * @link               http://wpxtre.me
 * @created            07/06/12
 * @version            1.0.0
 *
 * @filename           defines.php
 *
 */

// -----------------------------------------------------------------------------------------------------------------
// General
// -----------------------------------------------------------------------------------------------------------------
define( 'WPDK_VERSION', '1.0' );

/*
 * Path unix: /var/
 */

/* Path unix della cartella wpdk. */
define( 'WPDK_DIR', trailingslashit( plugin_dir_path( __FILE__ ) ) );

/* Path unix della cartella classes wpdk. */
define( 'WPDK_DIR_CLASS', WPDK_DIR . 'classes/' );

/*
 * URI
 */

/* Set constant path: plugin URL. */
define( 'WPDK_URI', trailingslashit( plugin_dir_url( __FILE__ ) ) );

/* Set constant path: assets */
define( 'WPDK_URI_ASSETS', WPDK_URI . 'assets/' );
define( 'WPDK_URI_CSS', WPDK_URI_ASSETS . 'css/' );
define( 'WPDK_URI_JAVASCRIPT', WPDK_URI_ASSETS . 'js/' );

/*
 * Localization
 */

define( 'WPDK_TEXTDOMAIN', 'wpdk' );
define( 'WPDK_TEXTDOMAIN_PATH', 'wpxtreme/' . trailingslashit( basename( dirname( __FILE__ ) )) . 'localization' );

/*
 * Utility
 */
define( 'WPDK_CR', "\r" );
define( 'WPDK_LF', "\n" );
define( 'WPDK_CRLF', WPDK_CR . WPDK_LF );