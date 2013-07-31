<?php
/**
 * Impostazioni sulle spedizioni
 *
 * @package            wpx SmartShop
 * @subpackage         SettingsShipmentsView
 * @author             =undo= <g.fazioli@undolog.com>, <g.fazioli@wpxtre.me>
 * @copyright          Copyright (c) 2012 wpXtreme, Inc.
 * @link               http://wpxtre.me
 * @created            14/02/12
 * @version            1.0.0
 *
 */

class SettingsShipmentsView extends WPDKSettingsView {


    // -----------------------------------------------------------------------------------------------------------------
    // Init
    // -----------------------------------------------------------------------------------------------------------------

    function __construct() {
        $this->key          = 'shipments';
        $this->title        = __( 'Shipments', WPXSMARTSHOP_TEXTDOMAIN );
        $this->introduction = __( 'Please, write an introduction', WPXSMARTSHOP_TEXTDOMAIN );
        $this->settings     = WPXSmartShop::settings();
    }


    function fields() {
        $values =  WPXSmartShop::settings()->shipments();

        $fields = array(
            __( 'Carrier', WPXSMARTSHOP_TEXTDOMAIN )         => array(
                array(
                    array(
                        'type'    => WPDK_FORM_FIELD_TYPE_SELECT,
                        'name'    => 'default_carrier',
                        'label'   => __( 'Choose a default carrier', WPXSMARTSHOP_TEXTDOMAIN ),
                        'options' => WPXSmartShopCarriers::arrayCarriersForSDF(),
                        'value'   => $values ? $values['default_carrier'] : ''
                    )
                )
            )
        );

        return $fields;
    }

    // -----------------------------------------------------------------------------------------------------------------
    // Settings actions
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * @static
     */
    function save() {
        $values = array(
            'default_carrier'  => $_POST['default_carrier'],
        );

        WPXSmartShop::settings()->shipments( $values );

    }

}
