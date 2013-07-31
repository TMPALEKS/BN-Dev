<?php
/**
 * Classe per gestire le viste e funzionalità del calendario eventi e programmazione
 *
 * @package            BNMExtends
 * @subpackage         BNMExtendsCalendar.php
 * @author             =undo= <g.fazioli@saidmade.com>
 * @copyright          Copyright (C)2011-2012 Saidmade Srl.
 * @created            21/12/11
 * @version            1.0
 *
 */

class BNMExtendsCalendar {


    // -----------------------------------------------------------------------------------------------------------------
    // Static values
    // -----------------------------------------------------------------------------------------------------------------

    // -----------------------------------------------------------------------------------------------------------------
    // WordPress filters
    // -----------------------------------------------------------------------------------------------------------------

    public static function posts_fields( $fields ) {
        global $wpdb;
        $fields .= sprintf( ', %s.meta_key, %s.meta_value ', $wpdb->postmeta, $wpdb->postmeta );
        return ( $fields );
    }

    // -----------------------------------------------------------------------------------------------------------------
    // Home page
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Calendario compatto in Home Page
     *
     * @todo Riuscire a condensare lo stesso giorno nello stesso Item, controllando la data
     *
     * @static
     *
     */
    public static function calendarCompact() {
        /* Prototipo calendario nella Home Page - questo deve estrarre gli eventi del giorno attuale e va in avanti
        per n giorni */
        $today = date( 'Ymd0000' );

        /* Numero di eventi da oggi */
        $forwardDays = BNMExtendsOptions::numberEvents();

        /* Creo condizioni di query a oggi a n giorni in avanti, imposto anche una mia condizione di query, between con
        le date */

        add_filter( 'posts_fields', array( __CLASS__, 'posts_fields' ), 10, 1 );

        $meta_query = array(
            array(
                'key'     => kBNMExtendsEventMetaDateAndTime,
                'value'   =>  $today,
                'type'    => 'numeric',
                'compare' => '>='
            )
        );

        $args       = array(
            'numberposts'      => $forwardDays,
            'suppress_filters' => false,
            'post_status'      => 'publish',
            'post_type'        => kBNMExtendsEventPostTypeKey,
            'meta_key'         => kBNMExtendsEventMetaDateAndTime,
            'orderby'          => 'meta_value',
            'order'            => 'ASC',
            'meta_query'       => $meta_query
        );
        $events     = get_posts( $args );

        remove_filter( 'posts_fields', array( __CLASS__, 'posts_fields' ) );

        /* Creo un nuovo array raggruppando per date uguali */
        $events_goups = array();
        foreach ( $events as $event ) {
            $only_the_date                  = substr( $event->meta_value, 0, 8 );
            $events_goups[$only_the_date][] = $event;
        }

        /* Force break per via del fatto che il numero dei record non è detto che corrisponda al numero dei giorni */
        $force_break = 0;

        ?>

    <h2><?php _e( 'Calendar', 'bnmextends' ) ?></h2>
    <ul class="calendar-compact"><?php
        foreach ( $events_goups as $key => $events ) : $force_break++; ?>
            <?php $timestamp = WPDKDateTime::makeTimeFrom( 'YmdHi', $events[0]->meta_value ) ?>
            <?php $class = ( date( 'w', $timestamp ) == '0' ? 'sunday' : '' ) ?>
            <?php $class .= ( date( 'Ymd', $timestamp ) == date( 'Ymd' ) ? ' today ' : '' ) ?>
            <li class="<?php echo $class ?>">
                <table border="0" cellpadding="0" cellspacing="0">
                    <tbody>
                    <tr>
                        <td class="day-name">
                            <span><?php _e( date( 'l', $timestamp ) ) ?> <?php echo date( 'j',
                                $timestamp ) ?><br/><?php _e( date( 'F', $timestamp ) ) ?></span>
                        </td>
                        <td width="100%">

                            <?php
                            foreach ( $events as $event ) :
                                $time  = substr( $event->meta_value, 8, 2 ) . '.' . substr( $event->meta_value, 10, 2 );
                                $title = get_the_title( $event->ID );
                                ?>
                                <div class="event-item">
                                    <a href="<?php echo get_permalink( $event->ID ) ?>">
                                        <?php
                                        $artist = BNMExtendsEventPostType::artistWithEventID( $event->ID );
                                        if ( has_post_thumbnail( $event->ID ) ) {
                                            BNMExtendsEventPostType::thumbnail( $event->ID, kBNMExtendsThumbnailSizeSmallKey );
                                        } else if ( !is_null( $artist ) ) {
                                            BNMExtendsArtistPostType::thumbnail( $artist['ID'], kBNMExtendsThumbnailSizeSmallKey );
                                        } ?>
                                    </a>

                                    <h3>
                                        <a href="<?php echo get_permalink( $event->ID ) ?>">
                                            <span class="bnm-calendar-compact-time">
                                                <?php _e( 'Time', 'bnmextends' ) ?> <?php echo $time . '</span>' . $title ?>
                                        </a>
                                    </h3>
                                </div>
                                <?php endforeach; ?>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </li>
            <?php if( $force_break >= $forwardDays) break; ?>
            <?php endforeach; ?>
    </ul>
    <a href="<?php echo BNMExtends::pagePermalinkWithSlug( 'programmazione' ) ?>"
       class="button blue right"><?php _e( 'View All', 'bnmextends' ) ?></a>
    <?php
    }

