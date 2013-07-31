<?php
/**
 * Template Name: Profilo Utente
 *
 * Gestione del profilo di un utente
 *
 * @package         Blue Note Milano
 * @subpackage      page-template-profile
 * @author          =undo= <g.fazioli@undolog.com>, <g.fazioli@saidmade.com>
 * @copyright       Copyright (C)2012 Saidmade Srl.
 * @created         12/01/12
 * @version         1.0
 *
 */

/*
 * Un normale utente, un utente che non sia l'utente con id 1 Amministratore, puà vedere solo il suo profilo
 */

// Utente loggato
if ( is_user_logged_in() ) {
    $id_user_logged_in = get_current_user_id();
    if ( BNMExtendsUser::isSu() ) {
        if ( isset( $_GET['id'] ) ) {
            $id_user_logged_in = absint( $_GET['id'] );
        } else if ( isset( $_GET['uniqid'] ) ) {
            $user              = BNMExtendsUser::userConfirmedWithUniqID( $_GET['uniqid'] );
            $id_user_logged_in = $user->id_user;
        }
    }
    $id_user = $id_user_logged_in;
    $fields  = BNMExtendsUser::fields( $id_user );
} else {
    wp_redirect( '/' );
}

get_header(); ?>

<div class="page-wrap">

    <?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
    <div class="left sizeMedium">
        <article class="content box white" id="post-<?php the_ID(); ?>">
            <h2 class="entry-title"><?php the_title(); ?></h2>

            <?php if ( is_user_logged_in() && WPDKUser::hasCap( 'bnm_cap_offline') ) : ?>
            <div class="left">
                <a href="<?php echo BNMExtends::pagePermalinkWithSlug('summary') ?>" class="button blue"><?php _e('Summary', 'bnm') ?></a>
                <a href="<?php echo BNMExtends::pagePermalinkWithSlug('box-office-placeholder') ?>" class="button blue"><?php _e('Placeholder', 'bnm') ?></a>
            </div>
            <?php endif; ?>

            <div class="right">
                <a href="<?php echo BNMExtends::pagePermalinkWithSlug('ordini') ?>" class="button blue"><?php _e('Your orders', 'bnm') ?></a>
                <a href="<?php echo BNMExtends::pagePermalinkWithSlug('coupons') ?>" class="button blue"><?php _e('Your coupons', 'bnm') ?></a>
            </div>

            <br class="clear"/>

            <?php the_content(); ?>


            <?php if ( WPDKForm::isNonceVerify( 'profile' ) ) : ?>
            <?php
            $id_update_user = absint( $_POST['id_user'] );
            BNMExtendsUser::updateUser( $id_update_user );
            $fields = BNMExtendsUser::fields( $id_update_user ); ?>

            <div class="bnm-update"><?php _e( 'Your profile has been updated successfully', 'bnm' ); ?></div>
            <?php endif; ?>

            <?php if ( BNMExtendsUser::isSu() ) : ?>
            <form name="bnm-edit-user" id="bnm-edit-user" method="get" action="" autocomplete="off">
                <input type="hidden" name="id" id="id"/>
            </form>
            <?php endif; ?>

            <form class="bnm-profile" name="bnm-profile" method="post" action="" enctype="multipart/form-data">

                <?php WPDKForm::nonceWithKey( 'profile' ) ?>
                <?php WPDKForm::htmlForm( $fields ); ?>

                <p class="alignright">
                    <input type="submit" class="button blue" value="<?php _e( 'Save', 'bnm' ) ?>"/>
                </p>
            </form>

            <br class="clear"/>
        </article>
    </div>
    <?php get_sidebar( 'register' ); ?>
    <?php endwhile; endif; ?>

</div>


<?php get_footer();