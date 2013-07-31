<?php
/**
 * @class              WPXSmartShopSession
 * @description        Gestisce i dati volatili nella sessione
 *
 * @package            wpx SmartShop
 * @subpackage         core
 * @author             =undo= <g.fazioli@wpxtre.me>
 * @copyright          Copyright (c) 2012 wpXtreme, Inc.
 * @date               15/02/12
 * @version            1.0.0
 *
 */

/**
 * @addtogroup Filters Filtri
 *    Documentazione di tutti i filtri disponibili
 * @{
 * @defgroup session_filters Nel file wpxss-session.php
 * @ingroup Filters
 *    Filtri contenuti nel file wpxss-session.php
 * @}
 */

class WPXSmartShopSession {

    // -----------------------------------------------------------------------------------------------------------------
    // Core
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Restituisce l'intera sessione di Smart Shop 'as it is'.
     *
     * @todo               Eventualmente codificarla in base64 oltre che serializzarla
     *
     * @static
     *
     * @param null $session
     *
     * @retval mixed
     */
    public static function session( $session = null ) {
        if ( is_null( $session ) ) {
            if ( !isset( $_SESSION[WPXSMARTSHOP_SESSION_ID] ) ) {
                self::init();
            }
            return unserialize( $_SESSION[WPXSMARTSHOP_SESSION_ID] );
        } else {
            $_SESSION[WPXSMARTSHOP_SESSION_ID] = serialize( $session );
        }
        return true;
    }

    /**
     * Inizializza la sessione di Smart Shop
     *
     * @static
     *
     */
    public static function init() {
        $_SESSION[WPXSMARTSHOP_SESSION_ID] = serialize( array() );
    }

    // -----------------------------------------------------------------------------------------------------------------
    // Products Helper
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Restituisce o imposta l'array dei prodotti inseriti nella sessione usata dal carrello e dal summary order
     *
     * @static
     *
     * @param array $products Elenco prodotti come array o null per restituire l'elenco
     *
     * @retval array
     */
    public static function products( $products = null ) {
        $session = self::session();
        if ( is_null( $products ) ) {
            $products = array();
            if ( isset( $session['products'] ) ) {
                $products = $session['products'];
            }
            return $products;
        } else {
            $session['products'] = $products;
            self::session( $session );
        }
        return true;
    }

    /**
     * Restituisce o aggiorna un determinato prodotto dal carrello
     *
     * @static
     *
     * @param string $id_product_key ID del prodotto in jSON codificato base64
     * @param array  $product        Prodotto da impostare o null per leggerlo
     *
     * @retval bool|array Prodotto o false se non trovato
     */
    public static function product( $id_product_key, $product = null ) {
        $session = self::session();
        if ( is_null( $product ) ) {
            if ( isset( $session['products'] ) && isset( $session['products'][$id_product_key] ) ) {
                return $session['products'][$id_product_key];
            }
            return false;
        } else {
            $session['products'][$id_product_key] = $product;
            self::session( $session );
        }
        return true;
    }

    /**
     * Conta un prodotto nel carello senza considerare le varianti
     *
     * @static
     *
     * @param int $id_product ID del prodotto
     *
     * @retval int Numero di prodotti nel carrello con un determinato ID
     */
    public static function countProductWithID( $id_product ) {
        $result   = 0;
        $products = self::products();
        foreach ( $products as $product ) {
            if ( $product['id_product'] == $id_product ) {
                $result += intval( $product['qty'] );
            }
        }
        return $result;
    }

    /**
     * Conta un prodotto nel carello senza considerare le varianti
     *
     * @static
     *
     * @param string $id_product_key ID del prodotto in jSON codificato base64
     *
     * @retval int
     * Numero di prodotti nel carrello con un determinato ID in jSON codificato base64
     */
    public static function countProductWithIDProductKey( $id_product_key ) {
        $result   = 0;
        $products = self::products();
        if ( isset( $products[$id_product_key] ) ) {
            $result = $products[$id_product_key]['qty'];
        }
        return $result;
    }

