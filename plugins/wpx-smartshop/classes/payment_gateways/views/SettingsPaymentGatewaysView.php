<?php
/**
 * Impostazioni dei gateway di pagamento
 *
 * @package            wpx SmartShop
 * @subpackage         SettingsPaymentGatewaysView
 * @author             =undo= <g.fazioli@undolog.com>, <g.fazioli@wpxtre.me>
 * @copyright          Copyright (c)2012 wpXtreme, Inc.
 * @created            29/11/11
 * @version            1.0
 *
 */

class SettingsPaymentGatewaysView extends WPDKSettingsView  {
    

    // -----------------------------------------------------------------------------------------------------------------
    // Init
    // -----------------------------------------------------------------------------------------------------------------

    function __construct() {
        $this->key          = 'payment_gateways';
        $this->title        = __( 'Payment Gateways', WPXSMARTSHOP_TEXTDOMAIN );
        $this->introduction = __( 'Please, write an introduction', WPXSMARTSHOP_TEXTDOMAIN );
        $this->settings     = WPXSmartShop::settings();
    }


    function fields() {

        $values =  WPXSmartShop::settings()->payment_gateways();

        $fields = array(
            __( 'Available Payment Gateways', WPXSMARTSHOP_TEXTDOMAIN ) => array(
                __( 'Enable the appropriate Payment Gateways', WPXSMARTSHOP_TEXTDOMAIN ),
                self::arrayGatewayPluginForSDF(),
            ),
            __( 'Display', WPXSMARTSHOP_TEXTDOMAIN )                    => array(
                __( 'Choose display mode', WPXSMARTSHOP_TEXTDOMAIN ),
                array(
                    array(
                        'type'    => WPDK_FORM_FIELD_TYPE_RADIO,
                        'name'    => 'display_mode',
                        'label'   => __( 'Combo Menu', WPXSMARTSHOP_TEXTDOMAIN ),
                        'value'   => 'combo-menu',
                        'checked' => $values ? $values['display_mode'] : ''
                    )
                ),
                array(
                    array(
                        'type'    => WPDK_FORM_FIELD_TYPE_RADIO,
                        'name'    => 'display_mode',
                        'label'   => __( 'Radio button with image', WPXSMARTSHOP_TEXTDOMAIN ),
                        'value'   => 'radio-button',
                        'checked' => $values ? $values['display_mode'] : ''
                    )
                )
            )
        );

        return $fields;
    }

    // -----------------------------------------------------------------------------------------------------------------
    // Display
    // -----------------------------------------------------------------------------------------------------------------

