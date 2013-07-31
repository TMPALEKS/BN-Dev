<?php
/**
 * Template Name: Box Office Summary
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
if ( is_user_logged_in() && WPDKUser::hasCap( 'bnm_cap_offline' ) ) {

    /* Check for export */
    $export = isset( $_POST['export'] );
    if ( $export ) {
       /* Definisco un filename */
        $filename = sprintf( 'wpxSmartShop-Summary-%s.csv', date( 'Y-m-d H:i:s' ) );

        /* Contenuto */
        $buffer = get_transient( 'wpxss_frontend_summary_csv' );

        /* Header per download */
        header( 'Content-Type: application/download' );
        header( 'Content-Disposition: attachment; filename="' . $filename . '"' );
        header( 'Cache-Control: public' );
        header( "Content-Length: " . strlen( $buffer ) );
        header( 'Pragma: no-cache' );
        header( 'Expires: 0' );

        echo $buffer;

        die();
    }

    /* Check for printing */
    $printing = isset( $_POST['print'] );
} else {
    wp_redirect( '/' );
}

if ( !$printing ) {
    get_header();
} ?>
<?php //echo '<pre>'; echo "Records affected: " . BNMExtendsStats::batchUpdateCategoryForStats(); echo '</pre>'; ?>
<?php #echo '<pre>'; echo "Records affected: " . BNMExtendsOrders::closePendingOrders( 510 ); echo '</pre>'; ?>
<?php #echo '<pre>'; echo "Records affected: " . BNMExtendsOrders::updateOrderWithStatus( 510 ); echo '</pre>'; ?>
<?php //echo '<pre>'; BNMExtendsOrders::updateOrderWithStatus(510); echo '</pre>'; ?>

<div class="page-wrap">


<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
<div class="left sizeLarge">
<article class="content box white" id="post-<?php the_ID(); ?>">

    <?php
    /* Catch filtro spettacolo/prodotto */
    $product = null;
    if ( isset( $_POST['id_product'] ) && !empty( $_POST['id_product'] ) ) :
        $id_product = absint( $_POST['id_product'] );
        $product    = get_post( $id_product );
        ?><h2 class="entry-title"><?php echo $product->post_title ?></h2><?php else:
        ?><h2 class="entry-title"><?php the_title(); ?></h2><?php
    endif;
    ?>


<br class="clear"/>

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
                'name'    => 'product_title',
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
                'type'    => WPDK_FORM_FIELD_TYPE_SUBMIT,
                'name'    => 'filter',
                'value'   => 'Mostra',
                'class'   => 'button blue'
            ),
            array(
                array(
                    'type'    => WPDK_FORM_FIELD_TYPE_CHECKBOX,
                    'name'    => 'membership',
                    'label'   => 'Mostra solo Club e Platinum',
                    'value'   => 'yes',
                    'checked' => isset( $_POST['membership'] ) ? 'yes' : ''
                )
            ),
        )
    );

    ?>
