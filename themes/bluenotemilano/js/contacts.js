/**
 * Gestione del modulo contatti
 *
 * @package         Blue Note Milano
 * @subpackage      contacts
 * @author          =undo= <g.fazioli@undolog.com>, <g.fazioli@saidmade.com>
 * @copyright       Copyright (C)2011 Saidmade Srl.
 * @created         20/12/11
 * @version         1.0
 *
 */

jQuery(document).ready(function ($) {

    /**
     * Focus su Firstname
     */
    //$('input#bnmContactsFirstname').focus();

    // Rules
    var rules = {
        bnmContactsFirstname : "required",
        bnmContactsLastname  : "required",

        bnmContactsEmail            : {
            required : true,
            email    : true
        },
        bnmContactsPhone : {
            required : true
        }
    };

    // Messages
    // @todo Da tradurre
    var messages = {
    };

    $('form.bnmContacts').validate({
        errorPlacement : function (error, element) {
        },
        ignoreTitle    : true,
        errorClass     : "wpdk-form-wrong",
        validClass     : 'wpdk-form-ok',
        rules          : rules
    });


    //<iframe width="425" height="350" frameborder="0" scrolling="no" marginheight="0"
    // marginwidth="0" src="http://maps.google.it/maps/ms?msa=0&amp;
    // msid=206908268514082936027.0004b487658d0b94ea4c1&amp;hl=it&a
    // mp;ie=UTF8&amp;t=m&amp;vpsrc=1&amp;ll=45.489144,9.188873&amp;spn=0,0&amp;output=embed"></iframe><br /><small>Visualizza <a href="http://maps.google.it/maps/ms?msa=0&amp;msid=206908268514082936027.0004b487658d0b94ea4c1&amp;hl=it&amp;ie=UTF8&amp;t=m&amp;vpsrc=1&amp;ll=45.489144,9.188873&amp;spn=0,0&amp;source=embed" style="color:#0000FF;text-align:left">Blue Note</a> in una mappa di dimensioni maggiori</small>

    var latlng = new google.maps.LatLng(45.489144, 9.188873);
    var myOptions = {
        zoom      : 19,
        center    : latlng,
        mapTypeId : google.maps.MapTypeId.ROADMAP
    };
    var map = new google.maps.Map(document.getElementById("map_canvas"), myOptions);

    var marker = new google.maps.Marker({
        position : latlng,
        map      : map,
        title    : 'Blue Note Milano',
        draggable:true,
        animation: google.maps.Animation.DROP
    });

    var infowindow = new google.maps.InfoWindow({
        content : '<p><strong>Via Pietro Borsieri, 37</strong> 20159 - Milano<br/>Tel. +39 02 60856304 Mobile +39 347 4121078</p>'
    });

    //infowindow.open(map, marker);

    google.maps.event.addListener( marker, 'click', function () {
        if ( infowindow )
            infowindow.close();
        else
            infowindow.open( map, marker );
    } );

});