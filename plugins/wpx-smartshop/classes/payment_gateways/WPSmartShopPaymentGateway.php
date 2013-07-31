<?php
/**
 * @class WPSmartShopPaymentGateway
 *
 * Manage high level Payment gateway system
 *
 * @package            wpx SmartShop
 * @subpackage         payment_gateways
 * @author             =undo= <g.fazioli@undolog.com>, <g.fazioli@wpxtre.me>
 * @copyright          Copyright (c) 2012 wpXtreme, Inc.
 * @date               24/01/12
 * @version            1.0.0
 *
 */

require_once( 'WPSmartShopPaymentGatewayClass.php' );

class WPSmartShopPaymentGateway {

    /* @deprecated */
    const kCash = 'Cash';
    const kBank = 'Bank';

    // -----------------------------------------------------------------------------------------------------------------
    // Static values
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Get payment gateway store folder path : /var/ ....
     *
     * @static
     * @retval string
     */
    public static function paymentGatewayPath() {
        return trailingslashit( WPXSMARTSHOP_PATH_GATEWAY );
    }

    /**
     * Get payment gateway store folder URL : http://www.site.com/ ....
     *
     * @static
     * @retval string
     */
    public static function paymentGatewayURL() {
        return trailingslashit( WPXSMARTSHOP_URL ) . 'classes/payment_gateways/gateways/';
    }

    // -----------------------------------------------------------------------------------------------------------------
    // Methods
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Restituisce un array con l'elenco dei Gateway presenti su disco. La classe del gateway deve restituire enabled
     * True per essere inserita in questo array. Questo metodo viene utilizzato per matchare i gateway con quelli
     * impostati nelle preferenze del plugin.
     *
     * @static
     * @retval array
     *
     *  array(1) {
     *   ["MPS"] => array(3) {
     *          ["label"]          => string(25) "Monte dei Paschi di Siena"
     *          ["className"]      => string(3) "MPS"
     *          ["classFilename"]  => string(11) "MPS/MPS.php"
     *      }
     *  }
     *
     */
    public static function listPaymentGateways() {

        /* Payment gateways path */
        $root = self::paymentGatewayPath();

        /**
         * Esegue un dir ricorsiva delle sole cartelle. Lo standard è il seguente:
         *
         * - Ogni Plugin deve avere una cartella
         * - Il nome della classe dev'essere uguale al nome della cartella
         * - La classe deve avere una serie di metodi statici che fornisco qui sotto le informazioni sulla classe stessa
         *   come il titolo, versione o descrizione. Vedi WPSmartShopPaymentGateway.php per dettagli
         *
         */

        $result = array();
        if ( ( $objDir = opendir( $root ) ) ) {
            while ( ( $item = readdir( $objDir ) ) !== false ) {
                if ( is_dir( $root . $item ) && $item != "." && $item != ".." ) {
                    $classFilename = $item . '/' . $item . '.php';
                    include_once( $root . $classFilename );
                    if ( class_exists( $item ) ) {
                        if ( $item::enabled() ) {
                            $gateway = new $item;
                            $tabs[$item] = array(
                                'label'         => $gateway::title(),
                                'className'     => $item,
                                'classFilename' => $classFilename,
                                'description'   => $gateway::description(),
                                'thumbnail'     => $gateway->imageThumbnail,
                                'thumbnailURL'  => $gateway->urlThumbnail,
                            );
                            if ( $item != 'SampleGateway' ) {
                                $result[$item] = $tabs[$item];
                            }
                        }
                    }
                }
            }
            closedir( $objDir );
        }
        return $result;
    }


