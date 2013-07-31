<?php
/**
 * @class              WPXSmartShopSummaryOrder
 *
 * @description        Gestisce la parte logica e di visualizzazione del Summary Order, ovvero il sommario dell'ordine
 *
 * @package            wpx SmartShop
 * @subpackage         orders
 * @author             =undo= <g.fazioli@undolog.com>, <g.fazioli@wpxtre.me>
 * @copyright          Copyright (c) 2012 wpXtreme, Inc.
 * @link               http://wpxtre.me
 * @created            13/02/12
 * @version            1.0.0
 *
 * @filename           wpxss-summary-order
 *
 */

class WPXSmartShopSummaryOrder {

    public static $vatPercentage = 0;
    public static $vatValue = 0;
    public static $totalShipping = 0;
    public static $shipping = 0;
    public static $discount = 0;
    public static $coupon = 0;
    public static $subtotal = 0;
    public static $total = 0;
    public static $descriptionPriceRules;

    // -----------------------------------------------------------------------------------------------------------------
    // Static value
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Restituisce l'elenco degli elementi standard (default) presenti nel menu a tendina del pagamento a vista tramite
     * pos o carte.
     *
     * @static
     * @retval array
     */
    public static function arrayCash() {
        $values = array(
            'Cash'  => __( 'Cash', WPXSMARTSHOP_TEXTDOMAIN ),
            'Card'  => __( 'Card', WPXSMARTSHOP_TEXTDOMAIN ),
            'Pos'   => __( 'Pos', WPXSMARTSHOP_TEXTDOMAIN ),
        );
        return $values;
    }


    public static function init() {
        /**
         * @filters
         *
         * @param float $vat Valore dell'IVA (VAT) per questo store
         */
        $vat                 = apply_filters( 'wpss_summary_order_vat', WPSmartShopShippingCountries::vatShop() );
        self::$vatPercentage = floatval( $vat );
    }

    /**
     * Calcola i totali del Summary Order
     *
     * @static
     *
     */
    public static function total() {
        /* Calcolo Totale considerando: IVA, Spedizione, Coupon e sconti vari */
        if( WPXSmartShop::settings()->product_price_includes_vat() ) {
            self::$vatValue = 0;
        } else  {
            self::$vatValue = floatval( self::$subtotal * self::$vatPercentage / 100 );
        }

        /**
         * @filters
         *
         * @param float $shipping Valore della spedizione
         */
        self::$totalShipping = floatval( apply_filters( 'wpss_summary_order_shipping', self::$shipping ) );

        /**
         * @filters
         *
         * @param float $discount Sconto sull'ordine. Default 0
         */
        self::$discount      = floatval( apply_filters( 'wpss_summary_order_discount', 0 ) );
        self::$coupon        = self::applyOrderCoupon( self::$subtotal );
        self::$total         = self::$subtotal + self::$vatValue + self::$shipping - self::$discount - self::$coupon;

        WPXSmartShopSession::orderAmount( self::$total );
        WPXSmartShopSession::orderShipping( self::$totalShipping );
    }

