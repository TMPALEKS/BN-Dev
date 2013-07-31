<?php
/**
 * @class              WPXSmartShopAdmin
 * @description        Class for Manage Admin (back-end)
 *
 * @package            wpx SmartShop
 * @subpackage         admin
 * @author             =undo= <g.fazioli@wpxtre.me>
 * @copyright          Copyright (C) 2012 wpXtreme, Inc.
 * @version            1.0.0
 *
 */

class BNMExtendsAdminAdvance extends WPDKWordPressAdmin {

    /// Construct
    function __construct( ) {

        /* Trap special Get parameter */
        add_action( 'admin_init', array( $this, 'admin_init') );
        add_action( 'admin_menu', array( &$this, 'admin_menu' ) );


    }

    // -----------------------------------------------------------------------------------------------------------------
    // WordPress Hooks
    // -----------------------------------------------------------------------------------------------------------------

    /// Hook when admin is init
    function admin_init() {

        /* Sfrutto questa posizione privilegiata per eseguire tutta una serie di operazioni speciali quali download e
        stampe, in quanto ho bisogno di eludere tutto l'html del backend
        */

        /* Stats: Export CSV */
        if ( isset( $_GET['export_users_csv'] ) ) {
            BNMExtendsUser::downalodCSV();
            exit;
        }

    }

    /// Hook when menu is ready to add
    /**
     * Typically, you will use wp_enqueue_script() hooked to an early action that occurs before any content is sent to
     * the browser, when administrator is loaded
     *
     * @static
     */
    public function admin_menu() {

        /* Users */
        add_submenu_page( 'users.php', __( 'Export Utenti', 'bnmextends' ), __( 'Esporta Utenti', 'bnmextends' ), 'activate_plugins', 'bnmextends_users_options', array( $this, 'menu_users') );
        //add_action( 'admin_head-' . $orderss, array( $this, 'admin_head_menu_main' ) ); // Per adesso le stesse
    }

    /// Display Orders
    function menu_users() {
        if(!class_exists('BNMExtedsUserViewController')) {
            require_once( kBNMExtendsDirectoryPath .'Classes/user/BNMExtendsUser-viewcontroller.php' );
        }
        BNMExtendsUserViewController::listTableView();
    }
}