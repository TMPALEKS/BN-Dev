<?php
/**
 * Vista per la gestione delle impostazioni sul prodotto.
 *
 * L'appearance è connessa con la gestione delle spedizioni. Vedi anche i meta box del prodotto.
 *
 * @package            wpx SmartShop
 * @subpackage         SettingsProductView
 * @author             =undo= <g.fazioli@undolog.com>, <g.fazioli@wpxtre.me>
 * @copyright          Copyright (c) 2012 wpXtreme, Inc.
 * @link               http://wpxtre.me
 * @created            10/02/12
 * @version            1.0.0
 *
 */

class SettingsProductView extends WPDKSettingsView {
    
    // -----------------------------------------------------------------------------------------------------------------
    // Init
    // -----------------------------------------------------------------------------------------------------------------

    function __construct() {
        $this->key          = 'products';
        $this->title        = __( 'Products', WPXSMARTSHOP_TEXTDOMAIN );
        $this->introduction = __('The product is a core of wpx SmartShop. From this panel settings you can edit all product featured.', WPXSMARTSHOP_TEXTDOMAIN );
        $this->settings     = WPXSmartShop::settings();
    }

    /**
     * Prepara l'array che descrive i campi del form
     *
     * @retval array
     */
    function fields() {

        $values =  WPXSmartShop::settings()->products();

        $fields = array(
            __( 'WordPress integration', WPXSMARTSHOP_TEXTDOMAIN )          => array(
                __( 'Please select the Managment what you want to enable in the add/edit product window.', WPXSMARTSHOP_TEXTDOMAIN ),
                array(
                    array(
                        'type'          => WPDK_FORM_FIELD_TYPE_CHECKBOX,
                        'name'          => 'tab-purchasable',
                        'label'         => __( 'Purchasable', WPXSMARTSHOP_TEXTDOMAIN ),
                        'value'         => 'y',
                        'checked'       => $values ? $values['tab-purchasable'] : ''
                    )
                ),
                array(
                    array(
                        'type'          => WPDK_FORM_FIELD_TYPE_CHECKBOX,
                        'name'          => 'tab-appearance',
                        'label'         => __( 'Appearance & variants (Weight, Colors, ...)', WPXSMARTSHOP_TEXTDOMAIN ),
                        'value'         => 'y',
                        'checked'       => $values ? $values['tab-appearance'] : ''
                    )
                ),
                array(
                    array(
                        'type'          => WPDK_FORM_FIELD_TYPE_CHECKBOX,
                        'name'          => 'tab-shipping',
                        'label'         => __( 'Shipping', WPXSMARTSHOP_TEXTDOMAIN ),
                        'value'         => 'y',
                        'checked'       => $values ? $values['tab-shipping'] : ''
                    )
                ),
                array(
                    array(
                        'type'          => WPDK_FORM_FIELD_TYPE_CHECKBOX,
                        'name'          => 'tab-warehouse',
                        'label'         => __( 'Warehouse (Stock, availability)', WPXSMARTSHOP_TEXTDOMAIN ),
                        'value'         => 'y',
                        'checked'       => $values ? $values['tab-warehouse'] : ''
                    )
                ),
                array(
                    array(
                        'type'          => WPDK_FORM_FIELD_TYPE_CHECKBOX,
                        'name'          => 'tab-membership',
                        'label'         => __( 'Membership (Subscriptions, ability user role)', WPXSMARTSHOP_TEXTDOMAIN ),
                        'value'         => 'y',
                        'checked'       => $values ? $values['tab-membership'] : ''
                    )
                ),
                array(
                    array(
                        'type'          => WPDK_FORM_FIELD_TYPE_CHECKBOX,
                        'name'          => 'tab-coupons',
                        'label'         => __( 'Coupons (Discount, gift, special subscriptions)', WPXSMARTSHOP_TEXTDOMAIN ),
                        'value'         => 'y',
                        'checked'       => $values ? $values['tab-coupons'] : ''
                    )
                ),
                array(
                    array(
                        'type'          => WPDK_FORM_FIELD_TYPE_CHECKBOX,
                        'name'          => 'tab-digital',
                        'label'         => __( 'Digital product', WPXSMARTSHOP_TEXTDOMAIN ),
                        'value'         => 'y',
                        'checked'       => $values ? $values['tab-coupons'] : ''
                    )
                ),
            ),

            __( 'Price rules', WPXSMARTSHOP_TEXTDOMAIN ) => array(
                __( 'Set all price rules', WPXSMARTSHOP_TEXTDOMAIN ),
                array(
                    array(
                        'type'    => WPDK_FORM_FIELD_TYPE_CHECKBOX,
                        'name'    => 'price_includes_vat',
                        'label'   => __( 'Prices includes the VAT', WPXSMARTSHOP_TEXTDOMAIN ),
                        'value'   => 'y',
                        'checked' => $values ? $values['price_includes_vat'] : '',
                        'help'    => __( 'When you enter a price this is VAT included', WPXSMARTSHOP_TEXTDOMAIN )
                    )
                ),
                array(
                    array(
                        'type'    => WPDK_FORM_FIELD_TYPE_CHECKBOX,
                        'name'    => 'price_display_with_vat',
                        'label'   => __( 'Display price with the VAT', WPXSMARTSHOP_TEXTDOMAIN ),
                        'value'   => 'y',
                        'checked' => $values ? $values['price_display_with_vat'] : '',
                        'help'    => __( 'Display the price with or without the VAT. Be ware to setting above', WPXSMARTSHOP_TEXTDOMAIN )
                    )
                ),
                array(
                    array(
                        'type'    => WPDK_FORM_FIELD_TYPE_CHECKBOX,
                        'name'    => 'price_display_vat_information',
                        'label'   => __( 'Display VAT information close to the price', WPXSMARTSHOP_TEXTDOMAIN ),
                        'value'   => 'y',
                        'checked' => $values ? $values['price_display_vat_information'] : '',
                        'help'    => __( 'Set on for "10,33 (vat included/excluded)" or off for  "10,33"', WPXSMARTSHOP_TEXTDOMAIN )
                    )
                ),
                array(
                    array(
                        'type'    => WPDK_FORM_FIELD_TYPE_CHECKBOX,
                        'name'    => 'price_display_currency_simbol',
                        'label'   => __( 'Show currency symbol close to the price', WPXSMARTSHOP_TEXTDOMAIN ),
                        'value'   => 'y',
                        'checked' => $values ? $values['price_display_currency_simbol'] : '',
                    )
                ),
            )
        );
        return $fields;
    }


