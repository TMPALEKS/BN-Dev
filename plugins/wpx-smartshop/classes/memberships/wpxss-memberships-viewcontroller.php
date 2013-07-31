<?php
/**
 * @class              WPXSmartShopMembershipsViewController
 *
 * @description        View Controller per la gestione delle sottoscrizioni membership
 *
 * @package            wpx SmartShop
 * @subpackage         memberships
 * @author             =undo= <g.fazioli@undolog.com>, <g.fazioli@wpxtre.me>
 * @copyright          Copyright (c) 2012 wpXtreme, Inc.
 * @link               http://wpxtre.me
 * @created            22/02/12
 * @version            1.0.0
 *
 * @filename           wpxss-memberships-viewcontroller
 *
 */

class WPXSmartShopMembershipsViewController {

    // -----------------------------------------------------------------------------------------------------------------
    // Views
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * View per la visualizzazione della lista dei corrieri
     *
     * @static
     */
    public static function listTableView() {
        if ( !class_exists( 'WPXSmartShopMembershipsListTable' ) ) {
            require_once( 'wpxss-memberships-listtable.php' );
        }
        $url = add_query_arg( array( 'action' => 'new' ), $_SERVER['REQUEST_URI'] ); ?>

    <div class="wrap wpss-memberships wpdk-list-table-box">
        <div class="icon32 icon32-posts-wpss-cpt-product" id="icon-edit"></div>
        <h2>
            <?php _e( 'Memberships', WPXSMARTSHOP_TEXTDOMAIN ) ?> <a class="add-new-h2"
                                                       href="<?php echo $url ?>"><?php _e( 'Add New', WPXSMARTSHOP_TEXTDOMAIN ) ?></a>
        </h2>

        <?php
        $listTable = new WPXSmartShopMembershipsListTable();

        /* Fetch, prepare, sort, and filter our data... */
        if ( !$listTable->prepare_items() ) :
            $listTable->views(); ?>

            <form id="memberships-filter" class="wpdk-list-table-form" method="get" action="">
                <!-- For plugins, we also need to ensure that the form posts back to our current page -->
                <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>"/>
                <!-- Now we can render the completed list table -->
                <?php $listTable->search_box( __( 'Name or email', WPXSMARTSHOP_TEXTDOMAIN ), 'wpxss_memberships_search_name_email' ); ?>
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
            $title = __( 'New Memberships', WPXSMARTSHOP_TEXTDOMAIN );
        } else {
            $title = __( 'Edit Memberships', WPXSMARTSHOP_TEXTDOMAIN );
        }
        ?>
    <div class="metabox-holder">
        <div id="post-body">
            <div id="post-body-content">
                <div class="postbox">
                    <h3><span><?php echo $title ?></span></h3>

                    <div class="inside">
                        <form class="wpss-form-memberships" name="" method="post" action="">
                            <?php if ( is_null( $id ) ) : ?>
                            <input name="action" value="insert" type="hidden"/>
                            <?php else : ?>
                            <input name="action" value="update" type="hidden"/>
                            <input name="id" value="<?php echo $id ?>" type="hidden"/>
                            <?php endif; ?>
                            <?php WPDKForm::nonceWithKey( 'memberships' ) ?>
                            <?php WPDKForm::htmlForm( WPXSmartShopMemberships::fields( $id ) ); ?>

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