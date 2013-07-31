<?php
/**
 * @class WPSmartShopPaymentGatewayViewController
 *
 * Payment Gateway View Controller
 *
 * @package            wpx SmartShop
 * @subpackage         payment_gateways.php
 * @author             =undo= <g.fazioli@undolog.com>, <g.fazioli@wpxtre.me>
 * @copyright          Copyright (c)2012 wpXtreme, Inc.
 * @created            17/12/11
 * @version            1.0
 *
 */


class WPSmartShopPaymentGatewayViewController {

    // -----------------------------------------------------------------------------------------------------------------
    // Display
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * @static
     * @deprecated Use display() instead
     */
    public static function view() {
        _deprecated_function( __CLASS__ . '::' . __FUNCTION__, '1.0', 'display()' );
        self::display();
    }

    public static function display() {
        require_once( 'views/SettingsPaymentGatewaysView.php' );
        $view = new SettingsPaymentGatewaysView();
        $view->display();
    }

}