    /**
     * Aggiunge un prodotto alla sessione
     *
     * @static
     *
     * @param array $product Array che descrive il prodotto da aggiungere. Questo può essere di due tipi: o quello
     *                       completo proveniente dalla select sulla tabella stats o uno simile, ma con meno campi,
     *                       costruito a parte ma strutturato in modo che le chiavi obbligatorie ci siamo.
     *
     * @retval bool|WP_Error
     *                     Restituisce false se non è stato possibile aggiungere un item per qualche errore, altrimenti
     *                     restituisce un oggetto WP_Error con note aggiuntive
     *
     */
    public static function addProduct( $product ) {
        $variant     = WPXSmartShopStats::variant( $product );
        $product_key = self::encodeProductKey( $product['id_product'], $variant );

        $products = self::products();
        if ( isset( $products[$product_key] ) ) {
            $qty = absint( $products[$product_key]['qty'] ) + 1;
            return WPXSmartShopShoppingCart::updateProductQuantity( $product_key, $qty );
        } else {
            $products[$product_key] = $product;
            self::products( $products );
        }
        return false;
    }

    public static function productCustomDiscount( $id_product_key, $id_custom_discount = null ) {
        $products = self::products();
        if ( !is_null( $id_custom_discount ) ) {
            if ( isset( $products[$id_product_key] ) ) {
                $products[$id_product_key]['id_custom_discount'] = $id_custom_discount;
                self::products( $products );
            }
        } else {
            if ( isset( $products[$id_product_key]['id_custom_discount'] ) ) {
                return $products[$id_product_key]['id_custom_discount'];
            }
        }
        return false;
    }

    /**
     * Elimina tutti i prodotti dal carrello
     *
     * @static
     *
     */
    public static function emptyProducts() {
        self::products( array() );
    }

    /**
     * Restituisce una chiave 'unica' per memorizzare un prodotto insieme alla sua eventuale variante
     *
     * @static
     *
     * @param int    $id_product ID del prodotto
     * @param array  $variant    Elenco del parametro/valore con chiave uguale all'id_variant
     *
     * @retval string Chiave in formato jSON e codificata in base64
     * @sa self::decodeProductKey()
     */
    public static function encodeProductKey( $id_product, $variant = array() ) {
        $result = array(
            'id_product' => $id_product,
            'variant'    => $variant
        );

        $result = base64_encode( json_encode( $result ) );
        return $result;
    }

    /**
     * Decodifica una chiave prodotto restituiendo un array key/value.
     *
     * @static
     *
     * @param string $key Chiave jSON codificata in base64
     *
     * @retval array Array key pair con l'id prodotto e l'eventuali informazioni sulla variante
     * @see self::encodeProductKey()
     */
    public static function decodeProductKey( $key ) {
        return json_decode( base64_decode( $key ), true );
    }


    // -----------------------------------------------------------------------------------------------------------------
    // Order Helper
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Ricostruisce una sessione partendo dalle informazioni presenti nel database. In base all'id dell'utente,
     * vengono ricercati eventuali ordini in stato pending. Da questi (in teroria ce ne dovrebbe essere solo uno)
     * viene ricostruita la sessione volatile.
     *
     * @static
     *
     * @param int $id_user ID utente
     *
     * @retval array|WP_Error Restituisce la sessione ricostruita o un oggetto WP_Error in caso di errore
     */
    public static function sessionFromPendingOrderWithUser( $id_user ) {

        /* Inizializzo una sessione vuota */
        self::init();

        if ( WPXSmartShopOrders::orderExistsWithUserAndStatus( $id_user ) ) {
            $orders = WPXSmartShopOrders::ordersWithUserAndStatus( $id_user, WPXSMARTSHOP_ORDER_STATUS_PENDING, ARRAY_A );

            if ( count( $orders ) > 1 ) {
                $message = __( 'Too many orders in %s status for user with id %s', WPXSMARTSHOP_TEXTDOMAIN );
                $message = sprintf( $message, WPXSMARTSHOP_ORDER_STATUS_PENDING, $id_user );
                $error   = new WP_Error( 'wpss_warning-too_many_order_in_pending_for_current_user', $message );
                return $error;
            }

            /* Un solo ordine */
            $order = $orders[0];
            self::order( $order );

            /* Recupero prodotti */
            $products = WPXSmartShopStats::productsWithOrderID( $order['id'] );

            foreach ( $products as $product ) {
                self::addProduct( $product );
            }
        }

        /* Elenco di id_prodotto e relativa count nella storia utente */
        $products_count = WPXSmartShopStats::countsProductsWithUserID( $id_user );
        if ( !empty( $products_count ) ) {
            $session                   = self::session();
            $session['products_count'] = $products_count;
            self::session( $session );
        }

        return self::session();
    }