<form action="" method="post"><?php
    if ( !$printing ) {
        WPDKForm::htmlForm( $sdf );
    }

    /* Eseguo select se spettacolo selezionato */
    if ( !is_null( $product ) ) {

        $membership_where = '';
        if ( isset( $_POST['membership'] ) ) {
            $membership_where = sprintf( ' AND ( usermeta.meta_value LIKE \'%%"%s"%%\' OR usermeta.meta_value LIKE \'%%"%s"%%\' ) ', 'bnm_role_5', 'bnm_role_6' );
        }

        $table_stats    = WPXSmartShopStats::tableName();
        $table_orders   = WPXSmartShopOrders::tableName();
        $table_coupons  = WPXSmartShopCoupons::tableName();
        $table_products = $wpdb->posts;

        $sql = <<< SQL
SELECT
   COUNT(stats.id) AS posti,
   stats.*,
   orders.note AS order_note,
   orders.order_datetime AS order_datetime,
   orders.track_id AS track_id,
   orders.transaction_id AS transaction_id,
   orders.id_user,
   orders.id_user_order,
   orders.bill_first_name,
   orders.bill_last_name,
   orders.bill_email,
   orders.bill_phone,

   products.post_title AS coupon_product_maker_name,

   coupons.uniqcode AS coupon_uniqcode,
   users.display_name AS user_display_name,
   users_orders.display_name AS user_order_display_name,
   usermeta.meta_value

FROM `{$table_stats}` AS stats

LEFT JOIN `{$table_orders}` AS orders ON orders.id = stats.id_order
LEFT JOIN `{$table_coupons}` AS coupons ON (stats.id_coupon <> 0 AND coupons.id = stats.id_coupon)
LEFT JOIN `{$table_products}` AS products ON products.ID = coupons.id_product_maker
LEFT JOIN `{$wpdb->users}` AS users ON orders.id_user = users.ID
LEFT JOIN `{$wpdb->users}` AS users_orders ON orders.id_user_order = users_orders.ID
LEFT JOIN `{$wpdb->usermeta}` AS usermeta ON orders.id_user_order = usermeta.user_id AND usermeta.meta_key = '{$wpdb->prefix}capabilities'

WHERE stats.id_product = {$id_product}
AND orders.status = 'confirmed'
{$membership_where}
AND stats.status <> 'trash'

GROUP BY orders.bill_first_name, orders.bill_last_name, orders.id, coupons.uniqcode, stats.price_rule, stats.model

ORDER BY bill_last_name
SQL;

        $data = $wpdb->get_results( $sql, ARRAY_A );

        if ( !empty( $data ) ) :

            ?>
        <style type="text/css">
            table.bnm-show-summary {
                border          : 1px solid #aaa;
                font-size       : 10px;
                font-family     : Arial;
                border-collapse : collapse;
            }

            table.bnm-show-summary thead th {
                background   : #444;
                border-right : 1px solid #555;
                color        : #fff;
                font-size    : 13px;
                padding      : 10px 0;
            }

            table.bnm-show-summary tbody td {
                padding        : 4px;
                border-bottom  : 1px solid #aaa;
                border-right   : 1px solid #aaa;
                vertical-align : middle;
            }

            table.bnm-show-summary tbody tr:nth-child(2n) td {
                background-color : #fafafa;
            }

            table.bnm-show-summary tbody td:nth-child(5),
            table.bnm-show-summary tbody td:nth-child(8),
            table.bnm-show-summary tbody td:nth-child(9) {
                text-align : right;
            }

            table.bnm-show-summary tbody td:nth-child(1),
            table.bnm-show-summary tbody td:nth-child(2) {
                white-space : nowrap;
            }

            table.bnm-show-summary tbody td:nth-child(5) {
                font-weight      : bold;
                background-color : rgba(255, 0, 0, 0.1) !important;
            }

            ul.bnm-summary {
                list-style-type : none;
                font-size       : 12px;
                font-weight     : bold;
                margin          : 8px;
                padding         : 0;
                font-family     : Arial;
            }

            ul.bnm-summary li {
                background-image : none;
                padding          : 4px 0;
                margin           : 0;
                text-align       : right;
                border-bottom    : 1px solid #ddd;
            }

        </style>

        <table class="bnm-show-summary" width="100%" cellpadding="0" cellspacing="0">
            <thead>
            <tr>
                <th>Spettatore</th>
                <th>Ordine</th>
                <th>Categoria</th>
                <th>Coupon</th>
                <th>Posti</th>
                <th>Cena</th>
				<th>Fattura</th>
                <th>Email</th>
                <th>Tel</th>
                <th>Note</th>
            </tr>
            </thead>

            <tbody>
                <?php

                /* Export CSV */
                $export_columns = array(
                    'Spettatore',
                    'Ordine',
                    'Categoria',
                    'Coupon',
                    'Posti',
                    'Cena',
                    'Fattura',
                    'Email',
                    'Tel',
                    'Note'
                );

                $export_buffer = '';
                $toExclude = 0;
                $toExcludePlatinum = 0;
                $toExcludeClub = 0;
                $toExcludeArray = array();


                foreach ( $data as $item ) :
                    $price_rule      = '';
                    $price_rule_code = $item['price_rule'];

                    if ( $price_rule_code == kWPSmartShopProductTypeRuleOnlinePrice ) {
                        $price_rule = 'Advance';
                    } elseif ( $price_rule == kWPSmartShopProductTypeRuleDatePrice ) {
                        $price_rule = 'Per data';
                    } elseif ( isset( $roles[$price_rule_code] ) ) {
                        $price_rule = $roles[$price_rule_code];
                    } elseif ( isset( $discountID[$price_rule_code] ) ) {
                        $price_rule = $discountID[$price_rule_code]['label'];
                    } else {
                        $price_rule = 'Sconosciuto';
                    }

                    $coupon = '';
                    if ( !is_null( $item['coupon_product_maker_name'] ) ) {
                        $coupon = sprintf( '%s<br/>(%s)', $item['coupon_uniqcode'], $item['coupon_product_maker_name'] );
                    }

                    $dinner = ( $item['model'] == BNMEXTENDS_WITH_DINNER_RESERVATION_KEY ) ? 'Cena' : '';

                    /* Comulative branch */
                    if ( $item['model'] == BNMEXTENDS_2_ADULTS_2_CHILDREN_KEY ) {
                        $item['posti'] *= 4;
                        $dinner = BNMEXTENDS_2_ADULTS_2_CHILDREN;
                    } elseif ( $item['model'] == BNMEXTENDS_2_ADULTS_1_CHILD_KEY ) {
                        $item['posti'] *= 3;
                        $dinner = BNMEXTENDS_2_ADULTS_1_CHILD;
                    } elseif ( $item['model'] == BNMEXTENDS_2_ADULTS_KEY ) {
                        $item['posti'] *= 2;
                        $dinner = BNMEXTENDS_2_ADULTS;
                    }


                    /*
                     * Escludo i tutti i coupon in corrispondenza di Advance o BoxOffice. Servirà ad epurare il conteggio degli Advance
                     */
                    if ( ( $item['coupon_product_maker_name'] != "" ) &&  ($price_rule == 'Advance' || $price_rule == 'Box Office' ) )
                        $toExclude += $item['posti'];

                    if ( $price_rule == 'Club Platinum' && ( $item['coupon_product_maker_name'] != "" )  )

                    #if ( ( $item['coupon_product_maker_name'] == "Platinum Membership" ) &&  ($price_rule == 'Club Platinum') )
                        $toExcludePlatinum += $item['posti'];

                    #if ( ( $item['coupon_product_maker_name'] == "Club Membership" ) &&  $price_rule == 'Club Member' )
                    if ( $price_rule == 'Club Member' && ( $item['coupon_product_maker_name'] != "" )  )
                        $toExcludeClub += $item['posti'];

                   # if (
                       # !(( $item['coupon_product_maker_name'] == "Club Membership"  ) || ( $item['coupon_product_maker_name'] == "Platinum Membership" ) )
                       # && !($price_rule == 'Advance' || $price_rule == 'Box Office') )
                   # var_dump( $price_rule );

                    if ( !($price_rule == 'Advance' || $price_rule == 'Box Office' ) && ( $item['coupon_product_maker_name'] != "" )  )
                    {
                        $toExcludeArray[$item['coupon_product_maker_name']] += $item['posti'];
                    }

                    $transaction_id = '';
                    if ( !empty( $item['transaction_id'] ) ) {
                        $transaction_id = sprintf( '<br/>(%s)', $item['transaction_id'] );
                        $transaction_id_printf = sprintf( "\r\n(%s)", $item['transaction_id'] );
                    }

                    $has_invoice = BNMExtendsInvoices::getInvoiceByOrder( $item['id_order'] );

                    $export_buffer .= sprintf( '"%s","%s","%s","%s","%s","%s","%s","%s","%s","%s"',
                        sprintf( '%s %s', $item['bill_last_name'], $item['bill_first_name'] ),
                        sprintf( "# %s - %s%s\r\n%s", $item['id_order'], $item['track_id'], $transaction_id_printf, ( mysql2date( __( 'm/d/Y H:i', WPXSMARTSHOP_TEXTDOMAIN ), $item['order_datetime'] ) ) ),
                        sprintf( '%s', $price_rule ),
                        sprintf( '%s', $coupon ),
                        sprintf( '%s', $item['posti'] ),
                        sprintf( "%s\r\n%s", $dinner, $item['note'] ),
                        sprintf( '%s', $has_invoice ? __('Yes', 'bnm' ) :  "" ),
                        sprintf( '%s', $item['bill_email'] ),
                        sprintf( '%s', $item['bill_phone'] ),
                        sprintf( '%s', $item['order_note'] ) );
                    $export_buffer .= WPDK_CRLF;


                    ?>
                <tr>
                    <td><?php printf( '%s %s', $item['bill_last_name'], $item['bill_first_name'] ); ?></td>
                    <td><?php printf( '# <span style="color:#789;">%s</span> - %s%s<br/>%s', $item['id_order'], $item['track_id'], $transaction_id, ( mysql2date( __( 'm/d/Y H:i', WPXSMARTSHOP_TEXTDOMAIN ), $item['order_datetime'] ) ) );?></td>
                    <td><?php printf( '%s', $price_rule ) ?></td>
                    <td><?php printf( '%s', $coupon ) ?></td>
                    <td><?php printf( '%s', $item['posti'] ) ?></td>
                    <td><?php printf( '%s<br/><strong>%s</strong>', $dinner, $item['note'] ) ?></td>
                    <td><?php printf( '%s', $has_invoice ? __('Yes', 'bnm' ) :  "" ) ?></td>
                    <td><?php printf( '%s', $item['bill_email'] ) ?></td>
                    <td><?php printf( '%s', $item['bill_phone'] ) ?></td>
                    <td><?php printf( '%s', $item['order_note'] ) ?></td>
                </tr>
                    <?php endforeach;
#var_dump($toExcludeArray);

                $export_columns_row = sprintf( '"%s"', join( '","', $export_columns ) ) . WPDK_CRLF;
                $export             = $export_columns_row . $export_buffer;
                set_transient( 'wpxss_frontend_summary_csv', $export );
                ?>
            </tbody>
        </table>
            <?php

            /* Totale */
            $sql    = <<< SQL
SELECT COUNT(stats.id)
FROM `{$table_stats}` AS stats

LEFT JOIN `{$table_orders}` AS orders ON orders.id = stats.id_order
LEFT JOIN `{$wpdb->usermeta}` AS usermeta ON orders.id_user_order = usermeta.user_id AND usermeta.meta_key = '{$wpdb->prefix}capabilities'

WHERE stats.id_product = {$id_product}
AND orders.status = 'confirmed'
{$membership_where}
SQL;
            $totale = $wpdb->get_var( $sql );

            /* Cenanti */
            $with_dinner = BNMEXTENDS_WITH_DINNER_RESERVATION_KEY;
            $sql         = <<< SQL
SELECT COUNT(stats.id)
FROM `{$table_stats}` AS stats

LEFT JOIN `{$table_orders}` AS orders ON orders.id = stats.id_order
LEFT JOIN `{$wpdb->usermeta}` AS usermeta ON orders.id_user_order = usermeta.user_id AND usermeta.meta_key = '{$wpdb->prefix}capabilities'

WHERE stats.id_product = {$id_product}
AND orders.status = 'confirmed'
AND stats.model = '{$with_dinner}'
{$membership_where}
SQL;
            $dinner      = $wpdb->get_var( $sql );

            /* Advance */
            $sql = <<< SQL
SELECT COUNT(stats.id) AS count, stats.price_rule
FROM `{$table_stats}` AS stats

LEFT JOIN `{$table_orders}` AS orders ON orders.id = stats.id_order
LEFT JOIN `{$wpdb->usermeta}` AS usermeta ON orders.id_user_order = usermeta.user_id AND usermeta.meta_key = '{$wpdb->prefix}capabilities'

WHERE stats.id_product = {$id_product}
AND orders.status = 'confirmed'
{$membership_where}
GROUP BY stats.price_rule
SQL;

            $result      = $wpdb->get_results( $sql );
            $price_rules = array();
            foreach ( $result as $row ) {
                if ( !empty( $row->price_rule ) ) {
                    $price_rules[$row->price_rule] = $row->count;
                }
            }

            /* Coupons */
            $sql = <<< SQL
SELECT COUNT(stats.id) AS count,
products.post_title AS coupon_product_maker_name,
coupons.uniqcode AS coupon_uniqcode,
coupons.id_product_type,
stats.price_rule


FROM `{$table_stats}` AS stats

LEFT JOIN `{$table_orders}` AS orders ON orders.id = stats.id_order
LEFT JOIN `{$wpdb->usermeta}` AS usermeta ON orders.id_user_order = usermeta.user_id AND usermeta.meta_key = '{$wpdb->prefix}capabilities'
LEFT JOIN `{$table_coupons}` AS coupons ON (stats.id_coupon <> 0 AND coupons.id = stats.id_coupon)
LEFT JOIN `{$table_products}` AS products ON products.ID = coupons.id_product_maker

WHERE stats.id_product = {$id_product}
AND orders.status = 'confirmed'
{$membership_where}
AND stats.id_coupon > 0
GROUP BY coupons.uniqcode

SQL;

            $coupons = $wpdb->get_results( $sql );


            ?>

            <?php /** Start HTML Response */  ?>
        <ul class="bnm-summary">
            <li>Totale: <?php printf( $totale ); ?></li>
            <li>Cenanti: <?php printf( $dinner ); ?></li>



            <?php


            $count_coupon_membership = 0;
            $count_coupon_membership_platinum = 0;
            $count_coupon_membership_club = 0;

            $count_young = 0;
            $count_coupon = 0;
            $count_show_voucher = 0;
            $count_dinner_voucher = 0;

            foreach ( $coupons as $coupon ) :
                #var_dump( $coupons );
                if ( !empty( $coupon->count ) ) :

                    $count_coupon = $count_coupon + $coupon->count;

                    if ( $coupon->coupon_product_maker_name == "Platinum Membership" ){
                        $count_coupon_membership_platinum += $coupon->count;
                    }

                    if ( $coupon->coupon_product_maker_name == "Club Membership" ){
                        $count_coupon_membership_club += $coupon->count;
                    }

                    if ( $coupon->coupon_product_maker_name == "Abbonamento Under 26"){
                        $count_young += $coupon->count;
                    }

                endif;
            endforeach;

            $count_coupon_membership = $count_coupon_membership_platinum + $count_coupon_membership_club; //Servirà sotto per epurare gli Advance



            $RateoTotals = array(
                'Door' => $price_rules['door'],
                'ADV+PREV' => $price_rules['adv_prev'],
                'Bambino' => $price_rules['kids'],
                'Scuola Musica Normale' => $price_rules['bnm_role_12'],
                'Scuola Musica 18€' => $price_rules['music-school-18'],
                'Scuola Musica 20%' => $price_rules['misic-school'],
                'Cral' => $price_rules['bnm_role_10'] + $price_rules['cral'],
                'Carte' => $price_rules['bnm_role_11'] + $price_rules['carte'],
                'Platinum' =>  $price_rules['bnm_role_6'] + $price_rules['platinum'],  //Se ci sono coupon non li considero i reateo
                'Club Member' =>   $price_rules['bnm_role_5'] + $price_rules['club'], //Se ci sono coupon non  li considero i reateo
                'Over 65' =>  $price_rules['bnm_role_4'] + $price_rules['over65'],
                'Under 26' =>  $price_rules['bnm_role_3'] + $price_rules['under26'],
                'Gruppi' => $price_rules['groups'],
                'Rateo coupon members' => $price_rules['rateo_members'] + $count_coupon_membership,
                'Intermediaries' =>  $price_rules['bnm_role_7'],
                'Cambio Spettacolo' => $price_rules['cambio_spettacolo'],
                'Rateo Abbonamento Giovani' => ($count_young != 0) ? $count_young : 0,

                );


            ?>

            <?php
            $advance = $price_rules['-2'] - $toExclude ;
            foreach ( $price_rules as $key => $value ) {
                if ( $key == 'bnm_role_8' && isset( $price_rules[$key] ) )
                    $advance += $price_rules[$key];
            }

            ?>

            <?php /* Stampa dei valori */ ?>
            <li>Advance: <?php echo ( intval($advance) > 0 ) ? $advance : 0 ?></li>

            <?php

            #var_dump($toExcludeArray);

            foreach ( $RateoTotals as $label => $tot ):
                $total = $tot;

                if ( $label == 'Platinum')
                    //$total = $tot - $platinum_coupons_exclude;
                    $total = $tot - $toExcludePlatinum;

                if ( $label == 'Club Member' )
                    $total = $tot - $toExcludeClub;

                if ( $label == "Under 26" )
                    $total = $tot - $toExcludeArray['Abbonamento Under 26'];

                if ( $label == "Show Voucher" )
                    $total = $tot - $toExcludeArray['5 Show Voucher'] - $toExcludeArray['Show Voucher'] - - $toExcludeArray['2 Show Voucher'];

                if ( $total ):
            ?>
            <li><?php printf( '%s: %s', $label, $total ) ?></li>
            <?php endif; endforeach; ?>

            <?php
            $c2d = BNMExtendsSummaryOrder::coupons2Discounts();

            $total = 0;

            foreach ( $c2d as $key => $value ):
                $total = isset($price_rules[$key]) ? intval($price_rules[$key]) : 0;
                foreach ( $value as $name => $label):
                    foreach ( $coupons as $coupon):
                        if( $coupon->coupon_product_maker_name == $name ){
                            $total += intval($coupon->count);

                        }
                    endforeach;
                endforeach;

                if ($label == "Rateo Abbonamento Satchmo")
                    $total = $total - $toExcludeArray["Abbonamento Satchmo"];

                if ( $total ):
            ?>
            <li><?php  printf( '%s: %s', $label, $total ) ?></li>
            <?php endif; endforeach; ?>
          </ul>

         <?php endif;
         }
        ?>

    <?php if ( !empty( $data ) && !$printing ) :
        $language_code = ( ICL_LANGUAGE_CODE == 'it' ) ? '' : trailingslashit( ICL_LANGUAGE_CODE );
        $print_url     = sprintf( '/%s%s/', $language_code, __( 'print', 'bnm' ) );
        printf( '<p class="aligncenter"><input name="print" type="submit" class="button blue" value="%s"/> <input name="export" type="submit" class="button blue" value="%s"/>', __( 'Print', 'bnm' ), __( 'Export CSV', 'bnm' ) ); else:
        if ( !$printing ) {
            _e( 'Nessun acquisto per questo spettacolo', 'bnm' );
        }

    endif;

    ?>
</form>

<br class="clear"/>
</article>
</div>
    <?php //get_sidebar( 'register' ); ?>
    <?php endwhile; endif; ?>

</div>


<?php if ( !$printing ) {
    get_footer();
}