    /**
     * Processa ogni singola riga di prodotto - vedi view controller - calcolando per il prodotto in questione
     * i vari sconti
     *
     * @static
     *
     * @param array $product Prodotto
     * @param       int      Ennesimo valore, somma dei precendeti quantità dello stesso prodotto
     *
     * @retval mixed|void
     */
    public static function processing( $product, $id_product_key, $nth = 0 ) {

        $id_product                  = isset( $product['id_product'] ) ? $product['id_product'] : '';
        $id_variant                  = isset( $product['id_variant'] ) ? $product['id_variant'] : '';
        $qty                         = $product['qty'];
        $qty_product_price           = WPXSmartShopProduct::price( $id_product, $qty, $id_variant, $nth );

        /**
         * @todo Documentare
         *
         * @filters
         */
        $qty_product_price           = apply_filters( 'wpss_summary_order_apply_custom_discount', $qty_product_price, $id_product_key, $qty, $nth );

        self::$descriptionPriceRules = WPXSmartShopProduct::$descriptionPriceRules;

        /* Lo sconto coupon è applicato in base alla quantità di coupon disponibili */
        if ( $qty > 1 ) {
            /* Numero di coupon utilizzabili per il prodotto */
            $available_coupon = 0;
            if ( !empty( $product['ids_coupon'] ) ) {
                $available_coupon = count( $product['ids_coupon'] );
            }

            if ( empty( $available_coupon ) ) {
                /* Se non ci sono coupon, il subtotale rimane lo stesso */
                $row_subtotal = $qty_product_price;
            } else {
                /* Calcolo il numero di prodotti su cui NON applicare il coupon */
                $no_coupon = $qty - $available_coupon;

                if ( $no_coupon <= 0 ) {
                    /* Ho più coupon rispetto ai prodotti che sto acquistando, quindi li prendo tutti scontati, o meglio
                    posso applicare lo sconto del coupon al totale dei prodotti */
                    $row_subtotal = 0;
                    for ( $n = 0; $n < $qty; $n++ ) {
                        $price_for = WPXSmartShopProduct::price( $id_product, 1, $id_variant, $n + $nth );
                        $row_subtotal += WPXSmartShopSession::applyProductCoupon( $price_for, $product );
                    }
                } else {
                    /* Non possiedo abbastanza coupon, quindi acquisto a meno prezzo solo alcuni */
                    $row_subtotal = WPXSmartShopProduct::price( $id_product, $no_coupon, $id_variant, $nth );
                    for ( $n = $no_coupon; $n < $qty; $n++ ) {
                        $price_for = WPXSmartShopProduct::price( $id_product, 1, $id_variant, $nth + $n );
                        $row_subtotal += WPXSmartShopSession::applyProductCoupon( $price_for, $product );
                    }
                }
            }
        } else {
            /* $qty == 1 */
            $row_subtotal = WPXSmartShopSession::applyProductCoupon( $qty_product_price, $product );
        }

        /**
         * Chiamato quando si deve mostrare il totale (prezzo * qty) di ogni singola riga prodotto; utile per
         * applicare sconti custom.
         *
         * @todo Documentare
         *
         * @filters
         *
         * @param float $price      Valore
         * @param int   $id_product ID del prodotto
         */
        $row_subtotal = apply_filters( 'wpss_summary_order_apply_custom_discount_sub_total', $row_subtotal, $id_product_key );

        self::$subtotal += $row_subtotal;

        /**
         * Chiamata per il cacolo sulla spedizione di ogni singolo prodotto
         *
         * @todo Aggiungere variante? Non so se qui è necessario
         *
         * @filters
         *
         * @param float $price      Prezzo aggiuntivo spedizione. Derfault 0
         * @param int   $id_product ID del prodotto
         * @param int   $qty        Quantità
         */
        self::$shipping += floatval( apply_filters( 'wpss_summary_order_product_shipping', 0, $id_product, $qty ) );

        return $row_subtotal;
    }

    // -----------------------------------------------------------------------------------------------------------------
    // Display
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Visualizza il resoconto completo dei prodotti acquistati in forma tabellare modificabile
     *
     * @static
     * @retval bool|WP_Error True se tutto è andato bene, altrimenti viene restituito un oggetto WP_Error
     */
    public static function display() {
        if ( !class_exists( 'WPXSmartShopSummaryOrderViewController' ) ) {
            require_once( 'wpxss-summary-order-viewcontroller.php' );
        }
        return WPXSmartShopSummaryOrderViewController::display();
    }

    /**
     * Restituisce il summary order includendo a bisogno il view controller
     *
     * @static
     * @retval string
     */
    public static function summaryOrder() {
        if ( !class_exists( 'WPXSmartShopSummaryOrderViewController' ) ) {
            require_once( 'wpxss-summary-order-viewcontroller.php' );
        }
        return WPXSmartShopSummaryOrderViewController::summaryOrder();
    }

