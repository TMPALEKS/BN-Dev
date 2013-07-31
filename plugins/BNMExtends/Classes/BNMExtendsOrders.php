<?php
/**
 * Gestisce Estensioni Ordini
 *
 * @package            BNMExtends
 * @subpackage         BNMExtendsOrders
 * @author             =undo= <g.fazioli@undolog.com>, <g.fazioli@saidmade.com>
 * @copyright          Copyright (C)2011 Saidmade Srl.
 * @created            18/07/12
 * @version            1.0
 *
 */

class BNMExtendsOrders {

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
     * @todo
     *
     * @param $fields
     */
    public static function addCutomFieldsValues( $fields ) {
        //var_dump( $fields );
        //die();
    }

    /**
     *
     * Trasforma un oggetto pendings in Values per l'update
     *
     * @param array $pendings
     * @return mixed
     */
    public static function getValues( $pendings = array() ) {

        if ( is_object( $pendings ) )
            $pendings = get_object_vars( $pendings );

        $values["order_datetime"] = $pendings["order_datetime"];
        $values["subtotal"] = $pendings["subtotal"];
        $values["tax"] = $pendings["tax"];
        $values["total"] = $pendings["total"];
        $values["bill_first_name"] = $pendings["bill_first_name"];
        $values["bill_last_name"] = $pendings["bill_last_name"];
        $values["bill_country"] = $pendings["bill_country"];
        $values["bill_town"] = $pendings["bill_town"];
        $values["bill_zipcode"] = $pendings["bill_zipcode"];
        $values["bill_email"] = $pendings["bill_email"];
        $values["bill_phone"] = $pendings["bill_phone"];
        $values["status"] = $pendings["status"];

        return $values;
    }

    /**
     * Innesca il Cron Job ogni $fewseconds
     */
    public static function closePendingOrders( $order_id ) {

        $fewSeconds = time() + 2 * 60; // (30 * 24 * 60 * 60)
        $args = array('order_id' => $order_id );
        $timestamp = wp_next_scheduled( 'bnmextends_orders_status' );

        # wp_schedule_single_event($fewSeconds, 'bnmextends_orders_status', $args);
        WPDKWatchDog::watchDog( __METHOD__, "***** START CRONJOB " . $timestamp . " *****");

        if( !wp_next_scheduled( 'bnmextends_orders_status' )){
            wp_schedule_event(time(), 'in_per_15_minutes', 'bnmextends_orders_status', $args);
            WPDKWatchDog::watchDog( __METHOD__, "Start processo Cronjob per ordine " . $order_id);
        }

    }

    /**
     * @return array
     * Set di intervalli custom
     */
    public static function cornSchedules(){
        return array(
            'in_per_minute' => array(
                'interval' => 60,
                'display' => 'In every Mintue'
            ),
            'in_per_15_minutes' => array(
                'interval' => 60 * 15,
                'display' => 'In every 15 mins'
            ),
            'in_per_some_minutes' => array(
                'interval' => 60 * 30,
                'display' => 'In every 30 mins'
            ),
            'three_hourly' => array(
                'interval' => 60 * 60 * 3,
                'display' => 'Once in Three hours'
            )
        );
    }

