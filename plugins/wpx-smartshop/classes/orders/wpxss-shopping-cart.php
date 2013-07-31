<?php
/**
 * @class              WPXSmartShopShoppingCart
 *
 * @description        Gestisce tutte le informazioni e le view relative al carrello elettronico, soprattutto lato
 *                     ront end
 *
 * @package            wpx SmartShop
 * @subpackage         orders
 * @author             =undo= <g.fazioli@undolog.com>, <g.fazioli@wpxtre.me>
 * @copyright          Copyright (c)2012 wpXtreme, Inc.
 * @created            14/12/11
 * @version            1.0
 *
 * @filename           wpxss-shopping-cart
 *
 * @todo               Trasferire lacuni metodi nel modello costituito dalla sessione. Il carrello, come il summary
 *                     order, devo occuparsi solo di visualizzare, al limite mantenere dei meotdi di alias o di comodità
 *
 */
class WPXSmartShopShoppingCart {

    // -----------------------------------------------------------------------------------------------------------------
    // Static values
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Quando è stato impostato il sistema in modo tale che le funzioni di acquisto siano visibili solo dagli utenti
     * registrati, viene richiesta questa sringa.
     *
     * @filter     wpss_message_you_have_to_login
     * @filter     wpss_message_html_you_have_to_login
     *
     * @static
     * @retval mixed|void
     */
    public static function messageYouHaveToLogin() {
        /**
         * @filters
         *
         * @param string $message Stringa con il messaggio di obbligo di login per acquistare
         */
        $message = apply_filters( 'wpss_message_you_have_to_login', __( 'Log in to Purchase', WPXSMARTSHOP_TEXTDOMAIN ) );
        $html    = sprintf( '<span class="wpss-message you-have-to-login">%s</span>', $message );

        /**
         * @filters
         *
         * @param string $message Stringa HTML con il messaggio di obbligo di login per acquistare
         */
        return apply_filters( 'wpss_message_html_you_have_to_login', $html );
    }

    /**
     * Elenco delle colonne da mostrare nel carrello
     *
     * @static
     * @retval array Array keypair con le colonne da mostrare nel carrello
     */
    public static function columns() {
        $columns = array(
            'cb'      => '',
            'product' => __( 'Product', WPXSMARTSHOP_TEXTDOMAIN ),
            'qty'     => __( 'Qty', WPXSMARTSHOP_TEXTDOMAIN ),
            'price'   => __( 'Price', WPXSMARTSHOP_TEXTDOMAIN ),
        );

        /* @todo Documnentare */
        return apply_filters( 'wpss_shopping_cart_columns', $columns );
    }

    /**
     * Elenco delle righe (footer) da mostrare nel carrello
     *
     * @static
     * @retval array Array keypair con le righe footer da mostrare nel carrello
     */
    public static function rows() {
        $columns = array(
            'subtotal' => __( 'Subtotal', WPXSMARTSHOP_TEXTDOMAIN ),
            'tax'      => __( 'Tax', WPXSMARTSHOP_TEXTDOMAIN ),
            'total'   => __( 'Total', WPXSMARTSHOP_TEXTDOMAIN ),
        );

        /* @todo Documnentare */
        return apply_filters( 'wpss_shopping_cart_rows', $columns );
    }

    // -----------------------------------------------------------------------------------------------------------------
    // Commodity
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Svuota il carrello dai prodotti, ma mantiene le informazioni sull'ordine
     *
     * @static
     *
     */
    public static function emptyCart() {

        /* Svuota solo il carrello e non l'ordine che dev'essere quindi annullato */
        WPXSmartShopSession::emptyProducts();

        /**
         * Il carrello è stato svuotato. Questa azione viene chiamata quando il carrello viene svuotato. Di questa ne
         * esiste anche la versione Javascript.
         *
         * @action
         */
        do_action( 'wpss_cart_empty' );
    }

    // -----------------------------------------------------------------------------------------------------------------
    // Read
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Restituisce la lista dei prodotti caricata nel carrello
     *
     * @alias
     * @static
     * @retval array Ritorna sempre un array. Se vuoto nessun prodotto nel carrello
     *
     */
    public static function products() {
        return WPXSmartShopSession::products();
    }

