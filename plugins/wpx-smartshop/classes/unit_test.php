<?php
/**
 * WordPress Prototyping Unit Test area
 *
 * @package         wpx SmartShop
 * @subpackage      UnitTest.php
 * @author          =undo= <g.fazioli@undolog.com>, <g.fazioli@wpxtre.me>
 * @copyright       Copyright (C)2012 wpXtreme, Inc.
 * @created         04/01/12
 * @version         1.0
 *
 *
 *
 * array(1) {
 *   ["eyJpZF9wcm9kdWN0IjoyMDMyLCJpZF92YXJpYW50IjoiQ29sb3JpIiwidmFyaWFudCI6W3sibmFtZSI6Im1vZGVsIiwidmFsdWUiOiJTbWFsbCJ9XX0="]=>
 *   array(6) {
 *     ["productName"]=>
 *     string(25) "Maglietta Bluenote Milano"
 *     ["price"]=>
 *     float(25.1)
 *     ["qty"]=>
 *     int(1)
 *     ["link"]=>
 *     string(65) "http://www.bluenotemilano.net/prodotto/maglietta-bluenote-milano/"
 *     ["id_variant"]=>
 *     string(6) "Colori"
 *     ["variant"]=>
 *     array(1) {
 *       [0]=>
 *       array(2) {
 *         ["name"]=>
 *         string(5) "model"
 *         ["value"]=>
 *         string(5) "Small"
 *       }
 *     }
 *   }
 * }
 *
 * 4F74B3-8D88AC2
 * 4F74B3-8D88AC2
 *
 */
?>

<pre class="wpdk-monitor">
    <?php
    global $wpdb;

    function output( $v ) {
        ?>
        <pre>--------------------------------------------------</pre>
        <pre><?php var_dump( $v ) ?></pre>
        <?php
    }

    $wpdb->show_errors();
    error_reporting( E_ALL );
    ini_set( 'display_errors', true );
    $r = '';

    class TestFoo {
        const PLUTO = 5;
        const PIPPO = 'Ciao';

        function __construct() {
            echo "constr";
        }

        function __wakeup() {
            var_dump( 'pppp' );
        }

        static function topolino() {
            echo "-----";
        }
    }

    //
    // Unit test area
    //


    //    $r = WPXSmartShopSession::session();
    //    output($r);
    //
    //    $r = WPSmartShopStats::countsCouponsWithUserIDAndUniqcode(get_current_user_id(), '4FA3BB6DDFD52', 8321);
    //    output($r);

    // Questo senza PECL non funziona
    //$r = new HttpRequest( 'http://wordpress.org/', HttpRequest::METH_GET );

    //    $r = wp_remote_post( 'http://wpxtre.me/category/portfolio/', array(
    //                                                         'method'      => 'GET',
    //                                                         'timeout'     => 45,
    //                                                         'redirection' => 5,
    //                                                         'httpversion' => '1.0',
    //                                                         'user-agent'  => 'X',
    //                                                         'blocking'    => true,
    //                                                         'headers'     => array(),
    //                                                         'cookies'     => array(),
    //                                                         'body'        => NULL,
    //                                                         'compress'    => false,
    //                                                         'decompress'  => true,
    //                                                         'sslverify'   => true,
    //                                                    ) );


    //    $cm = new WPXSmartShopCouponMaker();
    //    $cm->value(33);
    //    $cm->qty(3);
    //    $cm->limit_product_qty(1);
    //    $cm->id_owner(1);
    //    //$cm->durability(1, 'day');
    //
    //    $r = $cm->create();
    //
    //    output($r);

    // "-- 60 -- eyJpZF9wcm9kdWN0Ijo4NDkzLCJ2YXJpYW50Ijp7IkRpbm5lciI6eyJtb2RlbCI6IlNlbnphIFByZW5vdGF6aW9uZSBDZW5hIn19fQ== -- 1 -- 0 --"


    $sWPDKGlobalConstPrefix = 'wpxss_';
    define( $sWPDKGlobalConstPrefix . 'PLUGINMAINFILENAME', dirname( __FILE__ )  );


    output( $sWPDKGlobalConstPrefix . 'PLUGINMAINFILENAME' );

    ?>

</pre>
<?php
/*
$ticket = get_post_meta( $id_event, 'bnm-event-ticket', true );


if ( !empty( $ticket ) ) {
    ?><span class="bnm-event-price-door-message"><?php _e('Door price','bnm') ?></span><?php
    echo WPXSmartShopCurrency::currencyHTML( WPXSmartShopProduct::priceBase( $ticket ), 'bnm-price-door' );
    ?><span class="bnm-event-purchase-message"><?php _e('Purchase Tickets', 'bnm') ?></span><?php
    echo WPXSmartShopProduct::priceHTML( $ticket, 1, WPXSmartShopSession::countProductWithID( $ticket ) + 1 );

    // Visualizzo Combo Cena solo per i biglietti che hanno la variante correttamente impostata
    if ( WPXSmartShopProduct::hasProductVariant( $ticket, 'Dinner', 'model' ) ) {
        $options = array(
            __( 'Without Dinner', 'bnm' ),
            __( 'With Dinner', 'bnm' ),
        );

        echo WPXSmartShopProduct::variantCustom( $ticket, 'Dinner', 'model', $options );
    } else {

        echo WPXSmartShopShoppingCart::buttonAddShoppingCart( $ticket, 'Cena' );
    }
 } else {
    // @todo Da fare nel backend dell'evento
    $ticket_brunch_cumulative   = get_post_meta( $id_event, 'bnm-event-ticket-brunch-cumulative', true );
    $ticket_brunch_single_kid   = get_post_meta( $id_event, 'bnm-event-ticket-brunch-single-kid', true );
    $ticket_brunch_single_adult = get_post_meta( $id_event, 'bnm-event-ticket-brunch-single-adult', true );
 }

*/
?>
