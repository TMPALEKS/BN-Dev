<?php
/**
 * @class              WPXSmartShopOrders
 * @description        Modello per la gestione degli ordini
 *
 * @package            wpx SmartShop
 * @subpackage         orders
 * @author             =undo= <g.fazioli@wpxtre.me>
 * @copyright          Copyright (c)2012 wpXtreme, Inc.
 * @created            28/11/11
 * @version            1.0
 *
 * @todo               Questa va un attimo rivista in occasione dell'introduzione della WPDKDBTable. Inparticolare
 *                     rivedere il create e l'update che sono sbagliati.
 * @todo               La read, ad esempio, non viene mai usata...
 *
 */

class WPXSmartShopOrders extends WPDKDBTable {

    // -----------------------------------------------------------------------------------------------------------------
    // Static values
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Restituisce il nome della tabella degli ordini
     *
     * @static
     * @retval string
     */
    public static function tableName() {
        global $wpdb;
        return sprintf( '%s%s', $wpdb->prefix, WPXSMARTSHOP_DB_TABLENAME_ORDERS );
    }

    /**
     * Costruisce e restituisce l'array usato dall'engine WPDKForm per l'inserimento e l'editing di un ordine
     *
     * @static
     *
     * @param int $id ID dell'ordine
     *
     * @retval array
     */
    public static function fields( $id = null ) {
        if ( !is_null( $id ) ) {
            /* @todo Il metodo è polimorfico */
            $order = self::order( absint( $id ), ARRAY_A );
        }

        $fields = array(
            __( 'Order information', WPXSMARTSHOP_TEXTDOMAIN )   => array(
                __( 'Important', WPXSMARTSHOP_TEXTDOMAIN ),
                array(
                    array(
                        'type'      => WPDK_FORM_FIELD_TYPE_DATETIME,
                        'label'     => __( 'Order Date', WPXSMARTSHOP_TEXTDOMAIN ),
                        'name'      => 'order_datetime',
                        'size'      => 18,
                        'not null'  => true,
                        'value'     => isset( $order['order_datetime'] ) ? WPDKDateTime::formatFromFormat( $order['order_datetime'], 'Y-m-d H:i:s', __( 'm/d/Y H:i', WPXSMARTSHOP_TEXTDOMAIN ) ) : date( __( 'm/d/Y H:i', WPXSMARTSHOP_TEXTDOMAIN ) )
                    ),
                    array(
                        'type'      => WPDK_FORM_FIELD_TYPE_SELECT,
                        'label'     => __( 'Status', WPXSMARTSHOP_TEXTDOMAIN ),
                        'name'      => 'status',
                        'value'     => isset( $order['status'] ) ? $order['status'] : WPXSMARTSHOP_ORDER_STATUS_CONFIRMED,
                        'options'   => self::arrayStatusesForSDF( self::arrayStatuses() )
                    )
                ),
                array(
                    array(
                        'type'      => WPDK_FORM_FIELD_TYPE_CUSTOM,
                        'id_order'  => $id,
                        'callback'  => array( __CLASS__, 'productsList' )
                    )
                )
            ),
            __( 'Price information', WPXSMARTSHOP_TEXTDOMAIN )   => array(
                array(
                    array(
                        'type'   => WPDK_FORM_FIELD_TYPE_NUMBER,
                        'name'   => 'subtotal',
                        'label'  => __( 'Sub total', WPXSMARTSHOP_TEXTDOMAIN ),
                        'append' => WPXSmartShopCurrency::currencySymbol(),
                        'value'  => isset( $order['subtotal'] ) ? $order['subtotal'] : '0.00'
                    )
                ),
                array(
                    array(
                        'type'   => WPDK_FORM_FIELD_TYPE_NUMBER,
                        'name'   => 'tax',
                        'label'  => __( 'Tax', WPXSMARTSHOP_TEXTDOMAIN ),
                        'append' => '%',
                        'value'  => isset( $order['tax'] ) ? $order['tax'] : WPSmartShopShippingCountries::vatShop()
                    )
                ),
                array(
                    array(
                        'type'     => WPDK_FORM_FIELD_TYPE_NUMBER,
                        'name'     => 'shipping',
                        'append'   => WPXSmartShopCurrency::currencySymbol(),
                        'label'    => __( 'Shipping', WPXSMARTSHOP_TEXTDOMAIN ),
                        'value'    => isset( $order['shipping'] ) ? $order['shipping'] : '0.00'
                    )
                ),
                array(
                    array(
                        'type'     => WPDK_FORM_FIELD_TYPE_NUMBER,
                        'name'     => 'total',
                        'append'   => WPXSmartShopCurrency::currencySymbol(),
                        'label'    => __( 'Total', WPXSMARTSHOP_TEXTDOMAIN ),
                        'readonly' => true,
                        'value'    => isset( $order['total'] ) ? $order['total'] : '0.00'
                    )
                ),
            ),
            __( 'Billing information', WPXSMARTSHOP_TEXTDOMAIN ) => array(
                array(
                    array(
                        'type'  => WPDK_FORM_FIELD_TYPE_TEXT,
                        'name'  => 'bill_first_name',
                        'size'  => 32,
                        'label' => __( 'First Name', WPXSMARTSHOP_TEXTDOMAIN ),
                        'value' => isset( $order['bill_first_name'] ) ? $order['bill_first_name'] : ''
                    ),
                    array(
                        'type'  => WPDK_FORM_FIELD_TYPE_TEXT,
                        'name'  => 'bill_last_name',
                        'size'  => 32,
                        'label' => __( 'Last name', WPXSMARTSHOP_TEXTDOMAIN ),
                        'value' => isset( $order['bill_last_name'] ) ? $order['bill_last_name'] : ''
                    )
                ),
                array(
                    array(
                        'type'      => WPDK_FORM_FIELD_TYPE_EMAIL,
                        'name'      => 'bill_email',
                        'size'      => 32,
                        'label'     => __( 'Email', WPXSMARTSHOP_TEXTDOMAIN ),
                        'value'     => isset( $order['bill_email'] ) ? $order['bill_email'] : '',
                    ),
                    array(
                        'type'  => WPDK_FORM_FIELD_TYPE_TEXT,
                        'name'  => 'bill_address',
                        'size'  => 32,
                        'label' => __( 'Address', WPXSMARTSHOP_TEXTDOMAIN ),
                        'value' => isset( $order['bill_address'] ) ? $order['bill_address'] : ''
                    ),
                ),
                array(
                    array(
                        'type'  => WPDK_FORM_FIELD_TYPE_NUMBER,
                        'name'  => 'bill_zipcode',
                        'size'  => 8,
                        'label' => __( 'ZIP code', WPXSMARTSHOP_TEXTDOMAIN ),
                        'value' => isset( $order['bill_zipcode'] ) ? $order['bill_zipcode'] : ''
                    ),
                    array(
                        'type'  => WPDK_FORM_FIELD_TYPE_TEXT,
                        'name'  => 'bill_town',
                        'size'  => 16,
                        'label' => __( 'Town', WPXSMARTSHOP_TEXTDOMAIN ),
                        'value' => isset( $order['bill_town'] ) ? $order['bill_town'] : ''
                    ),
                    array(
                        'type'    => WPDK_FORM_FIELD_TYPE_SELECT,
                        'name'    => 'bill_country',
                        'value'   => isset( $order['bill_country'] ) ? $order['bill_country'] : '',
                        'options' => WPSmartShopShippingCountries::countriesForSelectMenu()
                    )
                ),
                array(
                    array(
                        'type'    => WPDK_FORM_FIELD_TYPE_PHONE,
                        'label'   => __( 'Phone', WPXSMARTSHOP_TEXTDOMAIN ),
                        'size'    => 10,
                        'name'    => 'bill_phone',
                        'value'   => isset( $order['bill_phone'] ) ? $order['bill_phone'] : '',
                    ),
                )
            ),            
        );
        
        $fields = apply_filters( 'wpxss_orders_custom_field', $fields, $order );
        return $fields;
    }

