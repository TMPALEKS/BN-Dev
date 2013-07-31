/* trigger when page is ready */
jQuery( document ).ready( function ( $ ) {

    /* Usato per debug del top header bannerize. */
    if ( 1 == 0 ) {
        $( '.socialButtons' ).toggle(
            function () {
                $( '#topAdv' ).stop().animate( {height : '140px'} );
                return false;
            },
            function () {
                $( '#topAdv' ).stop().animate( {height : '10px'} );
                return false;
            }
        );
    }

    /* Empty Cart - Evento custom rilasciato da Smart Shop quando si preme 'svuota carrello' */
    $( document ).on( 'wpss_cart_empty', function () {
        if ( $( 'p.bnm-has-product-in-cart' ).length ) {
            $( 'p.bnm-has-product-in-cart' ).replaceWith( '' );
        }
    } );

    /* Delete Cart */
    $( document ).on( 'wpss_cart_delete', function ( e, count, total ) {
        if ( $( 'p.bnm-has-product-in-cart' ).length ) {
            if ( count == 0 ) {
                $( 'p.bnm-has-product-in-cart' ).replaceWith( '' );
            }
        }
    } );

    /* Accordion */
    $( '.accordion .head' ).click(
        function () {
            $( this ).next().toggle( 'slow' );
            return false;
        } ).next()
        .hide();

    /* Login/Signin */
    $( '#buttonOpenLogin' ).click( function () {
        var y = $( '#bnm-login-request-box' ).offset().top;
        $( '#bnm-login-box' ).stop().animate( {top : y}, 1000 );
    } );

    $( '#buttonCancel' ).click( function () {
        $( '#bnm-login-box' ).stop().animate( {top : '-400px'}, 1000 );
    } );

    /* Submit form login */
    $( 'form.bnm-login' ).submit( function () {
        if ( $( 'input[name=username]' ).val() == '' || $( 'input[name=password]' ).val() == '' ) {
            alert( bnmExtendsJavascriptLocalization.login_empty );
            $( 'input[name=username]' ).focus();
            return false;
        }
    } );

    /* FancyBox */
    $( 'a.fancybox' ).fancybox( {
        'transitionIn'  : 'elastic',
        'transitionOut' : 'elastic',
        'easingIn'      : 'easeOutBack',
        'easingOut'     : 'swing',
        'speedIn'       : 600,
        'speedOut'      : 200,
        'overlayShow'   : true,
        'titleShow'     : true,
        'titlePosition' : 'over'
    } );

    var fb_timeout = null;
    var fb_opts = { 'overlayShow' : true, 'centerOnScroll' : true, 'showCloseButton' : true, 'showNavArrows' : true, 'onCleanup' : function () {
        if ( fb_timeout ) {
            window.clearTimeout( fb_timeout );
            fb_timeout = null;
        }
    } };

    /* IMG */
    var fb_IMG_select = 'a[href$=".jpg"]:not(.nofancybox),a[href$=".JPG"]:not(.nofancybox),a[href$=".gif"]:not(.nofancybox),a[href$=".GIF"]:not(.nofancybox),a[href$=".png"]:not(.nofancybox),a[href$=".PNG"]:not(.nofancybox)';
    $( fb_IMG_select ).addClass( 'fancybox' ).attr( 'rel', 'gallery' );
    $( 'a.fancybox, area.fancybox' ).fancybox( $.extend( {}, fb_opts, { 'transitionIn' : 'elastic', 'easingIn' : 'easeOutBack', 'transitionOut' : 'elastic', 'easingOut' : 'easeInBack', 'opacity' : false, 'titleShow' : true, 'titlePosition' : 'over', 'titleFromAlt' : true } ) );

    /* Auto-click */
    $( '#fancybox-auto' ).trigger( 'click' );
    
    /* Target Link su Widget Twitter by Webeing.net */
 
	$('.twitter-link, .twitter-user, .twitter_title_link').addClass('extlink');
	$('.extlink').click( function() {
    	window.open( $(this).attr('href') );
    	return false;
    });

} );
