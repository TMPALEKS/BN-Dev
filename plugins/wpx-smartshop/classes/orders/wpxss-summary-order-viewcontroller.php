<?php
/**
 * @class              WPXSmartShopSummaryOrderViewController
 *
 * @description        Gestisce la visualizzazione del Summary Order
 *
 * @package            wpx SmartShop
 * @subpackage         orders
 * @author             =undo= <g.fazioli@undolog.com>, <g.fazioli@wpxtre.me>
 * @copyright          Copyright (c) 2012 wpXtreme, Inc.
 * @link               http://wpxtre.me
 * @created            13/02/12
 * @version            1.0.0
 *
 * @filename           wpxss-summary-order-viewcontroller
 *
 */

class WPXSmartShopSummaryOrderViewController {

    public static $carrier = false;

    // -----------------------------------------------------------------------------------------------------------------
    // Static values
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Restituisce un array con le colonne standard ddel Summary Order, chiave => label
     *
     * @static
     *
     * @retval mixed|void
     */
    private static function columns() {
        $columns = array(
            'cb'             => '',
            'product'        => __( 'Product', WPXSMARTSHOP_TEXTDOMAIN ),
            'quantity'       => __( 'Qty', WPXSMARTSHOP_TEXTDOMAIN ),
            'price'          => __( 'Price', WPXSMARTSHOP_TEXTDOMAIN ),
            'coupon_product' => __( 'Coupon Code', WPXSMARTSHOP_TEXTDOMAIN ),
            'product_price'  => __( 'Total', WPXSMARTSHOP_TEXTDOMAIN )
        );

        /**
         * @filters
         *
         * @param array $colums Elenco delle colonne; presenza e ordine
         */
        return apply_filters( 'wpss_summary_order_columns', $columns );
    }

    /**
     * Restituisce un array con le chiavi delle righe standard del Summary Order
     *
     * @static
     *
     * @retval mixed|void
     */
    private static function rows() {
        $rows = array(
            'sub_total'   => __( 'Subtotal', WPXSMARTSHOP_TEXTDOMAIN ),
            'coupon_order'=> __( 'Coupon', WPXSMARTSHOP_TEXTDOMAIN ),
            'discount'    => __( 'Discount', WPXSMARTSHOP_TEXTDOMAIN ),
            'vat'         => __( 'VAT', WPXSMARTSHOP_TEXTDOMAIN ),
            'shipping'    => __( 'Shipping', WPXSMARTSHOP_TEXTDOMAIN ),
            'total'       => __( 'Total', WPXSMARTSHOP_TEXTDOMAIN )
        );

        $includes_vat = WPXSmartShop::settings()->product_price_includes_vat();
        if ( $includes_vat ) {
            unset($rows['vat']);
        }

        /**
         * @filters
         *
         * @param array $rows Le righe di default del summary order; presenza e ordine
         */
        return apply_filters( 'wpss_summary_order_rows', $rows );
    }
    
    // -----------------------------------------------------------------------------------------------------------------
    // Display
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Mostra il solo resoconto tabellare
     *
     * @todo Trasformare tutta la catena degli output in return HTML, per adesso metto pezza con ob_start()
     *
     * @static
     * @retval string Restituisce l'html del summary order
     */
    public static function summaryOrder() {

        /* Carico le variabili fisse */
        WPXSmartShopSummaryOrder::init();

        /* Sel il carrello (sessione) è vuoto esco con FALSE */
        if( WPXSmartShopShoppingCart::isCartEmpty() ) {
            return false;
        }

        /* @todo Da eliminare */
        ob_start();

        /**
         * Avverte che il summary order sta per essere visualizzato
         *
         * @todo Cambiare con will_display
         *
         * @action
         */
        do_action( 'wpss_summary_order_will_loaded' );
        do_action( 'wpss_summary_order_will_display' );
        ?>
    <div class="wpss-summary-order-container">
        <?php
            /**
             * @param string $html Codice HTML da inserire prima della tabella del Summary Order
             */
            $before = apply_filters('wpss_summary_order_before', '');
            echo $before;
        ?>
        <table class="wpss-summary-order" border="0" cellpadding="0" cellspacing="0">
            <thead>
                <tr><?php echo self::thead() ?></tr>
            </thead>
            <tbody><?php self::tbody() ?></tbody>
            <tfoot><?php self::tfoot() ?></tfoot>
        </table>
    </div>
    <?php
        $result = ob_get_contents();
        ob_end_clean();

        return $result;
    }