    // -----------------------------------------------------------------------------------------------------------------
    // Payment Flow
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Questo metodo è sempre chiamato quando si arriva dal checkout (summary order). Quindi le informazioni si
     * trovano in POST
     *
     * @static
     * @retval WP_Error
     */
    public static function payment() {

        WPDKWatchDog::watchDog( __CLASS__ );
        WPDKWatchDog::watchDog( __CLASS__, 'START PAYMENT' );

        /* Il primo controllo è sul carrello */

        if ( WPXSmartShopShoppingCart::isCartEmpty() ) {
            /* @todo Far partire una action */
            $error = new WP_Error( 'wpss_error-payment_shopping_cart_empty', __( 'Your Shopping Cart is empty', WPXSMARTSHOP_TEXTDOMAIN ) );
            return $error;
        }

        /* Totale a zero: controlliamo se il totale dell'ordine è a zero per qualche motivo (coupon, sconti) */
        $order_amount = WPXSmartShopSession::orderAmount();
        WPDKWatchDog::watchDog(__CLASS__,"Totale Ordine in sessione: " . $order_amount);

        if ( empty( $order_amount ) ) {
            WPXSmartShopSession::orderPaymentType( '' );
            WPXSmartShopSession::orderPaymentGateway( '' );
            $values = self::createOrder(); //Creo l'ordine
            if ( is_wp_error( $values ) ) {
                return $values;
            }
            $message = __( 'Transaction zero', WPXSMARTSHOP_TEXTDOMAIN );
            $result  = new WP_Error( 'wpss_status-transaction_zero', $message );
            return $result;
        }

        /**
         * @filters
         *
         * @param string $paymentType Stringa che identifica il tipo di pagamento. Default 'Bank'
         */
        $payment_type = apply_filters( 'wpss_payment_gateway_payment_type', self::kBank );
        WPXSmartShopSession::orderPaymentType( $payment_type );

        if ( $payment_type != self::kBank ) {
            $values = self::createOrder(); //creo l'ordine
            if ( is_wp_error( $values ) ) {
                return $values;
            }
            //do_action( 'wpss_payment_gateway_did_order_insert', $values );

            $message = __( 'Payment with Cash', WPXSMARTSHOP_TEXTDOMAIN );
            $status  = new WP_Error( 'wpss_status-payment_with_cash', $message );
            return $status;
        }

        /* Imposto il Gateway */

        if ( !isset( $_POST['wpss-checkout-payment-gateway'] ) ) {
            $error = new WP_Error( 'wpss_error-payment_gateway_not_set', __( 'Payment Gateway not set', WPXSMARTSHOP_TEXTDOMAIN ) );
            return $error;
        }

        $payment_gateway = esc_attr( $_POST['wpss-checkout-payment-gateway'] );

        /**
         * @filters
         *
         * @param string $paymentGateway Stringa con il nome del gateway che si sta usando per pagare
         */
        $payment_gateway = apply_filters( 'wpss_payment_gateway_name', $payment_gateway );

        /* Ricontrollo */
        if ( !isset( $_POST['wpss-checkout-payment-gateway'] ) ) {
            $error = new WP_Error( 'wpss_error-payment_gateway_not_set', __( 'Payment Gateway not set', WPXSMARTSHOP_TEXTDOMAIN ) );
            return $error;
        }

        WPXSmartShopSession::orderPaymentGateway( $payment_gateway );

        WPDKWatchDog::watchDog( __CLASS__, 'PAYMENT WITH ' . $payment_gateway );

        if ( !class_exists( $payment_gateway ) ) {
            include( self::paymentGatewayPath() . $payment_gateway . '/' . $payment_gateway . '.php' );
        }

        /* Creo istanza del gateway. */
        $instance_gateway = new $payment_gateway;

        /* Corriere */
        $id_carrier = 0;
        if ( isset( $_POST['id_carrier'] ) ) {
            $id_carrier = absint( $_POST['id_carrier'] );
            WPXSmartShopSession::orderShippingCarrier( $id_carrier );
        }

        /* Ricreo ordine e stats */
        $values = self::createOrder();
        if ( is_wp_error( $values ) ) {
            return $values;
        }

        $order_track_id = $values['track_id'];

        /* Data per back */
        $order_amount = $values['total'];
        $error_data   = array(
            'track_id' => $order_track_id,
            'amount'   => $order_amount
        );

        /* Inizializza la transazione sul gateway selezionato */
        WPDKWatchDog::watchDog( __CLASS__, 'GATEWAY TRANSACTION' );

        $result = $payment_gateway::transaction( $order_track_id, $order_amount );

        $error_data = array(
            'track_id' => $order_track_id,
            'amount'   => $order_amount
        );

        if ( is_wp_error( $result ) ) {
            $message = __( 'Error while contacting secure Bank', WPXSMARTSHOP_TEXTDOMAIN );
            WPDKWatchDog::watchDog( __CLASS__, $message );
            $result->add( 'wpss_error-payment_gateway_error_while_contacting_bank', $message, $error_data );

            $updating = self::updateOrderWithStatus(); //Se la transazione ha un errore aggiorno tutto subito con DEFUNCT

            return $result;

        } elseif ( $result ) {
            $message = __( 'Redirect to secure Bank', WPXSMARTSHOP_TEXTDOMAIN );
            WPDKWatchDog::watchDog( __CLASS__, $message );
            $status  = new WP_Error( 'wpss_status-redirect_to_secure_bank', $message, $error_data );
            $status->add_data( $instance_gateway->messageRedirect(), 'message' );

            return $status;
        }

        $message = __( 'Sever Error', WPXSMARTSHOP_TEXTDOMAIN );
        WPDKWatchDog::watchDog( __CLASS__, $message );
        $error   = new WP_Error( 'wpss_error-payment_severe_error', $message, $error_data );
        return $error;
    }

