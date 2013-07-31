<?php
/**
 * @description        Classe base da estendere per il controllo di un plugin
 *
 * @package            WPDK
 * @subpackage         WPDKWordPressPlugin
 * @author             =undo= <g.fazioli@undolog.com>, <g.fazioli@wpxtre.me>
 * @copyright          Copyright (c) 2012 wpXtreme, Inc.
 * @link               http://wpxtre.me
 * @created            28/05/12
 * @version            1.0.0
 *
 * @filename           wpdl-wordpress-plugin
 *
 */

class WPDKWordPressPlugin {

    /**
     * @var string Version
     */
    var $version;

    /**
     * @var string Get from get_plugin_data(). This is the 'Plugin Name' param
     */
    var $name;

    /**
     * @var string Get from get_plugin_data(). This is the 'Text Domain' param
     */
    var $textdomain;

    /**
     * @var string Get from get_plugin_data(). This is the 'Domain Path' param
     */
    var $textdomain_path;

    /**
     * @var string Eg. wpx-smartshop/main.php
     */
    var $plugin_basename;

    /**
     * @var string Name o folder plugin with slash, Eg. wpx-smartshop/
     */
    var $folder_name;

    /**
     * @var string This is the strtolower() name with space replaced by '-'
     */
    var $slug;


    /**
     * @var string Unix path
     */
    var $path;

    /**
     * @var string Unix path of 'classes/' forlder
     */
    var $path_classes;

    /**
     * @var string Unix path of 'database/' forlder
     */
    var $path_database;

    /**
     * @var string Plugin url path
     */
    var $url;

    /**
     * @var string Plugin url path for folder 'assets/'
     */
    var $url_assets;

    /**
     * @var string Plugin url path for folder 'assets/css/'
     */
    var $url_css;

    /**
     * @var string Plugin url path for folder 'assets/css/images/'
     */
    var $url_images;
    /**
     * @var string Plugin url path for folder 'assets/js/'
     */
    var $url_javascript;

    /**
     * @var string Protocol http:// or https://
     * @see self::protocol() static method
     */
    var $protocol;

    /**
     * @var string Default WordPress admin Ajax url gateway
     * @see self::url_ajax() static method
     */
    var $url_ajax;

    /**
     * @var WPDKSettings
     */
    static $settings;

    private $update;

    /**
     * Init
     */
    function __construct( $file ) {

        /* Path unix. */
        $this->path          = trailingslashit( plugin_dir_path( $file ) );
        $this->path_classes  = $this->path . 'classes/';
        $this->path_database = $this->path . 'database/';

        /* URL/uri */
        $this->url            = trailingslashit( plugin_dir_url( $file ) );
        $this->url_assets     = $this->url . 'assets/';
        $this->url_css        = $this->url_assets . 'css/';
        $this->url_images     = $this->url_css . 'images/';
        $this->url_javascript = $this->url_assets . 'js/';

        /* Only folder name. */
        $this->folder_name = trailingslashit( basename( dirname( $file ) ) );

        /* WordPress slug plugin, Eg. wpx-smartshop/main.php */
        $this->plugin_basename = plugin_basename( $file );

        /* Use WordPress get_plugin_data() function for auto retrive plugin information. */
        if ( !function_exists( 'get_plugin_data' ) ) {
            require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
        }
        $result = get_plugin_data( $file, false );

        /* Sanitize properties. */
        $this->name            = $result['Name'];
        $this->version         = $result['Version'];
        $this->textdomain      = $result['TextDomain'];
        $this->textdomain_path = $this->folder_name . $result['DomainPath'];

        /* Built-in slug */
        $this->slug = sanitize_title( $this->name );

        /* Useful property. */
        $this->protocol = self::protocol();
        $this->url_ajax = self::url_ajax();

        /* My own alternative API check uri. */
        $this->update = new WPDKUpdate( $file );

        /* Standard hook actions and filters. */
        add_action( 'plugins_loaded', array( $this, 'plugins_loaded'), 1 );

        /* Activation & Deactivation Hook */
        register_activation_hook( $file , array( $this, 'activation' ));
        register_deactivation_hook( $file, array( $this, 'deactivation' ));

    }

    // -----------------------------------------------------------------------------------------------------------------
    // WordPress Hook
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Called when the plugin is loaded
     *
     * @return mixed
     */
    function plugins_loaded() {

        /* Good place for init options. */
        $this->init_options();

        /* Check Ajax. */
        if( wpdk_is_ajax() ) {
            $this->ajax();
            return;
        }

        /* Check admin backend. */
        if( is_admin() ) {
            $this->admin();
        } else {
            $this->theme();
        }

        /* Load the translation of the plugin. */
        load_plugin_textdomain( $this->textdomain, false, $this->textdomain_path );

    }

    // -----------------------------------------------------------------------------------------------------------------
    // Methods to overwrite
    // -----------------------------------------------------------------------------------------------------------------

    function ajax() {
        /* To overwrite. */
    }

    function admin() {
        /* To overwrite. */
    }

    /* @deprecated Use theme() instead */
    function frontend() {
        _deprecated_function( __CLASS__ . '::' . __FUNCTION__, '1.0', 'use theme' );
        /* To overwrite. */
        //$this->theme();
    }

    function theme() {
        /* To overwrite. */
    }

    function activation() {
        /* To overwrite. */
    }

    function deactivation() {
        /* To overwrite. */
    }

    function init_options() {
        /* To overwrite. */
    }

    // -----------------------------------------------------------------------------------------------------------------
    // Utility methods
    // -----------------------------------------------------------------------------------------------------------------

    public function reloadTextDomain() {
        load_plugin_textdomain( $this->textdomain, false, $this->textdomain_path );
    }

    // -----------------------------------------------------------------------------------------------------------------
    // Static methods
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Get WordPress protocol
     *
     * @static
     * @return string http:// or https://
     */
    public static function protocol() {
        return isset( $_SERVER['HTTPS'] ) ? 'https://' : 'http://';
    }

    /**
     * WordPress default ajax gateway url
     *
     * @static
     * @return string|void
     */
    public static function url_ajax() {
        return admin_url( 'admin-ajax.php', self::protocol() );
    }


}
