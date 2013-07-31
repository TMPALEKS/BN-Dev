<?php
/**
 * @class              WPXSmartShopCarriersViewController
 * @description        View Controller per i Corrieri
 *
 * @package            wpx SmartShop
 * @subpackage         carriers
 * @author             =undo= <g.fazioli@undolog.com>, <g.fazioli@wpxtre.me>
 * @copyright          Copyright (c) 2012 wpXtreme, Inc.
 * @created            06/02/12
 * @version            1.0.0
 *
 */

class WPXSmartShopCarriersViewController {

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
        if ( !class_exists( 'WPXSmartShopCarriersListTable' ) ) {
            require_once( 'wpxss-carriers-listtable.php' );
        }
        $url = add_query_arg( array( 'action' => 'new' ), $_SERVER['REQUEST_URI'] ); ?>

    <div class="wrap wpss-carriers wpdk-list-table-box">
        <div class="icon32 icon32-posts-wpss-cpt-product" id="icon-edit"></div>
        <h2>
            <?php _e( 'Carriers', WPXSMARTSHOP_TEXTDOMAIN ) ?> <a class="add-new-h2"
                                                         href="<?php echo $url ?>"><?php _e( 'Add New', WPXSMARTSHOP_TEXTDOMAIN ) ?></a>
        </h2>

        <?php
        $listTable = new WPXSmartShopCarriersListTable();

        /* Fetch, prepare, sort, and filter our data... */
        if ( !$listTable->prepare_items() ) :
            $listTable->views(); ?>

            <form id="carriers-filter" class="wpdk-list-table-form" method="get" action="">
                <!-- For plugins, we also need to ensure that the form posts back to our current page -->
                <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>"/>
                <input type="hidden" name="post_type" value="<?php echo WPXSMARTSHOP_PRODUCT_POST_KEY ?>"/>
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
            $title = __( 'New Carrier', WPXSMARTSHOP_TEXTDOMAIN );
        } else {
            $title = __( 'Edit Carrier', WPXSMARTSHOP_TEXTDOMAIN );
        }
        $url   = remove_query_arg( array( 'action', 'carrierid' ) );
        ?>
    <div class="metabox-holder">
        <div id="post-body">
            <div id="post-body-content">
                <div class="postbox">
                    <h3><span><?php echo $title ?></span></h3>

                    <div class="inside">
                        <form class="wpss-form-carrier" name="" method="post" action="<?php echo $url ?>">
                            <?php if ( is_null( $id ) ) : ?>
                            <input name="action" value="insert" type="hidden"/>
                            <?php else : ?>
                            <input name="action" value="update" type="hidden"/>
                            <input name="id" value="<?php echo $id ?>" type="hidden"/>
                            <?php endif; ?>
                            <?php WPDKForm::nonceWithKey( 'carriers' ) ?>
                            <?php WPDKForm::htmlForm( WPXSmartShopCarriers::fields( $id ) ); ?>

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
