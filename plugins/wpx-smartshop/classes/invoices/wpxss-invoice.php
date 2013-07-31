<?php
/**
 * @class              WPXSmartShopInvoice
 *
 * @description        Gestione delle fatture
 *
 * @package            wpx SmartShop
 * @subpackage         invoice
 * @author             =undo= <g.fazioli@undolog.com>, <g.fazioli@wpxtre.me>
 * @copyright          Copyright (c) 2012 wpXtreme, Inc.
 * @created            12/04/12
 * @version            1.0.0
 *
 */

class WPXSmartShopInvoice {

    private static $total;
    private static $order;

    static $output_email = false;

    // -----------------------------------------------------------------------------------------------------------------
    // Static values
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Colonne del riepilogo ordini
     *
     * @static
     * @retval array
     *                     Array con le colonne da usare nel riepilogo ordine dei prodotti
     */
    public static function columnsSummaryProducts() {
        $columns = array(
            'item'   => __( 'Code', WPXSMARTSHOP_TEXTDOMAIN ),
            'name'   => __( 'Product name', WPXSMARTSHOP_TEXTDOMAIN ),
            'price'  => __( 'Unit Price', WPXSMARTSHOP_TEXTDOMAIN ),
            'qty'    => __( 'Quantity', WPXSMARTSHOP_TEXTDOMAIN ),
            'vat'    => __( 'VAT', WPXSMARTSHOP_TEXTDOMAIN ),
            'amount' => __( 'Price', WPXSMARTSHOP_TEXTDOMAIN ),
        );

        /**
         * @filters
         *
         * @param array $columns Array con le colonne da usare nel riepilogo ordine dei prodotti
         */
            $columns = apply_filters( 'wpss_invoice_columns_summary_products', $columns );

        return $columns;
    }

    /**
     * Righe finali del riepilogo ordini
     *
     * @static
     * @retval array
     *                     Array con le righe da mettere in fondo alla fattura
     */
    public static function rowsSummaryProducts() {
        $rows = array(
            'sub_total'   => __( 'Subtotal', WPXSMARTSHOP_TEXTDOMAIN ),
            'coupon_order'=> __( 'Coupon', WPXSMARTSHOP_TEXTDOMAIN ),
            'discount'    => __( 'Discount', WPXSMARTSHOP_TEXTDOMAIN ),
            'vat'         => __( 'VAT', WPXSMARTSHOP_TEXTDOMAIN ),
            'shipping'    => __( 'Shipping', WPXSMARTSHOP_TEXTDOMAIN ),
            'total'       => __( 'Total', WPXSMARTSHOP_TEXTDOMAIN )
        );

        /**
         * @filters
         *
         * @param array $columns Array con le colonne da usare nel riepilogo ordine dei prodotti
         */
        $rows = apply_filters( 'wpss_invoice_rows_summary_products', $rows );

        return $rows;
    }


    // -----------------------------------------------------------------------------------------------------------------
    // UI
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Costruisce una fattura (invoice)
     *
     * @static
     *
     * @param int|string|object $order ID, trackID o Object dell'ordine
     *
     * @retval WP_Error|string Restituisce l'html della fattura
     */
    public static function invoice( $order ) {

        $order = WPXSmartShopOrders::order( $order );

        if ( $order && is_object( $order ) ) {
            /* Recupero i singoli prodotti */
            $products = WPXSmartShopStats::productsWithOrderID( $order->id );

            if ( $products ) {
                /* store for later */
                self::$total = 0;
                self::$order = $order;
                return self::htmlInvoice( $order, $products );
            } else {
                $message = __( 'No products found for order', WPXSMARTSHOP_TEXTDOMAIN );
                $error   = new WP_Error( 'wpss_error-invoice_no_products_found_for_order', $message, $order );
                return $error;
            }

        } else {
            $message = __( 'No order found', WPXSMARTSHOP_TEXTDOMAIN );
            $error   = new WP_Error( 'wpss_error-invoice_no_order_found', $message, $order );
            return $error;
        }
    }

