/**
 * Created with JetBrains PhpStorm.
 * User: WebeingNet
 * Date: 24/10/12
 * To change this template use File | Settings | File Templates.
 */

jQuery( document ).ready( function ( $ ) {

    var ajaxUrl = wpphVars.ajaxUrl;


    $("#placeholder-summary td .del").live('click', function(){
        var aPos = oTable.fnGetPosition(this.parentNode);
        var aData = oTable.fnGetData(aPos[0]);
        $.ajax({
            type: "GET",
            url: ajaxUrl,
            data: "id=" + aData[1],
            success: function(msg){
                oTable.fnDeleteRow(aPos[0]);
            }
        });
    });


    $.fn.dataTableExt.oApi.fnReloadAjax = function ( oSettings, sNewSource ) {

        if ( typeof sNewSource != 'undefined' ) {
            oSettings.sAjaxSource = sNewSource;
        }

        this.fnClearTable( this );
        this.oApi._fnProcessingDisplay( oSettings, true );

        aoData = [];
        aoData.push(
            { "name":"action","value":"wpph_datatables_summary" },
            { "name":"id","value": $('#product_id').val() }
        );

        doAjax(oSettings.sAjaxSource, aoData, $.proxy(function(json) {

            for ( var i=0 ; i<json.aaData.length ; i++ ) {
                this.oApi._fnAddData( oSettings, json.aaData[i] );
            }

            oSettings.aiDisplay = oSettings.aiDisplayMaster.slice();
            this.fnDraw( this );
            this.oApi._fnProcessingDisplay( oSettings, false );
        }, this));
    }

    /**
     * Crea il datatable del sommario
     */
    var dTable = $('#placeholder-summary').dataTable({
        "sDom":'frt',
        "oLanguage": {
            "sSearch": "Ricerca rapida:"
        },
        "aaSorting": [[1,'asc']],
        //*"sPaginationType": "full_numbers",
        "bProcessing": false,
        "bServerSide": false,
        "sAjaxSource": ajaxUrl,
        "bJQueryUI": false,
        "iDisplayLength": 100,

        "fnServerData": function ( sSource, aoData, fnCallback ) {

            var pid = $('#product_id').val();
            if(pid != ""){

                aoData.push(
                    { "name":"action","value":"wpph_datatables_summary" },
                    { "name":"id","value": pid }
                );

                doAjax(sSource, aoData, fnCallback);
            }
        },
        "aoColumnDefs" : [
            {
                "aTargets": [6],
                "fnCreatedCell" : function(nTd, sData, oData, iRow, iCol){
                    var ed = $('<button style="margin: 0">Modifica</button>');
                    var del = $('<button style="margin: 0">Elimina</button>');

                    var idU = $(nTd).text();
                    $(nTd).empty();

                    ed.button().addClass('s_edit button blue').val(idU);
                    del.button().addClass('s_delete button orange').val(idU);

                    /**
                     * Edit Button Click
                     */
                    ed.on('click',function(){
                        var el = $(this).parent().parent().find('td');
                        var order = $(this).parent().parent().find('td').eq(4).text();
                        var note = $(this).parent().parent().find('td').eq(5).text();

                        //doAjax(ajaxUrl, editData, fnEditCallback);
                        places = $(this).parent().parent().find('td').eq(2).text();
                        user = $(this).val();

                        //Elaboro la lista dei tavoli
                        if(places){
                            var places_list = places.split(',');
                            $( '#placeholder-reservation-table-number').html('');
                            $.each(places_list,function(key, value){
                                table = value.split('-');
                                if(table.length > 1)
                                    value = table[1];
                                addTable( value.trim() )
                            });
                        }

                        //Imposto i dati utente
                        $('#wpxph_stats_filter_user').val( el.eq(3).text()).attr('disabled','disabled'); //email
                        $('#wpxph_stats_filter_id_user').val(idU); //ID
                        $('#wpxph_order_id').val(order);
                        $('#wpph_note').val(note);

                        $( '#placeholder-reservation-table-number').removeClass('to-remove'); //rimuovo la classe se per caso avessi fatto modifiche

                        //apro la dialog
                        $( '#edit-placeholder').addClass('editform').dialog( "open" );
                        return false;
                    });


                    /**
                     * Delete Button Click
                     */
                    del.on('click',function(){

                        places = $(this).parent().parent().find('td').eq(2).text();
                        //user = $(this).parent().parent().find('td').eq(6).text();
                        var user = $(this).val();
                        var order = $(this).parent().parent().find('td').eq(4).text();
                        var product = $('#product_id').val();



                        if(places){
                            var places_list = places.split(',');
                            if ( !confirm('Sicuri di voler rimuovere i posti ['  + places + "] ?") )
                                return false;
                        }


                        deleteData = {
                            "date_start":$('#wpph_date_start_filter').val(),
                            "date_expiry":$('#wpph_date_expiry_filter').val(),
                            "user": user,
                            "order_id": order,
                            "product_id": product,
                            "tablelist":places_list,
                            "action":"wpph_datatables_delete_places"
                        };

                        doAjax(ajaxUrl, deleteData, fnReloadCallback);

                        return false;
                    });

                    $(nTd).prepend(del).prepend(ed);
                }
            }],
        "fnDrawCallback": function( oSettings ) {
        }
    });

    /**
     * Funzione generica per la gestione delle transazioni Ajax in datatable
     * Realizzata per gestire più semplicemente le tabelle
     *
     * @param sSource
     * @param aoData
     * @param fnCallback
     */
    function doAjax(sSource, aoData, fnCallback){
        $.ajax( {
            "dataType": 'json',
            "type": "POST",
            "url": sSource,
            "data": aoData,
            "success": fnCallback
        } );
    }

    function fnEditCallback(response){
        dTable.fnReloadAjax();
    }

    function fnReloadCallback(response){
        //document.location.reload( true );
        $('#wpph_search_product').submit();
    }


    $( ".bnm-dialog-reservations")
        .tabs()
        .fadeIn(700);


    /*$( ".accordion-placeholder" ).accordion({
        collapsible: true
    });*/

    $('.editform #placeholder-reservation-table-number li').live('click',function(){
        $(this).toggleClass('to-remove');
    });

    $('.close-box a').live('click',function(){
        $('.accordion-content').slideToggle(500);
        return false;
    });

    /* Validazione del form */
    $( 'form#wpph_placeholder_booking' ).validate({
        ignore         : ":not(:visible)",
        ignoreTitle    : true,
        errorClass     : "wpdk-form-wrong",
        validClass     : 'wpdk-form-ok',
        rules: {
            wpxph_stats_filter_user: "required"
        },
        messages: {
            wpxph_stats_filter_user: "Seleziona un utente"
        }
    });


    $('#selectable')
        .bind( "mousedown", function ( e ) {
            e.metaKey = true; // Disabilita la selezione esclusiva
            deselectGroup( $(e.target) ); //Deseleziona il gruppo associato  e toglie i tavoli dalla lista
        } )
        .selectable({
            stop: function(event,tableGroups) {
                $( '.ui-selected', this ).each(function() {

                    if ( $(this).hasClass('ui-selected') ){

                        //Seleziona il gruppo associato
                        selectGroup( $(this) );

                    }
                });
            }
        });


    $('#selectable-balconata')
        .bind( "mousedown", function ( e ) {
            e.metaKey = true; // Disabilita la selezione esclusiva
            deselectGroup( $(e.target) ); //Deseleziona il gruppo associato  e toglie i tavoli dalla lista
        } )
        .selectable({
            stop: function(event,tableGroups) {
                $( '.ui-selected', this ).each(function() {

                    if ( $(this).hasClass('ui-selected') ){

                        //Seleziona il gruppo associato
                        selectGroup( $(this) );

                    }
                });
            }
        });

    /**
     * API SECTION
     */

    /**
     * Imposta i gruppi
     * @return {*}
     */
    function getGroups(){
        return tableGroups = new Array(

            /* Piano terra */
            new Array("26T","26BIS","27T","27BIS","27TR","28T","28BIS","28TR","29T","29BIS","29TR","30T","30BIS","30TR"),
            new Array("31T","31BIS","31TR","32T","32BIS","32TR","33T","33BIS","33TR","34T","34BIS","34TR","40T","40BIS"),
            new Array("49T","49BIS","48T","48BIS","48TR","47T","47BIS","47TR","46T","46BIS","46TR","45T","45BIS"),
            new Array("44T","44BIS","44TR","43T","43BIS","42T","42BIS","41T","41BIS"),
            new Array("23BIS","23T","2BIS","2F","14F","14BIS","37T","37BIS","24BIS","24T","3BIS","3F","12F","12BIS","36T","36BIS","4BIS","4F","11F","11BIS","6F","7F","8F","9F","5","6BIS","7BIS","8BIS","9BIS","10","6TR","7TR","8TR","8TR","9TR","7QR","8QR"),

            /* Balconata */
            new Array("161","161BIS","162","162BIS","163","163BIS","164","164BIS","165","165BIS","166","166BIS"),
            new Array("131","132","133","134","135","136","137","138","139","140","121","122","123","124","125","126","127","128","129","130"),
            new Array("141","142","143","144","145","146","147","148","149","150","151","152","153","154","155","156","157","158","159","160"),
            new Array("101","102","103","104","105","106","107","108","109","110","111","112","113","114","115","116","117","118","119","120")
        );
    }

    function addTable( tName ) {
        var result = $('#placeholder-reservation-table-number'); //Ul che gestisce tutti i tavoli da inserire

        result.append('<li class="' + tName +'">' + tName + '</li>' );

    }

    //Reset generici su tutta la maschera
    function resetTables(){
        $('#placeholder-reservation-table-number').empty(); //Svuota la cache di tavoli selezionati
    }

    function removeTable( tName ) {
        $( '#placeholder-reservation-table-number li.' + tName ).remove();
    }

    // Rimuove la prenotazione dal DB
    function removeReservationFromServer( table ){

        $.ajax({
            type: "POST",
            url: ajaxUrl,
            data: {
                action: "wpph_remove_reservation",
                date_start: $('#wpph_date_start_filter').val(),
                date_expiry: $('#wpph_date_expiry_filter').val(),
                tablelist: table
            },
            dataType: "json",
            success: function(data,fnEditCallback){
                $('.wpph-plan-reservations-cell_' + data).find('div').removeClass('wpph-plan-place-taken').removeClass('wpph-plan-reservations-table-busy');
                fnReloadCallback();
            },
            error: function(data){
                console.log("errore " + data);
                return;
            }
        });
    }

    //Deseleziona i tavoli associati al gruppo g
    function deselectGroup(g){
        var tableGroups = getGroups();

        el = $( '.ui-selectable li' ).index( g );
        for (var groupEl in tableGroups[el]){
            $( '.wpph-plan-reservations-table[data-place_name="' + tableGroups[el][groupEl] + '"]' ).removeClass('wpph-plan-place-waiting');
            removeTable( tableGroups[el][groupEl] );
        }
    }

    //Seleziona i tavoli associati al gruppo g
    function selectGroup(g){
        var tableGroups = getGroups();

        el = $( '.ui-selectable li' ).index( g );

        var valid = true;

        //Check some selected items
        for (var groupEl in tableGroups[el]){
            $currEl = $( '.wpph-plan-reservations-table[data-place_name="' + tableGroups[el][groupEl] + '"]' );
            if ( $currEl.hasClass('wpph-plan-place-taken') || $currEl.hasClass('wpph-plan-reservations-table-busy') ){

                g.removeClass('ui-selected');

                resetTables();

                valid = false;
                continue;
            }

        }
        if (valid == true){
            for (var groupEl in tableGroups[el]){

                $( '.wpph-plan-reservations-table[data-place_name="' + tableGroups[el][groupEl] + '"]' ).addClass('wpph-plan-place-waiting');
                addTable( tableGroups[el][groupEl] ); //Aggiungo il tavolo alla lista da salvare
            }
        }
        else
            alert('La selezione cumulativa non è possible per questo gruppo: ci sono elementi del gruppo già prenotati!');
    }



    $('#datatable-placeholder a.editor_edit').click(function() {
        $( '#edit-placeholder' ).dialog( "open" );
        return false;
    });


    $('#edit-placeholder').dialog({
        width: '750px',
        autoOpen: false,
        resizable: false,
        modal: true,
        show: { effect: 'slide', direction: "down" },
        hide: { effect: 'slide', direction: "up" },
        beforeClose: function(){
            $( '#placeholder-reservation-table-number').html(''); //Rimuovo tutti i tavoli in lista ad ogni chiusura
        },
        buttons: {
            Cancel: function() {

                //Rimuovo le classi dei selezionati e li rimuovo dalla lista degli invii
                $('table.wpph-plan-reservations td div.wpph-plan-place-waiting').each(function(){
                    $(this).removeClass('wpph-plan-place-waiting');
                    removeTable( $(this).attr('data-place_name') );
                });

                $('#selectable .ui-selected').removeClass('ui-selected');
                $( this ).dialog( "close" );
            },
            "Prenota": function() {

                if(!$( 'form#wpph_placeholder_booking' ).valid()) //Controllo di validazione
                   return false;

                var tablelist = new Array();

                /**
                 * Controllo se abbiamo qualcosa da rimuovere
                 */
                var items = new Array();
                var action;
                itemsToRemove = $('ul#placeholder-reservation-table-number li.to-remove');

                if ( $(this).hasClass('editform') ){
                    if (itemsToRemove.size() > 0) { //Se ci sono tavoli da eliminare...
                        items = itemsToRemove;
                        action = "wpph_datatables_delete_places";
                    } else { //altrimenti aggiorno solo le note
                        action = "wpph_datatables_update_places_notes";
                        items =  $('ul#placeholder-reservation-table-number li');
                    }
                }
                else if (!$(this).hasClass('editform')){ //mi accerto che non siamo in edit per fare la prenotazione tavoli (evita la riprenotazione)
                    items =  $('ul#placeholder-reservation-table-number li');
                    action = "wpph_reservations";
                }

                /*
                else if (  $(this).hasClass('editform') ){
                    action = "wpph_update_who"; //@todo Non richiesto per ora, eventualmente implementare il metodo lato server
                }*/
                if( items.length > 0 ){
                   items.each(function(){
                        tablelist.push( $(this).text() );
                    });
                }


                var order = $('#wpxph_order_id').val();


                $('ul#placeholder-reservation-table-number').after('<div id="messages"></div>');

                $.ajax({
                    type: "POST",
                    url: ajaxUrl,
                    data: {
                        action: action,
                        user_id: $('#wpxph_stats_filter_id_user').val(),
                        order_id: order,
                        product_id: $('#id_product').val(),
                        procuct_title: $('#wpph_product_title').val(),
                        date_start: $('#wpph_date_start_filter').val(),
                        date_expiry: $('#wpph_date_expiry_filter').val(),
                        tablelist: tablelist,
                        note: $('#wpph_note').val()
                    },
                    dataType: "json",
                    success: function(data){

                        txt = "Operazione effettuata!";

                        $.each(data, function(i, item) {

                            if ( item != false ){
                                $('.' + item).addClass('reserved');
                                $('#messages').text(txt);
                            }
                            else if( item.success != ""){
                                $('#messages').text(txt + " Elementi modificati");
                            }
                            else{
                                $('.' + item).addClass('error');
                                $('#messages').text(txt + " Alcuni tavoli non sono stati prenotati");
                            }

                        });


                        /**
                         * Refresh datatable data
                         */


                        if ( $(this).hasClass('editform') )
                            dTable.fnReloadAjax();
                        else
                            fnReloadCallback();

                        return;
                    },
                    error: function(msg){
                        $('#messages').text(txt + " Errori durante la scrittura dei dati!");
                        return;
                    }
                });

                setTimeout(function(){

                    //Rimuovo le classi dei selezionati, aggiungo le classi Taken e li rimuovo dalla lista degli invii
                    $('table.wpph-plan-reservations td div.wpph-plan-place-waiting').each(function(){
                        $(this).removeClass('wpph-plan-place-waiting').addClass('wpph-plan-place-taken');
                        removeTable( $(this).attr('data-place_name') );
                    });

                    $('#edit-placeholder').removeClass('editform').dialog( "close" );
                    $('#wpxph_stats_filter_user').removeAttr('disabled');
                    $('.to-remove').removeClass('to-remove');
                    $('#messages').html('');

                }, 3000);

            }
        }
    });


    function singleClick(e) {
        e.stopPropagation();
        $curEl = $(this).find('.wpph-plan-reservations-table');
        if( $curEl.hasClass('wpph-plan-place-taken') || $curEl.hasClass('wpph-plan-reservations-table-busy') ){
            if ( confirm('sicuro di voler eliminare la prenotazione?') ){
                $curEl.removeClass('wpph-plan-place-taken');
                removeTable( $(this).find('div.wpph-plan-place-taken').attr('data-place_name') );
                removeReservationFromServer( $(this).find('div').attr('data-place_name') );
                return;
            }
        } else {
            $(this).find('.wpph-plan-reservations-table').toggleClass('wpph-plan-place-waiting');
            if ( $(this).find('.wpph-plan-reservations-table').hasClass('wpph-plan-place-waiting') )
                addTable( $(this).find('.wpph-plan-reservations-table').attr('data-place_name') );
            else
                removeTable( $(this).find('.wpph-plan-reservations-table').attr('data-place_name') );


        }
    }

    function doubleClick(e) {
        if( $('table.wpph-plan-reservations td div.wpph-plan-place-waiting').size() == 0 ){
            $(this).find('div').addClass('wpph-plan-place-waiting');
            addTable( $(this).find('.wpph-plan-reservations-table').attr('data-place_name') );
            $('#edit-placeholder').dialog( "open" );
        } else {
            alert('Ci sono già elementi selezionati, utilizzare il pulsante Prenota per la prenotazione')
        }

    }

    $('.wpph-plan-reservations td').not('wpph-plan-reservations-cell_RSRV-2').bind('click',function(e) {

        var that = this;
        setTimeout(function() {
            var dblclick = parseInt($(that).data('double'), 10);
            if (dblclick > 0) {
                $(that).data('double', dblclick-1);
            } else {
                singleClick.call(that, e);
            }
        }, 300);
    }).dblclick(function(e) {
            $(this).data('double', 2);
            doubleClick.call(this, e);

        });

    $('#btn-reserve, #btn-reserve-b').live('click',function() {
        $( '#edit-placeholder' ).dialog( "open" );
        return false;
    });

   // $('#datatable-placeholder').dataTable();

});