    /**
     * Restituisce l'elenco degli stati della tabella wpss_orders
     *
     * @static
     * @see self::statusesWithCount()
     * @retval array
     */
    static function arrayStatuses() {

        $statuses = array(
            'all'                            => array(
                'label' => __( 'All', WPXSMARTSHOP_TEXTDOMAIN ),
                'count' => 0
            ),
            WPXSMARTSHOP_ORDER_STATUS_PENDING   => array(
                'label' => __( 'Pending', WPXSMARTSHOP_TEXTDOMAIN ),
                'count' => 0
            ),
            WPXSMARTSHOP_ORDER_STATUS_CONFIRMED => array(
                'label' => __( 'Confirmed', WPXSMARTSHOP_TEXTDOMAIN ),
                'count' => 0
            ),
            WPXSMARTSHOP_ORDER_STATUS_CANCELLED => array(
                'label' => __( 'Cancelled', WPXSMARTSHOP_TEXTDOMAIN ),
                'count' => 0
            ),
            WPXSMARTSHOP_ORDER_STATUS_DEFUNCT   => array(
                'label' => __( 'Defunct', WPXSMARTSHOP_TEXTDOMAIN ),
                'count' => 0
            ),
            'trash'                          => array(
                'label' => __( 'Trash', WPXSMARTSHOP_TEXTDOMAIN ),
                'count' => 0
            )
        );
        return $statuses;
    }

    /**
     * Restituisce un array con il tipo di status, la sua label e la count sul database
     *
     * @static
     * @retval array
     */
    function statuses() {
        return parent::statusesWithCount( self::tableName(), self::arrayStatuses() );
    }


    /**
   	 * Usato per popolare il combo menu della durata
   	 *
     * @todo Pensare di metterlo sotto altra forma in WPDK
   	 *
   	 * @static
   	 * @retval array
   	 */
   	public static function durabilityType() {
   		$result = array(
   			'minutes'   => __( 'Minutes', WPXSMARTSHOP_TEXTDOMAIN ),
   			'days'      => __( 'Days', WPXSMARTSHOP_TEXTDOMAIN ),
   			'months'    => __( 'Months', WPXSMARTSHOP_TEXTDOMAIN ),
   			'years'     => __( 'Years', WPXSMARTSHOP_TEXTDOMAIN ),
   		);
   		return $result;
   	}

    /**
     * Genera un ID univoco universale per identificare la transazione durante l'acquisto di un prodotto.
     *
     * @static
     * @retval string Track ID per l'ordine
     */
    public static function trackID() {
        $track_id = uniqid();
        $track_id = strtoupper( sprintf( '%s-%s', substr( $track_id, 0, 6 ), substr( $track_id, 6, 13 ) ) );
        return $track_id;
    }

    /**
     * Metodo polimorfico in grado di restituire l'id dell'ordine in base al parametro di input
     *
     * @static
     *
     * @param int|string|object|array $order Ordine
     *
     * @retval int ID dell'ordine
     */
    public static function id( $order ) {
        $id_order = false;

        if ( is_numeric( $order ) ) {
            $id_order = $order;
        } elseif ( is_string( $order ) ) {
            $id_order = self::order( $order )->id;
        } elseif ( is_object( $order ) ) {
            $id_order = $order->id;
        } elseif ( is_array( $order ) ) {
            $id_order = $order['id'];
        }
        return $id_order;
    }

    // -----------------------------------------------------------------------------------------------------------------
    // Database actions
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Restituisce le informazioni su un ordine con un dato ID o trackID
     *
     * @static
     *
     * @param int|string    $id     ID o trackID dell'ordine
     * @param string        $output Tipo di restituzione: oggetti o array. Presa in considerazione solo se $id non è già
     *                              un object o un array
     *
     * @retval mixed Array o Object della riga rappresentatnte un ordine
     */
    public static function order( $id, $output = OBJECT ) {
        global $wpdb;

        if( $id === false ) {
            return $id;
        }

        $where = '';
        if ( is_numeric( $id ) ) {
            $where = sprintf( 'WHERE id = %s',  $id );
        } elseif ( is_string( $id ) ) {
            $where = sprintf( 'WHERE track_id = \'%s\'', $id );
        } elseif ( is_object( $id ) || is_array( $id ) ) { // paradossalmente potrebbe essere già un ordine
            return $id;
        }

        $table = self::tableName();

        $sql = <<< SQL
        SELECT * FROM `{$table}`
        {$where}
SQL;
        $row = $wpdb->get_row( $sql, $output );
        return $row;
    }


