<?php
/**
 * @class              WPXSmartShopWPML
 *
 * @description        Gestisce l'integrazione con il plugin WPML™ per il multi-lingua.
 *                     Contiene una serie di utilità comode da usare velocemente per eseguire la converse da/a gli id
 *                     originali. Questo è utile in quanto un Post (di tipo qualsiasi) può avere una serie di
 *                     informazioni aggiuntive che non devono essere necessariamente tradotte (vedi post meta) e che
 *                     quindi i diversi linguaggi posso tranquillamente condividere. Ne deriva che dato un ID
 *                     (tradotto) per recuperare i suo post meta - ad esempio - è necessario ottenere l'id originale
 *                     a partire da quest'ultimo.
 *
 * @package            wpx SmartShop
 * @subpackage         core
 * @author             =undo= <g.fazioli@undolog.com>, <g.fazioli@wpxtre.me>
 * @copyright          Copyright (c) 2012 wpXtreme, Inc.
 * @created            21/02/12
 * @version            1.0
 *
 */

/* I post, compresi quelli custom, esistono in primis in */
define( 'WPXSMARTSHOP_WPML_DEFAULT_LANGUAGE', 'it' );

class WPXSmartShopWPML {

    // -----------------------------------------------------------------------------------------------------------------
    // WPML Integration
    // -----------------------------------------------------------------------------------------------------------------

    public static function requestIn( $language ) {

    }

    /**
     * Restituisce l'ID del post originale nella lingua base (WPXSMARTSHOP_WPML_DEFAULT_LANGUAGE) se l'ID
     * passato è, ad esempio, in altro linguaggio.
     *
     * @static
     *
     * @param int  $id Post Custom Product: WPXSMARTSHOP_PRODUCT_POST_KEY
     * @param bool $return_original_if_missing
     *
     * @retval int ID post originale
     */
    public static function originalProductID( $id, $return_original_if_missing = true ) {
        if ( defined( 'ICL_LANGUAGE_CODE' ) ) {
            $id = icl_object_id( $id, WPXSMARTSHOP_PRODUCT_POST_KEY, $return_original_if_missing, WPXSMARTSHOP_WPML_DEFAULT_LANGUAGE );
        }
        return absint( $id );
    }

    /**
     * Restituisce l'ID del post originale nella lingua base (WPXSMARTSHOP_WPML_DEFAULT_LANGUAGE) se l'ID
     * passato è, ad esempio, in altro linguaggio.
     *
     * @example            Se a questo metodo viene passato un array(10,13,14) e i post 10 e 14 sono tradotti con id
     *                     pari a 77, 78 ecco cosa accade a seconda dei parametri d'ingresso:
     *
     * ::IDsProductIn( array(10,13,14), true, null) = array(77,13,78)
     * ::IDsProductIn( array(10,13,14), false, null) = array(77,78)
     * ::IDsProductIn( array(10,13,14), true, WPXSMARTSHOP_WPML_DEFAULT_LANGUAGE) = array(10,13,14)
     *
     * @static
     *
     * @param array $ids Array con la lista degli id da convertire
     * @param bool  $return_original_if_missing
     *
     * @param null  $in  Se null restituisce gli id tradotti. Se WPXSMARTSHOP_WPML_DEFAULT_LANGUAGE quelli
     *                   della lingua base.
     *
     * @retval array Lista dei nuovi ID
     */
    public static function IDsProductIn( $ids, $return_original_if_missing = true, $in = null ) {
        if ( defined( 'ICL_LANGUAGE_CODE' ) ) {
            $result = array();
            foreach ( $ids as $id ) {
                $temp = icl_object_id( $id, WPXSMARTSHOP_PRODUCT_POST_KEY, $return_original_if_missing, $in );
                if ( !is_null( $temp ) ) {
                    $result[] = $temp;
                }
            }
            return $result;
        }
        return $ids;
    }

    /**
     * Restituisce l'ID del post originale nella lingua base (WPXSMARTSHOP_WPML_DEFAULT_LANGUAGE) se l'ID
     * passato è, ad esempio, in altro linguaggio.
     *
     * @static
     *
     * @param int  $id Post Custom Showcase: kWPSmartShopShowcasePostTypeKey
     * @param bool $return_original_if_missing
     *
     * @retval int ID post originale
     */
    public static function originalShowcaseID( $id, $return_original_if_missing = true ) {
        $result = $id;
        if ( defined( 'ICL_LANGUAGE_CODE' ) ) {
            $result = icl_object_id( $id, kWPSmartShopShowcasePostTypeKey, $return_original_if_missing, WPXSMARTSHOP_WPML_DEFAULT_LANGUAGE );
        }
        return absint( $result );
    }

    // -----------------------------------------------------------------------------------------------------------------
    // has / is zone
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Short-hand per determinare se WPML è installato e attivo
     *
     * @static
     * @retval bool
     */
    public static function isWPLM() {
        return defined( 'ICL_LANGUAGE_CODE' );
    }

    /**
     * Restituisce true se WPML è disabilitato o ci troviamo nella lingua di default
     * (WPXSMARTSHOP_WPML_DEFAULT_LANGUAGE)
     *
     * @static
     * @retval bool
     */
    public static function isDefaultLanguage() {
        if( defined('ICL_LANGUAGE_CODE') ) {
            return ( ICL_LANGUAGE_CODE == WPXSMARTSHOP_WPML_DEFAULT_LANGUAGE );
        }
        return true;
    }
}