    /**
     * Restituisce la quantità di un prodotto nel carrello
     *
     * @static
     *
     * @param string $id_product_key
     *  ID del prodotto (ID prodotto + varianti) jSON codificato base 64
     *
     * @retval int
     *  Quantità del prodotto $id_product_key
     */
    public static function productQuantity( $id_product_key ) {
        $products = WPXSmartShopSession::products();
        return absint( $products[$id_product_key]['qty'] );
    }

    /**
     * Restituisce il numero totale degli elementi del carrello e il numero totale dei prodotti, cioè la somma delle
     * quantità. Nel carrello, ad esempio, posso caricare due tipi di prodotti diversi (count) ma acquistare 5 quantità
     * del secondo (total). In questo caso avrà 2 (count) prodotti ma 6 in totale (total).
     *
     * @static
     * @retval array Una array con due chiavi 'count' e 'total':
     *
     *  array(2) {
     *   ["count"]=> int(2)
     *   ["total"]=> int(3)
     * }
     *
     */
    public static function productNumbers() {
        $products = WPXSmartShopSession::products();
        $total    = 0;
        foreach ( $products as $product ) {
            $total += $product['qty'];
        }
        $result = array(
            'count'    => count( $products ),
            'total'    => $total
        );

        return $result;
    }


    // -----------------------------------------------------------------------------------------------------------------
    // Edit
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Aggiorna la quantità di un prodotto
     *
     * @static
     *
     * @param string $id_product_key Stringa jSON codificata base 64
     * @param int    $qty Nuova quantità
     *
     * @retval int Nuova quantità. Questa potrebbe essere differente da quella passata negli inputs
     */
    public static function updateProductQuantity( $id_product_key, $qty ) {
        $products = WPXSmartShopSession::products();

        $id_product = $products[$id_product_key]['id_product'];
        $id_variant = $products[$id_product_key]['id_variant'];
        $variant    = WPXSmartShopStats::variant( $products );

        /* Quantità della riga prima del cambio */
        $current_qty = absint( $products[$id_product_key]['qty'] );

        /* Numero di prodotti nel carrello solo con id - meno questo */
        $qty_cart = WPXSmartShopSession::countProductWithID( $id_product );
        if ( $qty_cart > 0 ) {
            $qty_cart = $qty_cart - $current_qty;
        }

        /**
         * Aggiornamento della quantità di prodotto nel Widget carrello
         *
         * @filters
         * @todo Passare anche la vecchia quantità $current_qty
         *
         * @param int    $qty        Quantità
         * @param int    $id_product ID del prodotto
         * @param string $id_variant ID della variante
         * @param array  $variant    Array con le indicazioni sulla variante (Colore, Modello, etc...)
         *
         * @retval int|WP_Error
         * Può restituire direttamente la qty alterata oppure fornire una descrizione più dettagliata tramite un
         * oggetto WP_Error con codice 'alter_quantity', nel messaggio il perchè e nei data la nuova quantità.
         */
        $qty = apply_filters( 'wpss_cart_update_quantity', $qty, $id_product, $id_variant, $variant );

        if( is_wp_error( $qty ) && WPDKWatchDog::getErrorCode( $qty ) == 'alter_quantity' ) {
            $alter_warning = $qty;
            $qty = $alter_warning->get_error_data();
        }

        /* Magazzino */
        $warehouse = WPXSmartShopProduct::warehouse( $id_product );
        $qty_store = $warehouse['qty'];

        if ( wpdk_is_infinity( $qty_store ) ) {
            $qty_store = $qty + $qty_cart + 1;
        }

        if ( ( $qty + $qty_cart ) > $qty_store ) {
            $qty     = $current_qty;
            $message = sprintf( __( 'Warning! Quantity overflow. In the warehouse there are %s products available.', WPXSMARTSHOP_TEXTDOMAIN ), $qty_store );
            $warning = new WP_Error( 'wpss_warning-shopping_cart_update_quantity', $message, $qty_store );
            return $warning;
        }

        $products[$id_product_key]['qty'] = $qty;
        WPXSmartShopSession::products( $products );

        WPXSmartShopSession::deleteProductCoupons($id_product_key);

        /** @var $qty int|WP_Error */
        return isset( $alter_warning ) ? $alter_warning : $qty;
    }

