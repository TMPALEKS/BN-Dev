<?php
/**
 * @class              WPPlaceholdersAdmin
 * @description        Class for Manage Admin (back-end)
 *
 * @package            wpx Placeholders
 * @subpackage         admin
 * @author             =undo= <g.fazioli@wpxtre.me>
 * @copyright          Copyright (C) 2012 wpXtreme, Inc.
 * @version            1.0.0
 *
 */

class WPPlaceholdersAdmin extends WPDKWordPressAdmin {

    /// Construct
    function __construct( WPXPlaceholders $plugin ) {
        parent::__construct( $plugin );

        /* Loading Script & style for backend */
        add_action('admin_head', array( $this, 'admin_head' ) );

        /* Plugin List */
        add_action('plugin_action_links_' . $this->plugin->plugin_basename, array( $this, 'plugin_action_links' ), 10, 4);
    }

    // -----------------------------------------------------------------------------------------------------------------
    // Methods to overwrite
    // -----------------------------------------------------------------------------------------------------------------

    public function admin_head() {
        wp_enqueue_style( 'wpxph-admin-styles', $this->plugin->url_css . 'admin.css' );
    }

    /// Init menu
    function admin_menu() {
        /* Icona */
        $icon_menu = $this->plugin->url_images . 'logo-16x16.png';

        /* Main menu */
        $menu_main = add_menu_page( 'Placeholders', 'Placeholders', WPXPLACEHOLDERS_MENU_CAPABILITY, 'wpxph_menu_main', array( $this, 'menu_main'), $icon_menu );
        $this->menus['menu_main'] = $menu_main = add_submenu_page( 'wpxph_menu_main', __( 'Reservations', WPXPLACEHOLDERS_TEXTDOMAIN ), __( 'Reservations', WPXPLACEHOLDERS_TEXTDOMAIN ), WPXPLACEHOLDERS_MENU_CAPABILITY, 'wpxph_menu_main', array( $this, 'menu_main') );
        add_action( 'load-' . $menu_main, array( $this, 'load_menu_reservations' ) );

        /* Envirorment */
        $envirorment = add_submenu_page( 'wpxph_menu_main', __( 'Environments', WPXPLACEHOLDERS_TEXTDOMAIN ), __( 'Environments', WPXPLACEHOLDERS_TEXTDOMAIN ), WPXPLACEHOLDERS_MENU_CAPABILITY, 'wpxph_menu_envirorment', array( $this, 'menu_envirorment') );

        /* Places */
        $places = add_submenu_page( 'wpxph_menu_main', __( 'Places', WPXPLACEHOLDERS_TEXTDOMAIN ), __( 'Places', WPXPLACEHOLDERS_TEXTDOMAIN ), WPXPLACEHOLDERS_MENU_CAPABILITY, 'wpxph_menu_places', array( $this, 'menu_places') );

    }

    // -----------------------------------------------------------------------------------------------------------------
    // Plugin page Table List integration
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Aggiunge un link nella riga che identifica questo Plugin nella schermata con l'elenco dei Plugin nel backend di
     * WordPrsss.
     *
     * @static
     *
     * @param $links
     *
     * @return array
     */
    public function plugin_action_links($links) {
        $result = '<a href="index.php?page=wpx-placeholders">' . __( 'Settings', WPXPLACEHOLDERS_TEXTDOMAIN ) . '</a>';
        array_unshift($links, $result);
        return $links;
    }

    // -----------------------------------------------------------------------------------------------------------------
    // Menu items View Controller
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Visualizza lista prenotazioni
     */
    public function menu_main() {
        WPPlaceholdersReservationsViewController::listTableView();
    }

    /// Reservations page is loading
    function load_menu_reservations() {

        /* Screen options */
        global $wpxph_stats_list_table;
        $args = array(
            'label'   => __( 'Items per page', WPXPLACEHOLDERS_TEXTDOMAIN ),
            'default' => 10,
            'option'  => 'orders_per_page'
        );
        add_screen_option( 'per_page', $args );

        if ( !class_exists( 'WPPlaceholdersReservationsListTable' ) ) {
            require_once( WPXPLACEHOLDERS_PATH_CLASSES . 'reservations/wpxph-reservations-listtable.php' );
        }
        $wpxph_stats_list_table = new WPPlaceholdersReservationsListTable();
    }

    /**
     * Visualizza list ambienti
     */
    public function menu_envirorment() {
        WPPlaceholdersEnvironmentsViewController::listTableView();
    }

    /**
     * Visualizza lista posti
     */
    public function menu_places() {
        WPPlaceholdersPlacesViewController::listTableView();
    }


    // -----------------------------------------------------------------------------------------------------------------
    // Menu item Unit Test
    // -----------------------------------------------------------------------------------------------------------------

} // end of class