    /**
     * Mostra l'interfaccia comprensiva di titolo e content del calendario completo, sia in versione a caselle che in
     * versione lineare
     *
     * @todo Estrarre due funzioni seprate rendendole private
     *
     * @static
     *
     */
    public static function calendar($pdf = false) {

        $args = self::queryArgs();

        $today = 0;
        if ( $args['calendarMonth'] == date( 'n' ) && $args['calendarYear'] == date( 'Y' ) ) {
            $today = date( 'j' );
        }

        $timestamp = mktime( 0, 0, 0, absint( $args['calendarMonth'] ), 1, $args['calendarYear'] );
        $maxday    = absint( date( 't', $timestamp ) ); // 28 / 31
        $thismonth = getdate( $timestamp );
        $startday  = $thismonth['wday']; // 0 (for Sunday) through 6 (for Saturday)
        $one_day   = 60 * 60 * 24;


        $days = array(
            'Monday',
            'Tuesday',
            'Wednesday',
            'Thursday',
            'Friday',
            'Saturday',
            'Sunday',
        );

        // Build the between date for extract custom post
        $dateFrom = date( 'Ymd0000', $timestamp );

        /* Qui c'è un baco di PHP durante il calcolo */
        //$dateTo   = date( 'Ymd2359', $timestamp + ( ( $maxday - 1 ) * $one_day ) );
        $dateTo = sprintf( '%s%02d%s2359', $args['calendarYear'], $args['calendarMonth'], $maxday );

        $events = self::eventsBetweenDate($dateFrom, $dateTo);

        /* Adesso costruisco un array di eventi con la chiave formata dal giorno nel formato 'yyyymmdd', in questo
         * modo quando costrisco il calendario non faccio altro che vedere se esiste in evento per quel giorno
         */
        $eventsForDay = array();
        foreach ( $events as $event ) {
            $dateTimeForThisEvent                   = get_post_meta( $event->ID, 'bnm-event-date', true );
            $event->event_date                      = $dateTimeForThisEvent;
            $dateForThisEvent                       = substr( $dateTimeForThisEvent, 0, 8 );
            $time                                   = substr( $dateTimeForThisEvent, 8, 2 ) . '.' . substr( $dateTimeForThisEvent, 10, 2 );
            $eventsForDay[$dateForThisEvent][$time] = $event;
        }

        /**
         * Qui determiniamo i vari tipi di visualizzazione
         */
        ?>

    <?php if ( $args['view'] == 'detail' ) : ?>

        <table class="detail box white" width="100%" border="0" cellpadding="0" cellspacing="0">
            <tbody><?php for ( $i = 1; $i < $maxday + 1; $i++ ) :
                $keyIndex = date( 'Ymd', mktime( 0, 0, 0, $args['calendarMonth'], $i, $args['calendarYear'] ) );
                $sunday   = ( date( 'w', mktime( 0, 0, 0, $args['calendarMonth'], $i, $args['calendarYear'] ) ) > 0 ) ? '' : 'sunday';
                ?>

            <tr class="<?php echo $sunday ?>">
                <td class="<?php echo ( $today == $i ) ? 'today' : '' ?>">
                    <p class="day <?php echo isset( $eventsForDay[$keyIndex] ) ? 'withShow' : '' ?>"><?php echo $i; ?>
                        <span class="weekName"><?php _e( date( 'l', mktime( 0, 0, 0, $args['calendarMonth'], $i, $args['calendarYear'] ) ) ) ?></span>
                    </p>
                </td>
                <td class="item">
                    <?php if ( isset( $eventsForDay[$keyIndex] ) ) : ?>

                    <?php foreach ( $eventsForDay[$keyIndex] as $key => $event ) : ?>
                        <div class="single-item">
                            <a href="<?php echo get_permalink( $event->ID ) ?>">
                            <?php
                            $artist = BNMExtendsEventPostType::artistWithEventID( $event->ID );
                            if ( has_post_thumbnail( $event->ID ) ) {
                                BNMExtendsEventPostType::thumbnail( $event->ID, kBNMExtendsThumbnailSizeMediumKey );
                            } else if ( !is_null( $artist ) ) {
                                BNMExtendsArtistPostType::thumbnail( $artist['ID'], kBNMExtendsThumbnailSizeMediumKey );
                            } ?>
                            <span class="bnm-calendar-compact-time">
                                <?php _e('Time', 'bnmextends') ?> <?php echo $key ?>
                            </span>
                                <?php echo get_the_title( $event->ID ) ?></a>
                        </div>
                        <?php endforeach; ?>

                    <?php endif; ?>
                </td>
            </tr>
                <?php endfor; ?></tbody>
        </table>

        <?php else : ?>

        <?php
            // -----------------------------------------------------------------------------------------------------------------
            // Vista Calendario
            // -----------------------------------------------------------------------------------------------------------------

            /**
             * Per costruire il calendario dobbiamo partire da una struttura a tabella con le colonne che indicano i giorni
             * della settimana, così da muoverci in tal senso
             */
            ?>
        <?php if($pdf) : ?>
        <table border="1" cellpadding="4" cellspacing="0">
        <?php else : ?>
        <table class="calendar box white" width="100%" border="0" cellpadding="0" cellspacing="0">
        <?php endif; ?>
            <thead>
            <tr>
                <?php foreach ( $days as $day ) : ?>
                <th align="center"><?php _e( $day ) ?></th>
                <?php endforeach; ?>
            </tr>
            </thead>

            <tbody>
                <?php
                $rows     = 0;
                $startday = ( $startday == 0 ) ? 6 : ( $startday - 1 );
                for ( $i = 0; $i < ( $maxday + $startday ); $i++ ) {
                    $keyIndex = date( 'Ymd', mktime( 0, 0, 0, $args['calendarMonth'], ( $i - $startday + 1 ), $args['calendarYear'] ) );
                    if ( ( $i % 7 ) == 0 ) {
                        $rows++;
                        echo '<tr>';
                    }

                    if ( $i < $startday ) : ?>
                    <td class="empty"></td>

                        <?php else: ?>

                    <td class="<?php echo ( $today == ( $i - $startday + 1 ) ) ? 'today' : '' ?>">
                        <div class="item">
                            <p class="day <?php echo isset( $eventsForDay[$keyIndex] ) ? 'withShow' : '' ?>">
                                <?php echo ( $i - $startday + 1 ) ?>
                            </p>
                            <?php if ( isset( $eventsForDay[$keyIndex] ) ) : ?>

                            <?php foreach ( $eventsForDay[$keyIndex] as $key => $event ) : ?>
                                <div class="single-item">
                                    <a href="<?php echo get_permalink( $event->ID ) ?>">
                                    <?php

                                    $artist = BNMExtendsEventPostType::artistWithEventID( $event->ID );

                                    /* Se sono in modalità PDF niente immagini; la libreria non le supporta */
                                    if ( !$pdf ) {
                                        if ( has_post_thumbnail( $event->ID ) ) {
                                            BNMExtendsEventPostType::thumbnail( $event->ID, kBNMExtendsThumbnailSizeMediumKey );
                                        } else if ( !is_null( $artist ) ) {
                                            BNMExtendsArtistPostType::thumbnail( $artist['ID'], kBNMExtendsThumbnailSizeMediumKey );
                                        }
                                    }
                                    ?>

                                    <span class="bnm-calendar-compact-time" style="text-align: center">
                                        <?php _e('Time', 'bnmextends') ?> <?php echo $key ?>
                                    </span>
                                        <span style="clear:both;text-align: center">
                                        <?php echo get_the_title( $event->ID ) ?>
                                            </span>
                                    </a>

                                    <?php

                                    /* Magazzino per Box Officer. */
                                    if ( WPDKUser::hasCaps( array( 'bnm_cap_offline' ) ) ) {
                                        $ticket = get_post_meta( $event->ID, 'bnm-event-ticket', true );
                                        if ( !empty( $ticket ) ) {
                                            $warehouse = WPXSmartShopProduct::warehouse( $ticket );
                                            if ( is_array( $warehouse ) ) {
                                                $html = sprintf( '<div title="Biglietti venduti/totale" class="wpdk-tooltip bnm-event-warehouse">%s/%s</div>', $warehouse['product_store_quantity_for_order_confirmed'], $warehouse['product_store_quantity'] );
                                                echo $html;
                                            }

                                            /* Placeholder - sempre per il solo utente Box Officer */
                                            $count_seat_available = WPPlaceholdersPlaces::statusesWithCount();
                                            $date_time            = WPDKDateTime::dateTime2MySql( $event->event_date, 'YmdHi' );
                                            $reservation          = WPPlaceholdersReservations::countWithDatetime( $date_time );
                                            if ( !empty( $reservation ) ) {
                                                $html = sprintf( '<div title="Posti a cena prenotati" class="wpdk-tooltip bnm-event-reservation">%s/%s</div>', $reservation, $count_seat_available['publish']['count'] );
                                                echo $html;
                                            }

                                            /* Cenanti */
                                            $count = WPXSmartShopStats::countsProductsWithModel( $ticket, BNMEXTENDS_WITH_DINNER_RESERVATION_KEY );
                                            if( $count ) {
                                                $html = sprintf( '<div title="Cenanti" class="wpdk-tooltip bnm-event-dinner-reservation">%s/%s</div>', $count, $warehouse['product_store_quantity'] );
                                                echo $html;
                                            }
                                        }

                                        /* Ticket brunch */
                                        $tickets_brunch = get_post_meta( $event->ID, 'bnm-event-tickets-brunch', true );
                                        if ( !empty( $tickets_brunch ) ) {
                                            /* Recupero ogni singolo id dei biglietti brunch. */
                                            $array_single_tickets_brunch = explode( ',', $tickets_brunch );
                                            $class                       = array(
                                                'comulative'   => 'Comulativo',
                                                'single-adult' => 'Singolo Adulto',
                                                'single-child' => 'Singolo Bambino'
                                            );
                                            foreach ( $array_single_tickets_brunch as $ticket ) {
                                                $warehouse = WPXSmartShopProduct::warehouse( $ticket );
                                                if ( is_array( $warehouse ) ) {
                                                    $sell  = $warehouse['product_store_quantity_for_order_confirmed'];
                                                    $total = $warehouse['product_store_quantity'];
                                                    $html  = sprintf( '<div title="%s venduti/totale" class="wpdk-tooltip bnm-event-warehouse %s">%s/%s</div>', $class[key( $class )], key( $class ), $sell, $total );
                                                    echo $html;
                                                }
                                                next( $class );
                                            }
                                        }
                                    }
                                     ?>
                                </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </td>
                    <?php endif;
                    if ( ( $i % 7 ) == 6 ) {
                        echo '</tr>';
                    }
                }
                for ( $j = 0; $j < ( ( 7 * $rows ) - ($maxday + $startday) ); $j++ ) : ?>
                    <td class="empty"></td>
                <?php endfor; echo ($j >0) ? '</tr>' : ''; ?>

            </tbody>

        </table>
        <?php endif;
    }