    /**
     * Elimina un prodotto dal carrello
     *
     * @static
     *
     * @param string $id_product_key String jSON codificata base 64
     */
    public static function deleteProduct( $id_product_key ) {
        $products = WPXSmartShopSession::products();

        /* @todo Aggiungere action */

        /* Rimuove gli eventuali coupon anche dalla Transient */
        WPXSmartShopSession::deleteProductCoupons($id_product_key);

        unset( $products[$id_product_key] );
        WPXSmartShopSession::products( $products );
    }


    // -----------------------------------------------------------------------------------------------------------------
    // has/is zone
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Controlla se il carrello della spesa è pieno o vuoto
     *
     * @static
     * @retval bool True se è vuoto. False se è pieno
     *
     */
    public static function isCartEmpty() {
        $products = WPXSmartShopSession::products();
        return empty( $products );
    }


    // -----------------------------------------------------------------------------------------------------------------
    // can zone
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Verifica che sia possibile mostrare funzioni relative all'aggiunta al carrello.
     * In pratica controlla se a livello di impostazioni è stato scelto di far interagire con il carrello solo gli
     * utenti registrati. Se così è, allora viene controllato che un utente sia loggato.
     *
     * @deprecated Use canDisplayAddShoppingCart()
     *
     * @static
     * @retval bool Restituisce true se è permesso di visualizzare funzioni di carrello. False, utente non loggato
     */
    public static function canDisplayAddToCart() {
        _deprecated_function( __FUNCTION__, '1.0', 'canDisplayAddShoppingCart()');

        $settings = WPXSmartShop::settings();
        if ( $settings->shopping_cart_display_for_user_logon_only() ) {
            if ( !is_user_logged_in() ) {
                return false;
            }
        }
        return true;
    }

    /**
     * Controlla se un prodotto (insieme alla sua eventuale variante) può esserw aggiunto al carrello. Questo metodo
     * esegue una serie di controlli che, in caso falliscono, scatenano dei relativi filtri.
     * La sequenza dei controlli è la seguente:
     * 1) Il negozio è aperto
     * 2) Solo utenti loggati
     * 3) Disponibilità del prodotto (magazzino e data)
     * 4) Magazzino + qty stesso prodotto nel carello
     * 5) Acquistabilità (Purchasable)
     *
     * @static
     *
     * @param int $id_product
     * @param string $id_variant
     *
     * @retval bool|WP_Error
     */
    public static function canDisplayAddShoppingCart( $id_product, $id_variant = '' ) {

        $result   = true;
        $settings = WPXSmartShop::settings();
        $general  = $settings->general();

        /* 1. The shop is open? */

        if( isset( $general['shop_open']) && 'n' == $general['shop_open'] ) {
            $result = apply_filters( 'wpxss_shopping_cart_shop_closed', false );
            if ( $result === false ) {
                $message = __( 'Shop Closed for maintenance', WPXSMARTSHOP_TEXTDOMAIN );
                $error   = new WP_Error( 'wpss_error-shopping_cart_shop_closed', $message, array( $id_product, $id_variant ) );
                return $error;
            }
            return $result;
        }


        /* 2. Mostro il bottone solo per gli utenti loggati */

        if ( $settings->shopping_cart_display_for_user_logon_only() ) {
            if ( !is_user_logged_in() ) {
                /* @todo Filtro da documentare */
                $result = apply_filters( 'wpxss_shopping_cart_display_for_user_login_only', false );
                if ( $result === false ) {
                    $message = apply_filters( 'wpss_message_you_have_to_login', __( 'Log in to Purchase', WPXSMARTSHOP_TEXTDOMAIN ) );
                    $error   = new WP_Error( 'wpss_error-shopping_cart_you_have_to_login', $message, array( $id_product, $id_variant ) );
                    return $error;
                }
                return $result;
            }
        }

        /* 3. Disponibilità prodotto */

        if( !WPXSmartShopProduct::isAvailable( $id_product ) ) {
            /* @todo Filtro da documentare */
            $result = apply_filters( 'wpxss_shopping_cart_product_not_available', false );
            if ( $result === false ) {
                $message = __( 'Product not available', WPXSMARTSHOP_TEXTDOMAIN );
                $error   = new WP_Error( 'wpss_error-shopping_cart_product_not_available', $message, array( $id_product, $id_variant ) );
                return $error;
            }
            return $result;
        }


        /* 4. Numero di prodotti nel carrello solo con id */

        $qty_cart = WPXSmartShopSession::countProductWithID( $id_product );

        /* Magazzino */
        $warehouse = WPXSmartShopProduct::warehouse( $id_product );
        $qty_store = $warehouse['qty'];

        if ( wpdk_is_infinity( $qty_store ) ) {
            $qty_store = $qty_cart + 1;
        }

        if ( ( $qty_store - $qty_cart ) <= 0 ) {
            /* @todo Filtro da documentare */
            $result = apply_filters( 'wpxss_shopping_cart_stocks_sold_out', false, $id_product, $id_variant );
            if ( $result === false ) {
                $message =  __( 'Product sold out', WPXSMARTSHOP_TEXTDOMAIN );
                $error   = new WP_Error( 'wpss_error-shopping_cart_stocks_sold_out', $message, array( $id_product, $id_variant ) );
                return $error;
            }
            return $result;
        }

        /* 5. Controllo sull'acquistabilità */

        if ( !WPXSmartShopProduct::isPurchasable( $id_product ) ) {
            /* @todo Filtro da documentare */
            $result = apply_filters( 'wpxss_shopping_cart_puchasable', false, $id_product, $id_variant );
            if ( $result === false ) {
                $message = __( 'Product not available', WPXSMARTSHOP_TEXTDOMAIN );
                $error   = new WP_Error( 'wpss_error-shopping_cart_product_not_purchasable', $message, array( $id_product, $id_variant ) );
                return $error;
            }
            return $result;
        }

        /**
         * @filters
         *
         * @param bool $enabled True se posso aggiungere al carrello
         */
        $result = apply_filters( 'wpxss_cart_add_enabled', $result, $id_product );

        return $result;
    }

