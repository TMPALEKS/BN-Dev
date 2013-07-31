<?php
/**
 * @class              WPXSmartShopShortCode
 *
 * @description        Gestione degli shortcode WordPress
 *
 * @package            wpx SmartShop
 * @subpackage         core
 * @author             =undo= <g.fazioli@wpxtre.me>
 * @copyright          Copyright (c)2012 wpXtreme, Inc.
 * @created            14/12/11
 * @version            1.0
 *
 *
 * @addtogroup Filters Filtri
 *    Documentazione di tutti i filtri disponibili
 * @{
 * @defgroup shortcode_filters Nel file wpxss-shortcode.php
 * @ingroup Filters
 *    Filtri contenuti nel file wpxss-shortcode.php
 * @}
 *
 * @todo               Da rivedere alcuni shortcode
 *
 */

class WPXSmartShopShortCode {

    // -----------------------------------------------------------------------------------------------------------------
    // Static values
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Restituisce un array con tutti gli shortcode da registrare, L'array è un classico key => value dove la chiave
     * indica lo shortcode e il metodo, mentre il value (true|false) indica se lo shortcode dev'essere registrato.
     *
     * @static
     * @retval array
     *             Restituisce un array con tutti gli shortcode da registrare
     */
    public static function shortcodes() {
        $shortcodes = array(
            'wpss_product_title'              => true,
            'wpss_show_product'               => true,
            'wpss_checkout'                   => true,
            'wpss_store_product_type_toolbar' => true,
            'wpss_store_item_list'            => true,

            'wpss_payment'                    => true,
            'wpss_payment_results'            => true,
            'wpss_payment_error'              => true,

            'wpxss_showcase_gallery'          => true,
        );
        return $shortcodes;
    }

    // -----------------------------------------------------------------------------------------------------------------
    // WordPress integration
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Registra gli shortcode
     *
     * @static
     *
     */
    public static function registerShortcodes() {

        foreach ( self::shortcodes() as $shortcode => $to_register ) {
            if ( $to_register ) {
                add_shortcode( $shortcode, array( __CLASS__, $shortcode ) );
            }
        }

    }

    // -----------------------------------------------------------------------------------------------------------------
    // Shortcode
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Vetrina dello store
     *
     * @static
     * @deprecated
     *
     * @param string|array Parametri
     *
     * @retval string
     */
    public static function wpss_store_item_list( $attrs ) {

        /* Attributi standard per questo shortcode */
        $defaults = array(
            'numberproducts'             => 2,
            'product_order_by'           => 'post_date',
            'product_order'              => 'DESC',

            'product_type_order_by'      => 'name',
            'product_type_order'         => 'ASC',
            'exclude_product_type'       => array(),
            'hide_empty_product_type'    => false,
            'numberproduct_type'         => 'all',
            'search_product_type'        => '',
            'name_like_product_type'     => ''
        );

        /* Merge i default con quelli passati nello shortcode */
        $args = wp_parse_args( $attrs, $defaults );

        /* Costruisco gli attributi per i termini (tassionomia) */
        $product_type_args = array(
            'orderby'    => $args['product_type_order_by'],
            'order'      => $args['product_type_order'],
            'exclude'    => explode(',', $args['exclude_product_type']),
            'hide_empty' => $args['hide_empty_product_type'],
            'number'     => $args['numberproduct_type'],
            'search'     => $args['search_product_type'],
            'name__like' => $args['name_like_product_type']
        );

        ob_start();

        /* Tutti i tipi di prodotto in base ai filtri di sopra */
        $productTypes = WPSmartShopProductTypeTaxonomy::productTypes( $product_type_args );

        if ( !is_wp_error( $productTypes ) ) : ?>
        <ul class="wpss-store-product-items-list">
            <?php foreach ( $productTypes as $term ) : ?>
            <?php $term_link = get_term_link( $term ) ?>
            <li>
                <h2><a href="<?php echo $term_link ?>"><?php echo $term->name ?></a></h2>
                <?php

                $posts_args = array(
                    'taxonomy'     => kWPSmartShopProductTypeTaxonomyKey,
                    'term'         => $term->slug,
                    'post_type'    => WPXSMARTSHOP_PRODUCT_POST_KEY,
                    'numberposts'  => $args['numberproducts'],
                    'orderby'      => $args['product_order_by'],
                    'order'        => $args['product_order']
                );

                $products = get_posts( $posts_args );
                foreach ( $products as $product ) : ?>
                    <?php $product_link = get_permalink( $product->ID ) ?>
                    <?php $price = WPXSmartShopProduct::price( $product->ID ) ?>
                    <?php $json = base64_encode( json_encode( array(
                                                                   'productName' => $product->post_title,
                                                                   'price'       => $price,
                                                                   'qty'         => 1,
                                                                   'link'        => $product_link
                                                              ) ) ) ?>
                    <div class="wpss-store-product-item">
                        <div class="wpss-store-product-item-image">
                            <a href="<?php echo $product_link ?>">
                                <?php echo WPXSmartShopProduct::thumbnail( $product->ID, kWPSmartShopThumbnailSizeMediumKey ) ?>
                            </a>
                        </div>
                        <h3><a href="<?php echo $product_link ?>"><?php echo $product->post_title ?></a></h3>

                        <p class="wpss-store-product-item-price"><?php echo WPXSmartShopProduct::priceHTML( $product->ID ) ?></p>

                        <?php if(WPXSmartShopShoppingCart::canDisplayAddToCart()) : ?>
                        <input id="wpss-product-<?php echo $product->ID ?>"
                               product="<?php echo $json ?>"
                               type="button"
                               class="wpss-cart-add"
                               value="<?php _e( 'Add to cart', WPXSMARTSHOP_TEXTDOMAIN ) ?>"/>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
            </li>
            <?php endforeach; ?>
        </ul>

        <?php else : ?>
        <?php return $productTypes->get_error_message() ?>
        <?php endif;
        $result = ob_get_contents();
        ob_end_clean();
        return $result;
    }

