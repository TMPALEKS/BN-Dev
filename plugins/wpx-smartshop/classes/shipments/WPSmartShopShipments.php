<?php
/**
 * Shipments Manager
 *
 * @package            wpx SmartShop
 * @subpackage         WPSmartShopShipments
 * @author             =undo= <g.fazioli@undolog.com>, <g.fazioli@wpxtre.me>
 * @copyright          Copyright (c) 2012 wpXtreme, Inc.
 * @link               http://wpxtre.me
 * @created            06/02/12
 * @version            1.0.0
 *
 */

require_once ( 'WPSmartShopShipmentsViewController.php' );

class WPSmartShopShipments  {


    // -----------------------------------------------------------------------------------------------------------------
    // CRUD (Create, Read, Update & Delete)
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Read a specify record o records from database
     *
     * @package            WPDK (WordPress Development Kit)
     *
     * @since              1.0.0
     *
     * @static
     *
     * @param int    $id
     *  ID del record o null per tutti
     *
     * @param string $id_id
     *  Nome del campo ID
     *
     * @param string $orderby
     *  Stringa ordinamento SQL, default '', Es. ORDER BY 'name'
     *
     * @param string $where
     *  Stringa WHERE in SQL, Es. AND status = 'trash'
     *
     * @param mixed  $output
     *  Tipo di output
     *
     * @retval mixed
     *                     Riga o elenco nel formato $output
     */
    public static function read( $id = null, $id_id = 'id', $orderby = '', $where = '', $output = OBJECT ) {
        global $wpdb;

        $table = self::tableName();

        $where_cond = 'WHERE 1';

        if ( !is_null( $id ) ) {
            if ( is_numeric( $id ) ) {
                $where_cond = sprintf( 'WHERE %s = %s', $id_id, $id );
            } elseif ( is_string( $id ) ) {
                $where_cond = sprintf( 'WHERE %s = \'%s\'', $id_id, $id );
            }
        }

        if ( !empty( $where ) ) {
            $where_cond = sprintf( '%s %s', $where_cond, $where );
        }

        $sql = <<< SQL
        SELECT * FROM `{$table}`
        {$where_cond}
        {$orderby}
SQL;
        if ( !is_null( $id ) ) {
            $result = $wpdb->get_row( $sql, $output );
        } else {
            $result = $wpdb->get_results( $sql, $output );
        }
        return $result;
    }

    /**
     * Delete one or more records from database.
     * Elimina anche i relativi post meta
     *
     * @package            WPDK (WordPress Development Kit)
     *
     * @since              1.0.0
     *
     * @static
     *
     * @param int|array $ids
     * ID o array di ID da eliminare
     *
     * @param string    $id_id
     * Nome del campo ID
     *
     * @param bool      $delete_post_meta
     * Elimina la chiave '_[table name]_status dai post meta. Questa viene usata per memorizzare lo stato precedente
     * di un record, quando si ha una gestine a stati appunto: vedi 'trash' ad esempio. Vedi meotdo update per dettagli
     *
     * @retval mixed
     *                     Risultato della $wpdb->query()
     */
    public static function delete( $ids, $id_id = 'id', $delete_post_meta = true ) {
        global $wpdb;

        $table    = self::tableName();

        if ( !is_array( $ids ) ) {
            $ids = array( $ids );
        }

        if ( $delete_post_meta ) {
            $meta_key = sprintf( '_%s_status', $table );
            foreach ( $ids as $id ) {
                delete_post_meta( $id, $meta_key );
            }
        }

        $ids = implode( ',', $ids );

        $sql    = <<< SQL
		DELETE FROM `{$table}`
		WHERE {$id_id} IN({$ids})
SQL;
        $result = $wpdb->query( $sql );

        return $result;
    }

    // -----------------------------------------------------------------------------------------------------------------
    // Extra
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Restituisce il numero dei record di una tabella
     *
     * @package     WPDK (WordPress Development Kit)
     * @since       1.0.0
     *
     * @static
     * @retval int
     *              Restituisce il numero totale dei record
     */
    public static function count() {
        global $wpdb;

        $table = self::tableName();

        $sql = <<< SQL
		SELECT COUNT(*) AS count
		FROM `{$table}`
SQL;
        return absint( $wpdb->get_var( $sql ) );
    }

