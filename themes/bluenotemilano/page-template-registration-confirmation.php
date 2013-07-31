<?php
/**
 * Template Name: Conferma Registrazione
 *
 * Pagina conferma registrazione
 *
 * @package         Blue Note Milano
 * @subpackage      page-template-registration-confirmation
 * @author          =undo= <g.fazioli@undolog.com>, <g.fazioli@saidmade.com>
 * @copyright       Copyright (C)2011 Saidmade Srl.
 * @created         12/12/11
 * @version         1.0
 *
 */

/*
 * Prima di tutto verifico che sia stato passato in GET il parametro id, senza di quello non si
 * può fare nulla
 */

if ( !isset( $_GET['id'] ) ) {

    $url = '/';
    if ( function_exists( 'icl_get_home_url' ) ) {
        $url = icl_get_home_url();
    }
    wp_redirect( $url );

} elseif( !is_user_logged_in() ) {
    /* Ok, ho l'iD verifico che esista uno user in stato 'pending' */
    $uniqID = $_GET['id'];
    if ( ( $info = BNMExtendsUser::userPendingWithUniqID( $uniqID ) ) ) {
        $idUser = BNMExtendsUser::addUser( $info );

        /* Controlla se l'utente aveva richiesto la registrazione alla Newsletter */
        if ( BNMExtendsUser::shouldUserRegisterNewsletter( $uniqID ) ) {
            BNMExtendsWidgetNewsletter::storeAddress( $info->email );
        }

//        $url = BNMExtends::pagePermalinkWithSlug( 'conferma-registrazione', kBNMExtendsSystemPagePostTypeKey );
//        wp_redirect( $url );

    } else {

        /* Messaggio di errore */
        $url = BNMExtends::pagePermalinkWithSlug( 'error-registration', kBNMExtendsSystemPagePostTypeKey );
        wp_redirect( $url );
        exit;
    }
}

get_header(); ?>

<div class="page-wrap">

    <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
    <div class="left sizeMedium">
        <article class="content box white" id="post-<?php the_ID(); ?>">
            <h2 class="entry-title"><?php the_title(); ?></h2>
            <?php the_content(); ?>

            <br class="clear"/>
        </article>
    </div>
    <?php get_sidebar('register'); ?>
    <?php endwhile; endif; ?>

</div>

<?php get_footer();