    /**
     * Visualizza il resoconto completo dei prodotti acquistati in forma tabellare modificabile
     *
     * @static
     * @retval bool|WP_Error True se tutto è andato bene, altrimenti viene restituito un oggetto WP_Error
     */
    public static function display() {

        self::$carrier = false;

        $payment_slug = WPXSmartShop::settings()->payment_permalink();
        $payment_permalink = wpdk_permalink_page_with_slug( $payment_slug, kWPSmartShopStorePagePostTypeKey );

        if ( !WPXSmartShopShoppingCart::isCartEmpty() ) {
            ?>
        <form class="wpss-summary-order-form"
              name="wpss-summary-order-form"
              method="post"
              action="<?php echo $payment_permalink ?>">
            <?php

            echo self::summaryOrder();

            do_action( 'wpss_checkout_bill_information' );
            do_action( 'wpss_checkout_shipping_information' );
            ?>
            <div class="wpss-checkout-payment-gateway">
                <?php
                /* @todo Aggiungere controllo se visualizzare o meno questo controllo */
                $display = apply_filters( 'wpxss_button_cash', false );
                if( $display ) {
                    echo self::buttonCash();
                }

                /* @todo Da rimuovere per nuova gestione di sopra */
                //do_action( 'wpss_checkout_bottom_button' );

                $label  = __( 'Payment method', WPXSMARTSHOP_TEXTDOMAIN );
                $choose = WPSmartShopPaymentGateway::choosePaymentGateways( $label, 'wpss-checkout-payment-gateway' );
                if ( is_wp_error( $choose ) ) {
                    /* @todo Gestire errore */
                } else {
                    echo $choose;
                }
                ?>
                <input class="wpss-summary-order-form-bill-button"
                       type="submit"
                       value="<?php _e( 'Purchase', WPXSMARTSHOP_TEXTDOMAIN ) ?>"/>
            </div>
        </form>
        <?php
            return true;
        } else {
            $message = __( 'Your Shopping Cart is empty', WPXSMARTSHOP_TEXTDOMAIN );
            $error   = new WP_Error( 'wpss_error-shopping_cart_is_empty', $message );
            return $error;
        }
    }

    // -----------------------------------------------------------------------------------------------------------------
    // Display render
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Restituisce l'HTML che mostra una menu a tendina con il pulsante per l'acquisto immediato tramite cash.
     * L'acquisto in cash è un acquisto per cassa che permette di acquistare in contanti, tramite Pos o carta di
     * credito. Questo è utile per situazioni 'dal vivo'.
     *
     * @static
     * @retval string HTML
     */
    public static function buttonCash() {

        /* Elementi del menu a tendina */
        $values = WPXSmartShopSummaryOrder::arrayCash();

        /* @todo Filtro da documentare */
        $values = apply_filters( 'wpxss_button_cash_values', $values );

        /* Costruisco il menu a tendina */
        $options = '';
        foreach( $values as $value => $label ) {
            $options .= sprintf( '<option value="%s">%s</option>', $value, $label );
        }

        /* @todo Filtro da documentare */
        $select_class = apply_filters( 'wpxss_button_cash_select_class', array( 'wpdk-form-select' ) );
        if ( is_array( $select_class ) && !empty( $select_class ) ) {
            $select_class = join( ' ', $select_class );
        }

        /* @todo Filtro da documentare */
        $submit_class = apply_filters( 'wpxss_button_cash_submit_class', array( 'wpdk-form-button' ) );
        if ( is_array( $submit_class ) && !empty( $submit_class ) ) {
            $submit_class = join( ' ', $submit_class );
        }

        /* Localization */
        $label      = new stdClass();
        /* @todo Filtro da documentare */
        $label->pay = apply_filters( 'wpxss_button_cash_label', __( 'Paid', WPXSMARTSHOP_TEXTDOMAIN ) );

        /* HTML output */
        $html = <<< HTML
<select class="{$select_class}" name="wpxss_cash_values">
{$options}
</select>
<input name="wpxss_cash" class="{$submit_class}" type="submit" value="{$label->pay}" />
HTML;
        return $html;
    }

