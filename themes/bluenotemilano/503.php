<?php
/**
 * 503 page
 *
 * @package         Blue Note Milano Theme
 * @subpackage      503
 * @author          =undo= <g.fazioli@wpxtre.me>, <g.fazioli@undolog.com>
 * @copyright       Copyright (c) 2012 wpXtreme, Inc.
 * @link            http://wpxtre.me
 * @created         30/05/12
 * @version         1.0.0
 *
 */

$settings = WPXtreme::$settings->maintenance();

?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>" />
<title><?php echo $settings['page_title'] ?></title>
<link rel="stylesheet" type="text/css" href="<?php echo trailingslashit( get_bloginfo('template_url') ) ?>503.css" media="screen" />
<link href='http://fonts.googleapis.com/css?family=Oswald:400,300|Muli' rel='stylesheet' type='text/css'>
</head>
<body <?php body_class(); ?>>
<div id="wrapper">
	<div id="content" class="col-full">
    	<div id="main">
	       	<div id="intro" class="block">
	    		<h3><span><?php echo esc_html( $settings['title'] ); ?></span></h3>
	    		<p><?php echo esc_html( $settings['note'] ); ?></p>
	    	</div><!-- #intro -->
   		</div>
    </div><!-- /#content -->
    <a id="login" href="<?php echo esc_url( wp_login_url( site_url( '/' ) ) ); ?>" title="<?php esc_attr_e( 'Log in to your WordPress dashboard' ); ?>"><?php _e( 'Log In' ); ?></a>
</div><!-- /#wrapper -->
</body>
</html>