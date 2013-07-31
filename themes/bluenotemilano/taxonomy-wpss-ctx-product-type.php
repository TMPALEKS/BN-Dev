<?php
/**
 * Lista di tutti i prodotti di una determinata categoria (tassionomia).
 *
 * @package         Blue Note Milano
 * @subpackage      taxonomy-wpss-ctx-product-type
 * @author          =undo= <g.fazioli@undolog.com>, <g.fazioli@saidmade.com>
 * @copyright       Copyright (c) 2012 Saidmade Srl.
 * @link            http://www.saidmade.com
 * @created         19/01/12
 * @version         1.0.0
 *
 */

get_header(); ?>

<div class="page-wrap">

    <?php if ( have_posts() ) : ?>
    <div class="left sizeMedium">

        <article <?php post_class( 'archive-list content box white' ) ?> >

            <h2><?php $term = single_cat_title( '', false ); echo apply_filters('the_category', $term); ?></h2>

            <?php
            /* Recupero le stesse impostazioni della vetrina */

            $args               = WPXSmartShop::settings()->settings( 'product_card' );
            $args['appearance'] = false;
            $args['variants']   = false;
            $args['product_types'] = false;
            ?>

            <div class="wpss-showcase wpxss-product-type clearfix">
                <?php while ( have_posts() ) : the_post(); ?>

                <?php echo WPXSmartShopProduct::card( get_the_ID(), $args ); ?>

                <?php endwhile; ?>
            </div>

            <div class="bnm-back-to-store clear">
                <a class="button blue" href="<?php echo WPSmartShopShowcase::permalinkShowcase() ?>"><?php _e( 'Back to Store', 'bnm' ); ?></a>
            </div>

            <br class="clear"/>

        </article>

    </div>

    <?php get_sidebar( 'product' ); ?>

    <?php else : ?>

    <h2><?php _e( 'Product Type Not Found!', 'wp-smartshop' ) ?></h2>

    <?php endif; ?>

</div>

<?php get_footer();