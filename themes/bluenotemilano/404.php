<?php get_header(); ?>

<div class="page-wrap">


    <div class="left sizeMedium">

        <article class="content box white">

            <div class="title-blog left">
                <h2 class="entry-title"><?php _e('Page not found', 'bnm') ?></h2>
            </div>
            <br class="clear"/>

            <?php echo wpdk_content_page_with_slug('pagina-non-trovata', kBNMExtendsSystemPagePostTypeKey, 'page-not-found') ?>

            <br class="clear"/>

        </article>

    </div>

    <?php get_sidebar(); ?>

</div>
<?php get_footer();