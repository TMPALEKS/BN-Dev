<?php
/**
 * @description     Very usefull functions for common cases. All that is missing in WordPress
 *
 * @package         WordPress Development Kit
 * @author          =undo= <g.fazioli@undolog.com>, <g.fazioli@wpxtre.me>
 * @copyright       Copyright (c) 2012 wpXtreme, Inc.
 * @link            http://wpxtre.me
 * @created         20/01/12
 * @version         1.0.0
 *
 * @filename        wpdk-functions
 *
 */

// -----------------------------------------------------------------------------------------------------------------
// has/is zone
// -----------------------------------------------------------------------------------------------------------------

/**
 * Controlla se una stringa è FALSE
 *
 * @param string $str Stringa da controllare
 *
 * @return bool Restituisce true se la stringa passato non è uguale a '', 'false', '0', 'no', 'n' e 'off'
 */
function wpdk_is_bool( $str ) {

    return !in_array( strtolower( $str ), array( '', 'false', '0', 'no', 'n', 'off', null ) );
}

/**
 * Restituisce true se il parametro è infinito
 *
 * @param float|string $value Valore da controllare
 *
 * @return bool True se $value è uguale a INF (php) o a WPDK_MATH_INFINITY
 */
function wpdk_is_infinity( $value ) {
    return ( is_infinite( floatval( $value ) ) || ( is_string( $value ) && $value == WPDK_MATH_INFINITY ) );
}

/**
 * Restituisce true se siamo in chiamata da Ajax. Utilizzato per essere sicuri che ci troviamo a rispondere ad una
 * chiamata che provenie dall'oggetto HTTPRequest e, in particolare, che la define di WordPress DOING_AJAX è definita.
 *
 * @return bool True se chiamata da Ajax, altrimenti false
 */
function wpdk_is_ajax() {
    if ( defined( 'DOING_AJAX' ) ) {
        return true;
    }
    if ( isset( $_SERVER['HTTP_X_REQUESTED_WITH'] ) &&
        strtolower( $_SERVER['HTTP_X_REQUESTED_WITH'] ) == 'xmlhttprequest'
    ) {
        return true;
    } else {
        return false;
    }
}


/**
 * Restituisce true se la pagina attuale è figlia di un'altra.
 *
 * @param string $parent ID, Titolo o Slug della pagina
 *
 * @return bool True se la pagina attuale è figlia di un'altra
 */
function wpdk_is_child( $parent = '' ) {
    global $post;

    $parent_obj   = get_page( $post->post_parent, ARRAY_A );
    $parent       = (string)$parent;
    $parent_array = (array)$parent;

    if ( $parent_obj && isset( $parent_obj['ID'] ) ) {
        if ( in_array( (string)$parent_obj['ID'], $parent_array ) ) {
            return true;
        } elseif ( in_array( (string)$parent_obj['post_title'], $parent_array ) ) {
            return true;
        } elseif ( in_array( (string)$parent_obj['post_name'], $parent_array ) ) {
            return true;
        } else {
            return false;
        }
    }
    return false;
}

/**
 * Restituisce le informazioni su una image size registrata
 *
 * @param string $name ID delle dimensioni
 *
 * @return bool|array Restituisce le informazioni su una image size registrata
 */
function wpdk_get_image_size( $name ) {
    global $_wp_additional_image_sizes;
    if ( isset( $_wp_additional_image_sizes[$name] ) ) {
        return $_wp_additional_image_sizes[$name];
    }
    return false;
}

/**
 * Commodity for extends checked() WordPress function with array check
 *
 * @param string|array    $haystack Single value or array
 * @param mixed           $current Value to match
 *
 * @return void
 */
function wpdk_checked( $haystack, $current ) {
    if ( is_array( $haystack ) && in_array( $current, $haystack ) ) {
        $current = $haystack = 1;
    }
    checked( $haystack, $current );
}

/**
 * Restituisce il contenuto di una pagina recuperata dal sul slug.
 *
 * @todo Il recuper dell'id per la compatibilità WPML è del tutto simile a quello usato in wpdk_permalink_page_with_slug
 *       si potrebbe portare fuori visto che sarebbe anche il caso di creare una funzione generica al riguardo, tipo una:
 *       wpdk_page_with_slug() che restituisca appunto l'oggetto da cui recuperare tutto quello che serve.
 *
 * @param string $slug             Slug della pagina
 * @param string $post_type        Tipo di post - se custom post ad esempio
 * @param string $alternative_slug Eventuale slug alternativo per supporto multi-languale
 *
 * @note WPML compatible
 *
 * @return string Contenuto della pagina, di solito text/html. False se errore
 */
function wpdk_content_page_with_slug( $slug, $post_type, $alternative_slug = '' ) {
    global $wpdb;

    $page = get_page_by_path( $slug, OBJECT, $post_type );

    if ( is_null( $page ) ) {
        $page = get_page_by_path( $alternative_slug, OBJECT, $post_type );

        if ( is_null( $page ) ) {
            /* WPML? */
            if ( function_exists( 'icl_object_id' ) ) {
                $sql = <<< SQL
SELECT ID FROM {$wpdb->posts}
WHERE post_name = '{$slug}'
AND post_type = '{$post_type}'
AND post_status = 'publish'
SQL;
                $id  = $wpdb->get_var( $sql );
                $id  = icl_object_id( $id, $post_type, true );
            } else {
                return false;
            }
        } else {
            $id = $page->ID;
        }

        $page = get_post( $id );
    }

    return apply_filters( "the_content", $page->post_content );
}

/**
 * Restituisce il permalink di una pagina (post, page, custom post) partendo dal suo slug.
 * Questa funzione è compatibile con WPML.
 *
 * @param string $slug      Slug della pagina
 * @param string $post_type Tipo di post. Default 'page'
 *
 * @note WPML compatible
 *
 * @return mixed|string Restituisce il permalink trailing, o false se non esiste
 */
function wpdk_permalink_page_with_slug( $slug, $post_type = 'page' ) {
    global $wpdb;

    /* Cerco la pagina. */
    $page = get_page_by_path( $slug, OBJECT, $post_type );

    /* Se non la trovo, prima di restituire null eseguo un controllo per WPML. */
    if ( is_null( $page ) ) {

        /* WPML? */
        if ( function_exists( 'icl_object_id' ) ) {
            $sql = <<< SQL
SELECT ID FROM {$wpdb->posts}
WHERE post_name = '{$slug}'
AND post_type = '{$post_type}'
AND post_status = 'publish'
SQL;
            $id  = $wpdb->get_var( $sql );
            $id  = icl_object_id( $id, $post_type, true );
        } else {
            return false;
        }
    } else {
        $id = $page->ID;
    }

    $permalink = get_permalink( $id );

    return trailingslashit( $permalink );
}