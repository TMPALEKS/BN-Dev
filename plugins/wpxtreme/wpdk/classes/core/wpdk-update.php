<?php
/**
 * @description        Manage update from wpXtreme plugin repository
 *
 * @package            WPDK (WordPress Development Kit)
 * @subpackage         core
 * @author             =undo= <g.fazioli@undolog.com>, <g.fazioli@wpxtre.me>
 * @copyright          Copyright (c) 2012 wpXtreme, Inc.
 * @link               http://wpxtre.me
 * @created            22/05/12
 * @version            1.0.0
 *
 * @filename           wpdk-update
 *
 */

class WPDKUpdate {

    private $_plugin_slug;
    private $_response;

    function __construct( $file ) {

        $this->_plugin_slug = plugin_basename( $file );

        /* Alternate checking repository */
        add_filter( 'pre_set_site_transient_update_plugins', array( $this, 'pre_set_site_transient_update_plugins' ) );

        /* Check For Plugin Information */
        add_filter( 'plugins_api', array( $this, 'plugins_api' ), 10, 3 );
        add_action( 'in_plugin_update_message-' . $this->_plugin_slug, array( $this, 'in_plugin_update_message'), 10, 2 );

    }

    /* Debug */
    function test() {
        _deprecated_function( __FUNCTION__, '0', 'delete, only for debug' );

        return $this->_plugin_slug;
    }

