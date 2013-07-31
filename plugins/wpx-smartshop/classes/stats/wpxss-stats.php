<?php
/**
 * @class              WPXSmartShopStats
 *
 * @description        Classe dedicata alla gestione delle statistiche, ovvero della relazione tra il singolo prodotto
 *                     venduto e l'ordine.
 *
 * @package            wpx SmartShop
 * @subpackage         stats
 * @author             =undo= <g.fazioli@wpxtre.me>
 * @copyright          Copyright (c) 2012 wpXtreme, Inc.
 * @created            13/06/12
 * @version            1.0.0
 *
 * @filename           wpxss-stats
 *
 * @todo               Mancano i comodity method relativi alla CRUD, tipo delete() che dal view controller viene chiamato direttamente quelle del padre
 *
 */

class WPXSmartShopStats extends WPDKDBTable {

    /**
     * Init
     */
    function __construct() {
        parent::__construct( self::tableName(), 'id' );
    }

    // -----------------------------------------------------------------------------------------------------------------
    // Static values
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Restituisce il nome della tabbella delle statistiche
     *
     * @static
     * @return string Nome completo della tabella stats comprensivo di prefisso WordPress
     */
    static function tableName() {
        global $wpdb;
        return sprintf( '%s%s', $wpdb->prefix, WPXSMARTSHOP_DB_TABLENAME_STATS );
    }

