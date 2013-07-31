/**
 * Gestione su plugin store.
 * Questo lo lasciamo, anche se lo script vero e proprio viene inviato dal server, perché serve per la localizzazione
 * e l'indirizzo ajax.
 *
 * @package         WPXtreme
 * @subpackage      wpxm-plugin-list
 * @author          =undo= <g.fazioli@undolog.com>, <g.fazioli@wpxtre.me>
 * @copyright       Copyright (c) 2012 wpXtreme, Inc.
 * @link            http://wpxtre.me
 * @created         23/05/12
 * @version         1.0.0
 *
 */

var WPXMPluginStore = (function ( $ ) {

    // -----------------------------------------------------------------------------------------------------------------
    // Private variables
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Internal class pointer
     */
    var _self = {};

    // -----------------------------------------------------------------------------------------------------------------
    // Functions
    // -----------------------------------------------------------------------------------------------------------------

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