    // -----------------------------------------------------------------------------------------------------------------
    // WordPress hooks: own checking repositiry
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Questo hook filter non fa parte propriamente dell'engine di aggiornamento dei plugin. I filtri di questo tipo
     * fanno parte delle transient. Questo in particolare è costruito dal filtro 'pre_set_transient_' . $transient e
     * chiamato dalla set_transient(). In definitiva serve per alterare la lista dei plugin da aggiornare che WordPress
     * memorizza nelle option ( tramite appunto transient ) una volta al giorno.
     *
     * @note Per questo motivo è stato introdotto un delete_option( '_site_transient_update_plugins' ); in
     *       WPXtremeAPI::plugstore()
     *
     * @note Questa viene utilizzata singolarmente da ogni plugin installato. Vedi infatti il parametro 'plugin_name'
     *       negli $args che viene valorizzato con $this->_plugin_slug. Ricordo che la classe WPDKUpdate viene
     *       utilizzata come istanza in ogni bootstrap dei nostri plugin, che altrimenti non verrebbero mai aggiornati
     *       dallo store.
     *
     * @uses WPXtremeAPI::check_plugin_update()
     *
     * @param object $transient Elenco dei plugin da aggiornare:
     *
     * object(stdClass)#272 (3) {
     *   ["last_checked"]=>     int(1342125406)
     *   ["checked"]=>          array(7) {
     *     ["akismet/akismet.php"]=>        string(5) "2.5.6"
     *     ["members/members.php"]=>        string(3) "0.2"
     *     ["wpx-cleanfix/main.php"]=>      string(3) "1.0"
     *     ["wpx-sample/main.php"]=>        string(3) "1.0"
     *     ["wpx-smartshop/main.php"]=>     string(3) "1.0"
     *     ["wpxtreme/main.php"]=>          string(3) "1.0"
     *     ["wpxtreme-server/main.php"]=>   string(3) "1.0"
     *   }
     *   ["response"]=> array(0) { }
     * }
     *
     *
     * @return object
     *
     */
    public function pre_set_site_transient_update_plugins( $transient ) {

        /* Check if the transient contains the 'checked' information If no, just return its value without hacking it */
        if ( empty( $transient->checked ) ) {
            return $transient;
        }

        /* The transient contains the 'checked' information Now append to it information form your own API */
        $args = array(
            'action'      => 'update-check',
            'plugin_name' => $this->_plugin_slug,
            'version'     => $transient->checked[$this->_plugin_slug]
        );

        /* Send request checking for an update */
        $response = WPXtremeAPI::check_plugin_update( $args );

        /* If response is false, don't alter the transient */
        if ( false !== $response ) {
            $transient->response[$this->_plugin_slug] = $response;
        }

        return $transient;
    }

    /**
     * Hook filter dell'omonima funzione WordPress.
     *
     * @param bool   $false
     * @param string $action Identificativo del comando da eseguire, Eg. 'plugin_information'
     * @param array  $args   Arguments to serialize for the Plugin Info API.
     *
     * @uses WPXtremeAPI::plugin_information()
     *
     * @return bool|mixed
     */
    public function plugins_api( $false, $action, $args ) {

        $transient = get_site_transient( 'update_plugins' );

        /* Check if this plugins API is about this plugin */
        if ( $args->slug != $this->_plugin_slug ) {
            return $false;
        }

        /* POST data to send to your API */
        $args = array(
            'action'      => 'plugin_information',
            'plugin_name' => $this->_plugin_slug,
            'version'     => $transient->checked[$this->_plugin_slug]
        );

        /* Send request for detailed information */
        $response = WPXtremeAPI::plugin_information( $args );

        return $response;
    }

    /**
     * Questa action viene chiamata quando WordPress costruisce la riga sulla tabella della lista dei plugin e segnala
     * un aggiornamento. Vedi cmq la action orginale nella forma:
     *
     *     do_action( "in_plugin_update_message-$file", $plugin_data, $r );
     *
     * Questa contiene tutte le informazioni utili relative all'aggiornamento, compreso l'url dal quale scaricare lo
     * zip. Dove è $file è tipo "wpxtreme/main.php", questo è sempre lo slug del plugin; cartella/file pricipale.
     *
     * @param array  $plugin_data Queste sono le informazioni sul plugin che bisogna aggiornare. Sono del tutto simili
     *                            alle informazioni inserite come commento nel file principlae del plugin.
     *
     * array(12) {
     *   ["Name"]=>         string(9) "wpxSample"
     *   ["PluginURI"]=>    string(17) "http://wpxtre.me/"
     *   ["Version"]=>      string(3) "1.0"
     *   ["Description"]=>  string(13) "Sample Plugin"
     *   ["Author"]=>       string(8) "wpXtreme"
     *   ["AuthorURI"]=>    string(16) "http://wpxtre.me"
     *   ["TextDomain"]=>   string(0) ""
     *   ["DomainPath"]=>   string(0) ""
     *   ["Network"]=>      bool(false)
     *   ["Title"]=>        string(9) "wpxSample"
     *   ["AuthorName"]=>   string(8) "wpXtreme"
     *   ["update"]=>       bool(true)
     * }
     *
     * @param object $r Oggeto con le informazioni sulla versione e l'url del package da scaricare
     *
     * object(stdClass)#325 (4) {
     *   ["slug"]=>         string(19) "wpx-sample/main.php"
     *   ["new_version"]=>  string(3) "1.3"
     *   ["url"]=>          string(16) "http://wpxtre.me"
     *   ["package"]=>      string(111) "http://dev.wpxtre.me/api/download/wpxm-4fff289a3a478eccbc87e4b5ce2fe28308fd9f2a7baf3/?wpxpn=wpx-sample/main.php"
     * }
     *
     */
    public function in_plugin_update_message( $plugin_data, $r ) {
        $html_you_have_login = '';
        if ( empty( $r->package ) ) {
            $you_have_login = __( 'You have to login on order download this update.', WPDK_TEXTDOMAIN );

            $html_you_have_login = <<< HTML
<p>{$you_have_login}</p>
HTML;
        }

        $message = sprintf( __( 'There is an upgrade to %s release for %s. Remember that you can do this update from wpx Plugin Store too.', WPDK_TEXTDOMAIN ), $r->new_version, $plugin_data['Name'] );
        $html    = <<< HTML
    <p>{$message}</p>
    {$html_you_have_login}
HTML;
        echo $html;
    }

    // -----------------------------------------------------------------------------------------------------------------
    // Own checking repositiry
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Restituisce il numero dei plugin wpXtreme installati che devono essere aggiornati. Questa viene sicuramente usata
     * per mostrare il ballon sul menu principale.
     * Il conteggio viene svolto sui soli plugin wpXtreme. Per ottenere ciò lo standard impone che il nome del plugin
     * inizi sempre con wpx.
     * La transient 'update_plugins', quindi, contiene la lista dei plugin (gli slug) che devono essere aggiornati.
     * Questa contiene dunque tutti i plugin, anche quelli che non appartengono al wpx Plugin Store.
     *
     * @todo Per evitare di dover eseguire la substr() sulle prime lettere dello slug, si potrebbe prevdere una lista
     *       di tutti i plugin disponibili nel wpx Plugin Store e confrontare questa con la lista dei plugin da
     *       aggiornare. In alternativa, si potrebbe eseguire un controllo sulla sola lista dei plugin da aggiornare e
     *       verificare se appartengono (ad esempio controllando l'url) al wpx Plugin Store. Quest'ultima opzione
     *       potrebbe essere la migliore in quanto avere una lista di tutti i plugin dello store wpXtreme potrebbe
     *       risultare pesante nel tempo.
     *
     * @static
     * @return int Numero dei plugin (che iniziano con wpx) del wpx Plugin Store da aggiornare
     */
    public static function countUpdatingPlugins() {
        $count = 0;
        $transient = get_site_transient( 'update_plugins' );
        if ( !empty( $transient->response ) ) {
            foreach ( $transient->response as $plugin ) {
                if ( isset( $plugin->slug ) && substr( $plugin->slug, 0, 3 ) == 'wpx' ) {
                    $count++;
                }
            }
        }
        return $count;
    }
}