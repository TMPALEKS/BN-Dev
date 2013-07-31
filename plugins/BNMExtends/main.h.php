<?php
/**
 * Global defines and constant
 *
 * @package         BNMExtends
 * @author          =undo= <g.fazioli@saidmade.com>
 * @copyright       Copyright © 2011-2012 Saidmade Srl
 *
 */

// -----------------------------------------------------------------------------------------------------------------
// General
// -----------------------------------------------------------------------------------------------------------------

// ---------------------------------------------------------------------------------------------------------------------
// Version
// ---------------------------------------------------------------------------------------------------------------------
define('kBNMExtendsVersion', '1.0');
define('kBNMExtendsPluginName', 'Blue Note Milnao');
define('kBNMExtendsPluginSlugName', 'blue-note-milano');
define('kBNMExtendsWPMLIntegrationDefaultLanguage', 'it');

// ---------------------------------------------------------------------------------------------------------------------
// Database extra tables
// ---------------------------------------------------------------------------------------------------------------------
define('kBNMExtendsDatabaseTableEventsArtistsFilename', '/Database/BNMExtendsEventsArtists.sql');
define('kBNMExtendsDatabaseTableEventsArtistsName', 'bnmextends_eventsartists'); // Preappend wp prefix

define('kBNMExtendsDatabaseTableUsersFilename', '/Database/BNMExtendsUsers.sql');
define('kBNMExtendsDatabaseTableUsersName', 'bnmextends_users'); // Preappend wp prefix
define('kBNMExtendsDatabaseTableInvoices', 'bnmextends_invoices');

define('kBNMExtendsDashboardCapabilitiy', 'manage_options');

// ---------------------------------------------------------------------------------------------------------------------
// Post Types
// ---------------------------------------------------------------------------------------------------------------------
define('kBNMExtendsArtistPostTypeKey', 'bnm-artist');
define('kBNMExtendsEventPostTypeKey', 'bnm-event');
define('kBNMExtendsSystemPagePostTypeKey', 'bnm-system-page');

// ---------------------------------------------------------------------------------------------------------------------
// Event Post Types Meta
// ---------------------------------------------------------------------------------------------------------------------
define('kBNMExtendsEventMetaDateAndTime', 'bnm-event-date');
define('kBNMExtendsEventMetaArtistID', 'bnm-event-artist-id');

define('kBNMExtendsArtistPostTypeMenuItemPosition', 200);
define('kBNMExtendsEventPostTypeMenuItemPosition', 200);
define('kBNMExtendsSystemPagePostTypeMenuItemPosition', 200);

// ---------------------------------------------------------------------------------------------------------------------
// Summary Order
// ---------------------------------------------------------------------------------------------------------------------
define('kBNMExtendsSummaryOrderDiscountPrice', 5); // 5€ di sconto

// ---------------------------------------------------------------------------------------------------------------------
// Image and Thumbnail size
// ---------------------------------------------------------------------------------------------------------------------
define('kBNMExtendsThumbnailSizeSmallWidth', 55);
define('kBNMExtendsThumbnailSizeSmallHeight', 55);
define('kBNMExtendsThumbnailSizeSmallKey', 'thumbnail-small');

define('kBNMExtendsThumbnailSizeMediumWidth', 80);
define('kBNMExtendsThumbnailSizeMediumHeight', 80);
define('kBNMExtendsThumbnailSizeMediumKey', 'thumbnail-medium');

define('kBNMExtendsThumbnailSizeLargeWidth', 300);
define('kBNMExtendsThumbnailSizeLargeHeight', 300);
define('kBNMExtendsThumbnailSizeLargeKey', 'thumbnail-large');


// ---------------------------------------------------------------------------------------------------------------------
// Event Custom Fields
// ---------------------------------------------------------------------------------------------------------------------