    // -----------------------------------------------------------------------------------------------------------------
    // Extra for trash like WordPress
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Imposta lo stato di un record a 'trash' e memorizza lo stato attuale nella post meta con chiave
     * '_[table name]_status'
     *
     * @package            WPDK (WordPress Development Kit)
     *
     * @since              1.0.0
     *
     * @static
     *
     * @param int|array $ids
     * ID o Array di ID del record
     *
     * @param string    $id_id
     * ome del campo ID
     *
     * @param string    $status
     * Nome del campo status, default 'post_status'
     *
     * @param string    $value
     * Valore dello stati, default 'trash'
     *
     * @retval mixed
     *                     Risultato della $wpdb->query()
     */
    public static function trash( $ids, $id_id = 'id', $status = 'post_status', $value = 'trash' ) {
        global $wpdb;

        $table    = self::tableName();
        $meta_key = sprintf( '_%s_status', $table );

        if ( !is_array( $ids ) ) {
            $ids = array( $ids );
        }

        /* Memorizzo lo stato precendete nella tabella options */
        foreach ( $ids as $id ) {
            $previous_status = self::status( $id, $id_id, $status );
            update_post_meta( $id, $meta_key, $previous_status );
        }

        $ids = implode( ',', $ids );

        $sql = <<< SQL
        UPDATE `{$table}`
        SET `{$status}` = '{$value}'
        WHERE {$id_id} IN({$ids})
SQL;

        $result = $wpdb->query( $sql );
        return $result;

    }

    /**
     * Repristina un record dal cestino recuperando lo stato precedente dalla chiave ''_[table name]_status'
     * nella post meta. Se non la trova pone il record in status 'unknown'
     *
     * @package            WPDK (WordPress Development Kit)
     *
     * @since              1.0.0
     *
     * @static
     *
     * @param int|array $ids
     * ID o array di ID del record da repristinare
     *
     * @param string    $id_id
     * Nome del campo ID
     *
     * @param string    $status
     * Nome del campo status
     *
     * @retval mixed
     *                     Risultato della $wpdb->query() o false se errore
     */
    public static function untrash( $ids, $id_id = 'id', $status = 'post_status' ) {
        global $wpdb;

        $table    = self::tableName();
        $meta_key = sprintf( '_%s_status', $table );
        $result   = false;

        if ( !is_array( $ids ) ) {
            $ids = array( $ids );
        }

        foreach ( $ids as $id ) {
            $previous_status = get_post_meta( $id, $meta_key, true );
            if ( empty( $previous_status ) ) {
                /* @todo Prendere il primo disponibile in base alla classe ereditaria */
                $previous_status = 'unknown';
            }
            $sql    = <<< SQL
            UPDATE `{$table}`
            SET `{$status}` = '{$previous_status}'
            WHERE {$id_id} = {$id}
SQL;
            $result = $wpdb->query( $sql );

            delete_post_meta( $id, $meta_key );
        }

        return $result;

    }

    /**
     * Legge lo stato attuale di un record
     *
     * @package            WPDK (WordPress Development Kit)
     *
     * @since              1.0.0
     *
     * @static
     *
     * @param int|array $id
     * ID del record da repristinare
     *
     * @param string    $id_id
     * Nome del campo ID
     *
     * @param string    $status
     * Nome del campo status
     *
     * @retval string
     *                     Restituisce la stringa che identifica lo stato, ritorno della $wpdb->get_var(()
     */
    private static function status( $id, $id_id = 'id', $status = 'post_status' ) {
        global $wpdb;

        $table = self::tableName();

        $sql    = <<< SQL
        SELECT `{$status}`
        FROM `{$table}`
        WHERE `{$id_id}` = $id
SQL;
        $status = $wpdb->get_var( $sql );

        return $status;
    }