    /**
     * Restituisce la lista di ordini in un determinato stato per un determinato utente. Usata per la ricostrustrione
     * della sessione del carrello, cercando ordini pending per un determinato utente.
     *
     * @static
     *
     * @param int    $id_user ID Utente
     * @param string $status  Stato dell'ordine, default WPXSMARTSHOP_ORDER_STATUS_PENDING
     * @param string $output  Tipo di restituzione: oggetti o array
     *
     * @retval array Elenco ordini
     */
    public static function ordersWithUserAndStatus( $id_user, $status = WPXSMARTSHOP_ORDER_STATUS_PENDING, $output = OBJECT ) {
        global $wpdb;

        $table = self::tableName();

        $sql  = <<< SQL
        SELECT * FROM `{$table}`
        WHERE `id_user` = '{$id_user}' AND `status` = '{$status}'
SQL;
        $rows = $wpdb->get_results( $sql, $output );

        return $rows;
    }

    /**
     * Aggiunge un ordine
     *
     * @todo       Manca controllo errore
     *
     * @static
     *
     * @param array $args Array con i valori da inserire
     *
     * @retval array|WP_Error Restituisce un array con le informazioni arrivata negli inputs, quelle standard e quelle
     *             ottenute dopo l'inserimento, altrimenti WP_Error
     */
    public static function create( $args = array() ) {
        global $wpdb;

        $values    = array(
            'order_datetime'      => date( 'Y-m-d H:i:s' ),
            'id_user'             => get_current_user_id(),
            'status'              => WPXSMARTSHOP_ORDER_STATUS_PENDING
        );
        $newValues = array_merge( $args, $values );

        /**
         * Filtro sull'array dei valori che stanno per essere inseriti nel database
         * @note Questo filtro agisce anche da backend
         * @param array $values Array dei valori
         *
         * @retval array Array dei valori
         *
         */
        $newValues = apply_filters('wpss_orders_will_insert', $newValues);

        $result = $wpdb->insert( self::tableName(), $newValues );
        if( is_wp_error( $result ) ) {
            return $result;
        }

        /* Aggiunge/Cambia key/value per restituirle indietro */
        $newValues['id'] = $wpdb->insert_id;

        //$stats = new WPXSmartShopStats();
        //$stats->create( $wpdb->insert_id );
        WPXSmartShopStats::create( $wpdb->insert_id );

        /* Aggiorna stato coupon ordine se presente */
        $order_coupon_id = WPXSmartShopSession::orderCouponID();


        if ( !empty( $order_coupon_id ) ) {
            WPXSmartShopCoupons::updateStatus( $order_coupon_id, WPXSMARTSHOP_COUPON_STATUS_PENDING );
        }

        do_action( 'wpss_orders_after_insert', $newValues['id'] );


        return $newValues;
    }

    /**
     * Aggiorna un ordine
     *
     * @static
     *
     * @retval mixed
     */
    public static function update( $id, $values ) {
        $result = parent::update( self::tableName(), $id, $values );
        do_action('wpss_orders_after_update', $id);

        return $result;

    }


    /// Delete one o more order
    /**
     * Delete from database one or more order
     * @static
     *
     * @param int|array $id_order Single ID order or array order id
     *
     * @return mixed
     */
    public static function delete( $id_order ) {
    
        $result = parent::delete( self::tableName(), $id_order );

        /* This operation is permately, so remove stats record too */
        WPXSmartShopStats::deleteWithOrder( $id_order );

        /* Aggiorno i contatori */
        if ( is_array( $id_order ) ) {
            foreach ( $id_order as $id ) {
                self::updateProductsQuantityWithOrderID( $id );
            }
        } else {
            self::updateProductsQuantityWithOrderID( $id_order );
        }

        do_action('wpss_orders_after_delete');

        return $result;
    }

    /**
     * Aggiorna un ordine
     *
     * @deprecated Rename in update()
     *
     * @static
     *
     * @param int   $id
     * @param array $args
     *
     * @retval bool
     */
    public static function updateOrderWithID( $id, $args ) {
        global $wpdb;

        $result = false;
        if ( is_array( $args ) ) {
            $where  = array( 'id' => $id );
            $result = $wpdb->update( self::tableName(), $args, $where );
        }
        if($result)
            do_action('wpss_orders_after_update');
        return $result;
    }



    // -----------------------------------------------------------------------------------------------------------------
    // Cambiamenti di stato: usare questi per scatenare filtri e action
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Conferma un ordine. Questo è un alias in realtà, si potrebbe chianmare la updateOrderStatusWithTrackID() ma
     * questo metodo genera una action specifica.
     *
     * @static
     * @action     wpss_order_confirmed
     * @uses       updateOrderStatusWithTrackID()
     *
     * @param string $trackID
     * @param null   $transactionID
     * @param null   $note
     */
    public static function orderConfirmed( $trackID, $transactionID = null, $note = null ) {

        self::updateOrderStatusWithTrackID( $trackID, WPXSMARTSHOP_ORDER_STATUS_CONFIRMED, $transactionID, $note );

        /**
         * Ordine confermato
         *
         * @action
         *
         * @param string $trackID Identificativo dell'ordine
         */
        do_action( 'wpss_order_confirmed', $trackID );

        /* Recupero oggetto ordine dal suo track id */
        $order = self::order( $trackID );

        /* Prodotti con creazione Membership? */
        WPSmartShopProductMembership::membershipWithOrder( $order );

        /* Prodotti con creazione Coupon? */
        $result = WPSmartShopProductCoupon::couponsWithOrder( $order );
        /* Array keypair con id_prodotto ed array di elenco id coupon creati */
        if( !empty( $result ) ) {
            do_action( 'wpxss_product_coupons_with_order', $order, $result );
        }
    }