    /**
     * Restituisce l'elenco degli stati della tabella wpss_orders
     *
     * @static
     * @return array
     */
    public static function arrayStatuses() {

        /*
        $statuses = array(
            'all'                            => array(
                'label' => __( 'All', WPXSMARTSHOP_TEXTDOMAIN ),
                'count' => 0
            ),
            'publish' => array(
                'label' => __( 'Publish', WPXSMARTSHOP_TEXTDOMAIN ),
                'count' => 0
            ),
            'trash'                          => array(
                'label' => __( 'Trash', WPXSMARTSHOP_TEXTDOMAIN ),
                'count' => 0
            )
        );*/
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
     * @return array
     */
    function statuses() {
        //return parent::statusesWithCount( self::tableName(), self::arrayStatuses() );

        global $wpdb;

        $table_name = self::tableName();
        $statuses = self::arrayStatuses();
        $field_name = 'status';
        /*
         * @ToDo Decommentare qui se non vogliamo i conteggi dinamici in base al filtro
         * @ToDo La query è molto onerosa
         *
         *
         */
        $table_orders = BNMExtendsOrders::tableName();
               $sql    = <<< SQL
       SELECT DISTINCT( {$table_orders}.{$field_name} ),
              COUNT(*) AS count
       FROM `{$table_name}`
       LEFT JOIN `{$table_orders}`
       ON {$table_orders}.id = {$table_name}.id_order
       GROUP BY {$table_orders}.{$field_name}
SQL;



        $result = $wpdb->get_results( $sql, ARRAY_A );

        foreach ( $result as $status ) {
            if ( !empty( $status['status'] ) ) {
                $statuses[$status['status']]['count'] = $status['count'];
            }
        }

        $statuses['all']['count'] = self::count( $table_name );

        return $statuses;
    }


    /**
     * Crea o aggiorna (esegue un delta) la tabella delle statistiche ordini
     *
     * @static
     *
     */
    public static function updateTable() {
        if ( !function_exists( 'dbDelta' ) ) {
            require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        }
        $dbDeltaTableFile = sprintf( '%s%s', WPXSMARTSHOP_PATH_DATABASE, WPXSMARTSHOP_DB_TABLENAME_FILENAME_STATS );
        $file             = file_get_contents( $dbDeltaTableFile );
        $sql              = sprintf( $file, self::tableName() );
        @dbDelta( $sql );
    }

    // -----------------------------------------------------------------------------------------------------------------
    // Overwrite CRUD
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Crea le informazioni sulla tabella statistiche partendo da un ordine. Il record di un ordine, infatti, non tiene
     * conto del singolo prodotto acquistato, ma solo del totale. È la tabella statistiche che lega ogni singolo
     * prodotto (e quindi anche la sua quantità) ad un determinato ordine.
     * Questo metodo aggiorna anche lo stato degli eventuali coupon prodotto utilizzati nell'ordine.
     *
     * @static
     *
     * @param int $id_order ID ordine
     */
    static function create( $id_order ) {

        /* Le ulteriori informazioni le recupero dalla sessione */
        $products = WPXSmartShopShoppingCart::products();

        /* @todo Serve un array per id prodotto che tenga conto del contatore nth. Qui sotto a blocchi e considero solo
         * $qty. Quindi se acquisto lo stesso prodotto con varianti diverse, vengono creati gruppi diversi dello stesso
         * prodotto, riazzerando il contatore $qty si riazzera anche l'ennesimo $nth ($n)
         */
        $nth = array();

        foreach ( $products as $id_product_key => $product ) {

            /* Lo riazzero per ogni riga/prodotto */
            $values = array(
                'id_order' => $id_order,
            );

            $id_product = $product['id_product'];
            $coupons    = isset( $product['ids_coupon'] ) ? $product['ids_coupon'] : false;

            WPDKWatchDog::watchDog( __CLASS__, __METHOD__, "Numero di Coupon: " . count($coupons) );

            if ( !isset( $nth[$id_product] ) ) {
                $nth[$id_product] = 0;
            }

            /* Determino quanti coupon ho inserito o posso utilizzare */
            $no_coupon = 0;
            if( $coupons ) {
                $available_coupon = count( $coupons );

                /* Il coupon va applicato solo agli ultimi prodotti, quindi devo verificare se ho più o meno coupon
                rispetto ai prodotti che ho preso
                */
                $no_coupon = $product['qty'] - $available_coupon;
                $no_coupon = max(0, $no_coupon);
            }

            for ( $n = 0; $n < $product['qty']; $n++ ) {
                $values['id_product'] = $id_product;

                /* Recupero eventuale variante */
                $id_variant = '';
                if ( isset( $product['id_variant'] ) && !empty( $product['id_variant'] ) ) {
                    $id_variant = $product['id_variant'];
                }

                $product_amount       = WPXSmartShopProduct::price( $id_product, 1, $id_variant, ( $n + $nth[$id_product] ) );
                $values['price_rule'] = WPXSmartShopProduct::$price_rule;
                $values['amount']     = $product_amount;

                /* Custom discount */

                /**
                 * @filters
                 *
                 * Comunica a SmartShop il codice sconto personalizzato.
                 * Questo filtro controlla wpss_summary_order_apply_custom_discount, se viene restituito un valore vuoto o questo
                 * filtro non viene impostato, anche wpss_summary_order_apply_custom_discount non viene eseguito.
                 *
                 * @param mixed  $rule           Identificatico della regola. Di solito una stringa
                 * @param float  $price
                 * @param string $id_product_key ID prodotto + variante codificata base 64
                 * @param int    $qty            Quantità
                 * @param int    $nth            A partire da
                 *
                 * @return mixed
                 *
                 * @todo Manca il 5th parametro
                 *
                 */
                $custom_price_rule = apply_filters( 'wpss_summary_order_id_custom_discount', '', $product_amount, $id_product_key, 1 );
                if ( !empty( $custom_price_rule ) ) {
                    $values['price_rule'] = $custom_price_rule;

                    /**
                     * @filters
                     *
                     * Applica un determinato sconto ad un prodotto
                     *
                     * @param float  $price
                     * @param string $id_product_key ID prodotto + variante codificata base 64
                     * @param int    $qty            Quantità
                     * @param int    $nth            A partire da
                     *
                     * @return float
                     *
                     * @todo Manca il 4th parametro
                     *
                     */
                    $values['amount'] = apply_filters( 'wpss_summary_order_apply_custom_discount', $product_amount, $id_product_key, 1 );
                }

                unset( $values['id_coupon'] );

                /* Applico gli eventuali coupon solo agli ultimi prodotti, i primi li pago in base alle regole prodotto */
                if ( ( $n >= $no_coupon ) && $coupons && isset( $coupons[$n - $no_coupon] ) ) {
                    $values['id_coupon'] = absint( $coupons[$n - $no_coupon] );
                    $values['amount']    = WPXSmartShopSession::applyProductCoupon( $product_amount, $product );
                    WPXSmartShopCoupons::updateStatus( $coupons[$n], WPXSMARTSHOP_COUPON_STATUS_PENDING );
                }
                $values['product_amount'] = $product_amount;
                $values['product_title']  = $product['product_title'];

                /* Aspetto e Varianti */
                if ( !empty( $id_variant ) ) {
                    $values['id_variant'] = $id_variant;
                    $variants             = WPXSmartShopProduct::appearanceFields();
                    foreach ( $variants as $key => $foo ) {
                        if ( !empty( $product[$key] ) ) {
                            $values[$key] = $product[$key];
                        }
                    }

                    /* Recupero tutte le varianti di questo prodotto */
                    $array = unserialize( get_post_meta( $id_product, 'wpss_product_appearance', true ) );

                    /* Recupero la variante indicata */
                    $variant = $array[$id_variant];

                    /**
                     * Filtro sulle note del singolo prodotto stats
                     *
                     * @filters
                     *
                     * @param string $note    Testo note dalla variante, potrebbe essere vuoto o già valorizzato
                     * @param array  $product Array del prodotto come da sessione
                     * @param array  $variant Array della variante recuperata dai post meta
                     */
                    $note            = apply_filters( 'wpss_stats_create_variant_note', $variant['note'], $product, $variant );
                    $values['note']  = $note;
                    $values['value'] = $variant['value'];
                }

                $values = apply_filters('wpss_stats_extra_values', $values, $id_product);

                parent::create( self::tableName(), $values );
            }
            $nth[$id_product] = $product['qty'];

        }
    }
    
    /**
     * Elimina tutti i record che hanno come ordine $id_order o la lista di ordini.
     * Questa viene soprattutto usata quando bisogna ricostruire un ordine nel payment: ordine già presente ma
     * potrebbe essere cambiato.
     *
     * @static
     *
     * @param int|array $id_order Order id or list of order id
     *
     * @return mixed
     */
    public static function deleteWithOrder( $id_order ) {
        global $wpdb;

        $table = self::tableName();

        if( is_array( $id_order ) ) {
            $ids_order = join( ',', $id_order );
        } else {
            $ids_order = $id_order;
        }

        $sql    = <<< SQL
    DELETE FROM {$table}
    WHERE id_order IN ( {$ids_order} )
SQL;
        $result = $wpdb->query( $sql );

        return $result;
    }

    /**
     * Restituisce l'elenco dei prodotti - contati in modo distinto - appartenenti ad un determinato ordine. Il record
     * che si ottiene ci informa sul tipo di prodotto e quante volte compare in quest'ordine. Titolo e prezzo del
     * prodotto sono uno snapshoot di quando l'ordine è stato creato, eseguendo un match con l'ID del prodotto, sia il
     * titolo che il prezzo potrebbero essere diversi (nel tempo).
     *
     * @static
     *
     * @param int $id_order ID ordine
     *
     * @return array Array di array che rappresentano il record
     */
    public static function productsWithOrderID( $id_order ) {
        global $wpdb;

        $stats   = self::tableName();
        $coupons = WPXSmartShopCoupons::tableName();

        $sql = <<< SQL
        SELECT DISTINCT stats.*,
               COUNT(stats.id_product) AS qty,
               coupons.uniqcode,
               coupons.value AS coupon_value
        FROM `{$stats}` AS stats
        LEFT JOIN {$coupons} AS coupons ON coupons.id = stats.id_coupon

        WHERE stats.id_order = {$id_order}
        GROUP BY stats.id_product,
                 stats.id_coupon,
                 stats.id_variant,
                 stats.weight,
                 stats.width,
                 stats.height,
                 stats.depth,
                 stats.volume,
                 stats.color,
                 stats.material,
                 stats.model,
                 stats.amount
        ORDER BY stats.product_title
SQL;
        $results = $wpdb->get_results( $sql, ARRAY_A );
        return $results;
    }

    /**
     * Restituisce l'elenco di un serie di prodotto distinti e ragruppati per stato ordine e relativa quantità.
     *
     * @static
     *
     * @code
     *
     * status 	id_product 	id_coupon 	id_variant 	weight 	width 	height 	depth 	volume 	color 	material 	model 	amount 	qty
     * ----------------------------------------------------------------------------------------------------------------------------
     * confirmed 	4326 	0 	Cumulative 								                                      2 adults 	60.00 	1
     * confirmed 	4326 	0 	Cumulative 								                       2 adults and 2 children 	60.00 	1
     * confirmed 	4327 	0 										                                                    35.00 	4
     * confirmed 	4328 	0 										                                                    12.00 	1
     *
     * @endcode
     *
     * @param int|array $ids_products ID o array di ID prodotto
     *
     * @return array Restituisce tre colonne dalla tabella stats: status, id_product e qty
     */
    public static function productsCountDistinct( $ids_products ) {
        global $wpdb;

        if( empty( $ids_products ) ) {
            return array();
        }

        if ( !is_array( $ids_products ) ) {
            $ids_products = array( $ids_products );
        }
        $ids_products = join( ',', $ids_products );

        $stats  = self::tableName();
        $orders = WPXSmartShopOrders::tableName();

        $sql     = <<< SQL
SELECT DISTINCT(orders.status),
       stats.id_product,
       stats.id_coupon,
       stats.id_variant,
       stats.weight,
       stats.width,
       stats.height,
       stats.depth,
       stats.volume,
       stats.color,
       stats.material,
       stats.model,
       stats.amount,
       COUNT(stats.id_product) AS qty
FROM `{$stats}` AS stats
LEFT JOIN `{$orders}` AS orders ON orders.id = stats.id_order
WHERE stats.id_product IN( {$ids_products} )
GROUP BY stats.id_product, stats.id_variant, stats.model, orders.status
ORDER BY stats.id_product, stats.id_variant, stats.model, orders.status
SQL;

        $results = $wpdb->get_results( $sql, ARRAY_A );
        return $results;
    }

    /**
     * Restituisce l'elenco dei prodotti (id) e della quantità acquistata da sempre per un determinato utente
     *
     * @static
     *
     * @param int $id_user ID utente
     *
     * @return array Elenco dei prodotti (id) e della quantità acquistata da sempre per un determinato utente
     */
    public static function countsProductsWithUserID( $id_user ) {
        global $wpdb;

        $stats  = self::tableName();
        $orders = WPXSmartShopOrders::tableName();
        $status = WPXSMARTSHOP_ORDER_STATUS_CONFIRMED;

        $sql     = <<< SQL
SELECT DISTINCT stats.id_product,
       COUNT(stats.id_product) AS count
FROM `{$stats}` AS stats,
     `{$orders}` AS orders
WHERE  stats.id_order = orders.id
AND orders.status = '{$status}'
AND orders.id_user_order = {$id_user}
GROUP BY stats.id_product
SQL;
        $rows    = $wpdb->get_results( $sql );
        $results = array();

        /* Raggruppo in array keypair */
        foreach ( $rows as $row ) {
            $results[$row->id_product] = $row->count;
        }

        return $results;
    }

    /**
     * Conta per uno speifico prodotto, quanti con un determinato modello negli ordini confermati
     *
     * @static
     *
     * @param int    $id_product ID del prodotto
     * @param string $model      ID del modello
     *
     * @return mixed
     */
    public static function countsProductsWithModel( $id_product, $model ) {
        global $wpdb;

        $stats  = self::tableName();
        $orders = WPXSmartShopOrders::tableName();
        $status = WPXSMARTSHOP_ORDER_STATUS_CONFIRMED;

        $sql     = <<< SQL
SELECT COUNT(stats.id_product)
FROM `{$stats}` AS stats, `{$orders}` AS orders
WHERE  stats.id_product = {$id_product}
AND orders.status = '{$status}'
AND orders.id = stats.id_order
AND stats.model = '{$model}'
SQL;
        $count = $wpdb->get_var( $sql );

        return $count;
    }

    /**
     * Elenco dei coupon usati in ordini confermati da uno specifico utente per uno specifico prodotto
     *
     * @static
     *
     * @param int $id_user    ID user
     * @param int $id_product ID prodotto
     *
     * @return array
     *             Elenco dei coupon usati in ordini confermati da uno specifico utente per uno specifico prodotto
     */
    private static function countsCouponsWithUserID( $id_user, $id_product ) {
        global $wpdb;

        $stats   = self::tableName();
        $orders  = WPXSmartShopOrders::tableName();
        $coupons = WPXSmartShopCoupons::tableName();

        $sql  = <<< SQL
    SELECT orders.id AS id_order,
           stats.id_coupon,
           stats.id_product,
           COUNT(coupons.uniqcode)
           FROM `{$orders}` AS orders

    LEFT JOIN  `{$stats}` AS stats ON stats.id_order = orders.id
    LEFT JOIN  `{$coupons}` AS coupons ON coupons.id = stats.id_coupon

    WHERE orders.id_user_order = {$id_user}
    AND orders.status = 'confirmed'
    AND stats.id_product = {$id_product}
SQL;
        $rows = $wpdb->get_results( $sql );

        return $rows;
    }

    /**
     * Restituisce la count di quante volte è stato usato un coupon con un codice univoco specifico su ordini già
     * confermati per un dato utente e prodotto
     *
     * @static
     *
     * @param int    $id_user    ID user
     * @param string $uniqcode   Codice univoco del coupon
     * @param int    $id_product ID prodotto
     *
     * @return int Restituisce la count di quante volte è stato usato un coupon con un codice univoco specifico su
     *             ordini già confermati per un dato utente e prodotto
     */
    public static function countsCouponsWithUserIDAndUniqcode( $id_user, $uniqcode, $id_product ) {
        global $wpdb;

        $stats   = self::tableName();
        $orders  = WPXSmartShopOrders::tableName();
        $coupons = WPXSmartShopCoupons::tableName();

        $sql    = <<< SQL
    SELECT COUNT(*)
    FROM `wpbn_wpss_orders` AS orders

    LEFT JOIN  `wpbn_wpss_stats` AS stats ON stats.id_order = orders.id
    LEFT JOIN  `wpbn_wpss_coupons` AS coupons ON coupons.id = stats.id_coupon

    WHERE orders.id_user_order = {$id_user}
    AND orders.status = 'confirmed'
    AND stats.id_product = {$id_product}
    AND coupons.uniqcode = '{$uniqcode}'
SQL;
        $result = $wpdb->get_var( $sql );

        return $result;
    }    
    
    /**
     * Restituisce l'array degli ID dei coupon (id diversi ma stesso uniqcode) applicati ad un prodotto
     *
     * @static
     *
     * @param int $id_order ID dell'ordine
     *
     * @return array
     *
     */
    public static function productCoupons( $id_order ) {
        global $wpdb;
        $tableName = self::tableName();
        $sql       = <<< SQL
        SELECT id_coupon
        FROM {$tableName}
        WHERE id_order = {$id_order}
        AND id_coupon <> 0
SQL;
        $coupons   = $wpdb->get_results( $sql );

        return $coupons;
    }

    // -----------------------------------------------------------------------------------------------------------------
    // Variants
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Restituisce un array keypair
     *
     * @static
     *
     * @param int|array $id ID del record stats o record stats in ARRAY_A
     *
     * @return array Restituisce un array keypair con il codice variante e il suo valore a parte da un record della stats.
     */
    public static function variant( $id ) {
        $record = array();
        if ( is_array( $id ) ) {
            $record = $id;
        } elseif ( is_numeric( $id ) ) {
            /* @todo Select where $id */
        }

        $fields = array_keys( WPXSmartShopProduct::appearanceFields() );
        $result = array();
        foreach ( $fields as $key ) {
            if ( !empty( $record[$key] ) ) {
                $result[$key] = $record[$key];
            }
        }

        if ( !empty( $result ) ) {
            return array( $record['id_variant'] => $result );
        }

        return $result;
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
     * @return string
     */
    public static function exportCSV( $data ) {

        $roles = new WP_Roles();
        $roles_names = $roles->get_names();

        /**
         * @filters
         * @todo Da documentare
         */
        do_action( 'wpxss_stats_should_export_csv', $data );
        $result = apply_filters( 'wpxss_stats_export_csv', false, $data );
        if( $result ) {
            return $result;
        }

        $columns = array(
            __( 'Order', WPXSMARTSHOP_TEXTDOMAIN ),
            __( 'Product title', WPXSMARTSHOP_TEXTDOMAIN ),
            __( 'Ordered for', WPXSMARTSHOP_TEXTDOMAIN ),
            __( 'EMail & Phone', WPXSMARTSHOP_TEXTDOMAIN ),
            __( 'Ordered by', WPXSMARTSHOP_TEXTDOMAIN ),
            __( 'Note', WPXSMARTSHOP_TEXTDOMAIN ),
            __( 'Variant', WPXSMARTSHOP_TEXTDOMAIN ),
            __( 'Model', WPXSMARTSHOP_TEXTDOMAIN ),
            __( 'Coupon', WPXSMARTSHOP_TEXTDOMAIN ),
            __( 'Price', WPXSMARTSHOP_TEXTDOMAIN ),
            __( 'Price rule', WPXSMARTSHOP_TEXTDOMAIN ),
            __( 'Purchased', WPXSMARTSHOP_TEXTDOMAIN ),
        );
        $columns = apply_filters( 'wpxss_stats_export_columns_csv', $columns );

        /* Crea il CSV */
        $buffer =  '';
        foreach( $data as $item ) {

            /**
             * @filters
             *
             * @param string $localizable_value
             * @param int    $id_product
             * @param string $id_variant
             * @param array  $variant
             * @param string $key
             */
            $model = apply_filters( 'wpss_product_variant_localizable_value', $item['model'] );

            $price_rule_code = $item['price_rule'];
            if ( $price_rule_code == kWPSmartShopProductTypeRuleOnlinePrice ) {
                /**
                 * @filters
                 * @todo Da documentare
                 */
                $price_rule = apply_filters( 'wpxss_stats_column_price_rule_online', __( 'Online', WPXSMARTSHOP_TEXTDOMAIN ), $price_rule_code );
            } elseif ( $price_rule_code == 'base_price' ) {
                /**
                 * @filters
                 * @todo Da documentare
                 */
                $price_rule = apply_filters( 'wpxss_stats_column_price_rule_base_price', __( 'Base price', WPXSMARTSHOP_TEXTDOMAIN ), $price_rule_code );
            } elseif ( $price_rule_code == kWPSmartShopProductTypeRuleDatePrice ) {
                /**
                 * @filters
                 * @todo Da documentare
                 */
                $price_rule = apply_filters( 'wpxss_stats_column_price_rule_date_range', __( 'Date range', WPXSMARTSHOP_TEXTDOMAIN ), $price_rule_code );
            } elseif ( isset( $roles_names[$price_rule_code] ) ) {
                $price_rule = $roles_names[$price_rule_code];
            } else {
                /**
                 * Invia il codice della regola per attività personalizzate esterne
                 *
                 * @filters
                 *
                 * @param string Decsription
                 * @param string ID key ruole
                 *
                 * @todo Da documentare
                 */
                $price_rule = apply_filters( 'wpxss_stats_column_price_rule', __( 'Unknown', WPXSMARTSHOP_TEXTDOMAIN ), $price_rule_code );
            }

            $buffer .= sprintf( '"%s - %s","%s","%s %s","%s %s","%s (%s)","%s","%s","%s","%s","%s","%s","%s"',
                $item['id_order'],
                $item['track_id'],
                $item['product_title'],

                $item['bill_last_name'],
                $item['bill_first_name'],

                $item['bill_email'],
                $item['bill_phone'],

                $item['user_display_name'],
                WPDKUser::roleNameForUserID( $item['id_user']  ),
                //$item['user_order_display_name'],
                //WPDKUser::roleNameForUserID( $item['id_user_order']  ),
                $item['order_note'],
                $item['id_variant'],
                $model,
                $item['coupon_uniqcode'],
                sprintf( '%s %s', WPXSmartShopCurrency::currencySymbol(), WPXSmartShopCurrency::formatCurrency( $item['product_amount'] ) ),
                $price_rule,
                sprintf( '%s %s', WPXSmartShopCurrency::currencySymbol(), WPXSmartShopCurrency::formatCurrency( $item['amount'] ) )
            );
            $buffer .= WPDK_CRLF;
        }

        //Sovrascrive il buffer di default
        $buffer = apply_filters('wpxss_stats_export_buffer_csv', $data);

        $columns_row = sprintf( '"%s"', join( '","', $columns ) ) . WPDK_CRLF;
        $result      = $columns_row . $buffer;

        return $result;
    }

    /**
     * Esegue fisicamente il download del file csv.
     *
     * @static
     *
     */
    public static function downalodCSV() {
       /* Definisco un filename */
        $filename = sprintf( 'wpxSmartShop-Stats-%s.csv', date( 'Y-m-d H:i:s' ) );

        /* Contenuto */
        $buffer = get_transient( 'wpxss_stats_csv' );

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

    /**
     * Questo è il summary alla fine della pagina nel backend.
     *
     * @todo La select con le condizioni di where sono praticamente un duplicato del codice che si trova nella
     *       prepare_items() della list table. Trovare il modo di unificarle altrimenti ogni volta che si modifica una
     *       bisogna modificare anche altra.
     *
     * @static
     * @return string
     */
    static function summary() {
        global $wpdb;

        /* Costruisco la select */
        $where        = 'WHERE 1';

        /* Nomi delle tabelle */
        $table_stats   = WPXSmartShopStats::tableName();
        $table_orders  = WPXSmartShopOrders::tableName();
        $table_coupons = WPXSmartShopCoupons::tableName();

        /* Status */
        if ( isset( $_GET['status'] ) ) {
            if ( $_GET['status'] != 'all' ) {
                $where .= sprintf( " AND stats.`status` = '%s'", esc_attr( $_GET['status'] ) );
            } else {
                $where .= " AND stats.`status` <> 'trash'";
            }
        } else {
            $where .= " AND stats.`status` <> 'trash'";
        }

        /* Variant */
        if ( isset( $_GET['wpss-stats-variant-filter'] ) && !empty( $_GET['wpss-stats-variant-filter'] ) ) {
            $where .= sprintf( ' AND stats.`id_variant` = "%s"', esc_attr( $_GET['wpss-stats-variant-filter'] ) );
        }

        /* Model */
        if ( isset( $_GET['wpss-stats-model-filter'] ) && !empty( $_GET['wpss-stats-model-filter'] ) ) {
            $where .= sprintf( ' AND stats.`model` = "%s"', esc_attr( $_GET['wpss-stats-model-filter'] ) );
        }

        /* User for */
        if ( isset( $_GET['wpxss_stats_filter_id_user_for'] ) && !empty( $_GET['wpxss_stats_filter_id_user_for'] ) ) {
            $where .= sprintf( ' AND orders.`id_user_order` = %s', esc_attr( $_GET['wpxss_stats_filter_id_user_for'] ) );
        }

        /* Product */
        if ( isset( $_GET['wpss-stats-product-filter'] ) && !empty( $_GET['wpss-stats-product-filter'] ) ) {
            $where .= sprintf( ' AND stats.`id_product` = %s', esc_attr( $_GET['wpss-stats-product-filter'] ) );
        }

        /* Date */
        if( isset( $_GET['wpss-stats-datestart-filter'] ) && !empty( $_GET['wpss-stats-datestart-filter'] ) ) {
            $date_start_value = WPDKDateTime::dateTime2MySql( esc_attr( $_GET['wpss-stats-datestart-filter'] ), __( 'm/d/Y H:i', WPXSMARTSHOP_TEXTDOMAIN ) );
        } else {
            $date_start_value = date( MYSQL_DATE_TIME, time() - 60*60*24*7 );
        }

        if( isset( $_GET['wpss-stats-dateend-filter'] ) && !empty( $_GET['wpss-stats-dateend-filter'] ) ) {
            $date_end_value = WPDKDateTime::dateTime2MySql( esc_attr( $_GET['wpss-stats-dateend-filter'] ), __( 'm/d/Y H:i', WPXSMARTSHOP_TEXTDOMAIN ) );
        } else {
            $date_end_value = date( MYSQL_DATE_TIME );
        }

        if( isset( $_GET['wpss-stats-user-role'] ) && !empty( $_GET['wpss-stats-user-role'] ) ) {
            $where .= sprintf( ' AND usermeta.meta_value LIKE \'%%"%s"%%\' ', $_GET['wpss-stats-user-role'] );
        }

        $where .= sprintf( ' AND TIMESTAMP( orders.order_datetime ) BETWEEN "%s" AND "%s" ', $date_start_value, $date_end_value );

        //Apply custom Orders
        $orderby = apply_filters('wpxss_orderby_stats_summary');
        if( $orderby )
            $orderby = " ORDER BY " . $orderby;


        $sql = <<< SQL
SELECT COUNT(*) AS qty,
       SUM( stats.product_amount) AS total_amount,
       stats.*,
       orders.order_datetime AS order_datetime,
       orders.track_id AS track_id,
       orders.id_user,
       orders.id_user_order,
       orders.status,
       coupons.uniqcode AS coupon_uniqcode,
       users.display_name AS user_display_name,
       users_orders.display_name AS user_order_display_name,
       usermeta.meta_value
FROM `{$table_stats}` AS stats
LEFT JOIN `{$table_orders}` AS orders ON orders.id = stats.id_order
LEFT JOIN `{$table_coupons}` AS coupons ON (stats.id_coupon <> 0 AND coupons.id = stats.id_coupon)
LEFT JOIN `{$wpdb->users}` AS users ON orders.id_user = users.ID
LEFT JOIN `{$wpdb->users}` AS users_orders ON orders.id_user_order = users_orders.ID
LEFT JOIN `{$wpdb->usermeta}` AS usermeta ON orders.id_user_order = usermeta.user_id AND usermeta.meta_key = '{$wpdb->prefix}capabilities'
{$where}
GROUP BY stats.product_title, stats.id_variant, stats.model
{$orderby}
SQL;

        $data = $wpdb->get_results( $sql, ARRAY_A );

        $total_amount = 0;
        $body         = '';
        foreach ( $data as $item ) {
            $claass_alternate = empty( $claass_alternate ) ? 'class="alternate"' : '';

            /**
             * @filters
             *
             * @param string $localizable_value
             * @param int    $id_product
             * @param string $id_variant
             * @param array  $variant
             * @param string $key
             */
            $model = apply_filters( 'wpss_product_variant_localizable_value', $item['model'] );



            $body .= sprintf( '<tr %s><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td style="text-align: right">%s</td><td>%s</td></tr>',
                $claass_alternate,
                $item['qty'],
                $item['product_title'],
                $item['id_variant'],
                $model,
                $item['total_amount'],
                $item['status']
            );

            $total_amount += $item['total_amount'];
        }

        $footer = sprintf( '<th style="text-align: right;font-weight: bold" colspan="5">%s</th>', WPXSmartShopCurrency::formatCurrency( $total_amount ) );

        $labels              = new stdClass();
        $labels->title       = __( 'Summary', WPXSMARTSHOP_TEXTDOMAIN );
        $labels->qty         = __( 'Qty', WPXSMARTSHOP_TEXTDOMAIN );
        $labels->description = __( 'Description', WPXSMARTSHOP_TEXTDOMAIN );
        $labels->variant     = __( 'Variant', WPXSMARTSHOP_TEXTDOMAIN );
        $labels->model       = __( 'Model', WPXSMARTSHOP_TEXTDOMAIN );
        $labels->total       = __( 'Total', WPXSMARTSHOP_TEXTDOMAIN );
        $labels->status       = __( 'Status', WPXSMARTSHOP_TEXTDOMAIN );

        $html = <<< HTML
<h2 style="text-align: center">{$labels->title}</h2>
<table class="wp-list-table widefat fixed summary" border="0" cellpadding="0" cellspacing="0">
    <thead>
        <tr>
            <th>{$labels->qty}</th>
            <th>{$labels->description}</th>
            <th>{$labels->variant}</th>
            <th>{$labels->model}</th>
            <th style="text-align: right">{$labels->total}</th>
            <th>{$labels->status}</th>
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

    // -----------------------------------------------------------------------------------------------------------------
    // UI - Filters
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Costruisce il menu a tendina per la selezione del filtro sul ruolo utente
     * @static
     *
     * @param string $id_select
     * @param string $selected
     *
     * @return string
     */
    public static function selectFilterUserRole( $id_select, $selected = '' ) {

        $roles = WPDKUser::allRoles();

        $options = '';
        foreach ( $roles as $key => $role ) {
            if ( !empty( $role ) ) {
                $options .= sprintf( '<option %s value="%s">%s</option>', selected( $key, $selected, false ), $key, $role );
            }
        }

        $label = __( 'Filter for User Role', WPXSMARTSHOP_TEXTDOMAIN );

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
     * @param string $id_select
     * @param string $selected
     *
     * @return string
     */
    public static function selectFilterVariant( $id_select, $selected = '' ) {
        global $wpdb;

        $table_stats   = self::tableName();

        /* Seleziono i prodotti in group by */
        $sql   = <<< SQL
SELECT COUNT( stats.id_variant) AS count, stats.id_variant
FROM `{$table_stats}` AS stats
GROUP BY stats.id_variant
ORDER BY stats.id_variant
SQL;
        $results = $wpdb->get_results( $sql );

        $options = '';
        foreach ( $results as $item ) {
            if ( !empty( $item->id_variant ) ) {
                $options .= sprintf( '<option %s value="%s">%s (%s)</option>', selected( $item->id_variant, $selected, false ),$item->id_variant, $item->id_variant, $item->count );
            }
        }

        $label = __( 'Filter for Variant', WPXSMARTSHOP_TEXTDOMAIN );

        $html = <<< HTML
        <select class="wpdk-form-select" name="{$id_select}" id="{$id_select}">
            <option value ="">{$label}</option>
            {$options}
        </select>
HTML;
        return $html;
    }

    /**
     * Costruisce il menu a tendina per la selezione del filtro sul modello
     *
     * @static
     *
     * @param string $id_select
     * @param string $selected
     *
     * @return string
     */
    public static function selectFilterModel( $id_select, $selected = '' ) {
        global $wpdb;

        $table_stats   = self::tableName();

        /* Seleziono i prodotti in group by */
        $sql   = <<< SQL
SELECT COUNT( stats.model) AS count, stats.model
FROM `{$table_stats}` AS stats
GROUP BY stats.model
ORDER BY stats.model
SQL;
        $results = $wpdb->get_results( $sql );

        $options = '';
        foreach ( $results as $item ) {
            if ( !empty( $item->model ) ) {
                /**
                 * @filters
                 *
                 * @param string $localizable_value
                 * @param int    $id_product
                 * @param string $id_variant
                 * @param array  $variant
                 * @param string $key
                 */
                $localizable_value = apply_filters( 'wpss_product_variant_localizable_value', $item->model, $id_product, $id_variant, array(), $item->model );

                $options .= sprintf( '<option %s value="%s">%s (%s)</option>', selected( $item->model, $selected, false ), $item->model, $localizable_value, $item->count );
            }
        }

        $label = __( 'Filter for Model', WPXSMARTSHOP_TEXTDOMAIN );

        $html = <<< HTML
        <select class="wpdk-form-select" name="{$id_select}" id="{$id_select}">
            <option value ="">{$label}</option>
            {$options}
        </select>
HTML;
        return $html;
    }

    /**
     * Costruisce il menu a tendina per la selezione del filtro sul prodotto
     *
     * @static
     *
     * @param string $id_select
     * @param string $selected
     *
     * @return string
     */
    public static function selectFilterProduct( $id_select, $selected = '' ) {
        global $wpdb;

        $table_stats = self::tableName();

        /* Seleziono i prodotti in group by */
        $sql   = <<< SQL
SELECT COUNT( stats.id_product) AS count, stats.id_product, stats.product_title
FROM `{$table_stats}` AS stats
GROUP BY stats.id_product
ORDER BY stats.product_title
SQL;
        $results = $wpdb->get_results( $sql );

        $options = '';
        foreach ( $results as $item ) {
            if ( !empty( $item->product_title ) ) {
                $options .= sprintf( '<option %s value="%s">%s (%s)</option>', selected( $item->id_product, $selected, false ),$item->id_product, $item->product_title, $item->count );
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

}