// ---------------------------------------------------------------------------------------------------------------------
// Base64 images
// ---------------------------------------------------------------------------------------------------------------------
define('kBNMExtendsBase64AdminLogo', 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAydJREFUeNpUU3tIU3EU/u5j1kpnOOeurswyehDNMsQsCHqZEFFEQRERSBQ9iKKg/wqCiuifohdUREVFkVAR/pGLyDXKcLkaurJlPta0l5tza+bu7n6d370z6MB3ufee8333nO93LjoYw2HCAkI7gTE2/Xdy5HRnd2/HS++70WevWtPtn7p6hhOJq5Sr6qGaNQRkOcIOuogAThAKVHX/a3/gyPvOLyWdPWGUlRRBKZyElvcfUWyzoto5a7imYu6VtNl89A4wMpM4AuNqFL/jiQsPXC/2ut9+hNsXRNeXMPZsrcPJA9vx1NOKu43NyDHJWFE9DxtXLXVZrQUbiJbgH8doKrWPkx8+9+LWEw+R+wFV04VTKRXW/InYtakO7rZO3G704MHTF6ti8cQ5nucCZa3+wLFmbwCNbh+0TAYwSbw3CIKAeDIJWTZB09L63J6Wdrha/Hj19l09cZeJZNhuX+BTIVfXUlREJIBEWAbjSOjD515sOngKTa/9kERRzzfTmG2BIAajQ3vlvoGBdV2hb+jr/wlIgk40gumQZQk/fwzh/PVHwPgcCCQ6GBlGsHeAOANLxMhQbPqPwSGkaVaDxwwRHYbBunCOrD8z/p7G+U6c4XjCJtMpSJnMGEH4T0T4d8+MsZDNZzTyROM7I4r5ubnhwkm5kE00Hxf684eOhTAyAlVNGZaMdfNPDLARZ+IEc1QuLVFc0xxF9UqBBV/Jh/V1i7B22SJEYnFMsVuRUtNZX0TDFy2DPIsZM0rtmFxs98n5FstF56zyLZWzS81zyhQ0nD8OSZIxFo9dzUZnYtZc6qrGOQ8Vs8uh2GzXeGVbdWXFJV/Hh0MOxf4fmcevaJRIZDCtBjevcv5M1NY4sXhh5XPakwZuBIc5FA433bjfwLr7QoyHqqqszd/O5tZuZnBUM4tzNavdto+duXyNfe7uDlLJVM7V/4VREjcBuZHByFl3y5t6TcsI0VgMHq8fYdoBxVaA8lIFFXNmYGlVVZOtyLYrDvSMjv1MtELwE5YbB7UyFO7f2fs1vDiRTNolSRDz8/KiDkXxOoqLb/4C7gXJzVB21f4KMAA0cqqg+LYqDwAAAABJRU5ErkJggg==');


// ---------------------------------------------------------------------------------------------------------------------
// Views
// ---------------------------------------------------------------------------------------------------------------------
define('kBNMExtendspDashboardView', 'Views/DashboardView.php');


// ---------------------------------------------------------------------------------------------------------------------
// Services and system page - mail
// ---------------------------------------------------------------------------------------------------------------------
define( 'BNMEXTENDS_EMAIL_FROM', 'From: Blue Note Milano <info@bluenotemilano.com>' );
define( 'BNMEXTENDS_PRIMARY_EMAIL', 'tavoli@bluenotemilano.com, angelo.bufano@bluenotemilano.com' );
define( 'BNMEXTENDS_INVOICE_REQUEST_EMAIL', 'katia.trida@bluenotemilano.com,angelo.bufano@bluenotemilano.com' );

define( 'BNMEXTENDS_SERVICES_EMAIL', 'info@bluenotemilano.com' );

/* Tutte le mail inviate dal sito web vengono mandate in Bcc a */
//define( 'BNMEXTENDS_BCC_EMAIL_ADDRESSS', 'm.fantuzzi@saidmade.com' );
define( 'BNMEXTENDS_BCC_EMAIL_ADDRESSS', 'sitoweb@bluenotemilano.com' );

/* Stringhe che fungono anche da ID di controllo */
define( 'BNMEXTENDS_WITHOUT_DINNER_RESERVATION_KEY', 'Without Dinner Reservation' );
define( 'BNMEXTENDS_WITH_DINNER_RESERVATION_KEY', 'With Dinner Reservation' );

define( 'BNMEXTENDS_2_ADULTS_2_CHILDREN_KEY', '2 adults and 2 children' );
define( 'BNMEXTENDS_2_ADULTS_1_CHILD_KEY', '2 adults and 1 child' );
define( 'BNMEXTENDS_2_ADULTS_KEY', '2 adults' );

/* LOG ERRORS */
define( 'BNMEXTENDS_ENABLE_ERRORS_LOG_FILE', false );
define( 'BNMEXTENDS_ERRORS_LOG_FILE', __DIR__ . "/errors.log" );