    /**
     * Quando un Gateway di una banca deve rispondere un risultato (non di errore) viene usato questo metodo. La
     * sessione deve essere attiva.
     *
     * @package    wpx SmartShop
     * @subpackage WPSmartShopPaymentGateway
     * @since      1.0.0
     *
     * @static
     * @retval WP_Error
     */
    public static function payment_results() {
        WPDKWatchDog::watchDog( __CLASS__ );
        WPDKWatchDog::watchDog( __CLASS__, 'PAYMENT RESULTS' );

        /* Controllo ordine */

        $order_track_id = WPXSmartShopSession::orderTrackID();
        if ( empty( $order_track_id ) ) {
            $error = new WP_Error( 'wpss_error-payment_result_order_track_id_not_set', __( 'No order', WPXSMARTSHOP_TEXTDOMAIN ) );
            return $error;
        }

        /* Transazione a zero o Pagamento in contanti*/
        $payment_type = WPXSmartShopSession::orderPaymentType();
        $order_amount = WPXSmartShopSession::orderAmount();
        if( !empty( $order_amount) && $payment_type == self::kBank ) {

            /* Recupero il gateway che devo usare */
            $payment_gateway = WPXSmartShopSession::orderPaymentGateway();
            if ( empty( $payment_gateway ) ) {
                $error = new WP_Error( 'wpss_error-payment_result_gateway_not_set', __( 'Payment Gateway not set', WPXSMARTSHOP_TEXTDOMAIN ) );
                return $error;
            }

            /**
             * @filters
             *
             * @param string $paymentGateway Stringa con il nome del gateway che si sta usando per pagare
             */
            $payment_gateway = apply_filters( 'wpss_payment_gateway_name', $payment_gateway );

            if ( !class_exists( $payment_gateway ) ) {
                include( self::paymentGatewayPath() . $payment_gateway . '/' . $payment_gateway . '.php' );
            }

            $result = $payment_gateway::transactionResult();

        } else {
            /* Costruisco un result per transazione a zero */
            $result = array(
                'trackID'       => WPXSmartShopSession::orderTrackID(),
                'transactionID' => '',
                'resultCode'    => '',
            );
        }

        if ( is_wp_error( $result ) ) {
            $string = sprintf( 'Error %s (%s)', $result->get_error_code(), $result->get_error_message() );
            /* @todo Do update on order table with tramsaction ID:  $result['transactionID'] */
            WPDKWatchDog::watchDog( __CLASS__, $string );
            return $result;

        } elseif ( $result ) {

            WPXSmartShopOrders::orderConfirmed( $result['trackID'], $result['transactionID'], $result['resultCode'] );
            WPDKWatchDog::watchDog( __CLASS__, 'Transazione [' . $result['trackID'] . '] OK' );

            /* Elimino sessione */
            WPXSmartShopSession::init();

            /* @todo Aggiungere filtri */
            $message = __( 'Thanks for purchase', WPXSMARTSHOP_TEXTDOMAIN );
            $status  = new WP_Error( 'wpss_status-payment_result_thank_for_purchase', $message, $result );
            return $status;
        }

        $message = __( 'Severe Error', WPXSMARTSHOP_TEXTDOMAIN );
        $error  = new WP_Error( 'wpss_error-payment_results_severe_error', $message );
        return $error;
    }