    // -----------------------------------------------------------------------------------------------------------------
    // Views
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Visualizza il carrello. Questo metodo viene utilizzato dal Widget e dalla parte Ajax.
     *
     * @static
     *
     * @param array $vargs
     *
     * @retval void
     */
    public static function cart( $vargs = array() ) {
        /* Get view options */

        /* Recupero il link alla pagina di checkout, memorizzata nelle options */
        $checkout_slug = WPXSmartShop::settings()->checkout_permalink();
        $checkout_permalink = wpdk_permalink_page_with_slug( $checkout_slug, kWPSmartShopStorePagePostTypeKey );

        /* Recupero e controllo se ci sono prodotti caricati nel carello */
        $products = self::products();

        if ( empty( $products ) ) {
            /* @todo Aggiungere filtro */
            $message = apply_filters( 'wpss_shopping_cart_message_is_empty', __( 'Your Shopping Cart is empty', WPXSMARTSHOP_TEXTDOMAIN ) );
            $html = <<< HTML
    <p>{$message}</p>
HTML;
        } else {

            /* Costruisco lo colonne */
            $columns    = self::columns();
            $html_thead = '';
            foreach ( $columns as $column_key => $column ) {
                if ( $column_key != 'cb' ) {
                    if ( $column_key == 'product' ) {
                        $th = sprintf( '<th class="wpss-shopping-cart-column-product" colspan="2">%s</th>', $column );
                    } else {
                        $th = sprintf( '<th class="wpss-shopping-cart-column-%s">%s</th>', $column_key, $column );
                    }
                    $html_thead .= apply_filters( 'wpss_shopping_cart_column-render', $th, $column_key, $column );
                }
            }

            /* Costruisco il tbody */
            $html_tbody   = '';
            $total_qty    = 0;
            $sub_total    = 0;
            $vat          = WPSmartShopShippingCountries::vatShop();
            $includes_vat = WPXSmartShop::settings()->product_price_includes_vat();
            $pseudo_cart  = array();

            /* key è $id_product_key, vedi WPXSmartShopAjax::action_cart_add_product() */
            foreach ( $products as $id_product_key => $product ) {
                $ukey       = WPXSmartShopSession::decodeProductKey( $id_product_key );
                $id_product = $ukey['id_product'];

                /* Recupero eventuale variante */
                $id_variant = '';
                $title      = '';
                if ( isset( $ukey['variant'] ) && !empty( $ukey['variant'] ) ) {
                    $id_variant = key( $ukey['variant'] );
                    $titles     = array();
                    foreach ( $ukey['variant'][$id_variant] as $variant ) {

                        /**
                         * @filters
                         *
                         * @param string $localizable_value
                         * @param int    $id_product
                         * @param string $id_variant
                         * @param array  $variant
                         * @param string $key
                         */
                        $titles[] = apply_filters( 'wpss_product_variant_localizable_value', $variant, $id_product, $id_variant, $ukey['variant'], $variant );
                    }
                    $title = join( ', ', $titles );
                }

                $qty = intval( $product['qty'] );

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

                // uncomment for count in cart
                // $count_in_cart = WPXSmartShopSession::countProductWithID( $id_product );
                $price = WPXSmartShopProduct::price( $id_product, $qty, $id_variant, $pseudo_cart[$id_product] );

                $pseudo_cart[$id_product] += $qty;

                $total_qty += $qty;
                $sub_total += $price;

                $tds = '';
                foreach ( $columns as $column_key => $column ) {
                    switch ( $column_key ) {
                        case 'cb':
                            $label = __( 'Delete', WPXSMARTSHOP_TEXTDOMAIN );
                            $td    = sprintf( '<td class="wpss-shopping-cart-cell-%s"><input type="button" class="delete" data-id_product_key="%s" value="%s" title="%s" /></td>', $column_key, $id_product_key, $label, $label );
                            break;
                        case 'product':
                            $td = sprintf( '<td class="wpdk-tooltip wpss-shopping-cart-cell-%s" title="%s">%s</td>', $column_key, $title, $product['product_title']);
                            break;
                        case 'qty':
                            $td = sprintf( '<td class="wpss-shopping-cart-cell-%s"><input type="text" name="wpssCartQty" size="2" class="qty" data-id_product_key="%s" data-value_undo="%s" value="%s" /></td>', $column_key, $id_product_key, $qty, $qty );
                            break;
                        case 'price':
                            $td = sprintf( '<td class="wpss-shopping-cart-cell-%s">%s</td>', $column_key, WPXSmartShopCurrency::formatCurrency( $price ) );
                            break;
                        default:
                            /* @todo Da completare */
                            $td = apply_filters( 'wpss_shopping_cart_column_render', '', $column_key );
                            break;
                    }
                    $tds .= $td;
                }
                $html_tbody .= sprintf( '<tr>%s</tr>', $tds );
            }

            /* Se i prodotti avevano già l'IVA */
            if ( $includes_vat ) {
                $total = $sub_total;
            } else {
                $total = $sub_total + ( $sub_total * $vat / 100 );
            }

            /* Costruisco il footer */
            $html_footer = '';
            $rows = self::rows();

            /* Righe da non mostrare in base alle impostazioni */
            if ( $includes_vat ) {
                unset( $rows['subtotal'] );
                unset( $rows['tax'] );
            }

            $colspan_2 = count( $columns ) - 2;
            $colspan_3 = count( $columns ) - 1;

            foreach( $rows as $row_key => $row ) {
                switch( $row_key ) {
                    case 'subtotal':
                        $td = sprintf( '<td colspan="%s">%s</td><td>%s</td><td>%s</td>', $colspan_2, $row, $total_qty, WPXSmartShopCurrency::formatCurrency( $sub_total ) );
                        break;
                    case 'tax':
                        $td = sprintf( '<td colspan="%s">%s</td><td>%s %%</td><td>%s</td>', $colspan_2, $row, $vat, WPXSmartShopCurrency::formatCurrency( ( $sub_total * $vat / 100 ) ) );
                        break;
                    case 'total':
                        $td = sprintf( '<td colspan="%s">%s</td><td>%s</td>', $colspan_3, $row,  WPXSmartShopCurrency::formatCurrency( $total ) );
                        break;
                    default:
                        break;
                }
                $html_footer .= sprintf( '<tr>%s</tr>', $td );
            }

            /* Bottoni */
            $html_button_empty    = sprintf( '<input id="clearWidgetCart" type="button" class="wpss-cart-empty-button" value="%s" />', __( 'Empty Cart', WPXSMARTSHOP_TEXTDOMAIN ) );
            $html_button_checkout = sprintf( '<input type="submit" class="wpss-cart-checkout-button" value="%s" />', __( 'Checkout', WPXSMARTSHOP_TEXTDOMAIN ) );

            if ( !WPXSmartShop::settings()->shopping_cart_display_empty_button() ) {
                $html_button_empty = '';
            }

            $html = <<< HTML
    <table class="wpss-cart-widget-table" border="0" cellpadding="0" cellspacing="0">
        <thead>
            <tr>{$html_thead}</tr>
        </thead>
        <tbody>{$html_tbody}</tbody>
        <tfoot>{$html_footer}</tfoot>
    </table>
    <div class="command">
        <form action="{$checkout_permalink}" method="post">
            {$html_button_empty}
            {$html_button_checkout}
        </form>
    </div>
HTML;
        }

        return $html;
    }


