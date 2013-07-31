<?php
/**
 * Widget per visualizzare gli Ultimi commenti in compatibilità WPML, nel senso che etrae tutti i commenti dalla tabella
 * comments, senza distinguere italiano o inglese
 *
 * @package            BNMExtends
 * @subpackage         BNMExtendsWidgetComments.php
 * @author             =undo= <g.fazioli@undolog.com>, <g.fazioli@saidmade.com>
 * @copyright          Copyright (C)2011 Saidmade Srl.
 * @created            22/12/11
 * @version            1.0
 *
 */

class BNMExtendsWidgetComments extends WP_Widget {

    function __construct() {
        $widget_ops = array();
        $this->WP_Widget('bnm_comments_widget', __('Ultimi commenti', 'bnmextends'), $widget_ops);
    }

    function widget($args, $instance) {
        global $wpdb;

        $before_widget = '';
        $after_widget  = '';

        extract($args);
        echo $before_widget;
        ?>
    <div class="footer_recent_comments">
        <h2><?php _e('Latest comments', 'bnmextends') ?></h2>
        <ul>

            <?php
            $sql = <<< SQL
            SELECT DISTINCT comment_ID, comment_post_ID, comment_content, comment_type, comment_author_email, comment_author, comment_date_gmt, comment_approved
            FROM {$wpdb->comments}
            WHERE comment_approved = '1'
            ORDER BY comment_date_gmt DESC
            LIMIT 5
SQL;
            $comments = $wpdb->get_results($sql);

            foreach ($comments as $comment) :
                ?>

                <?php if ($comment->comment_type == '') { ?>

                <?php $comm_title = get_the_title($comment->comment_post_ID); ?>
                <?php $comm_link = get_comment_link($comment->comment_ID); ?>
                <?php $comm_comm_temp = get_comment($comment->comment_ID, ARRAY_A); ?>

                <?php $comm_content = $comm_comm_temp['comment_content']; ?>

                <li>
                    <?php //echo get_avatar($comment->comment_author_email, '32')	?>
                    <p>
                        <span class="footer_comm_author">
                        <?php echo($comment->comment_author)?>
                        </span>
                    <?php _e('said', 'bnmextends') ?>
                    <a
                        href="<?php echo($comm_link)?>"
                        title="<?php comment_excerpt(); ?>"> <?php echo $comm_title?> </a>
                        <br/>
                        <?php echo substr(strip_tags($comm_content),0,80) ?>...
                    </p>
                </li>

                <?php } ?>

                <?php endforeach;?>

        </ul>
    </div>
    <?php
        echo $after_widget;
    }

    // -----------------------------------------------------------------------------------------------------------------
    // WPML Patch
    // -----------------------------------------------------------------------------------------------------------------
    function it_comments($comments, $post_ID) {

    }
}