    // -----------------------------------------------------------------------------------------------------------------
    // WordPress WP List Table
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Restituisce un array con il tipo di status, la sua label e la count sul database
     *
     * @package            WPDK (WordPress Development Kit)
     *
     * @since              1.0.0
     *
     * @static
     * @retval array
     * Restituisce un array con il tipo di status, la sua label e la count sul database
     */
    public static function statusesWithCount() {
        global $wpdb;

        $statuses = self::arrayStatuses();
        $table    = self::tableName();

        $sql    = <<< SQL
        SELECT DISTINCT(`status`),
               COUNT(*) AS count
        FROM `{$table}` GROUP BY `status`
SQL;
        $result = $wpdb->get_results( $sql, ARRAY_A );

        foreach ( $result as $status ) {
            if ( !empty( $status['status'] ) ) {
                $statuses[$status['status']]['count'] = $status['count'];
            }
        }

        $statuses['all']['count'] = self::count();

        return $statuses;
    }

    // -----------------------------------------------------------------------------------------------------------------
    // Static values
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Restituisce il nome della tabella delle spedizioni
     *
     * @package    wpx SmartShop
     * @subpackage WPSmartShopShipments
     * @since      1.0.0
     *
     * @static
     *
     * @param null $size
     *
     * @retval string
     */
    public static function tableName( $size = null ) {
        global $wpdb;
        if ( is_null( $size ) ) {
            return sprintf( '%s%s', $wpdb->prefix, WPXSMARTSHOP_DB_TABLENAME_SHIPMENTS );
        } else {
            return sprintf( '%s%s', $wpdb->prefix, WPXSMARTSHOP_DB_TABLENAME_SIZE_SHIPMENTS );
        }
    }

    /**
     * Modulo nello standard SFD per l'inserimento ed edit
     *
     * @package    wpx SmartShop
     * @subpackage WPSmartShopShipments
     * @since      1.0.0
     *
     * @static
     * @param null $id
     * @retval array
     */
    public static function fields( $id = null ) {
        if ( !is_null( $id ) ) {
            $shipment = self::shipment( $id );
        }
        $fields = array(
            __( 'Shipments Range', WPXSMARTSHOP_TEXTDOMAIN )   => array(

                __( 'Weight range', WPXSMARTSHOP_TEXTDOMAIN ),
                array(
                    array(
                        'type'   => WPDK_FORM_FIELD_TYPE_NUMBER,
                        'name'   => 'weight_from',
                        'label'  => __( 'Weight from', WPXSMARTSHOP_TEXTDOMAIN ),
                        'value'  => is_null( $shipment ) ? '' : $shipment->weight_from,
                        'append' => WPXSmartShopMeasures::weightSymbol()
                    ),
                    array(
                        'type'  => WPDK_FORM_FIELD_TYPE_NUMBER,
                        'name'  => 'weight_to',
                        'label' => __('Weight to', WPXSMARTSHOP_TEXTDOMAIN ),
                        'value' => is_null($shipment) ? '' : $shipment->weight_to,
                        'append' => WPXSmartShopMeasures::weightSymbol()
                    )
                ),

                __( 'Size range', WPXSMARTSHOP_TEXTDOMAIN ),
                array(
                    array(
                        'type'   => WPDK_FORM_FIELD_TYPE_NUMBER,
                        'name'   => 'width_from',
                        'label'  => __( 'Width from', WPXSMARTSHOP_TEXTDOMAIN ),
                        'value'  => is_null( $shipment ) ? '' : $shipment->width_from,
                        'append' => WPXSmartShopMeasures::sizeSymbol()
                    ),
                    array(
                        'type'   => WPDK_FORM_FIELD_TYPE_NUMBER,
                        'name'   => 'width_to',
                        'label'  => __( 'Width to', WPXSMARTSHOP_TEXTDOMAIN ),
                        'value'  => is_null( $shipment ) ? '' : $shipment->width_to,
                        'append' => WPXSmartShopMeasures::sizeSymbol()
                    )
                ),
                array(
                    array(
                        'type'   => WPDK_FORM_FIELD_TYPE_NUMBER,
                        'name'   => 'height_from',
                        'label'  => __( 'Height from', WPXSMARTSHOP_TEXTDOMAIN ),
                        'value'  => is_null( $shipment ) ? '' : $shipment->height_from,
                        'append' => WPXSmartShopMeasures::sizeSymbol()
                    ),
                    array(
                        'type'   => WPDK_FORM_FIELD_TYPE_NUMBER,
                        'name'   => 'height_to',
                        'label'  => __( 'Height to', WPXSMARTSHOP_TEXTDOMAIN ),
                        'value'  => is_null( $shipment ) ? '' : $shipment->height_to,
                        'append' => WPXSmartShopMeasures::sizeSymbol()
                    )
                ),
                array(
                    array(
                        'type'   => WPDK_FORM_FIELD_TYPE_NUMBER,
                        'name'   => 'depth_from',
                        'label'  => __( 'Depth from', WPXSMARTSHOP_TEXTDOMAIN ),
                        'value'  => is_null( $shipment ) ? '' : $shipment->depth_from,
                        'append' => WPXSmartShopMeasures::sizeSymbol()
                    ),
                    array(
                        'type'   => WPDK_FORM_FIELD_TYPE_NUMBER,
                        'name'   => 'depth_to',
                        'label'  => __( 'Depth to', WPXSMARTSHOP_TEXTDOMAIN ),
                        'value'  => is_null( $shipment ) ? '' : $shipment->depth_to,
                        'append' => WPXSmartShopMeasures::sizeSymbol()
                    ),
                ),

                __( 'Volume', WPXSMARTSHOP_TEXTDOMAIN ),
                array(
                    array(
                        'type'   => WPDK_FORM_FIELD_TYPE_NUMBER,
                        'name'   => 'volume',
                        'label'  => __( 'Volume', WPXSMARTSHOP_TEXTDOMAIN ),
                        'value'  => is_null( $shipment ) ? '' : $shipment->volume,
                        'append' => WPXSmartShopMeasures::volumeSymbol()
                    ),
                ),
            ),

            __( 'Shipements Rules', WPXSMARTSHOP_TEXTDOMAIN )   => array(
                __( 'Enter price for Carrier and Zone', WPXSMARTSHOP_TEXTDOMAIN ),
                array(
                    'type'     => WPDK_FORM_FIELD_TYPE_CUSTOM,
                    'callback' => array( __CLASS__, 'grid'),
                    'userdata' => $shipment
                )
            )
        );

        /* Aggiungo un campo hidden per tenermi in update lo id_size_shipment */
        if ( !is_null( $id ) ) {
            $fields[key( $fields )][1][] = array(
                'type'  => WPDK_FORM_FIELD_TYPE_HIDDEN,
                'name'  => 'id_size_shipment',
                'value' => $shipment->id_size_shipment
            );
        }

        return $fields;
    }

