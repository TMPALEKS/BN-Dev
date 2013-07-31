<?php
/**
 * wpXstore View Controller
 *
 * @package            WP Xtreme
 * @subpackage         WPXtremeStoreViewController
 * @author             =undo= <g.fazioli@undolog.com>, <g.fazioli@wpxtre.me>
 * @copyright          Copyright (c) 2012 wpXtreme, Inc.
 * @link               http://wpxtre.me
 * @created            09/05/12
 * @version            1.0.0
 *
 */

class WPXtremeStoreViewController {

    // -----------------------------------------------------------------------------------------------------------------
    // Static values
    // -----------------------------------------------------------------------------------------------------------------

    // -----------------------------------------------------------------------------------------------------------------
    // Display
    // -----------------------------------------------------------------------------------------------------------------

    public static function display() {

        /* Se arriva un action impostata eseguo comando. */
        if ( isset( $_GET['action'] ) ) {

            /* Update Plugin. */
            if ( $_GET['action'] == 'upgrade-plugin' ) {
                return self::update_plugin();
            } elseif ( $_GET['action'] == 'install-plugin' && !empty( $_GET['plugin'] ) ) {
                $result = self::install_plugin();
            } elseif ( $_GET['action'] == 'error-request_filesystem_credentials' ) {
                return self::error();
            }
        }

        ?>
    <div class="wrap">
        <div class="wpxm-icon-xtreme"></div>
        <h2><?php _e( 'Plugin Store', 'wp-xtreme' ); ?></h2>

        <div class="wpdk-jquery-ui">

            <?php
            $result = WPXtremeAPI::isAlive();
            if ( !empty( $result ) ) {
                /* Get Store */
                echo WPXtremeAPI::plugstore();
            } else {
                echo self::serverUnreachable();
                }
            ?>

        </div>
    </div>

    <?php
    }

    private static function serverUnreachable() {
        $title   = __( 'Warning', 'wp-xtreme' );
        $message = __( 'Sorry but the wpXtreme server do not response at this momnet. Retry later', 'wp-xtreme' );

        $html = <<< HTML
<div class="wpxm-alert">
    <h3>{$title}</h3>
    <p>{$message}</p>
HTML;
        return $html;
    }

    private static function error() {
        printf( '<h2>%s</h2>', __( 'An error detect', WPXTREME_TEXTDOMAIN ) );
    }

    /**
     * Riproduco update di WordPress
     *
     * @static
     * @return mixed
     */
    private static function update_plugin() {
        include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
        $plugin = isset( $_REQUEST['plugin'] ) ? trim( $_REQUEST['plugin'] ) : '';

        $title = __( 'Update Plugin from wpxStore' );

        $nonce = 'upgrade-plugin_' . $plugin;
        $url   = 'update.php?action=upgrade-plugin&plugin=' . $plugin;

        $upgrader = new Plugin_Upgrader( new Plugin_Upgrader_Skin( compact( 'title', 'nonce', 'url', 'plugin' ) ) );
        $upgrader->upgrade( $plugin );

        return;
    }

    private static function _install_plugin() {

        $plugin = isset( $_REQUEST['plugin'] ) ? trim( $_REQUEST['plugin'] ) : '';

        $download_link = WPXtremeAPI::pluginUrl( $plugin );

        include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
        include_once ABSPATH . 'wp-admin/includes/plugin-install.php'; //for plugins_api..

        $plugin = isset( $_REQUEST['plugin'] ) ? trim( $_REQUEST['plugin'] ) : '';

        $api = plugins_api('plugin_information', array('slug' => $plugin, 'fields' => array('sections' => false) ) );

        $title = __( 'Install Plugin from wpxStore' );

        $nonce = 'install-plugin_' . $plugin;
        $url   = 'update.php?action=install-plugin&plugin=' . $plugin;

//        $url = admin_url( 'admin.php?page=wpxm_menu_store' );
//        $url = add_query_arg( array(
//                                   'action'   => 'install-plugin',
//                                   'plugin'   => $plugin
//                              ), $url );

        $upgrader = new Plugin_Upgrader( new Plugin_Installer_Skin( compact( 'title', 'nonce', 'url', 'plugin', 'api' ) ) );
        $upgrader->install( $download_link );

        return;
    }

    /**
     * Installo un plugin
     *
     * @static
     *
     */
    private static function install_plugin() {
        global $wp_filesystem;

        $redirect_error_to = admin_url( 'admin.php?page=wpxm_menu_plugin_store' );
        $redirect_error_to = add_query_arg( array( 'action' => 'error-request_filesystem_credentials', ), $redirect_error_to );

        $url = admin_url( 'admin.php?page=wpxm_menu_plugin_store' );
        $url = add_query_arg( array( 'action'   => 'install-plugin', 'plugin'   => esc_attr( $_GET['plugin'] ) ), $url );

        $url = wp_nonce_url( $url,'install-plugin');

        $form_fields = array ('action', 'plugin');

        if ( false === ( $creds = request_filesystem_credentials( $url, '', false, false, $form_fields ) ) ) {
            return false;
        }

        if ( !WP_Filesystem( $creds ) ) {
            request_filesystem_credentials( $url, '', true, false, $form_fields );
            return false;
        }

        /* Chiedo al server l'indirizzo di download: */
        $url = WPXtremeAPI::pluginUrl( esc_attr( $_GET['plugin'] ) );

        if ( empty( $url ) ) {
            return false;
        }

        /* Nel caso la cartella non esistesse. */
        //        if ( !$wp_filesystem->is_dir( WPXTREME_DOWNLOAD_PATH ) ) {
        //            $wp_filesystem->mkdir( WPXTREME_DOWNLOAD_PATH );
        //        }

        /* Eseguo il download. */
        $remote_file = download_url( $url );

        /* Unzippo il plugin nella cartella dei plugin. */
        $unzip_folder = trailingslashit( WP_PLUGIN_DIR );

        /* @todo Rimuovere eventuale cartella/residuo */

        /* Unzippo da $remote_file (file temporaneo) a $unzip_folder */
        $downloaded = unzip_file( $remote_file, $unzip_folder );

        if ( is_wp_error( $downloaded ) ) {
            return false;
        }

        unlink( $remote_file );

        $result = activate_plugin( esc_attr( $_GET['plugin'] ) );

        return true;

    }

}
