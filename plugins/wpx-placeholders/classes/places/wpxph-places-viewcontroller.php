<?php
/**
 * View Controller dei Places
 *
 * @package            wpx Placeholders
 * @subpackage         WPPlaceholdersPlacesViewController
 * @author             =undo= <g.fazioli@undolog.com>, <g.fazioli@wpxtre.me>
 * @copyright          Copyright (c) 2012 wpXtreme, Inc.
 * @link               http://wpxtre.me
 * @created            03/04/12
 * @version            1.0.0
 *
 */

class WPPlaceholdersPlacesViewController {

    // -----------------------------------------------------------------------------------------------------------------
    // Views
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * View per la visualizzazione della lista dei corrieri
     *
     * @package            wpx Placeholders
     * @subpackage         WPPlaceholdersPlacesViewController
     * @since              1.0
     *
     * @static
     *
     */
    public static function listTableView() {
        if ( !class_exists( 'WPPlaceholdersPlacesListTable' ) ) {
            require_once( 'wpxph-places-listtable.php' );
        }
        $url = add_query_arg( array( 'action' => 'new' ), $_SERVER['REQUEST_URI'] ); ?>

    <div class="wrap wpxph-places wpph-list-table-box">
        <div class="wpxph-icon-64"></div>
        <h2><?php _e( 'Places', WPXPLACEHOLDERS_TEXTDOMAIN ) ?><a class="add-new-h2" href="<?php echo $url ?>"><?php _e( 'Add New', WPXPLACEHOLDERS_TEXTDOMAIN ) ?></a></h2>

        <?php
        $listTable = new WPPlaceholdersPlacesListTable();

        /* Fetch, prepare, sort, and filter our data... */
        if ( !$listTable->prepare_items() ) :
            $listTable->views(); ?>

            <form id="carriers-filter" class="wpph-list-table-form" method="get" action="">
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
     * @package            wpx Placeholders
     * @subpackage         WPPlaceholdersPlacesViewController
     * @since              1.0.0
     *
     * @static
     *
     * @param int $id ID del place
     */
    public static function editView( $id = null ) {
        if ( is_null( $id ) ) {
            $title = __( 'New Place', WPXPLACEHOLDERS_TEXTDOMAIN );
        } else {
            $title = __( 'Edit Place', WPXPLACEHOLDERS_TEXTDOMAIN );
        }
        $url = remove_query_arg( array(
                                      'action',
                                      'id_place'
                                 ) );
        ?>
    <div class="metabox-holder">
        <div id="post-body">
            <div id="post-body-content">
                <div class="postbox">
                    <h3><span><?php echo $title ?></span></h3>

                    <div class="inside">
                        <form class="wpph-form-place" name="" method="post" action="<?php echo $url ?>">
                            <?php if ( is_null( $id ) ) : ?>
                            <input name="action" value="insert" type="hidden"/>
                            <?php else : ?>
                            <input name="action" value="update" type="hidden"/>
                            <input name="id" value="<?php echo $id ?>" type="hidden"/>
                            <?php endif; ?>
                            <?php WPDKForm::nonceWithKey( __CLASS__ ) ?>
                            <?php WPDKForm::htmlForm( WPPlaceholdersPlaces::fields( $id ) ); ?>

                            <p>
                                <?php if ( is_null( $id ) ) : ?>
                                <input type="submit" class="button-primary"
                                       value="<?php _e( 'Add', WPXPLACEHOLDERS_TEXTDOMAIN ) ?>"/>
                                <?php else : ?>
                                <input type="submit" class="button-primary"
                                       value="<?php _e( 'Update', WPXPLACEHOLDERS_TEXTDOMAIN ) ?>"/>
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
