<?php
/**
 * Template Name: Coupons
 *
 * @package         Blue Note Milano
 * @subpackage      page-template-coupons
 * @author          =undo= <g.fazioli@undolog.com>, <g.fazioli@saidmade.com>
 * @copyright       Copyright (c) 2012 Saidmade Srl.
 * @link            http://www.saidmade.com
 * @created         07/05/12
 * @version         1.0.0
 *
 */

// Utente loggato
if ( is_user_logged_in() ) {
    $id_user_logged_in = get_current_user_id();
} else {
    wp_redirect( '/' );
}

get_header(); ?>

<div class="page-wrap">

    <?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
    <div class="left sizeMedium">
        <article class="content box white" id="post-<?php the_ID(); ?>">
            <h2 class="entry-title"><?php the_title(); ?></h2>

            <div class="right">
                <a href="<?php echo BNMExtends::pagePermalinkWithSlug('profile') ?>" class="button blue"><?php _e('Your profile', 'bnm') ?></a>
                <a href="<?php echo BNMExtends::pagePermalinkWithSlug('ordini') ?>" class="button blue"><?php _e('Your orders', 'bnm') ?></a>
            </div>

            <br class="clear"/>

            <?php the_content(); ?>

            <?php echo WPXSmartShopFrontend::coupons( $id_user_logged_in, '', '' ) ?>

            <br class="clear"/>
        </article>
    </div>
    <?php get_sidebar( 'register' ); ?>
    <?php endwhile; endif; ?>

</div>


<?php get_footer();