<?php get_header(); ?>

<div class="page-wrap">

    <?php if ( have_posts() ) : ?>
    <div class="left sizeMedium">

        <article <?php post_class( 'archive-list content box white' ) ?>>

            <?php $post = $posts[0]; // Hack. Set $post so that the_date() works. ?>

            <?php /* If this is a category archive */ if ( is_category() ) { ?>
            <h2><?php single_cat_title(); ?></h2>

            <?php /* If this is a tag archive */
        } elseif ( is_tag() ) {
            ?>
            <h2><?php _e( 'Post with Tag', 'bnm' ); ?> &#8216;<?php single_tag_title(); ?>&#8217;</h2>

            <?php /* If this is a daily archive */
        } elseif ( is_day() ) {
            ?>
            <h2><?php _e( 'Archive', 'bnm' ); ?> <?php the_time( 'F jS, Y' ); ?></h2>

            <?php /* If this is a monthly archive */
        } elseif ( is_month() ) {
            ?>
            <h2><?php _e( 'Archive', 'bnm' ); ?> <?php the_time( 'F, Y' ); ?></h2>

            <?php /* If this is a yearly archive */
        } elseif ( is_year() ) {
            ?>
            <h2 class="pagetitle"><?php _e( 'Archive', 'bnm' ); ?> <?php the_time( 'Y' ); ?></h2>

            <?php /* If this is an author archive */
        } elseif ( is_author() ) {
            ?>
            <h2 class="pagetitle"><?php _e( 'Author\'s Archive', 'bnm' ); ?></h2>

            <?php /* If this is a paged archive */
        } elseif ( isset( $_GET['paged'] ) && !empty( $_GET['paged'] ) ) {
            ?>
            <h2 class="pagetitle"><?php _e( 'Archives', 'bnm' ); ?></h2>

			<?php } ?>

			<?php
				global $wp_query;
			if ( $wp_query->max_num_pages > 1 ) : ?>
				<div class="navigation">
					<div class="next-posts"><?php next_posts_link('&laquo; Precedenti') ?></div>
					<div class="prev-posts"><?php previous_posts_link('Successivi &raquo;') ?></div>
				</div>

				<br style="clear:both;margin-bottom:32px;display:block"/>
				<?php endif; ?>

			<?php while (have_posts()) : the_post(); ?>

			<h2 id="post-<?php the_ID(); ?>"><a href="<?php the_permalink() ?>"><?php the_title(); ?></a></h2>

			<div class="entry-content">
                <?php the_post_thumbnail('thumbnail') ?>
                <?php the_content( ' [...]' ); ?>
                <p class="right"><a class="button blue" href="<?php the_permalink(); ?>"><?php _e('Continue', 'bnm') ?></a></p>
				<br class="clear" />
			</div>

			<?php endwhile; ?>

			<?php
				global $wp_query;
				if ($wp_query->max_num_pages > 1) : ?>
				<br style="clear:both;margin-bottom:32px;display:block"/>
				<div class="navigation">
					<div class="next-posts"><?php next_posts_link('&laquo; Precedenti') ?></div>
					<div class="prev-posts"><?php previous_posts_link('Successivi &raquo;') ?></div>
				</div>
			<?php endif; ?>

		</article>
	</div>

	<?php get_sidebar('archive'); ?>

	<?php else : ?>

	<h2><?php _e( 'Nothing found', 'bnm' ); ?></h2>

	<?php endif; ?>

</div>

<?php get_footer(); ?>