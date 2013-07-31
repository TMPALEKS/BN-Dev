<?php
//Add image size for pages to use as related thumbnails
add_image_size('page-logo', 120, 120);

//Add Page2Page Connection type to connect pages each other
function wb_connection_types() {
	// Make sure the Posts 2 Posts plugin is active.
	if ( !function_exists( 'p2p_register_connection_type' ) )
		return;

	p2p_register_connection_type( array(
		'name' => 'pages_to_pages',
		'from' => 'page',
		'to' => 'page',
		'admin_box' => array(
		  'show' => 'from',
		  'context' => 'advanced'
		)
	) );
}
add_action( 'init', 'wb_connection_types', 100 );
?>