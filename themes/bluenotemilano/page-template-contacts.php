<?php
/**
 * Template name: Contatti
 *
 * Pagina dei contatti
 *
 * @package            Blue Note Milano
 * @subpackage         page-template-contacts
 * @author             =undo= <g.fazioli@undolog.com>, <g.fazioli@saidmade.com>
 * @copyright          Copyright (C)2011 Saidmade Srl.
 * @created            07/12/11
 * @version            1.0
 *
 */

get_header(); ?>

<div class="page-wrap">

    <?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
    <div class="left sizeMedium">
        <article class="content box white" id="post-<?php the_ID(); ?>">
            <h2 class="entry-title"><?php the_title(); ?></h2>
            <?php the_content(); ?>

            <?php /* Google Maps API v3 */ ?>
            <div id="map_canvas"></div>

            <form class="wpdk-form" name="" method="post" action="">
                <p class="alignright"><a class="button blue" target="_blank" href="http://goo.gl/maps/EpS2"><?php _e('More on Google Maps', 'bnm') ?></a></p>
            </form>

            <?php BNMExtendsContacts::contacts() ?>

            <br class="clear"/>
        </article>
    </div>
    <?php get_sidebar( 'Single Page Contatti' ); ?>
    <?php endwhile; endif; ?>

</div>


<?php get_footer();