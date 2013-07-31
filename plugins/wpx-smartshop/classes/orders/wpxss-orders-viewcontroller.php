<?php
/**
 * @class              WPXSmartShopOrdersViewController
 *
 * @description        View Controller per gli ordini
 *
 * @package            wpx SmartShop
 * @subpackage         orders
 * @author             =undo= <g.fazioli@undolog.com>, <g.fazioli@wpxtre.me>
 * @copyright          Copyright (c)2012 wpXtreme, Inc.
 * @created            17/12/11
 * @version            1.0
 *
 * @filename           wpxss-orders-viewcontroller
 *
 */

class WPXSmartShopOrdersViewController {

    // -----------------------------------------------------------------------------------------------------------------
    // Views
    // -----------------------------------------------------------------------------------------------------------------

    /// Display list table
    /**
     * Visualizza la lista degli ordini
     *
     * @static
     */
    public static function listTableView() {
        if ( !class_exists( 'WPXSmartShopOrdersListTable' ) ) {
            require_once( 'wpxss-orders-listtable.php' );
        }

        /* Patch per eliminare il continuo e ricorsivo incremento della _wp_http_referer */
        $_SERVER['REQUEST_URI'] = remove_query_arg( array( '_wp_http_referer', '_wpnonce' ), stripslashes( $_SERVER['REQUEST_URI'] ) );

        $url = add_query_arg( array( 'action' => 'new', 'page' => $_REQUEST['page'] ), admin_url( 'admin.php' ) );
        ?>

    <div class="wrap wpss-orders wpdk-list-table-box">
        <div class="icon32 icon32-posts-wpss-cpt-product" id="icon-edit"></div>
        <h2><?php _e( 'Orders', WPXSMARTSHOP_TEXTDOMAIN ) ?>
            <?php if ( !isset( $_GET['action'] ) || $_GET['action'] != 'new' ) : ?>
                <a class="add-new-h2" href="<?php echo $url ?>"><?php _e( 'Add New', WPXSMARTSHOP_TEXTDOMAIN ) ?></a>
                <?php endif; ?>
        </h2>

        <?php
        $listTable = new WPXSmartShopOrdersListTable();

        /* Fetch, prepare, sort, and filter our data... */
        if ( !$listTable->prepare_items() ) :
            $listTable->views(); ?>

            <form id="order-filter" class="wpdk-list-table-form" method="get" action="">
                <!-- For plugins, we also need to ensure that the form posts back to our current page -->
                <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>"/>
                <!-- Now we can render the completed list table -->
                <?php $listTable->search_text_box(); ?>
                <?php $listTable->display() ?>
            </form>
            <?php endif; ?>

        <?php
        if ( $listTable->count > 0 ) {
            echo WPXSmartShopOrders::summary();
        }
        ?>
    </div>

    <?php
    }

    /// Printing
    /**
     * Costruisce una pagina vuota, con tanto di header, per effettuare la stampa di un ordine.
     *
     * @static
     *
     */
    public static function printing() {
        $charset = get_bloginfo( 'charset' );
        $title   = __( 'Print Orders', WPXSMARTSHOP_TEXTDOMAIN );
        $stylesheet = WPXSMARTSHOP_URL_CSS . 'print.css';

        if ( !class_exists( 'WPXSmartShopOrdersListTable' ) ) {
            require_once( 'wpxss-orders-listtable.php' );
        }
        $listTable = new WPXSmartShopOrdersListTable( true );
        if ( !$listTable->prepare_items() ) {
            ob_start();
            $listTable->display();
            if ( $listTable->count > 0 ) {
                echo WPXSmartShopOrders::summary();
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

    /// Edit an order
    /**
     * View per l'edit o l'insert
     *
     * @static
     *
     * @param null $id
     */
    public static function editView( $id = null ) {
        if ( is_null( $id ) ) {
            $title = __( 'New Order', WPXSMARTSHOP_TEXTDOMAIN );
        } else {

            /* Get all order info */
            $order = WPXSmartShopOrders::order( absint( $id ), ARRAY_A );
            $title = sprintf( __( 'Edit Order #%s', WPXSMARTSHOP_TEXTDOMAIN ), $order['track_id'] );
        }
        $url = remove_query_arg( array( 'action', 'id_order' ) );
        ?>
    <div class="metabox-holder">
        <div id="post-body">
            <div id="post-body-content">
                <div class="postbox">
                    <h3><span><?php echo $title ?></span></h3>

                    <div class="inside">
                        <form class="wpss-orders" name="" method="post" action="<?php echo $url ?>">
                            <?php if ( is_null( $id ) ) : ?>
                            <input name="action" value="insert" type="hidden"/>
                            <?php else : ?>
                            <input name="action" value="update" type="hidden"/>
                            <input name="id" value="<?php echo $id ?>" type="hidden"/>
                            <?php endif; ?>
                            <?php WPDKForm::nonceWithKey( 'orders' ) ?>
                            <?php WPDKForm::htmlForm( WPXSmartShopOrders::fields( $id ) ); ?>

                            <p>
                                <?php if ( is_null( $id ) ) : ?>
                                <input type="submit" class="button-primary"
                                       value="<?php _e( 'Add', WPXSMARTSHOP_TEXTDOMAIN ) ?>"/>
                                <?php else : ?>
                                <input type="submit" class="button-primary"
                                       value="<?php _e( 'Update', WPXSMARTSHOP_TEXTDOMAIN ) ?>"/>
                                <?php endif; ?>
                            </p>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php
    }

}