    /**
     * Restituisce l'elenco degli stati
     *
     * @static
     * @retval array
     */
    public static function arrayStatuses() {

        $statuses = array(
            'all'       => array(
                'label' => __( 'All', WPXSMARTSHOP_TEXTDOMAIN ),
                'count' => 0
            ),
            'publish'     => array(
                'label' => __( 'Publish', WPXSMARTSHOP_TEXTDOMAIN ),
                'count' => 0
            ),
            'trash'     => array(
                'label' => __( 'Trash', WPXSMARTSHOP_TEXTDOMAIN ),
                'count' => 0
            )
        );
        return $statuses;
    }

    public static function arrayStatusesForSDF() {
        $statuses = self::arrayStatuses();
        foreach ( $statuses as $key => $status ) {
            $result[$key] = $status['label'];
        }
        unset( $result['all'] );
        unset( $result['trash'] );
        return $result;
    }

    // -----------------------------------------------------------------------------------------------------------------
    // UI Aux
    // -----------------------------------------------------------------------------------------------------------------

    public static function grid( $shipment ) {
        $zones    = WPSmartShopShippingCountries::zonesArray();
        $carriers = WPXSmartShopCarriers::arrayCarriersForSDF();

        if ( isset( $shipment['userdata'] ) ) {
            $matrix = self::rulesForSize( $shipment['userdata']->id_size_shipment );
        } ?>

    <table class="wpss-shipment-carriers-zones" border="0" cellpadding="0" cellspacing="0">
        <thead>
        <tr>
            <th></th>
            <?php foreach ( $zones as $zone ) : ?>
            <th><?php echo $zone ?></th>
            <?php endforeach; ?>
        </tr>
        </thead>

        <tbody>
            <?php foreach ( $carriers as $key => $carrier ) : ?>
        <tr>
            <td><?php echo $carrier ?></td>
            <?php foreach ( $zones as $zone ) : $key_matrix = $key . '-' . $zone; ?>

            <td><input class="wpdk-form-input wpdk-form-number"
                       name="price-<?php echo $key ?>-<?php echo $zone ?>"
                       size="8"
                       value="<?php echo isset($matrix[$key_matrix]) ? $matrix[$key_matrix] : '' ?>"/>
                <span><?php echo WPXSmartShopCurrency::currencySymbol() ?></span>
            </td>
            <?php endforeach; ?>
        </tr>
            <?php endforeach; ?>
        </tbody>

    </table>
    <?php
    }

