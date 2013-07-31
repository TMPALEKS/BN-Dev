<?php
/**
 * @class WPSmartShopStorePagePostType
 *
 * Custom Post type utilizzati per le pagine di sistema e di servizio di Smart Shop
 *
 * @package            wpx SmartShop
 * @subpackage         WPSmartShopStorePagePostType
 * @author             =undo= <g.fazioli@undolog.com>, <g.fazioli@wpxtre.me>
 * @copyright          Copyright (c)2012 wpXtreme, Inc.
 * @created            01/12/11
 * @version            1.0
 *
 * @deprecated
 *
 */

class WPSmartShopStorePagePostType {

    // -----------------------------------------------------------------------------------------------------------------
    // Post Type
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Registra il custom post type
     *
     * @static
     *
     */
    public static function registerPostType() {
        $labels = array(
            'name'               => __( 'Store Pages', WPXSMARTSHOP_TEXTDOMAIN ),
            'singular_name'      => __( 'Store Page', WPXSMARTSHOP_TEXTDOMAIN ),
            'add_new'            => __( 'Add New', WPXSMARTSHOP_TEXTDOMAIN ),
            'add_new_item'       => __( 'Add New Store Page', WPXSMARTSHOP_TEXTDOMAIN ),
            'edit_item'          => __( 'Edit', WPXSMARTSHOP_TEXTDOMAIN ),
            'new_item'           => __( 'New Store Page', WPXSMARTSHOP_TEXTDOMAIN ),
            'view_item'          => __( 'View Store Page', WPXSMARTSHOP_TEXTDOMAIN ),
            'search_items'       => __( 'Store Page Search', WPXSMARTSHOP_TEXTDOMAIN ),
            'not_found'          => __( 'Store Pages not found', WPXSMARTSHOP_TEXTDOMAIN ),
            'not_found_in_trash' => __( 'No Store Page in trash', WPXSMARTSHOP_TEXTDOMAIN ),
            'parent_item_colon'  => ''
        );
        $args   = array(
            'labels'               => $labels,
            'public'               => true,
            'publicly_queryable'   => true,
            'show_ui'              => true,
            'menu_icon'            => WPXSMARTSHOP_URL_CSS . 'images/logo-16x16.png',
            'query_var'            => true,
            'rewrite'              => array(
                'slug'       =>  __( 'store', WPXSMARTSHOP_TEXTDOMAIN ),
                'with_front' => false
            ),
            'capability_type'      => 'page',
            'hierarchical'         => false,
            'menu_position'        => 102,
            'supports'             => array(
                'title',
                'editor',
                'thumbnail',
                'excerpt'
            )
        );

        register_post_type( kWPSmartShopStorePagePostTypeKey, $args );

    }
}