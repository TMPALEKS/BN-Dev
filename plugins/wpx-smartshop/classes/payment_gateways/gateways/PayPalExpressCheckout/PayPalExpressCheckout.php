<?php
/**
 * @class              PayPalExpressCheckout
 * @description        PayPal (ExpressCheckout) Payment Gateway
 * @package            PayPalExpressCheckout
 * @author             =undo= <g.fazioli@wpxtre.me>
 * @copyright          Copyright (c) 2012 wpXtreme, Inc.
 * @created            26/03/12
 * @version            1.0
 *
 *
 *
 * Specify the return URL
 * ----------------------
 * The return URL is the page to which PayPal redirects your buyer's browser after the buyer logs into PayPal and
 * approves the payment. Typically, this is a secure page (https://...) on your site.
 *
 * @note               You can use the return URL to piggyback parameters between pages on your site. For example,
 *                     you can set your Return URL to specify additional parameters using the:
 *                     https://www.yourcompany.com/page.html?param=value ... syntax.
 *                     The parameters become available as request parameters on the page specified by the Return URL.
 *
 * Specify the cancel URL
 * ----------------------
 * The cancel URL is the page to which PayPal redirects your buyer's browser if the buyer does not approve the payment.
 * Typically, this is the secure page (https://...) on your site from which you redirected the buyer to PayPal.
 *
 * @note               You can pass SetExpressCheckout request values as parameters in your URL to have the values
 *                     available, if necessary, after PayPal redirects to your URL.
 *
 * @todo               Aggiungere un proprio text domain per rendere davvero indipendenti i gateway
 *
 */

class PayPalExpressCheckout extends WPSmartShopPaymentGatewayClass {

    /// Construct
    function __construct() {
        parent::__construct( __CLASS__, self::title(), self::version(), self::description() );
    }

    // -----------------------------------------------------------------------------------------------------------------
    // Static values
    // -----------------------------------------------------------------------------------------------------------------

