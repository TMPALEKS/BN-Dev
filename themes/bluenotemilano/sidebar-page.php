<?php
/**
 * Sidebar for Pages
 *
 * @package			Blue Note milano
 * @subpackage		sidebar-page.php
 * @author 			=undo= <g.fazioli@undolog.com>, <g.fazioli@saidmade.com>
 * @copyright 		Copyright (C)2011 Saidmade Srl.
 * @created			23/11/11
 * @version			1.0
 *
 */
?>
<div id="sidebar" class="right sizeSmall">
	<?php
	if ( is_page( 'blue-note-milano' ) ) {
		dynamic_sidebar( 'sidebar-page-blue-note-milano' );

	} elseif ( is_page( 'ristorante' ) || is_page('restaurant') ) {
		dynamic_sidebar( 'sidebar-page-ristorante' );

	} elseif ( is_page( 'club' ) ) {
		dynamic_sidebar( 'sidebar-page-club' );

	} elseif ( is_page( 'store' ) || $post->ID == 0 ) {
		dynamic_sidebar( 'sidebar-page-store' );

	} elseif ( is_page( 'gallery' ) || wpdk_is_child('gallery') ) {
		dynamic_sidebar( 'sidebar-page-gallery' );

	} elseif ( is_page( 'per-le-aziende' ) || is_page('corporate')) {
		dynamic_sidebar( 'sidebar-page-aziende' );

	} elseif ( is_page( 'contatti' ) || is_page('contacts') ) {
		dynamic_sidebar( 'sidebar-page-contatti' );
	} else {
		dynamic_sidebar( 'sidebar-pages' );
	}
	?>
</div>