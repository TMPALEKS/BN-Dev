<?php
/**
 * Widget per l'iscrizione alla Newsletter di Mailchimp
 *
 * @package            BNMExtends
 * @subpackage         BNMExtendsWidgetBoxoffice.php
 * @author             n.ballotta@saidmade.com
 * @copyright          Copyright (C)2011 Saidmade Srl.
 * @created            01/02/12
 * @version            1.0
 *
 */

class BNMExtendsWidgetBoxoffice extends WP_Widget {

    function __construct() {
        $widget_ops = array();
        $this->WP_Widget( 'bnm_boxoffice_widget', __( 'Box Office', 'bnmextends' ), $widget_ops );
    }

    // -----------------------------------------------------------------------------------------------------------------
    // Static values
    // -----------------------------------------------------------------------------------------------------------------

    function widget( $args, $instance ) {
        $before_widget = '';
        $after_widget  = '';

        extract( $args );
        echo $before_widget;
        ?>
    <p>
        <a class="button orange" href="<?php echo BNMExtends::pagePermalinkWithSlug('programmazione') ?>"><?php _e( 'Purchase Online', 'bnmextends' );?></a>
        <a class="button orange" href="<?php echo BNMExtends::pagePermalinkWithSlug('contatti') ?>"><?php _e( 'Purchase by Phone', 'bnmextends' );?></a>
    </p>
    <?php
        echo $after_widget;
    }
}
