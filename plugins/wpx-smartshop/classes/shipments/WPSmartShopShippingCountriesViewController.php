<?php
/**
 * View Controller per i paesi, zone di spedizioni e altre info su moneta e tasse
 *
 * @package            wpx SmartShop
 * @subpackage         WPSmartShopShippingCountriesViewController
 * @author             =undo= <g.fazioli@undolog.com>, <g.fazioli@wpxtre.me>
 * @copyright          Copyright (c) 2012 wpXtreme, Inc.
 * @link               http://wpxtre.me
 * @created            07/02/12
 * @version            1.0.0
 *
 */

class WPSmartShopShippingCountriesViewController {

    // -----------------------------------------------------------------------------------------------------------------
    // Views
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * View per la visualizzazione della lista dei corrieri
     *
     * @package    wpx SmartShop
     * @subpackage WPSmartShopShippingCountriesViewController
     * @since      1.0.0
     *
     * @static
     *
     */
    public static function listTableView() {
        if ( !class_exists( 'WPSmartShopShippingCountriesListTable' ) ) {
            require_once( 'WPSmartShopShippingCountriesListTable.php' );
        }
        $url = add_query_arg( array( 'action' => 'new' ), $_SERVER['REQUEST_URI'] ); ?>

    <div class="wrap wpss-shipping-countries wpdk-list-table-box">
        <div class="icon32 icon32-posts-wpss-cpt-product" id="icon-edit"></div>
        <h2>
            <?php _e( 'Shipping Countries', WPXSMARTSHOP_TEXTDOMAIN ) ?> <a class="add-new-h2"
                                                                   href="<?php echo $url ?>"><?php _e( 'Add New', WPXSMARTSHOP_TEXTDOMAIN ) ?></a>
        </h2>

        <?php
        $listTable = new WPSmartShopShippingCountriesListTable();

        /* Fetch, prepare, sort, and filter our data... */
        if ( !$listTable->prepare_items() ) :
            $listTable->views(); ?>

            <form id="shipping-countries-filter" class="wpdk-list-table-form" method="get" action="">
                <!-- For plugins, we also need to ensure that the form posts back to our current page -->
                <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>"/>
                <input type="hidden" name="post_type" value="<?php echo WPXSMARTSHOP_PRODUCT_POST_KEY ?>"/>
                <!-- Now we can render the completed list table -->
                <?php $listTable->search_box( __( 'Country', WPXSMARTSHOP_TEXTDOMAIN ), 'country' ); ?>
                <?php $listTable->display() ?>
            </form>
            <?php endif; ?>
    </div>
    <?php
    }


    /**
     * View per l'edit o l'insert
     *
     * @package    wpx SmartShop
     * @subpackage WPSmartShopShippingCountriesViewController
     * @since      1.0.0
     *
     * @static
     *
     * @param null $id
     */
    public static function editView( $id = null ) {
        if ( is_null( $id ) ) {
            $title = __( 'New Shipping Country', WPXSMARTSHOP_TEXTDOMAIN );
        } else {
            $title = __( 'Edit Shipping Country', WPXSMARTSHOP_TEXTDOMAIN );
        }
        $url   = remove_query_arg( array( 'action', 'shippingcountryid' ) ); ?>
    <div class="metabox-holder">
        <div id="post-body">
            <div id="post-body-content">
                <div class="postbox">
                    <h3><span><?php echo $title ?></span></h3>

                    <div class="inside">
                        <form class="wpss-form-shipping-country" name="" method="post" action="<?php echo $url ?>">
                            <?php if ( is_null( $id ) ) : ?>
                                <input name="action" value="insert" type="hidden"/>
                            <?php else : ?>
                                <input name="action" value="update" type="hidden"/>
                                <input name="id" value="<?php echo $id ?>" type="hidden"/>
                            <?php endif; ?>
                            <?php WPDKForm::nonceWithKey( 'shipping-country' ) ?>
                            <?php WPDKForm::htmlForm( WPSmartShopShippingCountries::fields( $id ) ); ?>

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

    // -----------------------------------------------------------------------------------------------------------------
    // UI
    // -----------------------------------------------------------------------------------------------------------------

    public static function searchBox($text, $input_id) { ?>
        <p class="search-box">
        	<label class="screen-reader-text" for="<?php echo $input_id ?>"><?php echo $text; ?>:</label>
        	<input type="text" id="<?php echo $input_id ?>" name="s" value="<?php _admin_search_query(); ?>" />
        	<?php submit_button( $text, 'button', false, false, array('id' => 'search-submit') ); ?>
        </p>
     <?php
    }

}
