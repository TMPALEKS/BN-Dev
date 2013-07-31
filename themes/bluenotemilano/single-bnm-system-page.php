<?php
/**
 * Template per i messaggi di sistema
 *
 * @package         Blue Note Milano HTML5 Theme
 * @subpackage      single-bnm-system-page
 * @author          =undo= <g.fazioli@undolog.com>, <g.fazioli@saidmade.com>
 * @copyright       Copyright (C)2011 Saidmade Srl.
 * @created         12/12/11
 * @version         1.0
 *
 */

get_header(); ?>

<div class="page-wrap">

    <?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>

    <div class="left sizeMedium">
        <article class="content box white" id="post-<?php the_ID(); ?>">

            <h2 class="entry-title"><?php the_title(); ?></h2>

            <?php the_content(); ?>

            <br class="clear"/>
        </article>
    </div>

    <?php get_sidebar(); ?>

    <?php //comments_template(); ?>

    <?php endwhile; endif; ?>
</div>
<?php get_footer();