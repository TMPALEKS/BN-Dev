<?php
/// @cond private
/**
 * @description        Main class runtime define
 *
 * @package            wpx SmartShop
 * @author             =undo= <g.fazioli@wpxtre.me>
 * @copyright          Copyright (c) 2012 wpXtreme, Inc.
 * @date               07/06/12
 * @version            1.0.0
 *
 */

// ---------------------------------------------------------------------------------------------------------------------
// Shorthand
// ---------------------------------------------------------------------------------------------------------------------

/* Standard */
define( 'WPXSMARTSHOP_NAME', 'wpx SmartShop' );

define( 'WPXSMARTSHOP_VERSION', $this->version );
define( 'WPXSMARTSHOP_TEXTDOMAIN', $this->textdomain );
define( 'WPXSMARTSHOP_TEXTDOMAIN_PATH', $this->textdomain_path );

/*
* URL
*/

/* Set constant path: plugin URL. */
define( 'WPXSMARTSHOP_URL', $this->url );
define( 'WPXSMARTSHOP_URL_ASSETS', $this->url_assets );
define( 'WPXSMARTSHOP_URL_CSS', $this->url_css );
define( 'WPXSMARTSHOP_URL_JAVASCRIPT', $this->url_javascript );
define( 'WPXSMARTSHOP_URL_IMAGES', $this->url_images );

/*
* Path unix: /var/
*/

/* Set constant path: plugin directory. */
define( 'WPXSMARTSHOP_PATH', $this->path );
define( 'WPXSMARTSHOP_PATH_CLASSES', $this->path_classes );
define( 'WPXSMARTSHOP_PATH_DATABASE', $this->path_database );
define( 'WPXSMARTSHOP_PATH_GATEWAY', $this->path_classes . 'payment_gateways/gateways/' );

/**
 * Session
 */
define( 'WPXSMARTSHOP_SESSION_ID', WPXSMARTSHOP_TEXTDOMAIN );


/* Common */
define( 'kWPSmartShopUserCapability', 'edit_posts' );

/* Post Custom Type */

define( 'WPXSMARTSHOP_PRODUCT_POST_KEY', 'wpss-cpt-product' );
define( 'kWPSmartShopProductPostTypeMenuItemPosition', 100 );

define( 'kWPSmartShopStorePagePostTypeKey', 'wpss-store-page' );
define( 'kWPSmartShopStorePagePostTypeMenuItemPosition', 100 );

define( 'kWPSmartShopShowcasePostTypeKey', 'wpss-showcase-page' );
define( 'kWPSmartShopShowcasePostTypeMenuItemPosition', 100 );

// Product Post Type MetaBox extension
define( 'kWPSmartShopProductTypeRuleDatePrice', -1 );
define( 'kWPSmartShopProductTypeRuleOnlinePrice', -2 );

// Taxonomies

define( 'kWPSmartShopProductTypeTaxonomyKey', 'wpss-ctx-product-type' );
define( 'kWPSmartShopProductTagTaxonomyKey', 'wpss-ctx-product-tag' );

// Key for added item in WordPress Post Type Menu
define( 'kWPSmartShopPostTypeMenuKey', 'edit.php?post_type=' . WPXSMARTSHOP_PRODUCT_POST_KEY );
define( 'kWPSmartShopShowcaseTypeMenuKey', 'edit.php?post_type=' . kWPSmartShopShowcasePostTypeKey );

// Database
// dump for dbDelta(), creation and insert values

/* Orders */

define( 'kWPSmartShopOrdersTableFilename', 'OrdersTable.sql' );
define( 'WPXSMARTSHOP_DB_TABLENAME_ORDERS', 'wpss_orders' );

define( 'WPXSMARTSHOP_ORDER_STATUS_PENDING', 'pending' );
define( 'WPXSMARTSHOP_ORDER_STATUS_CONFIRMED', 'confirmed' );
define( 'WPXSMARTSHOP_ORDER_STATUS_CANCELLED', 'cancelled' );
define( 'WPXSMARTSHOP_ORDER_STATUS_DEFUNCT', 'defunct' );


/* Stats */

define( 'WPXSMARTSHOP_DB_TABLENAME_FILENAME_STATS', 'wpxss-stats-desc.sql' );
define( 'WPXSMARTSHOP_DB_TABLENAME_STATS', 'wpss_stats' );


/* Coupon */

define( 'WPXSMARTSHOP_DB_TABLENAME_FILENAME_COUPONS', 'CouponsTable.sql' );
define( 'WPXSMARTSHOP_DB_TABLENAME_COUPONS', 'wpss_coupons' );

define( 'WPXSMARTSHOP_COUPON_STATUS_AVAILABLE', 'available' );
define( 'WPXSMARTSHOP_COUPON_STATUS_PENDING', 'pending' );
define( 'WPXSMARTSHOP_COUPON_STATUS_CONFIRMED', 'confirmed' );
define( 'WPXSMARTSHOP_COUPON_STATUS_CANCELLED', 'cancelled' ); // not use yet


/* Membership */
define( 'kWPSmartShopMembershipsTableFilename', 'MembershipsTable.sql' );
define( 'WPXSMARTSHOP_DB_TABLENAME_MEMBERSHIPS', 'wpss_memberships' );

define( 'WPXSMARTSHOP_MEMBERSHIPS_STATUS_AVAILABLE', 'available' );
define( 'WPXSMARTSHOP_MEMBERSHIPS_STATUS_CURRENT', 'current' );
define( 'WPXSMARTSHOP_MEMBERSHIPS_STATUS_EXPIRED', 'expired' );
define( 'WPXSMARTSHOP_MEMBERSHIPS_STATUS_TRASH', 'trash' );

/* Carrier */
define( 'WPXSMARTSHOP_DB_TABLENAME_FILENAME_CARRIERS', 'CarriersTable.sql' );
define( 'WPXSMARTSHOP_DB_TABLENAME_CARRIERS', 'wpss_carriers' );

/* Shipping Countries */
define( 'kWPSmartShopShippingCountriesTableFilename', 'ShippingCountriesTable.sql' );
define( 'kWPSmartShopShippingCountriesValuesFilename', 'ShippingCountriesValues.sql' );
define( 'WPXSMARTSHOP_DB_TABLENAME_SHIPPING_COUNTRIES', 'wpss_shipping_countries' );

/* Shipments */
define( 'kWPSmartShopShipmentsTableFilename', 'ShipmentsTable.sql' );
define( 'WPXSMARTSHOP_DB_TABLENAME_SHIPMENTS', 'wpss_shipments' );

/* Size Shipments */
define( 'kWPSmartShopSizeShipmentsTableFilename', 'SizeShipmentsTable.sql' );
define( 'WPXSMARTSHOP_DB_TABLENAME_SIZE_SHIPMENTS', 'wpss_size_shipments' );

/* Price format */
/* @todo TRasformare in impostazioni */
define( 'WPXSMARTSHOP_CURRENCY_DECIMAL_POINT', ',' );
define( 'WPXSMARTSHOP_CURRENCY_THOUSANDS_SEPARATOR', '.' );

/// @endcond