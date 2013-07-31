<?php
/**
 * @class              WPXSmartShopPointer
 * @description
 *
 * @package            wpx SmartShop
 * @subpackage         helper
 * @author             =undo= <g.fazioli@undolog.com>, <g.fazioli@wpxtre.me>
 * @copyright          Copyright (c) 2012 wpXtreme, Inc.
 * @link               http://wpxtre.me
 * @created            08/06/12
 * @version            1.0.0
 *
 * @filename           wpxss-pointer
 *
 */

class WPXSmartShopPointer extends WPDKPointer {

    /**
     * Init
     */
    function __construct() {
        parent::__construct();
    }

    // -----------------------------------------------------------------------------------------------------------------
    // Orders
    // -----------------------------------------------------------------------------------------------------------------

    function orders_welcome() {
        $title   = __( 'Welcome in SmartShop Orders', WPXSMARTSHOP_TEXTDOMAIN );
        $body    = __( 'Fron this view you can manage and display all your orders.', WPXSMARTSHOP_TEXTDOMAIN );
        $content = <<< HTML
<h3>{$title}</h3>
<p>{$body}</p>
HTML;

        $args = array(
            'content'  => $content,
            'position' => array(
                'edge'  => 'left',
                'align' => 'top'
            ),
        );

        $this->display( 'orders', '#toplevel_page_wpxss-main-menu', $args );
    }

}
