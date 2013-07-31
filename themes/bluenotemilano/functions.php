<?php
/**
 * Theme functions
 *
 * @package            Blue Note Milano
 * @subpackage         functions.php
 * @author             =undo= <g.fazioli@undolog.com>, <g.fazioli@saidmade.com>
 * @copyright          Copyright (C)2011 Saidmade Srl.
 * @created            07/12/11
 * @version            1.0
 *
 */

/**
 * Patch WordPress per gestire avanti e indietro nei CPT
 */
add_filter('index_rel_link', '__return_false');
add_filter('parent_post_rel_link', '__return_false');
add_filter('start_post_rel_link', '__return_false');
add_filter('previous_post_rel_link', '__return_false');
add_filter('next_post_rel_link', '__return_false');

/* Come esempio */
include_once('wp_smartshop_hooks.php');

/* Init script and style */
add_action('wp_head', function() {

    //wp_enqueue_style('bnm-style-theme', get_bloginfo('template_directory') . '/style.css', false, kBNMExtendsVersion, 'all');
    wp_enqueue_script( 'bnm-functions', get_bloginfo( 'template_directory' ) . '/js/functions.js', array( 'jquery' ), kBNMExtendsVersion, true );

	// -----------------------------------------------------------------------------------------------------------------
	// WPML Integration
	// Se inglese carica l'overide degli stili per alcuni aggiustamenti
	// -----------------------------------------------------------------------------------------------------------------
    if ( defined( 'ICL_LANGUAGE_CODE' ) ) {
        if ( ICL_LANGUAGE_CODE == 'en' ) {
            wp_enqueue_style( 'bnm-english', get_bloginfo( 'template_directory' ) . '/en.css', false, kBNMExtendsVersion, 'all' );
        }
    }

	// -----------------------------------------------------------------------------------------------------------------
	// Featured slider Home Page
	// -----------------------------------------------------------------------------------------------------------------
    if ( is_home() ) {
        wp_enqueue_script( 'bnm-bootstrap-slider', get_bloginfo( 'template_directory' ) . '/js/bootstrap-carousel.js', array( 'jquery' ), kBNMExtendsVersion, true );
        wp_enqueue_script( 'bnm-featured-home', get_bloginfo( 'template_directory' ) . '/js/featured-home.js', array( 'bnm-bootstrap-slider' ), kBNMExtendsVersion, true );
    }

    // -----------------------------------------------------------------------------------------------------------------
    // Gallery
    // -----------------------------------------------------------------------------------------------------------------
    if ( is_page( 'gallery' ) || wpdk_is_child( 'gallery' ) ) {
        wp_enqueue_script( 'thickbox' );
        wp_enqueue_style( 'thickbox' );
        wp_enqueue_script( 'bnm-gallery',
        get_bloginfo( 'template_directory' ) . '/js/gallery.js', array( 'thickbox' ), kBNMExtendsVersion, true );
    }

	// -----------------------------------------------------------------------------------------------------------------
	// Contatti
	// -----------------------------------------------------------------------------------------------------------------
	if (is_page('contatti') || is_page('contacts')) {
		$lang = is_page('contacts') ? 'en' : 'it';
		wp_enqueue_script('bnm-contatti-googlemap', 'http://maps.googleapis.com/maps/api/js?sensor=false&language=' . $lang, array('jquery'), kBNMExtendsVersion, true);
		wp_enqueue_script('bnm-contatti', get_bloginfo('template_directory') . '/js/contacts.js', array('jquery'), kBNMExtendsVersion, true);
	}

	// -----------------------------------------------------------------------------------------------------------------
	// Registrazione
	// -----------------------------------------------------------------------------------------------------------------
	if (is_page('registrazione') || is_page('register')) {
		wp_enqueue_script('bnm-registration', get_bloginfo('template_directory') . '/js/registration.js', array('jquery'), kBNMExtendsVersion, true);
        /* @todo Questa riga può essere eliminata se la localizzazione viene già caricata */
		wp_localize_script('bnm-registration', 'bnmExtendsJavascriptLocalization', BNMExtendsEventPostType::scriptLocalization());
	}

	// -----------------------------------------------------------------------------------------------------------------
	// Profile
	// -----------------------------------------------------------------------------------------------------------------
    if ( is_page( 'profilo' ) || is_page( 'profile' ) ) {
        wp_enqueue_script( 'bnm-profile', get_bloginfo( 'template_directory' ) . '/js/profile.js', array( 'jquery' ), kBNMExtendsVersion, true );
        /* @todo Questa riga può essere eliminata se la localizzazione viene già caricata */
        wp_localize_script( 'bnm-profile', 'bnmExtendsJavascriptLocalization', BNMExtendsEventPostType::scriptLocalization() );
    }

    // -----------------------------------------------------------------------------------------------------------------
	// WPXtreme Collapse accordion
	// -----------------------------------------------------------------------------------------------------------------

    /* Lista pagine a cui applicare l'accordion */
    $pages = array(
        'chi-siamo',
        'about-us',

        'per-le-aziende',
        'corporate',

        'membership',
        'offerta-commerciale',
        'rates-and-policies',
        'come-acquistare',
        'condizioni-generali-di-contratto',
        'privacy'
    );

    foreach ( $pages as $page ) {
        if ( is_page( $page ) ) {
            wp_enqueue_script( 'wpxt-accordion', get_bloginfo( 'template_directory' ) . '/js/wpxt-accordion.js', array( 'jquery' ), kBNMExtendsVersion, true );
            break;
        }
    }


});