    // -----------------------------------------------------------------------------------------------------------------
    // Database
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Crea una matrice virtuale basata sulle key per costruire la griglia con corrieri, zone e campi input
     *
     * @static
     * @param $id_size_shipment
     * @retval array
     */
    public static function rulesForSize( $id_size_shipment ) {
        global $wpdb;

        $table = self::tableName();

        $sql    = <<< SQL
        SELECT shipments.zone,
               shipments.price,
               shipments.id_carrier
        FROM {$table} AS shipments
        WHERE shipments.id_size_shipment = {$id_size_shipment}
        AND shipments.`status` = 'publish'
        ORDER BY shipments.zone, shipments.price
SQL;
        $result = $wpdb->get_results( $sql );

        /* Costruisco una matrice */
        $matrix = array();
        foreach($result as $row) {
            $key = $row->id_carrier . '-' .$row->zone;
            $matrix[$key] = $row->price;
        }

        return $matrix;

    }

    /**
     * Crea o aggiorna (esegue un delta) la tabella delle spedizioni
     *
     * @package    wpx SmartShop
     * @subpackage WPSmartShopShipments
     * @since      1.0.0
     *
     * @static
     *
     */
    public static function updateTable() {
        if ( !function_exists( 'dbDelta' ) ) {
            require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        }
        $dbDeltaTableFile = sprintf( '%s%s', WPXSMARTSHOP_PATH_DATABASE, kWPSmartShopShipmentsTableFilename );
        $file             = file_get_contents( $dbDeltaTableFile );
        $sql              = sprintf( $file, self::tableName() );
        @dbDelta( $sql );

        $dbDeltaTableFile = sprintf( '%s%s', WPXSMARTSHOP_PATH_DATABASE, kWPSmartShopSizeShipmentsTableFilename );
        $file             = file_get_contents( $dbDeltaTableFile );
        $sql              = sprintf( $file, self::tableName( true ) );
        @dbDelta( $sql );
    }

    /**
     * Restituisce le informazioni su una spedizione eseguendo una left join anche sulla tabella delle size
     *
     * @package    wpx SmartShop
     * @subpackage WPSmartShopShipments
     * @since      1.0.0
     *
     * @static
     * @param $id
     * @param string $output
     * @retval mixed
     */
    public static function shipment($id, $output = OBJECT) {
        global $wpdb;

        $table     = self::tableName();
        $tableSize = self::tableName( true );

        $sql = <<< SQL
        SELECT size_shipments.*,
               shipments.price,
               shipments.id_carrier,
               shipments.id_size_shipment
        FROM `{$table}` AS shipments
        LEFT JOIN `{$tableSize}` AS size_shipments ON size_shipments.id = shipments.id_size_shipment
        WHERE shipments.id = {$id}
        AND shipments.`status` = 'publish'
SQL;

        $row = $wpdb->get_row($sql, $output);

        return $row;
    }

