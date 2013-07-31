<?php
/**
 * @class WPSmartShopShowcasePostType
 *
 * @package            wpx SmartShop
 * @subpackage         showcase
 * @author             =undo= <g.fazioli@undolog.com>, <g.fazioli@wpxtre.me>
 * @copyright          Copyright (c) 2012 wpXtreme, Inc.
 * @created            06/03/12
 * @version            1.0.0
 *
 */

require_once( 'WPSmartShopShowcaseMetaBox.php' );

class WPSmartShopShowcasePostType {

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
            'name'               => __( 'Showcase Pages', WPXSMARTSHOP_TEXTDOMAIN ),
            'singular_name'      => __( 'Showcase Page', WPXSMARTSHOP_TEXTDOMAIN ),
            'add_new'            => __( 'Add New', WPXSMARTSHOP_TEXTDOMAIN ),
            'add_new_item'       => __( 'Add New Showcase', WPXSMARTSHOP_TEXTDOMAIN ),
            'edit_item'          => __( 'Edit', WPXSMARTSHOP_TEXTDOMAIN ),
            'new_item'           => __( 'New Showcase', WPXSMARTSHOP_TEXTDOMAIN ),
            'view_item'          => __( 'View Showcase', WPXSMARTSHOP_TEXTDOMAIN ),
            'search_items'       => __( 'Showcase Search', WPXSMARTSHOP_TEXTDOMAIN ),
            'not_found'          => __( 'Showcase not found', WPXSMARTSHOP_TEXTDOMAIN ),
            'not_found_in_trash' => __( 'No Showcase in trash', WPXSMARTSHOP_TEXTDOMAIN ),
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
                'slug'       => __( 'showcase', WPXSMARTSHOP_TEXTDOMAIN ),
                'with_front' => false
            ),
            'capability_type'      => 'page',
            'hierarchical'         => false,
            'menu_position'        => 103,
            'supports'             => array(
                'title',
                'editor',
                'thumbnail',
                'excerpt'
            ),
            'register_meta_box_cb' => array( __CLASS__, 'metaBox' )
        );

        /* Register post type. */
        register_post_type( kWPSmartShopShowcasePostTypeKey, $args );

        /* Aggiunge una classe nel tag body nell'amministrazione per gli stili */
        add_filter( 'admin_body_class', array( __CLASS__, 'admin_body_class' ) );

        /* Gestione delle colonne */
        add_action('manage_' . kWPSmartShopShowcasePostTypeKey . '_posts_custom_column', array( __CLASS__, 'manage_columns' ));
        add_filter('manage_edit-' . kWPSmartShopShowcasePostTypeKey . '_columns', array( __CLASS__, 'manage_edit_wpss_showcase_columns'));

        /* Amazing action to replace standard theme template */
        add_action("template_redirect", array( __CLASS__, 'template_redirect') );

        /* Hook per il salvataggio dei dati extra */
        add_action('save_post', array(__CLASS__, 'save_post' ), 10, 2);

    }

    // -----------------------------------------------------------------------------------------------------------------
    // WordPress (meta box) Integration
    // -----------------------------------------------------------------------------------------------------------------

    public static function admin_body_class( $classes ) {
        $classes .= ' ' . kWPSmartShopShowcasePostTypeKey;
        return $classes;
    }

    /**
     * Aggiunge un MetaBox alla schemata di inserimento/modifica di un post di tipo Prodotto
     *
     * @retval void
     */
    public static function metaBox() {
        if ( WPXSmartShopWPML::isDefaultLanguage() ) {
            WPSmartShopShowcaseMetaBox::registerMetaBoxes();
        }
    }

    /**
     * Chiamata quando il post è inserito o aggiornato
     *
     * @param int | string $ID ID del prodotto
     *
     * @param object $post Oggetto Post
     *
     * @retval
     */
    public static function save_post($ID, $post) {

        /* Local variables. */
        $ID               = absint( $ID );
        $post_type        = get_post_type();
        $post_type_object = get_post_type_object( $post_type );
        $capability       = '';

        /* Do nothing on auto save. */
        if ( defined( 'DOING_AUTOSAVE' ) && true === DOING_AUTOSAVE ) {
            return;
        }

        /* This function only applies to the following post_types. */
        if ( !in_array( $post_type, array( kWPSmartShopShowcasePostTypeKey ) ) ) {
            return;
        }

        /* Verify this came from the our screen and with proper authorization. */
        if ( !WPDKForm::isNonceVerify( 'showcase' ) ) {
            return;
        }

        /* Find correct capability from post_type arguments. */
        if ( isset( $post_type_object->cap->edit_posts ) ) {
            $capability = $post_type_object->cap->edit_posts;
        }

        /* Return if current user cannot edit this post. */
        if ( !current_user_can( $capability ) ) {
            return;
        }

        /* Save */

        WPSmartShopShowcaseMetaBox::save( $post );

    }

    // -----------------------------------------------------------------------------------------------------------------
    // WordPress (columns) Integration
    // -----------------------------------------------------------------------------------------------------------------

    public static function manage_edit_wpss_showcase_columns( $columns ) {
        $columns['available'] = __( 'Available', WPXSMARTSHOP_TEXTDOMAIN );
        $columns['enabled']   = __( 'Enabled', WPXSMARTSHOP_TEXTDOMAIN );
        return $columns;
    }

    public static function manage_columns( $column ) {
        global $post;

        /* WPML Compatibility - get original base language product id */
        $id_showcase = WPXSmartShopWPML::originalShowcaseID( $post->ID );

        if ( 'available' == $column ) {
            /* @todo Da fare. */
            //$class = WPXSmartShopProduct::isProductAvailable( $id_product ) ? 'yes' : 'no';
            //$text  = WPXSmartShopProduct::isProductAvailable( $id_product ) ? __( 'Yes', WPXSMARTSHOP_TEXTDOMAIN ) : __( 'No', WPXSMARTSHOP_TEXTDOMAIN );
            $class = 'yes';
            $text  = __( 'Yes', WPXSMARTSHOP_TEXTDOMAIN );
            ?><span class="wpss_showcase_available <?php echo $class ?>"><?php echo $text ?></span><?php
        } elseif ( 'enabled' == $column ) {
            $item = array(
                'type'       => WPDK_FORM_FIELD_TYPE_SWIPE,
                'name'       => 'wpss_showcase_enabled',
                'userdata'   => $id_showcase,
                'afterlabel' => '',
                'value'      => ( get_post_status( $id_showcase ) == 'publish' ) ? 'on' : 'off'
            );
            WPDKForm::htmlSwipe( $item );
        }
    }


    // -----------------------------------------------------------------------------------------------------------------
    // WordPress Template Redirect
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Questa serve quando vengono chiamate delle pagine specifiche del tipo show case. Altrimenti non vengono viste.
     * da non confondere con il più alto templete redirect fatto da smart shop in persona. Quello serve, ad esempio,
     * per caricare la prima vetrina disponibile quando si indirizza /showcase.
     * Questo, invece, serve per visualizzare con il mio template onfly una qualsiasi delle vetrina create da backend e
     * che quindi non sono in nessuna lista predefinita.
     *
     * @static
     *
     */
    public static function template_redirect() {
        global $wp;

        /* Check showcase page */
        if ( isset( $wp->query_vars["post_type"] ) && $wp->query_vars["post_type"] == kWPSmartShopShowcasePostTypeKey ) {

            /* Check if exists */
            if ( have_posts() ) {
                WPSmartShopShowcase::display();
                die();
            }
        }
    }
}
