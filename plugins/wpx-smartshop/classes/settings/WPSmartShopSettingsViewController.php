<?php
/**
 * Impostazioni backend
 *
 * @package            wpx SmartShop
 * @subpackage         WPSmartShopSettingsViewController
 * @author             =undo= <g.fazioli@undolog.com>, <g.fazioli@wpxtre.me>
 * @copyright          Copyright (c)2012 wpXtreme, Inc.
 * @created            17/12/11
 * @version            1.0
 *
 */

class WPSmartShopSettingsViewController {

    // -----------------------------------------------------------------------------------------------------------------
    // Static values
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Restituisce un array speciale che indica quali schede e quali view mostrare nei settings.
     * La prima chiave è un id, dove poi seguono l'etichetta del tab e la classe (anche nome file) della view
     *
     * @package    wpx SmartShop
     * @subpackage WPSmartShopSettingsViewController
     * @since      1.0.0
     *
     * @retval array
     */
    function tabs() {
        $tabs = array(
            'generale'         => array(
                'title' => __( 'General', WPXSMARTSHOP_TEXTDOMAIN ),
                'view'  => 'SettingsGeneralView'
            ),
            'wp_integration'     => array(
                'title' => __( 'WordPress Integration', WPXSMARTSHOP_TEXTDOMAIN ),
                'view'  => 'SettingsIntegrationView'
            ),
            'products'         => array(
                'title' => __( 'Product', WPXSMARTSHOP_TEXTDOMAIN ),
                'view'  => 'SettingsProductView'
            ),
            'orders'            => array(
                'title' => __( 'Orders', WPXSMARTSHOP_TEXTDOMAIN ),
                'view'  => 'SettingsOrdersView'
            ),
            'shipments'        => array(
                'title' => __( 'Shipments', WPXSMARTSHOP_TEXTDOMAIN ),
                'view'  => 'SettingsShipmentsView'
            ),
        );
        return $tabs;
    }

    // -----------------------------------------------------------------------------------------------------------------
    // Display
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Emette l'html per visualizzare i tab dei settings, basandosi su un array
     *
     * @package    wpx SmartShop
     * @subpackage WPSmartShopSettingsViewController
     * @since      1.0.0
     *
     * @uses       tabs()
     *
     */
    function display() {
        $tabs = self::tabs(); ?>
<div class="wrap">
    <div id="icon-edit" class="icon32 icon32-posts-wpss-cpt-product"></div>
    <h2><?php _e( 'Settings', WPXSMARTSHOP_TEXTDOMAIN ); ?></h2>

    <div class="wpdk-jquery-ui">
        <?php
        $tabs = new WPDKjQueryTabs( 'wpss-settings' );
        foreach ( self::tabs() as $key => $tab ) {
            require( sprintf( 'views/%s.php', $tab['view'] ) );
            $view = new $tab['view'];
            $tabs->add( $key, $tab['title'], $view->html() );
        }
        $tabs->display();
        ?>
    </div>
</div>
    <?php
    }
}
