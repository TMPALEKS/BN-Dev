<?php
/**
 * @class              MPS
 * @description        Payment Gateway Plugin for MPS
 * @package            MPS
 * @author             =undo= <g.fazioli@wpxtre.me>
 * @copyright          Copyright (c) 2012 wpXtreme, Inc.
 * @created            29/11/11
 * @version            1.0
 *
 * @code
 *                     fake card
 * 4539990000000012 - OK
 * 4539990000000020 - CARTA PER ESITO NEGATIVO
 * 4999000055550000 - CARTA ELABORAZIONE DATI NON CORRETTI
 * @endcode
 *
 * @todo               Fornire un bottone di testing
 *
 */

class MPS extends WPSmartShopPaymentGatewayClass {

    /// Construct
    function __construct() {
        parent::__construct( __CLASS__, self::title(), self::version(), self::description() );
    }

    // -----------------------------------------------------------------------------------------------------------------
    // Static values
    // -----------------------------------------------------------------------------------------------------------------

    /// Get SDF array fields for form
    /**
     * Desfinisce una array di campi da utilizzare per la modifica delle impostazioni di questo gateway.
     *
     * @todo Alcuni campi dovrebbero evere lunghezze fisse da non superare, vedi password
     *
     * @static
     * @retval array
     */
    function fields() {
        $options = self::options();

        $fields = array(

            __( 'Mode', WPXSMARTSHOP_TEXTDOMAIN )                                                      => array(

                __( 'Please, select testing or real mode and purchase modality', WPXSMARTSHOP_TEXTDOMAIN ),
                array(
                    array(
                        'type'  => WPDK_FORM_FIELD_TYPE_HIDDEN,
                        'name'  => 'wpssMPSGatewayCurrencyCode',
                        'value' => $options['wpssMPSGatewayCurrencyCode']
                    ),

                    array(
                        'type'  => WPDK_FORM_FIELD_TYPE_HIDDEN,
                        'name'  => 'wpssMPSGatewayLangID',
                        'value' => $options['wpssMPSGatewayLangID']
                    ),

                    array(
                        'type'      => WPDK_FORM_FIELD_TYPE_CHECKBOX,
                        'name'      => 'wpssMPSGatewayTest',
                        'value'     => 'y',
                        'checked'   => $options['wpssMPSGatewayTest'],
                        'label'     => __( 'Test mode', WPXSMARTSHOP_TEXTDOMAIN ),
                        'help'      => __( 'Use test mode for testing transation', WPXSMARTSHOP_TEXTDOMAIN )
                    )
                ),
                array(
                    array(
                        'type'      => WPDK_FORM_FIELD_TYPE_SELECT,
                        'name'      => 'wpssMPSGatewayAction',
                        'label'     => __( 'Action Modality', WPXSMARTSHOP_TEXTDOMAIN ),
                        'value'     => $options['wpssMPSGatewayAction'],
                        'options'   => array(
                            ''  => __( 'Select a transition type', WPXSMARTSHOP_TEXTDOMAIN ),
                            '1' => __( 'Purchase', WPXSMARTSHOP_TEXTDOMAIN ),
                            '4' => __( 'Authorization', WPXSMARTSHOP_TEXTDOMAIN )
                        ),
                        'help'      => __( 'Use test mode for testing transition', WPXSMARTSHOP_TEXTDOMAIN )
                    )
                )
            ),

            __( 'Merchand Informations', WPXSMARTSHOP_TEXTDOMAIN )                                     => array(
                __( 'Your shop license informations', WPXSMARTSHOP_TEXTDOMAIN ),
                array(
                    array(
                        'type'   => WPDK_FORM_FIELD_TYPE_TEXT,
                        'name'   => 'wpssMPSGatewayID',
                        'locked' => true,
                        'label'  => __( 'ID', WPXSMARTSHOP_TEXTDOMAIN ),
                        'help'   => __( 'This is the your Merchand ID. If you do not have this code, please contact your Bank', WPXSMARTSHOP_TEXTDOMAIN ),
                        'value'  => $options['wpssMPSGatewayID'],
                        'class'  => ''
                    ),

                    array(
                        'type'   => WPDK_FORM_FIELD_TYPE_PASSWORD,
                        'name'   => 'wpssMPSGatewayPassword',
                        'locked' => true,
                        'label'  => __( 'Password', WPXSMARTSHOP_TEXTDOMAIN ),
                        'help'   => __( 'This is the password for your ID.', WPXSMARTSHOP_TEXTDOMAIN ),
                        'value'  => $options['wpssMPSGatewayPassword'],
                        'class'  => 'wpssPassword'
                    )
                ),
            ),

            __( 'URLs', WPXSMARTSHOP_TEXTDOMAIN )                                                      => array(
                __( 'These are the bank URL for test and exercise and response/error URL on your web site', WPXSMARTSHOP_TEXTDOMAIN ),

                array(
                    array(
                        'type'        => WPDK_FORM_FIELD_TYPE_TEXT,
                        'name'        => 'wpssMPSGatewayURL',
                        'label'       => __( 'Primary URL', WPXSMARTSHOP_TEXTDOMAIN ),
                        'help'        => __( 'This is the real URL for transition', WPXSMARTSHOP_TEXTDOMAIN ),
                        'placeholder' => 'http://',
                        'size'        => 64,
                        'value'       => $options['wpssMPSGatewayURL'],
                    )
                ),

                array(
                    array(
                        'type'        => WPDK_FORM_FIELD_TYPE_TEXT,
                        'name'        => 'wpssMPSGatewayURLForTest',
                        'label'       => __( 'URL for test', WPXSMARTSHOP_TEXTDOMAIN ),
                        'help'        => __( 'This is the test URL', WPXSMARTSHOP_TEXTDOMAIN ),
                        'placeholder' => 'http://',
                        'size'        => 64,
                        'value'       => $options['wpssMPSGatewayURLForTest'],
                    )
                ),

                array(
                    array(
                        'type'        => WPDK_FORM_FIELD_TYPE_TEXT,
                        'name'        => 'wpssMPSGatewayResponseURL',
                        'label'       => __( 'Response URL', WPXSMARTSHOP_TEXTDOMAIN ),
                        'help'        => __( 'When the transition is over with success, the Bank call this URL on your site', WPXSMARTSHOP_TEXTDOMAIN ),
                        'placeholder' => 'http://',
                        'size'        => 64,
                        'value'       => $options['wpssMPSGatewayResponseURL'],
                    )
                ),

                array(
                    array(
                        'name'        => 'wpssMPSGatewayErrorURL',
                        'type'        => 'text',
                        'label'       => __( 'Error URL', WPXSMARTSHOP_TEXTDOMAIN ),
                        'help'        => __( 'When the transition is over with an error, the Bank call this URL on your site', WPXSMARTSHOP_TEXTDOMAIN ),
                        'placeholder' => 'http://',
                        'size'        => 64,
                        'value'       => $options['wpssMPSGatewayErrorURL'],
                    )
                )
            )
        );
        return $fields;
    }