    /**
     * Restituisce text/HTML della composizione dei prezzi di un prodotto
     *
     * @static
     * @retval string Restituisce text/HTML della composizione dei prezzi di un prodotto
     */
    public static function descriptionPriceRules() {

        /* Rendo umani i codici interni onde evitare di nmostrare -1, -2, etc... */
        $human_rules = array(
            'base_price'                             => apply_filters( 'wpxss_stats_column_price_rule_base_price', __( 'Base price', WPXSMARTSHOP_TEXTDOMAIN ), 'base_price' ),
            kWPSmartShopProductTypeRuleDatePrice     => apply_filters( 'wpxss_stats_column_price_rule_date_range', __( 'Date range', WPXSMARTSHOP_TEXTDOMAIN ), kWPSmartShopProductTypeRuleDatePrice ),
            kWPSmartShopProductTypeRuleOnlinePrice   => apply_filters( 'wpxss_stats_column_price_rule_online', __( 'Online', WPXSMARTSHOP_TEXTDOMAIN ), kWPSmartShopProductTypeRuleOnlinePrice ),
        );

        /* @todo Inserire filtri per personalizzare stringhe */

        $result = '';
        $roles  = WPDKUser::allRoles();
        if ( is_array( self::$descriptionPriceRules ) ) {
            $stack = array();
            foreach ( self::$descriptionPriceRules as $key => $rule ) {

                /* Umanizzo il tipo di sconto */
                if ( isset( $human_rules[$key] ) ) {
                    $human_key = $human_rules[$key];
                } else {
                    $human_key = $roles[$key];
                    $human_key = apply_filters( 'wpxss_stats_column_price_rule', $human_key, $key );
                }

                /* @todo Aggiungere filtro inviando $human_keye $key per ulteriori personalizzazioni */

                $price = WPXSmartShopCurrency::formatCurrency( $rule['price'] ) . WPXSmartShopCurrency::currencySymbol();

                $stack[] = sprintf( __( '<strong>%s</strong> at the price of <strong>%s</strong> as <strong>%s</strong>', WPXSMARTSHOP_TEXTDOMAIN ), $rule['qty'], $price, $human_key );
            }
            $result = sprintf( __( 'You pay <ul><li>%s</li></ul>', WPXSMARTSHOP_TEXTDOMAIN ), join( '</li><li>', $stack ) );
        }
        return $result;
    }


    // -----------------------------------------------------------------------------------------------------------------
    // Orders & Coupons for entire order
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Restituisce l'array delle informazioni sul coupon ordine dove la chiave è il uniqcode e il valore l'id del coupon
     *
     * @static
     * @retval string | array Stringa vuota se il coupon ordine non esiste
     */
    public static function orderCoupon() {
        $result       = '';
        $order_coupon = WPXSmartShopSession::orderCoupon();
        if ( isset( $order_coupon ) ) {
            return $order_coupon;
        }
        return $result;
    }

    /**
     * Calcola ed applica lo sconto di un coupon di tipo ordine
     *
     * @static
     *
     * @param float $subtotal Importo
     *
     * @retval float|int
     */
    private static function applyOrderCoupon( $subtotal ) {
        $result       = 0;
        $order_coupon = self::orderCoupon();
        if ( !empty( $order_coupon ) ) {
            $key    = key( $order_coupon );
            $coupon = WPXSmartShopCoupons::coupon( $order_coupon[$key] );
            if ( !is_null( $coupon ) ) {
                $result = WPXSmartShopCoupons::applyCouponValue( $coupon->value, $subtotal );
            }
        }
        return $result;
    }

    // -----------------------------------------------------------------------------------------------------------------
    // Discount
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Restituisce il codice di sconto applicato ad un prodotto memorizzato nel carello
     *
     * @static
     *
     * @param int $id_product ID prodotto
     *
     * @retval mixed
     */
    public static function productDiscountID( $id_product ) {
        $products = WPXSmartShopSession::products();
        return $products[$id_product]['discountID'];
    }

    /**
     * Aggiorna il codice di sconto per un prodotto
     *
     * @static
     *
     * @param $id_product
     * @param $discountID
     */
    public static function updateProductDiscountID( $id_product, $discountID ) {
        $products                            = WPXSmartShopSession::products();
        $products[$id_product]['discountID'] = $discountID;
        WPXSmartShopSession::products( $products );
    }

}
