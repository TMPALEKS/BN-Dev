<?php
/**
 * $DESCRIPTION
 *
 * @package            ${PACKAGE}
 * @subpackage         BNMExtendsOptionViewController
 * @author             =undo= <g.fazioli@undolog.com>, <g.fazioli@saidmade.com>
 * @copyright          Copyright (c) 2012 Saidmade Srl.
 * @link               http://www.saidmade.com
 * @created            30/01/12
 * @version            1.0.0
 *
 */

class BNMExtendsOptionViewController {

    private static $menu;

    // -----------------------------------------------------------------------------------------------------------------
    // Static values
    // -----------------------------------------------------------------------------------------------------------------

    public static function fileds() {
        $fields = array(
            __( 'LeaderBoard Banner', 'bnmextends' ) => array(
                array(
                    array(
                        'type'      => WPDK_FORM_FIELD_TYPE_CHECKBOX,
                        'name'      => 'leaderboard',
                        'label'     => __( 'Display LeaderBoard Banner', 'bnmextends' ),
                        'value'     => 'y',
                        'checked'     => BNMExtendsOptions::leaderboard()
                    )
                )
            ),
            __( 'Featured', 'bnmextends' ) => array(
                array(
                    array(
                        'type'      => WPDK_FORM_FIELD_TYPE_NUMBER,
                        'name'      => 'numberFeatured',
                        'label'     => __( 'Number featured', 'bnmextends' ),
                        'value'     => BNMExtendsOptions::numberFeatured()
                    )
                )
            ),
            __( 'Programs', 'bnmextends' ) => array(
                array(
                    array(
                        'type'      => WPDK_FORM_FIELD_TYPE_NUMBER,
                        'name'      => 'numberEvents',
                        'label'     => __( 'Number Events', 'bnmextends' ),
                        'value'     => BNMExtendsOptions::numberEvents()
                    )
                )
            ),
            __( 'Products', 'bnmextends' ) => array(
                array(
                    array(
                        'type'      => WPDK_FORM_FIELD_TYPE_NUMBER,
                        'name'      => 'percentageAdvance',
                        'label'     => __( 'Percentage Advance', 'bnmextends' ),
                        'value'     => BNMExtendsOptions::percentageAdvance(),
                        'append'    => '%'
                    )
                )
            )
        );
        return $fields;
    }

    // -----------------------------------------------------------------------------------------------------------------
    // Init
    // -----------------------------------------------------------------------------------------------------------------

    public static function init( $menu ) {

        self::$menu = $menu;

        /* WordPress Postbox script */
        add_action('admin_enqueue_scripts', array( __CLASS__, 'admin_enqueue_scripts'));

        /* Scripts for postbox */
        add_action('admin_head-' . $menu['hook'], array( __CLASS__, 'admin_head'));

        add_action( 'load-' . $menu['hook'], array( __CLASS__, 'load_meta_boxes') );
    }

    // -----------------------------------------------------------------------------------------------------------------
    // WordPress hook
    // -----------------------------------------------------------------------------------------------------------------

    public static function load_meta_boxes() {
        /* Add the 'About' meta box. */
        add_meta_box( 'bnm-settings', __( 'Home Page', 'bnmextends' ), array( __CLASS__, 'display_settings'), self::$menu['hook'], 'normal', 'core' );

    }

    public static function add_meta_boxes() {
        do_action( 'add_meta_boxes', self::$menu['hook'] );
    }

    /* Non usato per ora */
    public static function screen_layout_columns($columns, $screen) {
        if ($screen == self::$menu['hook']) {
            $columns[self::$menu['hook']] = 2;
        }
        return $columns;
    }

    // -----------------------------------------------------------------------------------------------------------------
    // Scripts
    // -----------------------------------------------------------------------------------------------------------------
    /**
     * Loads JavaScript for handling the open/closed state of each meta box.
     *
     * @since 1.0.0
     */
    public static function admin_head() {
    	?>
    	<script type="text/javascript">
    		//<![CDATA[
    		jQuery(document).ready( function($) {
    			$('.if-js-closed').removeClass('if-js-closed').addClass('closed');
    			postboxes.add_postbox_toggles( '<?php echo self::$menu['hook'] ?>' );
    		});
    		//]]>
    	</script>
    <?php }


    public static function admin_enqueue_scripts( $hook_suffix ) {
        if ( isset( self::$menu['hook'] ) && $hook_suffix == self::$menu['hook'] ) {
            wp_enqueue_script( 'common' );
            wp_enqueue_script( 'wp-lists' );
            wp_enqueue_script( 'postbox' );
        }
    }

    // -----------------------------------------------------------------------------------------------------------------
    // Display
    // -----------------------------------------------------------------------------------------------------------------
    public static function display() { ?>
        <div class="wrap">

            <?php screen_icon(); ?>

            <h2><?php _e( 'Blue Note Milano Settings', 'bnmextends' ); ?></h2>
            <?php self::update() ?>
            <div id="poststuff">

                <form method="post" action="">

                    <?php WPDKForm::nonceWithKey('bnm_settings') ?>
                    <?php wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false ); ?>
                    <?php wp_nonce_field( 'meta-box-order', 'meta-box-order-nonce', false ); ?>

                    <div class="metabox-holder">
                        <div class="post-box-container normal"><?php do_meta_boxes( self::$menu['hook'], 'normal', null ); ?></div>
                    </div>

                    <?php submit_button( __( 'Update Settings', 'bnmextends' ) ); ?>

                </form>

            </div><!-- #poststuff -->

        </div><!-- .wrap -->
        <?php
    }

    public static function display_settings( $object, $box ) {
        WPDKForm::htmlForm( self::fileds() );
    }

    // -----------------------------------------------------------------------------------------------------------------
    // Update
    // -----------------------------------------------------------------------------------------------------------------

    public static function update() {
        if ( WPDKForm::isNonceVerify( 'bnm_settings' ) ) {

            $leaderboard = isset( $_POST['leaderboard'] ) ? $_POST['leaderboard'] : 'n';

            BNMExtendsOptions::leaderboard( $leaderboard );
            BNMExtendsOptions::numberFeatured( absint( esc_attr( $_POST['numberFeatured'] ) ) );
            BNMExtendsOptions::numberEvents( absint( esc_attr( $_POST['numberEvents'] ) ) );
            BNMExtendsOptions::percentageAdvance( absint( esc_attr( $_POST['percentageAdvance'] ) ) );
            ?>
        <div id="message" class="updated fade"><p>Update</p></div>
        <?php
        }
    }
}