/* Theme setup */
add_action('after_setup_theme', function () {

    //wp_enqueue_script( 'bnm-functions', get_bloginfo( 'template_directory' ) . '/js/functions.js', array( 'jquery' ), kBNMExtendsVersion, true );
    /* @todo Questa riga può essere eliminata se la localizzazione viene già caricata */
    //wp_localize_script( 'bnm-functions', 'bnmExtendsJavascriptLocalization', BNMExtendsEventPostType::scriptLocalization() );


	//		// Exclude pages from search results
	//		function exclude_post_content($where) {
	//			if (is_search()) {
	//				var_dump($where);
	//				$pattern = '/OR\s*\(\w+\.post_content\s+LIKE\s*(\'[^\']+\')\s*\)/';
	//				$where = preg_replace($pattern, "", $where);
	//			}
	//			return $where;
	//		}
	//
	//		add_filter('posts_where', 'exclude_post_content');
	//
	//		function search_filter($query) {
	//			if ($query->is_search) {
	//				$query->set('tag', 'prima-tag');
	//			}
	//			return $query;
	//		}
	//
	//		add_filter('pre_get_posts', 'search_filter');


	add_filter('wp_get_attachment_link', 'add_rel_attribute_to_attachment_link', 1, 2);
	function add_rel_attribute_to_attachment_link($anchor_tag, $image_id) {
		$image = get_post($image_id);
		$rel   = '';
		if (isset($image->post_parent)) {
			$rel = ' rel="attached-to-' . (int)$image->post_parent . '"';
		}
		if (!empty($rel)) {
			$anchor_tag = str_replace('<a', '<a' . $rel, $anchor_tag);
		}
		return $anchor_tag;
	}


	// This theme styles the visual editor with editor-style.css to match the theme style.
	//add_editor_style();

	// Load jQuery latest version
	if (!function_exists('core_mods')) {
		function core_mods() {
			if (!is_admin()) {
				$deps = array(
					'jquery',
					'jquery-ui-core',
					'jquery-ui-slider',
					'jquery-ui-datepicker',
					'jquery-ui-autocomplete'
				);

				wp_enqueue_script('jquery-ui-timepicker', get_bloginfo('template_directory') . '/js/jquery.timepicker.js', $deps, 1, true);
				wp_enqueue_style('datepickerStyle', get_bloginfo('template_directory') . '/css/smoothness/jquery-ui-smoothness.css');
			}
		}

		core_mods();
	}

	// Clean up the <head>
	function removeHeadLinks() {
		remove_action('wp_head', 'rsd_link');
		remove_action('wp_head', 'wlwmanifest_link');
	}

    add_action( 'init', 'removeHeadLinks' );
    add_action( 'init', function() {
        load_theme_textdomain( 'bnm', TEMPLATEPATH );
    } );

	// Custom Login Logo
	function customLoginLogo() {
		?>
	<style type="text/css">
		body, html, .wp-dialog {
			background: #004489 !important
		}

		#nav, #backtoblog {
			text-shadow: none;
			float: left
		}

		.login #nav a, .login #backtoblog a {
			color: #eee !important
		}

		#backtoblog {
			float: right
		}

		h1 a {
			background-image: url(<?php echo get_bloginfo('template_directory') ?>/images/login-logo.png) !important;
		}
	</style>
	<?php
	}

    add_action( 'login_head', 'customLoginLogo' );

    function login_headerurl( $url ) {
        return get_bloginfo('url');
    }
    add_filter( 'login_headerurl', 'login_headerurl' );

    function login_headertitle( $description ) {
        return get_bloginfo('description');
    }
    add_filter( 'login_headertitle', 'login_headertitle' );

    function login_message( $description ) {
        return '';
    }
    add_filter( 'login_message', 'login_message' );


	// Add default posts and comments RSS feed links to <head>.
	add_theme_support('automatic-feed-links');

	// This theme uses wp_nav_menu() in one location.
	register_nav_menu('primary', 'Main Menu');
	register_nav_menu('subFooterMenu', 'Sub Footer Menu');

	// Add support for a variety of post formats
	add_theme_support('post-formats', array(
		'aside',
		'gallery',
		'link',
		'image',
		'quote',
		'status',
		'audio',
		'chat',
		'video'
	)); // Add 3.1 post format theme support.


	// This theme uses Featured Images (also known as post thumbnails) for per-post/per-page Custom Header images
	add_theme_support( 'post-thumbnails'  );

	if (function_exists('register_sidebar')) {
		register_sidebar(array(
			'name'          => 'Home Page',
			'id'            => 'sidebar-home-page',
			'description'   => 'Sidebar per la Home Page',
			'before_widget' => '<div id="%1$s" class="widget %2$s">',
			'after_widget'  => '</div>',
			'before_title'  => '<h2>',
			'after_title'   => '</h2>'
		));

		register_sidebar(array(
			'name'          => 'Single Post',
			'id'            => 'sidebar-widgets',
			'description'   => 'Sidebar per articolo blog',
			'before_widget' => '<div id="%1$s" class="widget %2$s">',
			'after_widget'  => '</div>',
			'before_title'  => '<h2>',
			'after_title'   => '</h2>'
		));

		register_sidebar(array(
			'name'          => 'Single Event',
			'id'            => 'sidebar-event',
			'description'   => 'Sidebar per pagina singolo Evento',
			'before_widget' => '<div id="%1$s" class="widget %2$s">',
			'after_widget'  => '</div>',
			'before_title'  => '<h2>',
			'after_title'   => '</h2>'
		));

		register_sidebar(array(
			'name'          => 'Single Product',
			'id'            => 'sidebar-product',
			'description'   => 'Sidebar per pagina singolo Prodotto',
			'before_widget' => '<div id="%1$s" class="widget %2$s">',
			'after_widget'  => '</div>',
			'before_title'  => '<h2>',
			'after_title'   => '</h2>'
		));

		register_sidebar(array(
			'name'          => 'Single Page',
			'id'            => 'sidebar-pages',
			'description'   => 'Sidebar per singola pagina',
			'before_widget' => '<div id="%1$s" class="widget %2$s">',
			'after_widget'  => '</div>',
			'before_title'  => '<h2>',
			'after_title'   => '</h2>'
		));

		register_sidebar(array(
			'name'          => 'Single Page Blue Note Milano',
			'id'            => 'sidebar-page-blue-note-milano',
			'description'   => 'Sidebar per pagina Blue Note Milano',
			'before_widget' => '<div id="%1$s" class="widget %2$s">',
			'after_widget'  => '</div>',
			'before_title'  => '<h2>',
			'after_title'   => '</h2>'
		));

		register_sidebar(array(
			'name'          => 'Single Page Ristorante',
			'id'            => 'sidebar-page-ristorante',
			'description'   => 'Sidebar per pagina Ristorante',
			'before_widget' => '<div id="%1$s" class="widget %2$s">',
			'after_widget'  => '</div>',
			'before_title'  => '<h2>',
			'after_title'   => '</h2>'
		));

		register_sidebar(array(
			'name'          => 'Single Page Club',
			'id'            => 'sidebar-page-club',
			'description'   => 'Sidebar per pagina Club',
			'before_widget' => '<div id="%1$s" class="widget %2$s">',
			'after_widget'  => '</div>',
			'before_title'  => '<h2>',
			'after_title'   => '</h2>'
		));

		register_sidebar(array(
			'name'          => 'Single Page Store',
			'id'            => 'sidebar-page-store',
			'description'   => 'Sidebar per pagina Store',
			'before_widget' => '<div id="%1$s" class="widget %2$s">',
			'after_widget'  => '</div>',
			'before_title'  => '<h2>',
			'after_title'   => '</h2>'
		));

		register_sidebar(array(
			'name'          => 'Single Page Gallery',
			'id'            => 'sidebar-page-gallery',
			'description'   => 'Sidebar per pagina Gallery',
			'before_widget' => '<div id="%1$s" class="widget %2$s">',
			'after_widget'  => '</div>',
			'before_title'  => '<h2>',
			'after_title'   => '</h2>'
		));

		register_sidebar(array(
			'name'          => 'Single Page Aziende',
			'id'            => 'sidebar-page-aziende',
			'description'   => 'Sidebar per pagina Aziende',
			'before_widget' => '<div id="%1$s" class="widget %2$s">',
			'after_widget'  => '</div>',
			'before_title'  => '<h2>',
			'after_title'   => '</h2>'
		));

		register_sidebar(array(
			'name'          => 'Single Page Contatti',
			'id'            => 'sidebar-page-contatti',
			'description'   => 'Sidebar per pagina Store',
			'before_widget' => '<div id="%1$s" class="widget %2$s">',
			'after_widget'  => '</div>',
			'before_title'  => '<h2>',
			'after_title'   => '</h2>'
		));

		register_sidebar(array(
			'name'          => 'Category',
			'id'            => 'sidebar-archives',
			'description'   => 'Sidebar per gli archivi',
			'before_widget' => '<div id="%1$s" class="widget %2$s">',
			'after_widget'  => '</div>',
			'before_title'  => '<h2>',
			'after_title'   => '</h2>'
		));

		register_sidebar(array(
			'name'          => 'Footer',
			'id'            => 'sidebar-footer',
			'description'   => 'Sidebar per il footer',
			'before_widget' => '<div id="%1$s" class="widget %2$s">',
			'after_widget'  => '</div>',
			'before_title'  => '<h2>',
			'after_title'   => '</h2>'
		));

		register_sidebar(array(
			'name'          => 'Search results',
			'id'            => 'sidebar-search',
			'description'   => 'Sidebar per i risultati della ricerca',
			'before_widget' => '<div id="%1$s" class="widget %2$s">',
			'after_widget'  => '</div>',
			'before_title'  => '<h2>',
			'after_title'   => '</h2>'
		));

		register_sidebar(array(
			'name'          => 'Register',
			'id'            => 'sidebar-register',
			'description'   => 'Sidebar visibile durante la fase di Registrazione Utente',
			'before_widget' => '<div id="%1$s" class="widget %2$s">',
			'after_widget'  => '</div>',
			'before_title'  => '<h2>',
			'after_title'   => '</h2>'
		));

	}
});

