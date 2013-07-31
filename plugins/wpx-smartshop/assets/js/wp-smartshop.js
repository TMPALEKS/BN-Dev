/**
 * Script Javascript per Smart Shop
 *
 * @package         wpx SmartShop
 * @subpackage      wp-smartshop
 * @author          =undo= <g.fazioli@undolog.com>, <g.fazioli@wpxtre.me>
 * @copyright       Copyright (c)2012 wpXtreme, Inc.
 * @created         29/12/11
 * @version         1.0
 *
 * @todo Ragionare sempre per 'scope' quando usiamo jQuery nel Backend
 *
 */

var WPSmartShop = (function ($) {

    /* @todo Da capire meglio
    $.fn.dialogWrapScope = function ( scope ) {
        this.bind( 'dialogopen', function ( event, ui ) {
            $( this ).parents( ".ui-dialog" ).wrap(
                '<div class="wpdk-jquery-ui-scope '
                    + scope
                    + '"></div>'
            );
        } );
        this.bind( 'dialogclose', function ( event, ui ) {
            var wrapper = $( this ).parents( ".wpdk-jquery-ui-scope" );
            //wrapper.replaceWith( wrapper.children() );
            wrapper.remove();
        } );
        return this;
    };
    */

    // -----------------------------------------------------------------------------------------------------------------
    // Private variables
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Internal class pointer
     */
    var _wpsmartshop = {};

    // -----------------------------------------------------------------------------------------------------------------
    // Orders management
    //
    // Quest'oggetto gestisce la parte di ordini
    // -----------------------------------------------------------------------------------------------------------------
    var Orders = (function() {

        var _orders = {};

        /**
         * Regola per la validazione del form
         */
        var rules = {
            subtotal : "required",
            subtotal : "number",
            tax      : "required",
            tax      : "number",
            total    : "required",
            total    : "number",
            bill_first_name   : "required",
            bill_last_name    : "required",
            bill_address      : "required",
            bill_zipcode      : "required",
            bill_town         : "required",
            bill_country      : "required",
            bill_email        : {
                required : true,
                email    : true
            },
            bill_phone        : "required"
        };

        /**
         * Init Orders
         */
        function init() {
            /* Controllo che mi trovo in modifica di un ordine */
            if ( $( 'form.wpss-orders' ).length ) {
                $( 'input#bill_first_name' ).focus();
                validate();
                blur();
            }

            return _orders;
        }

        /**
         *
         * Edit dell'ordine
         *
         */

        /* Validazione del form */
        function validate() {
            $( 'form.wpss-orders' ).validate( {
                errorPlacement : function ( error, element ) {
                },
                ignoreTitle    : true,
                errorClass     : "wpdk-form-wrong",
                validClass     : 'wpdk-form-ok',
                rules          : rules
            } );
        }

        /* Imposto ricalcolo sul blur di alcuni elementi */
        function blur() {
            $( 'input#subtotal' ).blur( function () {
                if ( $( this ).val() == '' ) {
                    $( this ).val( '0.00' );
                }
                var subtotal = Math.max( parseFloat( $( this ).val().replace( ',', '.' ) ), 0 );
                var tax = Math.max( parseFloat( $( 'input#tax' ).val().replace( ',', '.' ) ), 0 );

                var total = subtotal + (subtotal * tax / 100);

                $( this ).val( subtotal.toFixed( 2 ) );
                $( 'input#total' ).val( total.toFixed( 2 ) );
            } );

            $( 'input#tax' ).blur( function () {
                if ( $( this ).val() == '' ) {
                    $( this ).val( '0.00' );
                }
                $( 'input#subtotal' ).triggerHandler( 'blur' );
            } );
        }

        return init();

    })();

    // -----------------------------------------------------------------------------------------------------------------
    // Settings
    //
    // -----------------------------------------------------------------------------------------------------------------

    var Settings = (function () {

        var _settings = {};

        function init() {
            if ( $().tabs ) {
                $( "#tabs" ).tabs( {
                    cookie : {
                        expires : 1
                    }
                } );
            }

            return _settings;
        }

        return init();

    })();

    // -----------------------------------------------------------------------------------------------------------------
    // Payment Gateway
    //
    // -----------------------------------------------------------------------------------------------------------------

    var PaymentGateway = (function () {

        var _paymentGAteway = {};

        function init() {

            if ( $().tabs ) {
                $( "#tabs" ).tabs( {
                    cookie : {expires : 1}
                } );
            }

            return _paymentGAteway;
        }

        return init();

    })();

    // -----------------------------------------------------------------------------------------------------------------
    // Product Picker
    //
    // -----------------------------------------------------------------------------------------------------------------

    var ProductPicker = (function () {

        var _productPicker = {};

        /* @todo Rendere globali */
        var wait16x16 = '<img class="wpss-product-picker-in-waiting-16x16" border="0" alt="Wait" src="data:image/gif;base64,R0lGODlhEgASAMQaAHl5d66urMXFw3l5dpSUk5WVlKOjoq+vrsbGw6Sko7u7uaWlpbm5t3h4doiIhtLSz4aGhJaWlsbGxNHRzrCwr5SUkqKiobq6uNHRz4eHhf///wAAAAAAAAAAAAAAAAAAACH/C05FVFNDQVBFMi4wAwEAAAAh+QQFAAAaACwAAAAAEgASAAAFaaAmjmRplstyrkmbrCNFaUZtaFF0HvyhWRZNYVgwBY4BEmFJOB1NlYpJoYBpHI7RZXtZZb4ZEbd7AodFDIYVAjFJJCYA4ISoI0hyuUnAF2geDxoDgwMnfBoYiRgaDQ1WiIqPJBMTkpYaIQAh+QQFAAAaACwBAAEAEAAQAAAFY6AmjhpFkSh5rEc6KooWzIG2LOilX3Kd/AnSjjcyGA0oBiNlsZAkEtcoEtEgrghpYVsQeAVSgpig8UpFlQrp8Ug5HCiMHEPK2DOkOR0A0NzxJBMTGnx8GhAQZwOLA2ckDQ0uIQAh+QQFAAAaACwBAAEAEAAQAAAFZKAmjpqikCh5rVc6SpLGthSFIjiiMYx2/AeSYCggBY4B1DB1JD0ertFiocFYMdGENnHFugxgg2YyiYosFhIAkIpEUOs1qUAvkAb4gcbh0BD+BCgNDRoZhhkaFRVmh4hmIxAQLiEAIfkEBQAAGgAsAQABABAAEAAABWOgJo6aJJEoiaxIOj6PJsyCpigopmNyff0X0o43AgZJk0mKwSABAK4RhaJ5PqOH7GHAHUQD4ICm0YiKwCSHI7VYoDLwDClBT5Di8khEY+gbUBAQGgWEBRoWFmYEiwRmJBUVLiEAIfkEBQAAGgAsAQABABAAEAAABWSgJo7a85Aoia1YOgKAxraShMKwNk0a4iOkgXBAEhgFqEYjZSQ5HK6RQqHJWDPRi/Zyxbq2Fw0EEhUxGKRIJEWhoArwAulAP5AIeIJmsdAE/gEoFRUaCYYJfoFRBowGZSQWFi4hACH5BAUAABoALAEAAQAQABAAAAVloCaOGgCQKGma6eg42iAP2vOgWZ5pTaNhQAxJtxsFhSQIJDWZkCKR1kgi0RSuBSliiyB4CVKBWKCpVKQiMWmxSCkUqIQ8QbrYLySD3qChUDR3eCQWFhoHhwcaDAxoAY4BaCSOLSEAIfkEBQAAGgAsAQABABAAEAAABWOgJo6a45Aoma1ZOkaRxrYAgBZ4oUGQVtckgpBAGhgHqEol1WiQFgvX6PHQJK4JKWaLMXgNWq7GYpGKJhMShZKSSFCH+IGEqCNIgXxAo1BoBIACKHkaF4YXf4JSh4hmIwwMLiEAIfkEBQAAGgAsAQABABAAEAAABWSgJo5aFJEoWaxFOi6LRsyE5jhooidaVWmZYIZkKBpIwiHJYklBICQKxTUCADSH7IFqtQa+AepgPNB8qaJGg6RQpB4P1GV+IWHuGBK9LpFo8HkkDAwaCIYIGhMTaAKNAmgkjS4hADs=" />';
        var wait32x32 = '<img class="wpss-product-picker-in-waiting-32x32" border="0" alt="Wait" alt="" src="data:image/gif;base64,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" />';

        function init() {

            /* Product Type click */
            $( 'div.wpss-product-picker-product-types-list ul li a' ).live( 'click', onProductTypeItemClick );

            /* Search filter box */
            $( 'div.wpss-product-picker-toolbar input.wpdk-form-button' ).live( 'click', onFilter );

            /* Product click */
            $( 'div.wpss-product-picker-products-list ul li a' ).live( 'click', onProductItemClicked );

            /* MCE Button edito */
            $( 'a#content_ProductPicker' ).live('click', dialogProductPicker );

            /* Set id draggable */
            productDraggable();

            return _productPicker;
        }

        // -------------------------------------------------------------------------------------------------------------
        // Gestione del Product Picker generico richiamabile dal bottone MCE, usato per l'inserimento dello shortcode
        // -------------------------------------------------------------------------------------------------------------

        /* Click sul bottone MCE Prodcuct selector */
        function dialogProductPicker() {
            var args = {
                dialogClass : 'wpdk-dialog-jquery-ui',
                modal       : true,
                resizable   : true,
                draggable   : true,
                closeText   : wpSmartShopJavascriptLocalization.closeText,
                title       : wpSmartShopJavascriptLocalization.productPickerTitle,
                open        : dialogProductPickerDidOpen,
                width       : 640,
                height      : 440,
                minWidth    : 500,
                minHeight   : 460,
                buttons     : [
                    {
                        text  : wpSmartShopJavascriptLocalization.Cancel,
                        click : function () {
                            $( this ).dialog( "close" );
                        }
                    }
                ]
            };

            if ( $( '#wpss-product-picker' ).parents( 'div.wpdk-jquery-ui .ui-dialog' ).length ) {
                $( '#wpss-product-picker' ).dialog( args );
            } else {
                $( '#wpss-product-picker' ).dialog( args ).parent( ".ui-dialog" ).wrap( "<div class='wpdk-jquery-ui'></div>" );
            }
        }

        /* Dialogo aperto, carico contenuto Product Dialog open */
        function dialogProductPickerDidOpen( event, ui ) {
            $( '#wpss-product-picker' ).html( WPSmartShop.wait32x32 );

            $.post( wpSmartShopJavascriptLocalization.ajaxURL, {
                    action : 'action_product_picker_display',
                    id     : 'dialogProductPicker'
                }, function ( data ) {
                    var result = $.parseJSON( data );

                    if ( typeof(result.message) != 'undefined' ) {
                        alert( result.message );
                    }

                    $( '#wpss-product-picker' ).html( result.content );
                    $( '#dialogProductPicker' ).on( 'productPickerDidProductSelected', productDidSelected );
                }
            );
        }

        /* Selezionato un prodotto sul dialogo generico Product did selected */
        function productDidSelected( el, product_picker_id, id_product, description ) {

            var shortcode = '[wpss_show_product id="' + id_product + '"]';

            window.send_to_editor( shortcode );

            $( '#wpss-product-picker' ).dialog( 'close' );
            return false;
        }


        // -------------------------------------------------------------------------------------------------------------
        // Gestione del product picker embed, non in dialogo. Qui ci sono tutti i controlli per la selezione del
        // tipo prodotto, sorter, drag & drop.
        // Alcune funzioni sono in comune con la parte dialog
        // -------------------------------------------------------------------------------------------------------------

        /* Click su un tipo prodotto - carico prodotti */
        function onProductTypeItemClick() {

            /* Get Product Picker ID */
            var product_picker = $( this ).parents( 'div.wpss-product-picker' );
            var product_picker_id = product_picker.attr( 'id' );

            var productPicker = productPickerData( product_picker_id );

            /* Highligh items */
            productPicker.product_types_list.find( 'li a' ).removeClass( 'selected' );
            $( this ).addClass( 'selected' );

            /* Reset paged */
            productPicker.products.data( 'paged', 1 );

            var id_product_type = $( this ).attr( 'data-term_id' );
            var product_type_name = $( this ).text();

            /* Se on c'è la lista c'è l'h6 con l'indicazione */
            if ( !productPicker.products_list.length ) {
                productPicker.products_list = productPicker.products.children( 'h6' );
            } else {
                /* Get ID and Name of Product Type */
                productPicker.products_list.attr( 'data-term_id', id_product_type );
            }

            /* Fire custom event */
            var result = product_picker.triggerHandler( 'productPickerDidProductTypeSelected', [ product_picker_id, id_product_type, product_type_name ] );

            /* Ajax */
            if ( result !== false && productPicker.products_list.length ) {
                productPicker.products_list.html( wait32x32 );
                $.post( wpSmartShopJavascriptLocalization.ajaxURL, {
                        action    : 'action_product_picker_html_products',
                        json_args : productPicker.json,
                        term_id   : id_product_type
                    }, function ( data ) {
                        var result = $.parseJSON( data );

                        if ( typeof(result.message) != 'undefined' ) {
                            alert( result.message );
                        }
                        productPicker.products_list.replaceWith( result.content );
                        productPicker = productPicker.refresh();
                        scrollSpyOn( productPicker );
                        productDraggable();
                    }
                );
            }
            return false;
        }

        /* Cliccato su un prodotto */
        function onProductItemClicked() {
            var id_product = $( this ).attr( 'data-id_product' );
            var product_name = $( this ).find('h4').text();

            /* Get Product Picker ID */
            var product_picker = $( this ).parents( 'div.wpss-product-picker' );
            var product_picker_id = product_picker.attr( 'id' );

            var productPicker = productPickerData( product_picker_id );

            /* Highligh items */
            productPicker.products_list.find( 'li a' ).removeClass( 'selected' );
            $( this ).addClass( 'selected' );

            /* Fire a custom event */
            var result = product_picker.triggerHandler( 'productPickerDidProductSelected', [product_picker_id, id_product, product_name] );

            return false;
        }

        /* Controllo sullo scroll del DIV dei prodotti, quando raggiunge la fine carica gli elementi successivi */
        function scrollSpyOn( productPicker ) {
            productPicker.products_list.bind( 'scroll', function () {
                if ( $( this ).scrollTop() + $( this ).innerHeight() >= $( this )[0].scrollHeight ) {

                    var li_load_next = productPicker.products_list.find( 'li.wpss-product-picker-load-next' );

                    if ( li_load_next.length && li_load_next.data( 'inloading' ) !== 'yes' ) {
                        li_load_next.data( 'inloading', 'yes' )
                        loadNextItems( productPicker );
                    }
                }
            } );
        }

        /* Auto carica i prossimo n elementi */
        function loadNextItems( productPicker ) {

            var li_load_next = productPicker.products_list.find( 'li.wpss-product-picker-load-next' );

            li_load_next.prepend( wait16x16 );

            var paging = productPicker.paged;
            paging++;
            productPicker.products.data( 'paged', paging );

            $.post( wpSmartShopJavascriptLocalization.ajaxURL, {
                    action    : 'action_product_picker_load_next_items',
                    json_args : productPicker.json,
                    term_id   : productPicker.term_id,
                    paged     : paging
                }, function ( data ) {
                    var result = $.parseJSON( data );

                    if ( typeof(result.message) != 'undefined' ) {
                        alert( result.message );
                    }

                    var ul = li_load_next.parent();
                    li_load_next.remove();
                    ul.append( result.content );
                }
            );
        }

        /* Chiamata alla pressione del tasto "search" nel filtro per titolo */
        function onFilter() {
            var product_picker_id = $( this ).parents( '.wpss-product-picker' ).attr( 'id' );

            var productPicker = productPickerData( product_picker_id );

            if ( productPicker.input_text.val() != '' && productPicker.term_id > 0 ) {
                productPicker.products_list.html( wait32x32 );
                $.post( wpSmartShopJavascriptLocalization.ajaxURL, {
                        action        : 'action_product_picker_html_products',
                        json_args     : productPicker.json,
                        search_filter : productPicker.input_text.val(),
                        term_id       : productPicker.term_id
                    }, function ( data ) {
                        var result = $.parseJSON( data );

                        if ( typeof(result.message) != 'undefined' ) {
                            alert( result.message );
                        }
                        productPicker.products_list.replaceWith( result.content );
                        scrollSpyOn( productPicker );
                    }
                );
            }
        }

        /* Recupera le informazioni di un Product Picker */
        function productPickerData( id ) {
            var product_picker = $( 'div#' + id ),
                _products_types,
                _products,
                _products_toolbar;

            if ( product_picker.length ) {
                var result = {
                    id                    : id,
                    product_picker        : product_picker,
                    json                  : product_picker.children( 'input[name=' + id + '_json]' ).val(),
                    product_types         : _products_types = product_picker.children( 'div.wpss-product-picker-product-types' ),
                    product_types_toolbar : _products_types.children( 'div.wpss-product-picker-toolbar' ),
                    product_types_list    : _products_types.children( 'div.wpss-product-picker-product-types-list' ),
                    products              : _products = product_picker.children( 'div.wpss-product-picker-products' ),
                    products_toolbar      : _products_toolbar = _products.children( 'div.wpss-product-picker-toolbar' ),
                    input_text            : _products_toolbar.find( 'input.wpdk-form-input' ),
                    products_list         : _products.children( 'div.wpss-product-picker-products-list' ),
                    term_id               : _products.children( 'div.wpss-product-picker-products-list' ).attr( 'data-term_id' ),
                    paged                 : _products.data( 'paged' ) | 1,
                    refresh               : function () {
                        return productPickerData( id );
                    }
                }
                return result;
            }
            return false;
        }

        /* Impsta draggable se deve - vedi classe css */
        function productDraggable() {
            /* Draggable product */
            $( '.wpss-product-picker-draggable-product' ).draggable( {
                helper            : 'clone',
                revert            : 'invalid',
                cursor            : 'move',
                connectToSortable : '.wpss-showcase-droppable'
            } );

            /* Droppable */
            $( '.wpss-showcase-droppable' ).sortable( {
                placeholder : 'ui-state-highlight',
                helper      : 'clone',
                receive     : function ( event, ui ) {
                    $( this ).find( 'li.placeholder' ).remove();
                    $( this ).find( 'li' ).removeClass( 'wpss-product-picker-draggable-product' ).addClass( 'wpss-product-picker-trashable' );
                },
                update      : handleUpdate
            } );
        }

        /* @todo Da fare - dovrebbe impedire di droppare due vole lo stesso elemento */
        function hasElement( ui ) {
            var sequence = $( 'input#wpss_showcase_products_sorter_sequence' ).val();
            if ( sequence ) {
                var id = ui.item.find( 'a' ).attr( 'data-id_product' );
                var list = sequence.split( ',' );
                for ( var e in list ) {
                    if ( list[e] == id ) {
                        return true;
                    }
                }
            }
            return false;
        }

        /* Aggiorna ordine - Update sorter order */
        function handleUpdate( event, ui ) {
            var sequence = '';
            var list = {};
            $( '.wpss-showcase-droppable li a' ).each( function ( index, element ) {
                list[$( element ).attr( 'data-id_product' )] = true;
            } );

            for ( var e in list ) {
                sequence += (sequence == '') ? e : ',' + e;
            }

            $( 'input#wpss_showcase_products_sorter_sequence' ).val( sequence );

        }

        return init();
    })();

    // -----------------------------------------------------------------------------------------------------------------
    // Product (Custom) Post Type
    // -----------------------------------------------------------------------------------------------------------------

    var ProductPostType = (function () {

        var _productPostType = {};

        function init() {

            /* Datepicker */
            if($.datepicker) {
                $.datepicker.setDefaults({
                    currentText : wpSmartShopJavascriptLocalization.currentText,
                    dayNamesMin : (wpSmartShopJavascriptLocalization.dayNamesMin).split(','),
                    monthNames  : (wpSmartShopJavascriptLocalization.monthNames).split(','),
                    dateFormat  : wpSmartShopJavascriptLocalization.dateFormat,
                    closeText   : wpSmartShopJavascriptLocalization.closeText
                });
            }


            /* List View: Swipe Button */
            if ( $( '#wpssProductEnabled' ).length ) {
                $( '.wpdk-form-swipe' ).on( 'swipe', onSwipeButton );
            }

            /* Sono in Add/Edit */
            if ( $( 'select.wpss-product-rule-id' ).length ) {

                /* Sezione per la gestione del prezzo  */

                /**
                 * Se cambio il prezzo di base vado a riaggiornare tutte le eventuali altri voci delle regole, prendendo - visto
                 * che sarà presente per forza - la percentuale così da mantenere i prezzi proporzionati; ad esempio se un prezzo
                 * base era impostato a 100 e un discount al 50% di sconto, se il prezzo base scende a 80 il prezzo di discount sarà
                 * di 40.
                 */
                $( 'input#wpss_product_base_price' ).blur( onChangeBasePrice );

                /**
                 * Auto calcolo per prezzo > percentuale e inverso
                 */
                $( 'input.wpss-product-rule-price' ).live( 'blur', onChangePriceRule );

                $( 'input.wpss-product-rule-percentage' ).live( 'blur', onChangePercentageRule );

                /* Sezione Appearance */
                $( 'input.wpss-product-appearance-add' ).live( 'click', onAddAppearanceRule );

                $( 'input.wpss-product-appearance-delete' ).live( 'click', onDeleteAppearanceRule );

            }

            // ---------------------------------------------------------------------------------------------------------
            // Events List View
            // ---------------------------------------------------------------------------------------------------------

            /* Swipe button sulla lista prodotti */
            function onSwipeButton( a, swipeButton, status, userdata ) {
                var params = {
                    action  : 'action_product_update_status',
                    id      : userdata,
                    enabled : status
                };

                $.post( wpSmartShopJavascriptLocalization.ajaxURL,
                    params, function ( data ) {
                        var result = $.parseJSON( data );

                        if ( typeof result.message !== 'undefined' ) {
                            alert( result.message );
                        }
                        if ( swipeButton.parent().prev( 'td.column-available' ).length ) {
                            swipeButton.parent().prev( 'td.column-available' ).html( result.content );
                        }
                    } );
            }

            // ---------------------------------------------------------------------------------------------------------
            // Events Add/Edit products rules price
            // ---------------------------------------------------------------------------------------------------------

            /* Prezzo base modificato */
            function onChangeBasePrice() {

                var newBasePrice = Math.max( parseFloat( $( this ).val().replace( ',', '.' ) ), 0 );

                $( 'input.wpss-product-rule-percentage' ).each( function ( i, e ) {
                    var percentage = Math.max( parseFloat( $( e ).val().replace( ',', '.' ) ), 0 );
                    if ( percentage > 0 ) {
                        var newPrice = newBasePrice * ((100 - percentage) / 100);
                        $( e )
                            .parents('tr')
                            .find( 'input.wpss-product-rule-price' )
                            .val( newPrice.toFixed( 2 ) );
                    }
                } );

                var basePrice = parseFloat( $( 'input#wpss_product_base_price' ).val().replace( ',', '.' ) );
                $( 'input#wpss_product_base_price' ).val( basePrice.toFixed( 2 ) );
            }

            /* Prezzo di regola cambiato */
            function onChangePriceRule() {
                var basePrice = parseFloat( $( 'input#wpss_product_base_price' ).val().replace( ',', '.' ) );
                $( 'input#wpss_product_base_price' ).val( basePrice.toFixed( 2 ) );

                var newPrice = Math.max( parseFloat( $( this ).val().replace( ',', '.' ) ), 0 );
                newPrice = isNaN( newPrice ) ? 0 : newPrice.toFixed( 2 );
                $( this ).val( newPrice );
                var percentage = 100 - parseFloat( (newPrice / basePrice) * 100 );
                $( this )
                    .parents('tr')
                    .find( 'input.wpss-product-rule-percentage' )
                    .val( percentage.toFixed( 4 ) );
            }

            /* Percentuale */
            function onChangePercentageRule() {
                var basePrice = parseFloat( $( 'input#wpss_product_base_price' ).val().replace( ',', '.' ) );
                $( 'input#wpss_product_base_price' ).val( basePrice.toFixed( 2 ) );

                var percentage = Math.max( parseFloat( $( this ).val().replace( ',', '.' ) ), 0 );
                percentage = isNaN( percentage ) ? 0 : percentage;

                var newPrice = basePrice * ((100 - percentage) / 100);
                $( this ) .val( percentage.toFixed( 4 ) );

                $( this )
                    .parents('tr')
                    .find( 'input.wpss-product-rule-price' )
                    .val( newPrice.toFixed( 2 ) );
            }

            // ---------------------------------------------------------------------------------------------------------
            // Events Add/Edit products rules appearance
            // ---------------------------------------------------------------------------------------------------------

            /* Aggiunge una riga per l'inserimento di una regola price */
            function onAddAppearanceRule() {
                var clone = $( '.wpss-product-appearance-master' ).clone();
                $( this ).remove();
                $( '.wpss-product-appearance-rules' ).last().next().before( clone );
                clone.removeClass( 'wpss-product-appearance-master' ).slideDown();
            }

            /* Rimuove una riga di regola */
            function onDeleteAppearanceRule() {
                $( this ).parents( '.wpdk-form-group' ).slideUp( function () {
                    $( this ).remove()
                } );
            }

            return _productPostType;
        }

        return init();

    })();


    // -----------------------------------------------------------------------------------------------------------------
    // Coupons
    //
    // Backend gestione Coupon
    // -----------------------------------------------------------------------------------------------------------------

    var Coupon = (function () {

        var _coupon = {};

        function init() {
            // Edit: click sui tre radio button per la selezione delle restrizioni su prodotto e tipo prodotto
            $( '#wpdk-form-choose-button_id_product' ).click( dialogCouponProductPicker );
            $( '#wpdk-form-choose-button_id_product_type' ).click( dialogCouponProductTypePicker );
            $( '#wpdk-form-choose-button_id_owner' ).click( dialogCouponUserPicker );
            return _coupon;
        }

        // -------------------------------------------------------------------------------------------------------------
        // Restrizione per prodotto
        // -------------------------------------------------------------------------------------------------------------

        /* Prodcuct selector */
        function dialogCouponProductPicker() {
            var args = {
                dialogClass : 'wpdk-dialog-jquery-ui',
                modal       : true,
                resizable   : true,
                draggable   : true,
                closeText   : wpSmartShopJavascriptLocalization.closeText,
                title       : wpSmartShopJavascriptLocalization.productPickerTitle,
                open        : dialogCouponProductPickerDidOpen,
                width       : 640,
                height      : 440,
                minWidth    : 500,
                minHeight   : 460,
                buttons     : [
                    {
                        text  : wpSmartShopJavascriptLocalization.Cancel,
                        click : function () {
                            $( this ).dialog( "close" );
                        }
                    }
                ]
            };

            if ( $( '#wpss-dialog-coupon-product-picker' ).parents( 'div.wpdk-jquery-ui .ui-dialog' ).length ) {
                $( '#wpss-dialog-coupon-product-picker' ).dialog( args );
            } else {
                $( '#wpss-dialog-coupon-product-picker' ).dialog( args ).parent( ".ui-dialog" ).wrap( "<div class='wpdk-jquery-ui'></div>" );
            }
        }

        /* Product Dialog open */
        function dialogCouponProductPickerDidOpen( event, ui ) {
            $( '#wpss-dialog-coupon-product-picker' ).html( WPSmartShop.wait32x32 );

            /* @todo Rinomicare oggetto di localizzazione e ajaxURL -> url_ajax */
            $.post( wpSmartShopJavascriptLocalization.ajaxURL, {
                    action : 'action_product_picker_display',
                    id     : 'pp_coupon'
                }, function ( data ) {

                    var result = $.parseJSON( data );

                    if ( typeof(result.message) != 'undefined' ) {
                        alert( result.message );
                    }

                    $( '#wpss-dialog-coupon-product-picker' ).html( result.content );
                    $( '#pp_coupon' ).on( 'productPickerDidProductSelected', productDidSelected );
                }
            );
        }

        /* Product did selected */
        function productDidSelected( el, product_picker_id, id_product, description ) {
            $( '#id_product' ).val( id_product );
            $( '#wpdk-form-choose-label_id_product' ).removeClass('hide').text( description );
            $( '#restrict_product' ).attr( "checked", "checked" );
            $( '#wpss-dialog-coupon-product-picker' ).dialog( 'close' );
            return false;
        }

        // -------------------------------------------------------------------------------------------------------------
        // Restrizione per tipo prodotto
        // -------------------------------------------------------------------------------------------------------------

        /* Product Type selector */
        function dialogCouponProductTypePicker() {
            var args = {
                dialogClass : 'wpdk-dialog-jquery-ui',
                modal       : true,
                resizable   : true,
                draggable   : true,
                closeText   : wpSmartShopJavascriptLocalization.closeText,
                title       : wpSmartShopJavascriptLocalization.productTypesPickerTitle,
                open        : dialogCouponProductTypePickerDidOpen,
                width       : 640,
                height      : 440,
                minWidth    : 500,
                minHeight   : 460,
                buttons     : [
                    {
                        text  : wpSmartShopJavascriptLocalization.Cancel,
                        click : function () {
                            $( this ).dialog( "close" );
                        }
                    }
                ]
            };
            if ( $( '#wpss-dialog-coupon-product-picker' ).parents( 'div.wpdk-jquery-ui .ui-dialog' ).length ) {
                $( '#wpss-dialog-coupon-product-picker' ).dialog( args );
            } else {
                $( '#wpss-dialog-coupon-product-picker' ).dialog( args ).parent( ".ui-dialog" ).wrap( "<div class='wpdk-jquery-ui'></div>" );
            }

        }

        /* Aperto carico */
        function dialogCouponProductTypePickerDidOpen( event, ui ) {

            $( '#wpss-dialog-coupon-product-picker' ).html( WPSmartShop.wait32x32 );

            $.post( wpSmartShopJavascriptLocalization.ajaxURL, {
                    action       : 'action_product_picker_display',
                    id           : 'pp_coupon',
                    hide_product : true
                }, function ( data ) {
                    var result = $.parseJSON( data );

                    if ( typeof(result.message) != 'undefined' ) {
                        alert( result.message );
                    }
                    $( '#wpss-dialog-coupon-product-picker' ).html( result.content );
                    $( '#pp_coupon' ).on( 'productPickerDidProductTypeSelected', productTypeDidSelected );
                }
            );
        }

        /* Tipo prodotto selezionato */
        function productTypeDidSelected( el, product_picker_id, id_product_type, description ) {
            $( '#id_product_type' ).val( id_product_type );
            $( '#wpdk-form-choose-label_id_product_type' ).removeClass('hide').text( description );
            $( '#restrict_product_type' ).attr( "checked", "checked" );
            $( '#wpss-dialog-coupon-product-picker' ).dialog( 'close' );
            return false;
        }

        // -------------------------------------------------------------------------------------------------------------
        // Restrizione per utente
        // -------------------------------------------------------------------------------------------------------------

        /* Users selector */
        function dialogCouponUserPicker() {
            var args = {
                dialogClass : 'wpdk-dialog-jquery-ui',
                modal       : true,
                resizable   : true,
                draggable   : true,
                closeText   : wpSmartShopJavascriptLocalization.closeText,
                title       : wpSmartShopJavascriptLocalization.userPickerTitle,
                open        : dialogCouponUserPickerDidOpen,
                width       : 640,
                height      : 440,
                minWidth    : 500,
                minHeight   : 460,
                buttons     : [
                    {
                        text  : wpSmartShopJavascriptLocalization.Cancel,
                        click : function () {
                            $( this ).dialog( "close" );
                        }
                    }
                ]
            };
            if ( $( '#wpss-dialog-userpicker' ).parent( 'div.wpdk-jquery-ui .ui-dialog' ).length ) {
                $( '#wpss-dialog-userpicker' ).dialog( args );

            } else {
                $( '#wpss-dialog-userpicker' ).dialog( args ).parent( ".ui-dialog" ).wrap( "<div class='wpdk-jquery-ui'></div>" );

            }
        }

        /* User pikcer dialog aperto */
        function dialogCouponUserPickerDidOpen() {
            $('.wpdk-tableview').on('wpdkTableViewRowDidSelected', userDidSelected);
        }

        /* Utente selezionato */
        function userDidSelected( a, id_user, row ) {
            var description = row.find( 'td' ).text();
            $( '#id_owner' ).val( id_user );
            $( '#wpdk-form-choose-label_id_owner' ).removeClass('hide').text( description );
            $( '#restrict_user' ).attr( "checked", "checked" );
            $( '#wpss-dialog-userpicker' ).dialog( 'close' );
        }

        return init();

    })();


    // -----------------------------------------------------------------------------------------------------------------
    // ShippingCountries
    //
    // -----------------------------------------------------------------------------------------------------------------

    var ShippingCountries = (function () {

        var _shippingCountries = {};

        function init() {
            $('#continent-select').change(onContinentChanged);
            $('#zone-select').change(onZoneChanged);

            return _shippingCountries;
        }

        /* Continent select */
        function onContinentChanged() {
            $('#continent').val($(this).val());
        }

        /* Zone select */
        function onZoneChanged() {
            $('#zone').val($(this).val());
        }

        return init();

    })();

    // -----------------------------------------------------------------------------------------------------------------
    // Showcase Settings
    //
    // -----------------------------------------------------------------------------------------------------------------

    var Showcase = (function () {

        var _showcase = {};

        /* Change Showcase theme page */
        function onChangeThemePage() {
            if ( $( this ).val() == 'custom' ) {
                $( 'form.wpdk-settings-view-showcase .wpss-showcase-custom-theme' ).slideDown();
            } else {
                $( 'form.wpdk-settings-view-showcase .wpss-showcase-custom-theme' ).slideUp();
            }
        }

        /* Seleziono tipo prodotto tramite checkbox */
        function onChangeProductTypeCheckBox() {
            if ( $( this ).is( ':checked' ) ) {
                $( this ).parent().addClass('wpdk-li-sortable-select');
            } else {
                $( this ).parent().removeClass('wpdk-li-sortable-select');

            }
        }

        /* Swipe button sulla lista prodotti */
        function onSwipeButton( a, swipeButton, status, userdata ) {
            var params = {
                action  : 'action_showcase_update_status',
                id      : userdata,
                enabled : status
            };

            $.post( wpSmartShopJavascriptLocalization.ajaxURL,
                params, function ( data ) {
                    var result = $.parseJSON( data );

                    if ( typeof result.message !== 'undefined' ) {
                        alert( result.message );
                    }
                    if ( swipeButton.parent().prev( 'td.column-available' ).length ) {
                        swipeButton.parent().prev( 'td.column-available' ).html( result.content );
                    }
                } );
        }

        function init() {

            /* List View: Swipe Button */
            if ( $( '#wpss_showcase_enabled' ).length ) {
                $( '.wpdk-form-swipe' ).on( 'swipe', onSwipeButton );
            }

            $( 'div.wpss-showcase-droppable-trash' ).droppable( {
                accept     : '.wpss-product-picker-trashable',
                hoverClass : "state-active",
                tolerance  : 'pointer',
                drop       : handleDrop
            } );

            function handleDrop( event, ui ) {
                ui.draggable.remove();
            }

            /* Edit - Sortable */
            if ( $( 'ul.wpdk-ul-sortable' ).length ) {
                $( 'ul.wpdk-ul-sortable' ).sortable( {
                    axis   : 'y',
                    update : function ( event, ui ) {
                        var order = $(this).sortable('toArray').toString();
                        $('input[name=wpss_showcase_prodct_type_orders]').val(order);
                    }
                } );

                $( 'ul.wpdk-ul-sortable input[type=checkbox]' ).change( onChangeProductTypeCheckBox );
            }

            if ( $( 'form.wpdk-settings-view-showcase' ).length ) {
                /* Change */
                $( 'form.wpdk-settings-view-showcase fieldset.wpdk-form-section1 input[type=radio]' ).change( onChangeThemePage );

            }

            return _showcase;
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

    /**
     * Ajax wait animated gif
     */
    _wpsmartshop.wait16x16 = '<img class="wpss-waiting-16x16" border="0" alt="Wait" src="data:image/gif;base64,R0lGODlhEgASAMQaAHl5d66urMXFw3l5dpSUk5WVlKOjoq+vrsbGw6Sko7u7uaWlpbm5t3h4doiIhtLSz4aGhJaWlsbGxNHRzrCwr5SUkqKiobq6uNHRz4eHhf///wAAAAAAAAAAAAAAAAAAACH/C05FVFNDQVBFMi4wAwEAAAAh+QQFAAAaACwAAAAAEgASAAAFaaAmjmRplstyrkmbrCNFaUZtaFF0HvyhWRZNYVgwBY4BEmFJOB1NlYpJoYBpHI7RZXtZZb4ZEbd7AodFDIYVAjFJJCYA4ISoI0hyuUnAF2geDxoDgwMnfBoYiRgaDQ1WiIqPJBMTkpYaIQAh+QQFAAAaACwBAAEAEAAQAAAFY6AmjhpFkSh5rEc6KooWzIG2LOilX3Kd/AnSjjcyGA0oBiNlsZAkEtcoEtEgrghpYVsQeAVSgpig8UpFlQrp8Ug5HCiMHEPK2DOkOR0A0NzxJBMTGnx8GhAQZwOLA2ckDQ0uIQAh+QQFAAAaACwBAAEAEAAQAAAFZKAmjpqikCh5rVc6SpLGthSFIjiiMYx2/AeSYCggBY4B1DB1JD0ertFiocFYMdGENnHFugxgg2YyiYosFhIAkIpEUOs1qUAvkAb4gcbh0BD+BCgNDRoZhhkaFRVmh4hmIxAQLiEAIfkEBQAAGgAsAQABABAAEAAABWOgJo6aJJEoiaxIOj6PJsyCpigopmNyff0X0o43AgZJk0mKwSABAK4RhaJ5PqOH7GHAHUQD4ICm0YiKwCSHI7VYoDLwDClBT5Di8khEY+gbUBAQGgWEBRoWFmYEiwRmJBUVLiEAIfkEBQAAGgAsAQABABAAEAAABWSgJo7a85Aoia1YOgKAxraShMKwNk0a4iOkgXBAEhgFqEYjZSQ5HK6RQqHJWDPRi/Zyxbq2Fw0EEhUxGKRIJEWhoArwAulAP5AIeIJmsdAE/gEoFRUaCYYJfoFRBowGZSQWFi4hACH5BAUAABoALAEAAQAQABAAAAVloCaOGgCQKGma6eg42iAP2vOgWZ5pTaNhQAxJtxsFhSQIJDWZkCKR1kgi0RSuBSliiyB4CVKBWKCpVKQiMWmxSCkUqIQ8QbrYLySD3qChUDR3eCQWFhoHhwcaDAxoAY4BaCSOLSEAIfkEBQAAGgAsAQABABAAEAAABWOgJo6a45Aoma1ZOkaRxrYAgBZ4oUGQVtckgpBAGhgHqEol1WiQFgvX6PHQJK4JKWaLMXgNWq7GYpGKJhMShZKSSFCH+IGEqCNIgXxAo1BoBIACKHkaF4YXf4JSh4hmIwwMLiEAIfkEBQAAGgAsAQABABAAEAAABWSgJo5aFJEoWaxFOi6LRsyE5jhooidaVWmZYIZkKBpIwiHJYklBICQKxTUCADSH7IFqtQa+AepgPNB8qaJGg6RQpB4P1GV+IWHuGBK9LpFo8HkkDAwaCIYIGhMTaAKNAmgkjS4hADs=" />';
    _wpsmartshop.wait32x32 = '<img class="wpss-waiting-32x32" border="0" alt="Wait" alt="" src="data:image/gif;base64,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" />';


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
        for ( property in obj ) {
            output += property + ': ' + obj[property] + ';\n';
            if( typeof window.console !== 'undefined' ) {
                console.log( output );
            }
        }
        return(output);
    }

    // -----------------------------------------------------------------------------------------------------------------
    // End
    // -----------------------------------------------------------------------------------------------------------------

    return _wpsmartshop;

})(jQuery);
