<?php
/**
 * Manage plugin options
 *
 * @package            wpx SmartShop
 * @subpackage         WPSmartShopSettings
 * @author             =undo= <g.fazioli@wpxtre.me>
 * @copyright          Copyright (c) 2012 wpXtreme, Inc.
 * @created            23/01/12
 * @version            1.0.0
 *
 */

class WPSmartShopSettings extends WPDKSettings {
    
    // -----------------------------------------------------------------------------------------------------------------
    // Init
    // -----------------------------------------------------------------------------------------------------------------

    /// Construct
    function __construct() {
        parent::__construct( 'wpx-smartshop', 'settings' );
    }
    
    // -----------------------------------------------------------------------------------------------------------------
    // Entry point
    // -----------------------------------------------------------------------------------------------------------------

    function general( $values = null ) {
        if ( is_null( $values ) ) {
            return $this->settings( 'general' );
        } else {
            $this->settings( 'general', $values );
        }
    }    
    
    function wp_integration( $values = null ) {
        if ( is_null( $values ) ) {
            return $this->settings( 'wp_integration' );
        } else {
            $this->settings( 'wp_integration', $values );
        }
    }
        
    function products( $values = null ) {
        if ( is_null( $values ) ) {
            return $this->settings( 'products' );
        } else {
            $this->settings( 'products', $values );
        }
    }
        
    function orders( $values = null ) {
        if ( is_null( $values ) ) {
            return $this->settings( 'orders' );
        } else {
            $this->settings( 'orders', $values );
        }
    }
            
    function shipments( $values = null ) {
        if ( is_null( $values ) ) {
            return $this->settings( 'shipments' );
        } else {
            $this->settings( 'shipments', $values );
        }
    }
            
    function payment_gateways( $values = null ) {
        if ( is_null( $values ) ) {
            return $this->settings( 'payment_gateways' );
        } else {
            $this->settings( 'payment_gateways', $values );
        }
    }
                
    function showcase( $values = null ) {
        if ( is_null( $values ) ) {
            return $this->settings( 'showcase' );
        } else {
            $this->settings( 'showcase', $values );
        }
    }        
    
    function product_card( $values = null ) {
        if ( is_null( $values ) ) {
            return $this->settings( 'product_card' );
        } else {
            $this->settings( 'product_card', $values );
        }
    }

    // -----------------------------------------------------------------------------------------------------------------
    // Static values
    // -----------------------------------------------------------------------------------------------------------------

