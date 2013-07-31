<?php
/**
 * Impostazioni backend
 *
 * @package            wpx SmartShop
 * @subpackage         WPSmartShopShowcaseViewController
 * @author             =undo= <g.fazioli@undolog.com>, <g.fazioli@wpxtre.me>
 * @copyright          Copyright (c) 2012 wpXtreme, Inc.
 * @link               http://wpxtre.me
 * @created            06/03/12
 * @version            1.0.0
 *
 */

class WPSmartShopShowcaseViewController {

    // -----------------------------------------------------------------------------------------------------------------
    // Static values
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Restituisce un array speciale che indica quali schede e quali view mostrare nei settings.
     * La prima chiave è un id, dove poi seguono l'etichetta del tab e la classe (anche nome file) della view
     *
     * @package    wpx SmartShop
     * @subpackage WPSmartShopShowcaseViewController
     * @since      1.0.0
     *
     * @retval array
     */
    function tabs() {
        $tabs = array(
            'showcase'              => array(
                'title' => __( 'Theme render layout', WPXSMARTSHOP_TEXTDOMAIN ),
                'view'  => 'SettingsShowcaseThemeView'
            ),
            'product_card'          => array(
                'title' => __( 'Product Card layout', WPXSMARTSHOP_TEXTDOMAIN ),
                'view'  => 'SettingsShowcaseProductCardView'
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
     * @subpackage WPSmartShopShowcaseViewController
     * @since      1.0.0
     *
     * @uses       tabs()
     *
     */
    function display() {
        $tabs = self::tabs(); ?>

    <div class="wrap">
        <div id="icon-edit" class="icon32 icon32-posts-wpss-cpt-product"></div>
        <h2><?php _e( 'Showcase', WPXSMARTSHOP_TEXTDOMAIN ); ?></h2>

        <div class="wpdk-jquery-ui">
            <?php
            $tabs = new WPDKjQueryTabs( 'wpss-showcase' );
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
