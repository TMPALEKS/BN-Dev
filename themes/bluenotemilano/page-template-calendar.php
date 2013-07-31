<?php
/**
 * Template Name: Programmazione
 *
 * Pagina Programmazione / Calendario
 *
 * @package         Blue Note Milano HTML5 Theme
 * @subpackage      page-template-calendar
 * @author          =undo= <g.fazioli@saidmade.com>
 * @copyright       Copyright © 2010-2011 Saidmade Srl
 *
 * Questa pagina deve permettere di:
 *
 * - Visualizzare la programmazione in modalità elenco
 * - Visualizzare la programmazione in modalità calendario
 * - Lista per Mese
 * - Filtri di ricerca: titolo e date
 *
 */

/* Download iCal */
if(isset($_GET['ical'])) {
    BNMExtendsCalendar::downloadiCal();
    exit;
}

/* Download PDF */
if(isset($_GET['pdf'])) {
    BNMExtendsCalendar::downloadPDF();
    exit;
}

/* Print */
if(isset($_GET['print'])) {
    BNMExtendsCalendar::printing();
    exit;
}

get_header(); ?>

<div class="page-wrap box blue">

    <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
    <article class="content box blue" id="post-<?php the_ID(); ?>">
        <h2 class="entry-title"><?php the_title(); ?> <?php echo BNMExtendsCalendar::title() ?></h2>

        <?php the_content() ?>

        <?php BNMExtendsCalendar::toolBar() ?>
        <?php BNMExtendsCalendar::navigation() ?>
        <?php BNMExtendsCalendar::calendar() ?>

        <br class="clear" />

    </article>
    <?php //get_sidebar('page'); ?>
    <?php endwhile; endif; ?>

</div>


<?php get_footer();