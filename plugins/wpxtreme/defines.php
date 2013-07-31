<?php
/**
 * @description        Defines
 *
 * @package            WPXtreme
 * @author             =undo= <g.fazioli@wpxtre.me>
 * @copyright          Copyright (c) 2012 wpXtreme, Inc.
 * @created            07/06/12
 * @version            1.0.0
 *
 */


/* Short hand for text domain. */
define( 'WPXTREME_TEXTDOMAIN', $this->textdomain );

define( 'WPXTREME_URL_ASSETS', $this->url_assets );
define( 'WPXTREME_URL_CSS', $this->url_css );
define( 'WPXTREME_URL_JAVASCRIPT', $this->url_javascript );
define( 'WPXTREME_URL_IMAGES', $this->url_images );

/*
* Path unix: /var/
*/

/* Set constant path: plugin directory. */
define( 'WPXTREME_PATH', $this->path );
define( 'WPXTREME_PATH_CLASSES', $this->path_classes );

/* @deprecated Solo per debug */
define( 'WPXTREME_DOWNLOAD_PATH', trailingslashit( trailingslashit( WP_PLUGIN_DIR ) . 'wpxtreme-downloads' ) );

/* Custom Post Type Mail */
define( 'WPXTREME_MAIL_CPT_KEY', 'wpx-mail' );
define( 'WPXTREME_MAIL_CPT_QUERY_VAR', 'wpx_mail' );

/* Placeholder per compilazione dinamica Mail */
define( 'WPXTREME_MAIL_PLACEHOLDER_USER_FIRST_NAME', '${USER_FIRST_NAME}' );
define( 'WPXTREME_MAIL_PLACEHOLDER_USER_LAST_NAME', '${USER_LAST_NAME}' );
define( 'WPXTREME_MAIL_PLACEHOLDER_USER_DISPLAY_NAME', '${USER_DISPLAY_NAME}' );
define( 'WPXTREME_MAIL_PLACEHOLDER_USER_EMAIL', '${USER_EMAIL}' );
define( 'WPXTREME_MAIL_PLACEHOLDER_USER_PASSWORD', '${USER_PASSWORD}' );
define( 'WPXTREME_MAIL_PLACEHOLDER_DOUBLE_OPTIN_ACTIVATION_URL', '${DOUBLE_OPTIN_ACTIVATION_URL}' );

/* Default wpx Plugin Store capability */
define( 'WPXTREME_DEFAULT_PLUGIN_STORE_CAPABILITY', 'manage_options' );