    /**
     * Restituisce l'head della tabella
     *
     * @static
     *
     * @retval string HTML della colonna dell'head
     */
    private static function thead() {
        $columns = self::columns();
        $ths     = '';
        foreach ( $columns as $key => $column ) {
            $th = '';
            if ( $key != 'cb' ) {
                /**
                 * @filters
                 *
                 * @param string $column Titolo colonna
                 * @param string $key    ID della colonna
                 */
                $column = apply_filters( 'wpss_summary_order_column_render', $column, $key );

                if ( $key == 'product' ) {
                    $th = sprintf( '<th class="wpss-summary-order-column-%s" colspan="2">%s</th>', $key, $column );
                } else {
                    $th = sprintf( '<th class="wpss-summary-order-column-%s">%s</th>', $key, $column );
                }
            }
            $ths .= $th;
        }
        return $ths;
    }

    /**
     * Costruisce il tbody - corpo centrale con tutte le righe del prodotto
     *
     * @static
     */
    private static function tbody() {

        /* Recupero e controllo se ci sono prodotti caricati nel carello */
        $products = WPXSmartShopShoppingCart::products();

        /* Recupero le info dettagliate sui prodotti in sessione/cart */
        $id_product_keys = array_keys( $products );

        /* Loop */
        $pseudo_cart = array();
        foreach ( $id_product_keys as $id_product_key ) :
            $product = WPXSmartShopSession::product( $id_product_key );

            $id_product = $product['id_product'];
            $qty        = absint( $product['qty'] );

            /*
            * Qui stiamo costruendo elemento per elemento quindi dobbiamo simulare, man mano che emettaimo righe
            * un caricamento nel carrello. In quest'ultimo, infatti, i prodotti sono già caricati a livello di
            * quantità ma non possiamo basarci su questo valore altrimenti i conteggi risulterebbero sbagliati
            * quindi creiamo uno pseudo carrello che incrementiamo per ogni riga e poi passiamo al metodo
            * WPXSmartShopProduct::price()
            */
            if ( !isset( $pseudo_cart[$id_product] ) ) {
                $pseudo_cart[$id_product] = 0;
            }

            $row_subtotal = WPXSmartShopSummaryOrder::processing( $product, $id_product_key, $pseudo_cart[$id_product] ); ?>
        <tr>
            <?php
            foreach ( self::columns() as $key => $column ) {
                self::tbody_cell( $key, $id_product_key, $row_subtotal, $pseudo_cart[$id_product] );
            }

            $pseudo_cart[$id_product] += $qty; ?>
        </tr>

        <?php
        endforeach;

        WPXSmartShopSummaryOrder::total();
    }

