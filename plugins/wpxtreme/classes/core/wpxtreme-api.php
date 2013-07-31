<?php
/**
 * @description        Esegue le Request versio il server principlae wpXtreme
 *
 * @package            WP Xtreme
 * @subpackage         core
 * @author             =undo= <g.fazioli@undolog.com>, <g.fazioli@wpxtre.me>
 * @copyright          Copyright (c) 2012 wpXtreme, Inc.
 * @link               http://wpxtre.me
 * @created            09/05/12
 * @version            1.0.0
 *
 * @filename           wpxtreme-api
 *
 */

class WPXtremeAPI {

    // -----------------------------------------------------------------------------------------------------------------
    // Constants values
    // -----------------------------------------------------------------------------------------------------------------

    // -----------------------------------------------------------------------------------------------------------------
    // Static values
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Esegue una richiesta verso il server wpXtreme
     *
     * @static
     * @internal
     *
     * @note Create una serie di short-hand an alias che usano questo metodo
     *
     * @param array $args Parametri
     *
     * @return array|WP_Error
     */

    private static function request( $action, $args = array(), $method = 'POST' ) {

        if ( isset( $_POST['wpxm-logout'] ) ) {
            delete_transient( 'secure_key' );
        }

        if ( isset( $_POST['wpxm-signin'] ) ) {
            $args['wpxm-login-username'] = sanitize_email( $_POST['wpxm-login-username'] );
            $args['wpxm-login-password'] = esc_attr( $_POST['wpxm-login-password'] );
        }

        /* Se passo un singolo parametro ci penso io a renderlo array, gli d'ho una chiave arbitraria 'param' */
        if ( !is_array( $args ) ) {
            $args = array( 'param' => $args );
        }

        /* Get transient secure key. */
        $secure_key = get_transient( 'secure_key' );

        if( !empty( $secure_key) ) {
            $args['secure_key'] = $secure_key;
        }

        $params  = array(
            'method'      => $method,
            'timeout'     => WPXTREME_API_TIMEOUT,
            'redirection' => 5,
            'httpversion' => '1.0',
            'user-agent'  => WPXTREME_API_USER_AGENT,
            'blocking'    => true,
            'headers'     => array(),
            'cookies'     => array(),
            'body'        => $args,
            'compress'    => false,
            'decompress'  => true,
            'sslverify'   => true,
        );

        $gateway = WPXTREME_API_GATEWAY;
        if ( !empty( $action ) ) {
            $gateway = sprintf( '%s%s', WPXTREME_API_GATEWAY, $action );
        }

        $request = wp_remote_request( $gateway, $params );

        if ( is_wp_error( $request ) || wp_remote_retrieve_response_code( $request ) != 200 ) {
            return false;
        }

        return $request;
    }

    /**
     * Restituisce il contenuto di un risposta dal server
     *
     * @static
     *
     * @param array $request
     *
     * @return string Contenuto, stringa vuota se nessun contenuto
     */
    private static function content( $request ) {
        $response = '';
        $body     = wp_remote_retrieve_body( $request );
        if ( !empty( $body ) ) {
            $result = json_decode( $body );
            if ( is_object( $result ) && empty( $result->error ) ) {
                if ( !empty( $result->secure_key ) ) {
                    set_transient( 'secure_key', $result->secure_key, WPXTREME_SECURE_KEY_TIMEOUT );
                }
                return $result->content;
            }
        }
        return $response;
    }

    // -----------------------------------------------------------------------------------------------------------------
    // Shorthands
    // -----------------------------------------------------------------------------------------------------------------

    public static function isAlive() {
        $result = self::request( 'live');
        return self::content( $result );
    }

    public static function welcome() {
        $result = self::request( 'welcome');
        return self::content( $result );
    }

    public static function login() {
        /* Recupero le impostazioni */
        $settings = get_option( 'wpxtreme' );

        if ( $settings === false || empty( $settings['secure_key'] ) ) {
            /* Prima volta in assoluto o no login */
            $secure_key = 'joshua';
        } else {
            $secure_key = $settings['secure_key'];
        }

        $result = self::request( 'login', array( 'secure_key' => $secure_key ) );

        return self::content( $result );
    }

    public static function plugstore() {

        /* @todo Da eliminare, solo per debug */
        delete_option( '_site_transient_update_plugins' );

        /* Devo inviare la lista dei miei plugin */
        $all_plugins = get_plugins();
        $wpx_pluings = array();
        foreach ( $all_plugins as $key => $plugin ) {
            /* Prendo solo quelli che iniziano con wpx. */
            if ( substr( $key, 0, 3 ) == 'wpx' ) {
                $wpx_pluings[$key] = $plugin['Version'];
            }
        }

        if( empty( $wpx_pluings ) ) {
            return 'error';
        }

        $result = self::request( 'plugstore', array( 'plugins' => $wpx_pluings ) );

        return self::content( $result );
    }

    public static function plugstore_featured() {
        $result = self::request( 'plugstore_featured' );
        return self::content( $result );
    }

    public static function product( $id_product ) {
        $result = self::request( 'product', array( 'id_product' => $id_product ) );
        return self::content( $result );
    }

    public static function pluginUrl( $plugin_slug ) {
        $result = self::request( 'pluginUrl', array( 'plugin_slug' => $plugin_slug) );
        return self::content( $result );
    }

    /**
     * Utilizzata per alterare il transient di WordPress con la lista dei plugin da aggiornare. Notare che questa viene
     * chiamata in una specie di loop, cioè enne volte per gli enne plugin. Infatti, come si vede dal parametro $args,
     * quest'ultimo contiene 'plugin_name'; in pratica è come se ogni plugin chiedesse di controllare eventuali
     * aggiornamenti.
     *
     * @static
     *
     * @param array $args Elenco delle azioni e info, tipo:
     *
     * $args = array(
     *     'action'      => 'update-check',
     *     'plugin_name' => $this->_plugin_slug,
     *     'version'     => $transient->checked[$this->_plugin_slug]
     * );
     *
     * @see WPDKUpdate::pre_set_site_transient_update_plugins()
     *
     * @return bool|mixed
     */
    public static function check_plugin_update( $args ) {

        $request = self::request( 'check_plugin_update', $args );
        $body    = wp_remote_retrieve_body( $request );
        if ( !empty( $body ) ) {
            $response = unserialize( $body );

            if ( is_object( $response ) ) {
                return $response;
            }
        }
        return false;
    }

    /**
     * Questo viene utilizzato quando dalla lista dei plugin di WordPress esiste un aggiornamento e si chiedono i
     * dettagli della nuova versione.
     *
     * @static
     *
     * @param array $args
     *
     * @see WPDKUpdate::plugins_api()
     *
     * @return bool|mixed
     */
    public static function plugin_information( $args ) {
        $request = self::request( 'plugin_information', $args );
        $body    = wp_remote_retrieve_body( $request );
        if ( !empty( $body ) ) {
            $response = unserialize( $body );

            if ( is_object( $response ) ) {
                return $response;
            }
        }
        return false;
    }

}