    /* @todo Incapsulo l'accesso al conteggio dei prodotti storico */
    public static function arrayOrderedProducts() {
        $session = self::session();
        if ( !isset( $session['products_count'] ) ) {
            $id_user        = get_current_user_id();
            $products_count = WPXSmartShopStats::countsProductsWithUserID( $id_user );
            if ( !empty( $products_count ) ) {
                $session['products_count'] = $products_count;
                self::session( $session );
            } else {
                return false;
            }
        }
        return $session['products_count'];
    }

    /// Return product count by id
    public static function countOrderedProduct( $id_product ) {
        $result = 0;
        $count  = self::arrayOrderedProducts();
        if ( $count && isset( $count[$id_product] ) ) {
            return $count[$id_product];
        }
        return $result;
    }

    /**
     * @todo Crea un array con tutti gli ordini
     *
     * @param $id_user
     */
    private function historyOrders( $id_user ) {

    }

    // -----------------------------------------------------------------------------------------------------------------
    // Summary Order Products Coupon
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Associa uno o più coupon ai prodotti presenti nel carrello
     *
     * @static
     *
     * @param string $id_product_key ID del prodotto in jSON codificato base64
     * @param string $coupon_code    Serial number del coupon (uniqcode)
     *
     * @retval bool|WP_Error Ritorna true se il coupon è stato cancellato e un oggetto WP_Error negli altri casi,
     *             anche se andato a buon fine (controllare il codice di errore)
     */
    public static function updateProductCoupons( $id_product_key, $coupon_code ) {

        /* Chi sono? */
        $id_user = get_current_user_id();

        /* Sanitizzo il codice coupon */
        $coupon_code = trim( esc_attr( $coupon_code ) );

        if ( empty( $coupon_code ) ) {
            self::deleteProductCoupons( $id_product_key );
            /* @todo Non è il caso di ritornare sempre un WP_Error? */
            return true;
        }

        $product    = self::product( $id_product_key );
        $id_product = $product['id_product'];

        /* I Coupon applicabili ad un prodotto, per differenziarli da quelli ordine, devono avere sempre o il campo
        id_product o id_product_type impostati, non possono essere generici altrimenti sono - appunto - di tipo ordine.
        Inoltre dall'id prodotto risalgo alla sua tipologia.
        */

        $coupons = WPXSmartShopCoupons::couponsWithUniqCodeApplicableForProductID( $coupon_code, $id_product );

        if ( !$coupons ) {
            $message = __( 'Your coupon code is incorrect or not valid for this product. Please try again!', WPXSMARTSHOP_TEXTDOMAIN );
            $error   = new WP_Error( 'wpss_error-coupon_does_not_exists', $message );
            return $error;
        }

        /* Se ci sono Coupons disponibili, carico la lista transitoria per vedere se non sono già utilizzati da me o
        da qualcun'altro
        */

        $transient_coupons = WPXSmartShopTransient::coupons();
        $available_coupons = array();
        $qty               = $product['qty'];
        $count             = 0;
        foreach ( $coupons as $coupon ) {
            /* Se questo coupon non è nella lista dei transitori lo posso usare */
            if ( !isset( $transient_coupons[$coupon->id] ) ) {
                /* Controllo che il coupon non abbiamo un id_owner impostato, nel qual cosa l'id utente connesso deve
                corrispondere a questo, altrimenti il coupon non è utilizzabile
                */
                if( !empty( $coupon->id_owner ) && $coupon->id_owner > 0 ) {
                    if( $id_user != $coupon->id_owner ) {
                        /* Coupon non utilizzabile dall'utente attuale */

                        /**
                         * @defgroup wpxss_coupon_user_owner_different wpxss_coupon_user_owner_different
                         * @{
                         *
                         * @ingroup session_filters
                         *   Called when a coupon is apply on current user different to owner user
                         *
                         * @param bool $true
                         *  Qui arriva sempre true nel coso l'utente owner è diverso dall'utente loggato
                         *
                         * @retval bool
                         *  Restituire false per far in modo che l'utente loggato possa avquistare anche un coupon non suo
                         *
                         * @}
                         */

                        $result = apply_filters( 'wpxss_coupon_user_owner_different', true );
                        if ( $result ) {
                            $message = __( 'This coupon code is not available for you. Please try with different coupon code.', WPXSMARTSHOP_TEXTDOMAIN );
                            $error   = new WP_Error( 'wpss_error-coupon_wrong_id_owner', $message );
                            return $error;
                        }
                    }
                }
                $transient_coupons[$coupon->id] = $coupon->uniqcode;
                $available_coupons[$coupon->id] = $coupon->limit_product_qty;
                $count++;
                if( $count == $qty) {
                    break;
                }
            }
        }

        /* Se ne sto prenotando almeno uno aggiorno la lista transitoria */
        if ( !empty( $available_coupons ) ) {
            WPXSmartShopTransient::coupons( $transient_coupons );
        } else {
            /* Coupon non disponibili o perchè li sta usando qualcun'altro o perché è già stato usato da me */
            $message = __( 'The Coupon code is busy by you and/or someone else . Please try again later', WPXSMARTSHOP_TEXTDOMAIN );
            $error   = new WP_Error( 'wpss_error-coupon_busy', $message );
            return $error;
        }

        /* Determino quanti coupon posso applicare a questa serie di prodotti */

        $product['ids_coupon'] = array();
        $count                 = WPXSmartShopStats::countsCouponsWithUserIDAndUniqcode( $id_user, $coupon_code, $id_product );
        foreach ( $available_coupons as $id_coupon => $coupon_limit_product_qty ) {
            if ( !empty( $coupon_limit_product_qty ) && $count >= $coupon_limit_product_qty ) {
                break;
            }
            $product['coupon_uniqcode'] = $coupon_code;
            $product['ids_coupon'][]    = $id_coupon;
            $count++;
        }

        self::product( $id_product_key, $product );

        $qty                    = $product['qty'];
        $available_count_coupon = count( $product['ids_coupon'] );

        if ( $available_count_coupon < $qty ) {
            $message = __( 'On %s purchased products, coupon successfully used for the %s units to which you are entitled', WPXSMARTSHOP_TEXTDOMAIN );
            $message = sprintf( $message, $qty, $available_count_coupon );
            $error   = new WP_Error( 'wpss_status-coupon_apply_succefully_with_warning', $message );
        } else {
            $message = __( 'Coupon successfully used for the units to which you are entitled.', WPXSMARTSHOP_TEXTDOMAIN );
            $error   = new WP_Error( 'wpss_status-coupon_apply_succefully', $message );
        }
        return $error;
    }


