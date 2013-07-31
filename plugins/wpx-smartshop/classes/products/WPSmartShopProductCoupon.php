<?php
/**
 * Gestione della creazione di coupon all'acquisto di un prodotto
 *
 * @package            wpx SmartShop
 * @subpackage         WPSmartShopProductCoupon
 * @author             =undo= <g.fazioli@undolog.com>, <g.fazioli@wpxtre.me>
 * @copyright          Copyright (C)2012 wpXtreme, Inc.
 * @created            10/01/12
 * @version            1.0
 *
 */

class WPSmartShopProductCoupon {

    // -----------------------------------------------------------------------------------------------------------------
    // Static values
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Restituice l'array dei campi nello standard SDF
     *
     * @package    wpx SmartShop
     * @subpackage WPSmartShopProductCoupon
     * @since      1.0.0
     *
     * @static
     * @retval array
     */
    public static function fields() {
        global $post;

        /*
         * Gestione delle regole di composizione della parte Coupon
         */

        $coupon_rules = false;
        if ( isset( $post ) ) {
            $coupon_rules      = self::couponRules( $post->ID );
            $id_product        = $coupon_rules['id_product'];
            $id_product_type   = $coupon_rules['id_product_type'];
            $product_title     = WPXSmartShopProduct::title( array( 'id' => $id_product ) );
            $product_type_name = WPSmartShopProductTypeTaxonomy::name( $id_product_type );
        }

        $fields = array(
            __('Enable this feature to create coupons packages when an order (with this product) is confirmed', WPXSMARTSHOP_TEXTDOMAIN ),
            array(
                array(
                    'type'    => WPDK_FORM_FIELD_TYPE_CHECKBOX,
                    'name'    => 'wpss_product_coupon_enabled',
                    'label'   => __('Enabled Coupon creation', WPXSMARTSHOP_TEXTDOMAIN ),
                    'title'   => __('Creates coupons when this product is purchased', WPXSMARTSHOP_TEXTDOMAIN ),
                    'value'   => '1',
                    'checked' => $coupon_rules ? '1' : '',
                )
            ),

            array(
                array(
                    'type'        => WPDK_FORM_FIELD_TYPE_TEXT,
                    'name'        => 'wpss_coupon_uniqcode_prefix',
                    'label'       => __( 'Custom Unique Code', WPXSMARTSHOP_TEXTDOMAIN ),
                    'value'       => $coupon_rules ? $coupon_rules['wpss_coupon_uniqcode_prefix'] : '',
                    'size'        => 8,
                    'placeholder' => __( 'Prefix', WPXSMARTSHOP_TEXTDOMAIN ),
                ),
                array(
                    'type'        => WPDK_FORM_FIELD_TYPE_TEXT,
                    'name'        => 'wpss_coupon_uniqcode_postfix',
                    'size'        => 8,
                    'placeholder' => __( 'Postfix', WPXSMARTSHOP_TEXTDOMAIN ),
                    'value'       => $coupon_rules ? $coupon_rules['wpss_coupon_uniqcode_postfix'] : '',
                    'title'       => __( 'Usually will be Smart Shop to create coupon uniqcode. You can set prefix and postfix. Left empty for automatic unique code or enter a your custom code.', WPXSMARTSHOP_TEXTDOMAIN )
                ),
            ),

            array(
                array(
                    'type'   => WPDK_FORM_FIELD_TYPE_NUMBER,
                    'name'   => 'wpss_coupon_value',
                    'label'  => __( 'Value', WPXSMARTSHOP_TEXTDOMAIN ),
                    'value'  => $coupon_rules ? $coupon_rules['wpss_coupon_value'] : '',
                    'title'   => __( 'This is a numeric value for currency money. Append % for percentage. For apply Free to a product insert 100%', WPXSMARTSHOP_TEXTDOMAIN )
                ),
                array(
                    'type'   => WPDK_FORM_FIELD_TYPE_NUMBER,
                    'name'   => 'wpss_coupon_limit_product_qty',
                    'label'  => __( 'Limit', WPXSMARTSHOP_TEXTDOMAIN ),
                    'value'  => $coupon_rules ? $coupon_rules['wpss_coupon_limit_product_qty'] : '',
                    'title'   => __( 'Limit this coupon for product', WPXSMARTSHOP_TEXTDOMAIN ),
                )
            ),
            array(
                array(
                    'type'    => WPDK_FORM_FIELD_TYPE_NUMBER,
                    'name'    => 'wpss_coupon_qty',
                    'label'   => __('Quantity', WPXSMARTSHOP_TEXTDOMAIN ),
                    'value'   => $coupon_rules ? $coupon_rules['wpss_coupon_qty'] : '',
                    'title'    => __('Coupon number to generate', WPXSMARTSHOP_TEXTDOMAIN )
                ),
            ),
            array(
                array(
                    'type'    => WPDK_FORM_FIELD_TYPE_CHECKBOX,
                    'name'    => 'wpss_coupon_same_uniqcode',
                    'label'   => __('Generate an only one unique code', WPXSMARTSHOP_TEXTDOMAIN ),
                    'value'   => '1',
                    'checked' => $coupon_rules ? $coupon_rules['wpss_coupon_same_uniqcode'] : '',
                    'title'    => __('Turn on this flag to create more coupons with the same unique code', WPXSMARTSHOP_TEXTDOMAIN )
                )
            ),
// Not used yet            
//            array(
//                array(
//                    'type'    => 'checkbox',
//                    'name'    => 'unlimited',
//                    'label'   => __('Makes quantity unlimited', WPXSMARTSHOP_TEXTDOMAIN ),
//                    'value'   => 1,
//                    'checked' => $coupon_rules ? $coupon_rules['unlimited'] : '',
//                    'title'    => __('If you check this flag the coupons will be used to up "date to" value if present.', WPXSMARTSHOP_TEXTDOMAIN )
//                )
//            ),
            __('Restrict your coupon usage for', WPXSMARTSHOP_TEXTDOMAIN ),
            array(
                array(
                    'type'      => WPDK_FORM_FIELD_TYPE_RADIO,
                    'name'      => 'wpss_coupon_restrict_product',
                    'id'        => 'restrict_none',
                    'class'     => 'wpss-coupon-restrict-product',
                    'checked'   => $coupon_rules ? ($id_product == 0 && $id_product_type == 0) : 'none',
                    'value'     => 'none',
                    'label'     => __('None', WPXSMARTSHOP_TEXTDOMAIN )
                )
            ),
            array(
                array(
                    'type'     => WPDK_FORM_FIELD_TYPE_RADIO,
                    'name'     => 'wpss_coupon_restrict_product',
                    'id'       => 'restrict_product',
                    'class'    => 'wpss-coupon-restrict-product',
                    'checked'  => $coupon_rules ? ($id_product > 0) : '',
                    'value'    => 'product',
                    'label'    => __('Product', WPXSMARTSHOP_TEXTDOMAIN )
                ),
                array(
                    'type'     => WPDK_FORM_FIELD_TYPE_CHOOSE,
                    'name'     => 'id_product',
                    'value'    => $coupon_rules ? $id_product : '',
                    'label'    => ($coupon_rules['wpss_coupon_restrict_product'] == 'product') ? $product_title : ''
                )
            ),
            array(
                array(
                    'type'     => WPDK_FORM_FIELD_TYPE_RADIO,
                    'name'     => 'wpss_coupon_restrict_product',
                    'id'       => 'restrict_product_type',
                    'class'    => 'wpss-coupon-restrict-product',
                    'checked'  => $coupon_rules ? ($id_product_type > 0) : '',
                    'value'    => 'product_type',
                    'label'    => __('Product type', WPXSMARTSHOP_TEXTDOMAIN )
                ),
                array(
                    'type'     => WPDK_FORM_FIELD_TYPE_CHOOSE,
                    'name'     => 'id_product_type',
                    'value'    => $coupon_rules ? $id_product_type: '',
                    'label'    => ($coupon_rules['wpss_coupon_restrict_product'] == 'product_type') ? $product_type_name : ''
                )
            ),
            array(
                array(
                    'type'     => WPDK_FORM_FIELD_TYPE_CHECKBOX,
                    'name'     => 'wpss_coupon_restrict_user',
                    'label'    => __('Buyer', WPXSMARTSHOP_TEXTDOMAIN ),
                    'value'    => 'y',
                    'checked'  => $coupon_rules ? ($coupon_rules['wpss_coupon_restrict_user'] == 'y') : '',
                    'title'     => __('Select this option to constrain the use of coupons only to the user who has purchased (buyer)', WPXSMARTSHOP_TEXTDOMAIN )
                )
            ),
            array(
                array(
                    'type'       => WPDK_FORM_FIELD_TYPE_NUMBER,
                    'name'       => 'wpss_coupon_durability',
                    'label'      => __( 'Keep the coupons valid', WPXSMARTSHOP_TEXTDOMAIN ),
                    'value'      => $coupon_rules ? $coupon_rules['wpss_coupon_durability'] : '',
                ),
                array(
                    'type'      => WPDK_FORM_FIELD_TYPE_SELECT,
                    'name'      => 'wpss_coupon_durability_type',
                    'afterlabel'=> '',
                    'options'   => self::durabilityType(),
                    'append'    => __( 'from purchase', WPXSMARTSHOP_TEXTDOMAIN ),
                    'value'     => $coupon_rules ? $coupon_rules['wpss_coupon_durability_type'] : '',
                ),
            )
        );
        return $fields;
    }

