/**
 * Addon per la pagina di Checkout di Smart Shop.
 * Questa viene caricata solo per l'utente Box Office e solo quando ci si trova nella pagina di Checkout
 *
 * @package         BNM Extends
 * @subpackage      checkout
 * @author          =undo= <g.fazioli@undolog.com>, <g.fazioli@saidmade.com>
 * @copyright       Copyright (C)2011 Saidmade Srl.
 * @created         19/12/11
 * @version         1.0
 *
 */

var checkout = (function($) {

    // -----------------------------------------------------------------------------------------------------------------
    // Private variables
    // -----------------------------------------------------------------------------------------------------------------

    var _self = {};

    /* Sub-regola per la validazione dei campi shipping solo per checkbox attivo */
    var differentShippingAddrees = {
        required : {
            depends : isCheckedDifferentShippingAddress
        }
    };

    /* Regole per la validazione del Form, in particolare per i campi di informazione billing and shipping */
    var _rules = {
        bill_first_name : "required",
        bill_last_name  : "required",
        bill_email      : "required",
        bill_address    : "required",
        bill_zipcode    : "required",
        bill_town       : "required",
        bill_country    : "required",

        company_name    : "required",
        vat_number      : "required",
        invoice_note    : "required",

        shipping_first_name : differentShippingAddrees,
        shipping_last_name  : differentShippingAddrees,
        shipping_address    : differentShippingAddrees,
        shipping_zipcode    : differentShippingAddrees,
        shipping_town       : differentShippingAddrees,
        shipping_country    : differentShippingAddrees
    };

    function isCheckedDifferentShippingAddress() {
        return $('#bnm-toggle-shipping-information').is(':checked');
    }

    // -----------------------------------------------------------------------------------------------------------------
    // Public properties
    // -----------------------------------------------------------------------------------------------------------------

    _self.wait16x16 = '<img style="float:right;display:block;width:16px;height:16px;border:none" border="0" alt="Wait" src="data:image/gif;base64,R0lGODlhEgASAMQaAHl5d66urMXFw3l5dpSUk5WVlKOjoq+vrsbGw6Sko7u7uaWlpbm5t3h4doiIhtLSz4aGhJaWlsbGxNHRzrCwr5SUkqKiobq6uNHRz4eHhf///wAAAAAAAAAAAAAAAAAAACH/C05FVFNDQVBFMi4wAwEAAAAh+QQFAAAaACwAAAAAEgASAAAFaaAmjmRplstyrkmbrCNFaUZtaFF0HvyhWRZNYVgwBY4BEmFJOB1NlYpJoYBpHI7RZXtZZb4ZEbd7AodFDIYVAjFJJCYA4ISoI0hyuUnAF2geDxoDgwMnfBoYiRgaDQ1WiIqPJBMTkpYaIQAh+QQFAAAaACwBAAEAEAAQAAAFY6AmjhpFkSh5rEc6KooWzIG2LOilX3Kd/AnSjjcyGA0oBiNlsZAkEtcoEtEgrghpYVsQeAVSgpig8UpFlQrp8Ug5HCiMHEPK2DOkOR0A0NzxJBMTGnx8GhAQZwOLA2ckDQ0uIQAh+QQFAAAaACwBAAEAEAAQAAAFZKAmjpqikCh5rVc6SpLGthSFIjiiMYx2/AeSYCggBY4B1DB1JD0ertFiocFYMdGENnHFugxgg2YyiYosFhIAkIpEUOs1qUAvkAb4gcbh0BD+BCgNDRoZhhkaFRVmh4hmIxAQLiEAIfkEBQAAGgAsAQABABAAEAAABWOgJo6aJJEoiaxIOj6PJsyCpigopmNyff0X0o43AgZJk0mKwSABAK4RhaJ5PqOH7GHAHUQD4ICm0YiKwCSHI7VYoDLwDClBT5Di8khEY+gbUBAQGgWEBRoWFmYEiwRmJBUVLiEAIfkEBQAAGgAsAQABABAAEAAABWSgJo7a85Aoia1YOgKAxraShMKwNk0a4iOkgXBAEhgFqEYjZSQ5HK6RQqHJWDPRi/Zyxbq2Fw0EEhUxGKRIJEWhoArwAulAP5AIeIJmsdAE/gEoFRUaCYYJfoFRBowGZSQWFi4hACH5BAUAABoALAEAAQAQABAAAAVloCaOGgCQKGma6eg42iAP2vOgWZ5pTaNhQAxJtxsFhSQIJDWZkCKR1kgi0RSuBSliiyB4CVKBWKCpVKQiMWmxSCkUqIQ8QbrYLySD3qChUDR3eCQWFhoHhwcaDAxoAY4BaCSOLSEAIfkEBQAAGgAsAQABABAAEAAABWOgJo6a45Aoma1ZOkaRxrYAgBZ4oUGQVtckgpBAGhgHqEol1WiQFgvX6PHQJK4JKWaLMXgNWq7GYpGKJhMShZKSSFCH+IGEqCNIgXxAo1BoBIACKHkaF4YXf4JSh4hmIwwMLiEAIfkEBQAAGgAsAQABABAAEAAABWSgJo5aFJEoWaxFOi6LRsyE5jhooidaVWmZYIZkKBpIwiHJYklBICQKxTUCADSH7IFqtQa+AepgPNB8qaJGg6RQpB4P1GV+IWHuGBK9LpFo8HkkDAwaCIYIGhMTaAKNAmgkjS4hADs=" />';

    // -----------------------------------------------------------------------------------------------------------------
    // Let's dance
    // -----------------------------------------------------------------------------------------------------------------

    /* Validazione del form */
    $( 'form.wpss-summary-order-form' ).validate( {
        ignore         : ":not(:visible)",
        errorPlacement : function ( error, element ) { /* empty */
        },
        ignoreTitle    : true,
        errorClass     : "wpdk-form-wrong",
        validClass     : 'wpdk-form-ok',
        rules          : _rules
    } );

    /* Toggle shipping  */
    $( '#bnm-toggle-shipping-information' ).click( toggleShippingAddress );

    /* Toggle invoice request */
    $( '#invoice_request' ).click( toggleInvoicerequest );

    $( '#bill_country' ).change( onChnageBillCountry );

    $( '#shipping_country' ).change( onChnageBillCountry );

    $( '#id_carrier' ).live( 'change', onChangeCarrier );

    /* Placeholders */
    $( 'a.bnm-button-dinner-choice' ).live( 'click', onOpenPlaceholdersReservations );

    /* combo select floor/environment */
    $( 'select.bnm-placeholder-select-environment' ).live( 'change', onChangeMap );

    /* free place */
    $( document ).on( 'wpph_plan_place_taken', onPlaceFreeClick );

    /* Avoid press return key */
    $( 'form' ).bind( "keypress", function ( e ) {
        if ( e.keyCode == 13 ) return false;
    } );

    // -----------------------------------------------------------------------------------------------------------------
    // Handlers
    // -----------------------------------------------------------------------------------------------------------------
    function onPlaceFreeClick( event, element, id ) {

        var id_product = $( element ).parents( '.bnm-dialog-reservations' ).attr( 'data-id_product' );

        /* Numero totale di Posti a sedere per place: 1 o 2 */
        var seats = 0;

        /* Numero tatale di Place selezionati */
        var places = 0;

        /* Se ho preso 2 Places da 2 posti a sedere, avrà seats = 4 e places = 2 */

        $( 'div#bnm-dialog-reservations-' + id_product + ' div.wpph-plan-place-taken' ).each( function ( index, element ) {
            seats += parseInt( $( element ).attr( 'data-place_size' ) );
            places++;
        } );

        var tickets = parseInt( $( 'a#bnm-button-dinner-choice-' + id_product ).attr( 'data-count_ticket' ) );

        if ( places > tickets || ( places <= tickets && seats > (tickets + 1) )  ) {
            alert( bnmExtendsJavascriptLocalization.placeholdersMaxPlacesMessage );
            return false;
        }

        var places = '';
        var list = {};
        $( 'div#bnm-dialog-reservations-' + id_product + ' div.wpph-plan-place-taken' ).each( function ( index, element ) {
            list[$( element ).attr( 'data-place_name' )] = $( element ).attr( 'data-environment_description' );
        } );

        for ( var e in list ) {
            var id = list[e] + '-' + e;
            places += (places == '') ? id : ',' + id;
        }

        $( 'input#table_selected-' + id_product ).val(places);

        return true;
    }

    /**
     * Controlla che si siano selezionati tutti i posti relativi ai biglietti scelti
     */
    function checkPlaces( id_product ) {
        /* Numero totale di Posti a sedere per place: 1 o 2 */
        var seats = 0;

        /* Numero tatale di Place selezionati */
        var places = 0;

        /* Se ho preso 2 Places da 2 posti a sedere, avrà seats = 4 e places = 2 */

        $( 'div#bnm-dialog-reservations-' + id_product + ' div.wpph-plan-place-taken' ).each( function ( index, element ) {
            seats += parseInt( $( element ).attr( 'data-place_size' ) );
            places++;
        } );

        var tickets = parseInt( $( 'a#bnm-button-dinner-choice-' + id_product ).attr( 'data-count_ticket' ) );

        if ( seats < tickets ) {
            alert( bnmExtendsJavascriptLocalization.placeholdersMinPlacesMessage );
            return false;
        }
        return true;
    }

    /**
     * Combo select
     */
    function onChangeMap() {
        var floor = $( this ).val();
        $( this ).parent().find( 'div.bnm-placeholder-environment' ).css( 'display', 'none' );
        $( this ).parent().find( 'div.bnm-placeholder-environment-' + floor ).fadeIn();
    }

    /**
     * Open dialog from summary order
     */
    function onOpenPlaceholdersReservations() {
        var id_product = $( this ).attr('data-id_product');
        var args = {
            modal     : true,
            resizable : false,
            draggable : true,
            closeText : bnmExtendsJavascriptLocalization.closeText,
            title     : bnmExtendsJavascriptLocalization.placeholdersReservationsTitle,
            width     : 684,
            height    : 780,
            minWidth  : 640,
            minHeight : 460,
            buttons   : [
                {
                    text  : bnmExtendsJavascriptLocalization.Cancel,
                    click : function () {
                        $( 'body' ).removeClass('bnm-tooltip-light');
                        $( this ).dialog( "close" );
                    }
                },
                {
                    text  : bnmExtendsJavascriptLocalization.Ok,
                    click : function () {
                        if( checkPlaces( id_product ) ) {
                            $( 'body' ).removeClass('bnm-tooltip-light');
                            $( this ).dialog( "close" );
                        }
                    }
                }
            ]
        };
        $( 'body' ).addClass('bnm-tooltip-light');
        $( '#bnm-dialog-reservations-' + id_product ).dialog( args );
        return false;
    }

    function toggleShippingAddress() {
        if ( $( this ).is( ':checked' ) ) {
            $( '#wpssPersonalInformationShippingAddress' ).slideDown();
        } else {
            $( '#wpssPersonalInformationShippingAddress' ).slideUp();
        }
    }

    function toggleInvoicerequest() {
        if ( $( this ).is( ':checked' ) ) {
            $( 'form.wpss-summary-order-form fieldset.wpdk-form-section2' ).slideDown();
        } else {
            $( 'form.wpss-summary-order-form fieldset.wpdk-form-section2' ).slideUp();
        }
    }

    function onChnageBillCountry() {

        /* Se ho selezionato 'indirizzo di spedizione diverso' non devo più considerare bill_country */
        if( $('#bnm-toggle-shipping-information').is(':checked') && $(this).attr('id') == 'bill_country' ) {
            return;
        }

        var id_country = $( this ).val();
        $( 'td.wpss-summary-order-row-value-shipping' ).html( _self.wait16x16 );
        $.post( bnmExtendsJavascriptLocalization.ajaxURL,
            {
                action     : 'action_summary_order_changed_id_country',
                id_country : id_country
            }, function ( data ) {
                var result = $.parseJSON( data );

                if ( typeof(result.message) != 'undefined' ) {
                    alert( result.message );
                }
                $( 'table.wpss-summary-order' ).replaceWith( result.content );
            } );
    }

    function onChangeCarrier() {
        var id_carrier = $( this ).val();
        $( 'td.wpss-summary-order-row-value-shipping' ).html( _self.wait16x16 );
        $.post( bnmExtendsJavascriptLocalization.ajaxURL,
            {
                action     : 'action_summary_order_changed_id_carrier',
                id_carrier : id_carrier
            }, function ( data ) {
                var result = $.parseJSON( data );

                if ( typeof(result.message) != 'undefined' ) {
                    alert( result.message );
                }
                $( 'table.wpss-summary-order' ).replaceWith( result.content );
            } );
    }

    // -----------------------------------------------------------------------------------------------------------------
    // Utility & commodity
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Only debug
     *
     * @param obj
     */
    _self.debug = function (obj) {
        var output = '';
        for (property in obj) {
            output += property + ': ' + obj[property] + ';\n';
            if( typeof window.console !== 'undefined' ) {
                console.log(output);
            }
        }
        return(output);
    }

    /* Version */
    _self.version = "1.0";

    // -----------------------------------------------------------------------------------------------------------------
    // End
    // -----------------------------------------------------------------------------------------------------------------

    return _self;

})(jQuery);