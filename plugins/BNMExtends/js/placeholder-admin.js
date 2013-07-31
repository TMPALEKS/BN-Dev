/**
 * Javascript per la gestione placeholders lato Backend
 *
 * @package         BNMExtends
 * @subpackage      placeholder
 * @author          =undo= <g.fazioli@saidmade.com>
 * @copyright       Copyright © 2010-2011 Saidmade Srl
 *
 */

function debugObject( obj ) {
    var output = '';
    for ( property in obj ) {
        output += property + ': ' + obj[property] + ';\n';
    }
    return(output);
}

/* trigger when page is ready */
jQuery( document ).ready( function ( $ ) {

	$( "#wpph_product_title" ).bind( "autocompleteselect", function(event, ui) {
		txt = ui.item.value;
	 	console.log( txt );
	 	dateStart = txt.substr(0,txt.indexOf(' -'));
	 	dateLessTimeStart = dateStart.substr(0, dateStart.indexOf(' '));
	 	timeLessDateStart = txt.substr(txt.indexOf(' ') + 1, txt.indexOf(' -') - txt.indexOf(' '));

		$( '#wpph_date_start_filter').val(dateStart);
        $( '#date_start').val(dateStart);

		myDateParts = dateLessTimeStart.split("/");
		dateStart = myDateParts[2] + "/" + myDateParts[1] + "/" + myDateParts[0] + " " + timeLessDateStart;
	 	dateStartObj = new Date(dateStart);
	 	dateEndObj = dateStartObj;
	 	dateEndObj.setHours(dateStartObj.getHours() + 2 );
	 	dateExpiry = dateEndObj.getDate() + "/" + ( 1 + dateEndObj.getMonth()) + "/" + dateEndObj.getFullYear() + " " + dateEndObj.getHours() + ":" + dateEndObj.getMinutes();

	  	$( '#wpph_date_expiry_filter').val(dateExpiry);
        $( '#date_expiry').val(dateExpiry);

	});
});