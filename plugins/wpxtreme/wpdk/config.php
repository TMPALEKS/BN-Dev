<?php
/**
 * @description     Configuration file
 *
 * @package         WPDK (WordPress Development Kit)
 * @author          =undo= <g.fazioli@undolog.com>, <g.fazioli@wpxtre.me>
 * @copyright       Copyright (c) 2012 wpXtreme
 * @link            http://wpxtre.me
 *
 * @filename        config.php
 *
 * @note            This file will be overwrite in future update. Please edit with care and only for debug.
 *
 */

// -----------------------------------------------------------------------------------------------------------------
// Debug with watch dog
// -----------------------------------------------------------------------------------------------------------------

/* Debug log file */
define( 'WPDK_LOG_FILE', dirname( __FILE__ ) . '/log.php' );

define( 'WPDK_WATCHDOG_DEBUG', true );
define( 'WPDK_WATCHDOG_DEBUG_ON_FILE', true );
define( 'WPDK_WATCHDOG_DEBUG_ON_DATABASE', true );
define( 'WPDK_WATCHDOG_DEBUG_ON_TRIGGER_ERROR', false );


// -----------------------------------------------------------------------------------------------------------------
// Transient Cache
// -----------------------------------------------------------------------------------------------------------------
define( 'WPDK_CACHE_POST', true );
define( 'WPDK_CACHE_RECORD', true );
