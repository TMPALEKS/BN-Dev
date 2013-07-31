<?php
/**
 * @class              WPXSmartShopShoppingCartWidget
 * @description        WordPress Widegt Class for Cart.
 *                     Il Widget del carrello si basa sulle informazioni lasciate nelle SESSIONI.
 *
 * @package            wpx SmartShop
 * @subpackage         widgets
 * @author             =undo= <g.fazioli@wpxtre.me>
 * @copyright          Copyright (c)2012 wpXtreme, Inc.
 * @created            11/11/11
 * @version            1.0
 *
 */

class WPXSmartShopShoppingCartWidget extends WP_Widget {

    /// Construct
    /**
     * Construct
     */
    function __construct() {

        /* Localization */
        load_plugin_textdomain( WPXSMARTSHOP_TEXTDOMAIN, false, WPXSMARTSHOP_TEXTDOMAIN_PATH );

        /* Default options: only the title */
        $this->defaultOption = array( 'title' => __( 'Cart', WPXSMARTSHOP_TEXTDOMAIN ) );

        /* Init */
        $widget_ops = array(
            'classname'   => 'WPXSmartShopShoppingCartWidget',
            'description' => __( 'This is the shopping Cart to position on your sidebar', WPXSMARTSHOP_TEXTDOMAIN )
        );

        /* Uncomment the code below to define width and height of widget's window

          $control_ops = array('width' => 430, 'height' => 350);
          $this->WP_Widget('wpss_cart_widget', WPXSMARTSHOP_NAME, $widget_ops, $control_ops);

          */
        $this->WP_Widget( 'wpss_cart_widget', __( 'WP SmartShop Shopping Cart', WPXSMARTSHOP_TEXTDOMAIN ), $widget_ops );
    }

    /// Display the widgets
    /**
     * Visualizza il Widget lato frontned. Questo metodo viene chiamato quando WordPress deve visualizzare il Widget
     *
     * @param array $args
     * @param array $instance
     *
     * @todo Mancano da inserire le taggature standard dei widget WordPress
     *
     * @retval void
     */
    function widget( $args, $instance ) {

        /* Get the general settings for check open/close shop below */

        $general = WPXSmartShop::settings()->general();

        $shop_closed = true;
        $shop_closed_message = apply_filters( 'wpxss_shopping_cart_widget_shop_closed_message', __( 'Shop Closed for maintenance', WPXSMARTSHOP_TEXTDOMAIN ) );
        if( isset( $general['shop_open']) && 'y' == $general['shop_open'] ) {
            $shop_closed = false;
        }

        /* Controlla se deve visualizzare il Widget solo se l'utente è loggato al sistema */

        if ( WPXSmartShop::settings()->shopping_cart_display_for_user_logon_only() ) {
            if ( !is_user_logged_in() ) {
                return;
            }
        }

        $before_widget = '';
        $after_widget  = '';

        extract( $args );
        echo $before_widget; ?>
    <div class="wpss-widget-cart-box <?php echo $shop_closed ? 'wpxss_shopping_cart_widget_shop_closed' : '' ?>">
        <h2>
            <?php
            /**
             * @filters
             *
             * @param string $title Titolo del Widget del carrello
             */
            $title = apply_filters( 'wpss_cart_widget_title', $instance['title'] );
            echo $title
            ?></h2>

        <div class="wpss-widget-ajax-cart-box">
            <?php if ( false === $shop_closed ) : ?>
                <?php echo WPXSmartShopShoppingCart::cart(); ?>
            <?php else : ?>
                <p><?php echo $shop_closed_message ?></p>
            <?php endif; ?>
        </div>

    </div>
    <?php
        echo $after_widget;
    }

    /// Display widget settings
    /**
     * Visualizza una maschera di impostazioni lato Backend
     *
     * @param $instance
     */
    function form( $instance ) {
        $instance = wp_parse_args( (array)$instance, $this->defaultOption );
        $title    = strip_tags( $instance['title'] ); ?>
    <p>
        <label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title', WPXSMARTSHOP_TEXTDOMAIN ); ?>:</label>
        <input class="widefat"
               id="<?php echo $this->get_field_id( 'title' ); ?>"
               name="<?php echo $this->get_field_name( 'title' ); ?>"
               type="text"
               value="<?php echo esc_attr( $title ); ?>"/>
    </p>
    <?php

    }

    /// Update widget settings
    /**
     * Chiamata da Backend per memorizzare le impostazioni
     *
     * @param array $new_instance
     * @param array $old_instance
     *
     * @retval array
     */
    function update( $new_instance, $old_instance ) {
        $new_instance = wp_parse_args( $new_instance, $this->defaultOption );

        $old_instance['title'] = strip_tags( $new_instance['title'] );

        return $old_instance;
    }

}