    /**
     * Elimina tutti i coupon applicati ad un prodotto.
     * Aggiorna anche la lista generale dei coupon usati in sessione.
     *
     * @static
     *
     * @param string $id_product_key ID del prodotto in jSON codificato base64
     */
    public static function deleteProductCoupons( $id_product_key ) {
        $product = self::product( $id_product_key );
        unset( $product['coupon_uniqcode'] );

        /* Aggiorno lista generale usata per controllo duplicazione uso */
        $transient_coupons = WPXSmartShopTransient::coupons();
        if ( !empty( $transient_coupons ) ) {
            foreach ( $product['ids_coupon'] as $id ) {
                unset( $transient_coupons[$id] );
            }
            WPXSmartShopTransient::coupons( $transient_coupons );
        }
        unset( $product['ids_coupon'] );
        self::product( $id_product_key, $product );
    }

    /**
     * Applica lo sconto coupon ad un prezzo (base) di prodotto
     *
     * @static
     *
     * @param float  $price    Prezzo singolo di un prodotto
     * @param array  $product  Prodotto
     *
     * @retval float
     */
    public static function applyProductCoupon( $price, $product ) {
        $result = $price;
        if ( !empty( $product['coupon_uniqcode'] ) ) {
            $coupons = WPXSmartShopCoupons::couponsWithUniqCodeApplicableForProductID( $product['coupon_uniqcode'], $product['id_product'] );
            if ( !is_null( $coupons ) ) {
                $result = WPXSmartShopCoupons::applyCouponValue( $coupons[0]->value, $price );
            }
        }
        return $result;
    }