/* Excerpt */
add_filter( 'excerpt_length', function( $length ) {
    return 30;
} );

/* Patch Post Views Widget title */
add_filter( 'widget_title', function( $title ) {
    $foo = __( 'Most read', 'bnm' );
    return __( $title, 'bnm' );
} );

/**
 * WPML Custom Language selector
 */

function language_selector_flags( $text = true ) {
    global $wp;

    if ( !function_exists( 'icl_get_languages' ) ) {
        return;
    }

    /* Get language */
    $languages = icl_get_languages( 'skip_missing=1&orderby=code' );

    /* Testo o immagine */
    if ( $text ) {
        $strings = array(
            'it'  => 'Italiano',
            'en'  => 'English'
        );
        if ( !empty( $languages ) ) {
            foreach ( $languages as $l ) {
                if ( !$l['active'] ) {
                    $url = $l['url'];
                    if ( $locale = WPXSmartShopPermalink::localization( $l['language_code'] ) ) {
                        $url .= $locale[1];
                    }
                    /* @todo Applico patch per i tipi prodotto */
                    if( $l['language_code'] == 'it' ) {
                        $url = str_replace( 'product-types', 'tipi-prodotto', $url );
                    } else {
                        $url = str_replace( 'tipi-prodotto', 'product-types', $url );
                    }
                    echo '<a href="' . $url . '">';
                    echo $strings[$l['language_code']];
                    echo '</a>';
                }
            }
        }
    } else {
        $flags = array(
            'it'  => get_bloginfo( 'template_directory' ) . '/images/it.png',
            'en'  => get_bloginfo( 'template_directory' ) . '/images/en.png'
        );

        if ( !empty( $languages ) ) {
            foreach ( $languages as $l ) {
                if ( !$l['active'] ) {
                    echo '<a href="' . $l['url'] . '">';
                    echo'<img src="' . $flags[$l['language_code']] . '" height="48" alt="' . $l['language_code'] .
                        '" width="48" />';
                    echo '</a>';
                }
            }
        }
    }
}

/**
 * WPSS Filters
 */

add_filter( 'wpss_cart_widget_title', function( $widget_title ) {
    $widget_title = __( 'Shopping Cart', 'bnm' );
    return $widget_title;
} );

/**
 * Social
 */
function bnm_social() {
    if ( function_exists( 'the_social_widgets' ) ) {
        the_social_widgets();
    }
}

/*
 function testCron( $order_id ) {

     $fewSeconds = time() + 20; // (30 * 24 * 60 * 60)

     WPDKWatchDog::watchDog("Cron starting at " . $fewSeconds);
     $args = array('order_id' => "123" );


     wp_schedule_single_event($fewSeconds, 'bnmextends_orders_status', $args);

}
*/
function printCron($id){
    WPDKWatchDog::watchDog("Cron fired and remove ID: " . $id);
}

function testCron($id = 434){
    var_dump(BNMExtendsOrders::updateOrderWithStatus( $id ));
}
//add_action( 'admin_head',array('BNMExtendsOrders','updateOrderStatus') );
//add_action( 'get_header', 'testCron' );
//add_action( 'bnmextends_orders_status', 'printCron' );