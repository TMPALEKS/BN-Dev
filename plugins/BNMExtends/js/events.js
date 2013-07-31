/**
 * Javascript per la gestione degli eventi lato Backend
 *
 * @package         BNMExtends
 * @subpackage      events
 * @author          =undo= <g.fazioli@saidmade.com>
 * @copyright       Copyright © 2010-2011 Saidmade Srl
 *
 */

function debugObject( obj ) {
    var output = '';
    for ( property in obj ) {
        output += property + ': ' + obj[property] + ';\n';
    }
    return(output);
}

/* trigger when page is ready */
jQuery( document ).ready( function ( $ ) {

    $.ui.autocomplete.prototype._renderItem = function ( ul, item ) {
        // <li class="ui-menu-item" role="menuitem"><a class="ui-corner-all" tabindex="-1">Natalie</a></li>
        return $( '<li class="ui-menu-item-with-icon"></li>' )
            .data( "item.autocomplete", item )
            .append( '<a><img src="' + item.icon + '" class="bnmArtistThumbnailMedium" />' + item.label + '</a>' )
            .appendTo( ul );
    };

    /**
     * Autocomplete su Artisti
     */
    $( 'input#bnm-event-artist-name' ).live( 'focus', function () {
        $( this ).autocomplete( {
            source    : function ( request, response ) {
                $.post( bnmExtendsJavascriptLocalization.ajaxURL, {
                        action : 'action_artist_by_title',
                        term   : request.term
                    },
                    function ( data ) {
                        response( $.parseJSON( data ) );
                    } );
            },
            select    : function ( event, ui ) {
                $( this ).next().val( ui.item.id );
                $( this ).siblings( 'img.bnmArtistThumbnail' ).attr( 'src', ui.item.icon ).fadeIn();
                $( this ).siblings( 'img.bnmArtistThumbnailBig' ).attr( 'src', ui.item.icon )
            },
            minLength : 0
        } )
    } );

    $( '.bnmArtistThumbnail' ).live( 'mouseenter', function () {
        //$(this).animate({maxWidth: 55, maxHeight:55, width: 55, height: 55});
        var position = $( this ).position();
        $( this ).next().css( {top : position.top - 65, left : position.left - 24} ).fadeIn();

    } );

    $( '.bnmArtistThumbnail' ).live( 'mouseleave', function () {
        //$(this).animate({maxWidth: 16, maxHeight:16, width: 16, height: 16});
        $( this ).next().fadeOut();
    } );

    /**
     * Data e ora di un evento
     */
    $( 'input.bnmDateTime' ).datetimepicker( {
        timeOnlyTitle : bnmExtendsJavascriptLocalization.timeOnlyTitle,
        timeText      : bnmExtendsJavascriptLocalization.timeText,
        hourText      : bnmExtendsJavascriptLocalization.hourText,
        minuteText    : bnmExtendsJavascriptLocalization.minuteText,
        secondText    : bnmExtendsJavascriptLocalization.secondText,
        currentText   : bnmExtendsJavascriptLocalization.currentText,
        dayNamesMin   : (bnmExtendsJavascriptLocalization.dayNamesMin).split( ',' ),
        monthNames    : (bnmExtendsJavascriptLocalization.monthNames).split( ',' ),
        closeText     : bnmExtendsJavascriptLocalization.closeText,
        dateFormat    : bnmExtendsJavascriptLocalization.dateFormat
    } );

} );