    // -----------------------------------------------------------------------------------------------------------------
    // Settings actions
    // -----------------------------------------------------------------------------------------------------------------

    /**
     *
     */
    function save() {
        $values = array(
            'tab-price'                     => 'y',
            'tab-purchasable'               => isset( $_POST['tab-purchasable'] ) ? $_POST['tab-purchasable'] : '',
            'tab-appearance'                => isset( $_POST['tab-appearance'] ) ? $_POST['tab-appearance'] : '',
            'tab-shipping'                  => isset( $_POST['tab-shipping'] ) ? $_POST['tab-shipping'] : '',
            'tab-warehouse'                 => isset( $_POST['tab-warehouse'] ) ? $_POST['tab-warehouse'] : '',
            'tab-membership'                => isset( $_POST['tab-warehouse'] ) ? $_POST['tab-warehouse'] : '',
            'tab-coupons'                   => isset( $_POST['tab-coupons'] ) ? $_POST['tab-coupons'] : '',
            'tab-digital'                   => isset( $_POST['tab-digital'] ) ? $_POST['tab-digital'] : '',

            'price_includes_vat'            => isset( $_POST['price_includes_vat'] ) ? $_POST['price_includes_vat'] : '',
            'price_display_with_vat'        => isset( $_POST['price_display_with_vat'] ) ? $_POST['price_display_with_vat'] : '',
            'price_display_vat_information' => isset( $_POST['price_display_vat_information'] ) ? $_POST['price_display_vat_information'] : '',
            'price_display_currency_simbol' => isset( $_POST['price_display_currency_simbol'] ) ? $_POST['price_display_currency_simbol'] : '',

        );

        WPXSmartShop::settings()->products( $values );

    }

}
