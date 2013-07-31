<?php
/**
 * Admin
 *
 * @package            WPXtreme
 * @subpackage         admin
 * @author             =undo= <g.fazioli@wpxtre.me>
 * @copyright          Copyright (c) 2012 wpXtreme, Inc.
 * @created            13/04/12
 * @version            1.0.0
 *
 */

class WPXtremeAdmin extends WPDKWordPressAdmin {

    static $plugin_store;

    var $settings;

    // -----------------------------------------------------------------------------------------------------------------
    // Constants values
    // -----------------------------------------------------------------------------------------------------------------

    const kMenuStoreCapability = WPXTREME_DEFAULT_PLUGIN_STORE_CAPABILITY;

    /// Construct
    function __construct( WPXtreme $plugin ) {
        parent::__construct( $plugin );

        /* Plugin List */
        add_action('plugin_action_links_' . $this->plugin->plugin_basename, array( $this, 'plugin_action_links' ), 10, 4);
        add_filter('plugin_row_meta', array( $this, 'plugin_row_meta' ), 10, 2);

        /* Loading Script & style for backend */
        add_action( 'admin_head', array( $this, 'admin_head') );

        /* @todo Solo se richesta esegue una serie di migliorie a WordPress */
        $this->settings = WPXtreme::settings();

        if ( $this->settings->enhanced_wordpress() ) {
            $this->body_classes['wpxm-body'] = true;
        }

    }

    // -----------------------------------------------------------------------------------------------------------------
    // WordPress Hooks
    // -----------------------------------------------------------------------------------------------------------------

    /// When admin loaded
    function admin_head() {

        /* Styles */
        wp_deregister_style('jquery-ui');

        wp_enqueue_style( 'wpxm-admin', $this->plugin->url_css . 'admin.css' );

        /* @todo Solo se richesta esegue una serie di migliorie a WordPress */
        if ( $this->settings->enhanced_wordpress() ) {
            wp_enqueue_style( 'wpxm-admin-enhanced', $this->plugin->url_css . 'admin-enhanced.css' );
        }

        /* @todo Solo se richesta esegue una serie di migliorie a WordPress */
        wp_enqueue_style( 'wpdk-badged-bar', $this->plugin->url_css . 'badged-bar.css' );

        /* @todo Solo se richesta esegue una serie di migliorie a WordPress */
        wp_enqueue_style( 'wpdk-badged-menu', $this->plugin->url_css . 'badged-menu.css' );

        /* Scripts */
        wp_enqueue_script( 'wpxm-admin', $this->plugin->url_javascript . 'wpxm-admin.js', array( 'jquery' ), $this->plugin->version, true );

    }

    /// Build Admin menu
    public function admin_menu() {

        /* Hack for wpXtreme icon. */
        $icon_menu    = $this->plugin->url_images . 'wpx-treme-16x16.png';
        $icon_submenu = '<i class="wpxm-menu-item-icon"></i>';
        $count        = WPDKUpdate::countUpdatingPlugins();

        /* Creo un badged da mettere nel menu store in caso ci siano dei plugin da aggiornare. */
        $badged = '';
        if ( !empty( $count ) ) {
            $title = sprintf( __( 'You have %s plugin to update!', WPXTREME_TEXTDOMAIN ), $count );
            $badged = sprintf( '<span title="%s" data-placement="bottom" class="wpdk-tooltip update-plugins count-%s"><span class="plugin-count">%s</span></span>', $title, $count, number_format_i18n( $count ) );
        }

        /* Aggiungo lo Store di wpXtreme a quelle di WordPress */
        self::$plugin_store = add_menu_page( 'wpXtreme', 'wpXtreme' . $badged, self::kMenuStoreCapability, 'wpxm_menu_plugin_store', array( $this, 'menu_plugin_store'), $icon_menu, 1 );
        $plugin_store = add_submenu_page( 'wpxm_menu_plugin_store', __( 'Plugin Store', WPXTREME_TEXTDOMAIN ),  __('Plugin Store', WPXTREME_TEXTDOMAIN ) . $badged, self::kMenuStoreCapability, 'wpxm_menu_plugin_store', array( $this, 'menu_plugin_store') );

        /* Head. */
        add_action( 'admin_head-' . self::$plugin_store, array( $this, 'admin_head_plugin_store' ) );

        /* Aggiungo le impostazioni per la nuova gestione utente WordPress */
        $users_settings = add_submenu_page( 'wpxm_menu_plugin_store', __( 'Users', WPXTREME_TEXTDOMAIN ), __( 'Users', WPXTREME_TEXTDOMAIN ), self::kMenuStoreCapability, 'wpxm_menu_users_settings', array( $this, 'menu_users_settings') );
        /* Head. */
        add_action( 'admin_head-' . $users_settings, array( $this, 'admin_head_settings' ) ); // Per adesso le stesse

        /* Aggiungo le impostazioni per le appearance */
        $settings = add_submenu_page( 'wpxm_menu_plugin_store', __( 'Settings', WPXTREME_TEXTDOMAIN ), __('Settings', WPXTREME_TEXTDOMAIN ), self::kMenuStoreCapability, 'wpxm_menu_settings', array( $this, 'menu_settings') );
        /* Head. */
        add_action( 'admin_head-' . $settings, array( $this, 'admin_head_settings' ) );

        /* Credits */
        $credits = add_submenu_page( 'wpxm_menu_plugin_store', __( 'wpXtreme Credits', WPXTREME_TEXTDOMAIN ), __( 'Credits', WPXTREME_TEXTDOMAIN ), self::kMenuStoreCapability, 'wpxm_menu_credits', array( $this, 'menu_credits') );
        add_action( 'admin_head-' . $credits, array( $this, 'admin_head_menu_main' ) ); // Per adesso le stesse


        /* Aggiungo lo unit test */
        $unit_test = add_menu_page( 'Unit Test', 'Unit Test', self::kMenuStoreCapability, 'wpxm_menu_unit_test', array( $this, 'menuUnitTest'), $icon_menu, 500 );
    }

