<?php
/**
 * @description        View controller per le impostazioni sull'aspetto
 *
 * @package            WPXtreme
 * @subpackage         WPXtremeSettingsViewController
 * @author             =undo= <g.fazioli@undolog.com>, <g.fazioli@wpxtre.me>
 * @copyright          Copyright (c) 2012 wpXtreme, Inc.
 * @link               http://wpxtre.me
 * @created            23/05/12
 * @version            1.0.0
 *
 */

class WPXtremeSettingsViewController {

    // -----------------------------------------------------------------------------------------------------------------
    // Static values
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * @return array
     */
    function tabs() {
        $tabs = array(
            'general'     => array(
                'title' => __( 'Enhanced', WPXTREME_TEXTDOMAIN ),
                'view'  => 'wpxtreme-settings-general-view'
            ),
            'maintenance' => array(
                'title' => __( 'Maintenance', WPXTREME_TEXTDOMAIN ),
                'view'  => 'wpxtreme-settings-maintenance-view'
            ),
        );
        return $tabs;
    }


    // -----------------------------------------------------------------------------------------------------------------
    // Display
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Display
     */
    function display() {
        ?>
    <div class="wrap">
        <div class="wpxm-icon-xtreme"></div>
        <h2><?php _e( 'Theme Settings', WPXTREME_TEXTDOMAIN ); ?></h2>

        <div class="wpdk-jquery-ui">
            <?php
            $tabs = new WPDKjQueryTabs( 'wpxtreme-settings' );
            foreach ( self::tabs() as $key => $tab ) {
                require( sprintf( 'views/%s.php', $tab['view'] ) );
                $class_name = sprintf( 'WPXtremeSettings%sView', ucfirst( $key ) );
                $view       = new $class_name;
                $tabs->add( $key, $tab['title'], $view->html() );
            }
            $tabs->display();
            ?>
        </div>
    </div>

    <?php
    }

}
