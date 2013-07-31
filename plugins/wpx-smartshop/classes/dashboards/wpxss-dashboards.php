<?php
/**
 * @class              WPXSmartShopDashboards
 * @description        Register and manage WordPress Dashboard
 *
 * @package            wpx SmartShop
 * @subpackage         dashboards
 * @author             =undo= <g.fazioli@wpxtre.me>
 * @copyright          Copyright (c) 2012 wpXtreme, Inc.
 * @created            16/04/12
 * @version            1.0.0
 *
 */

class WPXSmartShopDashboards {

    /// Register dashboards
    public static function dashboards() {
        $dashboards = array(
            'wpss-dashboard-summary-report' => array(
                'title'     => __( 'wpx SmartShop - Summary Report', WPXSMARTSHOP_TEXTDOMAIN ),
                'img'       => '<img src=" ' . kWPSmartShopBase64Logo16x16 . ' " /> ',
                'callback'  => array( __CLASS__, 'summaryReport' ),
            ),
        );
        return $dashboards;
    }

    /// Init
    /* @todo Could be an instance class */
    public static function init() {
        self::registerDashboards();
    }

    /// Loop for register registered dashboard
    public function registerDashboards() {
        foreach ( self::dashboards() as $key => $dashboard ) {
            wp_add_dashboard_widget( $key, $dashboard['img'] . $dashboard['title'], $dashboard['callback'] );
        }
    }

    /// Display dashboard
    /* @todo Rename in display */
    /* @todo Get the view from array self::dashboards */
    public static function summaryReport() {
        include_once( 'views/wpxss-dashboard-view.php' );
    }

}