    // -----------------------------------------------------------------------------------------------------------------
    // Display
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Restituisce l'HTML del bottone per aggiungere al carrello oppure dei messaggi per avvertire o che il prodotto
     * non è disponibile o per obbligare al login.
     *
     * @static
     *
     * @param int    $id_product ID del prodotto che si vuole acquistare
     * @param string $id_variant ID della variante. Questo puà essere empty
     * @param string $class      Classe aggiuntiva del bottone INPUT
     *
     * @retval string|WP_Error HTML del bottone o oggetto WP_Error in caso di errore grave
     */
    public static function buttonAddShoppingCart( $id_product, $id_variant = '', $class = '' ) {

        /* Se questa torna false o null l'errore è abbastanza grave */
        $product = WPXSmartShopProduct::product( $id_product );
        if ( !$product ) {
            $message = __( 'Wrong product parameter', WPXSMARTSHOP_TEXTDOMAIN );
            $error   = new WP_Error( 'wpss_error-product_wrong_product_parameter', $message, $product );
            return $error;
        }

        /* Informazioni utile per il bottone carrello */
        $data_product = self::jSONEncodeWithProductID( $product, $id_variant );
        $product_name = $product->post_name;

        $result = self::canDisplayAddShoppingCart( $id_product, $id_variant );

        if ( !is_wp_error( $result ) ) {

            /**
             * @filters
             *
             * @param string $label      Label del bottone
             * @param object $product    Record del prodotto
             * @param string $id_variant ID della variante
             */
            $label = apply_filters( 'wpss_cart_add_to_cart_button_label', __( 'Add to cart', WPXSMARTSHOP_TEXTDOMAIN ), $product, $id_variant );

            $html = <<< HTML
<div class="wpss-cart-add">
<input data-id_product="{$id_product}"
   data-id_variant="{$id_variant}"
   data-product="{$data_product}"
   type="button"
   class="wpss-cart-add {$product_name} {$class}"
   value="{$label}"/>
</div>
HTML;

        } else {
            $label = $result->get_error_message();
            $html = <<< HTML
<div class="wpss-cart-add-not-available">
    <p>{$label}</p>
</div>
HTML;
        }
        return $html;
    }

