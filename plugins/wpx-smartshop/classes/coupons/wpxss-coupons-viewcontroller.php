<?php
/**
 * @class              WPXSmartShopCouponsViewController
 *
 * @description        View Controller per i Coupons
 *
 * @package            wpx SmartShop
 * @subpackage         coupons
 * @author             =undo= <g.fazioli@undolog.com>, <g.fazioli@wpxtre.me>
 * @copyright          Copyright (c)2012 wpXtreme, Inc.
 * @created            30/12/11
 * @version            1.0
 *
 * @filename           wpxss-coupons-viewcontroller
 *
 */

class WPXSmartShopCouponsViewController {

    // -----------------------------------------------------------------------------------------------------------------
    // Views
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * View per la visualizzazione della lista dei coupon
     *
     * @static
     */
    public static function listTableView() {
        if ( !class_exists( 'WPXSmartShopCouponsListTable' ) ) {
            require_once( 'wpxss-coupons-listtable.php' );
        }
        /* Patch per eliminare il continuo e ricorsivo incremento della _wp_http_referer */
        $_SERVER['REQUEST_URI'] = remove_query_arg( array( '_wp_http_referer', '_wpnonce' ), stripslashes( $_SERVER['REQUEST_URI'] ) );

        $url = add_query_arg( array( 'action' => 'new', 'page' => $_REQUEST['page'] ), admin_url( 'admin.php' ) );
        ?>

    <div class="wrap wpss-coupon wpdk-list-table-box">
        <div class="icon32 icon32-posts-wpss-cpt-product" id="icon-edit"></div>
        <h2><?php _e( 'Coupons', WPXSMARTSHOP_TEXTDOMAIN ) ?>
            <?php if ( !isset( $_GET['action'] ) || $_GET['action'] != 'new' ) : ?>
                <a class="add-new-h2" href="<?php echo $url ?>"><?php _e( 'Add New', WPXSMARTSHOP_TEXTDOMAIN ) ?></a>
                <?php endif; ?>
        </h2>

        <?php
        $listTable = new WPXSmartShopCouponsListTable();

        /* Fetch, prepare, sort, and filter our data... */
        if ( !$listTable->prepare_items() ) :
            $listTable->views(); ?>

            <form id="coupon-filter" class="wpdk-list-table-form" method="get" action="">
                <!-- For plugins, we also need to ensure that the form posts back to our current page -->
                <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>"/>
                <!-- Now we can render the completed list table -->
                <?php $listTable->search_box( __( 'Coupon', WPXSMARTSHOP_TEXTDOMAIN ), 'wpss_coupons_search_uniqcode' ); ?>
                <?php $listTable->display() ?>
            </form>
            <?php endif; ?>

    </div>

    <?php
    }


    /**
     * View per l'edit o l'insert
     *
     * @static
     *
     * @param null $id
     */
    public static function editView( $id = null ) {
        if ( is_null( $id ) ) {
            $title = __( 'New Coupon', WPXSMARTSHOP_TEXTDOMAIN );
        } else {

            /* Get all order info */
            $coupon = WPXSmartShopCoupons::coupon( $id, ARRAY_A );
            $title  = sprintf( __( 'Edit Coupon #%s', WPXSMARTSHOP_TEXTDOMAIN ), $coupon['uniqcode'] );
        }
        $url   = remove_query_arg( array( 'action', 'id_coupon' ) );
        ?>
    <div class="metabox-holder">
        <div id="post-body">
            <div id="post-body-content">
                <div class="postbox">
                    <h3><span><?php echo $title ?></span></h3>

                    <div class="inside">
                        <form class="wpss-coupon-edit" name="" method="post" action="<?php echo $url ?>">
                            <?php if ( is_null( $id ) ) : ?>
                            <input name="action" value="insert" type="hidden"/>
                            <?php else : ?>
                            <input name="action" value="update" type="hidden"/>
                            <input name="id" value="<?php echo $id ?>" type="hidden"/>
                            <?php endif; ?>
                            <?php WPDKForm::nonceWithKey( 'coupons' ) ?>
                            <?php WPDKForm::htmlForm( WPXSmartShopCoupons::fields( $id ) ); ?>

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

                        <?php
                        /* Dialogs */
                        WPXSmartShopCoupons::dialogProductsPicker();
                        WPXSmartShopCoupons::dialogUserPicker();
                        ?>

                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php
    }

}
