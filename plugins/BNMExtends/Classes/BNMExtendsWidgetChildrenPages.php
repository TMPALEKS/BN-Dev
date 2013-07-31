<?php
/**
 * Visualizza un combo con i figli di una pagina
 *
 * @package            BNMExtends
 * @subpackage         BNMExtendsWidgetChildrenPages
 * @author             =undo= <g.fazioli@undolog.com>, <g.fazioli@saidmade.com>
 * @copyright          Copyright (c) 2012 Saidmade Srl.
 * @link               http://www.saidmade.com
 * @created            29/02/12
 * @version            1.0.0
 *
 */

class BNMExtendsWidgetChildrenPages extends WP_Widget {

    function __construct() {
        $widget_ops = array();
        $this->WP_Widget( 'bnm_children_page_widget', __( 'Children Pages', 'bnmextends' ), $widget_ops );
    }

    // -----------------------------------------------------------------------------------------------------------------
    // Static values
    // -----------------------------------------------------------------------------------------------------------------

    function widget( $args, $instance ) {
        $before_widget = '';
        $after_widget  = '';

        extract( $args );
        echo $before_widget;

        $page = get_page_by_path( $instance['parent_slug'] );

        $title = apply_filters( 'bnm_widget_children_pages', $instance['title'] );

        ?>

    <h2><?php _e( $title, 'bnmextends' ) ?></h2>

    <?php
        if ( $instance['mode'] == 'list' ) {
            $result = wp_list_pages( array(
                                          'title_li'  => false,
                                          'child_of'  => $page->ID,
                                          'echo'      => 0
                                     ) );
            $html = <<< HTML
            <ul>
                {$result}
            </ul>
HTML;

        } elseif ( $instance['mode'] == 'menu' ) {

            $result = wp_dropdown_pages( array(
                                              'title_li'  => false,
                                              'child_of'  => $page->ID,
                                              'echo'      => 0
                                         ) );
            $html = $result;
        }

        echo $html;

        echo $after_widget;
    }

    /**
     * Visualizza una maschera con la scelta della pagina padre
     *
     * @package            BNMExtends
     * @subpackage         BNMExtendsWidgetChildrenPages
     * @since              1.0.0
     *
     * @param $instance
     */
    function form( $instance ) {
        $instance = wp_parse_args( (array)$instance, $this->defaultOption );

        $parent_slug = strip_tags( $instance['parent_slug'] );
        $title       = strip_tags( $instance['title'] );
        $mode        = strip_tags( $instance['mode'] );

        ?>
    <p>
        <label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title', 'bnmextends' ); ?>:</label>
        <input class="widefat"
               id="<?php echo $this->get_field_id( 'title' ); ?>"
               name="<?php echo $this->get_field_name( 'title' ); ?>"
               type="text"
               value="<?php echo esc_attr( $title ); ?>"/>
    </p>

    <p>
        <label for="<?php echo $this->get_field_id( 'parent_slug' ); ?>"><?php _e( 'Parent Slug', 'bnmextends' ); ?>
            :</label>
        <input class="widefat"
               id="<?php echo $this->get_field_id( 'parent_slug' ); ?>"
               name="<?php echo $this->get_field_name( 'parent_slug' ); ?>"
               type="text"
               value="<?php echo esc_attr( $parent_slug ); ?>"/>
    </p>

    <p>
        <label for="<?php echo $this->get_field_id( 'mode' ); ?>"><?php _e( 'Mode', 'bnmextends' ); ?>:</label>
        <select class="widefat"
                id="<?php echo $this->get_field_id( 'mode' ); ?>"
                name="<?php echo $this->get_field_name( 'mode' ); ?>">
            <option <?php selected( 'list', esc_attr( $mode ) ) ?> value="list"><?php _e( 'List', 'bnmextends' ); ?></option>
            <option <?php selected( 'menu', esc_attr( $mode ) ) ?> value="menu"><?php _e( 'Menu', 'bnmextends' ); ?></option>
        </select>
    </p>

    <?php

    }

    /**
     * Chiamata da Backend per memorizzare le impostazioni
     *
     * @package            BNMExtends
     * @subpackage         BNMExtendsWidgetChildrenPages
     * @since              1.0.0
     *
     * @param $new_instance
     * @param $old_instance
     *
     * @return array
     */
    function update( $new_instance, $old_instance ) {
        $new_instance = wp_parse_args( $new_instance, $this->defaultOption );

        $old_instance['parent_slug'] = strip_tags( $new_instance['parent_slug'] );
        $old_instance['title']       = strip_tags( $new_instance['title'] );
        $old_instance['mode']        = strip_tags( $new_instance['mode'] );

        return $old_instance;
    }

}