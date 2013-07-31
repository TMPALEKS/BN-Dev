<?php
/**
 * Gestione dei messaggi privati Interni
 *
 * @package            BNMExtends
 * @subpackage         BNMExtendsSystemPagePostType
 * @author             =undo= <g.fazioli@undolog.com>, <g.fazioli@saidmade.com>
 * @copyright          Copyright (C)2011 Saidmade Srl.
 * @created            01/12/11
 * @version            1.0
 *
 */

class BNMExtendsSystemPagePostType {

    function __construct() {

    }

    // -----------------------------------------------------------------------------------------------------------------
    // Post Type
    // -----------------------------------------------------------------------------------------------------------------

    public static function registerPostType() {
        $labels = array(
            'name'               => 'Messaggi',
            'singular_name'      => 'Messaggio Sistema',
            'add_new'            => 'Aggiungi nuovo',
            'add_new_item'       => 'Aggiungi Nuovo Messaggio Sistema',
            'edit_item'          => 'Modifica',
            'new_item'           => 'Nuovo Messaggio Sistema',
            'view_item'          => 'Visualizza Messaggio Sistema',
            'search_items'       => 'Ricerca Messaggio Sistema',
            'not_found'          => 'Messaggi Sistema non trovati',
            'not_found_in_trash' => 'Nessun Messaggio Sistema nel cestino',
            'parent_item_colon'  => ''
        );
        $args   = array(
            'labels'               => $labels,
            'public'               => true,
            'publicly_queryable'   => true,
            'show_ui'              => true,
            'menu_icon'            => get_stylesheet_directory_uri() . '/images/admin_logo.png',
            'query_var'            => true,
            'rewrite'              => array(
                'slug'       => __('message', 'bnmextends'),
                'with_front' => false
            ),
            'capability_type'      => 'page',
            'hierarchical'         => false,
            'menu_position'        => kBNMExtendsSystemPagePostTypeMenuItemPosition,
            'supports'             => array(
                'title',
                'editor',
                'thumbnail'
            )
        );

        register_post_type(kBNMExtendsSystemPagePostTypeKey, $args);

        // Register columns
        add_filter('manage_edit-' . kBNMExtendsSystemPagePostTypeKey . '_columns', array(__CLASS__, 'registerColumns'));

        // Manage view custom columns
        add_action('manage_' . kBNMExtendsSystemPagePostTypeKey . '_posts_custom_column', array(__CLASS__, 'manageColumns'));

    }

    public static function registerColumns($columns) {
        $columns = array(
            'cb'        => '<input type="checkbox" />',
            'title'     => 'Messaggio');
        return $columns;
    }

    public static function manageColumns($column) {
        global $post;

        // Recupera il custom field dal post della lingua di base
        $id_message = self::idWPMLDefaultLanguage($post->ID);

        if ('ID' == $column) {
            echo $post->ID;
        }
    }

    // -----------------------------------------------------------------------------------------------------------------
    // Static common utility methods
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Compatibilità con WPML. Restituisce l'id della lingua base per condividere i custom field e altre informazioni
     * condivise, come le thumbnail ad esempio.
     *
     * @static
     *
     * @param $id
     *
     * @return null
     */
    public static function idWPMLDefaultLanguage($id) {
        return defined('ICL_LANGUAGE_CODE') ? icl_object_id($id, kBNMExtendsSystemPagePostTypeKey, true, kBNMExtendsWPMLIntegrationDefaultLanguage) : $id;
    }

}