    /**
     * Abbulla un ordine. Questo è un alias in realtà, si potrebbe chianmare la updateOrderStatusWithTrackID() ma
     * questo metodo genera una action specifica.
     *
     * @static
     * @action     wpss_order_cancelled
     * @uses       updateOrderStatusWithTrackID()
     *
     * @param string     $trackID
     * @param null       $transactionID
     * @param null       $note
     */
    public static function orderCancelled( $trackID, $transactionID = null, $note = null ) {
        self::updateOrderStatusWithTrackID( $trackID, WPXSMARTSHOP_ORDER_STATUS_CANCELLED, $transactionID, $note );
        do_action( 'wpss_order_cancelled', $trackID );
    }

    // -----------------------------------------------------------------------------------------------------------------
    // Database
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Crea o aggiorna (esegue un delta) la tabella degli ordini
     *
     * @static
     */
    public static function updateTable() {
        if ( !function_exists( 'dbDelta' ) ) {
            require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        }
        $dbDeltaTableFile = sprintf( '%s%s', WPXSMARTSHOP_PATH_DATABASE, kWPSmartShopOrdersTableFilename );
        $file             = file_get_contents( $dbDeltaTableFile );
        $sql              = sprintf( $file, self::tableName() );
        @dbDelta( $sql );
    }

    // -----------------------------------------------------------------------------------------------------------------
    // Database Commodity (updates)
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Aggiorna lo stato di un ordine identificato dal suo trackID
     *
     * @static
     *
     * @param string $trackID       Track ID dell'ordine
     * @param string $status        Stato
     * @param string $transactionID ID della transazione; normalmente è un parametro generato dalla banca
     * @param string $note          Ulteriori informazioni dipendenti dal gateway che si sta usando
     *
     * @retval mixed
     */
    private static function updateOrderStatusWithTrackID( $trackID, $status, $transactionID = null, $note = null ) {
        global $wpdb;

        $values = array(
            'status' => $status
        );

        if ( !is_null( $transactionID ) ) {
            $values['transaction_id'] = $transactionID;
        }

        if ( !is_null( $note ) ) {
            $values['payment_result'] = $note;
        }

        $where = array(
            'track_id' => $trackID
        );

        $result = $wpdb->update( self::tableName(), $values, $where );

        /* Aggiorno contatori prodotto */
        self::updateProductsQuantityWithTrackID( $trackID );

        /* Segnala ai coupon che l'ordine ha cambiato stato */
        WPXSmartShopCoupons::didOrderStatusUpdated( $trackID, $status );

        return $result;
    }

    /**
     * Aggiorna lo user_order di un ordine identificato dal suo trackID
     *
     * @static
     *
     * @param string     $trackID
     * @param int        $id_user_order
     * @param null       $note
     *
     * @retval mixed
     */
    private static function updateUserOrderWithTrackID( $trackID, $id_user_order, $note = null ) {
        global $wpdb;

        $values = array(
            'id_user_order' => $id_user_order
        );

        if ( !is_null( $note ) ) {
            $values['payment_result'] = $note;
        }

        $where = array(
            'track_id' => $trackID
        );

        $result = $wpdb->update( self::tableName(), $values, $where );

        return $result;
    }


    /// Aggiorna i conteggi del magazzino
    /**
     * Aggiorna e restituisce un array con chiave (id_product) e valore un array con chiave (status) e valore conteggio,
     * tipo:
     *
     * @code
     * array(2) {
     *   [2274] => array(4) {
     *     ["pending"]   => string(1) "1"
     *     ["cancelled"] => string(1) "2"
     *     ["defunct"]   => int(0)
     *     ["confirmed"] => string(1) "1"
     *   }
     *   [2567] => array(4) {
     *     ["pending"]   => string(1) "2"
     *     ["confimed"]  => int(0)
     *     ["cancelled"] => string(1) "1"
     *     ["defunct"]   => int(0)
     *   }
     * }
     * @endcode
     *
     * @static
     *
     * @param int  $id_order ID ordine. Tutti i prodtti dell'ordine sono aggiorni.
     * @param bool $update_meta Aggiorna anche i post meta dei prodotti
     *
     * @retval array Array con chiave (id_product) e valore un array con chiave (status) e valore conteggio
     *
     * @note Use the filter `wpxss_product_store_quantity_for_order`
     *
     * @todo   Farla diventare polimorfica, ovvero che risponda a diversi input primari
     */
    public static function updateProductsQuantityWithOrderID( $id_order, $update_meta = true ) {

        /* Lista prodotti di quest'ordine ordine */
        $products = WPXSmartShopStats::productsWithOrderID( $id_order );

        /* Creo array per conteggio */
        $result = array();
        foreach ( $products as $product ) {
            $result[$product['id_product']] = array(
                WPXSMARTSHOP_ORDER_STATUS_CANCELLED   => 0,
                WPXSMARTSHOP_ORDER_STATUS_CONFIRMED   => 0,
                WPXSMARTSHOP_ORDER_STATUS_DEFUNCT     => 0,
                WPXSMARTSHOP_ORDER_STATUS_PENDING     => 0,
            );
        }

        /* Elenco ID prodotto */
        $ids_products = array_keys( $result );

        /* Conto gli ID di sopra raggruppando per stato/id prodotto */
        $counts = WPXSmartShopStats::productsCountDistinct( $ids_products );
        
        /* Imposto per risultati */
        foreach ( $counts as $count ) {
            $id_product = $count['id_product'];
            if ( isset( $result[$id_product] ) ) {
                $status = $count['status'];

                /**
                 * Questo filtro permette di alterare la quantità quantità di un particolare prodotto, ordine e stato.
                 * Ultile qundo un prodotto è cumulativo ad esempio, e quindi il suo acquisto ne decurta in realtà
                 * più entità.
                 *
                 */
                $qty = apply_filters( 'wpxss_product_store_quantity_for_order', $count['qty'], $id_order, $id_product, $status, $count );

                $result[$id_product][$status] += $qty;
            }
        }

        /* Aggiorno anche i post meta dei prodotti singoli? */
        if ( $update_meta ) {
            foreach ( $result as $id_product => $counts ) {
                foreach ( $counts as $status => $qty ) {
                    /* Update meta data */
                    $meta_key = 'wpss_product_store_quantity_for_order_' . $status;
                    update_post_meta( $id_product, $meta_key, $qty );
                }
            }
        }

        return $result;
    }