    // -----------------------------------------------------------------------------------------------------------------
    // Order
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Restituisce o imposta l'ordine su cui si sta operando e a cui stanno facendo riferimento l'elenco dei prodotti
     *
     * @static
     *
     * @param array $order Oggetto ordine come da record del database
     *
     * @retval object Oggetto ordine
     */
    public static function order( $order = null ) {
        $session = self::session();
        if ( is_null( $order ) ) {
            if ( !isset( $session['order'] ) ) {
                $session['order'] = array();
                self::session( $session );
            }
            return $session['order'];
        } else {
            $session['order'] = $order;
            self::session( $session );
        }
        return $order;
    }

    /**
     * Restituisce l'id dell'ordine
     *
     * @static
     * @helper
     *
     * @retval bool|int ID dell'ordine o false se non presente
     */
    public static function orderID() {
        $order = self::order();
        if ( isset( $order['id'] ) ) {
            return $order['id'];
        } else {
            return false;
        }
    }

    /**
     * Get/set il TrackID di un ordine
     *
     * @static
     * @helper
     *
     * @param string $value Track ID
     *
     * @retval bool|string Se $value = null rirona il track id corrente o false se non impostato
     */
    public static function orderTrackID( $value = null ) {
        $order = self::order();
        if ( is_null( $value ) ) {
            if ( isset( $order['track_id'] ) ) {
                return $order['track_id'];
            } else {
                return false;
            }
        } else {
            $order['track_id'] = $value;
            self::order( $order );
        }
        return true;
    }

    /**
     * Get/set il Gateway di un ordine
     *
     * @static
     *
     * @param string $value Payment Gateway
     *
     * @retval bool|string Se $value = null rirona il Gateway id corrente o false se non impostato
     */
    public static function orderPaymentGateway( $value = null ) {
        $order = self::order();
        if ( is_null( $value ) ) {
            if ( isset( $order['payment_gateway'] ) ) {
                return $order['payment_gateway'];
            } else {
                return false;
            }
        } else {
            $order['payment_gateway'] = $value;
            self::order( $order );
        }
        return true;
    }

    /// Get/Set order shipping country
    public static function orderShippingCountry( $value = null ) {
        $order = self::order();
        if ( is_null( $value ) ) {
            if ( isset( $order['shipping_country'] ) ) {
                return $order['shipping_country'];
            } else {
                return false;
            }
        } else {
            $order['shipping_country'] = $value;
            self::order( $order );
        }
        return true;
    }

    /// Get/Set order shipping carrier
    public static function orderShippingCarrier( $value = null ) {
        $order = self::order();
        if ( is_null( $value ) ) {
            if ( isset( $order['id_carrier'] ) ) {
                return $order['id_carrier'];
            } else {
                return false;
            }
        } else {
            $order['id_carrier'] = $value;
            self::order( $order );
        }
        return true;
    }