    /**
     * Aggiunge una spedizione
     *
     * @package    wpx SmartShop
     * @subpackage WPSmartShopShipments
     * @since      1.0.0
     *
     * @static
     *
     */
    public static function create() {
        global $wpdb;

        /* Inserisco una nuova combinazione di size */
        /* @todo Qui andrebbero controllati eventuali duplicati - da studiarsela */
        $values = array(
            'weight_from'  => $_POST['weight_from'],
            'weight_to'    => $_POST['weight_to'],
            'width_from'   => $_POST['width_from'],
            'width_to'     => $_POST['width_to'],
            'height_from'  => $_POST['height_from'],
            'height_to'    => $_POST['height_to'],
            'depth_from'   => $_POST['depth_from'],
            'depth_to'     => $_POST['depth_to'],
            'volume'       => $_POST['volume'],
        );
        $result = $wpdb->insert( self::tableName( true ), $values );
        $id_size_shipment = $wpdb->insert_id;

        /* Inserisco regole */
        $carriers = WPXSmartShopCarriers::carriersArray();
        $zones    = WPSmartShopShippingCountries::zonesArray();
        foreach ( $carriers as $id_carrier => $carrier ) {
            foreach ( $zones as $zone ) {
                $key = 'price-' . $id_carrier . '-' . $zone;

                /* Se è vuoto o zero il valore dell'imput, elimino riga */
                if ( !empty( $_POST[$key] ) ) {

                    /* Provo ad aggiornare, se non ci riesco inserisco */
                    self::updatePrice( $_POST[$key], $id_carrier, $zone, $id_size_shipment );
                }
            }
        }
    }


    /**
     * Aggiorna una spedizione
     *
     * @package    wpx SmartShop
     * @subpackage WPSmartShopShipments
     * @since      1.0.0
     *
     * @static
     * @retval mixed
     */
    public static function update() {
        global $wpdb;

        /* Aggiorno i dati delle size */
        if ( isset( $_POST['id_size_shipment'] ) ) {
            $values = array(
                'weight_from'  => $_POST['weight_from'],
                'weight_to'    => $_POST['weight_to'],
                'width_from'   => $_POST['width_from'],
                'width_to'     => $_POST['width_to'],
                'height_from'  => $_POST['height_from'],
                'height_to'    => $_POST['height_to'],
                'depth_from'   => $_POST['depth_from'],
                'depth_to'     => $_POST['depth_to'],
                'volume'       => $_POST['volume'],
            );
            $where  = array( 'id' => absint( $_POST['id_size_shipment'] ) );
            $result = $wpdb->update( self::tableName( true ), $values, $where );
        }

        /* Per ogni corriere, aggiorno/cancello o inserisco il prezzo di zona */
        $carriers = WPXSmartShopCarriers::carriersArray();
        $zones    = WPSmartShopShippingCountries::zonesArray();
        foreach ( $carriers as $id_carrier => $carrier ) {
            foreach ( $zones as $zone ) {
                $key = 'price-' . $id_carrier . '-' . $zone;

                /* Se è vuoto o zero il valore dell'imput, elimino riga */
                if ( empty( $_POST[$key] ) ) {

                    /* Questa potrebbe fallire */
                    self::deleteForCarrierAndZone( $id_carrier, $zone, $_POST['id_size_shipment'] );
                } else {
                    /* Provo ad aggiornare, se non ci riesco inserisco */
                    self::updatePrice( $_POST[$key], $id_carrier, $zone, $_POST['id_size_shipment'] );
                }
            }
        }
    }

    /**
     * Elimina una riga per corriere e zona, quando il prezzo è vuoto o zero
     *
     * @param $id_carrier
     * @param $zone
     * @param $id_size_shipment
     */
    public function deleteForCarrierAndZone( $id_carrier, $zone, $id_size_shipment ) {
        global $wpdb;

        $table = self::tableName();

        $sql    = <<< SQL
        DELETE FROM {$table}
        WHERE id_carrier = {$id_carrier}
        AND id_size_shipment = {$id_size_shipment}
        AND zone = '{$zone}'
SQL;
        $result = $wpdb->query( $sql );
        return $result;
    }

