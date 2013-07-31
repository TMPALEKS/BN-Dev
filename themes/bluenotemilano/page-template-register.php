<?php
/**
 * Template Name: Registrazione
 *
 * Pagina registrazione
 *
 * @package            Blue Note Milano
 * @subpackage         page-template-register
 * @author             =undo= <g.fazioli@undolog.com>, <g.fazioli@saidmade.com>
 * @copyright          Copyright (C)2011 Saidmade Srl.
 * @created            07/12/11
 * @version            1.0
 *
 */

/* Se l'utente è loggato non può registrarsi */
if ( is_user_logged_in() ) {
    wp_redirect( '/' );
}

get_header(); ?>

<div class="page-wrap">

    <?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
    <div class="left sizeMedium">
        <article class="content box white" id="post-<?php the_ID(); ?>">
            <h2 class="entry-title"><?php the_title(); ?></h2>
            <?php the_content(); ?>

            <?php $fields = BNMExtendsUser::fields(); ?>

            <?php if ( WPDKForm::isNonceVerify( 'registration' ) ) : ?>
            <?php
            $result = BNMExtendsUser::addUserTemporary();
            if ( $result !== false ) : ?>
                <?php BNMExtendsUser::emailForConfirm( $result ) ?>

                <div class="bnm-update">
                    <?php _e( 'Your registration request has been successfully sent. Please, check your email and follow the link to complete your registration.', 'bnm' ); ?>
                </div>

                <?php else : ?>

                <h3><?php _e( 'User already register', 'bnm' ) ?></h3>

                <?php endif; ?>

            <?php else : ?>
            <form class="bnm-profile" name="" method="post" action="" enctype="multipart/form-data">

                <?php WPDKForm::nonceWithKey( 'registration' ) ?>
                <?php WPDKForm::htmlForm( $fields ); ?>

                <p class="alignright">
                    <input type="submit" class="button orange" value="<?php _e( 'Register', 'bnm' ) ?>"/>
                </p>
            </form>

            <?php endif; ?>

            <br class="clear"/>
        </article>
    </div>
    <?php get_sidebar( 'register' ); ?>
    <?php endwhile; endif; ?>

</div>

<?php get_footer();