/**
 * Featured slider in Home Page
 *
 * @package         Blue Note Milano
 * @subpackage      featured-home
 * @author          =undo= <g.fazioli@saidmade.com>
 * @copyright       Copyright (C) 2011-2012 Saidmade Srl.
 * @created         21/12/11
 * @version         1.0
 *
 */

jQuery( function ( $ ) {

    $( '.carousel' ).carousel();
    $( '.carousel' ).on( 'slid', function ( a ) {

        var index = $( this ).find( '.active' ).index();

        $( '.carousel-navigation span.active' ).removeClass( 'active' );

        $( '.carousel' ).carousel( index );

        $( '.carousel-navigation span' ).eq( index ).addClass( 'active' );

    } );

    $( '.carousel-navigation span' ).click( function () {
        $( '.carousel' ).carousel( $( this ).data( 'index' ) );
    } );

} );