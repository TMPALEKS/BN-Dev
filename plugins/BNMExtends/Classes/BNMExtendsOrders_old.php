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
     * Innesca il Cron Job ogni ora
     */
    public static function closePendingOrders( $order_id ) {

        $fewSeconds = time() + 2 * 60; // (30 * 24 * 60 * 60)
        $args = array('order_id' => $order_id );

        wp_schedule_single_event($fewSeconds, 'bnmextends_orders_status', $args);
    }

    public static function updateOrderWithStatus( $id, $updateTo = WPXSMARTSHOP_ORDER_STATUS_DEFUNCT ){
        $pending = WPXSmartShopOrders::order( $id );
        $order = self::getValues($pending);
        if ( $order && $order['status'] == WPXSMARTSHOP_COUPON_STATUS_PENDING ){
            $order['status'] = $updateTo;
            WPXSmartShopOrders::update( $id, $order );
        }
    }

    /**
     *
     * Aggiorna lo stato dell' evento al valore definito dalle costanti globali
     * @param $updateTo
     */
    public static function updateOrderStatus( $updateTo = WPXSMARTSHOP_ORDER_STATUS_DEFUNCT ) {

        $diff_base = 30;
        $pendings = self::ordersWithStatus();

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