    /**
     * Celle del tbody, in base alle colonne
     *
     * @static
     *
     * @param string $column
     * @param string $id_product_key Stringa jSON codificata base 64 con le informazioni sul prodotto
     * @param float  $row_subtotal
     */
    private static function tbody_cell( $column, $id_product_key, $row_subtotal, $nth = 0 ) {

        /* Recupero informazioni sul prodotto */
        $product    = WPXSmartShopSession::product($id_product_key);
        $id_product = $product['id_product'];

        /* Recupero eventuale variante */
        $id_variant = '';
        $add_title  = '';
        if ( isset( $product['id_variant'] ) && !empty( $product['id_variant'] ) ) {
            $id_variant = $product['id_variant'];
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
                    $titles[] = apply_filters( 'wpss_product_variant_localizable_value', $product[$key], $id_product, $id_variant, $fields, $product[$key] );
                }
            }
            $add_title = sprintf( ' (%s)', join( ', ', $titles ) );
        }

        $title    = $product['product_title'] . $add_title;

        switch ( $column ) {
            case 'cb': ?>
                <td class="wpss-summary-order-cell-cb">
                    <input type="button"
                           data-id_product_key="<?php echo $id_product_key ?>"
                           class="delete wpdk-tooltip"
                           value="<?php _e( 'Delete', WPXSMARTSHOP_TEXTDOMAIN ) ?>"
                           title="<?php _e( 'Delete', WPXSMARTSHOP_TEXTDOMAIN ) ?>"/>
                </td>
            <?php
                break;

            case 'product':
                $shipping_class = '';
                $title_attr = '';
                if ( WPXSmartShopProduct::shipping( $id_product ) == '1' ) {
                    self::$carrier = true;
                    $shipping_class = 'wpdk-tooltip wpss-summary-order-cell-shipping-flag';
                    $title_attr = sprintf('title="%s"', __( 'Shipping', WPXSMARTSHOP_TEXTDOMAIN ));
                } ?>
                <td <?php echo $title_attr ?> data-placement="left" class="wpss-summary-order-cell-product <?php echo $shipping_class ?>">
                    <?php
                    /**
                     * Chiamato quando viene resa una cella all'interno del `<tbody>` della tabella,
                     * comprese quelle di sistema.
                     *
                     * @filters
                     *
                     * @param string $title          Titolo
                     * @param string $column         ID della colonna
                     * @param string $id_product_key ID della sessione (ID prodotto + varianti) in jSON codificato base 64
                     */
                    echo apply_filters( 'wpss_summary_order_cell', $title, $column, $id_product_key ) ?></td><?php
                break;

            case 'quantity':
                /**
                 * @filters
                 *
                 * @param int    $qty            Quantità prodotti
                 * @param string $colum          ID della colonna
                 * @param string $id_product_key ID della sessione (ID prodotto + varianti) in jSON codificato base 64
                 */
                $qty = apply_filters( 'wpss_summary_order_cell', WPXSmartShopShoppingCart::productQuantity( $id_product_key ), $column, $id_product_key );
                ?>
                <td class="wpss-summary-order-cell-quantity">
                    <input type="text"
                           data-value_undo="<?php echo $qty ?>"
                           data-id_product_key="<?php echo $id_product_key ?>"
                           size="2"
                           class="qty wpdk-form-input wpdk-tooltip"
                           name="wpssCartQty"
                           title="<?php _e('You can change your product\'s quantity', WPXSMARTSHOP_TEXTDOMAIN ) ?>"
                           value="<?php echo $qty ?>"/>
                </td><?php
                break;

            case 'price':
                ?>
                <td class="wpss-summary-order-cell-price">
                    <?php
                    $price        = WPXSmartShopProduct::priceBase( $id_product );
                    $format_price = WPXSmartShopCurrency::formatCurrency( floatval( $price ) ) . WPXSmartShopCurrency::currencySymbol();

                    /**
                     * @filters
                     *
                     * @param float  $price          Prezzo
                     * @param string $colum          ID della colonna
                     * @param string $id_product_key ID della sessione (ID prodotto + varianti) in jSON codificato base 64
                     */
                    echo apply_filters( 'wpss_summary_order_cell', $format_price, $column, $id_product_key )
                    ?></td><?php
                break;

            case 'coupon_product':
                $placeholder = __( 'Coupon', WPXSMARTSHOP_TEXTDOMAIN );
                $coupon      = isset( $product['coupon_uniqcode'] ) ? $product['coupon_uniqcode'] : '';
                $html        = <<< HTML
<td class="wpss-summary-order-cell-coupon_product">
    <input type="text"
           data-id_product_key="{$id_product_key}"
           value="{$coupon}"
           placeholder="{$placeholder}"
           name="wpssSummaryOrderProductCoupon"
           size="14"
           class="wpss-summary-order-product-coupon wpdk-form-input" /></td>
HTML;
                /**
                 * Se si vuole alterare il campo input per l'inserimento dei coupon prodotto
                 *
                 * @filters
                 *
                 * @param int    $html           HTML del campo input
                 * @param string $colum          ID della colonna
                 * @param string $id_product_key ID della sessione (ID prodotto + varianti) in jSON codificato base 64
                 */
                echo apply_filters( 'wpss_summary_order_cell', $html, $column, $id_product_key );
                break;

           case 'product_price': ?>
                <td title="<?php echo WPXSmartShopSummaryOrder::descriptionPriceRules() ?>" class="wpdk-tooltip wpss-summary-order-cell-product_price">
                    <?php
                    /**
                     * @filters
                     *
                     * @param float  $price          Totlae di riga
                     * @param string $colum          ID della colonna
                     * @param string $id_product_key ID della sessione (ID prodotto + varianti) in jSON codificato base 64
                     */
                    echo
                        apply_filters( 'wpss_summary_order_cell', WPXSmartShopCurrency::formatCurrency( $row_subtotal ), $column, $id_product_key ) .
                            WPXSmartShopCurrency::currencySymbol(); ?>
                </td><?php
           break;
            default:
                ?>
                <td class="wpss-summary-order-cell-<?php echo $column ?>">
                    <?php
                    /**
                     * Caso default - non dovrebbe mai essere utilizzato
                     *
                     * @filters
                     *
                     * @param string $empty          Vuoto per default
                     * @param string $colum          ID della colonna
                     * @param string $id_product_key ID della sessione (ID prodotto + varianti) in jSON codificato base 64
                     */
                    echo apply_filters( 'wpss_summary_order_cell', '', $column, $id_product_key ); ?>
                </td>
                <?php
                break;
        }
    }

    /**
     * Costruisce il footer della tabella
     *
     * @static
     *
     */
    private static function tfoot() {
        /* Mi serve per il count e il colspan */
        $colspan = count( self::columns() ) - 1;
        $rows    = self::rows();
        foreach ( $rows as $key => $row ) :
            if ( $key == 'shipping' && !self::$carrier ) continue; ?>
        <tr class="wpss-summary-order-row-<?php echo $key ?>">
            <td colspan="<?php echo $colspan ?>" class="wpss-summary-order-description-<?php echo $key ?>">
                <?php
                if ( $key == 'coupon_order' ) {
                    $order_uniqcode = WPXSmartShopSession::orderCouponUniqCode();
                    $placeholder    = __( 'Insert your Coupon code', WPXSMARTSHOP_TEXTDOMAIN );
                    $html           = <<< HTML
 <input name="wpssSummaryOrderOrderCoupon"
type="text"
class="wpss-summmary-order-order-coupon wpdk-form-input"
value="{$order_uniqcode}"
placeholder="{$placeholder}"/>
HTML;
                    $row .= $html;
                } elseif ( $key == 'vat' ) {
                    /**
                     * @filters
                     *
                     * @param float $vat Valore IVA questo store
                     */
                    $vat = apply_filters( 'wpss_summary_order_vat', WPXSmartShopSummaryOrder::$vatPercentage );
                    $row = sprintf( '%s (%s) %%', $row, $vat );
                }
                /**
                 * Chiamato quando si costruisce il contenuto della prima cella del footer
                 *
                 * @filters
                 *
                 * @param string $row Contenuto riga
                 * @param string $key ID della riga
                 */
                echo apply_filters( 'wpss_summary_order_row_render', $row, $key );

                if ( $key == 'shipping' ) {
                    $id_carrier = WPXSmartShopSession::orderShippingCarrier();
                    if ( empty( $id_carrier ) ) {
                        $id_carrier = WPXSmartShop::settings()->default_carrier();
                    }
                    echo WPXSmartShopCarriers::carriersSelect( 'id_carrier', $id_carrier );
                } ?>
            </td>
            <td class="wpss-summary-order-row-value-<?php echo $key ?>">
                <?php self::tfoot_cell( $key ) ?>
            </td>
        </tr>
        <?php endforeach;
    }


    /**
     * Rende il contenuto delle celle, delle righe, nel footer della tabella
     *
     * @static
     * @param $key
     */
    private static function tfoot_cell( $key ) {
        $result = '';
        switch ( $key ) {
            case 'sub_total':
                $result =
                    WPXSmartShopCurrency::formatCurrency( WPXSmartShopSummaryOrder::$subtotal ) . WPXSmartShopCurrency::currencySymbol();
                break;
            case 'coupon_order':
                $result = '-' . WPXSmartShopCurrency::formatCurrency( WPXSmartShopSummaryOrder::$coupon ) .
                    WPXSmartShopCurrency::currencySymbol();
                break;
            case 'discount':
                $result = '-' . WPXSmartShopCurrency::formatCurrency( WPXSmartShopSummaryOrder::$discount ) .
                    WPXSmartShopCurrency::currencySymbol();
                break;
            case 'shipping':
                $result = WPXSmartShopCurrency::formatCurrency( WPXSmartShopSummaryOrder::$shipping ) .
                    WPXSmartShopCurrency::currencySymbol();
                break;
            case 'vat':
                $result = WPXSmartShopCurrency::formatCurrency( WPXSmartShopSummaryOrder::$vatValue ) .
                    WPXSmartShopCurrency::currencySymbol();
                break;
            case 'total':
                $result = WPXSmartShopCurrency::formatCurrency( WPXSmartShopSummaryOrder::$total ) .
                    WPXSmartShopCurrency::currencySymbol();
                break;
        }
        /**
         * Chiamato quando si costruisce il contenuto della seconda cella del footer, che di solito contiene valori
         *
         * @filters
         *
         * @param string $value   Contenuto cella
         * @param string $row_key ID della riga
         */
        echo apply_filters( 'wpss_summary_order_row_render_content', $result, $key );
    }


}