    /**
     * Costruisce una barra per la navigazione nei mesi/anni sullo stile del calendario iPad
     *
     * @return void
     */
    public static function navigation() {

        $args = self::queryArgs();

        $sMonth = $args['calendarMonth'];
        $sYear  = $args['calendarYear'];
        $view   = $args['view'];

        $months = array(
            'Jan' => 'January',
            'Feb' => 'February',
            'Mar' => 'March',
            'Apr' => 'April',
            'May' => 'May',
            'Jun' => 'June',
            'Jul' => 'July',
            'Aug' => 'August',
            'Sep' => 'September',
            'Oct' => 'October',
            'Nov' => 'November',
            'Dec' => 'December'
        );

        // Previous Month
        $tm = $sMonth - 1;
        $ty = $sYear;
        if ($tm < 1) {
            $ty = $sYear - 1;
            $tm = 12;
        }
        $urlPreviousMonth = add_query_arg(array(
                                               'view'          => $view,
                                               'calendarMonth' => $tm,
                                               'calendarYear'  => $ty
                                          ));

        // Next Month
        $tm = $sMonth + 1;
        $ty = $sYear;
        if ($tm > 12) {
            $ty = $sYear + 1;
            $tm = 1;
        }
        $urlNextMonth = add_query_arg(array(
                                           'view'          => $view,
                                           'calendarMonth' => $tm,
                                           'calendarYear'  => $ty
                                      ));

        $urlPreviousYear = add_query_arg(array(
                                              'view'          => $view,
                                              'calendarMonth' => $sMonth,
                                              'calendarYear'  => $sYear - 1
                                         ));
        $urlNextYear     = add_query_arg(array(
                                              'view'          => $view,
                                              'calendarMonth' => $sMonth,
                                              'calendarYear'  => $sYear + 1
                                         ));


        ?>
    <div class="box white calendarNavigation">
        <a href="<?php echo $urlPreviousMonth ?>" class="arrow previous"><</a>
        <a class="year" href="<?php echo $urlPreviousYear ?>"><?php echo $sYear - 1 ?></a>
        <?php
        $m = 1;
        foreach ($months as $month) :
            $urlForMonth = add_query_arg(array(
                                              'view'          => $view,
                                              'calendarMonth' => $m,
                                              'calendarYear'  => $sYear
                                         ));
            $m++;
            ?>
            <a href="<?php echo $urlForMonth ?>"
               class="month <?php echo ($m == ($sMonth + 1)) ? 'current' : '' ?>"><?php _e($month) ?></a>
            <?php endforeach; ?>
        <a class="year last" href="<?php echo $urlNextYear ?>"><?php echo $sYear + 1?></a>
        <a href="<?php echo $urlNextMonth ?>" class="arrow next">></a>
    </div>
    <?php

    }

