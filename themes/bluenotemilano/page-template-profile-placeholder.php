<?php
/**
 * Template Name: Profile Placeholder
 *
 * @package         Blue Note Milano
 * @subpackage      page-template-invoice
 * @author          =undo= <g.fazioli@undolog.com>, <g.fazioli@saidmade.com>
 * @copyright       Copyright (c) 2012 Saidmade Srl.
 * @link            http://www.saidmade.com
 * @created         17/07/12
 * @version         1.0.0
 *
 */

global $wpdb;
global $wp_roles;
/* Utente loggato e Box Office? */
if ( ! (is_user_logged_in() && WPDKUser::hasCap( 'bnm_cap_offline' ) ) ) {

    wp_redirect( '/' );
}
    get_header();
?>
<div class="page-wrap">

    <?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
    <div class="left sizeLarge">
        <article class="content box white" id="post-<?php the_ID(); ?>">

            <?php
            /* Catch filtro spettacolo/prodotto  */
            $product = null;
            $id_product = null;
            if ( isset( $_POST['id_product'] ) && !empty( $_POST['id_product'] ) ) :
                $id_product = absint( $_POST['id_product'] );
                $product    = get_post( $id_product );
            else:
                ?><h2 class="entry-title"><?php the_title(); ?></h2><?php
            endif;
            ?>


            <?php the_content(); ?>

            <?php

            $roles      = $wp_roles->get_names();
            $discountID = BNMExtendsSummaryOrder::discountIDs();

            /**
             * Elaborazione personalizzata per BlueNote a partire dalla tabella delle statistiche
             */

            /* Form per il filtro sullo spettacolo */

            $sdf = array(
                __( 'Seleziona uno spettacolo', 'bnm' )   => array(
                    array(
                        'type'    => WPDK_FORM_FIELD_TYPE_TEXT,
                        'name'    => 'wpph_product_title',
                        'id'      => 'wpph_product_title',
                        'data'    => array(
                            'autocomplete_action'   => 'bnm_action_product_title',
                            'autocomplete_target'   => 'id_product'
                        ),
                        'size'    => 64,
                        'label'   => __( 'Spettacolo', 'bnm' ),
                        'value'   => !is_null( $product ) ? $product->post_title : ''
                    ),
                    array(
                        'type'    => WPDK_FORM_FIELD_TYPE_HIDDEN,
                        'name'    => 'id_product',
                        'value'   => !is_null( $product ) ? $product->ID : ''
                    ),
                    array(
                        'type'    => WPDK_FORM_FIELD_TYPE_HIDDEN,
                        'name'    => 'wpph_date_start_filter',
                        'id'      => 'wpph_date_start_filter',
                        'value'   => $_POST['wpph_date_start_filter'] ? $_POST['wpph_date_start_filter'] : "",
                    ),
                    array(
                        'type'    => WPDK_FORM_FIELD_TYPE_HIDDEN,
                        'name'    => 'wpph_date_expiry_filter',
                        'id'      => 'wpph_date_expiry_filter',
                        'value'    => $_POST['wpph_date_expiry_filter'] ? $_POST['wpph_date_expiry_filter'] : "",
                    ),
                    array(
                        'type'    => WPDK_FORM_FIELD_TYPE_SUBMIT,
                        'name'    => 'filter',
                        'value'   => 'Mostra',
                        'class'   => 'button blue'
                    ),
                )
            );

            $dialog =  array(
                __( 'Dati Prenotazione', 'bnm' )   => array(
                    __( 'Search user By Email or name', 'bnm' ),
                    array(
                        array(
                            'type'    => WPDK_FORM_FIELD_TYPE_TEXT,
                            'name'    => 'wpxph_stats_filter_user',
                            'id'      => 'wpxph_stats_filter_user',
                            'class'   => 'wpdk-form-input',
                            'data'    => array(
                                'autocomplete_action'   => 'wpdk_action_user_by',
                                'autocomplete_target'   => 'wpxph_stats_filter_id_user'
                            ),
                            'label' => __( 'Reservation by', 'bnm' ),
                            'size'  => 95,
                        ),
                        /*
                        array(
                            'type'    => WPDK_FORM_FIELD_TYPE_HIDDEN,
                            'name'    => 'id_user_order',
                        ), */
                        array(
                            'type'    => WPDK_FORM_FIELD_TYPE_HIDDEN,
                            'name'    => 'table_selected-' . $id_product,
                            'class'   => 'wpph_product_id',
                            'id'      => 'table_selected',
                            'value'   => $id_product
                        ),
                        array(
                            'type'    => WPDK_FORM_FIELD_TYPE_HIDDEN,
                            'name'    => 'wpxph_stats_filter_id_user',
                            'id'      => 'wpxph_stats_filter_id_user'
                        ),
                        array(
                            'type'    => WPDK_FORM_FIELD_TYPE_HIDDEN,
                            'name'    => 'wpxph_order_id',
                            'id'      => 'wpxph_order_id'
                        )

                     ),
                    array(
                        array(
                            'type'    => WPDK_FORM_FIELD_TYPE_TEXTAREA,
                            'name'    => 'wpph_note',
                            'class'   => 'wpdk-form-textarea',
                            'title'   => 'Note',
                            'rows'    => 5,
                            'cols'    => 50,
                            'label'   => __( 'Note', 'bnm' )
                        ),
                    )
                )
            );

            ?>
            <br class="clear"/>
            <form id="wpph_search_product" name="wpph_search_product" method="post">
                <?php WPDKForm::htmlForm( $sdf ); //Form ricerca spettacolo ?>
            </form>
            <div id="edit-placeholder">
                <form id="wpph_placeholder_booking" name="wpph_placeholder_booking" method="post">
                    <?php WPDKForm::htmlForm( $dialog ); //Form Prenotazione ?>
                    <fieldset class="wpdk-form-fieldset wpdk-form-section3 clear">
                        <legend>Tavoli da prenotare</legend>
                        <ul id="placeholder-reservation-table-number">
                           <!-- Elenco tavoli che saranno prenotati -->
                        </ul>
                    </fieldset>

                </form>
            </div>
            <?php
                $title = isset($_POST['wpph_product_title']) ? $_POST['wpph_product_title'] : "";

                if ($id_product){
                    echo BNMExtendsPlaceHolder::renderEnvironmentForBoxOffice($title, $id_product);
                   // BNMExtendsPlaceHolder::summary( $id_product );
                }

            ?>
<input id="product_id" type="hidden" value="<?php echo $id_product ?>" />
<table id="placeholder-summary">
    <thead>
    <tr>
        <th>Q.tà</th>
        <th>Utente</th>
        <th>Posti</th>
        <th>Email</th>
        <th>Id Ordine</th>
        <th>Note</th>
        <th>Azioni</th>
    </tr>
    </thead>

    <tbody>
    </tbody>
</table>

            <div class="clear"></div>
     <!-- Closing -->
        </article>

    </div>
    <?php //get_sidebar( 'register' ); ?>
    <?php endwhile; endif; ?>

</div>


<?php get_footer(); ?>