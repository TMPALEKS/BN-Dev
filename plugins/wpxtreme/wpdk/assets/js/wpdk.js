/**
 * Aggiunge funzionalità interattive e di controllo ad un form di tipo WPDK
 *
 * @package         WPDK (WordPress Development Kit)
 * @subpackage      assets
 * @author          =undo= <g.fazioli@wpxtre.me>
 * @copyright       Copyright (C)2011 wpXtreme, Inc.
 * @created         30/12/11
 * @version         1.0
 *
 */

var WPDK = (function($) {

    // -----------------------------------------------------------------------------------------------------------------
    // Private variables
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Internal class pointer
     */
    var _wpdk = {};

    /**
     * Questa parte esegue un attach globale sullo starting e end di ajax in modo da far sempre comporari al centro
     * dello schermo una preloader. Ora, questo dovrebbe essere tra le patch. Per come è messo ora è sempre visualizzato
     * anche togliendo enhanced dalle impostazioni di wpXtreme
     */

    /* Ajax preloader start. */
    $( document ).ajaxStart( function () {
        $( '<div />' )
            .addClass( 'wpxm-loader' )
            .appendTo( 'body' )
            .fadeIn( 500 );
    } );

    /* Ajax preloader end. */
    $( document ).ajaxComplete( function () {
        $( 'div.wpxm-loader' )
            .fadeOut( function () {
                $( this ).remove()
            } );
    } );

    /* Twitter Alert */
    $().alert();

    /* Autocomplete */
    $( 'input[data-autocomplete_action]' ).each( function( index, element ) {
        $(element).autocomplete(
            {
                source    : function ( request, response ) {
                    $.post( wpdk_i18n.ajaxURL,
                        {
                            action : $(element).data('autocomplete_action'),
                            term   : request.term
                        },
                        function ( data ) {
                            response( $.parseJSON( data ) );
                        } );
                },
                select    : function ( event, ui ) {
                    var $name = $(element).data('autocomplete_target');
                    $( 'input[name='+$name+']' ).val( ui.item.id );
                },
                minLength : $(element).data('autocomplete_min_length') | 0
            }
        );
    });

    /**
     * Gestione del "copia" e "incolla" tra elementi del dom
     */

    /* Data from/to button add */
    $( '.wpdk-form-button-copy-paste' ).live( 'click', onCopyPaste );
    $( '.wpdk-form-button-remove' ).live( 'click', onRemove );

    /**
     *  Questo è un hacks per i controlli di tipo copy/cut/paste in modo che l'eventuale destinatario di tipo select
     *  multiplo sia inviato via POST/GET
     */

    $( 'form' ).submit( function () {
        $( '[data-paste]' ).each( function () {
            var paste = $( '#' + $( this ).attr( 'data-paste' ) );
            var element_paste_type = paste.get( 0 ).tagName;
            if ( element_paste_type.toLowerCase() == 'select' && paste.attr( 'multiple' ) !== 'undefined' ) {
                paste.find( 'option' ).attr( 'selected', 'selected' );
            }
        } );
    } );

    /*
     * Fullscreen
     */
    if ( $( 'body' ).hasClass( 'wpxm-body' ) ) {
        if ( screenfull ) {
            screenfull.onchange = function () {
                if ( screenfull.isFullscreen ) {
                    $( 'button.wpdk-fullscreen' ).addClass( 'normalscreen' );
                } else {
                    $( 'button.wpdk-fullscreen' ).removeClass( 'normalscreen' );
                }
            };
        }
        $( 'div.wrap' ).prepend( '<button title="" class="wpdk-fullscreen"></button>' );
        $( 'button.wpdk-fullscreen' ).live( 'click', function () {
            if ( screenfull ) {
                screenfull.toggle();
            }
        } );
    }

    // -----------------------------------------------------------------------------------------------------------------
    // Public properties
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Version
     */
    _wpdk.version = "1.0";

    // -----------------------------------------------------------------------------------------------------------------
    // Public methods
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Questo metodo deve essere chiamato quando si eseguono delle modifiche al contenuto del dom. Alcune impostazioni
     * infatti non possono essere fatte tramite il meotdo .live() di jQuery, quindi si è costretti a forzare un refresh.
     */
    _wpdk.refresh = function () {

        /* Campi data. */
        $( 'input.wpdk-form-date' ).datepicker();

        /* Campi data e ora. */
        if ( $().datetimepicker ) {
            $( 'input.wpdk-form-datetime:visible' ).datetimepicker( {
                timeOnlyTitle : wpdk_i18n.timeOnlyTitle,
                timeText      : wpdk_i18n.timeText,
                hourText      : wpdk_i18n.hourText,
                minuteText    : wpdk_i18n.minuteText,
                secondText    : wpdk_i18n.secondText,
                currentText   : wpdk_i18n.currentText,
                dayNamesMin   : (wpdk_i18n.dayNamesMin).split( ',' ),
                monthNames    : (wpdk_i18n.monthNames).split( ',' ),
                closeText     : wpdk_i18n.closeText,
                timeFormat    : wpdk_i18n.timeFormat,
                dateFormat    : wpdk_i18n.dateFormat
            } );
        } else {
            if( typeof window.console !== 'undefined' ) {
                console.log( 'Date Time Picker not loaded' );
            }
        }

        /* Date picker defaults */
        $.datepicker.setDefaults( {
            changeMonth     : true,
            changeYear      : true,
            dayNamesMin     : (wpdk_i18n.dayNamesMin).split( ',' ),
            monthNames      : (wpdk_i18n.monthNames).split( ',' ),
            monthNamesShort : (wpdk_i18n.monthNamesShort).split( ',' ),
            dateFormat      : wpdk_i18n.dateFormat
        } );

        /* Twitter Bootstrap tooltip. */
        $( '.wpdk-tooltip' ).tooltip();

        /* jQuery Tabs */
        $( ".wpdk-tabs" ).each( function () {
            var id = $( this ).attr( "id" );
            if ( typeof id === 'undefined' || id == null || id == "" ) {
                $( this ).tabs( {
                    cookie : {
                        expires : 1
                    }
                } );
            } else {
                $( this ).tabs( {
                    cookie : {
                        expires : 1,
                        name    : id
                    }
                } );
            }
        } );

    }

    // -----------------------------------------------------------------------------------------------------------------
    // Internal methods
    // -----------------------------------------------------------------------------------------------------------------

    //
    // Button Copy/PAste
    //
    function onCopyPaste( e ) {
        /* Recupero options */
        var options = $( this ).attr('data-options' ) ? $( this ).attr('data-options' ).split(' ') : [] ;

        /* @todo Aggiungere evento/filtro */

        var copy = $( '#' + $( this ).attr( 'data-copy' ) );
        var paste = $( '#' + $( this ).attr( 'data-paste' ) );

        /* Determino da dove copio e dove incollo */
        var element_copy_type = copy.get( 0 ).tagName;

        switch ( element_copy_type.toLowerCase() ) {
            case 'input':
                var value = copy.val();
                var text = value;
                if( $.inArray('clear_after_copy', options ) !== false ) copy.val('');
                break;
            case 'select':
                var value = $( 'option:selected', copy ).val();
                var text = $( 'option:selected', copy ).text();
                break;
        }

        if ( value != '' || value != '' ) {

            /* Determino dove devo incollare */
            var element_paste_type = paste.get( 0 ).tagName;

            switch ( element_paste_type.toLowerCase() ) {
                case 'select':
                    paste.append( '<option value="' + value + '">' + text + '</option>' );
                    break;
            }
        }

    }

    function onRemove(e) {
        /* @todo Aggiungere evento/filtro */

        var remove_from = $( this ).attr( 'data-remove_from' );
        $('option:selected', '#' + remove_from).remove();
    }

    //
    // wpdkClearLeft
    //
    function wpdkClearLeft() {
        $( 'span.wpdk-form-clear-left' ).live( 'click', function () {
            $( this ).prev().val( '' ).triggerHandler( 'change' );
        } );
    }

    //
    // wpdkSwipe
    //
    function wpdkSwipe() {
        /**
         * Swipe Availability/Enabled
         *
         * Questo rilascia un messaggio "swipe" con tre parametri:
         *
         * @param mixed a
         * @param object swipeButton
         * @param bool status
         * @param mixed userdata
         *
         * $('.wpdk-form-swipe').on('swipe', function(a, swipeButton, status, userdata) {});
         *
         */
        $( 'span.wpdk-form-swipe span' ).live( 'click', function () {
            var knob = $( this );
            var id = knob.parent().attr( 'id' );
            var userdata = knob.parent().attr( 'wpdk-userdata' );
            var input = $( this ).parent().next();

            $( this ).animate( {
                marginLeft : ($( this ).css( 'marginLeft' ) == '23px') ? '0' : '23px'
                }, 100, function () {
                $( this )
                    .parent()
                    .toggleClass( 'wpdk-form-swipe-on' );
                var enabled = knob.parent().hasClass( 'wpdk-form-swipe-on' ) ? 'on' : 'off';
                input.val( enabled );
                $( this )
                    .parent()
                    .triggerHandler( 'swipe', [knob.parent(), enabled, userdata] );
            } );
        } );
    }

    //
    // wpdkLocked - unlock locked field
    //
    function wpdkLocked() {
        $( '.wpdk-form-locked' ).live( 'click', function () {
            if ( confirm( wpdk_i18n.messageUnLockField ) ) {
                $( this )
                    .attr( 'class', 'wpdk-form-unlocked' )
                    .prev( 'input' )
                    .removeAttr( 'readonly' );
            }
        } );
    }

    // =================================================================================================================
    // WPDKForm
    //
    // =================================================================================================================

    var Form = (function () {

        var _form = {};

        function init() {
            $('#ui-datepicker-div').wrap('<div class="wpdk-jquery-ui"/>');

            wpdkClearLeft();
            wpdkSwipe();
            wpdkLocked();


            return _form;
        }

        _wpdk.refresh();

        return init();

    })();


    // -----------------------------------------------------------------------------------------------------------------
    // WPDKTableView
    //
    // -----------------------------------------------------------------------------------------------------------------

    var TableView = (function () {

        var _tableView = {};
        var _wait16x16 = '<img style="margin:6px 4px 0 8px;float:right;display:block;width:16px;height:16px;border:none" border="0" alt="Wait" src="data:image/gif;base64,R0lGODlhEgASAMQaAHl5d66urMXFw3l5dpSUk5WVlKOjoq+vrsbGw6Sko7u7uaWlpbm5t3h4doiIhtLSz4aGhJaWlsbGxNHRzrCwr5SUkqKiobq6uNHRz4eHhf///wAAAAAAAAAAAAAAAAAAACH/C05FVFNDQVBFMi4wAwEAAAAh+QQFAAAaACwAAAAAEgASAAAFaaAmjmRplstyrkmbrCNFaUZtaFF0HvyhWRZNYVgwBY4BEmFJOB1NlYpJoYBpHI7RZXtZZb4ZEbd7AodFDIYVAjFJJCYA4ISoI0hyuUnAF2geDxoDgwMnfBoYiRgaDQ1WiIqPJBMTkpYaIQAh+QQFAAAaACwBAAEAEAAQAAAFY6AmjhpFkSh5rEc6KooWzIG2LOilX3Kd/AnSjjcyGA0oBiNlsZAkEtcoEtEgrghpYVsQeAVSgpig8UpFlQrp8Ug5HCiMHEPK2DOkOR0A0NzxJBMTGnx8GhAQZwOLA2ckDQ0uIQAh+QQFAAAaACwBAAEAEAAQAAAFZKAmjpqikCh5rVc6SpLGthSFIjiiMYx2/AeSYCggBY4B1DB1JD0ertFiocFYMdGENnHFugxgg2YyiYosFhIAkIpEUOs1qUAvkAb4gcbh0BD+BCgNDRoZhhkaFRVmh4hmIxAQLiEAIfkEBQAAGgAsAQABABAAEAAABWOgJo6aJJEoiaxIOj6PJsyCpigopmNyff0X0o43AgZJk0mKwSABAK4RhaJ5PqOH7GHAHUQD4ICm0YiKwCSHI7VYoDLwDClBT5Di8khEY+gbUBAQGgWEBRoWFmYEiwRmJBUVLiEAIfkEBQAAGgAsAQABABAAEAAABWSgJo7a85Aoia1YOgKAxraShMKwNk0a4iOkgXBAEhgFqEYjZSQ5HK6RQqHJWDPRi/Zyxbq2Fw0EEhUxGKRIJEWhoArwAulAP5AIeIJmsdAE/gEoFRUaCYYJfoFRBowGZSQWFi4hACH5BAUAABoALAEAAQAQABAAAAVloCaOGgCQKGma6eg42iAP2vOgWZ5pTaNhQAxJtxsFhSQIJDWZkCKR1kgi0RSuBSliiyB4CVKBWKCpVKQiMWmxSCkUqIQ8QbrYLySD3qChUDR3eCQWFhoHhwcaDAxoAY4BaCSOLSEAIfkEBQAAGgAsAQABABAAEAAABWOgJo6a45Aoma1ZOkaRxrYAgBZ4oUGQVtckgpBAGhgHqEol1WiQFgvX6PHQJK4JKWaLMXgNWq7GYpGKJhMShZKSSFCH+IGEqCNIgXxAo1BoBIACKHkaF4YXf4JSh4hmIwwMLiEAIfkEBQAAGgAsAQABABAAEAAABWSgJo5aFJEoWaxFOi6LRsyE5jhooidaVWmZYIZkKBpIwiHJYklBICQKxTUCADSH7IFqtQa+AepgPNB8qaJGg6RQpB4P1GV+IWHuGBK9LpFo8HkkDAwaCIYIGhMTaAKNAmgkjS4hADs=" />';

        /* @todo Questo non va bene, fare qui quello che è stato fatto per il Product Picker */
        var _paged = 1;

        function init() {
            if ( $( '.wpdk-tableview' ).length ) { // Se esiste...

                /* Display more rows */
                $( '.wpdk-tableview tbody tr' ).live( 'click', tableViewDisplaydMoreRows );
            }
            return _tableView;
        }

        /**
         * Imposta gli eventi sulle righe del TableView e sull'ultima riga 'speciale' del more...
         */
        function tableViewDisplaydMoreRows() {
            var id = $( this ).attr( 'id' );
            if ( id != '' ) {
                var pa = id.split( '_' );
                var kc = pa[1];
                $( this ).parents( '.wpdk-tableview' ).triggerHandler( 'wpdkTableViewRowDidSelected', [kc, $( this )] );
            } else if ( $( this ).hasClass( 'wpdk-tableview-moreitems' ) ) {
                $( this ).prepend( _wait16x16 );
                var ajaxHook = $( '.wpdk-tableview #wpdk-tableview-ajaxhook' ).val();
                _paged++;
                $.post( wpdk_i18n.ajaxURL, {
                        action : ajaxHook,
                        paged  : _paged
                    }, function ( data ) {
                        $( 'tr.wpdk-tableview-moreitems' ).remove();
                        $( 'table.wpdk-tableview-table tbody' ).append( data );
                    }
                );
            }
        }

        return init();

    })();

    // -----------------------------------------------------------------------------------------------------------------
    // WPDKDynamicTable
    // -----------------------------------------------------------------------------------------------------------------

    var DynamicTable = (function () {

        var _dynamic_table = {};

        function onHoverInDeleteButton() {
            $( this )
                .parents( 'tr' )
                .addClass( 'wpdk-dt-highlight-delete' );
        }

        function onHoverOutDeleteButton() {
            $( this )
                .parents( 'tr' )
                .removeClass( 'wpdk-dt-highlight-delete' );
        }

        function onDeleteItem() {
            $( this ).tooltip( 'hide' );
            $( this )
                .parents( 'tr' )
                .fadeOut( 300, function () {
                    $( this ).remove();
                } );
        }

        function onAddItem() {
            var table = $( this )
                .parents( 'table.wpdk-dynamic-table' );

            var clone = $( this )
                .parents( 'tr' )
                .prevAll( '.wpdk-dt-clone' )
                .clone();

//            clone
//                .find( 'input.wpdk-form-datetime' )
//                .removeAttr( 'id' );

            clone
                .removeClass( 'wpdk-dt-clone' )
                .appendTo( table );

            $( this )
                .hide()
                .next()
                .show( function() { _wpdk.refresh(); });


        }

        function init() {
            $( 'input.wpdk-dt-delete-row' ).live( 'mouseenter', onHoverInDeleteButton );
            $( 'input.wpdk-dt-delete-row' ).live( 'mouseleave', onHoverOutDeleteButton );
            $( 'input.wpdk-dt-delete-row' ).live( 'click', onDeleteItem );

            $( 'input.wpdk-dt-add-row' ).live( 'click', onAddItem );

            return _dynamic_table;
        }

        _wpdk.refresh();

        return init();

    })();

    // -----------------------------------------------------------------------------------------------------------------
    // WPDKDynamicTable
    // -----------------------------------------------------------------------------------------------------------------

    /* @todo Queste sarebbero da spostare nel js di wpxtreme plugin più che qui in wpdk */

    var Users = (function () {

        var _users = {};

        function init() {

            /* Imposto handler se stiamo nella lista utenti */
            if ( $( '#wpdk-user-enabled' ).length ) {

                /* Intercetto lo swipe sull'abilitazione utenti. */
                $( '.wpdk-form-swipe' ).on( 'swipe', function ( el, knob, enabled, userdata ) {
                    /* Invio tramite Ajax l'abilitazione utente */
                    $.post( wpdk_i18n.ajaxURL, {
                            action  : 'action_user_set_status',
                            id_user : userdata,
                            status  : ( enabled == 'off' ) ? 'disabled' : 'confirmed'
                        }, function ( data ) {
                            /* I dati arrivano sempre in jSON, come sempre se message è undefined è tutto ok. */
                            var result = $.parseJSON( data );
                            if ( typeof result.message !== 'undefined' ) {
                                alert( result.message );
                            }
                        }
                    );
                } );
            }

            return _users;
        }

        _wpdk.refresh();

        return init();

    })();

    var Posts = (function () {

        var _posts = {};

        function init() {

            /* Imposto handler se stiamo nella lista utenti */
            if ( $( '#wpdk-post-publish' ).length ) {
                /* Intercetto lo swipe sulla pubblicazione dei post. */
                $( '.wpdk-form-swipe' ).on( 'swipe', function ( el, knob, enabled, userdata ) {
                    /* Invio tramite Ajax lo swipe */
                    $.post( wpdk_i18n.ajaxURL, {
                            action  : 'action_post_set_publish',
                            id_post : userdata,
                            status  : ( enabled == 'off' ) ? 'draft' : 'publish'
                        }, function ( data ) {
                            /* I dati arrivano sempre in jSON, come sempre se message è undefined è tutto ok. */
                            var result = $.parseJSON( data );
                            if ( typeof result.message !== 'undefined' ) {
                                alert( result.message );
                            }
                        }
                    );
                } );
            }

            return _posts;
        }

        _wpdk.refresh();

        return init();

    })();



    // -----------------------------------------------------------------------------------------------------------------
    // End
    // -----------------------------------------------------------------------------------------------------------------

    return _wpdk;

})(jQuery);