<?php
/**
 * @class              SettingsGeneralView
 * @description        View General Settings (tab)
 *
 * @package            wpxSmartShop
 * @subpackage         views
 * @author             =undo= <g.fazioli@wpxtre.me>
 * @copyright          Copyright (c) 2012 wpXtreme, Inc.
 * @created            18/11/11
 * @version            1.0
 *
 */

class SettingsGeneralView extends WPDKSettingsView {

    // -----------------------------------------------------------------------------------------------------------------
    // Init
    // -----------------------------------------------------------------------------------------------------------------

    /// Construct
    function __construct() {
        parent::__construct( 'general', __( 'General', WPXSMARTSHOP_TEXTDOMAIN ), WPXSmartShop::settings(), false );
    }

    /// Get SDF array for form
    /**
     * Prepara l'array che descrive i campi del form
     *
     * @retval array
     */
    function fields() {

        $values = WPXSmartShop::settings()->general();

        /* Special check id the shop is open */
        $shop_open = 'off';
        if( isset( $values['shop_open']) && 'y' == $values['shop_open']) {
            $shop_open = 'on';
        }

        $fields = array(
            __( 'Shop information', WPXSMARTSHOP_TEXTDOMAIN ) => array(
                array(
                    array(
                        'type'  => WPDK_FORM_FIELD_TYPE_SWIPE,
                        'name'  => 'shop_open',
                        'label' => __( 'Shop open', WPXSMARTSHOP_TEXTDOMAIN ),
                        'value' => $shop_open
                    )
                ),
                array(
                    array(
                        'type'  => WPDK_FORM_FIELD_TYPE_TEXT,
                        'name'  => 'shop_name',
                        'label' => __( 'Shop name', WPXSMARTSHOP_TEXTDOMAIN ),
                        'value' => $values ? $values['shop_name'] : ''
                    )
                ),
                array(
                    array(
                        'type'  => WPDK_FORM_FIELD_TYPE_TEXT,
                        'name'  => 'shop_logo',
                        'label' => __( 'Logo', WPXSMARTSHOP_TEXTDOMAIN ),
                        'value' => $values ? $values['shop_logo'] : ''
                    )
                ),
                array(
                    array(
                        'type'  => WPDK_FORM_FIELD_TYPE_TEXT,
                        'name'  => 'shop_address',
                        'label' => __( 'Address', WPXSMARTSHOP_TEXTDOMAIN ),
                        'value' => $values ? $values['shop_address'] : ''
                    )
                ),
                array(
                    array(
                        'type'    => WPDK_FORM_FIELD_TYPE_SELECT,
                        'name'    => 'shop_country',
                        'label'   => __( 'Country', WPXSMARTSHOP_TEXTDOMAIN ),
                        'options' => WPSmartShopShippingCountries::countriesForSelectMenuExtends(),
                        'value'   => $values ? $values['shop_country'] : ''
                    )
                ),
                array(
                    array(
                        'type'  => WPDK_FORM_FIELD_TYPE_TEXT,
                        'name'  => 'shop_email',
                        'label' => __( 'Email', WPXSMARTSHOP_TEXTDOMAIN ),
                        'value' => $values ? $values['shop_email'] : ''
                    )
                ),
                array(
                    array(
                        'type'  => WPDK_FORM_FIELD_TYPE_TEXTAREA,
                        'name'  => 'shop_info',
                        'class' => 'wpdk-form-lable-top',
                        'cols'  => 40,
                        'label' => __( 'Extra info', WPXSMARTSHOP_TEXTDOMAIN ),
                        'value' => $values ? $values['shop_info'] : ''
                    )
                ),
            ),
            __( 'Symbol measures', WPXSMARTSHOP_TEXTDOMAIN ) => array(
                array(
                    array(
                        'type'   => WPDK_FORM_FIELD_TYPE_TEXT,
                        'name'   => 'measures_weight',
                        'size'   => 4,
                        'label'  => __( 'Weight', WPXSMARTSHOP_TEXTDOMAIN ),
                        'append' => __( 'Eg. g or Kg', WPXSMARTSHOP_TEXTDOMAIN ),
                        'value'  => $values ? $values['measures_weight'] : ''
                    )
                ),
                array(
                    array(
                        'type'  => WPDK_FORM_FIELD_TYPE_TEXT,
                        'name'  => 'measures_size',
                        'size'  => 4,
                        'label' => __( 'Sizes', WPXSMARTSHOP_TEXTDOMAIN ),
                        'value' => $values ? $values['measures_size'] : ''
                    )
                ),
                array(
                    array(
                        'type'  => WPDK_FORM_FIELD_TYPE_TEXT,
                        'name'  => 'measures_volume',
                        'size'  => 4,
                        'label' => __( 'Volume', WPXSMARTSHOP_TEXTDOMAIN ),
                        'value' => $values ? $values['measures_volume'] : ''
                    )
                ),
            )
        );
        return $fields;
    }

    // -----------------------------------------------------------------------------------------------------------------
    // Settings actions
    // -----------------------------------------------------------------------------------------------------------------

    /// Get values for save
    /**
     *
     */
    function valuesForSave() {

        /* Special check id the shop is open */
        $shop_open = 'n';
        if ( isset( $_POST['shop_open'] ) && 'on' == $_POST['shop_open'] ) {
            $shop_open = 'y';
        }

        $values = array(
            'shop_open'        => $shop_open,
            'shop_name'        => esc_attr( $_POST['shop_name'] ),
            'shop_logo'        => esc_attr( $_POST['shop_logo'] ),
            'shop_address'     => esc_attr( $_POST['shop_address'] ),
            'shop_country'     => esc_attr( $_POST['shop_country'] ),
            'shop_email'       => esc_attr( $_POST['shop_email'] ),
            'shop_info'        => esc_textarea( $_POST['shop_info'] ),

            'measures_weight'  => esc_attr( $_POST['measures_weight'] ),
            'measures_size'    => esc_attr( $_POST['measures_size'] ),
            'measures_volume'  => esc_attr( $_POST['measures_volume'] ),
        );

        return $values;
    }

}