    /**
     * Quando un Gateay di una banca deve rispondere con un ERRORE, viene chiamato questo metodo.
     *
     * @static
     *
     * @retval \WP_Error Oggetto Errore
     */
    public static function payment_error() {
        WPDKWatchDog::watchDog( __CLASS__, __FUNCTION__ );
        WPDKWatchDog::watchDog( __CLASS__, 'PAYMENT ERROR' );

        $error = new WP_Error( 'wpss_error-payment_error', '' );
        return $error;
    }

    // -----------------------------------------------------------------------------------------------------------------
    // Order
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Crea l'ordine
     *
     * @package    wpx SmartShop
     * @subpackage WPSmartShopPaymentGateway
     * @since      1.0.0
     *
     * @static
     * @retval array|WP_Error
     */
    private static function createOrder() {

        /* Controllo sull'ordine */
        $order_track_id = WPXSmartShopSession::orderTrackID();

        if ( empty( $order_track_id ) ) {
            /* Questo è un ordine nuovo, non è stato ricaricato dal database */
            /* Genero quindi un nuovo track id */

            WPDKWatchDog::watchDog(__CLASS__,"Nuovo Ordine: " . $order_track_id);

            $order_track_id = WPXSmartShopOrders::trackID();
            WPXSmartShopSession::orderTrackID( $order_track_id );
        } else {
            /* Ordine presente sul database, ho già un track ID aggiorno eventuali varianze */
            $id_order = WPXSmartShopSession::orderID();

            WPDKWatchDog::watchDog(__CLASS__,"Ordine in Sessione: " . WPDKWatchDog::get_var_dump($id_order) );

            /* Elimino tutto per ricrearlo */
            WPXSmartShopOrders::delete( $id_order );

            /* Elimino dalla stats */
            WPXSmartShopStats::deleteWithOrder( $id_order );
        }

        /* Recupero tutte le informazioni per creare l'ordine */

        $id_user = get_current_user_id();

        /**
         * @filters
         *
         * @param int $id_user ID Questo è un filtro che invia per default (adesso) l'utente attualmente connesso.
         *                     Quindi nell'ordine si avranno così due valori di utenza identica. In particolari
         *                     casi, comunque, questo ID utente potrebbe essere diverso dall'utenza connessa,
         *                     e potrebbe corrispondere ad un altro utente registrato per il quale io sto
         *                     acquistando (o regalando) qualcosa. Si veda record dell'ordine per dettagli.
         */
        $id_user = apply_filters( 'wpss_payment_gateway_id_user_order', $id_user );

        $order_track_id        = WPXSmartShopSession::orderTrackID();
        $order_coupon_id       = WPXSmartShopSession::orderCouponID();
        $order_amount          = WPXSmartShopSession::orderAmount();
        $order_payment_type    = WPXSmartShopSession::orderPaymentType();
        $order_payment_gateway = WPXSmartShopSession::orderPaymentGateway();
        $order_shipping        = WPXSmartShopSession::orderShipping();

        $values = array(
            'id_user_order'         => $id_user,
            'id_coupon'             => $order_coupon_id,

            'track_id'              => $order_track_id,
            'transaction_id'        => '',
            'payment_type'          => $order_payment_type,
            'payment_gateway'       => $order_payment_gateway,

            'subtotal'              => 0,
            'tax'                   => 0,
            'total'                 => $order_amount,
            'shipping'              => $order_shipping,

            'bill_first_name'       => $_POST['bill_first_name'],
            'bill_last_name'        => $_POST['bill_last_name'],
            'bill_address'          => $_POST['bill_address'],
            'bill_country'          => $_POST['bill_country'],
            'bill_town'             => $_POST['bill_town'],
            'bill_zipcode'          => $_POST['bill_zipcode'],
            'bill_email'            => $_POST['bill_email'],
            'bill_phone'            => $_POST['bill_phone'],

            'shipping_first_name'   => $_POST['shipping_first_name'],
            'shipping_last_name'    => $_POST['shipping_last_name'],
            'shipping_address'      => $_POST['shipping_address'],
            'shipping_country'      => $_POST['shipping_country'],
            'shipping_town'         => $_POST['shipping_town'],
            'shipping_zipcode'      => $_POST['shipping_zipcode'],
            'shipping_email'        => $_POST['shipping_email'],
            'shipping_phone'        => $_POST['shipping_phone'],

            'note'                  => ''
        );

        /**
         * @filters
         *
         * @note front-end
         *
         * @param array $values Chiamato prima dell'inserimento di un ordine a fronte di un Checkout lato
         *                      frontend. Da qui è possibile alterare i valori in `$values` prima che l'ordine
         *                      venga inserito.
         */
        $values = apply_filters( 'wpss_payment_gateway_order_will_insert', $values );

        WPDKWatchDog::watchDog(__CLASS__,"Ordine in Sessione prima di un inserimento: " . $values);

        $values = WPXSmartShopOrders::create( $values );
        if ( is_wp_error( $values ) ) {
            return $values;
        }

        /**
         * @todo Da documentare
         *
         * @filters
         *
         * @param array $values Chiamata dopo che un ordine è stato inserito, contiene in 'id' l'id dell'ordine
         *                      appena inserito
         */
        $values = apply_filters( 'wpss_payment_gateway_order_did_insert', $values );

        /* Registro nuovo ordine in sessione (devessi mai fare back con il browser) */
        WPXSmartShopSession::order( $values );

        return $values;
    }

