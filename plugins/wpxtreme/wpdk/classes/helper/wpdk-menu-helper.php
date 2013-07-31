<?php
/**
 * Wrapper per le funzioni relativa alla creazione di menu e sottomenu in ambiente WordPress
 *
 * @package            WPDK (WordPress Development Kit)
 * @subpackage         WPDKMenu
 * @author             =undo= <g.fazioli@undolog.com>, <g.fazioli@wpxtre.me>
 * @copyright          Copyright (C)2011 wpXtreme, Inc.
 * @created            17/12/11
 * @version            1.0
 *
 */

class WPDKMenu {

    /**
     * Viene usato come puntatore per poter riaccedere in un secondo momento ai menu
     *
     * @package    WordPress Development Kit
     * @subpackage WPDKForm
     * @since      1.0.0
     *
     * @var array
     */
    public static $menus;

    // -----------------------------------------------------------------------------------------------------------------
    // Commodity
    // -----------------------------------------------------------------------------------------------------------------


    /**
     * Genera una serie di menu alterando il contenuto dell'array inviato negli inputs. In questo viene aggiunto il
     * parametro 'hook' restituito dalla funzione add_submenu_page().
     * Ad esempio questo è utile per creare filtri ed azioni, come:
     *
     * $hook = $menus['chiave']['hook'];
     * add_action("load-{$hook}",array(&$this,'create_help_screen'));
     *
     * @example
     *   Esempio di array menu
     * $menus = array(
     *   'menuItemOrders'          => array(
     *       'parent_slug'       => kWPSmartShopPostTypeMenuKey,
     *       'page_title'        => __('Orders', WPXSMARTSHOP_TEXTDOMAIN ),
     *       'menu_title'        => __('Orders', WPXSMARTSHOP_TEXTDOMAIN ),
     *       'capability'        => kWPSmartShopUserCapability,
     *       'callback'          => array(
     *           __CLASS__,
     *           'menuOrders'
     *       ),
     *       'load'              => array(
     *           __CLASS__,
     *           'didMenuItemOrderLoaded'
     *       )
     *   ),
     *   'menuItemSettings'       => array(
     *       'parent_slug'       => kWPSmartShopPostTypeMenuKey,
     *       'page_title'        => __('Settings', WPXSMARTSHOP_TEXTDOMAIN ),
     *       'menu_title'        => __('Settings', WPXSMARTSHOP_TEXTDOMAIN ),
     *       'capability'        => kWPSmartShopUserCapability,
     *       'callback'          => array(
     *           __CLASS__,
     *           'menuSettings'
     *       )
     *   ),
     *   'menuItemPaymentGateway' => array(
     *       'parent_slug'       => kWPSmartShopPostTypeMenuKey,
     *       'page_title'        => __('Payment Gateways', WPXSMARTSHOP_TEXTDOMAIN ),
     *       'menu_title'        => __('Payment Gateways', WPXSMARTSHOP_TEXTDOMAIN ),
     *       'capability'        => kWPSmartShopUserCapability,
     *       'callback'          => array(
     *           __CLASS__,
     *           'menuPaymentGateways'
     *       )
     *   ),
     *       );
     *
     * @package    WordPress Development Kit
     * @subpackage WPDKForm
     * @since      1.0.0
     *
     * @static
     *
     * @param array $menus
     */
    public static function menus( $menus ) {
        $menu_page = '';
        $hook      = '';
        if ( is_array( $menus ) && !empty( $menus ) ) {
            foreach ( $menus as $key => &$menu ) {
                if ( empty( $menu['parent_slug'] ) && empty( $menu_page ) ) {
                    $menu_page = $key;

                    $icon_url = isset( $menu['icon_url'] ) ? $menu['icon_url'] : '';
                    $position = isset( $menu['position'] ) ? absint( $menu['position'] ) : null;

                    $hook = add_menu_page( $menu['page_title'], $menu['menu_title'], $menu['capability'], $key, $menu['callback'], $icon_url, $position );
                    add_action( 'admin_head-' . $hook, $menu['admin_head'] );

                } elseif ( empty( $menu['parent_slug'] ) ) {
                    if ( !empty( $menu['key'] ) ) {
                        $key = $menu['key'];
                    }
                    $hook = add_submenu_page( $menu_page, $menu['page_title'], $menu['menu_title'], $menu['capability'], $key, $menu['callback'] );
                } else {
                    $hook = add_submenu_page( $menu['parent_slug'], $menu['page_title'], $menu['menu_title'], $menu['capability'], $key, $menu['callback'] );
                }
                if ( !empty( $hook ) ) {
                    $menu['hook'] = $hook;

                    /* Check for action hook 'load' */
                    if ( isset( $menu['load'] ) ) {
                        add_action( 'load-' . $menu['hook'], $menu['load'] );
                    }

                    /* Check for action hook 'admin_head' additional */
                    if ( isset( $menu['admin_head'] ) ) {
                        add_action( 'admin_head-' . $menu['hook'], $menu['admin_head'] );
                    }

                    /* Check for action hook 'admin_enqueue_scripts' */
                    if ( isset( $menu['admin_enqueue_scripts'] ) ) {
                        add_action( 'admin_enqueue_scripts' . $menu['hook'], $menu['admin_enqueue_scripts'] );
                    }

                    /* Deprecated wp 3+ Check Enqueue Styles */
                    if ( isset( $menu['enqueueStyles'] ) ) {
                        add_action( 'admin_print_styles-' . $menu['hook'], $menu['enqueueStyles'] );
                    }
                    /* Deprecated wp3+ Check Enqueue Scripts */
                    if ( isset( $menu['enqueueScripts'] ) ) {
                        add_action( 'admin_print_scripts-' . $menu['hook'], $menu['enqueueScripts'] );
                    }
                }
            }
            self::$menus = $menus;
        }
        return $menus;
    }
}