    /// Get/Set order shipping
    public static function orderShipping( $value = null ) {
        $order = self::order();
        if ( is_null( $value ) ) {
            if ( isset( $order['shipping'] ) ) {
                return $order['shipping'];
            } else {
                return false;
            }
        } else {
            $order['shipping'] = $value;
            self::order( $order );
        }
        return true;
    }

    /**
     * Get/set il Tipo di pagamento (Bank, Cash, ...) di un ordine
     *
     * @static
     *
     * @param string $value Payment Gateway
     *
     * @retval bool|string Se $value = null rirona il Gateway id corrente o false se non impostato
     */
    public static function orderPaymentType( $value = null ) {
        $order = self::order();
        if ( is_null( $value ) ) {
            if ( isset( $order['payment_type'] ) ) {
                return $order['payment_type'];
            } else {
                return false;
            }
        } else {
            $order['payment_type'] = $value;
            self::order( $order );
        }
        return true;
    }

    /**
     * Elimina il coupon dell'ordine
     *
     * @static
     *
     */
    public static function deleteOrderCoupon() {
        $order            = self::order();
        $order['id_coupon'] = '';
        self::order( $order );
    }

    /**
     * Legge o imposta il coupon ordine
     *
     * @static
     *
     * @param object $coupon Oggetto coupon o null per leggerlo
     *
     * @retval array Key pair con codice univo e ID del coupon ordine
     */
    public static function orderCoupon( $coupon = null ) {
        $order = self::order();
        if ( is_null( $coupon ) ) {
            if ( isset( $order['coupon_uniqcode'] ) ) {
                return array( $order['coupon_uniqcode'] => $order['id_coupon'] );
            } else {
                return array();
            }
        } else {
            $order['coupon_uniqcode'] = $coupon->uniqcode;
            $order['id_coupon']       = $coupon->id;
            self::order( $order );
        }
        return true;
    }

    /**
     * Restituisce l'id del coupon ordine se presente
     *
     * @static
     * @retval int ID del coupon, false se il coupon ordine non esiste
     */
    public static function orderCouponID() {
        $order = self::order();
        if ( isset( $order['coupon_uniqcode'] ) ) {
            return absint( $order['id_coupon'] );
        }
        return false;
    }

    /**
     * Get/set l'ammontare di un ordine
     *
     * @static
     *
     * @param float $value Totale ordine
     *
     * @retval float|bool Valore totale dell'ordine o false se non trovato
     */
    public static function orderAmount( $value = null ) {
        $order = self::order();
        if ( is_null( $value ) ) {
            if ( !empty( $order['total'] ) ) {
                return floatval( $order['total'] );
            } else {
                return false;
            }
        } else {
            $order['total'] = floatval( $value );
            self::order( $order );
        }
        return true;
    }

    /**
     * Restituisce lo uniqcode del coupon ordine se presente
     *
     * @static
     * @retval string Serial number (uniq code) del coupon ordine, false se non esiste
     */
    public static function orderCouponUniqCode() {
        $order = self::order();
        if ( isset( $order['coupon_uniqcode'] ) ) {
            return $order['coupon_uniqcode'];
        }
        return false;
    }