    // -----------------------------------------------------------------------------------------------------------------
    // Plugin page Table List integration
    // -----------------------------------------------------------------------------------------------------------------

    /// Plugin list
    public function plugin_action_links( $links ) {
        $l10n = array(
            'warnig_confirm_disable_plugin' => __( 'WARNING! If you disable WPXtreme Plugin, you can\'t manage plugin\'s suite! Are you sure to continue?', WPXTREME_TEXTDOMAIN )
        );

        wp_enqueue_script( 'wpxm-plugin-list', $this->plugin->url_javascript . 'wpxm-plugin-list.js',  $this->plugin->version, true );
        wp_localize_script( 'wpxm-plugin-list', 'WPXMPluginListL10n', $l10n );

        $result = '<a href="index.php?page=wpxm_menu_store">' . __( 'wpXStore', WPXTREME_TEXTDOMAIN ) . '</a>';
        array_unshift( $links, $result );
        return $links;
    }

    /// Plugin list row
    public function plugin_row_meta( $links, $file ) {
        if ( $file ==  $this->plugin->plugin_basename ) {
            $links[] = '<span class="wpxm-row-meta">' . __( 'For more info and plugins visit', WPXTREME_TEXTDOMAIN ) .
                ' <a href="http://www.wpxtre.me">wpXtre.me</a></span>';
        }
        return $links;
    }

    // -----------------------------------------------------------------------------------------------------------------
    // Menus
    // -----------------------------------------------------------------------------------------------------------------

    /// Plugin store
    function menu_plugin_store() {
        if ( !class_exists( 'WPXtremeStoreViewController' ) ) {
            require( $this->plugin->path_classes . 'store/wpxtreme-store-viewcontroller.php' );
        }
        WPXtremeStoreViewController::display();
    }

    function admin_head_plugin_store() {
        $this->body_classes['wpdk-body'] = true;

        wp_enqueue_script( 'wpxm-plugin-store', $this->plugin->url_javascript . 'wpxm-plugin-store.js', array( 'wpdk-screenfull' ), $this->plugin->version, true );

        $localization = array(
            'url_ajax'      => $this->plugin->url_ajax
        );

        wp_localize_script( 'wpxm-plugin-store', 'wpxtreme_localization_plugin_store', $localization );
    }

    /// User Settings
    function menu_users_settings() {
        if ( !class_exists( 'WPXtremeUsersSettingsViewController' ) ) {
            require_once( $this->plugin->path_classes . 'users/wpxtreme-users-settings-viewcontroller.php' );
        }
        WPXtremeUsersSettingsViewController::display();
    }

    /// wpXtreme Settings
    function menu_settings() {

        if ( !class_exists( 'WPXtremeSettingsViewController' ) ) {
            require( $this->plugin->path_classes . 'settings/wpxtreme-settings-viewcontroller.php' );
        }
        WPXtremeSettingsViewController::display();
    }

    function admin_head_settings() {
        $this->body_classes['wpdk-body'] = true;

        /* Eventuali script e style */
    }

    /// Credits
    function menu_credits() {
        if ( !class_exists( 'WPXtremeCreditsViewController' ) ) {
            require_once( $this->plugin->path_classes . 'admin/wpxtreme-credits-viewcontroller.php' );
        }
        WPXtremeCreditsViewController::display();
    }

    // -----------------------------------------------------------------------------------------------------------------
    // Menu item: UnitTest
    // -----------------------------------------------------------------------------------------------------------------

    /// Manual Unit test
    public static function menuUnitTest() {
        include('unit_test.php');
    }

}