    /**
     * Restituisce un array con chiave (id_product) e valore un array con chiave (status) e valore conteggio, tipo:
     *
     * @static
     *
     * @param string     $trackID
     * @param bool       $update_meta Aggiorna anche i post meta dei prodotti
     *
     * @retval array Array con chiave (id_product) e valore un array con chiave (status) e valore conteggio
     *
     * @see        self::updateProductsQuantityWithOrderID()
     */
    public static function updateProductsQuantityWithTrackID( $trackID, $update_meta = true ) {
        $order  = self::order( $trackID );
        $result = self::updateProductsQuantityWithOrderID( $order->id, $update_meta );
        return $result;
    }

    // -----------------------------------------------------------------------------------------------------------------
    // Database Commodity (exists and check)
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Restituisce True se esiste un ordine con uno specifico TrackID
     *
     * @static
     *
     * @param int|string    $id ID o trackID dell'ordine
     *
     * @retval bool True se l'ordine esiste, altrimenti false
     */
    public static function orderExists( $id ) {
        $row = self::order( $id );

        return !is_null( $row );
    }

    /**
     * Restituisce True se esiste un ordine con uno specifico TrackID e uno stato
     *
     * @static
     *
     * @param int|string    $id      ID o trackID dell'ordine
     * @param string        $status  default WPXSMARTSHOP_ORDER_STATUS_PENDING
     *
     * @retval bool True se l'ordine esiste
     */
    public static function orderExistsWithStatus( $id, $status = WPXSMARTSHOP_ORDER_STATUS_PENDING ) {
        global $wpdb;

        $where = '';
        if ( is_int( $id ) ) {
            $where = sprintf( 'WHERE %s = %s', 'id', $id );
        } elseif ( is_string( $id ) ) {
            $where = sprintf( 'WHERE %s = \'%s\'', 'trackID', $id );
        }

        $table = self::tableName();

        $sql = <<< SQL
        SELECT COUNT(*) FROM `{$table}`
        {$where}
        AND `status` = '{$status}'
SQL;
        $row = $wpdb->get_row( $sql );
        return ( $row > 0 );
    }

    /**
     * Verifica se esiste almeno un ordine, in un determinato stato, per un determinato utente. Questo viene
     * soprattutto usato per verificare se qualche utente ha lasciato qualche ordine in status pending.
     *
     * @static
     *
     * @param int    $id_user ID Utente - colui che ha effettuato l'ordine, non verso chi l'ordine è stato fatto
     * @param string $status  Stato dell'ordine. Per default WPXSMARTSHOP_ORDER_STATUS_PENDING
     *
     * @retval bool Se esiste almeno un ordine per quell'utente in quello stato
     */
    public static function orderExistsWithUserAndStatus( $id_user, $status = WPXSMARTSHOP_ORDER_STATUS_PENDING ) {
        global $wpdb;

        $table = self::tableName();

        $sql = <<< SQL
        SELECT COUNT(*) FROM `{$table}`
        WHERE `id_user` = '{$id_user}' AND `status` = '{$status}'
SQL;
        $row = $wpdb->get_var( $sql );

        return ( $row > 0 );
    }



    /**
     * Crea una tabella negli ordini per mostrare le informazioni sui prodotti legati all'ordine in questione
     *
     * @todo Fare elimina
     * @todo Fare aggiungi
     *
     * @static
     *
     * @param $item
     */
    public static function productsList( $item ) {
        if ( !is_null( $item['id_order'] ) ) {
            $results = WPXSmartShopStats::productsWithOrderID( $item['id_order'] );
            ?>
        <table class="wpssOrderProductsList" border="0" cellpadding="0" cellspacing="0">
            <thead>
            <tr>
                <th colspan="2"><?php _e( 'Product', WPXSMARTSHOP_TEXTDOMAIN ) ?></th>
                <th><?php _e( 'Qty', WPXSMARTSHOP_TEXTDOMAIN ) ?></th>
                <th><?php _e( 'Price', WPXSMARTSHOP_TEXTDOMAIN ) ?></th>
                <th><?php _e( 'Total', WPXSMARTSHOP_TEXTDOMAIN ) ?></th>
            </tr>
            </thead>
            <tbody>
                <?php
                $subTotal = 0;
                foreach ( $results as $record ) :
                    $total = $record['product_amount'] * $record['qty'];
                    $subTotal += $record['product_amount'] * $record['qty'];
                    $variant = '';
                    if ( !empty( $record['id_variant'] ) ) {
                        $choose  = self::variant( $record );
                        $variant = sprintf( ' (<strong>%s</strong>: %s)', $record['id_variant'], $choose );
                    }

                    ?>
                <tr>
                    <td><input type="button" class="delete" value="<?php _e( 'Delete', WPXSMARTSHOP_TEXTDOMAIN ) ?>"
                               title="<?php _e( 'Delete', WPXSMARTSHOP_TEXTDOMAIN ) ?>"/></td>
                    <td><?php echo $record['product_title'] . $variant ?></td>
                    <td><?php echo $record['qty'] ?></td>
                    <td><?php echo WPXSmartShopCurrency::formatCurrency( $record['product_amount'] ) ?></td>
                    <td><?php echo WPXSmartShopCurrency::formatCurrency( $total ) ?></td>
                </tr>
                    <?php endforeach; ?>
            </tbody>
            <tfoot>
            <tr>
                <td colspan="4"><?php _e( 'Total', WPXSMARTSHOP_TEXTDOMAIN ) ?></td>
                <td><?php echo WPXSmartShopCurrency::formatCurrency( $subTotal ) ?></td>
            </tr>
            </tfoot>
        </table>
        <?php
        }
    }

