<?php
/**
 * Template Name: Invoice
 *
 * @package         Blue Note Milano
 * @subpackage      page-template-invoice
 * @author          =undo= <g.fazioli@undolog.com>, <g.fazioli@saidmade.com>
 * @copyright       Copyright (c) 2012 Saidmade Srl.
 * @link            http://www.saidmade.com
 * @created         16/07/12
 * @version         1.0.0
 *
 */

/* Utente loggato */
if ( is_user_logged_in() ) {
    $id_user_logged_in = get_current_user_id();

    /* Recupero ID dell'ordine da mostrare */
    if ( isset( $_GET['id_order'] ) ) {
        $id_order = absint( esc_attr( $_GET['id_order'] ) );
        if ( empty( $id_order ) ) {
            wp_redirect( '/' );
        }
    }
} else {
    wp_redirect( '/' );
}

get_header(); ?>

<div class="page-wrap">

    <?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
    <div class="left sizeLarge">
        <article class="content box white" id="post-<?php the_ID(); ?>">
            <h2 class="entry-title"><?php the_title(); ?></h2>

            <br class="clear"/>

            <p class="alignleft">
                <a href="<?php echo BNMExtends::pagePermalinkWithSlug('ordini') ?>" class="button blue"><?php _e('Your orders', 'bnm') ?></a>
            </p>

            <?php the_content(); ?>

            <?php echo WPXSmartShopInvoice::invoice( $id_order ) ?>

            <?php
            $language_code = ( ICL_LANGUAGE_CODE == 'it' ) ? '' : trailingslashit( ICL_LANGUAGE_CODE );
            $print_url     = sprintf( '/%s%s/', $language_code, __( 'print', 'bnm' ) );
            printf( '<p class="aligncenter"><a class="button blue" target="blank" href="%s?invoice&id_order=%s">%s</a></p>', $print_url, $id_order, __( 'Print', 'bnm' ) );
            ?>

            <br class="clear"/>
        </article>
    </div>
    <?php //get_sidebar( 'register' ); ?>
    <?php endwhile; endif; ?>

</div>


<?php get_footer();