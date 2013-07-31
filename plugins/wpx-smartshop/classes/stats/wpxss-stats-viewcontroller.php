<?php
/**
 * @class              WPXSmartShopStatsViewController
 *
 * @description
 *
 * @package            wpx SmartShop
 * @subpackage         stats
 * @author             =undo= <g.fazioli@wpxtre.me>
 * @copyright          Copyright (c) 2012 wpXtreme, Inc.
 * @created            13/06/12
 * @version            1.0.0
 *
 */

class WPXSmartShopStatsViewController {

    /// Construct
    function __construct() {
    }

    // -----------------------------------------------------------------------------------------------------------------
    // Views
    // -----------------------------------------------------------------------------------------------------------------

    /// Display WordPress List Table
    /**
     * View per la visualizzazione della lista degli ordini
     *
     * @static
     *
     */
    public static function listTableView() {

        if ( !class_exists( 'WPXSmartShopStatsListTable' ) ) {
            require_once( 'wpxss-stats-listtable.php' );
        }
        /* Patch per eliminare il continuo e ricorsivo incremento della _wp_http_referer */
        $_SERVER['REQUEST_URI'] = remove_query_arg( array( '_wp_http_referer', '_wpnonce' ), stripslashes( $_SERVER['REQUEST_URI'] ) );

        $url = add_query_arg( array( 'action' => 'new', 'page' => $_REQUEST['page'] ), admin_url( 'admin.php' ) );
        ?>

    <div class="wrap wpss-stats wpdk-list-table-box">
        <div class="icon32 icon32-posts-wpss-cpt-product" id="icon-edit"></div>
        <h2><?php _e( 'Stats', WPXSMARTSHOP_TEXTDOMAIN ) ?>
            <?php if ( !isset( $_GET['action'] ) || $_GET['action'] != 'new' ) : ?>
                <a class="add-new-h2" href="<?php echo $url ?>"><?php _e( 'Add New', WPXSMARTSHOP_TEXTDOMAIN ) ?></a>
                <?php endif; ?>
        </h2>

        <?php
        $listTable = new WPXSmartShopStatsListTable();

        /* Fetch, prepare, sort, and filter our data... */
        if ( !$listTable->prepare_items() ) :
            $listTable->views();
            ?>

            <form id="order-filter" class="wpdk-list-table-form" method="get" action="">
                <!-- For plugins, we also need to ensure that the form posts back to our current page -->
                <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>"/>
                <!-- Now we can render the completed list table -->
                <?php //$listTable->search_box( __( 'Product name', WPXSMARTSHOP_TEXTDOMAIN ), 'wpss_stats_search_product_name' ); ?>
                <?php $listTable->display() ?>
            </form>
            <?php endif; ?>

        <?php
        if ( $listTable->count > 0 ) {
            echo WPXSmartShopStats::summary();
        }
        ?>

    </div>

    <?php
    }

    /// Display for printings
    public static function printing() {
        $charset = get_bloginfo( 'charset' );
        $title   = __( 'Print Stats', WPXSMARTSHOP_TEXTDOMAIN );
        $stylesheet = WPXSMARTSHOP_URL_CSS . 'print.css';

        if ( !class_exists( 'WPXSmartShopStatsListTable' ) ) {
            require_once( 'wpxss-stats-listtable.php' );
        }
        $listTable = new WPXSmartShopStatsListTable( true );
        if ( !$listTable->prepare_items() ) {
            ob_start();
            $listTable->display();
            if ( $listTable->count > 0 ) {
                echo WPXSmartShopStats::summary();
            }
            $content = ob_get_contents();
            ob_end_clean();
        }

        $html = <<< HTML
<!DOCTYPE html>
<head>
    <meta content="text/html; charset=$charset" http-equiv="Content-Type">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="robots" content="noindex, nofollow"/>
    <title>{$title}</title>
    <meta name="viewport" content="width=1024"/>
    <link media="all" rel="stylesheet" href="{$stylesheet}">
</head>
<body class="wpxss-print-orders">
{$content}
</body>
</html>
HTML;
        echo $html;
    }

}
