<?php
/**
 * Pagina singola per il Blog
 *
 * @package            Blue Note Milano
 * @subpackage         single.php
 * @author             =undo= <g.fazioli@undolog.com>, <g.fazioli@saidmade.com>
 * @copyright          Copyright (C)2011 Saidmade Srl.
 * @created            23/01/12
 * @version            1.0
 *
 */
get_header(); ?>

<div class="page-wrap">

    <?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>

    <div class="left sizeMedium">

        <article class="content entry-content box white" id="post-<?php the_ID(); ?>">

            <h2 class="entry-title"><?php the_title(); ?></h2>
            <ul class="meta-info">
                <li class="date-time"><span><?php the_time( 'j F Y' ) ?></span></li>
                <li class="categories"><span><?php echo get_the_category_list( ', ' ) ?></span></li>
                <li class="comments"><span><a href="<?php the_permalink() ?>#comments"><?php //echo get_comments_number() ?>
                    <?php comments_number( __( 'No comment', 'bnm' ), __( 'One comment', 'bnm' ), __( '% comments', 'bnm' ) ) ?></a></span></li>
            </ul>

            <?php if(has_post_thumbnail()) the_post_thumbnail(kBNMExtendsThumbnailSizeLargeKey) ?>
            <?php the_content(); ?>

            <br style="clear:both" />

            <div class="bnm-sociable-post clearfix">
                <?php bnm_social() ?>
            </div>

            <?php comments_template(); ?>

            <br class="clear"/>

        </article>

    </div>

    <?php get_sidebar(); ?>

    <?php endwhile; endif; ?>
</div>
<?php get_footer();