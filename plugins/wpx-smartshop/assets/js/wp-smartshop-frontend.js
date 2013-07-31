/**
 * Script Javascript per Smart Shop lato fronend
 *
 * @package         wpx SmartShop
 * @subpackage      wp-smartshop-frontend
 * @author          =undo= <g.fazioli@undolog.com>, <g.fazioli@wpxtre.me>
 * @copyright       Copyright (c)2012 wpXtreme, Inc.
 * @created         30/12/11
 * @version         1.0
 *
 * @note
 *   In caso di necessità WPSmartShop può estendere WPSmartShop della parte di backend se caricato. Nella versione
 *   attuale se WPSmartShop (backend) non è presente viene passato un oggetto vuoto {}, identificato internamente da
 *   _wpsmartshop. In pratica questa classe è così com'è! Se viene incluso anche WPSmartShop del backend, questa
 *   eredità tutti i suoi metodi e la estende.
 *
 * @todo Rendere le funzioni del carrello e quelle del summary order più simili, quantomeno nei naming
 *
 */

var WPSmartShop = (function ( _wpsmartshop, $ ) {

    // -----------------------------------------------------------------------------------------------------------------
    // Private variables
    // -----------------------------------------------------------------------------------------------------------------

    // -----------------------------------------------------------------------------------------------------------------
    // Widget Cart
    //
    // Gestione carrello lato front end
    // -----------------------------------------------------------------------------------------------------------------

    var WidgetCart = (function () {

        var _widgetCart = {};

        /* Premuto il bottone di svuota carrello */
        function onEmptyCart() {
            if ( confirm( wpSmartShopJavascriptLocalization.confirmClearWidgetCart ) ) {
                $( 'body' ).css( 'cursor', 'wait' );
                $.post( wpSmartShopJavascriptLocalization.ajaxURL, {
                        action : 'action_cart_empty'
                    }, function ( data ) {
                        var result = $.parseJSON( data );

                        if ( typeof(result.message) != 'undefined' ) {
                            alert( result.message );
                        }
                        $( '.wpss-widget-cart-box .wpss-widget-ajax-cart-box' ).html( result.content );

                        $( document ).triggerHandler( 'wpss_cart_empty' );
                        $( 'body' ).css( 'cursor', 'default' );
                        reloadProductCard();
                    }
                );
            }
        }

        /* Aggiunge un item nel carrello */
        function onAddToCart() {
            var button = $( this );

            if( button.hasClass('wpss-busy') ) {
                return;
            }
            button.addClass('wpss-busy');

            var div_product_card = $( this ).parents( '.wpss-product-card' );
            div_product_card.css('opacity', '0.2');

            /* Recupero informazioni sul prodotto */
            var id_product = $(this).attr('data-id_product');
            var product = $(this).attr('data-product');

            /* Recupero informazioni sulla variante */
            var id_variant = $(this).attr('data-id_variant');
            var form = $('form#wpss-product-variant-form-' + id_variant);

            $.post( wpSmartShopJavascriptLocalization.ajaxURL, {
                action     : 'action_cart_add_product',
                id_product : id_product,
                product    : product,
                id_variant : id_variant,
                variant    : form.serializeArray()
            }, function ( data ) {
                var result = $.parseJSON( data );

                if ( typeof result.message  !== 'undefined' ) {
                    alert( result.message );
                }
                reloadProductCard( div_product_card );
                $( '.wpss-widget-cart-box .wpss-widget-ajax-cart-box' ).html( result.content );
                button.removeClass('wpss-busy');
            } );
        }

        /* Aggiorna la quantità di un prodotto */
        function onChangeProductQuantity() {
            var qty = parseInt( $( this ).val() );

            /* Se empty lo interpreta come un delete */
            if ( isNaN( qty ) || qty == 0 ) {
                var delete_button = $( this ).parents( 'tr' ).find( 'td input.delete' );
                delete_button.trigger('click');
            } else {

                $( this ).parents( 'tr' ).find( 'td:last-child' ).html( WPSmartShop.wait16x16 );
                var id_product_key = $( this ).attr( 'data-id_product_key' );

                $.post( wpSmartShopJavascriptLocalization.ajaxURL, {
                        action         : 'action_cart_update_qty',
                        id_product_key : id_product_key,
                        qty            : qty
                    }, function ( data ) {
                        var result = $.parseJSON( data );

                        if ( typeof(result.message) != 'undefined' ) {
                            alert( result.message );
                        }

                        $( '.wpss-widget-cart-box .wpss-widget-ajax-cart-box' ).html( result.content );
                        WPDK.refresh();
                        reloadProductCard();
                    }
                );
            }
        }

        /* Elimina un prodotto (item) dal carrelllo */
        function onDeleteProduct() {
            if ( confirm( wpSmartShopJavascriptLocalization.confirmDeleteItemWidgetCart ) ) {

                var id_product_key = $( this ).attr( 'data-id_product_key' );

                $( this ).replaceWith( WPSmartShop.wait16x16 );
                $.post( wpSmartShopJavascriptLocalization.ajaxURL, {
                        action         : 'action_cart_delete_product',
                        id_product_key : id_product_key
                    }, function ( data ) {
                        var result = $.parseJSON( data );

                        if( typeof result.count !== 'undefined' && typeof result.total !== 'undefined ') {
                            /* Triggers an handler */
                            $( document ).triggerHandler( 'wpss_cart_delete', [result.count, result.total] );
                        }
                        $( '.wpss-widget-cart-box .wpss-widget-ajax-cart-box' ).html( result.content );
                        WPDK.refresh();
                        reloadProductCard();
                    }
                );
            } else {
                var input_qty = $( this ).parents( 'tr' ).find( 'td input.qty' );
                var qty = parseInt( input_qty.val() );
                if ( isNaN( qty ) || qty == 0 ) {
                    input_qty.val( input_qty.attr('data-value_undo') );
                }
            }
        }

        function reloadProductCard( div_product_card ) {

            if ( typeof div_product_card === 'undefined' ) {
                $( '.wpss-product-card' ).each( function () {
                    reloadProductCard( $( this ) );
                } );
                return;
            }

            var id_product = div_product_card.attr( 'data-id_product' );
            var args = div_product_card.attr( 'data-args' );
            div_product_card.css( 'opacity', '0.4' );

            $.post( wpSmartShopJavascriptLocalization.ajaxURL, {
                action     : 'action_product_card_reload',
                id_product : id_product,
                args       : args
            }, function ( data ) {
                var result = $.parseJSON( data );

                if ( typeof result.message !== 'undefined' ) {
                    alert( result.message );
                }
                div_product_card.replaceWith( result.content );
                WPDK.refresh();
            } );
        }

        /* Init */
        function init() {

            /* Empty Cart */
            $( '#clearWidgetCart' ).live( 'click', onEmptyCart );

            /* Delete an item from Cart */
            $( '.wpss-widget-cart-box .delete' ).live( 'click', onDeleteProduct );

            /* Update Quantity Cart */
            $( '.wpss-widget-cart-box input.qty' ).live( 'change', onChangeProductQuantity );

            /* Intercetta il "bottone" generato dallo shortcode wpss_show_product */
            $( 'input.wpss-cart-add' ).live( 'click', onAddToCart );

            return _widgetCart;
        }

        return init();

    })();

    // -----------------------------------------------------------------------------------------------------------------
    // Summary Order
    //
    // Avendo una conformazione HTML diversa, si è deciso di duplicare queste funzioni che, apparentemente, sono simili
    // per il carrello e per il summary order del checkout
    // -----------------------------------------------------------------------------------------------------------------

    var SummaryOrder = (function () {

        var _summaryOrder = {};

        /* Product coupon update price */
        function onChangeProductCoupon() {
            var coupon_code = $( this ).val();
            $( this ).parents( 'tr' ).find( 'td.wpss-summary-order-cell-product_price' ).html( WPSmartShop.wait16x16 );
            var id_product_key = $( this ).attr( 'data-id_product_key' );
            $.post( wpSmartShopJavascriptLocalization.ajaxURL, {
                    action         : 'action_summary_order_update_product_coupon',
                    id_product_key : id_product_key,
                    coupon_code    : coupon_code
                }, function ( data ) {

                    var result = $.parseJSON( data );

                    if ( typeof(result.message) != 'undefined' ) {
                        alert( result.message );
                    }

                    $( 'div.wpss-summary-order-container' ).replaceWith( result.content );
                    WPDK.refresh();
                }
            );
        }

        /* Order Coupon update price */
        function onChangeOrderCoupon() {
            var value = $( this ).val();
            $( this ).parents( 'tr' ).find( 'td.wpss-summary-order-row-value-coupon_order' ).html( WPSmartShop.wait16x16 );
            $.post( wpSmartShopJavascriptLocalization.ajaxURL, {
                    action : 'action_summary_order_update_order_coupon',
                    value  : value
                }, function ( data ) {

                    var result = $.parseJSON( data );

                    if ( typeof(result.message) != 'undefined' ) {
                        alert( result.message );
                    }

                    $( 'div.wpss-summary-order-container' ).replaceWith( result.content );
                    WPDK.refresh();
                }
            );
        }

        /* Update Quantity Cart for Summary Order */
        function onChangeProductQuantity() {
            var qty = parseInt( $( this ).val() );

            /* Se empty lo interpreta come un delete */
            if ( isNaN( qty ) || qty == 0 ) {
                var delete_button = $( this ).parents( 'tr' ).find( 'td input.delete' );
                delete_button.trigger( 'click' );
            } else {

                var id_product_key = $( this ).attr( 'data-id_product_key' );

                /* Trigger custom event */

                /* @todo Da rivedere in quanto viene passato id_product_key al posto dell'id del prodotto + variante */
                var result = $( document ).triggerHandler( 'wpss_summary_order_change_quantity', [id_product_key, qty] );

                /* Se l'handler restituisce false esco, proseguo solo se non c'è nessun handler o quest'utlimo ha restituito true */
                if ( typeof result !== 'undefined' && result === false ) {
                    return;
                }

                $( this ).parents( 'tr' ).find( 'td.wpss-summary-order-cell-product_price' ).html( WPSmartShop.wait16x16 );

                /* @todo Da levare questo blocco javascript, fare tutto da php */

                $.post( wpSmartShopJavascriptLocalization.ajaxURL, {
                        action         : 'action_summary_order_update_qty',
                        id_product_key : id_product_key,
                        qty            : qty
                    }, function ( data ) {
                        var result = $.parseJSON( data );

                        if ( typeof result.message !== 'undefined' ) {
                            alert( result.message );
                        }

                        $( 'div.wpss-summary-order-container' ).replaceWith( result.content );
                        WPDK.refresh();
                    }
                );
            }
        }

        /* Delete an item from Summary Order */
        function onDeleteProduct() {
            if ( confirm( wpSmartShopJavascriptLocalization.confirmDeleteItemWidgetCart ) ) {
                $( this ).replaceWith( WPSmartShop.wait16x16 );

                var id_product_key = $( this ).attr( 'data-id_product_key' );
                $.post( wpSmartShopJavascriptLocalization.ajaxURL, {
                        action         : 'action_summary_order_delete_product',
                        id_product_key : id_product_key
                    }, function ( data ) {
                        var result = $.parseJSON( data );

                        if ( typeof(result.message) != 'undefined' ) {
                            alert( result.message );
                        }
                        $( 'div.wpss-summary-order-container' ).replaceWith( result.content );
                        WPDK.refresh();
                    }
                );
            } else {
                var input_qty = $( this ).parents( 'tr' ).find( 'td input.qty' );
                var qty = parseInt( input_qty.val() );
                if ( isNaN( qty ) || qty == 0 ) {
                    input_qty.val( input_qty.attr( 'data-value_undo' ) );
                }
            }
        }

        function init() {

            /* Product Coupon */
            $( 'form.wpss-summary-order-form input.wpss-summary-order-product-coupon' ).live( 'change', onChangeProductCoupon);

            /* Order Coupon */
            $( 'form.wpss-summary-order-form input.wpss-summmary-order-order-coupon' ).live( 'change', onChangeOrderCoupon);

            /* Update Quantity Cart for Summary Order */
            $( 'form.wpss-summary-order-form input.qty' ).live( 'change', onChangeProductQuantity);

            /* Delete an item from Summary Order */
            $( 'form.wpss-summary-order-form .delete' ).live( 'click', onDeleteProduct );

            return _summaryOrder;
        }

        return init();

    })();

    // -----------------------------------------------------------------------------------------------------------------
    //
    //
    // -----------------------------------------------------------------------------------------------------------------

    var Foo = (function () {

        var _foo = {};

        function init() {
            return _foo;
        }

        return init();

    })();

    // -----------------------------------------------------------------------------------------------------------------
    // Public properties
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Version
     */
    _wpsmartshop.version = "1.0";
    _wpsmartshop.wait16x16 = '<img class="wpss-shopping-cart-add-wait-16x16" border="0" alt="Wait" src="data:image/gif;base64,R0lGODlhEAAQAMQaANHRz7q6uIeHhcXFw3l5d66urMbGw6+vrqSko5SUk5WVlKOjonl5dpSUknh4dsbGxNLSz4aGhKKiobm5t9HRzru7uYiIhrCwr6WlpZaWlgAAAAAAAAAAAAAAAAAAAAAAACH/C05FVFNDQVBFMi4wAwEAAAAh/wtYTVAgRGF0YVhNUDw/eHBhY2tldCBiZWdpbj0i77u/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+IDx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDUuMy1jMDExIDY2LjE0NTY2MSwgMjAxMi8wMi8wNi0xNDo1NjoyNyAgICAgICAgIj4gPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4gPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9IiIgeG1sbnM6eG1wPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvIiB4bWxuczp4bXBNTT0iaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wL21tLyIgeG1sbnM6c3RSZWY9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9zVHlwZS9SZXNvdXJjZVJlZiMiIHhtcDpDcmVhdG9yVG9vbD0iQWRvYmUgUGhvdG9zaG9wIENTNiAoMTMuMCAyMDEyMDMwNS5tLjQxNSAyMDEyLzAzLzA1OjIxOjAwOjAwKSAgKE1hY2ludG9zaCkiIHhtcE1NOkluc3RhbmNlSUQ9InhtcC5paWQ6NTdGOTI0ODM4NUExMTFFMThENDlCMzhFQTZBNTY4NEEiIHhtcE1NOkRvY3VtZW50SUQ9InhtcC5kaWQ6NTdGOTI0ODQ4NUExMTFFMThENDlCMzhFQTZBNTY4NEEiPiA8eG1wTU06RGVyaXZlZEZyb20gc3RSZWY6aW5zdGFuY2VJRD0ieG1wLmlpZDo1N0Y5MjQ4MTg1QTExMUUxOEQ0OUIzOEVBNkE1Njg0QSIgc3RSZWY6ZG9jdW1lbnRJRD0ieG1wLmRpZDo1N0Y5MjQ4Mjg1QTExMUUxOEQ0OUIzOEVBNkE1Njg0QSIvPiA8L3JkZjpEZXNjcmlwdGlvbj4gPC9yZGY6UkRGPiA8L3g6eG1wbWV0YT4gPD94cGFja2V0IGVuZD0iciI/PgH//v38+/r5+Pf29fTz8vHw7+7t7Ovq6ejn5uXk4+Lh4N/e3dzb2tnY19bV1NPS0dDPzs3My8rJyMfGxcTDwsHAv769vLu6ubi3trW0s7KxsK+urayrqqmop6alpKOioaCfnp2cm5qZmJeWlZSTkpGQj46NjIuKiYiHhoWEg4KBgH9+fXx7enl4d3Z1dHNycXBvbm1sa2ppaGdmZWRjYmFgX15dXFtaWVhXVlVUU1JRUE9OTUxLSklIR0ZFRENCQUA/Pj08Ozo5ODc2NTQzMjEwLy4tLCsqKSgnJiUkIyIhIB8eHRwbGhkYFxYVFBMSERAPDg0MCwoJCAcGBQQDAgEAACH5BAUAABoALAAAAAAQABAAAAVloCaOGoaRKImsSDpel7bMi5Zl6KEfmiRpiqCCVCgWSIlkAlVMNRqkSsU1slg0gWyAKugKtFuXV6CZTKiiSIT0eKQIBJRhbiDB4aSBfqCBQDQMgQwoehoAhwAaDg5oiIloIxQULiEAIfkEBQAAGgAsAAAAABAAEAAABWOgJo7adZEoeaxHOlaVVsyFhmFooAdyjfwI0o43WhgXqMkkJZGQHg/XKJPRGK4GqWKrGHgH0oQ4ofFKRY0GCQJJWSwogBxAEtgFpDmdQNDc8SQUFBp8fBoREWcMiwxnJA4OLiEAIfkEBQAAGgAsAAAAABAAEAAABWSgJo5aVZEoGaxBOj6PxrbXhRq4oU2TdvwH0mA4IBWOBdQwdSRBIK4RBqMBWAFRhBZxxboW4IWGQomKJBISgZDKZFDrNUlBV5AYeIbGYtEk/gkoDg4aAoYCGg0NZoeIZiMRES4hACH5BAUAABoALAAAAAAQABAAAAVjoCaO2vOQKGmsRjpCkDbMg1ZVKKADch38AdKONwIGSRRKajIhEQiu0eWieT6jh+yBwWVEC+CCxuGIisAkiyWFwaAEcAEJQUeQ4vJMRrPoL1ARERoKhAoaEhJmCYsJZiQNDS4hACH5BAUAABoALAAAAAAQABAAAAVkoCaOGgSRKAmsQDoShMa2z4PCsEZRmuEbJIaQQRoYByiHI2UkWSyuUaWiEVgF0YA2cMW6tgFNJBIVTSakTCZ1uaAUcAXpQD+QEviEBoPRFP4FKA0NGgiGCH6BUQuMC2UkEhIuIQAh+QQFAAAaACwAAAAAEAAQAAAFZaAmjhpBkChpmuloWRojMxoEoUIuaI6jAUAASbcbBYWkSCRFoZAymdbo8dAorgqpYWtIeBPSgXigaTSkIjEJg0lVKiiEHEEK2AOkhX6huVw0d3gkEhIaB4cHGhMTaAWOBWgkji0hACH5BAUAABoALAAAAAAQABAAAAVjoCaOmmWRKCmsQjpmmca2BIEquKJFkVbXpIQwQWIYGahGI+VwkDAY1wgC0SCuCClgC1h4F1quRiKRiigU0uWSejxQh/iBZKgbSIV8QVOpaAaAAyh5GgGGAX+CUoeIZiMTEy4hACH5BAUAABoALAAAAAAQABAAAAVkoCaOWpaRKKmsSjpimJbMiWZZKKIjWtNogqCAtCguSMIhSSJJRSKky8U1IhA0h+yBarUWvgUqY8zQfKkih4NUqaQgEFRgHiAB7gASvf54aPB5JBMTGgaGBhoUFGgDjQNoJI0uIQA7" />';

    // -----------------------------------------------------------------------------------------------------------------
    // Private methods
    // -----------------------------------------------------------------------------------------------------------------

    // -----------------------------------------------------------------------------------------------------------------
    // Public methods
    // -----------------------------------------------------------------------------------------------------------------

    // -----------------------------------------------------------------------------------------------------------------
    // Utility & commodity
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Only debug
     *
     * @param obj
     */
    _wpsmartshop.debug = function ( obj ) {
        var output = '';
        for ( var property in obj ) {
            output += property + ': ' + obj[property] + ';\n';
        }
        return(output);
    };

    // -----------------------------------------------------------------------------------------------------------------
    // End
    // -----------------------------------------------------------------------------------------------------------------

    return _wpsmartshop;

})( WPSmartShop || {}, jQuery );