    /**
     * Elenca tutti i tipi prodotto, inserendo il link per la visualizzazione completa della "categoria"
     *
     * @static
     * @deprecated
     *
     * @retval string
     */
    public static function wpss_store_product_type_toolbar() {
        ob_start();
        $productTypes = WPSmartShopProductTypeTaxonomy::productTypes();
        if ( !is_wp_error( $productTypes ) ) : ?>
        <ul class="wpss-store-product-type-toolbar">
            <?php foreach ( $productTypes as $term ) : ?>
            <li>
                <a href="<?php echo get_term_link( $term ) ?>"><?php echo $term->name ?></a>
            </li>
            <?php endforeach; ?>
        </ul>
        <?php else : ?>
        <?php return $productTypes->get_error_message() ?>
        <?php endif;
        $result = ob_get_contents();
        ob_end_clean();
        return $result;
    }

    /**
     * Shortcode di Commodity. Restituisce il titolo di un prodotto
     *
     * @static
     * @deprecated
     * @filter     wpss_product_title
     *
     * @param string|array     $attrs Array key=>valore con key = id, sluge, title o sku
     *
     * @code
     * [wpss_product_title id="1"]
     * [wpss_product_title slug="maglietta"]
     * [wpss_product_title title="La Maglietta"]
     * [wpss_product_title sku="45-KML-7272"]
     * @endcode
     *
     * @retval string
     *             Titolo del prodotto
     */
    public static function wpss_product_title( $attrs ) {
        $post = WPXSmartShopProduct::product( $attrs );
        if ( $post ) {
            /**
             * @defgroup wpss_product_title wpss_product_title
             * @{
             *
             * @ingroup shortcode_filters
             *   Called from shortcode wpss_product_title
             *
             * @param string $title Product title name
             *
             * @retval string Product title name
             *
             * @}
             */
            return apply_filters( 'wpss_product_title', $post->post_title );
        }
        return null;
    }

