/**
 * Gestione su lista plugin
 *
 * @package         WPXtreme
 * @subpackage      wpxm-plugin-list
 * @author          =undo= <g.fazioli@undolog.com>, <g.fazioli@wpxtre.me>
 * @copyright       Copyright (c) 2012 wpXtreme, Inc.
 * @link            http://wpxtre.me
 * @created         16/04/12
 * @version         1.0.0
 *
 */

var WPXMPluginList = (function ( $ ) {

    // -----------------------------------------------------------------------------------------------------------------
    // Private variables
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Internal class pointer
     */
    var _self = {};

    /**
     * Chiede conferma per l'eventuale disabilitazione del plugin
     *
     * @return {*}
     */
    function onDisableLinkClick() {
        return confirm( WPXMPluginListL10n.warnig_confirm_disable_plugin );
    }
    /* Catch 'disable' plugin */
    $('tr#wpxtreme span.deactivate a').on( 'click', onDisableLinkClick );


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