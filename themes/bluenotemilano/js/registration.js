/**
 * Javascript per la form di registrazione
 *
 * @package         Blue Note Milano
 * @subpackage      registration
 * @author          =undo= <g.fazioli@undolog.com>, <g.fazioli@saidmade.com>
 * @copyright       Copyright (C)2011 Saidmade Srl.
 * @created         09/12/11
 * @version         1.0
 *
 */

var Registration = (function ($) {

    // -----------------------------------------------------------------------------------------------------------------
    // Private variables
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Internal class pointer
     */
    var _self = {};

    /* Date picker defaults */
    $.datepicker.setDefaults( {
        yearRange   : '1900:2000',
        defaultDate : '-50y'
    } );

    var Validate = (function () {

        var _validate = {};

        /*
         I Campi input di tipo file non possono essere resettati con val('') ma devono essere sovrascritti in jQuery
         Quindi prima di tutto, appena parto, me ne faccio uno copia in modo da non inviare attachment quando non
         necessario
         */
        var file_under_26 = $('#upload_pdf_under_26_id').parent().html();
        var file_over_65 = $('#upload_pdf_over_65_id').parent().html();
        var file_cral = $('#upload_pdf_cral').parent().html();

        function init() {

            // Focus sul primo campo del modulo
            $('input#first_name').focus();

            // Se selezione 'nessuno' nel combo riazzero il file input per l'upload
            $('#associations').change(function() {
                if($(this).val() == '') {
                    $('#upload_pdf_cral').parent().html(file_cral);
                }
            });

            // Controllo età per mostrare under 26 o over 65
            $('input#birth_date').change(function () {
                var birth_date = $(this).val();
                if (birth_date == '') {
                    $('fieldset.wpdk-form-section5').slideUp(); //
                    $('fieldset.wpdk-form-section6').slideUp(); //

                    $('#upload_pdf_under_26_id').parent().html(file_under_26);
                    $('#upload_pdf_over_65_id').parent().html(file_over_65);

                    return;
                }
                // Chiama una funzione php via Ajax che esegue il calcolo dell'età
                $.post(bnmExtendsJavascriptLocalization.ajaxURL,
                    {
                        action     : 'action_user_age',
                        birth_date : birth_date
                    },
                    function (data) {
                        if (data <= 26) {
                            alert(bnmExtendsJavascriptLocalization.under26);
                            $('fieldset.wpdk-form-section5').slideDown(); //
                            $('fieldset.wpdk-form-section6').slideUp(); //
                            $('#upload_pdf_over_65_id').parent().html(file_over_65);
                        } else if (data >= 65) {
                            alert(bnmExtendsJavascriptLocalization.over65);
                            $('fieldset.wpdk-form-section5').slideUp(); //
                            $('fieldset.wpdk-form-section6').slideDown(); //
                            $('#upload_pdf_under_26_id').parent().html(file_under_26);
                        } else {
                            $('fieldset.wpdk-form-section5').slideUp(); //
                            $('fieldset.wpdk-form-section6').slideUp(); //

                            $('#upload_pdf_under_26_id').parent().html(file_under_26);
                            $('#upload_pdf_over_65_id').parent().html(file_over_65);
                        }
                    });
            });

            // Regole di validazione del form profilo
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

                privacy_agree_a : "required",
                privacy_agree_b : "required",
                privacy_agree_c : "required"
            };

            // Registro Validate
            $('form.bnm-profile').validate({
                errorPlacement : function (error, element) {
                },
                ignoreTitle    : true,
                errorClass     : "wpdk-form-wrong",
                validClass     : 'wpdk-form-ok',
                rules          : rules
            });

            // Mostra le informazioni sullo shipping
            $('input#shipping_address_different').click(
                function () {
                    if ($(this).is(':checked')) {
                        $('form.bnm-profile fieldset.wpdk-form-section4').slideDown(); //
                    } else {
                        $('form.bnm-profile fieldset.wpdk-form-section4').slideUp(); //
                    }
                }
            );

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
    _self.version = "1.0";

    // -----------------------------------------------------------------------------------------------------------------
    // End
    // -----------------------------------------------------------------------------------------------------------------

    return _self;

})(jQuery);