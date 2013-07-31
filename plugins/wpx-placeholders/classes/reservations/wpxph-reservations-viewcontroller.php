<?php
/**
 * @description        View Controller delle prenotazioni
 *
 * @package            wpx Placeholders
 * @subpackage         reservation
 * @author             =undo= <g.fazioli@undolog.com>, <g.fazioli@wpxtre.me>
 * @copyright          Copyright (c) 2012 wpXtreme, Inc.
 * @link               http://wpxtre.me
 * @created            03/04/12
 * @version            1.0.0
 *
 * @filename           wpxph-reservations-viewcontroller
 *
 */

class WPPlaceholdersReservationsViewController {

    // -----------------------------------------------------------------------------------------------------------------
    // Views
    // -----------------------------------------------------------------------------------------------------------------

    /// Debug
    private static function debug() {
        /* run time debug */
        if ( defined( 'WPXPLACEHOLDERS_DEBUG' ) && true == WPXPLACEHOLDERS_DEBUG ) : ?>
        <div class="wpdk-list-table-filter-row" style="margin: 16px 0; border: 2px dashed #aaa; padding: 8px">
            <label for="wpxph_debug_show_sql">Show SQL:</label>
            <input type="checkbox" name="wpxph_debug_show_sql" value="y"/>
        </div>
        <?php endif;
    }

    /**
     * View per la visualizzazione della lista dei corrieri
     *
     * @static
     *
     */
    public static function listTableView() {
        if ( !class_exists( 'WPPlaceholdersReservationsListTable' ) ) {
            require_once( 'wpxph-reservations-listtable.php' );
        }

        /* Patch per eliminare il continuo e ricorsivo incremento della _wp_http_referer */
        $_SERVER['REQUEST_URI'] = remove_query_arg( array( '_wp_http_referer', '_wpnonce' ), stripslashes( $_SERVER['REQUEST_URI'] ) );

        $url = add_query_arg( array( 'action' => 'new', 'page' => $_REQUEST['page'] ), admin_url( 'admin.php' ) );
        ?>

    <div class="wrap wpph-reservation wpph-list-table-box">
        <div class="wpxph-icon-64"></div>
        <h2><?php _e( 'Reservations', WPXPLACEHOLDERS_TEXTDOMAIN ) ?>
            <?php if ( !isset( $_GET['action'] ) || $_GET['action'] != 'new' ) : ?>
                <a class="add-new-h2" href="<?php echo $url ?>"><?php _e( 'Add New', WPXPLACEHOLDERS_TEXTDOMAIN ) ?></a>
                <?php endif; ?>
        </h2>

        <?php
        $listTable = new WPPlaceholdersReservationsListTable();

        /* Fetch, prepare, sort, and filter our data... */
        if ( !$listTable->prepare_items() ) :
            $listTable->views(); ?>

            <form id="carriers-filter" class="wpph-list-table-form" method="get" action="">
                <!-- For plugins, we also need to ensure that the form posts back to our current page -->
                <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>"/>
                <!-- Now we can render the completed list table -->
                <?php $listTable->display() ?>
                <?php self::debug() ?>
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
     * @param int $id ID dell'ambiente (reservation)
     */
    public static function editView( $id = null ) {
        if ( is_null( $id ) ) {
            $title = __( 'New Reservation', WPXPLACEHOLDERS_TEXTDOMAIN );
        } else {
            $title = __( 'Edit Reservation', WPXPLACEHOLDERS_TEXTDOMAIN );
        }
        $url = remove_query_arg( array( 'action', 'id_reservation' ) );
        ?>
    <div class="metabox-holder">
        <div id="post-body">
            <div id="post-body-content">
                <div class="postbox">
                    <h3><span><?php echo $title ?></span></h3>

                    <div class="inside">
                        <form class="wpph-form-reservation" name="" method="post" action="<?php echo $url ?>">
                            <?php if ( is_null( $id ) ) : ?>
                            <input name="action" value="insert" type="hidden"/>
                            <?php else : ?>
                            <input name="action" value="update" type="hidden"/>
                            <input name="id" value="<?php echo $id ?>" type="hidden"/>
                            <?php endif; ?>
                            <?php do_action('wpdk_edit_before_table_filter'); ?>
                            <?php WPDKForm::nonceWithKey( __CLASS__ ) ?>
                            <?php WPDKForm::htmlForm( WPPlaceholdersReservations::fields( $id ) ); ?>

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