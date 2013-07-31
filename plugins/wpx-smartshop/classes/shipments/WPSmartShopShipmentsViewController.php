<?php
/**
 * @class WPSmartShopShipmentsViewController
 * @description View Controller per le spedizioni
 *
 * @package            wpx SmartShop
 * @subpackage         shipments
 * @author             =undo= <g.fazioli@wpxtre.me>
 * @copyright          Copyright (c) 2012 wpXtreme, Inc.
 * @created            07/02/12
 * @version            1.0.0
 *
 */

class WPSmartShopShipmentsViewController {

    // -----------------------------------------------------------------------------------------------------------------
    // Views
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * View per la visualizzazione della lista dei corrieri
     *
     * @static
     *
     */
    public static function listTableView() {
        if ( !class_exists( 'WPSmartShopShipmentsListTable' ) ) {
            require_once( 'WPSmartShopShipmentsListTable.php' );
        }
        /* Patch per eliminare il continuo e ricorsivo incremento della _wp_http_referer */
        $_SERVER['REQUEST_URI'] = remove_query_arg( array( '_wp_http_referer', '_wpnonce' ), stripslashes( $_SERVER['REQUEST_URI'] ) );

        $url = add_query_arg( array( 'action' => 'new', 'page' => $_REQUEST['page'] ), admin_url( 'admin.php' ) );
        ?>

    <div class="wrap wpss-shipments wpdk-list-table-box">
        <div class="icon32 icon32-posts-wpss-cpt-product" id="icon-edit"></div>
        <h2><?php _e( 'Shipments', WPXSMARTSHOP_TEXTDOMAIN ) ?>
            <a class="add-new-h2" href="<?php echo $url ?>"><?php _e( 'Add New', WPXSMARTSHOP_TEXTDOMAIN ) ?></a>
        </h2>

        <?php
        $listTable = new WPSmartShopShipmentsListTable();

        /* Fetch, prepare, sort, and filter our data... */
        if ( !$listTable->prepare_items() ) :
            $listTable->views(); ?>

            <form id="shipments-filter" class="wpdk-list-table-form" method="get" action="">
                <!-- For plugins, we also need to ensure that the form posts back to our current page -->
                <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>"/>
                <!-- Now we can render the completed list table -->
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
            $title = __( 'New Shipments', WPXSMARTSHOP_TEXTDOMAIN );
        } else {
            $title = __( 'Edit Shipments', WPXSMARTSHOP_TEXTDOMAIN );
        }
        $url   = remove_query_arg( array( 'action', 'shipmentid' ) );
        ?>
    <div class="metabox-holder">
        <div id="post-body">
            <div id="post-body-content">
                <div class="postbox">
                    <h3><span><?php echo $title ?></span></h3>

                    <div class="inside">
                        <form class="wpss-form-shipments" name="" method="post" action="<?php echo $url ?>">
                            <?php if ( is_null( $id ) ) : ?>
                                <input name="action" value="insert" type="hidden"/>
                            <?php else : ?>
                                <input name="action" value="update" type="hidden"/>
                                <input name="id" value="<?php echo $id ?>" type="hidden"/>
                            <?php endif; ?>
                            <?php WPDKForm::nonceWithKey( 'shipments' ) ?>
                            <?php WPDKForm::htmlForm( WPSmartShopShipments::fields( $id ) ); ?>

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
