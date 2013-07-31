<?php
/**
 * Impostazioni Ordini
 *
 * @package            wpx SmartShop
 * @subpackage         SettingsOrdersView
 * @author             =undo= <g.fazioli@undolog.com>, <g.fazioli@wpxtre.me>
 * @copyright          Copyright (c) 2012 wpXtreme, Inc.
 * @link               http://wpxtre.me
 * @created            05/03/12
 * @version            1.0.0
 *
 */

class SettingsOrdersView extends WPDKSettingsView {
    
    
    // -----------------------------------------------------------------------------------------------------------------
    // Init
    // -----------------------------------------------------------------------------------------------------------------

    function __construct() {
        $this->key          = 'orders';
        $this->title        = __( 'Orders', WPXSMARTSHOP_TEXTDOMAIN );
        $this->introduction = __( 'Please, write an introduction', WPXSMARTSHOP_TEXTDOMAIN );
        $this->settings     = WPXSmartShop::settings();
    }


    function fields() {
        $values =  WPXSmartShop::settings()->orders();

        $fields = array(
            __( 'Orders', WPXSMARTSHOP_TEXTDOMAIN ) => array(
                array(
                    array(
                        'type'    => WPDK_FORM_FIELD_TYPE_CHECKBOX,
                        'name'    => 'count_pending',
                        'label'   => __( 'Count Product in Pending orders', WPXSMARTSHOP_TEXTDOMAIN ),
                        'value'   => 'y',
                        'checked' => $values ? $values['count_pending'] : ''
                    )
                ),
                array(
                    array(
                        'type'    => WPDK_FORM_FIELD_TYPE_SELECT,
                        'name'    => 'defunct_status',
                        'label'   => __( 'Set an Order in', WPXSMARTSHOP_TEXTDOMAIN ),
                        'options' => array(
                            ''                                => __( 'None', WPXSMARTSHOP_TEXTDOMAIN ),
                            WPXSMARTSHOP_ORDER_STATUS_DEFUNCT    => __( 'Defunct', WPXSMARTSHOP_TEXTDOMAIN ),
                            WPXSMARTSHOP_ORDER_STATUS_CANCELLED  => __( 'Cancelled', WPXSMARTSHOP_TEXTDOMAIN ),
                        ),
                        'value'   => $values ? $values['defunct_status'] : ''
                    ),
                    array(
                        'type'  => WPDK_FORM_FIELD_TYPE_NUMBER,
                        'name'  => 'defunct_status_after',
                        'size'  => 4,
                        'label' => __( 'after', WPXSMARTSHOP_TEXTDOMAIN ),
                        'value' => $values ? $values['defunct_status_after'] : '',
                    ),
                    array(
                        'type'    => WPDK_FORM_FIELD_TYPE_SELECT,
                        'name'    => 'defunct_status_after_type',
                        'options' => WPXSmartShopOrders::durabilityType(),
                        'value'   => $values ? $values['defunct_status_after_type'] : ''
                    ),
                ),
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
            'count_pending'               => isset( $_POST['count_pending'] ) ? $_POST['count_pending'] : '',
            'defunct_status'              => $_POST['defunct_status'],
            'defunct_status_after'        => $_POST['defunct_status_after'],
            'defunct_status_after_type'   => $_POST['defunct_status_after_type'],
        );

        WPXSmartShop::settings()->orders( $values );

    }

}
