<?php
/**
 * @class              WPXtremeUsersSettingsViewController
 * @description        View controller per le impostazioni sugli utenti
 *
 * @package            WPXtreme
 * @subpackage         users
 * @author             =undo= <g.fazioli@wpxtre.me>
 * @copyright          Copyright (c) 2012 wpXtreme, Inc.
 * @created            15/05/12
 * @version            1.0.0
 *
 */


class WPXtremeUsersSettingsViewController {

    // -----------------------------------------------------------------------------------------------------------------
    // Static values
    // -----------------------------------------------------------------------------------------------------------------

    /// Get tabs view array
    function tabs() {
        $tabs = array(
            'security'     => array(
                'title' => __( 'Security', WPXTREME_TEXTDOMAIN ),
                'view'  => 'users-settings-security-view'
            ),
            'extrafields' => array(
                'title' => __( 'Extra Fields', WPXTREME_TEXTDOMAIN ),
                'view'  => 'users-settings-extrafields-view'
            ),
            'registration' => array(
                'title' => __( 'Registration', WPXTREME_TEXTDOMAIN ),
                'view'  => 'users-settings-registration-view'
            ),
        );
        return $tabs;
    }

    // -----------------------------------------------------------------------------------------------------------------
    // Display
    // -----------------------------------------------------------------------------------------------------------------

    /// Display tabs view
    function display() {
        ?>
    <div class="wrap">
        <div class="wpxm-icon-xtreme"></div>
        <h2><?php _e( 'Users Settings', WPXTREME_TEXTDOMAIN ); ?></h2>

        <div class="wpdk-jquery-ui">
            <?php
            $tabs = new WPDKjQueryTabs( 'wpxtreme-users-settings' );
            foreach ( self::tabs() as $key => $tab ) {
                require( sprintf( 'views/%s.php', $tab['view'] ) );
                $class_name = sprintf( 'UsersSettings%sView', ucfirst( $key ) );
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
