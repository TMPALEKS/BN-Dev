<?php
/**
 * Questo è il template Single usato dai Post Custom Type Event
 *
 * @package          Blue Note Milano HTML5 Theme
 * @subpackage       single-event
 * @author           =undo= <g.fazioli@saidmade.com>
 * @copyright        Copyright © 2010-2011 Saidmade, Srl
 *
 */

unset( $_SESSION['wpss_last_product_variant'] );

get_header(); ?>
<div class="page-wrap">

    <?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>

    <div class="left sizeMedium clearfix">
        <article class="content box white clearfix" id="post-<?php the_ID(); ?>">

            <h2 class="entry-title"><?php the_title(); ?></h2>
                <span class="eventDate">
                    <?php
                    $id_event  = get_the_ID();
                    $id_event  = BNMExtendsEventPostType::idWPMLDefaultLanguage( $id_event );
                    $eventDate = get_post_meta( $id_event, kBNMExtendsEventMetaDateAndTime, true );
                    $strtime   = WPDKDateTime::makeTimeFrom( 'YmdHi', $eventDate );
                    //$time      = substr( $eventDate, 8, 2 ) . ':' . substr( $eventDate, 10, 2 );
                    $time = date( 'H.i', $strtime );
                    _e( date( 'l', $strtime ) );
                    _e( date( ' j ', $strtime ) );
                    _e( date( 'F', $strtime ) );
                    _e( date( ', Y', $strtime ) );
                    ?> - <?php echo strtolower( __( 'Time', 'bnm' ) ) ?> <?php echo $time ?>
                </span>

            <?php BNMExtendsEventPostType::thumbnail( $id_event, kBNMExtendsThumbnailSizeLargeKey ) ?>

            <?php

            /* -------- Custom product Card */

            $ticket = get_post_meta( $id_event, 'bnm-event-ticket', true );

            $rows_card = array(
                'base_price',
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
                'thumbnail_click_to_enlarge' => false,
                'permalink'                  => false,
                'display_permalink_button'   => false,
                'price'                      => false,
                'label_price'                => __( 'Purchase Tickets', 'bnm' ),
                'content'                    => false,
                'excerpt'                    => false,
                'title'                      => false,
                'product_types'              => false,
                'product_types_tree'         => false,
                'appearance'                 => false,
                'variant_labels'             => array('model' => ''),
                'exclude_appearance'         => array(),
                'exclude_variants'           => array(),
            );

            add_filter( 'wpss_cart_add_to_cart_button_label', array( 'BNMExtends', 'wpss_cart_add_to_cart_button_label'), 10, 3 );

            if ( !empty( $ticket ) ) {
                ?>
                <div class="bnm-evemt-ticket"><?php
                    echo WPXSmartShopProduct::card( $ticket, $args );
                    if ( !is_user_logged_in() ) {
                        $rules = WPXSmartShopProduct::priceRules( $ticket );
                        foreach ( $rules as $rule ) {
                            if ( $rule['wpss-product-rule-id'] == kWPSmartShopProductTypeRuleOnlinePrice ) {
                                echo '<div style="margin:8px 0 0;text-align:center">';
                                _e( 'Advance price:', 'bnm' );
                                echo ' ';
                                echo WPXSmartShopCurrency::currencyHTML( $rule['price'] );
                                echo '</div>';
                                break;
                            }
                        }
                    }
                    ?></div><?php
            }

            /* -------- End custom product card */
            ?>

            <?php

            /* -------- Brunch */

            $tickets_brunch = get_post_meta( $id_event, 'bnm-event-tickets-brunch', true );

            if( !empty( $tickets_brunch ) ) {

                $tickets = explode( ',', $tickets_brunch );

                foreach( $tickets as $ticket ) {
                    ?><div class="bnm-evemt-ticket"><?php
                    echo WPXSmartShopProduct::card( $ticket, $args );
                    ?></div><?php
                }
            }

            /* -------- End Brunch */
            ?>


            <?php the_content(); ?>


                <?php
                $artist = BNMExtendsEventPostType::artistWithEventID( get_the_ID() );
                if ( !is_null( $artist ) ) {
                    $title = $artist['post_title'];
                    ?>
                <div class="artist">
                    <h3>
                        <a href="<?php echo get_post_permalink( $artist['ID'] )?>"><?php echo get_the_title( $artist['ID'] ) ?></a>
                    </h3>

                    <a href="<?php echo get_post_permalink( $artist['ID'] ) ?>">
                        <?php BNMExtendsArtistPostType::thumbnail( $artist['ID'], kBNMExtendsThumbnailSizeMediumKey ); ?>
                    </a>
                    <?php echo apply_filters( 'the_content', $artist['post_content'] );?>
                    </div><?php
                  } ?>

            <br clear="all" />

            <div class="bnm-sociable-post clearfix">
                <?php bnm_social() ?>
            </div>

            <nav id="nav-single" class="clearfix">
                <h3 class="assistive-text"><?php _e( 'Post navigation', 'bnm' ); ?></h3>
                <?php BNMExtendsEventPostType::themeNavigation( $eventDate, __( 'Previous Event', 'bnm' ), __( 'Next Event', 'bnm' ) ) ?>
            </nav>
            <!-- #nav-single -->
        </article>
    </div>

    <?php get_sidebar( 'event' ); ?>

    <?php endwhile; endif; ?>
</div>
<?php get_footer(); ?>