<?php
/**
 * Main WordPress Controller Static Class
 *
 * @package            WPDK (WordPress Development Kit)
 * @subpackage         WPDKWordPress
 * @author             =undo= <g.fazioli@undolog.com>, <g.fazioli@wpxtre.me>
 * @copyright          Copyright (C) 2010-2011 Saidmade Srl
 *
 * @deprecated Use WPDLWordPressPlugin instead
 *
 */

class WPDKWordPress {

    /**
     * @static
     * @deprecated
     */
    public static function init() {

        // WordPress compatibility
        if (!defined('WP_CONTENT_DIR')) {
            define('WP_CONTENT_DIR', ABSPATH . 'wp-content');
        }

        if (!defined('WP_CONTENT_URL')) {
            define('WP_CONTENT_URL', get_option('siteurl') . '/wp-content');
        }

        if (!defined('WP_ADMIN_URL')) {
            define('WP_ADMIN_URL', get_option('siteurl') . '/wp-admin');
        }

        if (!defined('WP_PLUGIN_DIR')) {
            define('WP_PLUGIN_DIR', WP_CONTENT_DIR . '/plugins');
        }

        if (!defined('WP_PLUGIN_URL')) {
            define('WP_PLUGIN_URL', WP_CONTENT_URL . '/plugins');
        }
    }

    /**
     * @static
     * @deprecated from 2.6
     */
    public static function definesBackwordConstats() {

        // WordPress compatibility
        if (!defined('WP_CONTENT_DIR')) {
            define('WP_CONTENT_DIR', ABSPATH . 'wp-content');
        }

        if (!defined('WP_CONTENT_URL')) {
            define('WP_CONTENT_URL', get_option('siteurl') . '/wp-content');
        }

        if (!defined('WP_ADMIN_URL')) {
            define('WP_ADMIN_URL', get_option('siteurl') . '/wp-admin');
        }

        if (!defined('WP_PLUGIN_DIR')) {
            define('WP_PLUGIN_DIR', WP_CONTENT_DIR . '/plugins');
        }

        if (!defined('WP_PLUGIN_URL')) {
            define('WP_PLUGIN_URL', WP_CONTENT_URL . '/plugins');
        }
    }

    // -----------------------------------------------------------------------------------------------------------------
    // Static values
    // -----------------------------------------------------------------------------------------------------------------

    public static function pluginURL($__FILE__) {
        return plugins_url('', $__FILE__);
    }

    public static function protocol() {
        return isset($_SERVER['HTTPS']) ? 'https://' : 'http://';
    }

    public static function ajaxURL() {
        return admin_url( 'admin-ajax.php', self::protocol() );
    }

    // -----------------------------------------------------------------------------------------------------------------
    // Static values
    // -----------------------------------------------------------------------------------------------------------------


    // -----------------------------------------------------------------------------------------------------------------
    // Commodity & utilities
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Restituisce una stringa casuale composta da caratteri alfa numerici di lunghezza arbitraria.
     *
     * @package    WordPress Development Kit
     * @subpackage WPDKWordPress
     * @since      1.0
     *
     * @static
     *
     * @param int    $len
     *   Lunghezza della stringa che si vuole ottenere, default 8
     *
     * @param string $extra
     *   Caratteri extra separati da virgola, default = '#,!,.'
     *
     * @return string
     *
     */
    public static function randomAlphaNumber($len = 8, $extra = '#,!,.') {
        $alfa = 'a,b,c,d,e,f,g,h,i,j,k,l,m,n,o,p,q,r,s,t,u,v,w,x,y,z,A,B,C,D,E,F,G,H,I,J,K,L,M,N,O,P,Q,R,S,T,U,V,W,X,Y,Z';
        $num  = '0,1,2,3,4,5,6,7,8,9';
        if ($extra != '') {
            $num .= ',' . $extra;
        }
        $alfa = explode(',', $alfa);
        $num  = explode(',', $num);
        shuffle($alfa);
        shuffle($num);
        $misc = array_merge($alfa, $num);
        shuffle($misc);
        $result = substr(implode('', $misc), 0, $len);

        return $result;
    }

    // -----------------------------------------------------------------------------------------------------------------
    // UI Helper
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Standard (WP Extreme) Layout dell'header
     *
     * @package    WordPress Development Kit
     * @subpackage WPDKWordPress
     * @since      1.0
     *
     * @static
     *
     * @param string $plugin_name    Plugin name
     * @param string $plugin_version Plugin version
     * @param bool   $echo           True for auto echo, false to html return
     *
     * @return string
     */
    public static function header( $plugin_name, $plugin_version, $echo = true ) {
        $more_info     = __( 'For more info and plugins visit', WPDK_TEXTDOMAIN );
        $title_version = sprintf( '%s ver. %s', $plugin_name, $plugin_version );

        $html = <<< HTML
<div class="wpxtreme_box">
	<p class="wpxtreme_info">
		{$more_info}
		<a href="http://wpxtre.me">wpXtreme</a>
    </p>
	<a class="wpxtreme_logo" href="http://wpxtre.me/">{$title_version}</a>
</div>
HTML;
        if ( $echo ) {
            echo $html;
        } else {
            return $html;
        }
    }

}