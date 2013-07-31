<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Webeing.net
 * Date: 27/07/13
 * Time: 12:46
 */

class BNMExtendsUserViewController {

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

        if ( !class_exists( 'BNMExtendsUserListTable' ) ) {
            require_once( 'BNMExtendsUser-listtable.php' );
        }

        $listTable = new BNMExtendsUserListTable();

        /* Fetch, prepare, sort, and filter our data... */
        if ( !$listTable->prepare_items() ) :
            $listTable->views();
            ?>
            <div class="wrap"><h2><?php echo __('Esportazione Utenti','bnmextends') ?></h2></div>
            <form id="order-filter" class="wpdk-list-table-form" method="get" action="">
                <!-- For plugins, we also need to ensure that the form posts back to our current page -->
                <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>"/>
                <!-- Now we can render the completed list table -->
                <?php //$listTable->search_box( __( 'Product name', WPXSMARTSHOP_TEXTDOMAIN ), 'wpss_stats_search_product_name' ); ?>
                <?php $listTable->display() ?>
            </form>
        <?php endif;
    }
}