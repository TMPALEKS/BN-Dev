<?php
/**
 * @class              WPXSmartShopHelp
 *
 * @description        Tutto l'help viene concentrato in questa classe
 *
 * @package            wpx SmartShop
 * @subpackage         helper
 * @author             =undo= <g.fazioli@undolog.com>, <g.fazioli@wpxtre.me>
 * @copyright          Copyright (c) 2012 wpXtreme, Inc.
 * @link               http://wpxtre.me
 * @created            08/06/12
 * @version            1.0.0
 *
 * @filename           wpxss-help
 *
 */

class WPXSmartShopHelp {

    /**
     * Init
     */
    function __construct() {
    }

    // -----------------------------------------------------------------------------------------------------------------
    // Common
    // -----------------------------------------------------------------------------------------------------------------

    static function sidebar() {
        $html = '<p><strong>' . __('For more information:', WPXSMARTSHOP_TEXTDOMAIN) . '</strong></p>' .
      		'<p>' . __('<a href="http://wpxtre.me/" target="_blank">wpXtreme Web Site</a>', WPXSMARTSHOP_TEXTDOMAIN ) . '</p>' .
      		'<p>' . __('<a href="http://wpxtre.me/" target="_blank">Store</a>', WPXSMARTSHOP_TEXTDOMAIN ) . '</p>' .
      		'<p>' . __('<a href="http://wpxtre.me/" target="_blank">Projects on Github</a>', WPXSMARTSHOP_TEXTDOMAIN ) . '</p>' .
      		'<p>' . __('<a href="http://wpxtre.me/" target="_blank">WPDK Docs</a>', WPXSMARTSHOP_TEXTDOMAIN ) . '</p>' .
      		'<p>' . __('<a href="http://wpxtre.me/" target="_blank">Official Forum</a>', WPXSMARTSHOP_TEXTDOMAIN ) . '</p>' .
      		'<p>' . __('<a href="http://wpxtre.me/" target="_blank">Official Support</a>', WPXSMARTSHOP_TEXTDOMAIN ) . '</p>';
        return $html;


    }

    // -----------------------------------------------------------------------------------------------------------------
    // Orders
    // -----------------------------------------------------------------------------------------------------------------

    static function orders_what_is_a_order() {
        return array(
            'title'    => __('What\'s an order?', WPXSMARTSHOP_TEXTDOMAIN),
            'id'       => 'orders_what_is_a_order',
            'content'  => '<p>An order is a....</p>',
            'callback' => false
        );
    }

    static function orders_manage() {
        return array(
            'title'    => __('Manage', WPXSMARTSHOP_TEXTDOMAIN),
            'id'       => 'orders_manage',
            'content'  => '<p>From this view you can manage your orders.</p>',
            'callback' => false
        );
    }

}
