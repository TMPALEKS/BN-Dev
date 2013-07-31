<?php
/**
 * Questa classe gestisce tutto quello che riguarda il "prodotto", dalla registrazione a custom post type a tutto il
 * resto.
 *
 * @package            wpx SmartShop
 * @subpackage         WPSmartShopProductPostType
 * @author             =undo= <g.fazioli@undolog.com>, <g.fazioli@wpxtre.me>
 * @copyright          Copyright (c)2012 wpXtreme, Inc.
 * @created            14/12/11
 * @version            1.0
 *
 */

require_once( 'wpxss-product-metabox.php' );

class WPSmartShopProductPostType {

    // -----------------------------------------------------------------------------------------------------------------
    // Static values
    // -----------------------------------------------------------------------------------------------------------------

    public static function enqueueStyles() {
        global $typenow;
        if ($typenow == WPXSMARTSHOP_PRODUCT_POST_KEY) {
            // Add custom own styles
        }
    }

    public static function enqueueScripts() {
        global $typenow;
        if ($typenow == WPXSMARTSHOP_PRODUCT_POST_KEY) {
            // Add your custom own script
        }
    }

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
        // Etichette per il Post Type
        $labels = array(
            'name'               => __( 'Products', WPXSMARTSHOP_TEXTDOMAIN ),
            'singular_name'      => __( 'Product', WPXSMARTSHOP_TEXTDOMAIN ),
            'add_new'            => __( 'Add New', WPXSMARTSHOP_TEXTDOMAIN ),
            'add_new_item'       => __( 'Add New Product', WPXSMARTSHOP_TEXTDOMAIN ),
            'edit_item'          => __( 'Edit', WPXSMARTSHOP_TEXTDOMAIN ),
            'new_item'           => __( 'New Product', WPXSMARTSHOP_TEXTDOMAIN ),
            'view_item'          => __( 'View Product', WPXSMARTSHOP_TEXTDOMAIN ),
            'search_items'       => __( 'Product Search', WPXSMARTSHOP_TEXTDOMAIN ),
            'not_found'          => __( 'Products not found', WPXSMARTSHOP_TEXTDOMAIN ),
            'not_found_in_trash' => __( 'No Product in trash', WPXSMARTSHOP_TEXTDOMAIN ),
            'parent_item_colon'  => ''
        );
        $args   = array(
            'labels'               => $labels,
            'public'               => true,
            'publicly_queryable'   => true,
            'show_ui'              => true,
            'show_in_nav_menus'    => true,
            'show_in_admin_bar'    => true,
            'menu_icon'            => WPXSMARTSHOP_URL_CSS . 'images/logo-16x16.png',
            'query_var'            => 'wpss_product',
            'rewrite'              => array(
                'slug'       => __( 'product', WPXSMARTSHOP_TEXTDOMAIN ),
                'with_front' => false
            ),
            'capability_type'      => 'post',
            'hierarchical'         => false,
            'menu_position'        => 101,
            'supports'             => array(
                'thumbnail',
                'title',
                'editor',
                'excerpt',
                'author'
            ),
            'register_meta_box_cb' => array( __CLASS__, 'metaBox' )
        );

        /* Registro il mio custom post type */
        register_post_type( WPXSMARTSHOP_PRODUCT_POST_KEY, $args);

        /* Sync post meta */
        if( WPXSmartShopWPML::isWPLM() ) {
            add_action( 'added_post_meta', array( __CLASS__, 'added_product_meta' ), 10, 4 );
            add_action( 'updated_post_meta', array( __CLASS__, 'updated_product_meta' ), 10, 4 );
            add_action( 'deleted_post_meta', array( __CLASS__, 'deleted_product_meta' ), 10, 4 );
        }

        /* Theme support. */
        /* Se il tema non supporto le miniature, potrebbe essere necessario attivarle a mano da qui. */
        //add_theme_support ( 'post-thumbnails' );

