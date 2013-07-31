<?php
/**
 * @class              WPXSmartShopTransient
 *
 * @description        Questa classe si occupa di gestire tutti quei dati transitori che devono essere condivisi con
 *                     tutte le sessioni utente e non. Cioè dati volatili temporanee condivisi però da tutti.
 *                     Ad esempio quando un utente usa un codice Coupon questo dovrebbe risultare non più disponibile ad
 *                     altri, anche se ancora non è stato confermato. Questa classe aiuta appunto a gestire dati
 *                     transitori di questo tipo.
 *
 * @package            wpx SmartShop
 * @subpackage         core
 * @author             =undo= <g.fazioli@undolog.com>, <g.fazioli@wpxtre.me>
 * @copyright          Copyright (c) 2012 wpXtreme, Inc.
 * @link               http://wpxtre.me
 * @created            04/05/12
 * @version            1.0.0
 *
 * @filename           wpxss-transient
 *
 */

class WPXSmartShopTransient {

    /* 5 minuti di blocco coupons */
    const COUPONS_TIMEOUT = 300;

    // -----------------------------------------------------------------------------------------------------------------
    // Coupons
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Array con l'elenco dei coupon attualmente 'prenotati'
     *
     * @package            wpx SmartShop
     * @subpackage         WPXSmartShopTransient
     * @since              1.0
     *
     * @static
     *
     * @param array $coupons Se null restituisce l'elenco dei coupon prenotati
     *
     * @retval bool|mixed Elenco dei coupon prenotati o false se non esiste la lista
     */
    public static function coupons( $coupons = null ) {
        if ( is_null( $coupons ) ) {
            $transient = get_transient( 'wpss_coupons_in_cart' );
            if ( $transient ) {
                return unserialize( $transient );
            }
            return false;
        } else {
            delete_transient( 'wpss_coupons_in_cart' );
            set_transient( 'wpss_coupons_in_cart', serialize( $coupons ), self::COUPONS_TIMEOUT );
        }
        return $coupons;
    }



}