    public static function toolBar() {

        /* Costruisce l'url per cambiare vista */
        $args = self::queryArgs();

        /* Immagini toolbar */
        $ical_image = '<img alt="' . __( 'Import iCal', 'bnmextends' ) . 'title="' . __( 'Import iCal', 'bnmextends' ) .
            '" src="data:image/png;base64,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" />';

        $pdf_image = '<img alt="' . __( 'Download PDF', 'bnmextends' ) . 'title="' . __( 'Download PDF', 'bnmextends' ) .
            '" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAABHNCSVQICAgIfAhkiAAAAAlwSFlzAAABuwAAAbsBOuzj4gAAABl0RVh0U29mdHdhcmUAd3d3Lmlua3NjYXBlLm9yZ5vuPBoAAAI3SURBVDiNlZM7SxxRFMd/Z+7s010NBmOhGAPBMhYqIlhrZyCriJUfQ7CxFTs/gIiVjSkUBAsLOyGx0IgaJBEFNWsTEuPEubN756TIOBg0gRw4cB+c/4t7RVURkedAD/9XH1T1k59sesLQvo0ii4jHbfUL2VKJOJcjXyggQBRFZLNZAHzfJ5PJVIAUAGstwc+Abzs7PO3t5cfFBVIsYpubyefz1Gq1lFpE0rWXHnqCMYZSWxvn8/MUWlpwh4cYY4iiCON5GGMwxjwO4IngeR4NnZ08GxqiOjeHFwQUCwUyvk8cx9SdwxiD56VjpBYkATCeR1NvLzlj+DozQ6m1FTMwQC6Xw1pLGIYUi8VHLCQAd13q66NpcJDa8jJ2chIODiiXyynQXxVIAkAQ0NDVhT89jdvb43p2FhobaZqaotDR8VABIr9zSID07Ay/owPnHIW+PlqWl6Fc5nJigs/9/Y9YuK9ChPjkBNrbEWu5XljgslJBurtp3drixfb2QwuIIMmwiFA7OSE8PibY2CA/Ps6TpSUkeUhks/owg3sq6gcHBOvrZEZGaFlbI1MqcXNzw9X5Ob7vs7u7+x44AkBVAd5Ya/X29la/r63pxdiYXi0uaq1WU+ecBkGg+/v7GgSBrq6ungKvAFHVP0OM45ioWiU3PEy5UsEYg6pSr9cRETY3N89GR0dfA/uaMKcK6s6ptVaD01O9ubxU55zGcazOOQ3DMF5ZWXl3n/muJfnOL5PLf9UR8DFlTuoXSUIEulWpxvwAAAAASUVORK5CYII=" />';

        $calendar_image =
            '<img alt="' . __( 'Calendar view', 'bnmextends' ) . 'title="' . __( 'Calendar view', 'bnmextends' ) .
                '" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAfdJREFUeNqckc9LVFEUx7/3vvfmMVpiNqsKU8QYArcFgbUIhShSGSYoEAnSTcv6CwJ30qrACtu0EByCVmFqi2nm5Y+FDjqOksoEzkYUlBDGmffjeu69k1FYTB44fDj38r3ne85ls6NPxZXddwA4JUN1ISgDzJ3pBRPD5wRuvqQ6+D89p4afH8OEQcX2PLD6vuKAVaGmjMYgteze2KZ4E7+AvKuvWRUuBOmbLaA/sQlulPcQpsNC2kEdudqadnDWBHZmHESO4fZ0GvXUpIY0JmnNsueqKQxaSt5JYXghAiuTIycNEJL4nUI0oDS3hA9P2lD2PPCS76v919XauHa9nchxN3YZp2v+zjuxNjXKge+BdbyaERMDV9H3dg02WQwZlVUyPeuflFl0fYw8jKLz9ax0EKjXToUZ4rdbYYc4um+1wrJ+sYto/qS6j2oHXgB+4PmqsG0DU+k8QjbHVEpzkmiRpfEv38EsAx+TmuOpjaMRjh6QHR50NMMwOe53asoaxKHuJjy6tI/nPU3oJ76It+gHXHJQ9PQIJnVMOPSv1DHhFDS/FsCI2ewiSr7AwuKSYiaTURqlvTg4KWSsr+aO5bcVzeXcimJ2WdcyGkmL84MT4qQhtSYr/khGnn264QaVP1Kf+C/qP7Y4Q7i8n5SnciP1OFnsHQowADLsOuq1c9H6AAAAAElFTkSuQmCC" />';

        $detail_image =
            '<img alt="' . __( 'List view', 'bnmextends' ) . 'title="' . __( 'List view', 'bnmextends' ) .
                '" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAfpJREFUeNqck79rFEEUx78zO7O7Z6Hrrh5iTFQiFhZhsQjpFHMIYkxl6dX2EhU7sZH8QP+EEI4romiI+BMuoo2IgkY7BS0sgmeylxA5i+ztrG9mc0eOO0jIWx4zuzPfN+99Zh8rl8urSZJ4SikwwHhqnB496WKMMXDOYVnWmtDiYrGI3VipVPKEPllbdekXftQUrj6OcG8kwOkeGw0IMDop3ZKK3h/HMfp6j5i5aC46UuD2u3/o7/Mw9SnB/Akbly5fwcF8Hr7vQwiJqBZhuVrFzMy00WitaNXFLdwYFLj+bBWTF/cjSTkePpjVBbel3cygaa0ARAXDJ/fi86l9IDX+bqRQaZIRxSZZlp26NSZvTmxKsfJ9HeH4T1S+rVNJNlwnB9fdk3kuGx36JmmtIwNbcEx8iHGs18PExwYKAxIXzo8aBkEQEAOBlShjMDf3qDNAyizcHJQYe1rD1IhPZQAvXzzfOQN9XYXwMBbDHlOwihVtbtCsPYBmwDnrZGBJC5XFJQzc+WJGLiXV6xIHp81zrgspRJdbYAJ332/gODEYJxaF0MLZc8PI5w/BD3zDICIGf35XsVB51SUAJXNryMG1Jyu4P3rAfHnzemHb31lQQ7ReCuFRfCXfqWmtqNfrb6kpziiVbHYja3Viuo1Ya7Wmn9zD7mztvwADAEx5vrwGYPLZAAAAAElFTkSuQmCC" />';

        $print_image = '<img alt="' . __( 'Print', 'bnmextends' ) . 'title="' . __( 'Print', 'bnmextends' ) .
            '" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAltJREFUeNqMU89PE1EQ/t7u2y2WSpWAlQONFq/iQSnEC4WDBw1WLyb+DRr/AROaktjEk4nRq3+BCcUmBj1I60VRDtZ4FRI9YMVEpYt0fz5nHgVZwsFJJm8y833fzps3K+5X5rCcKMBSPgRChGFU9fywGEQRoLBjApCGAdsyF0zTuKZgwhcWxt06pIKBi51XeNMzDaEEvMAtzt+9jA7xVFdAkEAPndcri0XblFBCak5Ap4xIgIMJUtv8/RMrqUvYJvDKl7jAhSzgU1PnNmroSx/XHOZKBoSKuzXoAlJF1B7zhtNxAQ65xphAGSLq5mUYhtUgCIoU42iqF8K0YFGcSyNmLhOoxhjX87W2lHJBzM7OqnK5rEG3bt+BJU2QIIIwiglI02AC/CDE40cPda5UKnHPEdrOFlZX1zAwMIhEwqahi70HwL+HoJyC63pofvyEXO40mKtnYFsWciOn0N/fTwIJupvAYaYUC7gaa0trpzOeRLvt4O2792g2P/yXQKNxBhP5MT1FyWBny4FtWzg7OopWqwVBS3OoALWcyWQ0ljliV2B726XhWciP5fFtfV3fn8GxGZAo93VyaAie52mOFjCo4Ps+epNHUKs9w4+N7yhMTumXUN1R8lD5BeqNJQwMnsDMzFXNYa7sdDqN6vzTSVZPJgysZm/gwc1pfHXiizScAp6sDSIbvcaLxedamrlcHyE/v+tX7r2kTVNqi9zpOsec49p+LHNFpVLRX6H1wbJdgLfZWjLsZCE88Dea1G7k/anbfZmpca/OK723HweNOzqGw+0X+ef9ib8CDACFvBEKYY958gAAAABJRU5ErkJggg==" />';

        ?>
    <div class="content box white">
        <ul class="bnm-calendar-toolbar">
            <li>
                <?php if ( $args['view'] == 'detail' ) : ?>
                <a href="<?php echo add_query_arg( array( 'view' => 'calendar' ) ) ?>"><?php echo $calendar_image ?>
                    <span><?php _e( 'Calendar view', 'bnmextends' ) ?></span></a>
                <?php else : ?>
                <a href="<?php echo add_query_arg( array( 'view' => 'detail' ) ) ?>"><?php echo $detail_image ?>
                    <span><?php _e( 'List view', 'bnmextends' ) ?></span></a>
                <?php endif; ?>
            </li>
            <li>
                <a href="<?php echo add_query_arg( array( 'ical' => '' ) ) ?>"><?php echo $ical_image ?>
                    <span><?php _e( 'Import iCal', 'bnmextends' ) ?></span></a>
            </li>
            <li>
                <a href="<?php echo add_query_arg( array( 'pdf' => '' ) ) ?>"><?php echo $pdf_image ?>
                    <span><?php _e( 'Download PDF', 'bnmextends' ) ?></span></a>
            </li>
            <li>
                <a href="<?php echo add_query_arg( array( 'print' => '' ) ) ?>"
                   onclick="window.open( this.href, 'Blue Note Milano <?php echo self::title() ?>', 'menubar, toolbar, location, directories, status, scrollbars, resizable, dependent, width=640, height=480, left=100, top=100');return false"><?php echo $print_image ?>
                    <span><?php _e( 'Print', 'bnmextends' ) ?></span></a>
            </li>
        </ul>
        <?php
        /*
        <form name="filter" method="get" action="<?php echo $url ?>">
            <p>Filtra per titolo: <input name="title" type="text" value="<?php echo $title ?>"/></p>

            <p>Filtra per date: <input name="dateFrom" type="text" value="<?php echo $dateFrom ?>"/> <input
                    name="dateTo" type="text" value="<?php echo $dateTo ?>"/></p>

            <p><input type="submit" value="Filtra"/></p>
        </form>
        */
        ?>
    </div>
        <?
    }

