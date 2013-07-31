<?php
/**
 * Questo è il template Single usato dai Post Custom Type Artista
 *
 * @package          Blue Note Milano HTML5 Theme
 * @subpackage       single-artist
 * @author           =undo= <g.fazioli@saidmade.com>
 * @copyright        Copyright © 2010-2011 Saidmade Srl
 *
 */

get_header(); ?>
<div class="page-wrap">

    <?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
    <div class="left sizeMedium">
        <article class="content box white" id="post-<?php the_ID(); ?>">

            <h2 class="entry-title"><?php the_title(); ?></h2>

            <?php BNMExtendsArtistPostType::thumbnail( $post_id, kBNMExtendsThumbnailSizeLargeKey ); ?>
            <?php the_content(); ?>

            <h3><?php //_e( 'Show in Calendar', 'bnm' ) ?></h3>

            <?php

            /* Elenco degli id degli Eventi a cui ha partecipato, partecipa o parteciperà questo artista */
            //$sql               = sprintf( 'SELECT `id_event` FROM `%s` WHERE `id_artist` = %s', BNMExtendsEventPostType::tableName(), $post_id );
            //$artistEventsArray = $wpdb->get_col( $sql );

            if ( count( $artistEventsArray ) > 0 ) :

                $args   = array(
                    'post_status' => 'publish',
                    'post__in'    => $artistEventsArray,
                    'post_type'   => kBNMExtendsEventPostTypeKey,
                    'meta_key'    => kBNMExtendsEventMetaDateAndTime,
                    'orderby'     => 'meta_value',
                    'order'       => 'DESC'
                );

                $events = get_posts( $args );

                ?>

                <ul class="events artist"><?php
                    foreach ( $events as $event ) :
                        $date     = get_post_meta( $event->ID, kBNMExtendsEventMetaDateAndTime, true );
                        /* @todo Da sanitizzare - c'era bnm_plainToTime() */
                        $inTime   = "******** {$date} ********";
                        $id_event = defined( 'ICL_LANGUAGE_CODE' ) ? icl_object_id( $event->ID, kBNMExtendsEventPostTypeKey, true, ICL_LANGUAGE_CODE ) : $event->ID;
                        ?>
                        <li>
				<span
                    class="dayName <?php echo ( date( 'w', $inTime ) ==
                        '0' ? 'sunday' : '' ) ?>"><?php _e( date( 'l', $inTime ) ) ?> <?php echo date( 'j', $inTime ) ?> <?php _e( date( 'F', $inTime ) ) ?> <?php echo date( 'Y', $inTime ) ?></span>

                            <table border="0" cellpadding="0" cellspacing="0">
                                <tbody>
                                <tr>
                                    <td width="100%">
                                        <div class="eventBox">
                                            <h3><?php echo get_the_title( $id_event ) ?></h3>
                                        </div>
                                    </td>
                                    <td>
                                        <a class="accessor" style="margin:4px 8px 0 0"
                                           href="<?php echo get_permalink( $id_event ); ?>">Detail</a></td>
                                </tr>
                                </tbody>
                            </table>
                        </li>

                        <?php endforeach; ?>
                </ul>
                <?php endif; ?>
            <br class="clear"/>
        </article>
    </div>
    <?php get_sidebar(); ?>

    <?php endwhile; endif; ?>
</div>
<?php get_footer();