    /// Return the default options array
    /**
     * Restituisce le impostazioni del Plugin di default. Qui abbiamo una fotografia di tutte le impostazioni di
     * Smart Shop. L'alberatura qui rappresentata viene comunque memorizzata (in forma serializzata) nel database. La
     * suddivisione garantisce che quando l'utente edita una sezione, solo i dati di quella sezione vengono modificati
     * mentre gli atri rimangono quelli presenti nel database.
     *
     * @retval array
     */
    function defaultOptions() {
        $defaults = array(
            'version'   => WPXSMARTSHOP_VERSION,

            'settings'  => array(

                'general'              => array(
                    'shop_open'          => 'n',
                    'shop_name'          => '',
                    'shop_logo'          => '',
                    'shop_address'       => '',
                    'shop_country'       => '',
                    'shop_email'         => '',
                    'shop_info'          => '',

                    'measures_weight'    => 'gr',
                    'measures_size'      => 'mm',
                    'measures_volume'    => 'm'
                ),

                'wp_integration'       => array(
                    'checkout_permalink'                         => __( 'checkout', WPXSMARTSHOP_TEXTDOMAIN ),
                    'payment_permalink'                          => __( 'payment', WPXSMARTSHOP_TEXTDOMAIN ),
                    'receipt_permalink'                          => __( 'receipt', WPXSMARTSHOP_TEXTDOMAIN ),
                    'error_permalink'                            => __( 'error', WPXSMARTSHOP_TEXTDOMAIN ),


                    'store_permalink'                             => '',

                    'shopping_cart_display_for_user_logon_only'   => 'y',
                    'shopping_cart_display_empty_button'          => 'y',

                    'product_picker_post_types'                   => null,
                    'product_picker_hide_empty_product_type'      => 'n',
                    'product_picker_number_of_items'              => 10
                ),

                'products'             => array(
                    'tab-price'                     => 'y',
                    // every visible
                    'tab-appearance'                => 'y',
                    'tab-purchasable'               => 'y',
                    'tab-shipping'                  => 'y',
                    'tab-warehouse'                 => 'y',
                    'tab-membership'                => 'y',
                    'tab-coupons'                   => 'y',
                    'tab-digital'                   => 'y',
                    'price_includes_vat'            => 'y',
                    'price_display_with_vat'        => 'y',
                    'price_display_vat_information' => 'y',
                    'price_display_currency_simbol' => 'y',
                ),

                'orders'               => array(
                    'count_pending'             => 'n',
                    'defunct_status'            => WPXSMARTSHOP_ORDER_STATUS_DEFUNCT,
                    'defunct_status_after'      => 1,
                    'defunct_status_after_type' => 'months'
                ),

                'shipments'            => array( 'default_carrier' => '', ),

                'payment_gateways'     => array(
                    'list_enabled'      => WPSmartShopPaymentGateway::listPaymentGateways(),
                    'display_mode'      => 'combo-menu'
                ),

                'showcase'             => array(
                    'theme_page'               => 'page',
                    // page | single | custom
                    'theme_header'             => 'y',
                    'theme_footer'             => 'y',
                    'theme_sidebar'            => 'n',
                    'theme_sidebar_id'         => '',
                    'theme_markup_header'      => base64_encode( WPSmartShopShowcase::markupHeader() ),
                    'theme_markup_footer'      => base64_encode( WPSmartShopShowcase::markupFooter() )
                ),

                'product_card'         => array(
                    'thumbnail'                => 'y',
                    'thumbnail_size'           => kWPSmartShopThumbnailSizeMediumKey,
                    'permalink'                => 'y',
                    'display_permalink_button' => 'y',
                    'price'                    => 'y',
                    'excerpt'                  => 'n',
                    'display_add_to_cart'      => 'n',
                    'product_types'            => 'y',
                    'product_types_tree'       => 'y',
                ),

            ),
        );
        return $defaults;
    }

    // -----------------------------------------------------------------------------------------------------------------
    // Very light utility
    // -----------------------------------------------------------------------------------------------------------------

    function measures_weight() {
        $settings = $this->general();
        return $settings['measures_weight'];
    }

    function measures_size() {
        $settings = $this->general();
        return $settings['measures_size'];
    }

    function measures_volume() {
        $settings = $this->general();
        return $settings['measures_volume'];
    }

    function default_carrier() {
        $settings = $this->shipments();
        return $settings['default_carrier'];
    }

    function checkout_permalink() {
        $settings = $this->wp_integration();
        return $settings['checkout_permalink'];
    }

    function payment_permalink() {
        $settings = $this->wp_integration();
        return $settings['payment_permalink'];
    }

    function receipt_permalink() {
        $settings = $this->wp_integration();
        return $settings['receipt_permalink'];
    }

    function error_permalink() {
        $settings = $this->wp_integration();
        return $settings['error_permalink'];
    }

    function product_price_includes_vat() {
        $settings = $this->products();
        return wpdk_is_bool( $settings['price_includes_vat'] );
    }

    function productPriceDisplayWithVat() {
        $settings = $this->products();
        return wpdk_is_bool( $settings['price_display_with_vat'] );
    }

    function productPriceWithVatInformation() {
        $settings = $this->products();
        return wpdk_is_bool( $settings['price_display_vat_information'] );
    }

    function productPriceWithCurrencySymbol() {
        $settings = $this->products();
        return wpdk_is_bool( $settings['price_display_currency_simbol'] );
    }

    function shopping_cart_display_empty_button() {
        $settings = $this->wp_integration();
        return wpdk_is_bool( $settings['shopping_cart_display_empty_button'] );
    }

    function shopping_cart_display_for_user_logon_only() {
        $settings = $this->wp_integration();
        return wpdk_is_bool( $settings['shopping_cart_display_for_user_logon_only'] );
    }

    function orders_count_pending() {
        $settings = $this->orders();
        return wpdk_is_bool( $settings['count_pending'] );
    }

    function payment_gateways_enabled() {
        $settings = $this->payment_gateways();
        return $settings['list_enabled'];
    }

}