    /**
     * @param $id
     * @param string $updateTo
     *
     * Aggiorna lo stato dell' ordine se necessario
     */
    public static function updateOrderWithStatus( $id, $updateTo = WPXSMARTSHOP_ORDER_STATUS_DEFUNCT ){
        $diff_base = 15; //Minuti per la scadenza della sessione

        $pending = WPXSmartShopOrders::order( $id );
        $order = self::getValues($pending);

        $now = strtotime("now");

        if ( $order && $order['status'] == WPXSMARTSHOP_COUPON_STATUS_PENDING ){

            WPDKWatchDog::watchDog( __METHOD__, "****** Rimuovo dalla coda il processo cronjob ancora PENDING per Ordine: " . $id . " ******");


            $from_time = strtotime($order['order_datetime']);

            $diff = round(abs($now - $from_time) / 60,2); //Valuto la differenza con la data di inserimento ordine (in minuti)
            WPDKWatchDog::watchDog( __METHOD__, "Durata della sessione di transazione: " . $diff);



            if( $diff >= $diff_base){ //Se la differnza è superiore a 30 (minuti) aggiorno lo stato - Sessione Scaduta
                $order['status'] = $updateTo;

                WPDKWatchDog::watchDog(__CLASS__ . __METHOD__, "Aggiorno a DEFUNCT lo stato dell'ordine: " . $id);

                WPXSmartShopOrders::update( $id, $order );

                //Rimuovo il processo che aveva generato il controllo
                $args = array('order_id' => $id );
                $timestamp = wp_next_scheduled( 'bnmextends_orders_status', $args );
                WPDKWatchDog::watchDog( __METHOD__, "Rimuovo dalla coda il processo cronjob " . $timestamp);

                wp_unschedule_event( $timestamp, 'bnmextends_orders_status', $args );

            }
            WPDKWatchDog::watchDog( __METHOD__, "***** Fine rimozione processi PENDING per Ordine:  " . $id . " *****");


        }
        else{
            WPDKWatchDog::watchDog(__METHOD__, "***** Rimuovo dalla coda eventuali processi andati a buon fine *****");

            //Controllo che non siano rimasti appesi ordini NON PENDING
            $args = array('order_id' => $id );
            $timestamp = wp_next_scheduled( 'bnmextends_orders_status', $args );
            if( $timestamp ){
                wp_unschedule_event( $timestamp, 'bnmextends_orders_status', $args );
                WPDKWatchDog::watchDog( __METHOD__, "Rimuovo dalla coda il processo cronjob andato a buon fine" . $timestamp);

            }
            WPDKWatchDog::watchDog(__METHOD__, "***** Fine rimozione processi andati a buon fine *****");

        }


    }

    /**
     *
     * Aggiorna lo stato dell' evento al valore definito dalle costanti globali
     * @param $updateTo
     */
    public static function updateOrderStatus( $updateTo = WPXSMARTSHOP_ORDER_STATUS_DEFUNCT ) {

        $diff_base = 30; //Minuti per la scadenza della sessione
        $pendings = self::ordersWithStatus(); //query sui prodotti pendings

        $now = strtotime("now");

        foreach ($pendings as $pending) {
            $from_time = strtotime($pending['order_datetime']);

            $diff = round(abs($now - $from_time) / 60,2); //Valuto la differenza con la data di inserimento ordine (in minuti)

            //@Todo Check this!


            if( $diff >= $diff_base){ //Se la differnza è superiore a 30 (minuti) aggiorno lo stato - Sessione Scaduta
                $pending['status'] = "defunct";
                WPXSmartShopOrders::update( $pending['id'], self::getValues($pending) );
            }
            else {
                continue;
            } //altrimenti salto l'ordine

        }

    }


    /**
     * @param $order_id
     * @param $note
     * @return mixed
     *
     * Aggiorna le note di un ordine
     */
    public static function updateOrderNotes($order_id, $note){
        return WPXSmartShopOrders::update($order_id, array('note' => $note));
    }


    /**
     *
     */
    public static function fixBoxOfficePlacehoder(){
        echo "<div id='wpss-tables-memory-fields'></div>";
    }

    /**
     * Restituisce la lista di ordini per un dato status
     *
     * @static
     *
     * @param string $status  Stato dell'ordine, default WPXSMARTSHOP_ORDER_STATUS_PENDING
     * @param string $output  Tipo di restituzione: oggetti o array
     *
     * @retval array Elenco ordini
     */
    private static function ordersWithStatus( $status = WPXSMARTSHOP_ORDER_STATUS_PENDING, $output = ARRAY_A ) {
        global $wpdb;

        $table = WPXSmartShopOrders::tableName();

        $sql  = <<< SQL
        SELECT * FROM `{$table}`
        WHERE `status` = '{$status}'
SQL;
        $rows = $wpdb->get_results( $sql, $output );

        return $rows;
    }


}

?>