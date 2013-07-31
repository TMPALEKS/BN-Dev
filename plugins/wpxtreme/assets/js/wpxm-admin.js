/**
 * @class           WPXtremeAdmin
 *
 * @description     Javascript per la parte backend amministrativa
 *
 * @package         wpXtreme
 * @subpackage      assets
 * @author          =undo= <g.fazioli@undolog.com>, <g.fazioli@wpxtre.me>
 * @copyright       Copyright (c) 2012 wpXtreme, Inc
 * @created         28/06/12
 * @version         1.0.0
 *
 */

var WPXtremeAdmin = ( function( $ ) {

    /**
     * Aggancia l'autocomplete ad un campo testo per visualizzare il "post_name" (lo slug) di una pagina. Questo viene
     * utilizzato nella sezione impostazioni utenti nella configurazione della registrazione.
     *
     * @todo Utilizzare il nuovo engine di WPDK
     * @todo Rinominare "email" con "mail"
     *
     */
    function slugPostEmail() {
        $( this ).autocomplete( {
            source    : function ( request, response ) {
                $.post( wpdk_i18n.ajaxURL, {
                        action : 'action_slug_post_email',
                        term   : request.term
                    },
                    function ( data ) {
                        response( $.parseJSON( data ) );
                    } );
            },
            minLength : 0
        } )
    }
    $( 'input[name=email_slug_confirmed]' ).live( 'focus', slugPostEmail );
    $( 'input[name=email_slug_confirm]' ).live( 'focus', slugPostEmail );

    /**
     * Utilizzato per inviare una mail di prova (in Ajax)
     */
    if ( $( 'input[name=wpxm_cpt_mail_test_sender]' ).length ) {
        $( 'input[name=wpxm_cpt_mail_test_sender]' ).click( function () {
            var to = $( 'input[name=wpxm_cpt_mail_test_to]' ).val();
            var id_post = $( 'input[name=wpxm_cpt_mail_test_sender]' ).data( 'id_post' );
            $.post( wpdk_i18n.ajaxURL, {
                    action  : 'action_send_email_test',
                    to      : to,
                    id_post : id_post
                },
                function ( data ) {
                    var result = $.parseJSON( data );

                    if ( typeof(result.message) != 'undefined' ) {
                        alert( result.message );
                    }
                } )
        } );
    }

})( jQuery);