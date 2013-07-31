<?php
/**
 * Classe per la gestione del modulo contatti
 *
 * @package            BNMExtends
 * @subpackage         BNMExtendsContacts.php
 * @author             =undo= <g.fazioli@undolog.com>, <g.fazioli@saidmade.com>
 * @copyright          Copyright (C)2011 Saidmade Srl.
 * @created            21/12/11
 * @version            1.0
 *
 */

class BNMExtendsContacts {

    /* Solo per debug */
    private static $test = false;
    private static $testEmail = 'enrico.corinti@gmail.com';

    //Captcha Keys
    private static $recaptcha_privatekey ="6LcXjt4SAAAAALZ3PboBTDRVy0a2LvBFw3p7BtId";
    private static $recaptcha_public_key = '6LcXjt4SAAAAAHVZ_0eXET_yd8OwaCrTXuEE4Uaq';



    function __construct() {
    }

    // -----------------------------------------------------------------------------------------------------------------
    // Static values
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Lista dei destinatari (subject) della email
     *
     * @static
     *
     */
    private static function emails() {
        $emails = array(
            'info@bluenotemilano.com '            => __( 'Contact the customer service', 'bnmextends' ),
            'mara.ferrari@bluenotemilano.com'     => __( 'Book for a group or private event', 'bnmextends' ),
            'fabio.mittino@bluenotemilano.com'    => __( 'Perform at the Blue Note', 'bnmextends' ),
            'daniele.genovese@bluenotemilano.com' => __( 'Submit a product or service', 'bnmextends' ),
            'info@paroleedintorni.it'             => __( 'Contact the press office', 'bnmextends' )
        );
        return $emails;
    }

    /**
     * Formattazione Form nello standard WPDKForm
     *
     * @static
     * @return array
     */
    public static function fields() {
        $fields = array(
            __( 'Contact Us', 'bnmextends' ) => array(
                __( 'Please fill out all fields. Company is optional', 'bnmextends' ),
                array(
                    array(
                        'type'    => WPDK_FORM_FIELD_TYPE_SELECT,
                        'name'    => 'bnmContactsSubject',
                        'label'   => __( 'I want to', 'bnmextends' ),
                        'options' => self::emails(),
                    )
                ),
                array(
                    array(
                        'type'  => WPDK_FORM_FIELD_TYPE_TEXT,
                        'name'  => 'bnmContactsFirstname',
                        'label' => __( 'First Name', 'bnmextends' )
                    )
                ),
                array(
                    array(
                        'type'  => WPDK_FORM_FIELD_TYPE_TEXT,
                        'name'  => 'bnmContactsLastname',
                        'label' => __( 'Last Name', 'bnmextends' )
                    )
                ),
                array(
                    array(
                        'type'  => WPDK_FORM_FIELD_TYPE_TEXT,
                        'name'  => 'bnmContactsCompany',
                        'label' => __( 'Company', 'bnmextends' )
                    )
                ),
                array(
                    array(
                        'type'  => WPDK_FORM_FIELD_TYPE_EMAIL,
                        'name'  => 'bnmContactsEmail',
                        'label' => __( 'Email', 'bnmextends' )
                    )
                ),
                array(
                    array(
                        'type'  => WPDK_FORM_FIELD_TYPE_PHONE,
                        'name'  => 'bnmContactsPhone',
                        'label' => __( 'Phone', 'bnmextends' )
                    )
                ),
                array(
                    array(
                        'type'  => 'textarea',
                        'name'  => 'bnmContactsBody',
                    )
                ),
            )
        );
        return $fields;
    }

    // -----------------------------------------------------------------------------------------------------------------
    // Form contacts
    // -----------------------------------------------------------------------------------------------------------------

    public static function contacts() {
        require_once( 'Libs/recaptchalib.php' );

        $isvalid = true;


        if( $_POST["recaptcha_response_field"] != "" ):

            $resp = recaptcha_check_answer (self::$recaptcha_privatekey,
                $_SERVER["REMOTE_ADDR"],
                $_POST["recaptcha_challenge_field"],
                $_POST["recaptcha_response_field"]);


            $isvalid = $resp->is_valid;
        endif;

        if ( WPDKForm::isNonceVerify( 'contacts' ) && $isvalid) {
            self::sendMail();
        } else {
            self::form( $isvalid );
        }

    }