    /// Get SDF array fields for form
    function fields() {
        $options = self::options();

        $fields = array(
            __( 'Test Mode', WPXSMARTSHOP_TEXTDOMAIN ) => array(

                __( 'Enable test mode', WPXSMARTSHOP_TEXTDOMAIN ),
                array(
                    /* Hidden field */
                    array(
                        'type'      => WPDK_FORM_FIELD_TYPE_HIDDEN,
                        'name'      => 'paypal_currencyID',
                        'value'     => $options['paypal_currencyID']
                    ),
                    array(
                        'type'      => WPDK_FORM_FIELD_TYPE_HIDDEN,
                        'name'      => 'paypal_paymentType',
                        'value'     => $options['paypal_paymentType']
                    ),

                    array(
                        'type'      => WPDK_FORM_FIELD_TYPE_CHECKBOX,
                        'name'      => 'paypal_test_mode',
                        'value'     => 'y',
                        'checked'   => $options['paypal_test_mode'],
                        'label'     => __( 'Test mode (uncheck for Live)', WPXSMARTSHOP_TEXTDOMAIN ),
                        'help'      => __( 'Use test mode for testing transation', WPXSMARTSHOP_TEXTDOMAIN )
                    )
                ),
                array(
                    array(
                        'type'      => WPDK_FORM_FIELD_TYPE_TEXT,
                        'label'     => __( '(Test) API Username', WPXSMARTSHOP_TEXTDOMAIN ),
                        'name'      => 'paypal_test_api_username',
                        'size'      => 64,
                        'value'     => $options['paypal_test_api_username'],
                        'locked'    => true,
                    )
                ),
                array(
                    array(
                        'type'      => WPDK_FORM_FIELD_TYPE_TEXT,
                        'label'     => __( '(Test) API Password', WPXSMARTSHOP_TEXTDOMAIN ),
                        'name'      => 'paypal_test_api_password',
                        'value'     => $options['paypal_test_api_password'],
                        'locked'    => true,
                    )
                ),
                array(
                    array(
                        'type'      => WPDK_FORM_FIELD_TYPE_TEXT,
                        'label'     => __( '(Test) API Signature', WPXSMARTSHOP_TEXTDOMAIN ),
                        'name'      => 'paypal_test_api_signature',
                        'size'      => 64,
                        'value'     => $options['paypal_test_api_signature'],
                        'locked'    => true,
                    )
                ),
            ),

            __( 'Merchand information', WPXSMARTSHOP_TEXTDOMAIN ) => array(

                array(
                    array(
                        'type'      => WPDK_FORM_FIELD_TYPE_TEXT,
                        'label'     => __( 'API Username', WPXSMARTSHOP_TEXTDOMAIN ),
                        'name'      => 'paypal_api_username',
                        'size'      => 64,
                        'value'     => $options['paypal_api_username'],
                        'locked'    => true,
                    )
                ),
                array(
                    array(
                        'type'      => WPDK_FORM_FIELD_TYPE_TEXT,
                        'label'     => __( 'API Password', WPXSMARTSHOP_TEXTDOMAIN ),
                        'name'      => 'paypal_api_password',
                        'value'     => $options['paypal_api_password'],
                        'locked'    => true,
                    )
                ),
                array(
                    array(
                        'type'      => WPDK_FORM_FIELD_TYPE_TEXT,
                        'label'     => __( 'API Signature', WPXSMARTSHOP_TEXTDOMAIN ),
                        'name'      => 'paypal_api_signature',
                        'size'      => 64,
                        'value'     => $options['paypal_api_signature'],
                        'locked'    => true,
                    )
                ),
            ),

            __( 'Callback URLs', WPXSMARTSHOP_TEXTDOMAIN ) => array(

                array(
                    array(
                        'type'      => WPDK_FORM_FIELD_TYPE_TEXT,
                        'label'     => __( 'Return URL', WPXSMARTSHOP_TEXTDOMAIN ),
                        'name'      => 'paypal_returnURL',
                        'size'      => 64,
                        'value'     => $options['paypal_returnURL'],
                        'locked'    => true,
                    )
                ),
                array(
                    array(
                        'type'      => WPDK_FORM_FIELD_TYPE_TEXT,
                        'label'     => __( 'Cancel URL', WPXSMARTSHOP_TEXTDOMAIN ),
                        'name'      => 'paypal_cancelURL',
                        'size'      => 64,
                        'value'     => $options['paypal_cancelURL'],
                        'locked'    => true,
                    )
                ),
            ),
        );

        return $fields;
    }

    /// Get gateway name
    /**
     * Title of gateway
     *
     * @static
     * @retval string
     */
    function title() {
        return 'PayPal ExpressCheckout';
    }

    /// Get gateway version
    /**
     * Version
     *
     * @static
     * @retval string
     */
    function version() {
        return '1.0';
    }

    /// Get gateway description
    /**
     * More description
     *
     * @static
     * @retval string
     */
    function description() {
        return 'PayPal Express Checkout';
    }

    /// Default gateway options
    /**
     * Queste sono le opzioni predefinite di questo plugin
     *
     * @static
     * @retval array
     */
    function defaultOptions() {
        $defaultOptions = array(
            /* Hidden */
            'paypal_currencyID'         => 'EUR',  // or other currency code ('GBP', 'EUR', 'JPY', 'CAD', 'AUD')
            'paypal_paymentType'        => 'Sale', // or 'Sale' or 'Order'

            'paypal_test_mode'          => 'y',
            'paypal_test_api_username'  => 'blue_1332837237_biz_api1.saidmade.com',
            'paypal_test_api_password'  => '1332837269',
            'paypal_test_api_signature' => 'AwCR0.TLf6Vb6hpNyqIPmnVzh0l-ATEZnuDaH.v.crxDQnfxpCuP8qWW',

            'paypal_api_username'       => 'alessandro.cavalla_api1.bluenotemilano.com',
            'paypal_api_password'       => 'KCK9DE4UBYVYJYZ2',
            'paypal_api_signature'      => 'A3oxZa4CAugqAf9GbS7Hej.Q.Dz7ApporyQFFjTIYNFk2AErfVaz0iHj',

            /* Callback url */
            'paypal_returnURL'          => '',
            'paypal_cancelURL'          => '',

        );
        return $defaultOptions;
    }

