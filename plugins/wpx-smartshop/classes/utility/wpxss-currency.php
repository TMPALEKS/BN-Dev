<?php
/**
 * @class              WPXSmartShopCurrency
 *
 * @description        Classe dedicata alla gestione della moneta, dalla parte database ai sistemi di conversione
 *
 * @package            wpx SmartShop
 * @subpackage         utility
 * @author             =undo= <g.fazioli@wpxtre.me>
 * @copyright          Copyright (C)2012 wpXtreme, Inc.
 * @created            05/01/12
 * @version            1.0
 *
 */

class WPXSmartShopCurrency {

    // -----------------------------------------------------------------------------------------------------------------
    // Sanitize
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Restituisce il valore (decimale in caso) di una stringa/intero
     *
     * @static
     *
     * @param $value
     *
     * @retval float
     *
     * @todo Questa è da sistemare. Bosgna offrire lato backend la possibilità di scegliere la modalità di edit e la
     * modalità di visulizzazione delle currency, ad esempio: in edit vedo '1,345.55' e in visualizzazione vedo '1.345,55'
     */
    public static function sanitizeCurrency( $value ) {
        $value = trim( $value );
        /* @todo La riga sotto non va bene */
        $value = str_replace( WPXSMARTSHOP_CURRENCY_DECIMAL_POINT, '', $value );
        $value = str_replace( ',', '.', $value );
        return doubleval( $value );
    }

    // -----------------------------------------------------------------------------------------------------------------
    // is/has zone
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Restituisce true se una stringa termina con il carattere '%'
     *
     * @static
     *
     * @param $value
     *
     * @retval bool
     */
    public static function isPercentage( $value ) {
        return ( substr( trim( $value ), -1, 1 ) == '%' );
    }

    // -----------------------------------------------------------------------------------------------------------------
    // Format
    // -----------------------------------------------------------------------------------------------------------------


    public static function currencyHTML( $price, $class = '' ) {

        if ( empty( $price ) ) {
            $result = sprintf( '<span class="wpss-price-html wpss-price-html-free %s">%s</span>', $class, __( 'Free', WPXSMARTSHOP_TEXTDOMAIN ) );
        } else {

            $products_settings = WPXSmartShop::settings()->products();

            $currency    = sprintf( '<span class="wpss-price-currency-symbol">%s</span>', WPXSmartShopCurrency::currencySymbol() );
            $information = sprintf( '<span class="wpss-price-vat-information">%s</span>', __( '(VAT excluded)', WPXSMARTSHOP_TEXTDOMAIN ) );

            if ( !wpdk_is_bool( $products_settings['price_display_currency_simbol'] ) ) {
                $currency = '';
            }

            /* Devo includere l'iva? */
            if ( WPXSmartShop::settings()->productPriceDisplayWithVat() &&
                !WPXSmartShop::settings()->product_price_includes_vat()
            ) {
                $vat       = WPSmartShopShippingCountries::vatShop();
                $vat_value = ( $price * $vat ) / 100;
                $price += $vat_value;
                $information = sprintf( '<span class="wpss-price-vat-information">%s</span>', __( '(VAT included)', WPXSMARTSHOP_TEXTDOMAIN ) );
            } elseif ( WPXSmartShop::settings()->productPriceDisplayWithVat() &&
                WPXSmartShop::settings()->product_price_includes_vat()
            ) {
                $information = sprintf( '<span class="wpss-price-vat-information">%s</span>', __( '(VAT included)', WPXSMARTSHOP_TEXTDOMAIN ) );
            } elseif ( !WPXSmartShop::settings()->productPriceDisplayWithVat() &&
                WPXSmartShop::settings()->product_price_includes_vat()
            ) {
                $vat         = WPSmartShopShippingCountries::vatShop();
                $price       = ( $price * 100 ) / ( 100 + $vat );
                $information = sprintf( '<span class="wpss-price-vat-information">%s</span>', __( '(VAT excluded)', WPXSMARTSHOP_TEXTDOMAIN ) );
            }

            /* Devo indicare le informazioni iva accanto al prezzo? */
            if ( !WPXSmartShop::settings()->productPriceWithVatInformation() ) {
                $information = '';
            }

            /* Riformatto il prezzo */
            $price = WPXSmartShopCurrency::formatCurrency( $price );

            /* Parto dal presupposto che il prezzo abbia la virgola: 1.234,77 */
            $price_parts = explode( ',', $price );

            $result = sprintf( '<span class="wpss-price-html %s">%s,<sup>%s</sup>%s</span> %s', $class, $price_parts[0], $price_parts[1], $currency, $information );
        }
        return $result;
    }