    /**
     * ReCaptch Fields
     * @var $recaptcha_public_key = '6LcXjt4SAAAAAHVZ_0eXET_yd8OwaCrTXuEE4Uaq'
     */
    private static function captchaFields(){

        $publickey = self::$recaptcha_public_key;

        $captcha_script = <<< SCRIPT
        <!-- Recaptcha Fields -->
            <legend>Codice di sicurezza</legend>
            <script type="text/javascript"
                    src="http://www.google.com/recaptcha/api/challenge?k={$publickey}">
            </script>
            <noscript>
                <iframe src="http://www.google.com/recaptcha/api/noscript?k={$publickey}"
                        height="400" width="700" frameborder="0"></iframe><br>
                <textarea name="recaptcha_challenge_field" rows="3" cols="40">
                </textarea>
                <input type="hidden" name="recaptcha_response_field"
                       value="manual_challenge">
            </noscript>
        <!-- End Recaptcha Fields -->

SCRIPT;
        return $captcha_script;
    }

    /**
     * Mostra la form dei contatti, creando in nonce WordPress per l'invio
     *
     * @static
     *
     */
    private static function form( $isvalid) {
        ?>
    <form class="bnmContacts wpdk-form" name="" method="post" action="">

        <?php WPDKForm::nonceWithKey( 'contacts' ) ?>
        <?php WPDKForm::htmlForm( self::fields() ); ?>
        <fieldset class="wpdk-form-fieldset wpdk-form-section2">
            <?php echo self::captchaFields();
            if (!$isvalid)
                echo '<span class="wpdk-form-description error">Il codice di sicurezza non &eacute; esatto. Reinserire il codice.  </span>';
            ?>
        </fieldset>

        <p class="alignright">
            <input type="submit" class="button orange" value="<?php _e( 'Send', 'bnmextends' ) ?>"/>
        </p>
    </form>
    <?php
    }

    /**
     * Invia fisicamente la mail
     *
     * @static
     */
    private static function sendMail() {

        /* Invio mail */
        if ( self::$test ) {
            $email_to = self::$testEmail;
        } else {
            $email_to = $_POST['bnmContactsSubject'];
        }

        $email_subject = 'Contatto dal Web: ' . $_POST['bnmContactsFirstname'] . ' ' . $_POST['bnmContactsLastname'];
        $mail_body     = nl2br( $_POST['bnmContactsBody'] );

        $email_message = <<< HTML
    <p>{$_POST['bnmContactsFirstname']} {$_POST['bnmContactsLastname']}</p>
    <p>A: {$_POST['bnmContactsSubject']}</p>
    <p>Tel. {$_POST['bnmContactsPhone']}</p>
    <p>Email: {$_POST['bnmContactsEmail']}</p>
    <p>{$mail_body}</p>
HTML;
        $headers       = array();
        $headers[]     =
            sprintf( 'From: %s %s <%s>', $_POST['bnmContactsFirstname'], $_POST['bnmContactsLastname'], $_POST['bnmContactsEmail'] ) .
                WPDK_CRLF;

        if ( defined( 'BNMEXTENDS_BCC_EMAIL_ADDRESSS' ) ) {
            $bcc = BNMEXTENDS_BCC_EMAIL_ADDRESSS;
            if ( !empty( $bcc ) ) {
                $headers[] = 'Bcc:' . BNMEXTENDS_BCC_EMAIL_ADDRESSS . WPDK_CRLF;
            }
        }

        $headers[] = 'Content-Type: text/html' . WPDK_CRLF;

        wp_mail( $email_to, $email_subject, $email_message, $headers );

        /* Invio mail all'utente */
        BNMExtendsMail::thanksForContacts( $_POST['bnmContactsEmail'] );

        echo wpdk_content_page_with_slug( 'grazie-per-averci-contattato', kBNMExtendsSystemPagePostTypeKey );
    }

}