/**
 * Gestione profilo
 *
 * @package         Blue Note Milano
 * @subpackage      profile
 * @author          =undo= <g.fazioli@undolog.com>, <g.fazioli@saidmade.com>
 * @copyright       Copyright (C)2012 Saidmade Srl.
 * @created         12/01/12
 * @version         1.0
 *
 */

var Profile = (function($) {

    // -----------------------------------------------------------------------------------------------------------------
    // Private variables
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Internal class pointer
     */
    var _profile = {};

    /* Date picker defaults */
    $.datepicker.setDefaults( {
        yearRange   : '1900:2000',
        defaultDate : '-50y'
    } );

    /*
     I Campi input di tipo file non possono essere resettati con val('') ma devono essere sovrascritti in jQuery
     Quindi prima di tutto, appena parto, me ne faccio uno copia in modo da non inviare attachment quando non
     necessario
     */
    var file_under_26 = $('#upload_pdf_under_26_id').parent().html();
    var file_over_65 = $('#upload_pdf_over_65_id').parent().html();
    var file_cral = $('#upload_pdf_cral').parent().html();


    /**
     * Classe per validare il form di modifica profilo
     *
     * @package         Blue Note Milano
     * @subpackage      profile
     * @since 1.0.0
     *
     */
    var Validate = (function () {

        var _validate = {};

        function init() {

            /* Focus su Nome*/
            $('input#bnmUserFirstname').focus();

            /* Solo admin: bottone edit */
            if($('#bnm-profile-button-edit').length) {
                $('#bnm-profile-button-edit').click(onEditButtonClick);
            }

            /* Se selezione 'nessuno' nel combo riazzero il file input per l'upload*/
             $('#associations').change(onAssociationChange);

            /* Controllo età per mostrare under 26 o over 65*/
            $('input#birth_date').change(onBirthDateChanged);

            /* Mostra le informazioni sullo shipping */
            $('input#shipping_address_different').click(onShippingAddressDifferent);

            /* Regole di validazione del form profilo */
            var rules = {
                first_name : "required",
                last_name  : "required",

                email        : {
                    required : true,
                    email    : true
                },
                email_repeat : {
                    equalTo : "#email"
                },
                password : {
                    required    : {
                        depends : function (element) {
                            var s = $(element).val();
                            return (s.length > 0);
                        }
                    },
                    rangelength : [6, 12]
                },

                password_repeat    : {
                    equalTo : "#password"
                },

                privacy_agree_a : "required",
                privacy_agree_b : "required",
                privacy_agree_c : "required"
            };

            /* Registro validazione */
            $('form.bnm-profile').validate({
                errorPlacement : function (error, element) {
                },
                ignoreTitle    : true,
                errorClass     : "wpdk-form-wrong",
                validClass     : 'wpdk-form-ok',
                rules          : rules
            });

            // ---------------------------------------------------------------------------------------------------------
            // Event functions
            // ---------------------------------------------------------------------------------------------------------

            /* Controllo età per mostrare under 26 o over 65 */
            function onBirthDateChanged() {
                var birth_date = $( this ).val();
                if ( birth_date == '' ) {
                    $( 'fieldset.wpdk-form-section5' ).slideUp(); //
                    $( 'fieldset.wpdk-form-section6' ).slideUp(); //

                    $( '#upload_pdf_under_26_id' ).parent().html( file_under_26 );
                    $( '#upload_pdf_over_65_id' ).parent().html( file_over_65 );

                    return;
                }
                /* Chiama una funzione php via Ajax che esegue il calcolo dell'età*/
                $.post( bnmExtendsJavascriptLocalization.ajaxURL,
                    {
                        action     : 'action_user_age',
                        birth_date : birth_date
                    },
                    function ( data ) {
                        if ( data <= 26 ) {
                            alert( bnmExtendsJavascriptLocalization.under26 );
                            $( 'fieldset.wpdk-form-section5' ).slideDown(); //
                            $( 'fieldset.wpdk-form-section6' ).slideUp(); //
                            $( '#upload_pdf_over_65_id' ).parent().html( file_over_65 );
                        } else if ( data >= 65 ) {
                            alert( bnmExtendsJavascriptLocalization.over65 );
                            $( 'fieldset.wpdk-form-section5' ).slideUp(); //
                            $( 'fieldset.wpdk-form-section6' ).slideDown(); //
                            $( '#upload_pdf_under_26_id' ).parent().html( file_under_26 );
                        } else {
                            $( 'fieldset.wpdk-form-section5' ).slideUp(); //
                            $( 'fieldset.wpdk-form-section6' ).slideUp(); //

                            $( '#upload_pdf_under_26_id' ).parent().html( file_under_26 );
                            $( '#upload_pdf_over_65_id' ).parent().html( file_over_65 );
                        }
                    } );
            }

            /* Se selezione 'nessuno' nel combo riazzero il file input per l'upload */
            function onAssociationChange() {
                if ( $( this ).val() == '' ) {
                    $( '#upload_pdf_cral' ).parent().html( file_cral );
                }
            }

            /* Checkbox per shipping address differente */
            function onShippingAddressDifferent() {
                if ( $( this ).is( ':checked' ) ) {
                    $( 'form.bnm-profile fieldset.wpdk-form-section4' ).slideDown(); //
                } else {
                    $( 'form.bnm-profile fieldset.wpdk-form-section4' ).slideUp(); //
                }
            }

            /* Solo admin: bottone di edit utente */
            function onEditButtonClick() {
                $( 'form#bnm-edit-user #id' ).val( $( '#user_id' ).val() );
                $( 'form#bnm-edit-user' ).submit();
            }

            return _validate;
        }

        return init();

    })();

    // -----------------------------------------------------------------------------------------------------------------
    // Public properties
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Version
     */
    _profile.version = "1.0";

    // -----------------------------------------------------------------------------------------------------------------
    // End
    // -----------------------------------------------------------------------------------------------------------------

    return _profile;

})(jQuery);