    public static function title() {
        $args = self::queryArgs();
        $timestamp = mktime( 0, 0, 0, $args['calendarMonth'], 1, $args['calendarYear'] );

        $result = ucfirst( __( date( 'F', $timestamp ) ) ) .  date( ' Y', $timestamp );

        return $result;
    }

    public static function queryArgs() {

        /* Interfaccia comune: filtri e modalità di visualizzazione */
        $result = array(
            'view'             => isset( $_GET['view'] ) ? $_GET['view'] : 'calendar',
            'title'            => esc_attr( isset( $_GET['title'] ) ? $_GET['title'] : '' ),
            'dateFrom'         => isset( $_GET['dateFrom'] ) ? $_GET['dateFrom'] : '',
            'dateTo'           => isset( $_GET['dateTo'] ) ? $_GET['dateTo'] : '',
            'calendarMonth'    => isset( $_GET['calendarMonth'] ) ? $_GET['calendarMonth'] : date( 'n' ),
            'calendarYear'     => isset( $_GET['calendarYear'] ) ? $_GET['calendarYear'] : date( 'Y' ),
        );

        /* Per adesso stampo sempre la versione Calendario, non quella detail */
        if(isset($_GET['ical']) || isset($_GET['pdf']) || isset($_GET['print']) ) {
            $result['view'] = 'calendar';
        }

        return $result;
    }