    /// Get gateway name
    /**
     * Usato come label in bottoni, tab o altro
     *
     * @static
     * @retval string
     */
    function title() {
        return 'Monte dei Paschi di Siena';
    }

    /// Get gateway version
    /**
     * Restituisce la versione del plugin
     *
     * @static
     * @retval string
     */
    function version() {
        return '1.0';
    }

    /// Get gateway description
    /**
     * Non usato per adesso
     *
     * @static
     * @retval string
     */
    function description() {
        return '';
    }

    /// Enable gateway
    /**
     * Enabled/disabled this Payment Gateway
     *
     * @static
     * @retval bool
     */
    function enabled() {
        return true;
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
            'wpssMPSGatewayURL'          => 'https://www.constriv.com/cg/servlet/PaymentInitHTTPServlet',
            'wpssMPSGatewayURLForTest'   => 'https://test4.constriv.com/cg301/servlet/PaymentInitHTTPServlet',
            'wpssMPSGatewayResponseURL'  => '',
            'wpssMPSGatewayErrorURL'     => '',
            'wpssMPSGatewayID'           => '51418812',
            'wpssMPSGatewayPassword'     => '2borsieri2',
//            'wpssMPSGatewayID'           => '89025555',
//            'wpssMPSGatewayPassword'     => 'test',
            'wpssMPSGatewayAction'       => '1',
            'wpssMPSGatewayCurrencyCode' => '978', // Euro
            'wpssMPSGatewayLangID'       => 'ITA',
            'wpssMPSGatewayTest'         => 'y' // Default in test
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
        $result     = update_option( $optionName, $options ); // WordPress auto serialize
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

    /// Display the settings view
    /**
     * Visualizza la form con le impostazioni specifiche di questo plugin. Questo metodo viene chiamato automaticamete
     * duranrte la visualizzazione dei plugin da backend di WordPress
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
                    'wpssMPSGatewayCurrencyCode' => $_POST['wpssMPSGatewayCurrencyCode'],
                    'wpssMPSGatewayLangID'       => $_POST['wpssMPSGatewayLangID'],
                    'wpssMPSGatewayTest'         => isset( $_POST['wpssMPSGatewayTest'] ) ? $_POST['wpssMPSGatewayTest'] : 'n',
                    'wpssMPSGatewayAction'       => $_POST['wpssMPSGatewayAction'],
                    'wpssMPSGatewayID'           => $_POST['wpssMPSGatewayID'],
                    'wpssMPSGatewayPassword'     => $_POST['wpssMPSGatewayPassword'],
                    'wpssMPSGatewayURL'          => $_POST['wpssMPSGatewayURL'],
                    'wpssMPSGatewayURLForTest'   => $_POST['wpssMPSGatewayURLForTest'],
                    'wpssMPSGatewayResponseURL'  => $_POST['wpssMPSGatewayResponseURL'],
                    'wpssMPSGatewayErrorURL'     => $_POST['wpssMPSGatewayErrorURL'],
                );
            }

            $optionName = md5( self::title() );
            update_option( $optionName, $options ); ?>

        <div class="updated fade"><p><?php _e( 'Settings update successfully!', WPXSMARTSHOP_TEXTDOMAIN ) ?></p></div>
        <?php
        }
        ?>

    <form name="mps" method="post" action="">

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
     * @param $amount  Importo nel formato NNNNN.NN
     *
     * @retval bool True se la connessione con la banca ha dato esisto positivo e stati per essere ridirezionato al
     *         pagamento False se errore
     *
     */
    function transaction( $trackID, $amount ) {
        $options = self::options();

        /* Imposto url di prova o reale */
        if ( 'n' == $options['wpssMPSGatewayTest'] ) {
            $secure_back_url = $options['wpssMPSGatewayURL'];

            /* Apro la connessione verso il server reale */
            $ch = curl_init( $secure_back_url );
        } else {
            $secure_back_url = $options['wpssMPSGatewayURLForTest'];

            /* Apro la connessione verso il server di prova */
            $ch = curl_init( $secure_back_url );
        }

        /* Imposto gli headers HTTP, imposto curl per protocollo https */
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_POST, 1);

        /* Invio i dati */
        $dataToSend = self::dataToSend();

        /* Imposto il transaction ID e importo */
        $dataToSend['trackid'] = $trackID;
        $dataToSend['amt']     = $amount;

        //curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($dataToSend));
        $c = '';

        foreach ( $dataToSend as $key => $value ) {
            if ( $c != '' ) {
                $c .= '&';
            }
            $c .= $key . '=' . $value;
        }

        /* Log */
        WPDKWatchDog::watchDog( __CLASS__ );
        WPDKWatchDog::watchDog( __CLASS__, 'Sto per contattare la Banca: ' . $secure_back_url );
        WPDKWatchDog::watchDog( __CLASS__, $c );
        WPDKWatchDog::watchDog( __CLASS__, join( ',', $dataToSend ) );
        WPDKWatchDog::watchDog( __CLASS__ );

        curl_setopt( $ch, CURLOPT_POSTFIELDS, $c );

        /* Imposta la variabile PHP */
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );

