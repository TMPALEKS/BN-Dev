<?php
/**
 * Sample Payment Gateway, Folder name must be the same Class name
 *
 * @package            wpx SmartShop
 * @subpackage         SampleGateway
 * @author             =undo= <g.fazioli@undolog.com>, <g.fazioli@wpxtre.me>
 * @copyright          Copyright (c)2012 wpXtreme, Inc.
 * @created            29/11/11
 * @version            1.0
 *
 */

class SampleGateway extends WPSmartShopPaymentGatewayClass  {

    function __construct() {
        parent::__construct( __CLASS__, self::title(), self::version(), self::description() );
    }

    /**
     * Title of gateway
     *
     * @static
     * @retval string
     */
    function title() {
        return 'Sample Payment Gateway';
    }

    /**
     * Version
     *
     * @static
     * @retval string
     */
    function version() {
        return '1.0';
    }

    /**
     * More description
     *
     * @static
     * @retval string
     */
    function description() {
        return '';
    }


    /**
     * Queste sono le opzioni predefinite di questo plugin
     *
     * @static
     * @retval array
     */
    function defaultOptions() {
        $defaultOptions = array(
            'option' => 'value'
        );
        return $defaultOptions;
    }

    /**
     * Enabled/disabled this Payment Gateway
     *
     * @static
     * @retval bool
     */
    function enabled() {
        return true; // Change to false for disabled
    }

    /**
     * Visualizza la form con le impostazioni specifiche di questo plugin
     *
     * @static
     * @retval void
     */
    function settings() {
        $this->header();
    ?>
    <p>Your own setting, form and inputs field</p>
    <p>For disabled this plugin edit class file in:<br/><code><?php echo __FILE__ ?></code><br/>and set
        <code>enabled()</code> method to <code>false</code></p>
    <pre class="wpdk-monitor">
<span style="color:#bfb7dd">/**
 * Enabled/disabled this Payment Gateway
 *
 * @static
 * @retval bool
 */</span>
function <span style="color:#649fff">enabled</span><span style="color:#fff">() {</span>
  return true; <span style="color:#bfb7dd">// Change to false for disabled</span>
<span style="color:#fff">}</span>
<span class="wpdk-monitor-cursor">|</span></pre>
    <?php
    }

    /**
     * Esegue la transazione.
     * L'implementazione di questo metodo è diversa per ogni plugin di payment. Essa effettua concretamente la
     * connessione (chiamata tramite curl() o altro) al server della banca, cioè al sistema di e-commerce del cliente.
     * Anche se l'implementazione è diversa da gateway a gatway, i parametri d'ingresso obbligatori di questo metodo
     * sono uguali per tutti. Possono tuttavia seguire ulteriori parametri (dal terzo in poi), rintracciabili poi con
     * func_num_args() e func_get_arg()
     *
     * @static
     *
     * @param $trackID
     *   Questo è l'id della transazione. Tale ID dev'essere univoko per ogni transazione
     *
     * @param $amount
     *   Importo nel formato NNNNN.NN
     *
     */
    function transaction( $trackID, $amount ) {
        // TODO: Implement transaction() method.
    }

    function transactionResult() {
        // TODO: Implement transactionResult() method.
    }
}
