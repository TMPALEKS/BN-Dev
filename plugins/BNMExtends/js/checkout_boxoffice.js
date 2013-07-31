/**
 * Javascript per operatore box office
 *
 * @package         BNMExtends
 * @subpackage      checkout_boxoffice
 * @author          =undo= <g.fazioli@undolog.com>, <g.fazioli@saidmade.com>
 * @copyright       Copyright (c) 2012 Saidmade Srl.
 * @link            http://www.saidmade.com
 * @created         17/01/12
 * @version         1.0.0
 *
 */

var checkout_boxoffice = (function (parent, $) {

    // -----------------------------------------------------------------------------------------------------------------
    // Private variables
    // -----------------------------------------------------------------------------------------------------------------

    // ---------------------------------------------------------------------------------------------------------
    // Let's dance
    // ---------------------------------------------------------------------------------------------------------

    /* @todo Verificare che non sia possibile farlo da PHP */
    $( 'span.wpdk-form-description' ).html( bnmExtendsJavascriptLocalization.boxOfficerMessage );

    /* Autocomplete per trovare le info di un utente terzo - operatore box officer */
    $( 'input#bill_email' ).autocomplete( {
        source    : function ( request, response ) {
            $.post( bnmExtendsJavascriptLocalization.ajaxURL,
                {
                    action : 'action_user_by_email',
                    term   : request.term
                },
                function ( data ) {
                    response( $.parseJSON( data ) );
                } );
        },
        select    : function ( event, ui ) {
            $( 'input[name=id_user_order]' ).val( ui.item.id );

            if( $.trim(ui.item.role) != '' ) {
                $( 'span.bnm-user-role' )
                    .addClass( 'orange' )
                    .html( ui.item.role );
            } else {
                $( 'span.bnm-user-role' )
                    .removeClass( 'orange' )
                    .html( '' );
            }

            $( 'input#bill_first_name' ).val( ui.item.bill_first_name );
            $( 'input#bill_last_name' ).val( ui.item.bill_last_name );
            $( 'input#bill_address' ).val( ui.item.bill_address );
            $( 'input#bill_zipcode' ).val( ui.item.bill_zipcode );
            $( 'input#bill_town' ).val( ui.item.bill_town );

            $( 'select#bill_country' ).val( ui.item.bill_country );

            $( 'input#bill_phone' ).val( ui.item.bill_phone );
            $( 'input#bill_mobile' ).val( ui.item.bill_mobile );
        },
        minLength : 0
    } );

    // ---------------------------------------------------------------------------------------------------------
    // Gestione sconti
    // ---------------------------------------------------------------------------------------------------------
	$( 'select.bnm-summary-order-discount' ).live( 'change', onChangeDiscount );

    // -----------------------------------------------------------------------------------------------------------------
    // Handlers
    // -----------------------------------------------------------------------------------------------------------------
    function onChangeDiscount() {

        var id_product_key = $( this ).attr( 'data-id_product_key' );
        var id_custom_discount = $( this ).val();

        savePlacesTables(); //Salva i places

//        var id_part = id.split( '-' );
//        var productID = id_part[1];
//        var discountID = $( this ).val();
//        $( this ).parents( 'tr' ).find( 'td.wpss-summary-order-cell-product_price' ).html( parent.wait16x16 );
        $.post( bnmExtendsJavascriptLocalization.ajaxURL,
            {
                action     : 'action_summary_order_apply_discount',
                id_custom_discount : id_custom_discount,
                id_product_key  : id_product_key
            }, function ( data ) {
                var result = $.parseJSON( data );

                if ( typeof result.message !== 'undefined' ) {
                    alert( result.message );
                }
                //$( 'table.wpss-summary-order' ).replaceWith( result.content );
                $( 'div.wpss-summary-order-container' ).replaceWith( result.content );
                restorePlacesTables();
                WPDK.refresh();
            } );
    }

    /* Update Quantity Cart for Summary Order */
    $( 'form.wpss-summary-order-form input.qty' ).live( 'change', onChangeProductQuantityFix);

    function onChangeProductQuantityFix(){
        savePlacesTables();
    }

    function savePlacesTables(){
        var places_memory = $('.wpss-summary-order-cell-product input');
        $('#wpss-tables-memory-fields').html('');
        places_memory.each(function(count,item){
            $('#wpss-tables-memory-fields').append( $(this) );
        });
    }

    function restorePlacesTables(){
        $('#wpss-tables-memory-fields input').each(function(){
            $('.wpss-summary-order-cell-product input').val( $(this).val() );
        });
    }

    $(document).ajaxSuccess(function() {
        restorePlacesTables();
    });

    // -----------------------------------------------------------------------------------------------------------------
    // End
    // -----------------------------------------------------------------------------------------------------------------

    return parent;

})(checkout, jQuery);