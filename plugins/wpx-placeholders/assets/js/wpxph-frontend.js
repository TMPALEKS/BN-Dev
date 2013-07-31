/**
 * @package
 * @subpackage      wp-placeholders-frontend
 * @author          =undo= <g.fazioli@undolog.com>, <g.fazioli@wpxtre.me>
 * @copyright       Copyright (c) 2012 wpXtreme, Inc.
 * @link            http://wpxtre.me
 * @created         04/04/12
 * @version         1.0.0
 *
 */

var WPPlaceholders = (function ( _wpplaceholders, $ ) {

    // -----------------------------------------------------------------------------------------------------------------
    // Private variables
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Internal class pointer
     */
    var _self = {};

    // -----------------------------------------------------------------------------------------------------------------
    //
    // -----------------------------------------------------------------------------------------------------------------

    var Reservations = (function () {

        var _reservations = {};

        /**
         * Click on free place
         */
        function onPlaceFreeClick() {
            var id_place = $( this ).attr( 'data-place_name' );

            $( this ).toggleClass( 'wpph-plan-place-taken' );

            var result = $( document ).triggerHandler( 'wpph_plan_place_taken', [$( this ), id_place] );
            if ( !result ) {
                $( this ).removeClass( 'wpph-plan-place-taken' );
            }
        }

        function init() {

            /* free place */
            $( 'div.wpph-plan-reservations-table-free' ).live( 'click', onPlaceFreeClick );

            return _reservations;
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

})( WPPlaceholders || {}, jQuery );