    /**
     * Restituisce l'array con le regole per la creazione dei coupon scelte in fase di creazione prodotto
     *
     * @static
     * @uses       hasCoupon()
     *
     * @param $id_product ID del prodotto
     *
     * @retval bool|array Restituisce le regole per la crezione del coupon o false se il prodotto indicato non contiene
     *             nessuna regola di creazione coupon
     */
    public static function couponRules( $id_product ) {
        $id_product = WPXSmartShopWPML::originalProductID( $id_product );
        if ( self::hasCoupon( $id_product ) ) {
            $coupon_rules = unserialize( get_post_meta( $id_product, 'wpss_product_coupon_rules', true ) );
            return $coupon_rules;
        }
        return false;
    }

    /**
     * Usato per popolare il combo menu della durata di una serie di coupon
     *
     * @package    wpx SmartShop
     * @subpackage WPSmartShopProductCoupon
     * @since      1.0.0
     *
     * @static
     * @retval array
     *   Elenco del tipo di durata: minuti, giorno, mese, anno
     */
    private static function durabilityType() {
        $result = array(
            'minutes'   => __('Minutes', WPXSMARTSHOP_TEXTDOMAIN ),
            'days'      => __('Days', WPXSMARTSHOP_TEXTDOMAIN ),
            'months'    => __('Months', WPXSMARTSHOP_TEXTDOMAIN ),
            'years'     => __('Years', WPXSMARTSHOP_TEXTDOMAIN ),
        );
        return $result;
    }