    /// Update gateway options
    /**
     * Memorizza le opzioni di questo specifico plugin sul database, sfruttando WordPress
     *
     * @uses update_option()
     *
     * @static
     *
     * @param $options
     */
    function saveOptions( $options ) {
        $optionName = md5( self::title() );
        $result     = update_option( $optionName, $options );
        return $result;
    }

    /// Get saved gateway options
    /**
     * Restituisce l'array con le opzioni memorizzate nel database. Se queste non sono presenti vengono usate quelle
     * predefinite.
     *
     * @uses self::defaultOptions()
     * @uses get_option()
     *
     * @static
     * @retval mixed|void
     */
    function options() {
        $optionName     = md5( self::title() );
        $defaultOptions = self::defaultOptions();
        $options        = get_option( $optionName, $defaultOptions );

        return $options;
    }

    /// Enable gateway
    /**
     * Enabled/disabled this Payment Gateway
     *
     * @static
     * @retval bool
     */
    function enabled() {
        return true; // Change to false for disabled
    }

    /// Display the settings view
    /**
     * Visualizza la form con le impostazioni specifiche di questo plugin
     *
     * @static
     * @retval void
     */
    function settings() {
        $this->header();

        if ( WPDKForm::isNonceVerify( __CLASS__ ) ) {

            if ( isset( $_POST['resetToDefault'] ) ) {
                $options = self::defaultOptions(); ?>

            <div class="error fade">
                <p><?php _e( 'Settings Reset to default successfully!', WPXSMARTSHOP_TEXTDOMAIN ) ?></p>
            </div>

            <?php
            } else {
                $options = array(
                    'paypal_currencyID'         => $_POST['paypal_currencyID'],
                    'paypal_paymentType'        => $_POST['paypal_paymentType'],

                    'paypal_test_mode'          => $_POST['paypal_test_mode'],
                    'paypal_test_api_username'  => $_POST['paypal_test_api_username'],
                    'paypal_test_api_password'  => $_POST['paypal_test_api_password'],
                    'paypal_test_api_signature' => $_POST['paypal_test_api_signature'],

                    'paypal_api_username'       => $_POST['paypal_api_username'],
                    'paypal_api_password'       => $_POST['paypal_api_password'],
                    'paypal_api_signature'      => $_POST['paypal_api_signature'],

                    'paypal_returnURL'          => $_POST['paypal_returnURL'],
                    'paypal_cancelURL'          => $_POST['paypal_cancelURL'],
                );
            }

            $optionName = md5( self::title() );
            update_option( $optionName, $options ); ?>

        <div class="updated fade"><p><?php _e( 'Settings update successfully!', WPXSMARTSHOP_TEXTDOMAIN ) ?></p></div>
        <?php
        }
    ?>

    <form name="paypal_expresscheckout" method="post" action="">

        <?php WPDKForm::nonceWithKey( __CLASS__ ) ?>
        <?php WPDKForm::htmlForm( self::fields() ) ?>

        <p class="">
            <input type="submit" class="button-primary" value="<?php _e( 'Update', WPXSMARTSHOP_TEXTDOMAIN ) ?>"/>
            <input type="submit"
                   class="button-secondary alignright"
                   name="resetToDefault"
                   value="<?php _e( 'Reset to default', WPXSMARTSHOP_TEXTDOMAIN ) ?>"/>
        </p>

    </form>

    <?php
    }

    /// Do transaction
    /**
     * Esegue la transazione.
     * L'implementazione di questo metodo è diversa per ogni plugin di payment. Essa effettua concretamente la
     * connessione (chiamata tramite curl() o altro) al server della banca, cioè al sistema di e-commerce del cliente.
     * Anche se l'implementazione è diversa da gateway a gatway, i parametri d'ingresso obbligatori di questo metodo
     * sono uguali per tutti. Possono tuttavia seguire ulteriori parametri (dal terzo in poi), rintracciabili poi con
     * func_num_args() e func_get_arg()
     *
     * @static
     *
     * @param $trackID Questo è l'id della transazione. Tale ID dev'essere univoko per ogni transazione
     *
     * @param $amount Importo nel formato NNNNN.NN
     *
     */
    function transaction( $trackID, $amount ) {

        /* retrive order information */
        $order = WPXSmartShopOrders::order( $trackID );

        // -------------------------------------------------------------------------------------------------------------
        // 1. SetExpressCheckout
        // -------------------------------------------------------------------------------------------------------------

        // -------------------------------------------------------------------------------------------------------------
        // 2. Get Token - transaction ID
        // -------------------------------------------------------------------------------------------------------------

        // -------------------------------------------------------------------------------------------------------------
        // 3. Redirect to PayPal (login)
        // -------------------------------------------------------------------------------------------------------------
        $result = self::setExpressCheckout( $amount, $order );

        return $result;

    }