    /**
     * Restituisce un array keypair con l'header e il footer per la stampa
     *
     * @static
     * @retval array
     */
    public static function wrapForPrinting( $content ) {
        $charset            = get_bloginfo( 'charset' );
        $title              = __( 'Print Order', WPXSMARTSHOP_TEXTDOMAIN );
        $stylesheet         = WPXSMARTSHOP_URL_CSS . 'print.css';

        if ( self::$output_email ) {
            $html = <<< HTML
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
	<title>{$title}</title>
	<style type="text/css">
		#outlook a {padding:0;}
		body{
		    width:100% !important; -webkit-text-size-adjust:100%; -ms-text-size-adjust:100%; margin:0; padding:0;
		}
		.ExternalClass {width:100%;}
		.ExternalClass, .ExternalClass p, .ExternalClass span, .ExternalClass font, .ExternalClass td, .ExternalClass div {line-height: 100%;}
		#backgroundTable {
		    margin:0; padding:0; width:100% !important; line-height: 100% !important;
		}
		img {outline:none; text-decoration:none; -ms-interpolation-mode: bicubic;}
		a img {border:none;}
		.image_fix {display:block;}
		p {margin: 1em 0;}
		h1, h2, h3, h4, h5, h6 {color: black !important;}

		h1 a, h2 a, h3 a, h4 a, h5 a, h6 a {color: blue !important;}

		h1 a:active, h2 a:active,  h3 a:active, h4 a:active, h5 a:active, h6 a:active {
		color: red !important;
		}

		h1 a:visited, h2 a:visited,  h3 a:visited, h4 a:visited, h5 a:visited, h6 a:visited {
		color: purple !important;
		}
		table td {border-collapse: collapse;}

        table { border-collapse:collapse; mso-table-lspace:0pt; mso-table-rspace:0pt; }

		a {color: orange;}

		@media only screen and (max-device-width: 480px) {
			/* Part one of controlling phone number linking for mobile. */
			a[href^="tel"], a[href^="sms"] {
						text-decoration: none;
						color: blue; /* or whatever your want */
						pointer-events: none;
						cursor: default;
					}

			.mobile_link a[href^="tel"], .mobile_link a[href^="sms"] {
						text-decoration: default;
						color: orange !important;
						pointer-events: auto;
						cursor: default;
					}

		}

		@media only screen and (min-device-width: 768px) and (max-device-width: 1024px) {
			a[href^="tel"], a[href^="sms"] {
						text-decoration: none;
						color: blue; /* or whatever your want */
						pointer-events: none;
						cursor: default;
					}

			.mobile_link a[href^="tel"], .mobile_link a[href^="sms"] {
						text-decoration: default;
						color: orange !important;
						pointer-events: auto;
						cursor: default;
					}
		}

		@media only screen and (-webkit-min-device-pixel-ratio: 2) {
		}

		/* Android targeting */
		@media only screen and (-webkit-device-pixel-ratio:.75){
		}
		@media only screen and (-webkit-device-pixel-ratio:1){
		}
		@media only screen and (-webkit-device-pixel-ratio:1.5){
		}

	</style>

	<!-- Targeting Windows Mobile -->
	<!--[if IEMobile 7]>
	<style type="text/css">

	</style>
	<![endif]-->

	<!-- ***********************************************
	****************************************************
	END MOBILE TARGETING
	****************************************************
	************************************************ -->

	<!--[if gte mso 9]>
		<style>
		/* Target Outlook 2007 and 2010 */
		</style>
	<![endif]-->
</head>
<body>
<!-- Wrapper/Container Table: Use a wrapper table to control the width and the background color consistently of your email. Use this approach instead of setting attributes on the body tag. -->
<table cellpadding="0" cellspacing="16" border="0" id="backgroundTable" width="100%" style="background-color: #fafafa;">
	<tr>
		<td valign="top" style="border:1px solid #aaa; background: white; padding: 32px">
		<!-- Tables are the most common way to format your email consistently. Set your table widths inside cells and in most cases reset cellpadding, cellspacing, and border to zero. Use nested tables as a way to space effectively in your message. -->
{$content}
		</td>
	</tr>
</table>
<!-- End of wrapper table -->
</body>
</html>
HTML;
        } else {

        $html = <<< HTML
<!DOCTYPE html>
<head>
    <meta charset={$charset}">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="robots" content="noindex, nofollow"/>
    <title>{$title}</title>
    <meta name="viewport" content="width=1024"/>
    <link media="all" rel="stylesheet" href="{$stylesheet}">
</head>
<body class="wpxss-print-invoice">
{$content}
</body>
</html>
HTML;
        }
        return $html;
    }


    /**
     * Metodo chiamato sostanzialmente da gestore dei permalink onfly. Infatti i parametri vengono presi dalla GET
     *
     * @static
     * @param $order
     */
    public static function printing( $order ) {
        if ( is_user_logged_in() ) {
            if ( isset( $_GET['invoice'] ) && !empty( $_GET['id_order'] ) ) {
                $result = self::invoice( absint( $_GET['id_order'] ) );
                if ( !is_wp_error( $result ) ) {
                    echo self::wrapForPrinting( $result );

                    /* Decommentare per provare l'invio della mail

                    $order = WPXSmartShopOrders::order( absint( $_GET['id_order'] ) );
                    do_action( 'wpss_order_confirmed', $order->track_id );

                    */
                }
            }
        }
    }

    /**
     * HTML della fattura completa
     *
     * @access             internal
     *
     * @param object $order    Ordine
     * @param array  $products Elenco prodotti come da sessione
     *
     * @retval string
     *                     HTML della fattura completa
     */
    private function htmlInvoice( $order, $products ) {

        //$html_title = __( '<h3>Thanks for purchase</h3>', WPXSMARTSHOP_TEXTDOMAIN );
        $html_title = '';

        $html_order_info = self::htmlOrder( $order );
        $html_merchant   = self::htmlMerchant();
        $html_account    = self::htmlAccount( $order->id_user_order );
        $html_products   = self::htmlProducts( $products );
        $html_disclaim   = self::htmlDisclaim();

        $html = <<< HTML
    <div class="wpss-invoice">
        {$html_title}
        {$html_merchant}
        {$html_order_info}
        {$html_account}
        {$html_products}
        {$html_disclaim}
    </div>
HTML;
        return $html;
    }

    private static function htmlOrder( $order ) {
        $order_id    = sprintf( '%s: %s', __( 'Order ID', WPXSMARTSHOP_TEXTDOMAIN ), $order->id );
        /* @todo Metterlo come opzioni nelle impostazioni. Per adesso lo spengo */
        //$order_track = sprintf( '%s: %s', __( 'Order Track ID', WPXSMARTSHOP_TEXTDOMAIN ), $order->track_id );
        $order_track = '';
        $style = '';
        if( self::$output_email ) {

        }

        $html = <<< HTML
<div class="wpss-invoice-order-information">
    <table width="100%" border="0" cellpadding="0" cellspacing="0">
        <tbody>
            <tr>
                <td>{$order_id}</td>
                <td>{$order_track}</td>
            </tr>
        </tbody>
    </table>
</div>
HTML;
        return $html;

    }

    /**
     * Costruisce le informazioni sul negoziante/commerciante, quali logo, nome e indirizzo
     *
     * @package            wpx SmartShop
     * @subpackage         WPXSmartShopInvoice
     * @since              1.0
     *
     * @access             internal
     * @static
     * @retval string
     *                     HTML delle informazioni sul negozio/venditore
     */
    private static function htmlMerchant() {
        /* @todo Aggiungere filtri e finire di sistemare */
        $values           = WPXSmartShop::settings()->general();
        $merchant_name    = $values['shop_name'];
        $merchant_address = $values['shop_address'];
        $merchant_logo    = $values['shop_logo'];
        $merchant_email   = $values['shop_email'];
        $merchant_info    = str_replace( "\r", '<br/>',  $values['shop_info'] );

        if( !empty( $merchant_logo) ) {
            $merchant_logo = sprintf( '<img src="%s" />', $merchant_logo );
        }

        $html = <<< HTML
    <div class="wpss-invoice-merchant">
        <table width="100%" border="0" rules="none" cellpadding="0" cellspacing="0">
            <tbody>
                <tr>
                    <td>{$merchant_logo}</td>
                    <td style="vertical-align: middle;text-align: right"><h1>{$merchant_name}</h1></td>
                </tr>
                <tr>
                    <td colspan="2" style="text-align: right"><p style="text-align: right">{$merchant_address}</p><p style="text-align: right">{$merchant_info}</p></td>
                </tr>
                </tbody>
        </table>
    </div>
HTML;
        return $html;
    }

    /**
     * Costruisce le informazioni sull'acquirente (bill to) e il destinatario (ship to) dell'ordine
     *
     * @access             internal
     * @static
     *
     * @param int $id_user_order ID dell'utente a cui è riferito l'ordine
     *
     * @retval string
     *                     HTML delle informazioni di chi sta acquistando e dove si sta spedendo
     */
    private static function htmlAccount( $id_user_order ) {

        /* true se esiste almeno un prodotto spedibile in quest'ordine */
        $shipping = WPXSmartShopOrders::hasShippingProducts( self::$order );

        if ( $shipping ) {
            $ship_to = __( 'Sent to', WPXSMARTSHOP_TEXTDOMAIN );


            /* Retrive account information */

            $first_name   = self::$order->shipping_first_name;
            $last_name    = self::$order->shipping_last_name;
            $address      = self::$order->shipping_address;
            $country      = WPSmartShopShippingCountries::shippingCountry( self::$order->shipping_country );
            $country_name = $country->country;
            $town         = self::$order->shipping_town;
            $zipcode      = self::$order->shipping_zipcode;

            $account_shipping = <<< HTML
<p>{$first_name} {$last_name}</p>
<p>{$address}</p>
<p>{$town} - {$zipcode} ({$country_name})</p>
HTML;

            /**
             * @filters
             *
             * @param string $info          Informazioni account
             * @param int    $id_user_order ID utente
             */
            $account_shipping = apply_filters( 'wpss_invoice_account_shipping', $account_shipping, $id_user_order );
        }

        $first_name   = self::$order->bill_first_name;
        $last_name    = self::$order->bill_last_name;
        $address      = self::$order->bill_address;
        $country      = WPSmartShopShippingCountries::shippingCountry( self::$order->bill_country );
        $country_name = $country->country;
        $town         = self::$order->bill_town;
        $zipcode      = self::$order->bill_zipcode;

        $account_billing = <<< HTML
    <p>{$first_name} {$last_name}</p>
    <p>{$address}</p>
    <p>{$town} - {$zipcode} ({$country_name})</p>
HTML;


        $bill_to = __( 'Purchased by', WPXSMARTSHOP_TEXTDOMAIN );

        /**
         * @filters
         *
         * @param string $info Informazioni account
         * @param int    $id_user_order ID utente
         */
        $account_billing  = apply_filters( 'wpss_invoice_account_billing', $account_billing, $id_user_order );

        $style        = '';
        $style_body   = '';
        $cell_spacing = 0;
        if ( self::$output_email ) {
            $style        = $shipping ? 'style="width:50%"' : '';
            $style_body   = 'style="background: #eeeeee;padding:8px;border-radius:8px;-webkit-border-radius:8px;-moz-border-radius:8px"';
            $cell_spacing = 8;
        }

        $shipping_th = $shipping_body = '';
        if ( $shipping ) {
            $shipping_th = "<th {$style}>{$ship_to}</th>";
            $shipping_body = "<td {$style_body}>{$account_shipping}</td>";
        }

        $html = <<< HTML
<div class="wpss-invoice-account">
    <table width="100%" border="0" cellpadding="0" cellspacing="{$cell_spacing}">
        <thead>
            <tr>
                {$shipping_th}
                <th {$style}>{$bill_to}</th>
            </tr>
        </thead>

        <tbody>
            <tr>
                {$shipping_body}
                <td {$style_body}>{$account_billing}</td>
            </tr>
        </tbody>
    </table>
</div>
HTML;
        return $html;
    }

    /**
     * Costruisce il sommario con tutti i prodotti e le loro informazioni
     *
     * @access             internal
     *
     * @static
     *
     * @param array $products Elenco dei prodotti
     *
     * @retval string
     *                     HTML del riepilogo dei prodotti acquistati
     */
    private static function htmlProducts( $products ) {

        /* Array delle colonne eventualmente alterato dal filtro */
        $columns = self::columnsSummaryProducts();

        /* THEAD */
        $html_thead = '';
        foreach ( $columns as $key => $name ) {
            $html_thead .= sprintf( '<th class="%s">%s</th>', $key, $name );
        }

        /* TBODY */
        $html_tbody = '';
        foreach ( $products as $id_product_key => $product ) {
            $html_tbody .= '<tr>';
            foreach ( $columns as $key => $name ) {
                $style = '';
                if ( self::$output_email ) {
                    $style = 'style="border-bottom:1px solid #aaa;padding:8px;"';
                    if( $key != 'item' && $key != 'name' ) {
                        $style = 'style="border-bottom:1px solid #aaa;padding:8px;text-align:right"';
                    }
                }
                $html_tbody .= sprintf( '<td %s class="%s">%s</td>' . WPDK_CRLF, $style, $key, self::productItemForColumnKey( $product, $key ) );
            }
            $html_tbody .= '</tr>' . WPDK_CRLF;
        }

        /* TFOOT */
        $colspan    = count( $columns ) - 1;
        $rows       = self::rowsSummaryProducts();
        $html_tfoot = '';
        foreach ( $rows as $key => $name ) {
            $value = self::summaryProductsRow( $key );
            $style = '';
            if ( self::$output_email ) {
                $style = 'style="border-bottom:1px solid #aaa;padding:8px"';
            }
            $html_tfoot .= sprintf( '<tr><td %s colspan="%s" class="wpss-invoice-label-%s">%s</td><td align="right" %s class="wpss-invoice-value-%s">%s</td></tr>' . WPDK_CRLF, $style, $colspan, $key, $name, $style, $key, $value );
        }

        $style = '';
        if( self::$output_email ) {
            $style = 'style="border:1px solid #aaa;padding:8px"';
        }

        $html = <<< HTML
    <div class="wpss-invoice-products">
        <table {$style} width="100%" border="0" cellpadding="0" cellspacing="0">
            <thead>
                <tr>{$html_thead}</tr>
            </thead>

            <tbody>
                {$html_tbody}
            </tbody>

            <tfoot>
                {$html_tfoot}
            </tfoot>
        </table>
    </div>
HTML;

        return $html;
    }

    /**
     * Parte in fondo alla fattura con note aggiuntive
     *
     * @access             internal
     * @static
     * @retval string
     *                     HTML della parte più in fondo alla fattura
     */
    private static function htmlDisclaim() {

        /* @todo Prendere dalle impostazioni */
        $disclaim = '';

        $html = <<< HTML
    <div class="wpss-invoice-disclaim">
        {$disclaim}
    </div>
HTML;
        return $html;
    }


    // -----------------------------------------------------------------------------------------------------------------
    // Aux
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Elabora il contenuti di una colonna del riepilogo dei prodotti
     *
     * @access     internal
     * @static
     *
     * @param array  $product Array con le informazioni sul prodotto
     * @param string $key     Identficativo della colonna
     *
     * @retval int|mixed|string Contenuto della cella
     */
    private static function productItemForColumnKey( $product, $key ) {

        switch ( $key ) {
            case 'item':
                $result = $product['id_product'];
                break;

            case 'name':
                /* Recupero eventuale variante */
                $add_title = '';
                if ( isset( $product['id_variant'] ) && !empty( $product['id_variant'] ) ) {
                    /* Costruisce un title per mostrare la variante */
                    $titles = array();
                    $fields = WPXSmartShopProduct::appearanceFields();
                    foreach ( $fields as $key => $value ) {
                        if ( !empty( $product[$key] ) ) {
                            /**
                             * @filters
                             *
                             * @param string $localizable_value
                             * @param int    $id_product
                             * @param string $id_variant
                             * @param array  $variant
                             * @param string $key
                             */
                            $titles[] = apply_filters( 'wpss_product_variant_localizable_value', $product[$key], $product['id_product'], $fields, $product['id_variant'], $product[$key] );

                        }
                    }
                    $add_title = sprintf( ' (%s)', join( ', ', $titles ) );
                }

                $result = $product['product_title'] . $add_title;

                break;

            case 'qty':
                $result = absint( $product['qty'] );
                break;

            case 'price':
                $result = WPXSmartShopCurrency::formatCurrency( floatval( $product['product_amount'] ) );
                if ( !empty( $product['coupon_value'] ) ) {
                    $result = sprintf( '%s (- %s)', $result, $product['coupon_value'] );
                }

                /* Custom discount */
                if( !empty( $product['id_custom_discount']) ) {
                    $result .= sprintf( ' (%s)', $product['id_custom_discount'] );
                }

                break;

            case 'vat':
                $result = 0;
                break;
            case 'amount':
                $qty    = absint( $product['qty'] );
                $amount = floatval( $product['amount'] ) * $qty;
                $result = WPXSmartShopCurrency::formatCurrency( $amount );
                self::$total += $amount;
                break;

            default:
                /**
                 * Usato per gestire i contenuti delle colonne custom del caso si sia agito sul filtro
                 * wpss_invoice_columns_summary_products
                 *
                 * @filters
                 *
                 * @param string $value   Valore della colonna
                 * @param array  $product Array con la descrizione del prodotto
                 * @param string $key     Identificativo della colonna
                 */
                $result = apply_filters( 'wpss_invoice_product_item_column_key', '', $product, $key );
                break;
        }

        return $result;
    }

    /**
     * Elabora il contenuto della riga in fondo al riepilogo dei prodotti acquistati
     *
     * @access     internal
     * @static
     *
     * @param string $key Identificativo della riga
     *
     * @retval mixed|void Contenuto della cella
     */
    private static function summaryProductsRow( $key ) {

        switch( $key ) {
            case 'sub_total':
                $result = WPXSmartShopCurrency::formatCurrency( floatval( self::$total ) );
                break;

            case 'coupon_order':
                $result = '';
                break;

            case 'vat':
                $result = WPXSmartShopCurrency::formatCurrency( floatval( 0 ) );
                break;

            case 'shipping':
                $result = WPXSmartShopCurrency::formatCurrency( floatval( self::$order->shipping ) );
                break;

            case 'total':
                $result = WPXSmartShopCurrency::formatCurrency( floatval( self::$order->shipping ) + floatval( self::$total ) );
                break;

            default:
                /**
                 * Usato per gestire i contenuti delle right custom del caso si sia agito sul filtro
                 * wpss_invoice_rows_summary_products
                 *
                 * @filters
                 *
                 * @param string $value   Valore della colonna
                 * @param string $key     Identificativo della riga
                 */
                $result = apply_filters( 'wpss_invoice_summary_products_row', '', $key );
                break;
        }
        return $result;
    }

}