    function display() {

        /**
         * Crea una serie di Tab per accedere alle impostazioni dei vari plugin-gateway per i pagamenti. Il primo e ultimo
         * tab sono riservati. Il primo speiga dove siamo mentre l'ultimo permette di accedere alle info per acquistare o
         * scrivere altri gateway di pagamento.
         */
        
        /**
         * Esegue un dir ricordiva delle sole cartelle. Lo standard è il seguente:
         *
         * - Ogni Plugin deve avere una cartella
         * - Il nome della classe dev'essere uguale al nome della cartella
         * - La classe deve avere una serie di metodi statici per fornisco qui sotto le informazioni sulla classe stessa
         *   come il titolo, versione o descrizione. Vedi WPSmartShopPaymentGateway.php per dettagli
         *
         */
        $root = WPSmartShopPaymentGateway::paymentGatewayPath();
        if ( $objDir = opendir( $root ) ) {
            while ( ( $item = readdir( $objDir ) ) !== false ) {
                if ( is_dir( $root . $item ) && $item != "." && $item != ".." ) {
                    $classFilename = $item . '/' . $item . '.php';
                    include_once( $root . $classFilename );
                    if ( class_exists( $item ) ) {
                        if ( $item::enabled() ) {
                            $tabs[$item] = array(
                                'label'         => $item::title(),
                                'className'     => $item,
                                'classFilename' => $classFilename
                            );
                        }
                    }
                }
            }
            closedir( $objDir );
        } ?>
<div class="wrap">
    <div id="icon-edit" class="icon32 icon32-posts-wpss-cpt-product"></div>
    <h2><?php _e( 'Payment Gateways', WPXSMARTSHOP_TEXTDOMAIN ); ?></h2>
    <div class="wpdk-border-container wpdk-jquery-ui">
        <div id="wpss-payment-gateway" class="wpdk-tabs">
            <ul>
                <li><a href="#introductionPaymentGateway"><?php _e('Introduction', WPXSMARTSHOP_TEXTDOMAIN ) ?></a></li>

                <?php foreach ($tabs as $key => $tab) : ?>
                <li><a href="#<?php echo $key ?>"><?php echo $tab['label'] ?></a></li>
                <?php endforeach; ?>

            </ul>

            <div id="introductionPaymentGateway">
                <?php  if ( WPDKForm::isNonceVerify( $this->key ) ) {
                            parent::update();
                        } ?>
                <form class="wpdk-form" action="" method="post">
                    <?php
                    WPDKForm::nonceWithKey( $this->key );
                    WPDKForm::htmlForm( self::fields() ) ?>

                    <p>
                        <input type="submit" class="button-primary" value="<?php _e( 'Update', WPXSMARTSHOP_TEXTDOMAIN ) ?>"/>
                        <input type="submit"
                               class="button-secondary alignright"
                               name="resetToDefault"
                               value="<?php _e( 'Reset to default', WPXSMARTSHOP_TEXTDOMAIN ) ?>"/>
                    </p>

                </form>
            </div>

            <?php foreach ($tabs as $key => $tab) : ?>
            <div id="<?php echo $key ?>">
                <?php
                if ( class_exists( $tab['className'] ) ) {
                    $gateway = new $tab['className'];
                    $gateway->settings();
                }
                ?>
            </div>
            <?php endforeach; ?>

        </div>
    </div>
</div>
        <?php
    }

    /**
     * Restituisce l'array in formato SDF per visualizzare i checkbox con l'elenco dei payment gateway da abilitare
     * per la visualizzazione in frontend
     *
     * @static
     * @retval array Checkbox array in formato SDF
     */
    function arrayGatewayPluginForSDF() {
        $gatewaysInOptions = WPXSmartShop::settings()->payment_gateways_enabled();
        $gateways          = WPSmartShopPaymentGateway::listPaymentGateways();
        $result            = array();


        foreach ( $gateways as $primaryKey => $gateway ) {
            $checked = '';
            foreach ( $gatewaysInOptions as $key => $gatewayOption ) {
                if ( $key == $primaryKey ) {
                    $checked = $primaryKey;
                    break;
                }
            }
            /* Ognuno su una riga diversa, quindi doppio array */
            $result[][] = array(
                'type'    => WPDK_FORM_FIELD_TYPE_CHECKBOX,
                'name'    => 'payment_gateways_enabled[]',
                'label'   => $gateway['label'],
                'value'   => $primaryKey,
                'checked' => $checked,
                'before'  => $gateway['thumbnail']
            );

        }
        return $result;
    }

    // -----------------------------------------------------------------------------------------------------------------
    // Settings actions
    // -----------------------------------------------------------------------------------------------------------------

    /**
     *
     */
    function save() {

        $gateways = WPSmartShopPaymentGateway::listPaymentGateways();

        $newGateways = array();
        foreach ( $gateways as $key => $gateway ) {
            if ( isset( $_POST['payment_gateways_enabled'] ) && in_array( $key, $_POST['payment_gateways_enabled'] ) ) {
                $newGateways[$key] = $gateway;
            }
        }

        $values = array(
            'list_enabled'  => $newGateways,
            'display_mode'  => isset( $_POST['display_mode'] ) ? $_POST['display_mode'] : ''
        );

        WPXSmartShop::settings()->payment_gateways( $values );
    }


}