    /// Transaction result
    /**
     * Chiamata da WPSmartShopPaymentGateway::payment() la seconda volta, ovvero quando dalla banca si torna al
     * payment-gateway.
     *
     * @static
     * @retval array|WP_Error Restituisce un oggetto WP_Error in caso di errore, altrimenti restituisce l'array con
     * le informazioni del gateway più quelle standard che ci si aspettano, che sono le chiavi: trackID,
     * transactionID, resultCode
     */
    function transactionResult() {
        // -------------------------------------------------------------------------------------------------------------
        // 4. GetExpressCheckoutDetails
        // -------------------------------------------------------------------------------------------------------------
        $result = self::getExpressCheckoutDetails();

        // -------------------------------------------------------------------------------------------------------------
        // 5. DoExpressCheckoutPayment
        // -------------------------------------------------------------------------------------------------------------
        if ( is_wp_error( $result ) ) {
            return $result;
        } else {
            $payerID = $_GET['PayerID'];
            $token   = $_GET['token'];
            $result  = self::doExpressCheckout( $payerID, $token );
            if ( !is_wp_error( $result ) ) {
                $result['trackID']       = WPXSmartShopSession::orderTrackID();
                $result['transactionID'] = $token;
                $result['resultCode']    = $payerID;
            }
        }

        return $result;
    }

    // -----------------------------------------------------------------------------------------------------------------
    // PayPal API
    // -----------------------------------------------------------------------------------------------------------------

