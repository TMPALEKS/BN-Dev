<?php get_header(); ?>

<div class="page-wrap">

    <?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
    <div class="left sizeMedium">
        <article class="content box white" id="page-<?php the_ID(); ?>">
            <h2 class="entry-title"><?php the_title(); ?></h2>
            <div id="accordion" class="clearfix"><?php the_content(); ?></div>
            <br class="clear"/>
        </article>
    </div>
    <?php get_sidebar( 'page' ); ?>
    <?php endwhile; endif; ?>

</div>


<?php get_footer();