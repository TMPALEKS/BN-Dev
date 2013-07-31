<?php
/**
 * @description
 *
 * @package            wpx Placeholders
 * @subpackage         defines
 * @author             =undo= <g.fazioli@undolog.com>, <g.fazioli@wpxtre.me>
 * @copyright          Copyright (c) 2012 SwpXtreme
 * @link               http://wpxtre.me
 * @created            07/06/12
 * @version            1.0.0
 *
 * @filename           defines.php
 *
 * @note               Questo file viene incluso nel main quindi si ha a disposizione il puntatore $this
 *
 */

// ---------------------------------------------------------------------------------------------------------------------
// Shorthand
// ---------------------------------------------------------------------------------------------------------------------

/* Standard */
define( 'WPXPLACEHOLDERS_VERSION', $this->version );
define( 'WPXPLACEHOLDERS_TEXTDOMAIN', $this->textdomain );
define( 'WPXPLACEHOLDERS_TEXTDOMAIN_PATH', $this->textdomain_path );

/*
* URL
*/

/* Set constant path: plugin URL. */
define( 'WPXPLACEHOLDERS_URL_ASSETS', $this->url_assets );
define( 'WPXPLACEHOLDERS_URL_CSS', $this->url_css );
define( 'WPXPLACEHOLDERS_URL_JAVASCRIPT', $this->url_javascript );
define( 'WPXPLACEHOLDERS_URL_IMAGES', $this->url_images );

/*
* Path unix: /var/
*/

/* Set constant path: plugin directory. */
define( 'WPXPLACEHOLDERS_PATH', $this->path );
define( 'WPXPLACEHOLDERS_PATH_CLASSES', $this->path_classes );


// ---------------------------------------------------------------------------------------------------------------------
// Database
// @todo Rinominare
// ---------------------------------------------------------------------------------------------------------------------

/* Environment */
define( 'kWPPlaceholdersEnvironmentsTableFilename', '/database/EnvironmentTable.sql' );
define( 'kWPPlaceholdersEnvironmentsTableName', 'wpph_environment' );

/* Places */
define( 'kWPPlaceholdersPlacesTableFilename', '/database/PlacesTable.sql' );
define( 'kWPPlaceholdersPlacesTableName', 'wpph_places' );

/* Placeholders */
define( 'kWPPlaceholdersReservationsTableFilename', '/database/ReservationsTable.sql' );
define( 'kWPPlaceholdersReservationsTableName', 'wpph_reservations' );

define( 'WPXPLACEHOLDERS_MENU_CAPABILITY', 'edit_posts' );

// ---------------------------------------------------------------------------------------------------------------------
// Define your own constant for low core events
// ---------------------------------------------------------------------------------------------------------------------

/// Debug mode
define( 'WPXPLACEHOLDERS_DEBUG', false );