    /**
     * Aggiorna un ordine - già presente e ritirato su in sessione - con 'possibili' nuovi valori.
     *
     * @package    wpx SmartShop
     * @subpackage WPSmartShopPaymentGateway
     * @since      1.0.0
     *
     * @static
     * @retval bool
     */
    private static function updateOrder() {

        $id_order = WPXSmartShopSession::orderID();

        $order_coupon_id       = WPXSmartShopSession::orderCouponID();
        $order_amount          = WPXSmartShopSession::orderAmount();
        $order_payment_type    = WPXSmartShopSession::orderPaymentType();
        $order_payment_gateway = WPXSmartShopSession::orderPaymentGateway();

        $values = array(
            'id_coupon'             => $order_coupon_id,

            'transaction_id'        => '',
            'payment_type'          => $order_payment_type,
            'payment_gateway'       => $order_payment_gateway,

            'subtotal'              => 0,
            'tax'                   => 0,
            'total'                 => $order_amount,

            'bill_first_name'       => $_POST['bill_first_name'],
            'bill_last_name'        => $_POST['bill_last_name'],
            'bill_address'          => $_POST['bill_address'],
            'bill_country'          => $_POST['bill_country'],
            'bill_town'             => $_POST['bill_town'],
            'bill_zipcode'          => $_POST['bill_zipcode'],
            'bill_email'            => $_POST['bill_email'],
            'bill_phone'            => $_POST['bill_phone'],

            'shipping_first_name'   => $_POST['shipping_first_name'],
            'shipping_last_name'    => $_POST['shipping_last_name'],
            'shipping_address'      => $_POST['shipping_address'],
            'shipping_country'      => $_POST['shipping_country'],
            'shipping_town'         => $_POST['shipping_town'],
            'shipping_zipcode'      => $_POST['shipping_zipcode'],
            'shipping_email'        => $_POST['shipping_email'],
            'shipping_phone'        => $_POST['shipping_phone'],

            'note'                  => ''
        );

        $result = WPXSmartShopOrders::updateOrderWithID( $id_order, $values );

        return $result;

    }

    private static function updateOrderWithStatus( $status = WPXSMARTSHOP_ORDER_STATUS_DEFUNCT){
        $id_order = WPXSmartShopSession::orderID();
        $values = WPXSmartShopOrders::order( $id_order, ARRAY_A );

        WPDKWatchDog::watchDog( __CLASS__ );
        WPDKWatchDog::watchDog( __CLASS__, 'UPDATE ORDER WITH STATUS ' . $status );

        $values['status'] = $status;

        $result = WPXSmartShopOrders::update( $id_order, $values );

        if ( is_wp_error( $result ) ) {
            WPDKWatchDog::watchDog( __CLASS__, "Error Updating Order" );
        }
        else
            return $result;

    }



