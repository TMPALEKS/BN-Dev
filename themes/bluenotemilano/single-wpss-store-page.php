<?php
/**
 * Template per le pagine di tipo Store Page di WP Smart Shop
 *
 * @package            Blue Note Milano
 * @subpackage         single-wpss-store-page.php
 * @author             =undo= <g.fazioli@undolog.com>, <g.fazioli@saidmade.com>
 * @copyright          Copyright (C)2011 Saidmade Srl.
 * @created            01/12/11
 * @version            1.0
 *
 */

get_header(); ?>

<div class="page-wrap">

	<?php if (have_posts()) : while (have_posts()) : the_post(); ?>

	<div class="left sizeLarge">
		<article class="content box white" id="post-<?php the_ID(); ?>">

			<h2 class="entry-title"><?php the_title(); ?></h2>

			<?php the_content(); ?>

			<br class="clear" />
		</article>
	</div>

	<?php //get_sidebar(); ?>

	<?php //comments_template(); ?>

	<?php endwhile; endif; ?>
</div>
<?php get_footer(); ?>