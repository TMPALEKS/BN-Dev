<div id="comments">
	<?php if ( post_password_required() ) : ?>
    <p class="nopassword"><?php _e( 'This post is password protected. Enter the password to view any comments.', 'bnm' ); ?></p>
	</div><!-- #comments -->
<?php
    /* Stop the rest of comments.php from being processed,
              * but don't kill the script entirely -- we still have
              * to fully load the template.
              */
    return;
endif;
?>

<?php if ( have_comments() ) : ?>

<h3 class="commenttitle">
    <?php comments_number( __( 'No comment', 'bnm' ), __( 'One comment', 'bnm' ), __( '% comments', 'bnm' ) ) ?>
    :
    &#8220;<?=the_title( '', '', false )?>&#8221;</h3>

<ol class="commentlist">
    <?php foreach ( $comments as $comment ) : ?>
    <?php
    $authcomment  = ( $comment->user_id == 3 ) ? ' authcomment' : '';
    $classcomment = ( empty( $classcomment ) ) ? ( ( $authcomment == '' ) ? ' alt' : '' ) : ''; ?>
    <li rel="<?php echo $comment->user_id?>" class="<?php echo $classcomment; echo $authcomment ?>"
        id="comment-<?php comment_ID() ?>">
<!--        <div class="content-avatar">-->
<!--            <a target="_blank"-->
<!--               href="--><?php //echo (
//                   $comment->comment_author_url == "" ) ? 'http://www.gravatar.com' : $comment->comment_author_url ?><!--">-->
<!--                --><?php ////echo get_avatar( $comment->comment_author_email, "48" ) ?>
<!--            </a>-->
<!--        </div>-->
        <div class="comment-content">
            <small class="comment-date"><a
                href="#comment-<?php comment_ID() ?>"><?php echo get_comment_date( 'd M, Y' )?></a></small>
            <cite><?php echo get_comment_author_link() ?>:</cite><?php //edit_comment_link('Modifica', ' | ', ''); ?>
            <?php if ( $comment->comment_approved == '0' ) : ?>
            <em><?php _e( 'Your comment is wating to moderation', 'bnm' ) ?></em>
            <?php endif; echo comment_text() ?>
            <?php if ( comments_open() && $comment->comment_type != "trackback" && $comment->comment_type != "pingback"
        ) : ?>
            <div class="jqr2c_ul">
                <?php
                //				echo sprintf('<a href="javascript:jqr2c_reply(\'comment-%s\');">%s</a>', get_comment_ID(), __('Reply', 'bnm'));
                //				echo sprintf('<a href="javascript:jqr2c_quote(\'comment-%s\');">%s</a>', get_comment_ID(), __('Quote', 'bnm'));
                ?>
            </div>
            <?php endif; ?>
        </div>
    </li>
    <?php endforeach; /* end for each comment */ ?>
</ol>
<?php else : // this is displayed if there are no comments so far ?>

<h3 class="commenttitle"><?php _e( 'No comments for this post', 'bnm' ) ?></h3>
<?php if ( 'open' == $post->comment_status ) : ?>
    <?php else : // comments are closed ?>
    <p class="nocomments"><?php _e( 'Comment are closed', 'bnm' ) ?></p>
    <?php endif; ?>
<?php endif; ?>

<?php if ( 'open' == $post->comment_status ) : ?>

<h3 class="respond"><?php _e( 'Leave a comment', 'bnm' ); ?></h3>
<?php if ( get_option( 'comment_registration' ) && !$user_ID ) : ?>
    <?php
        /*
       <p><?php _e('You have to', 'bnm') ?> <a href="<?php echo get_option('siteurl') . '/wp-login.php?redirect_to=' . urlencode(get_permalink()) ?>">login</a> <?php _e('for leave a reply', 'bnm') ?></p>
       */
        ?>
    
	<p><?php _e( 'Login upper right to comment', 'bnm' ); ?></p>
    <?php else : ?>
    <div id="comment-form" class="round-border">
        <form action="<?php echo get_option( 'siteurl' ); ?>/wp-comments-post.php" method="post" id="commentform">
            <?php if ( $user_ID ) : ?>
            <p>
                <?php
                /*
                <?php _e( 'You are logged in as', 'bnm' ) ?>
                <a href="<?php echo
                    get_option( 'siteurl' ) . '/wp-admin/profile.php' ?>"><?php echo $user_identity?></a>
                */
                ?>
            </p>
            <?php else : ?>
            <p><input type="text" name="author" id="author" value="<?php echo $comment_author; ?>" size="22"
                      tabindex="1"/>
                <label for="author">
                    <small>Nome</small>
                </label></p>
            <p><input type="text" name="email" id="email" value="<?php echo $comment_author_email; ?>" size="22"
                      tabindex="2"/>
                <label for="email">
                    <small>Email</small>
                </label></p>
            <p><input type="text" name="url" id="url" value="<?php echo $comment_author_url; ?>" size="22"
                      tabindex="3"/>
                <label for="url">
                    <small>Sito web</small>
                </label></p>
            <?php endif; ?>
            <p><textarea name="comment" id="comment" cols="10" rows="10" tabindex="4"></textarea></p>

            <p class="aligncenter">
                <input class="button blue"
                       name="submit"
                       type="submit"
                       id="submit"
                       tabindex="5"
                       value="<?php _e( 'Send', 'bnm' ) ?>"/>
                <input type="hidden" name="comment_post_ID" value="<?php echo $id ?>"/>
            </p>
            <?php do_action( 'comment_form', $post->ID ); ?>
        </form>
    </div>
    <?php endif; ?>
<?php endif; ?>
</div>