    /**
     * A partire dal record estratto dalla tabella stats, vengono controllati i campi 'variant' e se trovati non
     * vuoti viene restituita una stringa con i valori seprarti da virgola
     *
     * @static
     *
     * @param object $record Record dal database
     *
     * @retval string
     */
    private static function variant( $record ) {
        $fields = array_keys( WPXSmartShopProduct::appearanceFields() );
        $result = array();
        foreach ( $fields as $key ) {
            if ( !empty( $record[$key] ) ) {
                $result[] = $record[$key];
            }
        }
        return join( ',', $result );
    }


    // -----------------------------------------------------------------------------------------------------------------
    // Export
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Crea un buffer testuale in formato CVS
     *
     * @static
     *
     * @param array $data Array di array elementi esratti dalla select statistiche in tabella
     *
     * @todo Questa potrebbe diventare una classe WPDK
     *
     * @retval string
     */
    public static function exportCSV( $data ) {

        $columns = array(
            __( 'Order', WPXSMARTSHOP_TEXTDOMAIN ),
            __( 'Transaction ID', WPXSMARTSHOP_TEXTDOMAIN ),
            __( 'Date', WPXSMARTSHOP_TEXTDOMAIN ),
            __( 'Ordered by', WPXSMARTSHOP_TEXTDOMAIN ),
            __( 'Ordered for', WPXSMARTSHOP_TEXTDOMAIN ),
            __( 'Payment', WPXSMARTSHOP_TEXTDOMAIN ),
            __( 'Total', WPXSMARTSHOP_TEXTDOMAIN ),
        );

        /* Crea il CSV */
        $buffer =  '';
        foreach( $data as $item ) {
            $payment = '';
            if ( !empty( $item['payment_type'] ) && !empty( $item['payment_gateway'] ) ) {
                $payment = sprintf( '%s:%s', $item['payment_type'], $item['payment_gateway'] );
            } elseif ( !empty( $item['payment_type'] ) && empty( $item['payment_gateway'] ) ) {
                $payment = $item['payment_type'];
            }

            $buffer .= sprintf( '"# %s - %s","%s","%s","%s","%s","%s","%s"',
                $item['id'],
                $item['track_id'],
                $item['transaction_id'],
                $item['order_datetime'],
                $item['user_display_name'],
                $item['user_order_display_name'],
                $payment,
                sprintf( '%s %s', WPXSmartShopCurrency::currencySymbol(), WPXSmartShopCurrency::formatCurrency( $item['total'] ) )

            );
            $buffer .= WPDK_CRLF;
        }

        $columns_row = sprintf( '"%s"', join( '","', $columns ) ) . WPDK_CRLF;
        $result      = $columns_row . $buffer;

        return $result;
    }

    public static function downalodCSV() {
       /* Definisco un filename */
        $filename = sprintf( 'wpxSmartShop-Orders-%s.csv', date( 'Y-m-d H:i:s' ) );

        /* Contenuto */
        $buffer = get_transient( 'wpxss_orders_csv' );

        /* Header per download */
        header( 'Content-Type: application/download' );
        header( 'Content-Disposition: attachment; filename="' . $filename . '"' );
        header( 'Cache-Control: public' );
        header( "Content-Length: " . strlen( $buffer ) );
        header( 'Pragma: no-cache' );
        header( 'Expires: 0' );

        echo $buffer;
    }

    // -----------------------------------------------------------------------------------------------------------------
    // UI
    // -----------------------------------------------------------------------------------------------------------------