    /// PayPal internal http post
    /**
     * Send HTTP POST Request
     *
     * @author   PayPal, Inc.
     * @modified Giovambattista Fazioli (g.fazioli@wpxtre.me)
     * @internal
     *
     * @param string $methodName_ The API method name
     * @param string $nvpStr_     The POST Message fields in &name=value pair format
     *
     * @retval array|WP_Error    Parsed HTTP Response body
     */
    private static function PPHttpPost( $methodName_, $nvpStr_ ) {
        /* Get options */
        $options = self::options();

        /* Set variables */
        if ( wpdk_is_bool( $options['paypal_test_mode'] ) ) {
            $environment   = 'sandbox';
            $api_username  = $options['paypal_test_api_username'];
            $api_password  = $options['paypal_test_api_password'];
            $api_signature = $options['paypal_test_api_signature'];
        } else {
            $environment   = '';
            $api_username  = $options['paypal_api_username'];
            $api_password  = $options['paypal_api_password'];
            $api_signature = $options['paypal_api_signature'];
        }

        // -------------------------------------------------------------------------------------------------------------
        // Set up your API credentials, PayPal end point, and API version.
        // -------------------------------------------------------------------------------------------------------------
        $API_UserName  = urlencode( $api_username );
        $API_Password  = urlencode( $api_password );
        $API_Signature = urlencode( $api_signature );
        
        $API_Endpoint  = 'https://api-3t.paypal.com/nvp';
        if ( 'sandbox' === $environment || 'beta-sandbox' === $environment ) {
            $API_Endpoint = 'https://api-3t.' . $environment . '.paypal.com/nvp';
        }
        //$version = urlencode( '51.0' );
        $version = urlencode( '63.0' );

        // setting the curl parameters.
        $ch = curl_init();
        curl_setopt( $ch, CURLOPT_URL, $API_Endpoint );
        curl_setopt( $ch, CURLOPT_VERBOSE, 1 );

        // Set the curl parameters.
        curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, FALSE );
        curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, FALSE );

        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
        curl_setopt( $ch, CURLOPT_POST, 1 );

        // Set the API operation, version, and API signature in the request.
        $nvpreq = "METHOD=$methodName_&VERSION=$version&PWD=$API_Password&USER=$API_UserName&SIGNATURE=$API_Signature$nvpStr_";

        // Set the request as a POST FIELD for curl.
        curl_setopt( $ch, CURLOPT_POSTFIELDS, $nvpreq );

        // Get response from the server.
        $httpResponse = curl_exec( $ch );

        /* =undo= Chiudo la connessione */
        curl_close( $ch );

        if ( !$httpResponse ) {
            //exit( '$methodName_ failed: ' . curl_error( $ch ) . '(' . curl_errno( $ch ) . ')' );
            $message    = __( 'Method failed', WPXSMARTSHOP_TEXTDOMAIN );
            $error_data = array(
                'method_name' => $methodName_,
                'curl_error'  => curl_error( $ch ),
                'curl_errno'  => curl_errno( $ch ),
            );
            $error      = new WP_Error( 'wpss_error-paypal_method_failed', $message, $error_data );
            return $error;
        }

        // Extract the response details.
        $httpResponseAr = explode( "&", $httpResponse );

        $httpParsedResponseAr = array();
        foreach ( $httpResponseAr as $i => $value ) {
            $tmpAr = explode( "=", $value );
            if ( sizeof( $tmpAr ) > 1 ) {
                $httpParsedResponseAr[$tmpAr[0]] = $tmpAr[1];
            }
        }

        if ( ( 0 == sizeof( $httpParsedResponseAr ) ) || !array_key_exists( 'ACK', $httpParsedResponseAr ) ) {
            //exit( "Invalid HTTP Response for POST request($nvpreq) to $API_Endpoint." );
            $message = __( 'Invalid HTTP Response', WPXSMARTSHOP_TEXTDOMAIN );
            $error_data = array(
                'nvp_req'       => $nvpreq,
                'api_end_point' => $API_Endpoint
            );
            $error   = new WP_Error( 'wpss_error-paypal_invalid_http_response' , $message, $error_data);
            return $error;
        }

        return $httpParsedResponseAr;
    }

    /// PayPal internal
    /**
     * Esegue il comando SetExpressCheckout
     *
     * @static
     *
     * @param float|string $amount Costo
     * @param object       $order  Record ordine
     *
     * @retval WP_Error Restituisce un oggetto WP_Error in caso di errore, oppure effettua il redirect verso PayPal
     */
    private static function setExpressCheckout( $amount, $order ) {
        /* Get options */
        $options = self::options();

        /* Set variables */
        if ( wpdk_is_bool( $options['paypal_test_mode'] ) ) {
            $environment = 'sandbox';
        } else {
            $environment = '';
        }

        // Set request-specific fields.
        $paymentAmount = $amount;
        $currencyID    = $options['paypal_currencyID'];  // or other currency code ('GBP', 'EUR', 'JPY', 'CAD', 'AUD')
        $paymentType   = $options['paypal_paymentType']; // or 'Sale' or 'Order'
        $returnURL     = $options['paypal_returnURL'];

        /* @todo Da sistemare */
        $receipt_slug = WPXSmartShop::settings()->receipt_permalink();
        $returnURL = wpdk_permalink_page_with_slug( $receipt_slug, kWPSmartShopStorePagePostTypeKey );

        $cancelURL     = $options['paypal_cancelURL'];

        /* @todo Da sistemare */
        $error_slug = WPXSmartShop::settings()->error_permalink();
        $cancelURL = wpdk_permalink_page_with_slug( $error_slug, kWPSmartShopStorePagePostTypeKey );

        // Add request-specific fields to the request string.
        //$nvpStr = "&AMT=$paymentAmount&RETURNURL=$returnURL&CANCELURL=$cancelURL&PAYMENTACTION=$paymentType&CURRENCYCODE=$currencyID";

        $paypal_locale = 'IT';
        if ( defined( 'ICL_LANGUAGE_CODE' ) ) {
            $language_code = ICL_LANGUAGE_CODE;

            /* @todo Da completare con altre lingue */
            $matrix = array(
                'it'    => 'IT',
                'en'    => 'US'
            );
            $paypal_locale = $matrix[$language_code];
        }

        if( !empty( $order->shipping_country) ) {
            $shipping_country = WPSmartShopShippingCountries::shippingCountry( $order->shipping_country );
            $isocode = $shipping_country->isocode;
        }

        $params = array(
            'AMT'               => $paymentAmount,
            'DESC'              => sprintf( '%s: #%s - %s', __( 'Order', WPXSMARTSHOP_TEXTDOMAIN), $order->id, $order->track_id ),
            'BRANDNAME'         => 'Blue Note Store',
            'HDRIMG'            => trailingslashit( get_bloginfo('template_url') ) . 'images/logo-blue.png',
            'RETURNURL'         => $returnURL,
            'CANCELURL'         => $cancelURL,
            'PAYMENTACTION'     => $paymentType,
            'CURRENCYCODE'      => $currencyID,
            'LOCALECODE'        => $paypal_locale,
        );

        if ( !empty( $isocode ) && !empty( $order->shipping_first_name ) && !empty( $order->shipping_last_name ) &&
            !empty( $order->shipping_address ) && !empty( $order->shipping_town ) && !empty( $order->shipping_zipcode )
        ) {
            $shipping_info = array(
                'SHIPTONAME'        => sprintf( '%s %s', $order->shipping_first_name, $order->shipping_last_name ),
                'SHIPTOSTREET'      => $order->shipping_address,
                'SHIPTOCITY'        => $order->shipping_town,
                'SHIPTOZIP'         => $order->shipping_zipcode,
                'SHIPTOSTATE'       => $order->shipping_town,
                'SHIPTOCOUNTRYCODE' => $isocode,
                'ADDROVERRIDE'      => '1'
            );
            $params = array_merge( $params, $shipping_info );
        }

        $nvpStr = '&' . http_build_query( $params, '', '&' );

        WPDKWatchDog::watchDog( __CLASS__ );
        WPDKWatchDog::watchDog( __CLASS__, 'Sto per contattare la Banca: '. $nvpStr );

        /* Execute the API operation; see the PPHttpPost function above. */
        $httpParsedResponseAr = self::PPHttpPost( 'SetExpressCheckout', $nvpStr );

        if ( is_wp_error( $httpParsedResponseAr ) ) {
            return $httpParsedResponseAr;
        }

        if ( "SUCCESS" == strtoupper( $httpParsedResponseAr["ACK"] ) ||
            "SUCCESSWITHWARNING" == strtoupper( $httpParsedResponseAr["ACK"] )
        ) {
            /* Redirect to paypal.com. */
            $token     = urldecode( $httpParsedResponseAr["TOKEN"] );
            $payPalURL = "https://www.paypal.com/webscr&cmd=_express-checkout&token=$token";
            if ( "sandbox" === $environment || "beta-sandbox" === $environment ) {
                $payPalURL = "https://www.$environment.paypal.com/webscr&cmd=_express-checkout&token=$token&useraction=commit";
            }
            /* header( "Location: $payPalURL" ); */
            echo '<meta http-equiv="refresh" content="0;URL=' . $payPalURL . '">';
            return true;
        } else {
            //exit( 'SetExpressCheckout failed: ' . print_r( $httpParsedResponseAr, true ) );
            $message = __( 'SetExpressCheckout failed', WPXSMARTSHOP_TEXTDOMAIN );
            $error   = new WP_Error( 'wpss_error-paypal_set_express_checkout_failed', $message, $httpParsedResponseAr );
            return $error;
        }
    }

    /// PayPal internal
    /**
     * Recupera le informazioni sui dettagli della trasazione avviata con setExpressCheckpout
     *
     * @static
     * @retval WP_Error|array Restituisce un oggetto WP_Error in caso di errore, oppure un array con le informazioni
     * ricevute da PayPal
     */
    private static function getExpressCheckoutDetails() {
        /**
         * This example assumes that this is the return URL in the SetExpressCheckout API call.
         * The PayPal website redirects the user to this page with a token.
         */

        // Obtain the token from PayPal.
        if ( !array_key_exists( 'token', $_REQUEST ) ) {
            //exit( 'Token is not received.' );
            $message = __( 'Token is not received.', WPXSMARTSHOP_TEXTDOMAIN );
            $error   = new WP_Error( 'wpss_error-paypal_token_is_not_received', $message );
            return $error;
        }

        // Set request-specific fields.
        $token = urlencode( htmlspecialchars( $_REQUEST['token'] ) );

        // Add request-specific fields to the request string.
        $nvpStr = "&TOKEN=$token";

        // Execute the API operation; see the PPHttpPost function above.
        $httpParsedResponseAr = self::PPHttpPost( 'GetExpressCheckoutDetails', $nvpStr );

        if ( "SUCCESS" == strtoupper( $httpParsedResponseAr["ACK"] ) ||
            "SUCCESSWITHWARNING" == strtoupper( $httpParsedResponseAr["ACK"] )
        ) {
            // Extract the response details.
            $payerID = $httpParsedResponseAr['PAYERID'];
            $street1 = $httpParsedResponseAr["SHIPTOSTREET"];
            if ( array_key_exists( "SHIPTOSTREET2", $httpParsedResponseAr ) ) {
                $street2 = $httpParsedResponseAr["SHIPTOSTREET2"];
            }
            $city_name      = $httpParsedResponseAr["SHIPTOCITY"];
            $state_province = $httpParsedResponseAr["SHIPTOSTATE"];
            $postal_code    = $httpParsedResponseAr["SHIPTOZIP"];
            $country_code   = $httpParsedResponseAr["SHIPTOCOUNTRYCODE"];

            //exit( 'Get Express Checkout Details Completed Successfully: ' . print_r( $httpParsedResponseAr, true ) );
            return $httpParsedResponseAr;

        } else {
            //exit( 'GetExpressCheckoutDetails failed: ' . print_r( $httpParsedResponseAr, true ) );

            $message = __( 'GetExpressCheckoutDetails failed.', WPXSMARTSHOP_TEXTDOMAIN );
            $error   = new WP_Error( 'wpss_error-paypal_get_express_checkout_failed', $message, $httpParsedResponseAr );
            return $error;
        }
    }

    /// PayPal internal
    /**
     * Conclude la transazione iniziata con setExpressCheckout() e verificata con getExpressCheckoutDeatils()
     *
     * @static
     * @retval WP_Error|array Restituisce un oggetto WP_Error in caso di errore, oppure un array con le informazioni
     * ricevute da PayPal
     */
    private static function doExpressCheckout( $payerID, $token ) {
        /* Get options */
        $options = self::options();

        /**
         * This example assumes that a token was obtained from the SetExpressCheckout API call.
         * This example also assumes that a payerID was obtained from the SetExpressCheckout API call
         * or from the GetExpressCheckoutDetails API call.
         */

        // Set request-specific fields.
        $payerID       = urlencode( $payerID );
        $token         = urlencode( $token );
        $paymentAmount = urlencode( WPXSmartShopSession::orderAmount() );

        $paymentType = urlencode( $options['paypal_paymentType'] ); // or 'Sale' or 'Order'
        $currencyID  = urlencode( $options['paypal_currencyID'] ); // or other currency code ('GBP', 'EUR', 'JPY', 'CAD', 'AUD')

        // Add request-specific fields to the request string.
        $nvpStr = "&TOKEN=$token&PAYERID=$payerID&PAYMENTACTION=$paymentType&AMT=$paymentAmount&CURRENCYCODE=$currencyID";

        // Execute the API operation; see the PPHttpPost function above.
        $httpParsedResponseAr = self::PPHttpPost( 'DoExpressCheckoutPayment', $nvpStr );

        if ( "SUCCESS" == strtoupper( $httpParsedResponseAr["ACK"] ) ||
            "SUCCESSWITHWARNING" == strtoupper( $httpParsedResponseAr["ACK"] )
        ) {
            //exit( 'Express Checkout Payment Completed Successfully: ' . print_r( $httpParsedResponseAr, true ) );
            return $httpParsedResponseAr;

        } else {
            //exit( 'DoExpressCheckoutPayment failed: ' . print_r( $httpParsedResponseAr, true ) );
            $message = __( 'DoExpressCheckoutPayment failed', WPXSMARTSHOP_TEXTDOMAIN );
            $error   = new WP_Error( 'wpss_error-paypal_do_express_checkout_failed', $message, $httpParsedResponseAr );
            return $error;
        }
    }

}