    // -----------------------------------------------------------------------------------------------------------------
    // UI
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Restituisce l'HTML per la selezione dei Payment Gateway impostati come attivi da backend. Il formato dipende
     * dalle impostazioni di backend.
     *
     * @static
     *
     * @param string $label Label da mettere prima del combo select o radio button
     * @param string $name  Nome del controllo select o dei radio button
     * @param string $class Classe aggiuntiva del controllo select o della lista UL
     * @param string $id    ID del controllo select, non usato per i radio button
     *
     * @retval string HTML
     */
    public static function choosePaymentGateways( $label, $name, $class = '', $id = '' ) {

        $settings        = new WPSmartShopSettings();
        $payment_gateway = $settings->payment_gateways();
        $display_mode    = $payment_gateway['display_mode'];

        switch ( $display_mode ) {
            case 'combo-menu' :
                return self::selectPaymentGateways( $label, $name, $class, $id );
                break;
            case 'radio-button' :
                return self::radioPaymentGateways( $label, $name, $class );
                break;
        }

        $message = __( 'Unable to select display mode for Payment Gateways ', WPXSMARTSHOP_TEXTDOMAIN );
        $error   = new WP_Error( 'wpss_error-unable_to_select_display_mode_for_payment_gateways', $message,
            $display_mode );
        return $error;
    }


    /**
     * Restituisce l'HTML della label + select per la selezione dei Payment Gateway impostati come attivi da backend.
     * Se un gateway non è impostato da backend NON verrà caricato in questa lista.
     *
     * @package    wpx SmartShop
     * @subpackage WPSmartShopPaymentGateway
     * @since      1.0.0
     *
     * @static
     *
     * @param string $label Label da mettere prima del combo select
     * @param string $name  Nome del controllo select
     * @param string $class Classe aggiuntiva del controllo select
     * @param string $id    ID del controllo select
     *
     * @retval string HTML
     */
    private static function selectPaymentGateways( $label, $name, $class = '', $id = '' ) {

        /* Solo i gateway selezionati da backend */
        $gatewaysInOptions = WPXSmartShop::settings()->payment_gateways_enabled();
        $all_keys          = array_keys( $gatewaysInOptions );

        $payment_gateways = self::listPaymentGateways();
        $options          = '';
        foreach ( $payment_gateways as $key => $gateway ) {
            if ( in_array( $key, $all_keys ) ) {
                $options .= sprintf( '<option value="%s">%s</option>', $key, $gateway['label'] );
            }
        }
        $html = <<< HTML
    <label class="wpdk-form-label  wpdk-form-select">{$label}:</label>
    <select class="wpdk-form-select {$class}"
            name="{$name}"
            id="{$id}">
    {$options}
    </select>
HTML;
        return $html;
    }

    /**
     * Restituisce l'HTML della lista dei gateway disponibili in forma di radio button.
     *
     * @package    wpx SmartShop
     * @subpackage WPSmartShopPaymentGateway
     * @since      1.0.0
     *
     * @static
     *
     * @param string $label Label da mettere prima del combo select
     * @param string $name  Nome del controllo select
     * @param string $class Classe aggiuntiva del controllo select
     *
     * @retval string
     */
    private static function radioPaymentGateways( $label, $name, $class = '' ) {
        /* Solo i gateway selezionati da backend */
        $gatewaysInOptions = WPXSmartShop::settings()->payment_gateways_enabled();
        $all_keys          = array_keys( $gatewaysInOptions );

        $payment_gateways = self::listPaymentGateways();
        $radios           = '';
        $checked = 'checked="checked"';
        foreach ( $payment_gateways as $key => $gateway ) {
            if ( in_array( $key, $all_keys ) ) {
                $radios .= sprintf( '<li class="%s"><input type="radio" name="%s" value="%s" %s/>%s</li>', $key, $name, $key, $checked, $gateway['thumbnail'] );
                $checked = '';
            }
        }
        $html = <<< HTML
        <label class="wpdk-form-label  wpdk-form-select">{$label}:</label>
    <ul class="wpss-payment-gateways {$class}">
        {$radios}
    </ul>
HTML;
        return $html;
    }

}