        /* Aggiunge una classe nel tag body nell'amministrazione per gli stili */
        add_action( 'admin_head-edit.php', array( __CLASS__, 'admin_head') );
        //add_filter( 'admin_body_class', array( __CLASS__, 'admin_body_class' ) );

        /* Hook per il salvataggio dei dati extra */
        add_action('save_post', array(__CLASS__, 'save_post' ), 10, 2);

        /* Gestione delle colonne */
        add_action('manage_' . WPXSMARTSHOP_PRODUCT_POST_KEY . '_posts_custom_column', array( __CLASS__, 'manageColumns' ));
        add_filter('manage_edit-' . WPXSMARTSHOP_PRODUCT_POST_KEY . '_columns', array( __CLASS__, 'registerColumns'));

        /*
         * Registro le colonne ordinabili. Queste, in compatibilità con WPML, per questioni legate ai custom meta, solo
         * se la lingua è italiano o prinicipale
         */
        if ( WPXSmartShopWPML::isDefaultLanguage() ) {
            add_filter('manage_edit-' . WPXSMARTSHOP_PRODUCT_POST_KEY . '_sortable_columns', array( __CLASS__, 'registerSortableColumns' ));
            // Fetch sortables
            add_filter('request', array( __CLASS__, 'request' ));
        }

        /* Aggiunge filtri nella table list view */
        add_action('restrict_manage_posts', array( __CLASS__, 'taxonomy_filter_restrict_manage_posts' ));
        add_filter('parse_query', array( __CLASS__, 'taxonomy_filter_post_type_request' ));

        /* Cambia il titolo al meta box standard delle miniature */
        add_action('do_meta_boxes', array( __CLASS__, 'replaceThumbnailMetaBoxTitle' ));

