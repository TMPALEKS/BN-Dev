<?php
/**
 * Configuration file
 *
 * @package         WPXtreme
 * @author          =undo= <g.fazioli@wpxtre.me>
 * @copyright       Copyright © 2010-2012 wpXtreme, Inc.
 *
 * @note            This file will be overwrite in future update. Please edit with care and only for debug.
 *
 */

// -----------------------------------------------------------------------------------------------------------------
// API Gateway
// -----------------------------------------------------------------------------------------------------------------

define( 'WPXTREME_API_GATEWAY', 'http://dev.wpxtre.me/api/' );
define( 'WPXTREME_API_USER_AGENT', 'wpXtreme/1.0' );
define( 'WPXTREME_API_TIMEOUT', 45 );

// -----------------------------------------------------------------------------------------------------------------
// Secure Key
// -----------------------------------------------------------------------------------------------------------------

/* @todo Questa cosa sarebbe da spostare sul server */
define( 'WPXTREME_SECURE_KEY_TIMEOUT', 60 * 5 );