    static function summary() {
        global $wpdb;

        /* Costruisco la select */
        $where        = 'WHERE 1';

        /* Nomi delle tabelle */
        $table_stats   = WPXSmartShopStats::tableName();
        $table_orders  = WPXSmartShopOrders::tableName();

        /* Where condiction filters */

        /* Status */
        if ( isset( $_GET['status'] ) ) {
            if ( $_GET['status'] != 'all' ) {
                $where .= sprintf( " AND orders.`status` = '%s'", esc_attr( $_GET['status'] ) );
            } else {
                $where .= " AND orders.`status` <> 'trash'";
            }
        } else {
            $where .= " AND orders.`status` <> 'trash'";
        }

        /* Users */
        if ( isset( $_GET['wpss-order-user-filter'] ) && !empty( $_GET['wpss-order-user-filter'] ) ) {
            $where .= sprintf( ' AND `id_user` = %s', esc_attr( $_GET['wpss-order-user-filter'] ) );
        }

        /* Users order */
        if ( isset( $_GET['wpss-order-userorder-filter'] ) && !empty( $_GET['wpss-order-userorder-filter'] ) ) {
            $where .= sprintf( ' AND `id_user_order` = %s', esc_attr( $_GET['wpss-order-userorder-filter'] ) );
        }

        /* Payment Type */
        if ( isset( $_GET['wpss-order-payment-type-filter'] ) && !empty( $_GET['wpss-order-payment-type-filter'] ) ) {
            $where .= sprintf( ' AND `payment_type` = "%s"', esc_attr( $_GET['wpss-order-payment-type-filter'] ) );
        }

        /* Payment Gateway */
        if ( isset( $_GET['wpss-order-payment-gateway-filter'] ) && !empty( $_GET['wpss-order-payment-gateway-filter'] ) ) {
            $where .= sprintf( ' AND `payment_gateway` = "%s"', esc_attr( $_GET['wpss-order-payment-gateway-filter'] ) );
        }

       /* Products */
        if ( isset( $_GET['wpss-order-product-filter'] ) && !empty( $_GET['wpss-order-product-filter'] ) ) {
            $where .= sprintf( ' AND stats.id_product = %s', esc_attr( $_GET['wpss-order-product-filter'] ) );
        }

        /* Search for track ID */
        if ( isset( $_GET['s'] ) && !empty( $_GET['s'] ) ) {
            $where .= ' AND track_id LIKE "%' . esc_attr( $_GET['s'] ) . '%"';
        }

        /* Date */
        if ( isset( $_GET['wpss-order-datestart-filter'] ) && !empty( $_GET['wpss-order-datestart-filter'] ) ) {
            $date_start_value = WPDKDateTime::dateTime2MySql( esc_attr( $_GET['wpss-order-datestart-filter'] ), __( 'm/d/Y H:i', WPXSMARTSHOP_TEXTDOMAIN ) );
        } elseif ( isset( $_GET['wpss-order-datestart-filter'] ) && empty( $_GET['wpss-order-datestart-filter'] ) ) {
            $date_start_value = date( MYSQL_DATE_TIME, time() - 60 * 60 * 24 * 365 * 30 );
        } else {
            $date_start_value = date( 'Y-m-d 00:01:00' );
        }

        if ( isset( $_GET['wpss-order-dateend-filter'] ) && !empty( $_GET['wpss-order-dateend-filter'] ) ) {
            $date_end_value = WPDKDateTime::dateTime2MySql( esc_attr( $_GET['wpss-order-dateend-filter'] ), __( 'm/d/Y H:i', WPXSMARTSHOP_TEXTDOMAIN ) );
        } else {
            $date_end_value = date( MYSQL_DATE_TIME );
        }

        $where .= sprintf( ' AND TIMESTAMP( order_datetime ) BETWEEN "%s" AND "%s" ', $date_start_value, $date_end_value );

        $sql = <<< SQL
SELECT COUNT( orders.id ) AS qty,
       SUM( orders.total) AS total_amount,
       orders.*,
       users.display_name AS user_display_name,
       users_orders.display_name AS user_order_display_name

FROM `{$table_orders}` AS orders

LEFT JOIN `{$wpdb->users}` AS users ON orders.id_user = users.ID
LEFT JOIN `{$wpdb->users}` AS users_orders ON orders.id_user_order = users_orders.ID

{$where}
GROUP BY orders.payment_type, orders.payment_gateway, orders.status
SQL;

        $data = $wpdb->get_results( $sql, ARRAY_A );

        $total_amount = 0;
        $body         = '';
        foreach ( $data as $item ) {
            $claass_alternate = empty( $claass_alternate ) ? 'class="alternate"' : '';

            $body .= sprintf( '<tr %s><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td style="text-align: right">%s</td></tr>',
                $claass_alternate,
                $item['qty'],
                $item['status'],
                $item['payment_type'],
                $item['payment_gateway'],
                $item['total_amount']
            );

            $total_amount += $item['total_amount'];
        }

        $footer = sprintf( '<th style="text-align: right;font-weight: bold" colspan="5">%s</th>', WPXSmartShopCurrency::formatCurrency( $total_amount ) );

        $labels                  = new stdClass();
        $labels->title           = __( 'Summary', WPXSMARTSHOP_TEXTDOMAIN );
        $labels->qty             = __( 'Qty', WPXSMARTSHOP_TEXTDOMAIN );
        $labels->payment_type    = __( 'Payment type', WPXSMARTSHOP_TEXTDOMAIN );
        $labels->payment_gateway = __( 'Gateway', WPXSMARTSHOP_TEXTDOMAIN );
        $labels->status          = __( 'Status', WPXSMARTSHOP_TEXTDOMAIN );
        $labels->total           = __( 'Total', WPXSMARTSHOP_TEXTDOMAIN );

        $html = <<< HTML
<h2 style="text-align: center">{$labels->title}</h2>
<table class="wp-list-table widefat fixed summary" border="0" cellpadding="0" cellspacing="0">
    <thead>
        <tr>
            <th>{$labels->qty}</th>
            <th>{$labels->status}</th>
            <th>{$labels->payment_type}</th>
            <th>{$labels->payment_gateway}</th>
            <th style="text-align: right">{$labels->total}</th>
        </tr>
    </thead>
    <tbody>
        {$body}
    </tbody>
    <tfoot>
    <tr>
        {$footer}
    </tr>
    </tfoot>
</table>
HTML;

        return $html;
    }

    /**
     * Costruisce il combo menu select per i filtri nei list table
     *
     * @static
     *
     * @param string $id_select Specifica l'attributo name e id dell'elemento select
     * @param string $selected Identificatico dell'eventuale elementp nelle options da preselezionare
     *
     * @retval string
     */
    public static function selectFilterUsers( $id_select, $selected = '' ) {
        global $wpdb;

        $table_orders   = self::tableName();
        $table_wp_users = $wpdb->users;

        /* Seleziono gli utenti in group by */
        $sql   = <<< SQL
        SELECT orders.id_user, users.display_name
        FROM `{$table_orders}` AS orders
        LEFT JOIN `{$table_wp_users}` AS users ON users.ID = orders.id_user
        GROUP BY orders.id_user
        ORDER BY users.display_name
SQL;
        $users = $wpdb->get_results( $sql );

        $options = '';
        foreach ( $users as $user ) {
            if ( !empty( $user->display_name ) ) {
                $options .= sprintf( '<option %s value="%s">%s</option>', selected( $user->id_user, $selected, false ), $user->id_user, $user->display_name );
            }
        }

        $label = __( 'Filter for User', WPXSMARTSHOP_TEXTDOMAIN );

        $html = <<< HTML
        <select class="wpdk-form-select" name="{$id_select}" id="{$id_select}">
            <option value ="">{$label}</option>
            {$options}
        </select>
HTML;
        return $html;
    }