    /**
     * Aggiorno un coupon di tipo ordine
     *
     * @static
     *
     * @param string $coupon_code Serial number (uniqcode) del coupon ordine
     *
     * @retval bool|WP_Error Ritorna true se il coupon è stato cancellato e un oggetto WP_Error negli altri casi,
     *             anche se andato a buon fine (controllare il codice di errore)
     */
    public static function updateOrderCoupon( $coupon_code ) {

        $coupon_code = trim( esc_attr( $coupon_code ) );

        if ( empty( $coupon_code ) ) {
            self::deleteOrderCoupon();
            return true;
        }

        /* Se l'ordine ha già dei coupon associati a dei prodotti, bosogna cercare per coupon ordini cumulativi */
        if ( self::hasOrderProductsCoupon() ) {
            $coupons = WPXSmartShopCoupons::couponsWithUniqCodeApplicableForOrder( $coupon_code, true );
            if ( $coupons ) {
                $first_available = $coupons[0];
                self::orderCoupon( $first_available );
                $message = __( 'Your coupon discount apply successfully!', WPXSMARTSHOP_TEXTDOMAIN );
                $status  = new WP_Error( 'wpss_status-order_coupon_apply_successfully', $message );
                return $status;
            } else {
                $message = __( 'No cumulative coupon found!', WPXSMARTSHOP_TEXTDOMAIN );
                $warning = new WP_Error( 'wpss_warning-no_cumulative_order_coupon_found', $message );
                return $warning;
            }
        }

        /* Non ci sono prodotti con coupon, cerco ordini cumulativi e non */
        $coupons = WPXSmartShopCoupons::couponsWithUniqCodeApplicableForOrder( $coupon_code );
        if ( $coupons ) {
            $first_available = $coupons[0];
            self::orderCoupon( $first_available );
            $message = __( 'Your coupon discount apply successfully!', WPXSMARTSHOP_TEXTDOMAIN );
            $status  = new WP_Error( 'wpss_status-order_coupon_apply_successfully', $message );
            return $status;
        } else {
            $message = __( 'No order coupon available at this moment!', WPXSMARTSHOP_TEXTDOMAIN );
            $warning = new WP_Error( 'wpss_warning-no_order_available', $message );
            return $warning;
        }
    }

    // -----------------------------------------------------------------------------------------------------------------
    // Product variants
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Conta un determinato tipo di variante all'interno della sessione similmente a self::hasProductsVariant()
     *
     * @static
     *
     * @param string       $id_variant ID Della variante
     * @param string       $field      ID field della variante: model, color, ....
     * @param mixed|array  $value      Valore o serie di valori della variante
     *
     * @retval int
     * Restituisce il numero delle varianti trovati o zero se nessuna trovata
     */
    public static function countProductsVariants( $id_variant, $field = null, $value = null ) {
        $result   = 0;
        $products = self::products();
        if ( !empty( $products ) ) {
            foreach ( $products as $product ) {
                if ( !empty( $product['id_variant'] ) ) {

                    if ( !is_null( $value ) && !is_null( $field ) && isset( $product[$field] ) ) {
                        if ( is_array( $value ) ) {
                            $bool_value = in_array( $product[$field], $value );
                        } else {
                            $bool_value = ( $product[$field] == $value );
                        }
                        if ( $product['id_variant'] == $id_variant && $bool_value ) {
                            $result += $product['qty'];
                        }
                    } elseif ( !is_null( $field ) ) {
                        if ( $product['id_variant'] == $id_variant && !empty( $product[$field] ) ) {
                            $result += $product['qty'];
                        }
                    } elseif ( $product['id_variant'] == $id_variant ) {
                        $result += $product['qty'];
                    }
                }
            }
        }
        return $result;
    }

    /**
     * Conta un determinato tipo di variante per un determinato tipo di prodotto, all'interno della sessione similmente
     * a self::hasProductsVariant() e self::countProductsVariants()
     *
     * @static
     *
     * @param int          $id_product ID del prodotto
     * @param string       $id_variant ID Della variante
     * @param string       $field      ID field della variante: model, color, ....
     * @param mixed|array  $value      Valore o serie di valori della variante
     *
     * @retval int
     * Restituisce il numero delle varianti trovati o zero se nessuna trovata
     */
    public static function countProductVariants( $id_product, $id_variant, $field = null, $value = null ) {
        $result   = 0;
        $products = self::products();

        if ( !empty( $products ) ) {
            foreach ( $products as $product ) {
                if ( $product['id_product'] == $id_product && !empty( $product['id_variant'] ) ) {

                    if ( !is_null( $value ) && !is_null( $field ) && isset( $product[$field] ) ) {
                        if ( is_array( $value ) ) {
                            $bool_value = in_array( $product[$field], $value );
                        } else {
                            $bool_value = ( $product[$field] == $value );
                        }
                        if ( $product['id_variant'] == $id_variant && $bool_value ) {
                            $result += $product['qty'];
                        }
                    } elseif ( !is_null( $field ) ) {
                        if ( $product['id_variant'] == $id_variant && !empty( $product[$field] ) ) {
                            $result += $product['qty'];
                        }
                    } elseif ( $product['id_variant'] == $id_variant ) {
                        $result += $product['qty'];
                    }
                    break; // find it, then exit
                }
            }
        }
        return $result;
    }