        /* Aggiunge script e style alla vista table list view dei prodotti - vedi switcher e allineamenti */
        add_action('admin_enqueue_scripts', array( __CLASS__, 'enqueueStyles' ) );
        add_action('admin_enqueue_scripts', array( __CLASS__, 'enqueueScripts' ) );
    }

    public static function admin_head() {
        //global $typenow;
        global $post_type;

        if( $post_type == WPXSMARTSHOP_PRODUCT_POST_KEY ) {
            add_filter( 'admin_body_class', array( __CLASS__, 'admin_body_class' ) );
        }
    }

    public static function admin_body_class( $classes ) {
        $classes .= ' wpdk-body ' . WPXSMARTSHOP_PRODUCT_POST_KEY;
        return $classes;
    }

    public static function updated_product_meta( $meta_id, $object_id, $meta_key, $_meta_value ) {

        $wpml_object_id = icl_object_id( $object_id, WPXSMARTSHOP_PRODUCT_POST_KEY, true, 'en' );

        if ( $wpml_object_id != $object_id ) {
            update_post_meta( $wpml_object_id, $meta_key, $_meta_value );
        }
    }

    public static function added_product_meta( $meta_id, $object_id, $meta_key, $_meta_value ) {

        $wpml_object_id = icl_object_id( $object_id, WPXSMARTSHOP_PRODUCT_POST_KEY, true, 'en' );

        if ( $wpml_object_id != $object_id ) {
            update_post_meta( $wpml_object_id, $meta_key, $_meta_value );
        }
    }
    public static function deleted_product_meta( $meta_id, $object_id, $meta_key, $_meta_value ) {

        $wpml_object_id = icl_object_id( $object_id, WPXSMARTSHOP_PRODUCT_POST_KEY, true, 'en' );

        if ( $wpml_object_id != $object_id ) {
            delete_post_meta( $wpml_object_id, $meta_key );
        }
    }

    /**
     * Principalmente usata per cambiare il titolo del meta box usato per le miniature. Inoltre controlla se la
     * maschera è in lingua diversa da quella base, per l'integrazione con WPML; in questo caso elimina anche alcuni
     * meta box riservati alla sola lingua di base
     *
     * @retval void
     */
    public static function replaceThumbnailMetaBoxTitle() {
        global $typenow;

        if ( $typenow == WPXSMARTSHOP_PRODUCT_POST_KEY ) {
            // Sostituisco testo
            // @todo Non pulitissimo, va bene per WordPress con backend monolingua - considerare pezza
            add_filter( 'admin_post_thumbnail_html', array( __CLASS__, 'admin_post_thumbnail_html' ) );

            remove_meta_box( 'postimagediv', WPXSMARTSHOP_PRODUCT_POST_KEY, 'side' );
            add_meta_box( 'postimagediv', __( 'Product Image', WPXSMARTSHOP_TEXTDOMAIN ), 'post_thumbnail_meta_box', WPXSMARTSHOP_PRODUCT_POST_KEY, 'side', 'low' );
        }
    }

    public static function admin_post_thumbnail_html( $content ) {
        $content = str_replace( 'in evidenza', 'prodotto', $content );
        return $content;
    }

    /**
     * Registra (aggiunge) le colonne nella vista List View dei Prodotti
     *
     * @retval array
     */
    public static function registerColumns( $columns ) {

        $new = array(
            'icon'      => __( 'Preview', WPXSMARTSHOP_TEXTDOMAIN ),
            'title'     => __( 'Product', WPXSMARTSHOP_TEXTDOMAIN ),
            'price'     => __( 'Price', WPXSMARTSHOP_TEXTDOMAIN ),
            'qty'       => __( 'Qty', WPXSMARTSHOP_TEXTDOMAIN ),
            'sold'      => __( 'Sold', WPXSMARTSHOP_TEXTDOMAIN ),
            'available' => __( 'Available', WPXSMARTSHOP_TEXTDOMAIN ),
            'enabled'   => __( 'Enabled', WPXSMARTSHOP_TEXTDOMAIN )
        );

        $columns = WPDKArray::insert( $columns, $new, 1 );

        return $columns;
    }

    /**
     * Sortable Column
     *
     * @param array $columns
     *
     * @retval array
     */
    public static function registerSortableColumns( $columns ) {
        $columns['price'] = 'price';
        $columns['qty']   = 'qty';
        $columns['sold']  = 'sold';
        return $columns;
    }

    /**
     * Gestisce la visualizzazione delle colonne per un Post di typo Prodotto
     *
     * @param $column
     *
     * @retval void
     */
    public static function manageColumns( $column ) {

        global $post;

        /* WPML Compatibility - get original base language product id */
        $id_product = WPXSmartShopWPML::originalProductID( $post->ID );

        if ( 'icon' == $column ) {
            echo WPXSmartShopProduct::thumbnail( $post->ID );
        } elseif ( 'price' == $column ) {
            $price = get_post_meta( $id_product, 'wpss_product_base_price', true );
            echo WPXSmartShopCurrency::formatCurrency( $price );
        } elseif ( 'qty' == $column ) {
            echo get_post_meta( $id_product, 'wpss_product_store_quantity', true );
        } elseif ( 'sold' == $column ) {
            echo get_post_meta( $id_product, 'wpss_product_store_quantity_for_order_confirmed', true );
        } elseif ( 'available' == $column ) {
            $class = WPXSmartShopProduct::isProductAvailable( $id_product ) ? 'yes' : 'no';
            $text  = WPXSmartShopProduct::isProductAvailable( $id_product ) ? __( 'Yes', WPXSMARTSHOP_TEXTDOMAIN ) : __( 'No', WPXSMARTSHOP_TEXTDOMAIN );
            ?><span class="wpssProductAvailable <?php echo $class ?>"><?php echo $text ?></span><?php
        } elseif ( 'enabled' == $column ) {
            $item = array(
                'type'       => WPDK_FORM_FIELD_TYPE_SWIPE,
                'name'       => 'wpssProductEnabled',
                'userdata'   => $id_product,
                'afterlabel' => '',
                'value'      => ( get_post_status( $id_product ) == 'publish' ) ? 'on' : 'off'
            );
            WPDKForm::htmlSwipe( $item );
        }
    }

    /**
     * Ordinalmento colonne custom
     *
     * @param array $vars Array con indicazione dell'ordine e colonne
     *
     * @retval array
     */
    public static function request( $vars ) {
        if ( isset( $vars['orderby'] ) ) {

            if ( $vars['orderby'] == 'price' ) {
                $vars = array_merge( $vars, array(
                                                 'meta_key'  => 'wpss_product_base_price',
                                                 'orderby'   => 'meta_value_num'
                                            ) );
            } else if ( $vars['orderby'] == 'qty' ) {
                $vars = array_merge( $vars, array(
                                                 'meta_key'  => 'wpss_product_store_quantity',
                                                 'orderby'   => 'meta_value_num'
                                            ) );
            }
        }
        return $vars;
    }

    // -----------------------------------------------------------------------------------------------------------------
    // Taxonomy
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Ricreca e filtra per questa tassionomia
     *
     * @param $query
     *
     * @retval void
     */
    public static function taxonomy_filter_post_type_request( $query ) {
        global $pagenow, $typenow;

        if ( 'edit.php' == $pagenow ) {
            $filters = get_object_taxonomies( $typenow );
            foreach ( $filters as $tax_slug ) {
                $tax_obj = get_taxonomy( $tax_slug );
                $var     = &$query->query_vars[$tax_obj->query_var];

                if ( isset( $var ) ) {
                    $term = get_term_by( 'id', $var, $tax_slug );
                    $var  = $term->slug;
                }
            }
        }
    }

    /**
     * Filter the request to just give posts for the given taxonomy, if applicable.
     *
     * @retval void
     */
    public static function taxonomy_filter_restrict_manage_posts() {
        global $typenow;

        // If you only want this to work for your specific post type,
        // check for that $type here and then return.
        // This function, if unmodified, will add the dropdown for each
        // post type / taxonomy combination.

        $post_types = get_post_types( array( '_builtin' => false ) );

        if ( in_array( $typenow, $post_types ) ) {
            $filters = get_object_taxonomies( $typenow );

            foreach ( $filters as $tax_slug ) {

                $tax_obj = get_taxonomy( $tax_slug );

                wp_dropdown_categories( array(
                                             'show_option_all' =>
                                             __( 'Show All', WPXSMARTSHOP_TEXTDOMAIN ) . ' ' . $tax_obj->label,
                                             'taxonomy'        => $tax_slug,
                                             'name'            => $tax_obj->query_var,
                                             'orderby'         => 'name',
                                             'selected'        => isset( $_GET[$tax_obj->query_var] ) ? $_GET[$tax_obj->query_var] : '',
                                             'hierarchical'    => $tax_obj->hierarchical,
                                             'show_count'      => false,
                                             'hide_empty'      => true
                                        ) );
            }
        }
    }


    // -----------------------------------------------------------------------------------------------------------------
    // WordPress (meta box) Integration
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Aggiunge un MetaBox alla schemata di inserimento/modifica di un post di tipo Prodotto
     *
     * @retval void
     */
    public static function metaBox() {
        if ( WPXSmartShopWPML::isDefaultLanguage() ) {
            WPXSmartShopProductMetaBox::registerMetaBoxes();
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
        if ( !in_array( $post_type, array( WPXSMARTSHOP_PRODUCT_POST_KEY ) ) ) {
            return;
        }

        /* Verify this came from the our screen and with proper authorization. */
        if ( !WPDKForm::isNonceVerify( 'product' ) ) {
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

        WPXSmartShopProductMetaBox::savePrice( $post );
        WPXSmartShopProductMetaBox::savePurchasable( $post );
        WPXSmartShopProductMetaBox::saveAppearance( $post );
        WPXSmartShopProductMetaBox::saveShipping( $post );
        WPXSmartShopProductMetaBox::saveWarehouse( $post );
        WPXSmartShopProductMetaBox::saveMembership( $post );
        WPXSmartShopProductMetaBox::saveCoupon( $post );
        WPXSmartShopProductMetaBox::saveDigitalProduct( $post );

    }

}