    // -----------------------------------------------------------------------------------------------------------------
    // Events list methods
    // -----------------------------------------------------------------------------------------------------------------

    public static function eventsForThisMonth() {

        /* Inizio di questo mese */
        $startDate = date( 'Ym010000' );

        /* Ultimo giorno del mese */
        $endDate = date( 'Ymt2359' );

        return self::eventsBetweenDate( $startDate, $endDate, 'DESC' );
    }

    public static function eventsForCurrentView() {

        /* Recupero le informazioni di visualizzazione */
        $args          = self::queryArgs();
        $current_month = sprintf('%02s', $args['calendarMonth']);
        $current_year  = $args['calendarYear'];

        /* Inizio di questo mese */
        $startDate = date( $current_year . $current_month . '010000' );

        /* Ultimo giorno del mese */
        $endDate = date( $current_year . $current_month . 't2359' );

        return self::eventsBetweenDate( $startDate, $endDate, 'DESC' );
    }

    public static function eventsBetweenDate($startDate, $endDate, $order = 'ASC', $numberposts = -1) {

        /* Creo condizioni di query a oggi a n giorni in avanti, imposto anche una mia condizione di query, between con
        le date */
        add_filter( 'posts_fields', array( __CLASS__, 'posts_fields' ), 10, 1 );

        /* Condizioni di Where */
        $meta_query = array(
            array(
                'key'     => kBNMExtendsEventMetaDateAndTime,
                'value'   => array(
                    $startDate,
                    $endDate
                ),
                'type'    => 'numeric',
                'compare' => 'BETWEEN'
            )
        );

        /* Parametri standard */
        $args = array(
            'numberposts'      => $numberposts,
            'suppress_filters' => false,
            'post_status'      => 'publish',
            'post_type'        => kBNMExtendsEventPostTypeKey,
            'meta_key'         => kBNMExtendsEventMetaDateAndTime,
            'orderby'          => 'meta_value',
            'order'            => $order,
            'meta_query'       => $meta_query
        );

        $events = get_posts( $args );

        remove_filter( 'posts_fields', array(__CLASS__,'posts_fields') );

        return $events;
    }