        /* Ricevo la risposta dal server: vedi più sotto per dettagli */
        $varResponse = curl_exec( $ch );

        /* Chiudo la connessione */
        curl_close( $ch );

        WPDKWatchDog::watchDog( __CLASS__, $varResponse );

        if ( substr( $varResponse, 0, 7 ) == '!ERROR!' ) {

            WPDKWatchDog::watchDog( __CLASS__, 'Errore' );
            WPDKWatchDog::watchDog( __CLASS__, $varResponse );

            $message = __( 'MPS Error', WPXSMARTSHOP_TEXTDOMAIN );
            $error   = new WP_Error( 'wpss_error-mps_error', $message, $varResponse );
            return $error;

        } else {
            WPDKWatchDog::watchDog( __CLASS__, 'OK' );
            /**
             * La risposta che il Payment Gateway ritorna al Merchant dopo aver ricevuto il messaggio PaymentInit e averne
             * verificato la validità (ID e Password del Merchant) contiene i seguenti campi:
             *
             * PaymentID  (20)
             *  Codice univoco identificativo dell’ordine. Il Merchant deve inserirlo nella redirezione del Cardholder, in
             *  modo da permettere al Payment Gateway di verificare la validità dell’utente che sta accedendo al sistema di
             *  pagamento
             *
             * PaymentURL (256)
             *  URL della HPP a cui il Merchant deve redirezionare il Cardholder per procedere al pagamento
             *
             */

            /* Separo il contenuto della stringa ricevuta (PaymentID:RedirectURL) */

            $varPosiz     = strpos( $varResponse, ':http' );
            $varPaymentId = substr( $varResponse, 0, $varPosiz );
            //$nc             = strlen($varResponse);
            //$nc             = ($nc - 17);
            $varRedirectURL = substr( $varResponse, $varPosiz + 1 );

            /* Creo l'URL di redirezione */
            $varRedirectURL = "$varRedirectURL?PaymentID=$varPaymentId";
            // echo $varRedirectURL;

            WPDKWatchDog::watchDog( __CLASS__, $varRedirectURL );

            /* Redirezione finale del browser sulla HPP */
            echo '<meta http-equiv="refresh" content="1;URL=' . $varRedirectURL . '">';

            return true;
        }
    }

    /// Transaction result
    /**
     * Questo metodo viene invocato sempre dallo shortcode 'wpss_payment_gateway' la seconda volta che viene chiamato, o
     * meglio, quando la WPXSmartShopSession::orderTrackID() di un checkout è valorizzata.
     *
     * @todo Rivedremo il naming dei parametri che arrivano in GET: vedi anche file MPS/Receipt.php
     *
     * @static
     * @retval array|bool
     */
    function transactionResult() {
        $payID         = $_GET["PaymentID"];
        $transationID  = $_GET["TransID"];
        $resultCode    = $_GET["resultcode"];
        $autCode       = $_GET["auth"];
        $posDate       = $_GET["postdate"];
        $trackID       = $_GET["TrackID"];
        $language_code = $_GET["udf1"];
        //        $UD2          = $_POST["udf2"];
        //        $UD3          = $_POST["udf3"];
        //        $UD4          = $_POST["udf4"];
        //        $UD5          = $_POST["udf5"];

        $data = array(
            'transactionID' => $transationID,
            'resultCode'    => $resultCode,
            'trackID'       => $trackID,
            'autCode'       => $autCode
        );

        WPDKWatchDog::watchDog( __CLASS__ );

        if ( empty( $payID ) ) {

            /* Error */
            WPDKWatchDog::watchDog( __CLASS__, 'Errore: paymentid vuoto: $_POST: ' . implode( ',', $_POST ) . ' $_REQUEST: ' . join( ',', $_REQUEST ) );

            $message = __( 'MPS Error. Payment id empty', WPXSMARTSHOP_TEXTDOMAIN );
            $error   = new WP_Error( 'wpss_error-mps_payment_id_empty', $message, $data );
            return $error;

        } else {
            if ( $resultCode == 'CAPTURED' || $resultCode == 'APPROVED' ) {

                /* Transaction completed successfully */
                WPDKWatchDog::watchDog( __CLASS__, 'Transazione Approvata: ' . join( ',', $data ) );

                return $data;

            } else {

                /* The transaction has been rejected */
                WPDKWatchDog::watchDog( __CLASS__, 'Transazione rigettata: ' . implode( ',', $data ) );

                $message = __( 'MPS Error! Transaction reject', WPXSMARTSHOP_TEXTDOMAIN );
                $error   = new WP_Error( 'wpss_error-mps_transaction_reject', $message, $data );
                return $error;
            }
        }
    }

    /// Transaction error
    function transactionError() {}

    // -----------------------------------------------------------------------------------------------------------------
    // Metodi specifici
    // -----------------------------------------------------------------------------------------------------------------

    /// MPS internal
    /**
     * Questo è un metodo di comodità usato durante l'invio con il metodo transaction(). Questo prepara l'array dei
     * parametri che vanno inviati.
     *
     * @static
     * @retval array
     */
    function dataToSend() {
        $options = self::options();

        /* Compatibilità WPML multilingua */
        $mps_language_code = $options['wpssMPSGatewayLangID'];
        $language_code     = '';
        if ( defined( 'ICL_LANGUAGE_CODE' ) ) {
            $language_code = ICL_LANGUAGE_CODE;

            /* @todo Da completare con altre lingue */
            $matrix = array(
                'it'    => 'ITA',
                'en'    => 'USA'
            );

            $mps_language_code = $matrix[$language_code];
        }

        $result = array(
            'id'           => $options['wpssMPSGatewayID'], // Lunghezza (8) Merchant ID
            'password'     => $options['wpssMPSGatewayPassword'], // (8)
            'action'       => $options['wpssMPSGatewayAction'], // (1) Tipo di transazione: 1=Purchase, 4=Authorization
            'amt'          => '', // (10) Amount, importo nel formato NNNNN.NN
            'currencycode' => $options['wpssMPSGatewayCurrencyCode'], // (3) Codice ISO valuta Fisso a “978” (Euro).
            'langid'       => $mps_language_code, // (3) Codice per impostare la lingua con cui verrà visualizzata la HPP al Cardholder. La HPP supporta le seguenti lingue: “ITA” = italiano “USA” = inglese “FRA” = francese “DEU” = Tedesco “ESP” = spagnolo “SLO” = sloveno
            'responseURL'  => $options['wpssMPSGatewayResponseURL'], // (256) URL che verrà utilizzato dal Payment Gateway per comunicare al Merchant l’esito della transazione tramite il NotificationMessage. L’URL specificato: non può puntare a porte diverse dalla 80 e 443 se punta a siti protetti da un certificato SSL, il certificato deve essere emesso da una delle Certification Authority elencate in Appendice D. In caso contrario il Merchant dovrà fornire al Consorzio Triveneto il/i certificato/i della/e Certification Authority che garantiscono l’autenticità del certificato del Merchant.
            'errorURL'     => $options['wpssMPSGatewayErrorURL'], // (256) URL che verrà utilizzato dal Payment Gateway per presentare al Cardholder una pagina di errore, in caso dovessero verificarsi degli inconvenienti nella comunicazione del NotificationMessage.
            'trackid'      => '', // Codice identificativo di 256 char della transazione impostato dal Merchant. Di solito è il codice identificativo dell’ordine di acquisto presso il sito del Merchant. E’ consigliabile che questo codice sia univoco per ogni transazione.
            'udf1'         => $language_code, // Campo a discrezione del Merchant per informazioni che desidera inserire e che verranno restituite inalterate nel NotificationMessage. Valorizzazione speciale: se impostato con “SHA1” permette di ricevere nel campo UDF1 del Notification Message il codice hash, calcolato con algoritmo SHA-1, della carta di credito usata dall’acquirente per il pagamento.
            'udf2'         => 'BB', // Campo a discrezione del Merchant per informazioni che desidera inserire e che verranno restituite inalterate nel NotificationMessage.
            'udf3'         => 'CC', // Campo a discrezione del Merchant per informazioni che desidera inserire e che verranno restituite inalterate nel NotificationMessage. Valorizzazione speciale: se inizia con “EMAILADDR:” la parte seguente del campo viene interpretata come l’indirizzo e-mail del Cardholder. Se il Merchant ha impostato (tramite back office) l’invio dell’e-mail con l’esito del pagamento al Cardholder, la pagina di pagamento conterrà un campo “Indirizzo e-mail” che il Cardholder potrà valorizzare per ricevere l’e-mail. Il campo può essere pre-valorizzato con l’indirizzo ricevuto nel campo UDF3 (che può riportare, ad esempio, l’indirizzo usato dal Cardholder per la registrazione sul sito del Merchant).
            'udf4'         => 'DD', // Campo a discrezione del Merchant per informazioni che desidera inserire e che verranno restituite inalterate nel NotificationMessage.
            'udf5'         => 'EE' // Campo a discrezione del Merchant per informazioni che desidera inserire e che verranno restituite inalterate nel NotificationMessage. Valorizzazione speciale: se inizia con “HPP_TIMEOUT=”<XX> imposta un timeout di <XX> minuti sulla HPP. Se il Cardholder rimane sulla pagina oltre questo periodo, il Payment Gateway non elaborerà la transazione, inviando lo specifico codice di errore CT0001.
        );
        return $result;
    }
}