    /**
     * Costruisce il combo menu select per i filtri nei list table
     *
     * @static
     *
     * @param string $id_select Specifica l'attributo name e id dell'elemento select
     * @param string $selected Identificatico dell'eventuale elementp nelle options da preselezionare
     *
     * @retval string
     */
    public static function selectFilterUsersOrder( $id_select, $selected = '' ) {
        global $wpdb;

        $table_orders   = self::tableName();
        $table_wp_users = $wpdb->users;

        /* Seleziono gli utenti in group by */
        $sql   = <<< SQL
        SELECT orders.id_user_order, users.display_name
        FROM `{$table_orders}` AS orders
        LEFT JOIN `{$table_wp_users}` AS users ON users.ID = orders.id_user_order
        GROUP BY orders.id_user_order
        ORDER BY users.display_name
SQL;
        $users = $wpdb->get_results( $sql );

        $options = '';
        foreach ( $users as $user ) {
            if ( !empty( $user->display_name ) ) {
                $options .= sprintf( '<option %s value="%s">%s</option>', selected( $user->id_user_order, $selected, false ), $user->id_user_order, $user->display_name );
            }
        }

        $label = __( 'Filter for User Order', WPXSMARTSHOP_TEXTDOMAIN );

        $html = <<< HTML
        <select class="wpdk-form-select" name="{$id_select}" id="{$id_select}">
            <option value ="">{$label}</option>
            {$options}
        </select>
HTML;
        return $html;
    }

    /**
     * Costruisce il combo menu select per i filtri nei list table
     *
     * @static
     *
     * @param string $id_select Specifica l'attributo name e id dell'elemento select
     * @param string $selected Identificatico dell'eventuale elementp nelle options da preselezionare
     *
     * @retval string
     */
    public static function selectFilterPaymentType( $id_select, $selected = '' ) {
        global $wpdb;

        $table_orders   = self::tableName();

        /* Seleziono i tipi di pagamento in group by */
        $sql   = <<< SQL
SELECT orders.payment_type
FROM `{$table_orders}` AS orders
GROUP BY orders.payment_type
ORDER BY orders.payment_type
SQL;
        $payment_types = $wpdb->get_results( $sql );

        $options = '';
        foreach ( $payment_types as $payment_type ) {
            if ( !empty( $payment_type->payment_type ) ) {
                $options .= sprintf( '<option %s value="%s">%s</option>', selected( $payment_type->payment_type, $selected, false ), $payment_type->payment_type, $payment_type->payment_type );
            }
        }

        $label = __( 'Filter for Payment Type', WPXSMARTSHOP_TEXTDOMAIN );

        $html = <<< HTML
        <select class="wpdk-form-select" name="{$id_select}" id="{$id_select}">
            <option value ="">{$label}</option>
            {$options}
        </select>
HTML;
        return $html;
    }

    /**
     * @static
     *
     * @param string $id_select Specifica l'attributo name e id dell'elemento select
     * @param string $selected Identificatico dell'eventuale elementp nelle options da preselezionare
     *
     * @retval string
     */
    public static function selectFilterPaymentGateway( $id_select, $selected = '' ) {
        global $wpdb;

        $table_orders   = self::tableName();

        /* Seleziono i tipi di pagamento in group by */
        $sql   = <<< SQL
SELECT orders.payment_gateway
FROM `{$table_orders}` AS orders
GROUP BY orders.payment_gateway
ORDER BY orders.payment_gateway
SQL;
        $payment_gateways = $wpdb->get_results( $sql );

        $options = '';
        foreach ( $payment_gateways as $payment_gateway ) {
            if ( !empty( $payment_gateway->payment_gateway ) ) {
                $options .= sprintf( '<option %s value="%s">%s</option>', selected( $payment_gateway->payment_gateway, $selected, false ), $payment_gateway->payment_gateway, $payment_gateway->payment_gateway );
            }
        }

        $label = __( 'Filter for Payment Gateway', WPXSMARTSHOP_TEXTDOMAIN );

        $html = <<< HTML
        <select class="wpdk-form-select" name="{$id_select}" id="{$id_select}">
            <option value ="">{$label}</option>
            {$options}
        </select>
HTML;
        return $html;
    }

    /**
     * Costruisce il combo menu select per i filtri nei list table
     *
     * @static
     *
     * @param string $id_select Specifica l'attributo name e id dell'elemento select
     * @param string $selected Identificatico dell'eventuale elementp nelle options da preselezionare
     *
     * @retval string
     */
    public static function selectFilterProduct( $id_select, $selected = '' ) {
        global $wpdb;

        $table_stats   = WPXSmartShopStats::tableName();

        /* Seleziono i prodotti in group by */
        $sql   = <<< SQL
SELECT COUNT(stats.id_product) AS count, stats.id_product, stats.product_title
FROM `{$table_stats}` AS stats
GROUP BY stats.id_product
ORDER BY stats.product_title
SQL;
        $products = $wpdb->get_results( $sql );

        $options = '';
        foreach ( $products as $product ) {
            if ( !empty( $product->product_title ) ) {
                $options .= sprintf( '<option %s value="%s">%s (%s)</option>', selected( $product->id_product, $selected, false ), $product->id_product, $product->product_title, $product->count );
            }
        }

        $label = __( 'Filter for Product', WPXSMARTSHOP_TEXTDOMAIN );

        $html = <<< HTML
        <select class="wpdk-form-select" name="{$id_select}" id="{$id_select}">
            <option value ="">{$label}</option>
            {$options}
        </select>
HTML;
        return $html;
    }

    // -----------------------------------------------------------------------------------------------------------------
    // has/is Zone
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Restituisce true se almeno uno dei prodotti acquistati in questo ordine è da spedire
     *
     * @static
     *
     * @param int|string|object|array $order Ordine
     */
    public static function hasShippingProducts( $order ) {
        global $wpdb;

        $id_order = self::id( $order );

        $stats = WPXSmartShopStats::tableName();

        $sql = <<< SQL
SELECT COUNT(*)
     FROM `{$stats}` AS stats
LEFT JOIN $wpdb->postmeta AS postmeta ON stats.id_product = postmeta.post_id
     AND postmeta.meta_key = 'wpss_product_is_shipping'
WHERE stats.id_order = {$id_order}
     AND postmeta.meta_value = '1'
SQL;

        $result = $wpdb->get_var( $sql );
        return ( $result > 0 );
    }
}