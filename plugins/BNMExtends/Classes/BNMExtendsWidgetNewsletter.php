<?php
/**
 * @class              BNMExtendsWidgetNewsletter
 * @description        Widget per l'iscrizione alla Newsletter di Mailchimp
 *
 * @package            BNMExtends
 * @author             =undo= <g.fazioli@saidmade.com>
 * @copyright          Copyright (C) 2011-2012 Saidmade, Srl.
 * @created            22/12/11
 * @version            1.0
 *
 * @uses               Mail Chimp API 1.3
 *
 * @todo               Questo Widget potrebbe essere espanso implementando l'APi Key e l'ID della lista come opzioni del Widget
 *
 */

require_once( 'MCAPI/MCAPI.class.php' );

class BNMExtendsWidgetNewsletter extends WP_Widget {

    private static $apiKey = 'ef733f5711206d9bb8ebb0e66aea1829-us2';
    private static $idList = '62324faf2f';

    function __construct() {
        $widget_ops = array();
        $this->WP_Widget( 'bnm_mailchimp_widget', __( 'Mail Chimp Newsletter', 'bnmextends' ), $widget_ops );
    }

    // -----------------------------------------------------------------------------------------------------------------
    // Static values
    // -----------------------------------------------------------------------------------------------------------------

    function widget( $args, $instance ) {
        $before_widget = '';
        $after_widget  = '';

        extract( $args );
        echo $before_widget;
        ?>
    <h2><?php _e( 'Join our Mailing List', 'bnmextends' ) ?></h2>
    <form id="signup" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="get">

        <p class="alignright"><label for="email" id="address-label"><?php _e( 'Email', 'bnmextends' ) ?>:</label>
            <input class="wpdk-form-input wpdk-form-email" type="text" name="email" id="email"/></p>

        <p class="alignright"><input type="submit" name="submit" value="<?php _e( 'Send', 'bnmextends' ) ?>"
                                     class="button blue"/></p>

        <div id="bnmMCResponse"></div>

    </form>

    <script type="text/javascript">
        jQuery( document ).ready( function ( $ ) {
            $( '#signup' ).submit( function () {
                // update user interface
                $( '#bnmMCResponse' ).html( '<?php _e( 'Adding email address...', 'bnmextends' ) ?>' ).slideDown();

                $.post( '<?php echo WPDKWordPress::ajaxURL() ?>', {
                        action : 'action_mailchimp_store_address',
                        email  : escape( $( '#email' ).val() )
                    }, function ( data ) {
                        $( '#bnmMCResponse' ).slideUp( function () {
                            $( this ).html( data ).slideDown();
                            $( '#email' ).val( '' );
                        } );
                    }
                );

                return false;
            } );
        } );
    </script>

    <?php
        echo $after_widget;
    }

    // -----------------------------------------------------------------------------------------------------------------
    // Static methods
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Invia a Mail Chimp una richiesta di registrazione alla Newsletter
     *
     * @static
     *
     * @param string $email
     *
     * @return string|void
     */
    public static function storeAddress( $email = null ) {

        if ( is_null( $email ) ) {
            $email = $_POST['email'];
        }

        if ( !$email ) {
            return __( 'No email address provided', 'bnmextends' );
        }

        if ( !preg_match( "/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*$/i", $email ) ) {
            return __( 'Email address is invalid', 'bnmextends' );
        }

        // grab an API Key from http://admin.mailchimp.com/account/api/
        $api = new MCAPI( self::$apiKey );

        // grab your List's Unique Id by going to http://admin.mailchimp.com/lists/
        // Click the "settings" link for the list - the Unique Id is at the bottom of that page.
        $list_id = self::$idList;

        if ( $api->listSubscribe( $list_id, $email, '' ) === true ) {
            // It worked!
            return __( 'Success! Check your email to confirm sign up', 'bnmextends' );
        } else {
            // An error ocurred, return error message
            $error = sprintf( '%s: %s', __( 'Error', 'bnmextends' ), $api->errorMessage );
            return $error;
        }
    }
}