    /**
     * Costruisce la mini scheda di un prodotto:
     *
     * @code
     * +--------------------------------+
     * | +-----------+                  |
     * | | thumbanil | Title - abstract |
     * | +-----------+                  |
     * |               price (add cart) |
     * +--------------------------------+
     * @endcode
     *
     * @todo       Questo shortcode dovrà essere esteso con una serie di opzioni soprattutto di visualizzazione. Man mano che servono le inseriremo qui
     * @todo       Da rinominare in "wpss_product_sheet"
     *
     * @deprecated
     *
     * @code
     * [wpss_show_product id="1"]
     * [wpss_show_product slug="maglietta"]
     * [wpss_show_product title="La Maglietta"]
     * [wpss_show_product sku="45-KML-7272"]
     * @endcode
     *
     * @param string|array     $attrs
     *
     * @retval string
     *             HTML da visualizzare
     */
    public static function wpss_show_product( $attrs ) {
        ob_start();

        $default = array(
            'thumbnail' => true,
            'excerpt'   => true,
            'addToCart' => true,
            'currency'  => WPXSmartShopCurrency::currencySymbol(),
            'permalink' => true,
            'discount'  => true
        );

        $attrs = array_merge( $default, $attrs );

        $product = WPXSmartShopProduct::product( $attrs );
        if ( $product ) {

            /* WPML integration: il titolo sarà (se fatto) localizzato */
            $productName = $product->post_title;

            /* Thumbnail */
            $thumbnail = '';
            if ( $attrs['thumbnail'] ) {
                $thumbnail = WPXSmartShopProduct::thumbnail( $product->ID );
            }

            /* Excerpt - product description */
            $excerpt = '';
            if ( wpdk_is_bool( $attrs['excerpt'] ) ) {
                if ( !empty( $product->post_excerpt ) ) {
                    $excerpt = ' - ' . apply_filters( 'get_the_excerpt', $product->post_excerpt );
                } else {
                    $excerpt = ' - ' . $product->post_content;
                }
            }

            /* Permalink - scheda prodotto */
            $permalink = '';
            if ( $attrs['permalink'] ) {
                $permalink = get_post_permalink( $product->ID );
            }

            /* Questi sono i dati che saranno passati via javascript per l'inserimento in sessione php e quindi nel
            carrello
            $json = WPXSmartShopShoppingCart::jSONEncodeWithProductID( $product->ID ); */
            ?>

        <div class="wpssProductSheet">
            <p class="content">
                <?php echo $thumbnail ?>
                <?php echo (
                $permalink == '' ) ? $productName : sprintf( '<a href="%s">%s</a>', $permalink, $productName ) ?>
                <span class="excerpt"><?php echo $excerpt ?></span>
            </p>

            <p class="price">
                <?php echo WPXSmartShopProduct::priceHTML( $product->ID ) ?>
                <?php if ( $attrs['addToCart'] ) : ?>
                <?php if ( WPXSmartShopShoppingCart::canDisplayAddToCart() ) : ?>
                    <?php echo WPXSmartShopShoppingCart::buttonAddShoppingCart( $product->ID ) ?>
                    <?php else : ?>
                    <?php echo WPXSmartShopShoppingCart::messageYouHaveToLogin() ?>
                    <?php endif; ?>
                <?php endif; ?>
            </p>

        </div>
        <?php
            $content = ob_get_contents();
            ob_end_clean();

            $result = apply_filters( 'wpss_show_product', $content, $product );

            return $result;
        }
        return null;
    }


    // -----------------------------------------------------------------------------------------------------------------
    // Checkout and Summary Order
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Presenta la pagina con il summary order, cioè le informazioni sull'ordine, con la lista prodotti (modificabile)
     * e il totale da pagare comprensivo di tutto: tasse, spedizione, sconti, etc...
     *
     * @static
     *
     * @retval string
     *             HTML con il Summary Order
     */
    public static function wpss_checkout() {

        ob_start();

        $result = WPXSmartShopSummaryOrder::display();

        $content = ob_get_contents();
        ob_end_clean();

        if ( is_wp_error( $result ) ) {
            $content = $result->get_error_message();
        }

        return $content;
    }