    // -----------------------------------------------------------------------------------------------------------------
    // iCal
    // -----------------------------------------------------------------------------------------------------------------

    public static function iCal() {
        if(!class_exists('ICalExporter')) {
            require_once( 'Libs/ICalExporter.php' );
        }

        /* Recupero tutti gli eventi di questo mese */
        $events = self::eventsForCurrentView();

        /* Formatto gli eventi per iCal */
        $format_events = array();

        foreach ( $events as $event ) {
            $mktime = WPDKDateTime::makeTimeFrom( 'YmdHi', $event->meta_value );
            $mktime += 60*60;
            $endtime = date( 'YmdHi', $mktime );
            $format_events[] = array(
                'id'           => $event->ID,
                'start_date'   => WPDKDateTime::formatFromFormat( $event->meta_value, 'YmdHi', 'Y-m-d H:i:00' ),
                //'end_date'     => WPDKDateTime::formatFromFormat( $event->meta_value, 'YmdHi', 'Y-m-d H:i:10' ),
                'end_date'     => WPDKDateTime::formatFromFormat( $endtime, 'YmdHi', 'Y-m-d H:i:00' ),
                'text'         => $event->post_title,
                'rec_type'     => '',
                'event_pid'    => 0,
                'event_id'     => $event->ID,
                'event_length' => null
            );
        }

        $export = new ICalExporter();
        $ical   = $export->toICal( $format_events );

        return $ical;
    }

    public static function downloadiCal() {

        /* Definisco un filename */
        $filename = sprintf( 'BlueNote-Program-%s.ics', date( 'Y-m-d H:i:s' ) );

        /* Contenuto */
        $ical = self::iCal();

        /* Header per download */
        header( 'Content-Type: application/download' );
        header( 'Content-Disposition: attachment; filename="' . $filename . '"' );
        header( 'Cache-Control: public' );
        header( "Content-Length: " . strlen( $ical ) );
        header( 'Pragma: no-cache' );
        header( 'Expires: 0' );

        echo $ical;
    }

    // -----------------------------------------------------------------------------------------------------------------
    // PDF
    // -----------------------------------------------------------------------------------------------------------------

    public static function downloadPDF() {
        if ( !class_exists( 'TCPDF' ) ) {
            return false;
        }

        // create new PDF document
        $pdf = new TCPDF( PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false );

        // set document information
        $pdf->SetCreator( PDF_CREATOR );
        $pdf->SetAuthor( 'Blue Note Milano' );
        $pdf->SetTitle( 'Blue Note Milano' );
        $pdf->SetSubject( 'Programma del mese di' );
        $pdf->SetKeywords( 'Blue Note Milano, Programma' );

        // set default header data
        $pdf->SetHeaderData( '../../../../../../themes/bluenotemilano/images/login-logo.png', 40, 'Blue Note Milano', 'Programma ' . self::title() );

        // set header and footer fonts
        $pdf->setHeaderFont( Array(
                                  PDF_FONT_NAME_MAIN,
                                  '',
                                  PDF_FONT_SIZE_MAIN
                             ) );
        $pdf->setFooterFont( Array(
                                  PDF_FONT_NAME_DATA,
                                  '',
                                  PDF_FONT_SIZE_DATA
                             ) );

        // set default monospaced font
        $pdf->SetDefaultMonospacedFont( PDF_FONT_MONOSPACED );

        //set margins
        $pdf->SetMargins( PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT );
        $pdf->SetHeaderMargin( PDF_MARGIN_HEADER );
        $pdf->SetFooterMargin( PDF_MARGIN_FOOTER );

        $tagvs = array(
            'p' => array(
                0 => array(
                    'h' => 0,
                    'n' => 0
                ),
                1 => array(
                    'h' => 0,
                    'n' => 0
                )
            ),
            'a' => array(
                0 => array(
                    'h' => 0,
                    'n' => 0
                ),
                1 => array(
                    'h' => 0,
                    'n' => 0
                )
            ),
            'div' => array(
                0 => array(
                    'h' => 4,
                    'n' => 0
                ),
                1 => array(
                    'h' => 2,
                    'n' => 1
                )
            ),
            'span' => array(
                0 => array(
                    'h' => 0,
                    'n' => 0
                ),
                1 => array(
                    'h' => 0,
                    'n' => 0
                )
            )
        );
        $pdf->setHtmlVSpace($tagvs);

        //set auto page breaks
        $pdf->SetAutoPageBreak( TRUE, PDF_MARGIN_BOTTOM );

        //set image scale factor
        $pdf->setImageScale( PDF_IMAGE_SCALE_RATIO );

        //set some language-dependent strings

        $l = Array();

        // PAGE META DESCRIPTORS --------------------------------------

        $l['a_meta_charset'] = 'UTF-8';
        $l['a_meta_dir'] = 'ltr';
        $l['a_meta_language'] = 'it';

        // TRANSLATIONS --------------------------------------
        $l['w_page'] = 'pagina';

        $pdf->setLanguageArray( $l );

        // ---------------------------------------------------------

        // set font
        $pdf->SetFont('helvetica', 'B', 10);

        // add a page
        $pdf->AddPage();

        ob_start();

        ?>
    <style type="text/css">
        table th {
            font-size        : 14pt;
            background-color : #ccd;
        }

        p.day {
            text-align : right;
            font-size  : 14pt;
            color      : #888888;
        }

        p.withShow {
            color : #000000;
        }

        table td {
            vertical-align : top;
        }

        a {
            font-size       : 7pt;
            text-decoration : none;
            text-align      : left;
            color: #000;
        }

        span.bnm-calendar-compact-time {
            background-color : #f60;
            color: #fff;
        }

        div.single-item {
            text-align    : left;
            border-bottom : 2px solid #333;
        }
    </style>
        <?php
        self::calendar( true );

        $content = ob_get_contents();
        ob_end_clean();

        /* Internal use only */
        if ( isset( $_GET['debug'] ) ) {
            echo $content;
        } else {
            // output the HTML content
            $pdf->writeHTML( $content, true, false, false, false, '' );

            //Close and output PDF document
            $pdf->Output( 'BlueNote-Programma.pdf', 'D' );
        }
    }

