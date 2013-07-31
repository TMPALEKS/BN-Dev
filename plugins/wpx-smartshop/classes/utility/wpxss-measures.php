<?php
/**
 * @class              WPXSmartShopMeasures
 *
 * @description        Questa classe si occupa di gestire le unità di misura all'interno di smart shop con notevoli
 *                     improvement futuri
 *
 * @package            wpx SmartShop
 * @subpackage         utility
 * @author             =undo= <g.fazioli@undolog.com>, <g.fazioli@wpxtre.me>
 * @copyright          Copyright (c) 2012 wpXtreme, Inc.
 * @link               http://wpxtre.me
 * @created            09/02/12
 * @version            1.0.0
 *
 * @filename           wpxss-measures
 *
 */

class WPXSmartShopMeasures {

    // -----------------------------------------------------------------------------------------------------------------
    // Weight
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * @static
     * @retval mixed
     */
    public static function weightSymbol() {
        return WPXSmartShop::settings()->measures_weight();
    }

    // -----------------------------------------------------------------------------------------------------------------
    // Sizes
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * @static
     * @retval mixed
     */
    public static function sizeSymbol() {
        return WPXSmartShop::settings()->measures_size();
    }


    // -----------------------------------------------------------------------------------------------------------------
    // Volumes
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * @static
     * @retval mixed
     */
    public static function volumeSymbol() {
        return WPXSmartShop::settings()->measures_volume();
    }
}
