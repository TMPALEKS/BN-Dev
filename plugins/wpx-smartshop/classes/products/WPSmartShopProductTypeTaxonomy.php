<?php
/**
 * Gestore della tassionomia (categoria) Tipo di prodotto
 *
 * @package            wpx SmartShop
 * @subpackage         WPSmartShopProductTypeTaxonomy
 * @author             =undo= <g.fazioli@undolog.com>, <g.fazioli@wpxtre.me>
 * @copyright          Copyright (c)2012 wpXtreme, Inc.
 * @created            10/11/11
 * @version            1.0.0
 *
 */

class WPSmartShopProductTypeTaxonomy {

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
            'name'              => __('Products Types', WPXSMARTSHOP_TEXTDOMAIN ),
            'singular_name'     => __('Product Type', WPXSMARTSHOP_TEXTDOMAIN ),
            'search_items'      => __('Search Product Type', WPXSMARTSHOP_TEXTDOMAIN ),
            'all_items'         => __('All Product Types', WPXSMARTSHOP_TEXTDOMAIN ),
            'parent_item'       => __('Parent Product Type', WPXSMARTSHOP_TEXTDOMAIN ),
            'parent_item_colon' => __('Parent Product Type:', WPXSMARTSHOP_TEXTDOMAIN ),
            'edit_item'         => __('Edit Product Type', WPXSMARTSHOP_TEXTDOMAIN ),
            'update_item'       => __('Update Product Type', WPXSMARTSHOP_TEXTDOMAIN ),
            'add_new_item'      => __('Add New Product Type', WPXSMARTSHOP_TEXTDOMAIN ),
            'new_item_name'     => __('New Product Type', WPXSMARTSHOP_TEXTDOMAIN ),
            'menu_name'         => __('Product Type', WPXSMARTSHOP_TEXTDOMAIN )
        );

        $args = array(
            'hierarchical'          => true,
            'labels'                => $labels,
            'show_ui'               => true,
            'query_var'             => 'wpss_product_type',
            'update_count_callback' => '_update_post_term_count',
            'rewrite'               => array( 'slug'       => __( 'product-types', WPXSMARTSHOP_TEXTDOMAIN ),
                                              'with_front' => false
            )
        );

        register_taxonomy( kWPSmartShopProductTypeTaxonomyKey, array( WPXSMARTSHOP_PRODUCT_POST_KEY ), $args);
    }


    /**
     * Restituisce il nome di un termine.
     * Si potrebbe in realtà eseguire una select sulla tabella wp_terms ed estrarre il campo 'name' dove 'term_id' è
     * uguale al quello passato negli inputs. Qui comunque se usa la funzione WordPress get_term()
     *
     * @package    wpx SmartShop
     * @subpackage WPSmartShopProductTypeTaxonomy
     * @since      1.0.0
     *
     * @static
     *
     * @uses       get_term()
     *
     * @param $id_term
     *   ID del termine da cercare.
     *
     * @retval bool | string
     *   Se il termine non è trovato viene restituito false. Altrimenti il nome del termine
     */
    public static function name( $id_term ) {
        $result = false;
        $term   = get_term( $id_term, kWPSmartShopProductTypeTaxonomyKey );
        if ( $term && isset( $term->name )) {
            $result = apply_filters( 'the_category', $term->name );
        }
        return $result;
    }

    // -----------------------------------------------------------------------------------------------------------------
    // Gets terms
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Wrapper: restituisce l'elenco dei tipi prodoto
     *
     * @package    wpx SmartShop
     * @subpackage WPSmartShopProductTypeTaxonomy
     * @since      1.0.0
     *
     * @uses get_terms()
     * @see Vedi get_terms() per i parametri possibili da passare negli inputs
     *
     * @static
     * @param string $args
     * @retval array|WP_Error
     */
    public static function productTypes( $args = '' ) {
        $results = get_terms( kWPSmartShopProductTypeTaxonomyKey, $args );
        return $results;
    }

    /**
     * Restituisce un array con tutti i tipi di prodotto dove la chiave è l'id del termine e il valore è un array
     * che a sua volta può avere una chiave 'child' con i relativi figli.
     *
     * @package    wpx SmartShop
     * @subpackage WPSmartShopProductTypeTaxonomy
     * @since      1.0.0
     *
     * @static
     * @param array $args
     * @param null $child
     * @retval array|bool
     *
     * array(6) {
     *   [33] => array(4) {
     *     ["name"]   => string(21) "Abbonamenti e Vaucher"
     *     ["slug"]   => string(21) "abbonamenti-e-vaucher"
     *     ["count"]  => string(1) "4"
     *     ["child"]  => array(1) {
     *           [39] => array(4) {
     *         ["name"]  => string(6) "Zoppas"
     *         ["slug"]  => string(6) "zoppas"
     *         ["count"] => string(1) "0"
     *         ["child"] => bool(false)
     *       }
     *     }
     *   }
     * ...
     */
    public static function arrayTaxonomy( $args = array(), $child = null ) {
        $result = array();

        if ( !is_null( $child ) ) {
            $args['child_of'] = $child->term_id;
            $args['parent']   = $child->term_id;
        }

        $args['hierarchical'] = false;

        $taxonomies = get_terms( kWPSmartShopProductTypeTaxonomyKey, $args );

        if ( !empty( $taxonomies ) ) {
            foreach ( $taxonomies as $tax ) {
                if ( !is_null( $child ) || empty( $tax->parent ) ) {
                    $result[$tax->term_id] = array(
                        'name'   => $tax->name,
                        'slug'   => $tax->slug,
                        'count'  => $tax->count,
                        'child'  => self::arrayTaxonomy( $args, $tax )
                    );
                }
            }
            return $result;
        }
        return false;
    }

    /**
     * Restituisce un array lineare di tassionomie usato per ordinare un elenco di termini
     *
     * @static
     *
     * @param array $args
     *
     * @retval array
     */
    public static function arrayTaxonomySorter( $args = array() ) {
        $defaults = array(
            'hide_empty' => true
        );

        $args = array_merge( $args, $defaults );

        $taxonomies = get_terms( kWPSmartShopProductTypeTaxonomyKey, $args );
        $result     = array();

        foreach ( $taxonomies as $term ) {
            $result[] = array(
                'id'       => $term->term_id,
                'name'     => apply_filters( 'the_category', $term->name ),
                'selected' => false
            );
        }

        return $result;
    }

    /**
     * Restituisce un array keypair con chiave uguale al nome del termine tutto minuscoloo e valore uguale all'id del
     * termine
     *
     * @static
     *
     */
    public static function arrayTermsWithKeyName( $hide_empty = false ) {
        /* get_terms è già in cache da WordPress */
        $all_terms = get_terms( kWPSmartShopProductTypeTaxonomyKey, array( 'hide_empty' => $hide_empty ) );
        foreach ( $all_terms as $term ) {
            $term_id                                                                 = $term->term_id;
            $array_terms[strtolower( apply_filters( 'the_category', $term->name ) )] = $term_id;
        }
        return $array_terms;
    }

}