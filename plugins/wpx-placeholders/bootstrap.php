<?php
/**
 * Bootstrap
 *
 * This file provide to boostrap with WPDK framework. If the WPDK framework is not found on standard path or in the
 * plugin's path, a warning message is display.
 *
 * @author     wpXtreme, Inc.
 * @copyright  Copyright (c) 2012 wpXtreme, Inc.
 * @date       17/01/12
 * @version    1.1.2
 *
 */

// Avoid direct access
if( !defined( 'ABSPATH' ) ) {
  exit;
}

// Comodity constants and global variables uniq in all environment of wpXtreme
if( !defined( 'WPX_MISSING_WPDK' ) ) {
  define( 'WPX_MISSING_WPDK', '1' );
  define( 'WPX_INVALID_WPDK_VERSION', '2' );
  define( 'WPDK_CORE_KERNEL', ABSPATH . PLUGINDIR . "/wpxtreme/wpdk/wpdk.php" );
  $GLOBALS['aWpdkGlobalWpXtremePluginQueue'] = array();
}

//---------------------------------------------------------------------------------------------
// Functions definition
//---------------------------------------------------------------------------------------------

// define the function wpx_admin_notices only once, because the behaviour is equal to every wpXtreme plugin
if( FALSE == function_exists( 'wpx_admin_notices' )) :

/**
 *  This function shows a custom message in admin area. Engaged when WPDK is _NOT_ present in the system, or
 *  when there are some inconvenient in bootstrap phase of a wpXtreme plugin.
 *
 * @note
 *    WPX_MISSING_WPDK means that WPDK core kernel is phisically nonexistent in the whole WP system.
 *    WPX_INVALID_WPDK_VERSION means that WPDK version is lower than the minimum requirements for this plugin.
 *    The plugin auto-deactivation included in this function should not be here, but this is the quickest and
 *    the simplest way to execute it.
 *
 */
function wpx_admin_notices() {

  global $aWpdkGlobalWpXtremePluginQueue;

  // for every single element of the internal queue
  foreach( $aWpdkGlobalWpXtremePluginQueue as $aItem ) {

    if( $aItem['TypeOfNoticeForAdminArea'] == WPX_MISSING_WPDK ) {
      ?>
      <div id="message" class="error">
        <h3>wpx <?php echo $aItem['PluginName']; ?>: WARNING!</h3>

        <p>This plugin has been deactivated. You have to install <a href="http://wpxtre.me">the wpXtreme free Plugin</a> in
           order to
           have this plugin active and fully enabled.</p>
      </div>
      <?php
    }

    if( $aItem['TypeOfNoticeForAdminArea'] == WPX_INVALID_WPDK_VERSION ) {
      ?>
      <div id="message" class="error">
        <h3>wpx <?php echo $aItem['PluginName']; ?>: WARNING!</h3>

        <p>This plugin has been deactivated. You have to update <a href="http://wpxtre.me">wpXtreme free Plugin</a> to the
           latest version!
        </p>
      </div>
      <?php
    }

    // auto-deactivate myself: something goes wrong
    deactivate_plugins( $aItem['PluginMainFileName'] );

  } // foreach internal queue

  // clear the internal queue. WARNING: this must be the last thing to do here!
  $aWpdkGlobalWpXtremePluginQueue = array();

}

endif; // function_exists(wpx_admin_notices)

// define the function wpx_version_check only once, because the behaviour is equal to every wpXtreme plugin
if( FALSE == function_exists( 'wpx_version_check' )) :

/**
 *  Check environment versions related to this plugin.
 *
 * @param $sWPDKNeeded string WPDK version needed for the wpXtreme plugin invoking this function.
 *
 * @retval 0 ( zero ) if all versions are considered OK for this plugin, -1 in case of an error.
 *
 */
function wpx_version_check( $sWPDKNeeded ) {

  // if WPDK needed for this plugin is greater than actual WPDK in this system
  if( TRUE == version_compare( $sWPDKNeeded , WPDK_VERSION, '>' ) ) {

    // WPDK Kernel has to be updated in order to use this plugin
    return -1;

  }

  // All versions is OK!
  return 0;

}

endif; // function_exists(wpx_version_check)

// define the function wpdk_bootstrap only once, because the behaviour is equal to every wpXtreme plugin
if( FALSE == function_exists( 'wpdk_bootstrap' )) :

/**
 *  Perform the WPDK related bootstrap of the plugin invoking this function.
 *
 * @retval 0 ( zero ) if bootstrap has been correctly completed, -1 in case of an error.
 *
 */
function wpdk_bootstrap() {

  global $sWPDKGlobalConstPrefix, $aWpdkGlobalWpXtremePluginQueue;

  // Commodity internal variables
  $aDefinedConstants    = get_defined_constants();
  $sPluginName          = $aDefinedConstants[$sWPDKGlobalConstPrefix . 'PLUGINNAME'];
  $sPluginMainFileName  = $aDefinedConstants[$sWPDKGlobalConstPrefix . 'PLUGINMAINFILENAME'];
  $sPluginWPDKRequested = $aDefinedConstants[$sWPDKGlobalConstPrefix . 'WPDKMINREQUESTED'];

  // FIRST CHECK: is phisically there WPDK core kernel?
  // WARNING: class WPDK may be already defined by another wpXtreme plugin!
  if( !class_exists( 'WPDK' ) ) {

    if( file_exists( WPDK_CORE_KERNEL ) ) {

      // include WPDK kernel
      require_once( WPDK_CORE_KERNEL );

    }
    else {

      // alert the WP environment saving plugin bootstrap data in a specific dedicated queue

      $aWpdkGlobalWpXtremePluginQueue[] = array( 'PluginName' => $sPluginName,
                                                 'TypeOfNoticeForAdminArea' => WPX_MISSING_WPDK,
                                                 'PluginMainFileName' => $sPluginMainFileName );
      add_action( 'admin_notices', 'wpx_admin_notices' );

      // WPDK Kernel does _NOT_ exist in the system
      return -1;

    }

  }

  // SECOND CHECK: is all various versions considered OK?
  $iStatus = wpx_version_check($sPluginWPDKRequested);
  if( $iStatus == -1 ) {

    // alert the WP environment saving plugin bootstrap data in a specific dedicated queue
    $aWpdkGlobalWpXtremePluginQueue[] = array( 'PluginName' => $sPluginName,
                                               'TypeOfNoticeForAdminArea' => WPX_INVALID_WPDK_VERSION,
                                               'PluginMainFileName' => $sPluginMainFileName );
    add_action( 'admin_notices', 'wpx_admin_notices' );

    // Error in checking WPDK version - plugin will be deactivated
    return -1;

  }

  // All bootstrap conditions are correctly verified; I can regularly continue.
  return 0;

}

endif;

//---------------------------------------------------------------------------------------------
// End of functions definition
//---------------------------------------------------------------------------------------------

// Execute the real WPDK related bootstrap of this plugin, and return status
$iStatus=wpdk_bootstrap();
return $iStatus;