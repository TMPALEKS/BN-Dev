<?php
/**
 * Gestione Tag prodotto
 *
 * @package            wpx SmartShop
 * @subpackage         WPSmartShopProductTagTaxonomy
 * @author             =undo= <g.fazioli@undolog.com>, <g.fazioli@wpxtre.me>
 * @copyright          Copyright (c) 2012 wpXtreme, Inc.
 * @link               http://wpxtre.me
 * @created            06/03/12
 * @version            1.0.0
 *
 */

class WPSmartShopProductTagTaxonomy {

    // -----------------------------------------------------------------------------------------------------------------
    // WordPress integration
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Registra la taxonomia.
     * Prepara tutti i parametri per la registrazione della nuova tassionomia all'interno dell'ambiente WordPress.
     *
     * @package    wpx SmartShop
     * @subpackage WPSmartShopProductTypeTaxonomy
     * @since      1.0.0
     *
     * @static
     *
     * @uses       register_taxonomy()
     *
     */
    public static function registerTaxonomy() {
        $labels = array(
            'name'                       => __( 'Product Tags', WPXSMARTSHOP_TEXTDOMAIN ),
            'singular_name'              => __( 'Product Tag', WPXSMARTSHOP_TEXTDOMAIN ),
            'search_items'               => __( 'Search Product Tags', WPXSMARTSHOP_TEXTDOMAIN ),
            'all_items'                  => __( 'All Product Tags', WPXSMARTSHOP_TEXTDOMAIN ),
            'parent_item'                => __( 'Parent Product Tas', WPXSMARTSHOP_TEXTDOMAIN ),
            'parent_item_colon'          => __( 'Parent Product Tag:', WPXSMARTSHOP_TEXTDOMAIN ),
            'edit_item'                  => __( 'Edit Product Tags', WPXSMARTSHOP_TEXTDOMAIN ),
            'update_item'                => __( 'Update Product Tags', WPXSMARTSHOP_TEXTDOMAIN ),
            'add_new_item'               => __( 'Add New Product Tags', WPXSMARTSHOP_TEXTDOMAIN ),
            'new_item_name'              => __( 'New Product Tags', WPXSMARTSHOP_TEXTDOMAIN ),
            'menu_name'                  => __( 'Product Tags', WPXSMARTSHOP_TEXTDOMAIN ),

            'popular_items'              => __( 'Popular Product Tags', WPXSMARTSHOP_TEXTDOMAIN ),
            'separate_items_with_commas' => __( 'Separate Product Tags with commas', WPXSMARTSHOP_TEXTDOMAIN ),
            'add_or_remove_items'        => __( 'Add or remove Product Tags', WPXSMARTSHOP_TEXTDOMAIN ),
            'choose_from_most_used'      => __( 'Choose from the most popular Product Tags', WPXSMARTSHOP_TEXTDOMAIN )
        );

        $args = array(
            'hierarchical'          => false,
            'labels'                => $labels,
            'public'                => true,
            'show_ui'               => true,
            'show_tagcloud'         => true,
            'show_in_nav_menus'     => true,
            'query_var'             => 'wpss_product_tags',
            'update_count_callback' => '_update_post_term_count',
            'rewrite'               => array( 'slug'       => __( 'product-tags', WPXSMARTSHOP_TEXTDOMAIN ),
                                              'with_front' => false
            )
        );

        register_taxonomy( kWPSmartShopProductTagTaxonomyKey, array( WPXSMARTSHOP_PRODUCT_POST_KEY ), $args );
    }

    // -----------------------------------------------------------------------------------------------------------------
    // Terms
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Restituisce un array keypair con chiave uguale al nome del termine tutto minuscoloo e valore uguale all'id del
     * termine
     *
     * @static
     *
     */
    public static function arrayTermsWithKeyName( $hide_empty = false ) {
        $array_terms = get_transient( kWPSmartShopProductTagTaxonomyKey );

        if ( $array_terms ) {
            return unserialize( $array_terms );
        } else {
            $all_terms = get_terms( kWPSmartShopProductTypeTaxonomyKey, array( 'hide_empty' => $hide_empty ) );
            foreach ( $all_terms as $term ) {
                $terms_by_name[strtolower( apply_filters( 'the_category', $term->name ) )] = $term->term_id;
            }
            set_transient( kWPSmartShopProductTypeTaxonomyKey, serialize( $array_terms ), 60 * 10 );
        }
        return $array_terms;
    }


}
