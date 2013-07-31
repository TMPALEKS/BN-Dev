<?php
/**
 * Configuration file
 *
 * @package          WPXPlaceholders
 * @author           =undo= <g.fazioli@wpxtre.me>
 * @copyright        Copyright (c) 2012 wpXtreme, Inc.
 *
 * @note             Please edit with very care, or not at all, the section about bootstrap.php.
 *
 */

// ---------------------------------------------------------------------------------------------------------------------
// BEGIN SECTION - Constants used by bootstrap.php
// ---------------------------------------------------------------------------------------------------------------------

global $sWPDKGlobalConstPrefix;

// uniq wpXtreme constant prefix for bootstrap - EDIT VERY CAREFULLY OR NOT AT ALL
$sWPDKGlobalConstPrefix = 'WPX_wpxph_PREFIX';

// Plugin name used in the error view of admin_notices WP action - EDIT VERY CAREFULLY OR NOT AT ALL
define( $sWPDKGlobalConstPrefix . 'PLUGINNAME' , 'wpx PlaceHolders' );

// WPDK minimal requirements for this plugin - change as your needs
define( $sWPDKGlobalConstPrefix . 'WPDKMINREQUESTED' , '1.0' );

// Plugin main file name - IMPORTANT!!!! Path must be there! - EDIT VERY CAREFULLY OR NOT AT ALL
define( $sWPDKGlobalConstPrefix . 'PLUGINMAINFILENAME' , dirname( __FILE__ ) . '/' . 'wpx-placeholders.php' );

// ---------------------------------------------------------------------------------------------------------------------
// END SECTION - Constants used by bootstrap.php
// ---------------------------------------------------------------------------------------------------------------------