    // -----------------------------------------------------------------------------------------------------------------
    // has/is zone
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Controlla se un prodotto ha delle regole di creazione di coupon
     *
     * @package    wpx SmartShop
     * @subpackage WPSmartShopProductCoupon
     * @since      1.0.0
     *
     * @static
     * @param $id
     *   id del prodotto
     * @retval bool
     */
    public static function hasCoupon( $id ) {
        $id_product = WPXSmartShopWPML::originalProductID( $id );
        $coupon     = get_post_meta( $id_product, 'wpss_product_coupon_rules', true );
        if ( $coupon ) {
            return true;
        }
        return false;
    }

    /**
     * Recupera, da un ordine confermato, la lista dei prodotti acquistati.
     * Se uno o più prodotti risultano essere di tipo coupon, questi ultimi vengono generati in base alle loro regole.
     *
     * @static
     * @param $order Record dell'ordine confermato
     *
     * @retval array Elenco dei coupons creati per id prodotto
     */
    public static function couponsWithOrder( $order ) {
        $result = array();

        /* Eleneco dei prodotti per quest'ordine */
        $products = WPXSmartShopStats::productsWithOrderID( $order->id );

        foreach ( $products as $product ) {
            $id_product = $product['id_product'];
            /* Se il prodotto deve generare coupon, procede. */
            if ( ( $coupon_rules = self::couponRules( $id_product ) ) ) {
                $coupon_rules['id_product_maker'] = $id_product;
                $coupon_rules['id_user_maker']    = $order->id_user_order;

                /* Durability da ora */

                /* Controllo durability - da product */
                if ( isset( $coupon_rules['wpss_coupon_durability'] ) ) {
                    $coupon_rules['wpss_coupon_date_from'] = date( __( 'm/d/Y H:i', WPXSMARTSHOP_TEXTDOMAIN ) );
                    $date_start                            = WPDKDateTime::formatFromFormat( $coupon_rules['wpss_coupon_date_from'], __( 'm/d/Y H:i', WPXSMARTSHOP_TEXTDOMAIN ), MYSQL_DATE_TIME );
                    $date_expire                           = WPDKDateTime::expirationDate( $date_start, $coupon_rules['wpss_coupon_durability'], $coupon_rules['wpss_coupon_durability_type'] );
                    $coupon_rules['wpss_coupon_date_to']   = date( __( 'm/d/Y H:i', WPXSMARTSHOP_TEXTDOMAIN ), $date_expire );
                }

                /* Se ne ho acquistati 2, genero 2 set di coupon, e so on... */
                $qty = absint( $product['qty'] );
                for ( $i = 0; $i < $qty; $i++ ) {
                    $result[] = array(
                        'id_product' => $id_product,
                        'coupons'    => WPXSmartShopCoupons::create( $coupon_rules )
                    );
                }
            }
        }
        return $result;
    }

}