    /**
     * Restituisce il simbolo della moneta in base alle impostazioni e altri parametri di sistema. Comunque sia sono
     * informazioni presenti nella tabella sul database.
     *
     * @static
     *
     * @param null $overwrite
     *
     * @retval null|string
     */
    public static function currencySymbol( $overwrite = null ) {
        if ( !is_null( $overwrite ) ) {
            return $overwrite;
        }
        $symbol = WPSmartShopShippingCountries::currencySymbolShop();
        return $symbol;
    }

    public static function currencySymbolHTML( $overwrite = null ) {
        if ( !is_null( $overwrite ) ) {
            return $overwrite;
        }
        $symbol = WPSmartShopShippingCountries::currencySymbolHTMLShop();
        return $symbol;
    }

    public static function currencyName( $overwrite = null ) {
        if ( !is_null( $overwrite ) ) {
            return $overwrite;
        }
        $symbol = WPSmartShopShippingCountries::currencyShop();
        return $symbol;
    }

    /**
     * Metodo di comodità per formattare le valute in modo che vengano 2 cifre decimali e il punto o la virgola in
     * funzione della modalità di editing o meno.
     * Quando un valore dev'essere mostrato in un text box, ad esempio, $foredit deve essere a true,
     * in modo che la virgola venga rappresentata con il punto ".".
     * Quando il valore è visualizzato invece, è più corretto mostrarlo con la virgola, tipo 10,33 Euro.
     *
     * @static
     *
     * @param int|float $value   Valore/valuta
     * @param bool      $foredit Se true usa il carattere punto (.) per i decimale, altrimenti la virgola (,)
     *
     * @retval string Valore/valuta formattato
     */
    public static function formatCurrency( $value, $foredit = false ) {
        if ( !is_numeric( $value ) ) {
            $value = str_replace( WPXSMARTSHOP_CURRENCY_THOUSANDS_SEPARATOR, '', $value );
            $value = str_replace( WPXSMARTSHOP_CURRENCY_DECIMAL_POINT, '.', $value );
        }

        if ( $foredit ) {
            return number_format( doubleval( $value ), 2 );
        } else {
            return number_format( doubleval( $value ), 2, WPXSMARTSHOP_CURRENCY_DECIMAL_POINT, WPXSMARTSHOP_CURRENCY_THOUSANDS_SEPARATOR );
        }
    }

    /**
     * Metodo di comodità per formattare le percentuali in modo che vengano 4 cifre decimali e il punto o la virgola in
     * funzione della modalità di editing o meno.
     * Quando un valore dev'essere mostrato in un text box, ad esempio, $foredit deve essere a true,
     * in modo che la virgola venga rappresentata con il punto ".".
     * Quando il valore è visualizzato invece, è più corretto mostrarlo con la virgola, tipo 10,3344 %.
     *
     * @static
     *
     * @param int|float $value   Percentuale
     * @param bool      $foredit Se true usa il carattere punto (.) per i decimale, altrimenti la virgola (,)
     *
     * @retval string Valore/valuta formattato
     */
    public static function formatPercentage( $value, $foredit = false ) {

        if ( !is_numeric( $value ) ) {
            $value = str_replace( WPXSMARTSHOP_CURRENCY_THOUSANDS_SEPARATOR, '', $value );
            $value = str_replace( WPXSMARTSHOP_CURRENCY_DECIMAL_POINT, '.', $value );
        }

        if ( $foredit ) {
            return number_format( doubleval( $value ), 4 );
        } else {
            return number_format( doubleval( $value ), 4, WPXSMARTSHOP_CURRENCY_DECIMAL_POINT, WPXSMARTSHOP_CURRENCY_THOUSANDS_SEPARATOR );
        }
    }
}