    // -----------------------------------------------------------------------------------------------------------------
    // Payment Gateway
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Questo shortcode viene usato nella pagina dedicata al gateway di pagamento. Normalmente si viene rediretti ad
     * una pagina template che contiene questo shortcode.
     * Questo shortcode risiede su una pagina che viene chiamata due volte: la prima volta ci si arriva dal summary
     * order. La seconda volta ci si arriva dalla banca.
     *
     * @static
     *
     * @retval string
     *             Un messaggio
     */
    public static function wpss_payment() {

        $result = WPSmartShopPaymentGateway::payment();

        WPDKWatchDog::watchDog( __CLASS__ );

        if ( WPDKWatchDog::isStatus( $result ) ) {
            $status = WPDKWatchDog::getStatusCode( $result );
            WPDKWatchDog::watchDog( __CLASS__, 'STATUS ' . $status );
            if ( $status == 'redirect_to_secure_bank' ) {
                return $result->get_error_data( 'message' );
            } elseif ( $status == 'transaction_zero' || $status = 'payment_with_cash' ) {
                $receipt_slug      = WPXSmartShop::settings()->receipt_permalink();
                $receipt_permalink = wpdk_permalink_page_with_slug( $receipt_slug, kWPSmartShopStorePagePostTypeKey );
                printf( '<meta http-equiv="refresh" content="0;URL=%s">', $receipt_permalink );
            }
        } else {
            WPDKWatchDog::watchDog( __CLASS__, 'ERRORE FATALE' );
            return WPDKWatchDog::displayWPError( $result, false );
        }
    }

    /**
     * Usato nella pagina con risultato positivo, anche se viene comunque effettuato un ulteriore controllo.
     *
     * @static
     * @retval string|void
     */
    public static function wpss_payment_results() {

        $result = WPSmartShopPaymentGateway::payment_results();

        if ( WPDKWatchDog::isError( $result ) ) {
            self::wpss_payment_error();
            //return WPDKWatchDog::displayWPError( $result, false );
        } elseif ( WPDKWatchDog::isStatus( $result ) ) {
            $status = WPDKWatchDog::getStatusCode( $result );
            if ( $status == 'payment_result_thank_for_purchase' ) {
                $data     = $result->get_error_data();
                $track_id = $data['trackID'];
                $order    = WPXSmartShopOrders::order( $track_id );
                echo WPXSmartShopInvoice::invoice( $order );

                /* @todo Prova */
                $language_code = ( ICL_LANGUAGE_CODE == 'it' ) ? '' : trailingslashit( ICL_LANGUAGE_CODE );
                $print_url     = sprintf( '%s%s', $language_code, __( 'print', 'bnm' ) );
                printf( '<p class="aligncenter"><a class="button blue" target="blank" href="%s%s/?invoice&id_order=%s">%s</a></p>', trailingslashit( get_bloginfo('url') ), $print_url, $order->id, __('Print', WPXSMARTSHOP_TEXTDOMAIN ) );

                /* @todo Action */
                /* Non usato */
                do_action( 'wpss_payment_result_invoice', $order );

            }
        }
    }

    /**
     * Usato nella pagina con risultato negativo
     *
     * @static
     * @retval string|void
     */
    public static function wpss_payment_error() {

        /* @todo Per adesso alcuni gateway usano questo come cancel url, quindi rediriggo alla cassa */
       /* $checkoout_slug     = WPXSmartShop::settings()->checkout_permalink();
        $checkout_permalink = wpdk_permalink_page_with_slug( $checkoout_slug, kWPSmartShopStorePagePostTypeKey );
        wp_redirect( $checkout_permalink );
        die();*/

        $error = WPSmartShopPaymentGateway::payment_error();
        wp_redirect( "http://www.bluenotemilano.com/negozio/errore", 301 );
        die();

       // return WPDKWatchDog::displayWPError( $error, false );
    }


    // -----------------------------------------------------------------------------------------------------------------
    // Showcase
    // -----------------------------------------------------------------------------------------------------------------

    public static function wpxss_showcase_gallery() {
        $args = array(
            'post_parent'    => get_the_ID(),
            'post_type'      => 'attachment',
            'post_mime_type' => 'image'
        );

        $images = get_children( $args );

        $items = '';
        foreach ( $images as $image ) {
            $items .= sprintf('<div style="background-image:url(%s)" class="wpxss-showcase-gallery-item"></div>', $image->guid );
        }
        $html = <<< HTML
<div class="wpxss-showcase-gallery">
    {$items}
</div>
HTML;
        return $html;

    }


}