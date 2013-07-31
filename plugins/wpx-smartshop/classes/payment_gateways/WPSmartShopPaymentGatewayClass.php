<?php
/**
 * @class WPSmartShopPaymentGatewayClass
 *
 * Helper class for Payment Gateway
 *
 * @package            wpx SmartShop
 * @subpackage         payment_gateways
 * @author             =undo= <g.fazioli@undolog.com>, <g.fazioli@wpxtre.me>
 * @copyright          Copyright (c) 2012 wpXtreme, Inc.
 * @created            28/03/12
 * @version            1.0.0
 *
 */

class WPSmartShopPaymentGatewayClass {

    var $title;
    var $version;
    var $description;
    var $filenameThumbnail;
    var $filenameLogo;
    var $class_name;

    var $urlThumbnail;
    var $urlLogo;

    var $imageThumbnail;
    var $imageLogo;


    function __construct( $class_name, $title, $version, $description, $filenameThumbnail = 'thumbnail.png', $filenameLogo = 'logo.png' ) {
        $this->class_name        = $class_name;
        $this->title             = $title;
        $this->version           = $version;
        $this->description       = $description;
        $this->filenameThumbnail = $filenameThumbnail;
        $this->filenameLogo      = $filenameLogo;

        $url_payment_gateway = trailingslashit( WPSmartShopPaymentGateway::paymentGatewayURL() );
        $folder_gateway      = trailingslashit( $this->class_name );
        $this->urlThumbnail  = sprintf( '%s%s%s', $url_payment_gateway, $folder_gateway, $this->filenameThumbnail );
        $this->urlLogo       = sprintf( '%s%s%s', $url_payment_gateway, $folder_gateway, $this->filenameLogo );

        $this->imageThumbnail = sprintf( '<img class="wpss-payment-gateway-thumbnail" src="%s" alt="%s" />', $this->urlThumbnail, $description );
        $this->imageLogo      = sprintf( '<img class="wpss-payment-gateway-logo" src="%s" alt="%s" />', $this->urlLogo, $description );

    }


    // -----------------------------------------------------------------------------------------------------------------
    // Static values
    // -----------------------------------------------------------------------------------------------------------------

    // -----------------------------------------------------------------------------------------------------------------
    // Methods
    // -----------------------------------------------------------------------------------------------------------------

    // -----------------------------------------------------------------------------------------------------------------
    // UI - standard helper
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Visualizza l'header standrd del gateway di pagamento
     *
     * @package            wpx SmartShop
     * @subpackage         WPSmartShopPaymentGatewayClass
     * @since              1.0
     *
     * @static
     *
     */
    function header() { ?>
    <h3>
        <?php echo $this->imageThumbnail ?>
        <?php echo $this->title ?>
        <?php echo sprintf( '%s %s', __( 'Version', 'wp-smarrtshop' ), $this->version ) ?>
    </h3>
    <?php
    }

    /**
     * Redirect HTML message
     *
     * @package            wpx SmartShop
     * @subpackage         WPSmartShopPaymentGatewayClass
     * @since              1.0
     *
     * @static
     * @retval string
     * HTML del messaggio di default usato durante la ridirezione alla secure-back
     */
    function messageRedirect() {

        /**
         * @filters
         *
         * @param string $message Messaggio di redirect
         */
        $message = apply_filters('wpss_payment_gateway_message_redirect', __('Well, You are being redirected to the secure connection of the bank', WPXSMARTSHOP_TEXTDOMAIN ));

        /**
         * @filters
         *
         * @param string $message Messaggio di attesa
         */
        $wait = apply_filters('wpss_payment_gateway_message_redirect_wait', __('Please wait...', WPXSMARTSHOP_TEXTDOMAIN ));

        $logo = $this->imageLogo;

        $html = <<< HTML
    <div class="wpss-payment-gateway-message-redirect-box">
        <h3>{$message}</h3>
        {$logo}
        <p class="wpss-payment-gateway-message-wait">{$wait}</p>
    </div>
HTML;
        return $html;
    }

}