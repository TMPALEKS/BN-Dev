// You need an anonymous function to wrap around your function to avoid conflict
(function ( $ ) {

    // Attach this new method to jQuery
    $.fn.extend( {

        // This is where you write your plugin's name
        scroller : function ( options ) {

            // options
            var defaults = {
                option1 : "default_value"
            }

            var options = $.extend( defaults, options );

            // Iterate over the current set of matched elements
            return this.each( function () {

                var o = options;
                var $this = $( this );
                var $scroller = $this.find('div' ).eq(0);

                var scroller_height = parseInt( $scroller.height() );
                var timing = (200 + scroller_height) * 50 ;

                $this.css( { height : '200px', overflow : 'hidden', marginBottom : '32px' } );

                $scroller.css( { marginTop : '200px' } );

                var moveup = function () {
                    var posy = parseInt( $scroller.css('marginTop') );
                    posy-= 2;
                    $scroller.css( { marginTop : posy + 'px' } );
                    if( posy < -scroller_height ) {
                        $scroller.css( { marginTop : '200px' } );
                    }
                }

                $scroller.animate( { marginTop: -scroller_height }, timing );

            } );
        }
    } );

//pass jQuery to the function, 
//So that we will able to use any valid Javascript variable name 
//to replace "$" SIGN. But, we'll stick to $ (I like dollar sign: ) )       
})
    ( jQuery );