    // -----------------------------------------------------------------------------------------------------------------
    // has/is zone
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Restituisce true se almeno ad un prodotto è stato associato un coupon. Questo metodo di controllo serve per
     * determinare se è possibile o meno utilizzare un coupon ordine NON cumulativo.
     *
     * @static
     *
     * @retval bool
     */
    public static function hasOrderProductsCoupon() {
        $result   = false;
        $products = self::products();
        if ( !empty( $products ) ) {
            foreach ( $products as $product ) {
                if ( !empty( $product['coupon_uniqcode'] ) ) {
                    $result = true;
                    break;
                }
            }
        }
        return $result;
    }

    /**
     * Controlla che i prodotti in sessione abbiamo la variante o variante e campo o variante, campo e valore uguali a
     * quelli passati negli inputs
     *
     * @static
     *
     * @param string       $id_variant ID Della variante
     * @param string       $field      ID field della variante: model, color, ....
     * @param mixed|array  $value      Valore o serie di valori della variante
     *
     * @retval bool
     * Restituisce true se almeno uno dei prodotti ha la variante indicata
     */
    public static function hasProductsVariant( $id_variant, $field = null, $value = null ) {
        $result   = false;
        $products = self::products();
        if ( !empty( $products ) ) {
            foreach ( $products as $product ) {
                if ( ( $result = self::hasProductVariant( $product, $id_variant, $field, $value ) ) ) {
                    break;
                }
            }
        }
        return $result;
    }

    /**
     * Verifica che un determinato prodotto in sessione, identificato dalla key codificata base 64, abbiamo una
     * determinata variante con un campo (opzionale) e un valor (opzionale)
     *
     * @static
     *
     * @param string|array  $id_product_key ID della sessione (ID prodotto + varianti) in jSON codificato base 64 o array
     *                                      prodotto
     * @param string        $id_variant     ID della variante che deve avere
     * @param string        $field          Opzionale, campo che deve avere
     * @param string        $value          Opzionale, valore del campo che deve avere
     *
     * @retval bool
     * True se il prodotto ha la variante con i parametri indicati, false se non la possiede
     */
    public static function hasProductVariant( $id_product_key, $id_variant, $field = null, $value = null ) {
        $result = false;

        if ( is_string( $id_product_key ) ) {
            $products = self::products();
            if ( empty( $products ) ) {
                return $result;
            }
            $product = $products[$id_product_key];
        } elseif ( is_array( $id_product_key ) ) {
            $product = $id_product_key;
        }

        if ( !empty( $product['id_variant'] ) ) {

            if ( !is_null( $value ) && !is_null( $field ) && isset( $product[$field] ) ) {
                if ( is_array( $value ) ) {
                    $bool_value = in_array( $product[$field], $value );
                } else {
                    $bool_value = ( $product[$field] == $value );
                }
                if ( $product['id_variant'] == $id_variant && $bool_value ) {
                    $result = true;
                }
            } elseif ( !is_null( $field ) ) {
                if ( $product['id_variant'] == $id_variant && !empty( $product[$field] ) ) {
                    $result = true;
                }
            } elseif ( $product['id_variant'] == $id_variant ) {
                $result = true;
            }
        }
        return $result;
    }

}