    /**
     * Aggiorna un record per corriere, zona e size. Se non lo trova, lo inserisce
     *
     * @static
     *
     * @param $price
     * @param $id_carrier
     * @param $zone
     * @param $id_size_shipment
     */
    public static function updatePrice( $price, $id_carrier, $zone, $id_size_shipment ) {
        global $wpdb;

        $table = self::tableName();

        $sql = <<< SQL
        SELECT id
        FROM {$table}
        WHERE id_carrier = {$id_carrier}
        AND id_size_shipment = {$id_size_shipment}
        AND zone = '{$zone}'
SQL;

        $result = $wpdb->get_var( $sql );

        if ( is_null( $result ) ) {
            $values = array(
                'id_carrier'       => $id_carrier,
                'zone'             => $zone,
                'id_size_shipment' => $id_size_shipment,
                'price'            => $price
            );
            $result = $wpdb->insert( self::tableName(), $values );
        } else {
            $values = array(
                'price'            => $price
            );
            $where  = array(
                'id_carrier'       => $id_carrier,
                'zone'             => $zone,
                'id_size_shipment' => $id_size_shipment,
            );
            $result = $wpdb->update( self::tableName(), $values, $where );
        }
    }

    // -----------------------------------------------------------------------------------------------------------------
    // Shipping 
    // -----------------------------------------------------------------------------------------------------------------

    public static function shipmentValueForProduct( $id_product, $id_country, $id_carrier, $id_appearance = 0, $qty = 1 ) {

        /* FALSE se non spedibile o nessuna regola applicabile */
        $result = false;

        /* Verifico che il prodotto indicato sia spedibile */
        if ( WPXSmartShopProduct::shipping( $id_product ) == '1' ) {
            /* Carico le informazioni sull'aspetto (peso) per calcolare le spese */
            $rules = unserialize( get_post_meta( $id_product, 'wpss_product_appearance', true ) );

            if ( !empty( $rules ) ) {

                /* In teoria dovrei accedere alla 'variante scelta' */
                /* @todo Gestire key delle varianti - per adesso prendo il primo */
                if ( empty( $id_appearance ) ) {

                    /* Se l'id dell'aspetto non viene passato prendo la prima regola */
                    $id_appearance = key( $rules );
                }

                /* Aspetto del prodotto */
                $rule = $rules[$id_appearance];

                /* Unità di misure del corriere */
                $measure_shipping_unit = WPXSmartShopCarriers::measureShippingUnit( $id_carrier );

                /* Codice Zona in base al country - zona di spedizione */
                $zone = WPSmartShopShippingCountries::zoneShippingCountry( $id_country );

                /* In base all'unità di misura del corriere, cerco il range di speidzione */
                if ( $measure_shipping_unit == 'weight' ) {
                    /* Peso totale in base alla quantità */
                    $total_weight = $rule['weight'] * $qty;

                    /* ID della regola di shipping */
                    $id_size_shipment = self::idSizeShipmentForWeight( $total_weight );

                    /* Miglioro controllo, se non trovo la regola non posso calcolare il prezzo */
                    if( is_null( $id_size_shipment) ) {
                        $result = 0;
                    } else {
                        /* Ottengo prezzo di spedizione */
                        $result = self::price( $id_carrier, $id_size_shipment, $zone );
                    }
                }

            } else {
                /* @todo Il prodotto è spedibile ma non ha regole */
            }

        } else {
            /* Non spedibile */
        }

        return $result;
    }

    /**
     * Restituisce l'id della regola di spedizione in base al peso passato negli inputs
     *
     * @static
     * @param $weight
     * @retval mixed
     */
    public static function idSizeShipmentForWeight( $weight ) {
        global $wpdb;

        $tableSize = self::tableName( true );

        $sql    = <<< SQL
        SELECT id
        FROM {$tableSize}
        WHERE weight_from < {$weight}
        AND weight_to >= {$weight}
SQL;
        $result = $wpdb->get_var( $sql );
        return $result;
    }

    /**
     * Restituisce il prezzo per corriere, regola di shipment e zona
     *
     * @static
     *
     * @param $id_carrier
     * @param $id_size_shipment
     * @param $zone
     *
     * @retval mixed
     */
    public static function price( $id_carrier, $id_size_shipment, $zone ) {
        global $wpdb;

        $table = self::tableName();

        $sql    = <<< SQL
        SELECT price
        FROM {$table}
        WHERE id_carrier = {$id_carrier}
        AND id_size_shipment = {$id_size_shipment}
        AND zone = '{$zone}'
SQL;
        $result = $wpdb->get_var( $sql );
        if ( is_null( $result ) ) {
            return 0;
        }
        return $result;
    }
    
}