    // -----------------------------------------------------------------------------------------------------------------
    // Print
    // -----------------------------------------------------------------------------------------------------------------

    public static function printing() {
        ?>
    <style type="text/css">
        body {
            font-family : Helmet, Freesans, sans-serif;
        }

        table.calendar thead th {
            color         : #888;
            padding       : 16px 0;
            border-bottom : 1px solid #aaa;
            width         : 14%;
            text-shadow   : 1px 1px 0 #fff;
        }

        table.calendar thead th:nth-child(7) {
            color : #f90;
        }

        table.calendar {
            float           : left;
            border          : 1px solid #aaa;
            margin          : 0;
            border-collapse : collapse;
            border-spacing  : 0;
        }

        table.calendar tbody { }

        table.calendar tbody td {
            color          : #444;
            height         : 128px;
            border-right   : 1px solid #666;
            border-bottom  : 1px solid #666;
            vertical-align : top;
        }

        table.calendar div.item {
            text-align : center;
        }

        table.calendar div.item a {
            font-size   : 12px;
            display     : block;
            margin      : 8px 4px;
            font-weight : bold;
            text-align  : left;
            line-height : 15px;
        }

        table.calendar div.item span {
            display: block;
        }

        table.calendar div.item img.attachment-thumbnail-medium {
            display            : block;
            text-align         : center;
            margin             : 0 auto;
            float              : none;
            border             : 1px solid #000;
            -moz-box-shadow    : none;
            -webkit-box-shadow : none;
            box-shadow         : none;
        }

        table.calendar div.item p {
            color       : #666;
            font-size   : 11px;
            line-height : 100%;
            margin      : 2px 6px;
            text-align  : left;
            text-shadow : none;
        }

        table.calendar div.item h4 a:hover {
            text-decoration : underline;
        }

        table.calendar div.item h4 a {
            color     : #f60;
            font-size : 12px;
            margin    : 0 4px 2px;
        }

        table.calendar div.item p.day.withShow {
            color : #005191;
        }

        table.calendar div.item p.day {
            color       : #aaa;
            font-size   : 20px;
            line-height : 120%;
            margin      : 4px 4px 0 0;
            text-align  : right;
        }

        table.calendar td.today p.day {
            color : #f60;
        }

        table.calendar tbody tr:last-child td {
            border-bottom : none;
        }

        table.calendar tbody tr:last-child td:first-child {
            -moz-border-radius    : 0 0 0 4px;
            -webkit-border-radius : 0 0 0 4px;
            border-radius         : 0 0 0 4px;
        }

        table.calendar tbody td:last-child {
            border-right : none;
        }

        table.calendar tbody td:nth-child(7) {
            background : #eef;
        }

        table.calendar tbody td.today {
            background : #ffe;
        }

        table.calendar tbody td.empty {
            background : #f0f0f0;
        }

        table.calendar tbody tr:last-child td:last-child {
            -moz-border-radius    : 0 0 4px 0;
            -webkit-border-radius : 0 0 4px 0;
            border-radius         : 0 0 4px 0;
        }

        table.calendar a {
            text-decoration: none;
            color: #666;
        }

        table.calendar div.single-item {
            border-bottom : 1px solid #ddd;
            margin-bottom : 16px;
        }

        table.calendar div.single-item:last-child {
            border-bottom : none;
            margin-bottom : 0;
        }

        div.bnm-print-box {
            width: 1000px;
        }

        h1 {
            font-size: 18px;

        }
    </style>
        <div class="bnm-print-box">
        <h1>Blue Note Milano - <?php echo self::title() ?></h1>
    <?php
        self::calendar();
     ?>
        </div>
        <script type="text/javascript">
            window.print();
        </script>
    <?php
    }

}