    // -----------------------------------------------------------------------------------------------------------------
    // Prepare
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Restituisce una stringa codificata base64 con un array in jSON che contiene alcune informazioni sul prodotto.
     *
     * @static
     *
     * @param int | object   $id_product ID del prodotto o object del record
     * @param string         $id_variant ID della variante
     *
     * @retval string
     */
    public static function jSONEncodeWithProductID( $id_product, $id_variant = '' ) {
        if ( is_numeric( $id_product ) ) {
            $product = WPXSmartShopProduct::product( $id_product );
        } elseif ( is_object( $id_product ) ) {
            $product    = $id_product;
            $id_product = $product->ID;
        } else {
            return false;
        }
        $args = array(
            'product_title'        => $product->post_title,
            /* @todo Provare a commentare */
            'product_amount'       => WPXSmartShopProduct::price( $id_product, 1, $id_variant ),
            'qty'                  => 1,
            'link'                 => get_post_permalink( $id_product )
        );

        /**
         * Filtro sugli argomenti passati alla json_encode per serializzare i parametri javascript usati per
         * l'aggiunta al carrello
         *
         * @filters
         *
         * @param array  $args       Array con le informazioni sul nome prodotto, prezzo, qty, ...
         * @param object $product    Record del prodotto
         * @param string $id_variant ID della variante
         */
        $args = apply_filters( 'wpss_cart_json_encode', $args, $product, $id_variant );
        $json = base64_encode( json_encode( $args ) );
        return $json;
    }

}