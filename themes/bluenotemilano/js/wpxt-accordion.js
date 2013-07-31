/**
 * Special auto Collapse accordion
 *
 * @package         WPXtreme
 * @subpackage      WPXtremeAccordion
 * @author          =undo= <g.fazioli@undolog.com>, <g.fazioli@saidmade.com>
 * @copyright       Copyright (c) 2012 Saidmade Srl.
 * @link            http://www.saidmade.com
 * @created         10/02/12
 * @version         1.0.0
 *
 */

var WPXtremeAccordion = (function ( $ ) {

    // -----------------------------------------------------------------------------------------------------------------
    // Private variables
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Internal class pointer
     */
    var _self = {};

    var speed = 200;

    // -----------------------------------------------------------------------------------------------------------------
    // Auto Collapse Accordion
    // -----------------------------------------------------------------------------------------------------------------

    $('h3').addClass('wpxt-accordion-title').click(onAccordionTitleClick);


    $('h3').each(function(){
        $(this).nextUntil("h3").wrapAll('<div class="wpxt-accordion-content clearfix" />');
    });

    $( 'div.wpxt-accordion-content' ).each(function(){
        var h = parseInt( $(this).height() ) + 110;
        //$(this).css('height', h );
    });

    $('h3:first').next( 'div.wpxt-accordion-content' ).slideDown('slow');

    //$('#accordion').accordion();

    //$('h3').parent().wrap('<div id="accordion">');

    function onAccordionTitleClick() {
        var lastVisible = $( 'div.wpxt-accordion-content:visible' );
        if ( !$( this ).next( 'div.wpxt-accordion-content' ).is( ':visible' ) ) {
            $( this ).next( 'div.wpxt-accordion-content' ).slideDown( speed );
            lastVisible.slideUp( speed );
        }
    }

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

})( jQuery );