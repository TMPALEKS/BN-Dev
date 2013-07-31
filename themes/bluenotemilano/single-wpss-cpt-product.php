<?php
/**
 * Questo è il template Single usato dai Post Custom Type Product
 *
 * @package          Blue Note Milano HTML5 Theme
 * @subpackage       single-event
 * @author           =undo= <g.fazioli@saidmade.com>
 * @copyright        Copyright © 2010-2011 Saidmade Srl
 *
 */

get_header(); ?>
<div class="page-wrap">

    <?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>

    <?php $id_product = absint( get_the_ID() ) ?>

    <div class="left sizeMedium">
        <article class="content box white" id="post-<?php the_ID(); ?>">

            <h2 class="entry-title"><?php the_title(); ?></h2>

            <?php
                $thumbnail = WPXSmartShopProduct::thumbnail( $id_product, kWPSmartShopThumbnailSizeLargeKey );
                $full      = WPXSmartShopProduct::thumbnailSrc( $id_product, 'full' );
                $thumbnail = sprintf( '<a href="%s" title="%s" class="wpss-product-card-thumbnail-enlarge %s">%s</a>', $full, get_the_title(), 'fancybox', $thumbnail );
                echo $thumbnail;
                ?>

            <div class="bnm-evemt-ticket bnm-single-product">
                <?php
                $rows_card = array(
                    'html_appearance',
                    'html_product_types',
                    'html_thumbnail',
                    'open_link',
                    'html_title',
                    'html_excerpt',
                    'close_link',
                    'html_price',
                    'html_display_permalink_button',
                    'html_content',
                );

                $args = array(
                    'rows_card'                  => $rows_card,
                    'thumbnail'                  => false,
                    'permalink'                  => false,
                    'content'                    => false,
                    'excerpt'                    => false,
                    'title'                      => false,
                    'product_types'              => false,
                    'product_types_tree'         => false,
                    'price'                      => false,
                    'label_price'                => __( 'Purchase', 'bnm' ),
                    'exclude_variants'           => array( 'weight' ),
                );

                add_filter( 'wpss_cart_add_to_cart_button_label', array( 'BNMExtends', 'wpss_cart_add_to_cart_button_label'), 10, 3 );

                echo WPXSmartShopProduct::card( $id_product, $args );
                ?>
            </div>

            <?php the_content(); ?>

            <div class="bnm-back-to-store clear">
                <a class="button blue" href="<?php echo WPSmartShopShowcase::permalinkShowcase() ?>"><?php _e( 'Back to Store', 'bnm' ); ?></a>
            </div>

            <br class="clear"/>

            <div class="bnm-sociable-post clearfix">
                <?php bnm_social() ?>
            </div>

        </article>
    </div>

    <?php get_sidebar( 'product' ); ?>

    <?php endwhile; endif; ?>
</div>
<?php get_footer();