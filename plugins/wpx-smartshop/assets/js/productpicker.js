/**
 * Javascript usato per far comparire il Product Picker di Smart Shop all'interno dell'editor di WordPress
 *
 * @package         wpx SmartShop
 * @subpackage      productpicker
 * @author          =undo= <g.fazioli@undolog.com>, <g.fazioli@wpxtre.me>
 * @copyright       Copyright (c) 2012 wpXtreme, Inc.
 * @link            http://wpxtre.me
 * @created         12/03/12
 * @version         1.0
 *
 */

// closure to avoid namespace collision
(function () {
    // creates the plugin
    tinymce.create( 'tinymce.plugins.ProductPicker', {
        init          : function ( ed, url ) {
            ed.addButton( 'ProductPicker', {
                title   : 'ProductPicker',
                image   : wpSmartShopJavascriptLocalization.logo34x34
            } );

        },
        createControl : function ( n, cm ) {
            return null;
        },

        /**
         * Returns information about the plugin as a name/value array.
         * The current keys are longname, author, authorurl, infourl and version.
         *
         * @retval {Object} Name/value array containing information about the plugin.
         */
        getInfo : function () {
            return {
                longname  : 'wpx SmartShop Product Picker',
                author    : 'wpXtreme',
                authorurl : 'http://wpxtre.me/',
                infourl   : 'http://wpxtre.me/',
                version   : '1.0'
            };
        }
    } );

    // registers the plugin. DON'T MISS THIS STEP!!!
    tinymce.PluginManager.add( 'ProductPicker', tinymce.plugins.ProductPicker );
})();