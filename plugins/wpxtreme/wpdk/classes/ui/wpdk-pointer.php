<?php
/**
 * @class              WPDKPointer
 * @description        Classe (simile a quella interna presente in WordPress, per la gestione dei pointer help)
 *
 * @package            WPDK
 * @subpackage         ui
 * @author             =undo= <g.fazioli@undolog.com>, <g.fazioli@wpxtre.me>
 * @copyright          Copyright (c) 2012 wpXtreme, Inc.
 * @link               http://wpxtre.me
 * @created            08/06/12
 * @version            1.0.0
 *
 * @filename           wpdk-pointer.php
 *
 */

class WPDKPointer {

    /**
     * Init
     */
    function __construct() {

        /* WP Pointer featured. */
        wp_enqueue_style( 'wp-pointer' );
        wp_enqueue_script( 'wp-pointer' );
    }

    function registerPointer( $callback ) {
        if( !empty( $callback ) ) {
            add_action( 'admin_print_footer_scripts', $callback );
        }
    }

    /**
   	 * Print the pointer javascript data.
   	 *
   	 * @since 1.0
   	 *
   	 * @param string $pointer_id The pointer ID.
   	 * @param string $selector The HTML elements, on which the pointer should be attached.
   	 * @param array  $args Arguments to be passed to the pointer JS (see wp-pointer.dev.js).
   	 */
    function display( $pointer_id, $selector, $args ) {
        if ( empty( $pointer_id ) || empty( $selector ) || empty( $args ) || empty( $args['content'] ) ) {
            return;
        }

        /* Controllo se l'utente a deciso di dismettere questo pointer, in tal caso non lo visualizzo */
        $id_user   = get_current_user_id();

        /* Vedi la 'action_wpdk_dismiss_wp_pointer' in Ajax */
        $dismissed = unserialize( get_user_meta( $id_user, 'wpdk_dismissed_wp_pointer', true ) );
        if ( isset( $dismissed[$pointer_id] ) ) {
            return;
        }

        ?>
    <script type="text/javascript">
        //<![CDATA[
        jQuery( document ).ready( function ( $ ) {
            var options = <?php echo json_encode( $args ); ?>;

            if ( !options ) {
                return;
            }

            options = $.extend( options, {
                close : function () {
                    $.post( ajaxurl, {
                        pointer : '<?php echo $pointer_id; ?>',
                        action  : 'action_wpdk_dismiss_wp_pointer'
                    } );
                }
            } );

            $( '<?php echo $selector; ?>' ).pointer( options ).pointer( 'open' );
        } );
        //]]>
    </script>
    <?php
    }

}
