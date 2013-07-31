<?php get_header(); ?>

<div class="page-wrap">

    <?php if ( have_posts() ) : ?>
    <div class="left sizeMedium">

        <article <?php post_class( 'archive-list content box white' ) ?>>

            <?php $post = $posts[0]; // Hack. Set $post so that the_date() works. ?>

            <h2><?php _e( 'Search results', 'bnm' ) ?></h2>

            <?php
            global $wp_query;
            if ( $wp_query->max_num_pages > 1 ) : ?>
                <div class="navigation">
                    <div class="next-posts"><?php next_posts_link( '&laquo; Indietro' ) ?></div>
                    <div class="prev-posts"><?php previous_posts_link( 'Avanti &raquo;' ) ?></div>
                </div>

                <br style="clear:both;margin-bottom:32px;display:block"/>
                <?php endif; ?>

            <?php while ( have_posts() ) : the_post(); ?>

            <h2 id="post-<?php the_ID(); ?>"><a href="<?php the_permalink() ?>"><?php the_title(); ?></a></h2>

            <div class="entry-content">
                <?php the_excerpt(); ?>
                <a class="button blue right" href="<?php the_permalink(); ?>"><?php _e( 'Continue', 'bnm' ) ?></a>
                <br class="clear"/>
            </div>

            <?php endwhile; ?>

            <?php
            global $wp_query;
            if ( $wp_query->max_num_pages > 1 ) : ?>
                <br style="clear:both;margin-bottom:32px;display:block"/>
                <div class="navigation">
                    <div class="next-posts"><?php next_posts_link( '&laquo; Indietro' ) ?></div>
                    <div class="prev-posts"><?php previous_posts_link( 'Avanti &raquo;' ) ?></div>
                </div>
                <?php endif; ?>

        </article>
    </div>

    <?php get_sidebar( 'search' ); ?>

    <?php else : ?>

    <div class="left sizeMedium">
        <article <?php post_class( 'archive-list content box white' ) ?>>
            <h2><?php _e( 'Search results', 'bnm' ) ?></h2>
            <p><?php _e( 'Nothing found', 'bnm' ) ?></p>
        </article>
    </div>

    <?php get_sidebar( 'search' ); ?>

    <?php endif; ?>

</div>

<?php get_footer();