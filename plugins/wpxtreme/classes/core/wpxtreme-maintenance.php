<?php
/**
 * @description        Maintenace class
 *
 * @package            wpXtreme
 * @subpackage         WPXtremeMaintenance
 * @author             =undo= <g.fazioli@undolog.com>, <g.fazioli@wpxtre.me>
 * @copyright          Copyright (c) 2012 wpXtreme, Inc.
 * @link               http://wpxtre.me
 * @created            26/05/12
 * @version            1.0.0
 *
 * @filename           wpdk-maintenance
 *
 * @todo               Make documentation and Wiki
 * @todo               Add actions and filters
 * @todo               Se si è scelto lo scheduling scrivere un js per mostrare un count down alla riapertura
 *
 * @todo               Manca da fare l'autenticazione per utente secco, vedi wp_autenticate()
 * @todo               Migliorare gestione del 503 nel tema e pensare a proporre dei template già fatti
 *
 */

class WPXtremeMaintenance {

    static $settings;

    public static function init() {
        nocache_headers();

        /* Get settings for later. */
        self::$settings = WPXtreme::$settings->maintenance();

        if ( strstr( $_SERVER["REQUEST_URI"], 'api/' ) && in_array( $_SERVER["SERVER_ADDR"], self::$settings['ip_address'] ) ) {
            return;
        }

        if( self::isMaintenance() ) {

            /* Mi aggancio all'header del login se abilito blocco per IP. */
            add_action( 'login_head', array( __CLASS__, 'login_head' ), 1 );

            if ( !is_admin() && !in_array( $GLOBALS['pagenow'], array( 'wp-login.php' ) ) && !WPDKUser::hasCurrentUserRoles( self::$settings['user_roles']) ) {
                self::display503();
            } else{
                self::registerReminderForLoginAs();
            }
        }

    }

    // -----------------------------------------------------------------------------------------------------------------
    // is/has zone
    // -----------------------------------------------------------------------------------------------------------------

    public static function isMaintenance() {
        $enabled = false;

        /* Date range overwrite manual enabled. */
        $enabled = WPDKDateTime::isInRangeDatetime( self::$settings['date_start'], self::$settings['date_expire'], 'YmdHi' );

        if ( !$enabled ) {
            return wpdk_is_bool( self::$settings['enabled'] );
        }

        return $enabled;
    }

    /**
     * Mostra la 503 o un'altra delle scelte utente
     *
     * @static
     *
     */
    private static function display503() {

        switch( self::$settings['template'] ) {
            case 'wp_die':
                /* Standard WordPress maintenance */
                wp_die( self::$settings['note'], self::$settings['title'] );
                break;
            case 'theme-503':
                $path_to_load = sprintf( '%s%s', trailingslashit( TEMPLATEPATH ), '503.php' );
                include( $path_to_load );
                break;
        }
        exit();
    }


    /**
     * Messaggi per gli utenti loggati e permessi che ricordano che stiamo in maintenance mode
     *
     * @static
     *
     */
    public static function registerReminderForLoginAs() {
        if ( wpdk_is_bool( self::$settings['enabled_message_login'] ) ) {
            add_filter( 'login_message', array( __CLASS__, 'login_message' ) );
        }

        if ( wpdk_is_bool( self::$settings['enabled_message_admin'] ) ) {
        }
        add_filter( 'admin_notices', array( __CLASS__, 'admin_notice' ) );

        if ( wpdk_is_bool( self::$settings['enabled_message_footer'] ) ) {
            add_action( 'wp_footer', array( __CLASS__, 'wp_footer'  ) );
        }
    }

    // -----------------------------------------------------------------------------------------------------------------
    // WordPress hook
    // -----------------------------------------------------------------------------------------------------------------

    public static function login_head() {
        self::$settings = WPXtreme::$settings->maintenance();

        if ( wpdk_is_bool( self::$settings['disable_wp_login'] ) ) {
            if ( !in_array( $_SERVER['REMOTE_ADDR'], self::$settings['ip_address'] ) ) {
                wp_redirect( '/' );
            }
        }
    }

    /**
   	 * admin_notice function.
   	 *
   	 * @access public
   	 * @since 1.0.0
   	 * @return void
   	 */
   	public function admin_notice() {
           $message = self::$settings['message_admin'];
           $html = <<< HTML
    <div class="error">
        <p><strong>{$message}</strong></p>
    </div>
HTML;
        echo $html;
   	}

   	/**
   	 * wp_footer function.
   	 *
   	 * @access public
   	 * @since 1.0.0
   	 * @return void
   	 */
    public function wp_footer() {
        $message           = self::$settings['message_footer'];
        $admin_url_message = __( 'Go to WP Admin', WPXTREME_TEXTDOMAIN );
        $admin_url         = admin_url();
        $html              = <<< HTML
    <div style="position:fixed; bottom:0; width:100%; height:40px; background:red;">
        <p style="text-align:center; color:white; line-height:40px; font-size:18px;">
            {$message}
            <a href="{$admin_url}" style="color:white; text-decoration:underline;">{$admin_url_message}</a>
        </p>
    </div>
HTML;
    echo $html;

    }

   	/**
   	 * login_message function.
   	 *
   	 * @access public
   	 * @since 1.0.0
   	 * @return void
   	 */
    public function login_message() {
        $message = self::$settings['message_login'];
        $html    = <<< HTML
<div id="login_error">
    <p>{$message}</p>
</div>
HTML;
        return $html;
    }

}
